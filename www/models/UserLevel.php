<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户等级
 * Class UserLevel
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 等级名称
 * @property string $logo 等级图标
 * @property float $money 等级费用会员费
 * @property string $description 等级权益说明
 * @property string $commission_ratio_1 一级返佣比率
 * @property string $commission_ratio_2 二级返佣比率
 * @property string $commission_ratio_3 三级返佣比率
 * @property string $money_1 一级补贴
 * @property string $money_2 二级补贴
 * @property string $money_3 三级补贴
 * @property integer $create_time 创建时间
 * @property integer $status 状态
 */
class UserLevel extends ActiveRecord
{
    const STATUS_OK = 1; //正常
    const STATUS_STOP = 9; //弃用
    const STATUS_DELETE = 0; //删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description','commission_ratio_1', 'commission_ratio_2', 'commission_ratio_3'], 'required'],
            [['name', 'commission_ratio_1', 'commission_ratio_2', 'commission_ratio_3', 'money_1', 'money_2', 'money_3'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '等级名称',
            'logo' => '等级图标',
            'money' => '会员费',
            'description' => '权益说明',
            'commission_ratio_1' => '直接管理业绩【业绩提成比例】',
            'commission_ratio_2' => '2代销售管理业绩【业绩提成比例】',
            'commission_ratio_3' => '无限代销售管理业绩【业绩提成比例】',
            'money_1' => '一级补贴',
            'money_2' => '二级补贴',
            'money_3' => '三级补贴',
            'status' => '状态',
        ];
    }

    /**
     * 计算每个等级获取相应的返佣
     * @param integer $level 等级
     * @param float $money 要返佣的金额
     * @return float 计算所得佣金
     */
    public function compute($level, $money)
    {
        $user_level = UserLevel::findOne($this->id);
        $commission = $money * ($user_level['commission_ratio_' . $level] / 100);
        return $commission;
    }

    /**
     * 每个级别佣金变动记录
     * @param $level int 一二三级
     * @param $money float 返佣金额
     * @param $user_level object 用户所属级别
     * @param $uid int 用户编号
     * @param $from_uid int 来源用户编号
     * @return array
     */
    public function commissionLog($level, $money, $user_level, $uid,$from_uid)
    {
        $levels = ['1' => '一级', '2' => '二级', '3' => '三级'];
        $user_commission_1 = new UserCommission();
        $user_commission_1->commission = $money;
        $user_commission_1->uid = $uid;
        $user_commission_1->from_uid = $from_uid;
        $user_commission_1->level = $level;
        $user_commission_1->time = time();
        $user_commission_1->remark = '后台充值 下级返佣：下级充值：' .$money . ' 下级用户ID: ' . $from_uid . ' 金额：' . $money . ' 当前等级: ' . $user_level->name . ' 佣金比率：' . $user_level['commission_ratio_'.$level];
        if (!$user_commission_1->save()) {
            return ['result' => 'error', 'message' => '无法保存'. $levels[$level] .'父级的佣金。'];
        }
        $r = UserAccount::updateAllCounters(['commission' => $money], ['uid' => $uid]);
        if ($r <= 0) {
            return ['result' => 'error', 'message' => '无法更新'. $levels[$level] .'父级账户。'];
        }
        $ual1 = new UserAccountLog();
        $ual1->uid = $uid;
        $ual1->commission = $money;
        $ual1->time = time();
        $ual1->remark = $levels[$level] .'下级返佣：' . $money;
        if (!$ual1->save()) {
            return ['result' => 'error', 'message' => '无法保存'. $levels[$level] .'账户记录。'];
        }
        return ['result' => 'success'];
    }
}
