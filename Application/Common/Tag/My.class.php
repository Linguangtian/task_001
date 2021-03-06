<?php

namespace Common\Tag;
use Think\Template\TagLib;

class My extends TagLib {
    // 定义标签
    protected $tags=array(
        'jquery'=>array('attr'=>'','close'=>0),
        'bootstrapcss'=>array('attr'=>'icheck','close'=>0),
        'bootstrapjs'=>array('attr'=>'icheck','close'=>0),
        'suicss'=>array('attr'=>'icheck','close'=>0),
        'suijs'=>array('attr'=>'icheck','close'=>0),
        'framework7css'=>array('attr'=>'icheck','close'=>0),
        'framework7js'=>array('attr'=>'icheck','close'=>0),
        'frozenuicss'=>array('attr'=>'icheck','close'=>0),
        'frozenuijs'=>array('attr'=>'icheck','close'=>0),
        'icheckcss'=>array('attr'=>'','close'=>0),
        'icheckjs'=>array('attr'=>'color','close'=>0),
        'datejs'=>array('attr'=>'id,color','close'=>0),
        'layer'=>array('attr'=>'id,color','close'=>0),
        'animate'=>array('attr'=>'','close'=>0),
        'ueditor'=>array('attr'=>'name,content,height','close'=>0),
        'umeditorcss'=>array('attr'=>'','close'=>0),
        'umeditorjs'=>array('attr'=>'','close'=>0),
        'umeditor'=>array('attr'=>'name,content,height','close'=>0),
        'webuploadercss'=>array('attr'=>'','close'=>0),
        'webuploader'=>array('attr'=>'name,url,word','close'=>0),
        'webuploader2'=>array('attr'=>'name,url,word','close'=>0),
        'webuploaderjs'=>array('attr'=>'','close'=>0),
        'vue'=>array('attr'=>'','close'=>0)
        );

    /**
     * layer弹出层
     */
    public function _layer(){
        $str=<<<php
<script src="__PUBLIC__/statics/layer/layer.js"></script>
<script src="__PUBLIC__/statics/layer/extend/layer.ext.js"></script>        
php;
        return $str;
    }

    /**
     * jquery
     */
    public function _jquery(){
        $str=<<<php
<script src="__PUBLIC__/statics/js/jquery-1.10.2.min.js"></script>    
php;
        return $str;
    }
    
    /**
     * vue
     */
    public function _vue(){
        $str=<<<php
<script src="__PUBLIC__/statics/vue/vue.js"></script>    
<script src="__PUBLIC__/statics/vue/vue-resource.min.js"></script> 
<script>
    Vue.http.options.emulateJSON = true;
</script>    
php;
        return $str;
    }

    /**
    * 引入laydate的js部分
    * @param string $tag  颜色主题
    */
    public function _datejs($tag){
        $theme=isset($tag['theme']) ? $tag['theme'] : 'molv';
        $config='laydate({elem: ".xb-date",event: "click",format: "YYYY/MM/DD hh:mm:ss",istime: false,isclear: true,istoday: true,issure: true,festival: true,min: "2015-03-01 00:00:00",max: "2015-04-01 23:59:59",start: laydate.now(),fixed: false,zIndex: 99999999})';
        $link=<<<php
<script src="__PUBLIC__/statics/laydate-v1.1/laydate.js"></script>
<script>
    $('body').attr('id', 'xb-date');
    laydate.skin("$theme");
    var tody=laydate.now();
    laydate({
        elem: '#xb-date .xb-date',
        event: 'click',
        format: 'YYYY-MM-DD hh:mm:ss',
        istime: true,
        isclear: true,
        istoday: true,
        issure: true,
        festival: true,
        start: laydate.now(),
        fixed: false,
        zIndex: 99999999,
    })
</script>
php;
        return $link;
    }

    //引入animate
    public function _animate(){
        return '<link rel="stylesheet" href="__PUBLIC__/statics/css/animate.css">';
    }

