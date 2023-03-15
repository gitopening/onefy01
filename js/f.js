// JavaScript Document
layer.config({
    btnAlign: 'c' //按钮居中显示
});
function showsubmitweituo(shopid){
    asyncbox.open({
	　　　url : '/weituo.php?id=' + shopid + '&t=' + new Date().getSeconds(),
	　　　width : 500,
	　　　height : 400,
        scrolling: 'no',
	　　　title :'我要委托'	
	　});
    return false;
}
function showlevemsg(shopid){
    asyncbox.open({
	　　　url : '/msg.php?id=' + shopid + '&t=' + new Date().getSeconds(),
	　　　width : 500,
	　　　height : 400,
        scrolling: 'no',
	　　　title :'我要留言'	
	　});
return false;
}
function getarea(id){
    $.get('/ajax/index.php?action=getsubregion&id=' + id +'&t=' + new Date().getSeconds(), null, function(data){
        var tmp = '<option value="">请选择</option>';
        var count = data.length;
        for(var i=0; i< count; i++){
            tmp += '<option value="' + data[i].region_id + '">' + data[i].region_name + '</option>';
        }
        $('#smallzone').html(tmp);
    }, 'json');
}
function getarealettergroup(id){
    id = parseInt(id);
    if(id>0){
        $.get('/ajax/index.php?action=getarealettergroup&id=' + id +'&t=' + new Date().getSeconds(), null, function(data){
            $('#smallzone').html(data);
        });
    }else{
        $('#smallzone').html('<option value="0" selected="selected">请选择</option>');
    }
}

/*function GetSubRegion(obj){
    id = parseInt($(obj).val());
    var tmp = '<option value="0" selected="selected">请选择</option>';
    if (isNaN(id) || id == 0) {
        $(obj).parent().nextAll().find('select').html(tmp).addClass('Validform_error').last().removeClass('Validform_error');
        $(obj).addClass('Validform_error');
        return false;
    }
    $(obj).removeClass('Validform_error');
    $.get('/ajax/index.php?action=getsubregion&id=' + id +'&t=' + new Date().getTime(), null, function(data){
        var count = data.length;
        for(var i=0; i< count; i++){
            tmp += '<option value="' + data[i].region_id + '">' + data[i].region_name + '</option>';
        }
        $(obj).parent().nextAll().find('select').html(tmp);
    }, 'json');
}*/

function checkweituo(){
    var uid=$('#uid').val();
    var bigzone=$('#bigzone').val();
    var smallzone=$('#smallzone').val();
    var huxing=$('#huxing').val();
    var symj=$('#symj').val();
    var louceng=$('#louceng').val();
    var endlouceng=$('#endlouceng').val();
    var uname=$('#nikename').val();
    var tel=$('#weituotel').val();
    var note=$('#weituonote').val();
    var valid = $('#weituovalid').val();

    if(checkRate(uid)==false){
        alert('参数不正确，请联系管理员');
        return false;
    }
    if(bigzone=='' || bigzone==0){
        alert('请选择所在地区');
        return false;
    }
    if(checkRate(huxing)==false){
        alert('户型只能输入整数，请重新输入');
        return false;
    }
    if(checkRate(symj)==false){
        alert('面积为数字，请输入数字');
        return false;
    }
    if(checkRate(louceng)==false){
        alert('楼层为数字，请输入整数');
        return false;
    }
    if(checkRate(endlouceng)==false){
        alert('总楼层为数字，请输入整数');
        return false;
    }
    if(uname.length<2){
        alert('请输入您的姓名');
        return false;
    }
    if(tel.length<7 || checkRate(tel)==false){
        alert('请输入正确的联系电话');
        return false;
    }
    if(valid.length<4){
        alert('请输入正确的验证码');
        return false;
    }
    return true;
}
//检测是不是数字
function checkRate(str){
    var re = /^[1-9]+[0-9]*]*$/; //判断字符串是否为数字 //判断正整数 /^[1-9]+[0-9]*]*$/
    if (!re.test(str)){
        return false;
    }else{
        return true;
    }
}

