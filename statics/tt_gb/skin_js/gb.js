// JavaScript Document
/* 我爱模板网：www.52muban.com */
$(function () {
    var tab = $('.J_tab_cont.fix')
    tab.hide()
    tab.eq(0).css('display', 'block')
    var tabtit = $('.m-related-zt .g-tab li')
    tabtit.click(function () {
        var temp = $(this).index()
        $(this).addClass('cur').siblings().removeClass('cur')
        tab.hide().eq(temp).css('display', 'block')
    })
    $('.wrap_lf_tt .xgzt').click(function () {
        var temp = $('.m-related-zt').offset().top
        $('body,html').animate({ scrollTop: temp }, 500);
    })
    $('.wrap_lf_tt .xgbb').click(function () {
		var temp=0
		if($('.week-hot-game').length){
			var temp = $('.week-hot-game').offset().top
		}
        
        $('body,html').animate({ scrollTop: temp }, 500);
    })
	// 页面图片懒加载
	    !(function(){
	        var $win = $(window),
	                height = $win.height(),
	                distance = 200,
	                timer = 0;
	
	        $.loadable = function(el) {
	            return $win.scrollTop() + height + distance > $(el).offset().top;
	        };
	        $.lazyload = function() {
	            $('.lazy').not('.loaded').each(function(i){
	                if($.loadable(this)){
	                    $(this).attr('src', $(this).attr('data-src')).addClass('loaded');
	                }
	            });
	        };
	
	        // $.lazyload();
	        $win.on('scroll', function(){
	            clearTimeout(timer);
	            timer = setTimeout(function(){
	                $.lazyload();
	            }, 50);
	        });
	    })();
		
	var nav = $('.sealinktab')
	nav.each(function(ind,ele){
		var navlist = $(ele).find('.sealinklist')
		$(ele).find('.sealinknav').click(function(){
			// $(this).addClass('on').siblings().removeClass('on')
			var temp = $(this).index()
			navlist.addClass('hide').eq(temp).removeClass('hide')
		})
	})
})

