<?php

namespace app\modules\h5\controllers;

use app\models\City;
use app\models\GoodsComment;
use app\models\GoodsExpress;
use app\models\Order;
use app\models\OrderDeliver;
use app\models\OrderItem;
use app\models\OrderLog;
use app\models\OrderRefund;
use app\models\ShopScore;
use app\models\System;
use app\models\User;
use app\models\UserAddress;
use app\models\Util;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * 订单控制器
 * Class OrderController
 * @package app\modules\h5\controllers
 */
class OrderController extends BaseController
{
    /**
     * 文件上传AJAX接口
     * @see \app\modules\h5\controllers\UploadControllerTrait
     */
    use UploadControllerTrait;

    /**
     * 确认订单页
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionConfirm()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        Yii::warning($_GET);
        return $this->render('confirm');
    }

    /**
     * 设置订单用户留言AJAX接口
     * @return array
     */
    public function actionEditRemark()
    {
        if (Yii::$app->user->isGuest) {
            return ['message' => '没有登录。'];
        }
        $remark = $this->get('remark');
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != Yii::$app->user->id) {
            return ['message' => '没有找到订单信息。'];
        }
        if (!in_array($order->status, [Order::STATUS_CREATED, Order::STATUS_PAID])) {
            return ['message' => '订单状态不允许再附加留言信息。'];
        }
        $order->user_remark = $remark;
        $r = $order->save();
        if (!$r) {
            $errors = $order->errors;
            $error = array_shift($errors)[0];
            return ['message' => $error, 'errors' => $errors];
        }
        OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '设置用户留言。', $remark);
        return ['result' => 'success'];

    }

    /**
     * 支付订单页
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionPay()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $order_no = $this->get('order_no');
        if (empty($order_no)) {
            throw new BadRequestHttpException('参数错误。');
        }
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != Yii::$app->user->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        if ($order->status != Order::STATUS_CREATED) {
            return $this->redirect(['/h5/order']);
        }
        return $this->render('pay', [
            'order' => $order,
        ]);
    }

    /**
     * 评价订单
     * @return string|array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionComment()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != Yii::$app->user->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        if (!in_array($order->status, [Order::STATUS_RECEIVED, Order::STATUS_COMPLETE])) {
            throw new BadRequestHttpException('订单状态错误，禁止评论。');
        }
        if ($this->isPost()) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                if ($order->status == Order::STATUS_RECEIVED) {
                    $shop_score = new ShopScore();
                    if (!$shop_score->load($this->post())) {
                        throw new Exception('没有找到店铺评分。');
                    }
                    $shop_score->sid = $order->sid;
                    $shop_score->uid = $order->uid;
                    $shop_score->oid = $order->id;
                    $shop_score->create_time = time();
                    if (!$shop_score->save()) {
                        throw new Exception('无法保存店铺评分。');
                    }
                }
                foreach ($order->itemList as $orderItem) {
                    $goods_comment = new GoodsComment();
                    $post_data = $this->post($goods_comment->formName());
                    if (empty($post_data) || !is_array($post_data) || !isset($post_data[$orderItem->id])) {
                        throw new Exception('参数错误。');
                    }
                    $goods_comment->setAttributes($post_data[$orderItem->id]);
                    if ($order->status == Order::STATUS_COMPLETE) {
                        /** @var GoodsComment $p_comment */
                        $p_comment = GoodsComment::find()->andWhere(['gid' => $orderItem->gid, 'uid' => $order->uid, 'oid' => $order->id, 'sku_key_name' => $orderItem->sku_key_name])->one();
                        if (!empty($p_comment)) {
                            $goods_comment->pid = $p_comment->id;
                        }
                    }
                    $goods_comment->gid = $orderItem->gid;
                    $goods_comment->uid = $order->uid;
                    $goods_comment->oid = $order->id;
                    $goods_comment->sku_key_name = $orderItem->sku_key_name;
                    $goods_comment->status = System::getConfig('comment_need_verify', 0) == 1 ? GoodsComment::STATUS_SHOW : GoodsComment::STATUS_HIDE;
                    $goods_comment->create_time = time();
                    if (!$goods_comment->save()) {
                        $errors = $goods_comment->errors;
                        $error = array_shift($errors)[0];
                        throw new Exception('无法保存对商品[' . $orderItem->title . ']的评价：' . $error . '。');
                    }
                }
                if ($order->status == Order::STATUS_RECEIVED) {
                    $order->status = Order::STATUS_COMPLETE;
                    if (!$order->save()) {
                        throw new Exception('无法保存订单状态。');
                    }
                }
                OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '发表评价。');
                $trans->commit();
                if ($this->isAjax()) {
                    return ['result' => 'success'];
                }
                return $this->redirect(['/h5/order']);
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                if ($this->isAjax()) {
                    return ['message' => $e->getMessage()];
                }
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        return $this->render('comment', [
            'order' => $order,
        ]);
    }

    /**
     * 订单列表
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        return $this->render('index');
    }

    /**
     * 订单详情
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionView()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != Yii::$app->user->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        return $this->render('view', [
            'order' => $order,
        ]);
    }

    /**
     * 订单物流信息
     * @throws NotFoundHttpException
     * @return string
     */
    public function actionDeliverInfo()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != Yii::$app->user->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        $deliver_list = OrderDeliver::find()->joinWith(['express'])->where(['oid' => $order->id])->all();
        return $this->render('deliver_info',[
            'deliver_list' => $deliver_list,
        ]);
    }

    /**
     * 申请售后服务
     * @return string|array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionRequireAfterSaleService()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $user=User::findOne(Yii::$app->user->id);
        $oiid = $this->get('oiid');
        $order_item = OrderItem::findOne($oiid);
        if (empty($order_item) || $order_item->order->uid != Yii::$app->user->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        if ($order_item->order->status != Order::STATUS_RECEIVED) {
            throw new BadRequestHttpException('订单状态异常，无法申请售后，请联系客服解决。');
        }
        //$orderItemOld = $order_item;
        //$sku=$order_item->goodsSku;
//        if ($orderItemOld->goods->is_score == 1 && $orderItemOld->order->is_score == 1) {
//            $order_item->price = round($orderItemOld->price - round($orderItemOld->goods->score * $orderItemOld->amount * System::getConfig('score_ratio') / 100, 2), 2);
//        } else {
//            if (empty($sku) || $sku->commission == '') {
//                $order_item->price = round($orderItemOld->price - round($orderItemOld->goods->share_commission_value * $user->buyRatio / 100, 2), 2);
//            }else{
//                $order_item->price = round($orderItemOld->price - round($sku->commission * $user->buyRatio / 100, 2), 2);
//            }
//        }

        $supplier_id = !empty($order_item->goods->supplier_id) ? $order_item->goods->supplier_id : null;
        $order_refund = new OrderRefund();
        $order_refund->create_time = time();
        $order_refund->oiid = $oiid;
        $order_refund->oid = $order_item->order->id;
        $order_refund->supplier_id = $supplier_id;
        $order_refund->status = OrderRefund::STATUS_REQUIRE;
        if ($this->isPost()) {
            $count = OrderRefund::find()->where(['oiid' => $oiid])->count();
            if (!empty($count)) {
                return ['message' => '该商品已经申请售后，请勿重复申请'];
            }
            $post_data = $this->post($order_refund->formName());
            $order_refund->setAttributes($post_data);
            $order_refund->image_list = Json::encode(explode(',', $order_refund->image_list));
            if (Util::comp($order_refund->money, $order_item->getRefundMoney(), 2) > 0) {
                return ['message' => '您输入的退款金额超出最大可退款金额'];
            }
            if ($order_refund->save()) {
                $order_item->order->status = Order::STATUS_AFTER_SALE;
                $order_item->order->save(false);
                OrderLog::info($order_item->order->uid, OrderLog::U_TYPE_USER, $order_item->order->id, '申请售后。', print_r($order_refund->attributes, true));
                if ($this->isAjax()) {
                    return ['result' => 'success'];
                }
            } else {
                return ['message' => '无法保存申请退货信息'];
            }
        }
        return $this->render('require_after_sale_service', [
            'order_item' => $order_item,
            'order_refund' => $order_refund,
        ]);
    }

    /**
     * 未支付 修改收货地址 AJAX接口
     * @return array | Response
     * @throws \yii\web\ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdateOrderAddress()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != Yii::$app->user->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        $address_id = $this->get('address_id');
        /** @var UserAddress $address */
        $address = UserAddress::find()->where(['uid' => Yii::$app->user->id, 'id' => $address_id])->one();
        if (empty($address)) {
            return ['message' => '收货地址不存在'];
        }
        $order->deliver_info = json_encode([
            'area' => $address->area,
            'address' => $address->address,
            'name' => $address->name,
            'mobile' => $address->mobile,
        ]);
        // 计算运费价格
        $user_city = City::findByCode($address->area);
        $p_area = substr($user_city->code,0,2).'0000';
        $c_area = substr($user_city->code,0,4).'00';
        /** @var $fee_goods_list [] */
        $fee_goods_list = [];
        foreach ($order->itemList as $item) {
            $fee_goods_list[] = ['gid' => $item->gid, 'amount' => $item->amount];
        }
        $fee = GoodsExpress::multiGoodsExpress($fee_goods_list, $p_area, $c_area);
        if (!empty($fee['message'])) {
            return ['message' => '更新地址失败 ' . $fee['message']];
        }
        if (!empty($fee['fee']) && $fee['fee'] != 0) {
            $old_deliver_fee = $order->deliver_fee;
            $order->deliver_fee = $fee['fee'];
            $order->amount_money = $order->amount_money - $old_deliver_fee + $order->deliver_fee;
        }
        if (!$order->save()) {
            return ['message' => '订单更新失败'];
        }
        OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '修改收货地址。', print_r($order->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 退货相关
     * @return string|array
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionRefundRelated()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $id = $this->get('id');
        $model = OrderRefund::findOne($id);
        if (empty($model) || $model->orderItem->order->uid != Yii::$app->user->id) {
            throw new NotFoundHttpException('没有找到退货信息。');
        }
        $model->send_time = time();
        $model->status = OrderRefund::STATUS_SEND;
        if ($this->isPost()) {
            $post_data = $this->post($model->formName());
            $model->setAttributes($post_data);
            if ($model->save()) {
                OrderLog::info($model->orderItem->order->uid, OrderLog::U_TYPE_USER, $model->orderItem->order->id, '填写提货物流信息。', print_r($model->attributes, true));
                return ['result' => 'success'];
            } else {
                return ['message' => '无法保存退货物流信息'];
            }
        }
        return $this->render('refund_related', [
            'model' => $model,
        ]);
    }

    /**
     * 退款进度
     * @return string|Response
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionRefundView()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $id = $this->get('id');
        $model = OrderRefund::findOne($id);
        return $this->render('refund_view', [
            'model' => $model,
        ]);
    }

    /**
     * 退款信息
     * @return string|Response
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionRefund()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $query = Order::find();
        $item = $query->joinWith('itemList')
            ->where(['uid' => Yii::$app->user->id])
            ->andWhere(['<>', 'status', Order::STATUS_DELETE])
            ->select('{{%order_item}}.id')
            ->all();
        $oiid = ArrayHelper::getColumn($item, 'id');
        $query = OrderRefund::find();
        $query->select(["id", "oiid", "amount","money", "type", "reason", "image_list", "status", "express_name",
            "express_no", "contact_mobile", "create_time"]);
        $query->where(['in', 'oiid' , $oiid]);
        $query->andWhere(['<>', 'status', OrderRefund::STATUS_DELETE]);
        $list = $query->orderBy('create_time DESC')->all();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        return $this->render('refund',[
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 退款信息
     * @return array
     */
    public function actionDeleteRefund()
    {
        if (Yii::$app->user->isGuest) {
            return ['message' => '没有登录。'];
        }
        $id = $this->get('id');
        $model = OrderRefund::findOne($id);
        if (empty($model) || $model->orderItem->order->uid != Yii::$app->user->id) {
            return ['message' => '没有找到退货信息。'];
        }
        $model->status = OrderRefund::STATUS_DELETE;
        if ($model->save()) {
            return ['result' => 'success'];
        } else {
            return ['massage' => '无法删除退款申请'];
        }
    }


    /**
     * 优惠券活动支付成功页面
     * @return string

     */
    public function actionCouponPayCallback()
    {

        return $this->render('coupon_pay_callback');
    }
}
