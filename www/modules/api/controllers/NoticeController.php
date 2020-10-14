<?php

namespace app\modules\api\controllers;

use app\models\Notice;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\data\Pagination;

/**
 * 公告
 * Class NoticeController
 * @package app\modules\api\controllers
 */

class NoticeController extends BaseController
{
    /**
     * 公告列表
     */
    public function actionList()
    {
        $query = Notice::find();
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->orderBy('time DESC');
        $query->andWhere(['status' => Notice::STATUS_SHOW]);
        $query->limit($pagination->limit)->offset($pagination->offset);
        $list = [];
        /** @var Notice $model */
        foreach ($query->each() as $model) {
            $list[] = [
                'id' => $model->id,
                'title' => $model->title,
                'desc' => $model->desc,
                'main_pic' => !empty($model->main_pic) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $model->main_pic : '',
                'time' => $model->time,
                'url' => Yii::$app->params['site_host'] . '/h5/notice/view?id=' . $model->id . '&app=1',
            ];
        }
        return [
            'list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 公告详情
     * @return array
     */
    public function actionDetail()
    {
        $id = $this->get('id');
        $notice = Notice::findOne($id);
        if (empty($notice)) {
            return [
                'error' => 'PARAM',
                'message' => '参数错误。',
            ];
        }
        $detail = [
            'id' => $notice->id,
            'title' => $notice->title,
            'main_pic' => !empty($notice->main_pic) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $notice->main_pic : '',
            'content' => $notice->content,
            'time' => $notice->time,
        ];
        return [
            'detail' => $detail,
        ];
    }

//    /**
//     * 检查是否有新公告
//     */
//    public function actionCheckNewNotice()
//    {
//
//        return [
//            'have_new_msg' => Notice::find()->andWhere(['uid' => $user->id, 'status' => UserMessage::STATUS_NEW])->exists(),
//        ];
//    }






}
