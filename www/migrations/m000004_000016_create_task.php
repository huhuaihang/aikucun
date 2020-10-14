<?php

use yii\db\Migration;

class m000004_000016_create_task extends Migration
{
    public function up()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(), // PK
            'u_type' => $this->integer(), // 用户类型
            'uid' => $this->integer(), // 用户编号
            'name' => $this->string(32), // 名称
            'next' => $this->integer(), // 下次执行时间
            'cron' => $this->string(128), // CRON指令
            'todo' => $this->text(), // 任务内容JSON[class,method,params]
            'result' => $this->text(), // 上次执行结果
            'status' => $this->integer(), // 状态
        ]);
        $this->createIndex('fk_task_u1_idx', '{{%task}}', ['uid']);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['task_u_type', 1, '管理后台用户'],
            ['task_u_type', 2, '代理商'],
            ['task_u_type', 3, '商户'],
            ['task_u_type', 4, '前台用户'],
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['task_status', 1, '等待中'],
            ['task_status', 2, '进行中'],
            ['task_status', 3, '已结束'],
            ['task_status', 9, '暂停'],
        ]);
    }

    public function down()
    {
        $this->delete('{{%key_map}}', ['t' => 'task_status']);
        $this->delete('{{%key_map}}', ['t' => 'task_u_type']);
        $this->dropTable('{{%task}}');
    }
}
