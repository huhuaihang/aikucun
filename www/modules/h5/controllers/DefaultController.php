<?php

namespace app\modules\h5\controllers;

use app\models\Ad;
use app\models\AdLocation;
use app\models\Goods;
use app\models\Notice;
use app\models\Order;
use app\models\User;
use app\models\System;
use app\models\UserMessage;
use app\models\UserSearchHistory;
use app\models\Util;
use Yii;
use yii\helpers\Url;
use yii\web\Response;

/**
 * H5前台默认控制器
 * Class DefaultController
 * @package app\modules\h5\controllers
 */
class DefaultController extends BaseController
{
    /**
     * H5前台主页
     * @return string
     */
    public function actionIndex()
    {
//        Yii::$app->response->format = Response::FORMAT_HTML;
//        $list = Goods::find()->asArray()
//            ->select('id, title, desc, price, main_pic, share_commission_value, is_pack')
//            ->andWhere(['status' => Goods::STATUS_ON, 'is_index' => 1])
//            ->all();
//        $goods_list = [];
//
        //分享佣金
//        $share_commission = 30;
//        $commission_ratio = 0;
//        $self_buy_ratio = 0;
  
        $is_skip = 0;
        if (!Yii::$app->user->isGuest) {
            $user = User::findOne(Yii::$app->user->id);
//            $share_commission = $user->childBuyRatio;
//            $commission_ratio = $user->buyRatio;
//            $self_buy_ratio = $user->buyRatio;
            if ($user->status == 1) {
                $is_skip = 1;
            }
        }
//
//        // 分类导航广告
//        $nav_list = [];
//        foreach (AdLocation::findOne(5)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
//            $nav_list[] = [
//                'id' => $ad->id,
//                'name' => $ad->name,
//                'txt' => $ad->txt,
//                'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
//                'url' => $ad->url,
//                'location' => $ad->location->getAttributes(['id', 'height', 'width']),
//            ];
//        }
//        // 通栏专题广告
//        $ad2_list = [];
//        foreach (AdLocation::findOne(6)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
//            $ad2_list[] = [
//                'id' => $ad->id,
//                'name' => $ad->name,
//                'txt' => $ad->txt,
//                'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
//                'url' => $ad->url,
//                'location' => $ad->location->getAttributes(['id', 'height', 'width']),
//            ];
//        }
//        // 礼包广告
//        $ad3_list = [];
//        foreach (AdLocation::findOne(7)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
//            $ad3_list[] = [
//                'id' => $ad->id,
//                'name' => $ad->name,
//                'txt' => $ad->txt,
//                'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
//                'url' => $ad->url,
//                'location' => $ad->location->getAttributes(['id', 'height', 'width']),
//            ];
//        }
//        // 分类右上广告
//        $ad4_list = [];
//        foreach (AdLocation::findOne(8)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
//            $ad4_list[] = [
//                'id' => $ad->id,
//                'name' => $ad->name,
//                'txt' => $ad->txt,
//                'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
//                'url' => $ad->url,
//                'location' => $ad->location->getAttributes(['id', 'height', 'width']),
//            ];
//        }
//        // 分类右下广告
//        $ad5_list = [];
//        foreach (AdLocation::findOne(9)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
//            $ad5_list[] = [
//                'id' => $ad->id,
//                'name' => $ad->name,
//                'txt' => $ad->txt,
//                'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
//                'url' => $ad->url,
//                'location' => $ad->location->getAttributes(['id', 'height', 'width']),
//            ];
//        }
//
//        // 今日推荐
//        $today_list = [];
//        $query = Goods::find();
//        $query->andWhere(['is_today' => 1]);
//        $query->andWhere(['status' => Goods::STATUS_ON]);
//        $query->orderBy('sort desc, create_time DESC');
//        /** @var Goods $goods */
//        foreach ($query->limit(4)->each() as $goods) {
//            $today_list[] = [
//                'id' => $goods->id,
//                'title' => $goods->title,
//                'desc' => $goods->desc,
//                'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
//                'price' => $goods->price,
//                'share_commission' => round($goods->share_commission_value * $share_commission /100, 2),
//                'self_price' => round($goods->share_commission_value * $commission_ratio /100, 2),
//                'is_pack' => $goods->is_pack,
//                'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $user->invite_code], true),
//            ];
//        }
//        // 邀请新优品
//        $best_list = [];
//        $query = Goods::find();
//        $query->andWhere(['is_best' => 1]);
//        $query->andWhere(['status' => Goods::STATUS_ON]);
//        $query->orderBy('sort desc, create_time DESC');
//        /** @var Goods $goods */
//        foreach ($query->limit(10)->each() as $goods) {
//            $best_list[] = [
//                'id' => $goods->id,
//                'title' => $goods->title,
//                'desc' => $goods->desc,
//                'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
//                'price' => $goods->price,
//                'share_commission' => round($goods->share_commission_value * $share_commission /100, 2),
//                'self_price' => round($goods->share_commission_value * $commission_ratio /100, 2),
//                'is_pack' => $goods->is_pack,
//                'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $user->invite_code], true),
//            ];
//        }
//
//        /** @var Goods $goods */
//        foreach ($list as $key => $goods) {
//            $goods_list[] = $goods;
//            $goods_list[$key]['share_commission'] = round($goods['share_commission_value'] * $share_commission /100, 2);
//            $goods_list[$key]['self_money'] = round($goods['share_commission_value'] * $self_buy_ratio /100, 2);
//        }
//        $notice_list = Notice::find()->asArray()->andWhere(['<>', 'status' , Notice::STATUS_DEL])->select('id, title')->limit(1)->orderBy('time DESC')->all();
        return $this->render('index', [
//            'goods_list' => $goods_list,
//            'notice_list' => $notice_list,
//            'ad2_list' => $ad2_list, // 通栏专题广告
//            'ad3_list' => $ad3_list, // 礼包广告
//            'ad4_list' => $ad4_list, // 分类右上广告
//            'ad5_list' => $ad5_list, // 分类右下广告
//            'nav_list' => $nav_list, // 分类导航
//            'today_list' => $today_list,  // 今日推荐
//            'best_list' => $best_list,  // 特邀优品
            'is_skip' => $is_skip, //是否可以跳转
        ]);
    }

    /**
     * 搜索页面
     * @return string
     */
    public function actionSearch()
    {
        if (!Yii::$app->user->isGuest) {
            $query = UserSearchHistory::find();
            $query->andWhere(['uid' => Yii::$app->user->id]);
            $history_list = $query->orderBy('create_time DESC')->limit('10')->all();
        } else {
            $history_list = [];
        }
        return $this->render('search', [
            'history_list' => $history_list,
        ]);
    }

    /**
     * 清空搜索历史
     * @return array
     */
    public function actionDeleteHistory()
    {
        if (!Yii::$app->user->isGuest) {
            $history = new UserSearchHistory();
            $history->deleteAll(['uid' => Yii::$app->user->id]);
        }
        return [
            'result' => 'success'
        ];
    }

    /**
     * 品牌文化
     * @return string
     */
    public function actionAboutBrand()
    {
        return $this->render('about_brand');
    }
}
