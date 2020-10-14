<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商户结算记录
 * Class MerchantFinancialSettlementLog
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $mid 商户编号
 * @property float $money 金额
 * @property string $bank_info 银行信息
 * @property string $proof_file 凭证文件
 * @property integer $create_time 创建时间
 * @property integer $status 状态
 * @property string $remark 备注
 *
 * @property Merchant $merchant 关联商户
 */
class MerchantFinancialSettlementLog extends ActiveRecord
{
    const STATUS_WAIT = 1; // 未结算
    const STATUS_SETTLE = 2; // 已结算

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mid', 'money', 'bank_info', 'create_time', 'status'], 'required'],
            ['bank_info', 'string', 'max' => 256],
            ['proof_file', 'string', 'max' => 128],
            ['remark', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'money' => '结算金额',
            'bank_info' => '商户银行信息',
            'proof_file' => '打款凭证',
            'remark' => '备注',
            'status' => '状态',
        ];
    }

    /**
     * 关联商户
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return $this->hasOne(Merchant::className(), ['id' => 'mid']);
    }
}
