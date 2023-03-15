/**
 * Created by Administrator on 2018/10/13.
 */

(function (root, $) {
    root.FlashBannerScroll = {
        //默认配置
        default: {
            container: '#flash-banner',
            prevArrow: '.prev-arrow',
            nextArrow: '.next-arrow',
            autoInterval: 5000,
            itemNode: null,
            itemWidth: 0
        },
        create: function (opts) {
            var options = $.extend(true, {}, this.default, opts);
            var Obj = {
                options: options,
                container: $(options.container),
                prevArrow: $(options.container).find(options.prevArrow),
                nextArrow: $(options.container).find(options.nextArrow),
                autoInterval: options.autoInterval,
                scrollLeft: 0,
                currentIndex: 0,
                maxIndex: 0,
                timer: null,
                _init: function () {
                    this.container.find('.list').append(this.container.find('.list').html());
                    this.itemNode = this.container.find('.pic-item');
                    this.maxIndex = this.itemNode.length / 2;
                    if (this.maxIndex < 0) {
                        this.maxIndex = 0;
                    }
                    this.resize();
                    this.bindEvent();
                    var CurrentObj = this;
                    CurrentObj.timer = setTimeout(function () {
                        CurrentObj.scrollNext();
                    }, CurrentObj.autoInterval);
                    $(window).resize(function () {
                        CurrentObj.resize();
                    });
                },
                resize: function () {
                    //this.itemWidth = $(window).width();
                    this.itemWidth = $('#flash-banner').width();
                    this.container.height($(window).height() * 0.88);
                    this.itemNode.width(this.itemWidth);
                    this.container.find('.list').width(this.itemNode.length * this.itemWidth);
                    //this.container.find('.pic-list-box').stop().scrollLeft(this.currentIndex * this.itemWidth);
                    this.container.find('.list').css('transform', 'translateX(-' + this.currentIndex * this.itemWidth + 'px)');
                },
                bindEvent: function () {
                    var CurrentObj = this;
                    $(this.prevArrow).click(function () {
                        clearTimeout(CurrentObj.timer);
                        CurrentObj.scrollPrev();
                    });
                    $(this.nextArrow).click(function () {
                        clearTimeout(CurrentObj.timer);
                        CurrentObj.scrollNext();
                    });
                    $(this.container.find('.pic-item')).hover(function () {
                        clearTimeout(CurrentObj.timer);
                    }, function () {
                        CurrentObj.timer = setTimeout(function () {
                            CurrentObj.scrollNext();
                        }, CurrentObj.autoInterval);
                    });
                },
                scrollPrev: function () {
                    var CurrentObj = this;
                    if (this.currentIndex <= 0) {
                        this.currentIndex = this.maxIndex;
                        //this.container.find('.pic-list-box').stop().scrollLeft(this.currentIndex * this.itemWidth);
                        //this.container.find('.pic-list-box').stop().scrollLeft(this.currentIndex * this.itemWidth);
                        this.container.find('.list').removeClass('animate').css('transform', 'translateX(-' + this.currentIndex * this.itemWidth + 'px)');
                        setTimeout(function () {
                            CurrentObj.currentIndex--;
                            CurrentObj.scroll();
                        }, 50);
                    } else {
                        this.currentIndex--;
                        this.scroll();
                    }
                },
                scrollNext: function () {
                    var CurrentObj = this;
                    if (this.currentIndex >= this.maxIndex) {
                        this.currentIndex = 0;
                        //this.container.find('.pic-list-box').stop().scrollLeft(0);
                        this.container.find('.list').removeClass('animate').css('transform', 'translateX(0)');
                        setTimeout(function () {
                            CurrentObj.currentIndex++;
                            CurrentObj.scroll();
                        }, 50);
                    } else {
                        this.currentIndex++;
                        this.scroll();
                    }
                },
                scroll: function () {
                    var CurrentObj = this;
                    var scrollListNode = this.container.find('.pic-list-box');
                    var scrollLeft = this.currentIndex * this.itemWidth;
                    //scrollListNode.stop().animate({scrollLeft: scrollLeft + 'px'}, 'slow');
                    //scrollListNode.stop().scrollLeft(scrollLeft);
                    scrollListNode.find('.list').addClass('animate').css('transform', 'translateX(-' + scrollLeft + 'px)');
                    CurrentObj.timer = setTimeout(function () {
                        CurrentObj.scrollNext();
                    }, CurrentObj.autoInterval);
                }
            };
            //初始化
            Obj._init();
            return Obj;
        }
    };
})(this, jQuery);