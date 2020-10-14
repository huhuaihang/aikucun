<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * 供货商
 * Class Supplier
 * @package app\models
 * @property integer $id PK
 * @property string $name 公司名称
 * @property string $mobile 手机号
 * @property string $auth_key auth_key
 * @property string $password 密码
 * @property integer $status 状态码
 * @property integer $create_time 创建时间
 *
 * @property User $profitUser 关联收益用户
 */
class Supplier extends ActiveRecord implements IdentityInterface
{
    const STATUS_OK = 1; // 通过
    const STATUS_STOP = 9; // 停用
    const STATUS_DEL = 0; // 删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'mobile'], 'required'],
            ['password', 'safe'],
            ['mobile', function () {
                /** @var Supplier $existSupplier */
                $existSupplier = Supplier::find()
                    ->andWhere(['mobile' => $this->mobile])
                    ->andWhere(['<>', 'status', Supplier::STATUS_DEL])
                    ->one();
                if (!empty($existSupplier) && $existSupplier->id != $this->id) {
                    $this->addError('mobile', '手机号已被占用。');
                    return;
                }
            }],
            ["password", "required", 'on' => 'add'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '公司名称',
            'mobile' => '手机号',
            'password' => '登录密码',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return Supplier::findOne(['id' => $id]);
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
     * @param string $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }
}
