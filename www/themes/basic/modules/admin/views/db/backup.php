<?php

use app\assets\TableAsset;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $table_list string[] 表名列表
 */

TableAsset::register($this);

$this->title = '数据库备份';
$this->params['breadcrumbs'][] = '数据管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'post');?>
    <table class="table">
        <thead>
            <tr>
                <th colspan="2">备份类型</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo Html::radio('backup_type', Yii::$app->request->post('backup_type') == 'all', ['id'=>'backup_type_all', 'value'=>'all', 'onclick'=>'chooseDatabaseBackupType()']);?>
                    <?php echo Html::label('全部备份', 'backup_type_all');?></td>
                <td>备份数据库所有表</td>
            </tr>
            <tr>
                <td><?php echo Html::radio('backup_type', Yii::$app->request->post('backup_type', 'standard') == 'standard', ['id'=>'backup_type_standard', 'value'=>'standard', 'onclick'=>'chooseDatabaseBackupType()']);?>
                    <?php echo Html::label('标准备份（推荐）', 'backup_type_standard');?></td>
                <td>备份常用数据表</td>
            </tr>
            <tr>
                <td><?php echo Html::radio('backup_type', Yii::$app->request->post('backup_type') == 'min', ['id'=>'backup_type_min', 'value'=>'min', 'onclick'=>'chooseDatabaseBackupType()']);?>
                    <?php echo Html::label('最小备份', 'backup_type_min');?></td>
                <td>仅包括关键业务表</td>
            </tr>
            <tr>
                <td><?php echo Html::radio('backup_type', Yii::$app->request->post('backup_type') == 'custom', ['id'=>'backup_type_custom', 'value'=>'custom', 'onclick'=>'chooseDatabaseBackupType()']);?>
                    <?php echo Html::label('自定义备份', 'backup_type_custom');?></td>
                <td>根据自行选择备份数据表</td>
            </tr>
            <tr>
                <td id="table_list" style="display:<?php if (Yii::$app->request->post('backup_type') != 'custom') {echo 'none';}?>;" colspan="2">
                    <?php echo Html::checkboxList('backup_table[]', Yii::$app->request->post('backup_table'), array_combine($table_list, $table_list));?>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2"><button class="btn btn-primary">开始备份</button></th>
            </tr>
        </tfoot>
    </table>
<?php echo Html::endForm();?>
<script>
function chooseDatabaseBackupType() {
    var type = $('input[name=backup_type]:checked').val();
    if (type == 'custom') {
        $('#table_list').show();
    } else {
        $('#table_list').hide();
    }
}
</script>
