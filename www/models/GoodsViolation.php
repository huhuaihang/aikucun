<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品违规
 * Class GoodsViolation
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $gid 商品编号
 * @property integer $vid 违规类型编号
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 * @property string $remark 备注
 *
 * @property Goods $goods 关联商品表
 * @property ViolationType $violationType 关联违规类型表
 */
class GoodsViolation extends ActiveRecord
{
    const STATUS_WAIT_MERCHANT = 1;
    const STATUS_WAIT_MANAGER = 2;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gid', 'vid', 'status', 'create_time'], 'required'],
            [['remark'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'gid' => '商品',
            'vid' => '违规类型',
            'status' => '状态',
            'create_time' => '创建时间',
            'remark' => '备注',
        ];
    }

    /**
     * 关联商品表
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'gid']);
    }

    /**
     * 关联违规类型表
     * @return \yii\db\ActiveQuery
     */
    public function getViolationType()
    {
        return $this->hasOne(ViolationType::className(), ['id' => 'vid']);
    }
}
