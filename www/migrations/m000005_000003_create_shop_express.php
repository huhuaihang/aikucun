<?php

use yii\db\Migration;

class m000005_000003_create_shop_express extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_express}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'eid' => $this->integer(), // 快递编号
            'status' => $this->integer(), // 状态
        ]);
        $this->createIndex('fk_shop_express_shop1_idx', '{{%shop_express}}', ['sid']);
        try {
            $this->addForeignKey('fk_shop_express_shop1', '{{%shop_express}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_shop_express_express1_idx', '{{%shop_express}}', ['eid']);
        try {
            $this->addForeignKey('fk_shop_express_express1', '{{%shop_express}}', ['eid'], '{{%express}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['shop_express_status', 1, '使用'],
            ['shop_express_status', 9, '暂停'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'shop_express_status']);
        $this->dropTable('{{%shop_express}}');
    }
}
