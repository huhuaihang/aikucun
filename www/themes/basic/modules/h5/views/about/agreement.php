<?php

/**
 * @var $this \yii\web\View
 * @var $agreement \app\models\System
 */


$this->title = $agreement['show_name'];
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title"><?php echo $agreement['show_name'];?></div>
    </header>
    <div class="container">
        <div class="b_gipl_cont">
            <?php echo $agreement['value'];?>
        </div>
    </div>
</div>
<script>
    function page_init(){
        <?php if (!empty(Yii::$app->request->get('app'))) {?>
        $('.mall-header').hide();
        $('.container').css('margin-top','0');
        <?php }?>
    }
</script>