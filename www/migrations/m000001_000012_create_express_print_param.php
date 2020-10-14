<?php

use yii\db\Migration;

class m000001_000012_create_express_print_param extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%express_print_param}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(128), // 名称
        ]);
        $this->batchInsert('{{%express_print_param}}', ['name'], [
            ['发货点-名称'],
            ['发货点-联系人'],
            ['发货点-电话'],
            ['发货点-省份'],
            ['发货点-城市'],
            ['发货点-区县'],
            ['发货点-手机'],
            ['发货点-详细地址'],
            ['收件人-姓名'],
            ['收件人-手机'],
            ['收件人-电话'],
            ['收件人-省份'],
            ['收件人-城市'],
            ['收件人-区县'],
            ['收件人-邮编'],
            ['收件人-详细地址'],
            ['时间-年'],
            ['时间-月'],
            ['时间-日'],
            ['时间-当前日期'],
            ['订单-订单号'],
            ['订单-备注'],
            ['订单-配送费用'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%express_print_param}}');
    }
}
