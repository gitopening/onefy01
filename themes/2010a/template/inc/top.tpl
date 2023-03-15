<!-- 新增部分开始 -->

<div class="topbar_bg">
  <div class="topbar">
    <!--{if $username}-->
    <span class="s_l">您好，
    <!--{$username}-->
    ！</span><a class="s_l" href="<!--{$cfg.url}-->member" target="_blank">[我的办公室]</a><a class="s_l" href="<!--{$cfg.url}-->login/login.php?action=logout" target="_blank">[退出登录]</a>
    <!--{else}-->
    <span class="s_l">您好，欢迎来到
    <!--{$cfg.page.titlec}-->
    ！</span><a class="s_l" href="<!--{$cfg.url}-->login/login.php" target="_blank" title="登录">[登录]</a><a class="s_l" href="<!--{$cfg.url}-->login/login.php" target="_blank" title="经纪人注册">[经纪人注册]</a>
    <!--{/if}-->
  </div>
</div>
<div class="clear"></div>
<div class="wrapper">
  <div class="logo_grid"> <a class="logo" href="<!--{$cfg.url}-->" title="<!--{$cfg.page.titlec}-->"></a>
    <div class="search1">
      <div class="select_search1" id="select_menu">住宅楼</div>
      <form name="topSearchForm" id="topsearch" method="GET" action="<!--{$cfg.url}-->rent/index.php">
        <input class="txt_search1" name="q" type="text" value="" />
        <input type="hidden" name="type" id="searchtype" value="1" />
        <input class="btn_search1" type="submit" value="" />
      </form>
      <div class="select_cons" style="display:none;" id="select_cons"> <a href="javascript:void();" onclick="return changeSearch(2);">住宅楼</a>
        <div class="clear"></div>
        <a href="javascript:void();" onclick="return changeSearch(3);">写字楼</a>
        <div class="clear"></div>
        <a href="javascript:void();" onclick="return changeSearch(4);">找商铺</a>
        <div class="clear"></div>
        <a href="javascript:void();" onclick="return changeSearch(5);">合租</a>
        <div class="clear"></div>
        <a href="javascript:void();" onclick="return changeSearch(6);">日租</a>
        <div class="clear"></div>
        <a href="javascript:void();" onclick="return changeSearch(7);">经纪人</a> </div>
    </div>
    <div class="hot_label"> </div>
    <div class="clear"></div>
  </div>
  <div class="clear"></div>
  <div class="header">
    <div class="nav_bg">
      <div class="nav">
				<ul>
    	        	<li class="home">
						<a href="<!--{$cfg.url}-->" title="首 页" <!--{if $menu=='index'}-->class="selected"<!--{/if}--> rel="nofollow"><span>首 页</span></a>
                	</li>
		        	<li class="rental">
						<a <!--{if $type=='1'}-->class="selected"<!--{/if}--> href="<!--{$cfg.url_rent}-->type_1.html" title="住宅"><span>住宅</span></a>
                	</li>
		        	<li class="rental">
						<a <!--{if $type=='5'}-->class="selected"<!--{/if}--> href="<!--{$cfg.url_rent}-->type_5.html" title="写字楼"><span>写字楼</span></a>
                	</li>
		        	<li class="rental">
						<a <!--{if $type=='7'}-->class="selected"<!--{/if}--> href="<!--{$cfg.url_rent}-->type_7.html" title="商铺"><span>商铺</span></a>
                	</li>
		        	<li class="rental">
						<a <!--{if $type=='6'}-->class="selected"<!--{/if}--> href="<!--{$cfg.url_rent}-->type_6.html" title="合租"><span>合租</span></a>
                	</li>
		        	<li class="rental">
						<a <!--{if $type=='8'}-->class="selected"<!--{/if}--> href="<!--{$cfg.url_rent}-->type_8.html" title="日租"><span>日租</span></a>
                	</li>
					
		        	<li class="broker">
						<a <!--{if $menu=='broker'}-->class="selected"<!--{/if}--> href="<!--{$cfg.url_broker}-->" title="经纪人"><span>经纪人</span></a>
					</li>
				    
		        </ul>
    		</div>
      <h3 class="r_rel">
        <!--{$cfg.page.rexian}-->
      </h3>
      <span class="anjuke_tabbar_side"></span> </div>
    <div class="border_bg"></div>
  </div>
  <script type="text/javascript">
function CityTips(menu,tips){
	var menu = document.getElementById(menu);
	var con = document.getElementById(tips);
	
	function _showTips(){
		con.style.display = "block";
		}
	function _hideTips(){
		con.style.display = "none";
		}
	
	menu.onclick = _showTips;
	con.onmouseover = _showTips;
	con.onmouseout = _hideTips;
}
function changeSearch(switchNavId){
		if(switchNavId == 2){
			$('#select_menu').html('住宅楼');
			document.getElementById('topsearch').action='<!--{$cfg.url}-->rent/index.php';
			document.getElementById('searchtype').value='1';
		}else if(switchNavId == 3){
			$('#select_menu').html('写字楼');
			document.getElementById('topsearch').action = '<!--{$cfg.url}-->rent/index.php';
			document.getElementById('searchtype').value='5';
		}else if(switchNavId == 4){
			$('#select_menu').html('找商铺');
			document.getElementById('topsearch').action = '<!--{$cfg.url}-->rent/index.php';
			document.getElementById('searchtype').value='7';
		}else if(switchNavId == 5){
			$('#select_menu').html('找合租');
			document.getElementById('topsearch').action = '<!--{$cfg.url}-->rent/index.php';
			document.getElementById('searchtype').value='6';
		}else if(switchNavId == 6){
			$('#select_menu').html('找日租');
			document.getElementById('topsearch').action = '<!--{$cfg.url}-->rent/index.php';
			document.getElementById('searchtype').value='8';
		}else if(switchNavId == 7){
			$('#select_menu').html('经纪人');
			document.getElementById('topsearch').action = '<!--{$cfg.url}-->broker/index.php';
			document.getElementById('searchtype').value='';
		}
		$('#select_cons').hide();
		return false;		 
	}
	function switchNav(){
		try{
		if(!switchNavId){
			switchNavId = 0;
		}
		}
		catch(e){switchNavId = 0;}
		$("#top_nav_"+switchNavId).addClass('this');
	}
//	CityTips("city_tips_menu","city_tips");
	CityTips("select_menu","select_cons");
	try{changeSearch(switchNavId);}catch(e){switchNavId = 0;changeSearch(switchNavId);}
	switchNav();
	</script>
  <div class="clear"></div>
</div>
<!-- 新增部分结束 -->
