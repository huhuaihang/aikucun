<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * 积分规则配置
 * Class UserScoreLog
 * @package app\models
 *
 * @property integer $id PK、
 * @property integer $uid 用户编号
 * @property string $code 积分获得事件 sign 签到|register 注册|active 激活|share 分享|login 登录 |handle 手动发放 | signReword 签到奖励
 * @property integer $score 触发事件奖励积分
 * @property integer $from_uid 来源用户编号
 * @property integer $create_time 时间
 * @property string $remark 来源说明
 */
class UserScoreLog extends ActiveRecord
{
    const  HANDLE ='handle';//手动发放
    const  SIGN ='sign';//签到
    const  SIGN_REWORD ='signReword';//签到奖励

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'score' ,'create_time'], 'required'],
//            [['score'], 'compare', 'compareValue' => 0, 'operator' => '>'],
            [['from_uid','code','remark'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => '用户编号',
            'code' => '事件类型',
            'score' => '奖励积分',
            'create_time' => '创建时间',
            'remark' => '备注',
            'from_uid' =>'来源用户编号',
        ];
    }

    /**
     * 获取是否最后一天  发放奖励
     * @param $uid
     * @return bool
     */
    public static function checkSignReword($uid)
    {
        $beginDate=date('Y-m-01', strtotime(date("Y-m-d")));
        $endDate = date('Y-m-d', strtotime("$beginDate +1 month -1 day"));
        $allCount = UserScoreLog::find()->andWhere(['BETWEEN', 'create_time', strtotime($beginDate), strtotime($endDate)])
                    ->andWhere(['code' => UserScoreLog::SIGN])
                    ->andWhere(['uid' => $uid])->count();

        if (Yii::$app->params['site_host'] == 'http://yuntaobang.ysjjmall.com') {
            $test_user = [2781, 2783, 2784, 2785, 2786, 2787, 2788, 2789, 4543,2458,2464,2553,2613,2732,2733,2750,2879,
                2998,3147,3188,3207,3320,3364,3372,3410,3421,3487,3491,3597,3683,3723,3770,3783,3792,3823,3850,3851,3949,
                3979,4106,4492,4493,4494,4495,4496,4497,4498,4499,4500,4501,4502,4503,4504,4505,4506,4507,4508,4509,4510,
                4511,4512,4513,4514,4515,4516,4517,4518,4519,4520,4521,4522,4523,4524,4525,4527,4528,4529,4530,4531,4532,4533];
            if (in_array($uid, $test_user)) {
                $allCount = UserScoreLog::find()->andWhere(['BETWEEN', 'create_time', strtotime($beginDate), strtotime($endDate)])
                    ->andWhere(['code' => UserScoreLog::SIGN])
                    ->andWhere(['uid' => $uid])->count();
                if ($allCount == date('t')) {
                    return true;
                }
            }
        }
        $nowDate = date('Y-m-d', time());
        if (($endDate == $nowDate) && $allCount == date('t')) {
            return true;
        } else {
            return false;
        }
    }
}
