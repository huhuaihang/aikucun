<?php

namespace app\modules\merchant\controllers;

use app\models\City;
use app\models\Express;
use app\models\ExpressPrintTemplate;
use app\models\KeyMap;
use app\models\Kuaidi100;
use app\models\Order;
use app\models\OrderDeliver;
use app\models\OrderDeliverItem;
use app\models\OrderItem;
use app\models\OrderLog;
use app\models\OrderRefund;
use app\models\Sms;
use app\models\UserBuyPack;
use app\models\UserMessage;
use app\models\Util;
use PHPExcel;
use PHPExcel_Cell_DataType;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * 订单管理
 * Class OrderController
 * @package app\modules\admin\controllers
 */
class OrderController extends BaseController
{
    /**
     * 订单列表
     * @return string
     * @throws \PHPExcel_Exception
     * @throws Exception
     */
    public function actionList()
    {
        set_time_limit(0);
        $query = Order::find()->groupBy('{{%order}}.id');
        $query->andFilterWhere(['{{%order}}.no' => $this->get('search_no')]);
        $query->joinWith(['itemList', 'itemList.goods','shop']);
        $query->andFilterWhere(['like', '{{goods}}.title',$this->get('search_goods_name')]);
        $query->andWhere(['{{%order}}.sid' => $this->shop->id]);
        $query->andFilterWhere(['{{%order}}.status' => $this->get('search_status')]);
        if (!empty($this->get('search_start_date'))) {
            $query->andFilterWhere(['>=', '{{%order}}.create_time', strtotime($this->get('search_start_date'))]);
        }
        if (!empty($this->get('search_end_date'))) {
            $query->andFilterWhere(['<', '{{%order}}.create_time', strtotime($this->get('search_end_date')) + 86400]);
        }
        $query->joinWith('financeLog');
        $query->andFilterWhere(['{{%finance_log}}.pay_method' => $this->get('search_pay_method')]);
        $query->andFilterWhere(['{{%finance_log}}.status' => $this->get('search_pay_status')]);
        $query->andFilterWhere(['{{%finance_log}}.trade_no' => $this->get('search_trade_no')]);
        $search_deliver_info = $this->get('search_deliver_info');
        if (!empty($search_deliver_info)) {
            $query->andWhere(['like', '{{%order}}.deliver_info', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_deliver_info))) . '%', false]);
        }

        $query->joinWith('user');
        $search_user_info = $this->get('search_user_info');
        if (!empty($search_user_info)) {
            $query->andWhere(['like', '{{%user}}.mobile', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_user_info))) . '%', false]);
        }
        $query->orderBy('create_time DESC');
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new PHPExcel();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '订单号',
                         '买家',
                         '收货地址',
                         '收货人',
                         '收货人手机',
                         '订单总金额',
                         '物流费用',
                         '商品金额',
                         '支付交易号',
                         '支付金额',
                         '支付方式',
                         '支付状态',
                         '用户备注',
                         '商户备注',
                         '状态',
                         '创建时间',
                         '商品标题',
                         '商品规格',
                         '商品单价',
                         '商品数量',
                         '操作记录',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font'=>['bold'=>true], 'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            foreach ($query->each() as $order) {
                /** @var Order $order */
                $sheet->setCellValueExplicitByColumnAndRow(0, $r, $order->no, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, emoji_unified_to_docomo($order->user->nickname));
                if (!empty($order->deliver_info)) {
                    if (strstr($order->getDeliverInfoJson('address'), '省')
                        || (strstr($order->getDeliverInfoJson('address'), '上海市') && strstr($order->getDeliverInfoJson('address'), '省') == false)
                        || (strstr($order->getDeliverInfoJson('address'), '重庆市') && strstr($order->getDeliverInfoJson('address'), '省') == false)
                        || (strstr($order->getDeliverInfoJson('address'), '天津市') && strstr($order->getDeliverInfoJson('address'), '省') == false)
                        || (strstr($order->getDeliverInfoJson('address'), '自治区') && strstr($order->getDeliverInfoJson('address'), '省') == false)) {
                        $sheet->setCellValueExplicitByColumnAndRow(2, $r, $order->getDeliverInfoJson('address'));
                    } else {
                        $area = $order->getDeliverInfoJson('area');
                        $city = City::findByCode($area);
                        $sheet->setCellValueExplicitByColumnAndRow(2, $r, implode(' ', $city->address()) . ' ' . $order->getDeliverInfoJson('address'));
                    }
                    $sheet->setCellValueExplicitByColumnAndRow(3, $r, emoji_unified_to_docomo($order->getDeliverInfoJson('name')));
                    $sheet->setCellValueExplicitByColumnAndRow(4, $r, $order->getDeliverInfoJson('mobile'));
                }
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $order->amount_money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, $order->deliver_fee, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, $order->goods_money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                if (!empty($order->fid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(8, $r, $order->financeLog->trade_no);
                    $sheet->setCellValueExplicitByColumnAndRow(9, $r, $order->financeLog->money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(10, $r, KeyMap::getValue('finance_log_pay_method', $order->financeLog->pay_method));
                    $sheet->setCellValueExplicitByColumnAndRow(11, $r, KeyMap::getValue('finance_log_status', $order->financeLog->status));
                }
                $sheet->setCellValueExplicitByColumnAndRow(12, $r, emoji_unified_to_docomo($order->user_remark));
                $sheet->setCellValueExplicitByColumnAndRow(13, $r, $order->merchant_remark);
                $sheet->setCellValueExplicitByColumnAndRow(14, $r, KeyMap::getValue('order_status', $order->status));
                $sheet->setCellValueExplicitByColumnAndRow(15, $r, Yii::$app->formatter->asDatetime($order->create_time));
                $_r1 = 0;
                foreach ($order->itemList as $item) {
                    $sheet->setCellValueExplicitByColumnAndRow(16, $r + $_r1, $item->title);
                    $sheet->setCellValueExplicitByColumnAndRow(17, $r + $_r1, $item->sku_key_name);
                    $sheet->setCellValueExplicitByColumnAndRow(18, $r + $_r1, $item->price, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(19, $r + $_r1, $item->amount, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $_r1++;
                }
                $op_log = '';
                foreach (OrderLog::find()->andWhere(['oid' => $order->id])->each() as $log) {/** @var OrderLog $log */
                    $op_log .= Yii::$app->formatter->asDatetime($log->time) . chr(9);
                    $op_log .= KeyMap::getValue('order_log_u_type', $log->u_type) . chr(9);
                    $op_log .= $log->content . chr(10);
                }
                $sheet->setCellValueExplicitByColumnAndRow(20, $r, $op_log);
                $r += $_r1;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="订单列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $excelWriter->save('php://output');
            return null;
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 导入批量发货快递单号AJAX接口
     * @return array
     * @throws Exception
     */
    public function actionImport()
    {
        $files = UploadedFile::getInstancesByName('files');
        if (empty($files)) {
            return ['message' => '没有找到上传文件。'];
        }
        $error_list = [];
        $spreadsheet = IOFactory::load($files[0]->tempName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        for ($i = 2; $i <= count($sheetData); $i++){
            $item = $sheetData[$i];
            if (empty($item)) {
                continue;
            }
            $deliver = OrderDeliver::findOne($item['U']);
            if (empty($deliver)) {
                continue;
            }
            if (!empty($deliver->supplier)) {
                continue;
            }
            //$trans = Yii::$app->db->beginTransaction();
            try {
                if (empty($deliver)) {
                    throw new Exception('没有找到发货单信息。');
                }
                $order = $deliver->order;

                if ($deliver->order->status > Order::STATUS_PACKED || $deliver->order->status == Order::STATUS_DELETE) {
                    //throw new Exception($deliver->order->no. '订单状态不对不能发货。');
                    $error_list[] = $deliver->order->no;
                    continue;
                }
                $deliver->no = (string)$item['W'];
                if (!empty($item['V'])) {
                    /** @var Express $express */
                    $express = Express::find()->andFilterWhere(['LIKE', 'name', $item['V']])->one();
                    if (!empty($express)) {
                        $deliver->eid = $express->id;
                        $deliver->status = OrderDeliver::STATUS_SENT;
                        $deliver->send_time = time();

                        $r = $deliver->save();
                        if (!$r) {
                            Yii::warning($deliver->getErrors());
                            throw new Exception('无法保存发货单。');
                        }
                        if (!empty($deliver->no)) {
                            Kuaidi100::poll($deliver->id, empty($express)? '' : $express->code, $deliver->no);
                            //                    Kuaidi100::poll($deliver->no, $deliver->express, [
                            //                        'type' => 'order_deliver',
                            //                        'id' => $deliver->id,
                            //                    ]);
                        }
                        $left_amount = 0; // 剩下未真实发货出发货单的数量
                        foreach ($order->itemList as $item) {
                            $left_amount += $item->amount;
                            $left_amount -= $item->getSendDeliverAmount();
                        }
                        if ($order->status == Order::STATUS_PACKED
                            && $left_amount == 0
                            && !OrderDeliver::find()->where(['oid' => $order->id, 'status' => OrderDeliver::STATUS_WAIT])->exists()) {
                            $order->status = Order::STATUS_DELIVERED;
                            $r = $order->save();
                            if (!$r) {
                                throw new Exception('无法更新订单状态。');
                            }
                            //发送短信
                            /** @var OrderDeliverItem $deliver_item */
                            $deliver_item = OrderDeliverItem::find()->where(['did' => $deliver->id])->one();
                            /** @var OrderItem $order_item */
                            $order_item = OrderItem::findOne($deliver_item->oiid);
                            //$content = "亲爱的云淘帮会员您好！你购买的" .$order_item->goods->title . "商品已经安排发货，请您准备接收快递。有问题请及时联系云淘帮客服：18006490976";
                            $content = $order_item->goods->title;
                            Sms::send(Sms::U_TYPE_USER, $order->user->id, Sms::TYPE_SEND_NOTICE, $order->user->mobile, $content);
                        }
                        OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MANAGER, $order->id, '订单批量发货。 ', print_r($deliver->attributes, true));
                        //$trans->commit();
                    } else {
                        $error_list[] = $deliver->order->no;
                    }
                } else {
                    $error_list[] = $deliver->order->no;
                }


            } catch (Exception $e) {
                try {
                    //$trans->rollBack();
                } catch (Exception $e) {
                }
                return [
                    'message' => $e->getMessage(),
                ];
            }
        }
        return ['result' => 'success', 'files' => [['url' => $error_list]], 'error_list' => $error_list];
    }


    /**
     * 待配货订单列表
     * @return string
     * @throws Exception
     * @throws \PHPExcel_Exception
     */
    public function actionListWaitPack()
    {
        $_GET['search_status'] = Order::STATUS_PAID; // 强制设定搜索状态
        return $this->actionList();
    }

    /**
     * 待配货订单列表
     * @return string
     * @throws Exception
     * @throws \PHPExcel_Exception
     */
    public function actionOrderPackList()
    {
        $_GET['search_status'] = Order::STATUS_PACKED; // 强制设定搜索状态
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
        if (empty($order) || $order->shop->mid != $this->merchant->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        return $this->render('view', [
            'order' => $order,
        ]);
    }

    /**
     * 未支付订单修改订单价格
     * @return string | array
     * @throws NotFoundHttpException
     */
    public function actionUpdateMoney()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->shop->mid != $this->merchant->id) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        if ($order->status != Order::STATUS_CREATED) {
            throw new NotFoundHttpException('订单状态不正确。');
        }
        if ($this->isPost()) {
            /** @var Order $old_order */
            $old_order = $order->attributes;
            if ($order->load($this->post())) {
                $order->amount_money = $order->goods_money + $order->deliver_fee;
                if ($order->save()) {
                    OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '修改订单价格',  '订单内容 由' . print_r($old_order, true) . ' 改为' . print_r($order->attributes, true));
                    Yii::$app->session->addFlash('success', '添加成功，请等待审核。');
                    Yii::$app->session->setFlash('redirect', json_encode([
                        'url' => Url::to(['/merchant/order/view', 'order_no' => $order_no]),
                        'txt' => '订单详细'
                    ]));
                } else {
                    Yii::$app->session->addFlash('error', $order->errors[0]);
                }
            }
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
        $order->status = Order::STATUS_DELIVERED;
        $r=$order->save(false);
        if($r)
        {
          /** 订单发货用户消息通知 */
            $user_message = new UserMessage();
            $user_message->MessageSend($order->uid,'您的订单已经发货,请保持电话畅通!', Yii::$app->params['site_host'].'/h5/order/view?order_no=' . $order->no,'订单发货用户通知');
        }
        OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '设置订单无需物流。');
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
                        throw new Exception('[' . $item->title . ']数量错误。'.$item->id.'==='.$amount[$item->id] .'===='. $item->amount .'===='. $item->getDeliverAmount());
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
                $shopLeft = $left_amount;
//                $order->generateDeliver($this->shop->id, $amount);
//
//                /** @var OrderDeliver $deliver 找到一个可以发货的发货单 */
//                $deliver = OrderDeliver::find()
//                    ->andWhere(['oid' => $order->id, 'status' => OrderDeliver::STATUS_WAIT, 'supplier_id' => null])
//                    ->one();
//                $shopLeft = 0;
//                foreach ($order->itemList as $orderItem) {
//                    $left = $orderItem->amount - $orderItem->getDeliverAmount();
//                    if (empty($orderItem->goods->supplier_id)) {
//                        $shopLeft += $left;
//                    }
//                }
                OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '生成发货单。', print_r($deliver->attributes, true));
                $trans->commit();
                return ['result' => 'success', 'left_amount' => $shopLeft, 'did' => $deliver->id];
                //return ['result' => 'success', 'left_amount' => $left_amount, 'did' => $deliver->id];
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
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
        $query->andFilterWhere(['{{%order}}.status' => $this->get('search_order_status')]);
        $query->andFilterWhere(['like', '{{%order}}.no' , $this->get('search_no')]);
        $query->andFilterWhere(['like', '{{%order_deliver}}.no' , $this->get('search_express_no')]);
        $search_deliver_info = $this->get('search_deliver_info');
        if (!empty($search_deliver_info)) {
            $query->andWhere(['like', '{{%order}}.deliver_info', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_deliver_info))) . '%', false]);
        }
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            $express_name = Express::find()->all();
            $express_name = implode(',', array_column($express_name, 'name'));

            foreach ([
                         '卖家',
                         '订单号',
                         '创建时间',
                         '买家',
                         '收货地址',
                         '收货人',
                         '收货人手机',
                         '商品标题',
                         '商品规格',
                         '商品数量',
                         '结算单价',
                         '物流费用',
                         '支付交易号',
                         '支付方式',
                         '支付状态',
                         '用户备注',
                         '商户备注',
                         '状态',
                         '物流信息',
                         '操作记录',
                         '发货单编号',
                         '发货单物流快递公司',
                         '发货单快递单号',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var OrderDeliver $orderDeliver */
            foreach ($query->each() as $orderDeliver) {
                if ($orderDeliver->supplier_id) {
                    continue;
                }
                $order = $orderDeliver->order;
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, empty($order->sid) ? '' : $order->shop->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $order->no, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, Yii::$app->formatter->asDatetime($order->create_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->user->nickname), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->user->real_name), DataType::TYPE_STRING);
                if (!empty($order->deliver_info)) {
                    if (strstr($order->getDeliverInfoJson('address'), '省')
                        || (strstr($order->getDeliverInfoJson('address'), '上海市') && strstr($order->getDeliverInfoJson('address'), '省') == false)
                        || (strstr($order->getDeliverInfoJson('address'), '重庆市') && strstr($order->getDeliverInfoJson('address'), '省') == false)
                        || (strstr($order->getDeliverInfoJson('address'), '天津市') && strstr($order->getDeliverInfoJson('address'), '省') == false)
                        || (strstr($order->getDeliverInfoJson('address'), '自治区') && strstr($order->getDeliverInfoJson('address'), '省') == false)) {
                        $sheet->setCellValueExplicitByColumnAndRow(5, $r, $order->getDeliverInfoJson('address'), DataType::TYPE_STRING);
                    } else {
                        $area = $order->getDeliverInfoJson('area');
                        $city = City::findByCode($area);
                        $sheet->setCellValueExplicitByColumnAndRow(5, $r, implode(' ', $city->address()) . ' ' . $order->getDeliverInfoJson('address'), DataType::TYPE_STRING);
                    }
                    $sheet->setCellValueExplicitByColumnAndRow(6, $r, emoji_unified_to_docomo($order->getDeliverInfoJson('name')), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(7, $r, $order->getDeliverInfoJson('mobile'), DataType::TYPE_STRING);
                }
                $_r1 = 0;
                foreach ($order->itemList as $item) {
                    if ($item->goods->supplier_id) {
                        continue;
                    }
                    $sheet->setCellValueExplicitByColumnAndRow(8, $r + $_r1, $item->title, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(9, $r + $_r1, $item->sku_key_name, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(10, $r + $_r1, $item->amount, DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(11, $r + $_r1, empty($item->goodsSku) ? $item->goods->supplier_price : $item->goodsSku->supplier_price, DataType::TYPE_NUMERIC);
                    $_r1++;
                }
                $sheet->setCellValueExplicitByColumnAndRow(12, $r, $order->deliver_fee, DataType::TYPE_NUMERIC);
                if (!empty($order->fid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(13, $r, $order->financeLog->trade_no, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(14, $r, KeyMap::getValue('finance_log_pay_method', $order->financeLog->pay_method), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(15, $r, KeyMap::getValue('finance_log_status', $order->financeLog->status), DataType::TYPE_STRING);
                }
                $sheet->setCellValueExplicitByColumnAndRow(16, $r, $order->user_remark, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(17, $r, $order->merchant_remark, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(18, $r, KeyMap::getValue('order_status', $order->status), DataType::TYPE_STRING);
                $deliverInfo = [];
                /** @var OrderDeliver $orderDeliver */
                foreach (OrderDeliver::find()->andWhere(['oid' => $order->id])->andWhere('supplier_id IS NULL')->each() as $_orderDeliver) {
                    if (!empty($_orderDeliver->eid)) {
                        $deliverInfo[] = $_orderDeliver->express->name . chr(9) . $_orderDeliver->no;
                    }
                }
                $sheet->setCellValueExplicitByColumnAndRow(19, $r, implode(chr(10), $deliverInfo), DataType::TYPE_STRING);
                $op_log = '';
                foreach (OrderLog::find()->andWhere(['oid' => $order->id])->each() as $log) {/** @var OrderLog $log */
                    $op_log .= Yii::$app->formatter->asDatetime($log->time) . chr(9);
                    $op_log .= KeyMap::getValue('order_log_u_type', $log->u_type) . chr(9);
                    $op_log .= $log->content . chr(10);
                }
                $sheet->setCellValueExplicitByColumnAndRow(20, $r, $op_log, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(21, $r, $orderDeliver->id, DataType::TYPE_STRING);
                $validation = $excel->getActiveSheet()->getCell('V'. $r)
                    ->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST );
                $validation->setErrorStyle( DataValidation::STYLE_STOP);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('输入错误');
                $validation->setError('请下拉选择指定的物流公司.');
                $validation->setPromptTitle('从列表选择物流公司');
                $validation->setPrompt('请选择物流公司.');
                $validation->setFormula1('"'.$express_name.'"');

                $excel->getActiveSheet()->getStyle('V' . $r)
                    ->getProtection()
                    ->setLocked(Protection::PROTECTION_UNPROTECTED);

                $r += $_r1;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel;charset=utf-8');
            header('Content-Disposition: attachment;filename="订单列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
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
        $deliver = OrderDeliver::findOne($id);
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
                    Kuaidi100::poll($deliver->id, $deliver->express->code, $deliver->no);
                }
                $status = $this->post('status');
                if (!isset($status)) {
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
                        } else {
                            //发站内消息
                            $user_message = new UserMessage();
                            $user_message->MessageSend($order->uid,'您的订单已经发货,请保持电话畅通!', Yii::$app->params['site_host'].'/h5/order/view?order_no=' . $order->no,'订单发货用户通知');
                            //发送短信
                            /** @var OrderDeliverItem $deliver_item */
                            $deliver_item = OrderDeliverItem::find()->where(['did' => $deliver->id])->one();
                            /** @var OrderItem $order_item */
                            $order_item = OrderItem::findOne($deliver_item->oiid);
                            $content = $order_item->goods->title;
                            Sms::send(Sms::U_TYPE_USER, $order->user->id, Sms::TYPE_SEND_NOTICE, $order->user->mobile, $content);
                        }
                    }
                    OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '订单发货。 ', print_r($deliver->attributes, true));
                } else {
                    OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '订单修改发货快递信息。 ', print_r($deliver->attributes, true));
                }
                $trans->commit();
                return ['result' => 'success'];
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
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
        $deliver = OrderDeliver::findOne($id);
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
            } catch (Exception $e) {
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
        $deliver = OrderDeliver::findOne($deliverId);
        if (empty($deliver) || $deliver->order->shop->mid != $this->merchant->id) {
            return ['message' => '没有找到发货单。'];
        }
        $template = ExpressPrintTemplate::findOne($templateId);
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
     */
    public function actionRefundList()
    {
        $query = OrderRefund::find();
        $query->joinWith(['orderItem', 'orderItem.order', 'orderItem.order.shop']);
        $query->andWhere(['<>', '{{%order_refund}}.status', OrderRefund::STATUS_DELETE]);
        $query->andWhere(['{{%shop}}.mid' => $this->merchant->id]);
        $query->andFilterWhere(['{{%order_refund}}.status' => $this->get('search_status')]);
        $query->andFilterWhere(['{{%order_refund}}.type' => $this->get('search_type')]);
        $query->andFilterWhere(['like', '{{%order}}.no' , $this->get('search_no')]);
        $query->andFilterWhere(['like', '{{%order_refund}}.express_no' , $this->get('search_express_no')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('{{%order_refund}}.create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('refund_list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 售后申请审核 通过/拒绝 AJAX接口
     * @return array
     * @throws
     */
    public function actionStatusRefund()
    {
        $id = $this->get('id');
        $status = $this->get('status');
        if (empty($id) || empty($status)) {
            return ['message' => '参数错误。'];
        }
        /** @var OrderRefund $order_refund */
        $order_refund = OrderRefund::findOne($id);
        if (empty($order_refund) && $order_refund->orderItem->order->shop->mid != $this->merchant->id) {
            return ['message' => '没有找到申请记录信息。'];
        }
        if ($status == 'accept') {
            if ($order_refund->type == OrderRefund::TYPE_MONEY) {
                $r = $order_refund->doRefund(); // 直接退款
                if ($r !== true) {
                    return ['message' => $r];
                }
                $order_refund->status = OrderRefund::STATUS_COMPLETE;
                $order_refund->apply_time = time();
                $order_refund->complete_time = time();
                if (!$order_refund->save()) {
                    return ['message' => '申请售后审核通过失败。'];
                }
                $order_refund->orderItem->order->status = Order::STATUS_COMPLETE;
                $order_refund->orderItem->order->save(false);
                //售后结果通知
                $message=new UserMessage();
                $url=Yii::$app->params['site_host'].'/h5/order/refund-view?id='.$id;
                $message->MessageSend($order_refund->order->uid,'您申请的['.$order_refund->orderItem->title.']商品已通过售后',$url,'您申请的['.$order_refund->orderItem->title.']商品已通过售后');
                OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order_refund->orderItem->oid, '通过售后。', print_r($order_refund->attributes, true));
            } elseif ($order_refund->type == OrderRefund::TYPE_GOODS_MONEY) {
                $order_refund->status = OrderRefund::STATUS_ACCEPT;
                $order_refund->apply_time = time();
                if (!$order_refund->save()) {
                    return ['message' => '申请售后审核通过失败。'];
                }
                OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order_refund->orderItem->oid, '通过售后。', print_r($order_refund->attributes, true));
            }
        } else {
            $order_refund->status = OrderRefund::STATUS_REJECT;
            $order_refund->reject_time = time();
            if (!$order_refund->save()) {
                return ['message' => '申请售后审核通过失败。'];
            }
            //售后结果通知
            $message=new UserMessage();
            $url=Yii::$app->params['site_host'].'/h5/order/refund-view?id='.$id;
            $message->MessageSend($order_refund->order->uid,'您申请的['.$order_refund->orderItem->title.']商品已拒绝售后',$url,'您申请的['.$order_refund->orderItem->title.']商品已拒绝售后');
            $order_refund->orderItem->order->status = Order::STATUS_COMPLETE;
            $order_refund->orderItem->order->save(false);
            OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order_refund->orderItem->oid, '拒绝售后。', print_r($order_refund->attributes, true));
        }
        return ['result' => 'success'];
    }

    /**
     * 售后金额修改 AJAX接口
     * @return array
     * @throws
     */
    public function actionSaveRefundMoney()
    {
        $id = $this->get('id');
        $money = $this->get('money');
        $remark = $this->get('remark');
        if (empty($id) || empty($money)) {
            return ['message' => '参数错误。'];
        }
        /** @var OrderRefund $order_refund */
        $order_refund = OrderRefund::findOne($id);
        if (empty($order_refund) && $order_refund->orderItem->order->shop->mid != $this->merchant->id) {
            return ['message' => '没有找到申请记录信息。'];
        }
        if (Util::comp($money, $order_refund->orderItem->order->amount_money, 2) > 0)
        {
            return ['message' => '退款金额不能大于订单总金额'];
        } else {
            $order_refund->money = $money;
            $order_refund->update_money_remark =
                empty($order_refund->update_money_remark) ? $remark : $order_refund->update_money_remark . '\n' . $remark;
            $order_refund->update_time = time();
        }

        if (!$order_refund->save()) {
            return ['message' => '申请售后审核通过失败。'];
        }
        return ['result' => 'success'];
    }

    /**
     * 退货单详情页
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRefundDetail()
    {
        $id = $this->get('id');
        $order_refund = OrderRefund::findOne($id);
        if (empty($order_refund) || $order_refund->orderItem->order->shop->mid != $this->merchant->id) {
            throw new NotFoundHttpException('没有找到发货单信息。');
        }
        $order = $order_refund->orderItem->order;
        return $this->render('refund_detail', [
            'order' => $order,
            'order_refund' => $order_refund,
        ]);
    }

    /**
     * 确认买家发货 收货 退款  完成售后 AJAX接口
     * @return array
     */
    public function actionStatusReceiveComplete()
    {
        $id = $this->get('id');
        $order_refund = OrderRefund::findOne($id);
        if (empty($order_refund)
            || $order_refund->orderItem->order->shop->mid != $this->merchant->id
            || $order_refund->status == OrderRefund::STATUS_DELETE
        ) {
            return ['message' => '没有找到申请记录信息。'];
        }
        if ($order_refund->type != OrderRefund::TYPE_GOODS_MONEY) {
            return ['message' => '售后类型不匹配'];
        }
        if ($order_refund->status != OrderRefund::STATUS_SEND) {
            return ['message' => '售后状态错误，无法设置收货。'];
        }
        $order_refund->receive_time = time();
        $order_refund->status = OrderRefund::STATUS_RECEIVE;
        $order_refund->save();
//        $r = $order_refund->doRefund();
//        if ($r !== true) {
//            return ['message' => $r];
//        }
//        $order_refund->complete_time = time();
//        $order_refund->status = OrderRefund::STATUS_COMPLETE; // 申请退款到支付接口默认退款成功，不再等待异步通知
//        $order_refund->save();
//        $order_refund->orderItem->order->status = Order::STATUS_COMPLETE;
//        $order_refund->orderItem->order->save(false);
//        //售后结果通知
//        $message=new UserMessage();
//        $url=Yii::$app->params['site_host'].'/h5/order/refund-view?id='.$id;
//        $message->MessageSend($order_refund->order->uid,'您申请的['.$order_refund->orderItem->title.']商品已通过售后',$url,'');
//        OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order_refund->orderItem->oid, '通过售后。', print_r($order_refund->attributes, true));

        return ['result' => 'success'];
    }

    /**
     * 售后退款AJAX接口
     * @return array
     */
    public function actionRefundReturn()
    {
        $id = $this->get('id');
        if (empty($id)) {
            return ['message' => '参数错误。'];
        }
        $order_refund = OrderRefund::findOne($id);
        if (empty($order_refund)
            || $order_refund->orderItem->order->shop->mid != $this->merchant->id
            || $order_refund->status == OrderRefund::STATUS_DELETE
        ) {
            return ['message' => '没有找到申请记录信息。'];
        }
        if ($order_refund->type != OrderRefund::TYPE_MONEY && $order_refund->type != OrderRefund::TYPE_GOODS_MONEY) {
            return ['message' => '售后类型不匹配'];
        }
        if ($order_refund->status != OrderRefund::STATUS_RECEIVE) {
            return ['message' => '收货状态错误。'];
        }

        $r = $order_refund->doRefund();
        if ($r === true) {
            $order_refund->complete_time = time();
            $order_refund->status = OrderRefund::STATUS_COMPLETE;
            $order_refund->save();
            $order_refund->orderItem->order->status = Order::STATUS_COMPLETE;
            $order_refund->orderItem->order->save(false);
            OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order_refund->orderItem->oid, '通过售后。', print_r($order_refund->attributes, true));
        }

        return ['result' => 'success'];
    }

    /**
     * 取消订单审核列表
     * @return string
     */
    public function actionVerifyCancel()
    {
        $query = Order::find();
        $query->joinWith('shop')->andWhere(['{{%shop}}.mid' => $this->merchant->id]);
        $query->andWhere(['{{order}}.status' => Order::STATUS_CANCEL_WAIT_MERCHANT]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('cancel_verify', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 通过订单取消申请AJAX接口
     * @return array
     */
    public function actionAcceptCancel()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->shop->mid != $this->merchant->id) {
            return ['message' => '没有找到订单信息。'];
        }
        if ($order->status != Order::STATUS_CANCEL_WAIT_MERCHANT) {
            return ['message' => '订单状态错误。'];
        }
        try {
            $order->doCancel();
            OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '通过订单取消申请。');
            return ['result' => 'success'];
        } catch (Exception $e) {
            Yii::error('订单取消出现错误：' . $e->getMessage());
            return ['message' => $e->getMessage()];
        }
    }

    /**
     * 拒绝订单取消申请AJAX接口
     * @return array
     */
    public function actionRejectCancel()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->shop->mid != $this->merchant->id) {
            return ['message' => '没有找到订单信息。'];
        }
        if ($order->status != Order::STATUS_CANCEL_WAIT_MERCHANT) {
            return ['message' => '订单状态错误。'];
        }
        $order->status = Order::STATUS_CANCEL_WAIT_MANAGER;
        $order->save(false);
        OrderLog::info($this->merchant->id, OrderLog::U_TYPE_MERCHANT, $order->id, '拒绝订单取消申请。');
        return ['result' => 'success'];
    }

    /**
     * 套餐卡升级卡订单列表
     * @return string
     * @throws \PHPExcel_Exception
     * @throws Exception
     */
    public function actionPackUpgradeList()
    {
        $query = UserBuyPack::find();
        $query->andFilterWhere(['{{%user_buy_pack}}.no' => $this->get('search_no')]);
        $query->andFilterWhere(['{{%user_buy_pack}}.status' => $this->get('search_status')]);
        $query->andFilterWhere(['{{%user_buy_pack}}.type' => $this->get('search_type')]);
        if (!empty($this->get('search_start_date'))) {
            $query->andFilterWhere(['>=', '{{%user_buy_pack}}.create_time', strtotime($this->get('search_start_date'))]);
        }
        if (!empty($this->get('search_end_date'))) {
            $query->andFilterWhere(['<', '{{%user_buy_pack}}.create_time', strtotime($this->get('search_end_date')) + 86400]);
        }
        $query->joinWith('financeLog');
        $query->andFilterWhere(['{{%finance_log}}.pay_method' => $this->get('search_pay_method')]);
        $query->andFilterWhere(['{{%finance_log}}.status' => $this->get('search_pay_status')]);
        $query->andFilterWhere(['{{%finance_log}}.trade_no' => $this->get('search_trade_no')]);
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new PHPExcel();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '订单号',
                         '买家',
                         '购买数量',
                         '金额',
                         '状态',
                         '支付交易号',
                         '支付金额',
                         '支付方式',
                         '支付状态',
                         '创建时间',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font'=>['bold'=>true], 'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            foreach ($query->each() as $order) {
                /** @var UserBuyPack $order */
                $sheet->setCellValueExplicitByColumnAndRow(0, $r, $order->no, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, emoji_unified_to_docomo($order->user->nickname));
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $order->amount, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $order->money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, ['1' => '创建待支付', '2' => '已支付', '9' => '已取消'][$order->status], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                if (!empty($order->fid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(5, $r, $order->financeLog->trade_no);
                    $sheet->setCellValueExplicitByColumnAndRow(6, $r, $order->financeLog->money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(7, $r, KeyMap::getValue('finance_log_pay_method', $order->financeLog->pay_method));
                    $sheet->setCellValueExplicitByColumnAndRow(8, $r, KeyMap::getValue('finance_log_status', $order->financeLog->status));
                }
                $sheet->setCellValueExplicitByColumnAndRow(9, $r, Yii::$app->formatter->asDatetime($order->create_time));
                $r += 1;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="订单列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $excelWriter->save('php://output');
            return null;
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('pack_upgrade_list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }
}
