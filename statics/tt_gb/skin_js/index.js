// JavaScript Document


$(document).ready(function(){

	 var hfg=$(".hf1200").height();
     if(hfg==0){
		 $(".hf1200").addClass("mt0");
		 }else{
			 $(".hf1200").removeClass("mt0");
			 }

	var yl=$(".partner_con ul li").length;
	if(yl<=36){
		$(".link_arrow").hide();
		}

	 $(".main_news .left_lb .hot").parent().css("max-width","320px");
	 $(".main_news .left_lb .hot").parent().find("span").css("max-width","300px");
	 $(".nav .sanj").parent().parent().css("overflow","visible");
	 $(".nav .sanj").parent().parent().parent().css("overflow","visible");

	$('#newsTabs').each(function(){
		var $this = $(this), timer = 0;
		$this.find('.news_tabs li').on('mouseover', function () {
			var $ts = $(this);
			clearTimeout(timer);
			timer = setTimeout(function(){
				$ts.addClass('on').siblings().removeClass('on');
				$this.find('.news_box_item').removeClass('on').eq($ts.index()).addClass('on');
			},200);
		})
		.on('mouseleave', function () {
			clearTimeout(timer);
		})
	})

 $("#xyb ul li").mouseover(function(){
       var lid = $(this).index();
	   var s=$(this).parent().parent();
	   var par_class = $(s).attr("id");
	   $("#"+par_class+" ul li").removeClass("on");
	   $(this).addClass("on");
	   $("#"+par_class+"a" +" div").hide();
	   lid++;
	   $("#"+par_class+"a" +" div:nth-child("+lid+")").show();
	   });

   $(".main_soft .hd ul li").mouseover(function(){
	      $(".main_soft .hd ul li").removeClass("on");
			$(".main_softbox").css("display","none");
			$(this).addClass("on");
			var a=$(this).attr('id') + "a";
			$("#" + a).css("display","block");
		});
	 $("#az .index_gytit .tabs li").mouseover(function(){
	      $("#az .index_gytit .tabs li").removeClass("on");
			$("#az .mbox").css("display","none");
			$(this).addClass("on");
			var z=$(this).attr('id') + "z";
			$("#" + z).css("display","block");
		});
	$("#ios .index_gytit .tabs li").mouseover(function(){
	      $("#ios .index_gytit .tabs li").removeClass("on");
			$("#ios .mbox").css("display","none");
			$(this).addClass("on");
			var i=$(this).attr('id') + "i";
			$("#" + i).css("display","block");
		});
	$("#game .index_gytit .tabs li").mouseover(function(){
	      $("#game .index_gytit .tabs li").removeClass("on");
			$("#game .mbox").css("display","none");
			$(this).addClass("on");
			var g=$(this).attr('id') + "g";
			$("#" + g).css("display","block");
		});


	$('#a1a').each(function(){
		$('.index_r_lb').on('mouseover', 'dl', function(){
			$(this).parent().parent().find(".index_r_lb dl").removeClass("on");
			$(this).addClass("on");
		});
	})


$("#fenl ul li").mouseover(function(){
       var lid = $(this).index();
	   var s=$(this).parent().parent();
	   var par_class = $(s).attr("id");
	   $("#"+par_class+" ul li").removeClass("on");
	   $(this).addClass("on");
	   $("#"+par_class+"a" +" .bd").hide();
	   lid++;
	   $("#"+par_class+"a" +" .bd:nth-child("+lid+")").show();
	   });



});
$(function () {
        $(".top_bar .qzdh").live({ mouseenter: function () {
			$(this).addClass("on");
            $(".top_bar .qzdh .bd").show();
            }, mouseleave: function () {
               $(this).removeClass("on");
			   $(".top_bar .qzdh .bd").hide();
            }
        });
		$(".header .sj").live({ mouseenter: function () {
			$(this).find(".ewm").show();

            }, mouseleave: function () {
               $(this).find(".ewm").hide();
            }
        });

    });

 //收藏页面
  function AddFavorite(title, url) {
	var title = title || document.title;
	var url = url || window.location.href;
  try {
      window.external.addFavorite(url, title);
  }
  catch (e) {
     try {
      window.sidebar.addPanel(title, url, "");
    }
     catch (e) {
         alert("抱歉，您所使用的浏览器无法完成此操作。\n\n加入收藏失败，请使用Ctrl+D进行添加");
     }
  }
}


//返回顶部
(function() {
    var $backToTopTxt = "", $backToTopEle = $('<a class="go_top">11</a>').appendTo($("body"))
        .text($backToTopTxt).attr("title", $backToTopTxt).click(function() {
            $("html, body").animate({ scrollTop: 0 }, 120);
    }), $backToTopFun = function() {
        var st = $(document).scrollTop(), winh = $(window).height();
        (st > 0)? $backToTopEle.show(): $backToTopEle.hide();
    };
	// $('<span>返回顶部</span>').appendTo($(".go_top"));
    $(window).bind("scroll", $backToTopFun);
    $(function() { $backToTopFun(); });
})();


// 首页
$(function(){
	$('.section_box').each(function(){
		var $box = $(this), timer = 0;
		$box.on('mouseenter', '.tabs li', function(){
			var $tis = $(this);
			clearTimeout(timer);
			timer = setTimeout(function(){
				$tis.addClass('on').siblings().removeClass('on');
				$box.find('.section_box_cont').removeClass('on').eq($tis.index()).addClass('on');
				$.lazyload();
			}, 200);
		})
		$box.on('mouseleave', '.tabs li', function(){
			clearTimeout(timer);
		})
	})
	// 首页小编推荐tab切换
	$('#xbtjMain').each(function(){
	  	$cont = $(this), timeid = 0;
		$cont.find('.tab_hd li').hover(function(){
			clearTimeout(timeid);
			var $this = $(this);
			timeid = setTimeout(function(){
				$this.addClass('on').siblings().removeClass('on');
				$cont.find('.tab_bd_item').removeClass('on').eq($this.index()).addClass('on');
			}, 200);
		}, function(){
			clearTimeout(timeid);
		})
		$cont.find('.btn_more').on('click', function(){
			$cont.find('.tab_bd_item').addClass('auto');
		})
	})
})
