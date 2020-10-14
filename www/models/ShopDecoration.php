<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 店铺装修
 * Class ShopDecoration
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property string $header_background_image 头部背景图
 */
class ShopDecoration extends ActiveRecord
{
    /**
     * 返回店铺装修
     * @param $sid integer 店铺编号
     * @return ShopDecoration
     */
    public static function findBySid($sid)
    {
        $model = ShopDecoration::find()->andWhere(['sid' => $sid])->one();
        if (empty($model)) {
            $model = new ShopDecoration();
            $model->sid = $sid;
            $model->save();
        }
        return $model;
    }
}
