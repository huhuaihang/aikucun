<?php

use app\assets\ApiAsset;
use app\assets\CitySelectAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\MerchantJoinForm
 */

ApiAsset::register($this);
CitySelectAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '企业商家入驻申请';
?>
<div class="box" id="app">
    <form @submit.prevent="submit">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">企业商家入驻申请</div>
        <div class="mall-header-right">
            <button type="submit" id="btn_submit">提交</button>
        </div>
    </header>
    <div class="container">
        <!--申请-->
        <div class="b_edit_add">
            <input type="hidden" v-model="MerchantJoinForm.area" name="MerchantJoinForm.area">
            <label class="ubb b_magt1">
                <span>店铺名称</span>
                <input type="text" id="merchantjoinform-shop_name" v-model="MerchantJoinForm.shop_name" name="shop_name" placeholder="请填写店铺名称">
            </label>
            <label class="ubb">
                <span>联系人姓名</span>
                <input type="text" id="merchantjoinform-contact_name" v-model="MerchantJoinForm.contact_name" name="contact_name" placeholder="请填写店铺名称">
            </label>
            <label class="ubb">
                <span>联系电话</span>
                <input type="text" id="merchantjoinform-mobile" v-model="MerchantJoinForm.mobile" name="mobile" placeholder="请填写联系电话">
                <a class="b_get_yzm" @click="sendSmsCode">{{ btn_txt }}</a>
            </label>
            <label class="ubb">
                <span>短信验证码</span>
                <input type="text" id="merchantjoinform-sms_code" v-model="MerchantJoinForm.sms_code" name="sms_code" placeholder="请填写收到的验证码">
            </label>
            <label class="ubb b_magt1">
                <span>登录邮箱</span>
                <input type="text" id="merchantjoinform-username" v-model="MerchantJoinForm.username" name="username" placeholder="请填写商户登录邮箱（登录用）">
            </label>
            <label class="ubb">
                <span>登录密码</span>
                <input type="text" id="merchantjoinform-password" v-model="MerchantJoinForm.password" name="password" placeholder="请填写商户密码（登录用）">
            </label>
            <label class="ubb">
                <span>代理商</span>
                <input type="text" id="merchantjoinform-agent_username" v-model="MerchantJoinForm.agent_username" name="agent_username" placeholder="请填写代理商登录邮箱（非必填项）">
            </label>
            <div class="b_upload_area">
                <p>法人身份证照（正反面）：</p>
                <div class="b_upload_btn" onclick="$('#id_card_front').click()" id="btn_upload_id_card_front"<?php if (!empty($model->id_card_front)) {echo ' style="background-image :url(' . Yii::$app->params['upload_url'] . $model->id_card_front . ');background-position: 0 0; background-size:100% 100%;"';}?>>
                    <p>正面</p>
                </div>
                <input id="id_card_front" type="file" name="files" style="display:none;" @change="uploadIdCardFront" />
                <div class="b_upload_btn" onclick="$('#id_card_back').click()" id="btn_upload_id_card_back"<?php if (!empty($model->id_card_front)) {echo ' style="background-image :url(' . Yii::$app->params['upload_url'] . $model->id_card_back . ');background-position: 0 0; background-size:100% 100%;"';}?>>
                    <p>反面</p>
                </div>
                <input id="id_card_back" type="file" name="files" style="display:none;" @change="uploadIdCardBack" />
            </div>
            <div class="b_upload_area area_pic">
                <p>营业执照照片：</p>
                <div class="b_upload_btn" onclick="$('#business_license').click()" id="btn_upload_business_license"<?php if (!empty($model->business_license)) {echo ' style="background-image :url(' . Yii::$app->params['upload_url'] . $model->business_license . ');background-position: 0 0; background-size:100% 100%;"';}?>>
                    <p>营业执照</p>
                </div>
                <input id="business_license" type="file" name="files" style="display:none;" @change="uploadBusinessLicense" />
            </div>
            <div class="b_good_type b_upload_area category_select_box">
                <p>请选择经营类目：</p>
                <select class="b_good_type1" id="category1" v-model="cid1" @click="checkAll">
                    <option>请选择</option>
                    <option v-for="cate in category" :value="cate.id">{{cate.name}}</option>
                </select>
                <div class="b_good_type2 b_magt1">
                    <ul class="show clearfix" id="category2">
                        <li v-for="sub in sub_category"><input type="checkbox" class="checks" v-model="cid_list2" style="-webkit-appearance:checkbox;" :value="sub.id">{{sub.name}}</li>
                    </ul>
                </div>
            </div>
    </form>
        </div>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            MerchantJoinForm: {
                username: '',
                area: '',
                shop_name: '',
                contact_name: '',
                agent_username: '',
                id_card_back: '',
                id_card_front: '',
                business_license: '',
                password: '',
                mobile: '',
                sms_code: '',
                is_person: 0,
                cid_list: []
            },
            cid1: '',
            cid_list2: [],
            category:[],
            left_sec: 0,
            btn_txt: '获取验证码'
        },
        computed: {
            sub_category: function (){
                var sub_category = [];
                this.category.forEach(function (_sub_category) {
                    if (_sub_category.id == app.cid1) {
                        sub_category = _sub_category.menu;
                    }
                });
                return sub_category;
            }
        },
        methods: {
            uploadIdCardFront: function (e) {
                var self = this;
                apiFile('<?php echo Url::to(['/api/default/upload', 'dir' => 'merchant']);?>', e.target.files[0], function (json) {
                    if (callback(json)) {
                        var url = json['url'];
                        self.MerchantJoinForm.id_card_front = json['uri'];
                        $("#btn_upload_id_card_front").css('background-image','url(<?php echo Yii::$app->params['upload_url'];?>'+json['uri']+')');
                        $("#btn_upload_id_card_front").css('background-position','0 0');
                        $("#btn_upload_id_card_front").css('background-size','100% 100%');
                    }
                });
            },
            uploadIdCardBack: function (e) {
                var self = this;
                apiFile('<?php echo Url::to(['/api/default/upload', 'dir' => 'merchant']);?>', e.target.files[0], function (json) {
                    if (callback(json)) {
                        var url = json['url'];
                        self.MerchantJoinForm.id_card_back = json['uri'];
                        $("#btn_upload_id_card_back").css('background-image','url(<?php echo Yii::$app->params['upload_url'];?>'+json['uri']+')');
                        $("#btn_upload_id_card_back").css('background-position','0 0');
                        $("#btn_upload_id_card_back").css('background-size','100% 100%');
                    }
                });
            },
            uploadBusinessLicense: function (e) {
                var self = this;
                apiFile('<?php echo Url::to(['/api/default/upload', 'dir' => 'merchant']);?>', e.target.files[0], function (json) {
                    if (callback(json)) {
                        var url = json['url'];
                        self.MerchantJoinForm.business_license = json['uri'];
                        $("#btn_upload_business_license").css('background-image','url(<?php echo Yii::$app->params['upload_url'];?>'+json['uri']+')');
                        $("#btn_upload_business_license").css('background-position','0 0');
                        $("#btn_upload_business_license").css('background-size','100% 100%');
                    }
                });
            },
            sendSmsCode: function () {
                if (this.left_sec > 0) {
                    return;
                }
                if (!/^\d{11}$/.test(this.MerchantJoinForm.mobile)) {
                    layer.msg('手机号码格式错误。', function () {});
                    app.MerchantJoinForm.mobile.focus();
                    return false;
                }
                apiGet('<?php echo Url::to(['/api/join/send-sms-code-merchant']);?>', {'mobile':this.MerchantJoinForm.mobile}, function (json) {
                    if (callback(json)) {
                        layer.msg('短信验证码已发送。', function () {});
                        app.left_sec = 60;
                        app.update_time();
                    }
                });
            },
            update_time: function () {
                if (app.left_sec > 0) {
                    app.left_sec--;
                    app.btn_txt = '(' + this.left_sec + ')S';
                    window.setTimeout(function () {app.update_time();}, 1000);
                } else {
                    this.btn_txt = '重新发送';
                }
            },
            submit: function () {
                if (!/^.{1,32}$/.test(this.MerchantJoinForm.username)) {
                    layer.msg('用户名格式错误。', function () {});
                    app.$refs.username.focus();
                    return false;
                }
                if (!/^.+$/.test(this.MerchantJoinForm.password)) {
                    layer.msg('密码不能为空。', function () {});
                    app.$refs.password.focus();
                    return false;
                }
                if (!/^\d{11}$/.test(this.MerchantJoinForm.mobile)) {
                    layer.msg('手机号码格式错误。', function () {});
                    app.$refs.mobile.focus();
                    return false;
                }
                if (!/^\d{4}$/.test(this.MerchantJoinForm.sms_code)) {
                    layer.msg('手机验证码格式错误，只能填写4位数字。', function () {});
                    app.$refs.code.focus();
                    return false;
                }
                this.MerchantJoinForm.cid_list = [this.cid1].concat(this.cid_list2);
                apiPost('<?php echo Url::to(['/api/join/save-merchant']);?>', this.MerchantJoinForm, function (json) {
                    if (callback(json)) {
                        window.location = '<?php echo Url::to(['/h5/join']);?>';
                    }
                });
            },
            loadCity: function () {
                $('[name="MerchantJoinForm.area"]').after(
                    '<div class="ubb">' +
                    '<span>所在地区</span>' +
                    '<div class="b_select" id="citys">' +
                    '<select name="province"></select>' +
                    '<select name="city"></select>' +
                    '<select name="area"></select>' +
                    '</div>' +
                    '</div>'
                );
                $('#citys').citys({
                    dataUrl: makeApiUrl('<?php echo Url::to(['/api/default/city', 'format' => 'flat', 'level' => 3]);?>'),
                    code: this.MerchantJoinForm.area,
                    required: true,
                    onChange: function (city) {
                        app.MerchantJoinForm.area = city['code'];
                    }
                });
            },
            loadCategory: function () {
                apiGet('/api/default/category', {},function(json){
                    if (callback(json)) {
                        json['sub_tree_cate'].splice(0,1);
                        app.category = json['sub_tree_cate'];
                    }
                })
            },
            checkAll: function () {
                this.cid_list2 = [];
                this.sub_category.forEach(function (cate) {
                    app.cid_list2.push(cate.id);
                });
            }
        },
        mounted: function () {
            this.loadCity();
            this.loadCategory();
        }
    });
</script>
