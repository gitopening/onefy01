<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><!--{$about.title}--> - <!--{$cfg.page.title}--></title>
<meta name="description" content="<!--{$cfg.page.description}-->" />
<meta name="keywords" content="<!--{$cfg.page.keyword}-->" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<!--{$static_version}-->" />
</head>

<body id="body">
<!--{include file="inc/newhead.tpl"}-->
<div id="main">
	<div id="about_left">
    	<!--{include file="inc/about_left.tpl"}-->
    </div>
    <div id="about_right">
    	<div class="position">您现在的位置是：<a href="/"><!--{$cfg.page.titlec}--></a><span>-</span>帮助中心<span>-</span><!--{$about.title}--></div>
        <div class="title"><!--{$about.title}--></div>
        <div id="body2">
            <div><!--{$about.content}--></div>
        </div>
    </div>
    <div class="clear"></div>
</div>
<!--{include file="inc/newfoot.tpl"}-->
</body>
</html>