$(document).ready(function () {

    var hfg = $(".hf1200").height(),
        ggh = $(".ggwbox").height(),
        ggh2 = $(".ggwbox_new").height(),
        xzy = $(".xzsbox").height(),
        xzy2 = $(".ggbox").height(),
        art1 = $(".art_rgg").height(),
        ycgg1 = $(".rg_ggbox").height();


    if (hfg == 0) {
        $(".hf1200").addClass("mt0");
    } else {
        $(".hf1200").removeClass("mt0");
    }
    if (ggh != 0) {
        $(".ggwbox").addClass("mt15 mb15");
    } else {
        $(".ggwbox").removeClass("mt15 mb15");
    }
    if (xzy == 0) {
        $(".xzsbox").addClass("mar0");
    } else {
        $(".xzsbox").removeClass("mar0");
    }
    if (ggh2 == 0) {
        $(".ggwbox_new").addClass("mt0");
    } else {
        $(".ggwbox_new").removeClass("mt0");
    }
    if (xzy == 0) {
        $(".xzsbox").addClass("mar0");
    } else {
        $(".xzsbox").removeClass("mar0");
    }
    if (xzy2 == 0) {
        $(".ggbox").addClass("mar0");
    } else {
        $(".ggbox").removeClass("mar0");
    }
    if (art1 == 0) {
        $(".art_rgg").addClass("mar0");
    } else {
        $(".art_rgg").removeClass("mar0");
    }
    if (ycgg1 == 0) {
        $(".rg_ggbox").addClass("mar0");
    } else {
        $(".rg_ggbox").removeClass("mar0");
    }

    var hjgs = $(".lbhj .bd li").length;
    if (hjgs <= 6) {
        $(".lbhj .bd").css("height", "32px");
        $(".lbhj .bd").css("margin-top", "32px");
    }
    var yl = $(".partner_con ul li").length;
    if (yl <= 36) {
        $(".link_arrow").hide();
    }

    $(".zt_xgwz ul li").eq(1).addClass("wl");
    $(".zt_xgwz ul li").eq(3).addClass("wl");
    $(".zt_xgwz ul li").eq(5).addClass("wl");

    $(".main_news .left_lb .hot").parent().css("max-width", "320px");
    $(".main_news .left_lb .hot").parent().find("span").css("max-width", "300px");
    $(".nav .sanj").parent().parent().css("overflow", "visible");
    $(".nav .sanj").parent().parent().parent().css("overflow", "visible");

    // $(".map_box").find('dl:lt(3) dt').css("margin-top","5px");


    $(".sort_list").each(function (ind, ele) {
        $(this).find("dl").eq(1).addClass("der");
    });
    $(".sort_list dl").each(function (ind, ele) {
        var a = $(this).find("dd a").length;
        if (a <= 2) {
            $(this).find("dd a").css("margin-top", "18px");
        }
    });

    $("#blph ul li").mouseover(function () {
        var lid = $(this).index();
        var s = $(this).parent().parent();
        var par_class = $(s).attr("id");
        $("#" + par_class + " ul li").removeClass("on");
        $(this).addClass("on");
        $("#" + par_class + "a" + " ul").hide();
        lid++;
        $("#" + par_class + "a" + " ul:nth-child(" + lid + ")").show();
    });

    $("#xyb ul li").mouseover(function () {
        var lid = $(this).index();
        var s = $(this).parent().parent();
        var par_class = $(s).attr("id");
        $("#" + par_class + " ul li").removeClass("on");
        $(this).addClass("on");
        $("#" + par_class + "a" + " div").hide();
        lid++;
        $("#" + par_class + "a" + " div:nth-child(" + lid + ")").show();
    });

    $(".main_soft .hd ul li").mouseover(function () {
        $(".main_soft .hd ul li").removeClass("on");
        $(".main_softbox").css("display", "none");
        $(this).addClass("on");
        var a = $(this).attr('id') + "a";
        $("#" + a).css("display", "block");
    });
    $("#az .index_gytit .tabs li").mouseover(function () {
        $("#az .index_gytit .tabs li").removeClass("on");
        $("#az .mbox").css("display", "none");
        $(this).addClass("on");
        var z = $(this).attr('id') + "z";
        $("#" + z).css("display", "block");
    });
    $("#ios .index_gytit .tabs li").mouseover(function () {
        $("#ios .index_gytit .tabs li").removeClass("on");
        $("#ios .mbox").css("display", "none");
        $(this).addClass("on");
        var i = $(this).attr('id') + "i";
        $("#" + i).css("display", "block");
    });
    $("#game .index_gytit .tabs li").mouseover(function () {
        $("#game .index_gytit .tabs li").removeClass("on");
        $("#game .mbox").css("display", "none");
        $(this).addClass("on");
        var g = $(this).attr('id') + "g";
        $("#" + g).css("display", "block");
    });

    $(".index_r_lb dl").mouseover(function () {
        // $(this).parent().parent().find(".index_r_lb dl").removeClass("on");
        $(this).addClass("on").siblings().removeClass('on');
    });

    $('.bt_abtn').on('click', 'a', function (e) {
        var setht = $('.xzdzbox').offset().top;
        $('body,html').animate({ scrollTop: setht }, 500);
        return false;
    });

    //文章内页
    $(".art_showl .game").each(function (ind, ele) {
        $(this).find(".hd li").mouseover(function () {
            var lid = $(this).index();
            var s = $(this).parent().parent();
            $(this).parent().find("li").removeClass("on");
            $(this).addClass("on");
            $(this).parent().parent().parent().find(".qhnr .bd").hide();
            var nr = $(this).parent().parent().parent().find(".qhnr .bd");

            lid++;
            $(this).parent().parent().parent().find(".qhnr .bd").eq(lid - 1).show();
        });
    });

    $("#fenl ul li").mouseover(function () {
        var lid = $(this).index();
        var s = $(this).parent().parent();
        var par_class = $(s).attr("id");
        $("#" + par_class + " ul li").removeClass("on");
        $(this).addClass("on");
        $("#" + par_class + "a" + " .bd").hide();
        lid++;
        $("#" + par_class + "a" + " .bd:nth-child(" + lid + ")").show();
    });

    if(($('.xzbox').length && $('#soft_title').length) || $('.art_listl').length){
    	gensui()
    }
    if(($('.xzbox').length && $('#soft_title').length) || $('.art_listl').length){
    	gensui()
    }
	if(($('.art_showl').length && $('.wrap_rg').length && $('.art_list').length) || ($('.xz_list').length && $('.wrap_rg').length)){
		yougensui()
	}
});
function yougensui(){
	var $win = $(window),
        $els = $('.wrap_rg'),
		settop=$els.offset().top;
  var $lf = $('.art_list')
  if(!$lf.length){
	  $lf = $('.wrap_lf')
  }
    $win.scroll(function(){
        var scrollTop = $win.scrollTop();
        
        if($els.height()<$lf.height()){
        	$els.each(function(){
                if(scrollTop > settop+$els.height()){
                    $('#gameYouLike').addClass('fixed');
                }else{
                    $('#gameYouLike').removeClass('fixed');
                }
            })
        }
    })
}
function gensui(){
	var $win = $(window),
        $els = $('.wrap_rg'),
		settop=$els.offset().top,
		wdht=$(window).height();
  var $lf = $('.wrap_lf')
	if(!$lf.length && $('.art_list').length){
		$lf = $('.art_list')
	}
    $win.scroll(function(){
        var scrollTop = $win.scrollTop();
        
        if($els.height()<$lf.height()){
        	$els.each(function(){
                var $this = $(this);
                if(scrollTop > settop+$this.height()-wdht){
                    $this.addClass('pos-fixed');
                }else{
                    $this.removeClass('pos-fixed');
                }

                var v = scrollTop - (document.body.scrollHeight-170-wdht);
                // console.log(v);

                if(v > 0){
                    $this.css({bottom: v+'px'});
                }else{
                    $this.removeAttr('style');
                }
            })
        }
    })
}