function checkmsg(){
    uid=$('#uid').val();
    uname=$('#uname').val();
    email=$('#email').val();
    tel=$('#tel').val();
    note=$('#note').val();
    if(checkRate(uid)==false){
        asyncbox.tips('参数不正确，请联系管理员');
        return false;
    }
    if(uname.length<2){
        alert('请输入您的姓名');
        return false;
    }
    if(tel.length<7 || checkRate(tel)==false){
        alert('请输入正确的联系电话');
        return false;
    }
    if(checkemail(email)==false){
        alert('请输入正确的邮箱');
        return false;
    }
    if(note.length<10){
        alert('留言内容不能少于10个字符');
        return false;
    }
    return true;
}
function checkmsg1(){
    uid=$('#uid1').val();
    uname=$('#uname1').val();
    email=$('#email1').val();
    tel=$('#tel1').val();
    note=$('#note1').val();
    if(checkRate(uid)==false){
        alert('参数不正确，请联系管理员');
        return false;
    }
    if(uname.length<2){
        alert('请输入您的姓名');
        return false;
    }
    if(tel.length<7 || checkRate(tel)==false){
        alert('请输入正确的联系电话');
        return false;
    }
    if(checkemail(email)==false){
        alert('请输入正确的邮箱');
        return false;
    }
    if(note.length<10){
        alert('留言内容不能少于10个字符');
        return false;
    }
    return true;
}
function checkemail(str){
    var reyx= /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
    if(reyx.test(str)){
        return true;
    }else{
        return false;
    }
}
function showlogin(){
    asyncbox.open({
	　　　url : '/ajaxlogin.php?t=' + new Date().getSeconds(),
	　　　width : 450,
	　　　height : 300,
        scrolling: 'no',
	　　　title :'用户登录'	
	　});
}
function checkloginform(){
    username=$('#username').val();
    passwd=$('#passwd').val();
    if(username==''){
        alert('请输入用户名');
        return false;
    }
    if(passwd==''){
        alert('密码不能为空');
        return false;
    }
    return true;
}
function checkregform(){
    var username=$('#username').val();
    var passwd=$('#passwd').val();
    var passwd2=$('#passwd2').val();
    var email=$('#email').val();
    var realname=$('#realname').val();
    var cityarea_id=$('#cityarea_id').val();
    var cityarea2_id=$('#smallzone').val();
    var outlet_first=$('#outlet_first').val();	//所属公司门店
    var outlet_last=$('#outlet_last').val();
    var vaild=$('#vaild').val();

    if(checkusername(username) && checkpwd(passwd) && checkpwd2(passwd,passwd2) && checkemail(email) && checkrealname(realname) && checkcity(cityarea_id) && checkcity(cityarea2_id) && checkcompany(outlet_first) && checkoutlet(outlet_last) && checkvaild(vaild)){
        $('#registerform').submit();
    }else{
        return false;
    }
}
function checkusername(username){
    if(checkMobile(username)==false){
        $('#errMsg_username').html(setmsg('手机号码不正确',false));
        return false;
    }else{
        if(ckname(username)==false){
            $('#errMsg_username').html(setmsg('该手机号已注册',false));
            return false;
        }else{
            $('#errMsg_username').html(setmsg('',true));
            return true;
        }
    }
}
function checkpwd(passwd){
    if(passwd.length<6){
        $('#errMsg_passwd').html(setmsg('密码为6-20位',false));
        return false;
    }else{
        $('#errMsg_passwd').html(setmsg('',true));
        return true;
    }
}
function checkpwd2(passwd,passwd2){
    if(passwd2!=passwd || passwd2.length<6){
        $('#errMsg_passwd2').html(setmsg('重复密码输入错误',false));
        return false;
    }else{
        $('#errMsg_passwd2').html(setmsg('',true));
        return true;
    }
}
function checkemail(email){
    if(is_email(email)==false){
        $('#errMsg_email').html(setmsg('邮箱格式不正确',false));
        return false;
    }else{
        $('#errMsg_email').html(setmsg('',true));
        return true;
    }
}
function checkrealname(realname){
    if(realname.length<2){
        $('#errMsg_realname').html(setmsg('请输入真实姓名',false));
        return false;
    }else{
        $('#errMsg_realname').html(setmsg('',true));
        return true;
    }
}
function checkcity(cityareaid){
    if(cityareaid=='0' || cityareaid==''){
        $('#errMsg_cityarea_id').html(setmsg('请选择所在地区',false));
        return false;
    }else{
        $('#errMsg_cityarea_id').html(setmsg('',true));
        return true;
    }
}
function checkcompany(outlet_first){
    if(outlet_first.length<=0){
        $('#errMsg_outlet_first').html(setmsg('请输入公司名',false));
        return false;
    }else{
        $('#errMsg_outlet_first').html(setmsg('',true));
        return true;
    }
}
function checkoutlet(outlet_last){
    if(outlet_last.length<=0){
        $('#errMsg_outlet_last').html(setmsg('请输入所在门店',false));
        return false;
    }else{
        $('#errMsg_outlet_last').html(setmsg('',true));
        return true;
    }
}
function checkvaild(vaild){
    if(vaild.length<4){
        $('#errMsg_vaild').html(setmsg('请输入验证码',false));
        return false;
    }else{
        $('#errMsg_vaild').html(setmsg('',true));
        return true;
    }
}
//检测用户名是否可用
function ckname(inp) {
    $.ajax({type:"GET", url:"ajax.php?r="+Math.random()+'&action=unique&username='+inp, dataType:"text",async:false,success:function (msg){
        r = msg;
    }});
    if (r==0) {
        return false;
    } else {
        return true;
    }
}
//检察手机格式是否正确
function checkMobile(s){
    var regu =/^[1][0-9][0-9]{9}$/;
    var re = new RegExp(regu);
    if (re.test(s)) {
        return true;
    }else{
        return false;
    }
}
//检测邮箱是否正确
function is_email(s){
    var regu =/^[-_A-Za-z0-9]+@([_A-Za-z0-9]+\.)+[A-Za-z0-9]{2,3}$/;
    var re = new RegExp(regu);
    if (re.test(s)) {
        return true;
    }else{
        return false;
    }
}
function setmsg(str,type){
    if(type==true){
        return '<div class="rightmsg">' + str + '</div>';
    }else{
        return '<div class="errormsg">' + str + '</div>';
    }
}
function checkfilterform(){
    sprice=$('#sprice').val();
    eprice=$('#eprice').val();
    smj=$('#smj').val();
    emj=$('#emj').val();
    if(sprice!='' && checkRate(sprice)==false){
        alert('起始价格必需为数字');
        return false;
    }
    if(eprice!='' && checkRate(eprice)==false){
        alert('结束价格必需为数字');
        return false;
    }
    if(smj!='' && checkRate(smj)==false){
        alert('起始面积必需为数字');
        return false;
    }
    if(emj!='' && checkRate(emj)==false){
        alert('结束面积必需为数字');
        return false;
    }
    if((sprice!='' || eprice!='') && parseInt(sprice)>=parseInt(eprice)){
        alert('结束价格必需大于起始价格');
        return false;
    }
    if((emj!='' || emj!='') && parseInt(smj)>=parseInt(emj)){
        alert('结束面积必需大于起始面积');
        return false;
    }
    return true;
}
function updatephone(houseid){
    phone=$('#phone_' + houseid).val();
    if(phone!=''){
        if(phone.length!=8 && phone.length!=11){
            $('#tips_' + houseid).html('手机格式错误');
            $('#tips_' + houseid).removeClass('tipsright');
            $('#tips_' + houseid).addClass('tipserror');
            $('#phone_' + houseid).addClass('error');
            return false;
        }else{
            $.get('/admin/house/updatephone.php?action=update&id=' + houseid + '&phone=' + phone,null,function(data){
                if(data==1){
                    $('#tips_' + houseid).html('修改成功');
                    $('#tips_' + houseid).addClass('tipsright');
                    $('#phone_' + houseid).removeClass('error');
                    return true;
                }else{
                    $('#tips_' + houseid).html('修改出错，请重试');
                    $('#tips_' + houseid).addClass('tipserror');
                    $('#phone_' + houseid).addClass('error');
                    return false;
                }
            });
        }
    }
}
function updatesalephone(houseid){
    phone=$('#phone_' + houseid).val();
    if(phone!=''){
        if(phone.length!=8 && phone.length!=11){
            $('#tips_' + houseid).html('手机格式错误');
            $('#tips_' + houseid).addClass('tipserror');
            $('#phone_' + houseid).addClass('error');
            return false;
        }else{
            $.get('/admin/house/updatesalephone.php?action=update&id=' + houseid + '&phone=' + phone,null,function(data){
                if(data==1){
                    $('#tips_' + houseid).html('修改成功');
                    $('#tips_' + houseid).addClass('tipsright');
                    $('#phone_' + houseid).removeClass('error');
                    return true;
                }else{
                    $('#tips_' + houseid).html('修改出错，请重试');
                    $('#tips_' + houseid).addClass('tipserror');
                    $('#phone_' + houseid).addClass('error');
                    return false;
                }
            });
        }
    }
}

