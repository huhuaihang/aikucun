<?php

use yii\db\Migration;

class m000008_000002_create_user_fav_shop extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_fav_shop}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'sid' => $this->integer(), // 店铺编号
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_fav_shop_user1_idx', '{{%user_fav_shop}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_fav_shop_user1', '{{%user_fav_shop}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_fav_shop_shop1_idx', '{{%user_fav_shop}}', ['sid']);
        try {
            $this->addForeignKey('fk_user_fav_shop_shop1', '{{%user_fav_shop}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_fav_shop}}');
    }
}
