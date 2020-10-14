<?php

namespace app\modules\admin\controllers;

use app\models\NewHand;
use app\models\ManagerLog;
use kucha\ueditor\UEditorAction;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\models\AliyunOssApi;
use app\models\GoodsSource;
use app\models\GoodsTraceVideo;
use app\models\Util;
use yii\base\Exception;

/**
 * 商学院管理
 * Class HandController
 * @package app\modules\admin\controllers
 */
class HandController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return array_merge(parent::actions(), [
            'ue-upload' => [
                'class' => UEditorAction::className(),
                'config' => [
                    'imagePathFormat' => '/uploads/hand/{yy}/{mm}/{time}-{rand:6}', //上传保存路径
                    'imageRoot' => Yii::getAlias('@webroot'),
                    'scrawlPathFormat' => '/uploads/hand/{yy}/{mm}/{time}-{rand:6}',
                    'snapscreenPathFormat' => '/uploads/hand/{yy}/{mm}/{time}-{rand:6}',
                    'catcherPathFormat' => '/uploads/hand/{yy}/{mm}/{time}-{rand:6}',
                    'videoPathFormat' => '/uploads/hand/{yy}/{mm}/{time}-{rand:6}',
                    'filePathFormat' => '/uploads/hand/{yy}/{mm}/{time}-{rand:6}',
                    'imageManagerListPath' => '/uploads/hand',
                    'fileManagerListPath' => '/uploads/hand',
                ],
            ],
            'uploadsouce'=>[
                'class' => 'app\widgets\batchupload\UploadAction'
            ],
        ]);
    }

    /**
     * 新手入门列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionList()
    {
        if (!$this->manager->can('hand/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = NewHand::find()->where(['<>', 'status', NewHand::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 添加/编辑 新手入门
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionEdit()
    {
        if (!$this->manager->can('hand/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = NewHand::findOne($id);
        } else {
            $model = new NewHand();
            $model->status = NewHand::STATUS_OK;
            $model->create_time = time();
        }
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存新手入门', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/hand/list']),
                'txt' => '新手入门列表'
            ]));
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * 删除新手入门AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDelete()
    {
        if (!$this->manager->can('hand/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = NewHand::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到新手入门信息。'];
        }
        $model->status = NewHand::STATUS_DEL;
        $model->save();
        ManagerLog::info($this->manager->id, '删除新手入门', print_r($model->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 设置新手入门状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionStatus()
    {
        if (!$this->manager->can('hand/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        /* @var $model NewHand */
        $model = NewHand::find()->where(['id' => $id])->andWhere(['<>', 'status', NewHand::STATUS_DEL])->one();
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到新手入门信息。');
        }
        $new_status = [
            NewHand::STATUS_HIDE => NewHand::STATUS_OK,
            NewHand::STATUS_OK => NewHand::STATUS_HIDE
        ][$model->status];
        ManagerLog::info($this->manager->id, '设置新手入门状态', $model->id . ':' . $model->status . '->' . $new_status);
        $model->status = $new_status;
        $model->save();
        return [
            'result' => 'success'
        ];
    }

    /**
     * 视频列表
     * @return array | string
     * @throws ForbiddenHttpException
     */
    public function actionTraceVideo()
    {
        if (!$this->manager->can('hand/trace-video')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GoodsTraceVideo::find();
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andWhere(['<>', 'status', GoodsTraceVideo::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->andWhere(['sid'=>null]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $videoList = $query->all();
        return $this->render('trace_video', [
            'videoList' => $videoList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加、修改视频
     * @return array|string
     * @throws Exception
     */
    public function actionTraceVideoEdit()
    {
        if (!$this->manager->can('hand/trace-video')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $traceVideo = GoodsTraceVideo::findOne(['id' => $id]);
            if (empty($traceVideo)) {
                throw new NotFoundHttpException('没有找到视频。');
            }
        } else {
            $traceVideo = new GoodsTraceVideo();
            $traceVideo->status = GoodsTraceVideo::STATUS_OK;
            $traceVideo->create_time = time();
        }
        if ($traceVideo->load($this->post()) && $traceVideo->save()) {
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/hand/trace-video']),
                'txt' => '视频列表'
            ]));
        }
        $ossName = 'ytb_1_' . Util::randomStr(8);
        $ossCoverName = $ossName . '.jpg';
        $ossVideoName = $ossName . '.mp4';
        return $this->render('trace_video_edit', [
            'traceVideo' => $traceVideo,
            'ossCoverName' => $ossCoverName,
            'ossVideoName' => $ossVideoName,
            'ossPolicy' => (new AliyunOssApi())->ossPolicy('goods_trace'),
        ]);
    }

    /**
     * 删除视频AJAX接口
     * @return array
     */
    public function actionDeleteVideo()
    {
        $id = $this->get('id');
        $model = GoodsTraceVideo::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到视频信息。'];
        }
        $model->status = GoodsTraceVideo::STATUS_DEL;
        ManagerLog::info($this->manager->id, '删除视频', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

    /**
     * 素材图片管理
     * @return array|string
     * @throws ForbiddenHttpException
     */
    public function actionSource()
    {
        if (!$this->manager->can('hand/source')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GoodsSource::find();
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andFilterWhere(['<>', 'status', GoodsSource::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->andWhere(['<>', 'status', GoodsSource::STATUS_DEL]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $sourceList = $query->all();

        return $this->render('source', [
            'sourceList' => $sourceList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加、修改素材图片
     * @return array|string
     * @throws Exception
     */
    public function actionSourceEdit()
    {
        if (!$this->manager->can('hand/source')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $goodSource = GoodsSource::findOne(['id' => $id]);
            if (empty( $goodSource )) {
                throw new NotFoundHttpException('没有找到视频。');
            }
        } else {
            $goodSource = new GoodsSource();
            $goodSource ->create_time = time();
        }
        if($this->post())
        {
            if($goodSource->load($this->post()) && is_array($goodSource->img_list))
            {
                $goodSource->img_list=json_encode($goodSource->img_list);
            }
            else
            {
                $goodSource->img_list='';
            }

            if ($goodSource->save()) {
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/hand/source']),
                    'txt' => '素材列表'
                ]));
            }
        }

        return $this->render('source_edit', [
            'goodSource' =>  $goodSource ,
        ]);
    }

    /**
     * 删除图文素材AJAX接口
     * @return array
     */
    public function actionDeleteSource()
    {
        $id = $this->get('id');
        $model = GoodsSource::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到图文素材信息。'];
        }
        $model->status = GoodsSource::STATUS_DEL;
        ManagerLog::info($this->manager->id, '删除图文素材', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }
}
