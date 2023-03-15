/**
 * 晟讯二手车系统
 * 官方网站 http://www.suncent.cn
 * Copyright (c) 2016 石家庄晟讯网络科技有限公司 All rights reserved.
 * Licensed ( http://www.suncent.cn/licenses )
 * Author : net <geow@qq.com>
 * 多文件上传模块，封装jQuery、WebUploader和layer组件
 * 使用方法
 * var Uploader = FileUpload.create({
        pick : '#file-picker',
        listContainer : '#image-list',
        server : '/Admin/FileUpload/carTypePictureUpload/'
    });
 */

(function(root, $, WebUploader, layer){
    //引入样式表文件
    var js = document.scripts;
    filePath = js[js.length - 1].src.substring(0, js[js.length - 1].src.lastIndexOf("/") + 1);
    $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: filePath + 'imagelist.css'}).appendTo('head');
    root.FileUpload = {
        //默认配置
        default : {
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick : '#file-picker',
            autoUpload : true,
            // swf文件路径
            swf : 'Uploader.swf',
            // 文件接收服务端。
            server : '/Admin/FileUpload/carTypePictureUpload/',
            // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
            resize : false,
            // 默认只允许选择图片文件。
            accept : {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/*'
            },
            imageHeight : 480,
            imageWidth : 640,
            listContainer : '#image-list',
            mainPicInput : '#litpic',
            mainPicShowContainer : '#litpic-show',
            defaultThumb : '/Public/Image/nophoto.png'
        },
        layerIndex : {},
        create : function (opts) {
            var options = $.extend(true, {}, this.default, opts);
            var Obj = {
                uploader : {},
                options : options,
                _init : function () {
                    //初始化上传组件
                    var list = this;
                    //设置主图隐藏文本框
                    if (this.options.mainPicInput.length > 0 && $(this.options.mainPicInput).length == 0) {
                        var inputName = this.options.mainPicInput.replace('#', '');
                        var inputId = this.options.mainPicInput;
                        $(this.options.listContainer).after($('<input type="hidden" name="' + inputName + '" id="' + inputId + '" value="" />'));
                    }

                    //列表中已存在项目添加操作按钮
                    var itemNode = $(this.options.listContainer).find('.item');
                    itemNode.append(this.getButtons());
                    this.msgBox();
                    this.addItemHover(itemNode);
                    //初始化图片上传组件，已存在则不再创建
                    list.uploader = WebUploader.create({
                        auto: this.options.autoUpload,
                        swf: this.options.swf,
                        server: this.options.server,
                        pick: this.options.pick,
                        resize: this.options.resize,
                        accept: this.options.accept
                    });

                    // 当有文件添加进来的时候
                    list.uploader.on('fileQueued', function (file) {
                        // 创建缩略图
                        // 如果为非图片文件，可以不用调用此方法。
                        //todo js判断文件类型,图片创建缩略图
                        list.uploader.makeThumb(file, function (error, src) {
                            if (error) {
                                /*list.addItem({
                                    fileId : file.id,
                                    title : '',
                                    src : src,
                                    isExist : false,
                                    isMain : false
                                });*/
                                return;
                            } else {
                                //list.addItem(file.id, '', src, false, false);
                                list.addItem({
                                    fileId : file.id,
                                    title : '',
                                    src : src,
                                    isExist : false,
                                    isMain : false
                                });
                            }
                        }, list.options.imageWidth, list.options.imageHeight);
                        FileUpload.layerIndex = layer.load(1, {
                            shade: [0.6, '#333'] //0.1透明度的白色背景
                        });
                    });
                    // 显示上传中状态
                    list.uploader.on('uploadProgress', function (file, percentage) {
                        $('#item-' + file.id).find('.upload-state').html('上传中...');
                    });

                    // 文件上传成功，设置Item上传成功, 用样式标记上传成功。
                    list.uploader.on('uploadSuccess', function (file, response) {
                        $('#item-' + file.id).find('.upload-state').html('上传成功').addClass('upload-state-success');
                        //设置链接信息
                        $('#picurl-' + file.id).val(response.url);
                        $('#item-' + file.id).find('.pic-container').css('background-image', 'url(' + response.url + ')');
                    });

                    // 文件上传失败，显示上传出错。
                    list.uploader.on('uploadError', function (file, reason) {
                        $('#item-' + file.id).find('.upload-state').html('上传失败').addClass('upload-state-error');
                    });

                    // 完成上传完了，成功或者失败，先删除进度条。
                    list.uploader.on('uploadComplete', function (file) {
                        layer.close(FileUpload.layerIndex);
                        setTimeout(function () {
                            $('.upload-state-success').fadeOut();
                        }, 3000);
                    });
                },
                addList : function (data) {
                    var list = this;
                    $(data).each(function (index, item) {
                        list.addItem(item);
                    });
                },
                addItem : function (data) {
                    /*var data = {
                        fileId : file.id,
                        title : '',
                        src : src,
                        isExist : false,
                        isMain : false
                    };*/
                    var mainValue = data.isMain == true ? '1' : '0';
                    var item = $('<div class="item" id="item-' + data.fileId + '"></div>');
                    //todo 判断是否是合法图片
                    var picUrl = '';
                    var picStatusString = '等待上传';
                    if (data.isExist == true) {
                        picUrl = data.src;
                        picStatusString = '';
                    }
                    $('<div class="pic-container" style="background-image: url(' + data.src + ');"><div class="upload-state">' + picStatusString + '</div></div>').appendTo(item);
                    $('<div class="pic-name"><input type="hidden" name="picurl[]" id="picurl-' + data.fileId + '" value="' + picUrl + '" /><input type="hidden" name="mainpic[]" id="mainpic-' + data.fileId + '" value="' + mainValue + '" /><input type="text" name="picname[]" id="picname-' + data.fileId + '" value="' + data.title + '" placeholder="输入名称" /></div>').appendTo(item);
                    if (data.isMain == true) {
                        this.setMain(item, data);
                    }
                    this.addItemHover(item);
                    item.append(this.getButtons());
                    $(this.options.listContainer).append(item);
                    this.msgBox();
                },
                setMain : function (item, src) {
                    $(this.options.mainPicInput).val(src);
                    $(this.options.mainPicShowContainer).css('background-image', 'url(' + src + ')');
                    $(this.options.listContainer).find('input[name="mainpic[]"]').val('0');
                    $(this.options.listContainer).find('.main-pic').remove();
                    $(item).find('.pic-container').append('<div class="main-pic">主图</div>');
                    $(item).find('input[name="mainpic[]"]').val('1');
                },
                addItemHover : function (item) {
                    $(item).hover(function () {
                        $(this).addClass('hover');
                    }, function () {
                        $(this).removeClass('hover');
                    });
                },
                getButtons : function () {
                    var list = this;
                    var editBox = $('<div class="btn-box"></div>');

                    //设置为主图按钮
                    $('<div class="main-pic-btn fa fa-home" title="设为主图"></div>').click(function () {
                        var parentNode = $(this).closest('.item');
                        var url = parentNode.find('input[name="picurl[]"]').val();
                        if (url.length == 0) {
                            layer.msg('图片链接地址不正确，设置失败',{ icon: 2, time: 1500});
                            return false;
                        }
                        list.setMain(parentNode, url);
                        layer.msg('主图设置成功',{ icon: 1, time: 1500});

                    }).appendTo(editBox);
                    //删除按钮
                    $('<div class="delete-btn fa fa-trash-o" title="删除"></div>').click(function () {
                        var parentNode = $(this).closest('.item');
                        //删除封面图片
                        if ($(parentNode).find('input[name="mainpic[]"]').val() == '1') {
                            $(list.options.mainPicInput).val('');
                            $(list.options.mainPicShowContainer).css('background-image', 'url()');
                        }
                        var nodeId = parentNode.attr('id');
                        if (nodeId.length >= 0) {
                            var tmp = nodeId.split('-');
                            //webuploader中删除相关文件信息
                            if (nodeId.indexOf('WU_FILE') > -1) {
                                list.uploader.removeFile(tmp[1], true);
                            }
                        }
                        //todo 是否同时删除原文件


                        parentNode.fadeOut(500, function () {
                            this.remove();
                            list.msgBox();
                        });
                    }).appendTo(editBox);
                    return editBox;
                },
                msgBox : function () {
                    var itemCount = $(this.options.listContainer).find('.item').length;
                    if (itemCount == 0) {
                        $(this.options.listContainer).html('<div class="msg">还没有相关图片</div>');
                        $(this.options.listContainer).find('.msg').show();
                    } else {
                        $(this.options.listContainer).find('.msg').remove();
                    }
                }
            };
            //初始化
            Obj._init();
            return Obj;
        }
    };
})(this, jQuery, WebUploader, layer);