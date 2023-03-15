<?php
require_once(dirname(__FILE__) . '/path.inc.php');
$page->name = 'leave'; //页面名字,和文件名相同

if (!in_array($dataInfo['user_type'], array(1, 3, 4))) {
    header("http/1.1 404 not found");
    header("status: 404 not found");
    require_once(dirname(dirname(__FILE__)) . '/404.php');
    exit();
}

if ($_POST['action'] == 'save'){
	if (md5(strtolower($_POST['verifycode']))!=$_COOKIE[COOKIE_PREFIX . 'validString']) {
		$page->back('验证码错误！');
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
    if (!empty($email) && !is_email($email)) {
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
}else{
	$dataInfo['cityarea2_name'] = $cityarea2_option[$dataInfo['cityarea2_id']]['region_name'];
	$dataInfo['add_time'] = MyDate('Y-m-d',$dataInfo['add_time']);
	$page->title = $dataInfo['realname']."的网店-".$page->city.$page->titlec;
}
$websiteright = GetAdList(114, $query);
$page->title = '【给我留言-'.$middle_column.$dataInfo['realname'].'电话'.$dataInfo['mobile'].'】-'.$page->titlec.$cityInfo['city_name'];
$page->description = $dataInfo['company'].$dataInfo['outlet_addr'].',工作区域'.str_replace('|', ' ', $dataInfo['servicearea']).$page->titlec.$cityInfo['city_name'];
$page->keywords = $middle_column.'、'.$dataInfo['realname'].'、电话'.$dataInfo['mobile'].',房产经纪人';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page->title;?></title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<!--{$static_version}-->"></script>
<script type="text/javascript" src="/js/jquery.cookie.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/validform_v5.3.2_min.js?v=<!--{$static_version}-->"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/reg.js"></script>
<script type="text/javascript" src="/js/f.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var validForm = $("#leaveform").Validform({
            tiptype: 3,
            showAllError: true,
            ignoreHidden: true
        });
    });
</script>
</head>

<body id="body">
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div id="main" class="shop-main">
    <div class="broker_detail">
        <?php require_once(dirname(__FILE__).'/top.php');?>
    </div>
    <div id="art_left">
        <div class="levaemsg">
        <form action="" method="post" id="leaveform">
        <input type="hidden" name="id" id="uid" value="<?php echo $dataInfo['id'];?>" />
        <input type="hidden" name="action" id="action" value="save" />
        	<table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody><tr>
              <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                  <tbody><tr>
                    <td width="13%" align="right"><span class="red">*</span>姓名：</td>
                    <td width="87%"><input type="text" style="width:244px;border:1px solid #CCC;" id="uname" name="uname" datatype="*2-8" sucmsg=" " nullmsg="请填写您的真实姓名" errormsg="请填写您的真实姓名"></td>
                  </tr>
                </tbody></table>
              </td>
            </tr>
            <tr>
              <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                  <tbody><tr>
                    <td width="13%" align="right"><span class="red">*</span>手机号码：</td>
                    <td width="87%"><input type="text" style="width:244px;border:1px solid #CCC;" id="tel" name="tel" datatype="m" sucmsg=" " nullmsg="请输入您的手机号码" errormsg="手机号码输入错误"></td>
                  </tr>
                </tbody></table></td>
            </tr>
            <tr>
              <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                  <tbody><tr>
                    <td width="13%" align="right">QQ：</td>
                    <td width="87%"><input type="text" style="width:244px;border:1px solid #CCC;" id="qq" name="qq"></td>
                  </tr>
                </tbody></table></td>
            </tr>
            <tr>
              <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                  <tbody><tr>
                    <td width="13%" align="right">E-mail：</td>
                    <td width="87%"><input type="text" style="width:244px;border:1px solid #CCC;" id="email" name="email" datatype="e" ignore="ignore" sucmsg=" " nullmsg="" errormsg="E-mail输入错误"></td>
                  </tr>
                </tbody></table></td>
            </tr>
            <tr>
              <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                  <tbody><tr>
                    <td width="13%" align="right"><span class="red">*</span>留言内容：</td>
                    <td width="87%">
                        <textarea name="note" rows="4" style="width:95%;font-size:12px;" id="note" datatype="*10-800" sucmsg=" " nullmsg="留言内容不能少于10个字" errormsg="留言内容不能少于10个字"></textarea>
                        <span class="Validform_checktip" style="margin-left: 0;clear: both;display: block;">留言内容至少10个字</span>
                    </td>
                  </tr>
                </tbody></table></td>
            </tr>
            <tr>
              <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                  <tbody><tr>
                    <td width="13%" align="right"><span class="red">*</span>验 证 码：</td>
                    <td width="87%"><table width="100%" border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td valign="middle"><input name="verifycode" type="text" id="verifycode" style="border:1px solid #CCC;" size="6" maxlength="4" datatype="s4-4" sucmsg=" " nullmsg="请输入验证码" errormsg="请输入正确的验证码">
                            <img src="/valid.php" height="20" id="valid_pic" onclick="this.src='/valid.php?' + Math.random();">
                        </td>
                      </tr>
                    </table></td>
                  </tr>
                </tbody></table></td>
            </tr>
            <tr>
          <td height="50" align="left" style="padding-left:87px;"><img src="/images/wtj.jpg" onclick="sendmsg();" style="cursor:pointer;" /></td>
        </tr>
          </tbody></table>
          </form>
        </div>
   </div>
   <div id="right">
       <div id="banner_right"><?php echo $websiteright;?></div>
   </div>
   <div class="clear"></div>
</div>
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
</body>
</html>
