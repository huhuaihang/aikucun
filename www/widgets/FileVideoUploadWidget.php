<?php

namespace app\widgets;

use app\assets\FileUploadAsset;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * 文件上传组件
 * Class FileVideoUploadWidget
 * @package app\widgets
 *
 * 使用方法一：
 * <?php echo $form->field($model, 'attr')->widget(FileUploadWidget::className(), [
 *     'url' => Url::to(...),
 *     'callback' => 'uploadCallback',
 * ]);?>
 * 使用方法二：
 * <?php echo FileUploadWidget::widget([
 *     'name' => 'Model[attr]',
 *     'url' => Url::to(...),
 *     'click_node' => '#btn_upload',
 *     'callback' => 'uploadCallback',
 * ]);?>
 * <script>
 * function uploadCallback(url) {
 *     // 此处url为服务器保存目录的相对地址
 *     console.log(url);
 * }
 * </script>
 */
class FileVideoUploadWidget extends InputWidget
{
    /**
     * @var string 文件上传路径
     */
    public $url;
    /**
     * @var string 上传请求方式
     */
    public $method = 'POST';
    /**
     * @var array 同步提交的表单数据
     * [
     *     {name, value},
     *     ...
     * ]
     */
    public $formData = [];
    /**
     * @var boolean 是否支持多文件上传
     */
    public $multiple = false;
    /**
     * @var string 关联按钮，可以指定用户点击位置
     */
    public $click_node = '';
    /**
     * @var string 上传完成后回调函数名称
     * 例如在页面中定义
     * function myCallback(fid, url) {...}
     * 则此处写
     * myCallback
     */
    public $callback;
    /**
     * @var boolean 是否为阿里云OSS
     */
    public $isAliyunOss = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
        FileUploadAsset::register($this->getView());
        if ($this->hasModel()) {
            $input = Html::activeHiddenInput($this->model, $this->attribute);
        } else {
            if (!empty($this->name)) {
                $input = Html::hiddenInput($this->name, $this->value);
            }
        }
        if (!empty($input)) {
            echo $input; // Hidden Input
        }
        $id = rand(0, 99999999);
        $name = $this->multiple ? 'files[]' : 'file';
        $input = '<input id="fileupload_' . $id . '" type="file" name="' . $name . '" data-url="' . $this->url . '"' . ($this->multiple ? 'multiple' : '') . ' />';
        $formData = json_encode($this->formData);
        if (!$this->isAliyunOss) {
            $done = <<<DONE
var json = data.result;
if (callback(json)) {
    $.each(json['files'], function(index, file) {
        var uri = file['uri'];
        var url = file['url'];
        {$this->callback}(uri, url);
    });
}
DONE;
        } else {
            $done = $this->callback . '(data)';
        }
        $this->getView()->registerJs(
            <<<JS
$('#fileupload_{$id}').fileupload({
    method: '{$this->method}',
    formData: {$formData},
    done: function (e, data) { {$done} }
});
JS
        );
        $display = '';
        if (!empty($this->click_node)) {
            $display = 'none';
            $this->getView()->registerJs("$('{$this->click_node}').click(function() { $('#fileupload_{$id}').click() });");
        }

        echo
        <<<BUTTON
<div style="display:{$display};">
    <span class="btn btn-default fileinput-button">
        <span>选择文件...</span>
        {$input}
    </span>
</div>
BUTTON;
    }
}
