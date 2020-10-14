<?php

namespace app\modules\supplier\controllers;

use app\models\City;
use app\models\Goods;
use app\models\KeyMap;
use app\models\Express;
use app\models\Kuaidi100;
use app\models\Order;
use app\models\OrderDeliver;
use app\models\OrderDeliverItem;
use app\models\OrderItem;
use app\models\OrderLog;
use app\models\OrderRefund;
use app\models\OrderRefundProof;
use app\models\Sms;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * 订单管理
 * Class OrderController
 * @package app\modules\supplier\controllers
 */
class OrderController extends BaseController
{
    /**
     * 订单列表
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionList()
    {
        $query = Order::find()->alias('order')->groupBy('order.id');
        $query->andFilterWhere(['order.no' => $this->get('search_no')]);
        $query->joinWith(['itemList', 'itemList.goods goods', 'shop']);
        $query->andFilterWhere(['like', 'goods.title',$this->get('search_goods_name')]);
        $query->andWhere(['goods.supplier_id' => $this->supplier->id]);
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
        $query->andFilterWhere(['order.from_client' => $this->get('search_order_from')]);
        $search_deliver_info = $this->get('search_deliver_info');
        if (!empty($search_deliver_info)) {
            $query->andWhere(['like', 'order.deliver_info', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_deliver_info))) . '%', false]);
        }
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
                         '商品标题',
                         '商品规格',
                         '商品数量',
                         '结算价',
                         '物流费用',
                         '支付交易号',
                         '支付方式',
                         '支付状态',
                         '用户备注',
                         '商家备注',
                         '状态',
                         '物流信息',
                         '操作记录',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var Order $order */
            foreach ($query->each() as $order) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, empty($order->sid) ? '' : $order->shop->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $order->no, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, Yii::$app->formatter->asDatetime($order->create_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->user->nickname), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->user->real_name), DataType::TYPE_STRING);
                if (!empty($order->deliver_info)) {
                    if (strstr($order->getDeliverInfoJson('address'), '省')
                        || strstr($order->getDeliverInfoJson('address'), '上海')
                        || strstr($order->getDeliverInfoJson('address'), '重庆')
                        || strstr($order->getDeliverInfoJson('address'), '天津')
                        || strstr($order->getDeliverInfoJson('address'), '自治区')) {
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
                    if ($item->goods->supplier_id != $this->supplier->id) {
                        continue;
                    }
                    $sheet->setCellValueExplicitByColumnAndRow(8, $r + $_r1, $item->title, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(9, $r + $_r1, $item->sku_key_name, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(10, $r + $_r1, $item->amount, DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(11, $r + $_r1, empty($item->goodsSku) ? $item->goods->supplier_price: $item->goodsSku->supplier_price, DataType::TYPE_NUMERIC);
                    $_r1++;
                }
                $sheet->setCellValueExplicitByColumnAndRow(12, $r, $order->deliver_fee, DataType::TYPE_NUMERIC);
                if (!empty($order->fid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(13, $r, $order->financeLog->trade_no, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(14, $r, KeyMap::getValue('finance_log_pay_method', $order->financeLog->pay_method), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(15, $r, KeyMap::getValue('finance_log_status', $order->financeLog->status), DataType::TYPE_STRING);
                }
                $sheet->setCellValueExplicitByColumnAndRow(16, $r, emoji_unified_to_docomo($order->user_remark), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(17, $r, $order->merchant_remark, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(18, $r, KeyMap::getValue('order_status', $order->status), DataType::TYPE_STRING);
                $deliverInfo = [];
                /** @var OrderDeliver $orderDeliver */
                foreach (OrderDeliver::find()->andWhere(['oid' => $order->id])->andWhere(['supplier_id' => $this->supplier->id])->each() as $orderDeliver) {
                    if (!empty($orderDeliver->eid)) {
                        $deliverInfo[] = $orderDeliver->express->name . chr(9) . $orderDeliver->no;
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
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $orderList = $query->all();
        return $this->render('list', [
            'supplier' => $this->supplier->identity,
            'orderList' => $orderList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 代发货订单列表
     * @return string
     * @throws Exception
     * @throws \PHPExcel_Exception
     */
    public function actionListWaitPack()
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
        if (empty($order)) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        return $this->render('view', [
            'supplier' => $this->supplier->identity,
            'order' => $order,
        ]);
    }

    /**
     * 发货单列表
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionDeliverList()
    {
        $query = OrderDeliver::find()->alias('OD');
        $query->andWhere(['OD.supplier_id' => $this->supplier->id]);
        $query->joinWith(['itemList', 'order order', 'order.shop shop', 'express']);
        $query->andFilterWhere(['OD.status' => $this->get('search_status', OrderDeliver::STATUS_WAIT)]);
        $query->andWhere(['order.status' => $this->get('search_order_status', Order::STATUS_PACKED)]);
        $query->andFilterWhere(['like', 'order.no' , $this->get('search_no')]);
        $query->andFilterWhere(['like', 'OD.no' , $this->get('search_express_no')]);
        $search_deliver_info = $this->get('search_deliver_info');
        if (!empty($search_deliver_info)) {
            $query->andWhere(['like', 'order.deliver_info', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_deliver_info))) . '%', false]);
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
                $order = $orderDeliver->order;
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, empty($order->sid) ? '' : $order->shop->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $order->no, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, Yii::$app->formatter->asDatetime($order->create_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->user->nickname), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->user->nickname), DataType::TYPE_STRING);
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
                    if ($item->goods->supplier_id != $this->supplier->id) {
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
                foreach (OrderDeliver::find()->andWhere(['oid' => $order->id])->andWhere(['supplier_id' => $this->supplier->id])->each() as $_orderDeliver) {
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
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $deliverList = $query->all();
        //var_dump($query->createCommand()->getRawSql());
        return $this->render('deliver_list', [
            'deliverList' => $deliverList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 发货单所有列表
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionDeliverAllList()
    {
        $query = OrderDeliver::find()->alias('OD');
        $query->andWhere(['OD.supplier_id' => $this->supplier->id]);
        $query->joinWith(['itemList', 'order order', 'order.shop shop', 'express']);
        $query->andFilterWhere(['OD.status' => $this->get('search_status')]);
        $query->andFilterWhere(['order.status' => $this->get('search_order_status')]);
        $query->andFilterWhere(['like', 'order.no' , $this->get('search_no')]);
        $query->andFilterWhere(['like', 'OD.no' , $this->get('search_express_no')]);
        $search_deliver_info = $this->get('search_deliver_info');
        if (!empty($search_deliver_info)) {
            $query->andWhere(['like', 'order.deliver_info', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_deliver_info))) . '%', false]);
        }
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
                $order = $orderDeliver->order;
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, empty($order->sid) ? '' : $order->shop->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $order->no, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, Yii::$app->formatter->asDatetime($order->create_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->user->nickname), DataType::TYPE_STRING);
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
                    if ($item->goods->supplier_id != $this->supplier->id) {
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
                foreach (OrderDeliver::find()->andWhere(['oid' => $order->id])->andWhere(['supplier_id' => $this->supplier->id])->each() as $_orderDeliver) {
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
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $deliverList = $query->all();
//        echo $query->createCommand()->getRawSql();exit;
        return $this->render('deliver_all_list', [
            'deliverList' => $deliverList,
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
        setlocale(LC_ALL, 'en_US.UTF-8');
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
            //$trans = Yii::$app->db->beginTransaction();
            try {
                if (empty($deliver) || $deliver->supplier_id != $this->supplier->id) {
                    throw new Exception('没有找到发货单信息。');
                }
                $order = $deliver->order;

                if ($deliver->order->status > Order::STATUS_PACKED || $deliver->order->status == Order::STATUS_DELETE) {
//                    throw new Exception($deliver->order->no. '订单状态不对不能发货。');
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
                            //发送短信
                            /** @var OrderDeliverItem $deliver_item */
                            $deliver_item = OrderDeliverItem::find()->where(['did' => $deliver->id])->one();
                            /** @var OrderItem $order_item */
                            $order_item = OrderItem::findOne($deliver_item->oiid);
                            //$content = "亲爱的云淘帮会员您好！你购买的" .$order_item->goods->title . "商品已经安排发货，请您准备接收快递。有问题请及时联系云淘帮客服：18006490976";
                            $content = $order_item->goods->title;
                            Sms::send(Sms::U_TYPE_USER, $order->user->id, Sms::TYPE_SEND_NOTICE, $order->user->mobile, $content);
                        }
                        OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $order->id, '订单发货。 ', print_r($deliver->attributes, true));
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
     * 导入批量发货快递单号AJAX接口
     * @return array
     * @throws Exception
     */
    public function actionImportExpress()
    {
        setlocale(LC_ALL, 'en_US.UTF-8');
        $files = UploadedFile::getInstancesByName('files');
        if (empty($files)) {
            return ['message' => '没有找到上传文件。'];
        }

        $spreadsheet = IOFactory::load($files[0]->tempName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        for ($i = 2; $i <= count($sheetData); $i++){
            $item = $sheetData[$i];
            if (empty($item) || empty($item['U']) || empty($item['V'])|| empty($item['W'])) {
                continue;
            }
            $deliver = OrderDeliver::findOne($item['U']);
            if (empty($deliver)) {
                continue;
            }
            $trans = Yii::$app->db->beginTransaction();
            try {
                if (empty($deliver) || $deliver->supplier_id != $this->supplier->id) {
                    throw new Exception('没有找到发货单信息。');
                }

                $deliver->no = (string)$item['W'];
                /** @var Express $express */
                $express = Express::find()->andFilterWhere(['name' => $item['V']])->one();
                if (!empty($express)) {
                    $deliver->eid = $express->id;
                }
                $deliver->status = OrderDeliver::STATUS_SENT;

                $r = $deliver->save();
                if (!$r) {
                    Yii::warning($deliver->getErrors());
                    throw new Exception('无法保存发货单。');
                }
                if (!empty($deliver->no)) {
                    Kuaidi100::poll($deliver->id, empty($express)? '' : $express->code, $deliver->no);
                }

                $trans->commit();

            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                return [
                    'message' => $e->getMessage(),
                ];
            }
        }
        return ['result' => 'success', 'files' => [['url' => '']]];
    }
    /**
     * 设置备注
     * @return array
     */
    public function actionSetRemark()
    {
        $order_id = $this->get('id');
        $remark = $this->get('remark');
        if (empty($order_id)) {
            return ['result' => 'failure', 'message' => '参数错误。'];
        }
        $order = Order::findOne(['id' => $order_id]);

        if (empty($order)) {
            return ['result' => 'failure', 'message' => '未找到该订单。'];
        }

        $order->supplier_remark = $remark;
        $order->save(false);
        return ['result' => 'success'];
    }
    /**
     * 已发货订单列表
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionDeliveredList()
    {
        $_GET['search_status'] = OrderDeliver::STATUS_SENT;
        $_GET['search_order_status'] = Order::STATUS_DELIVERED;
        return $this->actionDeliverList();
    }

    /**
     * 发货单发货
     * @return string | array
     * @throws NotFoundHttpException
     */
    public function actionDeliverSend()
    {
        $id = $this->get('id');
        $deliver = OrderDeliver::findOne(['id' => $id]);
        if (empty($deliver) || $deliver->supplier_id != $this->supplier->id) {
            throw new NotFoundHttpException('没有找到发货单信息。');
        }

        $order = $deliver->order;
        $express_list = Express::find()->where(['<>', 'status', Express::STATUS_PAUSE])->all();
        if ($this->isPost()) {
            $status = $this->post('status');

                $trans = Yii::$app->db->beginTransaction();
                try {
                    if ((isset($status) && $deliver->order->status != Order::STATUS_DELIVERED) || (!isset($status) && $deliver->order->status > Order::STATUS_PACKED) || $deliver->order->status == Order::STATUS_DELETE) {
                        throw new Exception('订单状态不对不能发货。');
                    }
                    if ($deliver->load($this->post())) {
                        if (empty($deliver->no)) {
                            throw new Exception('请输入物流单号。');
                        }
                        $deliver->status = OrderDeliver::STATUS_SENT;
                        $deliver->send_time = time();
                    }
                    $r = $deliver->save();
                    if (!$r) {
                        throw new Exception('无法保存发货单。');
                    }
                    if (!empty($deliver->no)) {
                        Kuaidi100::poll($deliver->id, $deliver->express->code, $deliver->no);
//                    Kuaidi100::poll($deliver->no, $deliver->express, [
//                        'type' => 'order_deliver',
//                        'id' => $deliver->id,
//                    ]);
                    }
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
                        OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $order->id, '订单发货。 ', print_r($deliver->attributes, true));
                    } else {
                        OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $order->id, '订单修改发货快递信息。 ', print_r($deliver->attributes, true));
                    }

                    $trans->commit();
                    return ['result' => 'success'];
                } catch (Exception $e) {
                    try {
                        $trans->rollBack();
                    } catch (Exception $_) {
                    }
                    return ['result' => 'failure', 'message' => $e->getMessage()];
                }

        }
        return $this->render('deliver_send', [
            'order' => $order,
            'deliver' => $deliver,
            'express_list' => $express_list,
        ]);
    }

    /**
     * 售后列表
     * @return string
     */
    public function actionRefundList()
    {
        $query = OrderRefund::find()->alias('refund');
        $query->andWhere(['supplier_id' => $this->supplier->id]);
        $query->joinWith('order order');
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
     * 退货单详情页
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRefundView()
    {
        $id = $this->get('id');
        $refund = OrderRefund::findOne(['id' => $id]);
        if (empty($refund) || $refund->supplier_id != $this->supplier->id) {
            throw new NotFoundHttpException('没有找到售后信息。');
        }
        return $this->render('refund_view', [
            'refund' => $refund
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
        if (empty($refund) || $refund->supplier_id != $this->supplier->id) {
            return ['result' => 'failure', 'message' => '没有找到售后单。'];
        }
        if ($refund->status != OrderRefund::STATUS_REQUIRE) {
            return ['result' => 'failure', 'message' => '状态错误。'];
        }
        if (in_array($refund->type, [OrderRefund::TYPE_MONEY, OrderRefund::TYPE_CANCEL])) { // 直接退款就可以的
            try {
                $refund->doRefund(); // 退款操作
                OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $refund->oid, '通过订单售后申请。', $refund->id);
            } catch (Exception $e) {
                return ['result' => 'failure', 'message' => $e->getMessage()];
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
            OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $refund->oid, '通过订单售后申请。', $refund->id);
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
        if (empty($refund) || $refund->supplier_id != $this->supplier->id) {
            return ['result' => 'failure', 'message' => '没有找到售后单。'];
        }
        if ($refund->status != OrderRefund::STATUS_REQUIRE) {
            return ['result' => 'failure', 'message' => '状态错误。'];
        }
        $refund->status = OrderRefund::STATUS_REJECT;
        $refund->reject_time = time();
        $refund->save(false);
        if (OrderRefund::find()
                ->andWhere(['oid' => $refund->oid])
                ->andWhere(['not in', 'status', [
                    OrderRefund::STATUS_COMPLETE,
                    OrderRefund::STATUS_REJECT,
                    OrderRefund::STATUS_DELETE,
                ]])
                ->count() == 0) {
            $order = $refund->order;
            $order->status = $refund->order_status;
            $order->save(false);
        }
        OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $refund->oid, '拒绝订单售后申请。', $refund->id);
        return ['result' => 'success'];
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
        if (empty($order_refund) && $order_refund->orderItem->goods->supplier_id != $this->supplier->id) {
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
                OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $order_refund->orderItem->oid, '通过售后。', print_r($order_refund->attributes, true));
            } elseif ($order_refund->type == OrderRefund::TYPE_GOODS_MONEY) {
                $order_refund->status = OrderRefund::STATUS_ACCEPT;
                $order_refund->apply_time = time();
                if (!$order_refund->save()) {
                    return ['message' => '申请售后审核通过失败。'];
                }
                OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $order_refund->orderItem->oid, '通过售后。', print_r($order_refund->attributes, true));
            }
        } else {
            $order_refund->status = OrderRefund::STATUS_REJECT;
            $order_refund->reject_time = time();
            if (!$order_refund->save()) {
                return ['message' => '申请售后审核通过失败。'];
            }
            $order_refund->orderItem->order->status = Order::STATUS_COMPLETE;
            $order_refund->orderItem->order->save(false);
            OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $order_refund->orderItem->oid, '拒绝售后。', print_r($order_refund->attributes, true));
        }
        return ['result' => 'success'];
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
            || $refund->supplier_id != $this->supplier->id
            || $refund->status == OrderRefund::STATUS_DELETE
        ) {
            return ['result' => 'failure', 'message' => '没有找到申请记录信息。'];
        }
        if ($refund->type != OrderRefund::TYPE_GOODS_MONEY) {
            return ['result' => 'failure', 'message' => '售后类型不匹配。'];
        }
        if ($refund->status != OrderRefund::STATUS_SEND) {
            return ['result' => 'failure', 'message' => '售后状态错误，无法设置收货。'];
        }
        $refund->receive_time = time();
        $refund->status = OrderRefund::STATUS_RECEIVE;
        $refund->save(false);
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
        if (empty($order_refund) || $order_refund->orderItem->goods->supplier_id != $this->supplier->id) {
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
            || $order_refund->orderItem->goods->supplier_id != $this->supplier->id
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
//        OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $order_refund->orderItem->oid, '通过售后。', print_r($order_refund->attributes, true));

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
            return ['result' => 'failure', 'message' => '参数错误。'];
        }
        $refund = OrderRefund::findOne(['id' => $id]);
        if (empty($refund)
            || $refund->supplier_id != $this->supplier->id
            || $refund->status == OrderRefund::STATUS_DELETE
        ) {
            return ['result' => 'failure', 'message' => '没有找到申请记录信息。'];
        }
        if ($refund->status != OrderRefund::STATUS_RECEIVE) {
            return ['result' => 'failure', 'message' => '收货状态错误。'];
        }
        try {
            $refund->doRefund();
            $refund->complete_time = time();
            $refund->status = OrderRefund::STATUS_COMPLETE;
            $refund->save();
            $refund->orderItem->order->status = Order::STATUS_COMPLETE;
            $refund->orderItem->order->save(false);
            OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $refund->oid, '通过售后。', print_r($refund->attributes, true));
            return ['result' => 'success'];
        } catch (Exception $e) {
            return ['result' => 'failure', 'message' => $e->getMessage()];
        }
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
            || $order_refund->orderItem->goods->supplier_id != $this->supplier->id
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
            OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $order_refund->orderItem->oid, '通过售后。', print_r($order_refund->attributes, true));
        }

        return ['result' => 'success'];
    }

    /**
     * 售后订单设置有异议AJAX接口
     * @return array
     */
    public function actionDissentRefund()
    {
        $id = $this->get('id');
        $dissent_msg = $this->get('dissent_msg');
        $refund = OrderRefund::findOne(['id' => $id]);
        if (empty($refund) || $refund->supplier_id != $this->supplier->id) {
            return ['result' => 'failure', 'message' => '没有找到售后单。'];
        }
        if ($refund->status != OrderRefund::STATUS_SEND) {
            return ['result' => 'failure', 'message' => '状态错误。'];
        }
        $refund->status = OrderRefund::STATUS_DISSENT;
        $refund->dissent_msg = $dissent_msg;
        $refund->dissent_time = time();
        if (!$refund->save()) {
            return ['result' => 'failure', 'message' => '设置售后订单异议内容失败。'];
        }
        return ['result' => 'success'];
    }

    /**
     * 保存售后凭证AJAX接口
     * @return array
     */
    public function actionSaveRefundProof()
    {
        $refund = OrderRefund::findOne(['id' => $this->get('orid')]);
        if (empty($refund) || $refund->supplier_id != $this->supplier->id) {
            return ['result' => 'failure', 'message' => '没有找到售后。'];
        }
        if (in_array($refund->status, [OrderRefund::STATUS_COMPLETE, OrderRefund::STATUS_DELETE])) {
            return ['result' => 'failure', 'message' => '售后状态不允许添加凭证。'];
        }
        $proof = new OrderRefundProof();
        $proof->orid = $refund->id;
        $proof->from = OrderRefundProof::FROM_SELLER;
        $proof->time = time();
        if ($proof->load($this->post()) && $proof->save()) {
            return ['result' => 'success'];
        }
        return ['result' => 'failure', 'message' => '无法保存凭证。', 'errors' => $proof->errors];
    }

    public function actionEditDeliver()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order)) {
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
                        if ($item->goods->sale_type == Goods::TYPE_SUPPLIER && !empty($item->goods->supplier_id)) {
                            $deliver->supplier_id = $item->goods->supplier_id;
                            $deliver->save(false);
                        }
                        if (!$r) {
                            throw new Exception('无法保存发货单内容。');
                        }
                    }
                    $left_amount += $item->amount;
                    $left_amount -= $item->getDeliverAmount();
                }

