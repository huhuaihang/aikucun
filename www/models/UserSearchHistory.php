<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\Cookie;

/**
 * 用户搜索记录
 * Class UserSearchHistory
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property string $keyword 关键字
 * @property integer $create_time 创建时间
 */
class UserSearchHistory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'required'],
            ['keyword', 'string', 'max' => 128],
        ];
    }

    /**
     * 记录搜索记录到COOKIE
     * @param string $keywords
     */
    public static function addCookieHistory($keywords)
    {
        $history = Yii::$app->request->cookies->getValue('history');
        if (!empty($history)) {
            $keyword_list = preg_split('/,|，| |　|、/', $keywords, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($keyword_list as $k => $v) {
                array_unshift($history, $v); //入栈
                /** @var array $history */
                $history = array_unique($history);
                if (count($history) > 5) {
                    $history = array_slice($history, 0, 5);
                }
            }
        } else {
            $history = array_values(preg_split('/,|，| |　|、/', $keywords, -1, PREG_SPLIT_NO_EMPTY));
        }
        $history = array_splice($history, 0, 6);
        $cookie = new Cookie();
        $cookie->name = 'history';
        $cookie->value = $history;
        $cookie->expire = time() + 3600 * 24;
        Yii::$app->getResponse()->getCookies()->add($cookie);
    }
}
