<?php

namespace app\modules\merchant\controllers;

use app\models\Shop;
use Yii;

/**
 * 商户管理后台控制器基类
 * Class BaseController
 * @package app\modules\merchant\controllers
 */
class BaseController extends \app\controllers\BaseController
{
    /**
     * @var string 默认layouts文件名称
     */
    public $layout = 'main'; // @app/themes/basic/modules/merchant/views/layouts/main.php
    /**
     * @var $merchant false|\yii\web\User
     */
    protected $merchant = false;
    /**
     * @var $shop false|\app\models\Shop
     */
    protected $shop = false;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->merchant = Yii::$app->get('merchant');
        // 判断登录状态
        if (empty($this->merchant) || $this->merchant->isGuest) {
            $this->merchant->loginRequired();
            return false;
        }
        $this->shop = Shop::find()->andWhere(['mid' => $this->merchant->id])->one();
        return parent::beforeAction($action);
    }
}
