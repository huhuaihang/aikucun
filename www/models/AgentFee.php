<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 代理费用设置
 * Class AgentFee
 * @package app\models
 *
 * @property integer $id PK
 * @property string $area 区域编码
 * @property float $initial_fee 加盟费
 * @property float $earnest_money 保证金
 */
class AgentFee extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['area'], 'required'],
            [['area'], 'unique'],
            [['initial_fee', 'earnest_money'], 'filter', 'filter' => 'floatval'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'earnest_money' => '保证金',
            'initial_fee' => '加盟费用',
            'area' => '地区',
        ];
    }
}
