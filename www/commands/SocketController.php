<?php

namespace app\commands;

use app\models\Chat;
use app\models\ChatMember;
use app\models\ChatMessage;
use app\models\Goods;
use app\models\MemQueue;
use app\models\Shop;
use app\models\ShopConfig;
use app\models\User;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\Console;

define('HEARTBEAT_TIME', 100);

/**
 * Socket接口
 * Class SocketController
 * @package app\commands
 */
class SocketController extends Controller
{
    private $worker;
    private static $socket_data;
    private static $uid_conn_id;
    private static $msg_id;

    const TYPE_RESPONSE = 'response';     // 返回消息
    const TYPE_HEART_BEAT = 'heart_beat'; // 心跳包
    const TYPE_CHAT_MSG = 'chat_msg';     // 聊天消息
    const TYPE_COMMAND = 'command';       // 命令

    const ERROR_NONE      = 0;     // 没有错误
    const ERROR_LOGIN     = 20000; // 用户没有登录或登录Token失效，需要重新登录
    const ERROR_JSON      = 20001; // 解析JSON失败，需要重新发送
    const ERROR_TYPE      = 20002; // 消息类型不支持
    const ERROR_CHAT_TYPE = 20003; // 无法确定聊天消息类型
    const ERROR_CHAT_CID  = 20004; // 无法找到聊天对话信息
    const ERROR_SAVE_CHAT = 20005; // 无法保存聊天信息
    const ERROR_EMPTY     = 20006; // 没有返回值

    const CHAT_MSG_TYPE_TEXT = 'text'; // 文本消息
    const CHAT_MSG_TYPE_GOODS = 'goods'; // 商品消息

    /**
     * @inheritdoc
     */
    public function init()
    {
        static::$socket_data = [];
        static::$uid_conn_id = [];
        parent::init();
    }

    /**
     * 监听端口
     * @return int
     */
    public static function getPort()
    {
        return 2345;
    }

    /**
     * 入口
     */
    public function actionIndex()
    {
        $this->worker = new Worker('Websocket://0.0.0.0:' . SocketController::getPort());
        $this->worker->count = 1;
        $this->worker->onWorkerStart = function ($worker) {
            $this->onWorkerStart($worker);
        };
        $this->worker->onConnect = function ($connection) {
            $this->onConnection($connection);
        };
        $this->worker->onMessage = function ($connection, $data) {
            try {
                $this->onMessage($connection, $data);
            } catch (\Exception $e) {
                $this->stderr($e->getMessage() . chr(10));
            }
        };
        $this->worker->onClose = function ($connection) {
            $this->onClose($connection);
        };
        $this->worker->onError = function ($connection, $code, $msg) {};
        $this->worker->onBufferFull = function ($connection) {};
        $this->worker->onBufferDrain = function ($connection) {};
        $this->worker->onWorkerStop = function ($worker) {};
        $this->worker->onWorkerReload = function ($worker) {};

        global $argv;
        array_splice($argv, 1, 1);
        // Worker::$daemonize = true;
        Worker::runAll();
    }

    /**
     * 服务启动，增加定时器检查超时（没有心跳）自动断开
     * @param $worker Worker
     */
    private function onWorkerStart($worker)
    {
        // 超时检测
        Timer::add(HEARTBEAT_TIME, function () use ($worker) {
            $time = time();
            foreach ($worker->connections as $conn) {/** @var TcpConnection $conn */
                if ($time - static::$socket_data[$conn->id]['lastMessageTime'] > HEARTBEAT_TIME) {
                    if (static::$socket_data[$conn->id]['timeoutCount'] >= 3) {
                        // 超时状态，则主动断开链接
                        $conn_id = $conn->id;
                        $conn->close();
                        try {
                            $uid = static::$socket_data[$conn_id]['uid'];
                            unset(static::$uid_conn_id[$uid][$conn_id]);
                        } catch (\Exception $e) {
                        }
                        try {
                            unset(static::$socket_data[$conn_id]);
                        } catch (\Exception $e) {
                        }
                        continue;
                    }
                    static::$socket_data[$conn->id]['timeoutCount'] += 1;
                    $this->stdout(date('Y-m-d H:i:s') . ' ' . $conn->id . ' ' . $conn->getRemoteAddress() . chr(9) . static::$socket_data[$conn->id]['uid'] . chr(9) . ' Timeout ' . static::$socket_data[$conn->id]['timeoutCount'] . chr(10), Console::FG_RED);
                    // 发送一次心跳
                    $this->send($conn, json_encode([
                        'msg_id' => static::getMsgId(),
                        'type' => static::TYPE_HEART_BEAT,
                    ]));
                }
            }
        });
        // 新消息推送
        Timer::add(1, function () use ($worker) {
            $queue = new MemQueue('chat_msg_' . YII_ENV);
            while (true) {
                $list = $queue->get(100);
                if (empty($list)) {
                    break;
                }
                foreach ($list as $item) {
                    $uid = $item['to_uid'];
                    if (!isset(static::$uid_conn_id[$uid])) {
                        // 用户没有登录
                        // $queue->add($item);
                        continue;
                    }
                    foreach (static::$uid_conn_id[$uid] as $conn_id => $v) {
                        /** @var TcpConnection $connection */
                        $connection = $worker->connections[$conn_id];
                        $data = $item['data'];
                        $data['error_code'] = static::ERROR_NONE;
                        $data['msg_id'] = static::getMsgId();
                        $this->send($connection, json_encode($data));
                    }
                }
            }
        });
        // 定时显示统计信息
        Timer::add(100, function () use ($worker) {
            $sql_time = Yii::$app->db->createCommand('SELECT NOW() AS time')->queryColumn()[0];
            $this->stdout($sql_time . ' ' . $worker->count . ' ' . count($worker->connections) . chr(10), Console::FG_CYAN);
        });
    }

