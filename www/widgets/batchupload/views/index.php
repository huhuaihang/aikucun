<script>
    var allow = 1;
    var allow_size ='<?php echo $config['maxSize']?>';
    var serverUrl = '<?php echo $config['serverUrl']?>';
    var fileData = '<?php echo $config['fieldName']?>';
    var inputName = '<?=$inputName?>';
    var source_id = '<?=$id?>';
    var trueDelete = '<?=$config['trueDelete']?>';


</script>
<script src="/js/jquery-2.1.3.min.js"></script><!-- 必须jq -->
<script src="/js/webuploader.html5only.min.js"></script><!-- 多图上传必须的js -->
<script src="/js/diyUpload.js"></script><!-- 多图上传必须的js -->

<div id="main">
    <div class="demo">
        <div id="test" class="btn">
            <span>添加图片</span>

        </div>
        <p>最大<?php echo ceil($config['maxSize']/1024/1024)?>M，支持jpg，gif，png格式。</p>

        <div class="row" id="pics_preview">
            <?php if(!empty($value)):?>
                <?php $value = json_decode($value, 1);?>
                <?php foreach($value as $k => $r):?>
                 <?php if(!empty($r)):?>
                    <div class="col-xs-6 col-md-3" style="position: relative;width:225px;height: 180px;">
                        <span class="del_pic"  onclick="delpic(this)"></span>
                        <input type="hidden" value="<?=$r?>" name="<?=$inputName?>[]" >
                        <a title="点击查看大图" href="<?=$r?>" class="thumbnail" target="_blank"><img  src="<?=$r?>"  style="width: 225px;height: 120px!important;" ></a>
                    </div>
                    <?php endif;?>
                <?php endforeach;?>
            <?php endif;?>
        </div>

    </div>
</div>
<script>
    //删除图片
    function delpic(obj){
        var pic = $(obj).parent().find('img').attr('src');
        var id=source_id;
        var trueDeleteStr = trueDelete == 'true' ? '确认后，服务器上的真实图片也会被删除' : '';
        if(confirm('确认要删除图片吗？' + trueDeleteStr)){
            if(trueDelete == 'true'){

                pic = encodeURIComponent(pic);

                $.get(serverUrl + '?action=delete&pic='+pic+'&id='+id , function(res){

                    if(res){

                        $(obj).parent().remove();
                    }
                });
            }else{
                $(obj).parent().remove();
            }
        }
    }

    $('#test').diyUpload({
        url : serverUrl,  //这个是文件上传处理文件 用框架的请对应文件上传的控制器
        //formData: { _token: "{{csrf_token()}}"}, //Laravel 框架下需要 csrf_token 才能上传，可以在 formData 里面添加需要带过去的参数
        dataType:"json",
        success : function(data) {
            console.info(data);
            // if(data.status == 1){
            //     var span =$("<input type='hidden' value='"+data.imagepath+"' name='img[]'>");//将上传后保存的路径返回 通过隐藏域放进表单里面
            //     $("#test").append(span);
            // }


                var html = '<div class="col-xs-6 col-md-3" style="position: relative;width:225px;height: 180px;"><span class="del_pic" onclick="delpic(this)"></span>';
                html += '<input type="hidden" value="'+data.url+'" name="'+inputName+'[]" ><a class="thumbnail" title="点击查看大图" href="'+data.url+'" target="_blank"><img name="'+data.path+'" class="img-responsive" src="'+data.url+'" style="width: 225px;height: 120px!important;" ></a>';
                html += '</div>'
                $('#pics_preview').append(html );

        },
        error : function(err) {
            console.info(err);
        }
    });

</script>
