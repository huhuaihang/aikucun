<?php

namespace app\modules\supplier\controllers;

use app\models\KeyMap;
use app\models\Order;
use app\models\SupplierConfig;
use app\models\SupplierFinancialSettlement;
use app\models\SupplierFinancialSettlementLog;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * 财务管理
 * Class FinanceController
 * @package app\modules\supplier\controllers
 */
class  FinanceController extends BaseController
{
    /**
     * 供货商结算表
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSettlement()
    {
        $query = SupplierFinancialSettlement::find();
        $query->andWhere(['sid' => $this->supplier->id]);
        if (!empty($this->get('search_order_no'))) {
            $order_list = Order::find()->andWhere(['like', 'no', $this->get('search_order_no')])->all();
            $query->andWhere(['oid' => ArrayHelper::getColumn($order_list, 'id')]);
        }
        if (!empty($this->get('search_start_date'))) {
            $search_start_time = strtotime($this->get('search_start_date'));
            $query->andWhere(['>=', 'create_time', $search_start_time]);
        }
        if (!empty($this->get('search_end_date'))) {
            $search_end_time = strtotime($this->get('search_end_date'));
            $query->andWhere(['<', 'create_time', $search_end_time + 86400]);
        }
        $query->andFilterWhere(['status' => $this->get('search_status')]);
        if ($this->get('export') == 'excel') {
            return $this->exportSettlementList($query->all());
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $settlementList = $query->all();
        return $this->render('settlement', [
            'settlementList' => $settlementList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 导出供货商结算单
     * @param SupplierFinancialSettlement[] $settlementList
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function exportSettlementList($settlementList)
    {
        $excel = new Spreadsheet();
        $sheet = $excel->setActiveSheetIndex(0);
        foreach ([
                     '编号',
                     '供货商编号',
                     '供货商名称',
                     '订单号',
                     '商品',
                     '规格',
                     '结算数量',
                     '结算单价',
                     '结算金额',
                     '状态',
                     '创建时间',
                     '结算时间',
                     '备注',
                 ] as $index => $title) {
            $sheet->setCellValue(chr(65 + $index) . '1', $title);
        }
        $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        $r = 2;
        foreach ($settlementList as $settlement) {
            $sheet->setCellValueExplicitByColumnAndRow(1, $r, $settlement->id, DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicitByColumnAndRow(2, $r, $settlement->sid, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(3, $r, $settlement->supplier->name, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(4, $r, $settlement->order->no, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(5, $r, $settlement->orderItem->title, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(6, $r, $settlement->orderItem->sku_key_name, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(7, $r, $settlement->amount, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(8, $r, $settlement->price, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(9, $r, $settlement->money, DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicitByColumnAndRow(10, $r, KeyMap::getValue('supplier_financial_settlement_status', $settlement->status), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(11, $r, Yii::$app->formatter->asDatetime($settlement->create_time), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(12, $r, Yii::$app->formatter->asDatetime($settlement->settle_time), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(13, $r, $settlement->remark, DataType::TYPE_STRING);
            $r++;
        }
        $sheet->freezePane('A2');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="结算表_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        $excelWriter = IOFactory::createWriter($excel, 'Xls');
        $excelWriter->save('php://output');
        die();
    }

    /**
     * 供货商结算单添加到结算记录AJAX接口
     * @return array
     */
    public function actionSettlementAddToLog()
    {
        $id = $this->get('id');
        $settlement = SupplierFinancialSettlement::findOne(['id' => $id, 'sid' => $this->supplier->id]);
        if (empty($settlement)) {
            return ['result' => 'failure', 'message' => '没有找到结算单。'];
        }
        if ($settlement->status != SupplierFinancialSettlement::STATUS_MONEY_FIXED) {
            return ['result' => 'failure', 'message' => '结算单状态错误。'];
        }
        /** @var SupplierFinancialSettlementLog $log */
        $log = SupplierFinancialSettlementLog::find()
            ->andWhere(['sid' => $settlement->sid])
            ->andWhere(['status' => SupplierFinancialSettlementLog::STATUS_WAIT])
            ->one();
        if (empty($log)) {
            $log = new SupplierFinancialSettlementLog();
            $log->sid = $settlement->sid;
            $log->money = 0;
            $bank_info = '';
            $finance_bank_name = SupplierConfig::getConfig($log->sid, 'finance_bank_name');
            if (!empty($finance_bank_name)) {
                $bank_info .= '开户银行：' . $finance_bank_name . chr(10);
                $bank_info .= '开户行所在地：' . SupplierConfig::getConfig($log->sid, 'finance_bank_addr') . chr(10);
                $bank_info .= '银行账户名：' . SupplierConfig::getConfig($log->sid, 'finance_bank_account_name') . chr(10);
                $bank_info .= '银行账号：' . SupplierConfig::getConfig($log->sid, 'finance_bank_account') . chr(10);
            }
            $finance_alipay_name = SupplierConfig::getConfig($log->sid, 'finance_alipay_name');
            if (!empty($finance_alipay_name)) {
                $bank_info .= '支付宝姓名：' . $finance_alipay_name . chr(10);
                $bank_info .= '支付宝账号：' . SupplierConfig::getConfig($log->sid, 'finance_alipay_account') . chr(10);
            }
            $finance_weixin_account = SupplierConfig::getConfig($log->sid, 'finance_weixin_account');
            if (!empty($finance_weixin_account)) {
                $bank_info .= '微信账号：' . $finance_weixin_account . chr(10);
            }
            $log->bank_info = $bank_info;
            $log->create_time = time();
            $log->status = SupplierFinancialSettlementLog::STATUS_WAIT;
            if (!$log->save()) {
                return ['result' => 'failure', 'message' => '无法保存结算记录信息。', 'errors' => $log->errors];
            }
        }
        $settlement->lid = $log->id;
        $settlement->status = SupplierFinancialSettlement::STATUS_WAIT_PAY;
        $settlement->save(false);
        $log->updateCounters(['money' => $settlement->money]);
        return ['result' => 'success'];
    }

