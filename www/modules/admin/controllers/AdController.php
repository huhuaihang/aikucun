<?php

namespace app\modules\admin\controllers;

use app\models\Ad;
use app\models\AdLocation;
use app\models\AliyunOssApi;
use app\models\GoodsCategory;
use app\models\ManagerLog;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * 广告管理
 * Class AdController
 * @package app\modules\admin\controllers
 */
class AdController extends BaseController
{
    /**
     * 文件上传AJAX接口
     * @see \app\controllers\UploadControllerTrait
     */
    use UploadControllerTrait;

    /**
     * 广告列表
     * @throws ForbiddenHttpException
     * @return string
     */
    public function actionList()
    {
        if (!$this->manager->can('ad/list')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $query = Ad::find()->where(['status' => [Ad::STATUS_ACTIVE, Ad::STATUS_STOPED]]);
        $query->andFilterWhere(['lid' => $this->get('search_lid')]);
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 添加/修改广告
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return string
     * @throws \yii\base\ErrorException
     */
    public function actionEdit()
    {
        if (!$this->manager->can('ad/edit')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $id = $this->get('id');
        if ($id > 0) {
            $model = Ad::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到广告信息。');
            }
        } else {
            $model = new Ad();
            $model->lid = $this->get('lid');
        }
        if ($model->load($this->post()) && $model->save()) {
//            $upload = new AliyunOssApi();
//            $upload->ossPolicy('da');
//            $re = $upload->uploadFile($model->img, Yii::$app->params['upload_path'] . $model->img);
//            // 图片自动缩放
//            if ($model->location->type == AdLocation::TYPE_IMAGE) {
//                $location = $model->location;
//                $width = $location->width;
//                $height = $location->height;
//                if ($width > 0 && $height > 0) {
//                    /* @var $image_driver \yii\image\ImageDriver */
//                    $image_driver = Yii::$app->get('image');
//                    /* @var $image \yii\image\drivers\Image */
//                    $image = $image_driver->load(Yii::$app->params['upload_path'] . $model->img);
//                    $image->resize($width, $height)->save();
//
//                }
//
//                if ($upload) {
//                    $model->img = $re;
//                    $model->save();
//                }
//            }
            ManagerLog::info($this->manager->id, '保存广告', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/ad/list']),
                'txt' => '广告列表'
            ]));
        }
        return $this->render('edit', [
            'model' => $model
        ]);
    }

    /**
     * 删除广告AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionDelete()
    {
        if (!$this->manager->can('ad/edit')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Ad::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到广告信息。');
        }
        $model->status = Ad::STATUS_DELETED;
        $model->save();
        ManagerLog::info($this->manager->id, '删除广告', print_r($model->attributes, true));
        return [
            'result' => 'success'
        ];
    }

    /**
     * 设置广告状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionStatus()
    {
        if (!$this->manager->can('ad/edit')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Ad::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到广告信息。');
        }
        $model->status = [Ad::STATUS_ACTIVE => Ad::STATUS_STOPED, Ad::STATUS_STOPED => Ad::STATUS_ACTIVE][$model->status];
        if ($model->save()) {
            return [
                'result' => 'success'
            ];
        }
        return [
            'result' => 'failure',
            'message' => '无法保存广告信息。',
            'errors' => $model->errors
        ];
    }

    /**
     * 广告位置
     * @throws ForbiddenHttpException
     * @return string
     */
    public function actionLocation()
    {
        if (!$this->manager->can('ad/location')) {
            throw new ForbiddenHttpException('没有权限');
        }
        // * 一键添加/更新商品分类广告位置
        if($this->get('type')==1)
        {
            $model = new AdLocation();
            $max_id=$model->find()->select('id')->orderBy('id desc')->one();
            $id=$max_id->id;
            //获取一级商品分类
            $goodscat= GoodsCategory::find()->where(['status' => GoodsCategory::STATUS_SHOW,'pid'=>null]);
            foreach ($goodscat->each() as $item)
            {
                $car_id=$model->find()->select('remark')->where(['remark'=>$item->id])->one();
                if(!empty($car_id))
                {
                    if(AdLocation::updateAll(
                        ['name' =>'[' . $item->name . ']轮播广告'],
                        ['remark' => $item->id]
                    ))
                    {
                        continue;//如果已经更新,则跳过此次循环,到下一次
                    }
                }else{
                    $id += 1;
                    $data[] = [
                        'id' => $id,
                        'type' => AdLocation::TYPE_IMAGE,
                        'name' => '[' . $item->name . ']轮播广告',
                        'max_count' => 4,
                        'width' => '750',
                        'height' => '350',
                        'remark' => $item->id,
                    ];
                }
            }
            if(!empty($data))
            {
                Yii::$app->db->createCommand()->batchInsert(AdLocation::tableName(), ['id','type','name','max_count','width','height','remark'], $data)->execute();
            }

            ManagerLog::info($this->manager->id, '保存商品分类广告位', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已更新。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/ad/location']),
                'txt' => '广告位置'
            ]));

        }

        $query = AdLocation::find();
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('location', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }


    /**
     * 添加/修改广告位置
     * @throws ForbiddenHttpException
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionEditLocation()
    {
        if (!$this->manager->can('ad/location')) {
            throw new ForbiddenHttpException('没有权限');
        }
        $id = $this->get('id');
        if ($id > 0) {
            $model = AdLocation::findOne($id);
        } else {
            $model = new AdLocation();
        }
        if ($model->load($this->post()) && $model->validate() && $model->save()) {
            // 生成模板文件
            if (!empty($model->code)) {
                $path = Yii::getAlias('@runtime/ad');
                if (!file_exists($path)
                    && !FileHelper::createDirectory($path)) {
                    Yii::$app->session->addFlash('error', '无法创建广告模板目录：' . $path);
                }
                $file = $path . '/' . $model->id . '.tpl';
                $file_saved = file_put_contents($file, $model->code);
                if (!$file_saved) {
                    Yii::$app->session->addFlash('error', '无法保存广告模板文件：' . $file);
                }
            }
            ManagerLog::info($this->manager->id, '保存广告位', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/ad/location']),
                'txt' => '广告位置'
            ]));
        }
        return $this->render('location_edit', [
            'model' => $model
        ]);
    }

    /**
     * 清除Smarty缓存
     * @return array
     */
    public function actionClearSmartyCache()
    {
        if (!$this->manager->can('ad/location')) {
            return ['message' => '没有权限。'];
        }
        $id = $this->get('id');
        $path = Yii::getAlias('@runtime/ad');
        $r = unlink($path . '/' . $id . '.tpl');
        if ($r) {
            return ['result' => 'success'];
        }
        return ['message' => '删除缓存失败。'];
    }
}
