<?php

namespace app\models;

use yii\base\Model;

/**
 * 店铺设置表单
 * Class ShopProfileFrom
 * @package app\models;
 */
class ShopProfileForm extends Model
{
    /**
     * @var integer 商户编号
     */
    public $mid;
    /**
     * @var string 店铺名称
     */
    public $name;
    /**
     * @var string 店铺LOGO
     */
    public $logo;
    /**
     * @var string 店铺关键字
     */
    public $keywords;
    /**
     * @var string 分享图片
     */
    public $share_img;
    /**
     * @var string 分享描述
     */
    public $share_desc;
    /**
     * @var string 客服电话
     */
    public $service_tel;
    /**
     * @var string 地区
     */
    public $area;

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
     * @var string 一级分享佣金
     */
    public $share_commission_ratio_1;
    /**
     * @var string 二级分享佣金
     */
    public $share_commission_ratio_2;
    /**
     * @var string 三级分享佣金
     */
    public $share_commission_ratio_3;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mid', 'name'], 'required'],
            ['name', 'string', 'length' => [4, 12]],
            [['logo', 'keywords', 'share_img', 'share_desc', 'service_tel', 'area',
                'refund_deliver_user', 'refund_deliver_address', 'refund_deliver_mobile', 'refund_deliver_remark',
                'finance_bank_name', 'finance_bank_addr', 'finance_bank_account_name', 'finance_bank_account',
                'finance_alipay_name', 'finance_alipay_account', 'finance_weixin_account', 'share_commission_ratio_1',
                'share_commission_ratio_2', 'share_commission_ratio_3'
            ], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '店铺名称',
            'logo' => '店铺LOGO',
            'keywords' => '店铺关键字',
            'share_img' => '分享图片',
            'share_desc' => '分享描述',
            'service_tel' => '客服电话',
            'area' => '地区',
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
            'share_commission_ratio_1' => '一级分享佣金',
            'share_commission_ratio_2' => '二级分享佣金',
            'share_commission_ratio_3' => '三级分享佣金',
        ];
    }

    /**
     * 加载表单默认值
     */
    public function loadDefault()
    {
        /** @var Shop $shop */
        $shop = Shop::find()->where(['mid' => $this->mid])->one();
        $this->name = $shop->name;
        $this->area = $shop->area;
        $attributes = ['logo', 'keywords', 'share_img', 'share_desc', 'service_tel',
            'refund_deliver_user', 'refund_deliver_address', 'refund_deliver_mobile', 'refund_deliver_remark',
            'finance_bank_name', 'finance_bank_addr', 'finance_bank_account_name', 'finance_bank_account',
            'finance_alipay_name', 'finance_alipay_account', 'finance_weixin_account'];
        foreach ($attributes as $val) {
            $this->$val = ShopConfig::getConfig($shop->id, $val);
        }
    }

    /**
     * 保存店铺信息
     * @return boolean
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var Shop $shop */
        $shop = Shop::find()->where(['mid' => $this->mid])->one();
        $shop->name = $this->name;
        $shop->area = $this->area;
        $shop->save();
        foreach ($this->attributes as $key => $val) {
            if ($key != 'mid' && $key != 'name') {
                ShopConfig::setConfig($shop->id, $key, $val);
            }
        }
        return true;
    }
}
