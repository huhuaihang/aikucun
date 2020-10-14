<?php

use app\assets\TableAsset;
use app\models\Chat;
use app\models\ChatMember;
use app\models\ChatMessage;
use app\models\Shop;
use app\models\User;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $chat_list array
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);

$this->title = '留言列表';
$this->params['breadcrumbs'][] = '客户留言';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>成员</th>
        <th>最后内容</th>
        <th>时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($chat_list as $chat) {
        $message = ChatMessage::findOne($chat['last_msg_id']);?>
        <tr>
            <td><?php foreach (ChatMember::find()->andWhere(['cid' => $chat['id']])->each() as $member) {
                    $model = Chat::getModel($member->member);
                    if ($model instanceof User) {
                        echo '用户：', Html::a(Html::encode($model->nickname), ['/admin/user/view', 'id' => $model->id]), '<br />';
                    } elseif ($model instanceof Shop) {
                        echo '店铺：', Html::a(Html::encode($model->name), ['/admin/shop/view', 'id' => $model->id]), '<br />';
                    }
                }?></td>
            <td><?php if (!empty($message)) {
                    switch ($message->type) {
                        case ChatMessage::TYPE_TEXT:
                            echo nl2br(Html::encode($message->message));
                            break;
                        case ChatMessage::TYPE_GOODS:
                            $data = json_decode($message->message, true);
                            echo '<i>查看商品：', Html::encode($data['goods']['title']), '</i>';
                            break;
                    }
                }?></td>
            <td><?php echo empty($message) ? '' : Yii::$app->formatter->asRelativeTime($message->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/message/chat', 'id' => $chat['id']]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
