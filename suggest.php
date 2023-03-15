<?php
$page_name = 'suggest';
require_once(dirname(__FILE__) . '/common.inc.php');
if ($_POST['action'] == 'save') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $tel = trim($_POST['tel']);
    $getcode = trim($_POST['getcode']);

    if (empty($content)) {
        $page->back('投诉与建议内容不能为空');
    }

    if (!is_mobile($tel)) {
        $page->back('请填写正确的手机号码');
    }

    //检测短信验证码是不是正确
    $sql = "select * from fke_auth_mobile where mobile = '$tel' and is_checked = 0 order by id desc";
    $mobile_info = $query->getValue($sql);
    //检测是否已过有效期
    $sms_config = GetConfig('sms');
    if (time() - $mobile_info['send_time'] > $sms_config['expire_time']) {
        $page->back('验证码已过期失效，请重新获取');
    }

    if ($mobile_info['authcode'] != $getcode) {
        $page->back('验证码不正确，请重新输入');
    }

    $fields_array = array(
        'title' => $title,
        'mid' => intval($member_id),
        'content' => $content,
        's_name' => '',
        'tel' => $tel,
        'add_time' => time(),
        'website_id' => WEBHOSTID
    );
    $result = $query->table('suggest')->save($fields_array);
    if ($result) {
        $query->table('auth_mobile')->del('id = ' . $mobile_info['id']);

        //返积分给当前用户
        //积分功能开启状态，根据实际在线支付金额返积分
        /*$webConfig = $query->table('web_config')->where('id = ' . WEBHOSTID)->cache(true)->one();
        if ($member_id > 0 && $webConfig['score_open'] == 1 && $webConfig['score_suggest'] > 0) {
            //变更用户积分
            $member_query->table('member')->where('id = ' . $member_id)->setDec('score', $webConfig['score_suggest']);

            //添加积分变动日志
            $data = array(
                'mid' => $member_id,
                'team_id' => 0,
                'team_type' => 7,   //1房源置顶  2房源自动刷新 3会员开通VIP 4首页推荐房源推广 5首页封面房源推广 6被邀请人消费 7投诉建议 8新用户注册 9签到 10邀请会员
                'score' => $webConfig['score_suggest'],
                'note' => '',
                'add_time' => time()
            );
            $member_query->table('member_score_record')->save($data);
        }*/

        $page->Rdirect('/', '提交成功！非常感谢您的意见与建议，我们会不断对产品和服务进行改进和优化。');
    } else {
        $page->back('建议提交失败，请重试！');
    }
    exit();
}
if ($url_name == 'm') {
    require_once(dirname(__FILE__) . '/mobile/suggest.php');
    exit();
}
$page->title = '投诉与建议-' . $cfg['page']['title'];   //网站名称
//广告位
//$websiteright = GetAdList(68, $query);
//$websitefoot = GetAdList(65, $query);
$website_right_ad = GetAdList(112, $query);
//$info_bottom_ad = GetAdList(120, $query);
//关于我们列表
//$dataList = $query->field('id, title')->table('about')->where('website_id = ' . WEBHOSTID)->order('id asc')->cache(true)->all();
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
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<link rel="stylesheet" type="text/css" href="/css/reg.css?v=<?php echo $webConfig['static_version'];?>" />
<script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/verify.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="//g.alicdn.com/sd/nvc/1.1.112/guide.js?v=<?php echo strtotime(MyDate('Y-m-d H:00:00', time()));?>"></script>
<script type="text/javascript">
    var timer;
    var time_seconds = <?php echo $sms_config['interval'];?>;
    var time_left_seconds;
    function SendSMS(){
        var mobile = $('#tel').val();
        if (mobile.length != 11) {
            layer.msg('请输入正确的手机号码');
            return false;
        }

        //验证码输入窗口
        ShowVerifyCodeDialog();
    }

    function RequestSendSMS(verifyCode) {
        //判断验证码是否正确
        /*var verifyCode = $('#verify_code').val();
        if (verifyCode.length == 0) {
            layer.msg('请输入图片验证码', {offset: '150px'});
            return false;
        }*/

        var mobile = $('#tel').val();
        if (IsMobile(mobile) == false) {
            layer.msg('请输入正确的手机号码');
            return false;
        }

        time_left_seconds = time_seconds;
        $('#sendauthcode').unbind();
        $('#sendauthcode').addClass('disabled');
        $.ajaxSettings.async = true;
        if (verifyCode == undefined || verifyCode == '') {
            verifyCode = getNVCVal();
        }
        var layerLoadingIndex = layer.load(2);
        $.getJSON('/ajax/index.php', {
            action: 'send_sms',
            mobile: mobile,
            verify_code: verifyCode,
            t: new Date().getTime()
        }, function (data) {
            layer.close(layerLoadingIndex);
            if (data.code == 100 || data.code == 200) {
                //注册成功
                nvcReset();
                //layer.msg('验证成功', {offset: '150px'});
            } else {
                //直接拦截
                nvcReset();
                layer.msg('验证失败，稍后请重试', {offset: '150px'});
            }
            if (data.error == 1 || data.error == 3) {
                $('.sendauthcode').prop('disabled', false);
                $('.sendauthcode').val('获取动态码');
                $('.sendauthcode').bind('click', function(){RequestSendSMS('');});
                //layer.msg(data.msg, {icon: 2, time: 1500});
                return false;
            }

            $('.sendauthcode').val(time_seconds + ' 秒后重发');
            timer = setInterval(function () {
                left_time();
            }, 1000);
        });
    }

    function left_time(){
        time_left_seconds--;
        if(time_left_seconds <= 0){
            time_left_seconds = time_seconds;
            clearInterval(timer);
            $('#sendauthcode').removeClass('disabled');
            $('#sendauthcode').text('发送验证码');
            $('#sendauthcode').bind('click', function(){RequestSendSMS('');});
        }else{
            $('#sendauthcode').text(time_left_seconds + ' 秒后重发');
        }
    }

    $(document).ready(function () {
        $('#sendauthcode').click(function () {
            SendSMS();
        });
        $('#submit').click(function () {
            var content = $('#content').val();
            if (content.length < 10) {
                layer.msg('投诉内容不能少于10个字符');
                //layer.alert('投诉内容不能少于10个字符', {icon: 2});
                return false;
            }
            var mobile = $('#tel').val();
            if (mobile.length != 11) {
                layer.msg('请输入正确的手机号');
                return false;
            }

            var getcode = $('#getcode').val();
            if (getcode.length != 6) {
                layer.msg('请输入收到的短信验证码');
                return false;
            }
            $('#suggest').submit();
        });
    });
