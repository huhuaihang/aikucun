<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */
UtilAsset::register($this);
ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);

if(!empty(Yii::$app->request->get('gid')))
{
 $this->title = '确认订单';
}else{
$this->title = '选择会员大礼包';
}
?>
<style>
    .layui-layer-content{padding: .3rem;}
    .layui-layer-content li{margin-bottom: .2rem;}
    .layui-layer-btn0{border-color: #cc1000 !important;background-color: #cc1000 !important;}
    .layui-layer-btn1{border:none !important;background-color: #999 !important; color: #fff !important;}
    .layui-layer-btn2{border:none !important;background-color: #999 !important; color: #fff !important;}
    .layui-layer-title{background: #cc1000 !important; color: #fff !important; width: 100%; text-align: center; padding: 0 !important;}

</style>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title"><?php echo $this->title; ?></div>
    </header>
    <div class="container" v-if="gid">
        <a class="b_bang_phone clearfix b_magt1" href="<?php echo Url::to(['/h5/user/edit-address']);?>" v-if="address_list.length == 0">
            <div class="mizi">
                <p class="p1">没有设置默认地址。</p>
                <p class="p2"></p>
            </div>
            <input type="hidden" name="address_id" id="address_id">
        </a>

        <a class="b_bang_phone clearfix b_magt1" href="javascript:void(0)" @click="selectAddress()" v-if="address_list.length > 0">
            <div class="mizi">
                <p class="p1">{{deliver_address['name']}}<span>{{deliver_address['mobile']}}</span></p>
                <p class="p2">{{deliver_address['city'].join(' ')}} {{deliver_address['address']}}</p>
            </div>
            <input type="hidden" name="address_id" id="address_id" :value="deliver_address['id']">
        </a>
        <address-list :address_list="address_list" v-on:choose="chooseAddress" id="choose_address" style="display:none;"></address-list>
        <div class="sale_rm" >
            <p>注:请选择收货地址，该订单无需支付，提交兑换后直接下单该商品</p>
        </div>
    </div>
    <div style="height: 1rem; width: 100%;"></div>
    <div v-if="gid =='' || gid == undefined " class="sale_we" v-for="goods in goods_pack_list">
        <div class="sale_re_q" @click="check(goods.id,goods.is_have_sku)">
            <img v-if="goods.id == check_goods_id" src="/images/sale_sf.png" alt="">
            <img v-else src="/images/sale_yr.png" alt="">
        </div>
        <div class="sale_re_t">
            <img :src="goods.main_pic" alt="">
        </div>
        <div class="sale_re_u" >
            <p v-if="goods.is_have_sku == 1" class="have_sku" @click="check(goods.id,goods.is_have_sku)">{{goods.title}}</p>
            <p v-else @click="check(goods.id,goods.is_have_sku)">{{goods.title}}</p>
            <p ><b v-if="goods.is_have_sku == 1 && sku_value =='' ">请选择规格</b>
                <b v-if="goods.is_have_sku == 1 && sku_value !='' ">{{sku_value}}</b>
            </p>
            <p>¥{{goods.price}}</p>
        </div>
        <div class="sale_re_p" v-if="goods.is_have_sku == 1">
            <img src="/images/vip_y.png" alt=""  @click="check(goods.id,goods.is_have_sku)">
        </div>
    </div>
<!--    <div class="sale_we">-->
<!--        <div class="sale_re_q">-->
<!--            <img src="/images/sale_yr.png" alt="">-->
<!--        </div>-->
<!--        <div class="sale_re_t">-->
<!--            <img src="/images/shengji_17.jpg" alt="">-->
<!--        </div>-->
<!--        <div class="sale_re_u">-->
<!--            <p>商品名称</p>-->
<!--            <p>请选择规格</p>-->
<!--            <p>¥500</p>-->
<!--        </div>-->
<!--        <div class="sale_re_p">-->
<!--            <img src="/images/vip_y.png" alt="">-->
<!--        </div>-->
<!--    </div>-->
    <div class="sale_rm" v-if="mobile">
        <p>1. 您即将为该粉丝订购会员礼包，请选择任意一款礼包，确认下单后即可激活该粉丝。该操作只会扣除您的礼包数量，无需再付款。此大礼包由云淘帮平台发货。</p>
        <p>2. 下单之前请确保该粉丝在云淘帮平台填写好收货地址。</p>
    </div>

    <div class="sale_bp" v-if="gid"  @click="exchange">
        <a href="#">确认兑换</a>
    </div>
    <div class="sale_bp" v-else  @click="active_user">
        <a href="#" v-if="mobile">确认下单并激活</a>
        <a href="#" v-else>确认下单</a>
    </div>

    <div class="sale_yq">
        <p>温馨提示：此操作无法撤回</p>
    </div>
    <div class="sj-t" style="display: none;" >
        <div class="pack_su" v-if="sku_list.length>0">
            <div class="pack_re" >
                <div class="pack_re_w">
                    <img :src="sku_img" alt="">
                </div>
                <div class="pack_re_q" >
                    <p class="price_msg">价格：¥{{goods.price}}</p>
                    <p>库存：{{goods.stock}}</p>
<!--                    <p>请选择规格</p>-->
                </div>
                <div class="pack_re_r">
                    <img src="/images/pack_g.png" alt="">
                </div>
            </div>
            <div class="pack_op" v-for="(sku_name,index) in sku_gav_list" >
                <p>{{sku_name.name}}</p>
                <div class="pack_ty">
                    <ul>
                        <li v-for="sku_name_list in sku_name.v_list"  :class="{'pack_ty_x' :check_sku_key.indexOf(sku_name_list.id) !== -1  }" @click="check_sku(sku_name_list.id,index)">{{sku_name_list.value}}</li>
                    </ul>
                </div>
            </div>
            <div class="pack_rt">
                <p @click="confirm">确定</p>
            </div>
        </div>
    </div>
</div><!--box-->

<style>
    /*.layui-layer-dialog{*/
       /*display: none;*/
    /*}*/
</style>
<script>
    var mobile = Util.request.get('mobile');
    var coupon_id = Util.request.get('coupon_id');
    var gid = Util.request.get('gid');
    var sku_key_name =Util.request.get('sku_key_name');
    console.log(sku_key_name);
    var app = new Vue({
        el: '#app',
        data: {
            is_have_sku:0,
            check_goods_id:0,
            check_sku_id:0,//选中的规格id
            check_sku_key:[],//选中规格key json 判断样式
            check_index:[],//根据规格索引 判断该规格是否选择
            mobile:mobile,
            real_name:'',
            nickname:'',
            gid:gid,
            address_list: [],
            deliver_address:{},
            sku_key_name:sku_key_name,
            goods_pack_list:[],
            goods:[],//规格默认商品信息
            sku_list:[],//商品规格列表
            sku:[],//商品规格组合列表
            sku_gav_list:[],//规格名称列表
            sku_img:'',
            key:[],
            sku_value:'',//选中的规格名称
            sku_key:'',//选择规格组合字符串  201_205

        },
        methods: {

            /**
             * 获取地址列表
             */
            getAddress: function () {
                apiGet('<?php echo Url::to(['/api/user/address-list']);?>', {}, function (json) {
                    if (callback(json)) {
                        app.address_list = json['address_list'];
                        app.deliver_address = json['address_list'].length > 0 ? json['address_list'][0] : {};
                        //app.getOrder();
                    }
                });
            },
            selectAddress: function () {
                layer.open({
                    type: 1,
                    'content': $('#choose_address'),
                    'btn': ['确定', '新增收货地址', '取消'],
                    btn1: function (index) {
                     //  app.getDeliverFee();
                        layer.close(index);
                    },
                    btn2: function (index) {
                        window.location.href = '<?php echo Url::to(['/h5/user/edit-address']);?>?back=' + encodeURIComponent(window.location.href);
                        layer.close(index)
                    },
                    btn3: function (index) {
                        layer.close(index);
                    }
                });
            },
            chooseAddress: function (address) {
                app.deliver_address = address;
                console.log(app.deliver_address)
            },
            exchange:function(){
                if (Object.keys(app.deliver_address).length < 1) {
                    alert('请选择地址');
                    return false;
                }
                if (this.gid === undefined) {
                    alert('缺少必要商品参数');
                    return false;
                }
                console.log(this.sku_key_name);
                if (this.sku_key_name !== undefined) {
                    this.sku_list.forEach(function (sku) {

                       if(sku['key_name'] === app.sku_key_name)
                       {
                       app.check_sku_id=sku['id'];
                       }
                    });
                    console.log(this.check_sku_id);
                }
                // 兑换大礼包 生成订单
                apiPost('<?php echo Url::to(['/api/user/redeem-package']);?>', {gid:this.gid,sku_id: this.check_sku_id,address_id:this.deliver_address.id}, function (json) {
                    if(json["message"]!==undefined)
                    {
                        alert(json["message"]);
                        return false;
                    }
                    if (callback(json)) {
                        alert('兑换成功');
                        window.location.href='/h5/order';
                    }
                });

            },

            getPackList: function () {

                // 获取礼包商品列表信息
                apiPost('/api/goods/pack-list', {}, function (json) {

                    if (callback(json)) {
                      app.goods_pack_list=json['list'];

                        app.$nextTick(function () {

                            $(".sj-t").height($(window).height());
                            $(".sale_re_p,.have_sku").click(function () {
                                app.getSkuList();
                                //$(".sj-t").show();
                            });

                        });
                    }
                    console.log(app.goods_pack_list)
                });
            },

            active_user:function () {

               if(this.check_goods_id === 0){
                   alert('请选择大礼包商品');
                   return false;
               }


               if(app.is_have_sku === 0)
               {
                if(this.mobile!==undefined)
                {
                // 激活会员用户
                apiPost('<?php echo Url::to(['/api/user/active-user-new']);?>', {mobile:this.mobile,gid:this.check_goods_id,sku_id:''}, function (json) {
                    if(json["message"]!==undefined)
                    {
                        alert(json["message"]);
                        return false;
                    }
                    if (callback(json)) {
                        window.location.href='/h5/user/sale?mobile='+app.mobile+'&real_name='+app.real_name+'&nick_name='+app.nickname;
                    }
                });
                }
                if(coupon_id!==undefined)
                {
                    // 兑换大礼包 生成订单
                    apiPost('<?php echo Url::to(['/api/user/redeem-package']);?>', {gid:this.check_goods_id,sku_id:''}, function (json) {
                        if(json["message"]!==undefined)
                        {
                            alert(json["message"]);
                            return false;
                        }
                        if (callback(json)) {
                            alert('兑换成功');
                            window.location.href='/h5/order';
                        }
                    });
                }
               }else{

                   if (app.check_sku_id === 0) {
                       this.getSkuList();
                   } else {
                       if(this.mobile!==undefined) {
                           // 激活会员用户
                           apiPost('<?php echo Url::to(['/api/user/active-user-new']);?>', {
                               mobile: this.mobile,
                               gid: this.check_goods_id,
                               sku_id: this.check_sku_id
                           }, function (json) {
                               if(json["message"]!==undefined)
                               {
                                   alert(json["message"]);
                                   return false;
                               }
                               if (callback(json)) {
                                   window.location.href = '/h5/user/sale?mobile=' + app.mobile + '&real_name=' + app.real_name + '&nick_name=' + app.nickname;

                               }
                           });
                       }

                       if(coupon_id!==undefined)
                       {
                           // 兑换大礼包 生成订单
                           apiPost('<?php echo Url::to(['/api/user/redeem-package']);?>', {gid:this.check_goods_id,sku_id: this.check_sku_id}, function (json) {
                               if(json["message"]!==undefined)
                               {
                                   alert(json["message"]);
                                   return false;
                               }
                               if (callback(json)) {
                                   alert('兑换成功');
                                   window.location.href='/h5/order';
                               }
                           });
                       }
                   }

               }
            },
            getInfo: function () {
                // 获取用户信息
                apiPost('<?php echo Url::to(['/api/user/check-child-mobile']);?>', {mobile:this.mobile}, function (json) {
                    if (callback(json)) {
                        app.real_name = json['real_name'];
                        app.nickname = json['nickname'];

                    }
                });

            },
            check: function (id, is_have) {

                this.check_goods_id = id;
                this.is_have_sku = is_have;
            },
            check_sku: function (key, index) {


                if (app.check_sku_key.indexOf(key) === -1) {
                    app.key[index]=key;
                }

                app.check_sku_key=JSON.stringify(app.key);
                if (app.key.length > 1) {

                    var str = '';
                    for (var i = 0;i < app.key.length;i++) {

                        if (app.key[i] != undefined) {

                            str+= app.key[i]+('_');
                        }

                    }
                   app.sku_key=str.slice(0,-1)
                   // app.sku_key=str.slice(0,-1);
                }
                else {

                   app.sku_key = app.key.join('');//获取规格key 如84_85
                }
                console.log(app.sku_list);
                if(app.sku.indexOf(app.sku_key) > 0)
                {
                app.goods.price=app.sku_list[app.sku.indexOf(app.sku_key)]['price'];
                }else {
                app.goods.price='不存在此规格';
                }

            },
            confirm: function () {
                if(app.sku.indexOf(app.sku_key)===-1)
                {
                    layer.msg('不存在此规格，请重新选择');
                    $('.layui-layer-dialog').show();
                   // return false;

                }else{
                    console.log(app.check_goods_id)
                    app.sku_value=app.sku_list[app.sku.indexOf(app.sku_key)]['key_name'];
                    app.check_sku_id=app.sku_list[app.sku.indexOf(app.sku_key)]['id'];
                    app.goods_pack_list.forEach(function (goods) {

                     if(goods['id'] === app.check_goods_id)
                     {
                         goods['price'] = app.sku_list[app.sku.indexOf(app.sku_key)]['price'];
                         console.log(goods['price']);
                     }
                    });
                    $(".sj-t").hide();
                }

            },


            getSkuList:function () {
                this.sku=[];
                apiGet('<?php echo Url::to(['/api/goods/detail']);?>', {id:this.check_goods_id}, function (json) {

                    if (callback(json)) {
                        app.sku_list=json['sku_list'];
                        app.goods=json['goods'];
                        app.sku_img=json['goods']['main_pic'];
                        app.sku_gav_list=json['sku_gav_list'];

                        json['sku_list'].forEach(function (list) {
                            app.sku.push(list.key)
                        });
                        if(this.gid===undefined )
                        {
                        $(".sj-t").show();
                        app.$nextTick(function () {
                            $(".pack_re_r").click(function () {
                                $(".sj-t").hide();
                            });

                        });
                        }

                    }
                });
            },

        },
        watch: {

        },

        mounted:function () {

            if(this.mobile!=='' && this.mobile!==undefined) {
                this.getInfo();
            }
            this.getPackList();
            if(this.gid!=='' && this.gid!==undefined )
            {
            this.check_goods_id=this.gid;
            console.log(this.gid)
            this.getAddress();
            if(this.sku_key_name!==undefined){
                this.getSkuList();
            }
            }
        },
        updated:function () {



        }


    });

    Vue.component('address-list', {
        template:
            '<ul>' +
            '    <li style="border-bottom: 1px dashed #000" v-for="(address, address_index) in address_list">' +
            '        <input style="-webkit-appearance:radio;" type="radio" :id="\'a\'+address.id" v-model="choose_aid" class="address" :value="address.id" @click="emitChoose()">' +
            '        <label :for="\'a\'+address.id"><span class="title">{{address.name}} {{address.mobile}}</span></label><br />' +
            '        <span class="area">{{address.city.join(\' \')}} {{address.address}}</span>' +
            '    </li>' +
            '</ul>',
        data: function () {
            return {
                choose_aid: 0
            };
        },
        props: ['address_list'],
        updated: function () {
            console.log(this.choose_aid);
            if (this.choose_aid == 0) {
                this.choose_aid = this.address_list[0]['id'];
            }
        },
        methods: {
            emitChoose: function () {
                var self = this;
                self.$nextTick(function () {
                    var address = {};
                    this.address_list.forEach(function (_address) {
                        if (_address.id == self.choose_aid) {
                            address = _address;
                        }
                    });
                    this.$emit('choose', address);
                });
            }
        }
    });

</script>
