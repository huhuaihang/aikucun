<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * memcache队列类
 * 支持多进程并发写入、读取
 * 边写边读,AB面轮值替换
 *
 * @example:
 *      $queue = new MemQueue('duilie');
 *      $queue->add('1asdf');
 *      $queue->getQueueLength();
 *      $queue->read(11);
 *      $queue->get(8);
 */
class MemQueue extends Model
{
    public $access;       // 队列是否可更新
    private $currentSide; // 当前轮值的队列面:A/B
    private $lastSide;    // 上一轮值的队列面:A/B
    private $sideAHead;   // A面队首值
    private $sideATail;   // A面队尾值
    private $sideBHead;   // B面队首值
    private $sideBTail;   // B面队尾值
    private $currentHead; // 当前队首值
    private $currentTail; // 当前队尾值
    private $lastHead;    // 上轮队首值
    private $lastTail;    // 上轮队尾值
    private $expire;      // 过期时间,秒,1~2592000,即30天内;0为永不过期
    private $sleepTime;   // 等待解锁时间,微秒
    private $queueName;   // 队列名称,唯一值
    private $retryNum;    // 重试次数,= 10 * 理论并发数

    const   MAXNUM = 2000;                 // (单面)最大队列数,建议上限10K
    const   HEAD_KEY = '_mem_queue_Head_'; // 队列首key
    const   TAIL_KEY = '_mem_queue_Tail_'; // 队列尾key
    const   VALU_KEY = '_mem_queue_Valu_'; // 队列值key
    const   LOCK_KEY = '_mem_queue_Lock_'; // 队列锁key
    const   SIDE_KEY = '_mem_queue_Side_'; // 轮值面key

    /**
     * 构造函数
     * @param $queueName string 队列名称
     * @param $expire integer 过期时间
     */
    public function __construct($queueName = '', $expire = 0)
    {
        ignore_user_abort(true); // 当客户断开连接,允许继续执行
        set_time_limit(0); // 取消脚本执行延时上限

        $this->access = false;
        $this->sleepTime = 1000;
        $expire = (empty($expire) && $expire != 0) ? 3600 : (int)$expire;
        $this->expire = $expire;
        $this->queueName = $queueName;
        $this->retryNum = 10000;

        Yii::$app->cache->set($queueName . self::SIDE_KEY, 'A', $expire);
        $this->getHeadNTail($queueName);
        if (!isset($this->sideAHead) || empty($this->sideAHead)) $this->sideAHead = 0;
        if (!isset($this->sideATail) || empty($this->sideATail)) $this->sideATail = 0;
        if (!isset($this->sideBHead) || empty($this->sideBHead)) $this->sideBHead = 0;
        if (!isset($this->sideBTail) || empty($this->sideBTail)) $this->sideBTail = 0;
        parent::__construct();
    }

    /**
     * 获取队列首尾值
     * @param $queueName string 队列名称
     */
    private function getHeadNTail($queueName)
    {
        $this->sideAHead = intval(Yii::$app->cache->get($queueName . 'A' . self::HEAD_KEY));
        $this->sideATail = intval(Yii::$app->cache->get($queueName . 'A' . self::TAIL_KEY));
        $this->sideBHead = intval(Yii::$app->cache->get($queueName . 'B' . self::HEAD_KEY));
        $this->sideBTail = intval(Yii::$app->cache->get($queueName . 'B' . self::TAIL_KEY));
    }

    /**
     * 获取当前轮值的队列面
     * @return string 队列面名称
     */
    public function getCurrentSide()
    {
        $currentSide = Yii::$app->cache->get($this->queueName . self::SIDE_KEY);
        if ($currentSide == 'A') {
            $this->currentSide = 'A';
            $this->lastSide = 'B';

            $this->currentHead = $this->sideAHead;
            $this->currentTail = $this->sideATail;
            $this->lastHead = $this->sideBHead;
            $this->lastTail = $this->sideBTail;
        } else {
            $this->currentSide = 'B';
            $this->lastSide = 'A';

            $this->currentHead = $this->sideBHead;
            $this->currentTail = $this->sideBTail;
            $this->lastHead = $this->sideAHead;
            $this->lastTail = $this->sideATail;
        }

        return $this->currentSide;
    }

