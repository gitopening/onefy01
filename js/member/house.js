function SetInputFields() {
    $('input[name=house_type]').click(function () {
        var houseType = $(this).val();
        SetInputItem(houseType);
    });

    SetInputItem($('input[name=house_type]:checked').val());
}

function SetInputItem(houseType) {
    houseType = parseInt(houseType);
    if (isNaN(houseType)) {
        return false;
    }
    var box = $('#borough-name-box');
    var captionBox = box.find('.caption');
    var boroughInput = box.find('#borough_name');
    var checkTipBox = box.find('.Validform_checktip');
    var checkTips = checkTipBox.html();
    //去除包含的空格
    if (checkTips.length > 0) {
        checkTips = checkTips.replace(new RegExp(/ /, 'img'), '');
    }

    switch (houseType) {
        /*case 1:
         //住宅
         captionBox.html('<span class="require-flag">*</span>小区名称：');
         boroughInput.attr('nullmsg', '请输入小区名称');
         boroughInput.attr('errmsg', '小区名称为2到20个字符');
         break;*/
        case 5:
        //写字楼
        case 7:
            //商铺
            captionBox.html('<span class="require-flag">*</span>楼盘名称：');
            boroughInput.attr('nullmsg', '楼盘名称为2到20个字');
            boroughInput.attr('errmsg', '楼盘名称为2到20个字');
            if (checkTips != '') {
                checkTipBox.html('楼盘名称为2到20个字');
            }
            $('.switch-hide-box').hide();
            break;
        default:
            captionBox.html('<span class="require-flag">*</span>小区名称：');
            boroughInput.attr('nullmsg', '小区名称为2到20个字');
            boroughInput.attr('errmsg', '小区名称为2到20个字');
            if (checkTips != '') {
                checkTipBox.html('小区名称为2到20个字');
            }
            $('.switch-hide-box').show();
    }
    MakeHouseTitle();
}

var houseTitlePrefix = '';
function AutoCompleteHouseTitle(prefix) {
    houseTitlePrefix = prefix;
    $('#house_title').keyup(function () {
        $(this).attr('auto-fill', '0');
    });
    $('#borough_name,#cityarea_name,#cityarea2_name,#house_room,#house_hall,#house_toilet').bind('blur change keyup', function () {
        MakeHouseTitle();
    });
    //默认先执行一次自动生成标题
    MakeHouseTitle();
}

function MakeHouseTitle() {
    var houseTitleStatus = $('#house_title').attr('auto-fill');
    if (houseTitleStatus == '0') {
        return false;
    }
    var houseType = parseInt($('input[name=house_type]:checked').val());
    var boroughName = $('#borough_name').val();
    var cityAreaName = $('#cityarea_name').val();
    //var cityAreaName_2 = $('#cityarea2_name').val();
    var houseRoom = parseInt($('#house_room').val());
    var houseHall = parseInt($('#house_hall').val());
    var houseToilet = parseInt($('#house_toilet').val());

    var houseTitle = '';
    /*switch (column) {
        case 1:
            houseTitle += '出租';
            break;
        case 2:
            houseTitle += '出售';
            break;
        case 3:
            houseTitle += '求租';
            break;
        case 4:
            houseTitle += '求购';
            break;
        default:

    }*/

    if (cityAreaName != undefined) {
        houseTitle += cityAreaName;
    }
    /*if (cityAreaName_2 != undefined) {
        houseTitle += cityAreaName_2;
    }*/
    if (boroughName != undefined) {
        houseTitle += boroughName;
    }
    if (houseType != 5 && houseType != 7) {
        if (!isNaN(houseRoom) && houseRoom > 0) {
            if (houseRoom >= 10) {
                houseTitle += '9室以上'
            } else {
                houseTitle += houseRoom.toString() + '室';
            }
        }
        if (!isNaN(houseHall) && houseHall > 0) {
            if (houseHall >= 10) {
                houseTitle += '9厅以上'
            } else {
                houseTitle += houseHall.toString() + '厅';
            }
        }
        if (!isNaN(houseToilet) && houseToilet > 0) {
            if (houseToilet >= 10) {
                houseTitle += '9卫以上'
            } else {
                houseTitle += houseToilet.toString() + '卫';
            }
        }
    }
    if ((houseType == 5 || houseType == 7) && (houseTitlePrefix == '出租' || houseTitlePrefix == '出售')) {
        $('#house_title').val(houseTitlePrefix + houseTitle);
    } else {
        $('#house_title').val(houseTitle);
    }
}

