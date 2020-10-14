<?php

namespace app\modules\h5\controllers;

use app\models\GoodsCategory;

/**
 * 商品分类控制器
 * Class GoodsController
 * @package app\modules\h5\controllers
 */
class CategoryController extends BaseController
{
    /**
     * 全部分类
     * @return string
     */
    public function actionIndex()
    {
        $sub_tree_cate = GoodsCategory::subtree('', 0, 1);
        $choicest = GoodsCategory::find()->where(['status' => GoodsCategory::STATUS_SHOW, 'is_choicest' => 1])->asArray()->all();

        return $this->render('index', [
            'sub_tree_cate' => $sub_tree_cate,
            'choicest' => $choicest,
        ]);
    }

    /**
     * 丽人
     * @return string
     */
    public function actionBeauty()
    {
        return $this->render('beauty');
    }

    /**
     * 酒店
     * @return string
     */
    public function actionHotel()
    {
        return $this->render('hotel');
    }

    /**
     * 电影
     * @return string
     */
    public function actionMovie()
    {
        return $this->render('movie');
    }

    /**
     * 旅游
     * @return string
     */
    public function actionTravel()
    {
        return $this->render('travel');
    }

    /**
     * 外卖
     * @return string
     */
    public function actionTakeOut()
    {
        return $this->render('take_out');
    }
}
