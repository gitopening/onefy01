// JavaScript Document
$(document).ready(function(){
var config={"navispeed":200,"bannerspeed1":400,"bannerjiange":10000,"dropdownspeed":500}	 
	  
$(".fadeto").hover(function(){
	$(this).fadeTo(100,0.8);
	},function(){
		$(this).fadeTo(100,1);
		});	  
$(".enter_login_input").focus(function(){
		$(this).css("border-color","#f5a200").css("background-color","#fde4be").css("box-shadow","0px 0px 5px #f5a200");
		$(this).parent().find(".enter_login_word").css("color","#f5a200");
	});
	$(".enter_login_input").blur(function(){
		$(this).css("border-color","#d7d7d8").css("background-color","#fff").css("box-shadow","");
		$(this).parent().find(".enter_login_word").css("color","#838383");
	});
/*navihover*/
$("ul.navi li.normal").hover(function(){
	$(this).children("ul").stop(true,true).slideDown(config.navispeed);
	},function(){
	$(this).children("ul").stop(true,true).slideUp(config.navispeed);
		});

/**/	
/*index_big_slide*/
var ww=parseInt($(window).width());
$(".index_banner ul.slide_line li.normal").width(ww);
$(window).resize(function(){
	 ww=parseInt($(window).width());
$(".index_banner ul.slide_line li.normal").width(ww);
	});

$(".index_banner ul.slide_line li.normal").each(function(){
	var data=$(this).attr("data");
	$(this).css("background",data);
});
var step=0;
var slidenum1=$(".index_banner ul.slide_line li.normal").length;
$.extend({slide1:function(){
	if(step>=slidenum1-1)
	{
		$(".index_banner ul.slide_line").animate({"left":0},config.bannerspeed1);
		step=0;
		}
	else
	{
		step+=1;
		$(".index_banner ul.slide_line").animate({"left":-step*ww},config.bannerspeed1);
		}
	}});

var timmer1=setInterval("$.slide1()",config.bannerjiange);
/**/	   
/*goleft&right*/
$(".bigright").click(function(){
	if(step>=slidenum1-1)
	{
		$(".index_banner ul.slide_line").animate({"left":0},config.bannerspeed1);
		step=0;
		}
	else
	{
		step+=1;
		$(".index_banner ul.slide_line").animate({"left":-step*ww},config.bannerspeed1);
		}
	});
$(".bigleft").click(function(){
	if(step<=0)
	{	
		$(".index_banner ul.slide_line").animate({"left":-(slidenum1-1)*ww},config.bannerspeed1);
		step=slidenum1-1;
	
		}
	else
	{
		step-=1;
		$(".index_banner ul.slide_line").animate({"left":-step*ww},config.bannerspeed1);
		
		}
	});	
$(".bigleft").add(".bigright").hover(function(){
	clearInterval(timmer1);
	},function(){
		timmer1=setInterval("$.slide1()",config.bannerjiange);
		});
$(".bigleft").fadeTo(1,0.6).fadeOut();
		$(".bigright").fadeTo(1,0.6).fadeOut();		
$(".index_banner").hover(function(){
$(".bigleft").fadeIn();	
$(".bigright").stop(true,true).fadeIn();	
	},function(){
		$(".bigleft").fadeOut();
		$(".bigright").fadeOut();
		});

/**/
/*dropdown*/
	$("ul.dropdown").find("li.secul").eq(0).css("display",'block');
	$("ul.dropdown li.firstli").click(function(){
		$("ul.dropdown li.secul").slideUp(config.dropdownspeed);
		$(this).next("li.secul").slideDown(config.dropdownspeed);
		}); 
/**/	
/*xufeimoshi*/
$(".xufeimoshi").click(function(){
$(".xufeimoshi").css("background-image","url(images/xf/xf_13.jpg)").css("color","");	
$(this).css("background-image","url(images/xf/xf_12.jpg)").css("color","#a0c05b");	
	});
$(".xufeimoshi").eq(0).trigger("click");	
/**/
/*active*/
$("ul.act_list li.normal").hover(function(){
	$(this).find(".img2").find("div.hover").stop(true,true).slideDown(200);
	},function(){
	$(this).find("div.hover").slideUp(200);
	});
/**/
/*enter hover*/
var oldsrc;
$(".enter_broad").hover(function(){
	oldsrc=$(this).find("img").attr("src");
	$(this).find("img").attr("src",oldsrc+"s");
	},function(){
		$(this).find("img").attr("src",oldsrc);
		
		});

/**/  

   
						   });