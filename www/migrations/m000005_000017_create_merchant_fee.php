<?php

use yii\db\Migration;

class m000005_000017_create_merchant_fee extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%merchant_fee}}', [
            'id' => $this->primaryKey(), // PK
            'cid' => $this->integer(), // 商品分类编号
            'earnest_money' => $this->decimal(12, 2), // 保证金
        ]);
        $this->createIndex('fk_merchant_fee_goods_category1_idx', '{{%merchant_fee}}', ['cid']);
        try {
            $this->addForeignKey('fk_merchant_fee_goods_category1_idx', '{{%merchant_fee}}', ['cid'], '{{%goods_category}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%merchant_fee}}');
    }
}
