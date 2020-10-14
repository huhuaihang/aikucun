<?php

namespace app\modules\admin\controllers;

use app\models\Faq;
use app\models\FaqCategory;
use app\models\ManagerLog;
use kucha\ueditor\UEditorAction;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;


/**
 * 常见问题管理
 * Class UserController
 * @package app\modules\admin\controllers
 */
class FaqController extends BaseController
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
                    'imagePathFormat' => '/uploads/faq/{yy}/{mm}/{time}-{rand:6}', //上传保存路径
                    'imageRoot' => Yii::getAlias('@webroot'),
                    'scrawlPathFormat' => '/uploads/faq/{yy}/{mm}/{time}-{rand:6}',
                    'snapscreenPathFormat' => '/uploads/faq/{yy}/{mm}/{time}-{rand:6}',
                    'catcherPathFormat' => '/uploads/faq/{yy}/{mm}/{time}-{rand:6}',
                    'videoPathFormat' => '/uploads/faq/{yy}/{mm}/{time}-{rand:6}',
                    'filePathFormat' => '/uploads/faq/{yy}/{mm}/{time}-{rand:6}',
                    'imageManagerListPath' => '/uploads/faq',
                    'fileManagerListPath' => '/uploads/faq',
                ],
            ],
        ]);
    }

    /**
     * 常见问题列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionList()
    {
        if (!$this->manager->can('faq/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Faq::find()->where(['<>', 'status', Faq::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 添加/编辑 常见问题
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionEdit()
    {
        if (!$this->manager->can('faq/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $cat_list = '';
        if (!empty($id)) {
            $model = Faq::findOne($id);
            $cat_list = FaqCategory::find_category_all($model->cid);
            $cat_list = implode(',', array_column($cat_list, 'id'));
        } else {
            $model = new Faq();
            $model->create_time = time();
        }
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存常见问题', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/faq/list']),
                'txt' => '常见问题列表'
            ]));
        }
        return $this->render('edit', [
            'model' => $model,
            'cat_list' => $cat_list,
        ]);
    }

    /**
     * 删除常见问题AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDelete()
    {
        if (!$this->manager->can('faq/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Faq::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到常见问题信息。'];
        }
        $model->status = Faq::STATUS_DEL;
        $model->save();
        ManagerLog::info($this->manager->id, '删除常见问题', print_r($model->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 获取常见问题分类
     * @return array
     */
    public function actionGetFaqCat()
    {
        $pid = $this->get('pid');
        $query = FaqCategory::find()
            ->asArray()
            ->where(['<>', 'status', FaqCategory::STATUS_DEL])
            ->select(['id', 'name']);
        if (!empty($pid)) {
            $query->andWhere(['pid' => $pid]);
        } else {
            $query->andWhere(['pid' => null]);
        }
        $list = $query->all();
        return [
            'result' => 'success',
            'list' => $list
        ];
    }

    /**
     * 设置常见问题状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionStatus()
    {
        if (!$this->manager->can('faq/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        /* @var $model Faq */
        $model = Faq::find()->where(['id' => $id])->andWhere(['<>', 'status', Faq::STATUS_DEL])->one();
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到常见问题信息。');
        }
        $new_status = [
            Faq::STATUS_HIDE => Faq::STATUS_SHOW,
            Faq::STATUS_SHOW => Faq::STATUS_HIDE
        ][$model->status];
        ManagerLog::info($this->manager->id, '设置常见问题状态', $model->id . ':' . $model->status . '->' . $new_status);
        $model->status = $new_status;
        $model->save();
        return [
            'result' => 'success'
        ];
    }

    /**
     * 常见问题分类列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionFaqCategory()
    {
        if (!$this->manager->can('faq/category')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = FaqCategory::find()->where(['<>', 'status', FaqCategory::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('category', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 设置常见问题分类状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionToggleFaqCategoryStatus()
    {
        if (!$this->manager->can('faq/category')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        /* @var $model FaqCategory */
        $model = FaqCategory::find()->where(['id' => $id])->andWhere(['<>', 'status', FaqCategory::STATUS_DEL])->one();
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到常见问题分类信息。');
        }
        $new_status = [
            FaqCategory::STATUS_HIDE => FaqCategory::STATUS_SHOW,
            FaqCategory::STATUS_SHOW => FaqCategory::STATUS_HIDE
        ][$model->status];
        ManagerLog::info($this->manager->id, '设置常见问题分类状态', $model->id . ':' . $model->status . '->' . $new_status);
        $model->status = $new_status;
        $model->save();
        return ['result' => 'success'];
    }

    /**
     * 删除常见问题分类AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteFaqCategory()
    {
        if (!$this->manager->can('faq/category')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = FaqCategory::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到常见问题分类信息。'];
        }
        $model->status = FaqCategory::STATUS_DEL;
        $model->save();
        ManagerLog::info($this->manager->id, '删除常见问题分类', print_r($model->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 添加/编辑 常见问题分类
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionFaqCategoryEdit()
    {
        if (!$this->manager->can('faq/category')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $cat_list = '';
        if (!empty($id)) {
            $model = FaqCategory::findOne($id);
            $cat_list = FaqCategory::find_category_all($id);
            $cat_list = implode(',', array_column($cat_list, 'pid'));
        } else {
            $model = new FaqCategory();
        }
        if ($model->load($this->post())) {
            $model->pid = empty($model->pid) ? null : $model->pid;
            if ($model->save()) {
                ManagerLog::info($this->manager->id, '保存常见问题分类', print_r($model->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/faq/faq-category']),
                    'txt' => '常见问题分类列表'
                ]));
            }
        }
        return $this->render('category_edit', [
            'model' => $model,
            'cat_list' => $cat_list,
        ]);
    }

    /**
     * 获取上级分类和上级同级分类AJAX接口
     * @return array
     */
    public function actionFaqCategoryParent()
    {
        $id = $this->get('id');
        $first = FaqCategory::find()->where(['pid' => null])->asArray()->all();
        $ids = array_column($first, 'id');
        if (!empty($id)) {
            $model = FaqCategory::findOne($id);
            if (!empty($model->pid)) {
                $parent = FaqCategory::findOne($model->pid);
                if (!empty($parent->pid)) {
                    $cat_list = FaqCategory::find()
                        ->where(['pid' => $ids])
                        ->select('id, name')
                        ->asArray()
                        ->all();
                } else {
                    $cat_list = $first;
                }
                return ['result' => 'success', 'cat_list' => $cat_list];
            } else {
                return ['result' => 'success', 'cat_list' => ''];
            }
        } else {
            return ['message' => '请选择分类'];
        }
    }
}
