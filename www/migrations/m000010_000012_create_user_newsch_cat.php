<?php

use yii\db\Migration;

class m000010_000012_create_user_newsch_cat extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_newsch_cat}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'cid' => $this->integer(), // 商学院素材编号
            'type' => $this->integer(), // 素材类型
            'read_time' => $this->integer(), // 用户查看时间
        ]);
        $this->createIndex('fk_user_newsch_cat_user1_idx', '{{%user_newsch_cat}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_newsch_cat_user1', '{{%user_newsch_cat}}', ['uid']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_newsch_cat_cat1_idx', '{{%user_newsch_cat}}', ['cid']);
        try {
            $this->addForeignKey('fk_user_newsch_cat_cat1', '{{%user_newsch_cat}}', ['cid']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_newsch_cat}}');
    }
}
