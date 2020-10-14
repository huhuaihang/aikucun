<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 店铺主题
 * Class ShopTheme
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 名称
 * @property string $code 查找目录代号
 * @property string $cover_image 封面图片
 */
class ShopTheme extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'cover_image'], 'required'],
            ['name', 'string', 'max' => 128],
            ['code', 'string', 'max' => 32],
        ];
    }
}
