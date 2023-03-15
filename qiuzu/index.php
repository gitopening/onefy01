<?php
require_once(dirname(__FILE__) . '/path.inc.php');
require_once($cfg['path']['lib'] . 'classes/Pages.class.php');
$now_time = time();

//Sphinx搜索
$Sphinx = Sphinx::getInstance();
//标题
$page->title = '';
//链接参数处理list_mtype_area_area2_(sprice-eprice)_room_(sta-eta)_renttype_page_关键字.html
$param = explode('_', trim($_GET['param']));
$mtype = intval($param[0]);
$cityarea = intval($param[1]);
$cityarea2 = intval($param[2]);
$price = trim($param[3]) == '' ? '0-0' : trim($param[3]);
$room = intval($param[4]);
$totalarea = trim($param[5]) == '' ? '0-0' : trim($param[5]);
$rent_type = intval($param[6]);
$pageno = empty($param[7]) ? 1 : intval($param[7]);
$keywords = trim($param[8]);

//添加搜索关键字到搜索记录中
$search_history_list = get_search_history_list();
if (!empty($keywords)) {
    $search_history_list = add_search_keyword_history($keywords);
}

$cityarea_option = get_region_enum($cityInfo['city_id'], 'sort');
if ($cityarea){
	$cityarea2_option = get_region_enum($cityarea);
}
$parent_id_array = array();
foreach ($cityarea_option as $value){
	$parent_id_array[] = $value['region_id'];
}
$cityarea2_list = get_region_enum($parent_id_array);
$house_type_option = Dd::getArray('house_type');
$house_price_option = get_filter_array($column['price_option']);
$house_totalarea_option = get_filter_array($column['totalarea_option']);

$Sphinx->SetFilter('column_type', array($column['column_type']));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));

if ($mtype == 4) {
    $Sphinx->SetFilter('mtype', array(1, 3));
    $Sphinx->SetFilter('is_cooperation', array(1));
} elseif ($mtype) {
    $Sphinx->SetFilter('mtype', array($mtype));
}

if ($cityarea) {
    $page->title .= $cityarea_option[$cityarea]['region_name'];
    if (empty($cityarea2)) {
        $Sphinx->SetFilter('cityarea_id', array($cityarea));
    } else {
        $Sphinx->SetFilter('cityarea2_id', array($cityarea2));
        $page->title .= $cityarea2_option[$cityarea2]['region_name'];
    }
}

if ($room) {
    $Sphinx->SetFilter('house_room', array($room));
    $page->title .= $room_option[$room];
}

list($start, $end) = explode('-', $price);
$start = intval($start);
$end = intval($end);

if ($start && $end) {
    $Sphinx->SetFilterRange('house_price', $start, $end);
} elseif (empty($start) && !empty($end)) {
    $Sphinx->SetFilterRange('house_price', 0, $end);
} elseif (!empty($start) && empty($end)) {
    $Sphinx->SetFilterRange('house_price', $start, 10000000000);
}

/*if ($start && $end){
    $page->title .= $start.'~'.$end.'元';
}elseif ($start && empty($end)){
    $page->title .= $start.'元以上';
}elseif (empty($start) && $end){
    $page->title .= $end.'元以下';
}*/

list($start, $end) = explode('-', $totalarea);
$start = intval($start);
$end = intval($end);

if ($start && $end) {
    $Sphinx->SetFilterRange('house_totalarea', $start, $end);
} elseif (empty($start) && !empty($end)) {
    $Sphinx->SetFilterRange('house_totalarea', 0, $end);
} elseif (!empty($start) && empty($end)) {
    $Sphinx->SetFilterRange('house_totalarea', $start, 10000000000);
}

/*if ($start && $end){
    $page->title .= $start.'~'.$end.'㎡';
}elseif ($start && empty($end)){
    $page->title .= $start.'㎡以上';
}elseif (empty($start) && $end){
    $page->title .= $end.'㎡以下';
}*/

if ($rent_type) {
    $Sphinx->SetFilter('rent_type', array($rent_type));
}

//$Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, 'is_promote DESC,updated DESC,@relevance DESC,@id DESC');

