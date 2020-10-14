<?php

namespace app\modules\agent\controllers;

use app\models\Merchant;
use app\models\Shop;
use app\models\Util;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * 商户管理
 * Class MerchantController
 * @package app\modules\agent\controllers
 */
class MerchantController extends BaseController
{
    /**
     * 商户列表
     * @return string
     */
    public function actionList()
    {
        $query = Merchant::find();
        $query->andWhere(['aid' => $this->agent->id]);
        $query->joinWith('shop')->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andFilterWhere(['like', 'username', $this->get('search_username')]);
        $query->andFilterWhere(['like', 'mobile', $this->get('search_mobile')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加/修改商户
     * @return string
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionEdit()
    {
        $id = $this->get('id');
        if (!empty($id)) {
            $merchant = Merchant::findOne($id);
            if (empty($merchant)) {
                throw new NotFoundHttpException('没有找到商户信息。');
            }
            if ($merchant->aid != $this->agent->id) {
                throw new ForbiddenHttpException('没有权限。');
            }
            $shop = $merchant->shop;
        } else {
            $merchant = new Merchant();
            $merchant->status = Merchant::STATUS_WAIT_DATA1;
            $merchant->create_time = time();
            $shop = new Shop();
        }
        if ($merchant->load($this->post()) && $shop->load($this->post())) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                $merchant->aid = $this->agent->id;
                if (!$merchant->validate()) {
                    throw new Exception('无法验证请求数据。');
                }
                if (empty($merchant->id) && empty($merchant->password)) {
                    Yii::$app->session->addFlash('error', '请填写密码。');
                    throw new Exception('没有填写密码。');
                }
                if (!empty($merchant->password)) {
                    $merchant->password = Yii::$app->security->generatePasswordHash($merchant->password);
                    $merchant->auth_key = Util::randomStr(32, 7);
                }
                if (!$merchant->save()) {
                    throw new Exception('无法保存商户。');
                }
                $shop->mid = $merchant->id;
                $shop->status = Shop::STATUS_WAIT;
                if (!$shop->save()) {
                    throw new Exception('无法保存店铺信息。');
                }
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/agent/merchant/list']),
                    'txt' => '商户列表'
                ]));
                $trans->commit();
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
            }
        }
        return $this->render('edit', [
            'merchant' => $merchant,
            'shop' => $shop,
        ]);
    }

    /**
     * 查看商户
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        $id = $this->get('id');
        $model = Merchant::findOne($id);
        if (empty($model) || $model->aid != $this->agent->id) {
            throw new NotFoundHttpException('没有找到商户信息。');
        }
        return $this->render('view', [
            'model' => $model
        ]);
    }
}
