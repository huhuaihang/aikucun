<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户提现
 * Class UserWithdraw
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property float $money 提现金额
 * @property float $tax 手续费
 * @property string $bank_name 银行名称
 * @property string $bank_address 开户行所在地
 * @property string $account_name 账户名
 * @property string $account_no 账号
 * @property integer $create_time 创建时间
 * @property integer $apply_time 通过时间
 * @property integer $finish_time 完毕时间
 * @property integer $type 提现类型 补贴/佣金
 * @property integer $status 状态
 * @property string $remark 备注
 *
 * @property User $user 关联用户表
 */
class UserWithdraw extends ActiveRecord
{
    const STATUS_WAIT = 1;
    const STATUS_OK = 2;
    const STATUS_FINISH = 3;
    const STATUS_REFUSE = 9;
    const STATUS_DELETE = 0;

    const TYPE_SUBSIDY = 1; // 补贴提现
    const TYPE_COMMISSION = 2; // 佣金提现

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'money', 'tax', 'bank_name', 'account_name', 'account_no', 'create_time', 'status'], 'required'],
            ['bank_name', 'string', 'max' => 32],
            [['bank_address', 'account_name', 'account_no'], 'string', 'max' => 128],
            ['type', 'default', 'value'  => '2'],
            [['remark', 'type'], 'safe'],
        ];
    }

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }
}
