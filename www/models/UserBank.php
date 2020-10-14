<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户银行
 * Class UserBank
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property string $bank_name 银行名称
 * @property string $bank_address 开户行所在地
 * @property string $account_name 账户名
 * @property string $account_no 账号
 */
class UserBank extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'bank_name', 'account_name', 'account_no'], 'required'],
            ['bank_name', 'string', 'max' => 32],
            [['bank_address', 'account_name', 'account_no'], 'string', 'max' => 128],
        ];
    }
}
