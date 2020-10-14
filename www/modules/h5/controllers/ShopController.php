<?php

namespace app\modules\h5\controllers;

use app\models\Goods;
use app\models\Merchant;
use app\models\Shop;
use app\models\ShopDecoration;
use app\models\ShopDecorationItem;
use app\models\ShopGoodsCategory;
use app\models\ShopScore;
use app\models\UserFavShop;
use Yii;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * 店铺控制器
 * Class ShopController
 * @package app\modules\h5\controllers
 */
class ShopController extends BaseController
{
    /**
     * 店铺信息
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        $id = $this->get('id');
        $keywords = $this->get('keywords');
        $shop = Shop::findOne($id);
        if (empty($shop) || $shop->merchant->status != Merchant::STATUS_COMPLETE) {
            throw new NotFoundHttpException('没有找到店铺信息。');
        }
        $decoration = ShopDecoration::findBySid($shop->id);
        $decoration_item_list = ShopDecorationItem::listBySid($shop->id);
        $fav_count = UserFavShop::find()->where(['sid' => $shop->id])->count();
        $query = Goods::find();
        $query->where(['sid' => $id]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        if (!empty($keywords)) {
            $query->andWhere(['or',['like', 'title', $keywords], ['like', 'keywords', $keywords]]);
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $goods_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        $shop_score = intval(ShopScore::find()->where(['sid' => $shop->id])->average('score'));
        $shop_score = empty($shop_score) ? 5 : $shop_score;
        $category_count = ShopGoodsCategory::find()->where(['sid' => $id])->andWhere(['<>', 'status', ShopGoodsCategory::STATUS_DEL])->count();
        return $this->render('view', [
            'shop' => $shop,
            'decoration' => $decoration,
            'decoration_item_list' => $decoration_item_list,
            'fav_count' => $fav_count,
            'goods_list' => $goods_list,
            'pagination' => $pagination,
            'shop_score' => $shop_score,
            'category_count' => $category_count,
        ]);
    }

    /**
     * 店铺商品分类
     * @throws NotFoundHttpException
     * @return string
     */
    public function actionCategory()
    {
        $id = $this->get('id');
        $shop = Shop::findOne($id);
        if (empty($shop)) {
            throw new NotFoundHttpException('没有找到店铺信息。');
        }
        $category_list = ShopGoodsCategory::find()
            ->where(['sid' => $shop->id])
            ->andWhere(['<>', 'status', ShopGoodsCategory::STATUS_DEL])
            ->all();
        return $this->render('category', [
            'shop' => $shop,
            'category_list' => $category_list,
        ]);
    }

    /**
     * 搜索页面
     * @throws BadRequestHttpException
     * @return string
     */
    public function actionSearch()
    {
        if (empty($this->get('sid'))) {
            throw new BadRequestHttpException('参数错误！');
        }
        $sort = $this->get('sort', 'create_time');
        $order = $this->get('order', 'DESC');
        if ($order != 'DESC' && $order != 'ASC') {
            $order = 'DESC';
        }
        $query = Goods::find();
        $query->andWhere(['{{%goods}}.status' => Goods::STATUS_ON]);
        $query->joinWith(['goods_category', 'goods_brand', 'shop', 'orderItemList']);
        $query->andFilterWhere(['scid' => $this->get('scid')]);
        $query->andFilterWhere(['sid' => $this->get('sid')]);

        $pagination = new Pagination(['totalCount' => $query->count()]);
        if ($sort == 'price' || $sort == 'create_time') {
            $model_list = $query->orderBy($sort . " " . $order)->offset($pagination->offset)->limit($pagination->limit)->all();
        } else {
            $query->select('goods.*, SUM(order_item.amount) as amount');
            $model_list = $query->orderBy('amount ' . $order)->offset($pagination->offset)->limit($pagination->limit)
                ->groupBy('goods.id')
                ->all();
        }

        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render('search_result', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }
}
