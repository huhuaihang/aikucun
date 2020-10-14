<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\MaskedInputAsset;
use app\models\System;
use kucha\ueditor\UEditor;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $show_category string 需要展示的分类名称
 */

ApiAsset::register($this);
LayerAsset::register($this);
MaskedInputAsset::register($this);

$this->title = '系统设置';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tabbable tabs-left">
    <ul class="nav nav-tabs" id="user_role">
        <?php $category_list = System::find()->asArray()->select('category')->distinct()->all();?>
        <?php foreach ($category_list as $category) {?>
            <li<?php if ($show_category == $category['category']) {echo ' class="active"';}?>>
                <a href="<?php echo Url::to(['/admin/system/config', 'category'=>Html::encode($category['category'])]);?>">
                    <?php echo $category['category'];?>
                </a>
            </li>
        <?php }?>
    </ul>
    <div class="tab-content">
        <div class="tab-pane in active">
            <div class="container" style="width:100%;">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo Html::beginForm(['/admin/system/config', 'category'=>$show_category], 'post', ['class'=>'form-horizontal', 'id'=>'system-form', 'enctype'=>'multipart/form-data']);?>
                            <?php $config_list = System::find()->andWhere(['category'=>$show_category])->all();?>
                            <?php foreach ($config_list as $config) {?>
                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="<?php echo $config['name'];?>"><?php echo $config['show_name'];?></label>
                                    <div class="col-md-10">
                                        <?php $type = json_decode($config['type'], true);?>
                                        <?php switch ($type['type']) {
                                            case 'text':echo Html::textInput($config['name'], $config['value'], ['id'=>$config['name'], 'placeholder'=>$config['value'], 'style'=>'width:100%;', 'disabled'=>isset($type['disabled']) && $type['disabled'], 'title'=>isset($type['desc']) ? $type['desc'] : null]);break;
                                            case 'datetext':echo Html::textInput($config['name'], $config['value'], ['id'=>$config['name'], 'placeholder'=>$config['value'],  'class'=>'form-control masked','data-mask'=>'9999-99-99 99:99:99', 'style'=>'width:100%;', 'disabled'=>isset($type['disabled']) && $type['disabled'], 'title'=>isset($type['desc']) ? $type['desc'] : null]);break;
                                            case 'plaintext':echo Html::textarea($config['name'], $config['value'], ['id'=>$config['name'], 'style'=>'width:100%;']);break;
                                            case 'richtext':echo Html::button('编辑', ['class' => 'btn btn-info btn-sm', 'onclick' => 'layer.open({type:1,title:false,content:$(\'#box_' . $config['name'] . '\'),area:\'1000px\',shadeClose:true})']);
                                                echo '<div id="box_' . $config['name'] . '" style="display:none;">';
                                                echo UEditor::widget([
                                                    'id' => $config['name'],
                                                    'name' => $config['name'],
                                                    'value' => $config['value'],
                                                    'clientOptions' => [
                                                        'serverUrl' => Url::to(['ue-upload']),
                                                    ]
                                                ]);
                                                echo '</div>';
                                                break;
                                            case 'radio':echo Html::radioList($config['name'], $config['value'], $type['options']);break;
                                            case 'file':echo Html::a($config['value'], Yii::$app->params['upload_url'] . $config['value']);echo Html::fileInput($config['name']);break;
                                            case 'json':echo '<pre>';print_r($type['json']);print_r(json_decode($config['value'], true));echo '</pre>';echo Html::textInput($config['name'], $config['value'], ['id'=>$config['name'], 'placeholder'=>$config['value'], 'style'=>'width:100%;']);break;
                                            default:
                                        }?>
                                    </div>
                                </div>
                            <?php }?>
                            <?php if (count($config_list) > 0) {?>
                                <div class="form-group">
                                    <div class="col-md-offset-2 col-md-10">
                                        <button class="btn btn-info">
                                            <i class="ace-icon fa fa-check bigger-110"></i>
                                            保存
                                        </button>
                                        <button class="btn" type="reset">
                                            <i class="ace-icon fa fa-undo bigger-110"></i>
                                            重置
                                        </button>
                                    </div>
                                </div>
                            <?php }?>
                        <?php echo Html::endForm();?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
