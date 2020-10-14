<?php

namespace app\modules\admin\controllers;

use app\models\Notice;
use app\models\ManagerLog;
use kucha\ueditor\UEditorAction;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * 公告资讯管理
 * Class NoticeController
 * @package app\modules\admin\controllers
 */
class NoticeController extends BaseController
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
                    'imagePathFormat' => '/uploads/notice/{yy}/{mm}/{time}-{rand:6}', //上传保存路径
                    'imageRoot' => Yii::getAlias('@webroot'),
                    'scrawlPathFormat' => '/uploads/notice/{yy}/{mm}/{time}-{rand:6}',
                    'snapscreenPathFormat' => '/uploads/notice/{yy}/{mm}/{time}-{rand:6}',
                    'catcherPathFormat' => '/uploads/notice/{yy}/{mm}/{time}-{rand:6}',
                    'videoPathFormat' => '/uploads/notice/{yy}/{mm}/{time}-{rand:6}',
                    'filePathFormat' => '/uploads/notice/{yy}/{mm}/{time}-{rand:6}',
                    'imageManagerListPath' => '/uploads/notice',
                    'fileManagerListPath' => '/uploads/notice',
                ],
            ],
        ]);
    }

    /**
     * 公告资讯列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionList()
    {
        if (!$this->manager->can('notice/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Notice::find()->where(['<>', 'status', Notice::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 添加/编辑 公告资讯
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionEdit()
    {
        if (!$this->manager->can('notice/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = Notice::findOne($id);
        } else {
            $model = new Notice();
            $model->status = Notice::STATUS_SHOW;
            $model->time = time();
        }
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存公告资讯', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/notice/list']),
                'txt' => '公告资讯列表'
            ]));
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * 删除公告资讯AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDelete()
    {
        if (!$this->manager->can('notice/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Notice::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到公告资讯信息。'];
        }
        $model->status = Notice::STATUS_DEL;
        $model->save();
        ManagerLog::info($this->manager->id, '删除公告资讯', print_r($model->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 设置公告资讯状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionStatus()
    {
        if (!$this->manager->can('notice/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        /* @var $model Notice */
        $model = Notice::find()->where(['id' => $id])->andWhere(['<>', 'status', Notice::STATUS_DEL])->one();
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到公告资讯信息。');
        }
        $new_status = [
            Notice::STATUS_HIDE => Notice::STATUS_SHOW,
            Notice::STATUS_SHOW => Notice::STATUS_HIDE
        ][$model->status];
        ManagerLog::info($this->manager->id, '设置公告资讯状态', $model->id . ':' . $model->status . '->' . $new_status);
        $model->status = $new_status;
        $model->save();
        return [
            'result' => 'success'
        ];
    }
}
