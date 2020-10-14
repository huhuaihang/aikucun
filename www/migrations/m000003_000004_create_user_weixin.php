<?php

use yii\db\Migration;

class m000003_000004_create_user_weixin extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_weixin}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer()->notNull()->comment('用户编号'),
            'app_id' => $this->string(128)->notNull()->comment('微信AppId'),
            'open_id' => $this->string(128)->notNull()->comment('微信OpenId'),
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('user_weixin1_idx', '{{%user_weixin}}', ['app_id', 'open_id'], true);
        $this->createIndex('fk_user_weixin_user1_idx', '{{%user_weixin}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_weixin_user1', '{{%user_weixin}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_weixin}}');
    }
}
