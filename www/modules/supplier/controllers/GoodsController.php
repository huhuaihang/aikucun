<?php

namespace app\modules\supplier\controllers;

use app\models\Goods;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

/**
 * 商品管理
 * Class GoodsController
 * @package app\modules\supplier\controllers
 */
class GoodsController extends BaseController
{
    /**
     * 商品列表
     * @return string
     */
    public function actionList()
    {
        $query = Goods::find();
        $query->andWhere(['supplier_id' => $this->supplier->id]);
        $query->andFilterWhere(['id' => $this->get('search_id')]);
        $query->andFilterWhere(['like', 'title', $this->get('search_title')]);

        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $goodsList = $query->all();
        return $this->render('list', [
            'goodsList' => $goodsList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 商品详情
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        $id = $this->get('id');
        $goods = Goods::findOne(['id' => $id]);
        if (empty($goods) || $goods->supplier_id != $this->supplier->id) {
            throw new NotFoundHttpException('没有找到商品信息。');
        }
        return $this->render('view', [
            'goods' => $goods,
        ]);
    }
}
