<?php

use yii\db\Migration;

class m000006_000005_create_goods_violation extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_violation}}', [
            'id' => $this->primaryKey(), // PK
            'gid' => $this->integer(), // 商品编号
            'vid' => $this->integer(), // 违规类型编号
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_goods_violation_goods1_idx', '{{%goods_violation}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_violation_goods1', '{{%goods_violation}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_violation_violation_type1_idx', '{{%goods_violation}}', ['vid']);
        try {
            $this->addForeignKey('fk_goods_violation_violation_type1', '{{%goods_violation}}', ['vid'], '{{%violation_type}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_violation_status', 1, '等待商家处理'],
            ['goods_violation_status', 2, '等待管理审核'],
            ['goods_violation_status', 0, '已删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'goods_violation_status']);
        $this->dropTable('{{%goods_violation}}');
    }
}
