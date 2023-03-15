<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><!--{$cfg.page.title}--></title>
<meta name="description" content="<!--{$cfg.page.description}-->" />
<meta name="keywords" content="<!--{$cfg.page.keywords}-->" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<!--{$static_version}-->" />
</head>

<body id="body">
<!--{include file="inc/newhead.tpl"}-->
<div id="main">
	<div id="about_left">
    	<!--{include file="inc/about_left.tpl"}-->
    </div>
    <div id="about_right">
    	<div id="cas">
        	<h1>投诉与建议</h1>
            <div id="complaint">
            	<form action="suggest.php" method="post" id="suggest">
                <input type="hidden" value="save" name="action" />
                	<p><label>
                    	<strong >主题：</strong>
                        <input class="theme" type="text" name="title" id="title" maxlength="40" size="40" />
                  </label></p>
                    <p><label>
                    	<strong>内容：</strong>
                        <textarea rows="7" name="content" cols="65"></textarea>
                    </label></p>
                  <p>
                    <label>
                   	  <strong>姓名：</strong><input type="text"  name="s_name" id="s_name"/></label></p>
                    <p><label>
                    	<strong>电话：</strong>
                        <input type="text"  name="tel" id="tel"/>
                    </label></p>
                    <p><label>
                    	<strong>验证码：</strong>
                        <input maxlength="4" size="8" name="getcode"/>
                        <img src="/valid.php" id="valid_pic" onclick="this.src='/valid.php?' + Math.random();">
                  </label></p>
                    <p><label>
                    	<strong>&nbsp;</strong>
                        <input name="submit" type="submit" id="submit" value="提交" />
                    </label></p>
                </form>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>
<!--{include file="inc/newfoot.tpl"}--></body>
</html>
