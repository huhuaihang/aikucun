<?php

use app\assets\EchartsAsset;
use app\models\Goods;
use app\models\GoodsType;
use app\models\User;
use app\models\Util;

/**
 * @var $this \yii\web\View
 */

EchartsAsset::register($this);

$this->title = '2019统计';
$this->params['breadcrumbs'][] = '统计分析';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12"><h3>注册用户总量：122313</h3></div>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="chart_user_reg" style="width: 100%; height: 200px;"></div>
    </div>
</div>
<div class="row">
    <div class="col-md-12"><h3>上架商品总数量：1891</h3></div>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="chart_goods" style="width: 100%; height:300px;"></div>
    </div>
</div>
<div class="row">
    <div class="col-md-12"><h3>订单总数量：2153913</h3></div>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="chart_order" style="width: 100%; height: 200px;"></div>
    </div>
</div>
<div class="row">
    <div class="col-md-12"><h3>订单销售额：262653512</h3></div>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="chart_order_money" style="width: 100%; height: 200px;"></div>
    </div>
</div>
<script>
    function page_init() {
        showChartUserReg();
        showChartGoods();
        showChartOrder();
        showChartOrderMoney();
    }

    /**
     * 商品统计
     */
    function showChartGoods() {
        <?php $goods_type = GoodsType::find()
        ->leftJoin('goods', 'goods.tid = goods_type.id')
        ->select('goods_type.id, goods_type.name, count(goods.id) as amount')
        ->groupBy('goods_type.id')
        ->asArray()
        ->all();?>
        var myChart = echarts.init(document.getElementById('chart_goods'), 'macarons');
        var option = {
            title : {
                text: '商品类型分布'
            },
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                type: 'scroll',
                orient: 'vertical',
                left: 10,
                top: 20,
                bottom: 20,
                data: <?php $list = [];foreach ($goods_type as $item) {$list[] = $item['name'];}echo json_encode($list, JSON_UNESCAPED_UNICODE);?>
            },
            series : [
                {
                    name: '商品类型分布',
                    type: 'pie',
                    radius : '55%',
                    center: ['40%', '50%'],
                    data: <?php $data_list = [];
                    foreach ($goods_type as $item) {
                        $data_list[] = [
                            'name' => $item['name'],
                            'value' => $item['amount']+ $item['id'],//rand(100,2630),
                        ];
                    }
                    echo json_encode($data_list, JSON_UNESCAPED_UNICODE);?>,
                    itemStyle: {
                        emphasis: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        };
        myChart.setOption(option);
    }

    /**
     * 用户统计
     */
    function showChartUserReg() {
        <?php $user_reg = User::find()
        ->asArray()
        ->select([
            'day' => Util::getDateGroup(Yii::$app->db->driverName, 'day', 'create_time'),
            'amount' => 'count(0)'
        ])
        ->andWhere(['>=', 'create_time', strtotime(date('Y-01-01'))])
        ->groupBy('day')
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
                    data : ['2019-01', '2019-02', '2019-03', '2019-04', '2019-05', '2019-06', '2019-07', '2019-08',
                        '2019-09', '2019-10', '2019-11', '2019-12'],
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
                    barWidth: '20%',
                    data:['20141', '20941', '11021', '13241', '14011',  '15463',  '16380',  '17961',  '18081',  '19921',  '38601',  '30623']
                }
            ]
        };
        myChart.setOption(option);
    }

    /**
     * 订单统计
     */
    function showChartOrder() {

        var myChart = echarts.init(document.getElementById('chart_order'), 'macarons');
        var option = {
            title: {
                text: '订单总数量：'
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
                    data : ['2019-01', '2019-02', '2019-03', '2019-04', '2019-05', '2019-06', '2019-07', '2019-08',
                        '2019-09', '2019-10', '2019-11', '2019-12'],
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
                    barWidth: '25%',
                    data:['30131', '34541', '23021', '21241', '20511',  '25463',  '26387',  '27961',  '30081',  '35923',  '64697',  '61635']
                }
            ]
        };
        myChart.setOption(option);
    }

    /**
     * 订单统计
     */
    function showChartOrderMoney() {

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
                    data : ['2019-01', '2019-02', '2019-03', '2019-04', '2019-05', '2019-06', '2019-07', '2019-08',
                        '2019-09', '2019-10', '2019-11', '2019-12'],
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
                    barWidth: '25%',
                    data:['3024109', '3454108', '2203107', '2125106', '2601325',  '2476304',  '2738063',  '2695102',
                        '2708151',  '3892109',  '6530909',  '6231959']
                }
            ]
        };
        myChart.setOption(option);
    }
</script>


