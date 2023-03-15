// JavaScript Document
$(document).ready(function(e) {
    LoadHouse();
});

//载入房源信息
function LoadHouse(){
	var houseid = $('#houseid').val();
	var type = $('#type').val();
	$.get('loadhouse.php?type=' + type + '&houseid=' + houseid + '&t=' + new Date().getSeconds(), null, function(data){
		$('#house_info_box').html(data);
	});
}
function CheckPass(){
	var houseid = $('#houseid').val();
	var type = $('#type').val();
	showmessage('正在提交请求','loading');
	$.get('ajax_check_house.php?action=pass&type=' + type + '&houseid=' + houseid + '&t=' + new Date().getSeconds(), null, function(data){
		if(data.error == 0){
			showmessage(data.msg,'ok');
			$('#house_info_box').html('<div class="tips loading">正在载入房源信息...</div>');
			LoadHouse();
		}else if(data.error == 1){
			showmessage(data.msg,'error');
			return false;
		}
	}, 'json');
}
function CheckFail(checknote, disableUser){
	var houseid = $('#houseid').val();
	var type = $('#type').val();
	showmessage('正在提交请求','loading');
	$.get('ajax_check_house.php?action=checkfail&type=' + type + '&houseid=' + houseid + '&note=' + checknote + '&disableuser=' + disableUser + '&t=' + new Date().getSeconds(), null, function(data){
		if(data.error == 0){
			showmessage(data.msg,'ok');
			$('#house_info_box').html('<div class="tips loading">正在载入房源信息...</div>');
			LoadHouse();
		}else if(data.error == 1){
			showmessage(data.msg,'error');
			return false;
		}
	}, 'json');
}
function DisableUser(){
	var mid = $('#mid').val();
	showmessage('正在提交请求','loading');
	$.get('ajax_check_house.php?action=disableuser&mid=' + mid + '&t=' + new Date().getSeconds(), null, function(data){
		if(data.error == 0){
			showmessage(data.msg,'ok');
			$('#house_info_box').html('<div class="tips loading">正在载入房源信息...</div>');
			LoadHouse();
		}else if(data.error == 1){
			showmessage(data.msg,'error');
			return false;
		}
	}, 'json');
}

function DeleteHouse(){
	var houseid = $('#houseid').val();
	var type = $('#type').val();
	showmessage('正在提交请求','loading');
	$.get('ajax_check_house.php?action=delete&type=' + type + '&houseid=' + houseid + '&t=' + new Date().getSeconds(), null, function(data){
		if(data.error == 0){
			showmessage(data.msg,'ok');
			$('#house_info_box').html('<div class="tips loading">正在载入房源信息...</div>');
			LoadHouse();
		}else if(data.error == 1){
			showmessage(data.msg,'error');
			return false;
		}
	}, 'json');
}

function showmessage(message,type){
	$('#showmessage').remove();
	var msgtemplet = '<div id="showmessage"';
	if(type == 'error'){
		msgtemplet += ' class="errormsg"></div>';
	}else if(type == 'ok'){
		msgtemplet += ' class="okmsg"></div>';
	}else if(type == 'loading'){
		msgtemplet += ' class="loadingmsg"></div>';
	}
	$('body').append(msgtemplet);
	$('#showmessage').html(message);
	if(type!='loading'){
		setTimeout(function(){	$('#showmessage').remove();},3000);
	}
}
function docollect(){
	$('#action').val('collect');
	$('#search_nav').submit();
}
function dosearch(){
	$('#action').val('');
	$('#search_nav').submit();
}