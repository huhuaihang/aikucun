<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 企业商户资质表单
 * Class MerchantConfigForm
 * @package app\models
 */
class MerchantConfigForm extends Model
{
    /**
     * @var int mid
     */
    public $mid;
    /**
     * @var int sid
     */
    public $sid;
    /**
     * @var string 公司类型
     */
    public $company_type;
    /**
     * @var string 公司名称
     */
    public $company_name;
    /**
     * @var string 公司地址
     */
    public $company_address;
    /**
     * @var string 公司电话
     */
    public $company_tel;
    /**
     * @var string 营业执照
     */
    public $business_license;
    /**
     * @var string 组织机构代码证
     */
    public $company_org_license;
    /**
     * @var string 税务登记证
     */
    public $company_tax_license;
    /**
     * @var string 银行开户名
     */
    public $company_bank_account_name;
    /**
     * @var string 银行账户
     */
    public $company_bank_account_no;
    /**
     * @var string 开户支行
     */
    public $company_bank_name;
    /**
     * @var string 银行所在地
     */
    public $company_bank_address;
    /**
     * @var string 开户许可
     */
    public $company_bank_license;
    /**
     * @var string 纳税人识别号
     */
    public $company_tax_no;
    /**
     * @var string 一般纳税人证书
     */
    public $company_tax_general_license;
    /**
     * @var array 销售授权书
     */
    public $goods_sales_licenses;
    /**
     * @var array  质检报告
     */
    public $inspection_reports;
    /**
     * @var array 特殊资质 行业资质
     */
    public $industry_files;
    /**
     * @var int 入驻资质提交状态
     */
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mid', 'company_type', 'company_name', 'company_address', 'company_tel', 'goods_sales_licenses',
                'inspection_reports', 'industry_files', 'company_tax_no', 'company_bank_license',
                'business_license', 'company_bank_account_name', 'company_bank_account_no', 'company_bank_name',
                'company_bank_address', 'company_tax_general_license'], 'required'],
            [['company_org_license', 'company_tax_license', 'status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_type' => '公司类型',
            'company_name' => '公司名称',
            'business_license' => '营业执照',
            'company_address' => '公司地址',
            'company_tel' => '公司电话',
            'company_org_license' => '组织机构代码证',
            'company_tax_license' => '税务登记证',
            'company_bank_account_name' => '银行开户名',
            'company_bank_account_no' => '银行账户',
            'company_bank_name' => '开户支行',
            'company_bank_address' => '银行所在地',
            'company_bank_license' => '开户许可',
            'company_tax_no' => '纳税人识别号',
            'company_tax_general_license' => '一般纳税人证书',
            'inspection_reports' => '质检报告',
            'goods_sales_licenses' => '销售授权书/进货发票',
            'industry_files' => '行业资质',
        ];
    }

    /**
     * 加载表单默认值
     */
    public function loadDefault()
    {
        /** @var Merchant $merchant */
        $merchant = Merchant::findOne($this->mid);
        $attributes = ['company_type', 'company_name', 'company_address', 'company_tel', 'company_org_license',
            'company_tax_license', 'business_license', 'company_bank_account_name', 'company_bank_account_no', 'company_bank_name',
            'company_bank_address', 'company_tax_general_license', 'inspection_reports', 'goods_sales_licenses', 'industry_files',
            'company_tax_no', 'company_bank_license'];
        foreach ($attributes as $val) {
            $this->$val = MerchantConfig::getConfig($merchant->id, $val);
        }
    }

    /**
     * 保存入驻信息
     * @return boolean
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $merchant = Merchant::findOne($this->mid);
            MerchantConfig::setConfig($merchant->id, 'company_type', $this->company_type);
            MerchantConfig::setConfig($merchant->id, 'company_name', $this->company_name);
            MerchantConfig::setConfig($merchant->id, 'business_license', $this->business_license);
            MerchantConfig::setConfig($merchant->id, 'company_address', $this->company_address);
            MerchantConfig::setConfig($merchant->id, 'company_tel', $this->company_tel);
            MerchantConfig::setConfig($merchant->id, 'company_org_license', $this->company_org_license);
            MerchantConfig::setConfig($merchant->id, 'company_tax_license', $this->company_tax_license);
            MerchantConfig::setConfig($merchant->id, 'company_bank_account_name', $this->company_bank_account_name);
            MerchantConfig::setConfig($merchant->id, 'company_bank_account_no', $this->company_bank_account_no);
            MerchantConfig::setConfig($merchant->id, 'company_bank_name', $this->company_bank_name);
            MerchantConfig::setConfig($merchant->id, 'company_bank_address', $this->company_bank_address);
            MerchantConfig::setConfig($merchant->id, 'company_bank_license', $this->company_bank_license);
            MerchantConfig::setConfig($merchant->id, 'company_tax_no', $this->company_tax_no);
            MerchantConfig::setConfig($merchant->id, 'company_tax_general_license', $this->company_tax_general_license);
            MerchantConfig::setConfig($merchant->id, 'goods_sales_licenses', !empty($this->goods_sales_licenses) ? $this->goods_sales_licenses : '');
            MerchantConfig::setConfig($merchant->id, 'inspection_reports', !empty($this->inspection_reports) ? $this->inspection_reports : '');
            MerchantConfig::setConfig($merchant->id, 'industry_files', !empty($this->industry_files) ? $this->industry_files : '');
            $merchant->status = Merchant::STATUS_WAIT_DATA2;
            $merchant->save();
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