$list_num = $cfg['list_number'];
//缓存Key
$list_key = MEMCACHE_PREFIX . 'qzl_' . $cityInfo['id'] . '_' . $column['column_type'] . '_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $list_num . '_' . $pageno . '_' . $keywords;
$count_key = MEMCACHE_PREFIX . 'qzc_' . $cityInfo['id'] . '_' . $column['column_type'] . '_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $list_num . '_' . $keywords;
//从Memcache中取数据
$dataCount = $Cache->get($count_key);
$dataList = $Cache->get($list_key);
if (empty($dataList) || empty($dataCount)) {
    //$max_data_limit = MAX_DATA_LIMIT;
    $max_data_limit = 500;
    $cur_page = $pageno;
    $max_page = ceil($max_data_limit / $list_num);
    if ($cur_page <= 0) {
        $cur_page = 1;
    }
    if ($cur_page >= $max_page) {
        $cur_page = $max_page;
    }
    $row_from = intval(($cur_page - 1) * $list_num);
    $Sphinx->SetLimits($row_from, $list_num, $max_data_limit);
    $parsed_keywords = preg_replace('/(1\d{2}) (\d{4}) (\d{4})/i', '$1$2$3', $keywords);
    if ($parsed_keywords) {
        $Sphinx->SetMatchMode(SPH_MATCH_PHRASE);
    }
    $result = $Sphinx->Query($parsed_keywords, SPHINX_SEARCH_QIUZU_INDEX);
    $result['total'] = intval($result['total']);
    $pages = new Pages($result['total'], $list_num);
    $dataList = array();
    if ($result['total'] > 0) {
        $now_time = time();
        foreach ($result['matches'] as $item) {
            $data = array();
            $data['title'] = get_new_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column['house_title'], 'qz');
            $data['title'] = cn_substr_utf8($data['title'], 54);
            $data['is_promote'] = $item['attrs']['is_promote'];
            $data['id'] = $item['id'];
            $data['user_type'] = $item['attrs']['mtype'];
            $data['is_cooperation'] = intval($item['attrs']['is_cooperation']);
            if ($data['user_type'] == 2 || $data['is_promote'] || $data['is_cooperation'] == 1) {
                $data['title'] = cn_substr_utf8(str_replace(' ', '', $data['title']), 54);
                if ($data['user_type'] == 2) {
                    //$data['title'] .= '(个人)';
                } elseif ($data['user_type'] == 1 && $data['is_cooperation'] == 1) {
                    //$data['title'] .= '(合作)';
                }
            }
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['house_veranda'] = FormatHouseRoomType($item['attrs']['house_veranda'], 9);
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['start_totalarea'] = $item['attrs']['start_totalarea'];
            $data['end_totalarea'] = $item['attrs']['end_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['start_price'] = FormatHousePrice($item['attrs']['start_price']);
            $data['end_price'] = FormatHousePrice($item['attrs']['end_price']);
            $data['unit_type'] = $item['attrs']['unit_type'];
            $data['rent_type'] = $item['attrs']['rent_type'];
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] = 'house_' . $item['id'] . $url_fix . '.html';
            $dataList[] = $data;
        }
        //存储数据到Memcache
        if ($dataList && $result['total']) {
            $Cache->set($count_key, $result['total'], MEMCACHE_EXPIRETIME);
            $Cache->set($list_key, $dataList, MEMCACHE_EXPIRETIME);
        }
        unset($result);
    }
} else {
    $pages = new Pages($dataCount, $list_num);
}

//分页链接
$url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_{pageno}_' . $keywords . '.html';
$pagePanel = $pages->get_pager_nav('4', $pageno, $url);

if ($mtype == 1){
	$mtypetitle = ',房产中介';
}elseif ($mtype == 2){
	$mtypetitle = ',个人房源';
}elseif ($mtype == 3){
	$mtypetitle = ',非中介机构房源';
}
/*if ($cityarea){
	$cityareaTitle = $cityarea_option[$cityarea]['region_name'].$cityarea2_option[$cityarea2]['region_name'];
}else{
	$cityareaTitle = $cityInfo['city_name'];
}*/
$cityareaTitle = $cityarea_option[$cityarea]['region_name'].$cityarea2_option[$cityarea2]['region_name'];
switch ($_GET['column']){
	case 'qiuzu':
		$page->title = '【'.$cityInfo['city_name'].$cityareaTitle.'求租房屋信息-个人求租房】-'.$page->titlec.$cityInfo['city_name'];
		$page->description = $cityInfo['city_name'].$page->titlec.'大量'.$cityInfo['city_name'].$cityarea_option[$cityarea]['region_name'].'求租房屋信息,个人求租房屋信息，最全面最及时的房屋求购信息尽在'.$page->titlec;
		$page->keywords = $cityInfo['city_name'].'求租房屋，'.$cityarea_option[$cityarea]['region_name'].'求租房屋、个人求租房屋、个人房屋求租信息';
		break;
	case 'xzlqz':
        $page->title = '【'.$cityInfo['city_name'].$cityareaTitle.'求租写字楼息-个人求租房】-'.$page->titlec.$cityInfo['city_name'];
        $page->description = $cityInfo['city_name'].$page->titlec.'大量'.$cityInfo['city_name'].$cityarea_option[$cityarea]['region_name'].'求租写字楼信息,个人求租写字楼信息，最全面最及时的写字楼求购信息尽在'.$page->titlec;
        $page->keywords = $cityInfo['city_name'].'求租写字楼，'.$cityarea_option[$cityarea]['region_name'].'求租写字楼、个人求租写字楼、个人写字楼求租信息';
		break;
	case 'spqz':
        $page->title = '【'.$cityInfo['city_name'].$cityareaTitle.'求租商铺信息-个人求租房】-'.$page->titlec.$cityInfo['city_name'];
        $page->description = $cityInfo['city_name'].$page->titlec.'大量'.$cityInfo['city_name'].$cityarea_option[$cityarea]['region_name'].'求租商铺信息,个人求租商铺信息，最全面最及时的商铺求购信息尽在'.$page->titlec;
        $page->keywords = $cityInfo['city_name'].'求租商铺，'.$cityarea_option[$cityarea]['region_name'].'求租商铺、个人求租商铺、个人商铺求租信息';
		break;
    case 'cwqz':
        $page->title = '【'.$cityInfo['city_name'].$cityareaTitle.'求租车位信息-个人求租车位】-'.$page->titlec.$cityInfo['city_name'];
        $page->description = $cityInfo['city_name'].$page->titlec.'大量'.$cityInfo['city_name'].$cityarea_option[$cityarea]['region_name'].'求租车位信息,个人求租车位信息，最全面最及的车位求购信息尽在'.$page->titlec;
        $page->keywords = $cityInfo['city_name'].'求租车位，'.$cityarea_option[$cityarea]['region_name'].'求租车位、个人求租车位、个人车位求租信息';
        break;
    case 'cfqz':
        $page->title = '【'.$cityInfo['city_name'].$cityareaTitle.'求租厂房信息-个人求租厂房】-'.$page->titlec.$cityInfo['city_name'];
        $page->description = $cityInfo['city_name'].$page->titlec.'大量'.$cityInfo['city_name'].$cityarea_option[$cityarea]['region_name'].'求租厂房信息,个人求租厂房信息，最全面最及的厂房求购信息尽在'.$page->titlec;
        $page->keywords = $cityInfo['city_name'].'求租厂房，'.$cityarea_option[$cityarea]['region_name'].'求租厂房、个人求租厂房、个人厂房求租信息';
        break;
    case 'ckqz':
        $page->title = '【'.$cityInfo['city_name'].$cityareaTitle.'求租仓库信息-个人求租仓库】-'.$page->titlec.$cityInfo['city_name'];
        $page->description = $cityInfo['city_name'].$page->titlec.'大量'.$cityInfo['city_name'].$cityarea_option[$cityarea]['region_name'].'求租仓库信息,个人求租仓库信息，最全面最及的仓库求购信息尽在'.$page->titlec;
        $page->keywords = $cityInfo['city_name'].'求租仓库，'.$cityarea_option[$cityarea]['region_name'].'求租仓库、个人求租仓库、个人仓库求租信息';
        break;
    default:

}