function SelectBox() {
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
    $('.select-input-box .item').click(function (e) {
        e.stopPropagation();
        var optionList = $(this).closest('.option-list');
        optionList.hide();
        optionList.prevAll('.hidden-input').val($(this).attr('data-value'));
        optionList.prevAll('.box-title').html($(this).html());
        var nextControl = $(this).closest('.select-input-box').attr('next-control');
        if (nextControl != undefined && nextControl != '') {
            $('#' + nextControl)[0].focus();
        } else {
            $(this).closest('.select-input-box').next('.select-input-box').find('.option-list').show();
        }
        //创建标题
        MakeHouseTitle();
    });
}
function SelectInputControl() {
    $('.select-input-control').hover(function () {
        $(this).find('.option').show();
    }, function () {
        $(this).find('.option').hide();
    });
    $('.select-input-control .item').click(function (e) {
        e.stopPropagation();
        var optionList = $(this).closest('.option');
        var houseType = $('input[name=house_type]:checked').val();
        optionList.hide();
        optionList.prevAll('.hidden-input').val($(this).attr('data-value'));
        optionList.prevAll('.input').focus().val($(this).html()).blur();
        if ($(this).closest('.option').attr('id') == 'elevator-option') {
            $('#parking-lot-option').show();
        }
        if ($(this).closest('.option').attr('id') == 'parking-lot-option') {
            if (houseType == 5 || houseType == 7) {
                $('#house_totalarea')[0].focus();
            } else {
                $('#house-room-select .option-list').show();
            }
        }
    });

    //设置默认值
    $('.select-input-control').each(function (index, element) {
        var inputValue = $(element).find('.hidden-input').val();
        $(element).find('.item').each(function (i, e) {
            if ($(e).attr('data-value') == inputValue) {
                $(element).find('.input').val($(e).html());
            }
        });
    });
}
function sethousethumb(obj){
    $('#house_thumb_id').val($(obj).attr('pic-id'));
    //$('.setindexpicbtn').val('设为封面');
    $(obj).val('封面');
    //移动到第一位
    layer.msg('设置封面成功', {icon: 1});
    $('#house_picture_dis').find('.upload_shower').removeClass('main-pic');
    var picContainer = $(obj).closest('.upload_shower').removeClass('hover').addClass('main-pic');
    $('#house_picture_dis').prepend(picContainer.prop('outerHTML'));
    picContainer.remove();
    BindHousePictureListHoverEvent();
}

function SetRegion(regionData) {
    //所在地区选择
    $('#borough_addr_tr .column').hover(function () {
        $(this).find('.option-list').show();
    }, function () {
        $(this).find('.option-list').hide();
    });
    /*$('#borough_addr_tr .select-input').focus(function () {
        $(this).next('.option-list').show();
    });*/
    /*$('#borough_addr_tr .select-input').blur(function () {
        $(this).next('.option-list').hide();
    });*/

    //生成地区数据
    var cityAreaId = parseInt($('#cityarea_id').val());
    if (!isNaN(cityAreaId) && cityAreaId > 0 && regionData['big_' + cityAreaId] != undefined) {
        //注意处理顺序
        $('#cityarea_name').val(regionData['big_' + cityAreaId]['region_name']);
        var cityArea2Id = parseInt($('#cityarea2_id').val());
        GetSubRegion(regionData['big_' + cityAreaId]['list']);
        if (!isNaN(cityArea2Id) && cityArea2Id > 0) {
            $('#sub-option-list .item').each(function (index, element) {
                var itemValue = parseInt($(element).attr('data-id'));
                if (!isNaN(itemValue) && cityArea2Id == itemValue && cityArea2Id > 0) {
                    $('#cityarea2_name').val($(element).attr('data-name'));
                }
            });
        } else {
            $('#cityarea2_id').val('0');
        }
    } else {
        $('#cityarea2_id').val('0');
        $('#cityarea2_name').val('');
    }

    var tmp = '';
    for (var index in regionData) {
        tmp += '<div class="item" data-id="' + regionData[index].region_id + '" data-name="' + regionData[index].region_name + '">' + regionData[index].region_name + '</div>';
    }
    $('#cityarea-option-list').html(tmp);

    $('#cityarea-option-list .item').click(function (e) {
        e.stopPropagation();
        var regionId = parseInt($(this).attr('data-id'));
        var regionName = $(this).attr('data-name');
        $('#cityarea_name').val(regionName).focus().blur();
        $('#cityarea_id').val(regionId);
        $('#cityarea-option-list').hide();
        GetSubRegion(regionData['big_' + regionId]['list']);
        $('#sub-option-list').show();
        //清除二级地名
        $('#cityarea2_id').val('0');
        $('#cityarea2_name').val('');
    });
    $('#sub-option-list .item').click(function(e){
        e.stopPropagation();
        var regionId = $(this).attr('data-id');
        var regionName = $(this).attr('data-name');
        $('#cityarea2_id').val(regionId);
        $('#sub-option-list').hide();
        if ($(this).hasClass('custom') == true) {
            $('#cityarea2_name').select();
        } else {
            $('#cityarea2_name').val(regionName).focus().blur();
            $('#house_address')[0].focus();
        }
    });
}

