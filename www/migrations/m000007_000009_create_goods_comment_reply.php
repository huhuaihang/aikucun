<?php

use yii\db\Migration;

class m000007_000009_create_goods_comment_reply extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_comment_reply}}', [
            'id' => $this->primaryKey(), // PK
            'cid' => $this->integer(), // 评论编号
            'content' => $this->text(), // 回复内容
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_goods_comment_reply_goods_comment1_idx', '{{%goods_comment_reply}}', ['cid']);
        try {
            $this->addForeignKey('fk_goods_comment_reply_goods_comment1', '{{%goods_comment_reply}}', ['cid'], '{{%goods_comment}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_comment_reply}}');
    }
}
