<?php

namespace app\modules\admin\controllers;

use app\models\Chat;
use app\models\ChatMember;
use app\models\ChatMessage;
use app\models\MemQueue;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\db\Query;
use yii\web\NotFoundHttpException;

/**
 * 聊天消息
 * Class MessageController
 * @package app\modules\admin\controllers
 */
class MessageController extends BaseController
{
    /**
     * 留言列表
     * @return string
     */
    public function actionList()
    {
        $query = (new Query())
            ->select([
                'id' => 'CHAT.id',
                'last_msg_id' => 'max(MESSAGE.id)',
            ])
            ->from(Chat::tableName() . ' CHAT')
            ->leftJoin(ChatMember::tableName() . ' MEMBER', 'MEMBER.cid = CHAT.id')
            ->leftJoin(ChatMessage::tableName() . ' MESSAGE', 'MESSAGE.cid = CHAT.id')
            ->groupBy('CHAT.id');
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $chat_list = $query->orderBy('last_msg_id DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'chat_list' => $chat_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 聊天界面
     * @return string
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionChat()
    {
        $id = $this->get('id');
        $chat = Chat::findOne($id);
        if (empty($chat)) {
            throw new NotFoundHttpException('没有找到聊天信息。');
        }
        return $this->render('chat', [
            'chat' => $chat,
            'last_read_msg_id' => $this->get('last_read_msg_id', 0),
        ]);
    }

    /**
     * 发送消息AJAX接口
     * @return array
     */
    public function actionSend()
    {
        $message = new ChatMessage();
        $message->type = ChatMessage::TYPE_TEXT;
        if (!$message->load($this->post())) {
            return ['message' => '参数错误。'];
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            /** @var ChatMember $shopMember */
            $shopMember = ChatMember::find()->andWhere(['cid' => $message->cid])->andWhere(['like', 'member', 'shop_%', false])->one();
            $message->from = $shopMember->member;
            if (!$message->save()) {
                $errors = $message->errors;
                throw new Exception(array_shift($errors)[0]);
            }
            $trans->commit();
            $queue = new MemQueue('chat_msg_' . YII_ENV);
            $queue->add([
                'to_uid' => Chat::getModel($message->to)->id,
                'data' => [
                    'type' => 'chat_msg',
                    'msg' => [
                        'id' => $message->id,
                        'cid' => $message->cid,
                        'from' => $message->from,
                        'to' => $message->to,
                        'type' => $message->type,
                        'content' => $message->message,
                        'create_time' => $message->create_time,
                    ],
                ],
            ]);
            return ['result' => 'success'];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return ['message' => $e->getMessage()];
        }
    }
}