    /**
    * 引入ickeck的css部分
    */
    public function _icheckcss(){
        $link=<<<php
    <link rel="stylesheet" href="__PUBLIC__/statics/iCheck-1.0.2/skins/all.css">
php;
        return $link;
    }

    /**
    * 引入ickeck的js部分
    * @param string $tag  颜色主题
    */
    public function _icheckjs($tag){
        $color=isset($tag['color']) ? $tag['color'] : 'green';
        $link=<<<php
<script src="__PUBLIC__/statics/iCheck-1.0.2/icheck.min.js"></script>
<script>
$(document).ready(function(){
    $('.xb-icheck').iCheck({
        checkboxClass: "icheckbox_minimal-$color",
        radioClass: "iradio_minimal-$color",
        increaseArea: "20%"
    });
});
</script>
php;
        return $link;
    }

    // bootstrapcss标签
    public function _bootstrapcss($tag,$content) {
        $link=<<<php
    <meta http-equiv="Cache-Control" content="no-transform" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" href="__PUBLIC__/statics/bootstrap-3.3.5/css/bootstrap.min.css" />
    <link rel="stylesheet" href="__PUBLIC__/statics/bootstrap-3.3.5/css/bootstrap-theme.min.css" />
    <link rel="stylesheet" href="__PUBLIC__/statics/font-awesome-4.4.0/css/font-awesome.min.css" />

    <!--[if IE 7]>
        <link rel="stylesheet" href="__ADMIN_ACEADMIN__/css/font-awesome-ie7.min.css" />
    <![endif]-->
    <link rel="stylesheet" href="__ADMIN_ACEADMIN__/css/ace.min.css" />
    <link rel="stylesheet" href="__ADMIN_ACEADMIN__/css/ace-rtl.min.css" />
    <link rel="stylesheet" href="__ADMIN_ACEADMIN__/css/ace-skins.min.css" />
    <!--[if lte IE 8]>
        <link rel="stylesheet" href="__ADMIN_ACEADMIN__/css/ace-ie.min.css" />
    <![endif]-->
    <script src="__ADMIN_ACEADMIN__/js/ace-extra.min.js"></script>
    <!--[if lt IE 9]>
        <script src="__ADMIN_ACEADMIN__/js/html5shiv.js"></script>
        <script src="__ADMIN_ACEADMIN__/js/respond.min.js"></script>
    <![endif]-->

    <link rel="stylesheet" href="__PUBLIC_CSS__/base.css" />
    <link rel="stylesheet" href="__PUBLIC_JS__/artDialog/skins/default.css" />
php;
    return $link;
    }

    // bootstrapjs标签
    public function _bootstrapjs($tag,$content) {
        $link=<<<php
<!-- 引入bootstrjs部分开始 -->
<script src="__PUBLIC__/statics/js/jquery-1.10.2.min.js"></script>
<script src="__PUBLIC__/statics/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script src="__PUBLIC_JS__/artDialog/artDialog.js"></script>
<script src="__PUBLIC_JS__/artDialog/iframeTools.js"></script>
<script src="__PUBLIC_JS__/bootbox.js"></script>
<script src="__PUBLIC_JS__/base.js"></script>

<link rel="stylesheet" href="__PUBLIC_JS__/datepicker/css/bootstrap-datepicker3.min.css" />
<link rel="stylesheet" href="__PUBLIC_JS__/datepicker/css/bootstrap-datetimepicker.min.css" />
<script src="__PUBLIC_JS__/datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="__PUBLIC_JS__/datepicker/js/bootstrap-timepicker.min.js"></script>

<script src="__PUBLIC__/statics/layer/layer.js"></script>
<script src="__PUBLIC__/statics/layer/extend/layer.ext.js"></script>

php;
    return $link;
    }

    // bootstrapcss标签
    public function _suicss($tag,$content) {
        $link=<<<php
<link rel="stylesheet" href="__PUBLIC__/statics/sui-0.6.1/css/sm.min.css">
php;
    return $link;
    }

