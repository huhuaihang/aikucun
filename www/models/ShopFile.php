<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 店铺文件
 * Class ShopFile
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property integer $cid 店铺文件分类编号
 * @property integer $type 类型
 * @property string $url 路径
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 *
 * @property ShopFileCategory $fileType 关联店铺
 */
class ShopFile extends ActiveRecord
{
    const TYPE_IMAGE = 1; // 图片

    const STATUS_OK = 1; // 正常
    const STATUS_DEL = 0; // 删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'sid', 'status', 'create_time', 'type'], 'required'],
            ['cid', 'safe'],
        ];
    }

    /**
     * 关联文件类型
     * @return \yii\db\ActiveQuery
     */
    public function getFileType()
    {
        return $this->hasOne(ShopFileCategory::className(), ['id' => 'cid']);
    }
}
