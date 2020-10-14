<?php

namespace app\modules\admin\controllers;

use app\models\City;
use app\models\FakerUser;
use app\models\FinanceLog;
use app\models\FullCut;
use app\models\FullCutGoods;
use app\models\FullCutPreference;
use app\models\Coupon;
use app\models\Discount;
use app\models\DiscountGoods;
use app\models\Goods;
use app\models\GoodsSku;
use app\models\GroupBuy;
use app\models\GroupBuyGroup;
use app\models\GroupBuySku;
use app\models\GroupBuyUser;
use app\models\KeyMap;
use app\models\Kuaidi100;
use app\models\ManagerLog;
use app\models\Order;
use app\models\OrderDeliverAddress;
use app\models\OrderItem;
use app\models\OrderLog;
use app\models\Red;
use app\models\RedSpecial;
use app\models\ScoreCouponUser;
use app\models\ScoreDiscount;
use app\models\ScoreDiscountGoods;
use app\models\ScoreCoupon;
use app\models\ScoreGoods;
use app\models\ScoreGoodsUser;
use app\models\ScoreLevel;
use app\models\ScoreLucky;
use app\models\ScoreLuckyGift;
use app\models\ScoreLuckyUser;
use app\models\ScoreTryout;
use app\models\ScoreTryoutUser;
use app\models\System;
use app\models\Task;
use app\models\UCenterApi;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserRed;
use app\models\Util;
use app\modules\api\models\ApiException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * 营销管理
 * Class MarketingController
 * @package app\modules\admin\controllers
 */
class MarketingController extends BaseController
{
    /**
     * 文件上传AJAX接口
     * @see \app\controllers\UploadControllerTrait
     */
    use UploadControllerTrait;

    /**
     * 减折价列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionDiscount()
    {
        if (!$this->manager->can('marketing/discount')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Discount::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $discountList = $query->all();
        return $this->render('discount', [
            'discountList' => $discountList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 减折价详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDiscountView()
    {
        if (!$this->manager->can('marketing/discount')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $discount = Discount::findOne(['id' => $id]);
        if (empty($discount)) {
            throw new NotFoundHttpException('没有找到减折价信息。');
        }
        return $this->render('discount_view', [
            'discount' => $discount,
        ]);
    }

    /**
     * 添加/修改减折价
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDiscountEdit()
    {
        if (!$this->manager->can('marketing/discount')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $discount = Discount::findOne(['id' => $id]);
            if (empty($discount)) {
                throw new NotFoundHttpException('没有找到减折价。');
            }
        } else {
            $discount = new Discount();
            $discount->status = Discount::STATUS_EDIT;
            $discount->create_time = time();
        }
        if ($discount->load($this->post()) && $discount->validate()) {
            try {
                Yii::$app->db->transaction(function () use (&$discount) {
                    if (!$discount->save()) {
                        return false;
                    }
                    ManagerLog::info($this->manager->id, '保存减折价', print_r($discount->attributes, true));
                    DiscountGoods::deleteAll(['did' => $discount->id]);
                    $postDiscountGoodsList = $this->post('DiscountGoods');
                    if (is_array($postDiscountGoodsList)) {
                        foreach ($postDiscountGoodsList as $gid => $postDiscountGoods) {
                            $discountGoods = new DiscountGoods();
                           $discountGoods->did = $discount->id;
                            $discountGoods->gid = $gid;
                            $discountGoods->setAttributes($postDiscountGoods);

                            if (!$discountGoods->save(true)) {
                                $error=$discountGoods->getFirstErrors();
                              throw new Exception('无法保存商品减折价信息：' . reset($error));

                            }
                        }
                    }
                    Yii::$app->session->addFlash('success', '数据已保存。');
                    Yii::$app->session->setFlash('redirect', json_encode([
                        'url' => Url::to(['/admin/marketing/discount']),
                        'txt' => '减折价列表'
                    ]));
                    return true;
                });
            } catch (\Throwable $e) {
                $discount->id = null;
                Yii::$app->session->addFlash('warning', $e->getMessage());
            }
        }
        return $this->render('discount_edit', [
            'discount' => $discount,
        ]);
    }

    /**
     * 减折价商品列表AJAX接口
     * @throws ForbiddenHttpException
     * @return array
     */
    public function actionDiscountGoodsList()
    {
        if (!$this->manager->can('marketing/discount')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $did = $this->get('did');
        $discount = Discount::findOne(['id' => $did]);
        if (empty($discount)) {
            return [
                'message' => '没有找到减折价。',
            ];
        }
        $discount_goods_list = [];
        foreach ($discount->getDiscountGoodsList()->each() as $discountGoods) {
            /** @var $discountGoods DiscountGoods */
            $discount_goods_list[] = [
                'type' => $discountGoods->type,
                'price' => $discountGoods->price,
                'ratio' => $discountGoods->ratio,
                'goods' => [
                    'id' => $discountGoods->goods->id,
                    'main_pic' => Util::fileUrl($discountGoods->goods->main_pic, false, '_32x32'),
                    'title' => $discountGoods->goods->title,
                    'price' => $discountGoods->goods->price,
                    'hour' => empty($discountGoods->hour) ? '' : $discountGoods->hour,
                   // 'stock' => $discountGoods->goods->getAllStock(),
//                    'amount' => $discountGoods->goods->getSaleAmount(),
                ],
            ];
        }
        return ['result' => 'success', 'discount_goods_list' => $discount_goods_list];
    }

    /**
     * 启动减折价AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDiscountStart()
    {
        if (!$this->manager->can('marketing/discount')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $discount = Discount::findOne(['id' => $id]);
        if (empty($discount)) {
            return [
                'message' => '没有找到减折价。',
            ];
        }
        if ($discount->status != Discount::STATUS_EDIT) {
            return [
                'message' => '减折价状态错误。',
            ];
        }
//        $discount_start=Discount::find()->where(['status'=>Discount::STATUS_RUNNING])->one();
//        if(!empty($discount_start))
//        {
//            return [
//                'message' => '已存在正在进行的限时活动。',
//            ];
//        }

        $discount->status = Discount::STATUS_RUNNING;
        $discount->save(false);
        ManagerLog::info($this->manager->id, '启动减折价：' . $discount->id);
        return ['result' => 'success'];
    }

    /**
     * 暂停减折价AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDiscountStop()
    {
        if (!$this->manager->can('marketing/discount')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $discount = Discount::findOne(['id' => $id]);
        if (empty($discount)) {
            return [
                'message' => '没有找到减折价。',
            ];
        }
        if ($discount->status != Discount::STATUS_RUNNING) {
            return [
                'message' => '减折价状态错误。',
            ];
        }
        $discount->status = Discount::STATUS_EDIT;
        $discount->save(false);
        ManagerLog::info($this->manager->id, '暂停减折价：' . $discount->id);
        return ['result' => 'success'];
    }

    /**
     * 减折价商品设置展示小时数AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionSetDiscountGoodsHour()
    {
        if (!$this->manager->can('marketing/discount')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $gid=$this->get('gid');
        $hour=$this->get('hour');
        $did = $this->get('did');
        if (empty($hour) || empty($gid) || empty($did) || $hour<=0) {
            return [
                'message' => '参数错误。',
            ];
        }
        $discount = Discount::findOne(['id' => $did]);
        if (empty($discount)) {
            return [
                'message' => '没有找到限时活动。',
            ];
        }
        if($discount->end_time < time())
        {
            return [
                'message' => '活动已经结束。',
            ];
        }
        $second =$discount->end_time - $discount->start_time;//获取活动总时间秒数

        $hour_count=intval($second/3600);//总小时数 取整
        if($hour > $hour_count)
        {
            return [
                'message' => '设置展示小时数不能大于'.$hour_count,
            ];
        }
        /** @var $discount_goods DiscountGoods*/
        $discount_goods=DiscountGoods::find()->where(['gid' => $gid,'did' => $did])->one();
        if (empty($discount_goods)) {
            return [
                'message' => '没有找到该活动商品。',
            ];
        }
        $discount_goods->hour =$hour;
        $discount_goods->save(false);
        return ['result' => 'success','hour_count' => $hour_count];
    }


