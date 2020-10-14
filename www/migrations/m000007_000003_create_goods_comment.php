<?php

use yii\db\Migration;

class m000007_000003_create_goods_comment extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_comment}}', [
            'id' => $this->primaryKey(), // PK
            'pid' => $this->integer(), // 上级编号
            'gid' => $this->integer(), // 商品编号
            'uid' => $this->integer(), // 用户编号
            'oid' => $this->integer(), // 订单编号
            'sku_key_name' => $this->string(256), // 商品SKU属性值中文
            'score' => $this->integer(), // 评分值
            'img_list' => $this->text(), // 图片列表JSON
            'content' => $this->text(), // 评论内容
            'is_anonymous' => $this->integer(), // 是否匿名
            'status' => $this->integer(), // 状态
            'is_reply' => $this->integer()->defaultValue(0), // 是否已回复
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_goods_comment_goods_comment1_idx', '{{%goods_comment}}', ['pid']);
        try {
            $this->addForeignKey('fk_goods_comment_goods_comment1', '{{%goods_comment}}', ['pid'], '{{%goods_comment}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_comment_goods1_idx', '{{%goods_comment}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_comment_goods1', '{{%goods_comment}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_comment_user1_idx', '{{%goods_comment}}', ['uid']);
        try {
            $this->addForeignKey('fk_goods_comment_user1', '{{%goods_comment}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_comment_order1_idx', '{{%goods_comment}}', ['oid']);
        try {
            $this->addForeignKey('fk_goods_comment_order1', '{{%goods_comment}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_comment_status', 1, '正常'],
            ['goods_comment_status', 9, '待审核'],
            ['goods_comment_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'goods_comment_status']);
        $this->dropTable('{{%goods_comment}}');
    }
}
