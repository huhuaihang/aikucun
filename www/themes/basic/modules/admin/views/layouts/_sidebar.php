<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $manager \yii\web\User
 */
$manager = Yii::$app->get('manager');
?>

<div id="sidebar" class="sidebar responsive sidebar-fixed sidebar-scroll">
    <!--
    <div class="sidebar-shortcuts" id="sidebar-shortcuts">
        <div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
            <?php echo Html::a('<i class="ace-icon fa fa-plus"></i>', ['/'], ['class' => 'btn btn-success']); ?>
            <?php echo Html::a('<i class="ace-icon fa fa-book"></i>', ['/'], ['class' => 'btn btn-info']); ?>
            <?php echo Html::a('<i class="ace-icon fa fa-users"></i>', ['/user/admin/index'], ['class' => 'btn btn-warning']); ?>
            <?php echo Html::a('<i class="ace-icon fa fa-signal"></i>', ['/'], ['class' => 'btn btn-danger']); ?>
        </div>
        <div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
            <?php echo Html::a('', ['/'], ['class' => 'btn btn-success']); ?>
            <?php echo Html::a('', ['/'], ['class' => 'btn btn-info']); ?>
            <?php echo Html::a('', ['/user/admin/index'], ['class' => 'btn btn-warning']); ?>
            <?php echo Html::a('', ['/'], ['class' => 'btn btn-danger']); ?>
        </div>
    </div>
    -->

    <?php
    /**
     * 检查菜单是否需要设置为当前状态
     * @param string $route
     * @return boolean
     */
    function checkMenuActive($route)
    {
        $controller = Yii::$app->controller;
        if (!$controller) {
            return false;
        }
        if ($controller->module->id != 'admin') {
            return false;
        }
        $route = preg_split('/\//', $route);
        if (count($route) == 1) {
            if ($controller->id == $route[0]) {
                return true;
            }
        } elseif (count($route) == 2) {
            if ($controller->id == $route[0] && $controller->action->id == $route[1]) {
                return true;
            }
        }
        return false;
    }
    if (YII_ENV === 'prod') {
        $items = Yii::$app->cache->get('manager_sidebar_items_' . $manager->id);
    } else {
        $items = null;
    }
    if (empty($items)) {
        $items = [
            ['label' => '权限管理', 'rbac' => 'rbac/menu', 'icon' => 'fa fa-lock', 'active' => 'rbac', 'items' => [
                ['label' => '管理员列表', 'rbac' => 'rbac/manager', 'url' => ['/admin/rbac/manager'], 'active' => 'rbac/manager'],
                ['label' => '管理日志', 'rbac' => 'rbac/log', 'url' => ['/admin/rbac/log'], 'active' => 'rbac/log'],
                ['label' => '角色列表', 'rbac' => 'rbac/role', 'url' => ['/admin/rbac/role'], 'active' => 'rbac/role'],
                ['label' => '权限列表', 'rbac' => 'rbac/item', 'url' => ['/admin/rbac/item'], 'active' => 'rbac/item'],
            ]],
//            ['label' => '广告管理', 'rbac' => 'ad/menu', 'icon' => 'fa fa-paper-plane', 'active' => 'ad', 'items' => [
//                ['label' => '广告列表', 'rbac' => 'ad/list', 'url' => ['/admin/ad/list'], 'active' => 'ad/list'],
//                ['label' => '广告位置', 'rbac' => 'ad/location', 'url' => ['/admin/ad/location'], 'active' => 'ad/location'],
//            ]],
//            ['label' => '商户管理', 'rbac' => 'merchant/menu', 'icon' => 'fa fa-briefcase', 'active' => 'merchant', 'items' => [
//                ['label' => '代理商列表', 'rbac' => 'merchant/agent', 'url' => ['/admin/merchant/agent'], 'active' => 'merchant/agent'],
//                ['label' => '商户列表', 'rbac' => 'merchant/list', 'url' => ['/admin/merchant/list'], 'active' => 'merchant/list'],
//                ['label' => '店铺列表', 'rbac' => 'merchant/shop-list', 'url' => ['/admin/merchant/shop-list'], 'active' => 'merchant/shop-list'],
//                ['label' => '代理设置', 'rbac' => 'merchant/agent-config', 'url' => ['/admin/merchant/agent-config'], 'active' => 'merchant/agent-config'],
//                ['label' => '商户设置', 'rbac' => 'merchant/config', 'url' => ['/admin/merchant/config'], 'active' => 'merchant/config'],
//                ['label' => '商户入驻申请', 'rbac' => 'merchant/join', 'url' => ['/admin/merchant/join'], 'active' => 'merchant/join'],
//                ['label' => '代理商入驻申请', 'rbac' => 'merchant/agent-join', 'url' => ['/admin/merchant/agent-join'], 'active' => 'merchant/agent-join'],
//                ['label' => '店铺品牌管理', 'rbac' => 'merchant/shop-brand', 'url' => ['/admin/merchant/shop-brand'], 'active' => 'merchant/shop-brand'],
//            ]],
//            ['label' => '商品管理', 'rbac' => 'goods/menu', 'icon' => 'fa fa-tags', 'active' => 'goods', 'items' => [
//                ['label' => '商品列表', 'rbac' => 'goods/list', 'url' => ['/admin/goods/list'], 'active' => 'goods/list'],
//                ['label' => '套餐卡管理', 'rbac' => 'goods/list', 'url' => ['/admin/goods/package'], 'active' => 'goods/package'],
//                ['label' => '服务管理', 'rbac' => 'goods/service', 'url' => ['/admin/goods/service'], 'active' => 'goods/service'],
//                ['label' => '类型管理', 'rbac' => 'goods/type', 'url' => ['/admin/goods/type'], 'active' => 'goods/type'],
//                ['label' => '分类管理', 'rbac' => 'goods/category', 'url' => ['/admin/goods/category'], 'active' => 'goods/category'],
//                ['label' => '商品评论', 'rbac' => 'goods/comment', 'url' => ['/admin/goods/comment'], 'active' => 'goods/comment'],
//                ['label' => '违规商品', 'rbac' => 'goods/violation', 'url' => ['/admin/goods/verify-violation'], 'active' => 'goods/verify-violation'],
//                ['label' => '商品弹幕规则', 'rbac' => 'goods/barrage-rules', 'url' => ['/admin/goods/barrage-rules'], 'active' => 'goods/barrage-rules'],
//            ]],
//            ['label' => '商学院管理', 'rbac' => 'hand/menu', 'icon' => 'fa fa-book', 'active' => 'hand', 'items' => [
//                ['label' => '新手入门列表', 'rbac' => 'hand/list', 'url' => ['/admin/hand/list'], 'active' => 'hand/list'],
//                ['label' => '视频素材列表', 'rbac' => 'hand/trace-video', 'url' => ['/admin/hand/trace-video'], 'active' => 'hand/trace-video'],
//                ['label' => '图片素材列表', 'rbac' => 'hand/source', 'url' => ['/admin/hand/source'], 'active' => 'hand/source'],
//            ]],
//            ['label' => '营销管理', 'rbac' => 'marketing/menu', 'icon' => 'fa fa-gift', 'active' => 'marketing', 'items' => [
//                ['label' => '限时折扣管理', 'rbac' => 'marketing/discount', 'url' => ['/admin/marketing/discount'], 'active' => 'marketing/discount'],
//                    ]
//            ],
//            ['label' => '订单管理', 'rbac' => 'order/menu', 'icon' => 'fa fa-bars', 'active' => 'order', 'items' => [
//                ['label' => '订单列表', 'rbac' => 'order/list', 'url' => ['/admin/order/list'], 'active' => 'order/list'],
//                ['label' => '订单取消审核', 'rbac' => 'order/verify-cancel', 'url' => ['/admin/order/verify-cancel'], 'active' => 'order/verify-cancel'],
//            ]],
//            ['label' => '财务管理', 'rbac' => 'finance/menu', 'icon' => 'fa fa-dollar', 'active' => 'finance', 'items' => [
//                ['label' => '财务列表', 'rbac' => 'finance/list', 'url' => ['/admin/finance/list'], 'active' => 'finance/list'],
//                ['label' => '平安银行对账', 'rbac' => 'finance/reconciliation', 'url' => ['/admin/finance/reconciliation-pingan'], 'active' => 'finance/reconciliation-pingan'],
//                ['label' => '支付宝对账', 'rbac' => 'finance/reconciliation', 'url' => ['/admin/finance/reconciliation-alipay'], 'active' => 'finance/reconciliation-alipay'],
//                ['label' => '微信对账', 'rbac' => 'finance/reconciliation', 'url' => ['/admin/finance/reconciliation-wechat'], 'active' => 'finance/reconciliation-wechat'],
//                ['label' => '商户结算单', 'rbac' => 'finance/merchant-financial-settlement', 'url' => ['/admin/finance/merchant-financial-settlement'], 'active' => 'finance/merchant-financial-settlement'],
//                ['label' => '商户结算', 'rbac' => 'finance/merchant-financial-settlement', 'url' => ['/admin/finance/merchant-financial-settlement-statistics'], 'active' => 'finance/merchant-financial-settlement-statistics'],
//                ['label' => '商户结算记录', 'rbac' => 'finance/merchant-financial-settlement', 'url' => ['/admin/finance/merchant-financial-settlement-log'], 'active' => 'finance/merchant-financial-settlement-log'],
//                ['label' => '供货商结算单', 'rbac' => 'finance/supplier-financial-settlement', 'url' => ['/admin/finance/supplier-financial-settlement'], 'active' => 'finance/supplier-financial-settlement'],
//                ['label' => '供货商结算记录', 'rbac' => 'finance/supplier-financial-settlement', 'url' => ['/admin/finance/supplier-financial-settlement-log'], 'active' => 'finance/supplier-financial-settlement-log'],
//            ]],
            ['label' => '店主管理', 'rbac' => 'user/menu', 'icon' => 'fa fa-users', 'active' => 'user', 'items' => [
                ['label' => '销售员列表', 'rbac' => 'user/list', 'url' => ['/admin/user/list'], 'active' => 'user/list'],
                ['label' => '销售员结算单列表', 'rbac' => 'user/list', 'url' => ['/admin/user/account-list'], 'active' => 'user/account-list'],
                ['label' => '店主列表', 'rbac' => 'user/list', 'url' => ['/admin/user/master-list'], 'active' => 'user/master-list'],
                ['label' => '店主结算单列表', 'rbac' => 'user/list', 'url' => ['/admin/user/master-account-list'], 'active' => 'user/master-account-list'],
//                ['label' => '用户前台激活列表', 'rbac' => 'user/list', 'url' => ['/admin/user/sale-list'], 'active' => 'user/sale-list'],
//                ['label' => '用户团队统计列表', 'rbac' => 'user/list', 'url' => ['/admin/user/server-list'], 'active' => 'user/server-list'],
//                ['label' => '预售数量排行列表', 'rbac' => 'user/list', 'url' => ['/admin/user/count-list'], 'active' => 'user/count-list'],
//                ['label' => '团队列表', 'rbac' => 'user/list', 'url' => ['/admin/user/month-sale-list'], 'active' => 'user/month-sale-list'],
//                ['label' => '佣金统计', 'rbac' => 'user/list', 'url' => ['/admin/user/commission-month'], 'active' => 'user/commission-month'],
                ['label' => '销售员等级列表', 'rbac' => 'user/list', 'url' => ['/admin/user/level-list'], 'active' => 'user/level-list'],
//                ['label' => '用户补贴提现列表', 'rbac' => 'user/list', 'url' => ['/admin/user/withdraw-list'], 'active' => 'user/withdraw-list'],
//                ['label' => '用户佣金提现列表', 'rbac' => 'user/list', 'url' => ['/admin/user/commission-withdraw-list'], 'active' => 'user/commission-withdraw-list'],
//                ['label' => '会员卡等级列表', 'rbac' => 'user/list', 'url' => ['/admin/user/card-level-list'], 'active' => 'user/card-level-list'],
//                ['label' => '用户反馈', 'rbac' => 'user/feedback', 'url' => ['/admin/user/feedback'], 'active' => 'user/feedback'],
//                ['label' => '用户礼包兑换券列表', 'rbac' => 'user/list', 'url' => ['/admin/user/pack-coupon-list'], 'active' => 'user/pack-coupon-list'],
            ]],
//            ['label' => '供货商管理', 'rbac' => 'supplier/menu', 'icon' => 'fa fa-building', 'active' => 'supplier', 'items' => [
//                ['label' => '供货商列表', 'rbac' => 'supplier/list', 'url' => ['/admin/supplier/list'], 'active' => 'supplier/list'],
//                ['label' => '商品列表', 'rbac' => 'supplier/goods-list', 'url' => ['/admin/supplier/goods-list'], 'active' => 'supplier/goods-list'],
//                ['label' => '供货商订单', 'rbac' => 'supplier/order-list', 'url' => ['/admin/supplier/order-list'], 'active' => 'supplier/order-list'],
//            ]],
//            ['label' => '客户留言', 'rbac' => 'message/menu', 'icon' => 'fa fa-commenting-o', 'active' => 'message', 'items' => [
//                ['label' => '所有记录', 'rbac' => 'message/list', 'url' => ['/admin/message/list'], 'active' => 'message/list'],
//            ]],
//            ['label' => '物流快递管理', 'rbac' => 'express/menu', 'icon' => 'fa fa-truck', 'active' => 'express', 'items' => [
//                ['label' => '快递公司列表', 'rbac' => 'express/list', 'url' => ['/admin/express/list'], 'active' => 'express/list'],
//            ]],
//            ['label' => '常见问题管理', 'rbac' => 'faq/menu', 'icon' => 'fa fa-question', 'active' => 'faq', 'items' => [
//                ['label' => '常见问题列表', 'rbac' => 'faq/list', 'url' => ['/admin/faq/list'], 'active' => 'faq/list'],
//                ['label' => '常见问题分类', 'rbac' => 'faq/category', 'url' => ['/admin/faq/faq-category'], 'active' => 'faq/faq-category'],
//            ]],
//            ['label' => '公告资讯管理', 'rbac' => 'notice/menu', 'icon' => 'fas fa-newspaper-o', 'active' => 'notice', 'items' => [
//                ['label' => '公告资讯列表', 'rbac' => 'notice/list', 'url' => ['/admin/notice/list'], 'active' => 'notice/list'],
//            ]],
//
//            ['label' => '统计分析', 'rbac' => 'statistics/menu', 'icon' => 'fa fa-line-chart', 'active' => 'statistics', 'items' => [
//                ['label' => '今年统计数据', 'rbac' => 'statistics/this-year', 'url' => ['/admin/statistics/this-year'], 'active' => 'statistics/this-year'],
//            ]],
//            ['label' => '数据管理', 'rbac' => 'db/menu', 'icon' => 'fa fa-database', 'active' => 'db', 'items' => [
//                ['label' => '执行SQL', 'rbac' => 'db/sql', 'url' => ['/admin/db/sql'], 'active' => 'db/sql'],
//                ['label' => '数据库备份', 'rbac' => 'db/backup', 'url' => ['/admin/db/backup'], 'active' => 'db/backup'],
//            ]],
//            ['label' => '系统管理', 'rbac' => 'system/menu', 'icon' => 'fa fa-cogs', 'active' => 'system', 'items' => [
//                ['label' => '系统设置', 'rbac' => 'system/config', 'url' => ['/admin/system/config'], 'active' => 'system/config'],
//                ['label' => '版本管理', 'rbac' => 'system/version', 'url' => ['/admin/system/version'], 'active' => 'system/version'],
//                ['label' => '接口客户端', 'rbac' => 'system/api-client', 'url' => ['/admin/system/api-client'], 'active' => 'system/api-client'],
//                ['label' => '定时任务', 'rbac' => 'system/task', 'url' => ['/admin/system/task'], 'active' => 'system/task'],
//                ['label' => '消息管理', 'rbac' => 'system/message', 'url' => ['/admin/system/message'], 'active' => 'system/message'],
//                ['label' => '违规类型管理', 'rbac' => 'system/violation', 'url' => ['/admin/system/violation'], 'active' => 'system/violation'],
//                ['label' => '错误日志', 'rbac' => 'system/error', 'url' => ['/admin/system/error'], 'active' => 'system/error'],
//            ]],
        ];
        // 检查权限
        array_walk($items, function (&$item) use ($manager) {
            /** @var $manager \yii\web\User */
            if (isset($item['rbac']) && !$manager->can($item['rbac'])) {
                $item = null;
                return;
            }
            if (isset($item['items']) && is_array($item['items'])) {
                array_walk($item['items'], function (&$item) use ($manager) {
                    /** @var $manager \yii\web\User */
                    if (isset($item['rbac']) && !$manager->can($item['rbac'])) {
                        $item = null;
                        return;
                    }
                });
            }
        });
        Yii::$app->cache->set('manager_sidebar_items_' . $manager->id, $items, 86400);
    }?>

    <ul class="nav nav-list">
        <li class="">
            <a href="<?php echo Url::to(['/admin']); ?>">
                <i class="menu-icon fa fa-tachometer"></i>
                <span class="menu-text"> 控制台</span>
            </a>

            <b class="arrow"></b>
        </li>
        <?php foreach ($items as $item) {
            if (empty($item)) {
                continue;
            } ?>
            <li class="<?php if (checkMenuActive($item['active'])) {
                echo 'open active';
            } ?>">
                <a href="#" class="dropdown-toggle">
                    <i class="menu-icon <?php echo $item['icon']; ?>"></i>
                    <span class="menu-text"><?php echo $item['label']; ?></span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <?php if (isset($item['items']) && is_array($item['items'])) {
                        foreach ($item['items'] as $subitem) {
                            if (empty($subitem)) {
                                continue;
                            } ?>
                            <li class="<?php if (checkMenuActive($subitem['active'])) {
                                echo 'active';
                            } ?>">
                                <a href="<?php echo Url::to($subitem['url']); ?>">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    <?php echo $subitem['label']; ?>
                                </a>
                                <b class="arrow"></b>
                            </li>
                        <?php }
                    } ?>
                </ul>
            </li>
        <?php } ?>

    </ul>
    <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
        <i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left"
           data-icon2="ace-icon fa fa-angle-double-right"></i>
    </div>
    <script>
        try {
            ace.settings.check('sidebar', 'collapsed');
        } catch (e) {
        }
    </script>
</div>