    // bootstrapjs标签
    public function _suijs($tag,$content) {
        $link=<<<php
<script src="__PUBLIC__/statics/js/zepto-1.1.6.min.js"></script>
<script src="__PUBLIC__/statics/sui-0.6.1/js/sm.min.js"></script>
<script>
    $.init();
</script>
php;
    return $link;
    }

    // framework7css标签
    public function _framework7css($tag,$content) {
        $link=<<<php
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="__PUBLIC__/statics/Framework7-1.2.0/css/framework7.ios.min.css" />
    <link rel="stylesheet" href="__PUBLIC__/statics/Framework7-1.2.0/css/framework7.ios.colors.min.css" />
php;
    return $link;
    }

    // framework7js标签
    public function _framework7js($tag,$content) {
        $link=<<<php
    <script src="__PUBLIC__/statics/Framework7-1.2.0/js/framework7.min.js"></script>
php;
    return $link;
    }

    // frozenuicss标签
    public function _frozenuicss($tag,$content) {
        $link=<<<php
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="__PUBLIC__/statics/frozenui-1.3.0/css/frozen.css" />
    <link rel="stylesheet" href="__PUBLIC_CSS__/base.css" />
php;
    return $link;
    }

    // frozenuijs标签
    public function _frozenuijs($tag,$content) {
        $link=<<<php
<script src="__PUBLIC__/statics/frozenui-1.3.0/lib/zepto.min.js"></script>
<script src="__PUBLIC__/statics/frozenui-1.3.0/js/frozen.js"></script>
<script src="__PUBLIC_JS__/base.js"></script>
php;
    return $link;
    }

    /**
    * 引入umeidter的css部分
    */
   public function _umeditorcss(){
        $link=<<<php
<link rel="stylesheet" href="/Public/statics/umeditor1_2_2/themes/default/css/umeditor.css">
php;
        return $link;
   }
    /**
    * 引入umeidter的js部分
    */
   public function _umeditorjs(){
        $link=<<<php
<script src="/Public/statics/umeditor1_2_2/umeditor.config.js"></script>
<script src="/Public/statics/umeditor1_2_2/umeditor.js"></script>
<script src="/Public/statics/umeditor1_2_2/lang/zh-cn/zh-cn.js"></script>
php;
        return $link;
   }
    /**
    * 引入umeidter编辑器
    * @param string $tag  name:表单name content：编辑器初始化后 默认内容
    */
   public function _umeditor($tag){
        $name=isset($tag['name']) ? $tag['name'] : 'content';
        $content=isset($tag['content']) ? $tag['content'] : '';
        $height=isset($tag['height']) ? $tag['height'] : '320';
        $link=<<<php
<!-- 加载编辑器的容器 -->
<script id="container" name="$name" type="text/plain" >$content</script>
<!-- 实例化编辑器代码 -->
<script>
    $(function(){
        window.um = UM.getEditor('container',{
            initialFrameHeight:$height
        });
    });
</script>
php;
        return $link;
   }

    /**
    * 引入ueidter编辑器
    * @param string $tag  name:表单name content：编辑器初始化后 默认内容
    */
    public function _ueditor($tag){
        $name=isset($tag['name']) ? $tag['name'] : 'content';
        $content=isset($tag['content']) ? $tag['content'] : '';
        $height=isset($tag['height']) ? $tag['height'] : '300';
        $link=<<<php
<script id="container" name="$name" type="text/plain">
    $content
</script>
<script src="/Public/statics/ueditor1_4_3/ueditor.config.js"></script>
<script src="/Public/statics/ueditor1_4_3/ueditor.all.js"></script>
<script>
    var um = UE.getEditor('container',{
        initialFrameHeight:$height,
        toolbars: [[
            'fullscreen',  'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
            'link', 'unlink', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'simpleupload', 'emotion', 'scrawl', 'insertvideo', 'music', 'map',   'insertcode', 'template', '|',
            'horizontal', 'date', 'time', 'spechars', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
             'searchreplace', 'drafts'
        ]],
        autoHeightEnabled:false,
        catchRemoteImageEnable:true
    });
</script>
php;
        return $link;
    }

