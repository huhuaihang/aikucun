<?php

use yii\db\Migration;

class m000001_000002_create_ad extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%ad}}', [
            'id' => $this->primaryKey(), // PK
            'lid' => $this->integer(), // 位置编号
            'name' => $this->string(128), // 广告名称
            'txt' => $this->string(128), // 文字内容
            'img' => $this->string(128), // 图片地址
            'url' => $this->string(512), // 链接地址
            'start_time' => $this->integer(), // 开始时间
            'end_time' => $this->integer(), // 结束时间
            'sort' => $this->integer(), // 排序数字
            'status' => $this->integer(), // 状态
            'show' => $this->integer()->defaultValue(0), // 展示次数
            'click' => $this->integer()->defaultValue(0), // 点击次数
        ]);
        $this->createIndex('fk_ad_ad_location1_idx', '{{%ad}}', ['lid']);
        try {
            $this->addForeignKey('fk_ad_ad_location1', '{{%ad}}', ['lid'], '{{%ad_location}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['ad_status', 1, '正常'],
            ['ad_status', 9, '暂停'],
            ['ad_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'ad_status']);
        $this->dropTable('{{%ad}}');
    }
}
