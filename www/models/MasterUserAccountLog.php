<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户账户记录
 * Class MasterUserAccountLog
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $oid 订单id
 * @property float $money 现金
 * @property float $commission 佣金
 * @property integer $score 积分
 * @property float $level_money 等级现金 推荐获取的金额
 * @property float $prepare_level_money 预充值等待发展现金
 * @property float $subsidy_money 补贴金额
 * @property integer $time 时间
 * @property integer $create_time 创建时间
 * @property integer $status 状态
 * @property string $year 年份
 * @property string $jan 一月
 * @property string $feb 二月
 * @property string $mar 三月
 * @property string $apr 四月
 * @property string $may 五月
 * @property string $jun 六月
 * @property string $jul 七月
 * @property string $aug 八月
 * @property string $sep 九月
 * @property string $oct 十月
 * @property string $nov 十一月
 * @property string $dec 十二月
 *
 * @property User $user 关联用户
 */
class MasterUserAccountLog extends ActiveRecord
{
    const STATUS_ON = 1; // 正常已结算
    const STATUS_WAIT = 2; // 待结算
    const STATUS_DEL = 0; // 已删除
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['money', 'commission', 'level_money', 'prepare_level_money', 'score',
                'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'], 'default', 'value' => 0],
            [['time', 'uid', 'remark', 'status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '用户编号',
            'money' => '直邀店主销售额',
            'commission' => '佣金',
            'score' => '积分',
            'level_money' => '等级补贴金额',
            'prepare_level_money' => '预充值发展金额',
            'subsidy_money' => '补贴金额',
            'time' => '时间',
            'create_time' => '创建时间',
            'remark' => '备注',
            'status' => '状态',
            'jan' => '一月',
            'feb' => '二月',
            'mar' => '三月',
            'apr' => '四月',
            'may' => '五月',
            'jun' => '六月',
            'jul' => '七月',
            'aug' => '八月',
            'sep' => '九月',
            'oct' => '十月',
            'nov' => '十一月',
            'dec' => '十二月'
        ];
    }

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(MasterUser::className(), ['id' => 'uid']);
    }

    /**
     * 关联订单
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'oid']);
    }


}
