<?php

use yii\db\Migration;

class m000010_000003_create_goods_trace_video extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_trace_video}}', [
            'id' => $this->primaryKey(), // PK
            'cid' => $this->integer(), // 视频分类编号
            'desc' => $this->string(512), // 描述
            'gid' => $this->integer(), // 商品编号
            'name' => $this->string(32), // 名称
            'cover_image' => $this->string(128), // 封面图片地址
            'video' => $this->string(128), // video
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_goods_trace_video_cid1_idx', '{{%goods_trace_video}}', ['cid']);
        $this->createIndex('fk_goods_trace_video_goods1_idx', '{{%goods_trace_video}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_trace_video_goods1', '{{%goods_trace_video}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_trace_video}}');
    }
}