//广告设置
$website_left_ad = GetAdList(109, $query);
$website_right_ad = GetAdList(110, $query);
$website_header_ad = GetAdList(183, $query);
$website_right_ad_2 = GetAdList(224, $query);
$website_search_bottom_ad = GetAdList(227, $query);
$website_right_hot_search_ad = GetAdList(230, $query);
$website_right_news_ad = GetAdList(231, $query);
$website_list_ad_1 = GetAdList(234, $query);
$website_list_ad_2 = GetAdList(235, $query);
//数据统计
//$write_query = create_write_query();
//$visitCount = new VisitCount($write_query);
//$visitCount->AddVisitCount($cityInfo['id'], $_GET['column'], $cityarea, 0);

//浏览过的房源
$history_list = get_history_list($_GET['column'] . 'house_qiuzu');
$history_house_list = array();
if ($history_list) {
    //创建第三方会员数据库连接
    $member_db_config = GetConfig('member_db_4');
    if ($member_db_config) {
        $zfy_query = new DbQueryForMysql($member_db_config);
    }
}
foreach ($history_list as $item) {
    $house_id = intval($item['id']);
    if (empty($house_id)) {
        continue;
    }
    $memcache_house_key = 'house_qiuzu_' . $house_id;
    if ($item['db'] == MEMBER_DB_INDEX) {
        $memcache_house_key = MEMCACHE_PREFIX . 'member_' . $item['db'] . $memcache_house_key;
        $house_query = $member_query;
    } elseif ($item['db'] == 4) {
        if (!$zfy_query) {
            continue;
        }
        $memcache_house_key = MEMCACHE_ZFY_PREFIX . 'member_' . $item['db'] . $memcache_house_key;
        $house_query = $zfy_query;
    } elseif ($item['db'] == 0) {
        $house_query = $query;
    } else {
        continue;
    }
    $condition = "house.id = '{$house_id}' and is_checked in (0,1) and is_delete = 0 AND is_down = 0";
    $house_info = $house_query->field('house.*, extend.*')->table('qiuzu', 'house')->join($house_query->db_prefix . 'qiuzu_extend AS extend ON house.id = extend.house_id', 'LEFT')->where($condition)->cache($memcache_house_key, MEMCACHE_MAX_EXPIRETIME)->one();
    if (empty($house_info)) {
        continue;
    }
    $data = array();
    $data['title'] = get_new_house_title($house_info, $cityarea_option, $cityarea2_list, $column['house_title'], 'qz');
    $data['is_promote'] = $house_info['is_promote'];
    $data['id'] = $item['id'];
    $data['user_type'] = $house_info['mtype'];
    $data['is_cooperation'] = intval($house_info['is_cooperation']);
    /*if ($data['user_type'] == 2 || $data['is_promote'] || $data['is_cooperation'] == 1) {
        $data['title'] = cn_substr_utf8(str_replace(' ', '', $data['title']), 54);
        if ($data['user_type'] == 2) {
            $data['title'] .= '(个人)';
        } elseif ($data['user_type'] == 1 && $data['is_cooperation'] == 1) {
            $data['title'] .= '(合作)';
        }
    }*/
    $data['house_room'] = FormatHouseRoomType($house_info['house_room'], 9);
    $data['house_hall'] = FormatHouseRoomType($house_info['house_hall'], 9);
    $data['house_toilet'] = FormatHouseRoomType($house_info['house_toilet'], 9);
    $data['house_veranda'] = FormatHouseRoomType($house_info['house_veranda'], 9);
    $data['updated'] = time2Units($now_time - $house_info['updated']);
    $data['house_totalarea'] = $house_info['house_totalarea'];
    $data['start_totalarea'] = $house_info['start_totalarea'];
    $data['end_totalarea'] = $house_info['end_totalarea'];
    $data['house_price'] = FormatHousePrice($house_info['house_price']);
    $data['start_price'] = FormatHousePrice($house_info['start_price']);
    $data['end_price'] = FormatHousePrice($house_info['end_price']);
    $data['unit_type'] = $house_info['unit_type'];
    $data['rent_type'] = $house_info['rent_type'];
    if ($item['db'] == 0) {
        $url_fix = '';
    } else {
        $url_fix = MEMBER_DB_INDEX == $item['db'] ? 'x' : '_' . $item['db'];
    }
    $data['url'] = 'house_' . $item['id'] . $url_fix . '.html';
    $history_house_list[] = $data;
}
//今日热门资讯
/*$sql = "select id, title from `fke_news` where city_website_id = '{$cityInfo['id']}' order by id desc limit 5";
$news_list = SaveDataToMemcache($sql, $query, false, MEMCACHE_EXPIRETIME);
*/

