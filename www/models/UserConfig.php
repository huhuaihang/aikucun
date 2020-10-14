<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户设置
 * Class UserConfig
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property string $k 键
 * @property string $v 值
 */
class UserConfig extends ActiveRecord
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
     * @param integer $uid 用户编号
     * @param string $k 设置名称
     * @param mixed $default =NULL 返回默认值
     * @return string
     */
    public static function getConfig($uid, $k, $default = NULL)
    {
        /** @var $config UserConfig */
        $config = UserConfig::find()->where([
            'uid' => $uid,
            'k' => $k
        ])->one();
        if (empty($config)) {
            return $default;
        }
        return $config->v;
    }

    /**
     * 保存设置
     * @param integer $uid 用户编号
     * @param string $k 键
     * @param string $v 值
     * @return boolean
     */
    public static function setConfig($uid, $k, $v)
    {
        /** @var $config UserConfig */
        $config = UserConfig::find()->where([
            'uid' => $uid,
            'k' => $k
        ])->one();
        if (empty($config)) {
            $config = new UserConfig();
            $config->uid = $uid;
            $config->k = $k;
        }
        $config->v = $v;
        return $config->save();
    }
}
