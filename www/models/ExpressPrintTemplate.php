<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 快递单打印模板
 * Class ExpressPrintTemplate
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $eid 快递编号
 * @property string $name 名称
 * @property string $background_image 背景图片
 * @property integer $width 宽度毫米
 * @property integer $height 高度毫米
 * @property integer $offset_top 顶部偏移像素
 * @property integer $offset_left 左侧偏移像素
 * @property string $template 模板JSON
 *
 * @property Express $express 关联快递
 */
class ExpressPrintTemplate extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['width', 'height', 'offset_top', 'offset_left'], 'default', 'value' => 0],
            [['eid', 'name', 'background_image', 'width', 'height', 'offset_top', 'offset_left', 'template'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'eid' => '快递公司',
            'name' => '名称',
            'background_image' => '面单照片',
            'width' => '宽度',
            'height' => '高度',
            'offset_top' => '顶部偏移',
            'offset_left' => '左侧偏移',
            'template' => '模板',
        ];
    }

    /**
     * 关联快递
     * @return \yii\db\ActiveQuery
     */
    public function getExpress()
    {
        return $this->hasOne(Express::className(), ['id' => 'eid']);
    }
}
