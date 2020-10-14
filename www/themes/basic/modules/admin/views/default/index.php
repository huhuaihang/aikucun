<?php

use app\assets\EchartsAsset;
use app\models\Goods;
use app\models\GoodsType;
use app\models\Order;
use app\models\User;
use app\models\UserSubsidy;
use app\models\UserWithdraw;
use app\models\Util;

/**
 * @var $this \yii\web\View
 */

EchartsAsset::register($this);

$this->title = '控制台';
$controller = Yii::$app->controller;
if (!$controller) {
    return false;
}
$manager = Yii::$app->get('manager');
if ($manager->id != '1') {
    return false;
}
?>
<style>
    .box {

        width: 120px;
        height: 80px;
        margin: 5px;
        padding: 5px;
        box-shadow: 0 0 8px #b25151;
        border-radius: 10px;
        padding-top: 6px;
        display:inline-block;
        vertical-align:top;
    }
    .background {
        color: #ffffff;
        background-image: url('/images/data_background.jpg');
    }
</style>
<div class="background" style="display:none">
    <div class="box">
        昨日注册用户总数量：
        <?php
        if (Yii::$app->cache->exists('register_count')) {
            $count = Yii::$app->cache->get('register_count');
        } else {
            $count = User::find()->andWhere(['<>', 'status', User::STATUS_DELETE])
                ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
                ->count();
            Yii::$app->cache->set('register_count', $count, 7200);
        }
        echo $count;
        ?>
    </div>
    <div class="box">
        昨日激活用户总数量：
        <?php
        if (Yii::$app->cache->exists('active_count')) {
            $count = Yii::$app->cache->get('active_count');

        } else {
            $count = User::find()->andWhere(['=', 'status', User::STATUS_OK])
                ->andWhere(['BETWEEN', 'handle_time', strtotime(date('Y-m-d', time())) - 86400, strtotime(date('Y-m-d', time()))])
                ->count();
            Yii::$app->cache->set('active_count', $count, 7200);
        }
        echo $count;
        ?>
    </div>
    <div class="box">
        昨日前台购买激活用户总数量：
        <?php
        if (Yii::$app->cache->exists('active_self_buy_count')) {
            $count = Yii::$app->cache->get('active_self_buy_count');
        } else {
            $count = User::find()->andWhere(['=', 'status', User::STATUS_OK])
                ->andWhere(['is_self_active' => 1])
                ->andWhere(['BETWEEN', 'handle_time', strtotime(date('Y-m-d', time())) - 86400, strtotime(date('Y-m-d', time()))])
                ->count();
            Yii::$app->cache->set('active_self_buy_count', $count, 7200);
        }
        echo $count;
        ?>
    </div>
    <div class="box">
        昨日激活用户总金额：￥
        <?php
        if (Yii::$app->cache->exists('active_money')) {
            $count = Yii::$app->cache->get('active_money');
        } else {
            $count = User::find()->andWhere(['=', 'status', User::STATUS_OK])
                    ->andWhere(['BETWEEN', 'handle_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
                    ->count() * 399;
            Yii::$app->cache->set('active_money', $count, 7200);
        }
        echo $count;
        ?>
    </div>
    <div class="box">
        昨日购买激活用户总金额：￥
        <?php
        if (Yii::$app->cache->exists('active_self_buy_money')) {
            $count = Yii::$app->cache->get('active_self_buy_money');
        } else {
            $count = User::find()->andWhere(['=', 'status', User::STATUS_OK])
                    ->andWhere(['is_self_active' => 1])
                    ->andWhere(['BETWEEN', 'handle_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
                    ->count() * 399;
            Yii::$app->cache->set('active_self_buy_money', $count, 7200);
        }
        echo $count;
        ?>
    </div>
    <div class="box">
        昨日补贴总金额：￥
        <?php
        if (Yii::$app->cache->exists('subsidy_money')) {
            $count = Yii::$app->cache->get('subsidy_money');
        } else {
            $count = UserSubsidy::find()
                //->andWhere(['>', 'create_time', strtotime(date('Y-m-d',time()))])
                ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
                ->sum('money');
            Yii::$app->cache->set('subsidy_money', $count, 7200);
        }
        echo $count;
        ?>
    </div>
    <div class="box">
        昨日补贴提现申请总金额：￥
        <?php
        if (Yii::$app->cache->exists('subsidy_apply_money')) {
            $count = Yii::$app->cache->get('subsidy_apply_money');
        } else {
            $count = UserWithdraw::find()->andWhere(['=', 'status', 1])
                ->andWhere(['=', 'type', 1])
                ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
                ->sum('money');
            Yii::$app->cache->set('subsidy_apply_money', $count, 7200);
        }
        echo $count;
        ?>
    </div>
    <div class="box">
        昨日审核通过补贴提现申请总金额：￥
        <?php
        if (Yii::$app->cache->exists('subsidy_accept_apply_money')) {
            $count = Yii::$app->cache->get('subsidy_accept_apply_money');
        } else {
            $count = UserWithdraw::find()->andWhere(['=', 'status', 2])
                ->andWhere(['=', 'type', 1])
                ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
                ->sum('money');
            Yii::$app->cache->set('subsidy_accept_apply_money', $count, 7200);
        }
        echo $count;
        ?>
    </div>
    <div class="box">
        昨日补贴提现总金额：￥
        <?php
        if (Yii::$app->cache->exists('subsidy_apply_go_money')) {
            $count = Yii::$app->cache->get('subsidy_apply_go_money');
        } else {
            $count = UserWithdraw::find()->andWhere(['=', 'status', 3])
                ->andWhere(['=', 'type', 1])
                ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
                ->sum('money');
            Yii::$app->cache->set('subsidy_apply_go_money', $count, 7200);
        }
        echo $count;
        ?>
    </div>

<!--<div class="row">-->
<!--    <div class="col-md-12"><h3>昨日注册用户总数量：--><?php //echo User::find()->andWhere(['<>', 'status', User::STATUS_DELETE])
//                ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])->count();?><!--</h3></div>-->
<!--</div>-->
<!--<div class="row">-->
<!--    <div class="col-md-12">-->
<!--        <h3>昨日激活用户总数量：--><?php //echo User::find()->andWhere(['=', 'status', User::STATUS_OK])
//                ->andWhere(['BETWEEN', 'handle_time', strtotime(date('Y-m-d', time())) - 86400, strtotime(date('Y-m-d', time()))])
//                ->count(); ?><!--</h3>-->
<!--        <h3>昨日前台购买激活用户总数量：--><?php //echo User::find()->andWhere(['=', 'status', User::STATUS_OK])
//                ->andWhere(['is_self_active' => 1])
//                ->andWhere(['BETWEEN', 'handle_time', strtotime(date('Y-m-d', time())) - 86400, strtotime(date('Y-m-d', time()))])
//                ->count(); ?><!--</h3>-->
<!--    </div>-->
<!--</div>-->
<!--<div class="row">-->
<!--    <div class="col-md-12">-->
<!--        <h3>昨日激活用户总金额：￥--><?php //echo User::find()->andWhere(['=', 'status', User::STATUS_OK])
//                ->andWhere(['BETWEEN', 'handle_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
//                    ->count() * 399;?><!--</h3>-->
<!--        <h3>昨日购买激活用户总金额：￥--><?php //echo User::find()->andWhere(['=', 'status', User::STATUS_OK])
//                    ->andWhere(['is_self_active' => 1])
//                    ->andWhere(['BETWEEN', 'handle_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
//                    ->count() * 399;?><!--</h3>-->
<!--    </div>-->
<!--</div>-->
<!--<div class="row">-->
<!--    <div class="col-md-12"><h3>昨日补贴总金额：￥--><?php //echo UserSubsidy::find()
//                    //->andWhere(['>', 'create_time', strtotime(date('Y-m-d',time()))])
//                    ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
//                    ->sum('money');?><!--</h3></div>-->
<!--</div>-->
<!--<div class="row">-->
<!--    <div class="col-md-12"><h3>昨日补贴提现申请总金额：￥--><?php //echo UserWithdraw::find()->andWhere(['=', 'status', 1])
//                    ->andWhere(['=', 'type', 1])
//                    ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])->sum('money');?><!--</h3></div>-->
<!--</div>-->
<!--<div class="row">-->
<!--    <div class="col-md-12"><h3>昨日审核通过补贴提现申请总金额：￥--><?php
//            echo UserWithdraw::find()->andWhere(['=', 'status', 2])
//                ->andWhere(['=', 'type', 1])
//                ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])
//                ->sum('money');
//    ?><!--</h3></div>-->
<!--</div>-->
<!--<div class="row">-->
<!--    <div class="col-md-12"><h3>昨日补贴提现总金额：￥--><?php //echo UserWithdraw::find()->andWhere(['=', 'status', 3])
//                ->andWhere(['=', 'type', 1])
//                ->andWhere(['BETWEEN', 'create_time', strtotime(date('Y-m-d',time()))- 86400, strtotime(date('Y-m-d',time()))])->sum('money');?><!--</h3></div>-->
<!--</div>-->
<div class="row">
    <div class="col-md-12"><h3>注册用户总数量：<?php echo User::find()->andWhere(['<>', 'status', User::STATUS_DELETE])->count();?></h3></div>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="chart_user_reg" style="width: 100%;height:200px;"></div>
    </div>
</div>
<div class="row">
    <div class="col-md-12"><h3>上架商品总数量：<?php echo Goods::find()->Where(['status' => Goods::STATUS_ON])->count();?></h3></div>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="chart_goods_type" style="width: 100%;height:200px;"></div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <h3>最近一个月订单总数量：
            <?php echo Order::find()
                ->Where(['>=', 'create_time', time() - 30 * 86400])
                ->count();
            ?>
        </h3>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="chart_order_count" style="width: 100%;height:200px;"></div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <h3>订单销售额：￥
            <?php
                $order = Order::find()->select('sum(amount_money) as amount')->asArray()->one();
                echo $order['amount'];
            ?>
        </h3>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="chart_order_money" style="width: 100%;height:200px;"></div>
    </div>
</div>
</div>
<script type="text/javascript">
    function page_init() {
        showChartsUserReg();
        showChartsGoodsType();
        showChartsOrderCount();
        showChartsOrderMoney();
    }
    function showChartsUserReg() {
        <?php $user_reg = User::find()
            ->asArray()
            ->select([
                'date'=>Util::getDateGroup(Yii::$app->db->driverName, 'day', 'create_time'),
                'amount'=>'count(0)'
            ])
            ->andWhere(['>=', 'create_time', time() - 30 * 86400])
            ->groupBy('date')
            ->all();?>
        var myChart = echarts.init(document.getElementById('chart_user_reg'), 'macarons');
        var option = {
            title: {
                text: '用户注册量：'
            },
            tooltip : {
                trigger: 'axis',
                axisPointer : {
                    type : 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis : [
                {
                    type : 'category',
                    data : <?php echo json_encode(array_column($user_reg, 'date'));?>,
                    axisTick: {
                        alignWithLabel: true
                    }
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            series : [
                {
                    name:'数量',
                    type:'bar',
                    barWidth: '60%',
                    data:<?php echo json_encode(array_column($user_reg, 'amount'));?>
                }
            ]
        };
        myChart.setOption(option);
    }
    function showChartsGoodsType() {
        <?php $goods_type = GoodsType::find()
            ->leftJoin('goods', 'goods.tid = goods_type.id')
            ->select('goods_type.name, count(goods.id) as amount')
            ->groupBy('goods_type.id')
            ->asArray()
            ->all();?>
        var myChart = echarts.init(document.getElementById('chart_goods_type'), 'macarons');
        var option = {
            title: {
                text: '商品类型分布：'
            },
            tooltip: {
                trigger: 'item',
                formatter: "{a} <br/>{b}: {c} ({d}%)"
            },
            legend: {
                orient: 'vertical',
                x: 'left',
                y: 30,
                data:<?php $list = [];
                    foreach ($goods_type as $item) {
                        $list[] = $item['name'];
                    }
                    echo json_encode($list, JSON_UNESCAPED_UNICODE);?>
            },
            series: [
                {
                    name:'商品类型分布',
                    type:'pie',
                    radius: ['50%', '70%'],
                    avoidLabelOverlap: false,
                    label: {
                        normal: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            show: true,
                            textStyle: {
                                fontSize: '30',
                                fontWeight: 'bold'
                            }
                        }
                    },
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    data:<?php $data_list = [];
                        foreach ($goods_type as $item) {
                            $data_list[] = [
                                'name' => $item['name'],
                                'value' => $item['amount']
                            ];
                        }
                        echo json_encode($data_list, JSON_UNESCAPED_UNICODE);?>
                }
            ]
        };
        myChart.setOption(option);
    }
    function showChartsOrderCount() {
        <?php $order_count = Order::find()
        ->asArray()
        ->select([
            'date'=>Util::getDateGroup(Yii::$app->db->driverName, 'day', 'create_time'),
            'amount'=>'count(0)'
        ])
        ->andWhere(['>=', 'create_time', time() - 30 * 86400])
        ->groupBy('date')
        ->all();
        ?>
        var myChart = echarts.init(document.getElementById('chart_order_count'), 'macarons');
        var option = {
            title: {
                text: '用户下单量：'
            },
            tooltip : {
                trigger: 'axis',
                axisPointer : {
                    type : 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis : [
                {
                    type : 'category',
                    data : <?php echo json_encode(array_column($order_count, 'date'));?>,
                    axisTick: {
                        alignWithLabel: true
                    }
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            series : [
                {
                    name:'数量',
                    type:'bar',
                    barWidth: '60%',
                    data:<?php echo json_encode(array_column($order_count, 'amount'));?>
                }
            ]
        };
        myChart.setOption(option);
    }
    function showChartsOrderMoney() {
        <?php $order_money = Order::find()
        ->asArray()
        ->select([
            'date'=>Util::getDateGroup(Yii::$app->db->driverName, 'day', 'create_time'),
            'amount'=>'sum(amount_money)'
        ])
        ->groupBy('date')
        ->all();
        ?>
        var myChart = echarts.init(document.getElementById('chart_order_money'), 'macarons');
        var option = {
            title: {
                text: '订单销售额：'
            },
            tooltip : {
                trigger: 'axis',
                axisPointer : {
                    type : 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis : [
                {
                    type : 'category',
                    data : <?php echo json_encode(array_column($order_money, 'date'));?>,
                    axisTick: {
                        alignWithLabel: true
                    }
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            series : [
                {
                    name:'金额',
                    type:'bar',
                    barWidth: '60%',
                    data:<?php echo json_encode(array_column($order_money, 'amount'));?>
                }
            ]
        };
        myChart.setOption(option);
    }
</script>
