<?php

namespace app\modules\supplier\controllers;

use app\models\SupplierMessage;
use app\models\SystemMessage;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

/**
 * 系统管理
 * Class SystemController
 * @package app\modules\supplier\controllers
 */
class SystemController extends BaseController
{
    /**
     * 消息列表
     * @return string
     */
    public function actionMessage()
    {
        SupplierMessage::checkNewMessage($this->supplier->id);
        $query = SupplierMessage::find();
        $query->andWhere(['sid' => $this->supplier->id]);
        $query->andWhere(['<>', 'status', SystemMessage::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('status ASC, time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $model_list = $query->all();
        return $this->render('message', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 消息详情
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionViewMessage()
    {
        $id = $this->get('id');
        $model = SupplierMessage::findOne(['id' => $id]);
        if (empty($model) || $model->sid != $this->supplier->id) {
            throw new NotFoundHttpException('没有找到消息信息。');
        }
        $model->status = SystemMessage::STATUS_READ;
        $model->save();
        return $this->render('message_view', [
            'model' => $model
        ]);
    }
}
