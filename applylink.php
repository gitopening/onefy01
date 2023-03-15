<?php
require_once(dirname(__FILE__).'/common.inc.php');
if ($_POST['action']=='save'){
	$link_name=$_POST['link_name'];
	$link_url=$_POST['link_url'];
	$alexa=$_POST['alexa'];
	$qq=$_POST['qq'];
	$email=$_POST['email'];
	$tel=$_POST['tel'];
	$linkman=$_POST['linkman'];
	if (empty($link_url) || empty($link_name)){
		echo '<script type="text/javascript">alert("链接名称和网址不能为空，请重新填写！");history.go(-1);</script>';
		exit();
	}
	$sql="insert into `fke_outlink` (link_name,link_class,link_type,link_logo,link_text,link_url,list_order,click_num,status,add_time,shouye,alexa,qq,email,tel,linkman,website_id) values ('$link_name','0','1','','$link_name','$link_url','0','0','0','".time()."','0','$alexa','$qq','$email','$tel','$linkman','".WEBHOSTID."')";
	echo $sql;
	if ($query->execute($sql)){
		echo '<script type="text/javascript">alert("申请已提交，请等待管理员审核！");window.location.href="/";</script>';
		exit();
	}else{
		echo '<script type="text/javascript">alert("申请提请失败，请重试！");history.go(-1);</script>';
		exit();
	}
}else{
	$page->name = 'applylink'; //页面名字,和文件名相同
	$page->title = $cfg['page']['title'];   //网站名称
	//广告位
	$page->tpl->assign('websiteright',GetAdList(69, $query));
	$page->tpl->assign('websitefoot',GetAdList(65, $query));
	
	$page->show();
}