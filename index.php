<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件



//代理IP直接退出
empty($_SERVER['HTTP_VIA']) or exit('Access Denied');
//防止快速刷新
session_start();
$seconds = '3'; //时间段[秒]
$refresh = '6'; //刷新次数
//设置监控变量
$cur_time = time();
if(isset($_SESSION['last_time'])){
    $_SESSION['refresh_times'] += 1;
}else{
    $_SESSION['refresh_times'] = 1;
    $_SESSION['last_time'] = $cur_time;
}
//处理监控结果
if($cur_time - $_SESSION['last_time'] < $seconds){
    if($_SESSION['refresh_times'] >= $refresh){
        //跳转至攻击者服务器地址

        exit('访问过于频繁！');
    }
}else{
    $_SESSION['refresh_times'] = 0;
    $_SESSION['last_time'] = $cur_time;
}








// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

// 定义应用目录
define('APP_PATH','./Application/');

// 定义缓存目录
define('RUNTIME_PATH','./Runtime/');

// 定义模板文件默认目录
define("TMPL_PATH","./tpl/");

// 定义模板文件默认目录
define("UPLOAD_PATH","./Upload/");

// 定义oss的url
define("OSS_URL","");

define('SITE_PATH', getcwd());//网站当前路径

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单


