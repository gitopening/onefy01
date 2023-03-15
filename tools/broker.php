<?php
require_once(dirname(dirname(__FILE__)) . '/common.inc.php');
$cookie_key = COOKIE_PREFIX . 'mobile_query_count';
$mobile_query_count = intval($_COOKIE[$cookie_key]);

if ($_POST['action'] == 'query') {
    $mobile = trim($_POST['mobile']);
    $mobile = str_replace('　', '', $mobile);
    $mobile = str_replace(' ', '', $mobile);
    $valid = trim($_POST['valid']);

    $mobile_query_count++;
    setcookie($cookie_key, $mobile_query_count, time() + 43200, '/', $cfg['domain']);

    if ($mobile_query_count >= 5) {
        $validate = 1;
    } else {
        $validate = 0;
    }

    if (!is_mobile($mobile)) {
        ajax_return(array(
            'error' => 2,
            'validate' => $validate,
            'msg' => '请填写要查询的电话！'
        ));
    }

    if ((md5(strtolower($valid)) != $_COOKIE[COOKIE_PREFIX . 'validString']) && $mobile_query_count > 5) {
        setcookie(COOKIE_PREFIX . 'validString', '', -1, '/', $cfg['domain']);
        ajax_return(array(
            'error' => 3,
            'validate' => $validate,
            'msg' => '验证码错误'
        ));
    }

    //查询电话是否存在
    $BrokerData = new BrokerData($query);
    $result = $BrokerData->checkPhoneIsExist($mobile);

    if ($result) {
        ajax_return(array(
            'error' => 0,
            'validate' => $validate,
            'msg' => $mobile . '，此号码有可能是房产经纪人号码'
        ));
    } else {
        ajax_return(array(
            'error' => 0,
            'validate' => $validate,
            'msg' => '此号码未被收录'
        ));
    }
    exit();
}

$page->title = '【房产中介电话|房产经纪人电话|中介电话号码查询】' . $cfg['page']['title'];   //网站名称
$page->description = '房产中介电话号码查询系统，可以帮你准确查询识别房产中介电话号码，免费查询房产经纪人电话号码、房产中介门店电话号码，大量链家地产、我爱我家、中原地产、21世纪不动产、家家顺、美联物业、Q房网等。中介号码大全。';
$page->keywords = '房产中介电话号码，经纪人电话号码、房产中介电话号码识别查询、房产中介大全';
//广告位
//$websiteright = GetAdList(68, $query);
//$websitefoot = GetAdList(65, $query);
$website_right_ad = GetAdList(112, $query);
//$info_bottom_ad = GetAdList(120, $query);
//关于我们列表
//$dataList = $query->field('id, title')->table('about')->where('website_id = ' . WEBHOSTID)->order('id asc')->cache(true)->all();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page->title;?></title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#submit').click(function () {
            FormSubmit();
        });
        $('#mobile, #valid').keyup(function (e) {
            var keyCode = e.charCode || e.which || e.keyCode;
            if (keyCode == 13) {
                FormSubmit();
            }
        });
    });
    function FormSubmit() {
        var formData = $('#data-form').serialize();
        formData.t = new Date().getTime();
        var loading = layer.load(3);
        $('.query-result').hide();
        $.post('broker.html', formData, function (data) {
            //刷新验证码，清空输入内容
            $('#valid').val('');
            $('#valid_pic').attr('src', '/valid.php?' + Math.random());
            layer.close(loading);
            if (data.error == 0) {
                $('.query-result span').html(data.msg);
                $('.query-result').fadeIn('slow');
            } else {
                layer.alert(data.msg, {icon: 2});
            }
            if (data.validate == 1) {
                $('.query-form .container-box').addClass('valid');
            } else {
                $('.query-form .container-box').removeClass('valid');
            }
        }, 'json');
    }
</script>
</head>

<body>
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div class="main query-form" style="height: 400px;background-color: transparent;">
    <h1 class="title">房产经纪人电话号码识别</h1>
    <form action="" method="post" id="data-form" onsubmit="return false;">
        <input type="hidden" name="action" value="query">
        <div class="container-box <?php echo $mobile_query_count >= 5 ? ' valid' : '';?>">
            <div class="control-wrap">
                <input type="text" name="mobile" id="mobile" value="" placeholder="请输入要查询的电话号码" maxlength="30" />
            </div>
            <div class="control-wrap valid-box">
                <input type="text" name="valid" id="valid" value="" placeholder="验证码" maxlength="4"/>
                <img src="/valid.php" id="valid_pic" onclick="this.src='/valid.php?' + Math.random();" />
            </div>
            <div id="submit" class="common-submit-btn">立即查询</div>
            <div class="clear"></div>
        </div>
    </form>
    <div class="query-result">
        查询结果：<span></span>
        <div class="query-result-tips">注：以上数据均来自公开的互联网数据，查询结果仅供参考！</div>
    </div>
    <?php
    if ($info_bottom_ad) {
        echo '<div class="info-bottom-ad">' . $info_bottom_ad . '</div>';
    }
    ?>
</div>
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
</body>
</html>
