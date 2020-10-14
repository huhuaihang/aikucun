<?php

namespace app\widgets;

use yii\base\Widget;

/**
 * 管理后台表格右侧操作菜单
 * Class ManagerTableOp
 * @package app\widgets
 *
 * ```php
 * echo ManagerTableOp::widget([
 *     'items' => [
 *         ['icon' => 'fa fa-pencil', 'btn_class'=>'btn btn-xs btn-success', 'color'=>'green', 'tip'=>'修改', 'onclick'=>'', 'href'=>'/admin/user/edit'],
 *     ],
 * ]);
 * ```
 */
class ManagerTableOp extends Widget
{
    public $items = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('manager_table_op', [
            'items' => $this->items
        ]);
    }
}
