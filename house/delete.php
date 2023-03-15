<?php
require_once(dirname(__FILE__) . '/path.inc.php');
if (empty($dataInfo) || $member_db == 'x') {
    header("http/1.1 404 not found");
    header("status: 404 not found");
    require_once(dirname(dirname(__FILE__)) . '/404.php');
    exit();
}
if ($member_db == 'x') {
    if ($column == 'job') {
        $house_url = $House->GetURL($dataInfo, 'x');
    } else {
        $house_url = $House->GetHouseURL($dataInfo, 'x');
    }
} elseif ($member_db > 0) {
    if ($column == 'job') {
        $house_url = $House->GetURL($dataInfo, $member_db);
    } else {
        $house_url = $House->GetHouseURL($dataInfo, $member_db);
    }
} else {
    if ($column == 'job') {
        $house_url = $House->GetURL($dataInfo);
    } else {
        $house_url = $House->GetHouseURL($dataInfo);
    }
}

if ($column == 'job') {
    if ($dataInfo['status'] == 1) {
        $page->Rdirect($house_url, '招聘信息已删除！');
    } elseif ($dataInfo['status'] == 5) {
        $page->Rdirect($house_url, '招聘信息已失效，不能删除！');
    }
} else {
    if ($dataInfo['house_status'] == 1) {
        $page->Rdirect($house_url, '房源已删除！');
    } elseif ($dataInfo['house_status'] == 5) {
        $page->Rdirect($house_url, '房源已成交，不能删除！');
    }
}

if ($dataInfo['mid'] > 0) {
    $image_host = '';
} else {
    $image_host = IMG_HOST;
}
//房屋类型
$page->title = '删除信息-'.$page->titlec;
$page->keywords = '';
$page->description='';
//广告位
$website_right_ad = GetAdList(112, $query);
$info_bottom_ad = GetAdList(120, $query);
//短信发送配置
$sms_config = GetConfig('sms');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page->title;?></title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link href="/css/password.css?v=<?php echo $webConfig['static_version'];?>" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<link rel="stylesheet" type="text/css" href="/css/reg.css?v=<?php echo $webConfig['static_version'];?>" />
<script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/jquery.cookie.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/validform_v5.3.2_min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript">
$(function(){
	var validForm = $("#dataForm").Validform({
		tiptype:3,
		showAllError:true,
        beforeSubmit: function (curform) {
            var auth_code = $('#mobile_auth_code').val();
            $.getJSON('/ajax/index.php', {
                action: 'deletehouse',
                type: <?php echo $house_type;?>,
                id: <?php echo $id;?>,
                auth_code: auth_code,
                member_db: '<?php echo $member_db;?>',
                t: new Date().getTime()
            }, function (data) {
                if (data.error == 0) {
                    window.location.href = '/<?php echo $column == 'job' ? 'job' : 'house';?>/done_<?php echo $column != 'job' ? $column . '_' : '';?><?php
					if ($member_db == 'x'){
					    $url_fix = 'x';
					} elseif ($member_db > 0) {
					     $url_fix = '_' . $member_db;
					} else {
					    $url_fix = '';
					}
                    echo $id . $url_fix;
                    ?>.html';
                } else {
                    RefreshValidCode();
                    alert(data.msg);
                }
            });
            return false;
        }
	});
});
var timer;
var time_seconds = <?php echo $sms_config['interval'];?>;
var time_left_seconds;

function sendsms(){
    //验证码输入窗口
    ShowVerifyCodeDialog();
}

function RequestSendSMS(){
    var vcode = $('#verify_code').val();
    if (vcode.length < 4) {
        layer.msg('请填写验证码');
        return false;
    }

	time_left_seconds = time_seconds;
	$('#sendauthcode').unbind();
	$('#sendauthcode').addClass('disabled');

    $.getJSON('/ajax/index.php', {
        action: 'getdeletecode',
        type: <?php echo $house_type;?>,
        id: <?php echo $id;?>,
        member_db: '<?php echo $member_db;?>',
        vcode: vcode,
        t: new Date().getTime()
    }, function (data) {
        layer.closeAll();
        if (data.error == 0) {
            timer = setInterval(left_time, 1000);
            $('#sendauthcode').val(time_seconds + ' 秒后再发');
        }
        layer.msg(data.msg);
    });
}
function left_time(){
	time_left_seconds--;
	if(time_left_seconds <= 0){
		time_left_seconds = time_seconds;
		clearInterval(timer);
		$('#sendauthcode').removeClass('disabled');
		$('#sendauthcode').val('发送验证码');
		$('#sendauthcode').bind('click', function(){RequestSendSMS();});
	}else{
		$('#sendauthcode').val(time_left_seconds + ' 秒后再发');
	}
}
</script>

</head>

<body>
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div id="main">
	<div id="left" class="rent_r">
        <div class="d_table">
            <h1>使用手机验证码删除该房源信息</h1>
            <p class="d_t_t">
            <form action="" name="dataForm" method="post" id="dataForm" class="step1-form ft14 sf-grey2">
                <div class="sms-opa ft14 sf-grey2">
                    <input type="hidden" name="mobile" id="mobile" value="">
                    <input type="hidden" name="house_source" id="house_source" value="<?php echo $member_db;?>">
                    <div><span style="font-weight: bold;color: #EC3701; "></span></div>
                    <br>
                    <div class="question-mobile">
                        <span class="label">发贴手机号：</span>
                        <span class="fc-red fb"> <?php
                            if (!empty($dataInfo['owner_phone'])){
                                echo substr($dataInfo['owner_phone'], 0, 3).'****'.substr($dataInfo['owner_phone'], 7);
                            }else{
                                echo '<img src="' . GetPictureUrl($dataInfo['owner_phone_pic'], 0, 0) . '">';
                            }
                            ?></span>
                    </div>
                    <div class="question-mobile">
                        <span class="label"></span>
                        <input type="button" class="get-sms-code" id="sendauthcode" onclick="sendsms();" value="获取短信验证码" style="margin-left: 0;" />
                    </div>
                    <div class="question-mobile i2">
                        <span class="label">短信验证码：</span>
                        <span class="text-in checkcode">
                            <input type="text" maxlength="6" id="mobile_auth_code" name="mobile_auth_code" autocomplete="off" datatype="s6-6" sucmsg=" " nullmsg="请输入验证码" errormsg="请输入验证码">
                            <span id="tip_checkcode"></span>
                            <span class="Validform_checktip">请输入手机收到的验证码</span>
                        </span>
                    </div>
                    <div style="margin: 25px 0 90px 133px;">
                        <input type="submit" name="submit" value="确 定" class="submit_btn">
                        <!--<a class="next-btn seek-bg" onclick="$('#getpasswordform').submit();" href="###">下一步</a> </div>-->
                    </div>

                </div></form>
            </p>
            <div class="tips" style="display:none;">提示：由于各地网络原因验证码接收可能有延迟，如未收到验证码请您间隔一段时间再次提交获取短信验证码。</div>
        </div>
        <?php
        if ($info_bottom_ad) {
            echo '<div class="info-bottom-ad">' . $info_bottom_ad . '</div>';
        }
        ?>
    </div>
    <div id="right">
        <div id="banner_right"><?php echo $website_right_ad;?></div>
    </div>
    <div class="clear"></div>
</div>
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
</body>
</html>
