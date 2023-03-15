<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--{include file="inc/head.tpl"}-->
<link href="/css/css.css" rel="stylesheet" type="text/css" />
<link href="/css/style.css?v=<!--{$static_version}-->" rel="stylesheet" type="text/css" />
<link href="/images/asyncbox/skins/ext/asyncbox.css" rel="stylesheet" type="text/css" />
<script language="JavaScript" type="text/javascript" src="/js/jquery-1.4.2.min.js" charset="utf-8"></script>
<script type="text/javascript" src="/images/asyncbox/asyncbox.js"></script>
<script type="text/javascript" src="/js/f.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript">
$(document).ready(function(){$("#province").change(function(){$("#province option").each(function(i,o){if($(this).attr("selected")){$(".city").hide();$(".city").eq(i).show();}});});$("#province").change();});</script>
<link href="/css/region.css" rel="stylesheet" type="text/css" />
<link href="/images/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<!--{include file="inc/newhead.tpl"}-->
<div class="body">
  <div class="reg">
    <div class="reg_a" style="height:35px;background-image:none;">
    </div>
    <div class="reg_b" style="background-image:none;">
      <div class="leftinfo">
      	<h1>欢迎注册北京租房网账户</h1>
      	<dl class="reg_icon1">
        	<dt>获得业主的房产委托</dt>
        	<dd>北京租房网的每一位房产经纪人均有机会直接获得业主房产委托。</dd>
        </dl>
        <dl class="reg_icon2">
        	<dt>查看最新的房源信息及发布房产 </dt>
            <dd>在北京租房网可以免费获得全市最及时的房源信息，成功注册会员后，可以免费发布您的房产信息。</dd>
        </dl>
        <dl class="reg_icon3">
        	<dt>获得问题的解答或指导 </dt>
            <dd>成功注册会员后，可以免费提问与房产相关的任何问题，并获得专业的解答与指导。</dd>
        </dl>
      </div>
      <div class="reg_b2" style="float:left;display:inline;background-image:none;"></div>
      <div class="rightreg">
      <div class="reg_b31"><font class="t20 b">房产经纪人注册</font><div class="loginlink">已有帐户 <a onclick="showlogin();" href="javascript:;">登录</a></div></div>
      <form name="registerform" id="registerform" method="post" action="reg.php">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="user_type" value="1">
        <div class="reg_b1" style="margin-left:0;">
          <div class="reg_b12">
            <div class="reg_b121"><font class="t14">手机号码：</font></div>
            <div class="reg_b122">
              <input type="text" value="<!--{$smarty.post.username}-->" class="input_region" name="username" id="username" max="11" onblur="checkusername(this.value);">
            </div>
            <div class="reg_b123" id="errMsg_username"></div>
          </div>
          <div class="h5"></div>
          <div class="reg_b12">
            <div class="reg_b121"><font class="t14">您的密码：</font></div>
            <div class="reg_b122">
              <input type="password" value="" class="input_region" name="passwd" id="passwd" max="20" min="6" onblur="checkpwd(this.value);">
            </div>
            <div class="reg_b123" id="errMsg_passwd"></div>
          </div>
          <div style="height:4px; clear:both; overflow: hidden"></div>
          <div class="reg_b12">
            <div class="reg_b121"><font class="t14">重复密码：</font></div>
            <div class="reg_b122">
              <input type="password" value="" class="input_region" name="passwd2" id="passwd2" onblur="checkpwd2($('#passwd').val(),this.value);">
            </div>
            <div class="reg_b123" id="errMsg_passwd2"></div>
          </div>
          <div class="h5"></div>
          <div class="reg_b12">
            <div class="reg_b121"><font class="t14">电子邮箱：</font></div>
            <div class="reg_b122">
              <input type="text" value="<!--{$smarty.post.email}-->" class="input_region" name="email" id="email" onblur="checkemail(this.value);">
            </div>
            <div class="reg_b123" id="errMsg_email"></div>
          </div>
          <div style="height:4px; clear:both; overflow:hidden;"></div>
          <div class="reg_b12">
            <div class="reg_b121"><font class="t14">真实姓名：</font></div>
            <div class="reg_b122">
              <input type="text" value="<!--{$smarty.post.realname}-->" class="input_region" name="realname" id="realname" maxlength="16" onblur="checkrealname(this.value);">
            </div>
            <div class="reg_b123" id="errMsg_realname"></div>
          </div>
          <div style=" height:4px; clear:both; overflow:hidden;"></div>
          <div class="reg_b12">
            <div class="reg_b121"><font class="t14">服务区域：</font></div>
             <div class="reg_b124" style="width:auto;margin-top:11px;">
             <label>
              <select  name="cityarea_id" id="cityarea_id" value="" onblur="checkcity(this.value);" onchange="getarea(this.value);">
						  <option value="">请选择</option>
						  <!--{html_options options=$cityarea_option selected=$dataInfo.cityarea_id}-->
						</select></label>
             <label>&nbsp;
              <select  name="cityarea_id" id="smallzone" value="" onblur="checkcity(this.value);">
						  <option value="">请选择</option>
						</select></label>
              </div>
              
              <div class="reg_b123" id="errMsg_cityarea_id"></div><span class="reg_b123" id="errMsg_citypart_id" style="display:none; width:0px; height:0px;"></span>

              
            </div>
            <div style="height:7px; clear:both; overflow:hidden;"></div>
            <div class="reg_b12">
              <div class="reg_b121"><font class="t14">所属公司：</font></div>
     
            <div class="reg_b122">
              <input id="outlet_first"  name="outlet_first" type="text" value="" class="input_region" onblur="checkcompany(this.value);">
            </div>
            <div class="reg_b123" id="errMsg_outlet_first"></div>
          </div>
          <div style="height:1px; clear:both; overflow:hidden;"></div>
          <div class="reg_b12">
            <div class="reg_b121"><font class="t14">所属门店：</font></div>
            <div class="reg_b122">
              <input id="outlet_last" type="text" value="" class="input_region" name="outlet_last" onblur="checkoutlet(this.value);">
            </div>
            <div class="reg_b123" id="errMsg_outlet_last"></div>
          </div>
          <div style="height:3px; clear:both; overflow:hidden;"></div>
          <div class="reg_b12">
            <div class="reg_b121"><font class="t14">注册校验：</font></div>
            <div class="reg_b126">
              <input name="vaild" type="text" class="input_region_yzm" id="vaild"  onblur="checkvaild(this.value);"/>
            </div>
            <div class="reg_b127"><img onclick="this.src='<!--{$cfg.url}-->valid.php?' + Math.random();" id="valid_pic" src="<!--{$cfg.url}-->valid.php"></div>
            <div class="reg_b127" style="float:left; width:61px;"><span><a href="javascript:void(0)" onclick="document.getElementById('valid_pic').src='<!--{$cfg.url}-->valid.php?' + Math.random();"><font>换一换</font></a></span></div>
            <div style="float:left; width:110px;" class="reg_b123" id="errMsg_vaild"></div>
          </div>
          <div class="reg_b12">
            <div class="reg_b128"><img src="<!--{$cfg.url}-->images/reg_button.jpg" width="115" height="37" style="cursor:pointer;" onclick="checkregform();" /></div>
            <div class="reg_b129">
            </div>
          </div>
          <div style="height:20px; clear:both; overflow:hidden;"></div>
        </div>
      </form>
      </div>
      <div class="clear"></div>
    </div>
    <div class="reg_c" style="margin-bottom: 15px;background-image:none;"></div>
  </div>
</div>
<!--{include file="inc/newfoot.tpl"}-->
</body>
</html>
