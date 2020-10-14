<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 评论回复
 * Class GoodsCommentReply
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $cid 评论编号
 * @property string $content 回复内容
 * @property integer $create_time 创建时间
 */
class GoodsCommentReply extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid', 'content', 'create_time'], 'required'],
        ];
    }
}
