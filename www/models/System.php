<?php

namespace app\models;

use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * 系统设置
 * Class System
 * @package app\models
 *
 * @property integer $id PK
 * @property string $category 分类
 * @property string $show_name 显示名称
 * @property string $type 数据类型JSON
 * @property string $name 数据名称
 * @property string $value 值
 */
class System extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'show_name'], 'string', 'max' => 32],
            ['name', 'string', 'max' => 128],
            ['type', 'string', 'max' => 512],
            ['value', 'safe'],
        ];
    }

    /**
     * 获取系统设置
     * @param string $name 设置名称
     * @param mixed $default =NULL 返回默认值
     * @return string
     */
    public static function getConfig($name, $default = NULL)
    {
        /** @var $system System */
        $system = System::find()->where([
            'name' => $name
        ])->one();
        if (empty($system)) {
            return $default;
        }
        return $system->value;
    }

    /**
     * 保存系统设置
     * @param string $name
     * @param string $value
     * @throws Exception
     * @return boolean
     */
    public static function setConfig($name, $value)
    {
        /** @var $system System */
        $system = System::find()->where([
            'name' => $name
        ])->one();
        if (empty($system)) {
            throw new Exception('没有找到系统设置：' . $name);
        }
        $system->value = $value;
        return $system->save();
    }
}