function GetSubRegion(regionData) {
    //var tmp = '<div class="item" data-id="0" data-name="不限">不限</div>';
    var tmp = '';
    var firstLetter = '';
    var letterNode = '';
    for(var index in regionData){
        if (firstLetter != regionData[index]['first_letter']) {
            firstLetter = regionData[index]['first_letter'];
            letterNode = '<span class="letter">' + regionData[index]['first_letter'] + '</span>';
        } else {
            letterNode = '';
        }
        tmp += '<div class="item" data-id="' + regionData[index]['region_id'] + '" data-name="' + regionData[index]['region_name'] + '">' + letterNode + regionData[index]['region_name'] + '</div>';
    }
    tmp += '<div class="item custom" data-id="0" data-name="">自行填写</div>';
    $('#sub-option-list').html(tmp);
    $('#sub-option-list .item').click(function(e){
        e.stopPropagation();
        var regionId = $(this).attr('data-id');
        var regionName = $(this).attr('data-name');
        $('#cityarea2_id').val(regionId);
        $('#sub-option-list').hide();
        if ($(this).hasClass('custom') == true) {
            $('#cityarea2_name').select();
        } else {
            $('#cityarea2_name').val(regionName).focus().blur();
            $('#house_address')[0].focus();
        }
    });
}

function SetCityAreaDefaultValue(objId, value) {
    $('#' + objId).val(value);
}

function CheckCityArea() {
    var number = $('#cityarea_id').val();
    var ereg = /[1-9]\d*/;
    if (ereg.test(number)) {
        $('#cityarea-msg').html('').attr('class', 'Validform_checktip Validform_right');
        $('#cityarea_id').removeClass('Validform_error');
        return true;
    } else {
        $('#cityarea-msg').html('请选择所在地区大类!').attr('class', 'Validform_checktip Validform_wrong');
        $('#cityarea_id').addClass('Validform_error');
        return false;
    }
}

function CheckHouseDesc() {
    //var html = ue.getContent();
    //获取纯文本内容，返回: hello
    var txt = ue.getContentTxt();

    //去除HTML标签
    txt = txt.replace(/<\/?.+?>/g,"");  //去除HTML标签
    txt = txt.replace(/ /g,"");         //去空格

    if (txt.length > 1500) {
        $('#house-desc-tips').removeClass('Validform_right').addClass('Validform_wrong').html('长度最多1500字');
        return false;
    }
    $.ajaxSetup({
        async : false
    });
    $.post('/ajax/index.php', {
        action: 'check_words',
        name: 'house_desc',
        param: txt,
        t: new Date().getTime()
    }, function (data) {
        $.ajaxSetup({
            async : true
        });
        if (data.status == 'n') {
            $('#house-desc-tips').removeClass('Validform_right').addClass('Validform_wrong').html(data.info);
            return false;
        } else {
            $('#house-desc-tips').addClass('Validform_right').removeClass('Validform_wrong').html(data.info);
            return true;
        }
    }, 'json');
}

function BindHousePictureListHoverEvent() {
    $('#house_picture_dis .upload_shower').unbind('hover').hover(function () {
        $(this).addClass('hover');
    }, function () {
        $(this).removeClass('hover');
    });
}

