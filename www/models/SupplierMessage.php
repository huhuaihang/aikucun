<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 供货商消息
 * Class SupplierMessage
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 商户编号
 * @property integer $smid 系统消息编号
 * @property string $title 标题
 * @property string $content 内容
 * @property integer $time 添加时间
 * @property integer $status 状态
 */
class SupplierMessage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sid', 'title', 'content', 'time', 'status'], 'required'],
            ['title', 'string', 'max' => 128],
            ['content', 'safe'],
        ];
    }

    /**
     * 检查是否有新的消息
     * @param $sid integer 供货商编号
     * @return bool
     */
    public static function checkNewMessage($sid)
    {
        $smid = SystemMessage::find()
            ->andWhere(['<=', 'time', time()])
            ->andWhere('(target_type & ' . SystemMessage::TARGET_SUPPLIER . ') > 0')
            ->max('id');
        if ($smid == 0) { // 没有新的系统消息
            return false;
        }
        $max_smid = SupplierMessage::find()->where(['sid' => $sid])->max('smid');
        $max_smid = intval($max_smid);
        if ($max_smid >= $smid) { // 系统消息已经全部接收到了
            return false;
        }
        /** @var SystemMessage $system_message */
        foreach (SystemMessage::find()
                     ->andWhere(['<=', 'time', time()])
                     ->andWhere(['>', 'id', $max_smid])
                     ->andWhere('(target_type & ' . SystemMessage::TARGET_SUPPLIER . ') > 0')
                     ->each() as $system_message) {
            $supplier_message = new SupplierMessage();
            $supplier_message->sid = $sid;
            $supplier_message->smid = $system_message->id;
            $supplier_message->title = $system_message->title;
            $supplier_message->content = $system_message->content;
            $supplier_message->time = $system_message->time;
            $supplier_message->status = SystemMessage::STATUS_UNREAD;
            $supplier_message->save();
        }
        return true;
    }
}
