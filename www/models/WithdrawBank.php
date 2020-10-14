<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 提现银行
 * Class WithdrawBank
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 银行名称
 * @property string $code 代号
 * @property string $logo LOGO
 */
class WithdrawBank extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'logo'], 'required'],
        ];
    }
}
