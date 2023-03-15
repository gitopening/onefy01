/**
 * Created by Administrator on 2019/10/31.
 */

(function (root, $) {
    //引入样式表文件
    /*var js = document.scripts;
     filePath = js[js.length - 1].src.substring(0, js[js.length - 1].src.lastIndexOf("/") + 1);
     $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: filePath + 'tree.css'}).appendTo('head');*/
    root.Wechat = {
        //默认配置
        default: {
            container: '#wechat-qrcode-box',
            tokenInput: '#token',
            expireSeconds: 600,
            data: '',
            timestamp: 0
        },
        create: function (opts) {
            var options = $.extend(true, {}, this.default, opts);
            var Obj = {
                options: options,
                container: $(options.container),
                tokenInput: $(options.tokenInput),
                token: '',
                timestamp: options.timestamp,
                expireSeconds: options.expireSeconds,
                callback: options.callback,
                timer: null,
                _init: function () {
                    //this.showQrcode();
                },
                showQrcode: function () {
                    var that = this;
                    var now = parseInt(new Date().getTime() / 1000);
                    if (now - this.timestamp > that.expireSeconds) {
                        that.timestamp = now;
                        that.container.find('.message').removeClass('error').html('正在加载二维码').show();
                        //取得Token
                        $.post('/ajax/index.php', {
                            action: 'get_wechat_action_token',
                            type: that.options.type,
                            data: that.options.data,
                            member_db: that.options.memberDB,
                            t: new Date().getTime()
                        }, function (data) {
                            if (data.error == 0) {
                                that.token = data.token;
                                that.container.find('.message').hide();
                                var html = '<img src="' + data.qrcode_url + '" />';
                                that.container.find('.qrcode-container').append(html);
                                that.tokenInput.val(that.token);
                                that.checkWechatActionScanned();
                            } else {
                                that.container.find('.message').addClass('error').html(data.msg).show();
                                that.container.find('img').remove();
                                that.container.find('.qrcode-container').unbind().click(function () {
                                    that.timestamp = 0;
                                    that.showQrcode();
                                });
                            }
                        }, 'json');
                    }
                },
                checkWechatActionScanned: function () {
                    var that = this;
                    var now = parseInt(new Date().getTime() / 1000);
                    if (now - that.timestamp > that.expireSeconds) {
                        //超过有效期，提示点击重新获取
                        that.container.find('.message').addClass('error').html('二维码已过期，点击刷新').show();
                        that.container.find('img').remove();
                        that.container.find('.qrcode-container').unbind().click(function () {
                            that.showQrcode();
                        });
                        return false;
                    }
                    $.post('/ajax/index.php', {
                        action: 'check_wechat_action_scanned',
                        token: that.token,
                        member_db: that.options.memberDB,
                        t: new Date().getTime()
                    }, function (data) {
                        if (data.error == 0) {
                            if (data.is_scanned == 1) {
                                layer.closeAll();
                                that.timeStamp = now;

                                //回调函数
                                if (typeof that.callback === 'function') {
                                    that.callback(data);
                                }
                            } else {
                                that.timer = setTimeout(function () {
                                    that.checkWechatActionScanned();
                                }, 3000);
                            }
                        } else {
                            layer.closeAll();
                            that.container.find('.message').addClass('error').html(data.msg).show();
                            that.container.find('img').remove();
                            that.container.find('.qrcode-container').unbind().click(function () {
                                that.timestamp = 0;
                                that.showQrcode();
                            });
                        }
                    }, 'json');
                },
                destruct: function () {
                    clearTimeout(this.timer);
                    this.container.find('.qrcode-container img').remove();
                    this.token = '';
                    this.tokenInput.val('');
                }
            };
            //初始化
            Obj._init();
            return Obj;
        }
    };
})(this, jQuery);