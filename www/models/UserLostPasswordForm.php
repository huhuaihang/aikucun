<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 用户找回密码表单
 * Class UserLostPasswordForm
 * @package app\models
 */
class UserLostPasswordForm extends Model
{
    /**
     * @var string 手机号码
     */
    public $mobile;
    /**
     * @var string 短信验证码
     */
    public $code;
    /**
     * @var string 新密码
     */
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'code', 'password'], 'required'],
            ['mobile', function () {
                if (!User::find()
                    ->andWhere(['mobile' => $this->mobile, 'status' => User::STATUS_OK])
                    ->exists()) {
                    $this->addError('mobile', '手机号码错误。');
                    return;
                }
            }],
            ['code', function () {
                if (!Sms::checkCode($this->mobile, Sms::TYPE_FORGOT_PASSWORD, $this->code)) {
                    $this->addError('code', '验证码错误。');
                    return;
                }
            }],
        ];
    }

    /**
     * 重置用户密码
     * @return bool
     */
    public function resetPassword()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = User::find()->andWhere(['mobile' => $this->mobile, 'status' => User::STATUS_OK])->one();
        try {
            $user->password = Yii::$app->security->generatePasswordHash($this->password);
        } catch (Exception $e) {
            $this->addError('password', '无法加密密码。');
        }
        return $user->save();
    }
}
