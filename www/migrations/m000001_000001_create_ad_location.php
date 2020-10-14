<?php

use yii\db\Migration;

class m000001_000001_create_ad_location extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%ad_location}}', [
            'id' => $this->primaryKey(), // PK
            'type' => $this->integer(), // 类型
            'name' => $this->string(128), // 位置说明
            'max_count' => $this->integer(), // 最大展示数量
            'width' => $this->integer(), // 图片宽度
            'height' => $this->integer(), // 图片高度
            'code' => $this->text(), // 广告展示Smarty代码
            'remark' => $this->text(), // 备注
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['ad_type', 1, '文本'],
            ['ad_type', 2, '图片'],
            ['ad_type', 3, '商品'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'ad_type']);
        $this->dropTable('{{%ad_location}}');
    }
}