//                if ($order->status == Order::STATUS_PAID && $left_amount >0) {
//                    $order->status = Order::STATUS_PACKING;
//                } elseif (($order->status == Order::STATUS_PACKING && $left_amount ==0) || $order->status == Order::STATUS_PAID && $left_amount ==0) {
//                    $order->status = Order::STATUS_PACKED;
//                }
//                $r = $order->save();
//                if (!$r) {
//                    throw new Exception('无法更新订单状态。');
//                }
                $shopLeft = 0;
                foreach ($order->itemList as $orderItem) {
                    $left = $orderItem->amount - $orderItem->getDeliverAmount();
                    $shopLeft += $left;
                }
                if ($shopLeft == 0) {
                    $order->status = Order::STATUS_PACKED;
                    $order->save(false);
                }
                $deliver = OrderDeliver::find()
                    ->andWhere(['oid' => $order->id, 'status' => OrderDeliver::STATUS_WAIT, 'supplier_id' => $this->supplier->id])
                    ->one();
//                $order->generateDeliver('', $amount);
//
//                /** @var OrderDeliver $deliver 找到一个可以发货的发货单 */
//                $deliver = OrderDeliver::find()
//                    ->andWhere(['oid' => $order->id, 'status' => OrderDeliver::STATUS_WAIT, 'supplier_id' => $this->supplier->id])
//                    ->one();
//                $shopLeft = 0;
//                foreach ($order->itemList as $orderItem) {
//                    $left = $orderItem->amount - $orderItem->getDeliverAmount();
//                    if (empty($orderItem->goods->supplier_id)) {
//                        $shopLeft += $left;
//                    }
//                }
//                if ($shopLeft == 0) {
//                    $order->status = Order::STATUS_PACKED;
//                    $order->save(false);
//                }
                OrderLog::info($this->supplier->id, OrderLog::U_TYPE_SUPPLIER, $order->id, '生成发货单。', print_r($deliver->attributes, true));
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
            'supplier_id' => $this->supplier->id,
            'order' => $order,
        ]);
    }
}
