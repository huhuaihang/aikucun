<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 快递
 * Class Express
 * @package app\models
 *
 * @property integer $id PK
 * @property string $code 代码
 * @property string $name 名称
 * @property integer $sort 排序
 * @property integer $status 状态
 *
 * @property ShopExpress $shopExpresses 关联店铺物流公司
 */
class Express extends ActiveRecord
{
    const STATUS_OK = 1;
    const STATUS_PAUSE = 9;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code', 'name'], 'string', 'max' => 32],
            ['sort', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => '物流编号',
            'name' => '物流公司',
            'sort' => '排序',
            'status' => '状态',
        ];
    }

    /**
     * 关联店铺物流公司
     * @return \yii\db\ActiveQuery
     */
    public function getShopExpresses()
    {
        return $this->hasMany(ShopExpress::className(), ['eid' => 'id']);
    }
}
