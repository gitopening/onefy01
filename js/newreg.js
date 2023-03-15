/**
 * Created by Net on 15-4-3.
 */
var timer;
var time_seconds = 60;
var time_left_seconds = time_seconds;

$(function(){
    var validForm = $("#reg-form").Validform({
        tiptype: 2,
        ajaxPost: true,
        showAllError: true,
        callback: function (data) {
            if (data.status == 'y') {
                window.location.href = data.url;
                return true;
            } else {
                layer.msg(data.info, {icon: 2, time: 1500});
                return false;
            }
        }
    });
    $('.show-invite-box').click(function () {
        $('#invite-box').toggle();
    });
});

function showNormalRegisterQrcode() {
    WechatObj = Wechat.create({
        type: 2,
        memberDB: 1,
        callback: function (data) {
            if (data.error == 0) {
                layer.msg('注册成功', {
                    icon: 1,
                    time: 2000
                }, function(){
                    window.location.reload();
                });
            } else {
                layer.alert('系统错误，请联系管理员');
            }
        }
    });
    WechatObj.showQrcode();
}

function ShowMobileRegisterQrcode() {
    var tipsContainer = $('#sms-status .Validform_checktip');
    tipsContainer.removeClass('Validform_wrong').removeClass('Validform_right');

    var mobile = $('#mobile').val();
    if (IsMobile(mobile) == false) {
        //layer.msg($('#mobile').attr('errormsg'), {icon: 2, time: 1500});
        tipsContainer.addClass('Validform_wrong').html($('#mobile').attr('errormsg'));
        return false;
    }

    tipsContainer.addClass('Validform_right').html('');
    var index = layer.open({
        type: 1,
        title: '<div style="text-align:center;font-size: 17px;padding:0;">微信扫码验证，获取手机动态码</div>',
        content: '<div id="wechat-mobile-qrcode-box" class="wechat-box"><div class="wechat-qrcode-box"><div class="qrcode-container" id="qrcode-container"><div class="message"></div></div><div class="scan-tips">打开“微信”扫一扫验证<span class="small-tips">(需先关注公众号)</span></div></div></div>',
        success: function(layero, index){
            WechatObj = Wechat.create({
                container: '#wechat-mobile-qrcode-box',
                type: 4,
                data: mobile,
                memberDB: 1,
                callback: function (data) {
                    layer.closeAll();
                    if (data.error == 0) {
                        time_left_seconds = time_seconds;
                        $('.sendauthcode').unbind();
                        $('.sendauthcode').prop('disabled', true);
                        tipsContainer.removeClass('Validform_wrong').addClass('Validform_right').html('');
                        $('.sendauthcode').val(time_seconds + ' 秒后重发');
                        timer = setInterval(function () {
                            left_time();
                        }, 1000);
                    } else {
                        //layer.alert('系统错误，请联系管理员');
                        $('.sendauthcode').prop('disabled', false);
                        $('.sendauthcode').val('获取动态码');
                        $('.sendauthcode').bind('click', function(){ShowMobileRegisterQrcode();});
                        tipsContainer.removeClass('Validform_right').addClass('Validform_wrong').html(data.msg);
                        //layer.msg(data.msg, {icon: 2, time: 1500});
                    }
                }
            });
            WechatObj.showQrcode()
        },
        cancel: function(index, layero){
            WechatObj.destruct();
            delete WechatObj;
            $('#need_qrcode').val(1);
            return true;
        }
    });
}

function getarea(id){
    id = parseInt(id);
    if(id>0){
        $.ajaxSettings.async = true;
        $.get('/ajax/index.php?action=getsubregion&id=' + id +'&t=' + new Date().getSeconds(), null, function(data){
            var tmp = '<option value="">请选择</option>';
            var count = data.length;
            for(var i=0; i< count; i++){
                tmp += '<option value="' + data[i].region_id + '">' + data[i].region_name + '</option>';
            }
            $('#smallzone').html(tmp);
        }, 'json');
    }else{
        $('#smallzone').html('<option value="">请选择</option>');
    }
}

function GetCityWebsite(provinceID) {
    $.getJSON('/ajax/index.php', {
        action: 'get_province_website',
        province_id: provinceID,
        t: new Date().getTime()
    }, function (data) {
        var tmp = '<option value="" selected="selected">请选择</option>';
        $(data).each(function (index, item) {
            tmp += '<option value="' + item.id + '">' + item.city_name + '</option>';
        });
        $('#smallzone').html(tmp);
    });
}
function showbrokerform(){
    $('#utab').removeClass('on');
    $('#memberform').hide();
    $('#registerform').show();
    $('#broker-reg-btn').css('color', '#000');
    $('#member-reg-btn').css('color', '#999');
}

function showmemberform(){
    $('#utab').addClass('on');
    $('#registerform').hide();
    $('#memberform').show();
    $('#broker-reg-btn').css('color', '#999');
    $('#member-reg-btn').css('color', '#000');
}
function sendsms(){
    var tipsContainer = $('#sms-status .Validform_checktip');
    var mobile = $('#mobile').val();
    if (IsMobile(mobile) == false) {
        //layer.msg($('#mobile').attr('errormsg'), {icon: 2, time: 1500});
        tipsContainer.removeClass('Validform_right').addClass('Validform_wrong').html($('#mobile').attr('errormsg'));
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

    var tipsContainer = $('#sms-status .Validform_checktip');
    var mobile = $('#mobile').val();
    if (IsMobile(mobile) == false) {
        //layer.msg($('#mobile').attr('errormsg'), {icon: 2, time: 1500});
        tipsContainer.removeClass('Validform_right').addClass('Validform_wrong').html($('#mobile').attr('errormsg'));
        return false;
    }

    time_left_seconds = time_seconds;
    $('.sendauthcode').unbind();
    $('.sendauthcode').prop('disabled', true);
    $.ajaxSettings.async = true;
    var layerLoadingIndex = layer.load(2);
    if (verifyCode == undefined || verifyCode == '') {
        verifyCode = getNVCVal();
    }
    $.get('/ajax/index.php', {
        action: 'sendregsms',
        mobile: mobile,
        verify_code: verifyCode,
        t: new Date().getTime()
    }, function(data){
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
        timer = setInterval(function(){
            left_time();
        }, 1000);
    }, 'json');
}

function left_time(){
    time_left_seconds--;
    if(time_left_seconds <= 0){
        time_left_seconds = time_seconds;
        clearInterval(timer);
        $('.sendauthcode').removeAttr('disabled');
        $('.sendauthcode').val('获取动态码');
        $('.sendauthcode').bind('click', function(){ShowMobileRegisterQrcode();});
    }else{
        $('.sendauthcode').val(time_left_seconds + ' 秒后再发');
    }
}

function GoBack(regType){
    if (regType == 1) {
        $('#registerform .auth-mobile').hide();
        $('#registerform .reg-first').show();
        $('#step').val('0');
    } else{
        $('#memberform .auth-mobile').hide();
        $('#memberform .reg-first').show();
        $('#member_step').val('0');
    }
    return false;
}