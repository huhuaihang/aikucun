<?php

namespace app\models;

use phpDocumentor\Reflection\Types\Null_;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * 用户
 * Class User
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $pid 上级编号
 * @property integer $team_pid 团队上级编号
 * @property string $invite_code 邀请码
 * @property string $mobile 手机号
 * @property string $auth_key
 * @property string $password HASH密码
 * @property string $payment_password 支付密码
 * @property string $real_name 真实姓名
 * @property string $nickname 昵称
 * @property integer $gender 性别
 * @property string $avatar 头像
 * @property integer $prepare_count 预购数量
 * @property integer $status 状态
 * @property integer $level_id 等级
 * @property float $subsidy_money 补贴金额
 * @property float $growth_money 补贴金额
 * @property integer $create_time 创建时间
 * @property integer $handle_time 激活时间
 * @property integer $is_handle 是否后台手动激活
 * @property integer $is_per_handle 是否前台店主售卖服务商激活
 * @property integer $is_self_active 是否前台自己购买激活
 *
 * @property UserAccount $account 关联用户账户
 * @property User $childList 关联自己的下一级
 * @property User $parent 关联自己的父级
 * @property User $team_parent 关联自己团队的父级
 * @property User $teamParents 关联自己团队的父级
 * @property UserLevel $userLevel 获取自己的用户等级
 * @property UserCard $card 获取自己的会员卡
 * @property UserWithdraw $cumulative 关联提现金额
 * @property UserCommission $computeCommission 关联计算预估收益金额
 * @property $buyRatio 关联我的自购省钱比率
 * @property $childBuyRatio 关联我的直接下级购买返佣给我比率
 * @property $saleCount 关联我的销售记录总数
 * @property $teamCount 关联我的团队会员总数
 * @property $teamActiveCount 关联我的团队会员激活总数
 * @property $teamNotActiveCount 关联我的团队会员未激活总数
 */
