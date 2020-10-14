<?php

namespace app\modules\admin\controllers;

use app\models\Feedback;
use app\models\FinanceLog;
use app\models\Goods;
use app\models\GoodsCouponGiftUser;
use app\models\GoodsCouponRule;
use app\models\ManagerLog;
use app\models\MasterUser;
use app\models\MasterUserAccountLog;
use app\models\Order;
use app\models\System;
use app\models\User;
use app\models\UserAccount;
use app\models\UserAccountLog;
use app\models\UserCard;
use app\models\UserCardLevel;
use app\models\UserCommission;
use app\models\UserGrowth;
use app\models\UserLevel;
use app\models\UserPackageCoupon;
use app\models\UserRecharge;
use app\models\UserScoreLog;
use app\models\UserSubsidy;
use app\models\UserWithdraw;
use app\models\Util;
use kucha\ueditor\UEditorAction;
use PHPExcel;
use PHPExcel_Cell_DataType;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use function Sodium\compare;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * 用户管理
 * Class UserController
 * @package app\modules\admin\controllers
 */
class UserController extends BaseController
{

    /**
     * 文件上传AJAX接口
     * @see UploadControllerTrait
     */
    use UploadControllerTrait;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return array_merge(parent::actions(), [
            'ue-upload' => [
                'class' => UEditorAction::className(),
                'config' => [
                    'imagePathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}', //上传保存路径
                    'imageRoot' => Yii::getAlias('@webroot'),
                    'scrawlPathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                    'snapscreenPathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                    'catcherPathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                    'videoPathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                    'filePathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                ],
            ],
        ]);
    }

