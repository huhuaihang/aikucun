<?php

namespace app\modules\admin\controllers;

use yii\web\ForbiddenHttpException;

/**
 * 统计分析
 * Class StatisticsController
 * @package app\modules\admin\controllers
 */
class StatisticsController extends BaseController
{
    /**
     * 财务统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionFinance()
    {
        if (!$this->manager->can('statistics/finance')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('finance');
    }

    /**
     * 佣金统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionCommission()
    {
        if (!$this->manager->can('statistics/commission')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('commission');
    }

    /**
     * 储值卡统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionPrepaidCard()
    {
        if (!$this->manager->can('statistics/prepaid-card')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('prepaid_card');
    }

    /**
     * 店铺结算统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionShopSettlement()
    {
        if (!$this->manager->can('statistics/shop-settlement')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('shop_settlement');
    }

    /**
     * 商品统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionGoods()
    {
        if (!$this->manager->can('statistics/goods')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('goods');
    }

    /**
     * 订单统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionOrder()
    {
        if (!$this->manager->can('statistics/order')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('order');
    }

    /**
     * 用户统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionUser()
    {
        if (!$this->manager->can('statistics/user')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('user');
    }

    /**
     * 客户端版本访问统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionVersion()
    {
        if (!$this->manager->can('statistics/version')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('version');
    }

    /**
     * 去年统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionLastYear()
    {
        if (!$this->manager->can('statistics/last-year')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('last_year');
    }

    /**
     * 今年统计
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionThisYear()
    {
        if (!$this->manager->can('statistics/this-year')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('this_year');
    }
}