    /**
     * 供货商结算单搜索添加到结算记录AJAX接口
     * @return array
     */
    public function actionSettlementQueryAddToLog()
    {
        $query = SupplierFinancialSettlement::find();
        $query->andWhere(['sid' => $this->supplier->id]);
        if (!empty($this->get('search_order_no'))) {
            $order_list = Order::find()->andWhere(['like', 'no', $this->get('search_order_no')])->all();
            $query->andWhere(['oid' => ArrayHelper::getColumn($order_list, 'id')]);
        }
        if (!empty($this->get('search_start_date'))) {
            $search_start_time = strtotime($this->get('search_start_date'));
            $query->andWhere(['>=', 'create_time', $search_start_time]);
        }
        if (!empty($this->get('search_end_date'))) {
            $search_end_time = strtotime($this->get('search_end_date'));
            $query->andWhere(['<', 'create_time', $search_end_time + 86400]);
        }
        $query->andFilterWhere(['status' => SupplierFinancialSettlement::STATUS_MONEY_FIXED]);
        /** @var SupplierFinancialSettlementLog $log */
        $log = SupplierFinancialSettlementLog::find()
            ->andWhere(['sid' => $this->supplier->id])
            ->andWhere(['status' => SupplierFinancialSettlementLog::STATUS_WAIT])
            ->one();
        if (empty($log)) {
            $log = new SupplierFinancialSettlementLog();
            $log->sid = $this->supplier->id;
            $log->money = 0;
            $bank_info = '';
            $finance_bank_name = SupplierConfig::getConfig($log->sid, 'finance_bank_name');
            if (!empty($finance_bank_name)) {
                $bank_info .= '开户银行：' . $finance_bank_name . chr(10);
                $bank_info .= '开户行所在地：' . SupplierConfig::getConfig($log->sid, 'finance_bank_addr') . chr(10);
                $bank_info .= '银行账户名：' . SupplierConfig::getConfig($log->sid, 'finance_bank_account_name') . chr(10);
                $bank_info .= '银行账号：' . SupplierConfig::getConfig($log->sid, 'finance_bank_account') . chr(10);
            }
            $finance_alipay_name = SupplierConfig::getConfig($log->sid, 'finance_alipay_name');
            if (!empty($finance_alipay_name)) {
                $bank_info .= '支付宝姓名：' . $finance_alipay_name . chr(10);
                $bank_info .= '支付宝账号：' . SupplierConfig::getConfig($log->sid, 'finance_alipay_account') . chr(10);
            }
            $finance_weixin_account = SupplierConfig::getConfig($log->sid, 'finance_weixin_account');
            if (!empty($finance_weixin_account)) {
                $bank_info .= '微信账号：' . $finance_weixin_account . chr(10);
            }
            $log->bank_info = $bank_info;
            $log->create_time = time();
            $log->status = SupplierFinancialSettlementLog::STATUS_WAIT;
            if (!$log->save()) {
                return ['result' => 'failure', 'message' => '无法保存结算记录信息。', 'errors' => $log->errors];
            }
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $count = 0;
            /** @var SupplierFinancialSettlement $settlement */
            foreach ($query->each() as $settlement) {
                $settlement->lid = $log->id;
                $settlement->status = SupplierFinancialSettlement::STATUS_WAIT_PAY;
                $settlement->save(false);
                $log->updateCounters(['money' => $settlement->money]);
                $count++;
            }
            $trans->commit();
            return ['count' => $count, 'result' => 'success'];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $_) {
            }
            return ['result' => 'failure', 'message' => $e->getMessage()];
        }
    }

    /**
     * 供货商结算记录
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSettlementLog()
    {
        $query = SupplierFinancialSettlementLog::find();
        $query->andWhere(['sid' => $this->supplier->id]);
        if (!empty($this->get('search_start_date'))) {
            $search_start_time = strtotime($this->get('search_start_date'));
            $query->andWhere(['>=', 'create_time', $search_start_time]);
        }
        if (!empty($this->get('search_end_date'))) {
            $search_end_time = strtotime($this->get('search_end_date'));
            $query->andWhere(['<', 'create_time', $search_end_time + 86400]);
        }
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '金额',
                         '银行',
                         '凭证',
                         '创建时间',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var SupplierFinancialSettlementLog $log */
            foreach ($query->each() as $log) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $log->id, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $log->money, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $log->bank_info, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, $log->proof_file, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, Yii::$app->formatter->asDatetime($log->create_time), DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="结算记录_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $logList = $query->all();
        return $this->render('settlement_log', [
            'logList' => $logList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 供货商结算记录详情
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSettlementLogView()
    {
        $id = $this->get('id');
        $log = SupplierFinancialSettlementLog::findOne(['id' => $id, 'sid' => $this->supplier->id]);
        if (empty($log)) {
            throw new NotFoundHttpException('没有找到结算记录信息。');
        }
        $settlementList = SupplierFinancialSettlement::find()
            ->andWhere(['lid' => $log->id])
            ->all();
        if ($this->get('export') == 'excel') {
            return $this->exportSettlementList($settlementList);
        }
        return $this->render('settlement_log_view', [
            'log' => $log,
            'settlementList' => $settlementList,
        ]);
    }
}
