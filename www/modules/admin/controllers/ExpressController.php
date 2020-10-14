<?php

namespace app\modules\admin\controllers;

use app\models\Express;
use app\models\ExpressPrintTemplate;
use app\models\ManagerLog;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * 物流快递管理
 * Class ExpressController
 * @package app\modules\admin\controllers
 */
class ExpressController extends BaseController
{
    /**
     * 物流快递公司列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionList()
    {
        if (!$this->manager->can('express/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Express::find();
        $query->andFilterWhere(['id' => $this->get('search_id')]);
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andFilterWhere(['like', 'code', $this->get('search_code')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('sort DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加/修改物流快递公司
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditExpress()
    {
        if (!$this->manager->can('express/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = Express::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到物流快递公司信息。');
            }
        } else {
            $model = new Express();
            $model->status = 1;
        }
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存物流快递公司', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/express/list']),
                'txt' => '快递公司列表'
            ]));
        }

        return $this->render('edit', [
            'model' => $model
        ]);
    }

    /**
     * 设置物流快递公司状态
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionStatusExpress()
    {
        if (!$this->manager->can('express/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        /* @var $model Express */
        $model = Express::find()->where(['id' => $id])->one();
        if (empty($model)) {
            return ['message' => '没有找到商品评论信息。'];
        }
        $new_status = [
            Express::STATUS_OK => Express::STATUS_PAUSE,
            Express::STATUS_PAUSE => Express::STATUS_OK
        ][$model->status];
        $model->status = $new_status;
        ManagerLog::info($this->manager->id, '设置物流快递公司状态', $model->id . ':' . $model->status . '->' . $new_status);
        $model->save();
        return [
            'result' => 'success'
        ];
    }

    /**
     * 打印模板
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionPrintTemplate()
    {
        if (!$this->manager->can('express/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ExpressPrintTemplate::find();
        $query->andFilterWhere(['eid' => $this->get('search_eid')]);
        $template_list = $query->all();
        return $this->render('print_template', [
            'template_list' => $template_list,
        ]);
    }

    /**
     * 文件上传AJAX接口
     * @see \app\controllers\UploadControllerTrait
     */
    use UploadControllerTrait;

    /**
     * 打印模板编辑
     * @return string
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditPrintTemplate()
    {
        if (!$this->manager->can('express/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $template = ExpressPrintTemplate::findOne($id);
            if (empty($template)) {
                throw new NotFoundHttpException('没有找到打印模板。');
            }
        } else {
            if (empty($this->get('eid'))) {
                throw new BadRequestHttpException('参数错误。');
            }
            $express = Express::findOne($this->get('eid'));
            if (empty($express)) {
                throw new NotFoundHttpException('没有找到物流公司。');
            }
            $template = new ExpressPrintTemplate();
            $template->eid = $express->id;
        }
        if ($template->load($this->post()) && $template->save()) {
            ManagerLog::info($this->manager->id, '保存快递打印模板', print_r($template->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/express/print-template']),
                'txt' => '打印模板列表'
            ]));
        }
        return $this->render('print_template_edit', [
            'template' => $template,
        ]);
    }
}
