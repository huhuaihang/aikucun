<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 常见问题分类
 * Class FaqCategory
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $pid 上级编号
 * @property string $name 名称
 * @property integer $status 状态
 */
class FaqCategory extends ActiveRecord
{
    const STATUS_SHOW = 1;
    const STATUS_HIDE = 9;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'status'], 'required'],
            [['name'], 'string', 'max' => 128],
            ['pid', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '分类名称',
            'pid' => '父级分类',
            'status' => '状态',
        ];
    }

    /**
     * 返回完整的分类信息
     * @param $id
     * @param array $arr
     * @return array|null|ActiveRecord
     */
    public static function find_category_all($id, $arr = [])
    {
        $tree = array();
        /* @var $arr FaqCategory */
        if(empty($arr)) {
            $arr = FaqCategory::find()->select('id, name, pid')->asArray()->all();
        }
        foreach ($arr as $val) {
            if ($val['id'] == $id) {
                if ($val['pid'] > 0) {
                    $tree = array_merge($tree,FaqCategory::find_category_all($val['pid'], $arr));
                }
                $tree[] = $val;
            }
        }
        return $tree;
    }
}