    /**
     * 优惠券列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionCoupon()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Coupon::find();
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andFilterWhere(['status' => $this->get('search_status')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $coupon_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('coupon', [
            'coupon_list' => $coupon_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 优惠券详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionCouponView()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $coupon = Coupon::findOne(['id' => $id]);
        if (empty($coupon)) {
            throw new NotFoundHttpException('没有找到优惠券信息。');
        }
        return $this->render('coupon_view', [
            'coupon' => $coupon,
        ]);
    }

    /**
     * 添加修改优惠券
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionCouponEdit()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $coupon = Coupon::findOne(['id' => $id]);
            if (empty($coupon)) {
                throw new NotFoundHttpException('没有找到优惠券信息。');
            }
        } else {
            $coupon = new Coupon();
            $coupon->status = Coupon::STATUS_EDIT;
            $coupon->create_time = time();
        }
        if ($coupon->load($this->post()) && $coupon->save()) {
            ManagerLog::info($this->manager->id, '保存优惠券', print_r($coupon->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/marketing/coupon']),
                'txt' => '优惠券列表'
            ]));
        }
        return $this->render('coupon_edit', [
            'coupon' => $coupon,
        ]);
    }

    /**
     * 优惠券商品列表AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionCouponGoodsList()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $gidList = $this->get('gid_list');
        if (!is_array($gidList)) {
            $gidList = explode(',', $gidList);
        }
        $goodsList = [];
        /** @var Goods $goods */
        foreach (Goods::find()->andWhere(['id' => $gidList])->each() as $goods) {
            $goodsList[] = [
                'id' => $goods->id,
                'title' => $goods->title,
                'price' => $goods->price,
                'stock' => $goods->getAllStock(),
                'amount' => $goods->getSaleAmount(),
            ];
        }
        return ['result' => 'success', 'goods_list' => $goodsList];
    }

    /**
     * 启动优惠券AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionCouponStart()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $coupon = Coupon::findOne(['id' => $id]);
        if (empty($coupon)) {
            return [
                'message' => '没有找到优惠券。',
            ];
        }
        if ($coupon->status != Coupon::STATUS_EDIT) {
            return [
                'message' => '优惠券状态错误。',
            ];
        }
        $coupon->status = Coupon::STATUS_RUNNING;
        $coupon->save(false);
        ManagerLog::info($this->manager->id, '启动优惠券：' . $coupon->id);
        return ['result' => 'success'];
    }

    /**
     * 暂停优惠券AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionCouponStop()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $coupon = Coupon::findOne(['id' => $id]);
        if (empty($coupon)) {
            return [
                'message' => '没有找到优惠券。',
            ];
        }
        if ($coupon->status != Coupon::STATUS_RUNNING) {
            return [
                'message' => '优惠券状态错误。',
            ];
        }
        $coupon->status = Coupon::STATUS_EDIT;
        $coupon->save(false);
        ManagerLog::info($this->manager->id, '暂停优惠券：' . $coupon->id);
        return ['result' => 'success'];
    }

    /**
     * 发送优惠券
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    public function actionSendCoupon()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $coupon = Coupon::findOne(['id' => $id]);
        if (empty($coupon)) {
            throw new NotFoundHttpException('没有找到优惠券信息。');
        }
        if ($coupon->status != Coupon::STATUS_RUNNING) {
            throw new ServerErrorHttpException('优惠券状态错误。');
        }
        if ($this->isPost()) {
            $postMobileList = $this->post('mobile_list');
            if (empty($postMobileList)) {
                throw new BadRequestHttpException('没有找到手机号码。');
            }
            $mobileList = preg_split('/\D/', $postMobileList, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($mobileList)) {
                throw new BadRequestHttpException('没有找到手机号码。');
            }
            $errorMobileList = []; // 出错的手机号码
            $emptyMobileList = []; // 没有注册的手机号码
            $sendMobileList = []; // 已发送手机号码
            $api = new UCenterApi();
            foreach ($mobileList as $mobile) {
                try {
                    $userList = $api->userList(['user_phone' => $mobile, 'user_state' => 0]);
                    if (empty($userList)) {
                        $errorMobileList[] = $mobile;
                        continue;
                    }
                    $userId = $userList[0]['user_id'];
                    $user = User::findOne(['id' => $userId]);
                    if (empty($user)) {
                        $emptyMobileList[] = $mobile;
                        continue;
                    }
                    $userCoupon = new UserCoupon();
                    $userCoupon->uid = $user->id;
                    $userCoupon->cid = $coupon->id;
                    $userCoupon->receive_time = time();
                    if ($coupon->discount_type == Coupon::DISCOUNT_TYPE_FIXED) {
                        $userCoupon->money = $coupon->discount_money;
                    } else {
                        $userCoupon->money = rand(1, $coupon->discount_money);
                    }
                    $userCoupon->status = UserCoupon::STATUS_WAIT;
                    $userCoupon->save();
                    $sendMobileList[] = $mobile;
                } catch (ApiException $e) {
                    throw new ServerErrorHttpException($e->getMessage());
                }
            }
            ManagerLog::info($this->manager->id, '发送优惠券[' . $coupon->id . ']', print_r($postMobileList, true));
            Yii::$app->session->addFlash('success', '发送成功[' . count($sendMobileList) . ']条。');
            if (!empty($errorMobileList)) {
                Yii::$app->session->addFlash('warning', '手机号码错误：<br />' . implode(',', $errorMobileList));
            }
            if (!empty($emptyMobileList)) {
                Yii::$app->session->addFlash('warning', '未注册手机号码：<br />' . implode(',', $emptyMobileList));
            }
        }
        return $this->render('coupon_send', [
            'coupon' => $coupon,
        ]);
    }

    /**
     * 用户优惠券列表
     * @return string
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCouponUser()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = UserCoupon::find()->alias('user_coupon');
        $query->andFilterWhere(['user_coupon.cid' => $this->get('search_cid')]);
        $search_mobile = $this->get('search_mobile');
        if (!empty($search_mobile)) {
            try {
                $user_list = (new UCenterApi())->userList([
                    'user_phone' => $search_mobile,
                    'user_state' => 0,
                ]);
                if (!empty($user_list)) {
                    $query->andWhere(['user_coupon.uid' => ArrayHelper::getColumn($user_list, 'user_id')]);
                }
            } catch (ApiException $e) {
                throw new ServerErrorHttpException($e->getMessage());
            }
        }
        $search_coupon_name = $this->get('search_coupon_name');
        if (!empty($search_coupon_name)) {
            $query->joinWith('coupon coupon')->andWhere(['like', 'coupon.name', $search_coupon_name]);
        }
        $query->andFilterWhere(['user_coupon.status' => $this->get('search_status')]);
        $search_start_date = $this->get('search_start_date');
        if (!empty($search_start_date)) {
            $query->andFilterWhere(['>=', 'user_coupon.receive_time', strtotime($search_start_date)]);
        }
        $search_end_date = $this->get('search_end_date');
        if (!empty($search_end_date)) {
            $query->andFilterWhere(['<', 'user_coupon.receive_time', strtotime($search_end_date) + 86400]);
        }
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '用户编号',
                         '用户手机',
                         '用户昵称',
                         '优惠券编号',
                         '优惠券名称',
                         '金额',
                         '领取时间',
                         '使用时间',
                         '状态',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var UserCoupon $userCoupon */
            foreach ($query->each() as $userCoupon) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $userCoupon->id, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $userCoupon->uid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $userCoupon->user->user_phone, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, $userCoupon->user->user_nick_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $userCoupon->cid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, $userCoupon->coupon->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, $userCoupon->money, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, Yii::$app->formatter->asDatetime($userCoupon->receive_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(9, $r, Yii::$app->formatter->asDatetime($userCoupon->use_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(10, $r, KeyMap::getValue('user_coupon_status', $userCoupon->status), DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="优惠券领取列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $user_coupon_list = $query->orderBy('user_coupon.id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('coupon_user', [
            'user_coupon_list' => $user_coupon_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 用户优惠券详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionCouponUserView()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $userCoupon = UserCoupon::findOne(['id' => $id]);
        if (empty($userCoupon)) {
            throw new NotFoundHttpException('没有找到用户优惠券信息。');
        }
        return $this->render('coupon_user_view', [
            'user_coupon' => $userCoupon,
        ]);
    }

    /**
     * 优惠券统计分析
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionCouponStatistics()
    {
        if (!$this->manager->can('marketing/coupon')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $coupon = Coupon::findOne(['id' => $id]);
        if (empty($coupon)) {
            throw new NotFoundHttpException('没有找到优惠券信息。');
        }
        return $this->render('coupon_statistics', [
            'coupon' => $coupon,
        ]);
    }

    /**
     * 满减列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionFullCut()
    {
        if (!$this->manager->can('marketing/full-cut')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = FullCut::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $fullCutList = $query->all();
        return $this->render('full_cut', [
            'fullCutList' => $fullCutList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 满减详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionFullCutView()
    {
        if (!$this->manager->can('marketing/full-cut')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $fullCut = FullCut::findOne(['id' => $id]);
        if (empty($fullCut)) {
            throw new NotFoundHttpException('没有找到满减信息。');
        }
        return $this->render('full_cut_view', [
            'fullCut' => $fullCut,
        ]);
    }

    /**
     * 添加/修改满减
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionFullCutEdit()
    {
        if (!$this->manager->can('marketing/full-cut')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $fullCut = FullCut::findOne(['id' => $id]);
            if (empty($fullCut)) {
                throw new NotFoundHttpException('没有找到满减。');
            }
        } else {
            $fullCut = new FullCut();
            $fullCut->status = FullCut::STATUS_EDIT;
            $fullCut->create_time = time();
        }
        if ($fullCut->load($this->post())) {
            $fullCut->remote_area_list = $this->post('remote_area_list');
            if ($fullCut->validate()) {
                try {
                    Yii::$app->db->transaction(function () use (&$fullCut) {
                        $is_new_full_cut = $fullCut->isNewRecord;
                        if (!$fullCut->save()) {
                            return false;
                        }
                        ManagerLog::info($this->manager->id, '保存满减', print_r($fullCut->attributes, true));
                        // 处理商品
                        FullCutGoods::deleteAll(['fid' => $fullCut->id]);
                        $gid_list = $this->post('gid_list'); // 商品编号列表JSON
                        if (!empty($gid_list)) {
                            $gid_list = json_decode($gid_list, true);
                            foreach ($gid_list as $gid) {
                                $fullCutGoods = new FullCutGoods();
                                $fullCutGoods->fid = $fullCut->id;
                                $fullCutGoods->gid = $gid;
                                if (!$fullCutGoods->save()) {
                                    throw new Exception('无法保存满减商品，商品编号[' . $gid . ']。');
                                }
                            }
                        }
                        ManagerLog::info($this->manager->id, '保存满减商品', print_r($gid_list, true));
                        if ($is_new_full_cut) {
                            // 处理优惠
                            FullCutPreference::deleteAll(['fid' => $fullCut->id]);
                            $postPreferenceList = $this->post('Preference');
                            if (empty($postPreferenceList) || !is_array($postPreferenceList)) {
                                throw new Exception('优惠不能为空。');
                            }
                            foreach ($postPreferenceList as $postPreference) {
                                $preference = new FullCutPreference();
                                if (!$preference->load($postPreference, '')) {
                                    throw new Exception('无法读取提交的优惠内容。');
                                }
                                $preference->fid = $fullCut->id;
                                if (!$preference->save()) {
                                    throw new Exception('无法保存优惠内容：<br />' . implode('<br />', $preference->getErrorSummary(true)));
                                }
                                ManagerLog::info($this->manager->id, '保存满减优惠', print_r($preference->attributes, true));
                            }
                        }
                        Yii::$app->session->addFlash('success', '数据已保存。');
                        Yii::$app->session->setFlash('redirect', json_encode([
                            'url' => Url::to(['/admin/marketing/full-cut']),
                            'txt' => '满减列表'
                        ]));
                        return true;
                    });
                } catch (\Throwable $e) {
                    $fullCut->id = null;
                    Yii::$app->session->addFlash('warning', $e->getMessage());
                }
            }
        }
        return $this->render('full_cut_edit', [
            'fullCut' => $fullCut,
        ]);
    }

    /**
     * 满减商品列表AJAX接口
     * @throws ForbiddenHttpException
     * @return array
     */
    public function actionFullCutGoodsList()
    {
        if (!$this->manager->can('marketing/full-cut')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $fid = $this->get('fid');
        $idList = $this->post('id_list', []);
        $query = null;
        if (!empty($fid)) {
            $fullCut = FullCut::findOne(['id' => $fid]);
            if (empty($fullCut)) {
                return [
                    'message' => '没有找到满减。',
                ];
            }
            $query = $fullCut->getGoodsList();
        } else {
            $query = Goods::find()->andWhere(['id' => $idList]);
        }
        $goods_list = [];
        foreach ($query->each() as $chose_goods) {
            /** @var $chose_goods Goods */
            $goods_list[] = [
                'id' => $chose_goods->id,
                'main_pic' => Util::fileUrl($chose_goods->main_pic, false, '_32x32'),
                'title' => $chose_goods->title,
                'price' => $chose_goods->price,
                'stock' => $chose_goods->getAllStock(),
                'amount' => $chose_goods->getSaleAmount(),
            ];
        }
        return ['result' => 'success', 'goods_list' => $goods_list];
    }

    /**
     * 启动满减AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionFullCutStart()
    {
        if (!$this->manager->can('marketing/full-cut')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $fullCut = FullCut::findOne(['id' => $id]);
        if (empty($fullCut)) {
            return [
                'message' => '没有找到满减。',
            ];
        }
        if ($fullCut->status != FullCut::STATUS_EDIT) {
            return [
                'message' => '满减状态错误。',
            ];
        }
        $fullCut->status = FullCut::STATUS_RUNNING;
        $fullCut->save(false);
        ManagerLog::info($this->manager->id, '启动满减：' . $fullCut->id);
        return ['result' => 'success'];
    }

    /**
     * 暂停满减AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionFullCutStop()
    {
        if (!$this->manager->can('marketing/full-cut')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $fullCut = FullCut::findOne(['id' => $id]);
        if (empty($fullCut)) {
            return [
                'message' => '没有找到满减。',
            ];
        }
        if ($fullCut->status != FullCut::STATUS_RUNNING) {
            return [
                'message' => '满减状态错误。',
            ];
        }
        $fullCut->status = FullCut::STATUS_EDIT;
        $fullCut->save(false);
        ManagerLog::info($this->manager->id, '暂停满减：' . $fullCut->id);
        return ['result' => 'success'];
    }

    /**
     * 积分规则
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionScoreRules()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        if ($this->isPost()) {
            $score_rules = $this->post('score_rules');
            try {
                System::setConfig('score_rules', $score_rules);
                ManagerLog::info($this->manager->id, '保存积分规则', $score_rules);
                Yii::$app->session->addFlash('success', '数据已保存。');
            } catch (Exception $e) {
                Yii::$app->session->addFlash('warning', '系统错误：' . $e->getMessage());
            }
        }
        return $this->render('score_rules', [
            'rules' => System::getConfig('score_rules'),
        ]);
    }

    /**
     * 积分等级
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionScoreLevel()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreLevel::find();
        $query->orderBy('level ASC');
        $scoreLevelList = $query->all();
        return $this->render('score_level', [
            'scoreLevelList' => $scoreLevelList,
        ]);
    }

    /**
     * 添加修改积分等级
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreLevelEdit()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $scoreLevel = ScoreLevel::findOne(['id' => $id]);
            if (empty($scoreLevel)) {
                throw new NotFoundHttpException('没有找到积分等级。');
            }
        } else {
            $scoreLevel = new ScoreLevel();
        }
        if ($scoreLevel->load($this->post()) && $scoreLevel->save()) {
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/marketing/score-level']),
                'txt' => '积分等级列表'
            ]));
        }
        return $this->render('score_level_edit', [
            'scoreLevel' => $scoreLevel,
        ]);
    }

    /**
     * 积分抵扣
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionScoreDiscount()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreDiscount::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $scoreDiscountList = $query->all();
        return $this->render('score_discount', [
            'scoreDiscountList' => $scoreDiscountList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 积分抵扣详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreDiscountView()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreDiscount = ScoreDiscount::findOne(['id' => $id]);
        if (empty($scoreDiscount)) {
            throw new NotFoundHttpException('没有找到积分抵扣信息。');
        }
        return $this->render('score_discount_view', [
            'scoreDiscount' => $scoreDiscount,
        ]);
    }

    /**
     * 添加/修改积分抵扣
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreDiscountEdit()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $scoreDiscount = ScoreDiscount::findOne(['id' => $id]);
            if (empty($scoreDiscount)) {
                throw new NotFoundHttpException('没有找到积分抵扣。');
            }
        } else {
            $scoreDiscount = new ScoreDiscount();
            $scoreDiscount->status = ScoreDiscount::STATUS_EDIT;
            $scoreDiscount->create_time = time();
        }
        if ($scoreDiscount->load($this->post()) && $scoreDiscount->validate()) {
            try {
                Yii::$app->db->transaction(function () use (&$scoreDiscount) {
                    if (!$scoreDiscount->save()) {
                        return false;
                    }
                    ManagerLog::info($this->manager->id, '保存积分抵扣', print_r($scoreDiscount->attributes, true));
                    ScoreDiscountGoods::deleteAll(['sdid' => $scoreDiscount->id]);
                    $postDiscountGoodsList = $this->post('ScoreDiscountGoods');
                    if (is_array($postDiscountGoodsList)) {
                        foreach ($postDiscountGoodsList as $gid) {
                            $discountGoods = new ScoreDiscountGoods();
                            $discountGoods->sdid = $scoreDiscount->id;
                            $discountGoods->gid = $gid;
                            if (!$discountGoods->save()) {
                                throw new Exception('无法保存商品积分抵扣信息：' . implode('<br />', $discountGoods->getErrorSummary(true)));
                            }
                        }
                    }
                    Yii::$app->session->addFlash('success', '数据已保存。');
                    Yii::$app->session->setFlash('redirect', json_encode([
                        'url' => Url::to(['/admin/marketing/score-discount']),
                        'txt' => '积分抵扣列表'
                    ]));
                    return true;
                });
            } catch (\Throwable $e) {
                $scoreDiscount->id = null;
                Yii::$app->session->addFlash('warning', $e->getMessage());
            }
        }
        return $this->render('score_discount_edit', [
            'score_discount' => $scoreDiscount,
        ]);
    }

    /**
     * 积分抵扣商品列表AJAX接口
     * @throws ForbiddenHttpException
     * @return array
     */
    public function actionScoreDiscountGoodsList()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $sdid = $this->get('sdid');
        $discount = ScoreDiscount::findOne(['id' => $sdid]);
        if (empty($discount)) {
            return [
                'message' => '没有找到积分抵扣。',
            ];
        }
        $discount_goods_list = [];
        foreach ($discount->getScoreDiscountGoodsList()->each() as $discountGoods) {
            /** @var $discountGoods ScoreDiscountGoods */
            $discount_goods_list[] = [
                'goods' => [
                    'id' => $discountGoods->goods->id,
                    'main_pic' => Util::fileUrl($discountGoods->goods->main_pic, false, '_32x32'),
                    'title' => $discountGoods->goods->title,
                    'price' => $discountGoods->goods->price,
                    'stock' => $discountGoods->goods->getAllStock(),
                    'amount' => $discountGoods->goods->getSaleAmount(),
                ],
            ];
        }
        return ['result' => 'success', 'discount_goods_list' => $discount_goods_list];
    }

    /**
     * 启动积分抵扣AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreDiscountStart()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $discount = ScoreDiscount::findOne(['id' => $id]);
        if (empty($discount)) {
            return [
                'message' => '没有找到积分抵扣。',
            ];
        }
        if ($discount->status != ScoreDiscount::STATUS_EDIT) {
            return [
                'message' => '积分抵扣状态错误。',
            ];
        }
        $discount->status = ScoreDiscount::STATUS_RUNNING;
        $discount->save(false);
        ManagerLog::info($this->manager->id, '启动积分抵扣：' . $discount->id);
        return ['result' => 'success'];
    }

    /**
     * 暂停积分抵扣AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreDiscountStop()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $discount = ScoreDiscount::findOne(['id' => $id]);
        if (empty($discount)) {
            return [
                'message' => '没有找到积分抵扣。',
            ];
        }
        if ($discount->status != ScoreDiscount::STATUS_RUNNING) {
            return [
                'message' => '积分抵扣状态错误。',
            ];
        }
        $discount->status = ScoreDiscount::STATUS_EDIT;
        $discount->save(false);
        ManagerLog::info($this->manager->id, '暂停积分抵扣：' . $discount->id);
        return ['result' => 'success'];
    }

    /**
     * 积分兑换优惠券
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionScoreCoupon()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreCoupon::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $scoreCouponList = $query->all();
        return $this->render('score_coupon', [
            'scoreCouponList' => $scoreCouponList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 积分兑换优惠券详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreCouponView()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreCoupon = ScoreCoupon::findOne(['id' => $id]);
        if (empty($scoreCoupon)) {
            throw new NotFoundHttpException('没有找到积分兑换优惠券。');
        }
        return $this->render('score_coupon_view', [
            'scoreCoupon' => $scoreCoupon,
        ]);
    }

    /**
     * 添加修改积分兑换优惠券
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreCouponEdit()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $scoreCoupon = ScoreCoupon::findOne(['id' => $id]);
            if (empty($scoreCoupon)) {
                throw new NotFoundHttpException('没有找到积分兑换优惠券。');
            }
        } else {
            $scoreCoupon = new ScoreCoupon();
            $scoreCoupon->status = ScoreCoupon::STATUS_EDIT;
            $scoreCoupon->create_time = time();
        }
        if ($scoreCoupon->load($this->post()) && $scoreCoupon->save()) {
            ManagerLog::info($this->manager->id, '保存积分兑换优惠券', print_r($scoreCoupon->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/marketing/score-coupon']),
                'txt' => '积分兑换优惠券列表'
            ]));
        }
        return $this->render('score_coupon_edit', [
            'scoreCoupon' => $scoreCoupon,
        ]);
    }

    /**
     * 启动积分兑换优惠券AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreCouponStart()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreCoupon = ScoreCoupon::findOne(['id' => $id]);
        if (empty($scoreCoupon)) {
            return [
                'message' => '没有找到积分兑换优惠券。',
            ];
        }
        if ($scoreCoupon->status != ScoreCoupon::STATUS_EDIT) {
            return [
                'message' => '积分兑换优惠券状态错误。',
            ];
        }
        $scoreCoupon->status = ScoreCoupon::STATUS_RUNNING;
        $scoreCoupon->save(false);
        ManagerLog::info($this->manager->id, '启动积分兑换优惠券：' . $scoreCoupon->id);
        return ['result' => 'success'];
    }

    /**
     * 暂停积分兑换优惠券AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreCouponStop()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreCoupon = ScoreCoupon::findOne(['id' => $id]);
        if (empty($scoreCoupon)) {
            return [
                'message' => '没有找到积分兑换优惠券。',
            ];
        }
        if ($scoreCoupon->status != ScoreCoupon::STATUS_RUNNING) {
            return [
                'message' => '积分兑换优惠券状态错误。',
            ];
        }
        $scoreCoupon->status = ScoreCoupon::STATUS_EDIT;
        $scoreCoupon->save(false);
        ManagerLog::info($this->manager->id, '暂停积分兑换优惠券：' . $scoreCoupon->id);
        return ['result' => 'success'];
    }

    /**
     * 积分兑换优惠券列表
     * @return string
     * @throws ForbiddenHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionScoreCouponUser()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreCouponUser::find();
        $query->joinWith('scoreCoupon');
        $query->andFilterWhere(['cid' => $this->get('search_cid')]);
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '用户编号',
                         '用户手机',
                         '用户昵称',
                         '积分数量',
                         '优惠券编号',
                         '优惠券名称',
                         '金额',
                         '兑换时间',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var ScoreCouponUser $scoreCouponUser */
            foreach ($query->each() as $scoreCouponUser) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $scoreCouponUser->id, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $scoreCouponUser->uid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $scoreCouponUser->user->user_phone, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, $scoreCouponUser->user->user_nick_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $scoreCouponUser->scoreCoupon->score, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, $scoreCouponUser->scoreCoupon->cid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, $scoreCouponUser->scoreCoupon->coupon->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, $scoreCouponUser->scoreCoupon->coupon->discount_money, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(9, $r, Yii::$app->formatter->asDatetime($scoreCouponUser->create_time), DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="积分兑换优惠券用户列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $scoreCouponUserList = $query->all();
        return $this->render('score_coupon_user', [
            'scoreCouponUserList' => $scoreCouponUserList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 积分兑换商品
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionScoreGoods()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreGoods::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $scoreGoodsList = $query->all();
        return $this->render('score_goods', [
            'scoreGoodsList' => $scoreGoodsList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 积分兑换商品详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreGoodsView()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreGoods = ScoreGoods::findOne(['id' => $id]);
        if (empty($scoreGoods)) {
            throw new NotFoundHttpException('没有找到积分兑换商品。');
        }
        return $this->render('score_goods_view', [
            'scoreGoods' => $scoreGoods,
        ]);
    }

    /**
     * 添加修改积分兑换商品
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreGoodsEdit()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $scoreGoods = ScoreGoods::findOne(['id' => $id]);
            if (empty($scoreGoods)) {
                throw new NotFoundHttpException('没有找到积分兑换商品。');
            }
        } else {
            $scoreGoods = new ScoreGoods();
            $scoreGoods->status = ScoreGoods::STATUS_EDIT;
            $scoreGoods->create_time = time();
        }
        if ($scoreGoods->load($this->post()) && $scoreGoods->save()) {
            ManagerLog::info($this->manager->id, '保存积分兑换商品', print_r($scoreGoods->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/marketing/score-goods']),
                'txt' => '积分兑换商品列表'
            ]));
        }
        return $this->render('score_goods_edit', [
            'scoreGoods' => $scoreGoods,
        ]);
    }

    /**
     * 启动积分兑换商品AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreGoodsStart()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreGoods = ScoreGoods::findOne(['id' => $id]);
        if (empty($scoreGoods)) {
            return [
                'message' => '没有找到积分兑换商品。',
            ];
        }
        if ($scoreGoods->status != ScoreGoods::STATUS_EDIT) {
            return [
                'message' => '积分兑换商品状态错误。',
            ];
        }
        $scoreGoods->status = ScoreGoods::STATUS_RUNNING;
        $scoreGoods->save(false);
        ManagerLog::info($this->manager->id, '启动积分兑换商品：' . $scoreGoods->id);
        return ['result' => 'success'];
    }

    /**
     * 暂停积分兑换商品AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreGoodsStop()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreGoods = ScoreGoods::findOne(['id' => $id]);
        if (empty($scoreGoods)) {
            return [
                'message' => '没有找到积分兑换商品。',
            ];
        }
        if ($scoreGoods->status != ScoreGoods::STATUS_RUNNING) {
            return [
                'message' => '积分兑换商品状态错误。',
            ];
        }
        $scoreGoods->status = ScoreGoods::STATUS_EDIT;
        $scoreGoods->save(false);
        ManagerLog::info($this->manager->id, '暂停积分兑换商品：' . $scoreGoods->id);
        return ['result' => 'success'];
    }

    /**
     * 积分兑换商品列表
     * @return string
     * @throws ForbiddenHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionScoreGoodsUser()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreGoodsUser::find();
        $query->joinWith('scoreGoods');
        $query->andFilterWhere(['gid' => $this->get('search_gid')]);
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '用户编号',
                         '用户手机',
                         '用户昵称',
                         '积分数量',
                         '现金数量',
                         '商品编号',
                         '商品名称',
                         '兑换时间',
                         '订单号',
                         '订单状态',
                         '收货地址',
                         '收货人',
                         '收货人手机',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var ScoreGoodsUser $scoreGoodsUser */
            foreach ($query->each() as $scoreGoodsUser) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $scoreGoodsUser->id, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $scoreGoodsUser->uid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $scoreGoodsUser->user->user_phone, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, $scoreGoodsUser->user->user_nick_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $scoreGoodsUser->scoreGoods->score, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, $scoreGoodsUser->scoreGoods->money, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, $scoreGoodsUser->scoreGoods->gid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, $scoreGoodsUser->scoreGoods->goods->title, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(9, $r, Yii::$app->formatter->asDatetime($scoreGoodsUser->create_time), DataType::TYPE_STRING);
                if (!empty($scoreGoodsUser->oid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(10, $r, $scoreGoodsUser->order->no, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(11, $r, KeyMap::getValue('order_status', $scoreGoodsUser->order->status), DataType::TYPE_STRING);
                    if (!empty($scoreGoodsUser->order->deliver_info)) {
                        $area = $scoreGoodsUser->order->getDeliverInfoJson('area');
                        $city = City::findByCode($area);
                        $sheet->setCellValueExplicitByColumnAndRow(12, $r, implode(' ', $city->address()) . ' ' . $scoreGoodsUser->order->getDeliverInfoJson('address'), DataType::TYPE_STRING);
                        $sheet->setCellValueExplicitByColumnAndRow(13, $r, emoji_unified_to_docomo($scoreGoodsUser->order->getDeliverInfoJson('name')), DataType::TYPE_STRING);
                        $sheet->setCellValueExplicitByColumnAndRow(14, $r, $scoreGoodsUser->order->getDeliverInfoJson('mobile'), DataType::TYPE_STRING);
                    }
                }
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="积分兑换商品用户列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $scoreGoodsUserList = $query->all();
        return $this->render('score_goods_user', [
            'scoreGoodsUserList' => $scoreGoodsUserList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 积分抽奖
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionScoreLucky()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreLucky::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $scoreLuckyList = $query->all();
        return $this->render('score_lucky', [
            'scoreLuckyList' => $scoreLuckyList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 积分抽奖详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreLuckyView()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreLucky = ScoreLucky::findOne(['id' => $id]);
        if (empty($scoreLucky)) {
            throw new NotFoundHttpException('没有找到积分抽奖。');
        }
        return $this->render('score_lucky_view', [
            'scoreLucky' => $scoreLucky,
        ]);
    }

    /**
     * 添加修改积分抽奖
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreLuckyEdit()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $scoreLucky = ScoreLucky::findOne(['id' => $id]);
            if (empty($scoreLucky)) {
                throw new NotFoundHttpException('没有找到积分抽奖。');
            }
        } else {
            $scoreLucky = new ScoreLucky();
            $scoreLucky->status = ScoreLucky::STATUS_EDIT;
            $scoreLucky->create_time = time();
        }
        if ($scoreLucky->load($this->post()) && $scoreLucky->validate()) {
            try {
                Yii::$app->db->transaction(function () use (&$scoreLucky) {
                    if (!$scoreLucky->save()) {
                        return false;
                    }
                    ManagerLog::info($this->manager->id, '保存积分抽奖', print_r($scoreLucky->attributes, true));
                    $postScoreLuckyGiftList = $this->post('ScoreLuckyGift');
                    if (is_array($postScoreLuckyGiftList)) {
                        foreach ($postScoreLuckyGiftList as $giftId => $postGift) {
                            if ($giftId > 0) {
                                $gift = ScoreLuckyGift::findOne(['id' => $giftId]);
                            } else {
                                $gift = new ScoreLuckyGift();
                                $gift->slid = $scoreLucky->id;
                            }
                            $gift->setAttributes($postGift);
                            if (!$gift->save()) {
                                throw new Exception('无法保存奖品信息：' . implode('<br />', $gift->getErrorSummary(true)));
                            }
                        }
                        if (array_sum(array_column($postScoreLuckyGiftList, 'amount_in_base')) > $scoreLucky->base_amount) {
                            throw new Exception('数量每基数总额超过了中奖基数。');
                        }
                    }
                    Yii::$app->session->addFlash('success', '数据已保存。');
                    Yii::$app->session->setFlash('redirect', json_encode([
                        'url' => Url::to(['/admin/marketing/score-lucky']),
                        'txt' => '积分抽奖列表'
                    ]));
                    return true;
                });
            } catch (\Throwable $e) {
                Yii::$app->session->addFlash('warning', $e->getMessage());
            }
        }
        return $this->render('score_lucky_edit', [
            'scoreLucky' => $scoreLucky,
        ]);
    }

    /**
     * 启动积分抽奖AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreLuckyStart()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreLucky = ScoreLucky::findOne(['id' => $id]);
        if (empty($scoreLucky)) {
            return [
                'message' => '没有找到积分抽奖。',
            ];
        }
        if ($scoreLucky->status != ScoreLucky::STATUS_EDIT) {
            return [
                'message' => '积分抽奖状态错误。',
            ];
        }
        $scoreLucky->status = ScoreLucky::STATUS_RUNNING;
        $scoreLucky->save(false);
        ManagerLog::info($this->manager->id, '启动积分抽奖：' . $scoreLucky->id);
        return ['result' => 'success'];
    }

    /**
     * 暂停积分抽奖AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreLuckyStop()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreLucky = ScoreLucky::findOne(['id' => $id]);
        if (empty($scoreLucky)) {
            return [
                'message' => '没有找到积分抽奖。',
            ];
        }
        if ($scoreLucky->status != ScoreLucky::STATUS_RUNNING) {
            return [
                'message' => '积分抽奖状态错误。',
            ];
        }
        $scoreLucky->status = ScoreLucky::STATUS_EDIT;
        $scoreLucky->save(false);
        ManagerLog::info($this->manager->id, '暂停积分抽奖：' . $scoreLucky->id);
        return ['result' => 'success'];
    }

    /**
     * 积分抽奖用户记录
     * @return string
     * @throws ForbiddenHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionScoreLuckyUser()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreLuckyUser::find()->alias('luckyUser');
        $query->joinWith(['lucky lucky', 'gift gift']);
        $query->andWhere('luckyUser.uid > 0');
        $query->andFilterWhere(['luckyUser.slid' => $this->get('search_slid')]);
        $query->andFilterWhere(['luckyUser.status' => $this->get('search_status')]);
        $query->andFilterWhere(['gift.type' => $this->get('search_slg_type')]);
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '用户编号',
                         '用户手机',
                         '用户昵称',
                         '积分抽奖编号',
                         '积分数量',
                         '奖品编号',
                         '奖品名称',
                         '奖品类型',
                         '奖励积分数量',
                         '奖励优惠券编号',
                         '奖励优惠券名称',
                         '奖励商品编号',
                         '奖励商品名称',
                         '抽奖时间',
                         '发奖时间',
                         '收货地址',
                         '收货人',
                         '收货手机',
                         '快递名称',
                         '快递编号',
                         '收货时间',
                         '状态',
                         '备注',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var ScoreLuckyUser $scoreLuckyUser */
            foreach ($query->each() as $scoreLuckyUser) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $scoreLuckyUser->id, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $scoreLuckyUser->uid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $scoreLuckyUser->user->user_phone, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, $scoreLuckyUser->user->user_nick_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $scoreLuckyUser->slid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, $scoreLuckyUser->lucky->score, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, $scoreLuckyUser->slgid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, $scoreLuckyUser->gift->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(9, $r, KeyMap::getValue('score_lucky_gift_type', $scoreLuckyUser->gift->type), DataType::TYPE_STRING);
                if ($scoreLuckyUser->gift->type == ScoreLuckyGift::TYPE_SCORE) {
                    $sheet->setCellValueExplicitByColumnAndRow(10, $r, $scoreLuckyUser->gift->score, DataType::TYPE_NUMERIC);
                } elseif ($scoreLuckyUser->gift->type == ScoreLuckyGift::TYPE_COUPON) {
                    $sheet->setCellValueExplicitByColumnAndRow(11, $r, $scoreLuckyUser->gift->cid, DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(12, $r, $scoreLuckyUser->gift->coupon->name, DataType::TYPE_STRING);
                } elseif ($scoreLuckyUser->gift->type == ScoreLuckyGift::TYPE_GOODS) {
                    $sheet->setCellValueExplicitByColumnAndRow(13, $r, $scoreLuckyUser->gift->gid, DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(14, $r, $scoreLuckyUser->gift->goods->title, DataType::TYPE_STRING);
                }
                $sheet->setCellValueExplicitByColumnAndRow(15, $r, Yii::$app->formatter->asDatetime($scoreLuckyUser->lucky_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(16, $r, Yii::$app->formatter->asDatetime($scoreLuckyUser->gift_time), DataType::TYPE_STRING);
                if (!empty($scoreLuckyUser->deliver_info)) {
                    $city = City::findByCode($scoreLuckyUser->getDeliverInfoJson('area'));
                    $sheet->setCellValueExplicitByColumnAndRow(17, $r, implode(' ', $city->address()) . $scoreLuckyUser->getDeliverInfoJson('address'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(18, $r, $scoreLuckyUser->getDeliverInfoJson('name'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(19, $r, $scoreLuckyUser->getDeliverInfoJson('mobile'), DataType::TYPE_STRING);
                }
                $sheet->setCellValueExplicitByColumnAndRow(20, $r, $scoreLuckyUser->express_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(21, $r, $scoreLuckyUser->express_no, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(22, $r, Yii::$app->formatter->asDatetime($scoreLuckyUser->receive_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(23, $r, KeyMap::getValue('score_lucky_user_status', $scoreLuckyUser->status), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(24, $r, $scoreLuckyUser->remark, DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="积分抽奖用户列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('luckyUser.id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $scoreLuckyUserList = $query->all();
        return $this->render('score_lucky_user', [
            'scoreLuckyUserList' => $scoreLuckyUserList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 积分抽奖用户记录发货AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreLuckyUserDeliver()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $express_info = $this->get('express_info');
        if (empty($express_info)) {
            return ['result' => 'failure', 'message' => '没有找到物流信息。'];
        }
        $express_info = preg_split('/\s/', $express_info, -1, PREG_SPLIT_NO_EMPTY);
        if (count($express_info) != 2) {
            return ['result' => 'failure', 'message' => '物流信息格式错误，格式为：快递名称 快递编号。'];
        }
        $scoreLuckyUser = ScoreLuckyUser::findOne(['id' => $id]);
        if (empty($scoreLuckyUser)) {
            throw new NotFoundHttpException('没有找到积分抽奖用户记录。');
        }
        if ($scoreLuckyUser->gift->type != ScoreLuckyGift::TYPE_GOODS) {
            return ['result' => 'failure', 'message' => '不是实物奖品不需要物流信息。'];
        }
        if ($scoreLuckyUser->status != ScoreLuckyUser::STATUS_USER) {
            return ['result' => 'failure', 'message' => '状态错误，无法发货。'];
        }
        $scoreLuckyUser->status = ScoreLuckyUser::STATUS_SEND;
        $scoreLuckyUser->gift_time = time();
        $scoreLuckyUser->express_name = $express_info[0];
        $scoreLuckyUser->express_no = $express_info[1];
        if (!$scoreLuckyUser->save()) {
            return ['result' => 'failure', 'message' => '无法保存物流信息。', 'errors' => $scoreLuckyUser->errors];
        }
        Kuaidi100::poll($scoreLuckyUser->express_no, null, [
            'type' => 'score_lucky_user',
            'id' => $scoreLuckyUser->id,
        ]);
        return [
            'result' => 'success',
            'deliver_info' => json_decode($scoreLuckyUser->deliver_info),
        ];
    }

    /**
     * 积分试用
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionScoreTryout()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreTryout::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $scoreTryoutList = $query->all();
        return $this->render('score_tryout', [
            'scoreTryoutList' => $scoreTryoutList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 积分试用详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreTryoutView()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreTryout = ScoreTryout::findOne(['id' => $id]);
        if (empty($scoreTryout)) {
            throw new NotFoundHttpException('没有找到积分试用。');
        }
        return $this->render('score_tryout_view', [
            'scoreTryout' => $scoreTryout,
        ]);
    }

    /**
     * 添加修改积分试用
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScoreTryoutEdit()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $scoreTryout = ScoreTryout::findOne(['id' => $id]);
            if (empty($scoreTryout)) {
                throw new NotFoundHttpException('没有找到积分试用。');
            }
        } else {
            $scoreTryout = new ScoreTryout();
            $scoreTryout->status = ScoreTryout::STATUS_EDIT;
            $scoreTryout->create_time = time();
        }
        if ($scoreTryout->load($this->post()) && $scoreTryout->save()) {
            ManagerLog::info($this->manager->id, '保存积分试用', print_r($scoreTryout->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/marketing/score-tryout']),
                'txt' => '积分试用',
            ]));
        }
        return $this->render('score_tryout_edit', [
            'scoreTryout' => $scoreTryout,
        ]);
    }

    /**
     * 启动积分试用AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreTryoutStart()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreTryout = ScoreTryout::findOne(['id' => $id]);
        if (empty($scoreTryout)) {
            return [
                'message' => '没有找到积分试用。',
            ];
        }
        if ($scoreTryout->status != ScoreTryout::STATUS_EDIT) {
            return [
                'message' => '积分试用状态错误。',
            ];
        }
        $scoreTryout->status = ScoreTryout::STATUS_RUNNING;
        $scoreTryout->save(false);
        ManagerLog::info($this->manager->id, '启动积分试用：' . $scoreTryout->id);
        return ['result' => 'success'];
    }

    /**
     * 暂停积分试用AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreTryoutStop()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreTryout = ScoreTryout::findOne(['id' => $id]);
        if (empty($scoreTryout)) {
            return [
                'message' => '没有找到积分试用。',
            ];
        }
        if ($scoreTryout->status != ScoreTryout::STATUS_RUNNING) {
            return [
                'message' => '积分试用状态错误。',
            ];
        }
        $scoreTryout->status = ScoreTryout::STATUS_EDIT;
        $scoreTryout->save(false);
        ManagerLog::info($this->manager->id, '暂停积分试用：' . $scoreTryout->id);
        return ['result' => 'success'];
    }

    /**
     * 结束积分试用AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionScoreTryoutOff()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreTryout = ScoreTryout::findOne(['id' => $id]);
        if (empty($scoreTryout)) {
            return [
                'message' => '没有找到积分试用。',
            ];
        }
        if ($scoreTryout->status != ScoreTryout::STATUS_WAIT) {
            return [
                'message' => '积分试用状态错误。',
            ];
        }
        $scoreTryout->status = ScoreTryout::STATUS_FINISH;
        $scoreTryout->save(false);
        // 将没中奖的人的积分都退回
        $task = new Task();
        $task->u_type = Task::U_TYPE_MANAGER;
        $task->uid = $this->manager->id;
        $task->name = '积分试用退积分';
        $task->next = 0;
        $task->todo = json_encode([
            'class' => ScoreTryout::class,
            'method' => 'task_return_score',
            'params' => $scoreTryout->id
        ]);
        $task->status = Task::STATUS_WAITING;
        $task->save();
        ManagerLog::info($this->manager->id, '结束积分试用：' . $scoreTryout->id);
        return ['result' => 'success'];
    }

    /**
     * 积分试用用户记录列表
     * @return string
     * @throws ForbiddenHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionScoreTryoutUser()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ScoreTryoutUser::find();
        $query->andFilterWhere(['stid' => $this->get('search_stid')]);
        $query->andFilterWhere(['status' => $this->get('search_status')]);
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '用户编号',
                         '用户手机',
                         '用户昵称',
                         '积分试用编号',
                         '积分数量',
                         '商品编号',
                         '商品名称',
                         '订单号',
                         '收货地址',
                         '收货人',
                         '收货手机',
                         '状态',
                         '申请时间',
                         '备注',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var ScoreTryoutUser $scoreTryoutUser */
            foreach ($query->each() as $scoreTryoutUser) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $scoreTryoutUser->id, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $scoreTryoutUser->uid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $scoreTryoutUser->user->user_phone, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, $scoreTryoutUser->user->user_nick_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $scoreTryoutUser->stid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, $scoreTryoutUser->scoreTryout->score, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, $scoreTryoutUser->scoreTryout->gid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, $scoreTryoutUser->scoreTryout->goods->title, DataType::TYPE_STRING);
                if (!empty($scoreTryoutUser->oid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(9, $r, $scoreTryoutUser->order->no, DataType::TYPE_STRING);
                }
                if (!empty($scoreTryoutUser->deliver_info)) {
                    $city = City::findByCode($scoreTryoutUser->getDeliverInfoJson('area'));
                    $sheet->setCellValueExplicitByColumnAndRow(10, $r, implode(' ', $city->address()) . $scoreTryoutUser->getDeliverInfoJson('address'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(11, $r, $scoreTryoutUser->getDeliverInfoJson('name'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(12, $r, $scoreTryoutUser->getDeliverInfoJson('mobile'), DataType::TYPE_STRING);
                }
                $sheet->setCellValueExplicitByColumnAndRow(13, $r, KeyMap::getValue('score_tryout_user_status', $scoreTryoutUser->status), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(14, $r, Yii::$app->formatter->asDatetime($scoreTryoutUser->create_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(15, $r, $scoreTryoutUser->remark, DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="积分试用用户列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $scoreTryoutUserList = $query->all();
        return $this->render('score_tryout_user', [
            'scoreTryoutUserList' => $scoreTryoutUserList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 通过积分试用申请
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionAcceptScoreTryoutUser()
    {
        if (!$this->manager->can('marketing/score')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $scoreTryoutUser = ScoreTryoutUser::findOne(['id' => $id]);
        if (empty($scoreTryoutUser)) {
            throw new NotFoundHttpException('没有找到积分试用申请。');
        }
        if ($scoreTryoutUser->status != ScoreTryoutUser::STATUS_WAIT) {
            return ['result' => 'failure', 'message' => '状态错误，无法通过申请。'];
        }
        if ($scoreTryoutUser->scoreTryout->status != ScoreTryout::STATUS_WAIT) {
            return ['result' => 'failure', 'message' => '积分试用活动状态错误，无法通过申请。'];
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            /** @var GoodsSku $sku */
            $sku = GoodsSku::find()->andWhere(['gid' => $scoreTryoutUser->scoreTryout->gid])->one();
            // 创建订单
            $order = new Order();
            $order->uid = $scoreTryoutUser->uid;
            $order->sid = $scoreTryoutUser->scoreTryout->goods->sid;
            $order->goods_money = $sku->price;
            $order->is_score_tryout = 1;
            $order->score_money = $sku->price;
            $order->deliver_info = $scoreTryoutUser->deliver_info;
            $order->deliver_fee = $scoreTryoutUser->scoreTryout->deliver_fee;
            $order->status = Order::STATUS_CREATED;
            $order->create_time = time();
            if (!$order->save()) {
                throw new Exception('无法创建用户订单。');
            }
            $orderDeliverAddress = new OrderDeliverAddress();
            $orderDeliverAddress->oid = $order->id;
            $orderDeliverAddress->area = $scoreTryoutUser->getDeliverInfoJson('area');
            $orderDeliverAddress->address = $scoreTryoutUser->getDeliverInfoJson('address');
            $orderDeliverAddress->name = $scoreTryoutUser->getDeliverInfoJson('name');
            $orderDeliverAddress->mobile = $scoreTryoutUser->getDeliverInfoJson('mobile');
            $orderDeliverAddress->time = time();
            if (!$orderDeliverAddress->save()) {
                throw new Exception('无法保存订单收货地址。');
            }
            $orderItem = new OrderItem();
            $orderItem->oid = $order->id;
            $orderItem->gid = $scoreTryoutUser->scoreTryout->gid;
            $orderItem->title = $scoreTryoutUser->scoreTryout->goods->title;
            $orderItem->sku_key_name = $sku->key_name;
            $orderItem->amount = 1;
            $orderItem->price = $sku->price;
            $orderItem->score_money = $orderItem->price;
            $orderItem->d_price = 0;
            if (!$orderItem->save()) {
                throw new Exception('无法创建用户订单内容。');
            }
            $scoreTryoutUser->status = ScoreTryoutUser::STATUS_ACCEPT;
            $scoreTryoutUser->oid = $order->id;
            $scoreTryoutUser->save(false);
            OrderLog::info($this->manager->id, OrderLog::U_TYPE_MANAGER, $order->id, '创建积分试用订单。', print_r($scoreTryoutUser, true));
            if (Util::comp($order->deliver_fee, 0, 2) == 0) {
                // 没有运费，订单支付金额为0，直接设置为已支付
                $finance_log = new FinanceLog();
                $finance_log->type = FinanceLog::TYPE_ORDER_PAY;
                $finance_log->create_time = time();
                $finance_log->money = 0;
                $finance_log->pay_method = FinanceLog::PAY_METHOD_YE;
                $finance_log->status = FinanceLog::STATUS_WAIT;
                if (!$finance_log->save()) {
                    Yii::error(json_encode($finance_log->errors));
                    throw new Exception('无法保存财务记录。');
                }
                $order->fid = $finance_log->id;
                if (!$order->save(false)) {
                    Yii::error(json_encode($order->errors));
                    throw new Exception('无法更新订单财务关联。');
                }
                if (empty($finance_log->trade_no)) {
                    $finance_log->refreshTradeNo($order->uid);
                }
                $finance_log->save();
                // 余额扣款
                (new UCenterApi())->accountPurchase($order->uid, $finance_log->trade_no, $finance_log->money, System::getConfig('site_name') . ' - 支付订单');
                FinanceLog::payNotify($finance_log->trade_no, $finance_log->money, null);
                OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '生成支付信息。', print_r($finance_log->attributes, true));
            }
            $trans->commit();
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $_) {
            }
            return ['result' => 'failure', 'message' => $e->getMessage()];
        }
        ManagerLog::info($this->manager->id, '通过用户积分试用申请：' . $scoreTryoutUser->id);
        return ['result' => 'success'];
    }

    /**
     * 红包列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionRed()
    {
        if (!$this->manager->can('marketing/red')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Red::find();
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $red_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('red', [
            'red_list' => $red_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 红包详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRedView()
    {
        if (!$this->manager->can('marketing/red')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $red = Red::findOne(['id' => $id]);
        if (empty($red)) {
            throw new NotFoundHttpException('没有找到红包信息。');
        }
        return $this->render('red_view', [
            'red' => $red,
        ]);
    }

    /**
     * 添加修改红包
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionRedEdit()
    {
        if (!$this->manager->can('marketing/red')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $red = Red::findOne(['id' => $id]);
            if (empty($red)) {
                throw new NotFoundHttpException('没有找到红包信息。');
            }
        } else {
            $red = new Red();
            $red->create_time = time();
        }
        if ($red->load($this->post())) {
            if (Util::comp($red->amount_money_limit, 0, 2) <= 0) {
                throw new ServerErrorHttpException('发送金额错误。');
            }
            if ($red->amount_count_limit <= 0) {
                throw new ServerErrorHttpException('数量错误。');
            }
            $special_red_list = [];
            $special_red_money = $special_red_count = 0;
            if ($red->money_type == Red::MONEY_TYPE_RANDOM) { // 随机金额
                $special_red_list = $this->post('SpecialRed');
                if ($special_red_list) {
                    foreach ($special_red_list as $special_red) {
                        $special_red_money += $special_red['money'] * $special_red['count'];
                        $special_red_count += $special_red['count'];
                    }
                    if ($red->amount_money_limit < $special_red_money || $red->amount_count_limit < $special_red_count) {
                        $red->addError('amount_money_limit', '特殊红包总金额限制错误，不能超过红包总额限制金额。');
                    }
                }
                if (!empty($red->errors)) {
                    return $this->render('red_edit', [
                        'red' => $red,
                    ]);
                }
            } else {
                if (intval($red->amount_money_limit / $red->money) < 1) {
                    $red->addError('amount_money_limit', '固定金额红包总价单价必须大于一个红包。');
                }
            }
            if ($red->save()) {
                if (!empty($special_red_list)) {
                    foreach ($special_red_list as $special_red) {
                        $red_special = new RedSpecial();
                        $red_special->rid = $red->id;
                        $red_special->money = $special_red['money'];
                        $red_special->count = $special_red['count'];
                        $red_special->save();
                    }
                }
                $task = new Task();
                $task->u_type = Task::U_TYPE_MANAGER;
                $task->uid = $this->manager->id;
                $task->name = '生成预制红包';
                $task->next = 0;
                $task->todo = json_encode([
                    'class' => UserRed::class,
                    'method'=>'task_set_user_red',
                    'params' => $red->id
                ]);
                $task->status = Task::STATUS_WAITING;
                $task->save();

                ManagerLog::info($this->manager->id, '保存红包', print_r($red->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/marketing/red']),
                    'txt' => '红包列表'
                ]));
            }
        }
        return $this->render('red_edit', [
            'red' => $red,
        ]);
    }

    /**
     * 用户红包列表
     * @return string
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRedUser()
    {
        if (!$this->manager->can('marketing/red')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = UserRed::find();
        $query->andFilterWhere(['rid' => $this->get('search_rid')]);
        $search_mobile = $this->get('search_mobile');
        if (!empty($search_mobile)) {
            try {
                $user_list = (new UCenterApi())->userList([
                    'user_phone' => $search_mobile,
                    'user_state' => 0,
                ]);
                if (!empty($user_list)) {
                    $query->andWhere(['uid' => ArrayHelper::getColumn($user_list, 'user_id')]);
                }
            } catch (Exception $e) {
                throw new ServerErrorHttpException($e->getMessage());
            }
        }
        $query->andFilterWhere(['status' => $this->get('search_status')]);
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '用户编号',
                         '用户手机',
                         '用户昵称',
                         '红包编号',
                         '获取时间',
                         '使用时间',
                         '红包金额',
                         '状态',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var UserRed $userRed */
            foreach ($query->each() as $userRed) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $userRed->id, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $userRed->uid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $userRed->user->user_phone, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, $userRed->user->user_nick_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $userRed->rid, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, Yii::$app->formatter->asDatetime($userRed->receive_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, Yii::$app->formatter->asDatetime($userRed->use_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, $userRed->money, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(9, $r, KeyMap::getValue('user_red_status', $userRed->status), DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="用户红包列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $user_red_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('red_user', [
            'user_red_list' => $user_red_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 用户红包详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRedUserView()
    {
        if (!$this->manager->can('marketing/red')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $userRed = UserRed::findOne(['id' => $id]);
        if (empty($userRed)) {
            throw new NotFoundHttpException('没有找到用户红包信息。');
        }
        return $this->render('red_user_view', [
            'user_red' => $userRed,
        ]);
    }

    /**
     * 红包统计分析
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRedStatistics()
    {
        if (!$this->manager->can('marketing/red')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $red = Red::findOne(['id' => $id]);
        if (empty($red)) {
            throw new NotFoundHttpException('没有找到红包信息。');
        }
        return $this->render('red_statistics', [
            'red' => $red,
        ]);
    }

    /**
     * 拼团列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionGroupBuy()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GroupBuy::find();
        $query->andFilterWhere(['title' => $this->get('search_title')]);
        $query->andFilterWhere(['status' => $this->get('search_status')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $groupBuyList = $query->all();
        return $this->render('group_buy', [
            'groupBuyList' => $groupBuyList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 拼团规则
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionGroupBuyRules()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        if ($this->isPost()) {
            $group_buy_rules = $this->post('group_buy_rules');
            try {
                System::setConfig('group_buy_rules', $group_buy_rules);
                ManagerLog::info($this->manager->id, '保存拼团规则', $group_buy_rules);
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/marketing/group-buy']),
                    'txt' => '拼团列表'
                ]));
            } catch (Exception $e) {
                Yii::$app->session->addFlash('warning', '系统错误：' . $e->getMessage());
            }
        }
        return $this->render('group_buy_rules', [
            'rules' => System::getConfig('group_buy_rules'),
        ]);
    }

    /**
     * 添加修改拼团
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionGroupBuyEdit()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $groupBuy = GroupBuy::findOne(['id' => $id]);
            if (empty($groupBuy)) {
                throw new NotFoundHttpException('没有找到拼团信息。');
            }
        } else {
            $groupBuy = new GroupBuy();
            $groupBuy->create_time = time();
            $groupBuy->status = GroupBuy::STATUS_EDIT;
        }
        $setGid = $this->get('set_gid');
        if (!empty($setGid)) {
            $groupBuy->gid = $setGid;
        }
        if ($this->isPost()) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                if (!$groupBuy->load($this->post())) {
                    throw new Exception('无法加载数据。');
                }
                $groupBuy->remote_area_list = $this->post('remote_area_list');
                if (!$groupBuy->save()) {
                    throw new Exception('无法保存拼团信息。');
                }
                ManagerLog::info($this->manager->id, '保存拼团', print_r($groupBuy->attributes, true));
                GroupBuySku::deleteAll(['gid' => $groupBuy->id]);
                $postSkuList = $this->post('GroupBuySku');
                if (empty($postSkuList) || !is_array($postSkuList)) {
                    throw new Exception('拼团规格错误。');
                }
                foreach ($postSkuList as $postSku) {
                    $sku = new GroupBuySku();
                    $sku->gid = $groupBuy->id;
                    $sku->setAttributes($postSku);
                    if (!$sku->save()) {
                        Yii::$app->session->addFlash('warning', implode('<br />', $sku->getErrorSummary(true)));
                        throw new Exception('拼团规格无法保存。');
                    }
                    ManagerLog::info($this->manager->id, '保存拼团规格', print_r($sku->attributes, true));
                }

                $trans->commit();
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/marketing/group-buy']),
                    'txt' => '拼团列表'
                ]));
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $_) {
                }
                Yii::$app->session->addFlash('warning', $e->getMessage());
            }
        }
        return $this->render('group_buy_edit', [
            'groupBuy' => $groupBuy,
        ]);
    }

    /**
     * 拼团详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionGroupBuyView()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $groupBuy = GroupBuy::findOne(['id' => $id]);
        if (empty($groupBuy)) {
            throw new NotFoundHttpException('没有找到拼团信息。');
        }
        return $this->render('group_buy_view', [
            'groupBuy' => $groupBuy,
        ]);
    }

    /**
     * 启动拼团AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionGroupBuyStart()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $groupBuy = GroupBuy::findOne(['id' => $id]);
        if (empty($groupBuy)) {
            return [
                'message' => '没有找到拼团信息。',
            ];
        }
        if ($groupBuy->status != GroupBuy::STATUS_EDIT) {
            return [
                'message' => '拼团状态错误。',
            ];
        }
        $groupBuy->status = GroupBuy::STATUS_RUNNING;
        $groupBuy->save(false);
        ManagerLog::info($this->manager->id, '启动拼团：' . $groupBuy->id);
        return ['result' => 'success'];
    }

    /**
     * 暂停拼团AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionGroupBuyStop()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $groupBuy = GroupBuy::findOne(['id' => $id]);
        if (empty($groupBuy)) {
            return [
                'message' => '没有找到拼团信息。',
            ];
        }
        if ($groupBuy->status != GroupBuy::STATUS_RUNNING) {
            return [
                'message' => '拼团状态错误。',
            ];
        }
        $groupBuy->status = GroupBuy::STATUS_EDIT;
        $groupBuy->save(false);
        ManagerLog::info($this->manager->id, '暂停拼团：' . $groupBuy->id);
        return ['result' => 'success'];
    }

    /**
     * 拼团团列表
     * @return string
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     */
    public function actionGroupBuyGroup()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $search_user_phone = $this->get('search_mobile');
        $query = GroupBuyGroup::find()->alias('group_buy_group');
        $query->joinWith(['groupBuy group_buy']);
        $query->andFilterWhere(['like', 'group_buy.title', $this->get('search_title')]);
        $query->andFilterWhere(['group_buy_group.gid' => $this->get('search_gid')]);
        if (!empty($search_user_phone)) {
            try {
                $user_list = (new UCenterApi())->userList([
                    'user_phone' => $search_user_phone
                ]);
                $query->andWhere(['group_buy_group.uid' => ArrayHelper::getColumn($user_list, 'user_id')]);
            } catch (ApiException $e) {
                throw new ServerErrorHttpException($e->getMessage());
            }
        }
        $query->andFilterWhere(['group_buy_group.status' => $this->get('search_status')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('group_buy_group.id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $groupList = $query->all();
        return $this->render('group_buy_group', [
            'groupList' => $groupList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 拼团团详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionGroupBuyGroupView()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $group = GroupBuyGroup::findOne(['id' => $this->get('id')]);
        if (empty($group)) {
            throw new NotFoundHttpException('没有找到团信息。');
        }
        return $this->render('group_buy_group_view', [
            'group' => $group,
        ]);
    }

    /**
     * 拼团团添加虚拟成员AJAX接口
     * @return array
     */
    public function actionGroupBuyGroupAddVirtualUser()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            return ['result' => 'failure', 'message' => '没有权限。'];
        }
        $group = GroupBuyGroup::findOne(['id' => $this->get('gid')]);
        if (empty($group)) {
            return ['result' => 'failure', 'message' => '没有找到拼团团信息。'];
        }
        if ($group->status != GroupBuyGroup::STATUS_WAIT) {
            return ['result' => 'failure', 'message' => '拼团状态错误，只有等待成团时才可以添加。'];
        }
        if ($group->getJoinUserCount() >= $group->groupBuy->complete_user_amount) {
            return ['result' => 'failure', 'message' => '拼团已经满员了。'];
        }
        $virtualUser = new GroupBuyUser();
        $virtualUser->gid = $group->id;
        for ($i = 0; $i < 100; $i++) {
            /** @var FakerUser $fakerUser */
            $fakerUser = FakerUser::find()
                ->orderBy(new Expression('RAND()'))
                ->one();
            if (empty($fakerUser)) {
                // 随机生成昵称及头像
                $virtualUser->virtual_user = json_encode([
                    'user_nick_name' => '匿名用户',
                    'user_logo' => Yii::$app->params['site_host'] . '/images/user_icon_03.png',
                ]);
                break;
            } else {
                $virtualUser->virtual_user = json_encode([
                    'user_nick_name' => $fakerUser->nickname,
                    'user_logo' => Util::fileUrl($fakerUser->avatar, true),
                ]);
                if (!GroupBuyUser::find()
                    ->andWhere(['gid' => $group->id])
                    ->andWhere(['uid' => null])
                    ->andWhere(['virtual_user' => $virtualUser->virtual_user])
                    ->exists()) {
                    break;
                }
                // 虚拟用户重复了，再重新随机查一个
            }
        }
        /** @var GroupBuySku $sku */
        $sku = GroupBuySku::find()->andWhere(['gid' => $group->gid])->one();
        $virtualUser->sku_name = $sku->key_name;
        $virtualUser->price = $sku->d_price;
        $virtualUser->amount = 1;
        $virtualUser->is_free_charge = 0;
        $virtualUser->status = GroupBuyUser::STATUS_SUCCESS;
        $virtualUser->create_time = time();
        $virtualUser->save();
        if ($group->getJoinUserCount() >= $group->groupBuy->complete_user_amount) {
            // 成了
            $group->status = GroupBuyGroup::STATUS_SUCCESS;
            $group->complete_time = time();
            $group->save();
            // 设置免单
            if ($group->groupBuy->mode == GroupBuy::MODE_FREE_CHARGE) {
                $task = new Task();
                $task->u_type = Task::U_TYPE_MANAGER;
                $task->uid = 1;
                $task->name = '自动设置免单用户';
                $task->next = time() + System::getConfig('group_buy_auto_set_free_charge_minute', 30) * 60;
                $task->todo = json_encode([
                    'class' => GroupBuyGroup::class,
                    'method'=>'task_set_free_charge',
                    'params' => $group->id
                ]);
                $task->status = Task::STATUS_WAITING;
                $task->save();
            }
        }
        return ['result' => 'success'];
    }

    /**
     * 设置拼团成员免单AJAX接口
     * @return array
     */
    public function actionGroupBuySetFreeCharge()
    {
        if (!$this->manager->can('marketing/group-buy')) {
            return ['result' => 'failure', 'message' => '没有权限。'];
        }
        $id = $this->get('id');
        $groupBuyUser = GroupBuyUser::findOne(['id' => $id]);
        if (empty($groupBuyUser)) {
            return ['result' => 'failure', 'message' => '没有找到拼团成员信息。'];
        }
        if ($groupBuyUser->group->status != GroupBuyGroup::STATUS_SUCCESS) {
            return ['result' => 'failure', 'message' => '还没有成团。'];
        }
        if ($groupBuyUser->group->groupBuy->mode != GroupBuy::MODE_FREE_CHARGE) {
            return ['result' => 'failure', 'message' => '此团不是免单团。'];
        }
        if ($groupBuyUser->group->groupBuy->free_charge_choose_mode != GroupBuy::FREE_CHARGE_CHOOSE_MANUALLY) {
            return ['result' => 'failure', 'message' => '拼团设置为随机抽取免单，不能手动抽取。'];
        }
        if (!empty($groupBuyUser->oid)) {
            if (in_array($groupBuyUser->order->status, [
                Order::STATUS_PAID,
                Order::STATUS_PACKING,
                Order::STATUS_PACKED,
                Order::STATUS_DELIVERED,
                Order::STATUS_RECEIVED,
                Order::STATUS_COMPLETE,
            ])) {
                return ['result' => 'failure', 'message' => '订单没有支付。'];
            }
        }
        if (GroupBuyUser::find()
            ->andWhere(['gid' => $groupBuyUser->gid])
            ->andWhere(['is_free_charge' => 1])
            ->count() >= $groupBuyUser->group->groupBuy->free_charge_user_amount) {
            return ['result' => 'failure', 'message' => '免单人数已经达到数量。'];
        }
        if (empty($groupBuyUser->uid)) { // 虚拟成员
            $groupBuyUser->updateAttributes(['is_free_charge' => 1]);
        } else {
            try {
                $groupBuyUser->doFreeChargeRefund();
            } catch (Exception $e) {
                return ['result' => 'failure', 'message' => $e->getMessage()];
            }
        }
        return ['result' => 'success'];
    }
}
