<?php

namespace app\modules\api\controllers;

use app\models\NewHand;
use app\models\UserNewHand;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\data\Pagination;
use yii\base\Exception;
use yii\helpers\Url;

/**
 * 新人入门
 * Class NoticeController
 * @package app\modules\api\controllers
 */

class NewHandController extends BaseController
{


    /**
     * 新手入门列表
     * GET
     */
    public function actionNewHandList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $query = NewHand::find();
        $query->andWhere(['status' => NewHand::STATUS_OK]);
        $query->andWhere(['<=' ,'start_time',time()]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $list = [];
        $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit);
        /** @var NewHand $model */
        foreach ($query->each() as $model) {
            $user_status = UserNewHand::find()->where(['uid' => $user->id, 'nid' => $model->id])->exists();
            $list[] = [
                'id' => $model->id,
                'title' => $model->title,
                'main_pic' =>  Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $model->main_pic,
                'status' => $model->status,
                'read_str' => $user_status ? '已读' : '未读',
                'read_status' => $user_status ? 0 : 1,
                'create_time' => $model->create_time,
                'url' =>  Yii::$app->params['site_host'] .Url::to(['/h5/source/new-view?app=1&id='.$model->id]),
            ];
        }
        return [
            'list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 新人入门详情
     * @return array
     */
    public function actionNewDetail()
    {
      

        $id = $this->get('id');
        $news = NewHand::findOne($id);
        if (empty($news)) {
            return [
                'error' => 'PARAM',
                'message' => '参数错误。',
            ];
        }
        $detail = [
            'id' => $news->id,
            'title' => $news->title,
            'content' =>$news->content,
            'main_pic' => $news->main_pic,
            'create_time'=>$news->create_time,
        ];
        return [
            'detail' => $detail,
        ];
    }

    /**
     * 更新新人入门 用户已读状态
     */
    public function actionChangeNewHand()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $nid=$this->get('id');
        $newhand=NewHand::findOne($nid);
        $newhand->read_count+=1;
        $newhand->save();//增加阅读次数
        $res=UserNewHand::find()->where(['uid'=>$user->id,'nid'=>$nid])->exists();
        if(empty($res))
        {
            $model=new  UserNewHand();
            $model->uid=$user->id;
            $model->nid=$nid;
            $model->create_time=time();
            if(!$model->save())
            {
                 throw new  Exception('写入失败');
            }

        }
        return [];
    }






}
