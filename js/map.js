var markerList = [];

function InitMap(city) {
    var map = new BMap.Map('map-container');
    //map.enableScrollWheelZoom(true);
    map.addControl(new BMap.NavigationControl());
    map.addControl(new BMap.ScaleControl());
    //map.addControl(new BMap.OverviewMapControl());
    //map.addControl(new BMap.MapTypeControl());
    map.setCurrentCity(city);
    return map;
}

function GetPoint(map, memberDB, houseId, houseType, title, city, address) {
    // 创建地址解析器实例
    var myGeo = new BMap.Geocoder();
    // 将地址解析结果显示在地图上,并调整地图视野
    myGeo.getPoint(address, function(point){
        if (point) {
            //显示地图
            map.centerAndZoom(point, 16);
            MapAddMarker(map, point, '',  title, 0);

            //根据当前定位搜索周边信息
            SetSearchType(map, point);

            //存储坐标
            if (houseType == 1 || houseType == 2 || houseType == 5) {
                $.post('/ajax/index.php', {
                    action: 'map_point',
                    member_db: memberDB,
                    house_id: houseId,
                    house_type: houseType,
                    lng: point.lng,
                    lat: point.lat,
                    t: new Date().getTime()
                }, function (data) {
                    //console.log(point);
                });
            }
        } else {
            //地址解析失败
        }
    }, city);
}

function MapAddMarker(map, point, markerType, title, number) {
    var markerOpts = {
        enableMassClear: true,
        icon: new BMap.Icon('/images/map/marker-red.png', new BMap.Size(21, 31))
    };
    var label = new BMap.Label(title, {
        offset: new BMap.Size(20, -15)
    });
    label.setStyle({
        background:'#2b81ff',
        color:'#fff',
        border:'1px solid #2b81ff',
        padding:'0 5px',
        display:'none'
    });
    switch (markerType) {
        case '地铁站':
            break;
        case '公交站':
            break;
        case '学校':
            break;
        case '医院':
            break;
        case '银行':
            break;
        case '餐饮':
            break;
        case '商场|超市':

            break;
        default:
            markerOpts = {
                enableMassClear: false,
                icon: new BMap.Icon('/images/map/marker-red-1.gif', new BMap.Size(20, 20))
            };
            var label = new BMap.Label(title, {
                offset: new BMap.Size(20, -15)
            });
            label.setStyle({
                background:'red',
                color:'#fff',
                padding:'0 5px',
                border:'1px solid red'
            });
    }

    var marker = new BMap.Marker(point, markerOpts);
    marker.setLabel(label);
    map.addOverlay(marker);

    if (markerType != '') {
        //添加数字坐标
        var myCompOverlay = new ComplexCustomOverlay(point, number);
        map.addOverlay(myCompOverlay);

        markerList.push(marker);
        marker.addEventListener('click', function(e){
            var index = markerList.indexOf(marker);
            var itemNode = $('#result-data-list .item');
            itemNode.removeClass('on');
            itemNode.eq(index).addClass('on');
            //判断当前列表是不是显示出来了
            //console.log(itemNode.eq(index).position());
            var itemTop = itemNode.eq(index).position().top;
            $('#result-data-list').scrollTop(itemTop - 50);

            //显示Label
            ShowSelectedMarker(this);
        });
    } else {
        //marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画
    }
}

function ShowSelectedMarker(marker) {
    //隐藏所有Marker的Label
    $.each(markerList, function (index, markerItem) {
        var label = markerItem.getLabel();
        label.setStyle({
            display: 'none'
        });
        markerItem.setIcon(new BMap.Icon('/images/map/marker-red.png', new BMap.Size(21, 31)));
        markerItem.setTop(false);
    });

    //显示当前Marker的Label
    var label = marker.getLabel();
    label.setStyle({
        display: 'block'
    });

    marker.setIcon(new BMap.Icon('/images/map/marker-blue.png', new BMap.Size(21, 31)));
    marker.setTop(true);
}

