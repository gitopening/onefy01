<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><!--{$cfg.page.title}--></title>
<meta name="description" content="<!--{$cfg.page.description}-->" />
<meta name="keywords" content="<!--{$cfg.page.keywords}-->" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<!--{$static_version}-->" />
<link rel="stylesheet" type="text/css" href="/css/region.css" />
<script type="text/javascript" src="/js/jquery-1.4.2.min.js?v=<!--{$static_version}-->"></script>
<script type="text/javascript" src="/js/function.js?v=<!--{$static_version}-->"></script>
<script type="text/javascript" src="/js/reg.js"></script>
</head>

<body id="body">
<!--{include file="inc/newhead.tpl"}-->
<div id="main">
   <div class="broker_left">
        <div id="login">
            <form action="<!--{$cfg.url}-->login/login.php" method="post" name="loginform" id="loginform" onsubmit="return checklogin();">
            <input type="hidden" value="login" name="action">
            <input type="hidden" value="<!--{$back_to}-->" name="back_to">
        	<div class="coltitle">登录</div>
            <dl>
            	<dt>手机号码：</dt>
                <dd><label><input type="text" name="username" class="input_region" id="loginusername" /></label></dd>
            	<dt>密　码：</dt>
                <dd style="margin-bottom:0;"><label><input type="password" name="passwd" class="input_region" id="loginpwd" /></label><!--<br />由于系统升级，老会员密码已初始化，为身份证号码后6位，登陆后请立即修改密码。--></dd>
            	<dd><label><input name="notForget" value="1" type="checkbox" id="remberme" checked="checked" /></label> &nbsp;下次自动登录(公共场所慎用)
 <!--<span><a href="#">忘记密码？</a></span>--></dd>
                <dd class="loginbtn"><label><input type="image" src="/images/registerb.jpg" /></label>  
                <span class="linkgetpwd"><a href="forgetPsw.php">找回密码</a></span></dd>
            </dl>
      		</form>
        </div>
   		<div id="reg">
        	<form onsubmit="return checkreg();" action="<!--{$cfg.url}-->login/reg.php" method="post" name="registerform" id="registerform">
            <input type="hidden" value="register" name="action">
            <input type="hidden" value="<!--{$back_to}-->" name="back_to">
            <input type="hidden" value="1" name="user_type " />
            <div class="coltitle">注册</div>
            <dl>
            	<dt>手机号码：</dt>
                <dd>
                	<label><input name="username" type="text" class="input_region" id="username" value="<!--{$smarty.post.username}-->" onblur="checkusername(this.value);" ></label>
                	<div id="errMsg_username" class="validmsg"></div>
                    <div class="clear"></div>
                </dd>
            	<dt>密　码：</dt>
                <dd>
                	<label><input type="password" value="" class="input_region" name="passwd" id="passwd" onblur="checkpwd(this.value);" ></label>
                	<div id="errMsg_passwd" class="validmsg"></div>
                    <div class="clear"></div>
                </dd>
            	<dt>确认密码：</dt>
                <dd>
                	<label><input type="password" value="" class="input_region" name="passwd2" id="passwd2" onblur="checkpwd2($('#passwd').val(),this.value);" ></label>
                	<div id="errMsg_passwd2" class="validmsg"></div>
                    <div class="clear"></div>
                </dd>
            	<dt>E-mail：</dt>
                <dd>
                	<label><input type="text" value="<!--{$smarty.post.email}-->" class="input_region" name="email" id="email"  onblur="checkemail(this.value);" ></label>
                	<div id="errMsg_email" class="validmsg"></div>
                    <div class="clear"></div>
                </dd>
                <dt>验证码：</dt>
                <dd>
                	<label><input name="vaild" type="text" class="input_region_yzm" size="6" maxlength="4" id="vaild"  onblur="checkvaild(this.value);" /> <img onclick="this.src='<!--{$cfg.url}-->valid.php?' + Math.random();" id="valid_pic" src="<!--{$cfg.url}-->valid.php"></label> 
                	<div id="errMsg_vaild" class="validmsg"></div>
                    <div class="clear"></div>
                </dd>
                <dd><label><input type="checkbox" name="agree" id="agree" checked="checked" /></label> 我已阅读并同意《<a href="/about/show-12.html" target="_blank">第一时间房源网用户服务协议</a>》</dd>
                <dd><img src="/images/registera.jpg" onclick="checkregform();" style="cursor:pointer;" /></dd>

            </dl>
          </form>
        </div>
   </div>
   <div id="right">
   		<div id="banner_right"><!--{$websiteright}--></div>
   </div>
   <div class="clear"></div>
</div>
<!--{include file="inc/newfoot.tpl"}-->
</body>
</html>
