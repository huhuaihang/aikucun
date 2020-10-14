<?php

namespace app\modules\merchant\controllers;

use app\models\MerchantFinancialSettlement;
use app\models\MerchantFinancialSettlementLog;
use app\models\Order;
use app\models\ShopConfig;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * 财务管理
 * Class FinanceController
 * @package app\modules\merchant\controllers
 */
class FinanceController extends BaseController
{
    /**
     * 结算表
     * @return string
     */
    public function actionFinancialSettlement()
    {
        $query = MerchantFinancialSettlement::find();
        $query->andWhere(['mid' => $this->merchant->id]);
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
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('financial_settlement', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 申请结算
     * @return string|array
     */
    public function actionRequireFinancialSettlement()
    {
        $search_start_date = $this->get('search_start_date');
        $search_end_date = $this->get('search_end_date');
        $query = MerchantFinancialSettlement::find()
            ->andWhere(['mid' => $this->merchant->id])
            ->andWhere(['lid' => null])
            ->andWhere(['status' => MerchantFinancialSettlement::STATUS_WAIT]);
        if (!empty($search_start_date)) {
            $search_start_time = strtotime($search_start_date);
            $query->andWhere(['>=', 'create_time', $search_start_time]);
        }
        if (!empty($search_end_date)) {
            $search_end_time = strtotime($search_end_date);
            $query->andWhere(['<', 'create_time', $search_end_time]);
        }
        $model_list = $query->all();
        $model = new MerchantFinancialSettlementLog();
        $model->mid = $this->merchant->id;
        $model->money = array_sum(ArrayHelper::getColumn($model_list, 'merchant_receive_money'));
        $bank_info = '';
        $finance_bank_name = ShopConfig::getConfig($this->shop->id, 'finance_bank_name');
        if (!empty($finance_bank_name)) {
            $bank_info .= '开户银行：' . $finance_bank_name . chr(10);
            $bank_info .= '开户行所在地：' . ShopConfig::getConfig($this->shop->id, 'finance_bank_addr') . chr(10);
            $bank_info .= '银行账户名：' . ShopConfig::getConfig($this->shop->id, 'finance_bank_account_name') . chr(10);
            $bank_info .= '银行账号：' . ShopConfig::getConfig($this->shop->id, 'finance_bank_account') . chr(10);
        }
        $finance_alipay_name = ShopConfig::getConfig($this->shop->id, 'finance_alipay_name');
        if (!empty($finance_alipay_name)) {
            $bank_info .= '支付宝姓名：' . $finance_alipay_name . chr(10);
            $bank_info .= '支付宝账号：' . ShopConfig::getConfig($this->shop->id, 'finance_alipay_account') . chr(10);
        }
        $finance_weixin_account = ShopConfig::getConfig($this->shop->id, 'finance_weixin_account');
        if (!empty($finance_weixin_account)) {
            $bank_info .= '微信账号：' . $finance_weixin_account . chr(10);
        }
        $model->bank_info = $bank_info;
        $model->create_time = time();
        $model->status = MerchantFinancialSettlementLog::STATUS_WAIT;
        if ($model->load($this->post()) && !empty($model_list)) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                $r = $model->save();
                if (!$r) {
                    $errors = $model->errors;
                    $error = array_shift($errors)[0];
                    throw new Exception($error);
                }
                foreach ($model_list as $_model) {/** @var MerchantFinancialSettlement $_model */
                    $_model->lid = $model->id;
                    $_model->save(false);
                }
                $trans->commit();
                Yii::$app->session->addFlash('success', '申请已提交。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/merchant/finance/financial-settlement-log']),
                    'txt' => '结算记录列表'
                ]));
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
            }
        }
        return $this->render('financial_settlement_require', [
            'model' => $model,
            'model_list' => $model_list,
        ]);
    }

    /**
     * 结算记录
     * @return string
     */
    public function actionFinancialSettlementLog()
    {
        $query = MerchantFinancialSettlementLog::find();
        $query->andWhere(['mid' => $this->merchant->id]);
        if (!empty($this->get('search_start_date'))) {
            $search_start_time = strtotime($this->get('search_start_date'));
            $query->andWhere(['>=', 'create_time', $search_start_time]);
        }
        if (!empty($this->get('search_end_date'))) {
            $search_end_time = strtotime($this->get('search_end_date'));
            $query->andWhere(['<', 'create_time', $search_end_time + 86400]);
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('financial_settlement_log', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }
}
