// JavaScript Document
$(document).ready(function(){
    $('#list .list-house-title a, #art_left .art_list a').click(function (e) {
        $(this).css('color', '#999');
    });
   //搜索框					
/*	$(".text01,.f_color").click(function(){
		$(".f_color").hide();
		$(".text01").focus();
	});
	$(".text01").blur(function(){
		if($(this).val()==""){
			$(".f_color").show();	
		}
	})
*/	//列表页背景切换
	//$('#list li').hover(function(){$(this).addClass('list_hover');},function(){$(this).removeClass('list_hover');});
    //按钮样式
    $('#searchform img').hover(function(){$(this).attr('src', '/images/lvsean.png');}, function(){$(this).attr('src', '/images/an.jpg');});
    $('#search #so img').hover(function(){$(this).attr('src', '/images/so_lvse.png');}, function(){$(this).attr('src', '/images/so.jpg');});
    $('.submit_btn').hover(function(){$(this).addClass('btn_hover');},function(){$(this).removeClass('btn_hover');});

	//顶部导航下拉
	$('#nav .menu').hover(function () {
		$(this).addClass('hover');
	}, function () {
		$(this).removeClass('hover');
	});

    $('.popup-nav').hover(function () {
        $(this).find('.popup-box').show();
    }, function () {
        $(this).find('.popup-box').hide();
    });

    $('.header-right-member').hover(function () {
        $(this).addClass('member-hover');
    }, function () {
        $(this).removeClass('member-hover');
    });

	$('.house-type-list .item').hover(function () {
		$(this).addClass('hover');
	}, function () {
		$(this).removeClass('hover');
	}).click(function () {
        //取消文本框获取光标时显示下拉菜单事件
        $('#keywords').unbind('focus');

        var dataUrl = $(this).attr('data-url');
		$('#search-url').val(dataUrl);
		$('.house-type .selected-type').html($(this).html()).attr('data-url', dataUrl);
		$('.house-type').removeClass('hover');
		//保存最后选项
		$.cookie('home_search_type', $(this).attr('data-type'), { expires: 365, path: '/' });
        //重新生成区域直达链接
        MakeRegionSearchLink(dataUrl);
        //光标定位到文本框中
        $('#keywords')[0].focus();
    });
	$('.house-type').hover(function () {
        SetHouseTypeOptionTop();
		$(this).addClass('hover');
	}, function () {
		$(this).removeClass('hover');
	});
	$('#home-search-btn').click(function () {
		HomeSearchHouse();
	});
	$('#keywords').keyup(function (e) {
		var keyCode = e.charCode || e.which || e.keyCode;
		if (keyCode == 13) {
			HomeSearchHouse();
		}
	}).focus(function () {
        SetHouseTypeOptionTop();
        $('.house-type').addClass('hover');
    }).click(function (e) {
        e.stopPropagation();
    });
    $('body').click(function () {
        $('.house-type').removeClass('hover');
        $('#sub-option-list').hide();
        $('#brand-apartment-list .option-list').hide();
        $('.select-input-box .option-list').hide();
        $('.select-input-box .sub-option-list').hide();
        $('.select-input-control .option').hide();
    });
	$('#region-search').hover(function () {
        SetRegionListTop();
		$(this).addClass('region-search-hover');
	}, function () {
		$(this).removeClass('region-search-hover');
	});
    //列表页区间搜索
    $('#price-search').click(function () {
        PriceSearch();
    });
    $('#price-average-search').click(function () {
        PriceAvarageSearch();
    });
    $('#totalarea-search').click(function () {
        TotalAreaSearch();
    });

	//SetMainContainerHeight();

    //底部浮动窗口
    $('#float-box .item').hover(function () {
        $(this).addClass('hover');
    }, function () {
        $(this).removeClass('hover');
    });
    $('#float-box .go-top').click(function () {
        //$('html,body').animate({scrollTop: 0}, 500);
        $('html,body').scrollTop(0);
    });
    /*$('#float-box .item').hover(function () {
        //$('html,body').animate({scrollTop: 0}, 500);
        $(this).find('.wechat-qrcode').show();
    }, function () {
        $(this).find('.wechat-qrcode').hide();
    });*/
    SetFloatBoxPosition();
	$(window).scroll(function () {
		var scrollTop = $(this).scrollTop();
		if (scrollTop > 200) {
			$('#float-box').addClass('float-show-btn');
		} else {
			$('#float-box').removeClass('float-show-btn');
		}
	}).resize(function () {
        SetFloatBoxPosition();
    });

    /*$('.ad-close-btn').click(function () {
        $(this).closest('.float-ad').remove();
    });
    $(window).resize(function () {
        if ($(this).width() < 1366) {
            $('.float-ad').hide();
        } else {
            $('.float-ad').show();
        }
    });*/
    $('#share-btn').hover(function () {
        $(this).find('.wechat-qrcode').show();
    }, function () {
        $(this).find('.wechat-qrcode').hide();
    });

    //设置搜索过滤下拉选项事件
    SetFilterSelectBoxEvent();
});

