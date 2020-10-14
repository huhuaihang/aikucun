<?php

use yii\db\Migration;

class m000008_000004_create_user_commission extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_commission}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'from_uid' => $this->integer(), // 来源用户编号
            'level' => $this->integer(), // 推荐层级
            'type' => $this->integer(), // 返佣类型 直接 月结
            'commission' => $this->decimal(12, 2), // 佣金
            'time' => $this->integer(), // 创建时间
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_user_commission_user1_idx', '{{%user_commission}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_commission_user1', '{{%user_commission}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_commission_user2_idx', '{{%user_commission}}', ['from_uid']);
        try {
            $this->addForeignKey('fk_user_commission_user2', '{{%user_commission}}', ['from_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_commission}}');
    }
}
