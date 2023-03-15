<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>申请友情链接 - <!--{$cfg.page.title}--></title>
<meta name="description" content="<!--{$cfg.page.description}-->" />
<meta name="keywords" content="<!--{$cfg.page.keywords}-->" />
<link rel="stylesheet" type="text/css" href="/css/css.css" />
</head>

<body>
<!--head-->
<div id="center">
	<div id="center_left_banner"><img src="images/banner/left.jpeg" alt="" /></div>
    <div id="center_content">
    	<!--{include file="inc/newhead.tpl"}-->
    	<div id="content_banner"><!--{$websitehead}--></div>
        <div id="content">
             <ul class="sq_nc">
                <li><p class="sq_qbt">1.在您提交友情链接前请先做好本站链接</p>
                本站链接文字&ldquo;房东房源网&rdquo;，链接地址为&ldquo;www.fdfy.cn&rdquo; </li>
                <li><p class="sq_qbt">2.提交您的站点信息</p>
                    <strong>&ldquo;链接要求：您的网站在Alexa全球排名必须为20万以内，并且必须先加真房源为友链，之后按如下格式提交信息：&rdquo;</strong>
                    <div class="sq_bg">
                    <form action="applylink.php" method="post" id="applylink">
            			<input type="hidden" name="action" value="save" />
                        <dl>
                            <dt>网站名称:</dt>
                            <dd><input name="link_name" type="text" id="link_name" /><span>网站的连接名称（不超过6个字）</span></dd>
                        </dl>
                        <dl>
                            <dt>网站地址:</dt>
                            <dd><input name="link_url" type="text" id="link_url" value="http://" /><span>网站链接地址</span></dd>
                        </dl>
                        <dl>
                            <dt>Alexa排名:</dt>
                            <dd><input name="alexa" type="text" id="alexa" /><span>全球网站排名（查询Alexa排名）</span></dd>
                        </dl>
                        <div class="clear"></div>
                        <dl>
                            <dt>联系QQ:</dt>
                            <dd><input name="qq" type="text" id="qq" /><span></span></dd>
                        </dl>
                      <div class="clear"></div>
                        <dl>
                            <dt>联系邮箱:</dt>
                            <dd>
                              <input name="email" type="text" id="email" />
                            </dd>
                        </dl>
                        <div class="clear"></div>
                        <dl>
                            <dt>联系电话:</dt>
                            <dd><input name="tel" type="text" id="tel" /></dd>
                        </dl>
                        <div class="clear"></div>
                        <dl>
                            <dt>联系人:</dt>
                            <dd><input name="linkman" type="text" id="linkman" /></dd>
                        </dl>
                        <div class="clear"></div>
                        <p class="f_tj"><input type="submit" value="提交申请" class="yq_b"/></p>
                        
                    </form>
                    </div>
                    <div class="clear"></div>
                </li>
            </ul>
        </div>
    </div>
    <div id="center_right_banner"><img src="images/banner/left.jpeg" alt="" /></div>
    <div class="clear"></div>
</div>
<!--{include file="inc/newfoot.tpl"}-->
</body>
</html>
