<?php

use app\models\City;
use app\models\FinanceLog;
use app\models\GoodsCategory;
use app\models\KeyMap;
use app\models\MerchantConfig;
use app\models\ShopBrand;
use app\models\ShopConfig;
use app\models\ShopTheme;
use app\models\User;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $merchant \app\models\Merchant
 */

$this->title = '商户详情';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th colspan="2">基本信息</th>
    </tr>
    <tr>
        <th>编号</th>
        <td><?php echo $merchant->id;?></td>
    </tr>
    <tr>
        <th>类型</th>
        <td><?php echo KeyMap::getValue('merchant_type', $merchant->type);?></td>
    </tr>
    <tr>
        <th>代理商</th>
        <td><?php echo !empty($merchant->aid) ? Html::encode($merchant->agent->username) : '<i>没有代理商</i>';?></td>
    </tr>
    <tr>
        <th>登录邮箱账号</th>
        <td><?php echo Html::encode($merchant->username);?></td>
    </tr>
    <tr>
        <th>手机号</th>
        <td><?php echo $merchant->mobile;?></td>
    </tr>
    <tr>
        <th>联系人姓名</th>
        <td><?php echo Html::encode($merchant->contact_name);?></td>
    </tr>
    <tr>
        <th>头像</th>
        <td><?php if (!empty($merchant->avatar)) {
                echo Html::img(Yii::$app->params['upload_url'] . $merchant->avatar, ['style' => 'max-width:100px;']);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>是否为个人</th>
        <td><?php echo $merchant->is_person == 1 ? '是' : '否';?></td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('merchant_status', $merchant->status);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($merchant->create_time);?></td>
    </tr>
    <tr>
        <th>备注</th>
        <td><?php echo Html::encode($merchant->remark);?></td>
    </tr>
    <tr>
        <th colspan="2">申请信息</th>
    </tr>
    <tr>
        <th>身份证正面</th>
        <td><?php $id_card_front = MerchantConfig::getConfig($merchant->id, 'id_card_front');
            if (!empty($id_card_front)) {
                echo Html::img(Yii::$app->params['upload_url'] . $id_card_front, ['width' => 600]);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>身份证反面</th>
        <td><?php $id_card_back = MerchantConfig::getConfig($merchant->id, 'id_card_back');
            if (!empty($id_card_back)) {
                echo Html::img(Yii::$app->params['upload_url'] . $id_card_back, ['width' => 600]);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>申请人用户</th>
        <td><?php $require_uid = MerchantConfig::getConfig($merchant->id, 'register_from_uid');
            if (empty($require_uid)) {
                echo '<i>没有申请人</i>';
            } else {
                $require_user = User::findOne(MerchantConfig::getConfig($merchant->id, 'register_from_uid'));
                if (empty($require_user)) {
                    echo '<i>没有申请人</i>';
                } else {
                    echo Html::encode($require_user->nickname);
                }

            }?>
        </td>
    </tr>
    <tr>
        <th colspan="2">经营信息</th>
    </tr>
    <tr>
        <th>公司类型</th>
        <td><?php $company_type = MerchantConfig::getConfig($merchant->id, 'company_type');
            echo Html::encode($company_type);?>
        </td>
    </tr>
    <tr>
        <th>公司名称</th>
        <td><?php $company_name = MerchantConfig::getConfig($merchant->id, 'company_name');
            echo Html::encode($company_name);?>
        </td>
    </tr>
    <tr>
        <th>公司电话</th>
        <td><?php $company_tel = MerchantConfig::getConfig($merchant->id, 'company_tel');
            echo Html::encode($company_tel);?>
        </td>
    </tr>
    <tr>
        <th>公司地址</th>
        <td><?php $company_address = MerchantConfig::getConfig($merchant->id, 'company_address');
            echo Html::encode($company_address);?>
        </td>
    </tr>
    <tr>
        <th>营业执照</th>
        <td><?php $business_license = MerchantConfig::getConfig($merchant->id, 'business_license');
            if (!empty($business_license)) {
                echo Html::img(Yii::$app->params['upload_url'] . $business_license, ['width' => 600]);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>组织机构代码证</th>
        <td><?php $company_org_license = MerchantConfig::getConfig($merchant->id, 'company_org_license');
            if (!empty($company_org_license)) {
                echo Html::img(Yii::$app->params['upload_url'] . $company_org_license, ['width' => 600]);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>税务登记证</th>
        <td><?php $company_tax_license = MerchantConfig::getConfig($merchant->id, 'company_tax_license');
            if (!empty($company_tax_license)) {
                echo Html::img(Yii::$app->params['upload_url'] . $company_tax_license, ['width' => 600]);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th colspan="2">财务信息</th>
    </tr>
    <tr>
        <th>银行开户名</th>
        <td><?php $company_bank_account_name = MerchantConfig::getConfig($merchant->id, 'company_bank_account_name');
            echo Html::encode($company_bank_account_name);?>
        </td>
    </tr>
    <tr>
        <th>银行账户</th>
        <td><?php $company_bank_account_no = MerchantConfig::getConfig($merchant->id, 'company_bank_account_no');
            echo Html::encode($company_bank_account_no);?>
        </td>
    </tr>
    <tr>
        <th>开户支行</th>
        <td><?php $company_bank_name = MerchantConfig::getConfig($merchant->id, 'company_bank_name');
            echo Html::encode($company_bank_name);?>
        </td>
    </tr>
    <tr>
        <th>银行所在地</th>
        <td><?php $company_bank_address = MerchantConfig::getConfig($merchant->id, 'company_bank_address');?>
            <?php if (!empty($company_bank_address)) {
                $city = City::findByCode($company_bank_address);
                echo implode(' ', $city->address());
            } else {
                echo '<i>没有设置</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>开户许可证</th>
        <td><?php $company_bank_license = MerchantConfig::getConfig($merchant->id, 'company_bank_license');
            if (!empty($company_bank_license)) {
                echo Html::img(Yii::$app->params['upload_url'] . $company_bank_license, ['width' => 600]);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>纳税人识别号</th>
        <td><?php $company_tax_no = MerchantConfig::getConfig($merchant->id, 'company_tax_no');
            echo Html::encode($company_tax_no);?>
        </td>
    </tr>
    <tr>
        <th>一般纳税人证书</th>
        <td><?php $company_tax_general_license = MerchantConfig::getConfig($merchant->id, 'company_tax_general_license');
            if (!empty($company_tax_general_license)) {
                echo Html::img(Yii::$app->params['upload_url'] . $company_tax_general_license, ['width' => 600]);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th colspan="2">产品资质</th>
    </tr>
    <tr>
        <th>销售授权书/进货发票</th>
        <td><?php $goods_sales_licenses = MerchantConfig::getConfig($merchant->id, 'goods_sales_licenses');
            if (!empty($goods_sales_licenses)) {
                $goods_sales_licenses = json_decode($goods_sales_licenses, true);
                if (!empty($goods_sales_licenses) && is_array($goods_sales_licenses)) {
                    foreach ($goods_sales_licenses as $img) {
                        echo Html::img(Yii::$app->params['upload_url'] . $img, ['width' => 300]);
                    }
                }
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>质检报告</th>
        <td><?php $inspection_reports = MerchantConfig::getConfig($merchant->id, 'inspection_reports');
            if (!empty($inspection_reports)) {
                $inspection_reports = json_decode($inspection_reports, true);
                if (!empty($inspection_reports) && is_array($inspection_reports)) {
                    foreach ($inspection_reports as $img) {
                        echo Html::img(Yii::$app->params['upload_url'] . $img, ['width' => 300]);
                    }
                }
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>行业资质</th>
        <td><?php $industry_files = MerchantConfig::getConfig($merchant->id, 'industry_files');
            if (!empty($industry_files)) {
                $industry_files = json_decode($industry_files, true);
                if (!empty($industry_files) && is_array($industry_files)) {
                    foreach ($industry_files as $img) {
                        echo Html::img(Yii::$app->params['upload_url'] . $img, ['width' => 300]);
                    }
                }
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th colspan="2">品牌信息</th>
    </tr>
    <tr>
        <th>品牌名称</th>
        <td><?php
            /** @var ShopBrand $shop_brand */
            $shop_brand = ShopBrand::find()->where(['sid' => $merchant->shop->id])->one();
            echo empty($shop_brand) ?  '无品牌' : Html::encode($shop_brand->brand->name);?>
        </td>
    </tr>
    <tr>
        <th>品牌持有人</th>
        <td><?php
            echo empty($shop_brand) ?  '无品牌' :  Html::encode($shop_brand->brand->owner);?>
        </td>
    </tr>
    <tr>
        <th>品牌LOGO</th>
        <td><?php
            if (!empty($shop_brand->brand->logo)) {
                echo Html::img(Yii::$app->params['upload_url'] . $shop_brand->brand->logo, ['width' => 200]);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>TM / R</th>
        <td><?php echo empty($shop_brand) ?  '无品牌' :  Html::encode($shop_brand->brand->tm_r);?>
        </td>
    </tr>
    <tr>
        <th>品牌类型</th>
        <td><?php echo empty($shop_brand) ?  '无品牌' :  KeyMap::getValue('shop_brand_type',$shop_brand->type);?>
        </td>
    </tr>
    <tr>
        <th>Valid Time</th>
        <td><?php echo empty($shop_brand) ?  '无品牌' :  Html::encode($shop_brand->brand->valid_time);?>
        </td>
    </tr>
    <tr>
        <th>品牌资质</th>
        <td><?php
            if (!empty($shop_brand->file_list)) {
                $shop_brand->file_list = json_decode($shop_brand->file_list, true);
                foreach ($shop_brand->file_list as $img) {
                    echo Html::img(Yii::$app->params['upload_url'] . $img, ['width' => 200]);
                }
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th colspan="2">申请经营类目</th>
    </tr>
    <tr>
        <th>经营类目</th>
        <td>
            <?php
            $shop_config = ShopConfig::getConfig($merchant->shop->id, 'cid_list');
            $cid_list = json_decode($shop_config, true);
            if (!empty($shop_config) && !empty($cid_list)) {
                foreach (GoodsCategory::find()->where(['in', 'id' , $cid_list])->each() as $cate) {
                    if (empty($cate->pid)) {
                        echo '一级类目：' . $cate->name . '<br>';
                    }else{
                        echo '二级类目：' . $cate->name . ' ';
                    }
                }
            } else {
                echo '无';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th colspan="2">店铺信息</th>
    </tr>
    <tr>
        <th>编号</th>
        <td><?php echo $merchant->shop->id;?></td>
    </tr>
    <tr>
        <th>名称</th>
        <td><?php echo Html::encode($merchant->shop->name);?></td>
    </tr>
    <tr>
        <th>区域</th>
        <td><?php if (!empty($merchant->shop->name) && !empty($merchant->shop->area)) {
                $city = City::findByCode($merchant->shop->area);
                echo implode(' ', $city->address());
            } else {
                echo '<i>没有设置</i>';
            }?></td>
    </tr>
    <tr>
        <th>保证金</th>
        <td><?php if (!empty($merchant->shop->earnest_money_fid)) {
                $financeLog = FinanceLog::findOne($merchant->shop->earnest_money_fid);?>
                <table class="table">
                    <tr>
                        <th>编号</th>
                        <td><?php echo $financeLog->id;?></td>
                    </tr>
                    <tr>
                        <th>交易号</th>
                        <td><?php echo $financeLog->trade_no;?></td>
                    </tr>
                    <tr>
                        <th>类型</th>
                        <td><?php echo KeyMap::getValue('finance_log_type', $financeLog->type);?></td>
                    </tr>
                    <tr>
                        <th>金额</th>
                        <td><?php echo $financeLog->money;?></td>
                    </tr>
                    <tr>
                        <th>支付方式</th>
                        <td><?php echo KeyMap::getValue('finance_log_pay_method', $financeLog->pay_method);?></td>
                    </tr>
                    <tr>
                        <th>状态</th>
                        <td><?php echo KeyMap::getValue('finance_log_status', $financeLog->status);?></td>
                    </tr>
                    <tr>
                        <th>创建时间</th>
                        <td><?php echo Yii::$app->formatter->asDatetime($financeLog->create_time);?></td>
                    </tr>
                    <tr>
                        <th>更新时间</th>
                        <td><?php echo Yii::$app->formatter->asDatetime($financeLog->update_time);?></td>
                    </tr>
                    <tr>
                        <th>备注</th>
                        <td><?php echo Html::encode($financeLog->remark);?></td>
                    </tr>
                </table>
            <?php } else {
                echo '<i>没有记录</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>结算比率</th>
        <td><?php
            if (!empty(MerchantConfig::getConfig($merchant->id, 'merchant_charge_ratio'))) {
                echo MerchantConfig::getConfig($merchant->id, 'merchant_charge_ratio');
            } else {
                echo '<i>没有设置比率</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>主题</th>
        <td><?php if (!empty($merchant->shop->tid)) {
                $theme = ShopTheme::findOne($merchant->shop->tid);
                echo Html::encode($theme->name);
            } else {
                echo '<i>没有设置</i>';
            }?></td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('shop_status', $merchant->shop->status);?></td>
    </tr>
    <tr>
        <th>备注</th>
        <td><?php echo Html::encode($merchant->shop->remark);?></td>
    </tr>
</table>