    /**
     * 队列加锁
     * @return boolean
     */
    private function getLock()
    {
        if ($this->access === false) {
            $i = 0;
            while (!Yii::$app->cache->add($this->queueName . self::LOCK_KEY, 1, $this->expire)) {
                usleep($this->sleepTime);
                $i++;
                if ($i > $this->retryNum) { // 尝试等待N次
                    return false;
                }
            }
            return $this->access = true;
        }
        return false;
    }

    /**
     * 队列解锁
     */
    private function unLock()
    {
        Yii::$app->cache->delete($this->queueName . self::LOCK_KEY);
        $this->access = false;
    }

    /**
     * 添加数据
     * @param $data mixed 要存储的值
     * @return boolean
     */
    public function add($data)
    {
        $result = false;
        if (!$this->getLock()) {
            return $result;
        }
        $this->getHeadNTail($this->queueName);
        $this->getCurrentSide();

        if ($this->isFull()) {
            $this->unLock();
            return false;
        }

        if ($this->currentTail < self::MAXNUM) {
            $value_key = $this->queueName . $this->currentSide . self::VALU_KEY . $this->currentTail;
            if (Yii::$app->cache->add($value_key, $data, $this->expire)) {
                $this->changeTail();
                $result = true;
            }
        } else { // 当前队列已满,更换轮值面
            $this->unLock();
            $this->changeCurrentSide();
            return $this->add($data);
        }

        $this->unLock();
        if (isset($data['goods'])) {
            return $value_key;
        } else {
            return $result;
        }
    }



    /**
     * 取出数据
     * @param  $length integer 数据的长度
     * @return array|false
     */
    public function get($length = 0)
    {
        if (!is_numeric($length)) {
            return false;
        }
        if (empty($length)) $length = self::MAXNUM * 2; // 默认读取所有
        if (!$this->getLock()) {
            return false;
        }

        if ($this->isEmpty()) {
            $this->unLock();
            return false;
        }

        $keyArray = $this->getKeyArray($length);
        $lastKey = $keyArray['lastKey'];
        $currentKey = $keyArray['currentKey'];
        $keys = $keyArray['keys'];
        $this->changeHead($this->lastSide, $lastKey);
        $this->changeHead($this->currentSide, $currentKey);

        $data = Yii::$app->cache->multiGet($keys);
        foreach ($keys as $v) { // 取出之后删除
            Yii::$app->cache->delete($v);
        }
        $this->unLock();

        return $data;
    }

    /**
     * 读取数据
     * @param $length integer 数据的长度
     * @return  array|false
     */
    public function read($length = 0)
    {
        if (!is_numeric($length)) {
            return false;
        }
        if (empty($length)) {
            $length = self::MAXNUM * 2; // 默认读取所有
        }
        $keyArray = $this->getKeyArray($length);
        $data = Yii::$app->cache->multiGet($keyArray['keys']);
        return $data;
    }

    /**
     * 获取队列某段长度的key数组
     * @param $length integer 队列长度
     * @return  array
     */
    private function getKeyArray($length)
    {
        $result = array('keys' => [], 'lastKey' => 0, 'currentKey' => 0);
        $this->getHeadNTail($this->queueName);
        $this->getCurrentSide();
        if (empty($length)) {
            return $result;
        }

        // 先取上一面的key
        $result['lastKey'] = 0;
        for ($i = 0; $i < $length; $i++) {
            $result['lastKey'] = $this->lastHead + $i;
            if ($result['lastKey'] >= $this->lastTail) {
                break;
            }
            $result['keys'][] = $this->queueName . $this->lastSide . self::VALU_KEY . $result['lastKey'];
        }

        // 再取当前面的key
        $j = $length - $i;
        $result['currentKey'] = 0;
        for ($k = 0; $k < $j; $k++) {
            $result['currentKey'] = $this->currentHead + $k;
            if ($result['currentKey'] >= $this->currentTail) {
                break;
            }
            $result['keys'][] = $this->queueName . $this->currentSide . self::VALU_KEY . $result['currentKey'];
        }

        return $result;
    }

