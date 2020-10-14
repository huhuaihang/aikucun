<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $videoList \app\models\GoodsTraceVideo[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '视频列表';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_name" class="sr-only">名称</label>
    <?php echo Html::textInput('search_name', Yii::$app->request->get('search_name'), ['id' => 'search_name', 'class' => 'form-control', 'placeholder' => '名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br/>
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/hand/trace-video-edit']);?>">添加</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl"></span>
            </label>
        </th>
        <th>名称</th>
        <th>分类</th>
        <th>封面</th>
        <th>视频</th>
        <th>阅读量</th>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($videoList as $video) {?>
        <tr id="data_<?php echo $video->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $video->id;?>"/>
                    <span class="lbl"><?php echo $video->id;?></span>
                </label>
            </td>
            <td><?php echo Html::encode($video->name);?></td>
            <td><?php echo Html::encode(KeyMap::getValue('goods_trace_video_type', $video->cid));?></td>
            <td><img src="<?php echo $video->cover_image;?>" width="64"></td>
            <td><?php echo $video->video;?></td>
            <td  style="font-size: 16px;"><?php echo $video->read_count==null?0:$video->read_count;?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($video->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/hand/trace-video-edit', 'id' => $video->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteVideo(' . $video->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     * 删除图文素材
     * @param id 图文素材编号
     */
    function deleteVideo(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/hand/delete-video']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }
</script>
