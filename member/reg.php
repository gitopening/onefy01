<?php
session_start();
$currentColumn = 'login_reg';
require(dirname(dirname(__FILE__)) . '/common.inc.php');
JumpToCurrentWebsite();

//清除通用广告
unset($website_all_top_ad);
unset($website_footer_ad);
unset($website_footer_ad_2);

$page->title = '会员登陆 - ' . $page->title;

$page->tpl->assign('back_to', $_GET['back_to']);
$detailrightad = GetAdList(119, $query);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $page->title;?></title>
    <meta name="description" content="<?php echo $page->description;?>" />
    <meta name="keywords" content="<?php echo $page->keywords;?>" />
    <link type="text/css" rel="stylesheet" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
    <link type="text/css" rel="stylesheet" href="/css/reg.css?v=<?php echo $webConfig['static_version'];?>" />
    <script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/jquery.cookie.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/validform_v5.3.2.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/verify.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/login.js?=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="//g.alicdn.com/sd/nvc/1.1.112/guide.js?v=<?php echo strtotime(MyDate('Y-m-d H:00:00', time()));?>"></script>
    <style type="text/css">
        .layui-layer-btn {
            text-align: center;
        }
    </style>
</head>
<body>
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div class="clear"></div>
<script type="text/javascript">
    var jumpUrl = '';
</script>
<div class="reg">
    <div style="height:57px;background-image:none;" class="reg_a">
    </div>
    <div style="background-image:none; margin-left:auto; margin-right:auto;">
        <div id="left-ad-box" style="padding-top: 0;margin-right: 0;text-align: center;">
            <div class="register-title">“第一时间房源网”正式更名为“第一房源”，并更新域名为：<a href="https://<?php echo $url_name;?>.01fangyuan.com/member/">01fangyuan.com</a></div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<div class="clear"></div>
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
</body>
</html>
