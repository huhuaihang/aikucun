<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
/**
 * 用户消息
 * Class UserMessage
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property string $title 标题
 * @property string $content 内容
 * @property string $url 跳转路径
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 */
class UserMessage extends ActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_OLD = 9;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'title', 'content', 'status', 'create_time'], 'required'],
            [['title'], 'string', 'max' => 128],
            [['url'], 'string', 'max' => 512],
        ];
    }
    /**
     * 发送用户消息
     * @param $uid integer 用户id
     * @param $title string 标题
     * @param $url string 跳转链接
     * @param $content string 内容
     * @return int
     */
    public function  MessageSend($uid,$title,$url,$content)
    {

        $userMessage=new UserMessage();
        $userMessage->setAttributes(
            [
                'uid'=>$uid,
                'title' => $title,
                'url'=>$url,
                'content' =>$content,
                'status' => UserMessage::STATUS_NEW,
                'create_time' => time()
            ],false
        );
        if(!$userMessage->save())
        {
            Yii::warning('保存用户消息失败。');

        }
        return $userMessage->id;
    }


}
