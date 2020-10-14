<?php

use yii\db\Migration;

class m000005_000010_create_deliver_template extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%deliver_template}}', [
            'id' => $this->primaryKey(), // PK
            'se_id' => $this->integer(), // 店铺物流编号
            'name' => $this->string(32), // 名称
            'is_default' => $this->integer(), // 是否为默认
            'use_weight' => $this->integer(), // 是否启用重量计费
            'weight_start' => $this->decimal(12, 2), // 首重量
            'weight_start_fee' => $this->decimal(12, 2), // 首重量费
            'weight_extra' => $this->decimal(12, 2), // 续重量
            'weight_extra_fee' => $this->decimal(12, 2), // 续重量费
            'use_bulk' => $this->integer(), // 是否启用体积计费
            'bulk_start' => $this->decimal(12, 2), // 首体积
            'bulk_start_fee' => $this->decimal(12, 2), // 首体积费
            'bulk_extra' => $this->decimal(12, 2), // 续体积
            'bulk_extra_fee' => $this->decimal(12, 2), // 续体积费
            'use_count' => $this->integer(), // 是否启用件数计费
            'count_start' => $this->integer(), // 首件数
            'count_start_fee' => $this->decimal(12, 2), // 首件数费
            'count_extra' => $this->integer(), // 续件数
            'count_extra_fee' => $this->decimal(12, 2), // 续件数费
            'pid_list' => $this->text(), // 省编号列表JSON
            'cid_list' => $this->text(), // 市编号列表JSON
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_deliver_template_shop_express1_idx', '{{%deliver_template}}', ['se_id']);
        try {
            $this->addForeignKey('fk_deliver_template_shop_express1', '{{%deliver_template}}', ['se_id'], '{{%shop_express}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['deliver_template_status', 1, '正常'],
            ['deliver_template_status', 9, '暂停'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'deliver_template_status']);
        $this->dropTable('{{%deliver_template}}');
    }
}
