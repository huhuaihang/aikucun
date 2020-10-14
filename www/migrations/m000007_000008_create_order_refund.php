<?php

use yii\db\Migration;

class m000007_000008_create_order_refund extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order_refund}}', [
            'id' => $this->primaryKey(), // PK
            'oiid' => $this->integer(), // 订单内容编号
            'amount' => $this->integer(), // 商品数量
            'money' => $this->decimal(12, 2), // 退款金额
            'type' => $this->integer(), // 类型
            'reason' => $this->text(), // 原因
            'image_list' => $this->text(), // 图片列表JSON
            'fid' => $this->integer(), // 财务记录编号
            'status' => $this->integer(), // 状态
            'express_name' => $this->string(32), // 快递名称
            'express_no' => $this->string(32), // 快递单号
            'contact_mobile' => $this->string(32), // 联系手机
            'create_time' => $this->integer(), // 创建时间
            'apply_time' => $this->integer(), // 同意时间
            'send_time' => $this->integer(), // 发货时间
            'receive_time' => $this->integer(), // 收货时间
            'complete_time' => $this->integer(), // 完成时间
            'reject_time' => $this->integer(), // 拒绝时间
            'delete_time' => $this->integer(), // 删除时间
        ]);
        $this->createIndex('fk_order_refund_order_item1_idx', '{{%order_refund}}', ['oiid']);
        try {
            $this->addForeignKey('fk_order_refund_order_item1', '{{%order_refund}}', ['oiid'], '{{%order_item}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_order_refund_finance_log1_idx', '{{%order_refund}}', ['fid']);
        try {
            $this->addForeignKey('fk_order_refund_finance_log1', '{{%order_refund}}', ['fid'], '{{%finance_log}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['order_refund_type', 1, '退款'],
            ['order_refund_type', 2, '退货退款'],
            ['order_refund_type', 3, '换货'],
            ['order_refund_type', 4, '补发'],
            ['order_refund_status', 1, '买家申请等待卖家同意'],
            ['order_refund_status', 2, '卖家同意等待买家发货'],
            ['order_refund_status', 3, '买家已发货等待卖家收货'],
            ['order_refund_status', 4, '卖家已收货等待退款'],
            ['order_refund_status', 5, '退款成功售后完成'],
            ['order_refund_status', 9, '卖家拒绝'],
            ['order_refund_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'order_refund_status']);
        $this->delete('{{%key_map}}', ['t' => 'order_refund_type']);
        $this->dropTable('{{%order_refund}}');
    }
}