//左侧跟随

function zgens() {
    if (navigator.appName == "Microsoft Internet Explorer")
        if (navigator.appVersion.match(/7./i) == '7.')
            return false;
    var ht = $('.weizhi').height() + $('.head').height() + $('.ggwbox').height() + $('.phb_rmtj').height() + $('.zt_gytit').height(),
        hts = $('.weizhi').height() + $('.head').height() + $('.gxlist').height() + $('.phb_rmtj').height() + $('.zt_gytit').height(),
        htss = $('.weizhi').height() + $('.head').height() + $('.ggwbox').height() + $('.w1k_box').height() - 500;
    var zh = document.documentElement.clientHeight;
    if ($(window).scrollTop() < ht) {
        $('.left_menu,.phb_menu').removeClass('guding');
        $('.left_menu,.phb_menu').removeClass('guding2');
    }
    else if ($(window).scrollTop() < htss) {
        $('.left_menu,.phb_menu').addClass('guding');
        $('.left_menu,.phb_menu').removeClass('guding2');
    }
    else {
        $('.left_menu,.phb_menu').removeClass('guding');
        $('.left_menu,.phb_menu').addClass('guding2');
    }
}
//右侧跟随
function ygens() {
    var ygens = document.getElementById("gdgs_r");
    if (ygens != null) {
        var gdkh = $('#gdgs_r').height();
        var lh = $('.art_listl').height(),
            rh = $('.wrap_rg').height();
        if (lh > rh) {
            var rht = $('.head').height() + $('.weizhi').height() + $('.ggwbox').height() + $('.art_rgg').height() + $('.fldhbox').height() + $('.rmrjbox').height() + $('.rg_ggbox').height() + $('.blbox').height() + $('.rg_ggbox').height() + 120,
                rhtss = $('.head').height() + $('#main1k').height();
            if ($(window).scrollTop() < rht) {

                $('#gdgs_r').removeClass('gdgs');
                $('#gdgs_r').removeClass('gdgs2');
            }
            else if ($(window).scrollTop() < rhtss) {
                $('#gdgs_r').addClass('gdgs');
                $('#gdgs_r').removeClass('gdgs2');
            }
            else if ($(window).scrollTop() >= rhtss) {
                alert(0);
                $('#gdgs_r').removeClass('gdgs');
                $('#gdgs_r').addClass('gdgs2');
            }
        }
    }

}



