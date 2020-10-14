<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_subsidy".
 *
 * @property int $id
 * @property int $to_uid
 * @property int $to_1_uid
 * @property int $to_2_uid
 * @property int $to_3_uid
 * @property int $from_uid
 * @property string $money
 * @property string $to_user_level
 * @property int $type
 * @property string $no
 * @property string $active_name 活动名称
 * @property int $create_time
 *
 * @property User $fromUser
 * @property User $to1U
 * @property User $to2U
 * @property User $to3U
 * @property User $toUser
 */
class UserSubsidy extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_subsidy';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['to_uid', 'to_1_uid', 'to_2_uid', 'to_3_uid', 'from_uid', 'type', 'create_time'], 'integer'],
            [['money'], 'number'],
            [['to_user_level', 'no'], 'string', 'max' => 128],
            [['from_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['from_uid' => 'id']],
//            [['to_1_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['to_1_uid' => 'id']],
//            [['to_2_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['to_2_uid' => 'id']],
//            [['to_3_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['to_3_uid' => 'id']],
            [['to_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['to_uid' => 'id']],
            [['to_1_uid', 'to_2_uid', 'to_3_uid', 'active_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'to_uid' => 'To Uid',
            'to_1_uid' => 'To 1 Uid',
            'to_2_uid' => 'To 2 Uid',
            'to_3_uid' => 'To 3 Uid',
            'from_uid' => 'From Uid',
            'money' => 'Money',
            'to_user_level' => 'To User Level',
            'type' => 'Type',
            'no' => 'No',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTo1U()
    {
        return $this->hasOne(User::className(), ['id' => 'to_1_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTo2U()
    {
        return $this->hasOne(User::className(), ['id' => 'to_2_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTo3U()
    {
        return $this->hasOne(User::className(), ['id' => 'to_3_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_uid']);
    }

    /**
     * @param $id int 记录编号
     * @param $money float 金额
     * @return bool
     * @throws Exception
     */
    public function editSub($id, $money)
    {
        /** @var UserSubsidy $log */
        $log = UserSubsidy::find()->where(['id' => $id])->one();
        if (empty($log)) {
            return true;
        }

        $trans = Yii::$app->db->beginTransaction();
        try {
            $update_money = $money - $log->money;
            if (Util::comp($update_money, 0 , 2) <= 0) {
                throw new Exception('无法补贴记录：只能增加不能减少');
            }

            $log->money = $money;
            if(!$log->save()) {
                Yii::error($log->errors);
                $errors = $log->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法补贴记录：' . $error);
            }
            $r = User::updateAllCounters(['subsidy_money' => $update_money],['id' => $log->to_uid]);
            if ($r <=0) {
                throw new Exception('更新补贴失败');
            }
            // 增加账户明细记录
            $user_account_log = new UserAccountLog();
            $attributes = [
                'uid' => $log->to_uid,
                'subsidy_money' => $update_money,
                'time' => time(),
                'remark' => 'EDIT USER_SUBSIDY_MONEY ' . 'from_uid :' . $log->from_uid . ' to_uid:' . $log->to_uid,
            ];
            $user_account_log->setAttributes($attributes);
            $r1 = $user_account_log->save();
            if (!$r1) {
                throw new Exception('添加账户明细失败。');
            }
            //更新 补贴 下级人数  如果人数到达了  相应的人数  级别升级 升服务商  和 生店主
            //成长值  已经发展了 多少人多少钱 也需要更新
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
                Yii::error($e->getMessage());
                $this->addError('money', $e->getMessage());
                return false;
            } catch (Exception $e) {
                Yii::error($e->getMessage());
                $this->addError('money', $e->getMessage());
                return false;
            }
        }
    }
}
