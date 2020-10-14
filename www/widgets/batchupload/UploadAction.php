<?php
namespace app\widgets\batchupload;
/**
 * @author Aaron Zhanglong <815818648@qq.com>
 * 多图上传组件
 * @Date: 2017-07-14
 * @usage :
 * 模板页面： <?=$form->field($model, 'pics')->widget('common\widgets\batch_upload\FileUpload')?>
 *
 * 上传图片控制器脚本：
 *
 * public function actions() {
        return [
            'upload_more'=>[
                'class' => 'common\widgets\batch_upload\UploadAction'
            ]
        ];
    }
 */

use app\models\AliyunOssApi;
use app\models\GoodsSource;
use app\models\System;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use app\widgets\batchupload\Uploader;
use yii\helpers\Console;
use app\models\Util;
class UploadAction extends Action
{
    /**
     * 配置项
     */
    public $config = [];

    public function init() {
        //close csrf
        Yii::$app->request->enableCsrfValidation = false;
        //默认设置
        $_config = require(__DIR__ . '/config.php');
        $this->config = ArrayHelper::merge($_config, $this->config);
        parent::init();
    }

    public function run() {
        $action = Yii::$app->request->get('action');
        $id=Yii::$app->request->get('id');
        if($id)
        {
        $source= GoodsSource::findOne($id);

        }
        if($action == 'delete'){
            $pic_url=Yii::$app->request->get('pic');

            $pic=str_replace( System::getConfig('aliyun_oss_host'), '', $pic_url);
            $pic = $this->config['uploadFilePath'] .'/uploads'. $pic;

                if($this->config['trueDelete']){
                    @unlink($pic);
                    $oss=new AliyunOssApi();
                    $oss->deleteFile($pic_url);
                    if($id) {
                        $img_list = json_decode($source->img_list);
                        //$k = array_search($pic_url, $img_list);
                        foreach ($img_list as $k=>$v  )
                        {
                            if($v==$pic_url)
                            {
                                unset($img_list[$k]);
                            }
                        }
                        $source->img_list=json_encode(array_values($img_list));
                        $source->save(false);
                    }


                    return  true;
                }

        }else{
            $result = $this->ActUpload();
            echo $result;exit;
        }
    }



    /**
     * 上传
     * @return string
     */
    protected function ActUpload() {
        //上传类型
        $upload_type = $this->config['uploadType'];
        //上传路径
        $this->config['uploadFilePath'] = isset($this->config['uploadFilePath']) ? $this->config['uploadFilePath'] : '';
        //文件数组下标
        $fieldName = $this->config['fieldName'];
        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($fieldName, $this->config, $upload_type);
        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
        */
        /* 返回数据 */
        return json_encode($up->getFileInfo());
    }
}