function ShowEditMobileBox(){
    var mark = $('#box-mark');
    var doc_width = $(document).width();
    mark.css('width', doc_width + 'px');
    mark.css('height', $(document).height() + 'px');
    mark.css('opacity', 0.5);
    mark.show();
    var boxContainer = $('#box-container');
    var leftpos = (doc_width-boxContainer.outerWidth())/2;
    var top = (($(window).height() - boxContainer.height())/2 + $(document).scrollTop() - 150);
    boxContainer.css('top', top + 'px');
    boxContainer.css('left',leftpos + 'px');
    boxContainer.show();
}

function CloseDialog(){
    $('#box-mark').hide();
    $('#box-container').hide();
}

function GetUploadParams(uploadType, originFileName) {
    var now = Date.parse(new Date()) / 1000;
    var jsonData = {
        'error': 1,
        'msg': '文件' + originFileName + '上传失败'
    };
    $.ajax({
        type: 'post',
        url: '/ajax/get_oss_data.php',
        data: {
            upload_type: uploadType,
            origin_file_name: originFileName,
            t: new Date().getTime()
        },
        dataType: 'json',
        async: false,
        success: function(data){
            jsonData = data;
        }
    });
    return jsonData;
}

function SelectPromotionPrice() {
    //设置初始值
    SetPromotePrice();
    var itemNode = $('#promotion-price-list .item');
    itemNode.click(function () {
        var index = itemNode.index($(this));
        $('#promote_enable').attr('default-value-index', index);
        itemNode.removeClass('on');
        $(this).addClass('on');
        $('#promote').val($(this).attr('data-id'));
        $('.total-price').html($(this).attr('data-price'));
        $('.total-fee .price-number').html($(this).attr('data-price'));
        $('#promote_enable').prop('checked', true).closest('.container').addClass('active');
        GetPayMoney();
    });

    $('#promote_enable').click(function () {
        if ($(this).prop('checked') == true) {
            $(this).closest('.container').addClass('active');
            SetPromotePrice();
        } else {
            $(this).closest('.container').removeClass('active');
            itemNode.removeClass('on');
            $('#promote').val('0');
            $('.total-fee .price-number').html('0.00');
        }
    });
}

