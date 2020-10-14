<?php

use yii\db\Migration;

class m000005_000018_create_merchant_goods_category extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%merchant_goods_category}}', [
            'id' => $this->primaryKey(), // PK
            'mid' => $this->integer(), // 商户编号
            'cid_list' => $this->string(512), // 商品分类编号列表JSON
            'quality_inspection_report' => $this->text(), // 质检报告文件列表JSON
            'authorization_certificate' => $this->text(), // 销售授权书/进货发票文件列表JSON
            'industry_qualification' => $this->text(), // 行业资质文件列表JSON
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_merchant_goods_category_merchant1_idx', '{{%merchant_goods_category}}', ['mid']);
        try {
            $this->addForeignKey('fk_merchant_goods_category_merchant1', '{{%merchant_goods_category}}', ['mid'], '{{%merchant}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['merchant_goods_category_status', 1, '待审核'],
            ['merchant_goods_category_status', 2, '正常'],
            ['merchant_goods_category_status', 9, '审核拒绝'],
            ['merchant_goods_category_status', 0, '拒绝'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'merchant_goods_category_status']);
        $this->dropTable('{{%merchant_goods_category}}');
    }
}
