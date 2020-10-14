<?php

use app\assets\EchartsAsset;
use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $table array 统计数据
 * [
 *     $mid => [
 *         'mid' => $mid,
 *         'username' => $username,
 *         'merchant_receive_money' => $merchant_receive_money,
 *     ]
 * ]
 * @var $search_start_date string 搜索开始日期
 * @var $search_end_date string 搜索结束日期
 */

EchartsAsset::register($this);
MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '商户结算';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_merchant" class="sr-only">商户</label>
    <?php echo Html::textInput('search_merchant', Yii::$app->request->get('search_merchant'), ['id' => 'search_merchant', 'class' => 'form-control', 'placeholder' => '商户']);?>
</div>
<div class="form-group">
    <label for="search_start_date" class="sr-only">日期</label>
    <?php echo Html::textInput('search_start_date', $search_start_date, ['id' => 'search_start_date', 'class' => 'form-control masked', 'data-mask' => '9999-99-99', 'placeholder' => '开始日期', 'style' => 'max-width:90px;']);?>
    -
    <?php echo Html::textInput('search_end_date', $search_end_date, ['id' => 'search_end_date', 'class' => 'form-control masked', 'data-mask' => '9999-99-99', 'placeholder' => '结束日期', 'style' => 'max-width:90px;']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<?php echo Html::endForm();?>
<div class="row">
    <div class="col-md-12">
        <div id="chart" style="width:100%;height:200px;"></div>
    </div>
</div>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl"></span>
            </label>
        </th>
        <th>商户</th>
        <th>金额</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($table as $item) {?>
        <tr id="data_<?php echo $item['mid'];?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $item['mid'];?>"/>
                    <span class="lbl"><?php echo $item['mid'];?></span>
                </label>
            </td>
            <td><?php echo Html::encode($item['username']);?></td>
            <td><?php echo $item['merchant_receive_money'];?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-dollar', 'href' => Url::to(['/admin/finance/pay-merchant-financial-settlement', 'mid' => $item['mid'], 'start_date' => $search_start_date, 'end_date' => $search_end_date]), 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '支付', 'color' => 'yellow'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<script>
    function page_init() {
        show_chart();
    }
    function show_chart() {
        var myChart = echarts.init(document.getElementById('chart'), 'macarons');
        var option = {
            title: {
                text: '商户结算金额统计：'
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
                    data : <?php echo json_encode(array_column($table, 'username'));?>,
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
                    name:'结算金额',
                    type:'bar',
                    barWidth: '10%',
                    data:<?php echo json_encode(array_column($table, 'merchant_receive_money'));?>
                }
            ]
        };
        myChart.setOption(option);
    }
</script>
