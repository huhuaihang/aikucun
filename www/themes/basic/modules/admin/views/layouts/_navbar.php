<?php

use app\models\Agent;
use app\models\Feedback;
use app\models\GoodsViolation;
use app\models\Merchant;
use app\models\ShopBrand;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $manager \app\models\Manager
 */

$manager = Yii::$app->get('manager')->identity;
?>

<div id="navbar" class="navbar navbar-default navbar-fixed-top">

    <div class="navbar-container" id="navbar-container">
        <button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
            <span class="sr-only"><?= Yii::t('app', 'Toggle sidebar') ?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <div class="navbar-header pull-left">
            <a href="<?php echo Url::to(['/admin']); ?>" class="navbar-brand">
                <small>
                    <i class="fa fa-leaf"></i>
                    <?php echo Yii::$app->params['appName'], '管理后台'; ?>
                </small>
            </a>
        </div>

        <?php $task_count = 0; // 待处理任务总数
        // 商户入驻申请待处理数量
        $merchant_join_count = intval(Merchant::find()->andWhere(['status' => [Merchant::STATUS_WAIT_DATA1, Merchant::STATUS_WAIT_DATA2]])->count());
        if ($merchant_join_count > 0) {
            $task_count ++;
        }
        // 代理商入驻申请待处理数量
        $agent_join_count = intval(Agent::find()->andWhere(['status' => [Agent::STATUS_WAIT_CONTACT, Agent::STATUS_WAIT_INITIAL_FEE, Agent::STATUS_WAIT_FINANCE]])->count());
        if ($agent_join_count > 0) {
            $task_count ++;
        }
        // 店铺品牌申请待处理数量
        $shop_brand_verify_count = intval(ShopBrand::find()->andWhere(['status' => ShopBrand::STATUS_WAIT])->count());
        if ($shop_brand_verify_count > 0) {
            $task_count ++;
        }
        // 违规商品审核待处理数量
        $goods_violation_verify_count = intval(GoodsViolation::find()->andWhere(['status' => GoodsViolation::STATUS_WAIT_MANAGER])->count());
        if ($goods_violation_verify_count > 0) {
            $task_count ++;
        }
        // 用户返回待处理数量
        $feedback_wait_count = intval(Feedback::find()->andWhere(['status' => Feedback::STATUS_WAIT])->count());
        if ($feedback_wait_count > 0) {
            $task_count ++;
        }?>
        <div class="navbar-buttons navbar-header pull-right" role="navigation">
            <ul class="nav ace-nav">
                <!-- 任务列表 开始 -->
                <li class="grey">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="ace-icon fa fa-tasks"></i>
                        <span class="badge badge-grey"><?php echo $task_count;?></span>
                    </a>

                    <ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
                        <li class="dropdown-header">
                            <i class="ace-icon fa fa-check"></i>
                            <?php echo $task_count;?>个任务待处理
                        </li>

                        <li class="dropdown-content">
                            <ul class="dropdown-menu dropdown-navbar">
                                <?php if ($merchant_join_count > 0) {?>
                                    <li>
                                        <a href="<?php echo Url::to(['/admin/merchant/join']);?>">
                                            <div class="clearfix">
                                                <span class="pull-left">商户入驻申请审核</span>
                                                <span class="pull-right"><?php echo $merchant_join_count;?></span>
                                            </div>
                                        </a>
                                    </li>
                                <?php }?>
                                <?php if ($agent_join_count > 0) {?>
                                    <li>
                                        <a href="<?php echo Url::to(['/admin/merchant/agent-join']);?>">
                                            <div class="clearfix">
                                                <span class="pull-left">代理商入驻申请审核</span>
                                                <span class="pull-right"><?php echo $agent_join_count;?></span>
                                            </div>
                                        </a>
                                    </li>
                                <?php }?>
                                <?php if ($shop_brand_verify_count > 0) {?>
                                    <li>
                                        <a href="<?php echo Url::to(['/admin/merchant/shop-brand']);?>">
                                            <div class="clearfix">
                                                <span class="pull-left">店铺品牌申请审核</span>
                                                <span class="pull-right"><?php echo $shop_brand_verify_count;?></span>
                                            </div>
                                        </a>
                                    </li>
                                <?php }?>
                                <?php if ($goods_violation_verify_count > 0) {?>
                                    <li>
                                        <a href="<?php echo Url::to(['/admin/goods/verify-violation']);?>">
                                            <div class="clearfix">
                                                <span class="pull-left">违规商品审核</span>
                                                <span class="pull-right"><?php echo $goods_violation_verify_count;?></span>
                                            </div>
                                        </a>
                                    </li>
                                <?php }?>
                                <?php if ($feedback_wait_count > 0) {?>
                                    <li>
                                        <a href="<?php echo Url::to(['/admin/user/feedback']);?>">
                                            <div class="clearfix">
                                                <span class="pull-left">用户反馈处理</span>
                                                <span class="pull-right"><?php echo $feedback_wait_count;?></span>
                                            </div>
                                        </a>
                                    </li>
                                <?php }?>
                            </ul>
                        </li>
                    </ul>
                </li>
                <!-- 任务列表 结束 -->

                <li class="light-blue">
                    <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                        <span class="user-info">
                            <small>当前登录</small> <?= $manager->username ?>
                        </span>
                        <i class="ace-icon fa fa-caret-down"></i>

                    </a>
                    <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                        <li>
                            <a href="<?= \yii\helpers\Url::to(['/admin/identity/profile']) ?>"><i
                                        class="ace-icon fa fa-user"></i> 用户设置</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="<?= \yii\helpers\Url::to(['/admin/identity/logout']) ?>" data-method="post"><i
                                        class="ace-icon fa fa-power-off"></i> 退出</a>
                        </li>
                    </ul>

                </li>
            </ul>
        </div>
    </div>
</div>