<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 代理商设置
 * Class AgentConfig
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $aid 代理商编号
 * @property string $k 键
 * @property string $v 值
 */
class AgentConfig extends ActiveRecord
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
     * @param integer $aid 代理商编号
     * @param string $k 设置名称
     * @param mixed $default =NULL 返回默认值
     * @return string
     */
    public static function getConfig($aid, $k, $default = NULL)
    {
        /** @var $config AgentConfig */
        $config = AgentConfig::find()->where([
            'aid' => $aid,
            'k' => $k
        ])->one();
        if (empty($config)) {
            return $default;
        }
        return $config->v;
    }

    /**
     * 保存设置
     * @param integer $aid 代理商编号
     * @param string $k 键
     * @param string $v 值
     * @return boolean
     */
    public static function setConfig($aid, $k, $v)
    {
        /** @var $config AgentConfig */
        $config = AgentConfig::find()->where([
            'aid' => $aid,
            'k' => $k
        ])->one();
        if (empty($config)) {
            $config = new AgentConfig();
            $config->aid = $aid;
            $config->k = $k;
        }
        $config->v = $v;
        return $config->save();
    }
}
