/**
 * Created by Administrator on 2018/6/29.
 */
(function (root, $) {
    //引入样式表文件
    /*var js = document.scripts;
     filePath = js[js.length - 1].src.substring(0, js[js.length - 1].src.lastIndexOf("/") + 1);
     $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: filePath + 'tree.css'}).appendTo('head');*/
    root.Picture = {
        //默认配置
        default: {
            container: '#pic-show-box',
            prevArrow: '.prev-arrow',
            nextArrow: '.next-arrow',
            itemNode: null,
            itemWidth: 0,
            itemCount: 0,
            showPictureNumber: 9    //列表中显示出来的图片数量
        },
        create: function (opts) {
            var options = $.extend(true, {}, this.default, opts);
            var Obj = {
                options: options,
                container: $(options.container),
                prevArrow: $(options.prevArrow),
                nextArrow: $(options.nextArrow),
                currentIndex: 0,
                _init: function () {
                    //var CurrentObj = this;
                    this.itemNode = this.container.find('.thumb-list li');
                    this.itemWidth = this.itemNode.outerWidth(true);
                    this.itemCount = this.itemNode.length;
                    this.container.find('.thumb-list ul').width(this.itemWidth * this.itemCount);
                    var currentIndex = this.itemNode.index(this.container.find('.active'));
                    if (isNaN(currentIndex)) {
                        this.currentIndex = 0;
                    } else {
                        this.currentIndex = currentIndex
                    }
                    this.setCurrentIndex();
                    this.bindEvent();
                },
                bindEvent: function () {
                    var CurrentObj = this;
                    var videoPlayer = this.container.find('.video-player');

                    $(this.prevArrow).click(function () {
                        CurrentObj.currentIndex--;
                        if (CurrentObj.currentIndex < 0) {
                            CurrentObj.currentIndex = CurrentObj.itemNode.length - 1;
                        }
                        CurrentObj.itemNode.removeClass('active');
                        CurrentObj.itemNode.eq(CurrentObj.currentIndex).addClass('active');
                        CurrentObj.show();
                        CurrentObj.scroll();
                    });
                    $(this.nextArrow).click(function () {
                        CurrentObj.currentIndex++;
                        if (CurrentObj.currentIndex > (CurrentObj.itemCount - 1)) {
                            CurrentObj.currentIndex = 0;
                        }
                        CurrentObj.itemNode.removeClass('active');
                        CurrentObj.itemNode.eq(CurrentObj.currentIndex).addClass('active');
                        CurrentObj.show();
                        CurrentObj.scroll();
                    });
                    this.container.find('.thumb-list img').click(function () {
                        CurrentObj.itemNode.removeClass('active');
                        $(this).closest('li').addClass('active');
                        CurrentObj.currentIndex = CurrentObj.getCurrentIndex();
                        CurrentObj.show();
                        CurrentObj.scroll();

                        if (CurrentObj.currentIndex == 0) {
                            CurrentObj.container.find('.play-btn').show();
                            CurrentObj.container.find('.video-box').show();
                        } else {
                            CurrentObj.container.find('.play-btn').hide();
                            CurrentObj.container.find('.video-box').hide();
                        }
                        videoPlayer.get(0).pause();
                        videoPlayer.hide();
                    });
                    this.container.find('.big-pic-show img').click(function () {
                        CurrentObj.showPlayer();
                    });
                    $('.pic-list .desc-image img').click(function () {
                        var currentIndex = $('.pic-list .desc-image img').index($(this));
                        CurrentObj.showPlayer(currentIndex);
                    });

                    this.container.find('.play-btn').click(function () {
                        $(this).hide();
                        videoPlayer.show();
                        videoPlayer.get(0).play();
                    });
                },
                getCurrentIndex: function () {
                    return this.itemNode.index(this.container.find('.active'));
                },
                show: function () {
                    var bigImg = this.container.find('.active img').attr('thumb-pic');
                    this.container.find('.big-pic-show img').attr('src', '').attr('src', bigImg);
                    this.setCurrentIndex();
                },
                setCurrentIndex: function () {
                    this.container.find('.image-index').html((this.currentIndex + 1).toString() + ' / ' + this.itemCount.toString()).show();
                },
                scroll: function (direction) {
                    var maxMarginLeft = this.itemWidth * (this.itemNode.length - this.options.showPictureNumber);
                    var currentIndex = this.getCurrentIndex();
                    var marginLeft = 0;
                    var middleNumber = Math.ceil(this.options.showPictureNumber / 2);
                    if (currentIndex >= middleNumber) {
                        marginLeft = this.itemWidth * (currentIndex - (middleNumber - 1));
                        if (marginLeft >= maxMarginLeft) {
                            marginLeft = maxMarginLeft;
                        }
                        //this.container.find('.thumb-list ul').css('margin-left', -marginLeft);
                        this.container.find('.thumb-list ul').stop().animate({'margin-left': '-' + marginLeft.toString() + 'px'}, 'fast');
                    } else {
                        //this.container.find('.thumb-list ul').css('margin-left', 0);
                        this.container.find('.thumb-list ul').stop().animate({'margin-left': '0'}, 'fast');
                    }

                    if (currentIndex == 0) {
                        this.container.find('.play-btn').show();
                        this.container.find('.video-box').show();
                    } else {
                        this.container.find('.play-btn').hide();
                        this.container.find('.video-box').hide();
                    }
                    var videoPlayer = this.container.find('.video-player');
                    videoPlayer.get(0).pause();
                    videoPlayer.hide();
                },
                showPlayer: function (playerImageIndex) {
                    var CurrentObj = this;
                    if (isNaN(playerImageIndex)) {
                        playerImageIndex = this.currentIndex;
                    }
                    var imageUrl = this.getPlayerImageUrl(playerImageIndex);
                    $('body').append('<div class="picture-player" id="picture-player"><div class="show-picture"><div class="picture-box"><div class="picture-box-inner"><img src="' + imageUrl + '" /></div></div></div><div class="mask"></div><div class="prev-btn"></div><div class="next-btn"></div><div class="close-btn"></div><div class="image-index"></div></div>');

                    var playerContainer = $('#picture-player');
                    playerContainer.find('.image-index').html((playerImageIndex + 1).toString() + ' / ' + CurrentObj.itemCount.toString()).show();
                    playerContainer.find('.close-btn').click(function () {
                        $('#picture-player').remove();
                    });

                    CurrentObj.setPictureHeight();
                    $(window).resize(function () {
                        CurrentObj.setPictureHeight();
                    });

                    playerContainer.find('.prev-btn').click(function () {
                        playerImageIndex--;
                        playerImageIndex = playerImageIndex < 0 ? (CurrentObj.itemCount - 1) : playerImageIndex;
                        imageUrl = CurrentObj.getPlayerImageUrl(playerImageIndex);
                        CurrentObj.setPlayerImage(imageUrl);
                        playerContainer.find('.image-index').html((playerImageIndex + 1).toString() + ' / ' + CurrentObj.itemCount.toString()).show();
                    });

                    playerContainer.find('.next-btn').click(function () {
                        playerImageIndex++;
                        playerImageIndex = playerImageIndex >= CurrentObj.itemCount ? 0 : playerImageIndex;
                        imageUrl = CurrentObj.getPlayerImageUrl(playerImageIndex);
                        CurrentObj.setPlayerImage(imageUrl);
                        playerContainer.find('.image-index').html((playerImageIndex + 1).toString() + ' / ' + CurrentObj.itemCount.toString()).show();
                    });
                },
                setPictureHeight: function () {
                    var containerHeight = $('#picture-player .show-picture').height();
                    $('#picture-player .picture-box img').css('max-height', containerHeight);
                },
                getPlayerImageUrl: function (index) {
                    return this.itemNode.eq(index).find('img').attr('big-pic');
                },
                setPlayerImage: function (imageUrl) {
                    $('#picture-player .picture-box img').attr('src', '').attr('src', imageUrl);
                }
            };
            //初始化
            Obj._init();
            return Obj;
        }
    };
})(this, jQuery);