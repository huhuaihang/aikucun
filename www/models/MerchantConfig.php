<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商户设置
 * Class MerchantConfig
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $mid 商户编号
 * @property string $k 键
 * @property string $v 值
 */
class MerchantConfig extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['k', 'string', 'max' => 128],
            ['v', 'safe'],
        ];
    }

    /**
     * 获取设置
     * @param integer $mid 商户编号
     * @param string $k 设置名称
     * @param mixed $default =NULL 返回默认值
     * @return string
     */
    public static function getConfig($mid, $k, $default = NULL)
    {
        /** @var $config MerchantConfig */
        $config = MerchantConfig::find()->where([
            'mid' => $mid,
            'k' => $k
        ])->one();
        if (empty($config)) {
            return $default;
        }
        return $config->v;
    }

    /**
     * 保存设置
     * @param integer $mid 商户编号
     * @param string $k 键
     * @param string $v 值
     * @return boolean
     */
    public static function setConfig($mid, $k, $v)
    {
        /** @var $config MerchantConfig */
        $config = MerchantConfig::find()->where([
            'mid' => $mid,
            'k' => $k
        ])->one();
        if (empty($config)) {
            $config = new MerchantConfig();
            $config->mid = $mid;
            $config->k = $k;
        }
        $config->v = $v;
        return $config->save();
    }
}
