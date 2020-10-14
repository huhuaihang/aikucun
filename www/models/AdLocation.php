<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * 广告位置
 * Class AdLocation
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $type 类型
 * @property string $name 名称
 * @property integer $max_count 最大展示数量
 * @property integer $width 图片宽度
 * @property integer $height 图片高度
 * @property string $code 广告展示Smarty代码
 * @property string $remark 备注
 *
 * @property array activeAdList 当前可用广告列表的查询条件
 * @property Ad $ad
 */
class AdLocation extends ActiveRecord
{
    const TYPE_TEXT = 1;
    const TYPE_IMAGE = 2;
    const TYPE_GOODS = 3;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'name'], 'required'],
            [['type', 'max_count', 'width', 'height'], 'integer'],
            ['name', 'string', 'max' => 128],
            [['code', 'remark'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => '类型',
            'name' => '名称',
            'max_count' => '最大展示数量',
            'width' => '图片宽度',
            'height' => '图片高度',
            'code' => '广告展示Smarty代码',
            'remark' => '备注',
        ];
    }

    /**
     * 返回当前可用的广告查询条件
     * @return \yii\db\ActiveQuery
     */
    public function getActiveAdList()
    {

        $query = $this->hasMany(Ad::className(), ['lid' => 'id'])
            ->andWhere(['status' => Ad::STATUS_ACTIVE])
            ->andWhere(['<=', 'start_time', time()])
            ->andWhere(['>=', 'end_time', time()])
            ->orderBy('sort DESC, id DESC');
        if ($this->max_count > 0) {
            $query->limit($this->max_count);
        }
        return $query;
    }

    /**
     * 返回当前可用的广告查询条件
     * @param $ad
     * @return \yii\db\ActiveQuery
     */
    public function getActiveAdNotList($ad = [])
    {

        $query = $this->hasMany(Ad::className(), ['lid' => 'id'])
            ->andWhere(['status' => Ad::STATUS_ACTIVE])
            ->andWhere(['<=', 'start_time', time()])
            ->andWhere(['>=', 'end_time', time()])
            ->andWhere(['NOT IN', '{{ad}}.id', $ad])
            ->orderBy('sort DESC, id DESC');
        if ($this->max_count > 0) {
            $query->limit($this->max_count);
        }
        return $query;
    }

    /**
     * 返回当前可用的广告查询条件
     * @return \yii\db\ActiveQuery
     */
    public function getAd()
    {

        $query = $this->hasMany(Ad::className(), ['lid' => 'id'])
            ->andWhere(['status' => Ad::STATUS_ACTIVE])
            ->andWhere(['<=', 'start_time', time()])
            ->andWhere(['>=', 'end_time', time()])
            ->orderBy('sort DESC, id DESC');

        if ($this->max_count > 0) {
            $query->limit($this->max_count);
        }
        return $query;
    }
}