    // webuploader的css部分和jquery因为插件需要引在jquery后边；所以在头部引入了jquery
    public function _webuploadercss(){
        $str=<<<php
<link rel="stylesheet" href="__PUBLIC__/statics/webuploader-0.1.5/xb-webuploader.css?v=101">
<script src="__PUBLIC__/statics/js/jquery-1.10.2.min.js"></script>
php;
        return $str;
    }

    // webuploader的js部分
    public function _webuploaderjs(){
        $str=<<<php
<script>
    var BASE_URL = '__PUBLIC__/statics/webuploader-0.1.5';
    
</script>
<!--<script src="//cdn.staticfile.org/webuploader/0.1.5/webuploader.min.js"></script>-->
<script src="/Public/statics/js/webuploader.min.js?v=101"></script>
php;
        return $str;
    }

    /**
     * 上传标签
     * @param string $tag  
     * url：上传的图片处理的控制器方法   
     * name：表单name   
     * word：提示文字
     */




    /**
     * 上传标签
     * @param string $tag
     * url：上传的图片处理的控制器方法
     * name：表单name
     * word：提示文字
     */
    public function _webuploader2($tag){
        $url=isset($tag['url'])?$tag['url']:U('Home/Index/ajax_upload');
        $name=isset($tag['name'])?$tag['name']:'file_name';
        $word=isset($tag['word'])?$tag['word']:'或将照片拖到这里，单次最多可选1张';
        $text=isset($tag['text'])?$tag['text']:'点击选择文件';
        $max_num=isset($tag['max_num'])?$tag['max_num']:1;
        $w=isset($tag['w'])?$tag['w']:'160';
        $h=isset($tag['h'])?$tag['h']:'160';
        $id_name='upload-'.uniqid().rand(1000000,9999999);
        $str=<<<php
<div id="$id_name" class="xb-uploader">
    <div class="queueList">
        <div class="placeholder">
            <div class="filePicker"></div>
            <p>$word</p>
        </div>
    </div>
    <div class="statusBar" style="display:none;">
        <div class="progress">
            <span class="text">0%</span>
            <span class="percentage"></span>
        </div>
        <div class="info"></div>
        <div class="btns">
            <div class="webuploader-container filePicker2">
                <div class="webuploader-pick">继续添加</div>
                <div style="position: absolute; top: 0px; left: 0px; width: 1px; height: 1px; overflow: hidden;" id="rt_rt_$id_name">
                </div>
            </div>
            <div class="uploadBtn">开始上传</div>
        </div>
    </div>
</div>
<script>
jQuery(function() {
    var \$ = jQuery,    // just in case. Make sure it's not an other libaray.

        \$wrap = \$("#$id_name"),

        // 图片容器
        \$queue = \$('<ul class="filelist"></ul>')
            .appendTo( \$wrap.find('.queueList') ),

        // 状态栏，包括进度和控制按钮
        \$statusBar = \$wrap.find('.statusBar'),

        // 文件总体选择信息。
        \$info = \$statusBar.find('.info'),

        // 上传按钮
        \$upload = \$wrap.find('.uploadBtn'),

        // 没选择文件之前的内容。
        \$placeHolder = \$wrap.find('.placeholder'),

        // 总体进度条
        \$progress = \$statusBar.find('.progress').hide(),

        // 添加的文件数量
        fileCount = 0,

        // 添加的文件总大小
        fileSize = 0,

        // 优化retina, 在retina下这个值是2
        ratio = window.devicePixelRatio || 1,

        // 缩略图大小
        thumbnailWidth = $w * ratio,
        thumbnailHeight = $h * ratio,

        // 可能有pedding, ready, uploading, confirm, done.
        state = 'pedding',

        // 所有文件的进度信息，key为file id
        percentages = {},

        supportTransition = (function(){
            var s = document.createElement('p').style,
                r = 'transition' in s ||
                      'WebkitTransition' in s ||
                      'MozTransition' in s ||
                      'msTransition' in s ||
                      'OTransition' in s;
            s = null;
            return r;
        })(),
        thisSuccess,
        // WebUploader实例
        uploader;

    if ( !WebUploader.Uploader.support() ) {
        alert( 'Web Uploader 不支持您的浏览器！如果你使用的是IE浏览器，请尝试升级 flash 播放器');
        throw new Error( 'WebUploader does not support the browser you are using.' );
    }

    // 实例化
    uploader = WebUploader.create({
        pick: {
            id: "#$id_name .filePicker",
            label: '$text',
       
        },
       
        dnd: "#$id_name .queueList",
        paste: document.body,
        // accept: {
        //     title: 'Images',
        //     extensions: 'gif,jpg,jpeg,bmp,png',
        //     mimeTypes: 'image/*'
        // },

        // swf文件路径
         compress:{
                    width: 800,
                    height: 800,

                    // 图片质量，只有type为`image/jpeg`的时候才有效。
                    quality: 100,

                    // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.
                    allowMagnify: true,

                    // 是否允许裁剪。
                    crop: false,

                    // 是否保留头部meta信息。
                    preserveHeaders: true,

                    // 如果发现压缩后文件大小比原来还大，则使用原来图片
                    // 此属性可能会影响图片自动纠正功能
                    noCompressIfLarger: false,

                    // 单位字节，如果图片大小小于此值，不会采用压缩。
                    compressSize: 0
                },
        
        swf: BASE_URL + '/Uploader.swf',

        disableGlobalDnd: true,
       
        chunked: false,
        server: "$url",
        fileNumLimit: $max_num,
        fileSizeLimit: 200 * 1024 * 1024,          // 200 M
        fileSingleSizeLimit: 50 * 1024 * 1024,    // 50 M
    });

    // 添加“添加文件”的按钮，
    uploader.addButton({
       id: "#$id_name .filePicker2",
      label: '继续添加'
    });
        
        
   
    
    
    // 当有文件添加进来时执行，负责view的创建
    function addFile( file ) {
   
        var \$li = \$( '<li id="' + file.id + '">' +
                '<p class="title">' + file.name + '</p>' +
                '<p class="imgWrap"></p>'+
                '<p class="progress"><span></span></p>' +
                '<input class="bjy-filename" type="hidden" name="{$name}">'+
                '</li>' ),

            \$btns = \$('<div class="file-panel">' +
                '<span class="cancel">删除</span>' +
                '<span class="rotateRight">向右旋转</span>' +
                '<span class="rotateLeft">向左旋转</span></div>').appendTo( \$li ),
            \$prgress = \$li.find('p.progress span'),
            \$wrap = \$li.find( 'p.imgWrap' ),
            \$info = \$('<p class="error"></p>'),

            showError = function( code ) {
                switch( code ) {
                    case 'exceed_size':
                        text = '文件大小超出';
                        break;

                    case 'interrupt':
                        text = '上传暂停';
                        break;

                    default:
                        text = '上传失败，请重试';
                        break;
                }

                \$info.text( text ).appendTo( \$li );
            };

        if ( file.getStatus() === 'invalid' ) {
            showError( file.statusText );
        } else {
            // @todo lazyload
            \$wrap.text( '上传中' );
            uploader.makeThumb( file, function( error, src ) {
                if ( error ) {
                    \$wrap.text( '不能预览' );
                    return;
                }

                var img = \$('<img src="'+src+'">');
               /* \$wrap.empty().append( img );*/
            }, thumbnailWidth, thumbnailHeight );

            percentages[ file.id ] = [ file.size, 0 ];
            file.rotation = 0;
        }

        file.on('statuschange', function( cur, prev ) {
         
    
            if ( prev === 'progress' ) {
                \$prgress.hide().width(0);
            } else if ( prev === 'queued' ) {
                \$li.off( 'mouseenter mouseleave' );
                \$btns.remove();
            }

            // 成功
            if ( cur === 'error' || cur === 'invalid' ) {
                showError( file.statusText );
                percentages[ file.id ][ 1 ] = 1;
            } else if ( cur === 'interrupt' ) {
                showError( 'interrupt' );
            } else if ( cur === 'queued' ) {
                percentages[ file.id ][ 1 ] = 0;
            } else if ( cur === 'progress' ) {
                \$info.remove();
                \$prgress.css('display', 'block');
            } else if ( cur === 'complete' ) {
                \$li.append( '<span class="success"></span><span class="del"></span>' );
            }
  
            \$li.removeClass( 'state-' + prev ).addClass( 'state-' + cur );
           
        });

        \$li.on( 'mouseenter', function() {
            \$btns.stop().animate({height: 30});
        });

        \$li.on( 'mouseleave', function() {
            \$btns.stop().animate({height: 0});
        });
 
        \$btns.on( 'click', 'span', function() {
            var index = \$(this).index(),
                deg;

            switch ( index ) {
                case 0:
                    uploader.removeFile( file );
                    return;

                case 1:
                    file.rotation += 90;
                    break;

                case 2:
                    file.rotation -= 90;
                    break;
            }

            if ( supportTransition ) {
                deg = 'rotate(' + file.rotation + 'deg)';
                \$wrap.css({
                    '-webkit-transform': deg,
                    '-mos-transform': deg,
                    '-o-transform': deg,
                    'transform': deg
                });
            } else {
                \$wrap.css( 'filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation='+ (~~((file.rotation/90)%4 + 4)%4) +')');
                // use jquery animate to rotation
                // \$({
                //     rotation: rotation
                // }).animate({
                //     rotation: file.rotation
                // }, {
                //     easing: 'linear',
                //     step: function( now ) {
                //         now = now * Math.PI / 180;

                //         var cos = Math.cos( now ),
                //             sin = Math.sin( now );

                //         \$wrap.css( 'filter', "progid:DXImageTransform.Microsoft.Matrix(M11=" + cos + ",M12=" + (-sin) + ",M21=" + sin + ",M22=" + cos + ",SizingMethod='auto expand')");
                //     }
                // });
            }


        });

        \$li.appendTo( \$queue );

        //by dav
        uploader.upload();
    }

    // 负责view的销毁
    function removeFile( file ) {
        var \$li = \$('#'+file.id);

        delete percentages[ file.id ];
        updateTotalProgress();
        \$li.off().find('.file-panel').off().end().remove();
    }

    function updateTotalProgress() {
        var loaded = 0,
            total = 0,
            spans = \$progress.children(),
            percent;

        \$.each( percentages, function( k, v ) {
            total += v[ 0 ];
            loaded += v[ 0 ] * v[ 1 ];
        } );

        percent = total ? loaded / total : 0;

        spans.eq( 0 ).text( Math.round( percent * 100 ) + '%' );
        spans.eq( 1 ).css( 'width', Math.round( percent * 100 ) + '%' );
        updateStatus();
    }

    function updateStatus() {
        var text = '', stats;
   
        if ( state === 'ready' ) {
            text = '选中' + fileCount + '个文件，共' +
                    WebUploader.formatSize( fileSize ) + '。';
        } else if ( state === 'confirm' ) {
            stats = uploader.getStats();
            if ( stats.uploadFailNum ) {
                text = '已成功上传' + stats.successNum+ '个文件，'+
                    stats.uploadFailNum + '个上传失败，<a class="retry" href="#">重新上传</a>失败文件或<a class="ignore" href="#">忽略</a>'
            }

        } else {
            stats = uploader.getStats();
            //text = '上传成功' + WebUploader.formatSize( fileSize );
            text = '';

            if ( stats.uploadFailNum ) {
                text += '，失败' + stats.uploadFailNum + '个';
            }
            if (fileCount==stats.successNum && stats.successNum!=0) {
                $('#$id_name .webuploader-element-invisible').remove();
            }
        }

        \$info.html( text );
    }

    uploader.onUploadAccept=function(object ,ret){
        if(ret.error_info){
            fileError=ret.error_info;
            return false;
        }
    }

    uploader.onUploadSuccess=function(file ,response){
       var img = $('<img src="'+response.name+'">');
        $('.imgWrap').html( img );
    
        \$('#'+file.id +' .bjy-filename').val(response.name);
    }
    uploader.onUploadError=function(file){
        alert(fileError);
    }

    function setState( val ) {
        var file, stats;
        if ( val === state ) {
            return;
        }

        \$upload.removeClass( 'state-' + state );
        \$upload.addClass( 'state-' + val );
        state = val;

        switch ( state ) {
            case 'pedding':
                \$placeHolder.removeClass( 'element-invisible' );
                \$queue.parent().removeClass('filled');
                \$queue.hide();
                \$statusBar.addClass( 'element-invisible' );
                uploader.refresh();
                break;

            case 'ready':
                \$placeHolder.addClass( 'element-invisible' );
                \$( "#$id_name .filePicker2" ).removeClass( 'element-invisible');
                \$queue.parent().addClass('filled');
                \$queue.show();
                \$statusBar.removeClass('element-invisible');
                uploader.refresh();
                break;

            case 'uploading':
                \$( "#$id_name .filePicker2" ).addClass( 'element-invisible' );
                \$progress.show();
                \$upload.text( '暂停上传' );
                break;

            case 'paused':
                \$progress.show();
                \$upload.text( '继续上传' );
                break;

            case 'confirm':
                \$progress.hide();
                \$upload.text( '开始上传' ).addClass( 'disabled' );

                stats = uploader.getStats();
                if ( stats.successNum && !stats.uploadFailNum ) {
                    setState( 'finish' );
                    return;
                }
                break;
            case 'finish':
                stats = uploader.getStats();
                if ( stats.successNum ) {

                } else {
                    // 没有成功的图片，重设
                    state = 'done';
                    location.reload();
                }
                break;
        }
        updateStatus();
    }

    uploader.onUploadProgress = function( file, percentage ) {
        var \$li = \$('#'+file.id),
            \$percent = \$li.find('.progress span');

        \$percent.css( 'width', percentage * 100 + '%' );
        percentages[ file.id ][ 1 ] = percentage;
     
        updateTotalProgress();
    };

    uploader.onFileQueued = function( file ) {
        fileCount++;
        fileSize += file.size;
        file.type='image/jpeg';
        file.ext='jpg';
        if ( fileCount === 1 ) {
            \$placeHolder.addClass( 'element-invisible' );
            \$statusBar.show();
        }

        addFile( file );
        setState( 'ready' );
        updateTotalProgress();
    };

    uploader.onFileDequeued = function( file ) {
        fileCount--;
        fileSize -= file.size;

        if ( !fileCount ) {
            setState( 'pedding' );
        }

        removeFile( file );
        updateTotalProgress();

    };

    uploader.on( 'all', function( type ) {
        var stats;
        switch( type ) {
            case 'uploadFinished':
                setState( 'confirm' );
                break;

            case 'startUpload':
                setState( 'uploading' );
                break;

            case 'stopUpload':
                setState( 'paused' );
                break;

        }
    });

    uploader.onError = function( code ) {
        alert( 'Eroor: ' + code );
    };

    \$upload.on('click', function() {
        if ( \$(this).hasClass( 'disabled' ) ) {
            return false;
        }
  
        if ( state === 'ready' ) {
            uploader.upload();
        } else if ( state === 'paused' ) {
            uploader.upload();
        } else if ( state === 'uploading' ) {
            uploader.stop();
        }
    });

    \$info.on( 'click', '.retry', function() {
        uploader.retry();
    } );

    \$info.on( 'click', '.ignore', function() {
        alert( 'todo' );
    } );

    \$upload.addClass( 'state-' + state );
    updateTotalProgress();
});

</script>
<script>


    var  auto_upload =$('.auto_upload').html();


    $('.auto_upload').on('click','.success',function () {
       
        $('.auto_upload').html('');
        $('.auto_upload').html(auto_upload);
    })



</script>
php;
        return $str;
    }
}