    /**
     * 新连接
     * @param $connection TcpConnection
     */
    private function onConnection($connection)
    {
        $this->stdout(date('Y-m-d H:i:s') . ' ' . $connection->id . ' ' . $connection->getRemoteAddress() . chr(9) . 'New connection.' . chr(10), Console::FG_GREEN);
        /** @var TcpConnection $connection */
        $connection->onWebSocketConnect = function ($connection) {
            $this->stdout(date('Y-m-d H:i:s') . ' ' . $connection->id . ' ' . $connection->getRemoteAddress() . chr(9) . 'New WebSocket connection.' . chr(10), Console::FG_GREEN);
            $version = isset($_GET['version']) ? $_GET['version'] : '';
            $token = isset($_GET['token']) ? $_GET['token'] : '';
            if (empty($version)) {
                $this->send($connection, json_encode([
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_LOGIN,
                    'message' => '没有找到版本号参数。',
                ]));
                $connection->close();
                return;
            }
            if (empty($token)) {
                $this->send($connection, json_encode([
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_LOGIN,
                    'message' => '没有找到Token参数。',
                ]));
                $connection->close();
                return;
            }
            try {
                $user = User::findByToken($version, $token);
            } catch (Exception $e) {
                $this->send($connection, json_encode([
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_LOGIN,
                    'message' => 'Token失效或错误，没有找到对应用户信息，请使用接口重新登录获取Token。',
                    'version' => $version,
                    'token' => $token,
                    'errors' => $e->getMessage(),
                ]));
                $connection->close();
                return;
            }
            $this->stdout(date('Y-m-d H:i:s') . ' ' . $connection->id . ' ' . $connection->getRemoteAddress() . chr(9) . $user->id . chr(9) . 'User Login' . chr(10), Console::FG_GREEN);
            static::$socket_data[$connection->id]['uid'] = $user->id;
            static::$socket_data[$connection->id]['address'] = $connection->getRemoteAddress();
            static::$socket_data[$connection->id]['lastMessageTime'] = time();
            static::$socket_data[$connection->id]['timeoutCount'] = 0;
            static::$uid_conn_id[$user->id][$connection->id] = $connection->id;

            $this->checkPush($connection);
        };
    }

    /**
     * 检查需要推送的数据
     * @param $connection TcpConnection
     */
    private function checkPush($connection)
    {
        // TODO:检查是否有新的需要推送的信息
    }