//取得最新新闻资讯
$News = new News($query);
$new_limit = 3;
$sphinx_index = 'search_news,search_news_delta';
$news_list = $News->getHotFromSphinx(0, $new_limit, $sphinx_index, true);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page->title;?></title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" rel="stylesheet" type="text/css" />
<style type="text/css">
    .isStuck{
        border-bottom: 1px solid #e4e4e4;
        box-shadow: #efefef 2px 2px 2px;
    }
</style>
<script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/jquery.cookie.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body>
	<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
    <div class="header-search-wrap">
        <div class="header-search-box clear-fix">
            <div class="search-form">
                <form action="/qiugou/" method="get" id="search-form" onsubmit="return dosearch2();">
                    <input type="hidden" name="url" id="url" value="<?php echo 'list_'.$mtype.'_0_0_'.$price.'_'.$room.'_'.$totalarea.'_'.$rent_type.'_1_[keywords].html';?>" />
                    <input type="text" id="text" name="q" value="<?php echo $keywords;?>" autocomplete="off" placeholder="请输入区域、商圈或小区名进行搜索"/>
                    <div class="common-submit-btn search-submit-btn" onclick="dosearch2();">搜索</div>
                </form>
            </div>
            <a href="/member/houseQiuzu.php" target="_blank" class="publish-btn">发布房源</a>
            <div class="clear"></div>
        </div>
    </div>
    <div class="position list-position">
        <a href="/"><?php echo $page->titlec . $cityInfo['city_name'];?></a> &gt;
        <a href="/<?php echo $_GET['column'];?>/"><?php echo $cityInfo['city_name'] . $column['column_title'];?></a> &gt;
        <?php
        if ($cityarea) {
            ?>
            <a href="list_<?php echo $mtype;?>_<?php echo $cityarea;?>.html"><?php echo $cityarea_option[$cityarea]['region_name'] . $column['column_title'];?></a> &gt;
            <?php
        }
        if ($cityarea && $cityarea2) {
            ?>
            <a href="list_<?php echo $mtype;?>_<?php echo $cityarea;?>_<?php echo $cityarea2;?>.html"><?php echo $cityarea2_option[$cityarea2]['region_name'] . $column['column_title'];?></a> &gt;
            <?php
        }
        if ($mtype == 1) {
            echo '<a href="list_1.html">' . $column['column_position_title'] . '经纪人信息</a>';
        } elseif ($mtype == 2) {
            echo '<a href="list_2.html">' . $column['column_position_title'] . '个人信息</a>';
        } elseif ($mtype == 3) {
            echo '<a href="list_3.html">' . $column['column_position_title'] . '非中介机构房源</a>';
        } elseif ($mtype == 4) {
            //echo '<a href="list_4.html">' . $column['column_position_title'] . '合作信息</a>';
        } else {
            echo '<a href="list_0.html">' . $column['column_position_title'] . '全部信息</a>';
        }
        ?>
    </div>
    <div class="section-box house-list-section-box">
        <div id="Region">
            <ul>
                <?php
                if ($column['column_type'] == 1){
                    ?>
                    <li class="filter-house-type-box">
                        <div class="div02">
                            <?php
                            echo $_GET['column'] == 'rent' ? '<span><a href="/rent/">出租</a></span>' : '<a href="/rent/">出租</a>';
                            echo $_GET['column'] == 'qiuzu' ? '<span><a href="/qiuzu/">求租</a></span>' : '<a href="/qiuzu/">求租</a>';
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                    <?php
                }elseif ($column['column_type'] == 2){
                    ?>
                    <li class="filter-house-type-box">
                        <div class="div02">
                            <?php
                            echo $_GET['column'] == 'xzlcz' ? '<span><a href="/xzlcz/">出租</a></span>' : '<a href="/xzlcz/">出租</a>';
                            echo $_GET['column'] == 'xzlqz' ? '<span><a href="/xzlqz/">求租</a></span>' : '<a href="/xzlqz/">求租</a>';
                            echo $_GET['column'] == 'xzlcs' ? '<span><a href="/xzlcs/">出售</a></span>' : '<a href="/xzlcs/">出售</a>';
                            echo $_GET['column'] == 'xzlqg' ? '<span><a href="/xzlqg/">求购</a></span>' : '<a href="/xzlqg/">求购</a>';
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                    <?php
                }elseif ($column['column_type'] == 3){
                    ?>
                    <li class="filter-house-type-box">
                        <div class="div02">
                            <?php
                            echo $_GET['column'] == 'spcz' ? '<span><a href="/spcz/">出租</a></span>' : '<a href="/spcz/">出租</a>';
                            echo $_GET['column'] == 'spqz' ? '<span><a href="/spqz/">求租</a></span>' : '<a href="/spqz/">求租</a>';
                            echo $_GET['column'] == 'spcs' ? '<span><a href="/spcs/">出售</a></span>' : '<a href="/spcs/">出售</a>';
                            echo $_GET['column'] == 'spqg' ? '<span><a href="/spqg/">求购</a></span>' : '<a href="/spqg/">求购</a>';
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                    <?php
                } elseif ($column['column_type'] == 4){
                    ?>
                    <li class="filter-house-type-box">
                        <div class="div02">
                            <?php
                            echo $_GET['column'] == 'cwcz' ? '<span><a href="/cwcz/">出租</a></span>' : '<a href="/cwcz/">出租</a>';
                            echo $_GET['column'] == 'cwqz' ? '<span><a href="/cwqz/">求租</a></span>' : '<a href="/cwqz/">求租</a>';
                            echo $_GET['column'] == 'cwzr' ? '<span><a href="/cwzr/">转让</a></span>' : '<a href="/cwzr/">转让</a>';
                            echo $_GET['column'] == 'cwcs' ? '<span><a href="/cwcs/">出售</a></span>' : '<a href="/cwcs/">出售</a>';
                            echo $_GET['column'] == 'cwqg' ? '<span><a href="/cwqg/">求购</a></span>' : '<a href="/cwqg/">求购</a>';
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                    <?php
                } elseif ($column['column_type'] == 16 || $column['column_type'] == 17){
                    ?>
                    <li class="filter-house-type-box">
                        <div class="div02">
                            <?php
                            echo $currentColumn == 'cf' ? '<span><a href="/cf' . str_replace($currentColumn, '', $_GET['column']) . '/">厂房</a></span>' : '<a href="/cf'  . str_replace($currentColumn, '', $_GET['column']) . '/">厂房</a>';
                            echo $currentColumn == 'ck' ? '<span><a href="/ck' . str_replace($currentColumn, '', $_GET['column']) . '/">仓库</a></span>' : '<a href="/ck' . str_replace($currentColumn, '', $_GET['column']) . '/">仓库</a>';
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                    <?php
                }
                ?>
                <li>
                    <div class="div01">区域：</div>
                    <div class="div02">
                        <?php
                        $url = 'list_'.$mtype.'_{curentparam}_0_'.$price.'_'.$room.'_'.$totalarea.'_'.$rent_type.'_1_.html';
                        if (empty($cityarea)){
                            echo '<span><a href="'.build_url($url, 0).'">全'.$cityInfo['city_name'].'</a></span> ';
                        }else{
                            echo '<a href="'.build_url($url, 0).'">全'.$cityInfo['city_name'].'</a> ';
                        }
                        foreach ($cityarea_option as $key=>$val){
                            if ($val['region_id'] == $cityarea){
                                echo '<span><a href="'.build_url($url, $val['region_id']).'">'.$val['region_name'].'</a></span> ';
                            }else{
                                echo '<a href="'.build_url($url, $val['region_id']).'">'.$val['region_name'].'</a> ';
                            }
                        }
                        ?>
                    </div>
                    <div class="clear"></div>
                </li>
                <?php
                if ($cityarea > 0 && $cityarea2_option){
                    ?>
                    <li class="Region2"><?php
                        $url = 'list_'.$mtype.'_'.$cityarea.'_{curentparam}_'.$price.'_'.$room.'_'.$totalarea.'_'.$rent_type.'_1_'.$keywords.'.html';
                        if (empty($cityarea2)){
                            echo '<span><a href="'.build_url($url, 0).'">不限</a></span>';
                        }else{
                            echo '<a href="'.build_url($url, 0).'">不限</a>';
                        }
                        $first_letter = '';
                        foreach ($cityarea2_option as $key=>$val){
                            if ($first_letter != $val['first_letter']){
                                echo '<span class="wordindex">'.$val['first_letter'].'</span>';
                            }
                            $first_letter = $val['first_letter'];
                            if ($cityarea2 == $val['region_id']){
                                echo '<span><a href="'.build_url($url, $val['region_id']).'">'.$val['region_name'].'</a></span>';
                            }else{
                                echo '<a href="'.build_url($url, $val['region_id']).'">'.$val['region_name'].'</a>';
                            }
                        }
                        ?>
                    </li>
                    <?php
                }
                if ($show) {
                    ?>
                    <li>
                        <div class="div01">租金：</div>
                        <div class="div02">
                            <?php
                            $url = 'list_'.$mtype.'_'.$cityarea.'_'.$cityarea2.'_{curentparam}_'.$room.'_'.$totalarea.'_'.$rent_type.'_1_'.$keywords.'.html';
                            if (empty($price) || $price == '0-0'){
                                echo '<span><a href="'.build_url($url, '0-0').'">不限</a></span> ';
                            }else{
                                echo '<a href="'.build_url($url, '0-0').'">不限</a> ';
                            }
                            foreach ($house_price_option as $key=>$val){
                                if ($key == $price){
                                    echo '<span><a href="'.build_url($url, $key).'">'.$val.'</a></span> ';
                                }else{
                                    echo '<a href="'.build_url($url, $key).'">'.$val.'</a> ';
                                }
                            }
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                    <?php
                }
                if ($column['column_type'] == 4){
                    ?>
                    <li>
                        <div class="div01">面积：</div>
                        <div class="div02">
                            <?php
                            $url = 'list_'.$mtype.'_'.$cityarea.'_'.$cityarea2.'_'.$price.'_'.$room.'_{curentparam}_'.$rent_type.'_1_'.$keywords.'.html';
                            if (empty($totalarea) || $totalarea == '0-0'){
                                echo '<span><a href="'.build_url($url, '0-0').'">不限</a></span> ';
                            }else{
                                echo '<a href="'.build_url($url, '0-0').'">不限</a> ';
                            }
                            foreach ($house_totalarea_option as $key=>$val){
                                if ($key == $totalarea){
                                    echo '<span><a href="'.build_url($url, $key).'">'.$val.'</a></span> ';
                                }else{
                                    echo '<a href="'.build_url($url, $key).'">'.$val.'</a> ';
                                }
                            }
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                    <?php
                }
                ?>
                <?php
                if ($column['column_type'] == 1){
                    ?>
                    <li>
                        <div class="div01">户型：</div>
                        <div class="div02">
                            <?php
                            $url = 'list_'.$mtype.'_'.$cityarea.'_'.$cityarea2.'_'.$price.'_{curentparam}_'.$totalarea.'_'.$rent_type.'_1_'.$keywords.'.html';
                            if (empty($room)){
                                echo '<span><a href="'.build_url($url, 0).'">不限</a></span> ';
                            }else{
                                echo '<a href="'.build_url($url, 0).'">不限</a> ';
                            }
                            foreach ($room_option as $key=>$val){
                                if ($key == $room){
                                    echo '<span><a href="'.build_url($url, $key).'">'.$val.'</a></span> ';
                                }else{
                                    echo '<a href="'.build_url($url, $key).'">'.$val.'</a> ';
                                }
                            }
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                <?php }?>
                <?php
                if ($column['column_type'] == 1){
                    ?>
                    <li>
                        <div class="div01">方式：</div>
                        <div class="div02">
                            <?php
                            $url = 'list_'.$mtype.'_'.$cityarea.'_'.$cityarea2.'_'.$price.'_'.$room.'_'.$totalarea.'_{curentparam}_1_'.$keywords.'.html';
                            if (empty($rent_type) || $rent_type == 0){
                                echo '<span><a href="'.build_url($url, '0').'">不限</a></span> ';
                            }else{
                                echo '<a href="'.build_url($url, '0').'">不限</a> ';
                            }
                            if ($rent_type == 1){
                                echo '<span><a href="'.build_url($url, '1').'">整租</a></span> ';
                            }else{
                                echo '<a href="'.build_url($url, '1').'">整租</a>';
                            }
                            if ($rent_type == 2){
                                echo '<span><a href="'.build_url($url, '2').'">合租</a></span> ';
                            }else{
                                echo '<a href="'.build_url($url, '2').'">合租</a>';
                            }
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                <?php
                }
                if ($column['column_type'] == 16 || $column['column_type'] == 17) {
                    ?>
                    <li>
                        <div class="div01">类别：</div>
                        <div class="div02">
                            <?php
                            if ($_GET['column'] == $currentColumn . 'cz') {
                                echo '<span><a href="/' . $currentColumn . 'cz/">出租</a></span>';
                            } else {
                                echo '<a href="/' . $currentColumn . 'cz/">出租</a>';
                            }
                            if ($_GET['column'] == $currentColumn . 'cs') {
                                echo '<span><a href="/' . $currentColumn . 'cs/">出售</a></span>';
                            } else {
                                echo '<a href="/' . $currentColumn . 'cs/">出售</a>';
                            }
                            if ($_GET['column'] == $currentColumn . 'zr') {
                                echo '<span><a href="/' . $currentColumn . 'zr/">转让</a></span>';
                            } else {
                                echo '<a href="/' . $currentColumn . 'zr/">转让</a>';
                            }
                            if ($_GET['column'] == $currentColumn . 'qz') {
                                echo '<span><a href="/' . $currentColumn . 'qz/">求租</a></span>';
                            } else {
                                echo '<a href="/' . $currentColumn . 'qz/">求租</a>';
                            }
                            if ($_GET['column'] == $currentColumn . 'qg') {
                                echo '<span><a href="/' . $currentColumn . 'qg/">求购</a></span>';
                            } else {
                                echo '<a href="/' . $currentColumn . 'qg/">求购</a>';
                            }
                            ?>
                        </div>
                        <div class="clear"></div>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
    </div>
	<div id="main" class="list-box house-normal-list">
        <div id="title01">
            <span <?php if (empty($mtype)){echo ' class="on"';}?>><a href="<?php echo 'list_0_'.$cityarea.'_'.$cityarea2.'_'.$price.'_'.$room.'_'.$totalarea.'_'.$rent_type.'_1_' . $keywords . '.html';?>">全部求租</a></span>
            <span <?php if ($mtype == 2){echo ' class="on"';}?>><a href="<?php echo 'list_2_'.$cityarea.'_'.$cityarea2.'_'.$price.'_'.$room.'_'.$totalarea.'_'.$rent_type.'_1_' . $keywords . '.html';?>">个人</a></span>
            <span <?php if ($mtype == 1){echo ' class="on"';}?>><a href="<?php echo 'list_1_'.$cityarea.'_'.$cityarea2.'_'.$price.'_'.$room.'_'.$totalarea.'_'.$rent_type.'_1_' . $keywords . '.html';?>">经纪人</a></span>
            <span <?php if ($mtype == 3){echo ' class="on"';}?>><a href="<?php echo 'list_3_'.$cityarea.'_'.$cityarea2.'_'.$price.'_'.$room.'_'.$totalarea.'_'.$rent_type.'_1_' . $keywords . '.html';?>">非中介机构</a></span>
            <?php
            if (!in_array($column['column_type'], array(4, 16, 17)) && $show_cooperation) {
                ?>
            <span <?php if ($mtype == 4){echo ' class="on"';}?>><a href="<?php echo 'list_4_'.$cityarea.'_'.$cityarea2.'_'.$price.'_'.$room.'_'.$totalarea.'_'.$rent_type.'_1_' . $keywords . '.html';?>">合作信息</a></span>
            <?php
            }
            ?>
            <span><?php
                if (empty($member_id)) {
                    echo '<a href="javascript:void(0);" onclick="ShowLoginDialog(\'/history/list_' . $_GET['column'] . '_1.html\');">我浏览过的信息</a>';
                } else {
                    echo '<a href="/history/list_' . $_GET['column'] . '_1.html">我浏览过的信息</a>';
                }
                ?></span>
            <div class="clear"></div>
        </div>
        <div id="list_centent">
            <?php
            if ($website_search_bottom_ad) {
                echo '<div class="search-bottom-ad">' . $website_search_bottom_ad . '</div>';
            }
            ?>
            <?php
            if ($dataList) {
            ?>
            <div id="list">
                <ul>
                    <?php
                    foreach($dataList as $key => $item){
                        $unit_type = ' ' . get_unit_type($item['unit_type']);
                    ?>
                        <li>
                            <div class="div01">
                                <a target="_blank" href="<?php echo $item['url'];?>"><?php echo $item['title'];?></a>
                                <?php
                                if ($item['is_promote']) {
                                    echo '<span class="promote-top-icon">顶</span>';
                                }
                                ?>
                            </div>
                            <?php
                            if ($column['column_type'] == 2 || $column['column_type'] == 3 || $column['column_type'] == 4){
                                ?>
                                <div class="div07"><?php
                                    if ($item['house_totalarea'] > 0) {
                                        echo $item['house_totalarea'] . ' ㎡';
                                    } else {
                                        echo $item['end_totalarea'] . ' ㎡';
                                    }
                                    ?></div>
                                <div class="div08">
                                    <?php
                                    if ($item['house_price'] == 0) {
                                        if ($item['start_price'] > 0 || $item['end_price'] > 0) {
                                            echo get_range_str($item['start_price'], $item['end_price'], $unit_type);
                                        } else {
                                            echo '<strong>面议</strong>';
                                        }
                                    } else {
                                        echo '<strong>'.$item['house_price'].'</strong> ' . $unit_type;
                                    }
                                    ?>
                                </div>
                            <?php
                            }else{
                                ?>
                                <div class="div02"><?php
                                    if ($item['column_type'] == 1) {
                                        if ($item['rent_type'] == 2) {
                                            echo '合租';
                                        } else {
                                            if ($item['house_room'] > 0) {
                                                echo $item['house_room'].'室';
                                            } else {
                                                echo '不限';
                                            }
                                        }
                                    }
                                    ?></div>
                                <div class="div05"><?php
                                    if ($item['house_price'] == 0) {
                                        if ($item['start_price'] > 0 || $item['end_price'] > 0) {
                                            echo get_range_str($item['start_price'], $item['end_price'], $unit_type);
                                        } else {
                                            echo '<strong>面议</strong>';
                                        }
                                    } else {
                                        echo '<strong>'.$item['house_price'].'</strong> ' . $unit_type;
                                    }
                                    ?></div>
                            <?php
                            }
                            ?>
                            <div class="div04">
                                <?php echo $item['updated'];?>
                            </div>
                        </li>
                    <?php
                        if ($website_list_ad_1) {
                            if (($house_list_type == 1 && $key == 29) || ($house_list_type == 0 && $key == 4)) {
                                echo '<div class="data-list-ad">' . $website_list_ad_1 . '</div>';
                            }
                        }
                        if ($website_list_ad_2 && $key == 59) {
                            echo '<div class="data-list-ad">' . $website_list_ad_2 . '</div>';
                        }
                    }
                    ?>
                </ul>
            </div>
            <div id="page">
                <div class="pager">
                    <ul>
                        <?php echo $pagePanel;?>
                    </ul>
                </div>
            </div>
            <?php
            } else {
                /*if ($keywords) {
                    $tips = '很抱歉，没有找到与<strong>“' . $keywords . '”</strong>相符的信息，请您换个关键词试试吧~';
                } else {
                    $tips = '很抱歉，当前类目还没有相关信息~';
                }*/
                $tips = '暂无匹配信息，换个搜索条件试试';
                echo '<div class="data-list-tips">' . $tips . '</div>';
            }
            ?>
        </div>
        <div id="right">
            <div id="banner_right"><?php echo $website_right_ad_2;?></div>
            <div class="columns">
                <h2>我浏览过的信息<?php
                    if (empty($member_id)) {
                        echo '<span class="more" onclick="ShowLoginDialog(\'/history/list_' . $_GET['column'] . '_1.html\');">更多&gt;&gt;</span>';
                    } else {
                        echo '<a href="/history/list_' . $_GET['column'] . '_1.html" class="more">更多&gt;&gt;</a>';
                    }
                    ?></h2>
                <div class="same-house-list house-text-list">
                    <ul>
                        <?php
                        foreach ($history_house_list as $item) {
                            $unit_type = get_unit_type($item['unit_type']);
                            ?>
                            <li class="clear-fix">
                                <div class="list-house-info clear-fix">
                                    <div class="list-house-title">
                                        <a href="<?php echo $item['url'];?>" target="_blank"><?php echo $item['title'];?></a>
                                    </div>
                                    <div class="desc">
                                        <div class="price"><?php
                                            if ($item['house_price'] == 0) {
                                                if ($item['start_price'] > 0 || $item['end_price'] > 0) {
                                                    echo get_range_str($item['start_price'], $item['end_price'], $unit_type);
                                                } else {
                                                    echo '<strong>面议</strong>';
                                                }
                                            } else {
                                                echo '<strong>'.$item['house_price'].'</strong>' . $unit_type;
                                            }
                                            ?></div>
                                        <?php
                                        $tmp = '';
                                        if ($item['column_type'] == 1) {
                                            if ($item['rent_type'] == 1) {
                                                $tmp = '整租';
                                            } elseif ($item['rent_type'] == 2) {
                                                $tmp = '合租';
                                            } else {
                                                $tmp = '';
                                            }
                                            if ($item['house_room']) {
                                                if ($tmp) {
                                                    $tmp .= '<span class="split">|</span>';
                                                }
                                                $tmp .=  $item['house_room'] . '室';
                                                if ($item['house_hall']) {
                                                    $tmp .= $item['house_hall'] . '厅';
                                                }
                                                if ($item['house_toilet']) {
                                                    $tmp .= $item['house_toilet'] . '卫';
                                                }
                                            }
                                        }
                                        if ($item['house_totalarea']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">|</span>';
                                            }
                                            $tmp .= $item['house_totalarea'] . '㎡';
                                        }
                                        echo $tmp;
                                        ?><div class="update-time"><?php echo $item['updated'];?></div>
                                    </div>
                                </div>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div id="banner_right"><?php echo $website_right_ad;?></div>
            <?php
            if ($website_right_hot_search_ad) {
                echo '<div id="banner_right">' . $website_right_hot_search_ad . '</div>';
            }
            ?>
            <?php
            if ($news_list) {
                ?>
                <div class="columns section-box">
                    <h2>最新房产资讯</h2>
                    <div class="list list-text clearfix">
                        <?php
                        foreach ($news_list as $item) {
                            ?>
                            <div class="item">
                                <div class="title"><a href="//www.<?php echo $cfg['page']['basehost'];?>/news/<?php echo $item['url'];?>" target="_blank"><?php echo $item['title'];?></a></div>
                                <div class="info clearfix"><span class="text"><?php echo $item['description'];?></span><div class="time"><?php echo MyDate('m-d', $item['add_time']);?></div></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <?php
            if ($website_right_news_ad) {
                echo '<div id="banner_right">' . $website_right_news_ad . '</div>';
            }
            ?>
        </div>
        <div class="clear"></div>
    </div>
	<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
    <script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            <?php
                if (!empty($search_history_list)) {
                ?>
            var searchHistoryList = <?php echo json_encode($search_history_list);?>;
            GetSearchHistoryList('#text', searchHistoryList);
            <?php
            }
            ?>
        });
    </script>
    <script type="text/javascript" src="/js/stickUp.min.js?v=<?php echo $webConfig['static_version']; ?>"></script>
    <script type="text/javascript">
        jQuery(function($) {
            $(document).ready(function () {
                $('.header-search-wrap').stickUp({
                    marginTop: 0
                });
            });
        });
    </script>
</body>
</html>