$(function () {
    $(".top_bar .qzdh").live({
        mouseenter: function () {
            $(this).addClass("on");
            $(".top_bar .qzdh .bd").show();
        }, mouseleave: function () {
            $(this).removeClass("on");
            $(".top_bar .qzdh .bd").hide();
        }
    });

    $(".soft_list .hd .banb a").live({
        mouseenter: function () {
            $(this).find(".ewm").show();
            $(this).parent().parent().parent().css("z-index", "10");
        }, mouseleave: function () {
            $(this).find(".ewm").hide();
            $(this).parent().parent().parent().css("z-index", "0");
        }
    });

    $(".header .sj").live({
        mouseenter: function () {
            $(this).find(".ewm").show();

        }, mouseleave: function () {
            $(this).find(".ewm").hide();
        }
    });
});



function foot(id) {
    var sy = document.getElementById("main1_l");
    if (sy != null) return false;
    var zch = document.body.clientHeight;
    if (typeof ($('#' + id)) == 'object' && typeof (id) != 'undefined') {
        var pmh = $('#' + id).innerHeight();
    } else {
        var pmh = document.documentElement.clientHeight;
    }
    if (pmh > zch) {
        $('.footer').addClass('fixed');
    } else {
        $('.footer').removeClass('fixed');
    }

}


$(document).ready(function () {
    $($(".phb_menu dl dt").parent().children('dd')).css("display", "none");
    $(".phb_menu dl.down dd").css("display", "block");
    $(".phb_menu dt").click(function () {
        var ty = $(this).attr("data");
        // zjn 16.5.12 内容显示判断
        $(".conlist").hide();
        $("#cont_show_" + ty).show();
        //后面2个不展示
        //if(ty==4 || ty==5) return false;
        var arrow = $(this).parent();
        if (arrow.hasClass("down")) {
            arrow.removeClass("down");
            arrow.removeClass("on");
            $(this).parent().find("dd").slideUp();
        }
        else {
            $(".phb_menu dl").removeClass("down");
            $(".phb_menu dl").removeClass("on");
            $($(".phb_menu dl dt").parent().children('dd')).css("display", "none");
            arrow.addClass("down");
            $(this).parent().find("dd").slideDown();

        }

    });
    $(".phb_menu dd a").click(function () {
        var ty = $(this).attr("data");
        $(".phb_menu dd a").removeClass("on");
        $(this).addClass("on");
        $(".conlist").hide();
        $("#cont_show_" + ty).show();
    });
});



$(function () {
    var page = 1;
    var i = 3;//每版四个图片 
    //向右滚动 
    $(".tjzt_br").click(function () { //点击事件 
        var v_wrap = $(this).parents(".bg_list"); // 根据当前点击的元素获取到父元素 
        var v_show = v_wrap.find(".tjzt_pic"); //找到视频展示的区域 
        var v_cont = v_wrap.find(".pcont"); //找到视频展示区域的外围区域 
        var v_width = v_cont.width();
        var len = v_show.find("li").length; //我的视频图片个数 
        var page_count = Math.ceil(len / i); //只要不是整数，就往大的方向取最小的整数 
        if (!v_show.is(":animated")) {
            if (page == page_count) {
                v_show.animate({ left: '0px' }, "slow");
                page = 1;
            } else {
                v_show.animate({ left: '-=' + v_width }, "slow");
                page++;
            }
        }
    });
    //向左滚动 
    $(".tjzt_bl").click(function () { //点击事件 
        var v_wrap = $(this).parents(".bg_list"); // 根据当前点击的元素获取到父元素 
        var v_show = v_wrap.find(".tjzt_pic"); //找到视频展示的区域 
        var v_cont = v_wrap.find(".pcont"); //找到视频展示区域的外围区域 
        var v_width = v_cont.width();
        var len = v_show.find("li").length; //我的视频图片个数 
        var page_count = Math.ceil(len / i); //只要不是整数，就往大的方向取最小的整数 
        if (!v_show.is(":animated")) {
            if (page == 1) {
                v_show.animate({ left: '-=' + v_width * (page_count - 1) }, "slow");
                page = page_count;
            } else {
                v_show.animate({ left: '+=' + v_width }, "slow");
                page--;
            }
        }
    });
});
$(function () {
    var oLen = $(".tjzt_pic li").length;
    if (oLen > 3) {
        $(".tjzt_bl").show();
        $(".tjzt_br").show();
    }
});