</script>
</head>

<body>
<?php require_once(dirname(__FILE__).'/header.php');?>
<div id="main">
    <div class="single-page">
        <h1 class="suggest-h1">投诉与建议</h1>
        <div class="suggest-tips">
            亲爱的<?php echo $page->titlec;?>用户<br />感谢您提出的意见与建议，您留下的每个意见与建议都是我们改进的动力！
        </div>
        <div id="complaint" style="border:none;">
            <form action="suggest.html" method="post" id="suggest">
                <input type="hidden" name="action" value="save">
                <p><label>
                        <strong style="margin-top: -5px;">填写内容：</strong>
                        <textarea rows="7" name="content" id="content" cols="65"></textarea>
                    </label></p>
                <p class="suggest-red-tips">如果您需要删除或者举报房源，请在留言中填写房源链接地址或者房源编号。</p>
                <p class="contact-phone">
                    <label>
                        <strong>手机号码：</strong>
                        <input type="text" name="tel" id="tel" class="text-input" style="width: 200px;"/><span class="tips">请输入手机号码获取验证码</span>
                    </label>
                </p>
                <p>
                    <label>
                        <strong>短信验证：</strong>
                        <input maxlength="6" size="8" name="getcode" id="getcode" class="text-input" style="float: left;margin-right: 10px;width: 74px;"/>
                    </label>
                    <span class="seek-bg get-sms-code" id="sendauthcode" onclick="RequestSendSMS('');">获取验证码</span>
                <div class="clear"></div>
                </p>
                <div id="submit" class="common-submit-btn">确认提交</div>
            </form>
        </div>
        <?php
        if ($info_bottom_ad) {
            echo '<div class="info-bottom-ad">' . $info_bottom_ad . '</div>';
        }
        ?>
    </div>
</div>
<?php require_once(dirname(__FILE__).'/footer.php');?>
</body>
</html>
