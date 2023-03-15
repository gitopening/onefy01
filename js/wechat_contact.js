/**
 * Created by Administrator on 2019/10/31.
 */

(function (root, $) {
    //引入样式表文件
    /*var js = document.scripts;
     filePath = js[js.length - 1].src.substring(0, js[js.length - 1].src.lastIndexOf("/") + 1);
     $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: filePath + 'tree.css'}).appendTo('head');*/
    root.WechatContact = {
        //默认配置
        default: {
            container: '#show-phone-box',
            showContainer: '',
            expireSeconds: 600,
            timestamp: 0
        },
        create: function (opts) {
            var options = $.extend(true, {}, this.default, opts);
            var Obj = {
                options: options,
                container: $(options.container),
                showContainer: $(options.showContainer),
                timestamp: options.timestamp,
                expireSeconds: options.expireSeconds,
                _init: function () {
                    var that = this;
                    that.container.hover(function () {
                        that.container.find('.wechat-qrcode-container').show();
                        that.showWechatQrcode();
                    }, function () {
                        that.container.find('.wechat-qrcode-container').hide();
                    });
                },
                showWechatQrcode: function () {
                    var that = this;
                    var now = parseInt(new Date().getTime() / 1000);
                    if (now - this.timestamp > that.expireSeconds) {
                        that.timestamp = now;
                        that.container.find('.message').removeClass('error').html('正在加载').show();
                        //取得Token
                        $.post('/ajax/index.php', {
                            action: 'get_wechat_action_token',
                            type: that.options.type,
                            house_type: that.options.houseType,
                            house_id: that.options.houseId,
                            member_db: that.options.memberDB,
                            t: new Date().getTime()
                        }, function (data) {
                            if (data.error == 0) {
                                that.container.find('.message').hide();
                                var html = '<img src="' + data.qrcode_url + '" />';
                                that.container.find('.qrcode-container').append(html);
                                that.checkWechatActionScanned(data.token);
                            } else {
                                that.container.find('.message').addClass('error').html(data.msg).show();
                                that.container.find('img').remove();
                                that.container.find('.qrcode-container').unbind().click(function () {
                                    that.timestamp = 0;
                                    that.showWechatQrcode();
                                });
                            }
                        }, 'json');
                    }
                },
                checkWechatActionScanned: function (token) {
                    var that = this;
                    var now = parseInt(new Date().getTime() / 1000);
                    if (now - that.timestamp > that.expireSeconds) {
                        //超过有效期，提示点击重新获取
                        that.container.find('.message').addClass('error').html('二维码已过期，点击刷新').show();
                        that.container.find('img').remove();
                        that.container.find('.qrcode-container').unbind().click(function () {
                            that.showWechatQrcode();
                        });
                        return false;
                    }
                    $.post('/ajax/index.php', {
                        action: 'check_wechat_action_scanned',
                        token: token,
                        member_db: that.options.memberDB,
                        t: new Date().getTime()
                    }, function (data) {
                        if (data.error == 0) {
                            if (data.is_scanned == 1) {
                                layer.closeAll();
                                layer.msg(data.msg);
                                that.timeStamp = now;

                                //显示电话到页面中
                                var html = '';
                                if (data.data_info.owner_phone != undefined && data.data_info.owner_phone != '') {
                                    html += '<dl><dt>联系电话：</dt><dd><div class="telephone">' + data.data_info.owner_phone + '</div></dd></dl>';
                                    if (data.data_info.hide_phone != undefined && data.data_info.hide_phone == 1) {
                                        html += '<dl><dt></dt><dd>（该用户已设置隐藏电话，您可以通过微信、QQ联系）</dd></dl>';
                                    }
                                }
                                if (data.data_info.wechat != undefined && data.data_info.wechat != '') {
                                    html += '<dl><dt>微　　信：</dt><dd><div class="font16">' + data.data_info.wechat + '</div></dd></dl>';
                                }
                                if (data.data_info.qq != undefined && data.data_info.qq != '') {
                                    html += '<dl><dt>QQ：</dt><dd><div class="font16">' + data.data_info.qq + '</div></dd></dl>';
                                }
                                that.showContainer.html(html);
                                that.container.remove();
                            } else {
                                setTimeout(function () {
                                    that.checkWechatActionScanned(token);
                                }, 3000);
                            }
                        } else {
                            layer.closeAll();
                            that.container.find('.message').addClass('error').html(data.msg).show();
                            that.container.find('img').remove();
                            that.container.find('.qrcode-container').unbind().click(function () {
                                that.timestamp = 0;
                                that.showWechatQrcode();
                            });
                        }
                    }, 'json');
                }
            };
            //初始化
            Obj._init();
            return Obj;
        }
    };
})(this, jQuery);