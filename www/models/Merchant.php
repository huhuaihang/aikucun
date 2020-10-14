<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * 商户
 * Class Merchant
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $type 类型
 * @property integer $aid 代理商编号
 * @property string $username 登录邮箱账号
 * @property string $auth_key
 * @property string $password HASH密码
 * @property string $mobile 手机号
 * @property string $contact_name 联系人姓名
 * @property string $avatar 头像
 * @property integer $is_person 是否为个人
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 * @property string $remark 备注
 *
 * @property Shop $shop 关联店铺
 * @property Agent $agent 关联代理商
 */
class Merchant extends ActiveRecord implements IdentityInterface
{
    const TYPE_ONLINE = 1;
    const TYPE_OFFLINE = 2;

    const STATUS_REQUIRE       = 1; // 正在填写入驻申请
    const STATUS_WAIT_DATA1    = 2; // 基本填写完成等待数据审核
    const STATUS_DATA1_OK      = 3; // 客服审核通过，可登录商户后台，完善资料
    const STATUS_WAIT_DATA2    = 4; // 详细资料填写完成等待数据审核
    const STATUS_DATA2_OK      = 5; // 客服审核通过并确定保证金金额，等待支付
    const STATUS_COMPLETE      = 6; // 完成，此时可以正常使用
    const STATUS_STOPED = 9; // 停止
    const STATUS_DELETED = 0; // 删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'username', 'mobile'], 'required'],
            ['username', 'unique', 'filter' => ['<>', 'status', Merchant::STATUS_DELETED]],
            [['status', 'create_time'], 'integer'],
            [['username', 'password'], 'string', 'max' => 256],
            ['username', 'email'],
            [['auth_key', 'contact_name', 'mobile'], 'string', 'max' => 32],
            ['password', 'safe'],
            [['avatar'], 'string', 'max' => 128],
            [['password', 'avatar'], 'safe'],
            ['is_person', 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => '登录邮箱账号',
            'password' => '登录密码',
            'mobile' => '手机号码',
            'avatar' => '头像',
            'status' => '状态',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return Merchant::findOne($id);
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
     * 验证密码
     * @param string $password 明文密码
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $shop = new Shop();
            $shop->mid = $this->id;
            $shop->save();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * 关联店铺
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['mid' => 'id']);
    }

    /**
     * 关联代理商
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(Agent::className(), ['id' => 'aid']);
    }
}
