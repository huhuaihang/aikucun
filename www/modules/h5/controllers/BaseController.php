<?php

namespace app\modules\h5\controllers;

/**
 * 前端控制器基类
 * Class BaseController
 * @package app\modules\h5\controllers
 */
class BaseController extends \app\controllers\BaseController
{
    /**
     * @var string 默认layouts文件名称
     */
    public $layout = 'main'; // @app/themes/basic/modules/h5/views/layouts/main.php
}
