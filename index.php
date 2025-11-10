<?php
header("Content-type:text/html; Charset=utf-8");
session_start();

ob_start("ob_gzhandler");
//包含必要文件
require './config/config.php';
require './includes/AESUtil.php';
require './includes/mypdo.php';
require './includes/functions.php';
require './config/system.class.php';
require './includes/adminAction.php';
$class = new ReflectionClass("adminAction");

if(!isset($_GET['act']) || !isset($_GET['mod'])){
    header("Location:?act=index&mod=admin");
}else{
    $mod = $_GET['mod'];
    $act = $_GET['act'];
    
    if($mod == 'admin'){
        $instance = $class->newInstanceArgs();
        $instance->init();
        if(method_exists($instance, $act)){
            $method = $class->getMethod($act);
            $method->invoke($instance);
        }else{
            $instance->error();
        }
    }else if ($mod == 'account'){
        require './includes/accountAction.php';
        $class1 = new ReflectionClass("accountAction");  
        $instance1 = $class1->newInstanceArgs();
        $instance1->init();
        if(method_exists($instance1, $act)){
            $method = $class1->getMethod($act);
            $method->invoke($instance1);
        }else{
            $instance = $class->newInstanceArgs();
            $instance->error();
        }
    }else{
        $instance = $class->newInstanceArgs();
        $instance->error();
    }
}

ob_end_flush();