//更多安装教程

$(function () {
    return false;
    if ($('#j_app_desc').get(0)) {
        var descElem = $('#j_app_desc'),
            descHeight = descElem.height(),
            descBtn = $('#j_app_desc_btn'),
            minHeight = 448;
        if (descHeight > minHeight) {
            descElem.css('height', minHeight + 'px');
            descBtn.attr('rel', 0).show();

        }
        else {
            $("#j_app_desc_btn").css("display", "none");
        }

        descBtn.click(function () {
            var rel = $(this).attr('rel');
            if (rel == 0) {
                descElem.css('height', 'auto');
                descBtn.html('&#25910;&#36215;<span class="sq"></span>').attr('rel', 1);
                $("#j_app_desc_btn").addClass("bbj");
            }
            else {
                descElem.css('height', minHeight + 'px');
                descBtn.html('&#23637;&#24320;<span></span>').attr('rel', 0);
                $("#j_app_desc_btn").removeClass("bbj");
            }

        });

    }

});


//选项卡鼠标经过切换效果---通用
function shows(c) {
    var $items = $('#test_' + c + '>li');
    $items.mouseover(function () {
        var $this = $(this), index = $this.index();
        $this.addClass('last').siblings().removeClass('last');
        $('.' + c).addClass('hide').eq(index).removeClass('hide');
    });
    $items.eq(0).addClass('last');
    $('.' + c).eq(0).removeClass('hide');
}
function show(c) {
    $('#test_' + c + '>li').hover(function () {
        $(this).siblings().removeClass('last').end().addClass('last');
        var i = $(this).index() + 1;
        $('.' + c).addClass('hide');
        $('.' + c + '_' + i).removeClass('hide');
    });
}
$(function () {
    show('bl_list');   //推荐栏目
    show('rmbox_list');   //推荐栏目

    shows('hjtj_div');   //推荐栏目
    setTimeout(function () {
        shows('tab_list');
    }, 300);
});

//
$(function () {

    var temp, $wrap = $('.wrap_lf_tt');

    $wrap.find('li.rjjs').click(function (e) {
        temp = $('.rjjsbox').offset().top;
        $('body,html').animate({ scrollTop: temp }, 500);
    });
    $wrap.find('li.rjjt').click(function (e) {
        temp = $('.slider').offset().top - 90;
        $('body,html').animate({ scrollTop: temp }, 500);
    });
    $wrap.find('li.hjtj').click(function (e) {
        temp = $('.hjtjbox').offset().top;
        $('body,html').animate({ scrollTop: temp }, 500);
    });
    $wrap.find('li.qtbb').click(function (e) {
        temp = $('.otherbox').offset().top;
        $('body,html').animate({ scrollTop: temp }, 500);
    });
    $wrap.find('li.xgyd').click(function (e) {
        temp = $('.xgydbox').offset().top;
        $('body,html').animate({ scrollTop: temp }, 500);
    });

    $wrap.find('li.rqrj').click(function (e) {
        temp = $('.rqrjbox').offset().top;
        $('body,html').animate({ scrollTop: temp }, 500);
    });
    $wrap.find('li.xgwz').click(function (e) {
        temp = $('.xgwzbox').offset().top;
        $('body,html').animate({ scrollTop: temp }, 500);
    });
    $wrap.find('li.wypl, .list_a a.pl').click(function (e) {
        temp = $('.cmtbox').offset().top;
        $('body,html').animate({ scrollTop: temp }, 500);
    });


    $('.wrap_lf_tt .xzdz').click(function () {
        temp = $('.xzdzbox').offset().top;
        $('body,html').animate({ scrollTop: temp }, 500);
    });
    $('.artshow_hd .xx span.pls').click(function () {
        temp = $('.cmtbox').offset().top;
        $('body,html').animate({ scrollTop: temp }, 500);
    });


    // 排行tab
    $('#rankingBody').each(function () {
        var $this = $(this);
        $this.find('.tab li').on('click', function () {
            var i = $(this).index();
            $(this).addClass('cur').siblings().removeClass('cur');
            $this.find('.bd-item').removeClass('on').eq(i).addClass('on');
        })
    })
})


