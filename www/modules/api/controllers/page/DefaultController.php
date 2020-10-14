<?php

namespace app\modules\api\controllers\page;

use app\models\Ad;
use app\models\AdLocation;
use app\models\Discount;
use app\models\DiscountGoods;
use app\models\Goods;
use app\models\KeyMap;
use app\models\Notice;
use app\models\Order;
use app\models\OrderItem;
use app\models\System;
use app\models\UserNotice;
use app\models\Util;
use app\modules\api\controllers\BaseController;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;

/**
 * 默认控制器
 * Class DefaultController
 * @package app\modules\api\controllers\page
 */
class DefaultController extends BaseController
{
    /**
     * 主页
     */
    public function actionIndex()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            $user = null;
        }
        // 顶部轮播图广告列表
        $ad1_list = [];
        foreach (AdLocation::findOne(1)->getActiveAdList()->each() as $ad) {
            /** @var Ad $ad */
            $ad1_list[] = [
                'id' => $ad->id,
                'name' => $ad->name,
                'txt' => $ad->txt,
                'img' => Util::fileUrl($ad->img),
                'url' => $ad->url,
                'location' => $ad->location->getAttributes(['id', 'height', 'width']),
            ];
        }
        // 分类导航广告 | 顶部横栏广告
        $nav_list = [];
        if ($this->client_api_version == '1.0.3') {
            $q = Ad::find();
            $q->andWhere(['lid' => 5])->andWhere(['status' => Ad::STATUS_ACTIVE])
                ->andWhere(['<=', 'start_time', time()])
                ->andWhere(['>=', 'end_time', time()])
                ->andWhere(['NOT IN', 'id', [39,40]])
                ->orderBy('sort DESC, id DESC');
            foreach ($q->each() as $ad) {/** @var Ad $ad */
                $nav_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),//Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
        } elseif ($this->client_api_version >= '1.0.4') {
            $q = Ad::find();
            $q->andWhere(['lid' => 5])->andWhere(['status' => Ad::STATUS_ACTIVE])
                ->andWhere(['<=', 'start_time', time()])
                ->andWhere(['>=', 'end_time', time()])
                ->andWhere(['NOT IN', 'id', [25,26]])
                ->orderBy('sort DESC, id DESC');
            foreach ($q->each() as $ad) {/** @var Ad $ad */
                $nav_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
        } else {
            foreach (AdLocation::findOne(2)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
                $nav_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
        }
        // 首页专题广告
        $ad2_list = [];
        $ad3_list = [];
        $ad4_list = [];
        $ad5_list = [];
        $ad6_list = [];
        if ($this->client_api_version > '1.0.4') {
            foreach (AdLocation::findOne(33)->getActiveAdList()->each() as $ad) {
                /** @var Ad $ad */
                $ad6_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),
                    'url' => $ad->url,
                    //'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
            sort($ad6_list);
        } else {
            // 通栏专题广告

            foreach (AdLocation::findOne(6)->getActiveAdList()->each() as $ad) {

                /** @var Ad $ad */
                $ad2_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
            // 礼包广告

            foreach (AdLocation::findOne(7)->getActiveAdList()->each() as $ad) {
                /** @var Ad $ad */
                $ad3_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
            // 分类右上广告

            foreach (AdLocation::findOne(8)->getActiveAdList()->each() as $ad) {

                /** @var Ad $ad */
                $ad4_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
            // 分类右下广告

            foreach (AdLocation::findOne(9)->getActiveAdList()->each() as $ad) {
                /** @var Ad $ad */
                $ad5_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
        }

        // 公告
        $notice_1_list = Notice::find()->asArray()->andWhere(['status' => Notice::STATUS_SHOW])
            ->limit(1)->orderBy('time DESC')->all();
        $notice_list = [];
        /** @var Notice $notice */
        foreach ($notice_1_list as $notice) {
            $notice_list[] = [
                'id' => $notice['id'],
                'title' => $notice['title'],
                'url' => Yii::$app->params['site_host'] . '/h5/notice/view?id=' . $notice['id'] . '&app=1',
            ];
        }
        //分享佣金
        $share_commission = 30;
        $commission_ratio = 0;
        if ($user) {
            $share_commission = $user->childBuyRatio;
            $commission_ratio = $user->buyRatio;
        }

        // 精品推荐
        $recommend_list = [];
        $query = Goods::find();
        $query->andWhere(['is_index' => 1]);
        $query->andWhere(['is_pack' => Goods::NO]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        $query->orderBy('create_time DESC');
        /** @var Goods $goods */
        foreach ($query->each() as $goods) {
            $limit = [
                'is_limit' => empty($goods->is_limit) ? 0 : $goods->is_limit,
                'limit_type' => empty($goods->limit_type) ? 0 : $goods->limit_type,
                'limit_type_str' => empty($goods->limit_type) ? 0 : KeyMap::getValue('goods_limit_type', $goods->limit_type),
                'limit_amount' => empty($goods->limit_amount) ? 0 : $goods->limit_amount,
            ];
            $json = (object)$limit;
            $recommend_list[] = [
                'id' => $goods->id,
                'title' => $goods->title,
                'desc' => $goods->desc,
                'main_pic' => $this->client_api_version > '1.0.6' ? Util::fileUrl($goods->main_pic, true, '_300x300') : Util::fileUrl($goods->main_pic),
                'price' => Util::convertPrice($goods->price),
                'share_commission' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $share_commission / 100)),
                'self_price' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $commission_ratio / 100)),
                'is_pack' => $goods->is_pack,
                'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $user->invite_code], true),
                'sale_amount' => $goods->getSaleAmount(),
                'limit' => $json,
            ];
        }
        //限时抢购广告位
        $sale_ad = [];
        $ad_id = (Yii::$app->params['site_host'] == 'http://yun.yuntaobang.cn') ? 36 :38;
        foreach (AdLocation::findOne($ad_id)->getActiveAdList()->each() as $ad) {
            if ($ad->sort == 1) {
                /** @var Ad $ad */
                $sale_ad[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
        }
        // 限时抢购
        $sale_list = [];
        $discount_gid_arr = [];//限时抢购商品id数组
        /** @var $discount Discount */
        $discount = Discount::find()
            ->andWhere(['status' => Discount::STATUS_RUNNING])
            ->andWhere(['<=', 'start_time', time()])
            ->andWhere(['>=', 'end_time', time()])
            ->orderBy(['id' => SORT_ASC])
            ->one();

        if (!empty($discount)) {

            $discount_gid_arr = array_column($discount->discountGoodsList, 'gid');
            $did = $discount->id;
            $query = DiscountGoods::find();
            $query->joinWith('goods');
            $query->andWhere(['goods.status' => Goods::STATUS_ON]);
            $query->andWhere(['did' => $did]);
            $query->limit(10);
            $query->orderBy('id desc');
            /** @var Goods $goods
             * @var DiscountGoods $dis_goods
             */
            foreach ($query->each() as $dis_goods) {
                if (!empty($dis_goods->hour) && ($discount->start_time + $dis_goods->hour * 3600) < time()) {
                    continue;
                }
                $sold_amount = $dis_goods->getSaleAmount();// 已卖出的份数
                $goods = Goods::findOne($dis_goods->gid);
                if (!empty($goods->skuList)) {
                    $market_price = empty($goods->skuList[0]->market_price) ? $goods->price : $goods->skuList[0]->market_price;
                } else {
                    $market_price = $goods->price;
                }
                $sale_list[] = [
                    'id' => $goods->id,
                    'title' => $goods->title,
                    'main_pic' => $this->client_api_version > '1.0.6' ? Util::fileUrl($goods->main_pic, true, '_300x300') : Util::fileUrl($goods->main_pic),
                    'cost_price' => Util::convertPrice($market_price),
                    'price' => $dis_goods->type == DiscountGoods::TYPE_PRICE ? Util::convertPrice($goods->price - $dis_goods->price) : Util::convertPrice($goods->price * ($dis_goods->ratio / 10)),
                    'share_commission' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $share_commission / 100)),
                    'self_price' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $commission_ratio / 100)),
                    'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $user->invite_code], true),
                    'end_time' => empty($dis_goods->hour) ? $discount->end_time : $discount->start_time + $dis_goods->hour * 3600,//结束时间
                    //'end_time' => $discount->end_time,//结束时间
                    'amount' => $dis_goods->amount,//该商品活动数量
                    'sold_amount' => empty($sold_amount) ? 0 : $sold_amount,//该商品活动已售数量
                ];

            }
        }
        // 今日推荐

        $today_list = [];
        $query = Goods::find();
        $query->andWhere(['is_today' => 1]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        if (!empty($discount_gid_arr)) {
            $query->andWhere(['not in', 'id', $discount_gid_arr]);
        }

        $query->andWhere(['is_pack' => 0]);
        $query->andWhere(['is_coupon' => 0]);
        $query->limit(6);
        $query->orderBy('sort desc, create_time DESC');
        /** @var Goods $goods */
        foreach ($query->each() as $goods) {
            $limit = [
                'is_limit' => empty($goods->is_limit) ? 0 : $goods->is_limit,
                'limit_type' => empty($goods->limit_type) ? 0 : $goods->limit_type,
                'limit_type_str' => empty($goods->limit_type) ? 0 : KeyMap::getValue('goods_limit_type', $goods->limit_type),
                'limit_amount' => empty($goods->limit_amount) ? 0 : $goods->limit_amount,
            ];
            $json = (object)$limit;
            $today_list[] = [
                'id' => $goods->id,
                'title' => $goods->title,
                'desc' => $goods->desc,
                'main_pic' => $this->client_api_version > '1.0.6' ? Util::fileUrl($goods->main_pic, true, '_300x300') : Util::fileUrl($goods->main_pic),
                'price' => Util::convertPrice($goods->price),
                'share_commission' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $share_commission / 100)),
                'self_price' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $commission_ratio / 100)),
                'is_pack' => $goods->is_pack,
                'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $user->invite_code], true),
                'sale_amount' => $goods->getSaleAmount(),
                'limit' => $json,
            ];
        }

        //  $today_list=Util::array_even($today_list);
        // 邀请新优品

        $best_list = [];
        $query = Goods::find();
        $query->andWhere(['is_index_best' => 1]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        $query->andWhere(['is_pack' => 0]);
        $query->andWhere(['is_coupon' => 0]);
        if (!empty($discount_gid_arr)) {
            $query->andWhere(['not in', 'id', $discount_gid_arr]);
        }
        $query->orderBy('sort desc, create_time DESC');
        $query->limit(10);
        /** @var Goods $goods */
        foreach ($query->each() as $goods) {
            $limit = [
                'is_limit' => empty($goods->is_limit) ? 0 : $goods->is_limit,
                'limit_type' => empty($goods->limit_type) ? 0 : $goods->limit_type,
                'limit_type_str' => empty($goods->limit_type) ? 0 : KeyMap::getValue('goods_limit_type', $goods->limit_type),
                'limit_amount' => empty($goods->limit_amount) ? 0 : $goods->limit_amount,
            ];
            $json = (object)$limit;
            $best_list[] = [
                'id' => $goods->id,
                'title' => $goods->title,
                'desc' => $goods->desc,
                'main_pic' => $this->client_api_version > '1.0.6' ? Util::fileUrl($goods->main_pic, true, '_300x300') : Util::fileUrl($goods->main_pic),
                'price' => Util::convertPrice($goods->price),
                'share_commission' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $share_commission / 100)),
                'self_price' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $commission_ratio / 100)),
                'is_pack' => $goods->is_pack,
                'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $user->invite_code], true),
                'sale_amount' => $goods->getSaleAmount(),
                'limit' => $json,
            ];
        }
        $best_list = Util::array_even($best_list);
        // 猜你喜欢
        $like_list = [];
        $query = Goods::find();
        if (empty($user)) {
            $query->joinWith(['orderItemList']);
            $query->select([
                'goods.id',
                'goods.title',
                'goods.desc',
                'goods.price',
                'goods.stock',
                'goods.share_commission_value',
                'goods.main_pic',
                'goods.create_time',
                'SUM(order_item.amount) as amount',
            ]);

            $query->groupBy('gid');
            $query->orderBy('amount DESC');
            // echo $sql=$query->createCommand()->getRawSql();
        } else {
            $browse_cid = Yii::$app->cache->get('browse_' . $user->id);
            if (!empty($browse_cid)) {
                $query->andWhere(['in', 'cid', $browse_cid]);
                $query->orderBy('rand() DESC', 'create_time DESC');
            } else {
                $query->joinWith(['orderItemList']);
                $query->select([
                    'goods.id',
                    'goods.title',
                    'goods.desc',
                    'goods.price',
                    'goods.stock',
                    'goods.share_commission_value',
                    'goods.main_pic',
                    'goods.create_time',
                    'SUM(order_item.amount) as amount',
                ]);
                $query->groupBy('gid');
                $query->orderBy('amount DESC');
            }

        }
        $query->andWhere(['is_score' => 0]);
        $query->andWhere(['is_pack' => 0]);
        $query->andWhere(['is_coupon' => 0]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        if (!empty($discount_gid_arr)) {
            $query->andWhere(['not in', 'goods.id', $discount_gid_arr]);
        }
        $query->limit(6);
        //echo $sql=$query->createCommand()->getRawSql();
        /** @var Goods $goods */
        foreach ($query->each() as $goods) {
            $limit = [
                'is_limit' => empty($goods->is_limit) ? 0 : $goods->is_limit,
                'limit_type' => empty($goods->limit_type) ? 0 : $goods->limit_type,
                'limit_type_str' => empty($goods->limit_type) ? 0 : KeyMap::getValue('goods_limit_type', $goods->limit_type),
                'limit_amount' => empty($goods->limit_amount) ? 0 : $goods->limit_amount,
            ];
            $json = (object)$limit;
            $like_list[] = [
                'id' => $goods->id,
                'title' => $goods->title,
                'desc' => $goods->desc,
                'main_pic' => $this->client_api_version > '1.0.6' ? Util::fileUrl($goods->main_pic, true, '_300x300') : Util::fileUrl($goods->main_pic),
                'price' => Util::convertPrice($goods->price),
                'share_commission' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $share_commission / 100)),
                'self_price' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $commission_ratio / 100)),
                //'is_pack' => $goods->is_pack,
                'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $user->invite_code], true),
                'sale_amount' => $goods->getSaleAmount(),
                'limit' => $json,
            ];

        }
        $like_list = Util::array_even($like_list);


        return [
            'ad1_list' => $ad1_list, // 首页轮播
            'ad2_list' => $ad2_list, // 通栏专题广告
            'ad3_list' => $ad3_list, // 礼包广告
            'ad4_list' => $ad4_list, // 分类右上广告
            'ad5_list' => $ad5_list, // 分类右下广告
            'ad6_list' => $ad6_list, // 首页专题广告列表
            'nav_list' => $nav_list, // 分类导航
            'recommend_list' => $recommend_list,
            'today_list' => $today_list,  // 今日推荐
            'best_list' => $best_list,  // 特邀优品
            'like_list' => $like_list,  // 猜你喜欢
            'sale_ad' => $sale_ad,//限时抢购广告位
            'sale_list' => $sale_list,  // 限时抢购
            'notice_list' => $notice_list,
        ];
    }

    /**
     * 更多优品
     */
    public function actionMoreList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            $user = null;
        }
        //分享佣金
        $share_commission = 30;
        $commission_ratio = 0;
        if ($user) {
            $share_commission = $user->childBuyRatio;
            $commission_ratio = $user->buyRatio;
        }
        // 限时抢购
        $discount_gid_arr=[];//限时抢购商品id数组
        /** @var $discount Discount */
        $discount = Discount::find()
            ->andWhere(['status' => Discount::STATUS_RUNNING ])
            ->andWhere(['<=','start_time',time()])
            ->andWhere(['>=','end_time',time()])
            ->one();
        if(!empty($discount))
        {
        $discount_gid_arr=array_column($discount->discountGoodsList,'gid');
        }
        // 更多优品
        $best_more_list = [];
        $query = Goods::find();
        $query->andWhere(['is_index_best' => Goods::NO]);
        $query->andWhere(['is_today' => Goods::NO]);
        $query->andWhere(['is_score' => Goods::NO]);
        $query->andWhere(['is_pack'=>Goods::NO ]);
        $query->andWhere(['is_coupon'=>Goods::NO ]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        if(!empty($discount_gid_arr))
        {
        $query->andWhere(['not in','id',$discount_gid_arr]);
        }
        $query->orderBy('sort desc, create_time DESC');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $pagination->pageSize=10;
        $query->limit($pagination->limit)->offset($pagination->offset);
        // echo $sql=$query->createCommand()->getRawSql();
        /** @var Goods $goods */
        foreach ($query->each() as $goods) {
            $limit = [
                'is_limit' => empty($goods->is_limit) ? 0 : $goods->is_limit,
                'limit_type' => empty($goods->limit_type) ? 0 : $goods->limit_type,
                'limit_type_str' => empty($goods->limit_type) ? 0 : KeyMap::getValue('goods_limit_type', $goods->limit_type),
                'limit_amount' => empty($goods->limit_amount) ? 0 : $goods->limit_amount,
            ];
            $json = (object)$limit;
            $best_more_list[] = [
                'id' => $goods->id,
                'title' => $goods->title,
                'desc' => $goods->desc,
                'main_pic' => $this->client_api_version > '1.0.6' ? Util::fileUrl($goods->main_pic, true, '_300x300') : Util::fileUrl($goods->main_pic),
                'price' => floatval($goods->price),
                'share_commission' => floatval(sprintf('%.2f',$goods->share_commission_value * $share_commission /100)),
                'self_price' => floatval(sprintf('%.2f', $goods->share_commission_value * $commission_ratio /100)),
                'is_pack' => $goods->is_pack,
                'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $user->invite_code], true),
                'sale_amount' => $goods->getSaleAmount(),
                'limit' => $json,
            ];
        }
        $best_more_list=Util::array_even($best_more_list);
        if($pagination->totalCount % 2 != 0)
        {
            $pagination->totalCount=$pagination->totalCount-1;
        }

        return [
            'best_more_list' => $best_more_list,//更多优品
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 首页弹窗广告
     */
    public function actionPopAd()
    {
        //首页弹窗广告位
        $pop_ad = [];
        $ad_id = (Yii::$app->params['site_host'] == 'http://yun.yuntaobang.cn') ? 38 :39;
        foreach (AdLocation::findOne($ad_id)->getActiveAdList()->each() as $ad) {
            /** @var Ad $ad */
            $pop_ad[] = [
                'id' => $ad->id,
                'name' => $ad->name,
                'txt' => $ad->txt,
                'img' => Util::fileUrl($ad->img),
                'url' => $ad->url,
                'location' => $ad->location->getAttributes(['id', 'height', 'width']),
            ];
        }
        return [
            'pop_ad' => $pop_ad, //首页弹窗广告
        ];
    }

    public function actionNotice()
    {
        $open = System::getConfig('notice_open');
        if ($open == 1) {
            // 公告
            $notice_1_list = Notice::find()->asArray()->andWhere(['status' => Notice::STATUS_SHOW])
                ->limit(1)->orderBy('time DESC')->all();
            $notice_list = [];
            /** @var Notice $notice */
            foreach ($notice_1_list as $notice) {
                $notice_list[] = [
                    'id' => $notice['id'],
                    'title' => $notice['title'],
                    'url' => Yii::$app->params['site_host'] . '/h5/notice/view?id=' . $notice['id'] . '&app=1',
                ];
                $user = $this->loginUser();
                if (is_array($user) && isset($user['error_code'])) {
                    $user = null;
                } else {
                    if (UserNotice::find()->where(['uid' => $user->id, 'nid' => $notice['id']])->exists()) {
                        $notice_list = [];
                    }
                }
            }
        } else {
            $open = 0;
            $notice_list = [];
        }
        return ['is_open' => $open, 'notice_list' => $notice_list];
    }

}
