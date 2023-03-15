/**
 * Created by Administrator on 2018/10/13.
 */

(function (root, $) {
    //引入样式表文件
    /*var js = document.scripts;
     filePath = js[js.length - 1].src.substring(0, js[js.length - 1].src.lastIndexOf("/") + 1);
     $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: filePath + 'tree.css'}).appendTo('head');*/
    root.PictureScroll = {
        //默认配置
        default: {
            container: '.scroll-list-box',
            prevArrow: '.prev-arrow',
            nextArrow: '.next-arrow',
            autoInterval: 3000,
            itemNode: null,
            itemWidth: 0,
            itemCount: 0,
            showItemNumber: 4
        },
        create: function (opts) {
            var options = $.extend(true, {}, this.default, opts);
            var Obj = {
                options: options,
                container: $(options.container),
                prevArrow: $(options.container).find(options.prevArrow),
                nextArrow: $(options.container).find(options.nextArrow),
                showItemNumber: options.showItemNumber,
                autoInterval: options.autoInterval,
                scrollLeft: 0,
                maxLeft: 0,
                timer: null,
                _init: function () {
                    this.itemNode = this.container.find('.item');
                    this.itemWidth = this.itemNode.outerWidth(true);
                    this.itemCount = this.itemNode.length;
                    this.maxLeft = this.itemCount * this.itemWidth;
                    if (this.maxLeft < 0) {
                        this.maxLeft = 0;
                    }
                    this.container.find('.list').append(this.container.find('.list').html());
                    this.container.find('.list').width(this.maxLeft * 2);
                    this.bindEvent();
                    var CurrentObj = this;
                    CurrentObj.timer = setTimeout(function () {
                        CurrentObj.scrollNext();
                    }, CurrentObj.autoInterval);
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
                    $(this.container.find('.list')).hover(function () {
                        clearTimeout(CurrentObj.timer);
                    }, function () {
                        CurrentObj.timer = setTimeout(function () {
                            CurrentObj.scrollNext();
                        }, CurrentObj.autoInterval);
                    });
                },
                scrollPrev: function () {
                    this.scrollLeft -= this.itemWidth;
                    this.scroll();
                },
                scrollNext: function () {
                    this.scrollLeft += this.itemWidth;
                    this.scroll();
                },
                scroll: function () {
                    var CurrentObj = this;
                    var scrollListNode = this.container.find('.pic-list-box');
                    scrollListNode.stop();
                    scrollListNode.animate({scrollLeft: this.scrollLeft + 'px'}, 'slow', function () {
                        if (CurrentObj.scrollLeft >= CurrentObj.maxLeft) {
                            CurrentObj.scrollLeft = 0;
                            scrollListNode.scrollLeft(CurrentObj.scrollLeft);
                        }
                    });
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