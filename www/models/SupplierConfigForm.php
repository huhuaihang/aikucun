<?php

namespace app\models;

use yii\base\Model;

/**
 * 供货商设置表单
 * Class SupplierConfigForm
 * @package app\models
 */
class SupplierConfigForm extends Model
{
    /**
     * @var integer 供货商编号
     */
    public $sid;
    /**
     * @var string 收货人
     */
    public $refund_deliver_user;
    /**
     * @var string 收货地址
     */
    public $refund_deliver_address;
    /**
     * @var string 联系手机号码
     */
    public $refund_deliver_mobile;
    /**
     * @var string 附加信息
     */
    public $refund_deliver_remark;

    /**
     * @var string 开户银行
     */
    public $finance_bank_name;

    /**
     * @var string 开户行所在地
     */
    public $finance_bank_addr;

    /**
     * @var string 账户名
     */
    public $finance_bank_account_name;

    /**
     * @var string 银行账号
     */
    public $finance_bank_account;

    /**
     * @var string 支付宝姓名
     */
    public $finance_alipay_name;

    /**
     * @var string 支付宝账号
     */
    public $finance_alipay_account;

    /**
     * @var string 微信账号
     */
    public $finance_weixin_account;

    /**
     * @inheritdoc
     * @param $sid integer 供货商编号
     */
    public function __construct($sid)
    {
        $this->sid = $sid;
        parent::__construct([]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $attributes = [
            'refund_deliver_user', 'refund_deliver_address', 'refund_deliver_mobile', 'refund_deliver_remark',
            'finance_bank_name', 'finance_bank_addr', 'finance_bank_account_name', 'finance_bank_account',
            'finance_alipay_name', 'finance_alipay_account',
            'finance_weixin_account',
        ];
        foreach ($attributes as $key) {
            $this->$key = SupplierConfig::getConfig($this->sid, $key);
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['refund_deliver_user', 'refund_deliver_address', 'refund_deliver_mobile', 'refund_deliver_remark'], 'safe'],
            [['finance_bank_name', 'finance_bank_addr', 'finance_bank_account_name', 'finance_bank_account'], 'safe'],
            [['finance_alipay_name', 'finance_alipay_account'], 'safe'],
            [['finance_weixin_account'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'refund_deliver_user' => '收货人',
            'refund_deliver_address' => '收货地址',
            'refund_deliver_mobile' => '联系手机',
            'refund_deliver_remark' => '附加信息',
            'finance_bank_name' => '开户银行',
            'finance_bank_addr' => '开户行所在地',
            'finance_bank_account_name' => '账户名',
            'finance_bank_account' => '银行账号',
            'finance_alipay_name' => '支付宝姓名',
            'finance_alipay_account' => '支付宝账号',
            'finance_weixin_account' => '微信账号',
        ];
    }

    /**
     * 保存设置信息
     * @return boolean
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $supplier = Supplier::findOne(['id' => $this->sid]);
        if (empty($supplier)) {
            $this->addError('sid', '没有找到供货商。');
            return false;
        }
        $attributes = [
            'refund_deliver_user', 'refund_deliver_address', 'refund_deliver_mobile', 'refund_deliver_remark',
            'finance_bank_name', 'finance_bank_addr', 'finance_bank_account_name', 'finance_bank_account',
            'finance_alipay_name', 'finance_alipay_account',
            'finance_weixin_account',
        ];
        foreach ($attributes as $key) {
            SupplierConfig::setConfig($supplier->id, $key, $this->$key);
        }
        return true;
    }
}
