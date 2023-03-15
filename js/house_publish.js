//预先取得地区数据
var regionData;
$.getJSON('/ajax/index.php', {
    action: 'get_all_cityarea',
    city_id: '<!--{$house_city_id}-->',
    t: new Date().getTime()
}, function (data) {
    regionData = data;
});

function BindSelectBoxEvent() {
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
    });
}

function InitWebUploader() {
    if (!WebUploader.Uploader.support()) {
        layer.open({
            type: 1,
            closeBtn: 0,
            area:  ['530px', ''],
            title: '温馨提示',
            content: $('#webuploader-tips')
        });
        throw new Error('Please upgrade your web browser.');
    }

    uploader = WebUploader.create({
        // 选完文件后，是否自动上传。
        auto: true,
        fileSingleSizeLimit: 2048 * 1024,
        fileNumLimit: fileNumLimit,
        threads: 1,
        // swf文件路径
        swf: '/js/webuploader/Uploader.swf',
        // 文件接收服务端。
        //server: '/upload.php?action=upload_house_pic_list&to=uploadHousePicture|house|rent',
        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: '#upload-picture-btn',
        // 只允许选择图片文件。
        accept: {
            title: 'Images',
            extensions: 'jpg,jpeg,png',
            mimeTypes: 'image/jpg,image/jpeg,image/png'
        }
    });

    uploader.on('beforeFileQueued', function (file) {
        var fileExistCount = $('#house_picture_dis .upload_shower').length;
        fileExistCount++;
        if (fileExistCount > fileNumLimit) {
            return false;
        }
    });

    //当有文件添加进来的时候
    uploader.on('fileQueued', function(file) {
        var container = $('#divFileProgressContainer');
        container.slideDown();
    });

    uploader.on('uploadStart', function (file) {
        //取得上传签名
        ossData = GetUploadParams(ossData, 1, file.name);
        if (ossData.error == 0) {
            uploader.option('formData', {
                'key': ossData.key,
                'policy': ossData.policy,
                'OSSAccessKeyId': ossData.access_id,
                'success_action_status': ossData.success_action_status, //让服务端返回200,不然，默认会返回204
                'callback': ossData.callback,
                'signature': ossData.signature
            });
            uploader.option('server', ossData.host);
        } else {
            alert(ossData.msg);
        }
    });

    uploader.on('uploadProgress', function (file, percentage) {
        var container = $('#divFileProgressContainer');
        container.find('.progressName').html(file.name);
        container.find('.progressBarStatus').html('正在上传');
        container.find('.progressBarComplete').show().css('width', '0%');
        container.find('.progressBarComplete').animate({ width: (percentage * 100).toString() + '%'}, 500);
    });
    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
    uploader.on('uploadSuccess', function(file, response) {
        var container = $('#divFileProgressContainer');
        if (response.error == 0) {
            $item = '<div class="upload_shower" id="container_picture_' + file.id + '">' +
                '<a href="' + response.url + '" target="_blank">' +
                '<img src="' + response.url + '"></a><br>' +
                '<input type="text" class="pic_desc" name="house_picture_desc[' + response.id + ']" value="" size="21" placeholder="填写图片描述"><br>' +
                '<input type="hidden" name="house_picture_id[' + response.id + ']" value="' + response.id + '">' +
                '<input type="button" name="deletePicture_' + file.id + '" onClick="UploaderDropFile(\'' + file.id + '\')" value="删除" class="delpicbtn">' +
                '<input type="button" onClick="sethousethumb(this)" pic-id="' + response.id + '" value="设为封面" class="setindexpicbtn" id="setthumb_' + file.id + '">' +
                '</div>';
            $('#house_picture_dis').append($item);
            container.find('.progressBarStatus').html('上传成功');
        } else {
            //文件上传失败
            uploader.removeFile(file, true);
            container.find('.progressBarStatus').html('上传失败');
            alert(file.name + '上传失败');
        }
    });

    // 文件上传失败，显示上传出错。
    uploader.on('uploadError', function(file) {
        var container = $('#divFileProgressContainer');
        container.find('.progressBarStatus').html('上传失败');
        uploader.removeFile(file, true);
        alert(file.name + '上传失败');
    });

    // 完成上传完了，成功或者失败，先删除进度条。
    uploader.on('uploadFinished', function(file) {
        var container = $('#divFileProgressContainer');
        container.find('.progressBarStatus').html('已上传完所有图片');
        container.find('.progressBarComplete').hide();
        setTimeout(function(){
            container.slideUp();
        }, 1200);
    });
}

