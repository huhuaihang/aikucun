<?php

namespace app\modules\merchant\controllers;

use app\models\Order;
use app\models\UserFavGoods;
use app\models\UserFavShop;
use app\models\Util;
use Yii;

/**
 * 查询统计
 * Class ChartController
 * @package app\modules\merchant\controllers
 */
class ChartController extends BaseController
{
    /**
     * 订单统计查询
     * @return string
     */
    public function actionOrder()
    {
        $sid = $this->shop->id;
        $query = Order::find();
        if (!empty(Yii::$app->request->get('search_stime'))) {
            $query->andFilterWhere(['>', 'create_time', strtotime(Yii::$app->request->get('search_stime'))]);
        }
        if (!empty(Yii::$app->request->get('search_stime')) && !empty(Yii::$app->request->get('search_etime')) && strtotime(Yii::$app->request->get('search_etime')) > strtotime(Yii::$app->request->get('search_stime'))) {
            $query->andFilterWhere(['<', 'create_time', strtotime(Yii::$app->request->get('search_etime'))]);
        }
        $order_count = $query->asArray()->select(['status', 'count' => 'count(*)'])->andFilterWhere(['sid' => $sid])
            ->groupBy('status')
            ->all();

        return $this->render('order', [
            'order_count' => $order_count,
        ]);
    }

    /**
     * 交易统计查询
     * @return string
     */
    public function actionTrade()
    {
        $sid = $this->shop->id;
        $query = Order::find();
        if (!empty(Yii::$app->request->get('search_stime'))) {
            $query->andFilterWhere(['>', 'create_time', strtotime(Yii::$app->request->get('search_stime'))]);
        }
        if (!empty(Yii::$app->request->get('search_stime')) && !empty(Yii::$app->request->get('search_etime')) && strtotime(Yii::$app->request->get('search_etime')) > strtotime(Yii::$app->request->get('search_stime'))) {
            $query->andFilterWhere(['<', 'create_time', strtotime(Yii::$app->request->get('search_etime'))]);
        }

        $order_count = $query->asArray()
            ->select(['date' => Util::getDateGroup(Yii::$app->db->driverName, 'day', 'create_time'), 'count' => 'count(0)', 'amount' => 'sum(amount_money)'])
            ->andFilterWhere(['sid' => $sid])
            ->groupBy('date')
            ->all();

        return $this->render('trade', [
            'order_count' => $order_count
        ]);
    }


    /**
     * 店铺收藏统计
     * @return string
     */
    public function actionFavoriteShop()
    {
        $sid = $this->shop->id;
        $query = UserFavShop::find();
        if (!empty(Yii::$app->request->get('search_stime'))) {
            $query->andFilterWhere(['>', 'create_time', strtotime(Yii::$app->request->get('search_stime'))]);
        }
        if (!empty(Yii::$app->request->get('search_stime')) && !empty(Yii::$app->request->get('search_etime')) && strtotime(Yii::$app->request->get('search_etime')) > strtotime(Yii::$app->request->get('search_stime'))) {
            $query->andFilterWhere(['<', 'create_time', strtotime(Yii::$app->request->get('search_etime'))]);
        }

        $shop_count = $query->asArray()
            ->select(['date' => Util::getDateGroup(Yii::$app->db->driverName, 'day', 'create_time'), 'count' => 'count(*)'])
            ->andFilterWhere(['sid' => $sid])
            ->groupBy('date')
            ->all();

        return $this->render('shop', [
            'shop_count' => $shop_count
        ]);
    }

    /**
     * 商品收藏统计
     * @return string
     */
    public function actionFavoriteGoods()
    {
        $sid = $this->shop->id;
        $query = UserFavGoods::find();
        if (!empty(Yii::$app->request->get('search_stime'))) {
            $query->andFilterWhere(['>', '{{%user_fav_goods}}.create_time', strtotime(Yii::$app->request->get('search_stime'))]);
        }
        if (!empty(Yii::$app->request->get('search_stime')) && !empty(Yii::$app->request->get('search_etime')) && strtotime(Yii::$app->request->get('search_etime')) > strtotime(Yii::$app->request->get('search_stime'))) {
            $query->andFilterWhere(['<', '{{%user_fav_goods}}.create_time', strtotime(Yii::$app->request->get('search_etime'))]);
        }
        $query->andFilterWhere(['like', 'title', Yii::$app->request->get('search_title')]);
        $query->asArray()->select(['title', 'count' => 'count(*) ']);
        $query->join('LEFT JOIN', 'goods as g', 'user_fav_goods.gid = g.id');
        $goods_count = $query->andFilterWhere(['sid' => $sid])->groupBy('gid')->orderBy('count desc')->all();

        $query_count = UserFavGoods::find();
        if (!empty(Yii::$app->request->get('search_stime'))) {
            $query_count->andFilterWhere(['>', '{{%user_fav_goods}}.create_time', strtotime(Yii::$app->request->get('search_stime'))]);
        }
        if (!empty(Yii::$app->request->get('search_stime')) && !empty(Yii::$app->request->get('search_etime')) && strtotime(Yii::$app->request->get('search_etime')) > strtotime(Yii::$app->request->get('search_stime'))) {
            $query_count->andFilterWhere(['<', '{{%user_fav_goods}}.create_time', strtotime(Yii::$app->request->get('search_etime'))]);
        }
        $query_count->andFilterWhere(['like', 'title', Yii::$app->request->get('search_title')]);
        $query_count->asArray();
        $query_count->select(['date' => Util::getDateGroup(Yii::$app->db->driverName, 'day', '{{%user_fav_goods}}.create_time'), 'count' => 'count(*)']);
        $query_count->join('LEFT JOIN', 'goods as g', '{{%user_fav_goods}}.gid = g.id');
        $goods_date_count = $query_count->andFilterWhere(['sid' => $sid])->groupBy('date')->all();

        return $this->render('goods', [
            'goods_count' => $goods_count,
            'goods_date_count' => $goods_date_count
        ]);
    }
}