function CheckContact(gets, obj, curform, regxp) {
    var hidePhone = $('#hide_phone').is(':checked');
    var wechat = $('#wechat').val();
    var userType = parseInt($('#user_type').val());
    if (isNaN(userType)) {
        userType = 2; //默认为个人会员类型
    }

    if (userType == 2 && hidePhone == true && wechat.length == 0) {
        $('#wechat').addClass('Validform_error').next('.Validform_checktip').removeClass('Validform_right').addClass('Validform_wrong').html('选择隐藏手机号码时，微信必需填写');
        return false;
    }
    //如果微信是手机号时，检测是否和手机号一致
    if (wechat.length > 0) {
        $.getJSON('/ajax/index.php', {
            action: 'check_wechat',
            wechat: wechat,
            t: new Date().getTime()
        }, function (data) {
            if (data.error == 0) {
                $('#wechat').removeClass('Validform_error').next('.Validform_checktip').removeClass('Validform_wrong').addClass('Validform_right').html('');
            } else {
                $('#wechat').addClass('Validform_error').next('.Validform_checktip').removeClass('Validform_right').addClass('Validform_wrong').html(data.msg);
            }
        });
    } else {
        $('#wechat').removeClass('Validform_error').next('.Validform_checktip').removeClass('Validform_wrong').addClass('Validform_right').html('');
    }

    return true;
}

function CheckHouseRoom() {
    var houseType = parseInt($('input[name=house_type]:checked').val());
    if (houseType == 5 || houseType == 7 || houseType == 15) {
        $('#house-type-selected .Validform_checktip').html('').attr('class', 'Validform_checktip Validform_right');
        $('#house_room').removeClass('Validform_error');
        return true;
    }
    var number = parseInt($('#house_room').val());
    number = isNaN(number) ? 0 : number;
    if (number <= 0) {
        $('#house-type-selected .Validform_checktip').html('请选择户型!').attr('class', 'Validform_checktip Validform_wrong');
        $('#house-room-select .box-title').addClass('Validform_error');
        $(window).scrollTop($('#house-room-select').offset().top - 50);
        return false;
    } else {
        $('#house-type-selected .Validform_checktip').html('').attr('class', 'Validform_checktip Validform_right');
        $('#house-room-select .box-title').removeClass('Validform_error');
        return true;
    }
}

function BrandAppartment() {
    $('input[name="brand_apartment_type"]').click(function (e) {
        e.stopPropagation();
        /*var brandApartmentType = parseInt($(this).val());
        brandApartmentType = isNaN(brandApartmentType) ? 0 : brandApartmentType;
        if (brandApartmentType == 1) {
            $('#brand-apartment-list').show().find('.option-list').show();
        } else {
            $('#brand-apartment-list').hide();
        }*/
    });

    //设置默认值
    var brand_apartment_id = parseInt($('#brand_apartment_id').val());
    if (!isNaN(brand_apartment_id)) {
        $('#brand-apartment-list .item').each(function (index, element) {
            var itemValue = parseInt($(element).attr('data-id'));
            if (!isNaN(itemValue) && brand_apartment_id == itemValue && brand_apartment_id > 0) {
                $('#brand_apartment_name').val($(element).attr('data-name'));
                $('input[name="brand_apartment_type"]').eq(1).prop('checked', true);
            }
        });
    }

    $('#brand-apartment-list').hover(function () {
        $(this).find('.option-list').show();
    }, function () {
        $(this).find('.option-list').hide();
    });
    $('#brand-apartment-list .item').click(function (e) {
        e.stopPropagation();
        var dataId = $(this).attr('data-id');
        var dataName = $(this).attr('data-name');
        $('#brand_apartment_id').val(dataId);
        $('#brand_apartment_type').prop('checked', true);
        $('#brand-apartment-list').find('.option-list').hide();
        if ($(this).hasClass('custom') == true) {
            $('#brand_apartment_name').select();
        } else {
            $('#brand_apartment_name').val(dataName).focus().blur();
        }
    });

    $('#brand_apartment_name').keyup(function () {
        var dataName = $(this).val();
        if (dataName != '') {
            $('#brand_apartment_type').prop('checked', true);
        }
    });

    $('#brand_apartment_type').click(function () {
        if ($(this).prop('checked') == true) {
            $('#brand-apartment-list .option-list').show();
        }
    });
}

