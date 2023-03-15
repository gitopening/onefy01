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
		setTimeout(function(){	$('#showmessage').remove();},5000);
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