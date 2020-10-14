<?php

namespace app\modules\h5\controllers;

use app\models\Ad;
use app\models\AdLocation;
use app\models\City;
use app\models\Discount;
use app\models\DiscountGoods;
use app\models\Goods;
use app\models\GoodsAttrValue;
use app\models\GoodsComment;
use app\models\GoodsSku;
use app\models\IpCity;
use app\models\KeyMap;
use app\models\Order;
use app\models\User;
use app\models\UserPackageCoupon;
use app\models\UserRecommend;
use app\models\UserSearchHistory;
use app\models\Util;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * 商品控制器
 * Class GoodsController
 * @package app\modules\h5\controllers
 */
class GoodsController extends BaseController
{
    /**
     * 商品详情
     * @return string
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionView()
    {
        $id = $this->get('id');
        $goods = Goods::findOne($id);
        if (empty($goods)) {
            throw new NotFoundHttpException('没有找到商品信息。');
        }
        if ($goods->status != Goods::STATUS_ON) {
            throw new BadRequestHttpException('商品已下架。');
        }
        if (Yii::$app->user->isGuest) {
            UserSearchHistory::addCookieHistory($goods->keywords);
        }
        $invite_code = $this->get('invite_code');
        if (!empty($invite_code)) {
            $invite_code_cookie = new Cookie();
            $invite_code_cookie->name = 'invite_code';
            $invite_code_cookie->value = $invite_code;
            Yii::$app->response->cookies->add($invite_code_cookie);
            if (Yii::$app->user->isGuest) {
                $cookie_value = $invite_code . ':g' . $goods->id . '|';
                $cookie = Yii::$app->request->cookies->get('invite');
                if (empty($cookie)) {
                    $cookie = new Cookie();
                    $cookie->name = 'invite';
                    $cookie->value = '';
                }
                $cookie->value = $cookie->value . $cookie_value;
                Yii::$app->response->cookies->add($cookie);
            } else {
                UserRecommend::saveRecommend($invite_code, Yii::$app->user->id, null, $goods->id);
            }
        }
        $share_commission = $self_buy_ratio =   30;
        $user_score = $user_status = 0 ;
        $is_have=0; //检查是否有礼包兑换券
        if (!Yii::$app->user->isGuest) {
            $user = User::findOne(Yii::$app->user->id);
            $share_commission = $user->childBuyRatio;
            $self_buy_ratio = $user->buyRatio;
            $user_score = $user->account->score;
            $user_status = $user->status;
            //检查是否有礼包兑换券
            if (UserPackageCoupon::find()->where(['uid' => $user->id, 'status' => UserPackageCoupon::STATUS_OK])->exists()) {
                $is_have = 1;
            }
        }
        $gav_map = []; // GoodsAttrValue
        foreach (GoodsAttrValue::find()
                     ->joinWith(['goods_attr'])
                     ->where(['gid' => $goods->id, 'is_sku' => 1])
                     ->each() as $gav) {
            if (!isset($gav_map[$gav->aid])) {
                $gav_map[$gav->aid] = [];
            }
            $gav_map[$gav->aid][] = $gav;
        }
        /** @var $gav_list GoodsAttrValue[]*/
        $gav_list = GoodsAttrValue::find()
            ->joinWith(['goods_attr'])
            ->where(['gid' => $goods->id, 'is_sku' => 0])
            ->all();
        $sku_list = GoodsSku::find()->asArray()->where(['gid' => $goods->id])->all();
        $sold_amount=$goods->getSaleAmount();//商品销量
        //限时抢购相关
        $sale_goods['is_discount'] =0;
        /** @var DiscountGoods $discountGoods */
        $discountGoods = DiscountGoods::find()
            ->joinWith('discount')
            ->andWhere(['status' => Discount::STATUS_RUNNING])
            ->andWhere(['<=', 'start_time', time()])
            ->andWhere(['>=', 'end_time', time()])
            ->andWhere(['gid' => $goods['id']])
            ->one();
        if(!empty($discountGoods))
        {
            if(empty($discountGoods->hour) || ($discountGoods->discount->start_time + $discountGoods->hour * 3600) > time()) {
                $sale_goods['end_time'] = empty($discountGoods->hour) ? $discountGoods->discount->end_time : $discountGoods->discount->start_time + $discountGoods->hour * 3600;
                if (!empty($goods->skuList)) {
                    $market_price = empty($goods->skuList[0]->market_price) ? $goods->price : $goods->skuList[0]->market_price;
                } else {
                    $market_price = $goods->price;
                }
                $sale_goods['market_price'] = $market_price;
                $goods->price = $discountGoods->type == DiscountGoods::TYPE_PRICE ? Util::convertPrice($goods->price - $discountGoods->price) : Util::convertPrice($goods->price * ($discountGoods->ratio / 10));
                $sale_goods['discount_str'] = $discountGoods->type == DiscountGoods::TYPE_PRICE ? '减' : '折';
                $sale_goods['is_discount'] = 1;
                $sold_amount = $discountGoods->getSaleAmount();// 已卖出的份数
                if ($discountGoods->amount - $sold_amount <= 0) {
                    $sale_goods['is_discount'] = 2;//限时数量已售罄
                }
            }
        }
        if(!empty($sku_list))
        {
            foreach ($sku_list as $k=>$sku)
            {
                if($sku['commission']!='')
                {
                    $sku_list[$k]['share_commission']=Util::convertPrice(sprintf('%.2f',$sku['commission'] * $share_commission /100));
                }else
                {
                    $sku_list[$k]['share_commission']=Util::convertPrice(sprintf('%.2f',$goods->share_commission_value * $share_commission /100));
                }
                unset($sku_list[$k]['commission']);
                if(!empty($discountGoods)) {
                    if(empty($discountGoods->hour) || ($discountGoods->discount->start_time + $discountGoods->hour * 3600) > time()) {
                        $sku_list[$k]['price'] = $discountGoods->type == DiscountGoods::TYPE_PRICE ? Util::convertPrice($sku_list[$k]['price'] - $discountGoods->price) : Util::convertPrice($sku_list[$k]['price'] * ($discountGoods->ratio / 10));
                    }
                }
            }

        }

