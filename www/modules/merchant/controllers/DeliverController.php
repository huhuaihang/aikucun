<?php

namespace app\modules\merchant\controllers;

use app\models\DeliverTemplate;
use app\models\ShopExpress;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\Url;

/**
 * Class DeliverController
 * @package modules\merchant\controllers
 */
class DeliverController extends BaseController
{
    /**
     * 物流模板列表
     * @return string
     */
    public function actionList()
    {
        $gid=$this->get('gid');
        $model = DeliverTemplate::find();
        $model->joinWith(['shopExpress', 'shopExpress.express']);
        $model->where(['sid' => $this->shop->id]);
        if (!empty($gid)) {
            $model->andWhere(['gid' => $gid]);
        } else {
            $model->andWhere(['gid' => null]);
        }
        $pagination = new Pagination(['totalCount' => $model->count()]);
        $model_list = $model->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 增加/修改 物流模板
     * @return string
     */
    public function actionEdit()
    {
        $id = $this->get('id');
        $gid = $this->get('gid');
        $shop_express = new ShopExpress();
        $shop_express->sid = $this->shop->id;
        $shop_express->status = ShopExpress::STATUS_OK;
        if (!empty($id)) {
            $model = DeliverTemplate::findOne($id);
            $shop_express = ShopExpress::findOne($model->se_id);
        } else {
            $model = new DeliverTemplate();
            $model->create_time = time();
            $model->status = DeliverTemplate::STATUS_OK;
            $model->is_default = 0;
            if(!empty($gid))
            {
            $model->gid = $gid;
            }
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            if ($shop_express->load($this->post())) {
                if (!$shop_express->save()) {
                    throw new Exception('无法保存店铺物流信息。');
                }
                if ($model->load($this->post())) {
                    $model->se_id = $shop_express->id;
                    if (!isset($_POST['DeliverTemplate']['pid_list'])) {
                        $model->pid_list = '';
                    } else {
                        $model->pid_list = json_encode($model->pid_list);
                    }
                    if (!isset($_POST['DeliverTemplate']['cid_list'])) {
                        $model->cid_list = '';
                    } else {
                        $model->cid_list = json_encode($model->cid_list);
                    }
                    if (!$model->save()) {
                        throw new Exception('无法保存物流模板信息。');
                    }
                }
                $trans->commit();
                if(!empty($gid)) {
                    $url = '/merchant/deliver/list?gid='.$gid;
                }else{
                    $url = '/merchant/deliver/list';
                }
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to([$url]),
                    'txt' => '物流模板列表'
                ]));
            }
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
        }
        if (!empty($model->pid_list)) {
            $model->pid_list = json_decode($model->pid_list);
        } else {
            $model->pid_list = [];
        }
        if (!empty($model->cid_list)) {
            $model->cid_list = json_decode($model->cid_list);
        } else {
            $model->cid_list = [];
        }
        return $this->render('edit', [
            'model' => $model,
            'shop_express' => $shop_express,
        ]);
    }

    /**
     * 更换物流模板状态AJAX接口
     * @return array
     */
    public function actionStatus()
    {
        $id = $this->get('id');
        $model = DeliverTemplate::findOne($id);
        if (empty($model) || $model->shopExpress->sid != $this->shop->id) {
            return ['message' => '没有找到物流模板信息。'];
        }
        $new_status = [
            DeliverTemplate::STATUS_OK => DeliverTemplate::STATUS_STOP,
            DeliverTemplate::STATUS_STOP => DeliverTemplate::STATUS_OK
        ][$model->status];
        $model->status = $new_status;
        $model->save();
        return ['result' => 'success'];
    }
}