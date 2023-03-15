function switchNews(switchId){
	$("#news_dom_1").removeClass(); 
	$("#news_dom_2").removeClass();
	$("#news_dom_"+switchId).addClass("pub_r_tit2_tab");
	$("#news_switch_1").css("display","none");
	$("#news_switch_2").css("display","none");
	$("#news_switch_"+switchId).css("display","");
}
$(document).ready(function(){
	$('#tou #nav li').hover(function(){$(this).addClass('hover');}, function(){$(this).removeClass('hover');});
});