class User_bak2 extends ActiveRecord implements IdentityInterface
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
            ['gender', 'default', 'value' => 0],
            ['gender', 'in', 'range' => [User::GENDER_MALE, User::GENDER_FEMALE, User::GENDER_UNKNOWN, User::GENDER_SECRET]],
            [['team_pid', 'level_id', 'prepare_count', 'real_name'], 'safe'],
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
//                if (User::find()->andWhere(['nickname' => $nickname])->exists()) {
//                    continue;
//                }
//                $this->nickname = $nickname;
//                break;
//            }
            // 生成唯一邀请码
            while (true) {
                $invite_code = Util::randomStr(6, 5);
                if (User::find()->andWhere(['invite_code' => $invite_code])->exists()) {
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
            'nickname' => '昵称',
            'password' => '密码',
            'level_money' => '等级金额',
            'prepare_level_money' => '实际等级金额',
            'mobile' => '手机号',
            'remark' => '备注',
            'prepare_count' => '预购剩余数量',
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            // 生成账户信息
            $user_account = new UserAccount();
            $user_account->uid = $this->id;
            $user_account->save();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return User::findOne($id);
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
    public static function generateToken($api_version, $uid)
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
     * @return User
     * @throws Exception
     */
    public static function findByToken($api_version, $token)
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
        return User::findOne($uid);
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
        return $this->hasOne(UserAccount::className(), ['uid' => 'id']);
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
        return User::find()->andWhere(['pid' => $this->id])->andWhere(['IN', 'status', [User::STATUS_OK, User::STATUS_WAIT]])->all();
    }

    /**
     * 关联用户会员卡
     * @return \yii\db\ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(UserCard::className(), ['uid' => 'id'])->where(['status' => UserCard::STATUS_OK]);
    }

    /**
     * 获取用户等级
     */
    public function getUserLevel()
    {
        return $this->hasOne(UserLevel::className(), ['id' => 'level_id']);
//        return UserLevel::find()->asArray()->select(['id', 'money', 'name'])->andWhere(['id' => $this->level_id, 'status' => UserLevel::STATUS_OK])->one();
//        $level = UserLevel::find()
//            ->select(['id', 'money', 'name'])->orderBy('money desc')
//            ->andWhere(['status' => UserLevel::STATUS_OK])
//            ->andWhere(['<=', 'money', $this->account->level_money])
//            ->asArray()->one();
//        return $level;
    }

    /**
     * 获取用户父级
     */
    public function getParent()
    {
        return User::find()->andWhere(['id' => $this->pid])->one();
        //return User::find()->andWhere(['id' => $this->pid, 'status' => User::STATUS_OK])->one();
    }

    /**
     * 获取用户团队父级
     * @param $team_pid int
     * @return User | null | mixed
     */
    public function getTeamParent($team_pid)
    {
        return User::find()->andWhere(['id' => $team_pid])->one();
    }

    /**
     * 获取用户团队父级
     * @return User | null | mixed
     */
    public function getTeamParents()
    {
        return User::find()->where(['id' => $this->team_pid])->one();
    }

    /**
     * 升级  或者 购买 给相应的用户贴
     * @param $user User
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
     * @param $user User
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
     * @param $user User
     * @return bool
     */
    public function all_no_next_sub($user)
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
        if ($user->parent->level_id == 1 && (!empty($user->parent->parent) && $user->parent->parent->level_id ==1)
            && (!empty($user->parent->parent->parent) && $user->parent->parent->parent->level_id ==1)) {
            //无限找上级
            $parent = $this->tree($user->parent);
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
     * @param $user User
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
     * @param $user User
     * @return bool
     */
    public function init_subsidy_test($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            $p1_parent_count = User::find()->where(['pid' => $p1->id])
                ->andWhere(['<', 'id', '2790'])->count();
            $p1_10_child = User::find()->select('id')->where(['pid' => $p1->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('10')->all();
            $p1_10_child_uid = empty($p1_10_child) ? [] : array_column($p1_10_child, 'id');
            $p1_100_child = User::find()->select('id')->where(['pid' => $p1->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(10)->limit(90)->all();
            $p1_100_child_uid = empty($p1_100_child) ? [] : array_column($p1_100_child, 'id');

            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } else {
                    if (in_array($user->id, $p1_10_child_uid)) {
                        //店主 前10个 直属会员给 100
                        $this->add_sub($from_id, $p1->id, 100, 1);
                    }else {
                        //10个之后给200
                        $this->add_sub($from_id, $p1->id, 200, 1);
                    }
                }
            }
            if ($p1->level_id == 3) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } elseif(10 <= $p1_parent_count && $p1_parent_count < 100) {

                    if (in_array($user->id, $p1_10_child_uid)) {
                        //店主 前10个 直属会员给 100
                        $this->add_sub($from_id, $p1->id, 100, 1);
                    } elseif (in_array($user->id, $p1_100_child_uid)) {
                        //店主 前10个 直属会员给 100
                        $this->add_sub($from_id, $p1->id, 200, 1);
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
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->count();
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');
            if ($p2->id == '2529' && $user->id == '2531' && $p1->id == '2530') {
                Yii::warning($p2_parent_count);
                Yii::warning($p2_10_child_uid);
                Yii::warning($p2_100_child_uid);
            }
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
                    if (in_array($user->parent->id, $p2_10_child_uid)) {

                        $this->add_sub($from_id, $p2->id, 33, 2);
                    } elseif (in_array($user->parent->id, $p2_100_child_uid)) {

                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 33, 2);
                    }
                }
            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }

                if ($p2_parent_count < 10 ) {
                    $this->add_sub($from_id, $p2->id, 33, 2);
                } elseif ($p2_parent_count >= 10 && $p2_parent_count <= 100) {
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 33, 2);
                    } elseif (in_array($user->parent->id, $p2_100_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    }
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
                $p2_parent_count = User::find()->where(['pid' => $p2->id])
                    ->andWhere(['<', 'id', '2790'])->count();
                $p3_parent_count = User::find()->where(['pid' => $p3->id])
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

    /**
     * 初始数据 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_subsidy_test2($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                $this->add_sub($from_id, $p1->id, 200, 1);
            }
            if ($p1->level_id == 3) {
                $this->add_sub($from_id, $p1->id, 250, 1);
            }
        }
        if (!empty($user->parent->parent) && !empty($user->parent->parent) && $user->parent->parent->level_id == 1 && $user->parent->level_id != 1) {
            //return true;
        }
        $is_three = 0;
        //直接2级
        //先判断 上级  是 店主  还是服务商
        //是店主  也需要看按店主返  还是按照10个 的会员身份返
        //是服务商 也要看 是否有100个名额 给  返
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            $p1 = $user->parent;
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->count();
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');

            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }

            if ($p2->userLevel->id == 2) {
                if ($user->parent->level_id !=2 && !empty($p2->parent) && $p2->level_id ==2 && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                if ($p1->level_id == 2) {
                    //前10
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 15, 2);
                    }
                }

            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }

                if ($p1->level_id == 1) {
                    $this->add_sub($from_id, $p2->id, 150, 2);
                }
                if ($p1->level_id == 2) {
                    //前10无
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    }
                }
                if ($p1->level_id == 3) {
                    //if (in_array($user->parent->id, $p2_10_child_uid) && $user->level_id == 1) {
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                        //} elseif(in_array($user->parent->id, $p2_100_child_uid) && $user->level_id == 1) {
                    } elseif(in_array($user->parent->id, $p2_100_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    } elseif($user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 10, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 10, 2);
                    }
                }
            }

        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
                $p2 = $user->parent->parent;
                $p3 = $this->parent->parent->parent;
                $p3_10_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('9')->all();
                $p3_10_child_uid = empty($p3_10_child) ? [] : array_column($p3_10_child, 'id');
                $p3_100_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
                $p3_100_child_uid = empty($p3_100_child) ? [] : array_column($p3_100_child, 'id');
                if ($p2->level_id == 1 && $p3->userLevel->id == 2) {
                    //店主3级
                    $this->add_sub($from_id, $p3->id, 100, 3);
                }
                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    //店主3级
                    if (in_array($p2->id, $p3_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 100, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 15, 3);
                    }

                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    //育成店主了的服务商拿 50
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 50, 3);
                    }
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    //育成了服务商的服务商拿 10
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 10, 3);
                    }

                }
            }
        }

        //不是直接3级  无限往上找 找到店主 或者 服务商
        if ($user->level_id == 1 && (!empty($user->parent->parent) && ($user->parent->parent->level_id ==1) && !empty($user->parent->parent->parent))) {
            //无限找上级
            $parent = $this->tree($user->parent->parent);
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
     * 初始数据 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_subsidy_test3($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                $this->add_sub($from_id, $p1->id, 200, 1);
            }
            if ($p1->level_id == 3) {
                $this->add_sub($from_id, $p1->id, 250, 1);
            }
        }
        if (!empty($user->parent->parent) && !empty($user->parent->parent) && $user->parent->parent->level_id == 1 && $user->parent->level_id != 1) {
            //return true;
        }
        $is_three = 0;
        //直接2级
        //先判断 上级  是 店主  还是服务商
        //是店主  也需要看按店主返  还是按照10个 的会员身份返
        //是服务商 也要看 是否有100个名额 给  返
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            $p1 = $user->parent;
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->andWhere(['between', 'id', 3660, 3720])->count();
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['between', 'id', 3660, 3720])->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['between', 'id', 3660, 3720])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');

            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id, $p2->id, 33, 2);
            }

            if ($p2->userLevel->id == 2) {
                if ($user->parent->level_id !=2 && !empty($p2->parent) && $p2->level_id ==2 && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                if ($p1->level_id == 1) {
                    $this->add_sub($from_id, $p2->id, 100, 2);
                }
                if ($p1->level_id == 2) {
                    //前10
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 15, 2);
                    }
                }
            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }

                if ($p1->level_id == 1) {
                    $this->add_sub($from_id, $p2->id, 150, 2);
                }
                if ($p1->level_id == 2) {
                    //前10无
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    }
                }
                if ($p1->level_id == 3) {
                    //if (in_array($user->parent->id, $p2_10_child_uid) && $user->level_id == 1) {
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                        //} elseif(in_array($user->parent->id, $p2_100_child_uid) && $user->level_id == 1) {
                    } elseif(in_array($user->parent->id, $p2_100_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    } elseif($user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 10, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 10, 2);
                    }
                }
            }

        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
                $p2 = $user->parent->parent;
                $p3 = $this->parent->parent->parent;
                $p3_10_child = User::find()->select('id')->where(['pid' => $p3->id])
                    //->andWhere(['<', 'id', '2790'])
                    ->andWhere(['between', 'id', 3660, 3720])
                    ->asArray()->orderBy('id asc')->limit('9')->all();
                $p3_10_child_uid = empty($p3_10_child) ? [] : array_column($p3_10_child, 'id');
                $p3_100_child = User::find()->select('id')->where(['pid' => $p3->id])
//                    ->andWhere(['<', 'id', '2790'])
                    ->andWhere(['between', 'id', 3660, 3720])
                    ->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
                $p3_100_child_uid = empty($p3_100_child) ? [] : array_column($p3_100_child, 'id');
                if ($p2->level_id == 1 && $p3->userLevel->id == 2) {
                    //店主3级
                    $this->add_sub($from_id, $p3->id, 100, 3);
                }
                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    //店主3级
                    if (in_array($p2->id, $p3_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 100, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 15, 3);
                    }

                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    //育成店主了的服务商拿 50
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 50, 3);
                    }
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    //育成了服务商的服务商拿 10
                    if (in_array($p2->id, $p3_10_child_uid)|| in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 10, 3);
                    }

                }
            }
        }
        //不是直接3级  无限往上找 找到店主 或者 服务商
        if ($user->level_id == 1 && (!empty($user->parent->parent) && $user->parent->parent->level_id ==1 && !empty($user->parent->parent->parent))) {
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
     * 初始数据 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_handle_subsidy_test2($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                $this->add_sub($from_id, $p1->id, 200, 1);
            }
            if ($p1->level_id == 3) {
                $this->add_sub($from_id, $p1->id, 250, 1);
            }
        }
        if (!empty($user->parent->parent) && !empty($user->parent->parent) && $user->parent->parent->level_id == 1 && $user->parent->level_id != 1) {
            //return true;
        }
        $is_three = 0;
        //直接2级
        //先判断 上级  是 店主  还是服务商
        //是店主  也需要看按店主返  还是按照10个 的会员身份返
        //是服务商 也要看 是否有100个名额 给  返
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            $p1 = $user->parent;
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->count();
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');

            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }

            if ($p2->userLevel->id == 2) {
                if ($user->parent->level_id !=2 && !empty($p2->parent) && $p2->level_id ==2 && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                if ($p1->level_id == 2) {
                    //前10
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 15, 2);
                    }
                }

            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }

                if ($p1->level_id == 1) {
                    $this->add_sub($from_id, $p2->id, 150, 2);
                }
                if ($p1->level_id == 2) {
                    //前10无
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    }
                }
                if ($p1->level_id == 3) {
                    if (in_array($user->parent->id, $p2_10_child_uid) && $user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                    } elseif(in_array($user->parent->id, $p2_100_child_uid) && $user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    } elseif($user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 10, 2);
                    }
                }
            }

        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
                $p2 = $user->parent->parent;
                $p3 = $this->parent->parent->parent;
                $p3_10_child = User::find()->select('id')->where(['pid' => $p3->id])->andWhere(['<', 'id', '2790'])
                    ->asArray()->orderBy('id asc')->limit('9')->all();
                $p3_10_child_uid = empty($p3_10_child) ? [] : array_column($p3_10_child, 'id');
                $p3_100_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
                $p3_100_child_uid = empty($p3_100_child) ? [] : array_column($p3_100_child, 'id');
                if ($p2->level_id == 1 && $p3->userLevel->id == 2) {
                    //店主3级
                    $this->add_sub($from_id, $p3->id, 100, 3);
                }
                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    //店主3级
                    if (in_array($p2->id, $p3_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 100, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 15, 3);
                    }

                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    //育成店主了的服务商拿 50
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 50, 3);
                    }
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    //育成了服务商的服务商拿 10
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 10, 3);
                    }

                }
//                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
//                    //店主3级
//                    $this->add_sub($from_id, $p3->id, 15, 3);
//                }
//                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
//                    //育成店主了的服务商拿 50
//                    $this->add_sub($from_id, $p3->id, 50, 3);
//                }
//                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
//                    //育成了服务商的服务商拿 10
//                    $this->add_sub($from_id, $p3->id, 10, 3);
//                }
            }
        }
        return true;
    }


    /**
     * 手动激活 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_handle_subsidy_test21($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                $this->add_sub($from_id, $p1->id, 200, 1);
            }
            if ($p1->level_id == 3) {
                $this->add_sub($from_id, $p1->id, 250, 1);
            }
        }
        if (!empty($user->parent->parent) && !empty($user->parent->parent) && $user->parent->parent->level_id == 1 && $user->parent->level_id != 1) {
            //return true;
        }
        $is_three = 0;
        //直接2级
        //先判断 上级  是 店主  还是服务商
        //是店主  也需要看按店主返  还是按照10个 的会员身份返
        //是服务商 也要看 是否有100个名额 给  返
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            $p1 = $user->parent;
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->count();
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');

            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }

            if ($p2->userLevel->id == 2) {
                if ($user->parent->level_id !=2 && !empty($p2->parent) && $p2->level_id ==2 && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                if ($p2->level_id == 2) {
                    //前10
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 15, 2);
                    }
                }

            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }

                if ($p1->level_id == 1) {
                    $this->add_sub($from_id, $p2->id, 150, 2);
                }
                if ($p1->level_id == 2) {
                    //前10无
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    }
                }
                if ($p1->level_id == 3) {
                    if (in_array($user->parent->id, $p2_10_child_uid) && $user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                    } elseif(in_array($user->parent->id, $p2_100_child_uid) && $user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    } elseif($user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 10, 2);
                    }
                }
            }

        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
                $p2 = $user->parent->parent;
                $p3 = $this->parent->parent->parent;
                $p3_10_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->asArray()->orderBy('id asc')->limit('9')->all();
                $p3_10_child_uid = empty($p3_10_child) ? [] : array_column($p3_10_child, 'id');
                $p3_100_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
                $p3_100_child_uid = empty($p3_100_child) ? [] : array_column($p3_100_child, 'id');
                if ($p2->level_id == 1 && $p3->userLevel->id == 2) {
                    //店主3级
                    $this->add_sub($from_id, $p3->id, 100, 3);
                }
                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    //店主3级
                    if (in_array($p2->id, $p3_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 100, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 15, 3);
                    }

                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    //育成店主了的服务商拿 50
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 50, 3);
                    }
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    //育成了服务商的服务商拿 10
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 10, 3);
                    }

                }
            }
        }
        return true;
    }

    /**
     * 初始数据 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_handle_subsidy_test_update($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                $this->add_sub($from_id, $p1->id, 200, 1);
            }
            if ($p1->level_id == 3) {
                $this->add_sub($from_id, $p1->id, 250, 1);
            }
        }
        if (!empty($user->parent->parent) && !empty($user->parent->parent) && $user->parent->parent->level_id == 1 && $user->parent->level_id != 1) {
            //return true;
        }
        $is_three = 0;
        //直接2级
        //先判断 上级  是 店主  还是服务商
        //是店主  也需要看按店主返  还是按照10个 的会员身份返
        //是服务商 也要看 是否有100个名额 给  返
        if ($user->parent && $user->parent->parent) {
            $p2 = $user->parent->parent;
            $p1 = $user->parent;
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->count();
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');

            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }

            if ($p2->userLevel->id == 2) {
                if ($user->parent->level_id !=2 && !empty($p2->parent) && $p2->level_id ==2 && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                if ($p1->level_id == 2) {
                    //前10
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 15, 2);
                    }
                }

            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }

                if ($p1->level_id == 1) {
                    $this->add_sub($from_id, $p2->id, 150, 2);
                }
                if ($p1->level_id == 2) {
                    //前10无
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    }
                }
                if ($p1->level_id == 3) {
                    if (in_array($user->parent->id, $p2_10_child_uid) && $user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 150, 2);
                    } elseif(in_array($user->parent->id, $p2_100_child_uid) && $user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 50, 2);
                    } elseif($user->level_id == 1) {
                        $this->add_sub($from_id, $p2->id, 10, 2);
                    }
                }
            }

        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->parent && $user->parent->parent && $user->parent->parent->parent) {
                $p2 = $user->parent->parent;
                $p3 = $this->parent->parent->parent;
                $p3_10_child = User::find()->select('id')->where(['pid' => $p3->id])->andWhere(['<', 'id', '2790'])
                    ->asArray()->orderBy('id asc')->limit('9')->all();
                $p3_10_child_uid = empty($p3_10_child) ? [] : array_column($p3_10_child, 'id');
                $p3_100_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
                $p3_100_child_uid = empty($p3_100_child) ? [] : array_column($p3_100_child, 'id');
                if ($p2->level_id == 1 && $p3->userLevel->id == 2) {
                    //店主3级
                    $this->add_sub($from_id, $p3->id, 100, 3);
                }
                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    //店主3级
                    if (in_array($p2->id, $p3_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 100, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 15, 3);
                    }

                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    //育成店主了的服务商拿 50
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 50, 3);
                    }
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    //育成了服务商的服务商拿 10
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p3->id, 150, 3);
                    } else {
                        $this->add_sub($from_id, $p3->id, 10, 3);
                    }

                }
            }
        }
        return true;
    }

    /**
     * 初始数据 手动激活的 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_handle_subsidy_test($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            $p1_parent_count = User::find()->where(['pid' => $p1->id])
//                ->andWhere(['<', 'id', '2790'])
//                ->orWhere(['between', 'id', 3660, 3720])
                ->count();
            $p1_10_child = User::find()->select('id')->where(['pid' => $p1->id])
//                ->andWhere(['<', 'id', '2790'])
//                //->orWhere(['between', 'id', 3660, 3720])
                ->asArray()->orderBy('id asc')->limit('10')->all();
            $p1_10_child_uid = empty($p1_10_child) ? [] : array_column($p1_10_child, 'id');
            $p1_100_child = User::find()->select('id')->where(['pid' => $p1->id])
//                ->andWhere(['<', 'id', '2790'])
//                //->orWhere(['between', 'id', 3660, 3720])
                ->asArray()->orderBy('id asc')->offset(10)->limit(90)->all();
            $p1_100_child_uid = empty($p1_100_child) ? [] : array_column($p1_100_child, 'id');

            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } else {
                    if (in_array($user->id, $p1_10_child_uid)) {
                        //店主 前10个 直属会员给 100
                        $this->add_sub($from_id, $p1->id, 100, 1);
                    }else {
                        //10个之后给200
                        $this->add_sub($from_id, $p1->id, 200, 1);
                    }
                }
            }
            if ($p1->level_id == 3) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } elseif(10 <= $p1_parent_count && $p1_parent_count < 100) {

                    if (in_array($user->id, $p1_10_child_uid)) {
                        //店主 前10个 直属会员给 100
                        $this->add_sub($from_id, $p1->id, 100, 1);
                    } elseif (in_array($user->id, $p1_100_child_uid)) {
                        //店主 前10个 直属会员给 100
                        $this->add_sub($from_id, $p1->id, 200, 1);
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
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
//                ->andWhere(['<', 'id', '2790'])
//                //->orWhere(['between', 'id', 3660, 3720])
                ->count();
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
//                ->andWhere(['<', 'id', '2790'])
//                //->orWhere(['between', 'id', 3660, 3720])
                ->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])
                //->orWhere(['between', 'id', 3660, 3720])
                ->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');
            if ($p2->id == '2529' && $user->id == '2531' && $p1->id == '2530') {
                Yii::warning($p2_parent_count);
                Yii::warning($p2_10_child_uid);
                Yii::warning($p2_100_child_uid);
            }
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
                    if (in_array($user->parent->id, $p2_10_child_uid)) {

                        $this->add_sub($from_id, $p2->id, 33, 2);
                    } elseif (in_array($user->parent->id, $p2_100_child_uid)) {

                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 33, 2);
                    }
                }
            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }

                if ($p2_parent_count < 10 ) {
                    $this->add_sub($from_id, $p2->id, 33, 2);
                } elseif ($p2_parent_count >= 10 && $p2_parent_count <= 100) {
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 33, 2);
                    } elseif (in_array($user->parent->id, $p2_100_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    }
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
                $p2_parent_count = User::find()->where(['pid' => $p2->id])
//                    ->andWhere(['<', 'id', '2790'])
//                    ->orWhere(['between', 'id', 3660, 3720])
                    ->count();
                $p3_parent_count = User::find()->where(['pid' => $p3->id])
//                    ->andWhere(['<', 'id', '2790'])
//                    ->orWhere(['between', 'id', 3660, 3720])
                    ->count();
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



    /**
     * 初始数据 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_subsidy($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            $p1_parent_count = User::find()->where(['pid' => $p1->id])
                ->andWhere(['<', 'id', '2790'])->count();

            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } else {
                    $p1_parent_child_ids = User::find()->select('id')->where(['pid' => $p1->id])
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
                    $p1_parent_child_ids = User::find()->select('id')->where(['pid' => $p1->id])
                        ->asArray()->orderBy('id asc')->limit(10)->all();
                    if (!empty($p1_parent_child_ids) && is_array($p1_parent_child_ids)) {
                        $p1_parent_child_ids = array_column($p1_parent_child_ids, 'id');
                        if (in_array($user->id, $p1_parent_child_ids)) {
                            $this->add_sub($from_id, $p1->id, 100, 1);
                        }
                    }
                    $p1_parent_child_ids2 = User::find()->select('id')->where(['pid' => $p1->id])
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
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
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
                    $p1_parent_child_ids = User::find()->select('id')->where(['pid' => $p2->id])
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
                    $p1_parent_child_ids = User::find()->select('id')->where(['pid' => $p2->id])
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
                $p2_parent_count = User::find()->where(['pid' => $p2->id])
                    ->andWhere(['<', 'id', '2790'])->count();
                $p3_parent_count = User::find()->where(['pid' => $p3->id])
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

    /**
     * 初始数据 第二批 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_subsidy2($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            $p1_parent_count = User::find()->where(['pid' => $p1->id])
                ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->count();

            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } else {
                    $p1_parent_child_ids = User::find()->select('id')->where(['pid' => $p1->id])
                        ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])
                        ->asArray()->orderBy('id asc')->limit('10')->all();
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
                    $p1_parent_child_ids = User::find()->select('id')->where(['pid' => $p1->id])
                        ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])
                        ->asArray()->orderBy('id asc')->limit(10)->all();
                    if (!empty($p1_parent_child_ids) && is_array($p1_parent_child_ids)) {
                        $p1_parent_child_ids = array_column($p1_parent_child_ids, 'id');
                        if (in_array($user->id, $p1_parent_child_ids)) {
                            $this->add_sub($from_id, $p1->id, 100, 1);
                        }
                    }
                    $p1_parent_child_ids2 = User::find()->select('id')->where(['pid' => $p1->id])
                        ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])
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
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->count();
            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }

            if ($p2->userLevel->id == 2) {
                if ($user->parent->level_id !=2 && !empty($p2->parent) && $p2->level_id ==2 && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                //$this->add_sub($from_id,$p2->id, 100, 2);
                if ($p2_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p2->id, 33, 2);
                } elseif ($p2_parent_count == 10) {
                    //$this->add_sub($from_id, $p2->id, 100, 2);
                    $p1_parent_child_ids = User::find()->select('id')->where(['pid' => $p1->id])
                        ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('10')->all();
                    if (!empty($p1_parent_child_ids) && is_array($p1_parent_child_ids)) {
                        $p1_parent_child_ids = array_column($p1_parent_child_ids, 'id');
                        if (in_array($user->id, $p1_parent_child_ids)) {
                            $this->add_sub($from_id, $p1->id, 33, 1);
                        }
                    } else {
                        $this->add_sub($from_id, $p1->id, 100, 1);
                    }
                } else {
                    $this->add_sub($from_id, $p2->id, 100, 2);
                }
            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }
                //$this->add_sub($from_id,$p2->id, 150, 2);

                if ($p2_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p2->id, 33, 2);
                    //$this->add_sub($from_id, $p2->id, 100, 2);
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
                $p2_parent_count = User::find()->where(['pid' => $p2->id])
                    ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->count();
                $p3_parent_count = User::find()->where(['pid' => $p3->id])
                    ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->count();
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

    /**
     * 初始数据 第二批 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_subsidy2_test($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            $p1_parent_count = User::find()->where(['pid' => $p1->id])
                ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->count();

            $p1_10_child = User::find()->select('id')->where(['pid' => $p1->id])
                ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->asArray()->orderBy('id asc')->limit('10')->all();
            $p1_10_child_uid = empty($p1_10_child) ? [] : array_column($p1_10_child, 'id');
            $p1_100_child = User::find()->select('id')->where(['pid' => $p1->id])
                ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->asArray()->orderBy('id asc')->offset(10)->limit(90)->all();
            $p1_100_child_uid = empty($p1_100_child) ? [] : array_column($p1_100_child, 'id');

            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } else {
                    if (in_array($user->id, $p1_10_child_uid)) {
                        //店主 前10个 直属会员给 100
                        $this->add_sub($from_id, $p1->id, 100, 1);
                    }else {
                        //10个之后给200
                        $this->add_sub($from_id, $p1->id, 200, 1);
                    }
                }
            }
            if ($p1->level_id == 3) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } elseif(10 <= $p1_parent_count && $p1_parent_count < 100) {

                    if (in_array($user->id, $p1_10_child_uid)) {
                        //店主 前10个 直属会员给 100
                        $this->add_sub($from_id, $p1->id, 100, 1);
                    } elseif (in_array($user->id, $p1_100_child_uid)) {
                        //店主 前10个 直属会员给 100
                        $this->add_sub($from_id, $p1->id, 200, 1);
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
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->count();
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');
            if ($p2->id == '2529' && $user->id == '2531' && $p1->id == '2530') {
                Yii::warning($p2_parent_count);
                Yii::warning($p2_10_child_uid);
                Yii::warning($p2_100_child_uid);
            }
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
                    if (in_array($user->parent->id, $p2_10_child_uid)) {

                        $this->add_sub($from_id, $p2->id, 33, 2);
                    } elseif (in_array($user->parent->id, $p2_100_child_uid)) {

                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 33, 2);
                    }
                }
            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }

                if ($p2_parent_count < 10 ) {
                    $this->add_sub($from_id, $p2->id, 33, 2);
                } elseif ($p2_parent_count >= 10 && $p2_parent_count <= 100) {
                    if (in_array($user->parent->id, $p2_10_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 33, 2);
                    } elseif (in_array($user->parent->id, $p2_100_child_uid)) {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    } else {
                        $this->add_sub($from_id, $p2->id, 100, 2);
                    }
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
                $p2_parent_count = User::find()->where(['pid' => $p2->id])
                    ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->count();
                $p3_parent_count = User::find()->where(['pid' => $p3->id])
                    ->andWhere(['<=', 'id', '3719'])->andWhere(['>=', 'id', '3660'])->count();
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

    /**
     * 初始数据  邀请了一部人  但是需要后台激活 这部分人 补贴计算
     * @param $user User
     * @return bool
     */
    public function init_handle_subsidy($user)
    {
        //直接1级
        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            $p1_parent_count = User::find()->where(['pid' => $p1->id])->count();

            if ($p1->level_id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->level_id == 2) {
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } else {
                    $p1_parent_child_ids = User::find()->select('id')->where(['pid' => $p1->id])->asArray()->orderBy('id asc')->limit('10')->all();
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
                if ($p1_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p1->id, 100, 1);
                } elseif(10 < $p1_parent_count && $p1_parent_count < 100) {
                    $p1_parent_child_ids = User::find()->select('id')->where(['pid' => $p1->id])
                        ->asArray()->orderBy('id asc')->limit(10)->all();
                    if (!empty($p1_parent_child_ids) && is_array($p1_parent_child_ids)) {
                        $p1_parent_child_ids = array_column($p1_parent_child_ids, 'id');
                        if (in_array($user->id, $p1_parent_child_ids)) {
                            $this->add_sub($from_id, $p1->id, 100, 1);
                        }
                    }
                    $p1_parent_child_ids2 = User::find()->select('id')->where(['pid' => $p1->id])
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
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->count();
            $is_three = 0;
            if ($p2->userLevel->id == 1) {
                $this->add_sub($from_id,$p2->id, 33, 2);
            }

            if ($p2->userLevel->id == 2) {
                if ($user->parent->level_id !=2 && !empty($p2->parent) && $p2->level_id ==2 && $p2->parent->level_id >= 2) {
                    $is_three = 1;
                }
                //$this->add_sub($from_id,$p2->id, 100, 2);
                if ($p2_parent_count <= 10 ) {
                    $this->add_sub($from_id, $p2->id, 33, 2);
                } else {
                    $this->add_sub($from_id, $p2->id, 100, 2);
                }
            }
            if ($p2->userLevel->id == 3) {
                if ($p2->parent && $p2->level_id == 3 && $p2->parent->level_id >= 3) {
                    $is_three = 1;
                }
                //$this->add_sub($from_id,$p2->id, 150, 2);

                if ($p2_parent_count <= 10 ) {
                    //$this->add_sub($from_id, $p2->id, 100, 2);
                    $this->add_sub($from_id, $p2->id, 33, 2);
                } elseif(10 < $p2_parent_count && $p2_parent_count <= 100) {
                    $this->add_sub($from_id, $p2->id, 100, 2);
                } else {
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
                $p2_parent_count = User::find()->where(['pid' => $p2->id])->count();
                $p3_parent_count = User::find()->where(['pid' => $p3->id])->count();
                if ($p2->level_id == 2 && $p3->userLevel->id == 2 && $p2_parent_count <= 10) {
                    $this->add_sub($from_id, $p3->id, 15, 3);
                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3 && $p2_parent_count >= 10 && $p3_parent_count >= 100) {
                    $this->add_sub($from_id, $p3->id, 50, 3);
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3 && $p2_parent_count >=100 && $p3_parent_count >= 100) {
                    $this->add_sub($from_id, $p3->id, 10, 3);
                }
            }
        }
        return true;
    }

    /**
     * 后台手动激活账号
     * @param $user User
     * @return bool
     */
    public function hand_activate($user){
        //先判断 该用户上级 ==>> 店主 ==>> 店主
        //上级 ==>> 店主 ==>> 服务商
        //上级 ==>> 服务商 ==>> 服务商
        //上级 ==>> 服务商 ==>> 店主

        $from_id = $user->id;
        if ($user->parent) {
            $p1 = $user->parent;
            Yii::warning($p1->userLevel->id);
            Yii::warning($p1->userLevel);
            if ($p1->userLevel->id == 1) {
                $this->add_sub($from_id, $p1->id, 100, 1);
            }
            if ($p1->userLevel->id == 2) {
                //此处还要判断是否 是真实店主  满10个
                //$this->add_sub($from_id, $p1->id, 200, 1);
                $this->add_sub($from_id, $p1->id, 100, 1);
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
        return true;

    }

    public function add_sub($from_id, $to_id, $money, $type)
    {
        $log = UserSubsidy::find()->where(['from_uid' => $from_id, 'to_uid' => $to_id])->one();
        if (!empty($log)) {
            return true;
        }
        //echo $to_id. '新增一条记录 <br>';
        $trans = Yii::$app->db->beginTransaction();
        try {
            $to = User::findOne($to_id);
            $sub_log = new UserSubsidy();
            $sub_log->from_uid = $from_id;
            $sub_log->to_uid = $to_id;
            $sub_log->money = $money;
            $sub_log->type = $type;
            $sub_log->to_user_level = "$to->level_id";
            $sub_log->create_time = time();
            if(!$sub_log->save()) {
                Yii::error($sub_log->errors);
                $errors = $sub_log->errors;
                $error = array_shift($errors)[0];
                //var_dump($error);
                throw new Exception('无法补贴记录：' . $error);
            }
            $r = User::updateAllCounters(['subsidy_money' => $sub_log->money],['id' => $to_id]);
            //var_dump($r);
            //echo '<br>';
            if ($r <=0) {
                throw new Exception('更新补贴失败');
            }
            // 增加账户明细记录
            $user_account_log = new UserAccountLog();
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

    public function add_level_sub($from_id, $to_id, $money, $type, $level_id)
    {
        //echo $to_id. '新增一条记录 <br>';
        $trans = Yii::$app->db->beginTransaction();
        try {
            $to = User::findOne($to_id);
            $sub_log = new UserSubsidy();
            $sub_log->from_uid = $from_id;
            $sub_log->to_uid = $to_id;
            $sub_log->money = $money;
            $sub_log->type = $type;
            $sub_log->to_user_level = "$level_id";
            $sub_log->create_time = time();
            if(!$sub_log->save()) {
                Yii::error($sub_log->errors);
                $errors = $sub_log->errors;
                $error = array_shift($errors)[0];
                //var_dump($error);
                throw new Exception('无法补贴记录：' . $error);
            }
            $r = User::updateAllCounters(['subsidy_money' => $sub_log->money],['id' => $to_id]);
            //var_dump($r);
            //echo '<br>';
            if ($r <=0) {
                throw new Exception('更新补贴失败');
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
     * @param $user User
     * @return User
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
     * @param $user User
     * @return User
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
     * @param $user User
     * @return User
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
     * @param $user User
     * @return int
     */
    public function check_level($user)
    {
        if ($user->level_id == 1) {
            return 1;
        } elseif ($user->level_id == 2) {
            //检测自己是否已经推荐了10人  真实的成为 店主  还是 还应该按照会员待遇
            $child_ids = User::find()->select('id')->where(['pid' => $user->id])->asArray()->orderBy('id asc')->count();
            if ($child_ids <= 10) {
                return 1;
            } else {
                return 2;
            }
        } elseif ($user->level_id == 3) {
            //检测自己是否已经推荐了10人  真实的成为 店主  还是 还应该按照会员待遇
            $child_ids = User::find()->select('id')->where(['pid' => $user->id])->asArray()->orderBy('id asc')->limit('10')->count();
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
     * @param $user User
     * @return  bool
     */
    public function strong_level($user)
    {
        //先判断 pid team_pid是否一致  一直直接走原来的  不一致 先给直接上级
        if ($user->pid == $user->team_pid) {
            //直接一级
            if ($user->teamParents && $user->teamParents->level_id >= 1) {
                $this->add_growth($user->id, $user->teamParents->id, 399, 1);
                $to_user = User::findOne($user->teamParents->id);
                //升级
                $this->up_level($to_user);
            }
        } else {
            //直接一级
            if ($user->parent && $user->parent->level_id >= 1) {
                $this->add_growth($user->id, $user->parent->id, 399, 1);
                $to_user = User::findOne($user->parent->id);
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
                $to_user = User::findOne($parent->id);
                //升级
                $this->up_level($to_user);
            }
        }
        return true;
    }

    /**
     * 增加 计算 成长值 并且自动升级
     * @param $user User
     * @return  bool
     */
    public function strong_level_bak($user)
    {
        //直接一级
        if ($user->teamParents && ($user->teamParents->level_id == 1 || $user->teamParents->level_id == 2 || $user->teamParents->level_id == 3)) {
            $this->add_growth($user->id, $user->teamParents->id, 399, 1);
            $to_user = User::findOne($user->teamParents->id);
            //升级
            $this->up_level($to_user);
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
                $to_user = User::findOne($parent->id);
                //升级
                $this->up_level($to_user);
            }
        }
        return true;
    }

    public function add_growth($from_id, $to_id, $money, $type)
    {
        $log = UserGrowth::find()->where(['from_uid' => $from_id, 'to_uid' => $to_id])->one();
        if (!empty($log)) {
            return true;
        }
        //echo $to_id. '新增一条记录 <br>';
        $trans = Yii::$app->db->beginTransaction();
        try {
            $sub_log = new UserGrowth();
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
            $r = User::updateAllCounters(['growth_money' => $sub_log->money],['id' => $to_id]);
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
     * @param $user User
     * @return bool
     */
    public function up_level($user)
    {
        if ($user->level_id == 1 && $user->growth_money >= 8000) {
            $user->level_id = 2;
            $user->save();
            $userLevelLog = new UserLevelLog();
            $userLevelLog->uid = $user->id;
            $userLevelLog->level_id = 2;
            $userLevelLog->remark = '会员升店主';
            $userLevelLog->create_time = time();
            $userLevelLog->save();
        }
        if ($user->level_id == 2 && $user->growth_money >= 300000) {
            $childCount = User::find()->where(['level_id' => 2, 'status' => User::STATUS_OK, 'pid' => $user->id])->count();
            if ($childCount >=2) {
                $user->level_id = 3;
                $user->save();
                $userLevelLog = new UserLevelLog();
                $userLevelLog->uid = $user->id;
                $userLevelLog->level_id = 3;
                $userLevelLog->remark = '店主升服务商';
                $userLevelLog->create_time = time();
                $userLevelLog->save();
            }
        }
    }

    /**
     * 初始数据 成长值计算
     * @param $user User
     * @return bool
     */
    public function init_growth_test2($user)
    {
        //直接1级
        $from_id = $user->id;

        if (!empty($user->teamParents)) {
            $p1 = $user->teamParents;
            $this->add_growth($from_id, $p1->id, 399, 1);
        }
        $is_three = 0;
        //直接2级
        //先判断 上级  是 店主  还是服务商
        //是店主  也需要看按店主返  还是按照10个 的会员身份返
        //是服务商 也要看 是否有100个名额 给  返
        if (!empty($user->teamParents) && $user->teamParents && $user->teamParents->teamParents) {
            $p2 = $user->teamParents->teamParents;
            $p1 = $user->teamParents;
            $p2_parent_count = User::find()->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->count();
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');

            $is_three = 0;

            if ($p2->userLevel->id == 2) {
                if ($user->teamParents->level_id !=2 && !empty($p2->teamParents) && $p2->level_id ==2 && $p2->teamParents->level_id >= 2) {
                    $is_three = 1;
                }
                if ($p1->level_id == 2) {
                    //前10
                    if (in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p2->id, 399, 2);
                    }
                }

            }
            if ($p2->userLevel->id == 3) {
                if ($p2->teamParents && $p2->level_id == 3 && $p2->teamParents->level_id >= 3) {
                    $is_three = 1;
                }
                if ($p1->level_id == 2) {
                    //前10无
                    if (in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p2->id, 399, 2);
                    }
                }
                if ($p1->level_id == 3) {

                    if (in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p2->id, 399, 2);
                    } elseif(in_array($user->teamParents->id, $p2_100_child_uid)) {
                        $this->add_growth($from_id, $p2->id, 399, 2);
                    }
                }
            }

        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->teamParents && $user->teamParents->teamParents && $user->teamParents->teamParents->teamParents) {
                $p2 = $user->teamParents->teamParents;
                $p3 = $this->teamParents->teamParents->teamParents;
                $p3_10_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->limit('9')->all();
                $p3_10_child_uid = empty($p3_10_child) ? [] : array_column($p3_10_child, 'id');
                $p3_100_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->andWhere(['<', 'id', '2790'])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
                $p3_100_child_uid = empty($p3_100_child) ? [] : array_column($p3_100_child, 'id');
                if ($p2->level_id == 1 && $p3->userLevel->id == 2) {
                    //店主3级
                    $this->add_growth($from_id, $p3->id, 399, 2);
                }
                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    //店主3级
                    if (in_array($p2->id, $p3_10_child_uid)) {
                        $this->add_growth($from_id, $p3->id, 399, 2);
                    }

                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    //育成店主了的服务商拿 50
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p3->id, 399, 2);
                    }
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    //育成了服务商的服务商拿 10
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p3->id, 399, 2);
                    }
                }
            }
        }

        //不是直接3级  无限往上找 找到店主 或者 服务商
        if ($user->level_id == 1 && (!empty($user->teamParents->teamParents) && ($user->teamParents->teamParents->level_id ==1) && !empty($user->teamParents->teamParents->teamParents))) {
            //无限找上级
            $parent = $this->tree($user->teamParents->teamParents);
            if ($parent->level_id == 2) {
                $this->add_growth($from_id, $parent->id, 399, 2);
            }
        }
        return true;
    }

    /**
     * 初始数据 成长值计算
     * @param $user User
     * @return bool
     */
    public function init_growth_test3($user)
    {
        //直接1级
        $from_id = $user->id;
        if (!empty($user->teamParents)) {
            $p1 = $user->teamParents;
            $this->add_growth($from_id, $p1->id, 399, 1);
        }
        $is_three = 0;
        //直接2级
        //先判断 上级  是 店主  还是服务商
        //是店主  也需要看按店主返  还是按照10个 的会员身份返
        //是服务商 也要看 是否有100个名额 给  返
        if (!empty($user->teamParents) && $user->teamParents && $user->teamParents->teamParents) {
            $p2 = $user->teamParents->teamParents;
            $p1 = $user->teamParents;
            $p2_10_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['between', 'id', 3660, 3720])->asArray()->orderBy('id asc')->limit('9')->all();
            $p2_10_child_uid = empty($p2_10_child) ? [] : array_column($p2_10_child, 'id');
            $p2_100_child = User::find()->select('id')->where(['pid' => $p2->id])
                ->andWhere(['between', 'id', 3660, 3720])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
            $p2_100_child_uid = empty($p2_100_child) ? [] : array_column($p2_100_child, 'id');

            $is_three = 0;

            if ($p2->userLevel->id == 2) {
                if ($user->teamParents->level_id !=2 && !empty($p2->teamParents) && $p2->level_id ==2 && $p2->teamParents->level_id >= 2) {
                    $is_three = 1;
                }
                if ($p1->level_id == 2) {
                    //前10
                    if (in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p2->id, 399, 2);
                    }
                }

            }
            if ($p2->userLevel->id == 3) {
                if ($p2->teamParents && $p2->level_id == 3 && $p2->teamParents->level_id >= 3) {
                    $is_three = 1;
                }
                if ($p1->level_id == 2) {
                    //前10无
                    if (in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p2->id, 399, 2);
                    }
                }
                if ($p1->level_id == 3) {

                    if (in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p2->id, 399, 2);
                    } elseif(in_array($user->teamParents->id, $p2_100_child_uid)) {
                        $this->add_growth($from_id, $p2->id, 399, 2);
                    }
                }
            }

        }

        //直接3级  只有 第二级  是店主 或者 服务商的时候  才考虑要不要给 育成了店主服务商的  店主或者服务商 补贴
        if ($is_three == 1) {
            if ($user->teamParents && $user->teamParents->teamParents && $user->teamParents->teamParents->teamParents) {
                $p2 = $user->teamParents->teamParents;
                $p3 = $this->teamParents->teamParents->teamParents;
                $p3_10_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->andWhere(['between', 'id', 3660, 3720])->asArray()->orderBy('id asc')->limit('9')->all();
                $p3_10_child_uid = empty($p3_10_child) ? [] : array_column($p3_10_child, 'id');
                $p3_100_child = User::find()->select('id')->where(['pid' => $p3->id])
                    ->andWhere(['between', 'id', 3660, 3720])->asArray()->orderBy('id asc')->offset(9)->limit(90)->all();
                $p3_100_child_uid = empty($p3_100_child) ? [] : array_column($p3_100_child, 'id');
                if ($p2->level_id == 1 && $p3->userLevel->id == 2) {
                    //店主3级
                    $this->add_growth($from_id, $p3->id, 399, 2);
                }
                if ($p2->level_id == 2 && $p3->userLevel->id == 2) {
                    //店主3级
                    if (in_array($p2->id, $p3_10_child_uid)) {
                        $this->add_growth($from_id, $p3->id, 399, 2);
                    }

                }
                if ($p2->level_id ==2 && $p3->userLevel->id == 3) {
                    //育成店主了的服务商拿 50
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p3->id, 399, 2);
                    }
                }
                if ($p2->level_id ==3 && $p3->userLevel->id == 3) {
                    //育成了服务商的服务商拿 10
                    if (in_array($p2->id, $p3_10_child_uid) || in_array($user->teamParents->id, $p2_10_child_uid)) {
                        $this->add_growth($from_id, $p3->id, 399, 2);
                    }
                }
            }
        }

        //不是直接3级  无限往上找 找到店主 或者 服务商
        if ($user->level_id == 1 && (!empty($user->teamParents->teamParents) && ($user->teamParents->teamParents->level_id ==1) && !empty($user->teamParents->teamParents->teamParents))) {
            //无限找上级
            $parent = $this->tree($user->teamParents->teamParents);
            if ($parent->level_id == 2) {
                $this->add_growth($from_id, $parent->id, 399, 2);
            }
        }
        return true;
    }

    /**
     * 预估佣金收益
     * @return float $total_commission
     */
    public function getComputeCommission()
    {
        $total_commission = 0;
        $toUser = User::findOne($this->id);
        if ($toUser->level_id == 1) {
            $share_commission_ratio_1 = 30;
        } elseif ($toUser->level_id == 2) {
            $share_commission_ratio_1 = 40;
        } elseif ($toUser->level_id == 3) {
            $share_commission_ratio_1 = 50;
        }
        $queryUser = User::find();
        $userList = $queryUser->andWhere(['pid' => $this->id])->asArray()->all();
        $userList = array_column($userList, 'id');

        $queryOrder = Order::find();
        $queryOrder->where(['in', 'uid', $userList])->andWhere(['<>', 'status', 0])
            //->andWhere(["<", 'status', Order::STATUS_COMPLETE]);
            ->andWhere(["BETWEEN", 'status', Order::STATUS_PAID, Order::STATUS_COMPLETE]);
        /** @var Order $model */
        foreach ($queryOrder->each() as $model) {
            $share_commission_ratio_2 = 0;
            if ($model->user->status == User::STATUS_OK) {
                if ($model->user->level_id == 1) {
                    $share_commission_ratio_2 = 30;
                } elseif ($model->user->level_id == 2) {
                    $share_commission_ratio_2 = 40;
                } elseif ($model->user->level_id == 3) {
                    $share_commission_ratio_2 = 50;
                }
            }
            if ($model->user->status == User::STATUS_OK) {
                $share_commission_ratio_1 = 30;
            }
            /** @var OrderItem $item */
            foreach ($model->itemList as $item) {
                if (!in_array($item->goods->share_commission_type, [Goods::SHARE_COMMISSION_TYPE_MONEY, Goods::SHARE_COMMISSION_TYPE_RATIO])) {
                    // 此商品不参与分享佣金
                    continue;
                }
                if ($item->goods->is_pack == 1) {
                    continue;
                }
                // 一级分享
                if (empty($share_commission_ratio_1) || Util::comp($share_commission_ratio_1, 0, 2) <= 0) {
                    // 店铺没有设置一级分享佣金比例
                    continue;
                }
                $item_commission_1 = 0;


                if ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_MONEY) { // 固定金额
                    //$item_commission_1 = round($item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 100, 2);
                    if ($share_commission_ratio_2 != 0) {
                        $item_commission_1 = round(($item->goods->share_commission_value * $share_commission_ratio_2 * $item->amount / 100) * $share_commission_ratio_1 / 100, 2);
                    } else {
                        $item_commission_1 = round($item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 100, 2);
                    }
                } elseif ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_RATIO) { // 百分比
                    $item_commission_1 = round($item->price * $item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 10000, 2);
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
        $money = UserWithdraw::find()->where(['uid' => $this->id])->andWhere(['status' => UserWithdraw::STATUS_FINISH])->sum('money');
        return $money * 5 / 100;
    }

    /**
     * 获取自己自购 省钱比率
     */
    public function getBuyRatio()
    {
        $ratio = 0;
        if ($this->status == User::STATUS_OK) {
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
        if ($this->status == User::STATUS_OK) {
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
            $r = UserAccount::updateAllCounters(['score' => 400], ['uid' => $this->id]);
            if ($r <=0) {
                throw new Exception('更新积分失败。');
            }
            $userAccountLog = new UserAccountLog;
            $userAccountLog->uid = $this->id;
            $userAccountLog->score = 400;
            $userAccountLog->remark = '激活会员统一给400积分';
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
     * 获取销售记录个数
     */
    public function getSaleCount()
    {
        return UserSaleLog::find()->where(['uid' => $this->id])->count();
    }

    /**
     * 获取团队个数
     */
    public function getTeamCount()
    {
        return User::find()->where(['pid' => $this->id])->count();
    }

    /**
     * 获取团队激活个数
     */
    public function getTeamActiveCount()
    {
        return User::find()->where(['pid' => $this->id, 'status' => User::STATUS_OK])->count();
    }

    /**
     * 获取团队激活个数
     */
    public function getTeamNotActiveCount()
    {
        return User::find()->where(['pid' => $this->id, 'status' => User::STATUS_WAIT])->count();
    }

    /**
     *分佣比率
     * 会员 1.直接销售每单30% 2.直属会员月分销总额30%
     * 店主 1.直接销售每单40% 2.直属团队每人的分销总额30%  3.育成店主月结算佣金的30%
     * 服务商 1.直接销售每单50% 2.直属团队每人的分销总额30%  3.育成店主月结算佣金的30% 4.育成服务商月结算佣金的30%
     */
}