function SetPromotePrice() {
    var promoteEnableBtn = $('#promote_enable');
    if (promoteEnableBtn.prop('checked') == true) {
        var defaultValueIndex = parseInt(promoteEnableBtn.attr('default-value-index'));
        if (!isNaN(defaultValueIndex)) {
            $('#promotion-price-list .item').removeClass('on').eq(defaultValueIndex).addClass('on');
        }
    }
    var defaultPrice = $('#promotion-price-list .on').attr('data-price');
    var defaultPromote = $('#promotion-price-list .on').attr('data-id');
    if (isNaN(defaultPrice)) {
        defaultPrice = 0;
    }
    defaultPrice = Math.round(defaultPrice * 100) / 100;
    $('#promote').val(defaultPromote);
    $('.total-price').html(defaultPrice);
    $('.total-fee .price-number').html(defaultPrice);
    GetPayMoney();
}

function SetMaxUseScore() {
    var maxScore = parseInt($('#score').attr('user-score'));
    if (isNaN(maxScore)) {
        maxScore = 0;
    }
    var totalPrice = parseFloat($('#promotion-price-list .on').attr('data-price'));
    var percent = parseInt($('#score').attr('data-percent'));
    if (isNaN(percent) || percent == 0) {
        maxScore = 0;
    }
    if (maxScore > totalPrice * percent) {
        maxScore = totalPrice * percent
    }
    $('#score').attr('max-score', maxScore);
    $('.max-use-score').html(maxScore);
}