function SetControlItemHidden() {
    var value = parseInt($('input[name=publish_type]:checked').val());
    if (isNaN(value)) {
        value = parseInt($('input[name=publish_type]').val());
    }
    switch (value) {
        case 1:
            $('#house-total-area-box .caption').html('<span class="require-flag">*</span>面　　积：');
            $('#down-payment-box').hide();
            $('#expected-price-box').hide();
            $('#transfer-fee-box').hide();
            $('#left-rent-month-box').hide();
            $('#sale-price-box').hide();
            $('#store-rent-type-box').show();
            $('#price-box').show();
            $('#pay-method-box').show();
            $('#lease-term-box').show();
            $('#parking-type-box').show();
            $('.upload-picture-box').show();
            $('#floor-type-box').show();
            $('#first-floor-height-box').show();
            $('#house-support-box').show();
            break;
        case 5:
            $('#house-total-area-box .caption').html('<span class="require-flag">*</span>面　　积：');
            $('#store-rent-type-box').hide();
            $('#down-payment-box').hide();
            $('#expected-price-box').hide();
            $('#lease-term-box').hide();
            $('#sale-price-box').hide();
            $('#price-box').show();
            $('#transfer-fee-box').show();
            $('#left-rent-month-box').show();
            $('#pay-method-box').show();
            $('#parking-type-box').show();
            $('.upload-picture-box').show();
            $('#floor-type-box').show();
            $('#first-floor-height-box').show();
            $('#house-support-box').show();
            break;
        case 2:
            $('#house-total-area-box .caption').html('<span class="require-flag">*</span>面　　积：');
            $('#store-rent-type-box').hide();
            $('#pay-method-box').hide();
            $('#expected-price-box').hide();
            $('#lease-term-box').hide();
            $('#transfer-fee-box').hide();
            $('#left-rent-month-box').hide();
            $('#price-box').hide();
            $('#sale-price-box').show();
            $('#down-payment-box').show();
            $('#parking-type-box').show();
            $('.upload-picture-box').show();
            $('#floor-type-box').show();
            $('#first-floor-height-box').show();
            $('#house-support-box').show();
            break;
        case 3:
            $('#house-total-area-box .caption').html('<span class="require-flag">*</span>期望面积：');
            $('#store-rent-type-box').hide();
            $('#price-box').hide();
            $('#sale-price-box').hide();
            $('#down-payment-box').hide();
            $('#pay-method-box').hide();
            $('#lease-term-box').hide();
            $('#parking-type-box').hide();
            $('#transfer-fee-box').hide();
            $('#left-rent-month-box').hide();
            $('#expected-price-box .sale-unit-type').hide();
            $('#floor-type-box').hide();
            $('#first-floor-height-box').hide();
            $('#house-support-box').hide();
            $('#expected-price-box .unit-type-box').show();
            $('#expected-price-box').show();
            $('.upload-picture-box').hide();
            break;
        case 4:
            $('#house-total-area-box .caption').html('<span class="require-flag">*</span>期望面积：');
            $('#expected-price-box .unit-type-box').hide();
            $('#price-box').hide();
            $('#store-rent-type-box').hide();
            $('#sale-price-box').hide();
            $('#down-payment-box').hide();
            $('#pay-method-box').hide();
            $('#lease-term-box').hide();
            $('#parking-type-box').hide();
            $('#transfer-fee-box').hide();
            $('#left-rent-month-box').hide();
            $('#floor-type-box').hide();
            $('#first-floor-height-box').hide();
            $('#house-support-box').hide();
            $('#expected-price-box').show();
            $('#expected-price-box .sale-unit-type').show();
            $('.upload-picture-box').hide();
            break;
        default:

    }
}

function ShowWechatUploadPictureBox(houseType) {
    startTime = parseInt(new Date().getTime() / 1000);
    //取得Token
    $.post('/ajax/index.php', {
        action: 'get_wechat_upload_token',
        house_type: houseType,
        t: new Date().getTime()
    }, function (data) {
        if (data.error == 1) {
            layer.alert(data.msg, {icon: 2});
        } else {
            var verifyHTML = '<div class="wechat-qrcode-box clear-fix">' +
                '<div class="qrcode-picture">' +
                '<img src="' + data.qrcode_url + '" />' +
                '</div>' +
                '<div class="tips">' +
                '请用微信扫描左侧二维码<br />' +
                '在微信中批量上传手机中的图片' +
                '</div>' +
                '</div>';
            layer.open({
                id: 'verify-pic-dialog',
                type: 1,
                area: ['650px', ''],
                title: false,
                closeBtn: true,
                content: verifyHTML,
                success: function(layero, index){
                    CheckWechatScanned(data.token, houseType);
                },
                cancel: function(index, layero){

                }
            });
        }
    }, 'json');
}

