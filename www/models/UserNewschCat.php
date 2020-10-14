<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户阅读商学院分类文章
 * Class UserNewHand
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $cid 商学院分类编号
 * @property integer $type 商学院分类类型
 * @property integer $read_time 用户查看时间

 */
class UserNewschCat extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'cid', 'type','read_time'], 'required'],
        ];
    }


}
