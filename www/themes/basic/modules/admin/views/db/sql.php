<?php

use app\assets\TableAsset;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $sql string 被执行的SQL语句
 * @var $affected integer 影响的行数
 * @var $table array
 */

TableAsset::register($this);

$this->title = 'SQL查询';
$this->params['breadcrumbs'][] = '数据管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'post');?>
    <div class="form-group">
        <label for="sql" class="sr-only">SQL</label>
        <?php echo Html::textarea('sql', $sql, ['class'=>'form-control']);?>
        <button type="submit" class="btn btn-danger">执行</button>
    </div>
<?php echo Html::endForm();?>
<div class="alert alert-warning" role="alert">SQL影响行数：<?php echo $affected;?></div>
<?php $title = [];
if (count($table) > 0) {
    $title = array_keys($table[0]);
}?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <?php foreach ($title as $item) {?>
                <th><?php echo $item;?></th>
            <?php }?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($table as $row) {?>
            <tr>
                <?php foreach ($title as $key) {?>
                    <td><?php echo $row[$key];?></td>
                <?php }?>
            </tr>
        <?php }?>
    </tbody>
</table>
