<?php

namespace app\modules\api\controllers;

use app\models\City;
use app\models\Goods;
use app\models\GoodsAttrValue;
use app\models\GoodsCategory;
use app\models\GoodsComment;
use app\models\GoodsCommentReply;
use app\models\GoodsExpress;
use app\models\GoodsSku;
use app\models\IpCity;
use app\models\Order;
use app\models\OrderItem;
use app\models\User;
use app\models\UserAccount;
use app\models\UserAccountLog;
use app\models\UserAddress;
use app\models\UserCommission;
use app\models\UserFavGoods;
use app\models\UserRecommend;
use app\models\UserSearchHistory;
use app\models\UserWeixin;
use app\models\Util;
use app\models\WeixinMpApi;
use app\modules\api\models\ErrorCode;
use http\Exception;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;

/**
 * 相关
 * Class GoodsController
 * @package app\modules\api\controllers
 */
class PublicController extends BaseController
{
    /**
     * 验证openid的身份
     * POST{
     *    open_id,
     * }
     */
    public function actionCheckUser()
    {
        $json = $this->checkJson([
            [['open_id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $type = 3;
        $token = '';
        $user = new \stdClass();
        /** @var UserWeixin $wx_user */
        $wx_user = UserWeixin::find()->where(['open_id' => $json['open_id']])->one();
        if ($wx_user) {
            $user = $wx_user->user;
            if ($user->status == User::STATUS_OK) {
                //正常微信自动登录
                $type = 1;
                try {
                    //$token = User::generateToken($this->client_api_version, $user->id);
                    $token = $user->generateToken($this->app_id);
                } catch (Exception $e) {
                    return [
                        'error_code' => ErrorCode::SERVER,
                        'message' => '无法生成用户Token。',
                    ];
                }
                if (isset($json['save_session']) && $json['save_session'] == 1) {
                    $a = Yii::$app->user->login($user, 86400 * 30);
                    var_dump($a);
                }
                var_dump($user);exit;
                $cookie = Yii::$app->request->cookies->get('invite');
                if (!empty($cookie)) {
                    // 推荐关系，需要保存到数据库中
                    $invite = preg_split('/\|/', $cookie->value, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($invite as $item) {
                        $item = explode(':', $item);
                        $invite_user = User::findOne($item[0]);
                        if (!empty($invite_user)) {
                            if (strpos($item[1], 's') === 0) {
                                UserRecommend::saveRecommend($invite_user->id, $user->id, substr($item[1], 1), null);
                            } elseif (strpos($item[1], 'g') === 1) {
                                UserRecommend::saveRecommend($invite_user->id, $user->id, null, substr($item[1], 1));
                            }
                        }
                    }
                    Yii::$app->response->cookies->remove('invite');
                }
            } elseif ($user->status == User::STATUS_WAIT) {
                //跳转绑定微信激活
                $type = 2;
            }
        } else {
            //跳转注册 并 自动登录
            $type = 3;
        }
        return ['type' => $type, 'token' => $token, 'uid' => isset($user->id)? $user->id : ''];
    }

    public function actionTest()
    {
        $user = User::findOne('8354');
        //var_dump($user->parent->id,$user->parent->parent->id,$user->parent->parent->parent->id,$user->parent->parent->parent->parent->id);
        /** @var User $a */
        $a = $user->tree($user->parent);
        var_dump($a->id, $a->real_name);
        $user->all_no_next_sub($user);
        exit;
        //直接邀请 补 成长值
        /** @var User $user */
        foreach (User::find()->where(['pid' => '2631'])->each() as $user) {
            $user->add_growth($user->id, $user->pid, 399, 1);
        }

        exit;
        //查出大于400积分的用户  1 统一改成400积分  2 只留一条记录
        /** @var UserAccount $account */
        foreach (UserAccount::find()->where(['>', 'score', '400'])->each() as $account) {
            echo '大于400：'. $account->uid. '<br>';
            $account->score = 400;
            $account->save();
            $logId = UserAccountLog::find()->orderBy('id asc')->limit('1')
                ->where(['uid' => $account->uid])
                ->andWhere(['score' => '400'])
                ->one();
            if (!empty($logId)) {
                /** @var UserAccountLog $log */
                foreach (UserAccountLog::find()->where(['<>', 'id', $logId['id']])->andWhere(['uid' => $account->uid])
                             ->andWhere(['score' => '400'])
                             ->each() as $log) {
                    echo '删除的'.$log->uid. ' log_id:'. $log->id.  '<br>';
                    $log->delete();
                }
            }
        }
        //查出激活没积分的统一给400积分
        /** @var User $user */
        foreach(User::find()->where(['status' => 1])->each() as $user) {
            if ($user->account->score == 0 || empty($user->account->score)) {
                $user->account->score = 0;
                $user->account->save();
                echo '新增的'.$user->id . '<br>';
                $r = UserAccount::updateAllCounters(['score' => 400], ['uid' => $user->id]);
                if ($r <=0) {
                    var_dump('更新积分失败。'.$user->id);return;
                }
                $logs = new UserAccountLog();
                $logs->uid = $user->id;
                $logs->score = 400;
                $logs->time = time();
                $logs->remark = '激活会员统一给400积分';
                $logs->save();
            }else {
                continue;
            }
        }
        exit;

        // $arr = [2781 ,2475 ,2310 ,2382 ,2597 ,2766 ,2585 ,2326 ,2304 ,2313 ,2572 ,2573 ,2574 ,2319 ,2325 ,2587 ,2598 ,2599 ,2600 ,2601 ,2602 ,2363 ,2374 ,2375 ,2376 ,2379 ,2635 ,2381 ,2426 ,2431 ,2451 ,2452 ,2714 ,2461 ,2476 ,2480 ,2486 ,2492 ,2754 ,2756 ,2508 ,2509 ,2771 ,2527 ,2783 ,2788 ,2557 ,2558 ,2303 ,2563 ,2378 ,2408 ,2683 ,2716 ,2532 ,4340 ,2314 ,2637 ,2430 ,2691 ,2717 ,2761 ,2516 ,3048 ,2570 ,2588 ,2590 ,2591 ,2428 ,2429 ,2498 ,2757 ,2775 ,2530 ,2577 ,2384 ,2414 ,2450 ,2479 ,2481 ,2760 ,2511 ,2514 ,2778 ,2315 ,2355 ,2614 ,2377 ,2404 ,2407 ,2722 ,2502 ,2762 ,2539 ,2544 ,2569 ,2333 ,2604 ,2359 ,2425 ,2703 ,2504 ,2513 ,2578 ,3353 ,2335 ,2344 ,2362 ,2634 ,2680 ,2702 ,2493 ,2758 ,2506 ,2770 ,2529 ,2538 ,2653 ,2444 ,2715 ,2718 ,2477 ,2531 ,2540 ,2555 ,2560 ,2306 ,2307 ,2311 ,2568 ,2582 ,2586 ,2592 ,2616 ,2618 ,2621 ,2383 ,2652 ,2403 ,2484 ,2497 ,2499 ,2773 ,2534 ,3564 ,2553];
        $childUser = User::find()->where(['mobile' => '18639345839'])->one();
        var_dump($childUser);
        if (empty($childUser)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '该手机号未注册。',
            ];
        }
        exit;
        $api = new WeixinMpApi();

        /** @var UserWeixin $wx */
        foreach (UserWeixin::find()->orderBy('union_id asc, id desc')->each() as $wx) {
            echo strpos($wx->open_id, 'oY');
            if (strpos($wx->open_id, 'oY') !== false) {
                continue;
            }
            $token = $api->getAccessToken();
            echo $token . chr(9) . '========'. $wx->open_id;
            $user_info = $api->getInfo2($token,$wx->open_id);
            var_dump($user_info);
            echo chr(9);//exit;
            if (isset($user_info['unionid'])) {
                $wx->union_id = $user_info['unionid'];
                if (empty($wx->user->nickname) && isset($user_info['nickname'])) {
                    $user = $wx->user;
                    $user->nickname = $user_info['nickname'];
                    $user->save();
                }
                if (empty($wx->user->avatar) && isset($user_info['headimgurl'])) {
                    $user = $wx->user;
                    $user->avatar = $user_info['headimgurl'];
                    $user->save();
                }
                $wx->save();
            }
            echo $wx->id. 'end';
        }

        exit;
        $arr = [4272, 4273, 4274, 4279, 4280, 4281, 4282, 4283, 4284, 4285, 4286, 4287, 4288, 4289, 4290, 4291, 4292, 4293, 4294, 4295, 4296, 4297, 4298, 4299, 4300, 4301, 4302, 4303, 4304, 4305, 4306, 4307, 4308, 4309, 4310, 4311, 4312, 4313, 4314, 4315, 4316, 4317, 4318, 4319, 4320, 4321, 4322, 4323, 4324, 4325, 4326, 4327, 4328, 4329, 4330, 4331, 4332, 4333, 4334, 4335, 4336, 4337, 4338, 4339, 4340, 4341, 4342, 4343, 4344, 4345, 4346, 4347, 4348, 4349, 4350, 4351, 4352, 4353, 4354, 4355, 4356, 4357, 4358, 4359, 4360, 4361, 4362, 4363, 4364, 4365];
        foreach ($arr as $val) {
            echo $val . chr(10);
            $user = User::findOne($val);
            echo $user->real_name;
            $address = new UserAddress();
            $address->uid = $val;
            $address->name = $user->real_name;
            $address->address = '中关村软件园A座';
            $address->area = '371300';
            $address->mobile = $user->mobile;
            $address->status = 1;
            $address->create_time = time();
            $address->save();
            var_dump($address->save() . chr(9));
        }
        exit;
//        $list = Order::find()->joinWith('order_item')->where(['>=', '{order.status}', 2])->all();
//        exit;
//        //所有激活会员给400积分
//        /** @var User $user */
//        foreach (User::find()->where(['status' => 1])->orderBy('id asc')->each() as $user) {
//            if ($user->account->score < 400) {
//                echo $user->id . 'start' .chr(9);
//                $user->updateScore();
//                echo $user->id . 'end' .chr(9);
//            }
//        }
//        exit;

        $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")));
        $monthStartTime = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
        $monthEndTime = strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")))+86399;

        //$BeginDate = date('Y-m-01', strtotime('-1 month'));
        //$monthStartTime = strtotime($BeginDate);
        //$monthEndTime = strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")))+86399;
        /** @var User $user */
        /** @var UserCommission $commission */
        foreach (UserCommission::find()->joinWith('user')->groupBy('uid')->where(['>=', 'time', $monthStartTime])->andWhere(['<=', 'time', $monthEndTime])->orderBy('level_id asc, user.id desc')->each() as $commission) {
            $user = $commission->user;
            echo $user->id . 'start <br>';
            #先给直接上级
            if (!$user->parent) {
                Yii::warning('没有上级 ' .$user->id);
                continue;
            }
            if ($user->parent->level_id < $user->level_id) { // 如果上级比自己等级低 不再给上级
                Yii::warning('上级id ' . $user->parent->id . '上级level_id ' .$user->parent->level_id . ' 自身id ' . $user->id . '自身level_id' . $user->level_id);
                continue;
            }
            $toUser = $user->parent;
            if ($toUser->status == User::STATUS_WAIT) {
                continue;
            }
            // 普通会员直接给上级 30%
            // 店主或者服务商 是月结算给上级 30%
            $query = UserCommission::find();
            $query->where(['>=', 'time', $monthStartTime])
                ->andWhere(['<=', 'time', $monthEndTime])->andWhere(['uid' => $user->id]);
            if ($user->level_id == 1) {
                //$query->andWhere(['type' => UserCommission::TYPE_FIRST]);
            } elseif ($user->level_id == 2 || $user->level_id == 3) {
                //$query->andWhere(['type' => UserCommission::TYPE_MONTH]);
            }
            $commissionMoney = $query->sum('commission');
            $money = $commissionMoney * 30 /100;
            if (Util::comp($money, 0, 2) > 0) {
                $r = UserAccount::updateAllCounters(['commission' => $money], ['uid' => $toUser->id]);
                if ($r <= 0) {
                    //throw new Exception('无法更新用户账户：' . $toUser->id . ' commission ' . $money);
                    var_dump('无法更新用户账户：' . $toUser->id . ' commission ' . $money);
                }
                $ual = new UserAccountLog();
                $ual->uid = $toUser->id;
                $ual->commission = $money;
                $ual->time = time();
                $ual->remark = '直接1级月结佣金';
                $r = $ual->save();
                if (!$r) {
                    //throw new Exception('无法保存用户账户记录：' . print_r($ual->attributes, true) . print_r($ual->errors, true));
                    var_dump('无法保存用户账户记录：' . print_r($ual->attributes, true) . print_r($ual->errors, true));
                }

                $userCommission = new UserCommission();
                $userCommission->uid = $toUser->id;
                $userCommission->from_uid = $user->id;
                $userCommission->commission = $money;
                $userCommission->type = UserCommission::TYPE_MONTH;
                $userCommission->time = time();
                $userCommission->remark = '直接下级月结佣金返佣30%';
                $r = $userCommission->save();
                if (!$r) {
                    //throw new Exception('无法保存用户佣金记录：' . print_r($userCommission->attributes, true) . print_r($userCommission->errors, true));
                    var_dump('无法保存用户佣金记录：' . print_r($userCommission->attributes, true) . print_r($userCommission->errors, true));
                }
                Yii::warning('直接1级佣金 ' .$user->id . '给 ' . $toUser->id . '月佣金');
            }
            #团队上级
            if ($user->level_id == 1 && $user->parent->level_id == 1) {
                $teamUser = $user->tree($user->parent);
                if ($teamUser->status == User::STATUS_WAIT) {
                    continue;
                }
                $commissionMoney = UserCommission::find()->where(['>=', 'time', $monthStartTime])
                    ->andWhere(['type' => UserCommission::TYPE_MONTH])
                    ->andWhere(['<=', 'time', $monthEndTime])->andWhere(['uid' => $user->id])->sum('commission');
                $money = $commissionMoney * 30 /100;
                if (Util::comp($money, 0, 2) > 0) {
                    $r = UserAccount::updateAllCounters(['commission' => $money], ['uid' => $teamUser->id]);
                    if ($r <= 0) {
                        //throw new Exception('无法更新用户账户：' . $teamUser->id . ' commission ' . $money);
                        var_dump('无法更新用户账户：' . $teamUser->id . ' commission ' . $money);
                    }
                    $ual = new UserAccountLog();
                    $ual->uid = $teamUser->id;
                    $ual->commission = $money;
                    $ual->time = time();
                    $ual->remark = '直属会员团队月结';
                    $r = $ual->save();
                    if (!$r) {
                        //throw new Exception('无法保存用户账户记录：' . print_r($ual->attributes, true) . print_r($ual->errors, true));
                        var_dump('无法保存用户账户记录：' . print_r($ual->attributes, true) . print_r($ual->errors, true));
                    }

                    $userCommission = new UserCommission();
                    $userCommission->uid = $teamUser->id;
                    $userCommission->from_uid = $user->id;
                    $userCommission->commission = $money;
                    $userCommission->type = UserCommission::TYPE_MONTH;
                    $userCommission->time = time();
                    $userCommission->remark = '团队会员月佣金返佣30%';
                    $r = $userCommission->save();
                    if (!$r) {
                        //throw new Exception('无法保存用户佣金记录：' . print_r($userCommission->attributes, true) . print_r($userCommission->errors, true));
                        var_dump('无法保存用户佣金记录：' . print_r($userCommission->attributes, true) . print_r($userCommission->errors, true));
                    }
                    Yii::warning('直属会员团队佣金 ' .$user->id . '给 ' . $toUser->id . '月佣金');
                }

            }

        }
        exit;
        //月结测试
        $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
        $monthStartTime = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
        $monthEndTime = strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")))+86399;


        $query = UserCommission::find();
        $list = $query->asArray()->joinWith('user')->groupBy('uid')->where(['>=', 'time', $monthStartTime])->andWhere(['<=', 'time', $monthEndTime])->orderBy('level_id asc')->all();
        foreach ($list as $key => $val) {
            echo $val['user']['id'] . '==' . $val['user']['level_id']. chr(9);
        }
        //$query = Order::task_create_month_commission_log();
        exit;

        $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
        $monthStartTime = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
        $monthEndTime = strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")))+86399;
        $query = UserCommission::find();
        $list = $query->joinWith('user')->where(['>=', 'time', $monthStartTime])->andWhere(['<=', 'time', $monthEndTime])->orderBy('level_id asc')->all();
        echo $query->createCommand()->getRawSql();
        /** @var UserCommission $commission */
        foreach (UserCommission::find()->joinWith('user')->where(['>=', 'time', $monthStartTime])->andWhere(['<=', 'time', $monthEndTime])->orderBy('level_id asc, user.id desc')->each() as $commission) {
            $user = $commission->user;
            $fromUser = $commission->fromUser;
            #先给直接上级
            var_dump($user->id, $fromUser->id);
            echo '<br>';
            if (!$user->parent) {
                continue;
            }
            $toUser = $user->parent;
        }

    }
}