function SetHouseTypeOptionTop() {
    var containerTop = $('.house-type').offset().top;
    var containerHeight = $('.house-type').outerHeight();
    var scrollTop = $(window).scrollTop();
    var windowHeight = $(window).height();
    var optionHeight = $('.house-type .house-type-list').outerHeight();
    var distance = windowHeight - containerTop - containerHeight + scrollTop;

    if (distance < optionHeight) {
        var optionTop = 10 - optionHeight;
        $('.house-type .house-type-list').css('top', optionTop + 'px');
    } else {
        var optionTop = containerHeight - 10;
        $('.house-type .house-type-list').css('top', optionTop + 'px');
    }
}

function SetRegionListTop() {
    var containerTop = $('#region-search').offset().top;
    var containerHeight = $('#region-search').outerHeight();
    var scrollTop = $(window).scrollTop();
    var windowHeight = $(window).height();
    var optionHeight = $('#region-search .region-list').outerHeight();
    var distance = windowHeight - containerTop - containerHeight + scrollTop;

    if (distance < optionHeight) {
        var optionTop = 10 - optionHeight;
        $('#region-search .region-list').css('top', optionTop + 'px');
    } else {
        var optionTop = containerHeight - 10;
        $('#region-search .region-list').css('top', optionTop + 'px');
    }
}

function SetFloatBoxPosition() {
    /*var right = ($('body').width() - $('#head').width()) / 2 - $('#float-box').width();
    $('#float-box').css('right', right);*/
}

function MakeRegionSearchLink(targetUrl) {
    if (regionList == undefined || targetUrl.length == '') {
        return false;
    }
    var region = eval('(' + regionList + ')');
    var tmp = '';
    var url = '';
    for (var i = 0; i < region.length; i++) {
        url = targetUrl.replace('{$region_id}', region[i].region_id);
        url = url.replace('{$keywords}', '');
        tmp += '<div class="item"><a href="' + url + '"  target="_blank">' + region[i].region_name + '</a></div>';
    }
    if (tmp.length > 0) {
        $('#region-search .region-list').html(tmp);
    }
}

//页面左右两列高度自适应
function SetMainContainerHeight() {
	var leftHeight = $('#list_left').height();
	var centerHeight = $('#list_centent').height();
	var rightHeight = $('#right').height();
	var leftDetailHeight = $('#left').height();
	var articleHeight = $('#art_left').height();

	var containerHeight = 980;
	containerHeight = containerHeight > leftHeight ? containerHeight : leftHeight;
	containerHeight = containerHeight > centerHeight ? containerHeight : centerHeight;
	containerHeight = containerHeight > rightHeight ? containerHeight : rightHeight;
	containerHeight = containerHeight > leftDetailHeight ? containerHeight : leftDetailHeight;
	containerHeight = containerHeight > articleHeight ? containerHeight : articleHeight;
    containerHeight += 60;
	$('#main').height(containerHeight);
	setTimeout(SetMainContainerHeight, 3000);
}
function HomeSearchHouse(){
	var keywords = FilterWords($('#keywords').val());
	var url = $('.main-search .selected-type').attr('data-url');
	url = url.replace('{$keywords}', keywords);
	url = url.replace('{$region_id}', '0');
	window.location.href = url;
	return false;
}
function setsearchaction(curvalue){
	if(curvalue=='1'){
		$('#searchhouse').attr('action','/sale/');
	}else if(curvalue=='2'){
		$('#searchhouse').attr('action','/rent/');
	}
}

function goto_url(infotype, str){
	if(infotype == 'xzlcz' || infotype == 'xzlcs'){
		if(str == 'rent'){
			window.location.href = '/xzlcz/';
		}else if(str == 'sale'){
			window.location.href = '/xzlcs/';
		}
	}else if(infotype == 'spcz' || infotype == 'spcs'){
		if(str == 'rent'){
			window.location.href = '/spcz/';
		}else if(str == 'sale'){
			window.location.href = '/spcs/';
		}
	}
	
}
function open_url(url){
	window.location.href = url;
}

