<?php

namespace app\modules\merchant\controllers;

use app\models\ShopFile;
use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * 定义文件上传接口
 * Trait UploadControllerTrait
 * @package app\modules\merchant\controllers
 */
trait UploadControllerTrait
{
    /**
     * 文件上传AJAX接口
     * @var $dir string 上传保存目录，如da system article等
     * @var $file_field string 指定文件字段名称
     *       ，如果没有设置，首先获取名称为files的列表，如果列表为空，再尝试获取file字段
     * @return array(fid, url) 如果正常返回文件编号和url
     */
    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $dir = Yii::$app->request->get('dir', '.');
        $file_field = Yii::$app->request->get('file_field', 'files');
        $file_list = UploadedFile::getInstancesByName($file_field);
        if (empty($file_list)) { // 尝试读取单个文件
            $_file = UploadedFile::getInstanceByName('file');
            if (!empty($_file)) {
                $file_list[] = $_file;
            }
        }
        if (empty($file_list)) {
            return [
                'message' => '没有找到上传文件。'
            ];
        }
        if (!is_array($file_list)) {
            $file_list[] = $file_list;
        }
        try {
            $result = [];
            foreach ($file_list as $file) {
                $uri = $this->saveUpload($file, $dir);
                $shop_file = new ShopFile();
                $shop_file->sid = $this->shop->id;
                $shop_file->type = ShopFile::TYPE_IMAGE;
                $shop_file->url = $uri;
                $shop_file->status = ShopFile::STATUS_OK;
                $shop_file->create_time = time();
                $shop_file->save();
                $result[] = [
                    'url' => $uri,
                ];
            }
            return [
                'result' => 'success',
                'base' => Yii::$app->params['upload_url'],
                'files' => $result
            ];
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 保存上传的文件
     * @param UploadedFile $file 上传文件
     * @param string $dir 保存目录
     * @return string 相对于Yii::$app->params['upload_path']的保存地址
     * @throws Exception
     */
    private function saveUpload($file, $dir = '.')
    {
        if (empty($file)) {
            throw new Exception('没有找到上传文件。');
        }
        $relative_path = $dir . '/' . date('y/m/');
        $real_path = Yii::$app->params['upload_path'] . $relative_path;
        if (!file_exists($real_path)
            && !FileHelper::createDirectory($real_path)) {
            throw new Exception('无法创建目录。');
        }
        $file_name = substr(uniqid(md5(rand()), true), 0, 10);
        $file_name .= '-' . Inflector::slug($file->baseName);
        $file_name .= '.' . $file->extension;
        $uri = $relative_path . $file_name;
        if (!$file->saveAs($real_path . $file_name)) {
            Yii::error('无法保存上传文件：' . print_r($file->error, true));
            throw new Exception('无法保存文件。');
        }
        return $uri;
    }

    /**
     * 删除上传文件AJAX接口
     * @throws BadRequestHttpException
     * @return array
     */
    public function actionDeleteUpload()
    {
        $url = Yii::$app->request->get('url');
        if (empty($url)) {
            throw new BadRequestHttpException('参数错误。');
        }
        $uri = preg_replace('/^' . str_replace('/', '\/', Yii::$app->params['upload_url']) . '/', '', $url);
        $path = Yii::$app->params['upload_path'];
        if (!file_exists($path . $uri)) {
            return ['code' => -1, 'message' => '没有找到文件。'];
        }
        $r = @unlink($path . $uri);
        if ($r) {
            return ['result' => 'success'];
        } else {
            return ['message' => '无法删除文件。'];
        }
    }
}
