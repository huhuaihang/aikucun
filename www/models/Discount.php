<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * 减折价
 * Class Discount
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 名称
 * @property integer $start_time 开始时间
 * @property integer $end_time 结束时间
 * @property string $goods_flag_txt 商品标志文字
 * @property string $goods_flag_img 商品标志图标
 * @property integer $buy_limit 每人限购数量
 * @property integer $amount 限购总数量
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 * @property string $remark 备注
 *
 * @property DiscountGoods[] $discountGoodsList 关联减折价商品列表
 * @property Goods[] $goodsList 关联商品列表
 */
class Discount extends ActiveRecord
{
    const STATUS_EDIT = 1;
    const STATUS_RUNNING = 2;
    const STATUS_FINISH = 9;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'start_time', 'end_time'], 'required'],
            ['start_time', 'datetime', 'timestampAttribute' => 'start_time'],
            ['end_time', 'datetime', 'timestampAttribute' => 'end_time'],
            ['remark','safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'goods_flag_txt' => '商品标志文字',
            'goods_flag_img' => '商品标志图标',
            'buy_limit' => '每人限购数量',
            'amount' => '限购总数量',
            'remark' => '备注',
        ];
    }

    /**
     * 商品数量
     * @return int
     */
    public function getGoodsCount()
    {
        return $this->hasMany(DiscountGoods::class, ['did' => 'id'])->count();
    }

    /**
     * 关联减折价商品列表
     * @return \yii\db\ActiveQuery
     */
    public function getDiscountGoodsList()
    {
        return $this->hasMany(DiscountGoods::class, ['did' => 'id']);
    }

    /**
     * 关联商品列表
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsList()
    {
        return $this->hasMany(Goods::class, ['id' => 'gid'])->via('discountGoodsList');
    }

    /**
     * 返回当前进行的减折价列表
     * @return Discount[]
     */
    public static function getList()
    {
        $query = Discount::find();
        $query->andWhere(['status' => Discount::STATUS_RUNNING]);
        $query->andWhere(['<=', 'start_time', time()]);
        $query->andWhere(['>=', 'end_time', time()]);
        return $query->all();
    }

    /**
     * 定时任务：强制结束
     */
    public static function task_force_close()
    {
        /** @var Discount $discount */
        foreach (Discount::find()
                     ->andWhere(['status' => Discount::STATUS_RUNNING])
                     ->each() as $discount) {
            if ($discount->end_time < time()) {
                $discount->status = Discount::STATUS_FINISH;
                $discount->save(false);
                Yii::warning('自动将减折价[' . $discount->id . ']结束时间[' . $discount->end_time . ']状态设置为停止');
            }
        }
    }
}