function SelectScoreOption() {
    //积分变化
    $('#use_score').click(function () {
        if ($(this).prop('checked') == true) {
            $('#score').prop('disabled', false).select();
        } else {
            $('#score').prop('disabled', true);
        }
        GetPayMoney();
    });
    /*$('#score').keyup(function () {
        var regex = /[^\d]/;
        if (regex.test($(this).val())) {
            //替换非数字字符
            $(this).val($(this).val().replace(regex, ''));
        }
        var score = parseInt($(this).val());
        if (isNaN(score)) {
            score = 0;
        }
        var maxScore = parseInt($(this).attr('max-score'));
        if (isNaN(maxScore)) {
            maxScore = 0;
        }
        if (score > maxScore) {
            score = maxScore;
            $(this).val(score);
        }
        var percent = parseInt($('#score').attr('data-percent'));
        var deductMoney = 0;
        if (isNaN(percent) || percent == 0) {
            deductMoney = 0;
        }
        deductMoney = parseFloat(score / percent);
        $('#deduct-money').html(deductMoney);
        GetPayMoney();
    });*/
}

function GetPayMoney() {
    var totalPrice = parseFloat($('#promotion-price-list .on').attr('data-price'));
    if (isNaN(totalPrice)) {
        totalPrice = 0;
    }
    var totalFee = totalPrice;
    var totalPriceInt = parseInt(totalPrice / 2);
    var percent = parseInt($('#use_score').attr('data-percent'));
    if (isNaN(percent)) {
        percent = 0;
    }
    if (percent > 0) {
        var userScore = parseInt($('#use_score').attr('user-score'));
        if (isNaN(userScore)) {
            userScore = 0;
        }
        var canUseScore = parseInt(userScore / percent) * percent;
        var maxNeedScore = totalPriceInt * percent;
        if (maxNeedScore > canUseScore) {
            maxNeedScore = canUseScore;
        }
        maxNeedScore = Math.round(maxNeedScore);
        var deductMoney = parseFloat(maxNeedScore / percent);
        deductMoney = Math.round(deductMoney * 100) / 100;
        if ($('#use_score').prop('checked') == true) {
            totalFee = totalPrice - deductMoney;
        }
        if (maxNeedScore >= percent) {
            $('.score-tips').html('您共有<span class="score-number">' + userScore + '</span>积分，可用<span class="score-number" id="can-use-score">' + maxNeedScore + '</span>积分，抵扣<span id="deduct-money">' + deductMoney + '</span>元');
            $('.use-score-box').show();
        } else {
            $('.score-tips').html('您共有<span class="score-number">' + userScore + '</span>积分，满<span class="score-number" id="can-use-score">' + percent + '</span>积分可用');
            $('.use-score-box').hide();
        }
        $('.score-box').show();
    } else {
        $('.score-box').hide();
    }
    totalFee = Math.round(totalFee * 100) / 100;
    $('.total-fee .price-number').html(totalFee);
}

