<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 常见问题
 * Class Faq
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $cid 分类编号
 * @property string $title 标题
 * @property string $tags 标签
 * @property string $content 内容
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 *
 * @property FaqCategory $category 关联分类
 */
class Faq extends ActiveRecord
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
            [['cid', 'title', 'tags', 'content', 'status', 'create_time'], 'required'],
            [['title', 'tags'], 'string', 'max' => 128],
            [['cid', 'status', 'create_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cid' => '类型',
            'title' => '标题',
            'content' => '内容',
            'status' => '状态',
            'tags' => '标签',
        ];
    }

    /**
     * 关联分类
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(FaqCategory::className(), ['id' => 'cid']);
    }
}
