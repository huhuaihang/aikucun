<?php

namespace app\modules\agent\controllers;

use app\models\AgentMessage;
use app\models\SystemMessage;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

/**
 * 系统管理
 * Class SystemController
 * @package app\modules\agent\controllers
 */
class SystemController extends BaseController
{
    /**
     * 消息列表
     * @return string
     */
    public function actionMessage()
    {
        AgentMessage::checkNewMessage($this->agent->id);
        $query = AgentMessage::find();
        $query->andWhere(['aid' => $this->agent->id]);
        $query->andWhere(['<>', 'status', SystemMessage::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('status ASC, time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
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
        $model = AgentMessage::findOne($id);
        if (empty($model) || $model->aid != $this->agent->id) {
            throw new NotFoundHttpException('没有找到消息信息。');
        }
        $model->status = SystemMessage::STATUS_READ;
        $model->save();
        return $this->render('message_view', [
            'model' => $model
        ]);
    }
}