function SelectPayment() {
    $('#pay-method .pay-item').click(function () {
        $('#pay-method .pay-item').removeClass('on');
        $(this).addClass('on');
        var payment = $(this).attr('data-value');
        $('#payment').val(payment);
    });
}

function SetPromotion(houseId, houseType) {
    layer.open({
        type: 2,
        title: '置顶推广',
        content: 'promotion.php?house_type=' + houseType + '&house_id=' + houseId,
        area: ['600px', '400px']
    });
}

function Promotion(houseId, houseType) {
    var loadingIndex = layer.load(2);
    $.post('promote.php', {
        house_id: houseId,
        house_type: houseType,
        t: new Date().getTime()
    }, function (data) {
        layer.close(loadingIndex);
        if (data.error == 1) {
            layer.alert(data.msg, {icon: 2});
            return false
        }
        layer.open({
            id: 'promote-dialog',
            type: 1,
            title: ['置顶推广', 'font-size: 18px; font-weight: bold; padding: 0 15px;'],
            content: data.content,
            area: ['780px', '620px'],
            success: function(layero, index){
                SelectPromotionPrice();
                SelectScoreOption();
                SelectPayment();
                $('#promote-submit-btn').click(function () {
                    /*if ($('#agree:checked').val() != 1) {
                        layer.alert('请先同意《房源推广服务协议》', {icon: 2});
                        return false;
                    }*/
                    SubmitPay();
                });
            }
        });
    }, 'json');
}

function HomePromotion(houseId, houseType, hasPicutre) {
    if (hasPicutre == 0) {
        layer.alert('当前房源没有封面图片，不能在首页推广', {icon: 2});
        return false;
    }
    var loadingIndex = layer.load(2);
    $.post('home_promote.php', {
        house_id: houseId,
        house_type: houseType,
        t: new Date().getTime()
    }, function (data) {
        layer.close(loadingIndex);
        if (data.error == 1) {
            layer.alert(data.msg, {icon: 2});
            return false
        }

        //判断页面高度，动态调整弹出框大小
        var dialogHeight = 840;
        if ($(window).height() < 800) {
            dialogHeight = $(window).height() - 50;
        }

        layer.open({
            id: 'promote-dialog',
            type: 1,
            title: ['首页推荐房源推广', 'font-size: 18px; font-weight: bold; padding: 0 15px;'],
            content: data.content,
            area: ['780px', dialogHeight + 'px'],
            success: function(layero, index){
                SelectPromotionPrice();
                SelectScoreOption();
                SelectPayment();
                $('#promote-submit-btn').click(function () {
                    /*if ($('#agree:checked').val() != 1) {
                        layer.alert('请先同意《房源推广服务协议》', {icon: 2});
                        return false;
                    }*/
                    SubmitPay();
                });
            }
        });
    }, 'json');
}

function BannerPromotion(houseId, houseType, hasPicutre) {
    if (hasPicutre == 0) {
        layer.alert('当前房源没有封面图片，不能在首页推广', {icon: 2});
        return false;
    }
    var loadingIndex = layer.load(2);
    $.post('banner_promote.php', {
        house_id: houseId,
        house_type: houseType,
        t: new Date().getTime()
    }, function (data) {
        layer.close(loadingIndex);
        if (data.error == 1) {
            layer.alert(data.msg, {icon: 2});
            return false
        }

        //判断页面高度，动态调整弹出框大小
        var dialogHeight = 940;
        if ($(window).height() < 900) {
            dialogHeight = $(window).height() - 50;
        }

        layer.open({
            id: 'promote-dialog',
            type: 1,
            title: ['首页封面推广', 'font-size: 18px; font-weight: bold; padding: 0 15px;'],
            content: data.content,
            area: ['780px', dialogHeight + 'px'],
            success: function(layero, index){
                SelectPromotionPrice();
                SelectScoreOption();
                SelectPayment();
                $('#promote-submit-btn').click(function () {
                    /*if ($('#agree:checked').val() != 1) {
                        layer.alert('请先同意《房源推广服务协议》', {icon: 2});
                        return false;
                    }*/
                    SubmitPay();
                });
            }
        });
    }, 'json');
}

