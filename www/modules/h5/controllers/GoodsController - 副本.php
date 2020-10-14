<?php

namespace app\modules\h5\controllers;

use app\models\City;
use app\models\Goods;
use app\models\GoodsAttrValue;
use app\models\GoodsCategory;
use app\models\GoodsComment;
use app\models\GoodsSku;
use app\models\IpCity;
use app\models\User;
use app\models\UserRecommend;
use app\models\UserSearchHistory;
use app\models\GoodsExpress;
use Yii;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * 商品控制器
 * Class GoodsController
 * @package app\modules\h5\controllers
 */
class GoodsController_bak extends BaseController
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
        $share_commission = $self_buy_ratio = 30;
        if (!Yii::$app->user->isGuest) {
            $user = User::findOne(Yii::$app->user->id);
            $share_commission = $user->childBuyRatio;
            $self_buy_ratio = $user->buyRatio;
        }
        return $this->render('view', [
            'goods' => $goods,
            'gav_map' => $gav_map,
            'sku_list' => $sku_list,
            'city_data' => $city_data,
            'gav_list' =>$gav_list,
            'share_commission' => $share_commission,
            'self_buy_ratio' => $self_buy_ratio,
        ]);
    }

    /**
     * 商品分类 商品列表页
     * @return string
     */
    public function actionList()
    {
        $sort = $this->get('sort', 'create_time');
        $order = $this->get('order', 'DESC');
        $category = $this->get('search_category');
        if ($order != 'DESC' && $order != 'ASC') {
            $order = 'DESC';
        }
        $title = GoodsCategory::findOne($category);
        $query = Goods::find();
        $query->andWhere(['{{%goods}}.status' => Goods::STATUS_ON]);
        $query->joinWith(['goods_category', 'goods_brand', 'shop', 'orderItemList']);
        $query->andWhere(['cid' => $category]);
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
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
            'title' => $title,
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
