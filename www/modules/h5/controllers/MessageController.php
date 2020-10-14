<?php

namespace app\modules\h5\controllers;

use Yii;
use yii\web\Response;

/**
 * 消息留言控制器
 * Class MessageController
 * @package app\modules\h5\controllers
 */
class MessageController extends BaseController
{
    /**
     * 消息中心
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        return $this->render('index');
    }

    /**
     * 系统消息列表
     * @return string|Response
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        return $this->render('list');
    }

    /**
     * 聊天页面
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionChat()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
/*
        if (!$this->isAjax() && !Yii::$app->request->isPjax) {
            $gid = $this->get('gid'); // 来源商品编号
            $order_no = $this->get('order_no'); // 来源订单号
            if (!empty($gid)) {
                $goods = Goods::findOne($gid);
                if (empty($goods)) {
                    throw new NotFoundHttpException('没有找到商品信息。');
                }
                if ($goods->sid != $shop->id) {
                    throw new BadRequestHttpException('参数错误。');
                }
                $message = new ChatMessage();
                $message->cid = $chat->id;
                $message->from = Chat::getMember($user);
                $message->type = ChatMessage::TYPE_GOODS;
                $message->message = json_encode([
                    'goods' => [
                        'id' => $goods->id,
                        'title' => $goods->title,
                        'main_pic' => $goods->main_pic,
                        'price' => $goods->price,
                        'stock' => $goods->stock,
                        'status' => $goods->status,
                    ],
                ]);
                $message->save();
            }
            if (!empty($order_no)) {
                // TODO：发送订单信息
            }
        }
*/
        return $this->render('chat');
    }
}
