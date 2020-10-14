<?php

use app\assets\InfiniteScrollAsset;
use app\models\GoodsComment;
use app\models\GoodsCommentReply;
use app\widgets\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $comment_list \app\models\GoodsComment[]
 * @var $pagination \yii\data\Pagination
 * @var $gid int
 * @var $amount_all int
 * @var $amount_pic int
 * @var $amount_new int
 */

InfiniteScrollAsset::register($this);
$this->registerJsFile('/js/fs_forse.js', ['depends' => ['yii\web\JqueryAsset']]);

$this->title = '用户评论';
?>
<style>
    body {background:#fff;}
</style>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5/goods/view', 'id' => Yii::$app->request->get('gid')]);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">用户评论</div>
    </header>
    <div class="container">
        <div class="z_commentaries">
            <ul>
                <li<?php if (empty(Yii::$app->request->get('img')) && empty(Yii::$app->request->get('new')) && empty(Yii::$app->request->get('low'))) {echo ' class="color"';}?>><a href="<?php echo Url::to(['/h5/goods/comment', 'gid' => $gid]);?>">全部(<?php echo $amount_all;?>)</a></li>
                <li<?php if (!empty(Yii::$app->request->get('img'))) {echo ' class="color"';}?>><a href="<?php echo Url::to(['/h5/goods/comment', 'gid' => $gid, 'img' => 1]);?>">晒图(<?php echo $amount_pic;?>)</a></li>
                <li<?php if (!empty(Yii::$app->request->get('low'))) {echo ' class="color"';}?>><a href="<?php echo Url::to(['/h5/goods/comment', 'gid' => $gid, 'low' => 1]);?>">低分(<?php echo $amount_low;?>)</a></li>
                <li<?php if (!empty(Yii::$app->request->get('new'))) {echo ' class="color"';}?>><a href="<?php echo Url::to(['/h5/goods/comment', 'gid' => $gid, 'new' => 1]);?>">最新(<?php echo $amount_new;?>)</a></li>
            </ul>
        </div><!--z_commentaries-->
        <div class="clear"></div>
        <div class="product_details">
            <div class="div4_box data_list" style="border:0;">
                <?php foreach ($comment_list as $comment) {?>
                    <div class="div4">
                        <div class="p2">
                            <span class="left"><?php echo preg_replace('/^(\d{3})(\d{4})(\d{4})$/', '$1****$3', $comment->user->mobile);?></span>
                            <span class="right"><?php echo Yii::$app->formatter->asDate($comment->create_time);?></span>
                        </div>
                        <div class="p3">
                            <p class="left"><?php if (empty($comment->content)) {
                                    if (empty($comment->getImgList())) {
                                        echo '<i>此用户没有留下任何评价。</i>';
                                    }
                                } else {
                                    echo Html::encode($comment->content);
                                }?></p>
                        </div>
                        <ul class="thumbnails gallery">
                            <?php foreach ($comment->getImgList() as $img) {?>
                                <li class="span3">
                                    <a href="<?php echo Yii::$app->params['upload_url'], $img;?>">
                                        <img src="<?php echo Yii::$app->params['upload_url'], $img;?>_55x55" alt="" />
                                    </a>
                                </li>
                            <?php }?>
                        </ul>
                        <?php $append_comment_list = GoodsComment::find()->andWhere(['pid' => $comment->id])->all();?>
                        <?php $reply_list = GoodsCommentReply::find()->andWhere(['cid' => $comment->id])->all();?>
                        <?php if (!empty($append_comment_list) || !empty($reply_list)) {?>
                            <div class="clear"></div>
                            <div class="additional">
                                <?php if (!empty($append_comment_list)) {?>
                                    <div class="additional_headings">追加评论</div>
                                    <?php foreach ($append_comment_list as $append_comment) {/** @var GoodsComment $append_comment */?>
                                        <div class="additional_time"><?php echo Yii::$app->formatter->asDate($append_comment->create_time);?></div>
                                        <div class="additional_text"><?php echo Html::encode($append_comment->content);?></div>
                                    <?php }?>
                                <?php }?>
                                <?php if (!empty($reply_list)) {?>
                                    <div class="additional_revert">
                                        <div class="span1">商家回复:</div>
                                        <?php foreach ($reply_list as $reply) {/** @var GoodsCommentReply $reply */?>
                                        <p><?php echo nl2br(Html::encode($reply->content));?></p>
                                        <?php }?>
                                    </div>
                                <?php }?>
                            </div><!--additional-->
                        <?php }?>
                    </div><!--div4-->
                    <div class="clear"></div>
                <?php }?>
            </div><!--div4_box-->
        </div><!--product_details-->
    </div>
</div><!--box-->
<div style="display:none;"><?php echo LinkPager::widget(['pagination'=>$pagination]);?></div>
<script>
    function page_init() {
        // 自动加载更多记录
        $('.data_list').infinitescroll({
            loading: {
                msgText: '正在加载更多记录。',
                finishedMsg: '没有更多记录了。'
            },
            navSelector: ".pagination",
            nextSelector: ".pagination .next a",
            itemSelector: ".data_list",
            maxPage:<?php echo $pagination->pageCount;?>
        }, function () {
            $('.data_list:last .gallery img').fsgallery();
        });
        $('.gallery img').fsgallery();
    }
</script>
