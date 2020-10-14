<?php

namespace app\modules\h5\controllers;

use app\models\System;
use yii\web\BadRequestHttpException;

/**
 * 关于页面
 * Class AboutController
 * @package app\modules\h5\controllers
 */
class AboutController extends BaseController
{
    /**
     * 关于页面
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 协议及声明列表
     * @return string
     */
    public function actionAgreementList()
    {
        return $this->render('agreement_list');
    }

    /**
     * 协议及声明详细
     * @throws BadRequestHttpException
     * @return string
     */
    public function actionAgreement()
    {
        $name = $this->get('name');
        if (empty($name)) {
            throw new BadRequestHttpException('参数错误');
        }
        $agreement = System::find()->where(['name' => $name])->one();
        $agreement_list = array('merchant_join_agreement', 'merchant_join_aptitude', 'goods_category_money_table', 'refund_agreement');
        if (in_array($name, $agreement_list)) {
            return $this->render("$name");
        }
        return $this->render('agreement', [
            'agreement' => $agreement
        ]);
    }
}
