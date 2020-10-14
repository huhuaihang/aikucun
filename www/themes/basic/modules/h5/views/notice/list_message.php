<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '公告列表';
?>
<div class="box" id="app">
    <div class="new_header" style="border-bottom: 1px solid #f3f3f3;">
        <a href="<?php echo Url::to(['/h5'])?>" class="a1"><img src="/images/new_header.png"></a>
        <a href="javascript:;" class="a2">消息栏</a>
    </div><!--new_header-->
    <div ref="wrapper" class="new">
<!--        <ul>-->
<!--            <li v-for="notice in notice_list">-->
<!--                <a :href="'/h5/notice/view?id='+notice.id">-->
<!--                    <div class="notice">-->
<!--                        <div class="new-z">-->
<!--                            <p>{{notice.title}}</p>-->
<!--                            <p>{{notice.time | timeFormat}}</p>-->
<!--                        </div>-->
<!--                        <div class="new-y">-->
<!--                            <img :src="notice['main_pic']">-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <!-- <div class="notice-list">-->
<!--                        <p>{{notice.title}}</p>-->
<!--                        <p>{{notice.time | timeFormat}}</p>-->
<!--                    </div> -->
<!--                </a>-->
<!--            </li>-->
<!--        </ul>-->
        <div id="wrap_s">
            <div id="tit_s">
                <a href="/h5/notice/list"> <div><span >公告 </span></div></a>
                <a href="/h5/notice/message"><div><span class="select_s" id="tz">通知</span></div></a>
            </div>
            <div id="login_s">



                    <div class="login_s show_s">
                        <div class="message_s" v-for="message in message_list" >

                            <a  @click ='change_status(message.id,message.url)'    >

                            <p class="message_p">{{message['create_time'] | timeFormat}}</p>
                            <div class="message_n">
                                <h2>{{message['title']}}</h2>
                                <div class="message_m">
                                        <img v-if="message['status']==1" src="/images/yuandian.png" alt="" style="float: left; width: 3%; margin-right: .1rem; margin-top: .25rem;">
                                        <p>点击查看详情</p>
                                        <img src="/images/youjian.png" alt="">
                                </div>
                            </div>
                            </a>


<!---->
<!--                        <div v-else-if="message['url'].indexOf('app')!=-1" >-->
<!--                            <a   @click ='change_status(message.id)'  :href="'--><?php //echo Url::to(['/h5/notice/umview']);?><!--?id=' + message.id "   >-->
<!---->
<!--                                <p class="message_p">{{message['create_time'] | timeFormat}}</p>-->
<!--                                <div class="message_n">-->
<!--                                    <h2>{{message['title']}}</h2>-->
<!--                                    <div class="message_m">-->
<!--                                        <img v-if="message['status']==1" src="/images/yuandian.png" alt="" style="float: left; width: 3%; margin-right: .1rem; margin-top: .25rem;">-->
<!--                                        <p>点击查看详情</p>-->
<!--                                        <img src="/images/youjian.png" alt="">-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </a>-->
<!--                        </div>-->
<!---->
<!--                        <div v-else-if="message['url'].indexOf('team')!=-1" >-->
<!--                            <a  @click ='change_status(message.id)' href="/h5/notice/message"    >-->
<!---->
<!--                                <p class="message_p">{{message['create_time'] | timeFormat}}</p>-->
<!--                                <div class="message_n">-->
<!--                                    <h2>{{message['title']}}</h2>-->
<!--                                    <div class="message_m">-->
<!--                                        <img v-if="message['status']==1" src="/images/yuandian.png" alt="" style="float: left; width: 3%; margin-right: .1rem; margin-top: .25rem;">-->
<!--                                        <p v-if="message['status']==1">点击知晓</p>-->
<!--                                        <img src="/images/youjian.png" alt="">-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </a>-->
<!---->
<!--                        </div>-->




                    </div>




                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
<script>



    var app = new Vue({

        el: '#app',
        data: {
            message_list: [],

            SearchForm: {
                page: 1
            }, // 搜索表单
            page: {}, // 分页
            scroll: false // 滚动监听器
        },
        methods: {

            //获取通知列表
            getuserMessageList: function () {
                apiGet('/api/user/message-list', this.SearchForm, function (json) {
                    if (callback(json)) {
                        console.log(json);
                        json['message_list'].forEach(function (message) {
                            app.message_list.push(message);
                        });
                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true,
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {
                                    if (pos.y < this.maxScrollY + 30) {
                                        if (app.SearchForm.page >= json['page']['pageCount']) {
                                            layer.msg('没有更多数据了。');
                                        } else {
                                            app.SearchForm.page++;
                                            app.getuserMessageList();
                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
                    }
                });
            },


        },
        filters: {
            timeFormat: function (value) {
                var date = new Date(value * 1000);
                var y = date.getFullYear();
                var M = date.getMonth() + 1;
                var d = date.getDate();

                if (M < 10) {
                    M = '0' + M;
                }
                if (d < 10) {
                    d = '0' + d;
                }

                return y + '-' + M + '-' + d;
            },
           //去html格式
           //  noticeFormat:function (HTML)
           //  {
           //      var input = HTML;
           //      return  input.replace(/<(style|script|iframe)[^>]*?>[\s\S]+?<\/\1\s*>/gi, '').replace(/<[^>]+?>/g, '').replace(/\s+/g, ' ').replace(/ /g, ' ').replace(/>/g, ' ');
           //
           //  },
        },
        mounted: function () {
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 95) + 'px';

            this.getuserMessageList();
           // var umessge = '<?php //echo Yii::$app->request->get('umessge')?>//';
           //
           //if(umessge==1)//判断是否为通知详情返回
           //{
           // $('#tit_s').find('span').removeClass('select_s');
           //// $('#tz').addClass('select_s');
           // $('#tit_s').find('span').eq(1).addClass('select_s');
           // $('.login_s').eq(1).show().siblings().hide();
           //}
        },

    });



    // 更新消息状态
    var change_status=function (id,url) {

        apiGet('/api/user/set-message-read?id='+id, {}, function (json) {
            if (callback(json)) {
                if(url.indexOf('app')==-1 && url.indexOf('team')==-1)
                {

               window.location.href=url;
                }
                 if(url.indexOf('team')!=-1)
                {

                    window.location.href='';

                }
                if(url.indexOf('app')!=-1)
                {

                    window.location.href='/h5/notice/umview?id='+ id;

                }
            }
        });


    };


</script>

