<?php

use yii\db\Migration;

class m000006_000001_create_goods extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods}}', [
            'id' => $this->primaryKey(), // PK
            'type' => $this->integer(), // 类型
            'sid' => $this->integer(), // 店铺编号
            'tid' => $this->integer(), // 商品类型编号
            'cid' => $this->integer(), // 商品分类编号
            'scid' => $this->integer(), // 店铺商品分类编号
            'bid' => $this->integer(), // 商品品牌编号
            'title' => $this->string(128), // 标题
            'keywords' => $this->string(512), // 关键字
            'desc' => $this->string(512), // 描述
            'price' => $this->decimal(12, 2), // 价格
            'share_commission_type' => $this->integer(), // 佣金计算方式
            'share_commission_value' => $this->integer(), // 佣金或百分比
            'stock' => $this->integer(), // 库存
            'main_pic' => $this->string(128), // 主图
            'detail_pics' => $this->text(), // 详情图列表JSON
            'content' => $this->text(), // 详情
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
            'deliver_fee_type' => $this->integer(), // 运费计费方式
            'weight' => $this->decimal(12, 2), // 商品重量
            'bulk' => $this->decimal(12, 2), // 商品体积
            'remark' => $this->text(), // 备注
            'sale_time' => $this->integer(), // 上架时间
            'is_recommend' => $this->integer(), // 是否推荐
            'is_index' => $this->integer(), // 是否首页推荐
            'is_pack' => $this->integer(), // 是否礼包产品
        ]);
        $this->createIndex('fk_goods_shop1_idx', '{{%goods}}', ['sid']);
        try {
            $this->addForeignKey('fk_goods_shop1', '{{%goods}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_type1_idx', '{{%goods}}', ['tid']);
        try {
            $this->addForeignKey('fk_goods_type1', '{{%goods}}', ['tid'], '{{%goods_type}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_category1_idx', '{{%goods}}', ['cid']);
        try {
            $this->addForeignKey('fk_goods_category1', '{{%goods}}', ['cid'], '{{%goods_category}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_sgc1_idx', '{{%goods}}', ['scid']);
        try {
            $this->addForeignKey('fk_goods_sgc1', '{{%goods}}', ['scid'], '{{%shop_goods_category}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_brand1_idx', '{{%goods}}', ['bid']);
        try {
            $this->addForeignKey('fk_goods_brand1', '{{%goods}}', ['bid'], '{{%goods_brand}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_type', 1, '线上商品'],
            ['goods_type', 2, '线下商品'],
            ['goods_share_commission_type', 0, '分享没有佣金'],
            ['goods_share_commission_type', 1, '固定金额'],
            ['goods_share_commission_type', 2, '价格比例'],
            ['goods_status', 1, '上架'],
            ['goods_status', 9, '下架'],
            ['goods_status', 0, '删除'],
            ['goods_deliver_fee_type', 1, '重量'],
            ['goods_deliver_fee_type', 2, '体积'],
            ['goods_deliver_fee_type', 3, '件数'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'goods_deliver_fee_type']);
        $this->delete('{{%key_map}}', ['t' => 'goods_status']);
        $this->delete('{{%key_map}}', ['t' => 'goods_share_commission_type']);
        $this->delete('{{%key_map}}', ['t' => 'goods_type']);
        $this->dropTable('{{%goods}}');
    }
}
