<?php

namespace app\widgets;

use yii\base\Widget;

/**
 * 店铺图片选择组件
 * Class ShopFileChooseWidget
 * @package app\widgets
 */
class ShopFileChooseWidget extends Widget
{
    /**
     * @var integer 店铺编号
     */
    public $sid;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('shop_file_choose', [
            'sid' => $this->sid,
        ]);
    }
}
