<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\widgets\AdWidget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $history_list \app\models\UserSearchHistory[]
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '搜索';
?>
<div class="box">
    <?php echo Html::beginForm(['/h5/goods/list'], 'get', ['id' => 'search_form']);?>
    <header class="mall-header mall-search">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5']);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">
            <?php echo Html::textInput('keywords', '', ['placeholder' => '输入商家名或品类', 'id' => 'keywords']);?>
        </div>
        <div class="mall-header-right">
            <a href="javascript:void(0)" onclick="submitSearch()">搜索</a>
        </div>
    </header>
    <?php echo Html::endForm();?>
    <!--B_head-->
    <div class="container">
        <div class="sea_text">
            <div class="div1 div2">
                <p>历史搜索<span><a href="javascript:void(0);" onclick="clear_history()"><img src="/images/27.png"></a></span></p>
                <?php if (!empty($history_list)) {?>
                <ul>
                    <?php foreach ($history_list as $model) {?>
                    <li>
                        <a href="<?php echo Url::to(['/h5/goods/list', 'keywords' => $model->keyword])?>">
                            <?php echo Html::encode($model->keyword); ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
                <?php } else { ?>
                    <div class="clear"></div>
                    <div class="show">暂无搜索历史</div>
                <?php } ?>
            </div><!--div1-->
            <div class="div1">
                <p>热门搜索</p>
                <ul>
                    <?php AdWidget::begin(['lid' => 4]);?>
                    {foreach $ad_list as $ad}
                    <li><a href="<?php echo Url::to(['/site/da']);?>?id={$ad['id']}">{$ad['txt']}</a></li>
                    {/foreach}
                    <?php AdWidget::end();?>
                </ul>
            </div><!--div1-->
        </div>
    </div>
</div>
<script>
    function submitSearch() {
        if ($('#keywords').val() !== '') {
            $('#search_form').submit();
        } else {
            var keyword = '<?php echo empty($history_list) ? '' : $history_list[0]->keyword;?>';
            if (keyword !== '') {
                $('#keywords').val(keyword);
                $('#search_form').submit();
            }
        }
    }
    /**
     * 清空搜索历史
     */
    function clear_history() {
        $.getJSON('<?php echo Url::to(['/h5/default/delete-history'])?>', function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
</script>