function SearchByKeywords(map, point, keywords) {
    //清除地图覆盖物
    map.clearOverlays();
    markerList = [];
    $('#result-data-list').html('');

    //信息搜索
    //local.searchInBounds(keywords, map.getBounds());
    //local.search('公交');
    //local.searchNearby('公交', point, 1000);
    //local.searchInBounds("公交", map.getBounds());

    var options = {
        pageCapacity: 20,
        onSearchComplete: function(results){
            // 判断状态是否正确
            if (local.getStatus() == BMAP_STATUS_SUCCESS){
                var itemHtml = '';
                for (var i = 0; i < results.getCurrentNumPois(); i++){
                    var markerTitle = results.getPoi(i).title;
                    var markerPoint = results.getPoi(i).point;

                    //添加点到地图中
                    MapAddMarker(map, markerPoint, keywords, markerTitle, (i + 1));

                    //计算距离
                    var distance = (map.getDistance(point, markerPoint)).toFixed(0);
                    if (distance > 0) {
                        distance += 'm';
                    }

                    if (markerTitle.length > 14) {
                        itemTitle = markerTitle.substr(0, 12) + '...';
                    } else {
                        itemTitle = markerTitle;
                    }
                    itemHtml += '<div class="item clear-fix" data-uid="' + results.getPoi(i).uid + '"><div class="number">' + (i+1) + '</div><div class="info"><div class="title">' + itemTitle + '<span class="distance">' + distance + '</span></div><div class = "text" >' + results.getPoi(i).address + '</div></div></div >';
                }
                $('#result-data-list').html(itemHtml).show();
                $('#result-data-list .item').click(function () {
                    $('#result-data-list .item').removeClass('on');
                    $(this).addClass('on');

                    //设置对应的Label显示，并隐藏其它的所有Label
                    var index = $('#result-data-list .item').index($(this));
                    ShowSelectedMarker(markerList[index]);
                });
            } else {
                $('#result-data-list').html('<div class="no-data-box">当前周边暂无信息</div > ').show();
            }
        }
    };
    var local = new BMap.LocalSearch(map, options);
    local.searchInBounds(keywords, map.getBounds());
    //local.searchNearby(keywords, point, 1500);
}

function SetSearchType (map, point) {
    var searchType = [
        {text: '地铁', key: '地铁站', class: 'station'},
        {text: '公交', key: '公交站', class: 'bus'},
        {text: '学校', key: '学校', class: 'school'},
        {text: '医院', key: '医院', class: 'hospital'},
        {text: '银行', key: '银行', class: 'bank'},
        {text: '餐饮', key: '餐饮', class: 'rest'},
        {text: '购物', key: '商场|超市', class: 'shop'}
    ];

    var searchItemHtml = '';
    $.each(searchType, function (index, item) {
        searchItemHtml += '<div class="item" data-key="' + item.key + '"><div class="icon ' + item.class + '"></div><div class="text">' + item.text + '</div> </div>';
    });

    $('#map-box .search-type-list').html(searchItemHtml);
    var itemNote = $('#map-box .search-type-list .item');
    //itemNote.eq(0).addClass('on');
    //SearchByKeywords(map, point, itemNote.eq(0).attr('data-key'));

    itemNote.click(function () {
        if ($(this).hasClass('on') == true) {
            $('#result-data-list').toggle();
        } else {
            itemNote.removeClass('on');
            $(this).addClass('on');
            SearchByKeywords(map, point, $(this).attr('data-key'));
        }
    });
}

// 覆盖物构造方法

function ComplexCustomOverlay(point, index) {
    this._point = point;
    this._index = index;
}

ComplexCustomOverlay.prototype = new BMap.Overlay();
ComplexCustomOverlay.prototype.initialize = function (map) {
    this._map = map;
    var pointNode = this._span = document.createElement('span');

    //这里用jquery设置样式
    $(pointNode).css({
        'position': 'absolute',
        'zIndex': BMap.Overlay.getZIndex(this._point.lat),
        'display': 'block',
        'width': '26px',
        'color': '#FFF',
        'text-align': 'center',
        'point-events': 'none'
    });

    //设置数字也就是我们的标注
    this._span.innerHTML = this._index;
    map.getPanes().labelPane.appendChild(pointNode);
    return pointNode;
};

ComplexCustomOverlay.prototype.draw = function () {
    var map = this._map;
    var pixel = map.pointToOverlayPixel(this._point);

    //设置自定义覆盖物span 与marker的位置
    this._span.style.left = pixel.x - 13 + 'px';
    this._span.style.top = pixel.y - 14 + 'px';
};