<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户推荐
 * Class UserRecommend
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $from_uid 推荐者用户编号
 * @property integer $to_uid 接受者用户编号
 * @property integer $sid 店铺编号
 * @property integer $gid 商品编号
 * @property integer $create_time 创建时间
 */
class UserRecommend extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from_uid', 'to_uid', 'create_time'], 'required'],
            [['sid', 'gid'], function () {
                if (empty($this->sid) && empty($this->gid)) {
                    $this->addError('sid', '店铺和商品必须设置一个。');
                    $this->addError('gid', '店铺和商品必须设置一个。');
                    return;
                }
            }],
        ];
    }

    /**
     * 保存推荐信息
     * 根据系统设置判断是否保存，如果系统设置不保存，同样返回true
     * @param $invite_code string 推荐码
     * @param $to_uid integer 接受者用户编号
     * @param $sid integer 店铺编号
     * @param $gid integer 商品编号
     * @return boolean
     */
    public static function saveRecommend($invite_code, $to_uid, $sid, $gid)
    {
        /** @var User $invite_user */
        $invite_user = User::find()->andWhere(['invite_code' => $invite_code])->one();
        if (empty($invite_user)) {
            return false;
        }
        if ($invite_user->id == $to_uid) {
            // 自己不能分享给自己
            return true;
        }
        $recommend_begin_valid_time = time() - System::getConfig('recommend_keep_day') * 86400;
        /** @var UserRecommend $model */
        $model = UserRecommend::find()
            ->andWhere(['>=', 'create_time', $recommend_begin_valid_time])
            ->andWhere(['to_uid' => $to_uid, 'sid' => $sid, 'gid' => $gid])
            ->orderBy('create_time DESC')
            ->one();
        if (!empty($model) && System::getConfig('recommend_replace_mode', 0) == 1) { // 已经有人推荐了并且保留前者
            return true;
        }
        $model = new UserRecommend();
        $model->from_uid = $invite_user->id;
        $model->to_uid = $to_uid;
        $model->sid = $sid;
        $model->gid = $gid;
        $model->create_time = time();
        return $model->save();
    }

    /**
     * 返回有效的分享信息
     * @param $to_uid integer 接受者用户编号
     * @param $sid integer 店铺编号，如果没有直接分享的商品，则查找分享店铺
     * @param $gid integer 分享的商品编号
     * @param $valid_time integer 有效期
     * @return UserRecommend
     */
    public static function findRecommend($to_uid, $sid, $gid, $valid_time)
    {
        /** @var UserRecommend $recommend */
        $recommend = UserRecommend::find()
            ->andWhere(['>=', 'create_time', $valid_time])
            ->andWhere(['to_uid' => $to_uid, 'gid' => $gid])
            ->orderBy('create_time DESC')
            ->one();
        if (empty($recommend)) { // 没有人分享此商品
            $recommend = UserRecommend::find()
                ->andWhere(['>=', 'create_time', $valid_time])
                ->andWhere(['to_uid' => $to_uid, 'sid' => $sid])
                ->orderBy('create_time DESC')
                ->one();
            if (empty($recommend)) { // 没有人分享此店铺
                return null;
            }
        }
        return $recommend;
    }
}
