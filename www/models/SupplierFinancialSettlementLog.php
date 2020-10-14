<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 供货商结算记录
 * Class SupplierFinancialSettlementLog
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 供货商编号
 * @property float $money 结算金额
 * @property string $bank_info 银行信息
 * @property string $proof_file 凭证文件JSON
 * @property integer $create_time 创建时间
 * @property integer $status 状态
 * @property integer $is_bill 是否给了发票
 * @property string $remark 备注
 *
 * @property Supplier $supplier 关联供货商
 */
class SupplierFinancialSettlementLog extends ActiveRecord
{
    const STATUS_WAIT = 1; // 未结算
    const STATUS_LOCK = 2; // 已锁定
    const STATUS_SETTLE = 3; // 已结算

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sid', 'money', 'bank_info', 'create_time', 'status'], 'required'],
            ['bank_info', 'string', 'max' => 256],
            [['proof_file', 'remark', 'is_bill'], 'safe'],
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
            'is_bill' => '是否给了发票',
            'status' => '状态',
        ];
    }

    /**
     * 关联供货商
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'sid']);
    }

    /**
     * 获取凭证文件列表
     * @param boolean $schema 是否返回完整地址
     * @return array
     */
    public function getProofFileList($schema = false)
    {
        if (empty($this->proof_file)) {
            return [];
        }
        $list = json_decode($this->proof_file, true);
        if (!$schema) {
            return $list;
        }
        array_walk($list, function (&$item) {
            $item = Util::fileUrl($item, true);
        });
        return $list;
    }
}