//下载页
$(function () {
    var $box = $(".sliderbox");
    var $img = $box.find("img");

    if ($img.length) {
        var image = new Image();
        image.onload = function () {
            var w = image.width;
            var h = image.height;
            if (w > 700) {
                h = h * (700 / w);
            }
            $img.css("height", h);
            $box.css("height", h);
        };
        image.src = $img.attr('src');
    }
})

$('.sliderbox').hover(function () {
    $(".btn_bj").show();
}, function () {
    $(".btn_bj").hide();
});
var $slider = $('.slider ul');
var $slider_li = $('.slider ul li');
var $slider_xiao = $('.slider').width();
var $slider_child_l = $('.slider ul li').length;
var $slider_width = $('.slider ul li').width();
var $slider_img = $('.slider ul li img').width();
$slider.width($slider_child_l * $slider_width);
var slider_count = 0;

if ($slider_child_l <= 1) {
    $('#btn-right').css({ cursor: 'auto' });
    $('#btn-right').addClass("dasabled");
    $slider.parents('.slider').addClass('margin-auto');
}

$('#btn-right').click(function () {
    if ($slider_child_l <= 1 || slider_count >= $slider_child_l - 1) {
        return false;
    }

    slider_count++;
    $slider.animate({ left: '-=' + $slider_width + 'px' }, 'slow');
    slider_pic();
});

$('#btn-left').click(function () {
    if (slider_count <= 0) {
        return false;
    }

    slider_count--;
    $slider.animate({ left: '+=' + $slider_width + 'px' }, 'slow');
    slider_pic();
});

function slider_pic() {
    if (slider_count >= $slider_child_l - 1) {
        // $('.rgbd').css({ cursor: 'auto' });
        $('#btn-right').addClass("dasabled");
        //  $('#btn-left').css({ cursor: 'pointer' });
        $('#btn-left').removeClass("dasabled");
    }
    else if (slider_count > 0 && slider_count <= $slider_child_l - 1) {
        //$('#btn-right').css({ cursor: 'pointer' });
        $('#btn-right').removeClass("dasabled");
        // $('#btn-left').css({ cursor: 'pointer' });
        $('#btn-left').removeClass("dasabled");
    }
    else if (slider_count <= 0 && slider_count <= $slider_child_l - 1) {
        // $('#btn-left').css({ cursor: 'auto'});
        $('#btn-left').addClass("dasabled");
        // $('#btn-right').css({ cursor: 'pointer' });
        $('#btn-right').removeClass("dasabled");
    }
}

$('.slider a').hover(function () {
    if ($(this).find('img:animated').length) return;
    $(this).animate({ marginTop: '0px' }, 200);
    //$(this).find('img').animate({ width: '70px' }, 200);
}, function () {

    $(this).animate({ marginTop: '0' }, 200);
    //$(this).find('img').animate({ width: '70px' }, 200);
});

var t = n = 0, count = $(".content a").size();
$(function () {
    var play = ".play";
    var playText = ".play .text";
    var playNum = ".play .num a";
    var playConcent = ".play .content a";

    $(playConcent + ":not(:first)").hide();
    $(playText).html($(playConcent + ":first").find("img").attr("alt"));
    $(playNum + ":first").addClass("on");
    $(playText).mouseover(function () { window.open($(playConcent + ":first").attr('href'), "_blank") });
    $(playNum).mouseover(function () {
        var i = $(this).text() - 1;
        n = i;
        if (i >= count) return;
        $(playText).html($(playConcent).eq(i).find("img").attr('alt'));
        $(playText).unbind().click(function () { window.open($(playConcent).eq(i).attr('href'), "_blank") });
        $(playConcent).filter(":visible").hide().parent().children().eq(i).show();
        $(this).removeClass("on").siblings().removeClass("on");
        $(this).removeClass("on2").siblings().removeClass("on2");
        $(this).addClass("on").siblings().addClass("on2");
    });
    t = setInterval("showAuto()", 5000);
    $(playConcent).hover(function () { clearInterval(t) }, function () { t = setInterval("showAuto()", 5000); });
});
function showAuto() {
    n = (n >= count - 1) ? 0 : ++n;
    $(".num a").eq(n).trigger('mouseover');
}