//地区选择
function RegionSelect() {
    $('#borough_addr_tr .column').hover(function () {
        $(this).find('.option-list').show();
    }, function () {
        $(this).find('.option-list').hide();
    });
    $('#borough_addr_tr .select-input').focus(function () {
        $(this).next('.option-list').show();
    });
    $('#borough_addr_tr .select-input').blur(function () {
        $(this).next('.option-list').hide();
    });
    $('#cityarea-option-list .item').click(function () {
        var regionId = $(this).attr('data-id');
        var regionName = $(this).attr('data-name');
        $('#cityarea_name').val(regionName).focus().blur();
        $('#cityarea_id').val(regionId);
        $('#cityarea-option-list').hide();
        GetSubRegion(regionId);
    });
    $('#sub-option-list .item').click(function(){
        var regionId = $(this).attr('data-id');
        var regionName = $(this).attr('data-name');
        $('#cityarea2_name').val(regionName).focus().blur();
        $('#cityarea2_id').val(regionId);
        $('#sub-option-list').hide();
    });
}

function UploaderDropFile(fileId) {
    $('#container_picture_' + fileId).remove();
    uploader.removeFile(fileId, true);
}

function CheckContact() {
    var hidePhone = $('#hide_phone').is(':checked');
    var wechat = $('#wechat').val();
    var qq = $('#qq').val();
    var userType = parseInt($('#user_type').val());
    if (isNaN(userType)) {
        userType = 2; //默认为个人会员类型
    }

    if (userType == 2 && hidePhone == true && wechat.length == 0 && qq.length == 0) {
        $('#wechat').addClass('Validform_error').next('.Validform_checktip').removeClass('Validform_right').addClass('Validform_wrong').html('选择隐藏手机号码时，微信和QQ至少填写一项');
        $('#qq').addClass('Validform_error').next('.Validform_checktip').removeClass('Validform_right').addClass('Validform_wrong').html('选择隐藏手机号码时，微信和QQ至少填写一项');
        return false;
    }
    if (wechat.lengh > 60) {
        $('#wechat').addClass('Validform_error').next('.Validform_checktip').removeClass('Validform_right').addClass('Validform_wrong').html('微信号码最多为60个字符');
        return false;
    }
    if (qq.lengh > 60) {
        $('#qq').addClass('Validform_error').next('.Validform_checktip').removeClass('Validform_right').addClass('Validform_wrong').html('QQ号码最多为60个字符');
        return false;
    }
    $('#wechat').removeClass('Validform_error').next('.Validform_checktip').removeClass('Validform_wrong').addClass('Validform_right').html('');
    $('#qq').removeClass('Validform_error').next('.Validform_checktip').removeClass('Validform_wrong').addClass('Validform_right').html('');
    return true;
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

function GetSubRegion(id) {
    var tmp = '<div class="item" data-id="0" data-name="不限">不限</div>';
    var region = regionData['cityarea_' + id.toString()];
    var count = region.length;
    for(var i=0; i< count; i++){
        tmp += '<div class="item" data-id="' + region[i].region_id + '" data-name="' + region[i].region_name + '">' + region[i].region_name + '</div>';
    }
    $('#sub-option-list').html(tmp);
    $('#sub-option-list .item').click(function(){
        var regionId = $(this).attr('data-id');
        var regionName = $(this).attr('data-name');
        $('#cityarea2_name').val(regionName).focus().blur();
        $('#cityarea2_id').val(regionId);
        $('#sub-option-list').hide();
    });
    //清除二级域名
    $('#cityarea2_id').val('0');
    $('#cityarea2_name').val('');
}

function checkBoxNum() {
    var form = document.forms['dataForm'];
    var i, j = 0;
    for (i = 0; i < form.length; i++) {
        var e = form[i];
        if (e.checked && e.type == 'checkbox' && e.name == 'house_feature[]') {
            j++;
            if (j == 5) {
                alert("房源特色最多只能选择4项！");
                return false;
                break;
            }
        }
    }
}

function selectallsupport(val) {
    var support = document.getElementsByName('house_support[]');
    if (val == true) {
        for (i = 0; i < support.length; i++) {
            support[i].checked = true;
        }
    } else {
        for (i = 0; i < support.length; i++) {
            support[i].checked = false;
        }
    }
}
function sethousethumb(obj) {
    $('#house_thumb_id').val($(obj).attr('pic-id'));
    $('.setindexpicbtn').val('设为封面');
    $(obj).val('封面');
    alert('设置封面成功');
}