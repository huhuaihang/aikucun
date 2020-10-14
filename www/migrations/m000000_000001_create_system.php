<?php

use yii\db\Migration;

class m000000_000001_create_system extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%system}}', [
            'id' => $this->primaryKey(), // PK
            'category' => $this->string(32), // 类别
            'show_name' => $this->string(32), // 说明
            'name' => $this->string(128), // 名称
            'type' => $this->string(512), // 类型
            'value' => $this->text(), // 值
        ]);
        $this->batchInsert('{{%system}}', ['category', 'show_name', 'name', 'type', 'value'], [
            ['系统设置', '系统版本', 'system_version', json_encode(['type'=>'text', 'disabled'=>true]), '1.0.0'],
            ['网站设置', '商城名称', 'site_name', json_encode(['type' => 'text']), ''],
            ['网站设置', '商城LOGO', 'site_logo', json_encode(['type' => 'file']), ''],
            ['网站设置', '首页标题', 'site_index_title', json_encode(['type' => 'text']), ''],
            ['网站设置', '首页描述', 'site_index_desc', json_encode(['type' => 'text']), ''],
            ['网站设置', '首页关键字', 'site_index_keywords', json_encode(['type' => 'text']), ''],
            ['网站设置', '许可证', 'site_license', json_encode(['type' => 'text']), ''],
            ['网站设置', '联系客服页面', 'site_contact', json_encode(['type' => 'richtext']), ''],
            ['网站设置', '流量统计代码', 'site_statistics', json_encode(['type' => 'plaintext']), ''],
            ['支付设置', '平安银行支付', 'pingan_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['支付设置', '平安银行商户号码', 'pingan_master_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '平安银行私钥文件', 'pingan_merchant_cert_file', json_encode(['type' => 'file']), ''],
            ['支付设置', '平安银行公钥文件', 'pingan_trust_pay_cert_file', json_encode(['type' => 'file']), ''],
            ['支付设置', '平安银行SSL密码', 'pingan_ssl_password', json_encode(['type' => 'text']), ''],
            ['支付设置', '平安银行回调地址', 'pingan_notify_url', json_encode(['type' => 'text']), ''],
            ['支付设置', '平安银行返回地址', 'pingan_return_url', json_encode(['type' => 'text']), ''],
            ['支付设置', '支付宝支付', 'alipay_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['支付设置', '支付宝AppId', 'alipay_app_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '支付宝私钥', 'alipay_private_key', json_encode(['type' => 'text']), ''],
            ['支付设置', '支付宝公钥', 'alipay_public_key', json_encode(['type' => 'text']), ''],
            ['支付设置', '支付宝回调地址', 'alipay_notify_url', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信扫码支付', 'weixin_scan_pay_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['支付设置', '微信APP支付', 'weixin_app_pay_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['支付设置', '微信App支付AppId', 'weixin_app_app_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信App支付MchId', 'weixin_app_mch_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信App支付ApiKey', 'weixin_app_api_key', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信App支付公钥证书', 'weixin_app_cert_file', json_encode(['type' => 'file']), ''],
            ['支付设置', '微信App支付私钥证书', 'weixin_app_key_file', json_encode(['type' => 'file']), ''],
            ['支付设置', '微信App支付回调地址', 'weixin_app_notify_url', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信公众号支付', 'weixin_mp_pay_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['支付设置', '微信公众号支付AppId', 'weixin_mp_app_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信公众号支付AppSecret', 'weixin_mp_app_secret', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信公众号支付MchId', 'weixin_mp_mch_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信公众号支付ApiKey', 'weixin_mp_api_key', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信公众号支付公钥证书', 'weixin_mp_cert_file', json_encode(['type' => 'file']), ''],
            ['支付设置', '微信公众号支付私钥证书', 'weixin_mp_key_file', json_encode(['type' => 'file']), ''],
            ['支付设置', '微信公众号支付回调地址', 'weixin_mp_notify_url', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信H5支付', 'weixin_h5_pay_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['支付设置', '微信H5支付AppId', 'weixin_h5_app_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信H5支付MchId', 'weixin_h5_mch_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信H5支付ApiKey', 'weixin_h5_api_key', json_encode(['type' => 'text']), ''],
            ['支付设置', '微信H5支付公钥证书', 'weixin_h5_cert_file', json_encode(['type' => 'file']), ''],
            ['支付设置', '微信H5支付私钥证书', 'weixin_h5_key_file', json_encode(['type' => 'file']), ''],
            ['支付设置', '微信H5支付回调地址', 'weixin_h5_notify_url', json_encode(['type' => 'text']), ''],
            ['支付设置', '通联支付', 'allinpay_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['支付设置', '通联支付商户号', 'allinpay_merchant_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '通联支付Key', 'allinpay_key', json_encode(['type' => 'text']), ''],
            ['支付设置', '通联H5支付', 'allinpay_h5_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['支付设置', '通联H5支付商户号', 'allinpay_h5_merchant_id', json_encode(['type' => 'text']), ''],
            ['支付设置', '通联H5支付签名秘钥', 'allinpay_h5_md5_key', json_encode(['type' => 'text']), ''],
            ['支付设置', '通联支付支付宝', 'allinpay_ali_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['支付设置', '通联支付支付宝商户号', 'allinpay_ali_cusid', json_encode(['type' => 'text']), ''],
            ['支付设置', '通联支付支付宝APPID', 'allinpay_ali_appid', json_encode(['type' => 'text']), ''],
            ['支付设置', '通联支付支付宝KEY', 'allinpay_ali_key', json_encode(['type' => 'text']), ''],
            ['提现设置', '开启提现', 'withdraw_open', json_encode(['type' => 'radio', 'options' => ['关闭', '开通']]), 0],
            ['提现设置', '最低提现金额', 'withdraw_min', json_encode(['type' => 'text']), 100],
            ['短信接口', '短信接口地址', 'sms_gateway_url', json_encode(['type' => 'text']), ''],
            ['短信接口', '短信AppId', 'sms_appid', json_encode(['type' => 'text']), ''],
            ['短信接口', '短信ApiKey', 'sms_apikey', json_encode(['type' => 'text']), ''],
            ['快递接口', '快递100Key', 'kuaidi100_key', json_encode(['type' => 'text']), ''],
            ['快递接口', '快递100回调地址', 'kuaidi100_notify_url', json_encode(['type' => 'text']), ''],
            ['百度地图接口', '百度地图AK', 'baidu_map_ak', json_encode(['type' => 'text']), ''],
            ['百度地图接口', '百度地图SK', 'baidu_map_sk', json_encode(['type' => 'text']), ''],
            ['购物流程设置', '用户收货地址数量', 'user_address_limit', json_encode(['type' => 'text']), '0'],
            ['购物流程设置', '下单后几分钟自动取消', 'order_force_cancel_minute', json_encode(['type' => 'text']), '30'],
            ['购物流程设置', '发货后几天自动收货', 'deliver_force_receive_day', json_encode(['type' => 'text']), ''],
            ['购物流程设置', '收货后几天自动评价', 'receive_force_comment_day', json_encode(['type' => 'text']), ''],
            ['购物流程设置', '自动评价店铺评分', 'force_comment_shop_score', json_encode(['type' => 'text']), ''],
            ['购物流程设置', '自动评价商品评分', 'force_comment_goods_score', json_encode(['type' => 'text']), ''],
            ['购物流程设置', '评论需要审核', 'comment_need_verify', json_encode(['type' => 'radio', 'options' => ['不需要', '需要']]), '1'],
            ['结算设置', '订单完成后几天生成结算单', 'order_complete_financial_settlement_day', json_encode(['type' => 'text']), ''],
            ['结算设置', '平台服务费比例', 'merchant_charge_ratio', json_encode(['type' => 'text']), ''],
            ['协议设置', '商家入驻合作协议', 'merchant_join_agreement', json_encode(['type' => 'richtext']), ''],
            ['协议设置', '代理商入驻协议', 'agent_join_agreement', json_encode(['type' => 'richtext']), ''],
            ['协议设置', '店铺入驻资质要求', 'merchant_join_aptitude', json_encode(['type' => 'richtext']), ''],
            ['协议设置', '保证金管理规范', 'merchant_earnest_standard', json_encode(['type' => 'richtext']), ''],
            ['协议设置', '类目资费一览表', 'goods_category_money_table', json_encode(['type' => 'richtext']), ''],
            ['协议设置', '店铺运营规则规范', 'merchant_operate_rules_standard', json_encode(['type' => 'richtext']), ''],
            ['协议设置', '招商标准', 'canvass_business_orders_standard', json_encode(['type' => 'richtext']), ''],
            ['订单售后设置', '申请售后卖家几天自动同意', 'order_refund_force_accept_day', json_encode(['type' => 'text']), ''],
            ['订单售后设置', '申请售后卖家自动同意用户消息', 'order_refund_force_accept_user_message', json_encode(['type' => 'text']), ''],
            ['订单售后设置', '申请售后卖家自动同意商户消息', 'order_refund_force_accept_merchant_message', json_encode(['type' => 'text']), ''],
            ['订单售后设置', '同意售后几天没有发货自动取消', 'order_refund_force_delete_day', json_encode(['type' => 'text']), ''],
            ['订单售后设置', '同意售后没有发货自动取消用户消息', 'order_refund_force_delete_user_message', json_encode(['type' => 'text']), ''],
            ['订单售后设置', '发货后几天商户自动完成', 'order_refund_force_receive_day', json_encode(['type' => 'text']), ''],
            ['订单售后设置', '发货后商户自动完成用户消息', 'order_refund_force_receive_user_message', json_encode(['type' => 'text']), ''],
            ['订单售后设置', '发货后商户自动完成商户消息', 'order_refund_force_receive_merchant_message', json_encode(['type' => 'text']), ''],
            ['推荐设置', '推荐关系保持天数', 'recommend_keep_day', json_encode(['type' => 'text']), '30'],
            ['推荐设置', '推荐覆盖方式', 'recommend_replace_method', json_encode(['type' => 'radio', 'options' => ['后者覆盖前者', '前者覆盖后者']]), '0'],
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%system}}');
    }
}
