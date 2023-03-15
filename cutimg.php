<?php
require_once(dirname(__FILE__).'/common.inc.php');

if($page->action=="cutimg"){
	$uploadfile = $_POST['imgsrc'];
	if(!$uploadfile){
		die('no pic');
	}
	//起点
	$x = intval($_POST['x']);
	$y = intval($_POST['y']);
	//最终图大小
	$rw = 100;
	$rh = 124;
	//切图大小
	$w = intval($_POST['w']);
	$h = intval($_POST['h']);
	//压缩比率
	$scale = 100 ;
	$newPic = str_replace($cfg['url'],"",$uploadfile);
	$newPic = $cfg['path']['root'].$newPic;
	$image = new Image($newPic);
	$image->cutimg($newPic,$newPic,$x,$y,$rw,$rh,$w,$h,$scale);
	//echo "<META HTTP-EQUIV=REFRESH CONTENT='0;URL=$uploadfile?$timestamp'>";
	$timestamp = time();
	echo "
	<script language='javascript'>
		window.opener.document.getElementById('avatar_dis').innerHTML = '<img class=demoImgBorder src=".$uploadfile."?".$timestamp." width=104 height=124 title=上传的头像 >';
		window.close();
	</script>
	";
	exit;
	//die("剪裁成功,<A HREF='$uploadfile?$timestamp' target=_blank>点击查看效果</A> <a href='javascript:window.self.close()'>点击关闭</a>");
}

$srcimg = $_GET['srcimg'];
$srcimg = base64_decode($srcimg);
if(!ereg("^http:",$srcimg)){
	$srcimg = $cfg['url']."upfile/".$srcimg;
}

$page->addJs($cfg['path']['js'].'Jcrop/jquery.min.js');
$page->addJs($cfg['path']['js'].'Jcrop/jquery.Jcrop.min.js');
$page->addCss($cfg['path']['js'].'Jcrop/jquery.Jcrop.css');

$page->name = 'cutimg'; //页面名字,和文件名相同
//cutimg
$page->tpl->assign('width',100);
$page->tpl->assign('height',124);
$page->tpl->assign('srcimg',$srcimg);

$page->show();
?>