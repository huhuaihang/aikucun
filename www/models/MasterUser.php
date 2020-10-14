<?php

namespace app\models;

use phpDocumentor\Reflection\Types\Null_;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * 店主
 * Class MasterUser
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $pid 上级编号
 * @property integer $team_pid 团队上级编号
 * @property integer $jpush_id 极光编号
 * @property string $invite_code 邀请码
 * @property string $mobile 手机号
 * @property string $auth_key
 * @property string $password HASH密码
 * @property string $payment_password 支付密码
 * @property string $real_name 真实姓名
 * @property string $nickname 昵称
 * @property string $wx_no 微信号
 * @property integer $gender 性别
 * @property string $avatar 头像
 * @property string $shop_name 店铺名称
 * @property integer $prepare_count 预购数量
 * @property integer $hand_count 手中囤货数量
 * @property integer $status 状态
 * @property integer $level_id 等级
 * @property float $subsidy_money 补贴金额
 * @property float $growth_money 补贴金额
 * @property integer $create_time 创建时间
 * @property integer $handle_time 激活时间
 * @property integer $is_handle 是否后台手动激活
 * @property integer $is_per_handle 是否前台店主售卖服务商激活
 * @property integer $is_self_active 是否前台自己购买激活
 * @property integer $is_package_coupon_active 是否前台购买卡券激活
 * @property integer $is_invite_active 是否邀请激活
 *
 * @property MasterUserAccount $account 关联用户账户
 * @property MasterUser $childList 关联自己的下一级
 * @property MasterUser $parent 关联自己的父级
 * @property MasterUser $team_parent 关联自己团队的父级
 * @property MasterUser $teamParents 关联自己团队的父级
 * @property MasterUserLevel $userLevel 获取自己的用户等级
 * @property MasterUserCard $card 获取自己的会员卡
 * @property MasterUserWithdraw $cumulative 关联提现金额
 * @property MasterUserCommission $computeCommission 关联计算预估收益金额
 * @property $buyRatio 关联我的自购省钱比率
 * @property $childBuyRatio 关联我的直接下级购买返佣给我比率
 * @property $saleCount 关联我的销售记录总数
 * @property $teamCount 关联我的团队会员总数
 * @property $teamActiveCount 关联我的团队会员激活总数
 * @property $teamNotActiveCount 关联我的团队会员未激活总数
 * @property array $signList 关联用户本月签到列表
 */
class MasterUser extends ActiveRecord implements IdentityInterface
{
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    const GENDER_UNKNOWN = 0;
    const GENDER_SECRET = 9;