function ShowAutoRefreshBox(houseId, houseType, buy) {
    if (buy == 1) {
        layer.alert('您是购买套餐会员，无需另行购买自动刷新，已经赠送您自动刷新功能，房源自动刷新中。', {icon: 2});
        return false;
    }
    var loadingIndex = layer.load(2);
    $.post('auto_refresh.php', {
        house_id: houseId,
        house_type: houseType,
        t: new Date().getTime()
    }, function (data) {
        layer.close(loadingIndex);
        if (data.error == 1) {
            layer.alert(data.msg, {icon: 2});
            return false
        }
        layer.open({
            id: 'refresh-dialog',
            type: 1,
            title: ['自动刷新', 'font-size: 18px; font-weight: bold; padding: 0 15px;'],
            content: data.content,
            area: ['720px', '600px'],
            success: function(layero, index){
                SelectPromotionPrice();
                SelectScoreOption();
                SelectPayment();
                $('#promote-submit-btn').click(function () {
                    /*if ($('#agree:checked').val() != 1) {
                        layer.alert('请先同意《房源推广服务协议》', {icon: 2});
                        return false;
                    }*/

                    SubmitPay();
                });
            }
        });
    }, 'json');
}
function SubmitPay() {
    var houseType = parseInt($('#house_type').val());
    var houseId = parseInt($('#house_id').val());

    if (isNaN(houseType) || isNaN(houseId)) {
        layer.alert('参数错误', {icon: 2});
        return false;
    }

    //显示提示窗口
    var tipsBox = layer.open({
        type: 1,
        title: '提示',
        closeBtn: false,
        area: '400px',
        content: $('#pay-status-box').html(),
        success: function (tipsBox, tipsIndex) {
            $('.pay-status-box .fail').click(function () {
                layer.close(tipsIndex);
            });
            $('.pay-status-box .success').click(function () {
                //window.location.href = '/order/return_url.php';
                var url = $(this).attr('url');
                if (url == undefined) {
                    window.location.reload();
                } else {
                    window.location.href = url;
                }
                layer.close(tipsIndex);
            });
        }
    });

    $('#pay_form').submit();
}

function SetLeftPromoteTime() {
    var time = new Date().getTime() / 1000;
    $('.promote-info').each(function (index, element) {
        var endTime = parseInt($(element).attr('end-time'));
        var pauseTime = parseInt($(element).attr('pause-time'));
        if (!isNaN(endTime) && !isNaN(pauseTime)) {
            if (pauseTime == 0 && endTime > time) {
                $(element).html('推广中，' + LeftTimeToText(endTime - time) + '后到期');
            } else if (pauseTime > 0 && endTime > pauseTime) {
                $(element).html('暂停推广(剩余推广时间：' + LeftTimeToText(endTime - pauseTime) + ')');
            } else {
                $(element).hide();
            }
        }
    });
    setTimeout(function () {
        SetLeftPromoteTime();
    }, 1000);
}

function SetLeftRefreshTime() {
    var time = new Date().getTime() / 1000;
    $('.refresh-info').each(function (index, element) {
        var endTime = parseInt($(element).attr('end-time'));
        if (!isNaN(endTime)) {
            if (endTime > time) {
                $(element).html(LeftTimeToText(endTime - time) + '后到期');
            } else {
                $(element).hide();
            }
        }
    });
    setTimeout(function () {
        SetLeftRefreshTime();
    }, 1000);
}

