<?php

namespace app\modules\h5\controllers;

use app\models\notice;
use app\models\UserNotice;
use Yii;
/**
 * 公告控制器
 * Class NoticeController
 * @package app\modules\h5\controllers
 */

class NoticeController extends BaseController
{
    /**
     * 公告列表
     * @return string
     */
    public function actionList()
    {

        if (!Yii::$app->user->isGuest) {
            //点击用户
            $uid = Yii::$app->user->id;
            //最新文章id
            $new_id = notice::find()->select("id")->where(["status"=>Notice::STATUS_SHOW])->orderBy("id desc")->limit(1)->one();
            //用户上次阅读的文章id
            $userview_id = UserNotice::find()->select("nid")->where(["uid"=>$uid])->orderBy("id desc ")->limit(1)->one();
            if (empty($userview_id) || $new_id->id > $userview_id->nid){
                $user_notice = new UserNotice();
                $user_notice->uid = $uid;
                $user_notice->nid = $new_id->id;
                $user_notice->create_time = time();
                $user_notice->save();
            }
        }
        return $this->render('list');
    }

    /**
     * 公告详情
     * @return string
     */
    public function actionView()
    {
        return $this->render('view');
    }
    /**
     * 通知列表
     * @return string
     */
    public function actionMessage()
    {
        return $this->render('list_message');
    }

    /**
     * 通知详情
     * @return string
     */
    public function actionUmview()
    {
        return $this->render('umview');
    }




}
