<?php

use app\assets\ApiAsset;
use app\assets\CitySelectAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
CitySelectAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '编辑收货地址';
?>
<div class="box page" id="app">
    <form id="form" @submit.prevent="submit">
        <header class="mall-header">
            <div class="mall-header-left">
                <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
            </div>
            <div class="mall-header-title">编辑收货地址</div>
            <div class="mall-header-right"  style="position: relative">
                <button type="submit">保存</button>
            </div>
        </header>
        <div class="container">
            <!--地址编辑-->
            <div class="b_edit_add">
                <label class="ubb">
                    <span>收货人</span>
                    <input v-model="UserAddress.name" placeholder='姓名'>
                </label>
                <label class="ubb">
                    <span>联系电话</span>
                    <input v-model="UserAddress.mobile" placeholder='电话'>
                </label>
                <span class="ubb">
                    <span>所在地区</span>
                    <input v-model="UserAddress.area" name="UserAddress.area" type="hidden">
                </span>
                <textarea v-model="UserAddress.address" placeholder='详细地址'></textarea>
            </div>
        </div>
    </form>
</div>
<?php echo $this->render('../layouts/_bottom_nav');?>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            UserAddress: {}
        },
        methods: {
            submit: function () {
                if ($("select[name='province']").val() === '') {
                    layer.msg('请选择所在省份');
                    return false;
                }
                if ($("select[name='city']").is(":visible") === true) {
                    if ($("select[name='city']").val() === '') {
                        layer.msg('请选择所在城市');
                        return false;
                    }
                }
                if ($("select[name='area']").is(":visible") === true) {
                    if ($("select[name='area']").val() === '') {
                        layer.msg('请选择所在地区');
                        return false;
                    }
                }
                app.UserAddress.area = String(app.UserAddress.area);
                layer.msg('正在保存');
                apiPost('<?php echo Url::to(['/api/user/save-address']);?>', app.UserAddress, function (json) {
                    if (callback(json)) {
                        var back = Util.request.get('back');
                        if (back !== undefined) {
                            window.location.href = decodeURIComponent(back);
                        } else {
                            window.location.href = '<?php echo Url::to(['/h5/user/address']);?>';
                        }
                    }
                });
            },
            loadCity: function () {
                $('[name="UserAddress.area"]').after(
                    '<div class="b_select" id="citys">' +
                    '    <select name="province">请选择</select>'+
                    '    <select name="city"></select>' +
                    '    <select name="area"></select>' +
                    '</div>'
                );
                $('#citys').citys({
                    dataUrl: makeApiUrl('<?php echo Url::to(['/api/default/city', 'format' => 'flat', 'level' => 3]);?>'),
                    code: this.UserAddress.area,
                    required: false,
                    onChange: function (city) {
                        console.log(city)
                        app.UserAddress.area = city['code'];
                    }
                });
            }
        },
        mounted: function () {
            var id = "<?php echo Yii::$app->request->get('id')?>";
            if (id) {
                apiGet('<?php echo Url::to(['/api/user/address-detail']) . '?id=';?>' + id, '', function (json) {
                    if (callback(json)) {
                        if (json['address']) {
                            app.UserAddress = json['address'];
                        }
                        app.loadCity();
                    } else {
                        window.history.go(-1);
                    }
                });
            } else {
                this.loadCity();
            }
        }
    });
</script>
