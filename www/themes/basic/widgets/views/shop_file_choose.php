<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\ShopFile;
use app\models\ShopFileCategory;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $this \yii\web\View
 * @var $sid integer 店铺编号
 */

ApiAsset::register($this);
LayerAsset::register($this);
?>
<div id="shop_file_box" style="display:none;">
    <div class="container" style="width:840px;">
        <div class="row">
            <div class="col-sm-12">
                <h4>选择图片</h4>
            </div>
        </div>
        <div class="space-4"></div>
        <?php Pjax::begin(['enablePushState' => false]);?>
        <div class="row">
            <div class="col-sm-12">
                <?php echo Html::beginForm('', 'get', ['class' => 'form-inline', 'data-pjax' => true]);?>
                <div class="form-group">
                    <label class="control-label sr-only">分类</label>
                    <?php echo Html::dropDownList('search_cid', Yii::$app->request->get('search_cid'), ArrayHelper::map(ShopFileCategory::find()->andWhere(['sid' => $sid])->all(), 'id', 'name'), ['prompt' => '搜索分类', 'class' => 'form-control']);?>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary btn-sm">搜索</button>
                    <button type="button" class="btn btn-default btn-sm" onclick="$('.image_list li.chosen').each(function(){$(this).click();});">取消选择</button>
                </div>
                <?php echo Html::endForm();?>
            </div>
        </div>
        <div class="space-4"></div>
        <div class="row">
            <div class="col-sm-12">
                <ul class="ace-thumbnails clearfix image_list" style="max-height:500px; overflow-y:auto;">
                    <?php foreach (ShopFile::find()->andWhere(['sid' => $sid])->andFilterWhere(['cid' => Yii::$app->request->get('search_cid')])->each() as $file) {/** @var ShopFile $file */?>
                        <li data-url="<?php echo $file->url;?>" onclick="var $this=$(this);if($this.hasClass('chosen')){$this.removeClass('chosen').find('.text').css('opacity','0').find('.inner').html('');}else{$this.addClass('chosen').find('.text').css('opacity','1').find('.inner').html('已选中');}">
                            <a>
                                <img width="150" alt="150x*" src="<?php echo Yii::$app->params['upload_url'], $file->url;?>_150x150" />
                                <div class="text">
                                    <div class="inner"></div>
                                </div>
                            </a>
                        </li>
                    <?php }?>
                </ul>
            </div>
        </div>
        <div class="space-4"></div>
        <?php Pjax::end();?>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group pull-right">
                    <button type="button" class="btn btn-primary btn-sm btn_ok">完成</button>
                    <button type="button" class="btn btn-default btn-sm btn_cancel">取消</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    /**
     * 选择店铺文件
     * @param callback 回调方法
     * @param options 参数
     */
    function chooseShopFile(callback, options) {
        layer.open({
            type:1,
            title:false,
            shadeClose:true,
            closeBtn:false,
            area:'840px',
            scrollbar:false,
            content:$('#shop_file_box'),
            success:function (layero, idx) {
                layero.find('.btn_cancel').unbind('click').click(function () {layer.close(idx);});
                layero.find('.btn_ok').unbind('click').click(function () {
                    layer.close(idx);
                    var url_list = [];
                    layero.find('li.chosen').each(function () {
                        url_list.push($(this).data('url'));
                        $(this).click();
                    });
                    callback(url_list);
                });
            }
        });
    }
</script>
