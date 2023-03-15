/**
 * Created by Net on 2016/9/9.
 */

var isDrag = false;
var picListLeft = 0;
var startX = 0;
var endX = 0;
var picListMaxLeft = 0;
var bodyWidth = 0;
var picIndex = 1;
var picCount = 0;

function FlashBanner() {
    var picList = document.getElementById('pic-list');
    picList.addEventListener('touchstart', TouchStart);
    picList.addEventListener('touchmove', TouchMove);
    picList.addEventListener('touchend', TouchEnd);
    bodyWidth = $('#picture-box .item').width();
    picCount = $('#picture-box .item').length;
    picListMaxLeft = -($('#pic-list').width() - bodyWidth);
}

function TouchStart(e) {
    isDrag = true;
    e.preventDefault();
    picListLeft = parseInt($('#pic-list').css('left'));
    startX = e.touches[0].pageX;
}
function TouchMove(e) {
    if (isDrag) {
        e.preventDefault();
        endX = e.touches[0].pageX;
        var n = picListLeft + endX - startX;
        if (n >= 0) {
            n = 0;
        } else if (n < picListMaxLeft) {
            n = picListMaxLeft;
        }
        $('#pic-list').css('left', n);
    }
}
function TouchEnd(e) {
    isDrag = false;
    var picList = $('#pic-list');
    //计算位移量
    var moveDistance = endX - startX;
    if (Math.abs(moveDistance) > 10) {
        if (moveDistance > 0 && picIndex > 0) {
            //向右滑动
            picIndex--;
        } else if (moveDistance < 0 && picIndex < (picCount - 1)) {
            //向左滑动
            picIndex++;
        }
    }
    picList.animate({left: -(picIndex * bodyWidth) + 'px'}, 200, function () {
        if (picIndex >= picCount - 1) {
            picList.css('left', -bodyWidth);
            picIndex = 1;
        } else if (picIndex <= 0) {
            picList.css('left', picListMaxLeft + bodyWidth);
            picIndex = picCount - 2;
        }
        $('#pic-index').html(picIndex);

    });
}

function DetailFlashBanner() {
    var picList = $('#pic-list');
    var picItem = picList.find('.item');
    var firstItem = picItem.first();
    var lastItem = picItem.last();
    $(picList).append(firstItem.prop('outerHTML')).prepend(lastItem.prop('outerHTML'));

    ResizeFlashBinner();
    /*$('#left-arrow').click(function () {
     var container = $('#picture-box #container');
     var currentLeft = container.scrollLeft();
     var nextLeft = currentLeft + container.find('.item').width();
     var maxWidth = container.find('#pic-list').width();
     nextLeft = nextLeft > maxWidth ? maxWidth : nextLeft;
     container.animate({scrollLeft: nextLeft + 'px'},'slow');
     });
     $('#right-arrow').click(function () {
     var container = $('#picture-box #container');
     var currentLeft = container.scrollLeft();
     var nextLeft = currentLeft - container.find('.item').width();
     nextLeft = nextLeft < 0 ? 0 : nextLeft;
     container.animate({scrollLeft: nextLeft + 'px'},'slow');
     });*/
    FlashBanner();
}

function ResizeFlashBinner() {
    var pictureBox = $('#picture-box');
    var itemWidth = pictureBox.width();
    var itemCount = pictureBox.find('.item').length;
    var itemHeight = itemWidth / 1.5;
    //var arrowTop = (itemHeight - $('#left-arrow').height()) / 2;
    pictureBox.find('#pic-list').width(itemWidth * itemCount);
    pictureBox.find('.item').width(itemWidth).height(itemHeight);
    pictureBox.height(itemHeight);
    //pictureBox.find('#left-arrow').css('top', arrowTop + 'px');
    //pictureBox.find('#right-arrow').css('top', arrowTop + 'px');

    //重新设置文件需要的必需参数
    bodyWidth = $('#picture-box .item').width();
    picCount = $('#picture-box .item').length;
    picListMaxLeft = -($('#pic-list').width() - bodyWidth);
    $('#pic-list').animate({left: -(picIndex * bodyWidth) + 'px'}, 200);
}