    const STATUS_OK = 1;
    const STATUS_WAIT = 2;
    const STATUS_STOP = 9;
    const STATUS_DELETE = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile'], 'required'],
            [['mobile', 'nickname'], 'string', 'max' => 32],
            [['gender'], 'default', 'value' => 0],
            ['gender', 'in', 'range' => [MasterUser::GENDER_MALE, MasterUser::GENDER_FEMALE, MasterUser::GENDER_UNKNOWN, MasterUser::GENDER_SECRET]],
            [['team_pid', 'level_id', 'prepare_count', 'real_name', 'wx_no', 'pid', 'shop_name', 'create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
//            // 生成昵称
//            while (true) {
//                $nickname = 'ytb_' . Util::randomStr(8, 5);
//                if (MasterUser::find()->andWhere(['nickname' => $nickname])->exists()) {
//                    continue;
//                }
//                $this->nickname = $nickname;
//                break;
//            }
            // 生成唯一邀请码
            while (true) {
                $invite_code = Util::randomStr(6, 5);
                if (MasterUser::find()->andWhere(['invite_code' => $invite_code])->exists()) {
                    continue;
                }
                $this->invite_code = $invite_code;
                break;
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'real_name' => '真实姓名',
            'nickname' => '微信昵称',
            'password' => '密码',
            'level_money' => '等级金额',
            'prepare_level_money' => '实际等级金额',
            'mobile' => '手机号',
            'remark' => '备注',
            'prepare_count' => '预购剩余数量',
            'hand_count' => '手中真实囤货数量',
            'pid' => '上级编号',
            'team_pid' => '实际上级编号',
            'wx_no' => '微信号',
            'shop_name' => '店铺名称',
            'level_id' => '等级',
            'create_time' => '注册时间',
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            // 生成账户信息
            $user_account = new MasterUserAccount();
            $user_account->uid = $this->id;
            $user_account->save();
            $user_account_log = new MasterUserAccountLog();
            $user_account_log->uid = $this->id;
            $user_account_log->save();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return MasterUser::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * 生成接口Token
     * @param $api_version string 接口版本号
     * @param $uid integer 用户编号
     * @return string
     * @throws Exception
     */
    public static function generateTokenVersion($api_version, $uid)
    {
        $data = json_encode([
            'uid' => $uid,
            'create_time' => time(),
        ]);
        $encoded_token = SystemVersion::aesEncode($api_version, $data);
        $token = base64_encode($encoded_token);
        return $token;
    }

    /**
     * 根据接口Token返回用户
     * @param $api_version string 接口版本号
     * @param $token string 客户端提交的Token
     * @return MasterUser
     * @throws Exception
     */
    public static function findByTokenVersion($api_version, $token)
    {
        $data = SystemVersion::aesDecode($api_version, $token);
        if (empty($data)) {
            throw new Exception('Token错误。');
        }
        $json = json_decode($data, true);
        if (empty($json)) {
            throw new Exception('Token格式错误。');
        }
        $uid = $json['uid'];
        return MasterUser::findOne($uid);
    }

    /**
     * 生成接口Token
     * @param $appId string 客户端AppId
     * @return string
     */
    public function generateToken($appId)
    {
        $apiClient = ApiClient::findByAppId($appId);
        return Util::makeJWT([
            'iat' => time(),
            'uid' => $this->id,
            'app' => $appId,
        ], $apiClient->app_secret);
    }

    /**
     * 根据接口Token返回用户
     * @param $token string 客户端提交的Token
     * @return MasterUser || array
     * @throws Exception
     */
    public static function findByToken($token)
    {
        try {
            $payloadJson = Util::checkJWT($token);
            return MasterUser::findOne(['id' => $payloadJson['uid']]);
        } catch (Exception $e) {
            return ['error_code' => 11001, 'message' => $e->getMessage()];
            //throw new Exception('TOKEN', $e->getMessage());
        }
    }

    /**
     * 验证密码
     * @param string $password 明文密码
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * 验证支付密码
     * @param string $password 明文密码
     * @return bool
     */
    public function validatePaymentPassword($password)
    {
        return !empty($this->payment_password) && Yii::$app->security->validatePassword($password, $this->payment_password);
    }

    /**
     * 关联用户账户
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(MasterUserAccount::className(), ['uid' => 'id']);
    }

    /**
     * 返回可访问头像地址
     * @param $scheme boolean 是否返回完整的可用于外链的地址
     * @return string
     */
    public function getRealAvatar($scheme = false)
    {
        if (empty($this->avatar)) {
            return ($scheme ? Yii::$app->params['site_host'] : '') . '/images/user_icon_03.png';
        }
        if (preg_match('/^http/', $this->avatar)) {
            return $this->avatar;
        }
        return ($scheme ? Yii::$app->params['site_host'] : '') . Yii::$app->params['upload_url'] . $this->avatar;
    }

    /**
     * 返回下级列表
     * @return array|ActiveRecord[]
     */
    public function getChildList()
    {
        return MasterUser::find()->andWhere(['pid' => $this->id])->andWhere(['IN', 'status', [MasterUser::STATUS_OK, MasterUser::STATUS_WAIT]])->all();
    }

    /**
     * 关联用户会员卡
     * @return \yii\db\ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(MasterUserCard::className(), ['uid' => 'id'])->where(['status' => MasterUserCard::STATUS_OK]);
    }

    /**
     * 获取用户等级
     */
    public function getMasterUserLevel()
    {
        return $this->hasOne(MasterUserLevel::className(), ['id' => 'level_id']);
//        return MasterUserLevel::find()->asArray()->select(['id', 'money', 'name'])->andWhere(['id' => $this->level_id, 'status' => MasterUserLevel::STATUS_OK])->one();
//        $level = MasterUserLevel::find()
//            ->select(['id', 'money', 'name'])->orderBy('money desc')
//            ->andWhere(['status' => MasterUserLevel::STATUS_OK])
//            ->andWhere(['<=', 'money', $this->account->level_money])
//            ->asArray()->one();
//        return $level;
    }

    /**
     * 获取用户父级
     */
    public function getParent()
    {
        //return MasterUser::find()->andWhere(['id' => $this->pid])->one();
        return User::find()->andWhere(['id' => $this->pid])->one();
        //return MasterUser::find()->andWhere(['id' => $this->pid, 'status' => MasterUser::STATUS_OK])->one();
    }

    /**
     * 获取用户团队父级
     * @param $team_pid int
     * @return MasterUser | null | mixed
     */
    public function getTeamParent($team_pid)
    {
        return User::find()->andWhere(['id' => $team_pid])->one();
        //return MasterUser::find()->andWhere(['id' => $team_pid])->one();
    }

    /**
     * 获取用户团队父级
     * @return MasterUser | null | mixed
     */
    public function getTeamParents()
    {
        return MasterUser::find()->where(['id' => $this->team_pid])->one();
    }

    /**
     * 升级  或者 购买 给相应的用户贴
     * @param $user MasterUser
     * @return true
     */
    public function subsidy($user)
    {
        //先把三个上级都找出来  看看是否需要  给补贴
        //给补贴 则往 user_subsidy 插入记录  并且更新每个上级的 销售补贴总额
        //直接1级
        Yii::warning($user);
        Yii::warning($user->parent);
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            Yii::warning($p1->userLevel->id);
            Yii::warning($p1->userLevel);
            if ($p1->userLevel->id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->userLevel->id == 2) {
                $this->add_sub($from_id, $p1->id, 200, 1);
            }
            if ($p1->userLevel->id == 3) {
                $this->add_sub($from_id, $p1->id, 250, 1);
            }
        }

        //直接2级
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }
            if ($p2->userLevel->id == 2) {
                $this->add_sub($from_id,$p2->id, 100, 2);
            }
            if ($p2->userLevel->id == 3) {
                $this->add_sub($from_id,$p2->id, 150, 2);
            }
        }

        //直接3级
        if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
            $p3 = $this->parent->parent->parent;
//            if ($p3->userLevel->id == 1) {
//                //$this->add_sub($p3->id, 33, 2);
//            }
            if ($p3->userLevel->id == 2) {
                $this->add_sub($from_id, $p3->id, 15, 3);
            }
            if ($p3->userLevel->id == 3) {
                $this->add_sub($from_id, $p3->id, 50, 3);
            }
        }
        return true;
    }

    /**
     * 前台购买激活
     * @param $user MasterUser
     * @return bool
     */
    public function all_sub($user)
    {
        //先把三个上级都找出来  看看是否需要  给补贴
        //给补贴 则往 user_subsidy 插入记录  并且更新每个上级的 销售补贴总额
        //上级是普通会员 上上级也是普通会员 再找上上上级  一直找到店主 或者 服务商
        //上级是店主 上上是否店主 是 给上上级补贴  不是 不给补贴 或者  上上级是否服务商 是给补贴  不是不给
        //上级是服务商 上上级
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            if ($p1->userLevel->id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->userLevel->id == 2) {
                $this->add_sub($from_id, $p1->id, 200, 1);
            }
            if ($p1->userLevel->id == 3) {
                $this->add_sub($from_id, $p1->id, 250, 1);
            }
        }
        $is_three = 0;
        //直接2级
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }
            if ($p2->userLevel->id == 2) {
                $is_three = 1;
                $this->add_sub($from_id,$p2->id, 100, 2);
            }
            if ($p2->userLevel->id == 3) {
                $is_three =1;
                $this->add_sub($from_id,$p2->id, 150, 2);
            }
        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
                $p2 = $user->parent->parent;
                $p3 = $this->parent->parent->parent;
                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    $this->add_sub($from_id, $p3->id, 15, 3);
                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    $this->add_sub($from_id, $p3->id, 50, 3);
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    $this->add_sub($from_id, $p3->id, 10, 3);
                }
            }
        }

        //不是直接3级  无限往上找 找到店主 或者 服务商
        if ($user->level_id == 1 && (!empty($user->parent->parent) && $user->parent->parent->level_id ==1)) {
            //无限找上级
            $parent = $this->tree($user->parent);
            if ($parent->level_id == 2) {
                $this->add_sub($from_id,$parent->id, 100, 4);
            }
            if ($parent->level_id == 3) {
                $this->add_sub($from_id,$parent->id, 150, 4);
            }
            if (in_array($parent->level_id, [2,3]) && !empty($parent->parent) && $parent->parent->level_id == 2) {
                $this->add_sub($from_id,$parent->parent->id, 15, 4);
            }
            if (in_array($parent->level_id, [2,3]) && !empty($parent->parent) && $parent->parent->level_id == 3) {
                $this->add_sub($from_id,$parent->parent->id, 10, 4);
            }
        }
        return true;
    }

    /**
     * 前台购买激活 上级出现会员就脱离关系
     * @param $user MasterUser
     * @return bool
     */
    public function all_no_next_sub($user)
    {
        //如果上级都没激活以上所有都不给补贴
        if (empty($user->parent) || $user->parent->status == MasterUser::STATUS_WAIT) {
            return true;
        }
        //先把三个上级都找出来  看看是否需要  给补贴
        //给补贴 则往 user_subsidy 插入记录  并且更新每个上级的 销售补贴总额
        //上级是普通会员 上上级也是普通会员 再找上上上级  一直找到店主 或者 服务商
        //上级是店主 上上是否店主 是 给上上级补贴  不是 不给补贴 或者  上上级是否服务商 是给补贴  不是不给
        //上级是服务商 上上级
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            if ($p1->userLevel->id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->userLevel->id == 2) {
                $this->add_sub($from_id, $p1->id, 200, 1);
            }
            if ($p1->userLevel->id == 3) {
                $this->add_sub($from_id, $p1->id, 250, 1);
            }
            //发送短信
            //$content = "亲爱的云淘帮会员您好！您邀请的粉丝" . $user->real_name . "已经成功激活，补贴金已经发放到“我的补贴”中，请登录APP查看。";
            $content = $user->real_name;
            Sms::send(Sms::U_TYPE_USER, $p1->id, Sms::TYPE_ACTIVE_NOTICE, $p1->mobile, $content);
        }
        if (!empty($user->parent->parent) && !empty($user->parent->parent) && $user->parent->parent->level_id == 1 && $user->parent->level_id != 1) {
            return true;
        }
        $is_three = 0;
        //直接2级
        if (empty($user->parent->parent) || $user->parent->parent->status == MasterUser::STATUS_WAIT) {
            return true;
        }
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
                if ($p2->parent && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
            }

            if ($p2->userLevel->id == 2) {
                if ($p2->level_id == 2 && $p2->parent && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                if ($user->parent->level_id == 2) {
                    $this->add_sub($from_id,$p2->id, 15, 2);
                }
                if ($user->parent->level_id == 1) {
                    $this->add_sub($from_id,$p2->id, 100, 2);
                }

            }
            if ($p2->userLevel->id == 3) {
                if ($p2->level_id == 3 && $p2->parent && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }
                if ($user->parent->level_id ==2 && $p2->userLevel->id == 3) {
                    $this->add_sub($from_id, $p2->id, 50, 2);
                }
                if ($user->parent->level_id ==3 && $p2->userLevel->id == 3) {
                    $this->add_sub($from_id, $p2->id, 10, 2);
                }
                if ($user->parent->level_id ==1 && $p2->level_id == 3) {
                    $this->add_sub($from_id,$p2->id, 150, 2);
                }
            }

            if (in_array($user->parent->level_id, [2,3]) && in_array($user->parent->parent->level_id, [2,3])) {
                if ($user->id == '8354') {
                    Yii::warning('runing');
                }
                $is_three = 0;
            }
        }
        if ($user->id == '8354') {
            Yii::warning($is_three);
            Yii::warning($user->parent->level_id . '=>>'.  $user->parent->parent->level_id  . '=>>'. $user->parent->parent->parent->level_id. '=>>'.$user->parent->parent->parent->id);
        }
        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if (empty($user->parent->parent->parent) || $user->parent->parent->parent->status == MasterUser::STATUS_WAIT) {
                return true;
            }
            if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
                $p2 = $user->parent->parent;
                //$p3 = $this->parent->parent->parent;
                $p3 = $user->parent->parent->parent;
                if ($user->id == '8354') {
                    Yii::warning('3jijiji8354开始啦');
                    Yii::warning($user->parent->level_id . '=>>'.  $user->parent->parent->level_id  . '=>>'. $user->parent->parent->parent->level_id. '=>>'.$user->parent->parent->parent->id);
                }
                if ($p2->level_id ==1 && $p3->level_id == 2) {
                    $this->add_sub($from_id, $p3->id, 100, 3);
                }
                if ($p2->level_id ==1 && $p3->level_id == 3) {
                    $this->add_sub($from_id, $p3->id, 150, 3);
                }
                if (!in_array($p2->level_id, [2, 3]) && in_array($p3->level_id, [2,3]) && !empty($p3->parent) && $p3->parent->level_id == 2) {
                    $this->add_sub($from_id,$p3->parent->id, 15, 4);
                }
                if (!in_array($p2->level_id, [2, 3]) && in_array($p3->level_id, [2]) && !empty($p3->parent) && $p3->parent->level_id == 3) {
                    $this->add_sub($from_id,$p3->parent->id, 50, 4);
                }
                if (!in_array($p2->level_id, [2, 3]) && in_array($p3->level_id, [3]) && !empty($p3->parent) && $p3->parent->level_id == 3) {
                    $this->add_sub($from_id,$p3->parent->id, 10, 4);
                }

                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    $this->add_sub($from_id, $p3->id, 15, 3);
                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    $this->add_sub($from_id, $p3->id, 50, 3);
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    $this->add_sub($from_id, $p3->id, 10, 3);
                }
            }
        }

        if ($user->id == '8354') {
            Yii::warning('8354开始啦');
            Yii::warning($user->parent->level_id . '=>>'.  $user->parent->parent->level_id  . '=>>'. $user->parent->parent->parent->level_id. '=>>'.$user->parent->parent->parent->id);
        }
        //不是直接3级  无限往上找 找到店主 或者 服务商
