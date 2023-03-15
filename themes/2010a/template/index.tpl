<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><!--{$cfg.page.title}--></title>
<meta name="description" content="<!--{$cfg.page.description}-->" />
<meta name="keywords" content="<!--{$cfg.page.keywords}-->" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<!--{$static_version}-->" />
<script type="text/javascript" src="/js/jquery-1.4.2.min.js?v=<!--{$static_version}-->"></script>
<script type="text/javascript">
function loadprice(pricType){
	var rentprice = '<option value="0-0"></option><!--{foreach from=$rent_price_option item=item key=key}--><option value="<!--{$key}-->"><!--{$item}--></option><!--{/foreach}-->';
	var saleprice = '<option value="0-0"></option><!--{foreach from=$sale_price_option item=item key=key}--><option value="<!--{$key}-->"><!--{$item}--></option><!--{/foreach}-->';
	if(pricType== 'sale'){
		$('#price').html(saleprice);
		$('#searchhouse').attr('action','/sale/');
	}else if(pricType== 'rent'){
		$('#price').html(rentprice);
		$('#searchhouse').attr('action','/rent/');
	}
}
function searchhouse(){
	var column = $('#info_type').val();
	var keywords = $('#keywords').val();
	var cityarea = $('#cityarea').val();
	var roomtype = $('#roomtype').val();
	var price = $('#price').val();
	var url = '/' + column + '/list_' + cityarea + '_0_' + price + '_' + roomtype + '_0_' + keywords + '.html';
	window.location.href = url;
	return false;
}
</script>
</head>