        $city_data = []; // { province: [ id, name, city: [ { id, name } ] ] }
        foreach (City::getMap(2) as $p_code => $p) {
            $city_list = [];
            foreach ($p['c_list'] as $c_code => $c) {
                $city_list[] = [
                    'id' => $c_code,
                    'name' => $c['name'],
                ];
            }
            $city_data[] = [
                'id' => $p_code,
                'name' => $p['name'],
                'city' => $city_list,
            ];
        }

        $view = $goods->is_score == 1 ? 'score_view' : 'view';
        //是否一件代发货商品
        if ($goods->supplier_id && $goods->sale_type == Goods::TYPE_SUPPLIER) {
            $is_supplier = 1;
        } else {
            $is_supplier = 0;
        }

        /** $var $similar_list[] 相似产品列表   */
            $similar_list=[];
            $query = Goods::find();
            $query->andWhere(['cid' => $goods->cid]);
            $query->andWhere(['<>', 'id', $goods->id]);
            $query->andWhere(['is_coupon' => Goods::NO]);
            $query->andWhere(['status' => Goods::STATUS_ON]);
            $query->andWhere(['is_pack' => Goods::NO]);
            $query->limit(6);
            /** @var Goods $item */
            foreach($query->each() as $item)
            {
                $similar_list[] = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'desc' => $item->desc,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $item->main_pic,
                    'price' => $item->price,
                    'share_commission' => round($item->share_commission_value * $share_commission / 100, 2),
                    'share_url' => Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $invite_code], true),
                ];
            }
         $similar_list=Util::array_even($similar_list);
        // 用户浏览记录
        /** @var $browse_cid[] 浏览商品一级分类 取3个 */
        if (!Yii::$app->user->isGuest) {
            $browse_cid= Yii::$app->cache->get('browse_'.Yii::$app->user->id);
            if($browse_cid == false || count($browse_cid)<3 && !in_array($goods->cid,$browse_cid))
            {
                $browse_cid[]=$goods->cid;
            }
            if(count($browse_cid) >= 3  && !in_array($goods->cid,$browse_cid))
            {
                array_splice($browse_cid, 0, 1);
                array_push($browse_cid,$goods->cid);
            }
            Yii::$app->cache->set('browse_'.Yii::$app->user->id,$browse_cid);

        }
        if (!empty($user)) {
            $amount = Order::UserLimitGoods($user->id, $id);
        } else {
            $amount = $goods->getAllStock();
        }
        $limit = [
            'is_limit' => empty($goods->is_limit) ? 0 : $goods->is_limit,
            'limit_type' => empty($goods->limit_type) ? 0 : $goods->limit_type,
            'limit_type_str' => empty($goods->limit_type) ? 0 : KeyMap::getValue('goods_limit_type', $goods->limit_type),
            'limit_amount' => empty($goods->limit_amount) ? 0 : $goods->limit_amount,
            'left_limit_amount' => $amount,
        ];


        //视频相关
        $video= [];
        $video['video']=empty($goods->video) ? '' : $goods->video->video;
        $video['cover_image']=empty($goods->video) ? '' : $goods->video->cover_image;
        return $this->render($view, [
            'goods' => $goods,
            'is_have' => $is_have,//是否有礼包兑换券
            'sold_amount' => $sold_amount,//商品销量
            'is_supplier'=>$is_supplier,
            'gav_map' => $gav_map,
            'sku_list' => $sku_list,
            'city_data' => $city_data,
            'gav_list' =>$gav_list,
            'share_commission' => $share_commission,
            'similar_list' => $similar_list,
            'self_buy_ratio' => $self_buy_ratio,
            'user_score' => $user_score,
            'user_status' => $user_status,
            'sale_goods' => $sale_goods,//限时抢购相关
            'video' => $video,
            'limit' => $limit,
        ]);
    }

    /**
     * 商品列表页
     * @return string
     */
    public function actionList()
    {
        return $this->render('list');
    }
    /**
     * 限时抢购商品列表页
     * @return string
     */
    public function actionGoodsSaleList()
    {
        return $this->render('goods_sale_list');
    }

    /**
     * 礼包卡券详情页
     * @return string
     */
    public function actionPackCouponView()
    {
        return $this->render('pack_coupon_view');
    }
    /**
     * 商品列表(积分专区 爆款推荐 今日推荐 优品特邀)
     */
    public function actionGoodsList()
    {
        $type = $this->get('type');
        //分享佣金
        $share_commission = 30;
        $commission_ratio = 0;
        if (!Yii::$app->user->isGuest) {
            $user = User::findOne(Yii::$app->user->id);
            $share_commission = $user->childBuyRatio;
            $commission_ratio = $user->buyRatio;
            $self_buy_ratio = $user->buyRatio;
        }

        $query = Goods::find();
        $query->where(['status' => Goods::STATUS_ON]);
        $view = 'goods_list';
        switch ($type) {
            case $type == 'pack':
                $query->andWhere(['is_pack' => 1]);
                break;
            case $type == 'score':
                $query->andWhere(['is_score' => 1]);
                break;
            case $type == 'socre':
                $query->andWhere(['is_score' => 1]);
                break;
            case $type == 'best':
                $query->andWhere(['is_best' => 1]);
                break;
            case $type == 'index_best':
                $query->andWhere(['is_index_best' => 1]);
                break;
            case $type == 'today':
                $query->andWhere(['is_today' => 1]);
                break;
            case $type == 'commission':
                $query->andWhere(['is_height_commission' => 1]);
                break;
        }
        $query->orderBy('sort desc');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $goods_list = [];
        foreach ($query->each() as $model){
            $goods_list[] = [
                'id' => $model['id'],
                'title' => $model['title'],
                'price' => $model['price'],
                'main_pic' => $model['main_pic'],
                'is_pack' => $model['is_pack'],
                'share_commission' => round($model->share_commission_value * $share_commission /100, 2),
                'self_price' => round($model->share_commission_value * $commission_ratio /100, 2),
                'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $model->id, 'invite_code' => $user->invite_code], true),
            ];
        }
        /** 轮播图广告 */
        $banner_list = [];
        switch ($type) {
            case $type == 'pack':
                //$query->andWhere(['is_pack' => 1]);
                $view = 'goods_pack_list';
                break;
            case $type == 'score':
                foreach (AdLocation::findOne(11)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
                    $banner_list[] = [
                        'id' => $ad->id,
                        'name' => $ad->name,
                        'txt' => $ad->txt,
                        'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
                        'url' => $ad->url,
                        'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                    ];
                }
                break;
            case $type == 'best':
                foreach (AdLocation::findOne(10)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
                    $banner_list[] = [
                        'id' => $ad->id,
                        'name' => $ad->name,
                        'txt' => $ad->txt,
                        'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
                        'url' => $ad->url,
                        'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                    ];
                }
                break;
            case $type == 'index_best':
                //$query->andWhere(['is_index_best' => 1]);
                break;
            case $type == 'today':
                //$query->andWhere(['is_today' => 1]);
                break;
            case $type == 'commission':
                foreach (AdLocation::findOne(12)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
                    $banner_list[] = [
                        'id' => $ad->id,
                        'name' => $ad->name,
                        'txt' => $ad->txt,
                        'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
                        'url' => $ad->url,
                        'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                    ];
                }
                break;
        }
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render($view, [
            'banner_list' => $banner_list,
            'list' => $goods_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 商品评论
     * @return string
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionComment()
    {
        $gid = $this->get('gid');
        $goods = Goods::findOne($gid);
        if (empty($goods)) {
            throw new NotFoundHttpException('没有找到商品信息。');
        }
        if ($goods->status != Goods::STATUS_ON) {
            throw new BadRequestHttpException('商品已下架或删除。');
        }
        $query = GoodsComment::find();
        $query->andWhere(['status' => GoodsComment::STATUS_SHOW]);
        $query->andWhere(['gid' => $gid]);
        $amount_all = $query->count();
        $amount_pic_query = clone $query;
        $amount_new_query = clone $query;
        $amount_low_query = clone $query;
        if ($this->get('img') == 1) {
            $query->andWhere('img_list IS NOT NULL');
        }
        if ($this->get('new') == 1) {
            $query->andWhere(['>=', 'create_time', strtotime(date('Y-m-d 00:00:00'))]);
        }
        if ($this->get('low') == 1) {
            $query->andWhere(['<', 'score', 3]);
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $amount_pic = $amount_pic_query->andWhere('img_list IS NOT NULL')->count();
        $amount_new = $amount_new_query->andWhere(['>=', 'create_time', strtotime(date('Y-m-d 00:00:00'))])->count();
        $amount_low = $amount_low_query->andWhere(['<', 'score', 3])->count();
        $comment_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render('comment', [
            'comment_list' => $comment_list,
            'pagination' => $pagination,
            'gid' => $gid,
            'amount_all' => $amount_all,
            'amount_pic' => $amount_pic,
            'amount_new' => $amount_new,
            'amount_low' => $amount_low,
        ]);
    }

    /**
     * 获取物流列表AJAX接口
     * @return array
     */
    public function actionGetExpress()
    {
        $pid = $this->get('pid');
        $cid = $this->get('cid');
        $gid = $this->get('gid');
        $amount = $this->get('amount', 1);
        $result = GoodsExpress::getGoodsExpress($gid, $amount, $pid, $cid);
        return $result;
    }

    /**
     *获取当前ip地址对应地区AJAX接口
     * @throws \yii\base\Exception
     */
    public function actionGetIpAddress()
    {
        $ipCity = IpCity::findByIp();
        $city = City::findByCode('110000');
        if (!empty($ipCity) && !empty($ipCity->city)) {
            $city = $ipCity->city;
        }
        return ['result' => 'success', 'name' => $city->address(), 'area' => $city->address(true)];
    }
}
