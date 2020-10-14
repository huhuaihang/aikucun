<?php

use yii\db\Migration;

class m000005_000001_create_shop extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop}}', [
            'id' => $this->primaryKey(), // PK
            'mid' => $this->integer(), // 商户编号
            'name' => $this->string(128), // 名称
            'area' => $this->string(32), // 区域编码
            'earnest_money_fid' => $this->integer(), // 保证金财务记录编号
            'tid' => $this->integer(), // 主题编号
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_shop_merchant1_idx', '{{%shop}}', ['mid']);
        try {
            $this->addForeignKey('fk_shop_merchant1', '{{%shop}}', ['mid'], '{{%merchant}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_shop_finance_log1_idx', '{{%shop}}', ['earnest_money_fid']);
        try {
            $this->addForeignKey('fk_shop_finance_log1', '{{%shop}}', ['earnest_money_fid'], '{{%finance_log}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_shop_shop_theme1_idx', '{{%shop}}', ['tid']);
        try {
            $this->addForeignKey('fk_shop_shop_theme1', '{{%shop}}', ['tid'], '{{%shop_theme}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['shop_status', 1, '等待审核'],
            ['shop_status', 2, '审核通过'],
            ['shop_status', 9, '审核拒绝'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'shop_status']);
        $this->dropTable('{{%shop}}');
    }
}