    /**
     * 更新当前轮值面队列尾的值
     */
    private function changeTail()
    {
        $tail_key = $this->queueName .$this->currentSide . self::TAIL_KEY;
        Yii::$app->cache->add($tail_key, 0, $this->expire);
        $v = intval(Yii::$app->cache->get($tail_key));
        $v = $v + 1;
        Yii::$app->cache->set($tail_key, $v, $this->expire);
    }

    /**
     * 更新队列首的值
     * @param $side string 要更新的面
     * @param $headValue integer 队列首的值
     * @return boolean
     */
    private function changeHead($side, $headValue)
    {
        if ($headValue < 1) {
            return false;
        }
        $head_key = $this->queueName . $side . self::HEAD_KEY;
        $tail_key = $this->queueName . $side . self::TAIL_KEY;
        $sideTail = Yii::$app->cache->get($tail_key);
        if ($headValue < $sideTail) {
            Yii::$app->cache->set($head_key, $headValue + 1, $this->expire);
        } elseif ($headValue >= $sideTail) {
            $this->resetSide($side);
        }
        return true;
    }

    /**
     * 重置队列面,即将该队列面的队首、队尾值置为0
     * @param $side string 要重置的面
     */
    private function resetSide($side)
    {
        $head_key = $this->queueName . $side . self::HEAD_KEY;
        $tail_key = $this->queueName . $side . self::TAIL_KEY;
        Yii::$app->cache->set($head_key, 0, $this->expire);
        Yii::$app->cache->set($tail_key, 0, $this->expire);
    }

    /**
     * 改变当前轮值队列面
     * @return string
     */
    private function changeCurrentSide()
    {
        $currentSide = Yii::$app->cache->get($this->queueName . self::SIDE_KEY);
        if ($currentSide == 'A') {
            Yii::$app->cache->set($this->queueName . self::SIDE_KEY, 'B', $this->expire);
            $this->currentSide = 'B';
        } else {
            Yii::$app->cache->set($this->queueName . self::SIDE_KEY, 'A', $this->expire);
            $this->currentSide = 'A';
        }
        return $this->currentSide;
    }

    /**
     * 检查当前队列是否已满
     * @return boolean
     */
    public function isFull()
    {
        $result = false;
        if ($this->sideATail == self::MAXNUM && $this->sideBTail == self::MAXNUM) {
            $result = true;
        }
        return $result;
    }

    /**
     * 检查当前队列是否为空
     * @return boolean
     */
    public function isEmpty()
    {
        $result = true;
        if ($this->sideATail > 0 || $this->sideBTail > 0) {
            $result = false;
        }
        return $result;
    }

    /**
     * 获取当前队列的长度
     * 该长度为理论长度，某些元素由于过期失效而丢失，真实长度小于或等于该长度
     * @return integer
     */
    public function getQueueLength()
    {
        $this->getHeadNTail($this->queueName);
        $this->getCurrentSide();

        $sideALength = $this->sideATail - $this->sideAHead;
        $sideBLength = $this->sideBTail - $this->sideBHead;
        $result = $sideALength + $sideBLength;

        return $result;
    }

    /**
     * 清空当前队列数据,仅保留HEAD_KEY、TAIL_KEY、SIDE_KEY三个key
     * @return boolean
     */
    public function clear()
    {
        if (!$this->getLock()) return false;
        for ($i = 0; $i < self::MAXNUM; $i++) {
            Yii::$app->cache->delete($this->queueName . 'A' . self::VALU_KEY . $i);
            Yii::$app->cache->delete($this->queueName . 'B' . self::VALU_KEY . $i);
        }
        $this->unLock();
        $this->resetSide('A');
        $this->resetSide('B');
        return true;
    }
}
