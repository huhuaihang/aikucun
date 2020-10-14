<?php

use yii\db\Migration;

class m000008_000005_create_user_recommend extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_recommend}}', [
            'id' => $this->primaryKey(), // PK
            'from_uid' => $this->integer(), // 推荐者用户编号
            'to_uid' => $this->integer(), // 接受者用户编号
            'sid' => $this->integer(), // 店铺编号
            'gid' => $this->integer(), // 商品编号
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_recommend_user1_idx', '{{%user_recommend}}', ['from_uid']);
        try {
            $this->addForeignKey('fk_user_recommend_user1', '{{%user_recommend}}', ['from_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_recommend_user2_idx', '{{%user_recommend}}', ['to_uid']);
        try {
            $this->addForeignKey('fk_user_recommend_user2', '{{%user_recommend}}', ['to_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_recommend_shop1_idx', '{{%user_recommend}}', ['sid']);
        try {
            $this->addForeignKey('fk_user_recommend_shop1', '{{%user_recommend}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_recommend_goods1_idx', '{{%user_recommend}}', ['gid']);
        try {
            $this->addForeignKey('fk_user_recommend_goods1', '{{%user_recommend}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_recommend}}');
    }
}
