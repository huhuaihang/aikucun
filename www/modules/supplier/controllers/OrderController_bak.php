<?php

namespace app\modules\supplier\controllers;

use app\models\City;
use app\models\Express;
use app\models\ExpressPrintTemplate;
use app\models\FinanceLog;
use app\models\GroupBuyGroup;
use app\models\GroupBuyUser;
use app\models\KeyMap;
use app\models\Kuaidi100;
use app\models\Order;
use app\models\OrderDeliver;
use app\models\OrderDeliverItem;
use app\models\OrderLog;
use app\models\OrderRefund;
use app\models\UCenterApi;
use app\models\Util;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * 订单管理
 * Class OrderController
 * @package app\modules\admin\controllers
 */
class OrderController_bak extends BaseController
{
    /**
     * 订单列表
     * @return string
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionList()
    {
        $query = Order::find()->alias('order')->groupBy('order.id');
        $query->andFilterWhere(['order.no' => $this->get('search_no')]);
        $query->joinWith(['itemList', 'itemList.goods goods', 'shop']);
        $query->andFilterWhere(['like', 'goods.title',$this->get('search_goods_name')]);
        $query->andWhere(['order.sup_id' => $this->supplier->id]);
        $query->andFilterWhere(['order.status' => $this->get('search_status')]);
        if (!empty($this->get('search_start_date'))) {
            $query->andFilterWhere(['>=', 'order.create_time', strtotime($this->get('search_start_date'))]);
        }
        if (!empty($this->get('search_end_date'))) {
            $query->andFilterWhere(['<', 'order.create_time', strtotime($this->get('search_end_date')) + 86400]);
        }
        $query->joinWith('financeLog finance');
        $query->andFilterWhere(['finance.pay_method' => $this->get('search_pay_method')]);
        $query->andFilterWhere(['finance.status' => $this->get('search_pay_status')]);
        $query->andFilterWhere(['finance.trade_no' => $this->get('search_trade_no')]);
        $search_deliver_info = $this->get('search_deliver_info');
        if (!empty($search_deliver_info)) {
            $query->andWhere(['like', 'order.deliver_info', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_deliver_info))) . '%', false]);
        }
        $search_invoice_info = $this->get('search_invoice_info');
        if (!empty($search_invoice_info)) {
            $query->andWhere(['like', 'order.invoice_info', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_invoice_info))) . '%', false]);
        }
        $query->andFilterWhere(['order.is_pick_up' => $this->get('search_is_pick_up')]);
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '卖家',
                         '订单号',
                         '创建时间',
                         '买家',
                         '收货地址',
                         '收货人',
                         '收货人手机',
                         '发票信息',
                         '商品标题',
                         '商品规格',
                         '商品数量',
                         '商品单价',
                         '支付金额',
                         '物流费用',
                         '商品金额',
                         '支付交易号',
                         '支付方式',
                         '支付状态',
                         '用户备注',
                         '商户备注',
                         '状态',
                         '操作记录',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var Order $order */
            foreach ($query->each() as $order) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $order->shop->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $order->no, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, Yii::$app->formatter->asDatetime($order->create_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->user->user_nick_name), DataType::TYPE_STRING);
                if (!empty($order->deliver_info)) {
                    $area = $order->getDeliverInfoJson('area');
                    $city = City::findByCode($area);
                    $sheet->setCellValueExplicitByColumnAndRow(5, $r, implode(' ', $city->address()) . ' ' . $order->getDeliverInfoJson('address'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(6, $r, emoji_unified_to_docomo($order->getDeliverInfoJson('name')), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(7, $r, $order->getDeliverInfoJson('mobile'), DataType::TYPE_STRING);
                }
                if (!empty($order->invoice_info)) {
                    $sheet->setCellValueExplicitByColumnAndRow(8, $r, $order->getInvoiceInfoJson('str'), DataType::TYPE_STRING);
                }
                $_r1 = 0;
                foreach ($order->itemList as $item) {
                    $sheet->setCellValueExplicitByColumnAndRow(9, $r + $_r1, $item->title, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(10, $r + $_r1, $item->sku_key_name, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(11, $r + $_r1, $item->amount, DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(12, $r + $_r1, $item->price, DataType::TYPE_NUMERIC);
                    $_r1++;
                }
                if (!empty($order->fid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(13, $r, $order->financeLog->money, DataType::TYPE_NUMERIC);
                }
                $sheet->setCellValueExplicitByColumnAndRow(14, $r, $order->deliver_fee, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(15, $r, $order->goods_money, DataType::TYPE_NUMERIC);
                if (!empty($order->fid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(16, $r, $order->financeLog->trade_no, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(17, $r, KeyMap::getValue('finance_log_pay_method', $order->financeLog->pay_method), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(18, $r, KeyMap::getValue('finance_log_status', $order->financeLog->status), DataType::TYPE_STRING);
                }
                $sheet->setCellValueExplicitByColumnAndRow(19, $r, emoji_unified_to_docomo($order->user_remark), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(20, $r, $order->merchant_remark, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(21, $r, KeyMap::getValue('order_status', $order->status), DataType::TYPE_STRING);
                $op_log = '';
                foreach (OrderLog::find()->andWhere(['oid' => $order->id])->each() as $log) {/** @var OrderLog $log */
                    $op_log .= Yii::$app->formatter->asDatetime($log->time) . chr(9);
                    $op_log .= KeyMap::getValue('order_log_u_type', $log->u_type) . chr(9);
                    $op_log .= $log->content . chr(10);
                }
                $sheet->setCellValueExplicitByColumnAndRow(22, $r, $op_log, DataType::TYPE_STRING);
                $r += $_r1;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="订单列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 待配货订单列表
     * @return string
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionListWaitPack()
    {
        $_GET['fixed_search_status'] = 1;
        $_GET['search_status'] = Order::STATUS_PAID; // 强制设定搜索状态
        return $this->actionList();
    }

    /**
     * 订单详细查看
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->shop->mid != $this->supplier->shop->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        return $this->render('view', [
            'order' => $order,
        ]);
    }

    /**
     * 未支付订单修改订单价格
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdateMoney()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->sid != $this->shop->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        if ($order->status != Order::STATUS_CREATED) {
            throw new NotFoundHttpException('订单状态不正确。');
        }
        if ($this->isPost()) {
            $merchantDiscountMoney = $this->post('merchant_discount_money');
            $deliverFee = $this->post('deliver_fee');
            if ($merchantDiscountMoney == null || !is_numeric($merchantDiscountMoney) || Util::comp($merchantDiscountMoney, 0, 2) < 0) {
                throw new BadRequestHttpException('优惠金额错误。');
            }
            if ($deliverFee == null || !is_numeric($deliverFee) || Util::comp($deliverFee, 0, 2) < 0) {
                throw new BadRequestHttpException('配送费用参数错误。');
            }
            $goodsMoney = $order->goods_money
                - $order->discount_money
                - $order->coupon_money
                - $order->full_cut_money
                - $order->score_money
                - $order->group_buy_discount_money
                - $order->pick_up_discount_money
                - $order->red_money;
            if ($goodsMoney < 0) {
                $goodsMoney = 0;
            }
            if (Util::comp($merchantDiscountMoney, $goodsMoney, 2) > 0) {
                throw new BadRequestHttpException('优惠金额不能高于商品金额。');
            }
            $order->merchant_discount_money = $merchantDiscountMoney;
            $order->deliver_fee = $deliverFee;
            if (!empty($order->fid)) {
                // 需要重新生成支付信息，因为有的支付通道不允许更换金额
                $finance = $order->financeLog;
                $finance->status = FinanceLog::STATUS_CLOSED;
                $finance->save(false);
                $order->fid = null;
            }
            $order->save(false);
            // TODO:更改优惠价，需要考虑之前已经设置过优惠金额，优惠单价需要加回去
            OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '修改订单价格',  print_r($order->dirtyAttributes, true));
            Yii::$app->session->addFlash('success', '保存成功。');
        }
        return $this->render('update_money', [
            'order' => $order,
        ]);
    }

    /**
     * 保存商家备注 AJAX接口
     * @return array
     */
    public function actionMerchantRemark()
    {
        $order_no = $this->post('order_no');
        $merchant_remark = $this->post('merchant_remark');
        if (empty($order_no)) {
            return ['message' => '订单不存在。'];
        }
        $order = Order::findByNo($order_no);
        if (empty($order_no) || $order->shop->mid != $this->merchant->id) {
            return ['message' => '订单不存在。'];
        }
        $order->merchant_remark = $merchant_remark;
        if (!$order->save()) {
            $error = $order->errors;
            return ['message' => array_shift($error)[0]];
        }
        OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '修改商家备注',  print_r($merchant_remark, true));
        return ['result' => 'success'];
    }

    /**
     * 设置订单无需物流AJAX接口
     * @return array
     */
    public function actionSkipDeliver()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->shop->mid != $this->merchant->id) {
            return ['message' => '没有找到订单信息。'];
        }
        if ($order->status != Order::STATUS_PAID) {
            return ['message' => '订单状态错误。'];
        }
        if ($order->is_group_buy == 1) {
            /** @var GroupBuyUser $groupBuyUser */
            $groupBuyUser = GroupBuyUser::find()->andWhere(['oid' => $order->id])->one();
            if ($groupBuyUser->group->status != GroupBuyGroup::STATUS_SUCCESS) {
                return ['message' => '拼团订单没有成团。'];
            }
        }
        $order->status = Order::STATUS_RECEIVED;
        $order->receive_time = time();
        $order->save(false);
        OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '设置订单无需物流。');
        $order->createMerchantSettlement();
        return ['result' => 'success'];
    }

    /**
     * 生成订单发货单
     * @return string|array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditDeliver()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->shop->mid != $this->merchant->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        if ($order->status != Order::STATUS_PAID && $order->status != Order::STATUS_PACKING) {
            throw new BadRequestHttpException('订单状态错误。');
        }
        if ($order->is_pick_up == 1) {
            return ['message' => '自提订单不需要生成发货单。'];
        }
        if ($order->is_group_buy == 1) {
            /** @var GroupBuyUser $groupBuyUser */
            $groupBuyUser = GroupBuyUser::find()->andWhere(['oid' => $order->id])->one();
            if ($groupBuyUser->group->status != GroupBuyGroup::STATUS_SUCCESS) {
                return ['message' => '拼团订单没有成团。'];
            }
        }
        if ($this->isPost()) {
            $amount = $this->post('amount');
            if (!is_array($amount)) {
                return ['message' => '参数错误。'];
            }
            $trans = Yii::$app->db->beginTransaction();
            try {
                $deliver = new OrderDeliver();
                $deliver->oid = $order->id;
                $deliver->status = OrderDeliver::STATUS_WAIT;
                $deliver->create_time = time();
                $r = $deliver->save();
                if (!$r) {
                    throw new Exception('无法保存发货单。');
                }
                $left_amount = 0; // 剩下未出发货单的数量
                foreach ($order->itemList as $item) {
                    if (!isset($amount[$item->id])) {
                        continue;
                    }
                    if ($amount[$item->id] > $item->amount - $item->getDeliverAmount()) {
                        throw new Exception('[' . $item->title . ']数量错误。');
                    }
                    if (isset($amount[$item->id]) && $amount[$item->id] > 0) {
                        $deliver_item = new OrderDeliverItem();
                        $deliver_item->did = $deliver->id;
                        $deliver_item->oiid = $item->id;
                        $deliver_item->amount = $amount[$item->id];
                        $r = $deliver_item->save();
                        if (!$r) {
                            throw new Exception('无法保存发货单内容。');
                        }
                    }
                    $left_amount += $item->amount;
                    $left_amount -= $item->getDeliverAmount();
                }
                if ($order->status == Order::STATUS_PAID && $left_amount >0) {
                    $order->status = Order::STATUS_PACKING;
                } elseif (($order->status == Order::STATUS_PACKING && $left_amount ==0) || $order->status == Order::STATUS_PAID && $left_amount ==0) {
                    $order->status = Order::STATUS_PACKED;
                }
                $r = $order->save();
                if (!$r) {
                    throw new Exception('无法更新订单状态。');
                }
                OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '生成发货单。', print_r($deliver->attributes, true));
                $trans->commit();
                return ['result' => 'success', 'left_amount' => $left_amount, 'did' => $deliver->id];
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $_) {
                }
                return ['message' => $e->getMessage()];
            }
        }
        return $this->render('deliver_edit', [
            'order' => $order,
        ]);
    }

    /**
     * 发货单列表
     * @return string
     */
    public function actionOrderDeliverList()
    {
        $query = OrderDeliver::find();
        $query->joinWith(['itemList', 'order', 'order.shop', 'express']);
        $query->andWhere(['{{%shop}}.mid' => $this->merchant->id]);
        $query->andFilterWhere(['{{%order_deliver}}.status' => $this->get('search_status')]);
        $query->andFilterWhere(['like', '{{%order}}.no' , $this->get('search_no')]);
        $query->andFilterWhere(['like', '{{%order_deliver}}.no' , $this->get('search_express_no')]);
        $search_deliver_info = $this->get('search_deliver_info');
        if (!empty($search_deliver_info)) {
            $query->andWhere(['like', '{{%order}}.deliver_info', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_deliver_info))) . '%', false]);
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('deliver_list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 发货单发货
     * @throws NotFoundHttpException
     * @return string | array
     */
    public function actionDeliverSend()
    {
        $id = $this->get('id');
        $deliver = OrderDeliver::findOne(['id' => $id]);
        if (empty($deliver) || $deliver->order->shop->mid != $this->merchant->id) {
            throw new NotFoundHttpException('没有找到发货单信息。');
        }
        $order = $deliver->order;
        $express_list = Express::find()->where(['<>', 'status', Express::STATUS_PAUSE])->all();
        if ($this->isPost()) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                if ($deliver->load($this->post())) {
                    $deliver->status = OrderDeliver::STATUS_SENT;
                    $deliver->send_time = time();
                }
                $r = $deliver->save();
                if (!$r) {
                    throw new Exception('无法保存发货单。');
                }
                if (!empty($deliver->no)) {
                    Kuaidi100::poll($deliver->no, $deliver->express->code, [
                        'type' => 'order_deliver',
                        'id' => $deliver->id,
                    ]);
                }
                $left_amount = 0; // 剩下未出发货单的数量
                foreach ($order->itemList as $item) {
                    $left_amount += $item->amount;
                    $left_amount -= $item->getDeliverAmount();
                }
                if ($order->status == Order::STATUS_PACKED
                    && $left_amount == 0
                    && !OrderDeliver::find()->where(['oid' => $order->id, 'status' => OrderDeliver::STATUS_WAIT])->exists()) {
                    $order->status = Order::STATUS_DELIVERED;
                    $r = $order->save();
                    if (!$r) {
                        throw new Exception('无法更新订单状态。');
                    }
                }
                OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '订单发货。 ', print_r($deliver->attributes, true));
                $trans->commit();
                return ['result' => 'success'];
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $_) {
                }
                return ['message' => $e->getMessage()];
            }
        }
        return $this->render('deliver_send', [
            'order' => $order,
            'deliver' => $deliver,
            'express_list' => $express_list,
        ]);
    }

    /**
     * 删除发货单AJAX接口
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionDeliverDelete()
    {
        $id = $this->get('id');
        $deliver = OrderDeliver::findOne(['id' => $id]);
        if (empty($deliver) || $deliver->order->shop->mid != $this->merchant->id) {
            throw new NotFoundHttpException('没有找到发货单信息。');
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $r = OrderDeliverItem::deleteAll(['did' => $id]);
            if (!$r) {
                throw new Exception('无法更新发货单状态。');
            }
            $r = OrderDeliver::deleteAll(['id' => $id]);
            if (!$r) {
                throw new Exception('无法更新发货单状态。');
            }
            $count = OrderDeliver::find()->where(['oid' => $deliver->oid])->count();
            $order = $deliver->order;
            if ($count > 1) {
                $order->status = Order::STATUS_PACKING;
            } else {
                $order->status = Order::STATUS_PAID;
            }
            $r = $order->save();
            if (!$r) {
                throw new Exception('无法更新订单状态。');
            }
            OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '删除发货单。', print_r($deliver->attributes, true));
            $trans->commit();
            return ['result' => 'success'];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $_) {
            }
            return ['message' => $e->getMessage()];
        }
    }

    /**
     * 打印快递单AJAX接口
     * @return array
     */
    public function actionPrintDeliver()
    {
        $deliverId = $this->get('deliverId');
        $templateId = $this->get('templateId');
        $deliver = OrderDeliver::findOne(['id' => $deliverId]);
        if (empty($deliver) || $deliver->order->shop->mid != $this->merchant->id) {
            return ['message' => '没有找到发货单。'];
        }
        $template = ExpressPrintTemplate::findOne(['id' => $templateId]);
        if (empty($template)) {
            return ['message' => '没有找到打印模板。'];
        }

        $print = $this->renderPartial('deliver_print', [
            'deliver' => $deliver,
            'template' => $template,
        ]);
        return [
            'result' => 'success',
            'print' => $print,
        ];
    }

    /**
     * 申请售后列表
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionRefundList()
    {
        $query = OrderRefund::find()->alias('refund');
        $query->joinWith('order order');
        $search_mobile = $this->get('search_mobile');
        if (!empty($search_mobile)) {
            try{
                $user_list = (new UCenterApi())->userList(['user_phone' => $search_mobile]);
                $query->andWhere(['refund.uid' => ArrayHelper::getColumn($user_list, 'user_id')]);
            } catch (Exception $e){
                throw new ServerErrorHttpException($e->getMessage());
            }
        }
        $query->andWhere(['order.sid' => $this->shop->id]);
        $query->andWhere(['<>', 'refund.status', OrderRefund::STATUS_DELETE]);
        $query->andFilterWhere(['refund.status' => $this->get('search_status')]);
        $query->andFilterWhere(['refund.type' => $this->get('search_type')]);
        $query->andFilterWhere(['like', 'order.no' , $this->get('search_no')]);
        $query->andFilterWhere(['like', 'refund.express_no' , $this->get('search_express_no')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('refund.create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $model_list = $query->all();
        return $this->render('refund_list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 通过售后申请AJAX接口
     * @return array
     */
    public function actionAcceptRefund()
    {
        $id = $this->get('id');
        $refund = OrderRefund::findOne(['id' => $id]);
        if (empty($refund) || $refund->order->sid != $this->shop->id) {
            return ['message' => '没有找到售后单。'];
        }
        if ($refund->status != OrderRefund::STATUS_REQUIRE) {
            return ['message' => '状态错误。'];
        }
        if (in_array($refund->type, [OrderRefund::TYPE_MONEY, OrderRefund::TYPE_CANCEL])) { // 直接退款就可以的
            try {
                $refund->doRefund(); // 退款操作
                OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $refund->oid, '通过订单售后申请。', $refund->id);
            } catch (Exception $e) {
                return ['message' => $e->getMessage()];
            }
        } elseif ($refund->type == OrderRefund::TYPE_GOODS_MONEY) { // 需要退货
            $refund->status = OrderRefund::STATUS_ACCEPT;
            $refund->apply_time = time();
            if ($refund->order->is_pick_up == 1) {
                // 到店自提订单需要到店送货，默认设置为已发货，商户可以直接确认收货
                $refund->send_time = time();
                $refund->status = OrderRefund::STATUS_SEND;
            }
            $refund->save(false);
            OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $refund->oid, '通过订单售后申请。', $refund->id);
        }
        return ['result' => 'success'];
    }

    /**
     * 拒绝售后申请AJAX接口
     * @return array
     */
    public function actionRejectRefund()
    {
        $id = $this->get('id');
        $refund = OrderRefund::findOne(['id' => $id]);
        if (empty($refund) || $refund->order->sid != $this->shop->id) {
            return ['message' => '没有找到售后单。'];
        }
        if ($refund->status != OrderRefund::STATUS_REQUIRE) {
            return ['message' => '状态错误。'];
        }
        $refund->status = OrderRefund::STATUS_REJECT;
        $refund->reject_time = time();
        $refund->save(false);
        $order = $refund->order;
        $order->status = $refund->order_status;
        $order->save(false);
        OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '拒绝订单售后申请。', $refund->id);
        return ['result' => 'success'];
    }

    /**
     * 退货单详情页
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRefundView()
    {
        $id = $this->get('id');
        $refund = OrderRefund::findOne(['id' => $id]);
        if (empty($refund) || $refund->order->sid != $this->shop->id) {
            throw new NotFoundHttpException('没有找到发货单信息。');
        }
        return $this->render('refund_view', [
            'refund' => $refund,
        ]);
    }

    /**
     * 确认收货AJAX接口
     * @return array
     */
    public function actionReceiveRefund()
    {
        $id = $this->get('id');
        $refund = OrderRefund::findOne(['id' => $id]);
        if (empty($refund)
            || $refund->order->sid != $this->shop->id
            || $refund->status == OrderRefund::STATUS_DELETE
        ) {
            return ['message' => '没有找到申请记录信息。'];
        }
        if ($refund->type != OrderRefund::TYPE_GOODS_MONEY) {
            return ['message' => '售后类型不匹配。'];
        }
        if ($refund->status != OrderRefund::STATUS_SEND) {
            return ['message' => '售后状态错误，无法设置收货。'];
        }
        $refund->receive_time = time();
        $refund->status = OrderRefund::STATUS_RECEIVE;
        $refund->save(false);
        return ['result' => 'success'];
    }

    /**
     * 售后退款AJAX接口
     * @return array
     */
    public function actionDoRefund()
    {
        $id = $this->get('id');
        if (empty($id)) {
            return ['message' => '参数错误。'];
        }
        $refund = OrderRefund::findOne(['id' => $id]);
        if (empty($refund)
            || $refund->order->shop->mid != $this->merchant->id
            || $refund->status == OrderRefund::STATUS_DELETE
        ) {
            return ['message' => '没有找到申请记录信息。'];
        }
        if ($refund->status != OrderRefund::STATUS_RECEIVE) {
            return ['message' => '收货状态错误。'];
        }

        try {
            $refund->doRefund();
            OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $refund->oid, '通过售后。', print_r($refund->attributes, true));
            return ['result' => 'success'];
        } catch (Exception $e) {
            return ['message' => $e->getMessage()];
        }
    }

    /**
     * 自提核销
     * @return string
     */
    public function actionPickUp()
    {
        $searchPickUpCode = $this->get('search_pick_up_code');
        $order = null;
        if (!empty($searchPickUpCode)) {
            $order = Order::find()
                ->andWhere(['sid' => $this->shop->id])
                ->andWhere(['is_pick_up' => 1])
                ->andWhere(['pick_up_code' => $this->get('search_pick_up_code')])
                ->one();
        }
        return $this->render('pick_up', [
            'order' => $order,
        ]);
    }

    /**
     * 自提核销AJAX接口
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @return array
     */
    public function actionCheckPickUp()
    {
        $orderNo = $this->get('order_no');
        $order = Order::findByNo($orderNo);
        if (empty($order) || $order->sid != $this->shop->id) {
            throw new NotFoundHttpException('没有找到订单。');
        }
        if ($order->status != Order::STATUS_PAID) {
            throw new BadRequestHttpException('订单状态错误，不允许提货。');
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $deliver = new OrderDeliver();
            $deliver->oid = $order->id;
            $deliver->create_time = time();
            $deliver->send_time = time();
            $deliver->status = OrderDeliver::STATUS_SENT;
            if (!$deliver->save()) {
                throw new Exception('无法保存发货单。');
            }
            foreach ($order->itemList as $item) {
                $deliver_item = new OrderDeliverItem();
                $deliver_item->did = $deliver->id;
                $deliver_item->oiid = $item->id;
                $deliver_item->amount = $item->amount;
                if (!$deliver_item->save()) {
                    throw new Exception('无法保存发货单内容。');
                }
            }

            $order->status = Order::STATUS_RECEIVED;
            $order->receive_time = time();
            if (!$order->save(false)) {
                throw new Exception('无法保存订单状态。');
            }
            $order->createMerchantSettlement();
            OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '提货核销。', print_r($deliver->attributes, true));
            $trans->commit();
            return ['result' => 'success'];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $_) {
            }
            throw new ServerErrorHttpException($e->getMessage());
        }
    }
}
