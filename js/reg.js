// JavaScript Document
$(document).ready(function(e) {
    
});
function ckname (inp) {
	$.ajax({type:"GET", url:"ajax.php?r="+Math.random()+'&action=unique&username=' + inp, dataType:"text",async:false,success:function (msg){
		r = msg;
	}}); 
	if (r==0) {
		return false;
	} else {
		return true;
	}
}
function checklogin(){
	var username = $('#loginusername').val();
	var pwd = $('#loginpwd').val();
	
	if(username == ''){
		alert('用户名不能为空');
		return false;
	}
	if(pwd == ''){
		alert('请输入密码');
		return false;
	}
	return true;
}
//检测是不是数字
function checkRate(str){
	var re = /^[1-9]+[0-9]*]*$/; //判断字符串是否为数字 //判断正整数 /^[1-9]+[0-9]*]*$/
	if (!re.test(str)){
		return false;
	}else{
		return true;
	}
}
function sendmsg(){
	$('#leaveform').submit();
}
function checkmsg(){
	uid=$('#uid').val();
	uname=$('#uname').val();
	email=$('#email').val();
	tel=$('#tel').val();
	note=$('#note').val();
	if(checkRate(uid)==false){
		alert('参数不正确，请联系管理员');
		return false;
	}
	if(uname.length<2){
		alert('请输入您的姓名');
		return false;
	}
	if(tel.length<7 || checkRate(tel)==false){
		alert('请输入正确的联系电话');
		return false;
	}
	if(checkemail(email)==false){
		alert('请输入正确的邮箱');
		return false;
	}
	if(note.length<10){
		alert('留言内容不能少于10个字符');
		return false;
	}
	return true;
}
function checkregform(){
	if(document.getElementById('agree').checked == false){
		alert('请先阅读并同意网站用户服务协议');
		return false;
	}
	var username=$('#username').val();
	var passwd=$('#passwd').val();
	var passwd2=$('#passwd2').val();
	var email=$('#email').val();
	var realname=$('#realname').val();
	var cityarea_id=$('#cityarea_id').val();
	var cityarea2_id=$('#smallzone').val();
	var outlet_first=$('#outlet_first').val();	//所属公司门店
	var outlet_last=$('#outlet_last').val();
	var vaild=$('#vaild').val();
	
	/*if(checkusername(username) && checkpwd(passwd) && checkpwd2(passwd,passwd2) && checkemail(email) && checkrealname(realname) && checkcity(cityarea_id) && checkcity(cityarea2_id) && checkcompany(outlet_first) && checkoutlet(outlet_last) && checkvaild(vaild)){
		$('#registerform').submit();
	}else{
		return false;
	}*/
	if(checkusername(username) && checkpwd(passwd) && checkpwd2(passwd,passwd2) && checkemail(email) && checkvaild(vaild)){
		$('#registerform').submit();
	}else{
		return false;
	}

}
function checkusername(username){
	if(checkMobile(username)==false){
		$('#errMsg_username').html(setmsg('手机号码不正确',false));
		return false;
	}else{
		if(ckname(username)==false){
			$('#errMsg_username').html(setmsg('该手机号已注册',false));
			return false;
		}else{
			$('#errMsg_username').html(setmsg('',true));
			return true;
		}
	}
}
function checkpwd(passwd){
	if(passwd.length<6){
		$('#errMsg_passwd').html(setmsg('密码为6-20位',false));
		return false;
	}else{
		$('#errMsg_passwd').html(setmsg('',true));
		return true;
	}
}
function checkpwd2(passwd,passwd2){
	if(passwd2!=passwd || passwd2.length<6){
		$('#errMsg_passwd2').html(setmsg('重复密码错误',false));
		return false;
	}else{
		$('#errMsg_passwd2').html(setmsg('',true));
		return true;
	}
}
function checkemail(email){
	if(is_email(email)==false){
		$('#errMsg_email').html(setmsg('邮箱格式不正确',false));
		return false;
	}else{
		$('#errMsg_email').html(setmsg('',true));
		return true;
	}
}
function checkrealname(realname){
	if(realname.length<2){
		$('#errMsg_realname').html(setmsg('请输入真实姓名',false));
		return false;
	}else{
		$('#errMsg_realname').html(setmsg('',true));
		return true;
	}
}
function checkcity(cityareaid){
	if(cityareaid=='0' || cityareaid==''){
		$('#errMsg_cityarea_id').html(setmsg('请选择所在地区',false));
		return false;
	}else{
		$('#errMsg_cityarea_id').html(setmsg('',true));
		return true;
	}
}
function checkcompany(outlet_first){
	if(outlet_first.length<=0){
		$('#errMsg_outlet_first').html(setmsg('请输入公司名',false));
		return false;
	}else{
		$('#errMsg_outlet_first').html(setmsg('',true));
		return true;
	}
}
function checkoutlet(outlet_last){
	if(outlet_last.length<=0){
		$('#errMsg_outlet_last').html(setmsg('请输入所在门店',false));
		return false;
	}else{
		$('#errMsg_outlet_last').html(setmsg('',true));
		return true;
	}
}
function checkvaild(vaild){
	if(vaild.length<4){
		$('#errMsg_vaild').html(setmsg('请输入验证码',false));
		return false;
	}else{
		//$('#errMsg_vaild').html(setmsg('',true));
		$('#errMsg_vaild').html('');
		return true;
	}
}
//检测用户名是否可用
function ckname(inp) {
	$.ajax({type:"GET", url:"ajax.php?r="+Math.random()+'&action=unique&username='+inp, dataType:"text",async:false,success:function (msg){
		r = msg;
	}}); 
	if (r==0) {
		return false;
	} else {
		return true;
	}
}
//检察手机格式是否正确
function checkMobile(s){  
	var regu =/^[1][0-9][0-9]{9}$/;
	var re = new RegExp(regu);
	if (re.test(s)) {
		return true;
	}else{
		return false;
	}
}
//检测邮箱是否正确
function is_email(s){
	var regu =/^[-_A-Za-z0-9]+@([_A-Za-z0-9]+\.)+[A-Za-z0-9]{2,3}$/;
	var re = new RegExp(regu);
	if (re.test(s)) {
		return true;
	}else{
		return false;
	}
}
function setmsg(str,type){
	if(type==true){
		return '<div class="rightmsg">' + str + '</div>';
	}else{
		return '<div class="errormsg">' + str + '</div>';
	}
}
function validatorform(){
	var username = $('#username').val();
	var vaild = $('#vaild').val();
	if(username == ''){
		alert('请输入您注册时的邮箱!');
		return false;
	}
	if(vaild.length<4){
		alert('请输入正确的验证码!');
		return false;
	}
	return true;
}