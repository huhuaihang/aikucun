<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 线上店铺品牌关联
 * Class ShopBrand
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property integer $bid 商品品牌编号
 * @property integer $type 类型
 * @property integer $status 状态
 * @property string $file_list 资料文件列表JSON
 *
 * @property GoodsBrand $brand 关联品牌
 * @property Shop $shop 关联店铺
 */
class ShopBrand extends ActiveRecord
{
    const TYPE_OWNER = 1; // 自有
    const TYPE_AGENT = 2; // 代理

    const STATUS_WAIT = 1; // 等待审核
    const STATUS_VALID = 2; // 审核通过
    const STATUS_REJECTED = 9; // 审核拒绝

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sid', 'bid', 'type', 'status', 'file_list'], 'required'],
            [['sid', 'bid', 'type', 'status'], 'integer'],
            [['file_list'], 'string'],
            [['bid'], 'exist', 'skipOnError' => true, 'targetClass' => GoodsBrand::className(), 'targetAttribute' => ['bid' => 'id']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shop::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bid' => '商品品牌',
            'type' => '类型',
            'status' => '状态',
            'file_list' => '商标证书',
        ];
    }

    /**
     * 关联品牌
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(GoodsBrand::className(), ['id' => 'bid']);
    }

    /**
     * 关联店铺
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
    }

    /**
     * 返回文件列表
     * @return array
     */
    public function getFileList()
    {
        if (empty($this->file_list)) {
            return [];
        }
        return json_decode($this->file_list, true);
    }
}
