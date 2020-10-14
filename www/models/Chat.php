<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * 聊天
 * Class Chat
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $type 类型
 * @property string $create_member 创建人
 * @property integer $create_time 创建时间
 *
 * @property ChatMember[] $memberList 关联成员列表
 */
class Chat extends ActiveRecord
{
    const TYPE_CHAT = 1; // 单聊
    const TYPE_GROUP = 2; // 群聊

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'create_member', 'create_time'], 'required'],
            [['create_member'], 'string', 'max' => 128],
        ];
    }

    /**
     * 关联成员列表
     * @return \yii\db\ActiveQuery
     */
    public function getMemberList()
    {
        return $this->hasMany(ChatMember::className(), ['cid' => 'id']);
    }

    /**
     * 根据Model确定聊天Member名称
     * @param $model Model
     * @return string
     * @throws Exception
     */
    public static function getMember($model)
    {
        if ($model instanceof User) {
            return 'user_' . $model->id;
        } elseif ($model instanceof Shop) {
            return 'shop_' . $model->id;
        } else {
            throw new Exception('无法确定用户类型');
        }
    }

    /**
     * 根据Member名称返回Model
     * @param $member
     * @return User|Shop
     * @throws Exception
     */
    public static function getModel($member)
    {
        $member = explode('_', $member);
        switch ($member[0]) {
            case 'user':
                $user = Yii::$app->cache->get('chat_user_' . $member[1]);
                if (empty($user)) {
                    $user = User::findOne($member[1]);
                    Yii::$app->cache->set('chat_user_' . $member[1], $user);
                }
                return $user;
            case 'shop':
                $shop = Yii::$app->cache->get('chat_shop_' . $member[1]);
                if (empty($shop)) {
                    $shop = Shop::findOne($member[1]);
                    Yii::$app->cache->set('chat_shop_' . $member[1], $shop);
                }
                return $shop;
        }
        throw new Exception('找不到对象。');
    }

    /**
     * 查找用户和店铺的聊天
     * @param $user User 用户
     * @param $shop Shop 店铺
     * @param $create boolean 如果没有找到，是否创建
     * @return null|Chat
     * @throws Exception
     */
    public static function findUserShopChat($user, $shop, $create = false)
    {
        foreach (ChatMember::find()
            ->andWhere(['member' => Chat::getMember($user)])
            ->orderBy('id DESC')
            ->each() as $chat_member) {
            /** @var ChatMember $chat_member */
            if ($chat_member->chat->type != Chat::TYPE_CHAT) {
                continue;
            }
            if ($chat_member->chat->getMemberList()->andWhere(['member' => Chat::getMember($shop)])->exists()) {
                return $chat_member->chat;
            }
        }
        if (!$create) {
            return null;
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $chat = new Chat();
            $chat->type = Chat::TYPE_CHAT;
            $chat->create_member = Chat::getMember($user);
            $chat->create_time = time();
            $r = $chat->save();
            if (!$r) {
                throw new Exception('无法保存聊天信息。');
            }
            $chat_member = new ChatMember();
            $chat_member->cid = $chat->id;
            $chat_member->type = ChatMember::TYPE_OWNER;
            $chat_member->member = Chat::getMember($user);
            $chat_member->create_time = time();
            $r = $chat_member->save();
            if (!$r) {
                throw new Exception('无法保存聊天成员。');
            }
            $chat_member = new ChatMember();
            $chat_member->cid = $chat->id;
            $chat_member->type = ChatMember::TYPE_MEMBER;
            $chat_member->member = Chat::getMember($shop);
            $chat_member->create_time = time();
            $r = $chat_member->save();
            if (!$r) {
                throw new Exception('无法保存聊天成员。');
            }
            $trans->commit();
            return $chat;
        } catch (Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }
}
