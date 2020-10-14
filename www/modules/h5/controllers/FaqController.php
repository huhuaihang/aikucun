<?php

namespace app\modules\h5\controllers;

use app\models\Faq;
use app\models\FaqCategory;
use app\models\UserFaq;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * 常见问题
 * Class FaqController
 * @package app\modules\h5\controllers
 */
class FaqController extends BaseController
{
    /**
     * 常见问题列表
     * @return string
     */
    public function actionList()
    {
        $search_cid = $this->get('search_cid', null);
        $category_list = FaqCategory::find()
            ->andWhere(['status' => FaqCategory::STATUS_SHOW])
            ->andWhere(['pid' => $search_cid])
            ->all();
        $faq_list = Faq::find()
            ->andWhere(['status' => Faq::STATUS_SHOW])
            ->andWhere(['cid' => $search_cid])
            ->all();
        return $this->render('list', [
            'category_list' => $category_list,
            'faq_list' => $faq_list,
        ]);
    }

    /**
     * 常见问题详情
     * @param $id integer 问题编号
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $faq = Faq::findOne($id);
        if (empty($faq) || $faq->status != Faq::STATUS_SHOW) {
            throw new NotFoundHttpException('没有找到问题内容。');
        }
        // 相关问题
        $tags = $faq->tags;
        $tags = preg_split('/\s|,|，|、/', $tags, -1, PREG_SPLIT_NO_EMPTY);
        $faq_list = Faq::find()
            ->andWhere(['status' => Faq::STATUS_SHOW])
            ->andWhere(['<>', 'id', $faq->id])
            ->andWhere(['or like', 'tags', $tags])
            ->all();
        return $this->render('view', [
            'faq' => $faq,
            'faq_list' => $faq_list,
        ]);
    }

    /**
     * 保存用户常见问题结果AJAX接口
     * @return array
     */
    public function actionSaveResult()
    {
        if (Yii::$app->user->isGuest) {
            return ['message' => '登录后才可以设置。'];
        }
        $id = $this->get('id');
        $result = $this->get('result');
        $faq = Faq::findOne($id);
        if (empty($faq) || $faq->status != Faq::STATUS_SHOW) {
            return ['message' => '没有找到问题信息。'];
        }
        if (!in_array($result, [0, 1])) {
            return ['message' => '参数错误。'];
        }
        $user_faq = new UserFaq();
        $user_faq->uid = Yii::$app->user->id;
        $user_faq->fid = $faq->id;
        $user_faq->result = $result;
        $user_faq->create_time = time();
        $user_faq->save();
        return ['result' => 'success'];
    }
}
