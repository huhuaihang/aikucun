<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 代理商申请入驻表单
 * Class AgentJoinForm
 * @package app\models
 */
class AgentJoinForm extends Model
{
    /**
     * @var string 区域编码
     */
    public $area;
    /**
     * @var string 联系人姓名
     */
    public $contact_name;
    /**
     * @var string 手机号码
     */
    public $mobile;
    /**
     * @var string 手机验证码
     */
    public $sms_code;
    /**
     * @var string 代理商登录账号
     */
    public $username;
    /**
     * @var string 代理商登录密码
     */
    public $password;
    /**
     * @var string 身份证正面
     */
    public $id_card_front;
    /**
     * @var string 身份证背面
     */
    public $id_card_back;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['area', 'contact_name', 'mobile', 'sms_code', 'username', 'password', 'id_card_front', 'id_card_back'], 'required'],
            [['area', 'contact_name', 'mobile', 'sms_code'], 'string', 'max' => 32],
            [['username'], 'email'],
            [['username'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'area' => '所在地区',
            'contact_name' => '联系人姓名',
            'mobile' => '联系电话',
            'sms_code' => '短信验证码',
            'username' => '登录邮箱',
            'password' => '登录密码',
            'id_card_front' => '身份证正面',
            'id_card_back' => '身份证反面',
        ];
    }

    /**
     * 保存入驻信息
     * @param $uid integer 用户编号
     * @return boolean
     */
    public function save($uid)
    {
        if (!$this->validate()) {
            return false;
        }
        $exist_aid = UserConfig::getConfig($uid, 'join_agent');
        if (!empty($exist_aid)) {
            $agent = Agent::findOne($exist_aid);
            if ($agent->status != Agent::STATUS_REQUIRE) {
                $this->addError('username', '你之前的申请正在处理。');
                return false;
            }
        } else {
            $agent = Agent::find()
                ->andWhere(['username' => $this->username])
                ->andWhere(['<>', 'status', Agent::STATUS_DELETED])
                ->one();
            if (!empty($agent)) {
                $this->addError('username', '您输入的账号已被使用。');
                return false;
            }
        }
        $agent = Agent::find()
            ->andWhere(['mobile' => $this->mobile])
            ->andWhere(['<>', 'status', Agent::STATUS_DELETED])
            ->andWhere(['<>', 'status', Agent::STATUS_REQUIRE])
            ->one();
        if (!empty($agent)) {
            $this->addError('mobile', '您输入的联系电话已被使用。');
            return false;
        }
        if (!Sms::checkCode($this->mobile, Sms::TYPE_AGENT_JOIN, $this->sms_code)) {
            $this->addError('sms_code', '验证码错误。');
            return false;
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            if (!empty($exist_aid)) {
                $agent = Agent::findOne($exist_aid);
            } else {
                $agent = new Agent();
            }
            $agent->username = $this->username;
            $agent->password = Yii::$app->security->generatePasswordHash($this->password);
            $agent->mobile = $this->mobile;
            $agent->contact_name = $this->contact_name;
            $agent->area = $this->area;
            $agent->status = Agent::STATUS_WAIT_CONTACT;
            $agent->create_time = time();
            $r = $agent->save();
            if (!$r) {
                $errors = $agent->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法保存代理信息：' . $error);
            }
            AgentConfig::setConfig($agent->id, 'id_card_front', $this->id_card_front);
            AgentConfig::setConfig($agent->id, 'id_card_back', $this->id_card_back);
            AgentConfig::setConfig($agent->id, 'register_from_uid', $uid);
            UserConfig::setConfig($uid, 'join_agent', $agent->id);
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            $this->addError('username', '保存信息时出现错误：' . $e->getMessage());
        }
        return false;
    }
}