    /**
     * 收到消息
     * @param $connection TcpConnection
     * @param $data mixed
     * @throws Exception
     */
    private function onMessage($connection, $data)
    {
        static::$socket_data[$connection->id]['lastMessageTime'] = time();
        $this->stdout(date('Y-m-d H:i:s') . ' ' . $connection->id . ' ' . $connection->getRemoteAddress() . chr(9) . static::$socket_data[$connection->id]['uid'] . chr(9) . $data . chr(10), Console::FG_GREEN);
        $json = json_decode($data, true);
        if (empty($json)) {
            $this->send($connection, json_encode([
                'type' => static::TYPE_RESPONSE,
                'error_code' => static::ERROR_JSON,
                'message' => '无法解析数据，请重新发送。',
            ]));
            return;
        }
        $msg_id = isset($json['msg_id']) ? $json['msg_id'] : null;
        $response = [
            'type' => static::TYPE_RESPONSE,
            'msg_id' => $msg_id,
            'error_code' => static::ERROR_EMPTY,
        ];
        switch ($json['type']) {
            case static::TYPE_HEART_BEAT: // 心跳包
                $response = [
                    'type' => static::TYPE_RESPONSE,
                    'msg_id' => $msg_id,
                    'error_code' => static::ERROR_NONE,
                ];
                break;
            case static::TYPE_COMMAND: // 命令
                $response = $this->onCommand($connection, $json);
                $response['msg_id'] = $msg_id;
                break;
            case static::TYPE_CHAT_MSG: // 聊天消息
                $response = $this->onChatMsg($connection, $json);
                $response['msg_id'] = $msg_id;
                break;
            case static::TYPE_RESPONSE: // 消息结果
                break;
            default:
                $response = [
                    'type' => static::TYPE_RESPONSE,
                    'msg_id' => $msg_id,
                    'error_code' => static::ERROR_TYPE,
                    'message' => '无法确定消息类型。',
                ];
        }
        $response = json_encode($response);
        $this->send($connection, $response);
    }

    /**
     * 连接关闭
     * @param $connection TcpConnection
     */
    private function onClose($connection)
    {
        $conn_id = $connection->id;
        try {
            $uid = static::$socket_data[$conn_id]['uid'];
            unset(static::$uid_conn_id[$uid][$conn_id]);
        } catch (\Exception $e) {
        }
        try {
            unset(static::$socket_data[$conn_id]);
        } catch (\Exception $e) {
        }
        $this->stdout(date('Y-m-d H:i:s') . ' ' . $connection->id . ' ' . $connection->getRemoteAddress() . chr(9) . 'Closed.' . chr(10), Console::FG_YELLOW);
    }

