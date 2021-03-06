<?php
/**
 * @author Aaron Zhanglong <815818648@qq.com>
 * 多图上传组件
 * @Date: 2017-07-14
 */
namespace app\widgets\batchupload;
use app\widgets\batchupload\assets\FileUploadAsset;
use Yii;
use yii\widgets\InputWidget;
use yii\helpers\Html;

class FileUpload extends InputWidget{
    public $config = [];
    public $value = '';

    public function run($config = array()){
        $this->registerScripts();
        $_config = require(__DIR__ . '/config.php');

        if ($this->hasModel()) {
            $inputName = Html::getInputName($this->model, $this->attribute);
            $value = Html::getAttributeValue($this->model, $this->attribute);
            $id=$this->model->id;
        }else{
            $inputName = 'pics';
            $value = $this->value;
            $id='';
        }

        $vars = [
            'config' => $_config,
            'inputName' => $inputName,
            'value' => $value,
            'id'=>$id
        ];
        return $this->render('index.php', $vars);
    }

    public function registerScripts(){
        FileUploadAsset::register($this->view);
    }
}