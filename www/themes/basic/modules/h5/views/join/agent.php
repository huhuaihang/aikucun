<?php

use app\assets\ApiAsset;
use app\assets\CitySelectAsset;
use app\assets\LayerAsset;
use app\widgets\FileUploadWidget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\AgentJoinForm
 */

ApiAsset::register($this);
LayerAsset::register($this);
CitySelectAsset::register($this);

$this->title = '代理商在线申请';
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">代理商在线申请</div>
        <div class="mall-header-right">
            <button type="submit" id="btn_submit">提交</button>
        </div>
    </header>
    <div class="container">
        <?php echo Html::beginForm(['/h5/join/save-agent'], 'post', ['id' => 'agent_join_form']);?>
        <div class="b_edit_add">
            <?php echo Html::activeHiddenInput($model, 'area');?>
            <label class="ubb">
                <span>所在地区</span>
                <div class="b_select" id="citys">
                    <select name="province"></select>
                    <select name="city"></select>
                    <select name="area"></select>
                </div>
            </label>
            <label class="ubb">
                <span>负责人姓名</span>
                <?php echo Html::activeTextInput($model, 'contact_name', ['placeholder' => '请填写真实姓名']);?>
            </label>
            <label class="ubb">
                <span>联系电话</span>
                <?php echo Html::activeInput('number', $model, 'mobile', ['placeholder' => '请填写联系电话']);?>
                <a class="b_get_yzm" href="javascript:void(0)" id="btn_send_sms_code">获取验证码</a>
            </label>
            <label class="ubb">
                <span>短信验证码</span>
                <?php echo Html::activeInput('number', $model, 'sms_code', ['placeholder' => '请填写收到的验证码']);?>
            </label>
            <label class="ubb b_magt1">
                <span>登录邮箱</span>
                <?php echo Html::activeTextInput($model, 'username', ['placeholder' => '请填写代理邮箱（登录用）']);?>
            </label>
            <label class="ubb">
                <span>代理密码</span>
                <?php echo Html::activePasswordInput($model, 'password', ['placeholder' => '请填写代理密码（登录用）']);?>
            </label>
            <div class="b_upload_area">
                <p>申请人身份证照（正反面）：</p>
                <div class="b_upload_btn" id="btn_upload_id_card_front"<?php if (!empty($model->id_card_front)) {echo ' style="background-image :url(' . Yii::$app->params['upload_url'] . $model->id_card_front . ');background-position: 0 0; background-size:100% 100%;"';}?>>
                    <p>正面</p>
                </div>
                <?php echo FileUploadWidget::widget([
                    'name' => 'AgentJoinForm[id_card_front]',
                    'value' => $model->id_card_front,
                    'url' => Url::to(['/h5/join/upload', 'dir' => 'merchant']),
                    'click_node' => '#btn_upload_id_card_front',
                    'callback' => 'uploadCallbackFront',
                ]);?>
                <div class="b_upload_btn" id="btn_upload_id_card_back"<?php if (!empty($model->id_card_front)) {echo ' style="background-image :url(' . Yii::$app->params['upload_url'] . $model->id_card_back . ');background-position: 0 0; background-size:100% 100%;"';}?>>
                    <p>反面</p>
                </div>
                <?php echo FileUploadWidget::widget([
                    'name' => 'AgentJoinForm[id_card_back]',
                    'value' => $model->id_card_back,
                    'url' => Url::to(['/h5/join/upload', 'dir' => 'merchant']),
                    'click_node' => '#btn_upload_id_card_back',
                    'callback' => 'uploadCallbackBack',
                ]);?>
            </div>
        </div>
        <?php echo Html::endForm();?>
    </div>
</div>
<script>
    function page_init() {
        $('#citys').citys({
            dataUrl: makeApiUrl('<?php echo Url::to(['/api/default/city', 'format' => 'flat', 'level' => 3]);?>'),
            code: $('[name="AgentJoinForm[area]"]').val(),
            required: false,
            placeholder: ' - 选择区域 - ',
            onChange: function(city) {
                $('[name="AgentJoinForm[area]"]').val(city['code']);
            }
        });
        $('#btn_send_sms_code').click(function () {
            var mobile = $('[name="AgentJoinForm[mobile]"]').val();
            $.getJSON('<?php echo Url::to(['/h5/join/send-sms-code-agent']);?>', {'mobile':mobile}, function (json) {
                if (callback(json)) {
                    layer.msg('短信已发送。', function () {});
                }
            });
        });
        $('#btn_submit').click(function () {
            var $form = $('#agent_join_form');
            $.post($form.attr('action'), $form.serializeArray(), function (json) {
                if (callback(json)) {
                    layer.msg('您的申请已提交，请等待客服人员主动联系您。', function () {window.location.reload();});
                }
            });
        });
    }
    function uploadCallbackFront(url) {
        $('[name="AgentJoinForm[id_card_front]"]').val(url);
        $('#btn_upload_id_card_front')
            .css('background-image', 'url(<?php echo Yii::$app->params['upload_url'];?>' + url + ')')
            .css('background-position', '0 0')
            .css('background-size', '100% 100%');
    }
    function uploadCallbackBack(url) {
        $('[name="AgentJoinForm[id_card_back]"]').val(url);
        $('#btn_upload_id_card_back')
            .css('background-image', 'url(<?php echo Yii::$app->params['upload_url'];?>' + url + ')')
            .css('background-position', '0 0')
            .css('background-size', '100% 100%');
    }
</script>
