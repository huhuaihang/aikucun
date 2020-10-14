<?php

namespace app\modules\admin\controllers;

use app\models\BankReconciliationAlipay;
use app\models\BankReconciliationPingan;
use app\models\BankReconciliationWeixin;
use app\models\FinanceLog;
use app\models\KeyMap;
use app\models\ManagerLog;
use app\models\Merchant;
use app\models\MerchantFinancialSettlement;
use app\models\MerchantFinancialSettlementLog;
use app\models\Order;
use app\models\ShopConfig;
use app\models\Supplier;
use app\models\SupplierConfig;
use app\models\SupplierFinancialSettlement;
use app\models\SupplierFinancialSettlementLog;
use app\models\Util;
use PHPExcel;
use PHPExcel_Cell_DataType;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * 财务管理
 * Class FinanceController
 * @package app\modules\admin\controllers
 */
class FinanceController extends BaseController
{
    /**
     * 文件上传AJAX接口
     * @see \app\modules\admin\controllers\UploadControllerTrait
     */
    use UploadControllerTrait;

    /**
     * 财务列表
     * @return string
     * @throws ForbiddenHttpException
     * @throws \PHPExcel_Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList()
    {
        if (!$this->manager->can('finance/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = FinanceLog::find();
        $query->andFilterWhere(['trade_no' => $this->get('search_trade_no')]);
        $query->andFilterWhere(['type' => $this->get('search_type')]);
        $query->andFilterWhere(['pay_method' => $this->get('search_pay_method')]);
        $query->andFilterWhere(['status' => $this->get('search_status')]);
        if (!empty($this->get('search_start_date'))) {
            $query->andFilterWhere(['>=', 'create_time', strtotime($this->get('search_start_date'))]);
        }
        if (!empty($this->get('search_end_date'))) {
            $query->andFilterWhere(['<', 'create_time', strtotime($this->get('search_end_date')) + 86400]);
        }
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new PHPExcel();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '用户昵称',
                         '用户手机号码',
                         '交易号',
                         '类型',
                         '金额',
                         '支付方式',
                         '状态',
                         '创建时间',
                         '更新时间',
                         '备注'
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font'=>['bold'=>true], 'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            foreach ($query->each() as $log) {
                /** @var FinanceLog $log */
                $sheet->setCellValueExplicitByColumnAndRow(0, $r, $log->id, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $user = $log->getUser();
                if (!empty($user)) {
                    $sheet->setCellValueExplicitByColumnAndRow(1, $r, emoji_unified_to_docomo($user->nickname), PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow(2, $r, $user->mobile, PHPExcel_Cell_DataType::TYPE_STRING);
                }
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $log->trade_no, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, KeyMap::getValue('finance_log_type', $log->type), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $log->money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, KeyMap::getValue('finance_log_pay_method', $log->pay_method), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, KeyMap::getValue('finance_log_status', $log->status), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, Yii::$app->formatter->asDatetime($log->create_time), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(9, $r, Yii::$app->formatter->asDatetime($log->update_time), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(10, $r, $log->remark, PHPExcel_Cell_DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="财务列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $excelWriter->save('php://output');
            return null;
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 财务详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        if (!$this->manager->can('finance/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $finance = FinanceLog::findOne($id);
        if (empty($finance)) {
            throw new NotFoundHttpException('没有找到财务信息。');
        }
        return $this->render('view', [
            'finance' => $finance,
        ]);
    }

    /**
     * 刷新财务状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRefreshStatus()
    {
        if (!$this->manager->can('finance/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $finance = FinanceLog::findOne($id);
        if (empty($finance)) {
            throw new NotFoundHttpException('没有找到财务记录。');
        }
        try {
            $r = $finance->refreshStatus();
            if ($r) {
                return ['result' => 'success',];
            }
            return ['message' => '无法刷新状态，请稍后重试。'];
        } catch (Exception $e) {
            return ['message' => $e->getMessage()];
        }
    }

    /**
     * 平安银行对账
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionReconciliationPingan()
    {
        if (!$this->manager->can('finance/reconciliation')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = BankReconciliationPingan::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('reconciliation_pingan', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 支付宝对账
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionReconciliationAlipay()
    {
        if (!$this->manager->can('finance/reconciliation')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = BankReconciliationAlipay::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('reconciliation_alipay', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 微信对账
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionReconciliationWechat()
    {
        if (!$this->manager->can('finance/reconciliation')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = BankReconciliationWeixin::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('reconciliation_wechat', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 商户结算表
     * @return string
     * @throws ForbiddenHttpException
     * @throws \PHPExcel_Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMerchantFinancialSettlement()
    {
        if (!$this->manager->can('finance/merchant-financial-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = MerchantFinancialSettlement::find();
        if (!empty($this->get('search_merchant'))) {
            $merchant_list = Merchant::find()->andWhere(['like', 'username', $this->get('search_merchant')])->all();
            $query->andWhere(['mid' => ArrayHelper::getColumn($merchant_list, 'id')]);
        }
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
            // 导出Excel文件
            $excel = new PHPExcel();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '商户',
                         '订单号',
                         '订单金额',
                         '退款金额',
                         '商户实收金额',
                         '服务费',
                         '状态',
                         '创建时间',
                         '结算时间',
                         '备注',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font'=>['bold'=>true], 'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            foreach ($query->each() as $model) {
                /** @var MerchantFinancialSettlement $model */
                $sheet->setCellValueExplicitByColumnAndRow(0, $r, $model->id, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $model->merchant->username, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $model->order->no, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $model->order_money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, $model->refund_money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $model->merchant_receive_money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, $model->charge, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, KeyMap::getValue('merchant_financial_settlement_status', $model->status), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, Yii::$app->formatter->asDatetime($model->create_time), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(9, $r, Yii::$app->formatter->asDatetime($model->settle_time), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(10, $r, $model->remark, PHPExcel_Cell_DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="商户结算表_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $excelWriter->save('php://output');
            return null;
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('merchant_financial_settlement', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 商户结算统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionMerchantFinancialSettlementStatistics()
    {
        if (!$this->manager->can('finance/merchant-financial-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = MerchantFinancialSettlement::find();
        $query
            ->select([
                'mid' => 'mid',
                'merchant_receive_money' => 'sum(merchant_receive_money)'
            ])
            ->andWhere(['status' => MerchantFinancialSettlement::STATUS_WAIT])
            ->groupBy('mid');
        if (!empty($this->get('search_merchant'))) {
            $merchant_list = Merchant::find()->andWhere(['like', 'username', $this->get('search_merchant')])->all();
            $query->andWhere(['mid' => ArrayHelper::getColumn($merchant_list, 'id')]);
        }
        $search_start_date = $this->get('search_start_date');
        if (empty($search_start_date)) {
            $search_start_date = date('Y-m-01', strtotime('-1 month'));
        }
        $search_start_time = strtotime($search_start_date);
        $query->andWhere(['>=', 'create_time', $search_start_time]);
        $search_end_date = $this->get('search_end_date');
        if (empty($search_end_date)) {
            $search_end_date = date('Y-m-d', strtotime(date('Y-m-01')) - 86400);
        }
        $search_end_time = strtotime($search_end_date);
        $query->andWhere(['<', 'create_time', $search_end_time + 86400]);
        $table = [];
        foreach ($query->all() as $item) {/** @var MerchantFinancialSettlement $item */
            $table[$item->mid] = [
                'mid' => $item->mid,
                'username' => $item->merchant->username,
                'merchant_receive_money' => $item->merchant_receive_money,
            ];
        }
        return $this->render('merchant_financial_settlement_statistics', [
            'table' => $table,
            'search_start_date' => $search_start_date,
            'search_end_date' => $search_end_date,
        ]);
    }

    /**
     * 支付商户结算
     * @return string
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionPayMerchantFinancialSettlement()
    {
        if (!$this->manager->can('finance/merchant-financial-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $mid = $this->get('mid');
        $start_date = $this->get('start_date');
        $end_date = $this->get('end_date');
        if (empty($mid)) {
            throw new BadRequestHttpException('没有找到结算商户。');
        }
        if (empty($start_date) || empty($end_date)) {
            throw new BadRequestHttpException('没有指定结算日期。');
        }
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        if (!$start_time) {
            throw new BadRequestHttpException('开始日期格式错误。');
        }
        if (!$end_time) {
            throw new BadRequestHttpException('结束日期格式错误。');
        }
        $merchant = Merchant::findOne($mid);
        if (empty($merchant)) {
            throw new BadRequestHttpException('没有找到商户信息。');
        }
        $query = MerchantFinancialSettlement::find()
            ->andWhere(['mid' => $mid])
            ->andWhere(['>=', 'create_time', $start_time])
            ->andWhere(['<', 'create_time', $end_time + 86400])
            ->andWhere(['status' => MerchantFinancialSettlement::STATUS_WAIT]);
        $model_list = $query->all();
        $model = new MerchantFinancialSettlementLog();
        $model->mid = $merchant->id;
        $money = 0;
        foreach ($model_list as $item) {/** @var MerchantFinancialSettlement $item */
            $money += $item->merchant_receive_money;
        }
        if (Util::comp($money, 0, 2) <= 0) {
            throw new BadRequestHttpException('没有找到任何结算信息。');
        }
        $model->money = $money;
        $bank_info = '';
        $shop = $merchant->shop;
        $finance_bank_name = ShopConfig::getConfig($shop->id, 'finance_bank_name');
        if (!empty($finance_bank_name)) {
            $bank_info .= '开户银行：' . $finance_bank_name . chr(10);
            $bank_info .= '开户行所在地：' . ShopConfig::getConfig($shop->id, 'finance_bank_addr') . chr(10);
            $bank_info .= '银行账户名：' . ShopConfig::getConfig($shop->id, 'finance_bank_account_name') . chr(10);
            $bank_info .= '银行账号：' . ShopConfig::getConfig($shop->id, 'finance_bank_account') . chr(10);
        }
        $finance_alipay_name = ShopConfig::getConfig($shop->id, 'finance_alipay_name');
        if (!empty($finance_alipay_name)) {
            $bank_info .= '支付宝姓名：' . $finance_alipay_name . chr(10);
            $bank_info .= '支付宝账号：' . ShopConfig::getConfig($shop->id, 'finance_alipay_account') . chr(10);
        }
        $finance_weixin_account = ShopConfig::getConfig($shop->id, 'finance_weixin_account');
        if (!empty($finance_weixin_account)) {
            $bank_info .= '微信账号：' . $finance_weixin_account . chr(10);
        }
        $model->bank_info = $bank_info;
        $model->create_time = time();
        if ($model->load($this->post())) {
            if (Util::comp($model->money, 0, 2) <= 0) {
                throw new BadRequestHttpException('金额错误。');
            }
            $trans = Yii::$app->db->beginTransaction();
            try {
                if (!$model->save()) {
                    $errors = $model->errors;
                    $error = array_shift($errors)[0];
                    throw new Exception($error);
                }
                foreach ($model_list as $item) {/** @var MerchantFinancialSettlement $item */
                    $item->status = [
                        MerchantFinancialSettlementLog::STATUS_WAIT => MerchantFinancialSettlement::STATUS_WAIT,
                        MerchantFinancialSettlementLog::STATUS_SETTLE => MerchantFinancialSettlement::STATUS_SETTLE,
                    ][$model->status];
                    $item->settle_time = time();
                    $item->lid = $model->id;
                    $item->save();
                }
                ManagerLog::info($this->manager->id, '商户结算', print_r($model->attributes, true));
                $trans->commit();
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/finance/merchant-financial-settlement-log']),
                    'txt' => '结算记录列表'
                ]));
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                Yii::$app->session->addFlash('error', '商户结算错误：' . $e->getMessage());
            }
        }
        return $this->render('merchant_financial_settlement_pay', [
            'model_list' => $model_list,
            'model' => $model,
        ]);
    }

    /**
     * 商户结算记录
     * @return string
     * @throws ForbiddenHttpException
     * @throws \PHPExcel_Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMerchantFinancialSettlementLog()
    {
        if (!$this->manager->can('finance/merchant-financial-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = MerchantFinancialSettlementLog::find();
        if (!empty($this->get('search_merchant'))) {
            $merchant_list = Merchant::find()->andWhere(['like', 'username', $this->get('search_merchant')])->all();
            $query->andWhere(['mid' => ArrayHelper::getColumn($merchant_list, 'id')]);
        }
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
            $excel = new PHPExcel();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '商户',
                         '金额',
                         '银行',
                         '凭证',
                         '创建时间',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font'=>['bold'=>true], 'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            foreach ($query->each() as $model) {
                /** @var MerchantFinancialSettlementLog $model */
                $sheet->setCellValueExplicitByColumnAndRow(0, $r, $model->id, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $model->merchant->username, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $model->money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $model->bank_info, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, Url::base(true) . Yii::$app->params['upload_url'] . $model->proof_file, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, Yii::$app->formatter->asDatetime($model->create_time), PHPExcel_Cell_DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="商户结算记录_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $excelWriter->save('php://output');
            return null;
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('merchant_financial_settlement_log', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 修改商户结算记录
     * @return string
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditMerchantFinancialSettlementLog()
    {
        if (!$this->manager->can('finance/merchant-financial-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (empty($id)) {
            throw new BadRequestHttpException('参数错误。');
        }
        $model = MerchantFinancialSettlementLog::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到结算记录信息。');
        }
        $model_list = MerchantFinancialSettlement::find()
            ->andWhere(['lid' => $model->id])
            ->all();
        if ($model->load($this->post())) {
            if (Util::comp($model->money, 0, 2) <= 0) {
                throw new BadRequestHttpException('金额错误。');
            }
            $trans = Yii::$app->db->beginTransaction();
            try {
                if (!$model->save()) {
                    $errors = $model->errors;
                    $error = array_shift($errors)[0];
                    throw new Exception($error);
                }
                foreach ($model_list as $item) {/** @var MerchantFinancialSettlement $item */
                    $item->status = [
                        MerchantFinancialSettlementLog::STATUS_WAIT => MerchantFinancialSettlement::STATUS_WAIT,
                        MerchantFinancialSettlementLog::STATUS_SETTLE => MerchantFinancialSettlement::STATUS_SETTLE,
                    ][$model->status];
                    $item->settle_time = time();
                    $item->lid = $model->id;
                    $item->save();
                }
                ManagerLog::info($this->manager->id, '商户结算', print_r($model->attributes, true));
                $trans->commit();
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/finance/merchant-financial-settlement-log']),
                    'txt' => '结算记录列表'
                ]));
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                Yii::$app->session->addFlash('error', '商户结算错误：' . $e->getMessage());
            }
        }
        return $this->render('merchant_financial_settlement_log_edit', [
            'model' => $model,
            'model_list' => $model_list,
        ]);
    }

    /**
     * 供货商结算表
     * @return string
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSupplierFinancialSettlement()
    {
        if (!$this->manager->can('finance/supplier-financial-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = SupplierFinancialSettlement::find();
        if (!empty($this->get('search_supplier'))) {
            $supplier_list = Supplier::find()->andWhere(['like', 'name', $this->get('search_supplier')])->all();
            $query->andWhere(['sid' => ArrayHelper::getColumn($supplier_list, 'id')]);
        }
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
            return $this->exportSupplierFinancialSettlementList($query->all());
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $settlementList = $query->all();
        return $this->render('supplier_financial_settlement', [
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
    private function exportSupplierFinancialSettlementList($settlementList)
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
        header('Content-Disposition: attachment;filename="供货商结算表_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        $excelWriter = IOFactory::createWriter($excel, 'Xls');
        $excelWriter->save('php://output');
        die();
    }

    /**
     * 供货商结算单添加到结算记录AJAX接口
     * @return array
     */
    public function actionSupplierFinancialSettlementAddToLog()
    {
        if (!$this->manager->can('finance/supplier-financial-settlement')) {
            return ['result' => 'failure', 'message' => '没有权限。'];
        }
        $id = $this->get('id');
        $settlement = SupplierFinancialSettlement::findOne(['id' => $id]);
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
     * 供货商结算记录
     * @return string
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSupplierFinancialSettlementLog()
    {
        if (!$this->manager->can('finance/supplier-financial-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = SupplierFinancialSettlementLog::find();
        if (!empty($this->get('search_supplier'))) {
            $supplier_list = Supplier::find()->andWhere(['like', 'name', $this->get('search_supplier')])->all();
            $query->andWhere(['sid' => ArrayHelper::getColumn($supplier_list, 'id')]);
        }
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
                         '供货商',
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
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $log->supplier->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, $log->money, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, $log->bank_info, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $log->proof_file, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, Yii::$app->formatter->asDatetime($log->create_time), DataType::TYPE_STRING);
                $r++;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="供货商结算记录_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $logList = $query->all();
        return $this->render('supplier_financial_settlement_log', [
            'logList' => $logList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 供货商结算记录详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSupplierFinancialSettlementLogView()
    {
        if (!$this->manager->can('finance/supplier-financial-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $log = SupplierFinancialSettlementLog::findOne(['id' => $id]);
        if (empty($log)) {
            throw new NotFoundHttpException('没有找到结算记录信息。');
        }
        $settlementList = SupplierFinancialSettlement::find()
            ->andWhere(['lid' => $log->id])
            ->all();
        if ($this->get('export') == 'excel') {
            return $this->exportSupplierFinancialSettlementList($settlementList);
        }
        return $this->render('supplier_financial_settlement_log_view', [
            'log' => $log,
            'settlementList' => $settlementList,
        ]);
    }

    /**
     * 修改供货商结算记录
     * @return string
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionEditSupplierFinancialSettlementLog()
    {
        if (!$this->manager->can('finance/supplier-financial-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $log = SupplierFinancialSettlementLog::findOne(['id' => $id]);
        if (empty($log)) {
            throw new NotFoundHttpException('没有找到结算记录信息。');
        }
        $settlementList = SupplierFinancialSettlement::find()
            ->andWhere(['lid' => $log->id])
            ->all();
        if ($this->get('export') == 'excel') {
            return $this->exportSupplierFinancialSettlementList($settlementList);
        }
        if ($log->load($this->post())) {
            if (Util::comp($log->money, 0, 2) <= 0) {
                throw new BadRequestHttpException('金额错误。');
            }
            if ($log->save()) {
                ManagerLog::info($this->manager->id, '修改供货商结算记录', print_r($log->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/finance/supplier-financial-settlement-log']),
                    'txt' => '结算记录列表'
                ]));
            }
        }
        return $this->render('supplier_financial_settlement_log_edit', [
            'log' => $log,
            'settlementList' => $settlementList,
        ]);
    }

    /**
     * 设置供货商结算记录为已锁定AJAX接口
     * @return array
     */
    public function actionSetSupplierFinancialSettlementLogLock()
    {
        if (!$this->manager->can('finance/supplier-financial-settlement')) {
            return ['result' => 'failure', 'message' => '没有权限。'];
        }
        $id = $this->get('id');
        $log = SupplierFinancialSettlementLog::findOne(['id' => $id]);
        if (empty($log)) {
            return ['result' => 'failure', 'message' => '没有找到结算记录。'];
        }
        if ($log->status != SupplierFinancialSettlementLog::STATUS_WAIT) {
            return ['result' => 'failure', 'message' => '结算记录状态错误。'];
        }
        $log->status = SupplierFinancialSettlementLog::STATUS_LOCK;
        $log->save();
        ManagerLog::info($this->manager->id, '设置供货商结算记录为已锁定。', $log->id);
        return [];
    }

    /**
     * 设置供货商结算记录为已付款AJAX接口
     * @return array
     */
    public function actionSetSupplierFinancialSettlementLogPay()
    {
        if (!$this->manager->can('finance/supplier-financial-settlement')) {
            return ['result' => 'failure', 'message' => '没有权限。'];
        }
        $id = $this->get('id');
        $log = SupplierFinancialSettlementLog::findOne(['id' => $id]);
        if (empty($log)) {
            return ['result' => 'failure', 'message' => '没有找到结算记录。'];
        }
        if ($log->status != SupplierFinancialSettlementLog::STATUS_LOCK) {
            return ['result' => 'failure', 'message' => '结算记录状态错误。'];
        }
        if (empty($log->proof_file)) {
            return ['result' => 'failure', 'message' => '没有设置打款凭证。'];
        }
        $log->status = SupplierFinancialSettlementLog::STATUS_SETTLE;
        $log->save(false);
        SupplierFinancialSettlement::updateAll(['status' => SupplierFinancialSettlement::STATUS_SETTLE, 'settle_time' => time()], ['lid' => $log->id]);
        ManagerLog::info($this->manager->id, '设置供货商结算记录为已支付。', $log->id);
        return [];
    }

    /**
     * 设置供货商结算记录为已给发票AJAX接口
     * @return array
     */
    public function actionSetSupplierFinancialSettlementLogBill()
    {
        if (!$this->manager->can('finance/supplier-financial-settlement')) {
            return ['result' => 'failure', 'message' => '没有权限。'];
        }
        $id = $this->get('id');
        $log = SupplierFinancialSettlementLog::findOne(['id' => $id]);
        if (empty($log)) {
            return ['result' => 'failure', 'message' => '没有找到结算记录。'];
        }
        $log->is_bill = 1;
        $log->save(false);
        ManagerLog::info($this->manager->id, '设置供货商结算记录为已给发票。', $log->id);
        return [];
    }
}
