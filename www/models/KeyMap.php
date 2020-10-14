<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * 字典
 * Class KeyMap
 * @package app\models
 *
 * @property integer $id PK
 * @property string $t 类别
 * @property integer $k 键
 * @property string $v 值
 */
class KeyMap extends ActiveRecord
{
    /**
     * 查找字典返回值
     * @param string $t 类别
     * @param integer $k 键
     * @param mixed $v 默认值
     * @return string 值
     */
    public static function getValue($t, $k, $v = null)
    {
        /** @var $model KeyMap */
        $model = KeyMap::find()->where(['t' => $t, 'k' => $k])->one();
        if (!empty($model)) {
            return $model->v;
        }
        return $v;
    }

    /**
     * 查找字典返回列表
     * @param string $t 类别
     * @param integer $k =0 键，当键大于零时按照位运算对比模式获取值的数组
     * @return array($k=>$v)
     */
    public static function getValues($t, $k = 0)
    {
        if ($k == 0) {
            $model_list = KeyMap::find()->where(['t' => $t])->all();
            return ArrayHelper::map($model_list, 'k', 'v');
        }
        $map = KeyMap::getValues($t);
        if (empty($map)) {
            return [];
        }
        $values = [];
        foreach ($map as $key => $value) {
            if (($key & $k) > 0) {
                $values[$key] = $value;
            }
        }
        return $values;
    }
}
