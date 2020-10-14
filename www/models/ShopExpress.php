<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 店铺快递
 * Class ShopExpress
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property integer $eid 快递编号
 * @property integer $status 状态
 *
 * @property DeliverTemplate[] $deliver_templates 关联商户物流模板
 * @property Express $express 关联物流公司
 * @property Shop    $shop    关联店铺
 */
class ShopExpress extends ActiveRecord
{
    const STATUS_OK = 1;
    const STATUS_STOP = 9;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['eid', 'sid'], 'required'],
        ];
    }

    /**
     * 关联商户物流模板
     * @return \yii\db\ActiveQuery
     */
    public function getDeliver_templates()
    {
        return $this->hasMany(DeliverTemplate::className(), ['se_id' => 'id']);
    }

    /**
     * 关联物流公司
     * @return \yii\db\ActiveQuery
     */
    public function getExpress()
    {
        return $this->hasOne(Express::className(), ['id' => 'eid']);
    }

    /**
     * 关联店铺
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
    }
}
