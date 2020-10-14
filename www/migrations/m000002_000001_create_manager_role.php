<?php

use app\models\ManagerRole;
use yii\db\Migration;

class m000002_000001_create_manager_role extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%manager_role}}', [
            'id'=>$this->primaryKey(), // PK
            'name'=>$this->string(32), // 角色名称
            'status'=>$this->integer(), // 状态
            'remark'=>$this->text(), // 描述
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['manager_role_status', 1, '正常'],
            ['manager_role_status', 0, '删除'],
        ]);
        $this->insert('{{%manager_role}}', [
            'id'=>1,
            'name'=>'系统管理员',
            'status'=>ManagerRole::STATUS_OK,
            'remark'=>'维护系统用。',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%manager_role}}');
    }
}
