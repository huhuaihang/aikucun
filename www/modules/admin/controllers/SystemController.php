<?php

namespace app\modules\admin\controllers;

use app\models\ApiClient;
use app\models\ManagerLog;
use app\models\System;
use app\models\SystemError;
use app\models\SystemMessage;
use app\models\SystemVersion;
use app\models\Task;
use app\models\Util;
use app\models\ViolationType;
use kucha\ueditor\UEditorAction;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;

/**
 * 系统管理
 * Class SystemController
 * @package app\modules\admin\controllers
 */
class SystemController extends BaseController
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
                    'imagePathFormat' => '/uploads/system/{yy}/{mm}/{time}-{rand:6}', //上传保存路径
                    'imageRoot' => Yii::getAlias('@webroot'),
                    'scrawlPathFormat' => '/uploads/system/{yy}/{mm}/{time}-{rand:6}',
                    'snapscreenPathFormat' => '/uploads/system/{yy}/{mm}/{time}-{rand:6}',
                    'catcherPathFormat' => '/uploads/system/{yy}/{mm}/{time}-{rand:6}',
                    'videoPathFormat' => '/uploads/system/{yy}/{mm}/{time}-{rand:6}',
                    'filePathFormat' => '/uploads/system/{yy}/{mm}/{time}-{rand:6}',
                    'imageManagerListPath' => '/uploads/system',
                    'fileManagerListPath' => '/uploads/system',
                ],
            ],
        ]);
    }

    /**
     * 系统设置
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     * @return string
     */
    public function actionConfig()
    {
        if (!$this->manager->can('system/config')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        if ($this->isPost()) {
            $category = $this->get('category');
            if (empty($category)) {
                throw new BadRequestHttpException('参数错误。');
            }
            $trans = Yii::$app->db->beginTransaction();
            try {
                $config_list = System::find()->andWhere(['category' => $category])->all();
                $post = $this->post();
                foreach ($config_list as $config) {
                    /* @var $config System */
                    $type = json_decode($config->type, true);
                    if ($type['type'] == 'file') {
                        $_file = UploadedFile::getInstanceByName($config->name);
                        if (!empty($_file)) {
                            $dir = 'system';
                            $relative_path = $dir . '/' . date('y/m/');
                            $real_path = Yii::$app->params['upload_path'] . $relative_path;
                            if (!file_exists($real_path)
                                && !FileHelper::createDirectory($real_path)) {
                                throw new Exception('无法创建目录。');
                            }
                            $file_name = substr(uniqid(md5(rand()), true), 0, 10);
                            $file_name .= '-' . Inflector::slug($_file->baseName);
                            $file_name .= '.' . $_file->extension;
                            $uri = $relative_path . $file_name;
                            if (!$_file->saveAs($real_path . $file_name)) {
                                Yii::error('无法保存上传文件：' . print_r($_file->error, true));
                                throw new Exception('无法保存上传文件。');
                            }
                            $config->value = $uri;
                        }
                    } else {
                        if (isset($type['disabled']) && $type['disabled']) {
                            continue;
                        }
                        $config->value = isset($post[$config->name]) ? $post[$config->name] : '';
                    }
                    if ($config->save()) {
                        ManagerLog::info($this->manager->id, '保存系统设置', print_r($config->attributes, true));
                    }
                }
                Yii::$app->session->addFlash('success', '数据已保存。');
                $trans->commit();
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                Yii::$app->session->addFlash('error', $e->getMessage());
            }
        }
        return $this->render('config', [
            'show_category' => $this->get('category', '系统设置')
        ]);
    }

    /**
     * 版本管理
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionVersion()
    {
        if (!$this->manager->can('system/version')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $version_list = SystemVersion::find()->orderBy('id DESC')->all();
        return $this->render('version', [
            'version_list' => $version_list,
        ]);
    }

    /**
     * 版本详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionVersionView()
    {
        if (!$this->manager->can('system/version')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $version = SystemVersion::findOne($id);
        if (empty($version)) {
            throw new NotFoundHttpException('没有找到版本信息。');
        }
        return $this->render('version_view', [
            'version' => $version,
        ]);
    }

    /**
     * 添加/修改版本
     * @return string|\yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionVersionEdit()
    {
        if (!$this->manager->can('system/version')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $version = SystemVersion::findOne($id);
            if (empty($version)) {
                throw new NotFoundHttpException('没有找到版本信息。');
            }
        } else {
            $version = new SystemVersion();
            $version->aes_key = base64_encode(openssl_random_pseudo_bytes(32));
            $version->aes_iv = base64_encode(openssl_random_pseudo_bytes(16));
            $version->create_time = time();
        }
        if ($version->load($this->post()) && $version->save()) {
            Yii::$app->session->addFlash('success', '数据已保存。');
            return $this->redirect(['/admin/system/version']);
        }
        return $this->render('version_edit', [
            'version' => $version,
        ]);
    }

    /**
     * 接口客户端
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionApiClient()
    {
        if (!$this->manager->can('system/api-client')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $client_list = ApiClient::find()->all();
        return $this->render('api_client', [
            'client_list' => $client_list,
        ]);
    }

    /**
     * 接口客户端详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionApiClientView()
    {
        if (!$this->manager->can('system/api-client')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $client = ApiClient::findOne($id);
        if (empty($client)) {
            throw new NotFoundHttpException('没有找到接口客户端信息。');
        }
        return $this->render('api_client_view', [
            'client' => $client,
        ]);
    }

    /**
     * 添加/修改接口客户端
     * @return string|\yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionApiClientEdit()
    {
        if (!$this->manager->can('system/api-client')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $client = ApiClient::findOne($id);
            if (empty($client)) {
                throw new NotFoundHttpException('没有找到接口客户端信息。');
            }
        } else {
            $client = new ApiClient();
            $client->app_secret = Util::randomStr(32, 7);
            $client->create_time = time();
        }
        if ($client->load($this->post()) && $client->save()) {
            Yii::$app->session->addFlash('success', '数据已保存。');
            return $this->redirect(['/admin/system/api-client']);
        }
        return $this->render('api_client_edit', [
            'client' => $client,
        ]);
    }

    /**
     * 定时任务
     * @throws ForbiddenHttpException
     * @return string
     */
    public function actionTask()
    {
        if (!$this->manager->can('system/task')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Task::find();
        $pagination = new Pagination(['totalCount'=>$query->count()]);
        $model_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('task', [
            'model_list'=>$model_list,
            'pagination'=>$pagination
        ]);
    }

    /**
     * 定时任务详情
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return string
     */
    public function actionTaskView()
    {
        if (!$this->manager->can('system/task')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $task = Task::findOne($id);
        if (empty($task)) {
            throw new NotFoundHttpException('没有找到定时任务信息。');
        }
        return $this->render('task_view', [
            'task'=>$task
        ]);
    }

    /**
     * 清除任务下次执行时间AJAX接口
     * 任务将会在一分钟内开始执行
     * @return array
     */
    public function actionClearTaskNext()
    {
        if (!$this->manager->can('system/task')) {
            return ['message' => '没有权限。'];
        }
        $id = $this->get('id');
        $task = Task::findOne($id);
        if (empty($task)) {
            return ['message' => '没有找到定时任务信息。'];
        }
        $task->next = 0;
        $task->save(false);
        ManagerLog::info($this->manager->id, '清除任务下次执行时间', print_r($task->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 重置定时任务状态AJAX接口
     * @return array
     */
    public function actionResetTaskStatus()
    {
        if (!$this->manager->can('system/task')) {
            return ['message' => '没有权限。'];
        }
        $id = $this->get('id');
        $task = Task::findOne($id);
        if (empty($task)) {
            return ['message' => '没有找到定时任务信息。'];
        }
        $task->status = Task::STATUS_WAITING;
        $task->save();
        ManagerLog::info($this->manager->id, '重置任务状态', print_r($task->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 删除定时任务AJAX接口
     * @throws ForbiddenHttpException
     * @return array
     */
    public function actionDeleteTask()
    {
        if (!$this->manager->can('system/task')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Task::findOne($id);
        if (empty($model)) {
            return [
                'message'=>'没有找到任务信息。'
            ];
        }
        try {
            $model->delete();
        } catch (\Throwable $e) {
        }
        ManagerLog::info($this->manager->id, '删除用户定时任务', print_r($model->attributes, true));
        return [
            'result'=>'success'
        ];
    }

    /**
     * 系统消息列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionMessage()
    {
        if (!$this->manager->can('system/message')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = SystemMessage::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('message', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加系统消息
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionEditMessage()
    {
        if (!$this->manager->can('system/message')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $model = new SystemMessage();
        $model->time = time();
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存系统消息', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/system/message']),
                'txt' => '消息列表'
            ]));
        }
        return $this->render('message_edit', [
            'model' => $model,
        ]);
    }

    /**
     * 违规类型管理
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionViolation()
    {
        if (!$this->manager->can('system/violation')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ViolationType::find();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('violation', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加/编辑 违规类型
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditViolation()
    {
        if (!$this->manager->can('system/violation')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = ViolationType::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到违规类型信息。');
            }
        } else {
            $model = new ViolationType();
        }
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存违规类型', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/system/violation']),
                'txt' => '违规类型列表'
            ]));
        }
        return $this->render('violation_edit', [
            'model' => $model,
        ]);
    }

    /**
     * 错误日志
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionError()
    {
        if (!$this->manager->can('system/error')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = SystemError::find();
        $query->andWhere(['status' => [SystemError::STATUS_WAIT, SystemError::STATUS_OLD]]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $error_list = $query->orderBy('status ASC, id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('error', [
            'error_list' => $error_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 错误详情
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionErrorView()
    {
        if (!$this->manager->can('system/error')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $error = SystemError::findOne($id);
        if (empty($error)) {
            throw new NotFoundHttpException('没有找到错误信息。');
        }
        $error->status = SystemError::STATUS_OLD;
        $error->save();
        return $this->render('error_view', [
            'error' => $error,
        ]);
    }

    /**
     * 删除系统错误
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteError()
    {
        if (!$this->manager->can('system/error')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $ids = $this->get('ids');
        SystemError::updateAll(['status' => SystemError::STATUS_DEL], ['id' => $ids]);
        return ['result' => 'success'];
    }
}
