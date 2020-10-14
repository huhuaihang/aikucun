<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 管理员账号设置表单
 * Class ManagerProfileForm
 * @package app\models
 */
class ManagerProfileForm extends Model
{
    /**
     * @var integer 管理员编号
     */
    public $id;
    /**
     * @var string 登录用户名
     */
    public $username;
    /**
     * @var string 设置的新密码明文
     */
    public $new_password;
    /**
     * @var string 重复输入的新密码明文
     */
    public $confirm_password;
    /**
     * @var string 昵称（姓名）
     */
    public $nickname;
    /**
     * @var string 手机号
     */
    public $mobile;
    /**
     * @var string 邮箱
     */
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'nickname', 'mobile', 'email'], 'required'],
            [['username', 'mobile', 'email'], 'unique', 'targetClass' => Manager::className(),
                'filter' => function ($query) {
                    /** @var $query \yii\db\Query */
                    $query
                        ->andWhere(['<>', 'id', $this->id])
                        ->andWhere(['<>', 'status', Manager::STATUS_DELETED]);
                    return $query;
                }
            ],
            [['new_password', 'confirm_password'], function () {
                if ($this->new_password != $this->confirm_password) {
                    $this->addError('new_password', '两次输入密码不同。');
                }
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'nickname' => '姓名',
            'mobile' => '手机号',
            'email' => '邮箱',
            'new_password' => '新密码',
            'confirm_password' => '确认密码',
        ];
    }

    /**
     * 保存账号设置
     * @return boolean
     */
    public function save()
    {
        $manager = Manager::findOne($this->id);
        $manager->username = $this->username;
        $manager->nickname = $this->nickname;
        $manager->mobile = $this->mobile;
        $manager->email = $this->email;
        if (!empty($this->new_password)) {
            try {
                $manager->password = Yii::$app->security->generatePasswordHash($this->new_password);
            } catch (Exception $e) {
                $this->addError('password', '无法加密密码。');
                return false;
            }
        }
        if ($manager->save()) {
            return true;
        }
        $this->addErrors($manager->errors);
        return false;
    }
}
