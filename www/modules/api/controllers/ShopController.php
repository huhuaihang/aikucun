<?php

namespace app\modules\api\controllers;

use app\models\Ad;
use app\models\AdLocation;
use app\models\Goods;
use app\models\Merchant;
use app\models\OrderItem;
use app\models\Shop;
use app\models\ShopConfig;
use app\models\ShopDecoration;
use app\models\ShopDecorationItem;
use app\models\ShopGoodsCategory;
use app\models\ShopScore;
use app\models\System;
use app\models\UserFavShop;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;

/**
 * 店铺
 * Class ShopController
 * @package app\modules\api\controllers
 */
class ShopController extends BaseController
{
    /**
     * 店铺详情
     * GET
     * id 店铺编号
     */
    public function actionDetail()
    {
        $id = $this->get('id');
        $shop = Shop::findOne($id);
        if (empty($shop) || $shop->merchant->status != Merchant::STATUS_COMPLETE) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有找到店铺信息。',
            ];
        }
        $user = $this->loginUser();
        $is_fav = 0;
        if (is_array($user) && isset($user['error_code'])) {
            $user = null;
            $is_fav = 0;
        } else {
            if (UserFavShop::find()->andWhere(['uid' => $user->id, 'sid' => $id])->exists()) {
               $is_fav = 1;
            }
        }
        $decoration = ShopDecoration::findBySid($shop->id);
        $image_list = []; // 轮播图列表
        foreach (ShopDecorationItem::listBySid($shop->id) as $item) {
            if ($item->type == ShopDecorationItem::TYPE_SLIDE) {
                $json = json_decode($item->data, true);
                foreach ($json['image_list'] as $image) {
                    $image_list[] = [
                        'url' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $image['url'],
                        'link' => $image['link'],
                    ];
                }
            }
        }
        // 商品列表
        $sort = $this->get('sort', 'create_time');
        $query = Goods::find()->asArray();
        $query->select([
            'goods.id',
            'goods.scid',
            'goods.sid',
            'goods.bid',
            'goods.cid',
            'goods.title',
            'goods.desc',
            'goods.price',
            'goods.stock',
            'main_pic' => "CONCAT('".Yii::$app->params['site_host'] . Yii::$app->params['upload_url']."', `goods`.`main_pic`)",
            'goods.create_time',
        ]);
        $query->andWhere(['{{%goods}}.sid' => $id]);
        $query->andWhere(['{{%goods}}.status' => Goods::STATUS_ON]);
        $query->groupBy('goods.id');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        if ($sort == 'price' || $sort == 'create_time') {
            $query->orderBy($sort .' DESC, id DESC');
            $query->offset($pagination->offset);
            $query->limit($pagination->limit);
            $goods_list = [];
            foreach ($query->each() as $model){
                $amount = intval(OrderItem::find()->where(['gid' => $model['id']])->sum('amount'));
                $goods_list[] = [
                    'id' => $model['id'],
                    'title' => $model['title'],
                    'desc' => $model['desc'],
                    'price' => $model['price'],
                    'main_pic' => $model['main_pic'],
                    'sale_amount' => $amount,
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
                'goods.desc',
                'goods.price',
                'goods.stock',
                'main_pic'=> "CONCAT('".Yii::$app->params['site_host'] . Yii::$app->params['upload_url']."', `goods`.`main_pic`)",
                'goods.create_time',
                'SUM(order_item.amount) as amount',
            ]);
            $query->orderBy('amount DESC, id DESC');
            $query->offset($pagination->offset);
            $query->limit($pagination->limit);
            $goods_list = [];
            foreach ($query->each() as $model){
                $amount = intval(OrderItem::find()->where(['gid' => $model['id']])->sum('amount'));
                $goods_list[] = [
                    'id' => $model['id'],
                    'title' => $model['title'],
                    'desc' => $model['desc'],
                    'price' => $model['price'],
                    'main_pic' => $model['main_pic'],
                    'sale_amount' => $amount,
                ];
            }
        }
        return [
            'shop' => [
                'id' => $shop->id,
                'name' => $shop->name,
                'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] .ShopConfig::getConfig($shop->id, 'logo'),
                'share_url' => Url::to(['/h5/shop/view', 'id' => $shop->id, 'invite_code' => empty($user) ? null : $user->invite_code], true),
            ],
            'is_fav' => $is_fav,
            'decoration' => [
                'header_background_image' => empty($decoration->header_background_image) ? Yii::$app->params['site_host'] . "/images/beijing_02.png" : Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $decoration->header_background_image,
            ],
            'image_list' => $image_list,
            'goods_list' => $goods_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    public function actionRecommendList()
    {
        $id = $this->get('id');
        $shop = Shop::findOne($id);
        if (empty($shop) || $shop->merchant->status != Merchant::STATUS_COMPLETE) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有找到店铺信息。',
            ];
        }
        $query = Goods::find();
        $query->andWhere(['sid' => $id]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        $query->andWhere(['is_recommend' => 1]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('create_time DESC, id Desc');
        $goods_list = [];
        /** @var Goods $goods */
        foreach ($query->each() as $goods) {
            $goods_list[] = [
                'id' => $goods->id,
                'title' => $goods->title,
                'desc' => $goods->desc,
                'price' => $goods->price,
                'main_pic' => !empty($goods->main_pic) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic : '',
            ];
        }
        return [
            'goods_list' => $goods_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 店铺商品分类
     */
    public function actionCategory()
    {
        $id = $this->get('id');
        if (empty($id)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误。',
            ];
        }
        $model_list = ShopGoodsCategory::find()
            ->select(['id', 'name', 'sid'])->asArray()->where(['sid' => $id])
            ->andWhere(['<>', 'status', ShopGoodsCategory::STATUS_DEL])
            ->orderBy('sort DESC')->all();
        return [
            'category_list' => $model_list,
        ];
    }
}
