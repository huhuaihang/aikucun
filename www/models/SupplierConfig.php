<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 供货商设置
 * Class SupplierConfig
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 供货商编号
 * @property string $k 键
 * @property string $v 值
 */
class SupplierConfig extends ActiveRecord
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
     * @param integer $sid 供货商编号
     * @param string $k 设置名称
     * @param mixed $default =NULL 返回默认值
     * @return string
     */
    public static function getConfig($sid, $k, $default = NULL)
    {
        /** @var $config SupplierConfig */
        $config = SupplierConfig::find()->where([
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
     * @param integer $sid 供货商编号
     * @param string $k 键
     * @param string $v 值
     * @return boolean
     */
    public static function setConfig($sid, $k, $v)
    {
        /** @var $config SupplierConfig */
        $config = SupplierConfig::find()->where([
            'sid' => $sid,
            'k' => $k
        ])->one();
        if (empty($config)) {
            $config = new SupplierConfig();
            $config->sid = $sid;
            $config->k = $k;
        }
        $config->v = $v;
        return $config->save();
    }
}