<body id="body">
<!--{include file="inc/newhead.tpl"}-->
<div id="main">
	<div id="left">
    	<div id="box">
        	<ul>
            	<!--{foreach from=$borderSaleHouseList item=item key=key }-->
            	<li><a href="/brokersale/d-<!--{$item.id}-->.html" target="_blank"><img src="/upfile/<!--{$item.house_thumb}-->" alt="<!--{$item.title}-->" /></a>
				<a href="/brokersale/d-<!--{$item.id}-->.html" target="_blank"><!--{$item.house_title}--></a><br />
                    <!--{$item.house_room}-->室<!--{$item.house_hall}-->厅<!--{$item.house_toilet}-->卫&nbsp;&nbsp;&nbsp;&nbsp;<!--{$item.house_totalarea}-->m&sup2;<br />
                    <span><!--{$item.house_price}--></span>万（<!--{$item.danjia}-->元/m&sup2;）<br />
                    <p><!--{$item.house_desc}--></p>
                </li>
                <!--{/foreach}-->
                <div class="clear"></div>
            </ul>
        </div>
        <!---->
        <div id="banner"><!--{$websiteleft1}--></div>
        <!--最新个人二手房-->
        <div id="block">
        	<strong><a href="/sale/">最新个人二手房</a></strong>
            <ul class="block_ul">
            	<!--{foreach from=$houseSellNew item=item key=key }-->
            	<li>
                	<div class="div01"><a href="/sale/d-<!--{$item.id}-->.html" target="_blank"><!--{$item.title}--></a></div>
                    <div class="div02"><!--{$item.cityarea_name}--></div>
                    <div class="div02"><!--{$item.house_room}-->居</div>
                    <div class="div03"><!--{$item.house_totalarea}-->m&sup2;</div>
                    <div class="div04"><!--{if $item.house_price>0}--><strong><!--{$item.house_price}--></strong>万元<!--{else}-->面议<!--{/if}--></div>
                    <div class="div05"><!--{$item.pubdate}--></div>
                    <div class="clear"></div>
                </li>
                <!--{/foreach}-->
            </ul>
        </div>
		<!--{if $websiteleft2}-->
        <div id="banner"><!--{$websiteleft2}--></div>
		<!--{/if}-->
        <div id="block">
        	<strong><a href="/rent/">最新个人出租房</a></strong>
            <ul class="block_ul">
            	<!--{foreach from=$houseRentNew item=item key=key }-->
            	<li>
                	<div class="div01"><a href="/rent/d-<!--{$item.id}-->.html" target="_blank"><!--{$item.title}--></a></div>
                    <div class="div02"><!--{$item.cityarea_name}--></div>
                    <div class="div02"><!--{$item.house_room}-->居</div>
                    <div class="div03"><!--{$item.house_totalarea}-->m&sup2;</div>
                    <div class="div04"><!--{if $item.house_price>0}--><strong><!--{$item.house_price}--></strong>元/月<!--{else}-->面议<!--{/if}--></div>
                    <div class="div05"><!--{$item.pubdate}--></div>
                    <div class="clear"></div>
                </li>
                <!--{/foreach}-->
            </ul>
        </div>
    </div>
    <div id="right">
    	<!--form-->
        <div id="form">
        	<form method="get" id="searchhouse" action="/sale/" onsubmit="return searchhouse();">
            	<div class="formtitle"><span class="f01">租/售</span><span class="f02">输入小区名或地区</span></div>
                <div class="clear"></div>
              <div class="formitem1"><span class="f01"><select name="info_type" id="info_type" onchange="loadprice(this.value);">
					<option value="sale" selected="selected"></option>
                    <option value="sale">出售</option>
                    <option value="rent">出租</option>
                </select></span><span class="f02"><input type="text" id="keywords" class="text01" name="q"/></span></div>
                <div class="clear"></div>
                <div class="formtitle"><span class="f01">城区</span><span class="f01">户型</span><span class="f01">价格</span></div>
              <div class="clear"></div>
                <div class="formitem1"><span class="f01"><select class="select01" id="cityarea" name="cityarea">
                    	<option value="0"></option>
                        <!--{foreach from=$cityarea_option item=item key=key }-->
                        <option value="<!--{$key}-->"><!--{$item}--></option>
                        <!--{/foreach}-->
                    </select></span><span class="f01"><select class="select02" name="room" id="roomtype">
                    	<option value="0"></option>
                        <!--{foreach from=$room_option item=item key=key}-->
                        <option value="<!--{$key}-->"><!--{$item}--></option>
                        <!--{/foreach}-->
                    </select></span><span class="f01"><select style="width:85px;" class="select03" name="price" id="price">
                    	<option value="0-0"></option>
                        <!--{foreach from=$sale_price_option item=item key=key}-->
                        <option value="<!--{$key}-->"><!--{$item}--></option>
                        <!--{/foreach}-->
                    </select>
                  </span></div>
                <div class="clear"></div>
                <div><span class="f01">&nbsp;</span><span class="f01">&nbsp;</span><span class="f01 textright">
                  <input type="submit" value="" />
                </span></div>
                <div class="clear"></div>
            </form>
        </div>
        <div id="banner_right"><!--{$websiteright}--></div>
        <div class="side_box">
			<div class="box_title">
				<h2><a href="/news/">第一时间房产资讯</a></h2>
				<span><a href="/news/" target="_blank">更多资讯&gt;&gt;</a></span>
			</div>
			<ul>
				<!--{foreach from=$newslist item=item key=key}-->
				<li><a href="/news/article-<!--{$item.id}-->.html" target="_blank"><!--{$item.title}--></a></li>
				<!--{/foreach}-->
			</ul>
		</div>
        <div id="Expert">
        	<div class="title">值得关注的房地产专业人士</div>
            <ul class="content">
            	<!--{foreach from=$brokerlist item=item key=key}-->
            	<li>
                	<a href="/shop/<!--{$item.id}-->" target="_blank"><img src="<!--{$item.avatar}-->" /></a>
                    <span><a href="/shop/<!--{$item.id}-->" target="_blank"><!--{$item.realname}--></a>&nbsp;&nbsp;&nbsp;<!--{$item.outlet_first}--></span>
                    <p><!--{$item.servicearea}--></p>
                    <p><!--{$item.introduce}--></p>
                    <div class="clear"></div>
                </li>
                <!--{/foreach}-->
            </ul>
        </div>
    </div>
    <div class="clear"></div>
</div>
<!--{include file="inc/newfoot.tpl"}-->
</body>
</html>
