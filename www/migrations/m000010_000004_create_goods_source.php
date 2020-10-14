<?php

use yii\db\Migration;

class m000010_000004_create_goods_source extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_source}}', [
            'id' => $this->primaryKey(), // PK
            'cid' => $this->integer(), // 分类编号
            'gid' => $this->integer(), // 商品编号
            'name' => $this->string(32), // 名称
            'desc' => $this->string(512), // 描述
            'img_list' => $this->text(), // 九宫格图片json列表
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_goods_source_cid1_idx', '{{%goods_source}}', ['cid']);
        $this->createIndex('fk_goods_source_goods1_idx', '{{%goods_source}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_trace_video_goods1', '{{%goods_source}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_source_status', 1, '正常'],
            ['goods_source_status', 9, '弃用'],
            ['goods_source_status', 0, '删除'],
        ]);

        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_source_type', 1, '商品推广'],
            ['goods_source_type', 2, '营销素材'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'goods_source_status']);
        $this->delete('{{%key_map}}', ['t' => 'goods_source_type']);
        $this->dropTable('{{%goods_source}}');
    }
}
