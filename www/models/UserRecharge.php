<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * 用户充值
 * Class UserRecharge
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $fid 财务记录编号
 * @property float $money 充值金额
 * @property integer $create_time 创建时间
 * @property integer $status 状态
 * @property string $remark 备注
 *
 * @property User $user 关联用户
 * @property FinanceLog $financeLog 关联财务记录
 */
class UserRecharge extends ActiveRecord
{
    const STATUS_WAIT = 1; // 待支付
    const STATUS_SUCCESS = 2; // 支付成功
    const STATUS_FAIL = 9; // 支付失败
    const STATUS_DEL = 0; // 删除

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    /**
     * 关联财务记录
     * @return \yii\db\ActiveQuery
     */
    public function getFinanceLog()
    {
        return $this->hasOne(FinanceLog::className(), ['id' => 'fid']);
    }

    /**
     * 充值结果
     * @param boolean $is_success 是否成功
     * @param string $trade_no 交易号
     * @return boolean
     * @throws Exception
     */
    public function payNotify($is_success, $trade_no)
    {
        if (!$is_success) {
            $this->status = UserRecharge::STATUS_FAIL;
            $r = $this->save();
            if (!$r) {
                throw new Exception('无法更新充值状态。');
            }
        } else {
            if (!UserLevel::find()->andWhere(['money' => $this->money])->exists()) {
                throw new Exception('充值金额错误。');
            }
            /** @var UserAccount $user_account */
            $user_account = UserAccount::find()->andWhere(['uid' => $this->uid])->one();
            if ($this->money > $user_account->level_money) {
                $user_account->level_money = $this->money;
                if (!$user_account->save()) {
                    throw new Exception('无法更新用户等级金额。');
                }
            }
            $r = UserAccount::updateAllCounters(['money' => $this->money], ['uid' => $this->uid]);
            if ($r <= 0) {
                throw new Exception('无法更新账户。');
            }
            $ual = new UserAccountLog();
            $ual->uid = $this->uid;
            $ual->money = $this->money;
            $ual->time = time();
            $ual->remark = '用户充值：' . $trade_no;
            if (!$ual->save()) {
                throw new Exception('无法保存账户记录。');
            }
            $this->status = UserRecharge::STATUS_SUCCESS;
            if (!$this->save()) {
                throw new Exception('无法更新充值状态。');
            }
        }
        return true;
    }

    /**
     * 三级返佣
     * @param $money
     * @return bool
     */
    public function backCommission($money)
    {
        Yii::warning($this->user->id . '开始返佣');
        $first_user = User::findOne($this->user->pid);
        if (empty($first_user)) {
            Yii::warning('没有上级用户，返佣结束。');
            return true;
        }
        /** @var UserAccount $first_user_account */
        $first_user_account = UserAccount::find()->andWhere(['uid' => $first_user->id])->one();
        /** @var UserLevel $first_level */
        $first_level = UserLevel::find()
            ->andWhere(['<=', 'money', $first_user_account->level_money])
            ->orderBy('money DESC')
            ->limit(1)
            ->one();
        if (!empty($first_level)) {
            // 计算佣金
            $commission = $money / 100 * $first_level->commission_ratio_1;
            // 添加佣金记录
            $this->addCommissionRecord($commission, $first_user->id, 1, '一级返佣');
            $second_user = User::findOne($first_user->pid);
            if (empty($second_user)) {
                Yii::warning('没有上二级用户，返佣结束。');
                return true;
            }
            /** @var UserAccount $second_user_account */
            $second_user_account = UserAccount::find()->andWhere(['uid' => $second_user->id])->one();
            /** @var UserLevel $second_level */
            $second_level = UserLevel::find()
                ->andWhere(['<=', 'money', $second_user_account->level_money])
                ->orderBy('money DESC')
                ->limit(1)
                ->one();
            if (!empty($second_level)) {
                // 计算佣金
                $commission = $money / 100 * $second_level->commission_ratio_2;
                // 添加佣金记录
                $this->addCommissionRecord($commission, $second_user->id, 2, '二级返佣');
                $third_user = User::findOne($second_user->pid);
                if (empty($third_user)) {
                    Yii::warning('没有上三级用户，返佣结束。');
                    return true;
                }
                /** @var UserAccount $third_user_account */
                $third_user_account = UserAccount::find()->andWhere(['uid' => $third_user->id])->one();
                /** @var UserLevel $third_level */
                $third_level = UserLevel::find()
                    ->andWhere(['<=', 'money', $third_user_account->level_money])
                    ->orderBy('money DESC')
                    ->limit(1)
                    ->one();
                if (!empty($third_level)) {
                    // 计算佣金
                    $commission = $money / 100 * $third_level->commission_ratio_3;
                    // 添加佣金记录
                    $this->addCommissionRecord($commission, $third_user->id, 3, '三级返佣');
                }
            }
        }
        return true;
    }

    /**
     * 添加佣金记录
     * @param $commission string 佣金
     * @param $uid integer 用户编号
     * @param $level integer 级别
     * @param $remark string 备注
     * @return bool
     */
    public function addCommissionRecord($commission, $uid, $level, $remark)
    {
        // 增加佣金
        $r = UserAccount::updateAllCounters(['commission' => $commission], ['uid' => $uid]);
        if (!$r) {
            Yii::error($level . '级用户' . $uid . '增加佣金失败');
            return false;
        }
        // 增加佣金记录
        $uc = new UserCommission();
        $uc->uid = $uid;
        $uc->from_uid = $this->uid;
        $uc->level = $level;
        $uc->commission = $commission;
        $uc->time = time();
        $uc->remark = $remark;
        if (!$uc->save()) {
            Yii::error($level . '级用户' . $uid . '增加佣金记录失败');
            return false;
        }
        // 增加账户记录
        $ual = new UserAccountLog();
        $ual->uid = $uid;
        $ual->commission = $commission;
        $ual->time = time();
        $ual->remark = $remark;
        if (!$ual->save()) {
            Yii::error($level . '级用户' . $uid . '增加账户记录失败');
            return false;
        }
        return true;
    }
}