function dosearch(){
	var keyword = $('#text').val();
	var url = $('#url').val();
	url = url.replace('[keywords]',  FilterWords(keyword));
	$('#searchfrom').attr('action', url);
	$('#searchfrom').submit();
}
function dosearch2(){
	var keyword = $('#text').val();
	var url = $('#url').val();
	url = url.replace('[keywords]',  FilterWords(keyword));
	window.location.href = url;
	return false;
}

function FilterWords(keywords){
	if (keywords != ''){
		keywords = keywords.replace(/\//g, '');
		keywords = keywords.replace(/&/g, '');
		keywords = keywords.replace(/%/g, '');
		keywords = keywords.replace(/\?/g, '');
	}	
	return keywords;
}

function SearchNews(){
    var keywords = $('#news-keywords').val();
    if (keywords.length == 0) {
        alert('请输入要搜索的关键字！');
    } else {
        window.location.href = 'list_1_' + FilterWords(keywords) + '.html';
    }
}

function AddCollect(memberDB, houseId, houseType, houseTitle) {
	if (houseId.length == 0) {
		alert('参数错误，请重试');
	}
	var url = window.location.href;
	$.post('/ajax/index.php', {
		action: 'add_collect',
        member_db: memberDB,
		house_id: houseId,
		house_type: houseType,
		url: url,
		house_title: houseTitle,
		t: new Date().getTime()
	}, function (data) {
		if (data.error == 0) {
			//变更收录按钮为已收藏状态
			$('#add-collect-btn').addClass('collected').html('<i class="icon icon-collect"></i>已收藏');
			//alert(data.msg);
		} else if (data.error == 2) {
			alert(data.msg);
			window.location.href = '/member/login.php';
			return false;
		} else {
			alert(data.msg);
		}
	}, 'json');
}

//取消收藏
function CancelCollect(memberDB, houseId, houseType) {
	if (houseId.length == 0) {
		alert('参数错误，请重试');
	}
	$.post('/ajax/index.php', {
		action: 'cancel_collect',
        member_db: memberDB,
		house_id: houseId,
		house_type: houseType,
		t: new Date().getTime()
	}, function (data) {
		if (data.error == 0) {
			//变更收录按钮为已收藏状态
			$('#add-collect-btn').removeClass('collected').html('<i class="icon icon-collect"></i>收藏该信息');
			//alert(data.msg);
		} else if (data.error == 2) {
			alert(data.msg);
			window.location.href = '/member/login.php';
			return false;
		} else {
			alert(data.msg);
		}
	}, 'json');
}

//判断是否已收藏
function CheckIsCollected(memberDB, houseId, houseType) {
	$.post('/ajax/index.php', {
		action: 'check_is_collected',
        member_db: memberDB,
		house_id: houseId,
		house_type : houseType,
		t: new Date().getTime()
	}, function(data) {
		//已收藏
		if (data.is_collected == 1) {
			$('#add-collect-btn').addClass('collected').html('<i class="icon icon-collect"></i>已收藏');
		}
	}, 'json')
}

function PriceSearch() {
    var startPrice = GetInputValue('start_price', 'int', 0);
    var endPrice = GetInputValue('end_price', 'int', 0);
    var url = GetInputValue('price_url', '', '');

    if (startPrice == 0 && endPrice == 0) {
        alert('请输入正确的价格范围');
        return false;
    }
    if (startPrice > endPrice) {
        alert('结束价格不能小于起始价格');
        return false;
    }
    if (url.length == 0) {
        alert('搜索参数错误');
        return false;
    }
    url = url.replace('{curentparam}', startPrice + '-' + endPrice);
    window.location.href = url;
    return true;
}

function PriceAvarageSearch() {
    var startPrice = GetInputValue('start_price_average', 'int', 0);
    var endPrice = GetInputValue('end_price_average', 'int', 0);
    var url = GetInputValue('price_average_url', '', '');

    if (startPrice == 0 && endPrice == 0) {
        alert('请输入正确的价格范围');
        return false;
    }
    if (startPrice > endPrice) {
        alert('结束价格不能小于起始价格');
        return false;
    }
    if (url.length == 0) {
        alert('搜索参数错误');
        return false;
    }
    url = url.replace('{curentparam}', startPrice + '-' + endPrice);
    window.location.href = url;
    return true;
}

function TotalAreaSearch() {
    var startTotalArea = GetInputValue('start_totalarea', 'int', 0);
    var endTotalArea = GetInputValue('end_totalarea', 'int', 0);
    var url = GetInputValue('totalarea_url', '', '');

    if (startTotalArea == 0 && endTotalArea == 0) {
        alert('请输入正确的面积范围');
        return false;
    }
    if (startTotalArea > endTotalArea) {
        alert('结束面积不能小于起始面积');
        return false;
    }
    if (url.length == 0) {
        alert('搜索参数错误');
        return false;
    }
    url = url.replace('{curentparam}', startTotalArea + '-' + endTotalArea);
    window.location.href = url;
    return true;
}

function GetInputValue(obj, type, defaultValue) {
    var val = $('#' + obj).val();
    switch (type) {
        case 'int':
            val = parseInt(val);
            if (isNaN(val)) {
                val = defaultValue ? defaultValue : 0;
            }
            break;
        case 'float':
            val = parseFloat(val);
            if (isNaN(val)) {
                val = defaultValue ? defaultValue : 0;
            }
            break;
        default:
            if (val == undefined) {
                val = ddefaultValue ? defaultValue : '';
            }
    }
    return val;
}


function IsMobile(s){
	if (s.length !== 11) {
		return false;
	}
	var regex =/^1[3456789]{1}\d{9}$/;
	var re = new RegExp(regex);
	if (re.test(s)) {
		return true;
	}else{
		return false;
	}
}

function SwitchListType() {
    /*$('.list-type-switch .item').click(function () {
        var itemNode = $('.list-type-switch .item');
        var itemIndex = itemNode.index($(this));
        itemNode.removeClass('active');
        $(this).addClass('active');
        if (itemIndex == 1) {
            $('.list-box').removeClass('list-text').addClass('list-pic-text');
        } else {
            $('.list-box').addClass('list-text').removeClass('list-pic-text');
        }
        //设置Cookie
        $.cookie('house_list_type', itemIndex, { expires: 365, path: '/' });
    });*/

    $('.list-type-switch .switch-btn').click(function () {
        var status = 0;
        if ($(this).hasClass('text-btn') == true) {
            $(this).removeClass('text-btn');
            $('.list-box').addClass('list-text').removeClass('list-pic-text');
            $(this).attr('title', '切换到图文列表');
            status = 0;
        } else {
            $(this).addClass('text-btn');
            $('.list-box').addClass('list-pic-text').removeClass('list-text');
            $(this).attr('title', '切换到文本列表');
            status = 1;
        }
        //设置Cookie
        $.cookie('house_list_type', status, { expires: 365, path: '/' });
    });
}

function GetSearchHistoryList(obj, history_list) {
    if (history_list.length == 0) {
        return false;
    }
	//创建列表
    var list = '';
    $(history_list).each(function (index, item) {
        list += '<div class="item"><div class="caption">' + item + '</div><div class="close-btn"></div></div>';
    });
    if (list.length == 0) {
        return false;
    }
    $(obj).after('<div class="search-list">' + list + '</div>');
    $('.search-list .item').hover(function () {
        $(this).addClass('hover');
    }, function () {
        $(this).removeClass('hover');
    });
    //获取光标事件
    $(obj).focus(function () {
        $('.search-list').show();
    });
    /*$(obj).blur(function () {
        $('.search-list').hide();
    });*/

    //点击搜索
    $('.search-list .item .caption').click(function () {
        var url = $('#url').val();
        var keyword = $(this).html();
        url = url.replace('[keywords]',  FilterWords(keyword));
        window.location.href = url;
        return false;
    });
    $('.search-list .item .close-btn').click(function () {
        var index = $('.search-list .item .close-btn').index($(this));
        delete history_list[index];
        $('.search-list .item').eq(index).remove();
        $.post('/ajax/index.php', {
            action: 'delete_search_history',
            id: index,
            t: new Date().getTime()
        }, function (data) {
            if (data.error == 0) {

            }
        }, 'json');
    });
    $(document).click(function () {
        $('.search-list').hide();
    });
    $('.search-list').click(function (e) {
        e.stopPropagation();
    });
    $(obj).click(function (e) {
        e.stopPropagation();
    });
}

function ShowVerifyCodeDialog() {
    //验证码输入窗口
    var verifyHTML = '<div class="login-box verify-box">' +
        '<div class="box-title">请填写图片验证码</div>' +
        '<div class="input-item" id="valid-code-container">' +
        '<div class="input-wrap valid-code">' +
        '<input type="text" id="verify_code" name="verify_code" class="input_region_yzm" maxlength="4" autocomplete="off" placeholder="验证码" />' +
        '<img id="verify_pic" src="/verify.php" onclick="RefreshVerifyCode();" />' +
        '</div>' +
        '<div class="verify-code-refresh-btn"><a href="javascript:void(0);" onclick="RefreshVerifyCode();">看不清？换一换</a></div>' +
        '</div>' +
        '<div class="input-item">' +
        '<input type="submit" name="submit" value="确 定" class="submit-btn common-submit-btn" onclick="RequestSendSMS();" />' +
        '</div>' +
        '</div>';
    layer.open({
        id: 'verify-pic-dialog',
        type: 1,
        title: false,
        closeBtn: true,
        content: verifyHTML
    });
}

//弹窗验证码
function RefreshVerifyCode() {
    $('#verify_code').val('').focus();
    $('#verify_pic').attr('src', '/verify.php?' + Math.random());
}

//普通验证码
function RefreshValidateCode() {
    $('#valid').val('').focus();
    $('#valid_pic').attr('src', '/verify.php?' + Math.random());
}

function ShowVIPDialog() {
    var loadingIndex = layer.load(2);
    $.post('/member/member_vip.php', {
        t: new Date().getTime()
    }, function (data) {
        layer.close(loadingIndex);
        if (data.error == 1) {
            layer.alert(data.msg, {icon: 2});
            return false
        }
        layer.open({
            id: 'member-vip-dialog',
            type: 1,
            title: ['开通VIP', 'font-size: 18px; font-weight: bold; padding: 0 15px;'],
            content: data.content,
            area: ['800px', '']
        });
    }, 'json');
}

function BuyVipButton() {
    //判断是否已登录
    $.post('/ajax/index.php', {
        action: 'check_is_login',
        t: new Date().getTime()
    }, function (data) {
        if (data.error == 0) {
            window.location.href = '/member/member_vip.php';
        } else {
            layer.alert('请先登录会员中心！', {
                title: ''
            }, function(index){
                window.location.href = '/member/login.php';
            });
        }
    }, 'json');
}

function ResizeBrokerFace() {
    var container = $('.broker_left');
    var picNode = container.find('.broker_face');
    var itemWidth = picNode.width();
    var itemHeight = itemWidth;
    picNode.height(itemHeight);
}

function ResizeHomeBrokerFace() {
    var container = $('.section-box .list-broker');
    var picNode = container.find('.pic');
    var itemWidth = picNode.width();
    var itemHeight = itemWidth;
    picNode.height(itemHeight);
}

function AddClick(memberDB, id, type) {
    $.post('/ajax/index.php', {
        action: 'add_click',
        member_db: memberDB,
        type: type,
        id: id,
        t: new Date().getTime()
    }, function (data) {
        if (data.error == 0) {
            $('#click-number').html('浏览量：' + data.click);
        }
    }, 'json');
}

function GetHouseData(publishType, id) {
    $.post('/ajax/index.php', {
        action: 'get_house_data',
        publish_type: publishType,
        id: id,
        t: new Date().getTime()
    }, function (data) {
        if (data.error == 0 && data.price) {
            if (publishType == 2) {
                var mainHTML = '<b>' + data.price + '</b>万元';
                var generalHTML = data.price + '万元';

                if (data.average) {
                    mainHTML += ' <span class="text">(' + data.average + '元/㎡)</span>';
                    generalHTML += ' (单价' + data.average + '元/㎡)';
                }

                $('#main-price').html(mainHTML);
                $('#general-price-box').html(generalHTML);
            }
        }
    }, 'json');
}

function SetFilterSelectBoxEvent() {
    $('.filter-select-box').hover(function () {
        $(this).find('.option').show();
    }, function () {
        $(this).find('.option').hide();
    });
    $('.filter-select-box .item').click(function () {
        var itemValue = $(this).attr('data-value');
        var url = $(this).closest('.filter-select-box').attr('data-url');
        window.location.href = url.replace('{curentparam}', itemValue);
    });
}

function SetSubRegion(obj, parentId) {
    var layerLoad = layer.load(1);
    $.getJSON('/ajax/index.php', {
        action: 'getsubregion',
        id: parentId,
        t: new Date().getTime()
    }, function (data) {
        var tmp = '<option value="">请选择</option>';
        $.each(data, function (index, element) {
            tmp += '<option value="' + data[index].region_id + '">' + data[index].region_name + '</option>';
        });
        $('#' + obj).html(tmp);
        layer.close(layerLoad);
    }, 'json');
}

function ShowLoginDialog(jumpUrl) {
    layer.open({
        type: 2,
        title: '',
        shadeClose: true,
        closeBtn: true,
        shade: 0.6,
        area: ['430px', '520px'],
        content: '/member/login_box.php?jump_url=' + jumpUrl,
        success: function (layero, index) {

        }
    });
}