<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品分类
 * Class GoodsCategory
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $pid 上级编号
 * @property string $name 名称
 * @property string $url 链接地址
 * @property string $image 图片
 * @property integer $sort 排序
 * @property integer $status 状态
 * @property integer $is_choicest 是否精选
 *
 * @property GoodsCategory[] $childList 关联下级分类列表
 */
class GoodsCategory extends ActiveRecord
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
            [['name'], 'string', 'max' => 32],
            [['url'], 'string', 'max' => 128],
            [['image', 'pid'], 'safe'],
            [['is_choicest'], 'integer'],
            ['sort', 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pid' => '上级分类',
            'name' => '名称',
            'url' => '链接地址',
            'image' => '图片',
            'status' => '状态',
            'is_choicest' => '是否精选',
        ];
    }

    /**
     * 关联下级列表
     * @return \yii\db\ActiveQuery
     */
    public function getChildList()
    {
        return $this->hasMany(GoodsCategory::className(), ['pid' => 'id']);
    }

    /**
     * 商品数量
     * @return int
     */
    public function getGoodsCount()
    {
        return $this->hasMany(Goods::className(), ['cid' => 'id'])->count();
    }

    /**
     * 获取分类族谱树
     * @param array $arr 所有商品分类
     * @param integer $id
     * @return array
     */
    public static function familyTree($arr, $id)
    {
        $tree = array();
        if (empty($arr)) {
            $arr = GoodsCategory::find()->asArray()->all();
        }
        foreach ($arr as $v) {
            if ($v['id'] == $id) {
                if ($v['pid'] > 0) {
                    $tree = array_merge($tree, GoodsCategory::familyTree($arr, $v['pid']));
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * 递归，查找子孙树
     * @param $arr
     * @param int $id
     * @param int $lev
     * @return array
     */
    public static function subtree($arr, $id = 0, $lev = 1)
    {
        if (empty($arr)) {
            $arr = GoodsCategory::find()->where(['status' => GoodsCategory::STATUS_SHOW])->orderBy('sort DESC')->asArray()->all();
        }
        $subs = array(); // 子孙数组
        foreach ($arr as $v) {
            if ($v['pid'] == $id) {
                $v['lev'] = $lev;
                $v['menu'] = GoodsCategory::subtree($arr, $v['id'], $lev + 1);
                $subs[] = $v;
            }
        }
        return $subs;
    }

    /**
     * 获取分类下所有商品数量
     *     $id 为一级 则统计一级二级三级所有商品
     *     $id 为二级  则统计 二级三级所有商品数量
     * @param int $id
     * @return int|string
     */
    public static function getGoodsCountsById($id)
    {
        $cate_list = [];
        $goods_cate_list = GoodsCategory::subtree('', $id, '');
        foreach ($goods_cate_list as $goods_cate) {
            array_unshift($cate_list, $goods_cate['id']);
            if (!empty($goods_cate['menu'])) {
                foreach ($goods_cate['menu'] as $goods_child_cate) {
                    array_unshift($cate_list, $goods_child_cate['id']);
                }
            }
        }
        array_unshift($cate_list, $id);
        $goods_count = Goods::find()->where(['in', 'cid', $cate_list])->andWhere(['status' => Goods::STATUS_ON])->count();
        return $goods_count;
    }
}
