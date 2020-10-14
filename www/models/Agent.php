<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * 代理商
 * Class Agent
 * @package app\models
 *
 * @property integer $id PK
 * @property string $username 登录邮箱账号
 * @property string $auth_key
 * @property string $password HASH密码
 * @property string $mobile 手机号
 * @property string $contact_name 联系人姓名
 * @property string $avatar 头像
 * @property string $area 区域编码
 * @property integer $earnest_money_fid 保证金财务记录编号
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 * @property string $remark 备注
 *
 * @property FinanceLog $earnestMoneyFinanceLog 关联保证金财务记录
 */
class Agent extends ActiveRecord implements IdentityInterface
{
    const STATUS_REQUIRE = 1; // 正在填写入驻申请
    const STATUS_WAIT_CONTACT = 2; // 填写完成等待客服沟通
    const STATUS_WAIT_INITIAL_FEE = 3; // 客服沟通完成等待财务审核加盟费
    const STATUS_WAIT_EARNEST_MONEY = 4; // 加盟费付款完成等待保证金付款
    const STATUS_WAIT_FINANCE = 5; // 保证金付款完成等待财务审核
    const STATUS_ACTIVE = 6; // 正常
    const STATUS_STOPED = 9; // 停止
    const STATUS_DELETED = 0; // 删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'status'], 'required'],
            ['username', 'email'],
            [['username'], 'string', 'max' => 256],
            ['username', 'unique', 'filter' => ['<>', 'status', Agent::STATUS_DELETED]],
            [['mobile', 'contact_name', 'area'], 'string', 'max' => 32],
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
            'status' => '状态',
            'area' => '区域',
            'contact_name' => '联系人姓名',
            'avatar' => '头像',
            'remark' => '备注',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return Agent::findOne($id);
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

    /**
     * 关联保证金财务记录
     * @return \yii\db\ActiveQuery
     */
    public function getEarnestMoneyFinanceLog()
    {
        return $this->hasOne(FinanceLog::className(), ['id' => 'earnest_money_fid']);
    }

    /**
     * 保证金支付结果
     * @param boolean $is_success 是否成功
     * @return boolean
     * @throws Exception
     */
    public function payNotify($is_success)
    {
        if (!$is_success) {
            return true;
        }
        if ($this->status != Agent::STATUS_WAIT_EARNEST_MONEY) {
            throw new Exception('入驻申请状态错误。');
        }
        $this->status = Agent::STATUS_WAIT_FINANCE;
        return $this->save();
    }
}
