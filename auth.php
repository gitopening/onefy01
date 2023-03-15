<?php
session_start();
error_reporting(0);
$page_name = 'auth';
require_once(dirname(__FILE__) . '/common.inc.php');

$redis_config = GetConfig('redis');
$cache_prefix = $redis_config['prefix'];
if ($member_id > 0) {
    $cache_prefix = $redis_config['prefix'] . WEBHOSTID . '_';
    //页面刷新增加累计次数
    $user_verify_count_key = $cache_prefix . 'user_verify_count_' . $member_id;
    if ($Redis->exists($user_verify_count_key) == false) {
        $Redis->setex($user_verify_count_key, 86400, 0);
    }
    $user_verify_count = $Redis->incr($user_verify_count_key);
    if ($user_verify_count > 50 && $memberInfo['account_open'] == 1) {
        //刷新验证码页面50次后封掉用户账户
        $data = array(
            'disabled' => 1,
            'disabled_count' => array('disabled_count + 1'),
            'account_open' => 0,
            'disabled_time' => time()
        );
        $member_query->table('member')->where('id = ' . $member_id)->save($data);
        header('Location:/auth.php');
        exit();
    }

    if ($_POST['action'] == 'check' && $memberInfo['account_open'] == 1) {
        //检测验证码
        require_once(dirname(__FILE__) . '/common/lib/classes/Verify.class.php');
        $Verify = new Verify();
        $verify_code = trim($_POST['verify_code']);
        $check_result = $Verify->check($verify_code);
        if ($check_result == false) {
            ajax_return(array(
                'error' => 1,
                'msg' => '验证码错误'
            ));
        }

        //验证成功后的返回链接
        $back_url = trim($_POST['back_url']);
        $domain = explode('.', $_SERVER['HTTP_HOST']);
        if (empty($back_url) || $page_name == 'auth') {
            $back_url = '/';
        }

        //更新用户数据
        $data = array(
            'disabled' => 0
        );
        $member_query->table('member')->where('id = ' . $member_id)->save($data);

        //删除缓存
        //hash,内部key是否需要输入验证码，是否禁止用户账户等
        $user_data_key = $cache_prefix . 'user_data_' . $member_id;
        //用户单位时间内访问次数，设置过期时间
        $user_count_key = $cache_prefix . 'user_count_' . $member_id;
        $user_day_count_key = $cache_prefix . 'user_day_count_' . $member_id;
        //无序集合，用户单位时间内访问的城市数量
        $user_city_count_key = $cache_prefix . 'user_city_' . $member_id;
        //用户单位时间内访问城市数量过期时间
        $user_city_count_expire_key = $cache_prefix . 'user_city_expire_' . $member_id;
        $Redis->del($user_data_key);
        $Redis->del($user_count_key);
        $Redis->del($user_day_count_key);
        $Redis->del($user_city_count_key);
        $Redis->del($user_city_count_expire_key);
        $Redis->del($user_verify_count_key);
        ajax_return(array(
            'error' => 0,
            'msg' => '验证成功，访问正在解锁中，请稍等...',
            'url' => $back_url,
        ));
        exit();
    }
} else {
    if (!extension_loaded('redis')) {
        exit('Redis extension not loaded');
    }

    $redis_config = GetConfig('redis');
    $Redis = new Redis();
    $connect_result = $Redis->connect($redis_config['host'], $redis_config['port']);
    if (!$connect_result) {
        exit('Redis Connect Error');
    }

    if ($redis_config['password']) {
        $result = $Redis->auth($redis_config['password']);
        if ($result == false) {
            exit('Redis Connect Error');
        }
    }
    //IP行为检测
    $ip = getclientip(1);
    $ip_black_cache_key = $cache_prefix . 'black_' . $ip;
    $ip_verify_count_cache_key = $cache_prefix . 'verify_' . $ip;
    $now_time = time();
    $ip_black_denied = $Redis->get($ip_black_cache_key);

    if ($Redis->exists($ip_verify_count_cache_key) == false) {
        $Redis->setex($user_verify_count_key, 86400, 0);
    }
    $ip_verify_count = $Redis->incr($user_verify_count_key);

    /*if ($ip_verify_count > 50) {
        //超过50次连接验证，直接禁止访问
    }*/

    /*if ($_POST['action'] == 'check') {
        $IPBlack = new IpBlackListModel();

        //判断当前IP是否已解封
        $condition = 'ip = ' . $ip;
        $ip_black_info = $IPBlack->field('id, ip, allow_time, enable')->where($condition)->find();
        if (($ip_black_info['allow_time'] > 0 && $ip_black_info['allow_time'] < time()) || $ip_black_info['enable'] == 0) {
            //从黑名单中取消当前IP
            $data = array(
                'enable' => 0,
                'update_time' => $now_time
            );
            $IPBlack->where($condition)->save($data);
            //清除缓存
            $Redis->del($ip_black_denied_cache_key);
            $Redis->del($ip_verify_count_cache_key);
            $this->ajaxReturn(array(
                'error' => 0,
                'msg' => '验证成功，访问正在解锁中，请稍等...',
                'url' => $back_url,
            ));
        }

        //从黑名单中取消当前IP
        $data = array(
            'enable' => 0,
            'update_time' => $now_time,
            'allow_time' => $now_time - 1
        );
        $result = $IPBlack->where($condition)->save($data);

        if ($result === false) {
            $this->ajaxReturn(array(
                'error' => 1,
                'msg' => '系统忙，稍后请重试'
            ));
        }

        //清除缓存
        $Redis->del($ip_black_denied_cache_key);
        $Redis->del($ip_verify_count_cache_key);
        $this->ajaxReturn(array(
            'error' => 0,
            'msg' => '验证成功，访问正在解锁中，请稍等...',
            'url' => $back_url,
        ));

        exit();
    }*/
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>访问验证 - <?php echo $page->titlec;?></title>
    <meta name="description" content="{$website_name}" />
    <meta name="keywords" content="{$website_name}" />
    <link rel="stylesheet" href="/css/auth.css?v=<?php echo $webConfig['static_version'];?>" />
    <script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>
<body>
<header class="header-box">
    <div class="main wrap clear-fix">
        <div class="site-name"><a href="#"><?php echo $page->titlec;?></a></div>
    </div>
</header>
<div class="section-box">
    <div class="message wrap"><?php
        if ($memberInfo['account_open'] == 1) {
            echo '为了防止软件恶意攻击，请您输入验证码！';
        } else {
            echo '为了防止软件恶意攻击，请您使用微信扫码二维码进行验证！';
        }
?></div>
    <?php
    if ($memberInfo['account_open'] == 1) {
    ?>
    <div class="main">
        <div class="form-box">
            <form name="verify_form" id="verify_form" action="?action=check" method="post" onsubmit="return false;">
                <input type="hidden" name="back_url" id="back_url" value="<?php echo $_SERVER['HTTP_REFERER'];?>">
                <div class="item clear-fix">
                    <div class="control-wrap">
                        <img src="/verify.php" id="verify-image" class="refresh-image" />
                        <span id="refresh-btn" class="refresh-image">看不清？点击更换</span>
                    </div>
                </div>
                <div class="item clear-fix">
                    <div class="control-wrap">
                        <label><input type="text" name="verify_code" id="verify_code" value="" maxlength="4" autocomplete="off" placeholder="请输入验证码"></label>
                    </div>
                </div>
                <div class="item clear-fix">
                    <div class="control-wrap">
                        <div class="submit-btn btn">验证</div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
    } else {
    ?>
    <div class="main">
        <div class="get-qrcode-btn">点击获取二维码</div>
        <div class="wechat-box">
            <div id="wechat-qrcode-box" class="wechat-qrcode-box">
                <div class="qrcode-container" id="qrcode-container">
                    <div class="message"></div>
                </div>
                <div class="scan-tips">
                    打开“微信”扫一扫验证<span class="small-tips">(需先关注公众号)</span>
                </div>
                <script type="text/javascript" src="/js/wechat.js?v=<?php echo $webConfig['static_version'];?>"></script>
                <script type="text/javascript">
                    var Wechat = Wechat.create({
                        type: 2,
                        memberDB: 1,
                        callback: function (data) {
                            if (data.error == 0) {
                                layer.msg('验证成功', {
                                    icon: 1,
                                    time: 2000
                                }, function(){
                                    window.location.href = '/';
                                });
                            } else {
                                layer.alert('系统错误，请联系管理员');
                            }
                        }
                    });

                    $('.get-qrcode-btn').click(function () {
                        $(this).hide();
                        $('.wechat-box').show();
                        Wechat.showQrcode();
                    });
                </script>
            </div>
        </div>
    </div>
    <?php
    }
    if (($member_id > 0 && $memberInfo['account_open'] == 1) || $member_id == 0) {
        ?>
        <div class="message wrap tips">
            如遇到任何问题可与客户经理联系。<br/>
            邮箱：002@01fy.cn<br/>
            QQ：200881713
        </div>
        <?php
    }
    if ($member_id > 0 && $memberInfo['account_open'] != 1) {
    ?>
        <div class="message wrap tips" style="font-size: 18px;">
            由于您的账号访问异常，已被禁用！如有疑问请<a href="suggest.html" style="text-decoration: underline;color:red;">与我们联系</a>。
        </div>
    <?php
    } elseif ($member_id == 0 && $ip_verify_count > 50) {
        ?>
        <div class="message wrap tips" style="font-size: 18px;">
            由于您的IP访问异常，已被禁用！如有疑问请<a href="suggest.html" style="text-decoration: underline;color:red;">与我们联系</a>。
        </div>
    <?php
    }
    ?>
</div>
<footer class="footer-box">
    <div class="main">

    </div>
</footer>
<?php
if ($memberInfo['account_open'] == 1) {
?>
<script>
    $(document).ready(function (e) {
        $('#verify_code').focus();
        $('.refresh-image').click(function () {
            RefreshImage();
        });

        $('.submit-btn').click(function () {
            VerifySubmit();
        });
        $('#verify_code').keyup(function (e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode == 13) {
                VerifySubmit();
            }
        });
    });

    function VerifySubmit() {
        var verifyCode = $('#verify_code').val();
        if (verifyCode.length < 4) {
            layer.msg('验证码错误');
            return false;
        }
        var backUrl = $('#back_url').val();
        $.post('auth.php', {
            action: 'check',
            verify_code: verifyCode,
            back_url: backUrl,
            r: $('#r').val(),
            t: new Date().getTime()
        }, function (data) {
            if (data.error == 0) {
                layer.msg(data.msg);
                window.location.href = data.url;
                /*layer.msg(data.msg, {time: 5000});
                 setTimeout(function () {
                 window.location.href = data.url;
                 }, 5000);*/
            } else {
                RefreshImage();
                layer.msg(data.msg);
            }
        }, 'json');
        return false;
    }

    function RefreshImage() {
        var imageUrl = $('#verify-image').attr('src');
        var src = imageUrl.split('?');
        $('#verify-image').attr('src', src[0] + '?' + new Date().getTime());
        $('#verify_code').val('').focus();
    }
</script>
<?php
}
?>
</body>
</html>
