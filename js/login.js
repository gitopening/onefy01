/**
 * 官方网站 http://www.hictech.cn
 * Copyright (c) 2017 河北海聪科技有限公司 All rights reserved.
 * Licensed (http://www.hictech.cn/licenses)
 * Author : net <geow@qq.com>
 */
var timer;
var time_seconds = 60;
var time_left_seconds = time_seconds;
var is_need_validate = false;

$(function () {
    var loginForm = $("#loginform").Validform({
        tiptype: 2,
        ajaxPost: true,
        showAllError: true,
        datatype: {
            "valid": function (gets, obj, curform, regxp) {
                if (is_need_validate == true) {
                    var objValue = $(obj).val();
                    if (objValue.length != 4) {
                        return $(obj).attr('errormsg');
                    }
                }
                return true;
            }
        },
        callback: function (data) {
            if (data.status == 'y') {
                if (jumpUrl == '') {
                    if (self != top) {
                        parent.location.reload();
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (self != top) {
                        parent.location.href = jumpUrl;
                    } else {
                        window.location.href = jumpUrl;
                    }
                }
                return true;
            } else {
                if (data.error == 1) {
                    $('#username').focus();
                }
                if (data.error == 2) {
                    $('#passwd').focus();
                }
                if (data.error == 3) {
                    //刷新验证码
                    RefreshValidateCode();
                }
                if (data.validate == 1) {
                    is_need_validate = true;
                    $('#valid-code-container').show();
                } else {
                    is_need_validate = false;
                    $('#valid-code-container').hide();
                }
                layer.msg(data.info, {icon: 2, time: 1500});
                return false;
            }
        }
    });
    var mobileForm = $("#mobile-login-form").Validform({
        tiptype: 2,
        ajaxPost: true,
        showAllError: true,
        callback: function (data) {
            if (data.status == 'y') {
                if (jumpUrl == '') {
                    if (self != top) {
                        parent.location.reload();
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (self != top) {
                        parent.location.href = jumpUrl;
                    } else {
                        window.location.href = jumpUrl;
                    }
                }
                return true;
            } else {
                if (data.error == 1) {
                    $('#mobile').focus();
                }
                if (data.error == 2) {
                    $('#valid-code').focus();
                }
                layer.msg(data.info, {icon: 2, time: 1500});
                return false;
            }
        }
    });
    $('#login-type-select .item').click(function () {
        var itemNode = $('#login-type-select .item');
        itemNode.removeClass('on');
        $(this).addClass('on');
        var index = itemNode.index($(this));
        $('.login-box').hide();
        $('.login-box').eq(index).show();

        //记录最后一次使用的登录方式
        var host = window.location.host;
        var domain = host.replace(host.split('.')[0] + '.', '');
        $.cookie('login_type_index', index, {domain: domain, path: '/', expires: 180});
    });

    var loginTypeIndex = $.cookie('login_type_index');
    if (loginTypeIndex != undefined) {
        $('#login-type-select .item').removeClass('on');
        $('.login-box').hide();

        $('#login-type-select .item').eq(loginTypeIndex).addClass('on');
        $('.login-box').eq(loginTypeIndex).show();
    }

    $('#username').blur(function () {
        var username = $(this).val();
        if (username.length == 0) {
            is_need_validate = false;
            return false;
        }
        $.post('login.php', {
            action: 'is_need_validate',
            username: username,
            t: new Date().getTime()
        }, function (data) {
            var tipsContainer = $('#username-status');
            if (data.error == 1) {
                tipsContainer.removeClass('Validform_right').addClass('Validform_wrong').html(data.msg);
                is_need_validate = false;
                return false;
            }
            tipsContainer.removeClass('Validform_right').removeClass('Validform_wrong').html('');
            if (data.validate == 1) {
                is_need_validate = true;
                $('#valid-code-container').show();
            } else {
                is_need_validate = false;
                $('#valid-code-container').hide();
            }
        }, 'json');
    });

    $('#username').keyup(function () {
        $('#mobile').val($('#username').val());
    });

    $('#mobile').keyup(function () {
        $('#username').val($('#mobile').val());
    });
});

function sendsms() {
    var tipsContainer = $('#sms-status .Validform_checktip');
    tipsContainer.removeClass('Validform_wrong').removeClass('Validform_right');

    var mobile = $('#mobile').val();
    if (IsMobile(mobile) == false) {
        //layer.msg($('#mobile').attr('errormsg'), {icon: 2, time: 1500});
        tipsContainer.addClass('Validform_wrong').html($('#mobile').attr('errormsg'));
        return false;
    }

    //验证码输入窗口
    ShowVerifyCodeDialog();
}

function RequestSendSMS(verifyCode) {
    var tipsContainer = $('#sms-status .Validform_checktip');
    tipsContainer.removeClass('Validform_wrong').removeClass('Validform_right');

    var mobile = $('#mobile').val();
    if (IsMobile(mobile) == false) {
        //layer.msg($('#mobile').attr('errormsg'), {icon: 2, time: 1500});
        tipsContainer.addClass('Validform_wrong').html($('#mobile').attr('errormsg'));
        return false;
    }

    tipsContainer.addClass('Validform_right').html('');
    time_left_seconds = time_seconds;
    $('.sendauthcode').unbind();
    $('.sendauthcode').prop('disabled', true);
    $.ajaxSettings.async = true;
    if (verifyCode == undefined || verifyCode == '') {
        verifyCode = getNVCVal();
    }
    var layerLoadingIndex = layer.load(2);
    $.get('/ajax/index.php', {
        action: 'login_sms',
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
            tipsContainer.removeClass('Validform_right').addClass('Validform_wrong').html(data.msg);
            //layer.msg(data.msg, {icon: 2, time: 1500});
            return false;
        }
        tipsContainer.removeClass('Validform_wrong').addClass('Validform_right').html('');
        $('.sendauthcode').val(time_seconds + ' 秒后重发');
        timer = setInterval(function () {
            left_time();
        }, 1000);
    }, 'json');
}

function left_time() {
    time_left_seconds--;
    if (time_left_seconds <= 0) {
        time_left_seconds = time_seconds;
        clearInterval(timer);
        $('.sendauthcode').prop('disabled', false);
        $('.sendauthcode').val('发送验证码');
        $('.sendauthcode').bind('click', function(){RequestSendSMS('');});
    } else {
        $('.sendauthcode').val(time_left_seconds + ' 秒后再发');
    }
}