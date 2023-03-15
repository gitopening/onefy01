/**
 * 官方网站 http://www.hictech.cn
 * Copyright (c) 2017 河北海聪科技有限公司 All rights reserved.
 * Licensed (http://www.hictech.cn/licenses)
 * Author : net <geow@qq.com>
 */
(function (root, $) {
    //引入样式表文件
    /*var js = document.scripts;
     filePath = js[js.length - 1].src.substring(0, js[js.length - 1].src.lastIndexOf("/") + 1);
     $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: filePath + 'tree.css'}).appendTo('head');*/
    root.VIP = {
        //默认配置
        default: {
            container: '#vip-dialog'
        },
        create: function (opts) {
            var options = $.extend(true, {}, this.default, opts);
            var Obj = {
                options: options,
                container: $(options.container),
                data: options.data,
                _init: function () {
                    var currentObj = this;
                    $('#user_level_id').attr('default-user-level-id', $('#user_level_id').val());
                    //生成用户类型
                    this.getTypeList();
                    //this.setDefaultTypeList();
                    //生成用户续费时间
                    this.getTimeList();
                    //积分变化
                    $('#use_score').click(function () {
                        if ($(this).prop('checked') == true) {
                            $('#score').prop('disabled', false).select();
                        } else {
                            $('#score').prop('disabled', true);
                        }
                        currentObj.getPayMoney();
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
                        currentObj.getPayMoney();
                    });*/
                },
                setDefaultTypeList: function () {
                    var typeListItem = $('#vip-type-list .active');
                    var defaultUserLevelId = $('#user_level_id').attr('default-user-level-id');
                    typeListItem.removeClass('on');
                    typeListItem.each(function (index, item) {
                        var value = $(this).attr('data-id');
                        if (defaultUserLevelId == value) {
                            $(this).addClass('on');
                        }
                    });
                },
                getTypeList: function () {
                    var vipTips = '';
                    var userCurrentLevelId = parseInt($('#user_current_level_id').val());
                    if (isNaN(userCurrentLevelId)) {
                        userCurrentLevelId = 1;
                    }
                    var userLevelId = parseInt($('#user_level_id').attr('default-user-level-id'));
                    var userLeftTime = parseFloat($('#user_left_time').val());
                    if (isNaN(userLevelId)) {
                        userLevelId = 1;
                    }
                    var listContainer = $('#vip-type-list');
                    var userType = parseInt($('#user_type').val());
                    if (isNaN(userType)) {
                        userType = 1;
                    }
                    if (userType != 1 && userType != 2) {
                        userType = 1;
                    }
                    var content = '';
                    $(this.data).each(function (key, item) {
                        var className = ' active';
                        if (item.id < userLevelId) {
                            className = ' disabled';
                        }
                        var dayRefresh = userType == 1 ? item.house_day_refresh_broker : item.house_day_refresh_person;
                        var houseStock = userType == 1 ? item.house_stock_broker : item.house_stock_person;
                        var money = item.price_rule[0]['rule_price'];
                        if (userLeftTime <= 0 || userCurrentLevelId == 1) {
                            vipTips = '<div class="circle-icon"></div>开通' + item.level_name;
                        } else if (item.id < userCurrentLevelId) {
                            vipTips = '';
                        } else if (item.id == userCurrentLevelId) {
                            vipTips = '<div class="circle-icon"></div>续费' + item.level_name;
                        } else if (item.id > userCurrentLevelId) {
                            vipTips = '<div class="circle-icon"></div>升级' + item.level_name;
                        }
                        content += '<div class="item' + className + '" data-id="' + item.id + '">' +
                            '<div class="box-content">' +
                            '<div class="item-title">' + item.level_name + '</div><i></i>' +
                            '<div class="text">每天可发布房源<span class="number">' + dayRefresh + '</span>条</div>' +
                            '<div class="text">库存房源<span class="number">' + houseStock + '</span>条</div>' +
                            '<div class="text">每条房源每隔<span class="number">1</span>小时刷新<span class="number">1</span>次</div>' +
                            '<div class="text">套餐价格：<span class="number price">' + money + '</span>元/月</div>' +
                            '</div>' +
                            '<div class="item-tips">' + vipTips + '</div>' +
                            '</div>';
                    });
                    listContainer.html(content);
                    var levelId = parseInt($('#level_id').val());
                    if (isNaN(levelId)) {
                        levelId = 0;
                    }
                    if (levelId > userLevelId) {
                        $('#user_level_id').val(levelId);
                        $(listContainer.find('.active')).each(function (index, item) {
                            var id = parseInt($(item).attr('data-id'));
                            if (levelId == id) {
                                $(item).addClass('on');
                                $('#user_level_id').val(id);
                            }
                        });
                    } else {
                        listContainer.find('.active').eq(0).addClass('on');
                        $('#user_level_id').val(listContainer.find('.active').eq(0).attr('data-id'));
                    }
                    this.bindTypeListEvent();
                },
                bindTypeListEvent: function () {
                    var itemNode =  $('#vip-type-list .active').not('.disabled');
                    itemNode.click(function () {
                        var userLevelId = $('#user_level_id').val();
                        var value = $(this).attr('data-id');
                        if (userLevelId != value) {
                            itemNode.removeClass('on');
                            $(this).addClass('on');
                            $('#user_level_id').val(value);
                            Obj.getTimeList();
                        }
                    });
                },
                getTimeList: function () {
                    var listContainer = $('#time-list');
                    var userLevelId = $('#user_level_id').val();
                    var userDefaultLevelId = $('#user_level_id').attr('default-user-level-id');
                    var userLevelIndex = $('#vip-type-list .item').index($('#vip-type-list .on'));
                    var content = this.getTimeOption(this.data[userLevelIndex]['price_rule']);
                    listContainer.html(content);
                    var userLeftTime = parseFloat($('#user_left_time').val());

                    if (userLevelId > userDefaultLevelId) {
                        $(listContainer.find('.item')).each(function (key, item) {
                            var openTime = parseInt($(item).attr('data-time'));
                            if (isNaN(openTime)) {
                                $(item).removeClass('active').addClass('disabled');
                            } else if (openTime < userLeftTime) {
                                $(item).removeClass('active').addClass('disabled');
                            }
                        });
                    }
                    //默认第1个价格
                    var itemLength = listContainer.find('.item').length;
                    var itemIndex = 0;
                    if (itemLength < 3) {
                        itemIndex = itemLength - 1;
                    }
                    if (listContainer.find('.item').eq(itemIndex).hasClass('active')) {
                        listContainer.find('.item').eq(itemIndex).addClass('on');
                        $('#price_index').val(itemIndex);
                    } else {
                        listContainer.find('.active').eq(0).addClass('on');
                        $('#price_index').val(listContainer.find('.item').index(listContainer.find('.active').eq(0)));
                    }
                    //listContainer.find('.active').eq(0).addClass('on');
                    //$('#price_index').val(listContainer.find('.item').index(listContainer.find('.active').eq(0)));

                    this.getPrice();
                    this.bindTimeListEvent();
                },
                getTimeOption: function (data) {
                    var content = '';
                    $(data).each(function (key, item) {
                        var discount = '';
                        if (item.rule_discount != '') {
                            discount = '<div class="discount">' + item.rule_discount + '</div>';
                        }
                        content += '<div class="item active" data-price="' + item.rule_price + '" data-id="' + key + '" data-time="' + item.rule_time + '">' +
                            '<div class="text">' + item.rule_title + '</div><i></i>' + discount +
                            '</div>';
                    });
                    return content;
                },
                bindTimeListEvent: function () {
                    var CurrentObj = this;
                    var itemNode = $('#time-list .active');
                    itemNode.click(function () {
                        itemNode.removeClass('on');
                        $(this).addClass('on');
                        var index = $('#time-list .item').index($(this));
                        $('#price_index').val(index);
                        CurrentObj.getPrice();
                    });
                },
                getPrice: function () {
                    var currentObj = this;
                    var formData = $('#pay_form').serializeArray();
                    formData.t = new Date().getTime();
                    formData.action = 'get_price';
                    //$('.total-fee').html('需要支付：<span class="price-number">计算中...</span>');
                    $('.select-tips').html('<span class="analyze">计算中...</span>');
                    $('.member-expire-time').hide();
                    $.post('/member/member_vip.php', {
                        action: 'get_price',
                        t: new Date().getTime(),
                        order_type: $('#order_type').val(),
                        user_level_id: $('#user_level_id').val(),
                        price_index: $('#price_index').val()
                    }, function (data) {
                        $('.total-price-box').html('总计：¥<span class="total-price">' + data.price + '</span>元');
                        $('#total_price').val(data.price);

                        //计算实际需支付金额
                        currentObj.getPayMoney();

                        if (data.expire_time != '') {
                            $('.select-tips').html('您选择的是：<span>' + data.tips + '</span>');
                            $('.select-tips').show();
                            //$('.member-expire-time').html('购买成功后您的“' + data.level_name + '”到期日为：' + data.expire_time);
                            //$('.member-expire-time').show();
                        }
                        if (data.error == 1) {
                            alert(data.msg);
                        }
                    }, 'json');
                },
                getPayMoney: function () {
                    var totalPrice = parseFloat($('#total_price').val());
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
                    $('.total-fee').html('需要支付：&yen;<span class="total-money">' + totalFee + '</span>元');
                }
            };
            //初始化
            Obj._init();
            return Obj;
        }
    };
})(this, jQuery);