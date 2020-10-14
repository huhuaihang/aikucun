<?php

namespace app\modules\admin\controllers;

use app\models\Agent;
use app\models\AgentConfig;
use app\models\AgentFee;
use app\models\FinanceLog;
use app\models\Goods;
use app\models\GoodsBrand;
use app\models\GoodsCategory;
use app\models\ManagerLog;
use app\models\Merchant;
use app\models\MerchantConfig;
use app\models\MerchantFee;
use app\models\MerchantMessage;
use app\models\Shop;
use app\models\ShopBrand;
use app\models\ShopConfig;
use app\models\Sms;
use app\models\SystemMessage;
use app\models\User;
use app\models\UserMessage;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * 商户管理
 * Class MerchantController
 * @package app\modules\admin\controllers
 */
class MerchantController extends BaseController
{
    /**
     * 代理商列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionAgent()
    {
        if (!$this->manager->can('merchant/agent')) {
            throw new ForbiddenHttpException('没有权限。');
        }

        $query = Agent::find();
        $query->andWhere(['status' => [Agent::STATUS_ACTIVE, Agent::STATUS_STOPED]]);
        $query->andFilterWhere(['id' => $this->get('search_id')]);
        $query->andFilterWhere(['area' => $this->get('search_area')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('agent', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加/修改代理商
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionEditAgent()
    {
        if (!$this->manager->can('merchant/edit-agent')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = Agent::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到代理商信息。');
            }
        } else {
            $model = new Agent();
            $model->create_time = time();
            $model->status = Agent::STATUS_ACTIVE;
        }
        if ($model->load($this->post())) {
            if (!empty($this->post('Agent')) && !empty($this->post('Agent')['password'])) {
                $model->password = Yii::$app->security->generatePasswordHash($this->post('Agent')['password']);
            }
            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存代理商', print_r($model->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/merchant/agent']),
                    'txt' => '代理商列表'
                ]));
            }
        }
        return $this->render('agent_edit', [
            'model' => $model
        ]);
    }

    /**
     * 删除代理商AJAX接口
     * @return array
     */
    public function actionDeleteAgent()
    {
        if (!$this->manager->can('merchant/delete-agent')) {
            return ['message' => '没有权限。'];
        }
        $id = $this->get('id');
        $model = Agent::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到代理商信息。'];
        }
        $model->status = Agent::STATUS_DELETED;
        $model->save(false);
        ManagerLog::info($this->manager->id, '删除代理商', print_r($model->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 设置代理商状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionStatusAgent()
    {
        if (!$this->manager->can('merchant/status-agent')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        /* @var $model Agent */
        $model = Agent::find()->where(['id' => $id])->andWhere(['<>', 'status', Agent::STATUS_DELETED])->one();
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到代理商数据。');
        }
        $new_status = [
            Agent::STATUS_ACTIVE => Agent::STATUS_STOPED,
            Agent::STATUS_STOPED => Agent::STATUS_ACTIVE
        ][$model->status];
        ManagerLog::info($this->manager->id, '设置代理商状态', $model->id . ':' . $model->status . '->' . $new_status);
        $model->status = $new_status;
        $model->save();
        return [
            'result' => 'success'
        ];
    }

    /**
     * 商户列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionList()
    {
        if (!$this->manager->can('merchant/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }

        $query = Merchant::find();
        $query->joinWith(['shop']);
        $query->andFilterWhere(['{{%merchant}}.status' => $this->get('search_status')]);
        $query->andFilterWhere(['{{%merchant}}.id' => $this->get('search_id')]);
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andFilterWhere(['like', 'username', $this->get('search_username')]);
        $query->andFilterWhere(['like', 'mobile', $this->get('search_mobile')]);
        if ($this->get('search_agent')) {
            $agent_list = Agent::find()->andWhere(['like', 'username', $this->get('search_agent')])->all();
            if (empty($agent_list)) {
                $query->andWhere('1 <> 1');
            } else {
                $query->andWhere(['aid' => ArrayHelper::getColumn($agent_list, 'id')]);
            }
        }
        if ($this->get('search_area')) {
            $shop_list = Shop::find()->andWhere(['area' => $this->get('search_area')])->all();
            if (empty($shop_list)){
                $query->andWhere('1 <> 1');
            } else {
                $query->andWhere(['{{%merchant}}.id' => ArrayHelper::getColumn($shop_list, 'mid')]);
            }
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 商户详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        if (!$this->manager->can('merchant/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $merchant = Merchant::findOne($id);
        if (empty($merchant)) {
            throw new NotFoundHttpException('没有找到商户信息。');
        }
        return $this->render('view', [
            'merchant' => $merchant,
        ]);
    }

    /**
     * 设置商户结算比率AJAX接口
     */
    public function actionMerchantChargeRatio()
    {
        if (!$this->manager->can('merchant/edit')) {
            return ['message' => '没有权限。'];
        }
        $id = $this->get('id');
        $model = Merchant::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到商户信息。'];
        }
        $merchant_charge_ratio = $this->get('merchant_charge_ratio');
        if (empty($merchant_charge_ratio)) {
            return ['message' => '比率必填。'];
        }
        MerchantConfig::setConfig($model->id, 'merchant_charge_ratio', $merchant_charge_ratio);
        ManagerLog::info($this->manager->id, '设置商户结算比率', print_r($merchant_charge_ratio, true));
        return ['result' => 'success'];
    }

    /**
     * 添加商户
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionAdd()
    {
        if (!$this->manager->can('merchant/edit')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        if ($this->isPost()) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                $merchant = new Merchant();
                if (!$merchant->load($this->post())) {
                    throw new Exception('无法加载商户数据。');
                }
                $merchant->password = Yii::$app->security->generatePasswordHash($merchant->password);
                $merchant->status = Merchant::STATUS_COMPLETE;
                $merchant->create_time = time();
                $merchant->remark = '手动导入';
                if (!$merchant->save()) {
                    throw new Exception(print_r($merchant->errors, true));
                }
                $financeLog = new FinanceLog();
                $financeLog->type = FinanceLog::TYPE_MERCHANT_EARNEST_MONEY;
                $financeLog->money = 0;
                $financeLog->pay_method = FinanceLog::PAY_METHOD_YE;
                $financeLog->status = FinanceLog::STATUS_SUCCESS;
                $financeLog->create_time = time();
                $financeLog->update_time = time();
                if (!$financeLog->save()) {
                    throw new Exception(print_r($financeLog->errors, true));
                }
                $shop = new Shop();
                if (!$shop->load($this->post())) {
                    throw new Exception('无法加载店铺数据。');
                }
                $shop->mid = $merchant->id;
                $shop->earnest_money_fid = $financeLog->id;
                $shop->tid = 1;
                $shop->status = Shop::STATUS_ACCEPT;
                if (!$shop->save()) {
                    throw new Exception(print_r($shop->errors, true));
                }
                $trans->commit();
                ManagerLog::info($this->manager->id, '添加商户', print_r($merchant->attributes, true));
                ManagerLog::info($this->manager->id, '添加店铺', print_r($merchant->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/merchant/list']),
                    'txt' => '商户列表'
                ]));
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                Yii::$app->session->addFlash('error', '保存信息错误：' . $e->getMessage());
            }
        }
        return $this->render('add');
    }

    /**
     * 修改商户
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionEdit()
    {
        if (!$this->manager->can('merchant/edit')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Merchant::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到商户信息。');
        }
        if ($model->load($this->post())) {
            if (!empty($this->post('Merchant')) && !empty($this->post('Merchant')['password'])) {
                $model->password = Yii::$app->security->generatePasswordHash($this->post('Merchant')['password']);
            } else {
                $model->password = $model->oldAttributes['password'];
            }
            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存商户', print_r($model->attributes, true));
                if ($model->status != Merchant::STATUS_COMPLETE) {
                    //批量下架所有商品
                    Goods::updateAll(['status' => Goods::STATUS_OFF], ['sid' => $model->shop->id]);
                    ManagerLog::info($this->manager->id, '商户状态不是正常使用状态，下架商户所有商品，商户ID：', print_r($model->shop->id, true));
                }
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/merchant/list']),
                    'txt' => '商户列表'
                ]));
            }
        }
        return $this->render('edit', [
            'model' => $model
        ]);
    }

    /**
     * 删除商户AJAX接口
     * @return array
     */
    public function actionDelete()
    {
        if (!$this->manager->can('merchant/delete')) {
            return ['message' => '没有权限。'];
        }
        $id = $this->get('id');
        $model = Merchant::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到商户信息。'];
        }
        $model->status = Merchant::STATUS_DELETED;
        $model->save(false);

        ManagerLog::info($this->manager->id, '删除商户', print_r($model->attributes, true));
        //批量下架所有商品
        $sid = $model->shop->id;
        Goods::updateAll(['status' => Goods::STATUS_OFF], "sid = $sid");
        ManagerLog::info($this->manager->id, '删除商户下架商户所有商品，商户ID：', print_r($model->shop->id, true));
        return ['result' => 'success'];
    }

