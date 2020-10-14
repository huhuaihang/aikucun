<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 店铺配置
 * Class ShopConfig
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property string $k 键
 * @property string $v 值
 */
class ShopConfig extends ActiveRecord
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
     * @param integer $sid 店铺编号
     * @param string $k 设置名称
     * @param mixed $default =NULL 返回默认值
     * @return string
     */
    public static function getConfig($sid, $k, $default = NULL)
    {
        /** @var $config ShopConfig */
        $config = ShopConfig::find()->where([
            'sid' => $sid,
            'k' => $k
        ])->one();
        if (empty($config)) {
            return $default;
        }
        return $config->v;
    }

    /**
     * 保存设置
     * @param integer $sid 店铺编号
     * @param string $k 键
     * @param string $v 值
     * @return boolean
     */
    public static function setConfig($sid, $k, $v)
    {
        /** @var $config ShopConfig */
        $config = ShopConfig::find()->where([
            'sid' => $sid,
            'k' => $k
        ])->one();
        if (empty($config)) {
            $config = new ShopConfig();
            $config->sid = $sid;
            $config->k = $k;
        }
        $config->v = $v;
        return $config->save();
    }
}
