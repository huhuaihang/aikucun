<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * 广告
 * Class Ad
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 广告名称
 * @property integer $lid 位置编号
 * @property string $txt 文字内容
 * @property string $img 图片地址
 * @property string $url 链接地址
 * @property integer $start_time 开始时间
 * @property integer $end_time 结束时间
 * @property integer $sort 排序数字
 * @property integer $status 状态
 * @property integer $show 展示次数
 * @property integer $click 点击次数
 *
 * @property AdLocation $location 关联广告位
 */
class Ad extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_STOPED = 9;
    const STATUS_DELETED = 0;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time'], function ($attribute) {
                if (!is_int($this[$attribute]) && preg_match('/^[\d- :]+$/', $this[$attribute])) {
                    $this[$attribute] = strtotime($this[$attribute]);
                }
            }],
            [['lid', 'sort', 'status', 'show', 'click'], 'integer'],
            [['name', 'lid', 'txt', 'url', 'start_time', 'end_time', 'status'], 'required'],
            [['name', 'txt'], 'string', 'max' => 32],
            [['img'], 'string', 'max' => 256],
            ['url', 'string', 'max' => 512],
            [['sort', 'show', 'click'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '广告名称',
            'lid' => '广告位置',
            'txt' => '文字内容',
            'img' => '图片',
            'url' => '链接地址',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'sort' => '排序数字',
            'status' => '状态',
            'show' => '展示次数',
            'click' => '点击次数',
        ];
    }

    /**
     * 关联广告位
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(AdLocation::className(), ['id' => 'lid']);
    }

    /**
     * 返回关联广告商品
     * @return false|Goods
     */
    public function getGoods()
    {
        if ($this->location->type != AdLocation::TYPE_GOODS) {
            Yii::warning('显示广告[' . $this->id . ']时出现错误：广告类型不是商品。');
            return false;
        }
        $gid = intval($this->txt);
        $goods = Goods::findOne($gid);
        if (empty($goods)) {
            Yii::warning('显示广告[' . $this->id . ']时出现错误：没有找到商品编号[' . $this->txt . ']。');
            return false;
        }
        if ($goods->status != Goods::STATUS_ON) {
            Yii::warning('显示广告[' . $this->id . ']时出现错误：广告商品[' . $goods->id . ']已下架或删除。');
            return false;
        }
        return $goods;
    }
}
