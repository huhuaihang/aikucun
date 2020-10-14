<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * 店铺
 * Class Shop
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $mid 商户编号
 * @property string $name 名称
 * @property string $area 区域编码
 * @property integer $earnest_money_fid 保证金财务记录编号
 * @property integer $tid 主题编号
 * @property integer $status 状态
 * @property string $remark 备注
 *
 * @property City $city 关联城市
 * @property Merchant $merchant 关联商户
 */
class Shop extends ActiveRecord
{
    const STATUS_WAIT = 1;
    const STATUS_ACCEPT = 2;
    const STATUS_REJECT = 9;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            ['name', 'string', 'max' => 128],
            [['status', 'remark', 'area'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '店铺名称',
            'area' => '地区',
            'remark' => '备注',
            'status' => '状态',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (($this->status == Shop::STATUS_REJECT) && empty($this->remark)) {
            $this->addError('remark', '审核拒绝，备注不能为空！');
            return false;
        }
        return parent::beforeSave($insert);
    }

    /**
     * 关联城市
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['code' => 'area']);
    }

    /**
     * 关联商户
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return $this->hasOne(Merchant::className(), ['id' => 'mid']);
    }

    /**
     * 保证金支付结果
     * @param boolean $is_success 是否成功
     * @return boolean
     * @throws Exception
     */
    public function payNotify($is_success)
    {
        if (!$is_success) {
            return true;
        }
        $merchant = $this->merchant;
        if ($merchant->status != Merchant::STATUS_DATA2_OK) {
            throw new Exception('入驻申请状态错误。');
        }
        $merchant->status = Merchant::STATUS_COMPLETE;
        return $merchant->save();
    }
}
