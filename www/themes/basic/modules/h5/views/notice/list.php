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
                <a href="/h5/notice/list"><div><span class="select_s"> 公告 </span></div></a>
                <a href="/h5/notice/message">  <div><span id="tz">通知</span></div></a>
            </div>
            <div id="login_s">
                <div class="login_s show_s">
                    <div v-if="notice_list.length == 0" style="text-align: center; padding-top: 20px;">暂时没有公告</div>


                    <div class="message_s" v-for="notice in notice_list">
                        <p class="message_p">{{notice['time'] | timeFormat}}</p>
                        <div class="message_n">
                            <a :href="'<?php echo Url::to(['/h5/notice/view']);?>?id=' + notice.id ">
                            <h2>{{notice['title']}}</h2>
                            <p >{{notice['desc']}}</p>
                            <div class="message_m">
                                     <p>点击查看详情</p>
                                    <img src="/images/youjian.png" alt="">

                            </div>
                            </a>
                        </div>
                    </div>

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

            notice_list: [],
            SearchForm: {
                page: 1
            }, // 搜索表单
            page: {}, // 分页
            scroll: false // 滚动监听器
        },
        methods: {
            //获取公告列表
            loadNoticeList: function () {
                apiGet('/api/notice/list', this.SearchForm, function (json) {
                    console.log(json)
                    if (callback(json)) {
                        json['list'].forEach(function (notice) {
                            app.notice_list.push(notice);
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
                                            app.loadNoticeList();

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
               // var h = date.getHours();
              //  var m = date.getMinutes();
              //  var s = date.getSeconds();
                if (M < 10) {
                    M = '0' + M;
                }
                if (d < 10) {
                    d = '0' + d;
                }
                // if (h < 10) {
                //     h = '0' + h;
                // }
                // if (m < 10) {
                //     m = '0' + m;
                // }
                // if (s < 10) {
                //     s = '0' + s;
                // }
               // return y + '-' + M + '-' + d + ' ' + h + ':' + m + ':' + s;
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
            this.loadNoticeList();


        },

    });
    // $('#tit_s div').click(function() {
    //     var i = $(this).index();//下标第一种写法
    //
    //     $(this).find('span').addClass('select_s');
    //     $(this).siblings('div').find('span').removeClass('select_s');
    //     $('.login_s').eq(i).show().siblings().hide();
    // });





</script>

