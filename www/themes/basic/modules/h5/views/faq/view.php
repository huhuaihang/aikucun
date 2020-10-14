<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\UserFaq;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $faq \app\models\Faq
 * @var $faq_list \app\models\Faq[] 相关问题列表
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = $faq->title;
?>
<div class="box1">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title"><?php echo Html::encode($faq->title);?></div>
    </header>
    <div class="container">
        <ul class="to_account">
            <li>
                <p class="fw_bold"><?php echo Html::encode($faq->title);?></p>
            </li>
            <li>
                <?php echo $faq->content;?>
                <div class="like_unlike clearfix">
                    <div class="like lu_sp" onclick="saveResult(<?php echo $faq->id;?>, <?php echo UserFaq::RESULT_SUCCESS;?>)">
                        <div class="like_img">
                            <img id="img_result_<?php echo UserFaq::RESULT_SUCCESS;?>" src="/images/like.png" data-alt="/images/like1.png" width="100%" height="100%"/>
                        </div>
                        <span>已解决</span>
                    </div>
                    <div class="unlike lu_sp" onclick="saveResult(<?php echo $faq->id;?>, <?php echo UserFaq::RESULT_FAIL;?>)">
                        <div class="like_img">
                            <img id="img_result_<?php echo UserFaq::RESULT_FAIL;?>" src="/images/unlike.png" data-alt="/images/unlike1.png" width="100%" height="100%"/>
                        </div>
                        <span>未解决</span>
                    </div>
                </div>
            </li>
        </ul>
        <?php if (!empty($faq_list)) {?>
            <h3 class="wenti_title">相关问题</h3>
            <?php foreach ($faq_list as $faq) {?>
                <a class="wentinei" href="<?php echo Url::to(['/h5/faq/view', 'id' => $faq->id]);?>"><?php echo Html::encode($faq->title);?><div class="youjiantou"><img src="/images/12.jpg" width="100%" height="100%"/></div></a>
            <?php }?>
        <?php }?>
    </div>
</div><!--box-->
<script>
    function saveResult(id, result) {
        $.getJSON('<?php echo Url::to(['/h5/faq/save-result']);?>', {'id':id, 'result':result}, function (json) {
            if (callback(json)) {
                var $img = $('#img_result_' + result);
                $img.attr('src', $img.data('alt'));
            }
        });
    }
</script>
