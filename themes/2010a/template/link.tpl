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
	<div id="left">
    	<div id="content">
       	  <h1 style="text-align:center;">友情链接</h1>
       	  <div class="clear"></div>
          <div id="body2">
                	<div id="links02">
        	<!--{foreach from=$flink item=item key=key}--><a href="<!--{$item.link_url}-->" target="_blank"><!--{$item.link_text}--></a><span>|</span><!--{/foreach}-->
        </div>

        </div>

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
