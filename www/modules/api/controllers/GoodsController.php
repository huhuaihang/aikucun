<?php

namespace app\modules\api\controllers;

use app\models\Ad;
use app\models\AdLocation;
use app\models\City;
use app\models\Discount;
use app\models\DiscountGoods;
use app\models\Goods;
use app\models\GoodsAttrValue;
use app\models\GoodsBarrageRules;
use app\models\GoodsCategory;
use app\models\GoodsComment;
use app\models\GoodsCommentReply;
use app\models\GoodsExpress;
use app\models\GoodsSku;
use app\models\IpCity;
use app\models\KeyMap;
use app\models\Order;
use app\models\OrderItem;
use app\models\Package;
use app\models\System;
use app\models\User;
use app\models\UserAddress;
use app\models\UserFavGoods;
use app\models\UserSearchHistory;
use app\models\Util;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;

/**
 * 商品相关
 * Class GoodsController
 * @package app\modules\api\controllers
 */
class GoodsController extends BaseController
{
    /**
     * 商品详情
     * GET
     * id 商品编号
     */
    public function actionDetail()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            $user = null;
        }


        $id = $this->get('id');
        $model = Goods::findOne($id);
        if (empty($model)) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '没有找到商品信息。',
            ];
        }
        if ($model->status != Goods::STATUS_ON) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_PUBLIC,
                'message' => '商品已下架。',
            ];
        }
        $self_ratio = 0;
        if (!empty($user)) {
            $self_ratio = $user->buyRatio;
            if ($user->status == User::STATUS_WAIT) {
                $self_ratio = 30;
                $self_ratio = 0;
            }
        }

        #是否激活  激活了  则显示自己邀请码 未激活显示上级邀请码
        $code = null;
        if (!empty($user)) {
            if ($user->status == User::STATUS_WAIT) {
                if ($user->parent && $user->parent->status == User::STATUS_OK) {
                    $code = $user->parent->invite_code;
                }
            } else {
                $code = $user->invite_code;
            }
            $amount = Order::UserLimitGoods($user->id, $model->id);
        } else {
            $amount = $model->getAllStock();
        }

        $limit = [
            'is_limit' => empty($model->is_limit) ? 0 : $model->is_limit,
            'limit_type' => empty($model->limit_type) ? 0 : $model->limit_type,
            'limit_type_str' => empty($model->limit_type) ? 0 : KeyMap::getValue('goods_limit_type', $model->limit_type),
            'limit_amount' => empty($model->limit_amount) ? 0 : $model->limit_amount,
            'left_limit_amount' => $amount,
        ];
        $json = (object)$limit;

        // 商品基本资料
        $goods = [
            'id' => $model->id,
            'sid' => $model->sid,
            'title' => $model->title,
            'keywords' => $model->keywords,
            'desc' => $model->desc,
            'bill' => $model->bill,
            'cover_image' => empty($model->video) ? '' : $model->video->cover_image,
            'video' => empty($model->video) ? '' : $model->video->video,
            'price' => Util::convertPrice($model->price),
            'stock' => $model->getAllStock(),
            'self_price' => Util::convertPrice($model->share_commission_value * $self_ratio / 100),
            'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $model->main_pic,
            'detail_pic_list' => array_map(function ($item) {return Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $item;}, $model->getDetailPicList()),
            'share_url' => Url::to(['/h5/goods/view', 'id' => $model->id, 'invite_code' => $code], true),
            'deliver_from_city' => $model->shop->city->address(),
            'is_score' => $model->is_score,
            'score' => $model->score,
            'score_ratio' => '(100积分=' . System::getConfig('score_ratio') . '元)',
            'score_money' => Util::convertPrice($model->score * System::getConfig('score_ratio') / 100),
            'limit' => $json,
            'is_limit' => empty($model->is_limit) ? 0 : $model->is_limit
        ];
        // 商品销量
        $goods['sale_amount'] = $model->getSaleAmount();
        //商品服务
        $goods['service_list'] = $model->serviceList;
        // 商品属性
        $gav_list = GoodsAttrValue::find()
            ->joinWith('goods_attr')
            ->asArray()
            ->select([
                'aid' => '{{goods_attr_value}}.aid',
                'name' => '{{%goods_attr}}.name',
                'value' => '{{%goods_attr_value}}.value',
            ])
            ->andWhere(['{{%goods_attr_value}}.gid' => $goods['id'], '{{%goods_attr}}.is_sku' => 0])
            ->all();
        array_walk($gav_list, function (&$item) {
            $item['image'] = !empty($item['image']) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $item['image'] : '';
            unset($item['goods_attr']);
        });
        $gav_map = [];
        foreach ($gav_list as $gav) {
            if (!isset($gav_map[$gav['aid']])) {
                $gav_map[$gav['aid']] = [
                    'name' => $gav['name'],
                    'v_list' => [],
                ];
            }
            $gav_map[$gav['aid']]['v_list'][] = [
                'value' => $gav['value'],
            ];
        }
        $gav_list = array_values($gav_map);
        // 规格属性
        $sku_gav_list = GoodsAttrValue::find()
            ->joinWith('goods_attr')
            ->asArray()
            ->select([
                'id' => '{{goods_attr_value}}.id',
                'aid' => '{{goods_attr_value}}.aid',
                'name' => '{{%goods_attr}}.name',
                'value' => '{{%goods_attr_value}}.value',
                'image' => '{{%goods_attr_value}}.image',
            ])
            ->andWhere(['{{%goods_attr_value}}.gid' => $goods['id'], '{{%goods_attr}}.is_sku' => 1])
            ->all();
        array_walk($sku_gav_list, function (&$item) {
            $item['image'] = !empty($item['image']) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $item['image'] : '';
            unset($item['goods_attr']);
        });
        $sku_gav_map = [];
        foreach ($sku_gav_list as $gav) {
            if (!isset($sku_gav_map[$gav['aid']])) {
                $sku_gav_map[$gav['aid']] = [
                    'name' => $gav['name'],
                    'v_list' => [],
                ];
            }
            $sku_gav_map[$gav['aid']]['v_list'][] = [
                'id' => $gav['id'],
                'value' => $gav['value'],
                'image' => $gav['image'],
            ];
        }
        $sku_gav_list = array_values($sku_gav_map);
        // 商品规格
        $sku_list = GoodsSku::find()
            ->asArray()
            ->select([
                'id',
                'key',
                'key_name',
                'market_price',
                'price',
                'stock',
                'commission',
                'img',
            ])
            ->andWhere(['gid' => $goods['id']])
            ->all();
        // 运费
        $area = '';
        if (!empty($user)) {
            /** @var UserAddress $userAddress */
            $userAddress = UserAddress::find()
                ->andWhere(['uid' => $user->id, 'status' => UserAddress::STATUS_OK])
                ->orderBy('is_default DESC, id DESC')
                ->one();
            if (!empty($userAddress)) {
                $area = $userAddress->area;
            }
        }
        // TODO：参数提供地址（提供经纬度使用百度地图API获取城市信息）
        if (empty($p_area)) {
            // 尝试根据IP地址获取
            $ipCity = IpCity::findByIp();
            if (!empty($ipCity)) {
                $area = $ipCity->city->code;
            }
        }
        if (empty($p_area)) {
            // 实在无法确定地址，直接使用发货地址
            $area = $model->shop->area;
        }
        $deliver_to_city = City::findByCode($area);
        if (empty($deliver_to_city)) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '没有找到编号为[' . $area . ']的城市信息。',
            ];
        }
        $goods['deliver_to_city'] = $deliver_to_city->address();
        $code_list = $deliver_to_city->address(true);
        $r = GoodsExpress::getGoodsExpress($goods['id'], 1, $code_list[0], count($code_list) > 1 ? $code_list[1] : '');
        if (isset($r['message'])) {
            $goods['express_list'] = []; // 没有物流信息或不发货
        } else {
            $express = $r['deliver_list'][0];
            $goods['express_list'] = [
                [
                    'name' => $express['express_name'],
                    'fee' => $express['fee'],
                ]
            ];
        }
        $goods['is_fav'] = 0;
        if (!empty($user)) {
            $fav_goods = UserFavGoods::find()->where(['uid' => $user->id, 'gid' => $id])->one();
            if (!empty($fav_goods)) {
                $goods['is_fav'] = 1;
            }
        }

        //分享佣金
        $share_commission = 30;
        if ($user) {
            $share_commission = $user->childBuyRatio;
        }

        $goods['share_commission'] = Util::convertPrice(sprintf('%.2f',$model->share_commission_value * $share_commission /100));
        $goods['is_pack'] = $model->is_pack;
        //限时抢购相关
        $goods['is_discount'] =0;
        /** @var DiscountGoods $discountGoods */
        $discountGoods = DiscountGoods::find()
            ->joinWith('discount')
            ->andWhere(['status' => Discount::STATUS_RUNNING])
            ->andWhere(['<=', 'start_time', time()])
            ->andWhere(['>=', 'end_time', time()])
            ->andWhere(['gid' => $goods['id']])
            ->orderBy(['{{%discount}}.id' => SORT_ASC])
            ->one();

        if(!empty($discountGoods))
        {
            if(empty($discountGoods->hour) || ($discountGoods->discount->start_time + $discountGoods->hour * 3600) > time())
            {
            $goods['end_time']=empty($discountGoods->hour) ? $discountGoods->discount->end_time : $discountGoods->discount->start_time + $discountGoods->hour * 3600;
            if (!empty($model->skuList)) {
                $market_price = empty($model->skuList[0]->market_price) ? $model->price : $model->skuList[0]->market_price;
            } else {
                $market_price = $model->price;
            }
            $goods['market_price'] = $market_price;
            $goods['price'] = $discountGoods->type == DiscountGoods::TYPE_PRICE ? Util::convertPrice($goods['price'] - $discountGoods->price) : Util::convertPrice($goods['price'] * ($discountGoods->ratio/10));
            $goods['is_discount'] =1;
            $goods['discount_str'] = $discountGoods->type == DiscountGoods::TYPE_PRICE ? '减' : '折';
            $sold_amount  = $discountGoods->getSaleAmount();// 已卖出的份数
            if(empty($sold_amount))
            {
                $sold_amount=0;
            }
            $goods['stock'] =$discountGoods->amount - $sold_amount;
            $goods['is_sold_out'] = $discountGoods->amount - $sold_amount > 0 ? 1 : 0;//0已售光

            }

        }
        if(!empty($sku_list))
        {
            foreach ($sku_list as $k=>$sku)
            {
                $sku_list[$k]['img'] = !empty($sku['img']) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $sku['img'] :  $goods['main_pic'];
               if($sku['commission']!='')
               {
                   $sku_list[$k]['share_commission']=Util::convertPrice(sprintf('%.2f',$sku['commission'] * $share_commission /100));
               }else
               {
                   $sku_list[$k]['share_commission']=Util::convertPrice(sprintf('%.2f',$model->share_commission_value * $share_commission /100));
               }
               unset($sku_list[$k]['commission']);
                if(!empty($discountGoods)) {
                    $sku_list[$k]['price'] = $discountGoods->type == DiscountGoods::TYPE_PRICE ? Util::convertPrice($sku_list[$k]['price'] - $discountGoods->price) : Util::convertPrice($sku_list[$k]['price'] * ($discountGoods->ratio/10));;
                }
            }

        }
        //是否一件代发货商品
        if ($model->supplier_id && $model->sale_type == Goods::TYPE_SUPPLIER) {
            $goods['is_supplier'] = 1;
        } else {
            $goods['is_supplier'] = 0;
        }

        // 最有一条评论
        $comment_list = [];
        /** @var GoodsComment $commentModel */
        $commentModel = GoodsComment::find()
            ->andWhere(['gid' => $goods['id'], 'pid' => null, 'status' => GoodsComment::STATUS_SHOW])
            ->orderBy('create_time DESC')
            ->one();
        if (!empty($commentModel)) {
            $comment = [
                'user' => [
                    'nickname' => $commentModel->is_anonymous == 1 ? '匿名用户' : $commentModel->user->nickname,
                    'avatar' => $commentModel->user->getRealAvatar(true),
                ],
                'create_time' => $commentModel->create_time,
                'sku_key_name' => $commentModel->sku_key_name,
                'content' => $commentModel->content,
                'score' => $commentModel->score,
                'img_list' => array_map(function ($item) {
                    return Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $item;
                }, $commentModel->getImgList()),
            ];
            $append_list = GoodsComment::find()->select(['id', 'create_time', 'content'])->where(['pid' => $commentModel['id']])->asArray()->one();
            $replay_list = GoodsCommentReply::find()->select(['id', 'content', 'create_time'])->where(['cid' => $commentModel['id']])->asArray()->one();
            $comment_list[] = [
                                'comment' => $comment,
                                'append' => empty($append_list) ? new \stdClass() : $append_list,
                                'replay' => empty($replay_list) ? new \stdClass() : $replay_list,
                            ];
        }

        // 用户浏览记录
        /** @var $browse_cid[] 浏览商品所属分类 取3个 */
        if (!empty($user)) {
            $browse_cid= Yii::$app->cache->get('browse_'.$user->id);
            if($browse_cid == false || count($browse_cid) < 3 && !in_array($model->cid,$browse_cid))
            {
            $browse_cid[]=$model->cid;
            }
            if(count($browse_cid) >= 3  && !in_array($model->cid,$browse_cid))
            {
            array_splice($browse_cid, 0, 1);
            array_push($browse_cid,$model->cid);
            }
            Yii::$app->cache->set('browse_'.$user->id,$browse_cid);

        }

        return [
            'goods' => $goods,
            'gav_list' => $gav_list,
            'sku_gav_list' => $sku_gav_list,
            'sku_list' => $sku_list,
            'comment_list' => $comment_list,
            'detail_url' => Url::to(['/public/goods-content'], true),
            'mobile' => '18006490976',
        ];
    }

    /**
     * 商品列表
     */
    public function actionList()
    {
        $title = $keywords = $this->get('keywords');
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            $user = null;
        }
        if (!empty($keywords) && !empty($user)) {
            UserSearchHistory::deleteAll(['uid' => $user->id, 'keyword' => $keywords]);
            $search = new UserSearchHistory();
            $search->uid = $user->id;
            $search->keyword = $keywords;
            $search->create_time = time();
            $search->save();
        }
        $sort = $this->get('sort', 'create_time');
        $order = $this->get('order', 'DESC');
        $category = $this->get('category');
        if ($order != 'DESC' && $order != 'ASC') {
            $order = 'DESC';
        }
        if (!empty($category)) {
            $title = GoodsCategory::findOne($category)['name'];
        }
        $query = Goods::find();
        $query->select([
            'goods.id',
            'goods.scid',
            'goods.sid',
            'goods.bid',
            'goods.cid',
            'goods.title',
            'goods.price',
            'goods.stock',
            'goods.is_pack',
            'main_pic' => "CONCAT('".Yii::$app->params['site_host'] . Yii::$app->params['upload_url']."', `goods`.`main_pic`)",
            'goods.create_time',
            'goods.is_limit',
            'goods.limit_type',
            'goods.limit_amount'
        ]);
        $query->andWhere(['{{%goods}}.status' => Goods::STATUS_ON]);
        $query->andWhere(['{{%goods}}.is_coupon' => Goods::NO]);
        $query->andWhere(['{{%goods}}.is_pack_redeem' => Goods::NO]);
        $query->joinWith(['goods_brand', 'shop']);
        $query->andFilterWhere(['{{%goods}}.cid' => $category]);
        $query->andFilterWhere(['or',
            ['like', '{{%goods}}.title', $keywords],
            ['like', '{{%goods}}.desc', $keywords],
            ['like', '{{%shop}}.name', $keywords],
            ['like', '{{%goods_brand}}.name', $keywords]
        ]);
        if (!empty($this->get('sid'))) {
            $query->andFilterWhere(['{{%goods}}.sid' => $this->get('sid')]);
        }
        $query->andFilterWhere(['{{%goods}}.scid' => $this->get('scid')]);
        $query->groupBy('goods.id');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        if ($sort == 'price' || $sort == 'create_time') {
            $query->orderBy($sort . " " . $order . ', id DESC');
            $query->offset($pagination->offset);
            $query->limit($pagination->limit);
            $goods_list = [];
            foreach ($query->each() as $model){
                $limit = [
                    'is_limit' => empty($model['is_limit']) ? 0 : $model['is_limit'],
                    'limit_type' => empty($model['limit_type']) ? 0 : $model['limit_type'],
                    'limit_type_str' => empty($model['limit_type']) ? 0 : KeyMap::getValue('goods_limit_type', $model['limit_type']),
                    'limit_amount' => empty($model['limit_amount']) ? 0 : $model['limit_amount'],
                ];
                $json = (object)$limit;
                /** @var $model Goods*/
//                $amount = intval(OrderItem::find()->where(['gid' => $model['id']])->sum('amount'));
                $goods_list[] = [
                    'id' => $model['id'],
                    'title' => $model['title'],
                    'price' => $model['price'],
                    'main_pic' => $model['main_pic'],
                    'sale_amount' => $model->getSaleAmount(),
                    'is_pack' => $model['is_pack'],
                    'limit' => $json
                ];
            }
        } else {
            $query->joinWith(['orderItemList']);
            $query->select([
                'goods.id',
                'goods.sid',
                'goods.bid',
                'goods.cid',
                'goods.title',
                'goods.price',
                'goods.stock',
                'goods.is_pack',
                'main_pic'=> "CONCAT('".Yii::$app->params['site_host'] . Yii::$app->params['upload_url']."', `goods`.`main_pic`)",
                'goods.create_time',
                'SUM(order_item.amount) as amount',
                'goods.is_limit',
                'goods.limit_type',
                'goods.limit_amount',
            ]);
            $query->orderBy('amount ' . $order . ', id DESC');
            $query->offset($pagination->offset);
            $query->limit($pagination->limit);
            $goods_list = [];
            foreach ($query->each() as $model){
                $limit = [
                    'is_limit' => empty($model['is_limit']) ? 0 : $model['is_limit'],
                    'limit_type' => empty($model['limit_type']) ? 0 : $model['limit_type'],
                    'limit_type_str' => empty($model['limit_type']) ? 0 : KeyMap::getValue('goods_limit_type', $model['limit_type']),
                    'limit_amount' => empty($model['limit_amount']) ? 0 : $model['limit_amount'],
                ];
                $json = (object)$limit;
                /** @var $model Goods*/
//                $amount = intval(OrderItem::find()->where(['gid' => $model['id']])->sum('amount'));
                $goods_list[] = [
                    'id' => $model['id'],
                    'title' => $model['title'],
                    'price' => $model['price'],
                    'main_pic' => $model['main_pic'],
                    'sale_amount' => $model->getSaleAmount(),
                    'is_pack' => $model['is_pack'],
                ];
            }
        }
        return [
            'goods_list' => $goods_list,
            'title' => !empty($title) ? $title : '商品列表',
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 限时抢购列表
     */
    public function actionDiscountGoodsList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            $user = null;
        }
        /** 轮播图广告 */
        $banner_list = [];
        $ad_id = (Yii::$app->params['site_host'] == 'http://yun.yuntaobang.cn') ? 36 :38;
        $AdLocation=AdLocation::findOne($ad_id);
        if(!empty($AdLocation)){
        foreach ($AdLocation->getActiveAdList()->each() as $ad) {
            /** @var Ad $ad */
            if($ad->sort == 2) {
                $banner_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
        }
        }
        //分享佣金
        $share_commission = 30;
        $commission_ratio = 0;
        if (!empty($user)) {
            $share_commission = $user->childBuyRatio;
            $commission_ratio = $user->buyRatio;
        }
        // 限时抢购
        $sale_list = [];
        /** @var $discount Discount */
        $discount = Discount::find()
            ->andWhere(['status' => Discount::STATUS_RUNNING ])
            ->andWhere(['<=','start_time',time()])
            ->andWhere(['>=','end_time',time()])
            ->orderBy(['id' => SORT_ASC])
            ->one();
        if (!empty($discount)) {
            $did = $discount->id;
            $query = DiscountGoods::find();
            $query->joinWith('goods');
            $query->andWhere(['goods.status' => Goods::STATUS_ON]);
            $query->andWhere(['did' => $did]);
            $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
            $query->offset($pagination->offset);
            $query->limit($pagination->limit);
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
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                    'cost_price' => Util::convertPrice($market_price),
                    'price' => $dis_goods->type == DiscountGoods::TYPE_PRICE ? Util::convertPrice($goods->price - $dis_goods->price) : Util::convertPrice($goods->price * ($dis_goods->ratio/10)),
                    'share_commission' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $share_commission / 100)),
                    'self_price' => Util::convertPrice(sprintf('%.2f', $goods->share_commission_value * $commission_ratio / 100)),
                    'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $goods->id, 'invite_code' => $user->invite_code], true),
                    'end_time' =>empty($dis_goods->hour) ? $discount->end_time : $discount->start_time + $dis_goods->hour * 3600 ,//结束时间
                    'amount' =>  $dis_goods->amount,//该商品活动数量
                    'sold_amount' => empty($sold_amount) ? 0 : $sold_amount ,//该商品活动已售数量

                ];

            }

        }else{
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '活动暂未开始。',
            ];
        }
        return [
            'banner_list' => $banner_list,
            'goods_list' => $sale_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 限时抢购海报商品列表
     */
    public function actionDiscountGoodsPoster()
    {
        // 限时抢购
        $goods_list = [];
        /** @var $discount Discount */
        $discount = Discount::find()
            ->andWhere(['status' => Discount::STATUS_RUNNING ])
            ->andWhere(['<=','start_time',time()])
            ->andWhere(['>=','end_time',time()])
            ->orderBy(['id' => SORT_ASC])
            ->one();
        if (!empty($discount)) {
            $did = $discount->id;

            $query = DiscountGoods::find();
            $query->joinWith('goods goods');
            $query->andWhere(['goods.status' => Goods::STATUS_ON]);
            $query->andWhere(['did' => $did]);
            $query->limit(4);
            $query->orderBy('id desc');
            /** @var Goods $goods
             * @var DiscountGoods $dis_goods
             */
            foreach ($query->each() as $dis_goods) {
                if(!empty($dis_goods->hour))
                {
                    if($discount->start_time + $dis_goods->hour * 3600 < time())
                    {
                       continue;
                    }
                }
                if(count($goods_list) <4)
                {
                $goods = Goods::findOne($dis_goods->gid);
                if (!empty($goods->skuList)) {
                    $market_price = empty($goods->skuList[0]->market_price) ? $goods->price : $goods->skuList[0]->market_price;
                } else {
                    $market_price = $goods->price;
                }
                $goods_list[] = [
                    'id' => $goods->id,
                    'title' => $goods->title,
                    'desc' => $goods->desc,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                    'cost_price' => Util::convertPrice($market_price),
                    'price' => $dis_goods->type == DiscountGoods::TYPE_PRICE ? Util::convertPrice($goods->price - $dis_goods->price) : Util::convertPrice($goods->price * ($dis_goods->ratio/10))
                ];
                }

            }
        }
        else{
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '活动暂未开始。',
            ];
        }
        return [
            'end_time' => $discount->end_time,
            'goods_list' => $goods_list,
            'code_url' =>  Yii::$app->params['site_host'].'/h5/goods/goods-sale-list',
        ];
    }

    /**
     * 商品列表(积分专区 爆款推荐 今日推荐 优品特邀)
     */
    public function actionGoodsList()
    {
        $type = $this->get('type');
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            $user = null;
        }
        //分享佣金
        $share_commission = 30;
        $commission_ratio = 0;
        if (!empty($user)) {
            $share_commission = $user->childBuyRatio;
            $commission_ratio = $user->buyRatio;
        }

        $query = Goods::find();
        $query->where(['status' => Goods::STATUS_ON]);
        switch ($type) {
            case $type == 'pack':
                $query->andWhere(['is_pack' => 1]);
                break;
            case $type == 'score':
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
        if($type != 'pack')
        {
            $query->andWhere(['is_pack' => 0]);
        }
        if($type == 'best' || $type == 'index_best' || $type == 'today' )
        {
//            $query->andWhere(['is_score' => 0]);
            $query->andWhere(['is_pack' => 0]);
            $query->andWhere(['is_coupon' => 0]);
        }
        $query->orderBy('create_time desc,sort desc');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->offset($pagination->offset);
        $query->limit($pagination->limit);
        $goods_list = [];
        /** @var Goods $model */
        foreach ($query->each() as $model) {
            if ($model['is_pack'] == 1 && !empty($model['pack_pic']) && $type == 'pack') {
                $main_pic =  Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] .  $model['pack_pic'];
            } else {
                $main_pic =  Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] .  $model['main_pic'];
            }
            $goods_list[] = [
                'id' => $model['id'],
                'title' => $model['title'],
                'desc' => $model['desc'],
                'price' => $model['price'],
                'main_pic' => $main_pic,
                'is_pack' => $model['is_pack'],
                'is_score' => $model['is_score'],
                'score' => $model['score'],
                'score_money' => round(System::getConfig('score_ratio') * $model['score'] / 100, 2),
                'share_commission' => Util::convertPrice($model->share_commission_value * $commission_ratio / 100),
                'self_price' => Util::convertPrice($model->share_commission_value * $commission_ratio / 100),
                'share_url' => empty($user) ? "" : Url::to(['/h5/goods/view', 'id' => $model->id, 'invite_code' => $user->invite_code], true),
                'sale_amount' => $model->getSaleAmount(),
            ];
        }

        /** 轮播图广告 */
        $banner_list = [];
        switch ($type) {
            case $type == 'pack':
                //$query->andWhere(['is_pack' => 1]);
                break;
            case $type == 'score':
                $title='积分专区';
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
                $title='爆款推荐';
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
                $title='佣金专场';
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
        return [
            'banner_list' => $banner_list,
            'goods_list' => $goods_list,
            'title' => !empty($title) ? $title : '商品列表',
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 获取单商品 运费模板列表
     * get
     * {
     *      pid 省编号
     *      cid 市编号
     *      gid 商品编号
     *      amount 商品数量
     * }
     */
    public function actionGetExpress()
    {
        $area = $this->get('area');
        if (empty($area)) {
            return [
                'error_code' => ErrorCode::GOODS_EXPRESS_NO_AREA,
                'message' => '请选择收货地址。',
            ];
        }
        $deliver_to_city = City::findByCode($area);
        if (empty($deliver_to_city)) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '没有找到编号为[' . $area . ']的城市信息。',
            ];
        }
        $goods['deliver_to_city'] = $deliver_to_city->address();
        $code_list = $deliver_to_city->address(true);
        $gid = $this->get('gid');
        if (empty($gid)) {
            return [
                'error_code' => ErrorCode::GOODS_EXPRESS_NO_GOODS,
                'message' => '请选择商品。',
            ];
        }
        $amount = $this->get('amount', 1);
        $result = GoodsExpress::getGoodsExpress($gid, $amount, $code_list[0], count($code_list) > 1 ? $code_list[1] : '');
        if (!empty($result['message'])) {
            return [
                'error_code' => ErrorCode::GOODS_EXPRESS_NOT_FOUND,
                'message' => $result['message'],
            ];
        }
        return [
            'express_list' => $result['deliver_list']
        ];
    }

    /**
     * 获取多商品 物流运费
     * post
     * {
     *      goods_list:[{"gid":"9","amount":"1"},{"gid":"10","amount":"1"}]  gid 商品编号 amount数量
     *      area 收货地址编码
     * }
     *
     */
    public function actionGetMultiGoodsExpress()
    {
        $json = $this->checkJson([
            [['goods_list', 'area'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $goods_list = $json['goods_list'];
        if (empty($json['area'])) {
            return [
                'error_code' => ErrorCode::GOODS_EXPRESS_NO_AREA,
                'message' => '请选择收货地址。',
            ];
        }
        $deliver_to_city = City::findByCode($json['area']);
        if (empty($deliver_to_city)) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '没有找到编号为[' . $json['area'] . ']的城市信息。',
            ];
        }
        $goods['deliver_to_city'] = $deliver_to_city->address();
        $code_list = $deliver_to_city->address(true);
        if (empty($code_list[0])) {
            return [
                'error_code' => ErrorCode::GOODS_EXPRESS_NO_AREA,
                'message' => '请选择收货地址。',
            ];
        }
        if (empty($goods_list)) {
            return [
                'error_code' => ErrorCode::GOODS_EXPRESS_NO_GOODS,
                'message' => '请选择结算商品。',
            ];
        }
        $result = GoodsExpress::multiGoodsExpress($goods_list, $code_list[0], count($code_list) > 1 ? $code_list[1] : '');
        if (!empty($result['message'])) {
            return [
                'error_code' => ErrorCode::GOODS_EXPRESS_NOT_FOUND,
                'message' => $result['message'],
            ];
        }
        return [
            'fee' => $result['fee']
        ];
    }

    /**
     * 商品评论
     * get
     * {
     *      gid 商品ID
     *      img 晒图
     *      new 最新
     *      append 追评
     * }
     */
    public function actionCommentList()
    {
        $gid = $this->get('gid');
        $goods = Goods::findOne($gid);
        if (empty($goods)) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '没有找到商品信息。',
            ];
        }
        if ($goods->status != Goods::STATUS_ON) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_PUBLIC,
                'message' => '商品已下架或删除。',
            ];
        }
        $query = GoodsComment::find();
        $query->andWhere(['status' => GoodsComment::STATUS_SHOW]);
        $query->andWhere(['gid' => $gid]);
        $amount_all = $query->count();
        $all_score_query = clone $query;
        $all_score_query->andWhere('pid IS Null');
        $all_score = $all_score_query->average('score');
        $all_score = ($all_score == 0) ? 5 : $all_score;
        if ($this->get('img') == 1) {
            $query->andWhere('img_list IS NOT NULL');
        }
        if ($this->get('append') == 1) {
            $query->andWhere('pid IS NOT Null');
        } else {
            $query->andWhere('pid IS Null');
        }
        if ($this->get('new') == 1) {
            $query->orderBy('create_time DESC');
        } else {
            $query->orderBy('score DESC, create_time DESC');
        }
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        /** @var GoodsComment[] $comment_list */
        $comment_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();

        $list = [];
        foreach ($comment_list as $comment) {
            $append_list = GoodsComment::find()->select(['id', 'create_time', 'content'])->where(['pid' => $comment['id']])->asArray()->one();
            $replay_list = GoodsCommentReply::find()->select(['id', 'content', 'create_time'])->where(['cid' => $comment['id']])->asArray()->one();
            $img_list = [];
            if (!empty($comment->img_list)) {
                $img_list = json_decode($comment->img_list, true);
                $img_list = array_map(function ($v) {
                    return Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $v;
                }, $img_list);
            }
            $list[] = [
                'comment' => [
                    'user' => [
                        'nickname' => ($comment->is_anonymous == 1) ? '匿名用户' : (empty($comment->user->nickname) ? '匿名用户' : $comment->user->nickname),
                        'avatar' => $comment->user->getRealAvatar(true),
                    ],
                    'score' => $comment->score,
                    'sku_key_name' => $comment->sku_key_name,
                    'content' => $comment['content'],
                    'img_list' => $img_list,
                    'create_time' => $comment['create_time'],
                ],
                'append' => empty($append_list) ? new \stdClass() : $append_list,
                'replay' => empty($replay_list) ? new \stdClass() : $replay_list,
            ];
        }
        return [
            'comment_list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
            'comment_info' => [
                'gid' => $gid,
                'amount_all' => $amount_all,
                'all_score' => ceil($all_score),
                'goods_score' => round($all_score * 20) . "%",
            ],
        ];
    }

    /**
     * 获取套餐卡列表接口
     */
    public function actionPackageList()
    {
        $list = [];
        $query = Package::find()
            ->andWhere(['status' => Package::STATUS_SHOW])
            ->orderBy('id ASC');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->limit($pagination->limit)->offset($pagination->offset);
        /** @var Package $model */
        foreach ($query->each() as $model) {
            $list[] = [
                'id' => $model->id,
                'name' => $model->name,
                'count' => $model->count,
                'price' => sprintf('%d', $model->price),
                'package_price' => sprintf('%d', $model->package_price),
            ];
        }
        return[
            'list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 激活礼包商品列表
     */
    public function actionPackList()
    {
        $query = Goods::find();
        $query->where(['is_pack' => 1]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        $query->andWhere(['is_pack_redeem' => Goods::NO]);
        $goods_list = [];
        /** @var Goods $model */
        foreach ($query->each() as $model){
            /** @var GoodsSku $sku */
            $sku = GoodsSku::find()->where(['gid' => $model->id])->exists();
            $goods_list[] = [
                'id' => $model['id'],
                'title' => $model['title'],
                'price' => $model['price'],
                'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $model['main_pic'],
                'is_have_sku' => ($sku == true) ? 1 : 0,
            ];
        }

        $tip = System::getConfig('active_pack_list_tip');

        return [
            'list' => $goods_list,
            'tip' => $tip,
        ];
    }
    /**
     * 获取礼包兑换券详情接口
     */
    public function actionPackRedeemDetail()
    {
        $pack_redeem_goods = Goods::find()->where(['is_pack' => 1 ,'is_pack_redeem' => 1,'status' => Goods::STATUS_ON])->one();
        /** @var $pack_redeem_goods Goods*/
        if(empty($pack_redeem_goods))
        {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '没有找到卡券商品信息。',
            ];
        }
        return[
            'detail'=>[
                'id' => $pack_redeem_goods->id,
                'price' => $pack_redeem_goods->price,
                'content' => $pack_redeem_goods->content,
            ],
        ];

    }

    /**
     * 获取商品弹幕
     */
    public function actionBarrage()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            $user = null;
        }
        $barrage=[];//弹幕内容
        /** @var $xn_user User  弹幕随机用户*/
        while (true) {
            $query = User::find();
            $query->alias('r1');
            $query->join('JOIN', "(SELECT ROUND(RAND() * ((" . User::find()->select('MAX(id)')->createCommand()->getRawSql() . ") - (" . User::find()->select('MIN(id)')->createCommand()->getRawSql() . ")) + (" . User::find()->select('MIN(id)')->createCommand()->getRawSql() . ")) AS id2) AS r2");
            $query->where("r1.id >= r2.id2");
            if (!empty($user)) {
                $query->andwhere(['<>', 'r1.id', $user->id]);
            }
            $query->andWhere(['not', ['r1.nickname' => null]]);
            $query->andWhere(['not', ['r1.avatar' => null]]);
            $query->orderBy([
                'r1.id' => SORT_ASC
            ]);
            $xn_user = $query->one();
            if (empty($xn_user)) {
                continue;
            }
            break;
        }
        $i=1;
        while (true) {
            /** @var $barrage GoodsBarrageRules 弹幕随机内容 */
            $query2 = GoodsBarrageRules::find();
            $query2->alias('r1');
            $query2->join('JOIN', "(SELECT ROUND(RAND() * ((" . GoodsBarrageRules::find()->select('MAX(id)')->createCommand()->getRawSql() . ") - (" . GoodsBarrageRules::find()->select('MIN(id)')->createCommand()->getRawSql() . ")) + (" . GoodsBarrageRules::find()->select('MIN(id)')->createCommand()->getRawSql() . ")) AS id2) AS r2");
            $query2->where(['r1.status' => GoodsBarrageRules::STATUS_OK]);
            $query2->andwhere("r1.id >= r2.id2 ");
            $query2->orderBy([
                'r1.id' => SORT_ASC
            ]);

            $barrage = $query2->one();
            //$aa=$query2->createCommand()->getRawSql();

            if($i==10)
            {
                return [
                    'info' => [
                        'avatar' => $xn_user->getRealAvatar(true),
                        'nickname' => $xn_user->nickname,
                        'title' => '正在浏览商品',
                    ]
                ];
            }
            if (empty($barrage)) {
                $i++;
                continue;
            }
            break;
        }



        return [
            'info' => [
                'avatar' => $xn_user->getRealAvatar(true),
                'nickname' => $xn_user->nickname,
                'title' => $barrage->title,
            ]
        ];

    }


}
