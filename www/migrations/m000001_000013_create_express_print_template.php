<?php

use yii\db\Migration;

class m000001_000013_create_express_print_template extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%express_print_template}}', [
            'id' => $this->primaryKey(), // PK
            'eid' => $this->integer(), // 快递编号
            'name' => $this->string(128), // 名称
            'background_image' => $this->string(128), // 背景图片
            'width' => $this->integer(), // 宽度毫米
            'height' => $this->integer(), // 高度毫米
            'offset_top' => $this->integer(), // 顶部偏移像素
            'offset_left' => $this->integer(), // 左侧偏移像素
            'template' => $this->text(), // 模板JSON
        ]);
        $this->createIndex('fk_express_print_template_express1_idx', '{{%express_print_template}}', ['eid']);
        try {
            $this->addForeignKey('fk_express_print_template_express1', '{{%express_print_template}}', ['eid'], '{{%express}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%express_print_template}}');
    }
}