    /**
     * 用户列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = User::find();
        $query->andWhere(['<>', 'status', User::STATUS_DELETE]);
        $query->andFilterWhere(['like', 'mobile', $this->get('search_mobile')]);
        $query->andFilterWhere(['like', 'real_name', $this->get('search_real_name')]);
        $query->andFilterWhere(['like', 'nickname', $this->get('nickname')]);
        $p_mobile =$this->get('search_p_mobile');
        if ($p_mobile) {
            /** @var User $pid */
            $pid = User::find()->where(['mobile' => $p_mobile])->one();
            $query->andWhere(['pid' => $pid->id]);
        }
        $team_p_mobile =$this->get('search_team_p_mobile');
        if ($team_p_mobile) {
            /** @var User $pid1 */
            $pid1 = User::find()->where(['mobile' => $team_p_mobile])->one();
            $query->andWhere(['team_pid' => $pid1->id]);
        }
        $query->andFilterWhere(['pid' => $this->get('search_p_id')]);
        $query->andFilterWhere(['team_pid' => $this->get('search_team_p_id')]);
        $query->andFilterWhere(['status' => $this->get('search_status')]);
        if (!empty($this->get('search_handle_start_date'))) {
            $query->andFilterWhere(['>=', 'handle_time', strtotime($this->get('search_handle_start_date'))]);
            $query->andWhere(['>', 'handle_time', 0]);
            $query->andWhere(['status' => User::STATUS_OK]);
        }
        if (!empty($this->get('search_handle_end_date'))) {
            $query->andFilterWhere(['<', 'handle_time', strtotime($this->get('search_handle_end_date')) + 86400]);
        }
        if (!empty($this->get('search_create_start_date'))) {
            $query->andFilterWhere(['>=', 'create_time', strtotime($this->get('search_create_start_date'))]);
            $query->andWhere(['>', 'create_time', 0]);
        }
        if (!empty($this->get('search_create_end_date'))) {
            $query->andFilterWhere(['<', 'create_time', strtotime($this->get('search_create_end_date')) + 86400]);
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 添加/编辑 用户资料
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionEdit()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $id = $this->get('id');
        if ($id > 0) {
            $model = User::find()->where(['id' => $id])->andWhere(['<>', 'status', User::STATUS_DELETE])->one();
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到用户信息。');
            }
        } else {
            $model = new User();
            $model->create_time = time();
            $model->status = 1;
        }
        if ($model->load($this->post())) {
            $model->create_time = strtotime($model->create_time);
            if(!empty($this->post('User')['password'])){
                $model->password = $this->post('User')['password'];
                $model->auth_key = Util::randomStr(32, 7);
                $model->password = Yii::$app->security->generatePasswordHash($model->password);
            }
            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存用户资料：' . print_r($model->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/user/list']),
                    'txt' => '用户列表'
                ]));
            }
        }
        return $this->render('edit',[
            'model' => $model,
        ]);
    }

    /**
     * 添加 编辑 销售员结算记录单
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionAccountLogEdit()
    {

        $id = $this->get('id');
        $uid = $this->get('uid');
        if ($id > 0) {
            $model = UserAccountLog::find()->where(['id' => $id])->andFilterWhere(['=', 'uid', $uid])->one();
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到店主结算信息。');
            }
        } else {
            $model = new UserAccountLog();
            $model->create_time = time();
            $model->time = time();
            $model->uid = $uid;
            $model->status = 2;
            $model->bean_status = 2;
            $model->team_sale_status = 1;
        }
        if ($model->load($this->post())) {
            $model->time = strtotime($model->time);
            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存店主结算记录：' . print_r($model->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/user/account-list']),
                    'txt' => '用户列表'
                ]));
            }
        }
        return $this->render('account_log_edit',[
            'model' => $model,
        ]);
    }

    /**
     * 删除销售员结算单AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteAccountLog()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserAccountLog::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到销售员结算单信息。'];
        }
        $model->status = UserAccountLog::STATUS_DEL;
        ManagerLog::info($this->manager->id, '删除销售员结算单', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

    /**
     * 设置结算记录状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionAccountLogStatus()
    {
        if (!$this->manager->can('ad/edit')) {
//            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserAccountLog::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到结算记录信息。');
        }
        $model->status = [UserAccountLog::STATUS_ON => UserAccountLog::STATUS_WAIT, UserAccountLog::STATUS_WAIT => UserAccountLog::STATUS_ON][$model->status];
        if ($model->save()) {
            return [
                'result' => 'success'
            ];
        }
        return [
            'result' => 'failure',
            'message' => '无法保存广告信息。',
            'errors' => $model->errors
        ];
    }

    /**
     * 设置结算记录私发状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionAccountLogTeamStatus()
    {
        if (!$this->manager->can('ad/edit')) {
//            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserAccountLog::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到结算记录信息。');
        }
        $model->team_sale_status = [UserAccountLog::TEAM_STATUS_ON => UserAccountLog::TEAM_STATUS_WAIT, UserAccountLog::TEAM_STATUS_WAIT => UserAccountLog::TEAM_STATUS_ON][$model->team_sale_status];
        if ($model->save()) {
            return [
                'result' => 'success'
            ];
        }
        return [
            'result' => 'failure',
            'message' => '无法保存团队私发销售业绩提成结算状态。',
            'errors' => $model->errors
        ];
    }

    /**
     * 设置会员状态AJAX接口
     * @return array
     */
    public function actionActivate()
    {
        $id = $this->get('id');
        $level_id = $this->get('level_id');
        if (!in_array($level_id, [1, 2, 3])) {
            return ['message' => '激活等级不正确。'];
        }
        $user = User::findOne($id);
        if (empty($user) || $user->status == User::STATUS_DELETE) {
            return ['message' => '没有找到会员信息。'];
        }
        if ($user->status != User::STATUS_WAIT) {
            return ['message' => '会员状态不是待激活。'];
        }
        if ($user->level_id != 1) {
            return ['message' => '会员状态不能激活。'];
        }

        $trans = Yii::$app->db->beginTransaction();
        try {
            if (empty($user->parent)) {
                return ['message' => '该会员没有上级，不能通过后台激活'];
            }
            if ($user->parent->prepare_count <= 0) {
                return ['message' => '该会员上级预购数量已激活完毕'];
            }
            $level_array = [ 1=> -1, 2 => -10, 3 => -100];
            if ($user->parent->prepare_count < (-1 * $level_array[$level_id])) {
                return ['message' => '该会员上级预购数量只剩'.$user->parent->prepare_count];
            }
            $r = User::updateAllCounters(['prepare_count' => $level_array[$level_id]], ['id' => $user->parent->id]);
            if ($r <=0) {
                return ['message' => '该会员上级预购数量更新失败'];
            }
            $user->status = User::STATUS_OK;
            $user->level_id = $level_id;
            $user->is_handle = 1;
            $user->handle_time = time();
            if (!$user->save()) {
                throw new Exception('用户激活状态保存失败。');
            }
            ManagerLog::info($this->manager->id, '后台激活'.$user->parent->real_name.'直属会员：'.$user->real_name);
            //激活增加补贴
            //$user->init_handle_subsidy_test21($user);
            $user->all_no_next_sub($user);
            //给成长值并且自动升级
            //$user->strong_level($user);
            $user->updateScore(); // 激活给400积分
            $trans->commit();
            return ['result' => 'success'];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            $msg = $e->getMessage();
            if (!empty($msg)) {
                return ['message' => $msg];
            }
        }
        return [
            'result' => 'success'
        ];
    }

    /**
     * 导入会员资料AJAX接口
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
        $file = fopen($files[0]->tempName, 'r');
        if (!$file) {
            return ['message' => '无法读取文件。'];
        }
        $bom = fread($file, 2);
        rewind($file);
        if($bom === chr(0xff).chr(0xfe)  || $bom === chr(0xfe).chr(0xff)){
            // UTF16 Byte Order Mark present
            $encoding = 'UTF-16';
        } else {
            $file_sample = fread($file, 1000) . 'e'; // read first 1000 bytes
            // . e is a workaround for mb_string bug
            rewind($file);
            $encoding = mb_detect_encoding($file_sample , 'UTF-8, UTF-7, ASCII, EUC-JP, SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
        }
        if ($encoding){
            stream_filter_append($file, 'convert.iconv.' . $encoding . '/UTF-8');
        }

//        fgetcsv($file, 0, chr(9)); // Skip version line
//        fgetcsv($file, 0, chr(9)); // Skip english title line
        fgetcsv($file, 0, chr(9)); // Skip chinese title line
        while (!feof($file)) {

            $item = fgetcsv($file, 0, chr(9));
            //$item = fgetcsv($file, 1000, ',');
            if (empty($item) || empty($item[0])) {
                continue;
            }
            $check = User::find()->where(['mobile' => $item[0]])->one();
            if (!empty($check)) {
                continue;
            }
            $trans = Yii::$app->db->beginTransaction();
            try {
                // 0 手机号 1姓名 2邀请手机号 3团队邀请手机号 4等级 5已经交了的金额
                $user = new User();
                $user->create_time = time();
                $user->mobile = $item[0];
                $user->real_name = $item[1];
                $pid = null;
                if (!empty($item[2])) {
                    $p_user = User::find()->select('id')->where(['mobile' => $item[2]])->one();
                    if(!empty($p_user) ) $pid = $p_user['id'];
                }
                $user->pid = $pid;
                $team_pid = $pid;
                if (!empty($item[3])) {
                    $p_user = User::find()->select('id')->where(['mobile' => $item[3]])->one();
                    empty($p_user) ? $team_pid = $pid : $team_pid = $p_user['id'];
                }
                $user->team_pid = $team_pid;
                $user->status = 2 ;

                $user->level_id = empty($item[4]) ? 1 : $item[4];
                if ($user->level_id == 2) {
                    $user->prepare_count = 10;
                } elseif ($user->level_id == 3) {
                    $user->prepare_count = 100;
                }
                if (!$user->save()) {
                    throw new Exception('无法保存会员资料。');
                }
                if (!empty($item[5])) {
                    UserAccount::updateAllCounters(['prepare_level_money' => $item[5]], ['uid' => $user->id]);
                    $user_account_log = new UserAccountLog();
                    $user_account_log->uid = $user->id;
                    $user_account_log->prepare_level_money = $item[5];
                    $user_account_log->time = time();
                    $user_account_log->remark = '后台导入预充值金额待发展的金额';
                    if (!$user_account_log->save()) {
                        throw new Exception('无法保存资金变动。');
                    }
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
        fclose($file);
        return ['result' => 'success', 'files' => [['url' => '']]];
    }

    /**
     * 用户充值AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRecharge()
    {
        if (!$this->manager->can('user/recharge')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $money = $this->get('money', 0, 'floatval');
        $remark = $this->get('remark', '管理后台充值');
        if (empty($remark)) {
            $remark = '管理后台充值';
        }

        $user = User::findOne($id);
        if (empty($user)) {
            throw new NotFoundHttpException('没有找到用户信息。');
        }
        if (Util::comp($money, 0, 2) == 0) {
            return [
                'message' => '充值金额错误。',
            ];
        }

        if ($money < 0 ) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                $financeLog = new FinanceLog();
                $financeLog->trade_no = date('YmdHis');
                $financeLog->type = FinanceLog::TYPE_USER_RECHARGE;
                $financeLog->money = $money;
                $financeLog->pay_method = FinanceLog::PAY_METHOD_YE;
                $financeLog->status = FinanceLog::STATUS_SUCCESS;
                $financeLog->create_time = time();
                $financeLog->update_time = time();
                $financeLog->remark = $remark;
                if (!$financeLog->save()) {
                    throw new Exception('无法保存财务记录。');
                }
                $userRecharge = new UserRecharge();
                $userRecharge->uid = $user->id;
                $userRecharge->fid = $financeLog->id;
                $userRecharge->money = $money;
                $userRecharge->create_time = time();
                $userRecharge->status = UserRecharge::STATUS_SUCCESS;
                $userRecharge->remark = $remark;
                if (!$userRecharge->save()) {
                    throw new Exception('无法保存充值记录。');
                }
                $r = UserAccount::updateAllCounters(['money' => $userRecharge->money], ['uid' => $user->id]);
                if ($r <= 0) {
                    throw new Exception('无法更新账户。');
                }
                $ual = new UserAccountLog();
                $ual->uid = $user->id;
                $ual->money = $userRecharge->money;
                $ual->time = time();
                $ual->remark = $remark;
                if (!$ual->save()) {
                    throw new Exception('无法保存账户记录。');
                }
                $trans->commit();
                return [
                    'result' => 'success',
                ];
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                return [
                    'message' => $e->getMessage(),
                ];
            }
        } else {
            $trans = Yii::$app->db->beginTransaction();
            try {
                $financeLog = new FinanceLog();
                $financeLog->trade_no = date('YmdHis');
                $financeLog->type = FinanceLog::TYPE_USER_RECHARGE;
                $financeLog->money = $money;
                $financeLog->pay_method = FinanceLog::PAY_METHOD_YE;
                $financeLog->status = FinanceLog::STATUS_SUCCESS;
                $financeLog->create_time = time();
                $financeLog->update_time = time();
                $financeLog->remark = $remark;
                if (!$financeLog->save()) {
                    throw new Exception('无法保存财务记录。');
                }
                $userRecharge = new UserRecharge();
                $userRecharge->uid = $user->id;
                $userRecharge->fid = $financeLog->id;
                $userRecharge->money = $money;
                $userRecharge->create_time = time();
                $userRecharge->status = UserRecharge::STATUS_SUCCESS;
                $userRecharge->remark = $remark;
                if (!$userRecharge->save()) {
                    throw new Exception('无法保存充值记录。');
                }
                //查询等级金额  对比 充值金额   充值金额 大于 当前等级金额  并且等于上几级等级金额  则变换等级金额
                $user_level = UserLevel::find()->where(['status' => UserLevel::STATUS_OK])->all();
                $user_level_money = ArrayHelper::getColumn($user_level, 'money');
                if ($userRecharge->money >= $user->account->level_money && in_array($userRecharge->money, $user_level_money)) {
                    /** @var UserAccount $user_account */
                    $user_account = UserAccount::find()->andWhere(['uid' => $user->id])->one();
                    $user_account->level_money = $userRecharge->money;
                    if (!$user_account->save()) {
                        throw new Exception('无法更新用户等级金额。');
                    }
                    $r = UserAccount::updateAllCounters(['money' => $userRecharge->money], ['uid' => $user->id]);
                } elseif($user->account->level_money == 0) {
                    $r = UserAccount::updateAllCounters(['money' => $userRecharge->money, 'level_money' => $userRecharge->money], ['uid' => $user->id]);
                } else {
                    $r = UserAccount::updateAllCounters(['money' => $userRecharge->money], ['uid' => $user->id]);
                }
                if ($r <= 0) {
                    throw new Exception('无法更新账户。');
                }
                $ual = new UserAccountLog();
                $ual->uid = $user->id;
                $ual->money = $userRecharge->money;
                $ual->time = time();
                $ual->remark = $remark;
                if (!$ual->save()) {
                    throw new Exception('无法保存账户记录。');
                }
                //查找三级父级 返佣  待测试
                /** @var User $parent_1 */
                $parent_1 = $user->getParent();
                if (!empty($parent_1)) {
                    $user_level_1_id = $parent_1->getUserLevel();
                    if (!empty($user_level_1_id)) {
                        $user_level_1 = UserLevel::findOne($user_level_1_id['id']);
                        $user_level_1_money = $user_level_1->compute(1, $money);
                        $user_commission = $user_level_1->commissionLog(1,$user_level_1_money,$user_level_1,$parent_1->id,$user->id);
                        if ($user_commission['result'] == 'error') {
                            throw new Exception($user_commission['message']);
                        }
                    }
                    /** @var User $parent_2 */
                    $parent_2 = $parent_1->getParent();
                    if (!empty($parent_2)) {
                        $user_level_2_id = $parent_2->getUserLevel();
                        if (!empty($user_level_2_id)) {
                            $user_level_2 = UserLevel::findOne($user_level_2_id['id']);
                            $user_level_2_money = $user_level_2->compute(2, $money);
                            $user_commission = $user_level_2->commissionLog(2,$user_level_2_money,$user_level_2,$parent_2->id,$user->id);
                            if ($user_commission['result'] == 'error') {
                                throw new Exception($user_commission['message']);
                            }
                        }
                        /** @var User $parent_3 */
                        $parent_3 = $parent_2->getParent();
                        if (!empty($parent_3)) {
                            $user_level_3_id = $parent_3->getUserLevel();
                            if (!empty($user_level_3_id)) {
                                $user_level_3 = UserLevel::findOne($user_level_3_id['id']);
                                $user_level_3_money = $user_level_3->compute(3, $money);
                                $user_commission = $user_level_3->commissionLog(3,$user_level_3_money,$user_level_3,$parent_3->id,$user->id);
                                if ($user_commission['result'] == 'error') {
                                    throw new Exception($user_commission['message']);
                                }
                            }
                        }
                    }
                }
                $trans->commit();
                return [
                    'result' => 'success',
                ];
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
    }

    /**
     * 用户详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $user = User::findOne($id);
        if (empty($user)) {
            throw new NotFoundHttpException('没有找到用户信息。');
        }
        return $this->render('view', [
            'user' => $user,
        ]);
    }

    /**
     * 用户关系详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRelationView()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $user = User::findOne($id);
        if (empty($user)) {
            throw new NotFoundHttpException('没有找到用户信息。');
        }

        $child_list = $user->getChildAllList($id);
//        Yii::$app->cache->set('user_child_' . $id, $child_list, 2);
//        if (Yii::$app->cache->exists('user_child_' . $id)) {
//            $child_list = Yii::$app->cache->get('user_child_' . $id);
//        } else {
//            $child_list = $user->getChildAllList($id);
//            if (!empty($child_list)) Yii::$app->cache->set('user_child_' . $id, $child_list, 3600);
//        }

        $parent_list = $user->getParentAllList($id);
//        Yii::$app->cache->set('user_parent_' . $id, $parent_list, 2);
//        if (Yii::$app->cache->exists('user_parent_' . $id)) {
//            $parent_list = Yii::$app->cache->get('user_parent_' . $id);
//        } else {
//            $parent_list = $user->getParentAllList($id);
//            if (!empty($parent_list)) Yii::$app->cache->set('user_parent_' . $id, $parent_list, 3600);
//        }

        return $this->render('relation_view', [
            'user' => $user,
            'child_list' => $child_list,
            'parent_list' => $parent_list,
            'count' => count($child_list),
        ]);
    }

    /**
     * 财务记录
     * @return string
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionAccountList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $uid = $this->get('uid');
        $user = User::findOne($uid);
        if (empty($user)) {
            //throw new NotFoundHttpException('没有找到用户信息。');
        }

        $query = UserAccountLog::find();
        $query->joinWith('user');
        $query->andFilterWhere(['like', 'content', $this->get('search_content')]);
        $query->andFilterWhere(['=', '{{user_account_log}}.status', $this->get('search_status')]);
        $query->andFilterWhere(['uid' => $uid]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('account_list', [
            'list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 补贴记录
     * @return string
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionSubsidyList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $uid = $this->get('uid');
        $user = User::findOne($uid);
        if (empty($user)) {
            throw new NotFoundHttpException('没有找到用户信息。');
        }

        $query = UserSubsidy::find();
        //$query->joinWith('user');
        $query->andFilterWhere(['like', 'content', $this->get('search_content')]);
        $query->andWhere(['to_uid' => $uid]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query
            ->orderBy('create_time DESC')
//            ->orderBy('id asc')
            ->offset($pagination->offset)->limit($pagination->limit)->all();
//        echo $query->createCommand()->getRawSql();
        $user = User::findOne($uid);
        $sum = UserSubsidy::find()->where(['to_uid' => $user->id])->sum('money');
        return $this->render('subsidy_list', [
            'user' => $user,
            'sum' => $sum,
            'list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 成长值记录
     * @return string
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionGrowthList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $uid = $this->get('uid');
        $user = User::findOne($uid);
        if (empty($user)) {
            throw new NotFoundHttpException('没有找到用户信息。');
        }

        $query = UserGrowth::find();
        //$query->joinWith('user');
        $query->andFilterWhere(['like', 'content', $this->get('search_content')]);
        $query->andWhere(['to_uid' => $uid]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query
            //->orderBy('create_time DESC')
            ->orderBy('id asc')
            ->offset($pagination->offset)->limit($pagination->limit)->all();
//        echo $query->createCommand()->getRawSql();
        return $this->render('growth_list', [
            'list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 积分记录
     * @return string
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionScoreList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $uid = $this->get('uid');
        $user = User::findOne($uid);
        if (empty($user)) {
            throw new NotFoundHttpException('没有找到用户信息。');
        }

        $query = UserAccountLog::find();
        $query->select(['score', 'time', 'remark']);
        $query->union('SELECT score,create_time,remark FROM `user_score_log`  where uid='.$user->id,true);
        $query->andWhere(['uid' => $user->id]);
        $query->andWhere('score IS NOT NULL');
        $query->andWhere(['<>', 'score', 0]);
        $query_all= $query->orderBy('time DESC');
        $query2=(new Query())->from([$query_all])->orderBy(['time'=>SORT_DESC]);
        $pagination = new Pagination(['totalCount' => $query2->count(), 'validatePage' => false]);
        $list = $query2->offset($pagination->offset)->limit($pagination->limit)->all();
        $used_score=UserAccountLog::find()
            ->joinWith('order')
            ->andWhere(['{{%user_account_log}}.uid' => $user->id])
            ->andWhere(['<>','{{%order}}.status',Order::STATUS_CANCEL])
            ->sum('{{%user_account_log}}.score');
        $used_score=abs($used_score);//已使用积分

        return $this->render('score_list', [
            'list' => $list,
            'user' => $user,
            'used_score' => $used_score,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 用户反馈
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionFeedback()
    {
        if (!$this->manager->can('user/feedback')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Feedback::find();
        $query->joinWith('user');
        $query->andFilterWhere(['like', 'content', $this->get('search_content')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('feedback', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 查看用户反馈详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionViewFeedback()
    {
        if (!$this->manager->can('user/feedback')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Feedback::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到该信息。');
        }
        return $this->render('feedback_view', [
            'model' => $model
        ]);
    }

    /**
     * 删除用户反馈AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDeleteFeedback()
    {
        if (!$this->manager->can('user/feedback')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Feedback::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到该信息。');
        }
        $model->status = Feedback::STATUS_DEL;
        $model->save(false);
        ManagerLog::info($this->manager->id, '删除用户反馈', print_r($model->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 设置用户反馈状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionStatusFeedback()
    {
        if (!$this->manager->can('user/feedback')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        /* @var $model Feedback */
        $model = Feedback::find()->where(['id' => $id])->andWhere(['<>', 'status', Feedback::STATUS_DEL])->one();
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到用户反馈数据。');
        }
        $new_status = [
            Feedback::STATUS_WAIT => Feedback::STATUS_FINISH,
            Feedback::STATUS_FINISH => Feedback::STATUS_WAIT
        ][$model->status];
        ManagerLog::info($this->manager->id, '设置用户反馈状态', $model->id . ':' . $model->status . '->' . $new_status);
        $model->status = $new_status;
        $model->save();
        return [
            'result' => 'success'
        ];
    }

    /**
     * 用户等级列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionLevelList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = UserLevel::find();
        $query->andWhere(['<>', 'status', UserLevel::STATUS_DELETE]);
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('level_list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 添加/编辑 用户等级信息
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionLevelEdit()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $id = $this->get('id');
        if ($id > 0) {
            $model = UserLevel::find()->where(['id' => $id])->andWhere(['<>', 'status', UserLevel::STATUS_DELETE])->one();
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到用户等级信息。');
            }
        } else {
            $model = new UserLevel();
            $model->status = 1;
            $model->create_time = time();
        }
        if ($model->load($this->post())) {

            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存用户等级信息：' . print_r($model->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/user/level-list']),
                    'txt' => '用户等级列表'
                ]));
            }
        }
        return $this->render('level_edit',[
            'model' => $model,
        ]);
    }

    /**
     * 用户等级详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionLevelView()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserLevel::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到用户等级信息。');
        }
        return $this->render('level_view', [
            'model' => $model,
        ]);
    }

    /**
     * 删除用户等级AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteLevel()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserLevel::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到用户等级信息。'];
        }
        $model->status = UserLevel::STATUS_DELETE;
        ManagerLog::info($this->manager->id, '删除用户等级', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

    /**
     * 用户礼包兑换券列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionPackCouponList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = UserPackageCoupon::find();
        $query->joinWith('user');
//        $query->select([
//            '{{%user}}.mobile',
//            '{{%user}}.real_name',
//            '{{%goods_coupon_rule}}.name',
//            '{{%goods_coupon_rule}}.price',
//            '{{%goods}}.title',
//            '{{%goods_coupon_gift_user}}.create_time',
//            '{{%goods_coupon_gift_user}}.use_time',
//            '{{%goods_coupon_gift_user}}.status',
//        ]);
        $query->andWhere(['<>', '{{%user_package_coupon}}.status', UserPackageCoupon::STATUS_DELETE]);
        $query->andFilterWhere(['=', '{{%user_package_coupon}}.id', $this->get('search_coupon_id')]);
        $query->andFilterWhere(['like', '{{%user}}.mobile', $this->get('search_mobile')]);
        $query->andFilterWhere(['{{%user_package_coupon}}.status' => $this->get('search_status')]);
        $query->andFilterWhere(['like', '{{%user}}.real_name', $this->get('search_real_name')]);

        if (!empty($this->get('search_start_date'))) {
            $query->andFilterWhere(['>=', 'create_time', strtotime($this->get('search_start_date'))]);
            $query->andWhere(['>', 'create_time', 0]);
        }
        if (!empty($this->get('search_end_date'))) {
            $query->andFilterWhere(['<', 'create_time', strtotime($this->get('search_end_date')) + 86400]);
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('{{%user_package_coupon}}.create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('user_pack_coupon_list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }
    /**
     * 用户优惠券列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionCouponList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GoodsCouponGiftUser::find();
        $query->joinWith('user');
        $query->joinWith('goods');
        $query->joinWith('rule');
//        $query->select([
//            '{{%user}}.mobile',
//            '{{%user}}.real_name',
//            '{{%goods_coupon_rule}}.name',
//            '{{%goods_coupon_rule}}.price',
//            '{{%goods}}.title',
//            '{{%goods_coupon_gift_user}}.create_time',
//            '{{%goods_coupon_gift_user}}.use_time',
//            '{{%goods_coupon_gift_user}}.status',
//        ]);
        $query->andWhere(['<>', '{{%goods_coupon_gift_user}}.status', GoodsCouponGiftUser::STATUS_DELETE]);
        $query->andFilterWhere(['like', '{{%goods_coupon_rule}}.name', $this->get('search_coupon_name')]);
        $query->andFilterWhere(['like', '{{%user}}.mobile', $this->get('search_mobile')]);
        $query->andFilterWhere(['{{%goods_coupon_gift_user}}.status' => $this->get('search_status')]);
        $query->andFilterWhere(['like', '{{%user}}.real_name', $this->get('search_real_name')]);

        if (!empty($this->get('search_start_date'))) {
            $query->andFilterWhere(['>=', 'create_time', strtotime($this->get('search_start_date'))]);
            $query->andWhere(['>', 'create_time', 0]);
        }
        if (!empty($this->get('search_end_date'))) {
            $query->andFilterWhere(['<', 'create_time', strtotime($this->get('search_end_date')) + 86400]);
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('{{%goods_coupon_gift_user}}.id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('user_coupon_list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }




    /**
     * 用户补贴提现列表
     * @throws ForbiddenHttpException
     * @return string
     */
    public function actionWithdrawList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = UserWithdraw::find();
        $query->andWhere(['<>', 'status', UserWithdraw::STATUS_DELETE]);
        $query->andWhere(['type' => UserWithdraw::TYPE_SUBSIDY]);
        $query->andFilterWhere(['like', 'account_name', $this->get('search_account_name')]);
        $query->andFilterWhere(['like', 'account_no', $this->get('search_account_no')]);
        $query->andFilterWhere(['=', 'status', $this->get('search_status')]);
        if ($this->get('export') == 'excel') {
            $filename = "用户补贴提现列表导出_" . date('Y-m-d') . ".csv"; // 设置文件名
            header("Content-type: text/csv");
            header("Content-Disposition: attachment;filename=" . $filename);
            header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
            header('Expires: 0');
            header('Pragma: public');
            echo hex2bin('EFBBBF'); // 增加utf8 bom头
            echo "账号,账户名,银行名,开户行所在省,开户行所在地,提现金额,扣除费用,实际到账金额\r\n";
            /** @var UserWithdraw $model */
            $query_export = UserWithdraw::find()->where(['status' => UserWithdraw::STATUS_OK]);
            $query_export->andWhere(['type' => UserWithdraw::TYPE_SUBSIDY]);
            foreach ($query_export->each() as $model) {
                $money = $model->money;
                $total_money = $model->money - ($model->money * ( System::getConfig('subsidy_withdraw_point')/100));
                $bank_name = $model->bank_name;
                $bank_address = $model->bank_address;
                $account_name = $model->account_name;
                $account_no = $model->account_no;
                $province_data = explode('省', $model->bank_address);
                $province = $model->bank_address;
                if (is_array($province_data)) {
                    $province = $province_data[0]  . '省';
                }
                echo "\t"."$account_no"."\t", ",","$account_name", ",",  $bank_name, ",", $province, ",", "$bank_address", ",", $money, ",", $money - $total_money, ",", $total_money, ",".  "\r\n";
            }
            die();
        }
        if ($this->get('export') == 'search_excel') {
            $filename = "用户补贴提现列表导出_" . date('Y-m-d') . ".csv"; // 设置文件名
            header("Content-type: text/csv");
            header("Content-Disposition: attachment;filename=" . $filename);
            header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
            header('Expires: 0');
            header('Pragma: public');
            echo hex2bin('EFBBBF'); // 增加utf8 bom头
            echo "账号,账户名,银行名,开户行所在省,开户行所在地,提现金额,扣除费用,实际到账金额\r\n";
            /** @var UserWithdraw $model */
            $query_export = UserWithdraw::find();
            $query_export->andWhere(['type' => UserWithdraw::TYPE_SUBSIDY]);
            $query_export->andFilterWhere(['like', 'account_name', $this->get('search_account_name')]);
            $query_export->andFilterWhere(['like', 'account_no', $this->get('search_account_no')]);
            $query_export->andFilterWhere(['=', 'status', $this->get('search_status')]);
            foreach ($query_export->each() as $model) {
                $money = $model->money;
                $total_money = $model->money - ($model->money * ( System::getConfig('subsidy_withdraw_point')/100));
                $bank_name = $model->bank_name;
                $bank_address = $model->bank_address;
                $account_name = $model->account_name;
                $account_no = $model->account_no;
                $province_data = explode('省', $model->bank_address);
                $province = '';
                if (is_array($province_data)) {
                    $province = $province_data[0]  . '省';
                }
                echo "\t"."$account_no"."\t", ",","$account_name", ",",  $bank_name, ",", $province, ",", "$bank_address", ",", $money, ",", $money - $total_money, ",", $total_money, ",".  "\r\n";
            }
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('withdraw_list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 用户佣金提现列表
     * @throws ForbiddenHttpException
     * @return string
     */
    public function actionCommissionWithdrawList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = UserWithdraw::find();
        $query->andWhere(['<>', 'status', UserWithdraw::STATUS_DELETE]);
        $query->andWhere(['type' => UserWithdraw::TYPE_COMMISSION]);
        $query->andFilterWhere(['like', 'account_name', $this->get('search_account_name')]);
        $query->andFilterWhere(['like', 'account_no', $this->get('search_account_no')]);
        $query->andFilterWhere(['=', 'status', $this->get('search_status')]);
        if ($this->get('export') == 'excel') {
            $filename = "用户佣金提现列表导出_" . date('Y-m-d') . ".csv"; // 设置文件名
            header("Content-type: text/csv");
            header("Content-Disposition: attachment;filename=" . $filename);
            header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
            header('Expires: 0');
            header('Pragma: public');
            echo hex2bin('EFBBBF'); // 增加utf8 bom头
            echo "账号,账户名,银行名,开户行所在省,开户行所在地,提现金额,扣除费用,实际到账金额\r\n";
            /** @var UserWithdraw $model */
            $query_export = UserWithdraw::find()->where(['status' => UserWithdraw::STATUS_OK]);
            $query_export->andWhere(['type' => UserWithdraw::TYPE_COMMISSION]);
            foreach ($query_export->each() as $model) {
                $money = $model->money;
                $total_money = $model->money - ($model->money * ( System::getConfig('subsidy_withdraw_point')/100));
                $bank_name = $model->bank_name;
                $bank_address = $model->bank_address;
                $account_name = $model->account_name;
                $account_no = $model->account_no;
                $province_data = explode('省', $model->bank_address);
                $province = '';
                if (is_array($province_data)) {
                    $province = $province_data[0]  . '省';
                }
                echo "\t"."$account_no"."\t", ",","$account_name", ",",  $bank_name, ",", $province, ",", "$bank_address", ",", $money, ",", $money - $total_money, ",", $total_money, ",".  "\r\n";
            }
            die();
        }
        if ($this->get('export') == 'search_excel') {
            $filename = "用户佣金提现列表导出_" . date('Y-m-d') . ".csv"; // 设置文件名
            header("Content-type: text/csv");
            header("Content-Disposition: attachment;filename=" . $filename);
            header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
            header('Expires: 0');
            header('Pragma: public');
            echo hex2bin('EFBBBF'); // 增加utf8 bom头
            echo "账号,账户名,银行名,开户行所在省,开户行所在地,提现金额,扣除费用,实际到账金额\r\n";
            /** @var UserWithdraw $model */
            $query_export = UserWithdraw::find();
            $query_export->andWhere(['type' => UserWithdraw::TYPE_COMMISSION]);
            $query_export->andFilterWhere(['like', 'account_name', $this->get('search_account_name')]);
            $query_export->andFilterWhere(['like', 'account_no', $this->get('search_account_no')]);
            $query_export->andFilterWhere(['=', 'status', $this->get('search_status')]);
            foreach ($query_export->each() as $model) {
                $money = $model->money;
                $total_money = $model->money - ($model->money * ( System::getConfig('subsidy_withdraw_point')/100));
                $bank_name = $model->bank_name;
                $bank_address = $model->bank_address;
                $account_name = $model->account_name;
                $account_no = $model->account_no;
                $province_data = explode('省', $model->bank_address);
                $province = '';
                if (is_array($province_data)) {
                    $province = $province_data[0]  . '省';
                }
                echo "\t"."$account_no"."\t", ",","$account_name", ",",  $bank_name, ",", $province, ",", "$bank_address", ",", $money, ",", $money - $total_money, ",", $total_money, ",".  "\r\n";
            }
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('withdraw_list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }


    /**
     * 用户提现详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionWithdrawView()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserWithdraw::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到用户等级信息。');
        }
        return $this->render('withdraw_view', [
            'model' => $model,
        ]);
    }

    /**
     * 删除用户提现AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteWithdraw()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserWithdraw::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到用户提现信息。'];
        }
        $model->status = UserWithdraw::STATUS_DELETE;
        ManagerLog::info($this->manager->id, '删除用户提现', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

    /**
     * 审核通过用户提现AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionAcceptWithdraw()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserWithdraw::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到用户提现信息。'];
        }
        $model->status = UserWithdraw::STATUS_OK;
        $model->apply_time = time();
        ManagerLog::info($this->manager->id, '审核通过用户提现', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

    /**
     * 完毕用户提现AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionFinishWithdraw()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserWithdraw::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到用户提现信息。'];
        }
        $model->status = UserWithdraw::STATUS_FINISH;
        $model->finish_time = time();
        ManagerLog::info($this->manager->id, '完毕用户提现', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

    /**
     * 审核拒绝用户提现AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionRejectWithdraw()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $remark = $this->get('remark');
        $model = UserWithdraw::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到用户提现信息。'];
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $r = 0;
            if ($model->type == $model::TYPE_SUBSIDY) {
                $r = User::updateAllCounters(['subsidy_money' => $model->money], ['id' => $model->uid]);
            } elseif ($model->type == $model::TYPE_COMMISSION) {
                $r = UserAccount::updateAllCounters(['commission' => $model->money], ['uid' => $model->uid]);
            }
            if ($r <= 0) {
                throw new Exception('无法更新账户。');
            }
            $model->status = UserWithdraw::STATUS_REFUSE;
            $model->apply_time = time();
            $model->remark = $remark;
            ManagerLog::info($this->manager->id, '审核通过用户提现', $model->id);
            if (!$model->save(false)) {
                throw new Exception('无法更新账户。');
            }
            $ual = new UserAccountLog();
            $ual->uid = $model->uid;
            if ($model->type == $model::TYPE_SUBSIDY) {
                $ual->subsidy_money = $model->money;
                $remark = '补贴提现失败。';
            } elseif ($model->type == $model::TYPE_COMMISSION) {
                $ual->commission = $model->money;
                $remark = '佣金提现失败。';
            }
            $ual->time = time();
            $ual->remark = $remark;
            if (!$ual->save()) {
                throw new Exception('无法保存账户记录。');
            }
            $trans->commit();
            return [
                'result' => 'success'
            ];
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

    /**
     * 用户卡等级列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionCardLevelList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = UserCardLevel::find();
        $query->andWhere(['<>', 'status', UserCardLevel::STATUS_DELETE]);
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('card_level_list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 添加/编辑 用户卡等级
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionCardLevelEdit()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $id = $this->get('id');
        if ($id > 0) {
            $model = UserCardLevel::find()->where(['id' => $id])->andWhere(['<>', 'status', UserCardLevel::STATUS_DELETE])->one();
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到用户卡等级信息。');
            }
        } else {
            $model = new UserCardLevel();
            $model->create_time = time();
        }
        if ($model->load($this->post())) {
            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存用户卡等级资料：' . print_r($model->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/user/card-level-list']),
                    'txt' => '用户卡等级列表'
                ]));
            }
        }
        return $this->render('card_level_edit',[
            'model' => $model,
        ]);
    }

    /**
     * 用户会员卡等级详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionCardLevelView()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserCardLevel::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到用户会员卡等级信息。');
        }
        return $this->render('card_level_view', [
            'model' => $model,
        ]);
    }

    /**
     * 删除用户会员卡等级AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteCardLevel()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = UserCardLevel::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到用户等级信息。'];
        }
        $model->status = UserCardLevel::STATUS_DELETE;
        ManagerLog::info($this->manager->id, '删除用户会员卡等级', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

    /**
     * 用户绑定会员卡AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionBindCard()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $card_no = $this->get('card_no');
        $c_lid = $this->get('c_lid');
        if (empty($card_no) || empty($c_lid)) {
            return [
                'message' => '缺少必要参数。',
            ];
        }
        $user = User::findOne($id);
        if (empty($user)) {
            return [
                'message' => '没有找到用户信息。',
            ];
        }
        /** @var UserCard $card */
        foreach (UserCard::find()->where(['uid' => $user->id])->each() as $card) {
            $card->status = UserCard::STATUS_STOP;
            $card->unset_bind_time = time();
            $card->save();
        }

        /** @var UserCard $userCard */
        $userCard = new UserCard();
        $userCard->uid = $user->id;
        $userCard->card_no = $card_no;
        $userCard->c_lid = $c_lid;
        $userCard->bind_time = time();
        $userCard->status = UserCard::STATUS_OK;
        $userCard->create_time = time();
        if (!$userCard->save()) {
            return [
                'message' => '无法保存充值记录。',
            ];
        }
        return [
            'result' => 'success',
        ];
    }

    /**
     * 删除用户AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionDelete()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = User::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到用户信息。');
        }
        $recharge = UserRecharge::find()->where(['uid' => $id])->exists();
        $order = Order::find()->where(['uid' => $id])->exists();
        if ($recharge || $order) {
            return [
                'message' => '有关联数据不能删除'
            ];
        }
        $model->status = User::STATUS_DELETE;
        $model->save();
        ManagerLog::info($this->manager->id, '删除用户', print_r($model->attributes, true));
        return [
            'result' => 'success'
        ];
    }

    public function actionRunSubsidy()
    {
        $init_user_list = User::find()->where(['<', 'id', '2790'])->all();
        /** @var User $user */
        foreach ($init_user_list as $user) {
            //$user->init_subsidy($user);  //第一批导入数据  补贴计算
            //$user->init_subsidy_test($user);  //第一批导入数据  补贴计算
            echo $user->id . 'begin:' . chr(10);
            $user->init_subsidy_test2($user);  //第一批导入数据  补贴计算
        }

        $init_user_list1 = User::find()->where(['between', 'id', 3660, 3720])->all();
        /** @var User $user */
        foreach ($init_user_list1 as $user) {
            //$user->init_subsidy2($user);  //第一批导入数据  补贴计算
            //$user->init_subsidy2_test($user);  //第一批导入数据  补贴计算
            echo $user->id . 'begin:' . chr(10);
            $user->init_subsidy_test3($user);  //第一批导入数据  补贴计算
        }
//        /** @var User $user */
//        foreach (User::find()->where(['status' => User::STATUS_OK])->each() as $user) {
//            echo $user->id . 'begin:' . chr(10);
//            //$r = $user->all_sub($user);
//            $r = $user->all_no_next_sub($user);  //前台正常流程  激活返 补贴
//            var_dump($r);
//            echo $user->id. 'end' . chr(10);
//        }
    }

    public function actionHandleRunSubsidy()
    {

        //付款购买的 uid 数组
        $no_buy_list = Order::find()->where(['>=', 'status', 2])->asArray()->all();
        //var_dump($no_buy_list);
        $no_buy_uid_list = array_column($no_buy_list, 'uid');
        $no_list = [
            3660,            3661,            3662,            3663,            3664,            3665,            3666,            3667,            3668,
            3669,            3670,            3671,            3672,            3673,            3674,            3675,            3676,
            3677,            3678,            3679,            3680,            3681,            3682,            3683,            3684,
            3685,            3686,            3687,            3688,            3689,            3690,            3691,            3692,
            3693,            3694,            3695,            3696,            3697,            3698,            3699,            3700,
            3701,            3702,            3703,            3704,            3705,            3706,            3707,            3708,
            3709,            3710,            3711,            3712,            3713,            3714,            3715,            3716,
            3717,            3718,            3719        ];
        $no_buy_uid_list = array_merge($no_buy_uid_list, $no_list);
        //没付款  后台手动激活的
        /** @var User $user */
        foreach (User::find()->where(['status' => 1])
                     ->andWhere(['>', 'id', '2790'])
                     ->andWhere(['NOT IN', 'id', $no_buy_uid_list])->all() as $user) {
            echo $user->id . 'begin:' . chr(10);
//            var_dump($user->id);
//            echo '<br>';
            //$r = $user->init_handle_subsidy($user);
            //$r = $user->init_handle_subsidy_test($user);
            $r = $user->init_handle_subsidy_test2($user);
            var_dump($r);
            echo $user->id. 'end' . chr(10);
        }
    }

    public function actionBuyRunSubsidy()
    {
        $buy_list = Order::find()->where(['>=', 'status', 2])->asArray()->all();
        $buy_list = array_column($buy_list, 'uid');
        /** @var User $user */
        foreach (User::find()->where(['status' => User::STATUS_OK])->andWhere(['IN', 'id', $buy_list])->all() as $user) {
            echo $user->id . 'begin:' . chr(10);
            //$r = $user->all_sub($user);
            $r = $user->all_no_next_sub($user);  //前台正常流程  激活返 补贴
            var_dump($r);
            echo $user->id. 'end' . chr(10);
        }
    }

    public function actionRunGrowth()
    {
        $init_user_list = User::find()->where(['<', 'id', '2790'])->all();
        /** @var User $user */
        foreach ($init_user_list as $user) {
            //$user->init_subsidy($user);  //第一批导入数据  成长值
            //$user->init_subsidy_test($user);  //第一批导入数据  成长值计算
            echo $user->id . 'begin:' . chr(10);
            $user->init_growth_test2($user);  //第一批导入数据  成长值计算
        }

        $init_user_list1 = User::find()->where(['between', 'id', 3660, 3720])->all();
        /** @var User $user */
        foreach ($init_user_list1 as $user) {
            //$user->init_subsidy2($user);  //第一批导入数据  成长值计算
            //$user->init_subsidy2_test($user);  //第一批导入数据  成长值计算
            echo $user->id . 'begin:' . chr(10);
            $user->init_growth_test3($user);  //第二批导入数据  成长值计算
        }

        //付款购买的 uid 数组
        $no_buy_list = Order::find()->where(['>=', 'status', 2])->asArray()->all();
        //var_dump($no_buy_list);
        $no_buy_uid_list = array_column($no_buy_list, 'uid');
        $no_list = [
            3660,            3661,            3662,            3663,            3664,            3665,            3666,            3667,            3668,
            3669,            3670,            3671,            3672,            3673,            3674,            3675,            3676,
            3677,            3678,            3679,            3680,            3681,            3682,            3683,            3684,
            3685,            3686,            3687,            3688,            3689,            3690,            3691,            3692,
            3693,            3694,            3695,            3696,            3697,            3698,            3699,            3700,
            3701,            3702,            3703,            3704,            3705,            3706,            3707,            3708,
            3709,            3710,            3711,            3712,            3713,            3714,            3715,            3716,
            3717,            3718,            3719        ];
        $no_buy_uid_list = array_merge($no_buy_uid_list, $no_list);
        //没付款  后台手动激活的
        /** @var User $user */
        foreach (User::find()->where(['status' => 1])
                     ->andWhere(['>', 'id', '2790'])
                     ->andWhere(['NOT IN', 'id', $no_buy_uid_list])->all() as $user) {
            echo $user->id . 'begin:' . chr(10);
//            var_dump($user->id);
//            echo '<br>';
            //$r = $user->init_handle_subsidy($user);
            //$r = $user->init_handle_subsidy_test($user);
            $r = $user->init_growth_test2($user);
            var_dump($r);
            echo $user->id. 'end' . chr(10);
        }

        $buy_list = Order::find()->where(['>=', 'status', 2])->asArray()->all();
        $buy_list = array_column($buy_list, 'uid');
        /** @var User $user */
        foreach (User::find()->where(['status' => User::STATUS_OK])->andWhere(['IN', 'id', $buy_list])->all() as $user) {
            echo $user->id . 'begin:' . chr(10);
            //$r = $user->all_sub($user);
            $r = $user->init_growth_test2($user);  //前台正常流程  激活返 补贴
            var_dump($r);
            echo $user->id. 'end' . chr(10);
        }
    }

    /**
     * 春节期间手动激活
     */
    public function actionHandleSpringRunSubsidy()
    {
        //按照手机号码查询
        $user_list = User::find()->where(['IN', 'mobile', [13939374409,15863833777,15196535551,13792553144,18354573126,
            15163988036,15946419808,13329336478,15804658592,15946560813,18646437729,18769131808,13001508481,15506657229,
            15330393134,15768062949,13030229905,15964223093,15864238661,13301458139,13228807728, 13527021888, 15651436291, 13792555367,
            13792250858, 13655434602, 13908976081, 13004327729, 13912127321, 18754916281, 15020903303, 13834697121,15066649236,
            13137347596,
            15139352717,
            13461746756,
            15670130593,
            13461635628,
            15372938078,
            15315215140,
            18622131977,
            15826769318,
            15161153437,
            15189857706,
            18757940539,
            18252733581,
            13952745791,
            13721750908,
            18353925159,
            15762019535,
            18782629816,
            15510254212,
            15893252207,
            13763049683,
            13542022890,
            15952772062,
            13373689572]])->all();
        $no_buy_uid_list = array_column($user_list, 'id');

        //没付款  后台手动激活的
        /** @var User $user */
        foreach (User::find()->where(['status' => 1])
                     ->andWhere(['IN', 'id', $no_buy_uid_list])->all() as $user) {
            echo $user->id . 'begin:' . chr(10);
//            var_dump($user->id);
//            echo '<br>';
            //$r = $user->init_handle_subsidy($user);
            //$r = $user->init_handle_subsidy_test($user);
            $r = $user->init_handle_subsidy_test2($user);
            var_dump($r);
            echo $user->id. 'end' . chr(10);
        }
    }

    /**
     * 春节期间 手动激活 成长值
     */
    public function actionSpringRunGrowth()
    {
        //按照手机号码查询
        $user_list = User::find()->where(['IN', 'mobile', [13939374409,15863833777,15196535551,13792553144,18354573126,
            15163988036,15946419808,13329336478,15804658592,15946560813,18646437729,18769131808,13001508481,15506657229,
            15330393134,15768062949,13030229905,15964223093,15864238661,13301458139,13228807728, 13527021888, 15651436291, 13792555367,
            13792250858, 13655434602, 13908976081, 13004327729, 13912127321, 18754916281, 15020903303, 13834697121,15066649236,
            13137347596,
            15139352717,
            13461746756,
            15670130593,
            13461635628,
            15372938078,
            15315215140,
            18622131977,
            15826769318,
            15161153437,
            15189857706,
            18757940539,
            18252733581,
            13952745791,
            13721750908,
            18353925159,
            15762019535,
            18782629816,
            15510254212,
            15893252207,
            13763049683,
            13542022890,
            15952772062,
            13373689572]])->all();
        $no_buy_uid_list = array_column($user_list, 'id');
        //没付款  后台手动激活的
        /** @var User $user */
        foreach (User::find()->where(['status' => 1])
                     ->andWhere(['IN', 'id', $no_buy_uid_list])->all() as $user) {
            echo $user->id . 'begin:' . chr(10);
//            var_dump($user->id);
//            echo '<br>';
            //$r = $user->init_handle_subsidy($user);
            //$r = $user->init_handle_subsidy_test($user);
            $r = $user->init_growth_test2($user);
            var_dump($r);
            echo $user->id. 'end' . chr(10);
        }
    }

    /**
     * 手动 增加补贴
     */
    public function actionHandleSub()
    {
        if ($this->isPost()) {
            $to_uid = $this->post('to_uid');
            $from_uid = $this->post('from_uid');
            $money = $this->post('money');
            $type = $this->post('type');
            if (empty($from_uid) || empty($to_uid)) {
                throw new NotFoundHttpException('用户编号必填。');
            }
            if (empty($money) || empty($type)) {
                throw new NotFoundHttpException('金额或者类型必填。');
            }
            $to_user = User::findOne($to_uid);
            if (empty($to_user)) {
                throw new NotFoundHttpException('接收用户编号不存在。');
            }
            $fromUidList = preg_split('/\D/', $from_uid, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($fromUidList)) {
                throw new BadRequestHttpException('发送用户编号不能为空。');
            }
            foreach ($fromUidList as $from) {
                $from_user = User::findOne($from);
                if (!empty($from_user)) {
                    $log = UserSubsidy::find()->where(['from_uid' => $from, 'to_uid' => $to_uid])->one();
                    if (empty($log)) {
                        $to_user->add_sub($from_user->id, $to_uid, $money, $type);
                        $to_user->strong_level($from_user);
                    }
                }
            }
            ManagerLog::info($this->manager->id, '手动增加补贴' . $money . '元给 ' . $to_user->real_name . '第' . $type . '层', print_r($fromUidList, true));
            Yii::$app->session->addFlash('success', '手动增加补贴成功。');
        }
        return $this->render('handle_subsidy', []);
    }

    /**
     * 手动 根据手机号码 增加补贴
     */
    public function actionHandleMobileSub()
    {
        if (!$this->manager->can('user/subsidy-edit')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $mobile = $this->get('mobile');
        if ($this->isPost()) {
            $to_uid = $this->post('to_uid');
            $from_uid = $this->post('from_uid');
            $money = $this->post('money');
            $type = $this->post('type');
            if (empty($from_uid) || empty($to_uid)) {
                throw new NotFoundHttpException('用户编号必填。');
            }
            if (empty($money) || empty($type)) {
                throw new NotFoundHttpException('金额或者类型必填。');
            }

            //$to_user = User::findOne($to_uid);
            /** @var User $to_user */
            $to_user = User::find()->where(['mobile' => $to_uid])->one();
            if (empty($to_user)) {
                throw new NotFoundHttpException('接收用户不存在。');
            }
            $fromUidList = preg_split('/\D/', $from_uid, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($fromUidList)) {
                throw new BadRequestHttpException('发送用户编号不能为空。');
            }
            foreach ($fromUidList as $from) {
                //$from_user = User::findOne($from);
                /** @var User $from_user */
                $from_user = User::find()->where(['mobile' => $from])->one();
                if (!empty($from_user)) {
                    $log = UserSubsidy::find()->where(['from_uid' => $from_user->id, 'to_uid' => $to_user->id])->one();
                    if (empty($log)) {
                        $to_user->add_sub($from_user->id, $to_user->id, $money, $type);
                        $to_user->strong_level($from_user);
                        $log_param = ['from' => $from_user->attributes, 'to' => $to_user->attributes, 'money' => $money, 'type' => $type];
                        ManagerLog::info($this->manager->id, '手动增加补贴' . $money . '元给 ' . $to_user->real_name . '第' . $type . '层' . $from_user->real_name, print_r($log_param, true));
                    }
                }
            }

            Yii::$app->session->addFlash('success', '手动增加补贴成功。');
        }
        return $this->render('handle_mobile_subsidy', [
            'mobile' => $mobile
        ]);
    }

    /**
     * 手动 根据手机号码 发放活动补贴
     */
    public function actionHandleActiveSub()
    {
        if (!$this->manager->can('user/subsidy-edit')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $mobile = $this->get('mobile');
        if ($this->isPost()) {
            $to_uid = $this->post('to_uid');
            $money = $this->post('money');
            $name = $this->post('name');
            $type = 5;
            if (empty($to_uid)) {
                throw new NotFoundHttpException('用户编号必填。');
            }
            if (empty($money) || empty($type) || $money <= 0 || Util::comp($money, 0, 2) == -1) {
                throw new NotFoundHttpException('金额或者类型必填。');
            }

            /** @var User $to_user */
            $to_user = User::find()->where(['mobile' => $to_uid])->one();
            if (empty($to_user)) {
                throw new NotFoundHttpException('接收用户不存在。');
            }
            /** @var User $from_user */
            $from_user = User::findOne(3433);
            if (!empty($from_user)) {
                $to_user->add_sub($from_user->id, $to_user->id, $money, $type, $name);
                $log_param = ['from' => $from_user->attributes, 'to' => $to_user->attributes, 'money' => $money, 'type' => $type];
                ManagerLog::info($this->manager->id, $name. '手动发放活动奖励补贴' . $money . '元给 ' . $to_user->real_name , print_r($log_param, true));
            }

            Yii::$app->session->addFlash('success', '手动发放活动奖励补贴成功。');
        }
        return $this->render('handle_active_subsidy', [
            'mobile' => $mobile
        ]);
    }

    /**
     * 手动 根据手机号码 增加地推优惠券
     */
    public function actionSendMobileCoupon()
    {
        if (!$this->manager->can('user/subsidy-edit')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $mobile = $this->get('mobile');
        if ($this->isPost()) {
            $from_uid = $this->post('from_uid');
            $fromUidList = preg_split('/\D/', $from_uid, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($fromUidList)) {
                throw new BadRequestHttpException('发送用户编号不能为空。');
            }
            /** @var Goods $goods */
            $goods = Goods::find()->where(['is_coupon' => 1, 'status' => Goods::STATUS_ON])->one();
            /** @var GoodsCouponRule $coupon */
            $coupon = GoodsCouponRule::find()->where(['status' => GoodsCouponRule::STATUS_OK, 'gid' => $goods->id])->one();
            foreach ($fromUidList as $from) {
                /** @var User $from_user */
                $from_user = User::find()->where(['mobile' => $from])->one();
                if (!empty($from_user)) {
                    $log = GoodsCouponGiftUser::find()->where(['uid' => $from_user->id, 'gid' => $goods->id, 'cid' => $coupon->id])->one();
                    if (empty($log)) {
                        for ($i = 1; $i <= $coupon->count; $i++) {
                            /** @var GoodsCouponGiftUser $gift */
                            $gift = new GoodsCouponGiftUser();
                            $gift->uid = $from_user->id;
                            $gift->gid = $goods->id;
                            $gift->create_time = time();
                            $gift->status = GoodsCouponGiftUser::STATUS_WAIT;
                            $gift->cid = $coupon->id;
                            $gift->save();
                            ManagerLog::info($this->manager->id, '后台发放地推优惠券' . $from_user->real_name , print_r($gift->attributes, true));
                        }
                    }
                }
            }

            Yii::$app->session->addFlash('success', '后台发放地推优惠券。');
        }
        return $this->render('send_mobile_coupon', [
            'mobile' => $mobile
        ]);
    }

    /**
     * 手动 根据手机号码 发放活动积分
     * @throws
     */
    public function actionSendActiveScore()
    {
        if (!$this->manager->can('user/subsidy-edit')) {
            throw new ForbiddenHttpException('没有权限');
        }
        //$mobile = $this->get('mobile');
        if ($this->isPost()) {
            $post_array = Yii::$app->request->post('data');
            $post = array_column($post_array, 'value', 'name');
            $to_mobile = explode(',', $post['to_mobile']);
            $score = $post['score'];
            $remark = $post['name'];
            $code = UserScoreLog::HANDLE;
            if (empty($remark)) {
                return ['message' => '活动名称必填。'];
            }
            if (empty($to_mobile)) {
                return ['message' => '用户手机号必填。'];
            }
            if (empty($score) || $score <= 0 || Util::comp($score, 0, 2) == -1) {
                return ['message' => '积分或者类型必填。'];
            }
            $error_mobile=[];//发放失败的手机号记录
            $send_user=[];
            /** @var User $to_user */
            foreach ($to_mobile as $mobile) {
                $mobile=trim($mobile);
                if (strlen($mobile) == 11) {
                    $to_user = User::find()->where(['mobile' => $mobile])->one();
                    if (empty($to_user)) {
                        $error_mobile[] = ['mobile' => $mobile, 'message' => '该手机号用户不存在'];
                    }else{
                        $send_user[]=$to_user;
                    }

                } else {
                    $error_mobile[] = ['mobile' => $mobile, 'message' => '请检查手机号位数不对['.strlen($mobile).']'];

                }
            }
           if(count($error_mobile) < 1)
           {
               foreach ($send_user as $user) {
                   /** @var User $user */
                   $from_user = User::findOne(3433);
                   if (!empty($from_user)) {
                       if ($user->addScore($score, $code, $remark)) {
                           $log_param = ['from' => $from_user->attributes, 'to' => $user->attributes, 'score' => $score, 'type' => $code];
                           ManagerLog::info($this->manager->id, $remark . '手动发放活动积分' . $score . '给 ' . $user->real_name, print_r($log_param, true));
                       } else {
                           $error_mobile[] = ['mobile' => $user->mobile, 'message' => '更新积分失败'];
                       }
                   }
               }
           }

            //Yii::$app->session->addFlash('success', '手动发放活动积分成功。');
            return [
                'result' => 'success',
                'error_mobile' => $error_mobile
            ];

        }
        return $this->render('send_active_score');
    }

    /**
     * 手动 根据手机号码 发放礼包兑换券
     * @throws
     */
    public function actionSendPackCoupon()
    {
        if (!$this->manager->can('user/subsidy-edit')) {
            throw new ForbiddenHttpException('没有权限');
        }
        if ($this->isPost()) {
            $post_array = Yii::$app->request->post('data');
            $post = array_column($post_array, 'value', 'name');
            $to_mobile = explode(',', $post['to_mobile']);
            if (empty($to_mobile)) {
                return ['message' => '用户手机号必填。'];
            }
            $error_mobile = [];//发放失败的手机号记录
            $send_user = [];
            /** @var User $to_user */
            foreach ($to_mobile as $mobile) {
                $mobile = trim($mobile);
                if (strlen($mobile) == 11) {
                    $to_user = User::find()->where(['mobile' => $mobile])->one();
                    if (empty($to_user)) {
                        $error_mobile[] = ['mobile' => $mobile, 'message' => '该手机号用户不存在'];
                    } else {
                        if(UserPackageCoupon::find()->where(['uid' => $to_user->id])->exists())
                        {
                            $error_mobile[] = ['mobile' => $mobile, 'message' => '该手机号已发放过'];
                            continue;
                        }
                        $send_user[] = $to_user;
                    }

                } else {
                    $error_mobile[] = ['mobile' => $mobile, 'message' => '请检查手机号位数不对[' . strlen($mobile) . ']'];
                }
            }
            //发放礼包卡券
            if (count($error_mobile) < 1) {
                $data = [];
                foreach ($send_user as $user) {
                    /** @var User $user */
                    $from_user = User::findOne(3433);
                    if (!empty($from_user)) {
                        $data[] = [
                            'uid' => $user->id,
                            'create_time' => time(),
                            'over_time' =>  time() + 86400 * System::getConfig('pack_redeem_over_day'),
                            'status' => UserPackageCoupon::STATUS_OK,

                        ];
                        ManagerLog::info($this->manager->id, '手动发放礼包卡券给 ' . $user->real_name);
                    }
                }
                //再执行批量插入
                if (isset($data)) {
                    Yii::$app->db->createCommand()
                        ->batchInsert(UserPackageCoupon::tableName(), ['uid', 'create_time', 'over_time', 'status'],
                            $data)
                        ->execute();
                }
            }

            return [
                'result' => 'success',
                'error_mobile' => $error_mobile
            ];

        }
        return $this->render('send_pack_coupon');
    }
    /**
     * 编辑 用户获取的补贴记录
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionSubsidyEdit()
    {
        if (!$this->manager->can('user/subsidy-edit')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $id = $this->get('id');
        /** @var UserSubsidy $model */
        $model = UserSubsidy::find()->where(['id' => $id])->one();
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到用户补贴记录信息。');
        }
        if ($model->load($this->post())) {
            $r = $model->editSub($id, $model->money);
            if ($r === true) {
                ManagerLog::info($this->manager->id, '保存用户补贴记录：' . print_r($model->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/user/subsidy-list', 'uid' => $model->to_uid]),
                    'txt' => '用户补贴记录列表'
                ]));
            } else {
                Yii::$app->session->addFlash('error', '数据保存失败' . $model->errors['money'][0]);
            }
        }
        return $this->render('subsidy_edit',[
            'model' => $model,
        ]);
    }

    /**
     * 添加 用户预购数量
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionPrepareCountEdit()
    {
        if (!$this->manager->can('user/add_prepare_count')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $id = $this->get('id');
        if ($id > 0) {
            /** @var User $model */
            $model = User::find()->where(['id' => $id])->andWhere(['<>', 'status', User::STATUS_DELETE])->one();
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到用户信息。');
            }
        } else {
            throw new NotFoundHttpException('没有找到用户信息。');
        }
        if ($this->isPost()) {
            $count = $this->post('count');
            if ($count <= 0) {
                Yii::$app->session->addFlash('error', '数量错误。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/user/list']),
                    'txt' => '用户列表'
                ]));
            }
            $model->prepare_count = $model->prepare_count + $count;
            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存用户新增预购数量：' . $count .' 保存为 '. print_r($model->prepare_count, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/user/list']),
                    'txt' => '用户列表'
                ]));
            }
        }
        return $this->render('prepare_count_edit',[
            'model' => $model,
        ]);
    }

    /**
     * 前台发展用户排行
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionSaleList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }

        $pid_query = User::find();
        $pid_query->select(['id', 'real_name', 'nickname', 'mobile', 'level_id','pid', 'count(id) as c']);
        if (!empty($this->get('search_start_date'))) {
            $pid_query->andFilterWhere(['>=', 'handle_time', strtotime($this->get('search_start_date'))]);
        }
        if (!empty($this->get('search_end_date'))) {
            $pid_query->andFilterWhere(['<', 'handle_time', strtotime($this->get('search_end_date')) + 86400]);
        }
        $pid_query->orderBy('c desc');
//        $pid_query->andWhere(['is_self_active' => 1]);
        $pid_query->groupBy('pid');
        $pid_query->asArray();
        $pid_arr = $pid_query->all();

        $pid_arr = array_column($pid_arr, 'pid');
        $list = [];
        if (!empty($pid_arr)) {
            foreach ($pid_arr as $pid) {
                $query = User::find();
                if (!empty($this->get('search_start_date'))) {
                    $query->andFilterWhere(['>=', 'handle_time', strtotime($this->get('search_start_date'))]);
                }
                if (!empty($this->get('search_end_date'))) {
                    $query->andFilterWhere(['<', 'handle_time', strtotime($this->get('search_end_date')) + 86400]);
                }
//                $query->andWhere(['is_self_active' => 1]);
                $query->andWhere(['status' => 1]);
                $query->andWhere(['pid' => $pid]);
                $count = $query->count();
                $info = User::findOne($pid);
                if (!empty($info)) {
                    $list[] = [
                        'id' => $pid,
                        'real_name' => $info->real_name,
                        'nickname' => $info->nickname,
                        'mobile' => $info->mobile,
                        'level_id' => $info->level_id,
                        'count' => $count,
                    ];
                }
            }
        }
        return $this->render('sale_list', [
            'model_list' => $list,
        ]);

        $query = User::find()->groupBy('id');
//        $query->andFilterWhere(['like', 'real_name',$this->get('search_real_name')]);
//        $query->andFilterWhere(['like', 'nickname', $this->get('search_nickname')]);
//        $query->andFilterWhere(['=', 'mobile', $this->get('search_mobile')]);
//        $query->andFilterWhere(['=', 'level_id', $this->get('search_level_id')]);
        $query->where(['IN', 'id', $pid_arr]);


        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        $list = [];
        /** @var User $user */
        foreach ($model_list as $user) {
            $query = User::find();
            if (!empty($this->get('search_start_date'))) {
                $query->andFilterWhere(['>=', 'handle_time', strtotime($this->get('search_start_date'))]);
            }
            if (!empty($this->get('search_end_date'))) {
                $query->andFilterWhere(['<', 'handle_time', strtotime($this->get('search_end_date')) + 86400]);
            }
            //$query->andWhere(['is_self_active' => 1]);
            $query->andWhere(['pid' => $user->id]);
            $count = $query->count();
            $list[] = [
                'id' => $user->id,
                'real_name' => $user->real_name,
                'nickname' => $user->nickname,
                'mobile' => $user->mobile,
                'level_id' => $user->level_id,
                'count' => $count,
            ];
        }
        return $this->render('sale_list', [
            'model_list' => $list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 整个网体团队排行
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionServerList()
    {
        set_time_limit(0);
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $type = $this->get('search_level_id', 3);
        $type == 3 ? $arr = [3] : $arr = [2];
        $list = [];
        /** @var User $server */
        foreach (User::find()->andWhere(['in', 'level_id', $arr])->andWhere(['id' => 2306])->each() as $server) {
            //$child = $server->getOkBottomUser($server->id, '', '1559318400');
            $child1 = $server->getOkBottomUsers($server->id, '');
            //$child = count(explode(',', $child)) - 1;
            $list[] = [
                'id' => $server->id,
                'real_name' => $server->real_name,
                'nickname' => $server->nickname,
                'mobile' => $server->mobile,
                'count' => 0,//$child,
                'count1' => count(explode(',', $child1)),
            ];
        }

        // 取得列的列表
        foreach ($list as $key => $row)
        {
            $counts[$key]  = $row['count'];
            $id[$key]  = $row['id'];
        }

        if (!empty($id)) {
            array_multisort($counts, SORT_DESC, $id, SORT_ASC, $list);
        }

        return $this->render('server_list', [
            'model_list' => $list,
        ]);
    }

    /**
     * 整个网体团队排行
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionServerCountList()
    {
        set_time_limit(0);
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $type = $this->get('search_level_id', 3);
        $type == 3 ? $arr = [3] : $arr = [2];
        $list = [];
        $c = 0;
        /** @var User $server */
        foreach (User::find()->where(['in', 'level_id', $arr])->each() as $server) {
//            $child = $server->getOkBottomUser($server->id, '', '1559318400');
//            $child = count(explode(',', $child)) - 1;
            $sql = "select getChildList(".$server->id.")";
            $condition = Yii::$app->db->createCommand($sql)->queryAll();
            //echo substr($condition[0]["getChildList(".$server->id.")"], 2, -1);
            $q1 = User::find();
            $q2 = User::find();
            $a = explode(',', substr($condition[0]["getChildList(".$server->id.")"], 2, -1));
            $child = $q1->select('count(*) as c')->andWhere(['in', 'id', $a])->asArray()->one();
            $c += $child['c'];
            //echo $q1->createCommand()->getRawSql();
            $child_handle = $q2->select('count(*) as c')->andWhere(['in', 'id', $a])
                        ->andWhere(['>', 'handle_time', '1559318400'])->asArray()->one();
            //echo $q2->createCommand()->getRawSql();
            $list[] = [
                'c' => $c,
                'id' => $server->id,
                'real_name' => $server->real_name,
                'nickname' => $server->nickname,
                'mobile' => $server->mobile,
                'count' => $child['c'],
                'handle_count' => $child_handle['c']
            ];
        }

        // 取得列的列表
        foreach ($list as $key => $row)
        {
            $counts[$key]  = $row['handle_count'];
            $id[$key]  = $row['id'];
        }

        if (!empty($id)) {
            array_multisort($counts, SORT_DESC, $id, SORT_ASC, $list);
        }

        return $this->render('server_count_list', [
            'c'=> $c,
            'model_list' => $list,
        ]);
    }

    /**
     * 预购数量排行
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionCountList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }

        $query = User::find()->groupBy('id');
        $query->andFilterWhere(['like', 'real_name',$this->get('search_real_name')]);
        $query->andFilterWhere(['like', 'nickname', $this->get('search_nickname')]);
        $query->andFilterWhere(['=', 'mobile', $this->get('search_mobile')]);
        $query->andFilterWhere(['=', 'level_id', $this->get('search_level_id')]);
        $query->andWhere(['>', 'prepare_count', 0]);


        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->offset($pagination->offset)->orderBy('prepare_count desc')->limit($pagination->limit)->all();
        return $this->render('count_list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 预购数量排行
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionMonthSaleList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        ini_set('max_execution_time','180');
        ini_set ('memory_limit', '128M');

        //$query = User::find()->groupBy('id');
        $query = User::find();
        $query->andFilterWhere(['like', 'real_name',$this->get('search_real_name')]);
        $query->andFilterWhere(['like', 'nickname', $this->get('search_nickname')]);
        $query->andFilterWhere(['=', 'mobile', $this->get('search_mobile')]);
        $query->andFilterWhere(['=', 'id', $this->get('search_id')]);
        //$query->andWhere(['level_id' => 2]);
        $query->andWhere(['{{user}}.status' => User::STATUS_OK]);

        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->offset($pagination->offset)->orderBy('prepare_count desc')->limit($pagination->limit)->all();
        $list = [];
        /** @var User $model */
        foreach ($model_list as $model) {

            $child_list = $model->getOkBottomUsers($model->id);
            $child_list = explode(',', $child_list);
            array_pop($child_list);

            $count = $count_all = count($child_list);
            $money = $count * 399;
            foreach ($child_list as $child) {
                //Yii::warning($child);
                $count = User::find()->where(['pid' => $child, 'status' => User::STATUS_OK])->count();
                $money += $count * 399;
                $count_all += $count;
                //Yii::warning($count);
                //Yii::warning($money);
                //Yii::warning($count_all);
            }
            $list[] = [
                'user' => $model,
                'money' => $money,
                'count' => $count_all,
            ];
        }

        return $this->render('month_list', [
            'model_list' => $list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 佣金月度统计
     */
    public function actionCommissionMonth()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        ini_set('max_execution_time','3600');
        ini_set ('memory_limit', '512M');
        $level_id = $this->get('level_id');
        $start = $this->get('search_start_date');
        $end = $this->get('search_end_date');
        if (empty($start)) {
            $begin_time = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
        } else {
            $begin_time = strtotime($start);
        }

        if (empty($end)) {
            $end_time = strtotime(date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("t"), date("Y"))));
        } else {
            $end_time = strtotime($end);
        }


        $query = User::find();
        $query->andFilterWhere(['like', 'real_name',$this->get('search_real_name')]);
        $query->andFilterWhere(['like', 'nickname', $this->get('search_nickname')]);
        $query->andFilterWhere(['=', 'mobile', $this->get('search_mobile')]);
        $query->andFilterWhere(['=', 'id', $this->get('search_id')]);
        $query->andFilterWhere(['=', 'level_id', $this->get('level_id')]);
        $query->andWhere(['{{user}}.status' => User::STATUS_OK]);


        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new PHPExcel();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         'UID',
                         '用户真实姓名',
                         '佣金',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font'=>['bold'=>true], 'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var User $model */
            foreach ($query->each() as $model) {
                if ($level_id > 1) {
                    $child_list = $model->getBottomUsers($model->id);
                    $child_list = explode(',', $child_list);
                    array_pop($child_list);
                } else {
                    $child_list = $model->childList;

                    $child_list = ArrayHelper::getColumn(ArrayHelper::toArray($child_list), 'id');
                }
                $money = 0;
                foreach ($child_list as $child) {
                    $money += UserCommission::find()->where(['uid' => $child, 'type' => 1])
                        ->andWhere(['BETWEEN', 'time', $begin_time, $end_time])->sum('commission');
                }
                /** @var User $model */
                $sheet->setCellValueExplicitByColumnAndRow(0, $r, $model->id, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $model->real_name);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, sprintf('%.2f',$money));
                $r +=1;

            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="佣金列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $excelWriter->save('php://output');
            return null;
        }

        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->offset($pagination->offset)->orderBy('prepare_count desc')->limit($pagination->limit)->all();
        $list = [];


        /** @var User $model */
        foreach ($model_list as $model) {
            if ($level_id > 1) {
                $child_list = $model->getBottomUsers($model->id);
                $child_list = explode(',', $child_list);
                array_pop($child_list);
            } else {
                $child_list = $model->childList;

                $child_list = ArrayHelper::getColumn(ArrayHelper::toArray($child_list), 'id');
            }
            $money = 0;
            foreach ($child_list as $child) {
                $money += UserCommission::find()->where(['uid' => $child, 'type' => 1])
                    ->andWhere(['BETWEEN', 'time', $begin_time, $end_time])->sum('commission');
            }
            $list[] = [
                'user' => $model,
                'money' => $money,
            ];
        }

        return $this->render('commission_month_list', [
            'model_list' => $list,
            'pagination' => $pagination,
        ]);
    }


    /**
     * 店主列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionMasterList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = MasterUser::find();
        $query->andWhere(['<>', 'status', User::STATUS_DELETE]);
        $query->andFilterWhere(['like', 'mobile', $this->get('search_mobile')]);
        $query->andFilterWhere(['like', 'real_name', $this->get('search_real_name')]);
        $query->andFilterWhere(['like', 'nickname', $this->get('nickname')]);
        $p_mobile =$this->get('search_p_mobile');
        if ($p_mobile) {
            /** @var User $pid */
            $pid = MasterUser::find()->where(['mobile' => $p_mobile])->one();
            $query->andWhere(['pid' => $pid->id]);
        }
        $team_p_mobile =$this->get('search_team_p_mobile');
        if ($team_p_mobile) {
            /** @var User $pid1 */
            $pid1 = MasterUser::find()->where(['mobile' => $team_p_mobile])->one();
            $query->andWhere(['team_pid' => $pid1->id]);
        }
        $query->andFilterWhere(['pid' => $this->get('search_p_id')]);
        $query->andFilterWhere(['team_pid' => $this->get('search_team_p_id')]);
        $query->andFilterWhere(['status' => $this->get('search_status')]);
        if (!empty($this->get('search_handle_start_date'))) {
            $query->andFilterWhere(['>=', 'handle_time', strtotime($this->get('search_handle_start_date'))]);
            $query->andWhere(['>', 'handle_time', 0]);
            $query->andWhere(['status' => MasterUser::STATUS_OK]);
        }
        if (!empty($this->get('search_handle_end_date'))) {
            $query->andFilterWhere(['<', 'handle_time', strtotime($this->get('search_handle_end_date')) + 86400]);
        }
        if (!empty($this->get('search_create_start_date'))) {
            $query->andFilterWhere(['>=', 'create_time', strtotime($this->get('search_create_start_date'))]);
            $query->andWhere(['>', 'create_time', 0]);
        }
        if (!empty($this->get('search_create_end_date'))) {
            $query->andFilterWhere(['<', 'create_time', strtotime($this->get('search_create_end_date')) + 86400]);
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('master_list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 添加/编辑 用户资料
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionMasterEdit()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $id = $this->get('id');
        if ($id > 0) {
            $model = MasterUser::find()->where(['id' => $id])->andWhere(['<>', 'status', User::STATUS_DELETE])->one();
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到用户信息。');
            }
        } else {
            $model = new MasterUser();
            $model->create_time = time();
            $model->status = 1;
        }
        if ($model->load($this->post())) {
            $model->create_time = strtotime($model->create_time);
            if(!empty($this->post('User')['password'])){
                $model->password = $this->post('User')['password'];
                $model->auth_key = Util::randomStr(32, 7);
                $model->password = Yii::$app->security->generatePasswordHash($model->password);
            }
            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存用户资料：' . print_r($model->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/user/list']),
                    'txt' => '店主列表'
                ]));
            }
        }
        return $this->render('master_edit',[
            'model' => $model,
        ]);
    }

    /**
     * 添加 编辑 店主结算记录单
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionMasterAccountLogEdit()
    {

        $id = $this->get('id');
        $uid = $this->get('uid');
        if ($id > 0) {
            $model = MasterUserAccountLog::find()->where(['id' => $id])->andFilterWhere(['=', 'uid', $uid])->one();
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到店主结算信息。');
            }
        } else {
            $model = new MasterUserAccountLog();
            $model->create_time = time();
            $model->time = time();
            $model->uid = $uid;
            $model->status = 2;
        }
        if ($model->load($this->post())) {
            $model->time = strtotime($model->time);
            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存销售员结算记录：' . print_r($model->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/user/account-list']),
                    'txt' => '销售员结算单列表'
                ]));
            }
        }
        return $this->render('master_account_log_edit',[
            'model' => $model,
        ]);
    }

    /**
     * 设置结算记录状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionMasterAccountLogStatus()
    {
        if (!$this->manager->can('ad/edit')) {
//            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = MasterUserAccountLog::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到结算记录信息。');
        }
        $model->status = [MasterUserAccountLog::STATUS_ON => MasterUserAccountLog::STATUS_WAIT, MasterUserAccountLog::STATUS_WAIT => MasterUserAccountLog::STATUS_ON][$model->status];
        if ($model->save()) {
            return [
                'result' => 'success'
            ];
        }
        return [
            'result' => 'failure',
            'message' => '无法保存信息。',
            'errors' => $model->errors
        ];
    }

    /**
     * 财务记录
     * @return string
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionMasterAccountList()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $uid = $this->get('uid');
        $user = MasterUser::findOne($uid);
        if (empty($user)) {
            //throw new NotFoundHttpException('没有找到用户信息。');
        }

        $query = MasterUserAccountLog::find();
        $query->joinWith('user');
        $query->andFilterWhere(['like', 'content', $this->get('search_content')]);
        $query->andFilterWhere(['=', '{{user_account_log}}.status', $this->get('search_status')]);
        $query->andFilterWhere(['uid' => $uid]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('master_account_list', [
            'list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 删除店主AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionMasterDelete()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = MasterUser::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到店主信息。');
        }
        $order = Order::find()->where(['uid' => $id])->exists();
        if ($order) {
            return [
                'message' => '有关联数据不能删除'
            ];
        }
        $model->status = MasterUser::STATUS_DELETE;
        $model->save();
        ManagerLog::info($this->manager->id, '删除店主', print_r($model->attributes, true));
        return [
            'result' => 'success'
        ];
    }

    /**
     * 删除店主结算单AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteMasterAccountLog()
    {
        if (!$this->manager->can('user/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = MasterUserAccountLog::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到店主结算单信息。'];
        }
        $model->status = MasterUserAccountLog::STATUS_DEL;
        ManagerLog::info($this->manager->id, '删除店主结算单', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

}
