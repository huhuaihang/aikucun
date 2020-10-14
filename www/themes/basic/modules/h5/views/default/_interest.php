<?php

use app\models\Goods;
use app\models\UserSearchHistory;
use app\widgets\AdWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

/**
 * 推荐策略：
 * 用户最近查看的商品关键字和最近的搜索关键字
 * 需要考虑未登录用户最后几次查看记录和最后几次搜索记录放到cookie中
 */
$keywords = [];
if (!Yii::$app->user->isGuest) {
    $query = UserSearchHistory::find();
    $query->andWhere(['uid' => Yii::$app->user->id]);
    /** @var UserSearchHistory[] $history_list */
    $history_list = $query->orderBy('create_time DESC')->limit('10')->all();
    $keywords = ArrayHelper::getColumn($history_list, 'keyword');
} else {
    if (Yii::$app->request->cookies->has('history')) {
        $keywords = Yii::$app->request->cookies->getValue('history');
    }
}
$query = Goods::find();
$query->andFilterWhere(['or like', 'keywords', $keywords]);
$query->andWhere(['status' => Goods::STATUS_ON]);
$goods_list = $query->limit(6)->all();
?>
<div class="biaoti">您可能感兴趣</div>
<div id="data_list">
    <?php if (count($goods_list) % 2 != 0) { array_pop($goods_list);}?>
    <?php foreach ($goods_list as $key => $goods) {/** @var Goods $goods */?>
        <dl>
            <dt><a href="<?php echo Url::to(['/h5/goods/view', 'id' => $goods->id]);?>"><img src="<?php echo Yii::$app->params['upload_url'], $goods->main_pic;?>_200x200"></a></dt>
            <dd class="dd1"><a href="<?php echo Url::to(['/h5/goods/view', 'id' => $goods->id]);?>"><?php echo Html::encode($goods->title);?></a></dd>
            <dd class="dd2"><a href="<?php echo Url::to(['/h5/goods/view', 'id' => $goods->id]);?>">￥<?php echo $goods->price;?></a></dd>
        </dl>
    <?php } ?>
    <?php AdWidget::begin(['lid' => 4]);?>
    {foreach $ad_list as $ad}
    <div class="interested-ad">
        <a href="{$ad['url']}"><img src="<?php echo Yii::$app->params['upload_url'];?>{$ad['img']}" /></a>
    </div>
    {/foreach}
    <?php AdWidget::end();?>
</div>
<div id="da-goods">
<?php AdWidget::begin(['lid' => 5]);?>
{if count($ad_list) > 0}
    {foreach $ad_list as $ad}
    {assign var='goods' value=$ad->getGoods()}
    {if $goods === false}{continue}{/if}
        <dl>
            <dt><a href="<?php echo Url::to(['/site/da']);?>?id={$ad['id']}"><img src="<?php echo Yii::$app->params['upload_url'];?>{$goods['main_pic']}"></a></dt>
            <dd class="dd1"><a href="<?php echo Url::to(['/site/da']);?>?id={$ad['id']}">{$goods['title']}</a></dd>
            <dd class="dd2"><a href="<?php echo Url::to(['/site/da']);?>?id={$ad['id']}">￥{$goods->getMinPrice()}</a></dd>
        </dl>
    {/foreach}
{/if}
</div>
<?php AdWidget::end();?>
<?php AdWidget::begin(['lid' => 7]);?>
    {foreach $ad_list $ad}
    <div class="interested-ad">
        <a href="{$ad['url']}"><img src="<?php echo Yii::$app->params['upload_url'];?>{$ad['img']}" /></a>
    </div>
    {/foreach}
<?php AdWidget::end();?>