//        if ($user->parent->level_id == 1 && (!empty($user->parent->parent) && $user->parent->parent->level_id ==1)
//            && (!empty($user->parent->parent->parent) && $user->parent->parent->parent->level_id ==1)) {
        if ($user->parent->level_id == 1 && $user->parent->parent->level_id == 1 && $user->parent->parent->parent->level_id == 1) {
            //无限找上级
            $parent = $this->tree($user->parent);
            if ($user->id == '8354') {
                Yii::warning($parent->real_name . '===' . $parent->id);
            }
            if (!empty($parent)) {
                if ($parent->level_id == 2) {
                    $this->add_sub($from_id,$parent->id, 100, 4);
                }
                if ($parent->level_id == 3) {
                    $this->add_sub($from_id,$parent->id, 150, 4);
                }
                if (in_array($parent->level_id, [2,3]) && !empty($parent->parent) && $parent->parent->level_id == 2) {
                    $this->add_sub($from_id,$parent->parent->id, 15, 4);
                }
                if (in_array($parent->level_id, [2]) && !empty($parent->parent) && $parent->parent->level_id == 3) {
                    $this->add_sub($from_id,$parent->parent->id, 50, 4);
                }
                if (in_array($parent->level_id, [3]) && !empty($parent->parent) && $parent->parent->level_id == 3) {
                    $this->add_sub($from_id,$parent->parent->id, 10, 4);
                }
            }
        }

        // 给成长值  看看是否自动升级
        $this->strong_level($user);
        return true;
    }

    /**
     * 前台购买激活 上级出现会员就脱离关系
     * @param $user MasterUser
     * @return bool
     */
    public function all_no_next_sub_bak($user)
    {
        //先把三个上级都找出来  看看是否需要  给补贴
        //给补贴 则往 user_subsidy 插入记录  并且更新每个上级的 销售补贴总额
        //上级是普通会员 上上级也是普通会员 再找上上上级  一直找到店主 或者 服务商
        //上级是店主 上上是否店主 是 给上上级补贴  不是 不给补贴 或者  上上级是否服务商 是给补贴  不是不给
        //上级是服务商 上上级
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            if ($p1->userLevel->id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->userLevel->id == 2) {
                $this->add_sub($from_id, $p1->id, 200, 1);
            }
            if ($p1->userLevel->id == 3) {
                $this->add_sub($from_id, $p1->id, 250, 1);
            }
        }
        if (!empty($user->parent->parent) && !empty($user->parent->parent) && $user->parent->parent->level_id == 1 && $user->parent->level_id != 1) {
            return true;
        }
        $is_three = 0;
        //直接2级
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }

            if ($p2->userLevel->id == 2) {
                if ($user->parent->level_id !=2 && !empty($p2->parent) && $p2->level_id ==2 && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                $this->add_sub($from_id,$p2->id, 100, 2);
            }
            if ($p2->userLevel->id == 3) {
                if ($p2->level_id == 3 && $p2->parent && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }
                $this->add_sub($from_id,$p2->id, 150, 2);
            }

            if (in_array($user->parent->level_id, [2,3]) && in_array($user->parent->parent->level_id, [2,3])) {
                $is_three = 0;
            }
        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
                $p2 = $user->parent->parent;
                $p3 = $this->parent->parent->parent;
                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    $this->add_sub($from_id, $p3->id, 15, 3);
                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    $this->add_sub($from_id, $p3->id, 50, 3);
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    $this->add_sub($from_id, $p3->id, 10, 3);
                }
            }
        }

        //不是直接3级  无限往上找 找到店主 或者 服务商
        if ($user->level_id == 1 && (!empty($user->parent->parent) && $user->parent->parent->level_id ==1)) {
            //无限找上级
            $parent = $this->tree($user->parent);
            if ($parent->level_id == 2) {
                $this->add_sub($from_id,$parent->id, 100, 4);
            }
            if ($parent->level_id == 3) {
                $this->add_sub($from_id,$parent->id, 150, 4);
            }
            if (in_array($parent->level_id, [2,3]) && !empty($parent->parent) && $parent->parent->level_id == 2) {
                $this->add_sub($from_id,$parent->parent->id, 15, 4);
            }
            if (in_array($parent->level_id, [2]) && !empty($parent->parent) && $parent->parent->level_id == 3) {
                $this->add_sub($from_id,$parent->parent->id, 50, 4);
            }
            if (in_array($parent->level_id, [3]) && !empty($parent->parent) && $parent->parent->level_id == 3) {
                $this->add_sub($from_id,$parent->parent->id, 10, 4);
            }
        }

        // 给成长值  看看是否自动升级
        $this->strong_level($user);
        return true;
    }

    /**
     * 初始数据 补贴计算
     * @param $user MasterUser
     * @return bool
     */
    public function init_subsidy($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            $p1_parent_count = MasterUser::find()->where(['pid' => $p1->id])
                ->andWhere(['<', 'id', '2790'])->count();

            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } else {
                    $p1_parent_child_ids = MasterUser::find()->select('id')->where(['pid' => $p1->id])
                        ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('10')->all();
                    if (!empty($p1_parent_child_ids) && is_array($p1_parent_child_ids)) {
                        $p1_parent_child_ids = array_column($p1_parent_child_ids, 'id');
                        if (in_array($user->id, $p1_parent_child_ids)) {
                            $this->add_sub($from_id, $p1->id, 100, 1);
                        }
                    } else {
                        $this->add_sub($from_id, $p1->id, 200, 1);
                    }
                }
            }
            if ($p1->level_id == 3) {
                if ($p1_parent_count < 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } elseif(10 <= $p1_parent_count && $p1_parent_count < 100) {
                    $p1_parent_child_ids = MasterUser::find()->select('id')->where(['pid' => $p1->id])
                        ->asArray()->orderBy('id asc')->limit(10)->all();
                    if (!empty($p1_parent_child_ids) && is_array($p1_parent_child_ids)) {
                        $p1_parent_child_ids = array_column($p1_parent_child_ids, 'id');
                        if (in_array($user->id, $p1_parent_child_ids)) {
                            $this->add_sub($from_id, $p1->id, 100, 1);
                        }
                    }
                    $p1_parent_child_ids2 = MasterUser::find()->select('id')->where(['pid' => $p1->id])
                        ->asArray()->orderBy('id asc')->offset(10)->limit(90)->all();
                    if (!empty($p1_parent_child_ids2) && is_array($p1_parent_child_ids2)) {
                        $p1_parent_child_ids2 = array_column($p1_parent_child_ids2, 'id');
                        if (in_array($user->id, $p1_parent_child_ids2)) {
                            $this->add_sub($from_id, $p1->id, 200, 1);
                        }
                    }
                } elseif ($p1_parent_count > 100) {
                    $this->add_sub($from_id, $p1->id, 250, 1);
                }

            }
        }
        if (!empty($user->parent->parent) && !empty($user->parent->parent) && $user->parent->parent->level_id == 1 && $user->parent->level_id != 1) {
            return true;
        }
        $is_three = 0;
        //直接2级
        //先判断 上级  是 店主  还是服务商
        //是店主  也需要看按店主返  还是按照10个 的会员身份返
        //是服务商 也要看 是否有100个名额 给  返
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            $p1 = $user->parent;
            $p2_parent_count = MasterUser::find()->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->count();
            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }

            if ($p2->userLevel->id == 2) {
                if ($user->parent->level_id !=2 && !empty($p2->parent) && $p2->level_id ==2 && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                //$this->add_sub($from_id,$p2->id, 100, 2);

                if ($p2_parent_count < 10 ) {
                    $this->add_sub($from_id, $p2->id, 33, 2);
                } elseif ($p2_parent_count >= 10) {
                    //$this->add_sub($from_id, $p2->id, 100, 2);
                    $p1_parent_child_ids = MasterUser::find()->select('id')->where(['pid' => $p2->id])
                        ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('10')->all();
                    if (!empty($p1_parent_child_ids) && is_array($p1_parent_child_ids)) {
                        $p1_parent_child_ids = array_column($p1_parent_child_ids, 'id');
                        if (in_array($user->parent->id, $p1_parent_child_ids)) {
                            $this->add_sub($from_id, $p1->id, 33, 2);
                        }
                    } else {
                        $this->add_sub($from_id, $p1->id, 100, 2);
                    }
                }
            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }
                //$this->add_sub($from_id,$p2->id, 150, 2);

                if ($p2_parent_count < 10 ) {
                    $this->add_sub($from_id, $p2->id, 33, 2);
                    //$this->add_sub($from_id, $p2->id, 100, 2);
                } elseif ($p2_parent_count == 10) {
                    $p1_parent_child_ids = MasterUser::find()->select('id')->where(['pid' => $p2->id])
                        ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('10')->all();
                    if (!empty($p1_parent_child_ids) && is_array($p1_parent_child_ids)) {
                        $p1_parent_child_ids = array_column($p1_parent_child_ids, 'id');
                        if (in_array($user->parent->id, $p1_parent_child_ids)) {
                            $this->add_sub($from_id, $p1->id, 33, 2);
                        }else {
                            $this->add_sub($from_id, $p1->id, 100, 2);
                        }
                    }else {
                        $this->add_sub($from_id, $p1->id, 100, 2);
                    }
                } elseif(10 < $p2_parent_count && $p2_parent_count <= 100) {
                    $this->add_sub($from_id, $p2->id, 100, 2);
                } elseif($p2_parent_count > 100) {
                    $this->add_sub($from_id, $p2->id, 150, 2);
                }
            }

            if (in_array($user->parent->level_id, [2,3]) && in_array($user->parent->parent->level_id, [2,3])) {
                $is_three = 0;
            }
        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
                $p2 = $user->parent->parent;
                $p3 = $this->parent->parent->parent;
                $p2_parent_count = MasterUser::find()->where(['pid' => $p2->id])
                    ->andWhere(['<', 'id', '2790'])->count();
                $p3_parent_count = MasterUser::find()->where(['pid' => $p3->id])
                    ->andWhere(['<', 'id', '2790'])->count();
                if ($p2->level_id == 2 && $p3->userLevel->id == 2 && $p2_parent_count >= 10) {
                    //店主3级
                    $this->add_sub($from_id, $p3->id, 15, 3);
                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3 && $p2_parent_count >= 10 && $p3_parent_count >= 100) {
                    //育成店主了的服务商拿 50
                    $this->add_sub($from_id, $p3->id, 50, 3);
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3 && $p2_parent_count >=100 && $p3_parent_count >= 100) {
                    //育成了服务商的服务商拿 10
                    $this->add_sub($from_id, $p3->id, 10, 3);
                }
            }
        }
        return true;
    }

    public function add_sub($from_id, $to_id, $money, $type, $active_name = '')
    {
        $log = MasterUserSubsidy::find()->where(['from_uid' => $from_id, 'to_uid' => $to_id])->one();
        if (!empty($log) && $type < 5) {
            return true;
        }
        //echo $to_id. '新增一条记录 <br>';
        $trans = Yii::$app->db->beginTransaction();
        try {
            $to = MasterUser::findOne($to_id);
            $sub_log = new MasterUserSubsidy();
            $sub_log->from_uid = $from_id;
            $sub_log->to_uid = $to_id;
            $sub_log->money = $money;
            $sub_log->type = $type;
            $sub_log->to_user_level = "$to->level_id";
            $sub_log->active_name = $active_name;
            $sub_log->create_time = time();
            if(!$sub_log->save()) {
                Yii::error($sub_log->errors);
                $errors = $sub_log->errors;
                $error = array_shift($errors)[0];
                //var_dump($error);
                throw new Exception('无法补贴记录：' . $error);
            }
            $r = MasterUser::updateAllCounters(['subsidy_money' => $sub_log->money],['id' => $to_id]);
            //var_dump($r);
            //echo '<br>';
            if ($r <=0) {
                throw new Exception('更新补贴失败');
            }
            // 增加账户明细记录
            $user_account_log = new MasterUserAccountLog();
            $attributes = [
                'uid' => $to_id,
                'subsidy_money' => $money,
                'time' => time(),
                'remark' => '邀请会员增加补贴' . 'from_uid :' . $from_id,
            ];
            $user_account_log->setAttributes($attributes);
            $r1 = $user_account_log->save();
            if (!$r1) {
                throw new Exception('添加账户明细失败。');
            }
            //更新 补贴 下级人数  如果人数到达了  相应的人数  级别升级 升服务商  和 生店主
            //成长值  已经发展了 多少人多少钱 也需要更新
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
                Yii::error($e->getMessage());
                $this->addError('mobile', $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     *迭代，求家谱树
     * @param $user MasterUser
     * @return MasterUser
     */
    public function tree($user) {
        if ($user && ($user->level_id == 2 || $user->level_id == 3)) {
            return $user;
        } else {
            if (empty($user->parent)) {
                return $user;
            }
            return $this->tree($user->parent);
        }
//        $tree = array();
//        while($id !== 0) {
//            foreach($arr as $v) {
//                if($v['id'] == $id) {
//                    $tree[] = $v;
//                    $id = $v['parent'];
//                    break;
//                }
//            }
//        }
//        return $tree;
    }

    /**
     *迭代，求家谱树
     * @param $user MasterUser
     * @return MasterUser
     */
    public function commissionTree($user) {
        if ($user && ($user->level_id == 2 || $user->level_id == 3)) {
            return $user->parent;
        } else {
            if (!$user->parent) {
                return $user;
            }
            return $this->tree($user->parent);
        }
    }

    /**
     *迭代，求家谱树
     * @param $user MasterUser
     * @return MasterUser
     */
    function team_parent_tree($user)
    {
        if ($user && (($user->level_id == 2 || $user->level_id == 3) || is_null($user->pid))) {
            return $user;
        } else {
            if (!$user->teamParents) {
                return $user;
            }
            return $this->team_parent_tree($user->teamParents);
        }
    }

    /**
     * 返回真实 等级
     * @param $user MasterUser
     * @return int
     */
    public function check_level($user)
    {
        if ($user->level_id == 1) {
            return 1;
        } elseif ($user->level_id == 2) {
            //检测自己是否已经推荐了10人  真实的成为 店主  还是 还应该按照会员待遇
            $child_ids = MasterUser::find()->select('id')->where(['pid' => $user->id])->asArray()->orderBy('id asc')->count();
            if ($child_ids <= 10) {
                return 1;
            } else {
                return 2;
            }
        } elseif ($user->level_id == 3) {
            //检测自己是否已经推荐了10人  真实的成为 店主  还是 还应该按照会员待遇
            $child_ids = MasterUser::find()->select('id')->where(['pid' => $user->id])->asArray()->orderBy('id asc')->limit('10')->count();
            if ($child_ids <= 10) {
                return 1;
            } elseif ($child_ids > 10 && $child_ids <= 100) {
                return 2;
            } elseif ($child_ids > 100) {
                return 3;
            }
        }
    }

    /**
     * 获取
     */
    public function parent_position()
    {

    }

    /**
     * 增加 计算 成长值 并且自动升级
     * @param $user MasterUser
     * @return  bool
     */
    public function strong_level($user)
    {
        //先判断 pid team_pid是否一致  一直直接走原来的  不一致 先给直接上级
        if ($user->pid == $user->team_pid) {
            //直接一级
            if ($user->teamParents && $user->teamParents->level_id >= 1) {
                $this->add_growth($user->id, $user->teamParents->id, 399, 1);
                $to_user = MasterUser::findOne($user->teamParents->id);
                //升级
                $this->up_level($to_user);
            }
        } else {
            //直接一级
            if ($user->parent && $user->parent->level_id >= 1) {
                $this->add_growth($user->id, $user->parent->id, 399, 1);
                $to_user = MasterUser::findOne($user->parent->id);
                //升级
                $this->up_level($to_user);
            }
        }
        //无线找上级  给 成长值
        if ($user->teamParents && $user->teamParents->level_id >= 1) {
            if ($user->teamParents->level_id == 2 ||$user->teamParents->level_id == 3) {
                //不再继续给无限上级的店主成长值了
                return true;
            }
            //无线找上级  给 成长值
            if (!isset($user->teamParents->teamParents)) {
                return true;
            }
            $parent = $this->team_parent_tree($user->teamParents);
            if (!empty($parent)) {
                $this->add_growth($user->id, $parent->id, 399, 2);
                $to_user = MasterUser::findOne($parent->id);
                //升级
                $this->up_level($to_user);
            }
        }
        return true;
    }

    public function add_growth($from_id, $to_id, $money, $type)
    {
        $log = MasterUserGrowth::find()->where(['from_uid' => $from_id, 'to_uid' => $to_id])->one();
        if (!empty($log)) {
            return true;
        }
        //echo $to_id. '新增一条记录 <br>';
        $trans = Yii::$app->db->beginTransaction();
        try {
            $sub_log = new MasterUserGrowth();
            $sub_log->from_uid = $from_id;
            $sub_log->to_uid = $to_id;
            $sub_log->money = $money;
            $sub_log->type = $type;
            $sub_log->create_time = time();
            if(!$sub_log->save()) {
                Yii::error($sub_log->errors);
                $errors = $sub_log->errors;
                $error = array_shift($errors)[0];
                var_dump($error);
                throw new Exception('无法更新成长值记录：' . $error);
            }
            $r = MasterUser::updateAllCounters(['growth_money' => $sub_log->money],['id' => $to_id]);
            //var_dump($r);
            //echo '<br>';
            if ($r <=0) {
                throw new Exception('更新成长值失败');
            }
            //更新 补贴 下级人数  如果人数到达了  相应的人数  级别升级 升服务商  和 生店主
            //成长值  已经发展了 多少人多少钱 也需要更新
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
                Yii::error($e->getMessage());
                $this->addError('mobile', $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * @param $user MasterUser
     * @return bool
     */
    public function up_level($user)
    {
        if ($user->level_id == 1 && $user->growth_money >= 7980) {
            $user->level_id = 2;
            $user->save();
            $userLevelLog = new MasterUserLevelLog();
            $userLevelLog->uid = $user->id;
            $userLevelLog->level_id = 2;
            $userLevelLog->remark = '会员升店主';
            $userLevelLog->create_time = time();
            $userLevelLog->save();
        }
        if ($user->level_id == 2 && $user->growth_money >= 300000) {
            $childCount = MasterUser::find()->where(['level_id' => 2, 'status' => MasterUser::STATUS_OK, 'pid' => $user->id])->count();
            if ($childCount >=2) {
                $user->level_id = 3;
                $user->save();
                $userLevelLog = new MasterUserLevelLog();
                $userLevelLog->uid = $user->id;
                $userLevelLog->level_id = 3;
                $userLevelLog->remark = '店主升服务商';
                $userLevelLog->create_time = time();
                $userLevelLog->save();
            }
        }
    }

    /**
     * 预估佣金收益
     * @return float $total_commission
     */
    public function getComputeCommission()
    {
        $total_commission = 0;
        $queryMasterUser = MasterUser::find();
        $userList = $queryMasterUser->andWhere(['pid' => $this->id])->asArray()->all();
        $userList = array_column($userList, 'id');

        $queryOrder = Order::find();
        $queryOrder->where(['in', 'uid', $userList])->andWhere(['<>', 'status', 0])
            //->andWhere(["<", 'status', Order::STATUS_COMPLETE]);
            ->andWhere(["BETWEEN", 'status', Order::STATUS_PAID, Order::STATUS_COMPLETE]);
        /** @var Order $model */
        foreach ($queryOrder->each() as $model) {
            $share_commission_ratio_2 = 0;
            if ($this->level_id == 1) {
                $share_commission_ratio_1 = 30;
            } elseif ($this->level_id == 2) {
                $share_commission_ratio_1 = 40;
            } elseif ($this->level_id == 3) {
                $share_commission_ratio_1 = 50;
            }
            if ($model->user->status == MasterUser::STATUS_OK) {
                if ($model->user->level_id == 1) {
                    $share_commission_ratio_2 = 30;
                } elseif ($model->user->level_id == 2) {
                    $share_commission_ratio_2 = 40;
                } elseif ($model->user->level_id == 3) {
                    $share_commission_ratio_2 = 50;
                }
            }
            if ($model->user->status == MasterUser::STATUS_OK) {
                $share_commission_ratio_1 = 30;
            }
            if ($model->user->status == MasterUser::STATUS_OK && $this->status == MasterUser::STATUS_WAIT) {
                continue;
            }
            /** @var OrderItem $item */
            foreach ($model->itemList as $item) {
                if (!in_array($item->goods->share_commission_type, [Goods::SHARE_COMMISSION_TYPE_MONEY, Goods::SHARE_COMMISSION_TYPE_RATIO])) {
                    // 此商品不参与分享佣金
                    continue;
                }
                if (MerchantFinancialSettlement::find()->where(['oid' => $model->id])->exists()) {
                    continue;
                }
                if ($item->goods->is_pack == 1 || $model->is_coupon ==1 ) {
                    continue;
                }
                // 一级分享
                if (empty($share_commission_ratio_1) || Util::comp($share_commission_ratio_1, 0, 2) <= 0) {
                    // 店铺没有设置一级分享佣金比例
                    continue;
                }
                $item_commission_1 = 0;
                $sku=$item->goodsSku;//多规格佣金设置
                if ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_MONEY) { // 固定金额
                    //$item_commission_1 = round($item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 100, 2);
                    if ($share_commission_ratio_2 != 0) {
                        if (empty($sku) || $sku->commission == '') {
                            $item_commission_1 = round(($item->goods->share_commission_value * $share_commission_ratio_2 * $item->amount / 100) * $share_commission_ratio_1 / 100, 2);
                        } else {
                            $item_commission_1 = round(($sku->commission * $share_commission_ratio_2 * $item->amount / 100) * $share_commission_ratio_1 / 100, 2);
                        }
                    } else {
                        if (empty($sku) || $sku->commission == '') {
                            $item_commission_1 = round(($item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 100), 2);
                        } else {
                            $item_commission_1 = round(($sku->commission * $share_commission_ratio_1 * $item->amount / 100), 2);
                        }
                    }
                } elseif ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_RATIO) { // 百分比
                    if (empty($sku) || $sku->commission == '') {
                        $item_commission_1 = round($item->price * $item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 10000, 2);
                    } else {
                        $item_commission_1 = round($item->price * $sku->commission * $share_commission_ratio_1 * $item->amount / 10000, 2);
                    }
                }
                if (Util::comp($item_commission_1, 0, 2) > 0) {
                    $total_commission += $item_commission_1;
                }

            }
        }
        return $total_commission;
    }

    /**
     * 累计基金
     * @return float
     */
    public function getCumulative()
    {
        $money = MasterUserWithdraw::find()->where(['uid' => $this->id])->andWhere(['status' => MasterUserWithdraw::STATUS_FINISH])->sum('money');
        return $money * 5 / 100;
    }

    /**
     * 获取自己自购 省钱比率
     */
    public function getBuyRatio()
    {
        $ratio = 0;
        if ($this->status == MasterUser::STATUS_OK) {
            if ($this->level_id == 1) {
                $ratio = 30;
            } elseif ($this->level_id == 2) {
                $ratio = 40;
            } elseif ($this->level_id == 3) {
                $ratio = 50;
            }
        }
        return $ratio;
    }

    /**
     * 获取直接一级自购 省钱比率
     */
    public function getChildBuyRatio()
    {
        $ratio = 30;
        if ($this->status == MasterUser::STATUS_OK) {
            if ($this->level_id == 1) {
                $ratio = 30;
            } elseif ($this->level_id == 2) {
                $ratio = 40;
            } elseif ($this->level_id == 3) {
                $ratio = 50;
            }
        }
        return $ratio;
    }

    /**
     * 批量跑一次  所有激活会员 统一给400积分
     */
    public function updateScore()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $r = MasterUserAccount::updateAllCounters(['score' => 400], ['uid' => $this->id]);
            if ($r <=0) {
                throw new Exception('更新积分失败。');
            }
            $userAccountLog = new MasterUserAccountLog;
            $userAccountLog->uid = $this->id;
            $userAccountLog->score = 400;
            $userAccountLog->remark = '激活成云淘帮会员，赠送400购物积分';
            $userAccountLog->time = time();
            $result = $userAccountLog->save();
            if (!$result) {
                throw new Exception('更新积分记录失败。');
            }
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
                return false;
            } catch (Exception $e) {
                Yii::error($e->getMessage());
                return false;
            }
        }
    }
    /**
     * 增加用户积分
     * @param  integer $score 积分数量
     * @param  string $code  类型
     * @param  string $remark  说明
     * @throws
     * @return bool
     */
    public function addScore($score,$code='',$remark='')
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $r = MasterUserAccount::updateAllCounters(['score' => $score], ['uid' => $this->id]);
            if ($r <=0) {
                throw new Exception('更新积分失败。');
            }
            $userScoreLog = new MasterUserScoreLog();
            $userScoreLog->uid = $this->id;
            $userScoreLog->score = $score;
            $userScoreLog->code = $code;
            $userScoreLog->remark = $remark;
            $userScoreLog->create_time = time();
            $result = $userScoreLog->save();
            if (!$result) {
                throw new Exception('更新积分记录失败。');
            }
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
                return false;
            } catch (Exception $e) {
                Yii::error($e->getMessage());
                return false;
            }
        }
    }


    /**
     * 优惠券活动商品下单 已激活会员第一次购买送3张优惠券 全部使用 购买再次赠送
     * @param integer $gid 活动商品id
     * @param integer $coupon_id 优惠券编号
     * @throws
     * @return bool
     */
    public function updateCoupons($gid,$coupon_id)
    {
        $trans = Yii::$app->db->beginTransaction();
        try {

            $r= GoodsCouponGiftMasterUser::find()
                ->where(['or', ['status' => GoodsCouponGiftMasterUser::STATUS_WAIT], ['status' => GoodsCouponGiftMasterUser::STATUS_LOCK]])
                ->andWhere(['uid'=>$this->id])
                ->andWhere(['gid'=>$gid])
                ->count();
            if ($r <= 0 ) {
                /** @var $coupon GoodsCouponRule */
                $coupon = GoodsCouponRule::find()->where(['gid' => $gid])->one();
                if (empty($coupon)) {
                    throw new Exception('该商品不是优惠券活动商品');
                }
                //发放优惠券
                $data=[];
                for ($i = 0; $i < $coupon->count; $i++) {
                    $data[] = [
                        'cid' => $coupon->id,
                        'gid' => $gid,
                        'uid' => $this->id,
                        'create_time' => time(),
                        'status' => GoodsCouponGiftMasterUser::STATUS_WAIT,
                    ];
                }
                //再执行批量插入
                if (isset($data))
                {
                    Yii::$app->db->createCommand()
                        ->batchInsert(GoodsCouponGiftMasterUser::tableName(),['cid','gid','uid','create_time','status'],
                            $data)
                        ->execute();
                }


            } else {
                /** @var $user_coupon_lock GoodsCouponGiftMasterUser */
                if(empty($coupon_id))
                {
                    throw new Exception('订单优惠券参数错误');
                }
                $user_coupon_lock = GoodsCouponGiftMasterUser::findOne($coupon_id);
                if ($user_coupon_lock->status != GoodsCouponGiftMasterUser::STATUS_LOCK) {
                    throw new Exception('未找到可更新优惠券');
                }
                $user_coupon_lock->status = GoodsCouponGiftMasterUser::STATUS_USED;
                $user_coupon_lock->use_time = time();
                if (!$user_coupon_lock->save()) {
                    throw new Exception('优惠券状态更新失败。');
                }

            }
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
                return false;
            } catch (Exception $e) {
                Yii::error($e->getMessage());
                return false;
            }
        }
    }

    /**
     * 激活新会员发送通知
     * @throws Exception
     */
    public function activateMessage()
    {

        /** 激活新会员发送给上级消息通知 */
        $user_message = new MasterUserMessage();
        $nick_name='';
        if(!empty($this->nickname))
        {
            $nick_name='('.$this->nickname.')';
        }
        if(!empty($this->pid))
        {
        $user_message->MessageSend($this->pid, '您名下新人' . $nick_name . '已激活成会员!', Yii::$app->params['site_host'] . '/h5/user/team-list?status=1', '激活新会员');
        }
        /** 激活新会员发送给用户自身消息通知 */
        $active_user_message = System::getConfig('active_user_message');
        $id = $user_message->MessageSend($this->id, '您已经激活成为会员,点击查看您的权益!', Yii::$app->params['site_host'] . '/h5/notice/umview', $active_user_message);
        if ($id) {
            $message = MasterUserMessage::findOne($id);
            $message->url = Yii::$app->params['site_host'] . '/h5/notice/umview?id=' . $id . '&app=1';

            if (!$message->save(false)) {
                Yii::warning('更新激活通知记录失败。');
            }
        }

        return true;
    }





    /**
     * 获取销售记录个数
     */
    public function getSaleCount()
    {
        return MasterUserSaleLog::find()->where(['uid' => $this->id])->count();
    }

    /**
     * 获取团队个数
     */
    public function getTeamCount()
    {
        return MasterUser::find()->where(['pid' => $this->id])->count();
    }

    /**
     * 获取团队激活个数
     */
    public function getTeamActiveCount()
    {
        return MasterUser::find()->where(['pid' => $this->id, 'status' => MasterUser::STATUS_OK])->count();
    }

    /**
     * 获取团队激活个数
     */
    public function getTeamNotActiveCount()
    {
        return MasterUser::find()->where(['pid' => $this->id, 'status' => MasterUser::STATUS_WAIT])->count();
    }

    /**
     * 获取用户网体用户以下的用户
     * @param $uid
     * @param string $uidArr
     * @return  mixed | string
     */
    public function getBottomMasterUsers($uid, $uidArr=''){
        $userList = MasterUser::find()->select('id')->where(['pid' => $uid])->all();
        foreach ($userList as $key=>$value){
            $uidArr .= $value['id'].',';
            $user = MasterUser::find()->select('id')->where(['pid' => $value['id']])->all();
            if($user){
                $uidArr = $this->getBottomMasterUsers($value['id'],$uidArr);
            }
        }
        return $uidArr;
    }

    /**
     * 获取用户网体用户以下的用户
     * @param $uid
     * @param string $uidArr
     * @return  mixed | string
     */
    public function getOkBottomMasterUsers($uid, $uidArr=''){
        $userList = MasterUser::find()->select('id')->where(['pid' => $uid, 'status' => MasterUser::STATUS_OK])->all();
        foreach ($userList as $key=>$value){
            $uidArr .= $value['id'].',';
            $user = MasterUser::find()->select('id')->where(['pid' => $value['id'], 'status' => MasterUser::STATUS_OK])->all();
            if($user){
                $uidArr = $this->getOkBottomMasterUsers($value['id'],$uidArr);
            }
        }
        return $uidArr;
    }

    /**
     * 获取用户网体用户以下的用户
     * @param $uid
     * @param string $uidArr
     * @param $time
     * @return  mixed | string
     */
    public function getOkBottomMasterUser($uid, $uidArr='', $time = ''){
        if (empty($time)) {
            $userList = MasterUser::find()->select('id')->where(['pid' => $uid, 'status' => MasterUser::STATUS_OK])->all();
            foreach ($userList as $key=>$value){
                $uidArr .= $value['id'].',';
                $user = MasterUser::find()->select('id')->where(['pid' => $value['id'], 'status' => MasterUser::STATUS_OK])->all();
                if($user){
                    $uidArr = $this->getOkBottomMasterUser($value['id'],$uidArr, $time);
                }
            }
        } else {
            $userList = MasterUser::find()->select('id')->where(['pid' => $uid, 'status' => MasterUser::STATUS_OK])
                ->andWhere(['>=', 'handle_time', $time])
                ->all();
            foreach ($userList as $key=>$value){
                $uidArr .= $value['id'].',';
                $user = MasterUser::find()->select('id')->where(['pid' => $value['id'], 'status' => MasterUser::STATUS_OK])
                    ->andWhere(['>=', 'handle_time', $time])->all();
                if($user){
                    $uidArr = $this->getOkBottomMasterUser($value['id'],$uidArr, $time);
                }
            }
        }

        return $uidArr;
    }

    /**
     * 获取团队所有用户编号
     * @param $uid  integer 用户编号
     * @param $status integer 状态
     * @return array
     */
    public function getChildAllList($uid, $status = 3)
    {
        if ($status == 3) {
            $status_str = ' t.STATUS <> 0';
        } else {
            $status_str = ' t.STATUS = ' . $status;
        }
        $sql = "SELECT
            t3.id,
            t3.pid,
            t3.real_name, t3.mobile
        FROM
            (
            SELECT
                t1.id,
                t1.pid,
                t1.real_name,
                t1.mobile,
            IF
                ( find_in_set( pid, @pids ) > 0, @pids := concat( @pids, ',', id ), 0 ) AS ischild 
            FROM
                ( SELECT id, pid, real_name,mobile FROM `user` t WHERE ".$status_str." ORDER BY pid, id ) t1,
                ( SELECT @pids := " . $uid . " ) t2 
            ) t3 
        WHERE
            ischild != 0;";

        $connection = Yii::$app->db;
        $result = $connection->createCommand($sql)->queryAll();
        return $result;
    }

    /**
     * 获取团队所有用户编号
     * @param $uid  integer 用户编号
     * @return array
     */
    public function getParentAllList($uid)
    {

        $sql = "SELECT ID.level, DATA.* FROM(
SELECT
@id as _id,
( SELECT @id := pid
FROM `user`
WHERE id = @id
limit 1
) as _pid,
@l := @l+1 as level
FROM `user`,
(SELECT @id := " . $uid . ", @l := 0 ) b
WHERE @id > 0
) ID, `user` DATA
WHERE ID._id = DATA.id
ORDER BY level;";

        $connection = Yii::$app->db;
        $result = $connection->createCommand($sql)->queryAll();
        return $result;
    }

    /**
     * 获取 当月签到情况
     */
    public function getSignList()
    {
        $noe = mktime(0, 0, 0, date('m'), 1, date('y')); //获取当前的月的一号
        $week = date("w", $noe); // 每个月的一号是星期几
        $days = date("t", $noe); //每个月的总天数

        $list = [];
        for($w = 0; $w < $week; $w++){
            //获取当月一号前面的空格
            $list[] = [
                'date' => '',
                'day' => '',
                'is_sign' => 0
            ];
        }
        for ($i =1; $i <= $days; $i++) {
            $day = $i;
            if ($i < 10) {
                $i = '0'. $i;
            }
            $date = date('Y-m-'. $i, time());
            $is_sign = MasterUserScoreLog::find()
                ->andWhere(['BETWEEN', 'create_time', strtotime($date), strtotime($date)+86399])
                ->andWhere(['code' => MasterUserScoreLog::SIGN])
                ->andWhere(['uid' => $this->id])->exists();
            $list[] = [
                'date' => $date,
                'day' => $day,
                'is_sign' => $is_sign ? 1 : 0
            ];
        }
        return $list;
    }


    /**
     *分佣比率
     * 会员 1.直接销售每单30% 2.直属会员月分销总额30%
     * 店主 1.直接销售每单40% 2.直属团队每人的分销总额30%  3.育成店主月结算佣金的30%
     * 服务商 1.直接销售每单50% 2.直属团队每人的分销总额30%  3.育成店主月结算佣金的30% 4.育成服务商月结算佣金的30%
     */
}
