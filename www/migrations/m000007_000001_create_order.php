<?php

use yii\db\Migration;

class m000007_000001_create_order extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(), // PK
            'no' => $this->string(128), // 订单号
            'uid' => $this->integer(), // 用户编号
            'sid' => $this->integer(), // 店铺编号
            'fid' => $this->integer(), // 财务记录编号
            'cancel_fid' => $this->integer(), // 取消订单退款财务记录编号
            'deliver_info' => $this->string(1024), // 收货信息JSON
            'deliver_fee' => $this->decimal(12, 2), // 物流费用
            'goods_money' => $this->decimal(12, 2), // 商品金额
            'amount_money' => $this->decimal(12, 2), // 订单总金额
            'self_buy_money' => $this->decimal(12, 2), // 自购优惠总金额
            'user_remark' => $this->string(128), // 用户备注
            'merchant_remark' => $this->string(512), // 商户备注
            'status' => $this->integer(), // 订单状态
            'create_time' => $this->integer(), // 创建时间
            'receive_time' => $this->integer(), // 确认收货时间
            'delete_time' => $this->integer(), // 删除时间
        ]);
        $this->createIndex('idx_order_no', '{{%order}}', ['no'], true);
        $this->createIndex('fk_order_user1_idx', '{{%order}}', ['uid']);
        try {
            $this->addForeignKey('fk_order_user1', '{{%order}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_order_shop1_idx', '{{%order}}', ['sid']);
        try {
            $this->addForeignKey('fk_order_shop1', '{{%order}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_order_finance_log1_idx', '{{%order}}', ['fid']);
        try {
            $this->addForeignKey('fk_order_finance_log1', '{{%order}}', ['fid'], '{{%finance_log}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_order_finance_log2_idx', '{{%order}}', ['cancel_fid']);
        try {
            $this->addForeignKey('fk_order_finance_log2', '{{%order}}', ['cancel_fid'], '{{%finance_log}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['order_status', 1, '已创建待支付'],
            ['order_status', 2, '已支付待配货'],
            ['order_status', 3, '配货中'],
            ['order_status', 4, '已配货待发货'],
            ['order_status', 5, '已发货待收货'],
            ['order_status', 6, '已收货待评价'],
            ['order_status', 7, '订单完成'],
            ['order_status', 8, '售后处理中'],
            ['order_status', 91, '订单取消待商户审核'],
            ['order_status', 92, '订单取消待管理审核'],
            ['order_status', 9, '订单取消'],
            ['order_status', 0, '已删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'order_status']);
        $this->dropTable('{{%order}}');
    }
}
