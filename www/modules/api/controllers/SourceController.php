<?php

namespace app\modules\api\controllers;
use app\models\GoodsSource;
use app\models\KeyMap;
use app\models\NewHand;
use app\models\System;
use app\models\UserNewschCat;
use yii\data\Pagination;
use yii\base\Exception;

/**
 * 图文素材
 * Class SurveyController
 * @package app\modules\api\controllers
 */
class SourceController extends BaseController
{


    /**
     * 图文素材
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

        $type='img';
        $cid = $this->get('cid', GoodsSource::TYPE_GOODS);
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

        $query = GoodsSource::find();
        $query->where(['<>', 'status', GoodsSource::STATUS_DEL]);
        $query->andWhere(['<=', 'start_time', time()]);
        $query->andFilterWhere(['cid' => $cid]);
        $pagination = new Pagination(['totalCount' => $query->count(),'validatePage' => false]);
        $query->orderBy('id DESC')
            ->offset($pagination->offset)->limit($pagination->limit);
        $sduty_system=[];//商学院logo  昵称
        $sduty_system['nick_name']=System::getConfig('sduty_nick_name');
        $sduty_system['avatar']=System::getConfig('sduty_avatar');


        $list = [];
        /** @var GoodsSource $source */
        foreach ($query->each() as $source ) {
            $list[] = [
                'id' => $source->id,
                'name' => $source->name,
                'cid' => $source->cid,
                'cid_str' => KeyMap::getValue('goods_source_type', $source->cid),
                'desc' => $source->desc,
                'img_list' => json_decode($source->img_list),
                'create_time' => $source->create_time,
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
     * $source
     * @return array
     */
    public function actionSearch()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $keyword = $this->get('keyword');
        $cid = $this->get('cid', GoodsSource::TYPE_GOODS);
        $query = GoodsSource::find();
        $query->where(['<>', 'status', GoodsSource::STATUS_DEL]);
        $query->andFilterWhere(['LIKE', 'name', $keyword]);
        $query->andFilterWhere(['cid' => $cid]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->orderBy('id DESC')
            ->offset($pagination->offset)->limit($pagination->limit);
        $list = [];
        /** @var GoodsSource $source */
        foreach ($query->each() as $source ) {
            $list[] = [
                'id' => $source->id,
                'name' => $source->name,
                'cid' => $source->cid,
                'cid_str' => KeyMap::getValue('goods_source_type', $source->cid),
                'desc' => $source->desc,
                'img_list' =>json_decode($source->img_list),
                'create_time' => $source->create_time,
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
     * 图片素材详情
     * @return array
     */
    public function actionDetail()
    {
        $id = $this->get('id');
        $source = GoodsSource::findOne($id);
        if (empty($source)) {
            return [
                'error' => 'PARAM',
                'message' => '参数错误。',
            ];
        }
        if(empty($this->get('type')))
        {
        $this->actionReadSource();//进详情 增加次数
        }
        $detail = [
            'id' => $source->id,
            'name' => $source->name,
            'desc' =>$source->desc,
            'img_list' => json_decode($source->img_list),
            'create_time'=>$source->create_time,
        ];
        return [
            'detail' => $detail,
        ];
    }

    /**
     * 增加阅读次数
     */
    public function actionReadSource()
    {

        $id=$this->get('id');
        $source=GoodsSource::findOne($id);
        if(empty($source))
        {
            return [
                'error' => 'PARAM',
                'message' => '参数错误。',
            ];

        }
        $source->read_count+=1;
        $source->save();//增加阅读次数
        return [
            'count'=> $source->read_count,
        ];
    }


}
