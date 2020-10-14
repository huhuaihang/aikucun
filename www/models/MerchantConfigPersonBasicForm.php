<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 个人商户资质表单
 * Class MerchantConfigForm
 * @package app\models
 */
class MerchantConfigPersonBasicForm extends Model
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
     * @var string 身份证正面
     */
    public $id_card_front;
    /**
     * @var string 身份证反面
     */
    public $id_card_back;
    /**
     * @var string 联系人名字
     */
    public $contact_name;
    /**
     * @var string 联系人电话
     */
    public $mobile;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mid', 'contact_name', 'mobile', 'id_card_front', 'id_card_back', 'goods_sales_licenses'], 'required'],
            [['status', 'mobile', 'contact_name', 'inspection_reports', 'industry_files'], 'safe'],
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
            'mobile' => '联系电话',
            'contact_name' => '联系人',
            'id_card_front' => '身份证正面',
            'id_card_back' => '身份证反面',
        ];
    }

    /**
     * 加载表单默认值
     */
    public function loadDefault()
    {
        /** @var Merchant $merchant */
        $merchant = Merchant::findOne($this->mid);
        $attributes = ['id_card_front', 'id_card_back', 'inspection_reports', 'goods_sales_licenses', 'industry_files'];
        foreach ($attributes as $val) {
            $this->$val = MerchantConfig::getConfig($merchant->id, $val);
        }
        $this->mobile = $merchant->mobile;
        $this->contact_name = $merchant->contact_name;
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
            MerchantConfig::setConfig($merchant->id, 'id_card_front', $this->id_card_front);
            MerchantConfig::setConfig($merchant->id, 'id_card_back', $this->id_card_back);
            MerchantConfig::setConfig($merchant->id, 'goods_sales_licenses', !empty($this->goods_sales_licenses) ? $this->goods_sales_licenses : '');
            MerchantConfig::setConfig($merchant->id, 'inspection_reports', !empty($this->inspection_reports) ? $this->inspection_reports : '');
            MerchantConfig::setConfig($merchant->id, 'industry_files', !empty($this->industry_files) ? $this->industry_files :'');
            $merchant->status = Merchant::STATUS_WAIT_DATA2;
            $merchant->mobile = $this->mobile;
            $merchant->contact_name = $this->contact_name;
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
