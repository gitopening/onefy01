<?php
error_reporting(0);
session_start();
require_once(dirname(__FILE__) . '/config.cfg.php');
require_once(dirname(__FILE__) . '/common/common.func.php');
require_once(dirname(__FILE__) . '/common/lib/classes/Verify.class.php');
if (strpos($_SERVER['HTTP_REFERER'], $cfg['domain'] . '/member/login.php') === false
    && strpos($_SERVER['HTTP_REFERER'], $cfg['domain'] . '/member/reg.php') === false
    && strpos($_SERVER['HTTP_REFERER'], $cfg['domain'] . '/member/') === false
    && strpos($_SERVER['HTTP_REFERER'], $cfg['domain'] . '/house/delete') === false
    && strpos($_SERVER['HTTP_REFERER'], $cfg['domain'] . '/house/report') === false
    && strpos($_SERVER['HTTP_REFERER'], $cfg['domain'] . '/suggest.html') === false
    && strpos($_SERVER['HTTP_REFERER'], $cfg['domain'] . '/auth.php') === false
    && strpos($_SERVER['HTTP_REFERER'], $cfg['domain'] . '/member/forgetpwd.php') === false) {
    header("http/1.1 404 not found");
    header("status: 404 not found");
    require_once(dirname(__FILE__) . '/404.php');
    exit();
}
//header('Content-type: image/png');
$config = array(
    'expire' => 300,    //5分钟有效期
    'fontSize' => 16,    // 验证码字体大小
    'length' => 4,     // 验证码位数
    'imageW' => 120,
    'imageH' => 30,
    'useNoise' => false, // 关闭验证码杂点
    'useCurve' => false,    //干扰线
    'useImgBg' => false  //背景
);
$Verify = new Verify($config);
$Verify->entry();