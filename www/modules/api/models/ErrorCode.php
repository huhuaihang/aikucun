<?php

namespace app\modules\api\models;

use yii\base\Model;

/**
 * 错误码定义
 * Class ErrorCode
 * @package app\modules\api\models
 */
class ErrorCode extends Model
{
    const NULL = 0; // 没有错误，接口调用成功

    const PARAM       = 10000; // 缺少必要参数或参数类型错误，需要检查参数名称或类型
    const AUTH        = 10001; // 鉴权失败，需要检查加密过程
    const TIME        = 10002; // 时间戳超时，需要检查客户端时间
    const SIGN_REPEAT = 10003; // 签名已经使用过，请重新生成新的签名
    const VERSION     = 10004; // 版本太旧，需要更新客户端版本
    const NO_RESULT   = 10005; // 没有任何返回值
    const SERVER      = 10006; // 服务端错误
    const APP_ID      = 10007; // 接口没有授权给此客户端
    const REQUEST_TO_MANY = 10010; // 请求太频繁

    const USER_TOKEN        = 11001; // Token错误或失效，需要重新登录
    const USER_LOGIN        = 11002; // 登录失败，需要检查登录信息
    const USER_MOBILE_EXIST = 11003; // 手机号码已存在
    const USER_SEND_SMS     = 11004; // 短信验证码发送失败
    const USER_REGISTER     = 11005; // 注册失败
    const USER_SAVE         = 11008; // 保存用户信息失败
    const USER_UNION_ID_EXIST      = 11009; // 用户union_id已经存在

    const USER_ADDRESS_SAVE        = 11010; // 用户收货地址保存失败
    const USER_ADDRESS_NOT_FOUND   = 11011; // 用户收货地址不存在
    const USER_ADDRESS_DEL_DEFAULT = 11012; // 用户收货默认地址不能删除
    const USER_ADDRESS_DELETE      = 11013; // 用户收货地址删除失败

    const USER_FAV_SAVE = 11014; // 保存收藏失败
    const USER_FAV_EXIST = 11022; // 收藏已存在

    const USER_PASSWORD_EMPTY = 11015; // 密码没有设置
    const USER_PAYMENT_PASSWORD = 11016; // 支付密码错误

    const USER_COMMISSION_LESS = 11017; // 佣金不足
    const USER_ACCOUNT_SAVE_FAIL = 11018; // 保存账户信息失败
    const USER_WITHDRAW_SAVE_FAIL = 11019; // 提现记录保存失败
    const USER_BANK_SAVE_FAIL = 11020; // 提现账户保存失败
    const USER_ACCOUNT_LOG_SAVE_FAIL = 11021; // 账户明细保存失败

    const USER_SUBSIDY_LESS = 11022; // 佣金不足
    const USER_SUBSIDY_SAVE_FAIL = 11023; // 保存账户信息失败
    const USER_SUBSIDY_WITHDRAW_SAVE_FAIL = 11024; // 提现记录保存失败
    const USER_SUBSIDY_BANK_SAVE_FAIL = 11025; // 提现账户保存失败
    const USER_SUBSIDY_ACCOUNT_LOG_SAVE_FAIL = 11026; // 账户明细保存失败

    const GOODS_NOT_FOUND  = 12001; // 没有找到商品信息
    const GOODS_NOT_PUBLIC = 12002; // 商品没有发布

    const AD_LOC       = 13001; // 没有找到广告位
    const AD_NOT_FOUND = 13002; // 没有找到广告信息

    const ORDER_NOT_FOUND = 14001; // 没有找到订单信息
    const ORDER_DELETE_DENIED = 14002; // 订单不允许删除
    const ORDER_CANCEL_DENIED = 14003; // 订单不允许取消
    const ORDER_RECEIVED_DENIED = 14005; // 订单不允许确认收货
    const ORDER_HURRY_FAIL = 14006; // 催单失败
    const ORDER_SHOP_NOT_FOUND = 14007; // 订单关联店铺没找到
    const ORDER_GOODS_NOT_FOUND = 14008; // 订单关联商品没找到
    const ORDER_GOODS_SKU_NOT_FOUND = 14009; // 订单关联商品SKU没找到
    const ORDER_NO_GOODS = 14010; // 订单没有有效商品
    const ORDER_SAVE_FAIL = 14011; // 订单保存失败
    const ORDER_NO_ADDRESS = 14012; // 订单保存没有选择收货地址
    const ORDER_REMARK_DENIED = 14013; // 订单不允许再添加留言
    const ORDER_SAVE_ADDRESS_FAIL = 14014; // 订单保存地址失败
    const ORDER_STATUS_EXCEPTION = 14015; // 订单状态异常
    const ORDER_REFUND_MONEY_EXCEPTION = 14016; //您输入的退款金额超出最大退款金额
    const ORDER_REFUND_SAVE_FAIL = 14017; //无法保存申请退货信息
    const ORDER_STATUS = 14018; // 订单状态错误
    const ORDER_REFUND_NOT_FOUND = 14019; //售后申请信息找不到
    const ORDER_REFUND_DELETE_FAIL = 14021; //无法删除退款申请
    const ORDER_GOODS_ACCOUNT_SCORE = 14022; //积分兑换商品 积分不够

    const SHOP_NOT_FOUND = 14020; //店铺没找到

    const GOODS_EXPRESS_NOT_FOUND = 15001; // 此地区物流可能不能达到请联系客服
    const GOODS_EXPRESS_NO_AREA = 15002; // 请选择收货地址
    const GOODS_EXPRESS_NO_GOODS = 15003; // 请选择商品

    const CART_ADD_FAIL     = 16001; // 加入购物车失败
    const CART_NOT_FOUND    = 16002; // 没有找到购物车信息

    const FEEDBACK_ADD_FAIL    = 17001; // 意见反馈保存失败

    const MERCHANT_SAVE_FAIL    = 18001; // 申请商家入驻保存失败
    const MERCHANT_PERSON_SAVE_FAIL    = 18002; // 申请个人商家入驻保存失败

    const COMMENT_SAVE_FAIL    = 19001; // 商品评论保存失败
    const COMMENT_SHOP_SAVE_FAIL    = 19002; // 商品评论保存失败
    const COMMENT_SHOP_EMPTY_SCORE    = 19003; // 商品评论店铺评分不能为空
    const COMMENT_GOODS_EMPTY    = 19004; // 商品评论店铺订单商品编号不能为空

    const COUPON_ORDER_FAIL = 30001; // 优惠券活动订单限制错误

    // 20### 为聊天消息保留
}
