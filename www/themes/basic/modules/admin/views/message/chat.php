<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\Chat;
use app\models\ChatMessage;
use app\models\Shop;
use app\models\ShopConfig;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $this \yii\web\View
 * @var $chat \app\models\Chat
 * @var $last_read_msg_id integer
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '聊天';
$this->params['breadcrumbs'][] = '客户留言';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="widget-box">
    <div class="widget-header">
        <h4 class="widget-title lighter smaller">
            <i class="ace-icon fa fa-comment blue"></i>
            <?php foreach ($chat->memberList as $chatMember) {
                $model = Chat::getModel($chatMember->member);
                if ($model instanceof User) {
                    echo '用户：', Html::a(Html::encode($model->nickname), ['/admin/user/view', 'id' => $model->id]), '、';
                } elseif ($model instanceof Shop) {
                    echo '店铺：', Html::a(Html::encode($model->name), ['/admin/shop/view', 'id' => $model->id]), ' ';
                }
            }?>
            的聊天记录
        </h4>
    </div>

    <div class="widget-body">
        <div class="widget-main no-padding">
            <div class="dialogs">
                <?php Pjax::begin(['id' => 'pjax_chat_message', 'options' => ['style' => 'width:100%;']]);?>
                <?php $query = ChatMessage::find()
                    ->andWhere(['cid' => $chat->id])
                    ->orderBy('create_time ASC');
                foreach ($query->each() as $message) {
                    if (strpos($message->from, 'shop_') === 0) {
                        $shop = Chat::getModel($message->from);
                    } else {
                        $user = Chat::getModel($message->from);
                    }
                    /** @var ChatMessage $message */?>
                    <a id="message_<?php echo $message->id;?>" name="message_anchor"></a>
                    <div class="itemdiv dialogdiv">
                        <div class="user">
                            <?php if (strpos($message->from, 'shop_') === 0) {?>
                                <img alt="<?php echo Html::encode($shop->name);?>"
                                     src="<?php echo Yii::$app->params['upload_url'], ShopConfig::getConfig($shop->id, 'logo');?>"/>
                            <?php } else {?>
                                <img alt="<?php echo Html::encode($user->nickname);?>"
                                     src="<?php echo $user->getRealAvatar();?>"/>
                            <?php } ?>
                        </div>
                        <div class="body">
                            <div class="time">
                                <i class="ace-icon fa fa-clock-o"></i>
                                <span class="<?php echo $last_read_msg_id >= $message->id ? 'green' : 'orange';?>"><?php echo Yii::$app->formatter->asRelativeTime($message->create_time);?></span>
                            </div>

                            <div class="name">
                                <?php if (strpos($message->from, 'shop_') === 0) {?>
                                    <a href="#"><?php echo Html::encode($shop->name);?></a>
                                <?php } else {?>
                                    <a href="#"><?php echo Html::encode($user->nickname);?></a>
                                <?php } ?>
                            </div>
                            <?php if ($message->type == ChatMessage::TYPE_TEXT) { // 文本消息?>
                                <div class="text"><?php echo nl2br(Html::encode($message->message));?></div>
                            <?php } elseif ($message->type == ChatMessage::TYPE_GOODS) { // 商品消息
                                $json = json_decode($message->message, true);?>
                                <div class="text"><?php echo Html::a(Html::img(Yii::$app->params['upload_url'] . $json['goods']['main_pic'], ['width' => 64]) . '<br />' . Html::encode($json['goods']['title']), ['/h5/goods/view', 'id' => $json['goods']['id']], ['target' => '_blank']);?></div>
                            <?php }?>
                        </div>
                    </div>
                <?php } ?>
                <?php Pjax::end();?>
            </div>

            <?php echo Html::beginForm(['/admin/message/send'], 'post', ['id' => 'chat_message_form', 'onsubmit' => 'return sendMsg();']);?>
                <?php echo Html::hiddenInput('ajax', 1);?>
                <?php echo Html::hiddenInput('ChatMessage[cid]', $chat->id);?>
                <div class="form-actions">
                    <div class="input-group">
                        <input placeholder="输入内容" type="text" class="form-control" name="ChatMessage[message]" />
                        <span class="input-group-btn">
                            <button class="btn btn-sm btn-info no-radius">
                                <i class="ace-icon fa fa-share"></i>
                                发送
                            </button>
                        </span>
                    </div>
                </div>
            <?php echo Html::endForm();?>
        </div><!-- /.widget-main -->
    </div><!-- /.widget-body -->
</div><!-- /.widget-box -->
<script>
    function page_init() {
        $('[name=message_anchor]:last').get(0).scrollIntoView();
        $('#pjax_chat_message').on('pjax:complete', function () {
            $('[name=message_anchor]:last').get(0).scrollIntoView();
        });
        window.setInterval(function () {
            $.pjax.reload('#pjax_chat_message', {
                history: false,
                push: false
            });
        }, 3000);
    }
    function sendMsg() {
        var $form = $('#chat_message_form');
        var $msg = $('[name="ChatMessage[message]"]');
        if (/^\s*$/.test($msg.val())) {
            $msg.focus();
            return false;
        }
        $.post($form.attr('action'), $form.serializeArray(), function (json) {
            if (callback(json)) {
                $('[name="ChatMessage[message]"]').val('');
                $.pjax.reload('#pjax_chat_message', {
                    history: false,
                    push: false
                });
            }
        });
        $msg.focus();
        return false;
    }
</script>

