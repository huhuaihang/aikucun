<?php

use yii\db\Migration;

class m000008_000003_create_user_fav_goods extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_fav_goods}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'gid' => $this->integer(), // 商品编号
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_fav_goods_user1_idx', '{{%user_fav_goods}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_fav_goods_user1', '{{%user_fav_goods}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_fav_goods_goods1_idx', '{{%user_fav_goods}}', ['gid']);
        try {
            $this->addForeignKey('fk_user_fav_goods_goods1', '{{%user_fav_goods}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_fav_goods}}');
    }
}
