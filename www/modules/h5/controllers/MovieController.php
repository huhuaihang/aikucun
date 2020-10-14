<?php

namespace app\modules\h5\controllers;

/**
 * 电影控制器
 * Class MovieController
 * @package app\modules\h5\controllers
 */
class MovieController extends BaseController
{
    /**
     * 电影院
     * @return string
     */
    public function actionCinema()
    {
        return $this->render('cinema');
    }

    /**
     * 电影
     * @return string
     */
    public function actionView()
    {
        return $this->render('view');
    }

    /**
     * 座位
     * @return string
     */
    public function actionSeat()
    {
        return $this->render('seat');
    }
}