    /**
     * 收到命令
     * @param $connection TcpConnection
     * @param $json array
     * @return array
     * @throws Exception
     */
    private function onCommand($connection, $json)
    {
        if (!isset($json['command'])) {
            return [
                'type' => static::TYPE_RESPONSE,
                'error_code' => static::ERROR_JSON,
                'message' => '没有找到命令内容信息。',
            ];
        }
        switch ($json['command']) {
            case 'get_chat_list': // 获取聊天会话列表
                $user = User::findOne(static::$socket_data[$connection->id]['uid']);
                $member = Chat::getMember($user);
                $chat_list = (new Query())
                    ->select([
                        'id' => 'CHAT.id',
                        'last_read_msg_id' => 'MEMBER.last_read_msg_id',
                        'last_msg_id' => 'max(MESSAGE.id)',
                    ])
                    ->from(Chat::tableName() . ' CHAT')
                    ->leftJoin(ChatMember::tableName() . ' MEMBER', 'MEMBER.cid = CHAT.id')
                    ->leftJoin(ChatMessage::tableName() . ' MESSAGE', 'MESSAGE.cid = CHAT.id')
                    ->andWhere(['MEMBER.member' => $member])
                    ->groupBy('CHAT.id, MEMBER.last_read_msg_id')
                    ->orderBy('last_msg_id DESC')
                    ->all();
                array_walk($chat_list, function (&$chat) use ($member) {
                    /** @var ChatMember $shop_member */
                    $shop_member = ChatMember::find()->andWhere(['cid' => $chat['id']])->andWhere(['<>', 'member', $member])->one();
                    $shop = Chat::getModel($shop_member->member);
                    $chat['shop'] = [
                        'id' => $shop->id,
                        'name' => $shop->name,
                        'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($shop->id, 'logo'),
                    ];
                    if (!empty($chat['last_msg_id'])) {
                        $message = ChatMessage::findOne($chat['last_msg_id']);
                        $chat['last_msg'] = [
                            'id' => $message->id,
                            'from' => $message->from,
                            'to' => $message->to,
                            'type' => $message->type,
                            'content' => $message->message,
                            'create_time' => $message->create_time,
                        ];
                    } else {
                        $chat['last_msg_id'] = '';
                    }
                    if (empty($chat['last_read_msg_id'])) {
                        $chat['last_read_msg_id'] = '';
                    }
                });
                return [
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_NONE,
                    'member' => $member,
                    'chat_list' => $chat_list,
                ];
            case 'get_chat': // 获取聊天会话，如果会话不存在，自动创建
                $sid = $json['sid'];
                $shop = Shop::findOne($sid);
                if (empty($shop)) {
                    return [
                        'type' => static::TYPE_RESPONSE,
                        'error_code' => static::ERROR_SAVE_CHAT,
                        'message' => '没有找到店铺信息。',
                    ];
                }
                $user = User::findOne(static::$socket_data[$connection->id]['uid']);
                $chat = Chat::findUserShopChat($user, $shop, true);
                return [
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_NONE,
                    'member' => Chat::getMember($user),
                    'cid' => $chat->id,
                ];
            case 'get_chat_member_list': // 获取聊天会话成员列表
                $cid = $json['cid'];
                $chat = Chat::findOne($cid);
                if (empty($chat)) {
                    return [
                        'type' => static::TYPE_RESPONSE,
                        'error_code' => static::ERROR_EMPTY,
                        'message' => '没有找到会话信息。',
                    ];
                }
                $member_list = [];
                /** @var ChatMember $member */
                foreach ($chat->getMemberList()->each() as $member) {
                    $data = [];
                    $model = Chat::getModel($member->member);
                    if ($model instanceof User) {
                        $data = [
                            'id' => $model->id,
                            'name' => $model->nickname,
                            'avatar' => $model->getRealAvatar(true),
                        ];
                    } elseif ($model instanceof Shop) {
                        $data = [
                            'id' => $model->id,
                            'name' => $model->name,
                            'avatar' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($model->id, 'logo'),
                        ];
                    }
                    $member_list[] = [
                        'id' => $member->id,
                        'type' => $member->type,
                        'member' => $member->member,
                        'data' => $data,
                    ];
                }
                return [
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_NONE,
                    'member' => Chat::getMember(User::findOne(static::$socket_data[$connection->id]['uid'])),
                    'member_list' => $member_list,
                ];
            case 'get_chat_msg': // 获取聊天记录
                $chat = Chat::findOne($json['cid']);
                if (empty($chat)) {
                    return [
                        'type' => static::TYPE_RESPONSE,
                        'error_code' => static::ERROR_CHAT_CID,
                        'message' => '没有找到聊天会话信息。',
                    ];
                }
                $user = User::findOne(static::$socket_data[$connection->id]['uid']);
                $from = Chat::getMember($user);
                if (!$chat->getMemberList()->andWhere(['member' => $from])->exists()) {
                    return [
                        'type' => static::TYPE_RESPONSE,
                        'error_code' => static::ERROR_CHAT_CID,
                        'message' => '用户不属于此聊天对话。',
                    ];
                }
                $query = ChatMessage::find();
                $query->andWhere(['cid' => $chat->id]);
                $query->orderBy('create_time DESC, id DESC');
                if (isset($json['end_id']) && $json['end_id'] > 0) {
                    $query->andWhere(['<', 'id', $json['end_id']]);
                }
                $query->limit(10);
                $msg_list = [];
                foreach ($query->each() as $msg) {/** @var ChatMessage $msg */
                    if ($msg->type == ChatMessage::TYPE_TEXT) {
                        $msg_list[] = [
                            'id' => $msg->id,
                            'from' => $msg->from,
                            'to' => $msg->to,
                            'type' => $msg->type,
                            'content' => $msg->message,
                            'create_time' => $msg->create_time,
                        ];
                    } elseif ($msg->type == ChatMessage::TYPE_GOODS) {
                        $json = json_decode($msg->message, true);
                        $json['goods']['main_pic'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $json['goods']['main_pic'];
                        $msg_list[] = [
                            'id' => $msg->id,
                            'from' => $msg->from,
                            'to' => $msg->to,
                            'type' => $msg->type,
                            'content' => $msg->message,
                            'goods' => $json['goods'],
                            'create_time' => $msg->create_time,
                        ];
                    }
                }
                return [
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_NONE,
                    'msg_list' => $msg_list,
                ];
            default:
                return [
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_JSON,
                    'message' => '无法确定命令类型。',
                ];
        }
    }

    /**
     * 收到聊天消息
     * @param $connection TcpConnection
     * @param $json array
     * @return array
     * @throws Exception
     */
    private function onChatMsg($connection, $json)
    {
        if (!isset($json['msg'])) {
            return [
                'type' => static::TYPE_RESPONSE,
                'error_code' => static::ERROR_JSON,
                'message' => '没有找到聊天内容信息。',
            ];
        }
        $msg = $json['msg'];
        $chat = Chat::findOne($msg['cid']);
        if (empty($chat)) {
            return [
                'type' => static::TYPE_RESPONSE,
                'error_code' => static::ERROR_CHAT_CID,
                'message' => '没有找到对话内容，请先创建对话。',
            ];
        }
        $user = User::findOne(static::$socket_data[$connection->id]['uid']);
        $from = Chat::getMember($user);
        if (!$chat->getMemberList()->andWhere(['member' => $from])->exists()) {
            return [
                'type' => static::TYPE_RESPONSE,
                'error_code' => static::ERROR_CHAT_CID,
                'message' => '用户不属于此聊天对话。',
            ];
        }
        switch ($msg['type']) {
            case static::CHAT_MSG_TYPE_GOODS:
                $content = $msg['content'];
                if (empty($content)) {
                    return [
                        'type' => static::TYPE_RESPONSE,
                        'error_code' => static::ERROR_JSON,
                        'message' => '聊天内容为空。',
                    ];
                }
                $json = json_decode($content, true);
                if (empty($json)) {
                    return [
                        'type' => static::TYPE_RESPONSE,
                        'error_code' => static::ERROR_JSON,
                        'message' => '聊天内容无法解析。',
                    ];
                }
                $gid = $json['goods']['id'];
                $goods = Goods::findOne($gid);
                if (empty($goods)) {
                    return [
                        'type' => static::TYPE_RESPONSE,
                        'error_code' => static::ERROR_JSON,
                        'message' => '没有找到商品编号。',
                    ];
                }
                $json = [
                    'goods' => [
                        'id' => $goods->id,
                        'title' => $goods->title,
                        'main_pic' => $goods->main_pic,
                        'price' => $goods->price,
                        'stock' => $goods->stock,
                        'status' => $goods->status,
                    ],
                ];
                $message = new ChatMessage();
                $message->cid = $chat->id;
                $message->from = $from;
                $message->type = ChatMessage::TYPE_GOODS;
                $message->message = json_encode($json);
                $r = $message->save();
                if (!$r) {
                    return [
                        'type' => static::TYPE_RESPONSE,
                        'error_code' => static::ERROR_SAVE_CHAT,
                        'message' => '无法保存聊天消息，请稍候重试。',
                        'errors' => $message->errors,
                    ];
                }
                $json['goods']['main_pic'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $json['goods']['main_pic'];
                return [
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_NONE,
                    'msg' => [
                        'id' => $message->id,
                        'from' => $message->from,
                        'to' => $message->to,
                        'type' => $message->type,
                        'content' => $message->message,
                        'goods' => $json['goods'],
                        'create_time' => $message->create_time,
                    ],
                ];
            case static::CHAT_MSG_TYPE_TEXT:
                $message = new ChatMessage();
                $message->cid = $chat->id;
                $message->from = $from;
                $message->type = ChatMessage::TYPE_TEXT;
                $message->message = $msg['content'];
                $r = $message->save();
                if (!$r) {
                    return [
                        'type' => static::TYPE_RESPONSE,
                        'error_code' => static::ERROR_SAVE_CHAT,
                        'message' => '无法保存聊天消息，请稍候重试。',
                        'errors' => $message->errors,
                    ];
                }
                return [
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_NONE,
                    'msg' => [
                        'id' => $message->id,
                        'from' => $message->from,
                        'to' => $message->to,
                        'type' => $message->type,
                        'content' => $message->message,
                        'create_time' => $message->create_time,
                    ],
                ];
            default:
                return [
                    'type' => static::TYPE_RESPONSE,
                    'error_code' => static::ERROR_CHAT_TYPE,
                    'message' => '无法确定聊天消息类型。',
                ];
        }
    }

    /**
     * 获取消息编号
     * @return mixed
     */
    private static function getMsgId()
    {
        if (static::$msg_id >= PHP_INT_MAX) {
            static::$msg_id = 0;
        }
        static::$msg_id++;
        return static::$msg_id;
    }

    /**
     * 发送信息
     * @param $connection TcpConnection
     * @param $data mixed
     */
    private function send($connection, $data)
    {
        $uid = isset(static::$socket_data[$connection->id]) ? static::$socket_data[$connection->id]['uid'] : 0;
        $this->stdout(date('Y-m-d H:i:s') . ' ' . $connection->id . ' ' . $connection->getRemoteAddress() . chr(9) . $uid . chr(9) . $data . chr(10), Console::FG_BLUE);
        $connection->send($data);
    }
}