function select_change(obj, shid) {
    var fl = $(obj).attr("data");
    var hid = 1;
    if (fl == 1) hid = 2;
    $(obj).attr("data", hid);
    $("#" + shid + fl).show();
    $("#" + shid + hid).hide();

}
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

/*文章首页*/
$(document).ready(function () {
    $(".art_zt a").mouseover(function () {
        $(".art_zt a").removeClass("on");
        $(this).addClass("on");
    });

    $('.game-content-r').each(function () {
        var $sbar = $(this);

        $(window).on('scroll', function () {
            var offsetTop = $sbar.offset().top + $sbar.height();
            var scrollTop = $(this).scrollTop();
            if (offsetTop < scrollTop) {
                $sbar.addClass('fixed');
            }
            else {
                $sbar.removeClass('fixed');
            }
        })
    })
});



window.onload = window.onscroll = window.onresize = function () {
    foot();
if(!$('.art_listl').length){
    ygens();
}
};



//12.20
$(function () {
    $(".game_tj ul li").mouseenter(function () {
        $(this).children('em').hide();
        $(this).children('a').children('span').show();
        $(this).siblings(".game_tj ul li").addClass('focused');
    }).mouseleave(function () {
        $(this).children('em').show();
        $(this).children('a').children('span').hide();
        $(".game_tj ul li").removeClass('focused');
    });

});


//返回顶部
(function () {
    var $backToTopTxt = "", $backToTopEle = $('<a class="go_top">11</a>').appendTo($("body"))
        .text($backToTopTxt).attr("title", $backToTopTxt).click(function () {
            $("html, body").animate({ scrollTop: 0 }, 120);
        }), $backToTopFun = function () {
            var st = $(document).scrollTop(), winh = $(window).height();
            (st > 0) ? $backToTopEle.show() : $backToTopEle.hide();
        };
    // $('<span>返回顶部</span>').appendTo($(".go_top"));
    $(window).bind("scroll", $backToTopFun);
    $(function () { $backToTopFun(); });
})();


// 游戏列表-猜你喜欢切换
$(function () {
    $('#gameYouLike').each(function () {
        var $this = $(this);
        $this.find('.btn-change').on('click', function () {
            var $list = $this.find('.list');
            var $next = $list.not('.hide').next();

            $list.addClass('hide');
            if ($next.length) {
                $next.removeClass('hide');
            } else {
                $list.eq(0).removeClass('hide');
            }
        })
    })

    // 资讯-侧栏-推荐游戏
    $('#sideGoodGame').each(function () {
        var $this = $(this);
        $this.find('.btn-change').on('click', function () {
            //$this.find('.hide').removeClass('hide').siblings().addClass('hide');
            var $list = $this.find('.list');
            var $next = $list.not('.hide').next();

            $list.addClass('hide');
            if ($next.length) {
                $next.removeClass('hide');
            } else {
                $list.eq(0).removeClass('hide');
            }
        })
    })

    // 资讯-详情-更多资讯
    $('#gdzxBox').each(function () {
        $(this).slide({
            mainCell: ".ui-box-bd ul",
            effect: "left",
            autoPage: true,
            pnLoop: false,
            vis: 4
        });
    })

    // 专题-详情-下载按钮
    $('#topicDownload').each(function () {
        var $this = $(this);
        $this.find('.ewm').hover(function () {
            $this.addClass('on');
        }, function () {
            $this.removeClass('on');
        })
    })

    // 专题-首页-查看跟多
    $('#ztRemark').each(function () {
        var $this = $(this);
        $this.find('.btn').on('click', function () {
            if ($this.hasClass('on')) {
                $(this).html('\u67e5\u770b\u66f4\u591a');
                $this.removeClass('on');
            }
            else {
                $(this).html('\u6536\u8d77');
                $this.addClass('on');
            }
        })
    })

    $('#focus-bar-box').each(function () {
        $('#focus').slide({
            titCell: '#focus-num a',
            mainCell: ".focus-bar",
            effect: "fold",
            autoPlay: true
        });
    })

    // 列表页-游戏排行/应用排行
    $('#listGameTop').each(function(){
        $(this).on('mouseenter', 'li', function(){
            $(this).addClass('hover').siblings().removeClass('hover');
        })
    })

})
