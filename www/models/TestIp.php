<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户账户
 * Class TestIp
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property float $money 现金
 * @property float $commission 佣金
 * @property float $level_money 等级金额  补贴发的金额
 * @property float $prepare_level_money 预充值等级金额
 * @property float $subsidy_money 补贴金额
 * @property integer $score 积分
 */
class TestIp extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip', 'create_time'], 'safe'],
        ];
    }
}