    /**
     * 设置商户状态AJAX接口
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionStatus()
    {
        if (!$this->manager->can('merchant/status')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        /* @var $model Merchant */
        $model = Merchant::find()->where(['id' => $id])->andWhere(['<>', 'status', Merchant::STATUS_DELETED])->one();
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到商户数据。');
        }
        if (!in_array($model->status, [Merchant::STATUS_COMPLETE, Merchant::STATUS_STOPED])) {
            throw new BadRequestHttpException('商户状态异常。');
        }
        $new_status = [
            Merchant::STATUS_COMPLETE => Merchant::STATUS_STOPED,
            Merchant::STATUS_STOPED => Merchant::STATUS_COMPLETE
        ][$model->status];
        ManagerLog::info($this->manager->id, '设置商户状态', $model->id . ':' . $model->status . '->' . $new_status);
        $model->status = $new_status;
        $model->save();
        if ($model->status == Merchant::STATUS_STOPED) {
            $sid = $model->shop->id;
            Goods::updateAll(['status' => Goods::STATUS_OFF], "sid = $sid");
            ManagerLog::info($this->manager->id, '设置商户状态停用下架此商户所有商品，店铺ID：', $model->id);
        }
        return [
            'result' => 'success'
        ];
    }

    /**
     * 代理设置
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionAgentConfig()
    {
        if (!$this->manager->can('merchant/agent-config')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = AgentFee::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $model_list = $query->orderBy('area ASC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('agent_config', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加/编辑 代理设置
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionEditAgentConfig()
    {
        if (!$this->manager->can('merchant/agent-config')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = AgentFee::findOne($id);
        } else {
            $model = new AgentFee();
        }
        if ($model->load($this->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->addFlash('success', '代理设置已保存。');
            ManagerLog::info($this->manager->id, '保存代理设置', print_r($model->attributes, true));
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/merchant/agent-config']),
                'txt' => '代理设置'
            ]));
        }
        return $this->render('agent_config_edit', [
            'model' => $model
        ]);
    }

    /**
     * 删除代理地区AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteAgentConfig()
    {
        if (!$this->manager->can('merchant/agent-config')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = AgentFee::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到代理地区信息。'];
        }
        try {
            $model->delete();
        } catch (\Throwable $t) {
        }
        ManagerLog::info($this->manager->id, '删除代理地区', print_r($model->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 商户保证金
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionConfig()
    {
        if (!$this->manager->can('merchant/config')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = MerchantFee::find();
        $query->andFilterWhere(['cid' => $this->get('search_cid')]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $model_list = $query->orderBy('cid ASC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('config', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加/编辑 商户保证金
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionEditConfig()
    {
        if (!$this->manager->can('merchant/config')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = MerchantFee::findOne($id);
        } else {
            $model = new MerchantFee();
        }
        if ($model->load($this->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', '商户保证金已保存。');
            ManagerLog::info($this->manager->id, '保存商户保证金已保存', print_r($model->attributes, true));
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/merchant/config']),
                'txt' => '商户保证金'
            ]));
        }
        return $this->render('config_edit', [
            'model' => $model
        ]);
    }

    /**
     * 删除商户保证金AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteConfig()
    {
        if (!$this->manager->can('merchant/config')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = MerchantFee::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到商户保证金信息。'];
        }
        try {
            $model->delete();
        } catch (\Throwable $e) {
        }
        ManagerLog::info($this->manager->id, '删除商户保证金', print_r($model->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 商户入驻申请列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionJoin()
    {
        if (!$this->manager->can('merchant/join')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Merchant::find();
        $query->andWhere(['{{%merchant}}.status' => [
            Merchant::STATUS_WAIT_DATA1,
            Merchant::STATUS_DATA1_OK,
            Merchant::STATUS_WAIT_DATA2,
            Merchant::STATUS_DATA2_OK,
        ]]);
        $query->joinWith(['shop']);
        $query->andFilterWhere(['like', 'username', $this->get('search_username')]);
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andFilterWhere(['{{%shop}}.area' => $this->get('search_area', '')]);
        $query->andFilterWhere(['{{%merchant}}.status' => $this->get('search_status')]);

        $merchant_list = $query->all();
        return $this->render('join', [
            'merchant_list' => $merchant_list,
        ]);
    }

    /**
     * 商户入驻申请数据审核通过AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionAcceptData1()
    {
        if (!$this->manager->can('merchant/join')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $merchant = Merchant::findOne($id);
        if (empty($merchant)) {
            throw new NotFoundHttpException('没有找到商户信息。');
        }
        if ($merchant->status != Merchant::STATUS_WAIT_DATA1) {
            throw new ServerErrorHttpException('商户状态不允许数据审核。');
        }
        $merchant->status = Merchant::STATUS_DATA1_OK;
        $merchant->save();
        // 给发出申请的用户发送短信和站内信
        $from_user = User::findOne(MerchantConfig::getConfig($merchant->id, 'register_from_uid'));// 申请入驻的用户
        if (!empty($from_user)) {
            $msg_content = '恭喜您，平台开店申请初审已通过！请登录个人账户查看，根据提示在电脑端进一步的完善商品及企业资质，感谢您的支持！';
            Sms::send(Sms::U_TYPE_USER, $from_user->id, Sms::TYPE_MERCHANT_JOIN, $from_user->mobile, $msg_content);
            $user_message = new UserMessage();
            $user_message->uid = $from_user->id;
            $user_message->title = '商户入驻申请审核结果';
            $user_message->content = $msg_content;
            $user_message->status = UserMessage::STATUS_NEW;
            $user_message->create_time = time();
            $user_message->save();
        }
        ManagerLog::info($this->manager->id, '审核商户数据通过', print_r($merchant->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 商户入驻申请数据审核拒绝AJAX接口
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionRejectData1()
    {
        if (!$this->manager->can('merchant/join')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $info = $this->get('info');
        $merchant = Merchant::findOne($id);
        if (empty($merchant)) {
            throw new NotFoundHttpException('没有找到商户信息。');
        }
        if ($merchant->status != Merchant::STATUS_WAIT_DATA1) {
            throw new ServerErrorHttpException('商户状态不允许数据审核。');
        }
        if (empty($info)) {
            throw new BadRequestHttpException('必须填写被拒绝的原因。');
        }
        $merchant->status = Merchant::STATUS_REQUIRE;
        $merchant->save();
        ManagerLog::info($this->manager->id, '审核商户数据拒绝：[' . $info . ']' . print_r($merchant->attributes, true));

        // 给发出申请的用户发送短信和站内信
        $from_user = User::findOne(MerchantConfig::getConfig($merchant->id, 'register_from_uid'));// 申请入驻的用户
        if (!empty($from_user)) {
            $msg_content = '您填写的商户入驻申请审核没有通过，原因[' . $info . ']，请重新填写。';
            Sms::send(Sms::U_TYPE_USER, $from_user->id, Sms::TYPE_MERCHANT_JOIN, $from_user->user_phone, $msg_content);
            $user_message = new UserMessage();
            $user_message->uid = $from_user->id;
            $user_message->title = '商户入驻申请审核结果';
            $user_message->content = $msg_content;
            $user_message->status = UserMessage::STATUS_NEW;
            $user_message->create_time = time();
            $user_message->save();
        }
        return ['result' => 'success'];
    }

    /**
     * 商户入驻申请数据审核通过AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionAcceptData2()
    {
        if (!$this->manager->can('merchant/join')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $merchant_fee = $this->get('merchant_fee');
        if ($merchant_fee < 0 || !is_numeric($merchant_fee)) {
            return ['message' => '请核对保证金金额。'];
        }
        $merchant = Merchant::findOne($id);
        if (empty($merchant)) {
            throw new NotFoundHttpException('没有找到商户信息。');
        }
        if ($merchant->status != Merchant::STATUS_WAIT_DATA2) {
            throw new ServerErrorHttpException('商户状态不允许数据审核。');
        }
        $trans = Yii::$app->db->beginTransaction();
        try{
            //审核通过 并且确定 商户根据经营类目该交多少保证金
            $finance_log = new FinanceLog();
            $finance_log->money = $merchant_fee;
            $finance_log->type = FinanceLog::TYPE_MERCHANT_EARNEST_MONEY;
            //保证金设成0  则直接保存余额支付成功
            if (bccomp($finance_log->money, $merchant_fee, 2) == 0) {
                $finance_log->pay_method = FinanceLog::PAY_METHOD_YE;
                $finance_log->status = FinanceLog::STATUS_SUCCESS;
            } else {
                $finance_log->pay_method = FinanceLog::PAY_METHOD_YHK;
                $finance_log->status = FinanceLog::STATUS_WAIT;
            }
            $finance_log->create_time = time();
            $r = $finance_log->save();
            if (!$r) {
                throw new Exception('无法保存支付保证金财务记录。');
            }
            ManagerLog::info($this->manager->id, '审核商户数据2通过 设置应交保证金', $merchant_fee);
            $shop = $merchant->shop;
            $shop->earnest_money_fid = $finance_log->id;
            $r = $shop->save();
            if (!$r) {
                throw new Exception('无法保存支付保证金财务记录：');
            }
            ManagerLog::info($this->manager->id, '审核商户数据2通过 设置应交保证金 缴费记录ID', $finance_log->id);
            //保证金设成0  则直接通过审核 正常使用
            if (bccomp($finance_log->money, $merchant_fee, 2) == 0) {
                $merchant->status = Merchant::STATUS_COMPLETE;
            } else {
                $merchant->status = Merchant::STATUS_DATA2_OK;
            }
            $r = $merchant->save();
            if (!$r) {
                throw new Exception('无法保存店铺状态。');
            }
            // 给发出申请的用户发送短信和站内信
            $from_user = User::findOne(MerchantConfig::getConfig($merchant->id, 'register_from_uid'));// 申请入驻的用户
            if (!empty($from_user)) {
                $msg_content = '尊敬的商户：恭喜您店铺申请成功，请尽快登录商家中心，根据提示完成后续操作环节，经营店铺，感谢您的支持，顺祝商祺！';
                Sms::send(Sms::U_TYPE_USER, $from_user->id, Sms::TYPE_MERCHANT_JOIN, $from_user->mobile, $msg_content);
                $user_message = new UserMessage();
                $user_message->uid = $from_user->id;
                $user_message->title = '商户入驻申请审核结果';
                $user_message->content = $msg_content;
                $user_message->status = UserMessage::STATUS_NEW;
                $user_message->create_time = time();
                $user_message->save();
            }
            ManagerLog::info($this->manager->id, '审核商户数据2通过', print_r($merchant->attributes, true));
            $shop = Shop::findOne($merchant->shop->id);
            $shop->status = Shop::STATUS_ACCEPT;
            $r = $shop->save();
            if (!$r) {
                throw new Exception('无法保存店铺状态。');
            }
            ManagerLog::info($this->manager->id, '审核商户数据2通过', print_r($shop->attributes, true));
            $trans->commit();
            return ['result' => 'success'];
        } catch(\Exception $e){
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return ['message' => '设置失败' . $e->getMessage()];
        }
    }

    /**
     * 设置商户保证金页面
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @return string
     */
    public function actionGetMerchantFee()
    {
        if (!$this->manager->can('merchant/join')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $merchant_id = $this->get('id');
        $merchant = Merchant::findOne($merchant_id);
        if (empty($merchant)) {
            throw new NotFoundHttpException('没有找到商户信息。');
        }
        if ($merchant->status != Merchant::STATUS_WAIT_DATA2) {
            throw new ServerErrorHttpException('商户状态不允许数据审核。');
        }
        $merchant_fee = 0;
        $shop_config = ShopConfig::getConfig($merchant->shop->id, 'cid_list');
        $cid_list = json_decode($shop_config, true);
        $goods_cate = '';
        $merchant_fee_list = '';
        if (!empty($cid_list) && empty($merchant->shop->earnest_money_fid)){
            foreach (GoodsCategory::find()->where(['in', 'id', $cid_list])->each() as $cate) {
                $goods_cate .= $cate->name . chr(10);
            }
            $merchant_fee = MerchantFee::find()->where(['in', 'cid', $cid_list])->max('earnest_money');
            $merchant_fee_list = MerchantFee::find()->where(['in', 'cid', $cid_list])->all();
        }
        return $this->render('merchant_fee_edit', [
            'merchant_id' => $merchant_id,
            'merchant_fee' => $merchant_fee,
            'goods_cate' => $goods_cate,
            'merchant_fee_list' => $merchant_fee_list,
        ]);
    }

    /**
     * 商户入驻申请数据审核拒绝AJAX接口
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionRejectData2()
    {
        if (!$this->manager->can('merchant/join')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $info = $this->get('info');
        $merchant = Merchant::findOne($id);
        if (empty($merchant)) {
            throw new NotFoundHttpException('没有找到商户信息。');
        }
        if ($merchant->status != Merchant::STATUS_WAIT_DATA2) {
            throw new ServerErrorHttpException('商户状态不允许数据审核。');
        }
        if (empty($info)) {
            throw new BadRequestHttpException('必须填写被拒绝的原因。');
        }
        $merchant->status = Merchant::STATUS_DATA1_OK;
        $merchant->save();
        ManagerLog::info($this->manager->id, '审核商户数据拒绝：[' . $info . ']' . print_r($merchant->attributes, true));

        // 给发出申请的用户发送短信和站内信
        $from_user = User::findOne(MerchantConfig::getConfig($merchant->id, 'register_from_uid'));// 申请入驻的用户
        if (!empty($from_user)) {
            $msg_content = '您填写的商户入驻申请审核没有通过，原因[' . $info . ']，请重新填写。';
            Sms::send(Sms::U_TYPE_USER, $from_user->id, Sms::TYPE_MERCHANT_JOIN, $from_user->user_phone, $msg_content);
            $user_message = new UserMessage();
            $user_message->uid = $from_user->id;
            $user_message->title = '商户入驻申请审核结果';
            $user_message->content = $msg_content;
            $user_message->status = UserMessage::STATUS_NEW;
            $user_message->create_time = time();
            $user_message->save();
        }
        return ['result' => 'success'];
    }

    /**
     * 代理商入驻申请列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionAgentJoin()
    {
        if (!$this->manager->can('merchant/agent-join')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Agent::find();
        $query->andWhere(['status' => [Agent::STATUS_WAIT_CONTACT, Agent::STATUS_WAIT_INITIAL_FEE, Agent::STATUS_WAIT_FINANCE]]);
        $agent_list = $query->all();
        return $this->render('agent_join', [
            'agent_list' => $agent_list,
        ]);
    }

    /**
     * 代理商入驻客服审核通过AJAX接口
     * @return array
     */
    public function actionAgentContactFinish()
    {
        if (!$this->manager->can('merchant/agent-join-contact')) {
            return ['message' => '没有权限。'];
        }
        $id = $this->get('id');
        $agent = Agent::findOne($id);
        if (empty($agent)) {
            return ['message' => '没有找到代理商信息。'];
        }
        if ($agent->status != Agent::STATUS_WAIT_CONTACT) {
            return ['message' => '代理商状态异常。'];
        }
        $agent->status = Agent::STATUS_WAIT_INITIAL_FEE;
        $agent->save();
        ManagerLog::info($this->manager->id, '代理商入驻通过客服审核', print_r($agent->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 代理商入驻通过加盟费审核AJAX接口
     * @return array
     */
    public function actionCheckAgentInitialFee()
    {
        if (!$this->manager->can('merchant/check-agent-initial-fee')) {
            return ['message' => '没有权限。'];
        }
        $id = $this->get('id');
        $agent = Agent::findOne($id);
        if (empty($agent)) {
            return ['message' => '没有找到代理商信息。'];
        }
        if ($agent->status != Agent::STATUS_WAIT_INITIAL_FEE) {
            return ['message' => '代理商状态异常。'];
        }
        $agent->status = Agent::STATUS_WAIT_EARNEST_MONEY;
        $agent->save();
        ManagerLog::info($this->manager->id, '代理商入驻通过加盟费审核', print_r($agent->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 代理商入驻申请财务审核通过AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionAcceptAgentFinance()
    {
        if (!$this->manager->can('merchant/agent-join-finance')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $agent = Agent::findOne($id);
        if (empty($agent)) {
            throw new NotFoundHttpException('没有找到代理商信息。');
        }
        if ($agent->status != Agent::STATUS_WAIT_FINANCE) {
            throw new ServerErrorHttpException('代理商状态不允许财务审核。');
        }
        $agent->status = Agent::STATUS_ACTIVE;
        $agent->save();
        ManagerLog::info($this->manager->id, '审核代理商入驻财务通过', print_r($agent->attributes, true));
        // 给发出申请的用户发送短信和站内信
        $from_user = User::findOne(AgentConfig::getConfig($agent->id, 'register_from_uid'));// 申请入驻的用户
        if (!empty($from_user)) {
            $msg_content = '您填写的代理商入驻申请已通过审核，现在可以登录代理后台使用了。';
            Sms::send(Sms::U_TYPE_USER, $from_user->id, Sms::TYPE_AGENT_JOIN, $from_user->user_phone, $msg_content);
            $user_message = new UserMessage();
            $user_message->uid = $from_user->id;
            $user_message->title = '代理商入驻申请财务审核结果';
            $user_message->content = $msg_content;
            $user_message->status = UserMessage::STATUS_NEW;
            $user_message->create_time = time();
            $user_message->save();
        }
        return ['result' => 'success'];
    }

    /**
     * 添加修改品牌
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditShopBrand()
    {
        if (!$this->manager->can('merchant/shop-brand')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = ShopBrand::find()->joinWith(['brand', 'shop'])->where(['{{%shop_brand}}.id' => $id])->one();
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到品牌信息。');
            }
            $goods_brand = GoodsBrand::findOne($model['bid']);
        } else {
            $model = new ShopBrand();
            $goods_brand = new GoodsBrand();
        }
        if ($model->load($this->post()) && $model->save() && $goods_brand->load($this->post()) && $goods_brand->save()) {
            ManagerLog::info($this->manager->id, '保存商户品牌', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/merchant/shop-brand']),
                'txt' => '店铺品牌列表'
            ]));
        }
        return $this->render('shop_brand_edit', [
            'model' => $model
        ]);
    }

    /**
     * 商户店铺品牌列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionShopBrand()
    {
        if (!$this->manager->can('merchant/shop-brand')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ShopBrand::find();
        $query->joinWith(['brand', 'shop']);
        $query->andFilterWhere(['like', '{{%goods_brand}}.name', $this->get('search_brand_name')]);
        $query->andFilterWhere(['like', '{{%shop}}.name', $this->get('search_shop_name')]);
        $query->andFilterWhere(['type' => $this->get('search_brand_type')]);
        $query->andFilterWhere(['{{%shop_brand}}.status' => $this->get('search_brand_status')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('{{%shop_brand}}.status ASC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('shop_brand', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 设置店铺品牌审核状态AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionShopBrandStatus()
    {
        if (!$this->manager->can('merchant/shop-brand')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $status = $this->get('status');
        $remark = $this->get('remark');
        /** @var ShopBrand $shop_brand */
        $shop_brand = ShopBrand::findOne($id);
        if (empty($shop_brand)) {
            throw new NotFoundHttpException('没有找到违规商户品牌信息。');
        }
        if ($status == 'accept') {
            $shop_brand->status = ShopBrand::STATUS_VALID;
            ManagerLog::info($this->manager->id, '审核商户品牌', print_r($shop_brand->attributes, true));
            if (!$shop_brand->save()) {
                return ['message' => '审核商户品牌通过失败。'];
            }
            return ['result' => 'success'];
        }
        if ($status == 'refuse') {
            $shop_brand->status = ShopBrand::STATUS_REJECTED;
            /** @var  $merchant_message MerchantMessage */
            $merchant_message = new MerchantMessage();
            $merchant_message->mid = $shop_brand->shop->mid;
            $merchant_message->title = '商户品牌审核';
            $merchant_message->content = '品牌ID:' . $shop_brand->bid . ' 审核被拒绝 拒绝理由：' . $remark;
            $merchant_message->time = time();
            $merchant_message->status = SystemMessage::STATUS_UNREAD;
            if (!$merchant_message->save()) {
                return ['message' => '商户消息发送失败。'];
            }
            ManagerLog::info($this->manager->id, '审核商户品牌', print_r($shop_brand->attributes, true));
            if (!$shop_brand->save()) {
                return ['message' => '商户品牌审核拒绝失败。'];
            } else {
                return ['result' => 'success'];
            }
        }
        return ['message' => '参数错误。'];
    }

    /**
     * 店铺列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionShopList()
    {
        if (!$this->manager->can('merchant/shop-list')) {
            throw new ForbiddenHttpException('没有权限。');
        }

        $query = Shop::find();
        $query->joinWith(['merchant']);
        $query->andWhere(['{{%merchant}}.status' => [Merchant::STATUS_COMPLETE, Merchant::STATUS_STOPED]]);
        $query->andFilterWhere(['{{%shop}}.id' => $this->get('search_id')]);
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andFilterWhere(['like', 'username', $this->get('search_username')]);
        $query->andFilterWhere(['like', 'mobile', $this->get('search_mobile')]);

        if ($this->get('search_area')) {
            $shop_list = Shop::find()->andWhere(['area' => $this->get('search_area')])->all();
            if (empty($shop_list)){
                $query->andWhere('1 <> 1');
            } else {
                $query->andWhere(['{{%merchant}}.id' => ArrayHelper::getColumn($shop_list, 'mid')]);
            }
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('shop_list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 店铺详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionShopView()
    {
        if (!$this->manager->can('merchant/shop-list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $shop = Shop::findOne($id);
        if (empty($shop)) {
            throw new NotFoundHttpException('没有找到店铺信息。');
        }
        return $this->render('shop_view', [
            'shop' => $shop,
        ]);
    }
}