function ManageListSelectBox() {
    $('.select-input-box').each(function (index, selectElement) {
        $(selectElement).append('<div class="down-arrow"></div>');
        var value = $(selectElement).find('.hidden-input').val();
        $(selectElement).find('.item').each(function (itemIndex, item) {
            if ($(item).attr('data-value') == value) {
                $(selectElement).find('.box-title').html($(item).html());
                return true;
            }
        });
    });
    $('.select-input-box').hover(function () {
        $(this).find('.option-list').show();
    }, function () {
        $(this).find('.option-list').hide();
    });
    $('.select-input-box .item').click(function () {
        var optionList = $(this).closest('.option-list');
        optionList.hide();
        optionList.prevAll('.hidden-input').val($(this).attr('data-value'));
        optionList.prevAll('.box-title').html($(this).html());
        window.location.href = '?status=' + $(this).attr('data-value');
    });
}

function LeftTimeToText(leftTime) {
    if (leftTime > 86400) {
        var day = Math.floor(leftTime / 86400);
        var hour = Math.floor((leftTime - day * 86400) / 3600);
        return (day.toString() + '天' + hour.toString() + '小时');
    } else if (leftTime > 3600) {
        var hour = Math.floor(leftTime / 3600);
        var minute = Math.floor((leftTime - hour * 3600) / 60);
        return (hour.toString() + '小时' + minute.toString() + '分钟');
    } else if (leftTime > 60) {
        return (Math.floor(leftTime / 60).toString() + '分钟');
    } else {
        return (Math.floor(leftTime).toString() + '秒');
    }
}

function MemberDeleteHouseAll() {
    var ids = GetIdArray();
    if (ids.length == 0) {
        layer.alert('请选择要删除的房源', {icon: 2});
        return false;
    }
    AjaxRequest('', 'delete', ids, '你确定要删除选中的房源？删除后将不可恢复！');
}

function MemberRefreshAll() {
    var ids = GetIdArray();
    if (ids.length == 0) {
        layer.alert('请选择要刷新的房源', {icon: 2});
        return false;
    }
    AjaxRequest('', 'refresh', ids, '你确定要刷新选中的房源吗？');
}

function MemberRecoverHouseAll() {
    var ids = GetIdArray();
    if (ids.length == 0) {
        layer.alert('请选择要恢复上架的房源', {icon: 2});
        return false;
    }
    AjaxRequest('', 'recover', ids, '你确定要恢复上架选中的房源吗？');
}

function MemberDownHouseAll() {
    var ids = GetIdArray();
    if (ids.length == 0) {
        layer.alert('请选择要下架的房源', {icon: 2});
        return false;
    }
    AjaxRequest('', 'down', ids, '你确定要下架选中的房源吗？');
}

function DeleteAll() {
    var ids = GetIdArray();
    if (ids.length == 0) {
        layer.alert('请选择要删除的信息', {icon: 2});
        return false;
    }
    AjaxRequest('', 'delete', ids, '你确定要删除选中的信息？');
}

function GetIdArray() {
    var ids = new Array();
    var i = 0;
    $('input[type=checkbox]:checked').not('#checkBoxAll').each(function(index, element) {
        var id = parseInt($(element).val());
        if (!isNaN(id)) {
            ids[i] = id;
            i++;
        }
    });
    return ids;
}

function AjaxRequest(url, action, id, msg) {
    if (msg == undefined || msg == '') {
        msg = '您确定要执行当前操作吗？';
    }
    if (url.length == '') {
        url = window.location.href;
    }
    layer.confirm(msg, {
        icon: 3,
        btn: ['确定','取消'],
        area: [280, '']
    }, function(layerIndex) {
        layer.close(layerIndex);
        var loadingIndex = layer.load(2);
        $.post(url, {
            action: action,
            ids: id,
            t: new Date().getTime()
        }, function (data) {
            layer.close(loadingIndex);
            if (data.error == 0) {
                if (Array.isArray(id)) {
                    layer.alert(data.msg, {
                        title: '提示'
                        //icon: 1
                    }, function() {
                        window.location.reload();
                    });
                } else {
                    layer.msg(data.msg);
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                if (Array.isArray(id)) {
                    layer.alert(data.msg, {
                        title: '错误'
                        //icon: 2
                    });
                } else {
                    layer.msg(data.msg);
                }
            }
        }, 'json');
    });
}