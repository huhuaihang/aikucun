<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\UserAddress[]
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '收货地址管理';
?>
<style>
    body{ background-color: #f4f4f4;}
</style>
<div class="box" id="list">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5/user/profile']);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">收货地址管理</div>
    </header>
    <div class="container">
        <!--地址-->
        <div class="b_addlist">
            <dl class="b_magt" v-for="(item,index) in items">
                <dt class="ubb">
                    <p class="b_addp1">{{item.name}}<span>{{item.mobile}}</span></p>
                    <p class="b_addp2 ">{{item.city.join(' ')}} {{item.address}}</p>
                </dt>
                <dd class="clearfix">
                    <div class="b_add_default">
                        <span class="b_add_radio" data-toggle="set-default" @click="setDefault(index,item.id)" v-bind:data-val="item.id" v-if='item.is_default != 1'><img src="/images/address_noselect_03.png"/></span>
                        <span class="b_add_radio" v-else>
                            <img src="/images/address_selected_03.png"/>
                        </span>
                        <span v-if="item.is_default" data-toggle="set-default" @click="setDefault(index,item.id)" v-bind:data-val='item.id'>设为默认</span>
                        <span v-else>默认地址</span>
                    </div>
                    <div class="b_add_default">
                        <span>
                            <img src="/images/address_edit_03.png"/>
                        </span>
                        <span><a :href="'<?php echo Url::to(['/h5/user/edit-address']);?>?id='+item.id">编辑</a></span>
                    </div>
                     <div class="b_add_default b_address_cancel" @click="deleteAddress(index)">
                        <span>
                            <img src="/images/address_cancel_03.png"/>
                        </span>
                        <span>删除</span>
                    </div>
                </dd>
            </dl>
        </div>
        <a class="b_add_new_address" href="<?php echo Url::to(['/h5/user/edit-address']);?>">新增地址</a>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#list',
        data: {
            items: []
        },
        methods: {
            setDefault: function(index, id){
                apiPost('<?php echo Url::to(['/api/user/set-address-default']);?>', {'id':id}, function (json) {
                    if (callback(json)) {
                        app.items.forEach(function (item,i) {
                            item.is_default = item.id == id;
                        });
                    }
                });
            },
            deleteAddress: function (index) {
                layer.confirm('确定要删除地址吗？', {
                    title: '确认',
                    btn: ['确定', '取消']
                }, function(layer_index){
                    apiPost('<?php echo Url::to(['/api/user/delete-address']);?>', {'id':app.items[index].id}, function (json) {
                        if (callback(json)) {
                            app.items.splice(index, 1);
                            layer.close(layer_index);
                        }
                    });
                });
            }
        },
        mounted: function () {
            apiGet('<?php echo Url::to(['/api/user/address-list']);?>', '', function (json) {
                if (callback(json)) {
                    app.items = json['address_list'];
                }
            });
        }
    });
</script>
