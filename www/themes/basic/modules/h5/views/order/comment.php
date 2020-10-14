<?php

use app\assets\ApiAsset;
use app\assets\FileUploadAsset;
use app\assets\LayerAsset;
use app\models\GoodsComment;
use app\models\GoodsCommentReply;
use app\models\Order;
use yii\helpers\Html;
use yii\helpers\Url;

/**
* @var $this \yii\web\View
* @var $order \app\models\Order
*/

ApiAsset::register($this);
LayerAsset::register($this);
FileUploadAsset::register($this);

$this->title = '订单评价';
?>
<?php echo Html::beginForm('', 'post', ['id' => 'comment_form', 'onsubmit' => 'return saveComment();']);?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5/order/view', 'order_no' => $order->no]);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">评价</div>
        <div class="mall-header-right">
            <?php echo Html::submitButton('提交');?>
        </div>
    </header>
    <div class="container">
        <div class="zpj">
            <div class="div3">
                <div class="left"><img src="/images/pingjia_01.jpg"><?php echo Html::encode($order->shop->name);?></div>
                <div class="right score">
                    <?php if ($order->status == Order::STATUS_RECEIVED) {?>
                        <?php echo Html::hiddenInput('ShopScore[score]', 5);?>
                        <img src="/images/heart1.png">
                        <img src="/images/heart1.png">
                        <img src="/images/heart1.png">
                        <img src="/images/heart1.png">
                        <img src="/images/heart1.png">
                    <?php }?>
                </div>
            </div>
            <?php foreach ($order->itemList as $item) {?>
                <div class="div1 zpj">
                    <dl class="left">
                        <dt>
                            <a href="<?php echo Url::to(['/h5/goods/view', 'id' => $item->gid]);?>">
                                <img src="<?php echo Yii::$app->params['upload_url'], $item->goods->main_pic;?>">
                            </a>
                        </dt>
                        <dd class="dd1">
                            <a href="<?php echo Url::to(['/h5/goods/view', 'id' => $item->gid]);?>">
                                <?php echo $item->title;?>
                            </a>
                        </dd>
                        <dd class="dd2">
                            <a href="javascript:void(0);">商品评分</a>
                        </dd>
                    </dl>
                    <div class="right score">
                        <?php if ($order->status == Order::STATUS_RECEIVED) {?>
                            <?php echo Html::hiddenInput('GoodsComment[' . $item->id . '][score]', 5);?>
                            <img src="/images/heart1.png">
                            <img src="/images/heart1.png">
                            <img src="/images/heart1.png">
                            <img src="/images/heart1.png">
                            <img src="/images/heart1.png">
                        <?php }?>
                    </div>
                </div>
                <?php if ($order->status == Order::STATUS_COMPLETE) {?>
                    <?php $comment = GoodsComment::find()->andWhere(['pid' => null, 'gid' => $item->gid, 'uid' => $order->uid, 'oid' => $order->id])->one();/** @var GoodsComment $comment */?>
                    <div class="b_letter1">
                        <p class="b_fbtime b_magt3"><?php echo Yii::$app->formatter->asDatetime($comment->create_time);?></p>
                        <p class="b_fedback_prev"><?php echo Html::encode($comment->content);?></p>
                    </div>
                    <?php $append_comment_list = GoodsComment::find()->andWhere(['pid' => $comment->id])->all();
                    $reply_list = GoodsCommentReply::find()->andWhere(['cid' => $comment->id])->all();
                    if (!empty($append_comment_list) || !empty($reply_list)) {?>
                        <div class="b_letter1">
                            <div class="b_letter_in">
                                <p class="b_zhuiping">追评：</p>
                                <?php foreach ($append_comment_list as $append_comment) {/** @var GoodsComment $append_comment */?>
                                    <p class="b_fbtime "><?php echo Yii::$app->formatter->asDatetime($append_comment->create_time);?></p>
                                    <p class="b_fedback_prev "><?php echo Html::encode($append_comment->content);?></p>
                                <?php }?>
                                <?php foreach ($reply_list as $reply) {/** @var GoodsCommentReply $reply */?>
                                    <div class="b_merchant_replay">
                                        <p>[卖家回复]<?php echo nl2br(Html::encode($reply->content));?></p>
                                    </div>
                                <?php }?>
                            </div>
                        </div>
                    <?php }?>
                <?php }?>
                <div class="textarea">

                    <textarea name="GoodsComment[<?php echo $item->id;?>][content]" onkeyup='value=value.substr(0,512);this.nextSibling.innerHTML=value.length+""; '></textarea><div class="p1">0</div><div class="p2">/ 512个字</div>
                </div>
                <?php if ($order->status == Order::STATUS_RECEIVED) {?>
                    <div class="b_upload_area">
                        <p>上传张片：(点击图片可以删除)</p>
                        <div class="b_upload_btn">
                            <?php echo Html::hiddenInput('GoodsComment[' . $item->id . '][img_list]');?>
                            <input type="file" data-oi_id="<?php echo $item->id;?>"  name="files[]" data-url="<?php echo Url::to(['/h5/order/upload', 'dir' => 'goods_comment'])?>" />
                        </div>
                    </div>
                <?php }?>
                <div class="z_zpj_div2">
                    <input class="filled-in" data-oi_id="<?php echo $item->id;?>" id="filled-in-box<?php echo $item->id;?>" name="GoodsComment[<?php echo $item->id;?>][is_anonymous]" type="checkbox" value="1" checked="checked" style="display:none;" >
                    <label for="filled-in-box<?php echo $item->id;?>"><span>匿名评价</span></label>
                </div>
            <?php }?>
        </div>
    </div>
</div>
<?php echo Html::endForm();?>
<script>
    function page_init() {
        <?php $error = Yii::$app->session->getFlash('error');
        if (!empty($error)) {
            echo 'layer.msg("' . $error . '", function () {});';
        }?>
        $('input[type="file"]').fileupload({
            done: function (e, data) {
                var oi_id = $(this).data('oi_id');
                var json = data.result;
                if (callback(json)) {
                    var url = json.files[0].url,
                        src= json.base+url,
                        $img_list = $(this).parent().find('[name="GoodsComment[' + oi_id + '][img_list]"]'),
                        val = $img_list.val();
                    if (val == '') {
                        val = [];
                    } else {
                        val = JSON.parse(val);
                    }
                    val.push(url);
                    $img_list.val(JSON.stringify(val));
                    $(this).parent('.b_upload_btn').before("<img src="+src+" width='50'>");
                }
            }
        });

        $('.score img').on('click', function () {
            var index = $(this).index();
            $(this).prevAll('img').attr('src', '/images/heart1.png');
            $(this).attr('src', '/images/heart1.png');
            $(this).nextAll('img').attr('src', '/images/heart.png');
            $(this).siblings('input[type="hidden"]').val(index);
        });

        // 点击删除上传的评论图片  未来事件on()
        $('.b_upload_area').on('click', 'img', function(){
            var $this = $(this),
                src = $this.attr('src'),
                hideinput = $this.siblings('.b_upload_btn').children('input[type="hidden"]');
            $this.remove();
            src = src.substr(9, src.length);
            var jsonstr = hideinput.val(),
                json = JSON.parse(jsonstr);
            if ($.inArray(src, json) >= 0) {
                json.splice($.inArray(src, json),1);
            }
            jsonstr = JSON.stringify(json);
            hideinput.val(jsonstr);
        });
    }

    function saveComment() {
        var $form = $('#comment_form');
        var data = $form.serializeArray();
        data.push({'name':'ajax', 'value':1});
        $.post($form.attr('action'), data, function (json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
        return false;
    }
</script>
