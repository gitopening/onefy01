<?php 
require_once(dirname(__FILE__).'/path.inc.php');
$id=intval($_POST['id']);
$act=$_REQUEST['action'];
if (empty($id)){
	echo '<script>alert("参数错误，请联系网站管理员！");window.location.href="'.$_SERVER['HTTP_REFERER'].'";</script>';
}
if ($act=='addweituo'){
	if (md5(strtolower($_POST['valid']))!=$_COOKIE[COOKIE_PREFIX . 'validString']) {
		$page->back('验证码错误！');
		exit;
	}
	$fieldArray = array(
			'bigzone' => intval($_POST['bigzone']),
			'smallzone' => intval($_POST['smallzone']),
			'huxing' => intval($_POST['huxing']),
			'symj' => intval($_POST['symj']),
			'louceng' => intval($_POST['louceng']),
			'endlouceng' => intval($_POST['endlouceng']),
			'uname' => trim($_POST['uname']),
			'tel' => trim($_POST['tel']),
			'note' => trim($_POST['note']),
			'uid' => $id,
			'add_time' => time(),
			'is_check' => 0,
			'city_website_id' => $cityInfo['id']
			);
	$member_query->insert('fke_weituo', $fieldArray);
	echo '<script>alert("提交委托信息成功！");window.location.href="'.$_SERVER['HTTP_REFERER'].'";</script>';
	exit();
}elseif ($act=='addmsg'){
	if (md5(strtolower($_POST['valid']))!=$_COOKIE[COOKIE_PREFIX . 'validString']) {
		$page->back('验证码错误！');
		exit;
	}

	$uname = trim($_POST['uname']);
	$tel = trim($_POST['tel']);
	$qq = trim($_POST['qq']);
	$email = trim($_POST['email']);
	$note = trim($_POST['note']);

	if (empty($uname)) {
		$page->back('请输入您的姓名');
	}
	if (!is_mobile($tel)) {
		$page->back('请输入正确的手机号');
	}
	if (empty($email) && !is_email($email)) {
		$page->back('邮箱输入错误');
	}
	if (strlen($note) < 10) {
		$page->back('留言内容不能少于10个字符');
	}

	$fieldArray = array(
			'uname' => $uname,
			'tel' => $tel,
			'qq' => $qq,
			'email' => $email,
			'note' => $note,
			'add_time' => time(),
			'uid' => $id,
			'is_check' => 0,
			'city_website_id' => $cityInfo['id']
	);
	$member_query->insert('fke_msg', $fieldArray);
	
	echo '<script>alert("提交留言成功！");window.location.href="'.$_SERVER['HTTP_REFERER'].'";</script>';
	exit();
}