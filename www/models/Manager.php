<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * 管理员
 * Class Manager
 * @package app\models
 *
 * @property integer $id PK
 * @property string $username 用户名
 * @property string $auth_key
 * @property string $password HASH密码
 * @property string $nickname 昵称（姓名）
 * @property string $mobile 手机号码
 * @property string $email 电子邮箱
 * @property integer $rid 角色编号
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 *
 * @property ManagerRole $role 关联角色
 */
class Manager extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_STOPED = 9;
    const STATUS_DELETED = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', '!password', 'mobile', 'rid', 'status'], 'required'],
            [['username', 'nickname', 'email'], 'string', 'max' => 256],
            ['username', 'unique', 'filter' => ['<>', 'status', Manager::STATUS_DELETED]],
            ['mobile', 'string', 'max' => 32],
            ['email', 'email'],
            ['status', 'in', 'range' => [Manager::STATUS_ACTIVE, Manager::STATUS_STOPED, Manager::STATUS_DELETED]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'nickname' => '姓名',
            'username' => '用户名',
            'password' => '密码',
            'mobile' => '手机号',
            'email' => '邮箱',
            'rid' => '角色',
            'status' => '状态',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return Manager::findOne($id);
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
    public function validateAuthKey($auth_key)
    {
        return $this->auth_key === $auth_key;
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
     * 关联管理角色
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(ManagerRole::className(), ['id' => 'rid']);
    }
}
