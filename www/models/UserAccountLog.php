<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户账户记录
 * Class UserAccountLog
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
 * @property int $total_sale_people_count 总销售人数
 * @property int $total_sale_count 总销量
 * @property int $person_sale_count 个人销量
 * @property int $sale_sale_count 销售销量
 * @property float $total_sale_money 总销售额
 * @property float $direct_manager_money 直接管理业绩【学豆左下角】
 * @property float $infinite_sale_manager_money 无限代销售管理业绩【学豆右下角】
 * @property float $direct_manager_detail_money 直接管理业绩【学豆收入详情】
 * @property float $two_sale_manager_money 2代销售管理业绩【学豆收入详情】
 * @property float $sale_bean_detail_money 学豆本月收入【学豆收入详情】
 * @property float $two_infinite_sale_money 2代以上销售管理业绩【团队间邀2代以上销售业绩】
 * @property int $bean_status 学豆发放情况【学豆收入详情】
 * @property string $detail_pics 凭证截图
 * @property int $team_sale_status 团队私发销售业绩提成结算状态
 * @property float $team_direct_manager_one_money 团队直接管理业绩提成【团队直邀1代销售业绩提成】
 * @property float $team_direct_manager_two_money 团队2代销售管理业绩提成【团队间邀2代销售业绩提成】
 * @property float $team_direct_manager_three_money 团队2代以上销售管理业绩提成【团队间邀2代以上销售业绩提成】
 * @property float $team_direct_manager_all_money 团队销售业绩提成总额
 * @property float $team_direct_manager_pay_all_money 团队私发销售业绩提成金额
 *
 * @property User $user 关联用户
 */
class UserAccountLog extends ActiveRecord
{
    const STATUS_ON = 1; // 正常已结算
    const STATUS_WAIT = 2; // 待结算
    const STATUS_DEL = 0; // 已删除

    const TEAM_STATUS_ON = 1; // 已结算
    const TEAM_STATUS_WAIT = 2; // 已对账
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['money', 'commission', 'level_money', 'prepare_level_money', 'subsidy_money', 'score',
                'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'], 'default', 'value' => 0],
            [['time', 'uid', 'remark', 'status',
                'total_sale_people_count',
                'total_sale_count',
                'person_sale_count',
                'sale_sale_count',
                'total_sale_money',
                'direct_manager_money',
                'infinite_sale_manager_money',
                'direct_manager_detail_money',
                'two_sale_manager_money',
                'sale_bean_detail_money',
                'two_infinite_sale_money',
                'bean_status',
                'detail_pics',
                'team_sale_status',

                'team_direct_manager_one_money',
                'team_direct_manager_two_money',
                'team_direct_manager_three_money',
                'team_direct_manager_all_money',
                'team_direct_manager_pay_all_money',
                ], 'safe'],
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
            'money' => '结算金额',
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
            'dec' => '十二月',
            'total_sale_people_count' => '总销售人数',
            'total_sale_count' => '总销量',
            'person_sale_count' => '个人销量',
            'sale_sale_count' => '销售销量',
            'total_sale_money' => '总销售额',
            'direct_manager_money' => '直接管理业绩【学豆左下角】',
            'infinite_sale_manager_money' => '无限代销售管理业绩【学豆右下角】',
            'direct_manager_detail_money' => '直接管理业绩【学豆收入详情】',
            'two_sale_manager_money' => '2代销售管理业绩【学豆收入详情】',
            'sale_bean_detail_money' => '学豆本月收入【学豆收入详情】',
            'two_infinite_sale_money' => '2代以上销售管理业绩【团队间邀2代以上销售业绩】',
            'bean_status' => '学豆发放情况【学豆收入详情】',
            'team_sale_status' => '团队私发销售业绩提成结算状态',
            'team_direct_manager_one_money' => '团队直接管理业绩提成【团队直邀1代销售业绩提成】',
    'team_direct_manager_two_money' => '团队2代销售管理业绩提成【团队间邀2代销售业绩提成】',
    'team_direct_manager_three_money' => '团队2代以上销售管理业绩提成【团队间邀2代以上销售业绩提成】',
    'team_direct_manager_all_money' => '团队销售业绩提成总额',
    'team_direct_manager_pay_all_money' => '团队私发销售业绩提成金额',
            'detail_pics' => '爱库存丝丝截图',
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

    /**
     * 关联订单
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'oid']);
    }


}
