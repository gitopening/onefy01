<?php
require_once(dirname(__FILE__) . '/common.inc.php');
$currentColumn = 'citylist';
$url_name = str_replace('.' . $cfg['page']['basehost'], '', $_SERVER['HTTP_HOST']);
if ($url_name != 'www') {
    header('Location://www.' . $cfg['page']['basehost'] . '/index.htm');
    exit();
}

$page->title = '【二手房|租房|写字楼|商铺-全面及时的个人房源】' . $page->titlec;
$page->description = '第一时间房源网-专业商铺、写字楼、个人二手房、最新二手房、二手房出租、最新商铺、商铺出售、房屋出租、二手房出售、写字楼出租、商铺出租、个人二手房 信息、个人出租信息、房东直接出租及出售信息,全面及时的个人房源。';
$page->keywords = '商铺,写字楼,个人二手房,最新二手房,二手房出租,最新商铺,商铺出售,房屋出租,二手房出售,写字楼出租,商铺出租,个人出租信息,房东出租出售信息,个人房源网,房屋出租,第一时间房源网';

$indexFlag = false;
//广告位
$website_top_ad = GetAdList(95, $query);
$websiteFooterAd = GetAdList(96, $query);
$website_header_ad = GetAdList(181, $query);
//定位城市
/*if (empty($positionInfo)) {
    $url = '//ip.taobao.com/service/getIpInfo.php?ip='.getclientip();
    $result = json_decode(file_get_contents($url), true);
    $positionInfo = $result['data'];
    if ($positionInfo['city']) {
        $position_city_info = $query->table('city_website')->field('id, city_name, url_name')->where("city_name like '{$positionInfo['city']}%' and is_open = 1")->cache(true)->one();
    }
}*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $page->title;?></title>
    <meta name="description" content="<?php echo $page->description;?>" />
    <meta name="keywords" content="<?php echo $page->keywords;?>" />
    <link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
    <link rel="stylesheet" type="text/css" href="/css/city_list.css?v=<?php echo $webConfig['static_version'];?>" />
    <script type="text/javascript" src="//api.map.baidu.com/api?v=3.0&ak=KGf0A7gkqTowCrwG7N7RyFLtYYhCCVVv"></script>
    <script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/autocomplete/jquery.autocomplete.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body>
<?php require_once(dirname(__FILE__).'/header.php');?>
<div id="main">
    <div class="current-city" id="current-city">正在定位...</div>
    <div id="city">
        <div class="s-main">
            <div class="cityin all-city">
                <div class="chose">热门城市</div>
                <?php
                $cityList = get_hot_city();
                foreach ($cityList as $key=>$val){
                    echo '<a href="//'.$val['url_name'].'.'.$cfg['page']['basehost'].'">'.$val['city_name'].'</a> ';
                }
                ?>
            </div>
            <div class="clear"></div>
        </div>
        <div class="search-city">
            <div class="caption">搜索城市</div>
            <div class="control">
                <input type="text" name="city_name" id="city_name" value="" />
            </div>
        </div>
        <div class="all-city">
            <div id="area-list">
                <dl><dt><div class="first-letter"></div>直辖市</dt><dd><a href="//bj.<?php echo $cfg['page']['basehost'];?>">北京</a> <a href="//sh.<?php echo $cfg['page']['basehost'];?>" class="f-c-red">上海</a> <a href="//tj.<?php echo $cfg['page']['basehost'];?>" class="f-c-red">天津</a> <a href="//cq.<?php echo $cfg['page']['basehost'];?>" class="f-c-red">重庆</a> </dd></dl>
                <dl>
                    <?php
                    $province_option = get_region_enum(1);
                    $condition = 'site.is_open = 1 and site.province_id not in (16590, 27, 25, 2, 32)';
                    $cityList = $query->field('site.id, site.city_name, site.url_name, site.province_id, site.is_hot, site.is_recommend, region.region_id, region.region_name, region.first_letter')->table('city_website', 'site')->join($query->db_prefix . 'region AS region ON site.province_id = region.region_id', 'LEFT')->where($condition)->order('site.province_id asc,site.is_order asc')->cache(true)->all();
                    $province_id = '';
                    $first_letter = '';
                    foreach ($cityList as $key=>$val){
                        if ($province_id != $val['province_id']){
                            if($key > 0){
                                echo '</dl><dl>';
                            }
                            $province_id = $val['province_id'];
                            if ($first_letter != $province_option[$val['province_id']]['first_letter']) {
                                $first_letter = $province_option[$val['province_id']]['first_letter'];
                                $prefix_letter = '<div class="first-letter">' . $first_letter . '</div>';
                            } else {
                                $prefix_letter = '<div class="first-letter"></div>';
                            }
                            if ($key == 0){
                                echo '<dt>' . $prefix_letter . $province_option[$val['province_id']]['region_name'] . '</dt><dd>';
                            }else{
                                echo '</dd><dt>' . $prefix_letter . $province_option[$val['province_id']]['region_name'] . '</dt><dd>';
                            }
                        }
                        if ($val['is_recommend'] == 1){
                            $addClass = ' class="f-c-red"';
                        }else{
                            $addClass = '';
                        }
                        echo '<a href="//'.$val['url_name'].'.'.$cfg['page']['basehost'].'"'.$addClass.'>'.$val['city_name'].'</a> ';
                    }
                    echo '</dd>';
                    ?>
                </dl>
            </div>
        </div>
    </div>
</div>
<?php require_once(dirname(__FILE__).'/footer.php');?>
<script type="text/javascript">
    $(document).ready(function () {
        $.getJSON('/ajax/index.php', {
            action: 'get_all_city_website',
            t: new Date().getTime()
        }, function (data) {
            var objCity = new BMap.LocalCity();
            var cityList = data.data;
            objCity.get(function (result){
                var cityName = result.name.replace('市', '');
                for (var i = 0; i < cityList.length; i++) {
                    if (cityList[i].city_name == cityName) {
                        var url = '//' + cityList[i].url_name + '.<?php echo $cfg['page']['basehost'];?>';
                        <?php
                        if ($url_name == 'www' && empty($jump_url_name)){
                        ?>
                        window.location.href = url;
                        <?php
                        }
                         ?>
                        var html = '<a class="in_cur f-c-red" href="' + url + '">进入' + cityList[i].city_name + '站</a>';
                        $('#current-city').html(html);
                        break;
                    }
                }
            });

            $('#city_name').autocomplete(data.data, {
                max: 15,    //列表里的条目数
                minChars: 1,    //自动完成激活之前填入的最小字符
                width: 138,     //提示的宽度，溢出隐藏
                scrollHeight: 300,   //提示的高度，溢出显示滚动条
                matchContains: true,    //包含匹配，就是data参数里的数据，是否只要包含文本框里的数据就显示
                autoFill: false,    //自动填充
                cacheLength: 0,
                defaultValue : {
                    data: {
                        city_name: "暂无此城市",
                        id: 0,
                        url_name: ""
                    }
                },
                formatItem: function (item) {
                    var tmpl = '<div class="list-item';
                    //显示下拉列表的内容
                    if (item.id == 0) {
                        tmpl += ' no-city';
                    }
                    tmpl += '"><span class="title">' + item.city_name + '</span></div>';
                    return tmpl;
                },
                formatMatch: function (item) {
                    return item.city_name + item.url_name;
                },
                formatResult: function (item) {
                    return item.city_name;
                }
            }).result(function (event, item, formatted) {
                if (item.id == 0) {
                    $('#city_name').val('');
                    return false;
                } else {
                    //跳转到相应网站
                    window.location.href = '//' + item.url_name + '.' + '<?php echo $cfg['page']['basehost'];?>';
                }
            });
        });
    });
</script>
</body>
</html>

