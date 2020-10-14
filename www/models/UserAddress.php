<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户收货地址
 * Class UserAddress
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property string $area 区域编码
 * @property string $address 详细地址
 * @property string $name 收货人
 * @property string $mobile 电话
 * @property integer $is_default 是否默认
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 *
 * @property City $city 关联城市
 */
class UserAddress extends ActiveRecord
{
    const STATUS_OK = 1;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'area', 'address', 'name', 'mobile', 'status', 'create_time'], 'required'],
            [['area', 'name', 'mobile'], 'string', 'max' => 32],
            ['area', 'match', 'pattern' => '/^\d{6}$/'],
            ['is_default', 'default', 'value' => 0],
            [['mobile'], 'match', 'pattern' => '/^[1][345789][0-9]{9}$/', 'message' => '输入正确手机号码'],
            [['address'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'area' => '地区',
            'name' => '收货人',
            'mobile' => '联系方式',
            'address' => '详细地址',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $limit = System::getConfig('user_address_limit', 0);
            if ($limit > 0) {
                $count = UserAddress::find()->andWhere(['uid' => $this->uid, 'status' => UserAddress::STATUS_OK])->count();
                if (!empty($count) && $count >= $limit) {
                    $this->addError('', '每个用户最多只能保存' . $limit . '个收货地址。');
                    return false;
                }
            }
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
}
