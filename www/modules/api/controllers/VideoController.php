<?php

namespace app\modules\api\controllers;
use app\models\GoodsTraceVideo;
use app\models\KeyMap;
use app\models\System;
use app\models\UserNewschCat;
use yii\data\Pagination;
use yii\base\Exception;

/**
 * 视频小站
 * Class SurveyController
 * @package app\modules\api\controllers
 */
class VideoController extends BaseController
{
    /**
     * 视频小站
     * GET
     * {
     *     cid // 分类
     * }
     */
    public function actionList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $cid = $this->get('cid', GoodsTraceVideo::TYPE_BUY);

        $type='video';

        //更新分类状态 新
        $res=UserNewschCat::find()->where(['uid'=>$user->id,'cid'=>$cid,'type'=>$type])->one();
        /** @var $res UserNewschCat*/
        if(empty($res))
        {
            $model=new UserNewschCat();
            $model->uid=$user->id;
            $model->cid=$cid;
            $model->type=$type;
            $model->read_time=time();
           $model->save();
        }else
        {
            $res->read_time=time();
            $res->save();

        }
        $query = GoodsTraceVideo::find();
        $query->where(['<>', 'status', GoodsTraceVideo::STATUS_DEL]);
        $query->andWhere(['<=', 'start_time', time()]);
        $query->andFilterWhere(['cid' => $cid]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->orderBy('id DESC');
        $query->select('id, name, cid, desc,cover_image,video,create_time')->orderBy('id DESC')
            ->offset($pagination->offset)->limit($pagination->limit);
        $sduty_system=[];
        $sduty_system['nick_name']=System::getConfig('sduty_nick_name');
        $sduty_system['avatar']=System::getConfig('sduty_avatar');
        $list = [];
        /** @var GoodsTraceVideo $video */
        foreach ($query->each() as $video ) {
            $list[] = [
                'id' => $video->id,
                'name' => $video->name,
                'cid' => $video->cid,
                'cid_str' => KeyMap::getValue('goods_trace_video_type', $video->cid),
                'desc' => $video->desc,
                'cover_image' => $video->cover_image,
                'video' => $video->video,
                'create_time' => $video->create_time,
                'sduty_system'=>$sduty_system,
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
     * 搜索
     * @return array
     */
    public function actionSearch()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $keyword = $this->get('keyword');
        $cid = $this->get('cid', GoodsTraceVideo::TYPE_BUY);
        $query = GoodsTraceVideo::find();
        $query->where(['<>', 'status', GoodsTraceVideo::STATUS_DEL]);
        $query->andFilterWhere(['LIKE', 'name', $keyword]);
        $query->andFilterWhere(['cid' => $cid]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit);
        $list = [];
        /** @var GoodsTraceVideo $video */
        foreach ($query->each() as $video ) {
            $list[] = [
                'id' => $video->id,
                'name' => $video->name,
                'cid' => $video->cid,
                'cid_str' => KeyMap::getValue('goods_trace_video_type', $video->cid),
                'desc' => $video->desc,
                'cover_image' => $video->cover_image,
                'video' => $video->video,
                'create_time' => $video->create_time,
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
     * 小视频详情
     */
    public function actionDetail()
    {
        $id = $this->get('id');
        $video = GoodsTraceVideo::findOne($id);
        if (empty($video)) {
            return [
                'error' => 'PARAM',
                'message' => '参数错误。',
            ];
        }
        if(empty($this->get('type'))) {
            $this->actionReadVideo();//进详情 增加次数
        }
        $detail = [
            'id' => $video->id,
            'name' => $video->name,
            'desc' =>$video->desc,
            'cover_image' => $video->cover_image,
            'video' => $video->video,
            'create_time'=>$video->create_time
        ];
        return [
            'detail' => $detail,
        ];
    }

    /**
     * 增加阅读次数
     * @throws
     */
    public function actionReadVideo()
    {
        $id=$this->get('id');
        $video=GoodsTraceVideo::findOne($id);
        if(empty($video))
        {
            return [
                'error' => 'PARAM',
                'message' => '参数错误。',
            ];

        }
        $video->read_count+=1;
        $video->save();//增加阅读次数

        return [];
    }

}
