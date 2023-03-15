/**
 * Created by net on 2016/7/19.
 */
$(document).ready(function () {
    $('.filter-type li').click(function () {
        $('.filter-type li').removeClass('current');
        $(this).addClass('current');
        var list_type = $(this).attr('data-list');
        if (list_type == 'letter') {
            $('#area-list').hide();
            $('#letter-list').show();
        } else if (list_type == 'area') {
            $('#letter-list').hide();
            $('#area-list').show();
        }
    });

    $('#city_name').autocomplete('/ajax/index.php', {
        max: 15,    //列表里的条目数
        minChars: 1,    //自动完成激活之前填入的最小字符
        width: 138,     //提示的宽度，溢出隐藏
        scrollHeight: 300,   //提示的高度，溢出显示滚动条
        matchContains: true,    //包含匹配，就是data参数里的数据，是否只要包含文本框里的数据就显示
        autoFill: false,    //自动填充
        cacheLength: 0,
        dataType: 'json',
        extraParams: {
            action : 'search_city'
        },
        parse: function (data) {
            //解释返回的数据，把其存在数组里
            var parsed = [];
            if (data.error == 0 && data.data.length > 0) {
                var dataList = data.data;
                for (var i = 0; i < dataList.length; i++) {
                    parsed[i] = {
                        data: dataList[i],
                        value: dataList[i].id,
                        //返回的结果显示内容
                        result: dataList[i].title
                    };
                }
            }
            return parsed;
        },
        formatItem: function (item) {
            //显示下拉列表的内容
            var tmpl = '<div class="list-item"><span class="title">' + item.city_name + '</span></div>';
            return tmpl;
        },
        formatMatch: function (item) {
            return item.id;
        },
        formatResult: function (item) {
            return item.id;
        }
    }).result(function (event, item, formatted) {
        //跳转到相应网站
        window.location.href = item.url;
    });
});