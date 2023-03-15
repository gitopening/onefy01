/**
 * Created by net on 2016/8/17.
 */
$(document).ready(function () {
    $('#nav-menu-btn').click(function () {
        $(this).toggleClass('active');
        $('#main-nav').stop().slideToggle(120);
    });
});

function DoHomeSearch() {
    var column = $('#search-type-name').attr('data-type');
    var keywords = $('#home-search-keywords').val();
    var url = '';
    switch (column) {
        case 'sale':
            url = 'sale/list_0_0_0_0-0_0_0-0_0_2_0_1_' + keywords + '.html';
            break;
        case 'rent':
            url = 'rent/list_0_0_0_0-0_0_0-0_0_2_0_1_' + keywords + '.html';
            break;
        case 'person_sale':
            url = 'sale/list_2_0_0_0-0_0_0-0_0_2_0_1_' + keywords + '.html';
            break;
        case 'person_rent':
            url = 'rent/list_2_0_0_0-0_0_0-0_0_2_0_1_' + keywords + '.html';
            break;
        case 'xzl':
            url = 'xzlcz/list_0_0_0_0-0_0_0-0_0_2_0_1_' + keywords + '.html';
            break;
        case 'sp':
            url = 'spcz/list_0_0_0_0-0_0_0-0_0_2_0_1_' + keywords + '.html';
            break;
    }
    if (url.length == 0) {
        alert('搜索参数错误');
        return false;
    }
    window.location.href = url;
    return true;
}

function ResizeHouseListPicture() {
    var container = $('.house-list');
    var picNode = container.find('.pic');
    var itemWidth = picNode.width();
    var itemHeight = itemWidth / 1.5;
    picNode.height(itemHeight);
    /*container.find('img').css('width', '100%').css('height', 'auto').each(function (index, element) {
        if ($(element).width() > $(element).height() * 1.5) {
            $(element).css('width', 'auto').css('height', '100%');
        } else {
            $(element).css('width', '100%').css('height', 'auto');
        }
    });*/
}

function ResizeBrokerFace() {
    var container = $('.broker-list');
    var picNode = container.find('.pic');
    var itemWidth = picNode.width();
    var itemHeight = itemWidth;
    picNode.height(itemHeight);
    /*container.find('img').css('width', '100%').css('height', 'auto').each(function (index, element) {
        if ($(element).width() > $(element).height()) {
            $(element).css('width', 'auto').css('height', '100%');
        } else {
            $(element).css('width', '100%').css('height', 'auto');
        }
    });*/
}

function ResizePicture() {
    ResizeHouseListPicture();
    ResizeBrokerFace();
}

function GetInputValue(obj, defaultValue) {
    var val = $('#' + obj).val();
    if (val == undefined) {
        if (defaultValue) {
            val = defaultValue;
        } else {
            val = '';
        }
    }
    return val;
}

function GetSubRegion(parentId, obj) {
    $.getJSON('/ajax.php', {
        action: 'get_sub_region',
        parent_id: parentId,
        t: new Date().getTime()
    }, function (data) {
        var tmp = '<option value="0" selected="selected">请选择所在街道</option>';
        $(data.list).each(function (index, item) {
            tmp += '<option value="' + item.region_id + '">' + item.region_name + '</option>';
        });
        $(obj).html(tmp);
    });
}