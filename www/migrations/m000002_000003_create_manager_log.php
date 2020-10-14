<?php

use yii\db\Migration;

class m000002_000003_create_manager_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%manager_log}}', [
            'id' => $this->primaryKey(), // PK
            'mid' => $this->integer(), // 管理员编号
            'time' => $this->integer(), // 时间
            'ip' => $this->string(32), // IP地址
            'content' => $this->string(512), // 内容
            'data' => $this->text(), // 数据
        ]);
        $this->createIndex('fk_manager_log_manager1_idx', '{{%manager_log}}', ['mid']);
        try {
            $this->addForeignKey('fk_manager_log_manager1', '{{%manager_log}}', ['mid'], '{{%manager}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function down()
    {
        $this->dropTable('{{%manager_log}}');
    }
}