function CheckWechatScanned(token, houseType) {
    var nowTime = parseInt(new Date().getTime() / 1000);
    if (nowTime - startTime > 600) {
        return false;
    }
    $.post('/ajax/index.php', {
        action: 'check_wechat_scanned',
        token: token,
        house_type: houseType,
        t: new Date().getTime()
    }, function (data) {
        if (data.error == 0) {
            if (data.is_scanned == 1) {
                layer.closeAll();
                layer.msg(data.msg);
                //拉取用户上传的图片信息
                startTime = nowTime;
                GetWechatUploadPicture(token, houseType, uploadLastId);
            } else {
                setTimeout(function () {
                    CheckWechatScanned(token, houseType);
                }, 2000);
            }
        } else {
            layer.closeAll();
            layer.alert(data.msg);
        }
    }, 'json');
}

function GetWechatUploadPicture(token, houseType, uploadLastId) {
    var nowTime = parseInt(new Date().getTime() / 1000);
    if (nowTime - startTime > 600) {
        return false;
    }
    $.post('/ajax/index.php', {
        action: 'get_wechat_upload_picture',
        token: token,
        house_type: houseType,
        upload_last_id: uploadLastId,
        t: new Date().getTime()
    }, function (data) {
        if (data.error == 0) {
            //当前图片列表中图片数量
            var currentPictureCount = $('#house_picture_dis .upload_shower').length;
            var leftPictureCount = fileNumLimit - currentPictureCount;
            if (leftPictureCount > 0) {
                //添加上传的图片到图片列表中
                if (data.list.length > 0) {
                    $.each(data.list, function (index, item) {
                        if (index < leftPictureCount) {
                            //添加图片到列表中
                            var className = '';
                            if ($('#house_picture_dis .upload_shower').length == 0) {
                                className = 'main-pic';
                            }
                            $item = '<div class="upload_shower ' + className + '" id="container_picture_' + item.id + '">' +
                                '<a href="' + item.url + '" target="_blank">' +
                                '<img src="' + item.url + '"></a>' +
                                '<input type="hidden" name="house_picture_id[' + item.id + ']" value="' + item.id + '">' +
                                '<div class="del-pic-btn" onclick="UploaderDropFile(\'' + item.id + '\')" title="删除图片"></div>' +
                                '<div class="set-index-pic-btn" pic-id="' + item.id + '" onclick="sethousethumb(this)" title="设为封面"></div>' +
                                //'<div class="mask"></div>' +
                                '<div class="icon"></div>' +
                                '</div>';
                            $('#house_picture_dis').append($item);
                        } else {
                            $('#max-picture-tips').show();
                        }
                        uploadLastId = item.id;
                    });
                    BindHousePictureListHoverEvent();
                }
            } else {
                $('#max-picture-tips').show();
            }
            setTimeout(function () {
                GetWechatUploadPicture(token, houseType, uploadLastId);
            }, 2000);
        } else {
            //layer.alert(data.msg);
        }
    }, 'json');
}

function GetSelectInputRegionItem(region_id, currentObject) {
    var nextObject = currentObject.next('.select-input-box');
    currentObject.nextAll('.select-input-box').find('input').val(0);
    currentObject.nextAll('.select-input-box').find('.box-title').html('请选择');
    currentObject.nextAll('.select-input-box').find('.option-list').hide().html('');
    $.get('/ajax/index.php', {
        action: 'getsubregion',
        id: region_id,
        t: new Date().getTime()
    }, function(data){
        var tmp = '';
        var count = data.length;
        for(var i=0; i< count; i++){
            tmp += '<div class="item" data-value="' + data[i].region_id + '">' + data[i].region_name + '</div>';
        }
        nextObject.find('.option-list').html(tmp).show();
        nextObject.find('.option-list .item').click(function () {
            var region_id = parseInt($(this).attr('data-value'));
            var currentObject = $(this).closest('.select-input-box');
            currentObject.find('.box-title').html($(this).html());
            currentObject.find('input').val($(this).attr('data-value'));
            GetSelectInputRegionItem(region_id, currentObject);
        });
    }, 'json');
}

function ShowQuickRecordDialog(publishType, houseType) {
    var index = layer.open({
        type: 2,
        closeBtn: true,
        title: '<span style="margin-left: 10px;">秒录房源</span>',
        content: 'quick_record.php?publish_type=' + publishType + '&house_type=' + houseType,
        area: ['700px', '240px'],
        success: function(layero, index){

        },
        cancel: function(index, layero){

        }
    })
}