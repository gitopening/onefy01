<?php
require_once(dirname(__FILE__) . '/path.inc.php');
require_once($cfg['path']['lib'] . 'classes/Pages.class.php');
$now_time = time();

$DdObj = new Dd($query);
//房屋亮点
$bright_spot_dict = $DdObj->getItemArray(27, true, MEMCACHE_MAX_EXPIRETIME);
//装修情况
$fitment_dict = $DdObj->getItemArray(7, true, MEMCACHE_MAX_EXPIRETIME);
//朝向
$toward_dict = $DdObj->getItemArray(6, true, MEMCACHE_MAX_EXPIRETIME);
//产权
$belong_dict = $DdObj->getItemArray(23, true, MEMCACHE_MAX_EXPIRETIME);
//楼层
$floor_dict = array(
    '0-1' => '1层',
    '1-6' => '6层以下',
    '6-12' => '6-12层',
    '12-0' => '12层以上',
);
//房龄
$house_age_dict = array(
    '0-2' => '2年以下',
    '2-5' => '2-5年',
    '5-10' => '5-10年',
    '10-0' => '10年以上'
);

$order_dict = array(
    '2_0' => '默认排序',
    '3_1' => '总价由低到高',
    '3_0' => '总价由高到低',
    '5_1' => '单价由低到高',
    '5_0' => '单价由高到低',
    '4_1' => '面积由低到高',
    '4_0' => '面积由高到低'
);

$cityarea_option = get_region_enum($cityInfo['city_id'], 'sort');
$parent_id_array = array();
foreach ($cityarea_option as $value){
    $parent_id_array[] = $value['region_id'];
}
$cityarea2_list = get_region_enum($parent_id_array);
$house_type_option = Dd::getArray('house_type');

//Sphinx搜索
$Sphinx = Sphinx::getInstance();
//$Sphinx->SetFilter('column_type', array(1, 2, 3, 4));
if ($column['column_type']) {
    $Sphinx->SetFilter('column_type', array($column['column_type']));
}
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$Sphinx->SetFilterRange('house_thumb_length', 10, 300);
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, '@random');
$list_number = 4;
//$key = 'home_sale_list_cityid_mtype_listnumber';
$memcache_key_name = MEMCACHE_PREFIX . 'rec_new_list_' . $cityInfo['id'] . '_' . $column['column_type']. '_' . $list_number;
$recommend_house = $Cache->get($memcache_key_name);
if (empty($recommend_house)) {
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result_promote = $Sphinx->Query('', SPHINX_MEMBER_NEW_HOME_PROMOTE_INDEX);
    $list_count = intval($result_promote['total']);
    if ($list_count < 4) {
        $house_ids = array();
        foreach ($result_promote['matches'] as $item) {
            $house_ids[] = $item['id'];
        }
        $Sphinx->SetFilter('filter_id', $house_ids, true);

        $list_number = 4 - $list_count;
        $Sphinx->SetLimits(0, $list_number, $list_number);
        $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');
        $result = $Sphinx->Query('', SPHINX_MEMBER_NEW_INDEX);
    } else {
        $result = array();
    }
    $result_matches = array_merge((array)$result_promote['matches'], (array)$result['matches']);
    $recommend_house = array();
    foreach ($result_matches as $item){
        $data = array();
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column['house_title']);
        $data['title'] = cn_substr_utf8($data['title'], 48);
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
        $data['is_promote'] = $item['attrs']['is_promote'];
        /*if ($item['attrs']['mtype'] == 2 || $data['is_promote'] || $data['house_thumb']) {
            $data['title'] = cn_substr_utf8(str_replace(' ', '', $data['title']), 54);
            if ($item['attrs']['mtype'] == 2) {
                $data['title'] .= '(个人)';
            }
        }*/
        $data['id'] = $item['id'];
        $data['column_type'] = $item['attrs']['column_type'];
        $data['user_type'] = $item['attrs']['mtype'];
        $data['mid'] = intval($item['attrs']['mid']);
        $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
        $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
        $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
        $data['house_veranda'] = FormatHouseRoomType($item['attrs']['house_veranda'], 9);
        $data['house_floor'] = intval($item['attrs']['house_floor']);
        $data['house_top_floor'] = intval($item['attrs']['house_topfloor']);
        $data['house_toward'] = intval($item['attrs']['house_toward']);
        $data['house_fitment'] = intval($item['attrs']['house_fitment']);
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['house_price_average'] = FormatHousePrice($item['attrs']['house_price_average']);
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        if ($data['column_type'] == 1) {
            $data['url'] = '/zzx/';
        } elseif ($data['column_type'] == 2) {
            $data['url'] = '/xzlx/';
        } elseif ($data['column_type'] == 3) {
            $data['url'] = '/spx/';
        }
        $data['url'] .= 'house_' . $item['id'] . $url_fix . '.html';
        $data['owner_name'] = $item['attrs']['owner_name'];
        if ($data['mid'] && in_array($data['user_type'], array(1, 3, 4)) && $item['attrs']['member_db_index'] == MEMBER_DB_INDEX) {
            //取得经纪人公司门店
            $member_info = $member_query->table('member', 'm')->join($member_query->db_prefix . 'broker_info AS b ON b.id = m.id', 'LEFT')->field('m.id, m.user_type, m.user_type_sub, m.user_type_custom, b.realname, b.company, b.status')->where('m.id = ' . $data['mid'] . ' AND m.account_open = 1')->cache(MEMCACHE_PREFIX . 'member_house_list_' . $data['mid'], MEMCACHE_MAX_EXPIRETIME)->one();
            if (!empty($member_info['realname'])) {
                $data['owner_name'] = $member_info['realname'];
                $data['company'] = $member_info['company'];
                $data['user_type_sub'] = $member_info['user_type_sub'];
                $data['user_type_custom'] = $member_info['user_type_custom'];
                $data['user_type_status'] = $member_info['status'];
            }
        }
        $data['is_cooperation'] = intval($item['attrs']['is_cooperation']);
        if ($data['column_type'] == 1) {
            $data['bright_spot'] = $item['attrs']['bright_spot'];
            $data['parking_lot'] = intval($item['attrs']['parking_lot']);
        }
        $recommend_house[] = $data;
    }
    if ($recommend_house) {
        $Cache->set($memcache_key_name, $recommend_house, 60);
    }
}


$Sphinx->ResetFilters();

//链接参数处理list_mtype_area_area2_(sprice-eprice)_room_(sta-eta)_toward_fitment_floor_belong_age_orderfield_order_page_关键字.html
$param = explode('_', trim($_GET['param']));
$mtype = intval($param[0]);
$cityarea = intval($param[1]);
$cityarea2 = intval($param[2]);
$price = trim($param[3]) == '' ? '0-0' : trim($param[3]);
$house_price_average = trim($param[4]) == '' ? '0-0' : trim($param[4]);
$room = intval($param[5]);
$totalarea = trim($param[6]) == '' ? '0-0' : trim($param[6]);
$house_toward = intval($param[7]);
$house_fitment = intval($param[8]);
$house_floor = trim($param[9]) == '' ? '0-0' : trim($param[9]);
$house_belong = intval($param[10]);
$house_age = trim($param[11]) == '' ? '0-0' : trim($param[11]);
$orderby = empty($param[12]) ? 2 : intval($param[12]);
$orderway = intval($param[13]);
$pageno = empty($param[14]) ? 1 : intval($param[14]);
$keywords = trim($param[15]);
//$keywords = trim(mb_convert_encoding($keywords, 'gbk', 'utf-8')); //编码转换

if ($cityarea){
    $cityarea2_option = get_region_enum($cityarea);
}

//添加搜索关键字到搜索记录中
$search_history_list = get_search_history_list();
if (!empty($keywords)) {
    $search_history_list = add_search_keyword_history($keywords);
}

//房源列表展示类型
//$house_list_type = intval($_COOKIE['house_list_type']) == 1 ? 1 : 0;
if (!isset($_COOKIE['house_list_type']) || $_COOKIE['house_list_type'] === '') {
    $house_list_type = 1;
} else {
    $house_list_type = intval($_COOKIE['house_list_type']) == 1 ? 1 : 0;
}

//按更新时间和发布时间排序时，默认为倒序
if (empty($orderby) || $orderby == 1 || $orderby == 2) {
    $orderway = 0;
}

$house_price_option = get_filter_array($column['price_option']);
$house_price_average_option = array(
    '0-10000' => '1万以下',
    '10000-20000' => '1-2万',
    '20000-30000' => '2-3万',
    '30000-40000' => '3-4万',
    '40000-60000' => '4-6万',
    '60000-80000' => '6-8万',
    '80000-100000' => '8-10万',
    '100000-0' => '10万以上'
);
$house_totalarea_option = get_filter_array($column['totalarea_option']);

//$sphinx->SetFilterRange('updated', $expireTime + 1, 10000000000);
if ($column['column_type']) {
    $Sphinx->SetFilter('column_type', array($column['column_type']));
}

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

$filter_title = $cityInfo['city_name'];

if ($cityarea) {
    $filter_title .= $cityarea_option[$cityarea]['region_name'];
    if (empty($cityarea2)) {
        $Sphinx->SetFilter('cityarea_id', array($cityarea));
    } else {
        $Sphinx->SetFilter('cityarea2_id', array($cityarea2));
        $filter_title .= $cityarea2_option[$cityarea2]['region_name'];
    }
}

if ($room) {
    $Sphinx->SetFilter('house_room', array($room));
    $filter_title .= $room_option[$room];
}

list($start, $end) = explode('-', $house_price_average);
$start = intval($start);
$end = intval($end);

if ($orderway == 1 && $orderby == 5 && $start == 0 && empty($keywords)) {
    if ($column['column_type'] == 1) {
        $start = 10;
    } else {
        $Sphinx->SetFilter('house_price_average', array(0), true);
    }
}

if ($start && $end) {
    $Sphinx->SetFilterRange('house_price_average', $start, $end);
} elseif (empty($start) && !empty($end)) {
    $Sphinx->SetFilterRange('house_price_average', 0, $end);
} elseif (!empty($start) && empty($end)) {
    $Sphinx->SetFilterRange('house_price_average', $start, 10000000000);
}

if ($start && $end) {
    $filter_title .= $start . '~' . $end . '元/㎡';
} elseif ($start && empty($end)) {
    $filter_title .= $start . '元/㎡以上';
} elseif (empty($start) && $end) {
    $filter_title .= $end . '元/㎡以下';
}

list($start, $end) = explode('-', $price);
$start = intval($start);
$end = intval($end);

if ($orderway == 1 && $orderby == 3 && $start == 0 && empty($keywords)) {
    if ($column['column_type'] == 1) {
        $start = 10;
    } else {
        $Sphinx->SetFilter('house_price', array(0), true);
    }
}

if ($start && $end) {
    $Sphinx->SetFilterRange('house_price', $start, $end);
} elseif (empty($start) && !empty($end)) {
    $Sphinx->SetFilterRange('house_price', 0, $end);
} elseif (!empty($start) && empty($end)) {
    $Sphinx->SetFilterRange('house_price', $start, 10000000000);
}

if ($start && $end) {
    $filter_title .= $start . '~' . $end . '万元';
} elseif ($start && empty($end)) {
    $filter_title .= $start . '万元以上';
} elseif (empty($start) && $end) {
    $filter_title .= $end . '万元以下';
}

list($start, $end) = explode('-', $totalarea);
$start = intval($start);
$end = intval($end);

if ($orderway == 1 && $orderby == 4 && $start == 0) {
    $start = 1;
}

if ($start && $end) {
    $Sphinx->SetFilterRange('house_totalarea', $start, $end);
} elseif (empty($start) && !empty($end)) {
    $Sphinx->SetFilterRange('house_totalarea', 0, $end);
} elseif (!empty($start) && empty($end)) {
    $Sphinx->SetFilterRange('house_totalarea', $start, 10000000000);
}

/*if ($start && $end) {
    $filter_title .= $start . '~' . $end . '㎡';
} elseif ($start && empty($end)) {
    $filter_title .= $start . '㎡以上';
} elseif (empty($start) && $end) {
    $filter_title .= $end . '㎡以下';
}*/

if ($house_toward) {
    $Sphinx->SetFilter('house_toward', array($house_toward));
}

list($start, $end) = explode('-', $house_floor);
$start = intval($start);
$end = intval($end);
if ($start && $end) {
    $Sphinx->SetFilterRange('house_floor', $start, $end);
} elseif (empty($start) && !empty($end)) {
    $Sphinx->SetFilterRange('house_floor', 0, $end);
} elseif (!empty($start) && empty($end)) {
    $Sphinx->SetFilterRange('house_floor', $start, 10000000000);
}

if ($house_belong) {
    $Sphinx->SetFilter('belong', array($house_belong));
}

if ($house_fitment) {
    $Sphinx->SetFilter('house_fitment', array($house_fitment));
}

list($start, $end) = explode('-', $house_age);
$now_year = MyDate('Y', time());
$year_start = intval($now_year - $end);
$year_end = intval($now_year - $start);
if ($start && $end) {
    $Sphinx->SetFilterRange('house_age', $year_start, $year_end);
} elseif (empty($start) && !empty($end)) {
    $Sphinx->SetFilterRange('house_age', $year_start, 10000000000);
} elseif (!empty($start) && empty($end)) {
    $Sphinx->SetFilterRange('house_age', 0, $year_end);
}

if ($orderby == 1 || empty($orderby)) {
    $orderbyfield = 'created';
} elseif ($orderby == 2) {
    $orderbyfield = 'updated';
} elseif ($orderby == 3) {
    $orderbyfield = 'house_price';
} elseif ($orderby == 4) {
    $orderbyfield = 'house_totalarea';
} elseif ($orderby == 5) {
    $orderbyfield = 'house_price_average';
}
/*if ($orderway == 1){
    $sort_order = 'asc';
}else{
    $sort_order = 'desc';
}*/

if ($orderway == 1) {
    //$Sphinx->SetFilterRange('updated', strtotime('-6 month'), $now_time);
    //$Sphinx->SetSortMode(SPH_SORT_ATTR_ASC, $orderbyfield);
    $Sphinx->SetSortMode(SPH_SORT_EXTENDED, 'is_promote DESC,' . $orderbyfield . ' ASC,@relevance DESC,@id DESC');
} else {
    //$Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, $orderbyfield);
    $Sphinx->SetSortMode(SPH_SORT_EXTENDED, 'is_promote DESC,' . $orderbyfield . ' DESC,@relevance DESC,@id DESC');
}

$list_num = $cfg['list_number'];
//缓存Key
$list_key = MEMCACHE_PREFIX . 'newl_' . $cityInfo['id'] . '_' . $column['column_type'] . '_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_' . $list_num . '_' . $pageno . '_' . $keywords;
$count_key = MEMCACHE_PREFIX . 'newc_' . $cityInfo['id'] . '_' . $column['column_type'] . '_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_' . $list_num . '_' . $keywords;
//从Memcache中取数据
$dataCount = $Cache->get($count_key);
$dataList = $Cache->get($list_key);
if (empty($dataList) || empty($dataCount)) {
    $max_data_limit = MAX_DATA_LIMIT;
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
    $result = $Sphinx->Query($parsed_keywords, SPHINX_SEARCH_NEW_INDEX);
    $pages = new Pages(intval($result['total']), $list_num);
    $dataList = array();
    if ($result['total'] > 0) {
        foreach ($result['matches'] as $item) {
            $data = array();
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column['house_title']);
            $data['title'] = cn_substr_utf8($data['title'], 48);
            $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
            $data['is_promote'] = $item['attrs']['is_promote'];
            /*if ($item['attrs']['mtype'] == 2 || $data['is_promote'] || $data['house_thumb']) {
                $data['title'] = cn_substr_utf8(str_replace(' ', '', $data['title']), 54);
                if ($item['attrs']['mtype'] == 2) {
                    $data['title'] .= '(个人)';
                }
            }*/
            $data['id'] = $item['id'];
            $data['column_type'] = $item['attrs']['column_type'];
            $data['user_type'] = $item['attrs']['mtype'];
            $data['mid'] = intval($item['attrs']['mid']);
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['house_veranda'] = FormatHouseRoomType($item['attrs']['house_veranda'], 9);
            $data['house_floor'] = intval($item['attrs']['house_floor']);
            $data['house_top_floor'] = intval($item['attrs']['house_topfloor']);
            $data['house_toward'] = intval($item['attrs']['house_toward']);
            $data['house_fitment'] = intval($item['attrs']['house_fitment']);
            $data['cityarea_id'] = $item['attrs']['cityarea_id'];
            $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['house_price_average'] = FormatHousePrice($item['attrs']['house_price_average']);
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            if ($data['column_type'] == 1) {
                $data['url'] = '/zzx/';
            } elseif ($data['column_type'] == 2) {
                $data['url'] = '/xzlx/';
            } elseif ($data['column_type'] == 3) {
                $data['url'] = '/spx/';
            }
            $data['url'] .= 'house_' . $item['id'] . $url_fix . '.html';
            $data['owner_name'] = $item['attrs']['owner_name'];
            if ($data['mid'] && in_array($data['user_type'], array(1, 3, 4)) && $item['attrs']['member_db_index'] == MEMBER_DB_INDEX) {
                //取得经纪人公司门店
                $member_info = $member_query->table('member', 'm')->join($member_query->db_prefix . 'broker_info AS b ON b.id = m.id', 'LEFT')->field('m.id, m.user_type, m.user_type_sub, m.user_type_custom, b.realname, b.company, b.status')->where('m.id = ' . $data['mid'] . ' AND m.account_open = 1')->cache(MEMCACHE_PREFIX . 'member_house_list_' . $data['mid'], MEMCACHE_MAX_EXPIRETIME)->one();
                if (!empty($member_info['realname'])) {
                    $data['owner_name'] = $member_info['realname'];
                    $data['company'] = $member_info['company'];
                    $data['user_type_sub'] = $member_info['user_type_sub'];
                    $data['user_type_custom'] = $member_info['user_type_custom'];
                    $data['user_type_status'] = $member_info['status'];
                }
            }
            $data['is_cooperation'] = intval($item['attrs']['is_cooperation']);
            if ($data['column_type'] == 1) {
                $data['bright_spot'] = $item['attrs']['bright_spot'];
                $data['parking_lot'] = intval($item['attrs']['parking_lot']);
            }
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
$url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_{pageno}_' . $keywords . '.html';
$pagePanel = $pages->get_pager_nav('4', $pageno, $url);

if ($mtype == 1){
    $mtypetitle = ',房产中介';
}elseif ($mtype == 2){
    $mtypetitle = ',个人房源';
}elseif ($mtype == 3){
    $mtypetitle = ',非中介机构房源';
}
$cityareaTitle = $cityInfo['city_name'];
if ($cityarea){
    $cityareaTitle .= $cityarea_option[$cityarea]['region_name'] . $cityarea2_option[$cityarea2]['region_name'];
}

switch ($_GET['column']){
    case 'new':
        $page->title = '【' . $filter_title . '楼盘—' . $cityInfo['city_name'] . '房价' . $cityInfo['city_name'] . '新房-' . $cityInfo['city_name'] . '房产网】-' . $page->titlec;
        $page->description = $cityInfo['city_name'] . $page->titlec . '新房频道查找' . $cityInfo['city_name'] . '新开盘楼盘,' . $cityInfo['city_name'] . '新房房源房价信息尽在' . $page->titlec;
        $page->keywords = $filter_title . '楼盘,' . $filter_title. '房产信息,' . $filter_title . '新房,' . $cityInfo['city_name'] . '房价';
        break;
    case 'xzlx':
        $page->title = '【' . $filter_title . '楼盘—' . $cityInfo['city_name'] . '房价' . $cityInfo['city_name'] . '新房-' . $cityInfo['city_name'] . '房产网】-' . $page->titlec;
        $page->description = $cityInfo['city_name'] . $page->titlec . '新房频道查找' . $cityInfo['city_name'] . '新开盘楼盘,' . $cityInfo['city_name'] . '新房房源房价信息尽在' . $page->titlec;
        $page->keywords = $filter_title . '楼盘,' . $filter_title. '房产信息,' . $filter_title . '新房,' . $cityInfo['city_name'] . '房价';
        break;
    case 'spx':
        $page->title = '【' . $filter_title . '楼盘—' . $cityInfo['city_name'] . '房价' . $cityInfo['city_name'] . '新房-' . $cityInfo['city_name'] . '房产网】-' . $page->titlec;
        $page->description = $cityInfo['city_name'] . $page->titlec . '新房频道查找' . $cityInfo['city_name'] . '新开盘楼盘,' . $cityInfo['city_name'] . '新房房源房价信息尽在' . $page->titlec;
        $page->keywords = $filter_title . '楼盘,' . $filter_title. '房产信息,' . $filter_title . '新房,' . $cityInfo['city_name'] . '房价';
        break;
    default:
        $page->title = '【' . $filter_title . '楼盘—' . $cityInfo['city_name'] . '房价' . $cityInfo['city_name'] . '新房-' . $cityInfo['city_name'] . '房产网】-' . $page->titlec;
        $page->description = $cityInfo['city_name'] . $page->titlec . '新房频道查找' . $cityInfo['city_name'] . '新开盘楼盘,' . $cityInfo['city_name'] . '新房房源房价信息尽在' . $page->titlec;
        $page->keywords = $filter_title . '楼盘,' . $filter_title. '房产信息,' . $filter_title . '新房,' . $cityInfo['city_name'] . '房价';
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
$history_list = get_history_list('house_new');
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
    $memcache_house_key = 'house_new_' . $house_id;
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
    $house_info = $house_query->field('house.*, extend.*')->table('house_new', 'house')->join($house_query->db_prefix . 'house_new_extend AS extend ON house.id = extend.house_id', 'LEFT')->where($condition)->cache($memcache_house_key, MEMCACHE_MAX_EXPIRETIME)->one();
    if (empty($house_info)) {
        continue;
    }
    $data = array();
    $data['title'] = get_house_title($house_info, $cityarea_option, $cityarea2_list, $column['house_title']);
    $data['house_thumb'] = GetPictureUrl($house_info['house_thumb'], 2, $item['db']);
    $data['id'] = $item['id'];
    $data['column_type'] = $item['column_type'];
    $data['user_type'] = $house_info['mtype'];
    $data['mid'] = intval($house_info['mid']);
    $data['house_room'] = FormatHouseRoomType($house_info['house_room'], 9);
    $data['house_hall'] = FormatHouseRoomType($house_info['house_hall'], 9);
    $data['house_toilet'] = FormatHouseRoomType($house_info['house_toilet'], 9);
    $data['house_veranda'] = FormatHouseRoomType($house_info['house_veranda'], 9);
    $data['house_floor'] = intval($house_info['house_floor']);
    $data['house_top_floor'] = intval($house_info['house_topfloor']);
    $data['house_toward'] = intval($house_info['house_toward']);
    $data['house_fitment'] = intval($house_info['house_fitment']);
    $data['cityarea_id'] = $house_info['cityarea_id'];
    $data['cityarea2_id'] = $house_info['cityarea2_id'];
    $data['borough_name'] = $house_info['borough_name'];
    $data['updated'] = time2Units($now_time - $house_info['updated']);
    $data['house_totalarea'] = $house_info['house_totalarea'];
    $data['house_price'] = FormatHousePrice($house_info['house_price']);
    $data['house_price_average'] = FormatHousePrice($house_info['house_price_average']);
    if ($item['db'] == 0) {
        $url_fix = '';
    } else {
        $url_fix = MEMBER_DB_INDEX == $item['db'] ? 'x' : '_' . $item['db'];
    }
    if ($house_info['column_type'] == 1) {
        $data['url'] = '/zzx/';
    } elseif ($house_info['column_type'] == 2) {
        $data['url'] = '/xzlx/';
    } elseif ($house_info['column_type'] == 3) {
        $data['url'] = '/spx/';
    }
    $data['url'] .= 'house_' . $item['id'] . $url_fix . '.html';
    $data['owner_name'] = $house_info['owner_name'];
    $data['is_cooperation'] = intval($house_info['is_cooperation']);
    if ($data['column_type'] == 1) {
        $data['bright_spot'] = $house_info['bright_spot'];
        $data['parking_lot'] = intval($house_info['parking_lot']);
    }
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
    <script type="text/javascript" src="/js/jquery.lazyload.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/autocomplete/jquery.autocomplete.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            SwitchListType();
            <?php
                if (!empty($search_history_list)) {
                ?>
            var searchHistoryList = <?php echo json_encode($search_history_list);?>;
            GetSearchHistoryList('#text', searchHistoryList);
            <?php
            }
            ?>
            $('.list-house-thumb img, #recommend-house img').lazyload({effect: "fadeIn", threshold: 180});
        });
    </script>
</head>

<body>
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div class="header-search-wrap">
    <div class="header-search-box clear-fix">
        <div class="search-form">
            <form action="" method="get" id="search-form" onsubmit="return dosearch2();">
                <input type="hidden" name="url" id="url" value="<?php echo 'list_' . $mtype . '_0_0_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_[keywords].html';?>" />
                <input type="text" id="text" name="q" value="<?php echo $keywords;?>" autocomplete="off" placeholder="请输入区域、商圈或小区名进行搜索" />
                <div class="common-submit-btn search-submit-btn" onclick="dosearch2();">搜索</div>
            </form>
        </div>
        <a href="/member/house_new.php" target="_blank" class="publish-btn">发布房源</a>
        <div class="clear"></div>
    </div>
</div>
<div class="position list-position">
    <a href="/"><?php echo $page->titlec . $cityInfo['city_name'];?></a> &gt;
    <a href="/<?php echo $_GET['column'];?>/"><?php echo $cityInfo['city_name'] . $column['house_title'];?></a> &gt;
    <?php
    if ($cityarea) {
        ?>
        <a href="list_<?php echo $mtype;?>_<?php echo $cityarea;?>.html"><?php echo $cityarea_option[$cityarea]['region_name'] .  $column['house_title'];?></a> &gt;
        <?php
    }
    if ($cityarea && $cityarea2) {
        ?>
        <a href="list_<?php echo $mtype;?>_<?php echo $cityarea;?>_<?php echo $cityarea2;?>.html"><?php echo $cityarea2_option[$cityarea2]['region_name']. $column['house_title'];?></a> &gt;
        <?php
    }
    if ($column['column_type'] == 4) {
        $house_type_title = '信息';
    } else {
        $house_type_title = '房源';
    }
    if ($mtype == 1) {
        echo '<a href="list_1.html">' . $column['column_position_title'] . '经纪人' . $house_type_title . '</a>';
    } elseif ($mtype == 2) {
        echo '<a href="list_2.html">' . $column['column_position_title'] . '个人' . $house_type_title . '</a>';
    } elseif ($mtype == 3) {
        echo '<a href="list_3.html">' . $column['column_position_title'] . '非中介机构' . $house_type_title . '</a>';
    } elseif ($mtype == 4) {
        //echo '<a href="list_4.html">' . $column['column_position_title'] . '合作' . $house_type_title . '</a>';
    } else {
        echo '<a href="list_0.html">' . $column['column_position_title'] . '全部' . $house_type_title . '</a>';
    }
    ?>
</div>
<div class="section-box house-list-section-box">
    <div id="Region">
        <ul>
            <li>
                <div class="div01">区域：</div>
                <div class="div02">
                    <?php
                    $url = 'list_' . $mtype . '_{curentparam}_0_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_.html';
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
                    $url = 'list_' . $mtype . '_' . $cityarea . '_{curentparam}_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
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
            ?>
            <li>
                <div class="div01">总价：</div>
                <div class="div02">
                    <?php
                    $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_{curentparam}_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
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
                    <div class="filter-input-box">
                        <?php
                        $range_tmp = explode('-', $price);
                        ?>
                        <input type="hidden" id="price_url" name="price_url" value="<?php echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_{curentparam}_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_.html';?>" />
                        <input type="text" id="start_price" name="start_price" value="<?php echo !empty($range_tmp[0]) ? $range_tmp[0] : '';?>" />
                    </div>
                    <div class="filter-split">-</div>
                    <div class="filter-input-box">
                        <input type="text" id="end_price" name="end_price" value="<?php echo !empty($range_tmp[1]) ? $range_tmp[1] : '';?>" />
                    </div>
                    <div id="price-search" class="filter-search">价格筛选</div>
                </div>
                <div class="clear"></div>
            </li>
            <li>
                <div class="div01">单价：</div>
                <div class="div02">
                    <?php
                    $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_{curentparam}_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
                    if (empty($house_price_average) || $house_price_average == '0-0'){
                        echo '<span><a href="'.build_url($url, '0-0').'">不限</a></span> ';
                    }else{
                        echo '<a href="'.build_url($url, '0-0').'">不限</a> ';
                    }
                    foreach ($house_price_average_option as $key=>$val){
                        if ($key == $house_price_average){
                            echo '<span><a href="'.build_url($url, $key).'">'.$val.'</a></span> ';
                        }else{
                            echo '<a href="'.build_url($url, $key).'">'.$val.'</a> ';
                        }
                    }
                    ?>
                    <div class="filter-input-box">
                        <?php
                        $range_tmp = explode('-', $house_price_average);
                        ?>
                        <input type="hidden" id="price_average_url" name="price_average_url" value="<?php echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_{curentparam}_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_.html';?>" />
                        <input type="text" id="start_price_average" name="start_price_average" value="<?php echo !empty($range_tmp[0]) ? $range_tmp[0] : '';?>" />
                    </div>
                    <div class="filter-split">-</div>
                    <div class="filter-input-box">
                        <input type="text" id="end_price_average" name="end_price_average" value="<?php echo !empty($range_tmp[1]) ? $range_tmp[1] : '';?>" />
                    </div>
                    <div id="price-average-search" class="filter-search">价格筛选</div>
                </div>
                <div class="clear"></div>
            </li>
            <li>
                <div class="div01">面积：</div>
                <div class="div02">
                    <?php
                    $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_{curentparam}_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
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
                    <div class="filter-input-box">
                        <?php
                        $range_tmp = explode('-', $totalarea);
                        ?>
                        <input type="hidden" id="totalarea_url" name="totalarea_url" value="<?php echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_{curentparam}_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_.html';?>" />
                        <input type="text" id="start_totalarea" name="start_totalarea" value="<?php echo !empty($range_tmp[0]) ? $range_tmp[0] : '';?>" />
                    </div>
                    <div class="filter-split">-</div>
                    <div class="filter-input-box">
                        <input type="text" id="end_totalarea" name="end_totalarea" value="<?php echo !empty($range_tmp[1]) ? $range_tmp[1] : '';?>" />
                    </div>
                    <div id="totalarea-search" class="filter-search">面积筛选</div>
                </div>
                <div class="clear"></div>
            </li>
            <?php if ($column['column_type'] == 1){?>
                <li>
                    <div class="div01">户型：</div>
                    <div class="div02">
                        <?php
                        $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_{curentparam}_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
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
                <?php
            }
            ?>
            <li>
                <div class="div01">其它：</div>
                <div class="div02 clear-fix">
                    <?php
                    $column_type_option = array(
                        1 => '住宅',
                        2 => '写字楼',
                        3 => '商铺'
                    );
                    $column_type = empty($_GET['column']) ? 'new' : $_GET['column'];
                    $url = '/{curentparam}/list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
                    ?>
                    <div class="filter-select-box<?php echo in_array($column_type, array('zzx', 'xzlx', 'spx')) ? ' selected' : '';?>" data-url="<?php echo $url;?>">
                        <div class="caption"><?php echo $column_type == 'new' ? '物业类型' : $column_type_option[$column['column_type']];?></div>
                        <div class="option">
                            <div class="item" data-value="new">物业类型</div>
                            <div class="item" data-value="zzx">住宅</div>
                            <div class="item" data-value="xzlx">写字楼</div>
                            <div class="item" data-value="spx">商铺</div>
                        </div>
                    </div>
                    <?php
                    $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_{curentparam}_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
                    ?>
                    <div class="filter-select-box<?php echo empty($toward_dict[$house_toward]) ? '' : ' selected';?>" data-url="<?php echo $url;?>">
                        <div class="caption"><?php echo empty($toward_dict[$house_toward]) ? '朝向不限' : $toward_dict[$house_toward];?></div>
                        <div class="option">
                            <div class="item" data-value="0">朝向不限</div>
                            <?php
                            foreach ($toward_dict as $key => $item) {
                                echo '<div class="item" data-value="' . $key . '">'. $item . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_{curentparam}_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
                    ?>
                    <div class="filter-select-box<?php echo empty($floor_dict[$house_floor]) ? '' : ' selected';?>" data-url="<?php echo $url;?>">
                        <div class="caption"><?php echo empty($floor_dict[$house_floor]) ? '楼层不限' : $floor_dict[$house_floor];?></div>
                        <div class="option">
                            <div class="item" data-value="0">楼层不限</div>
                            <?php
                            foreach ($floor_dict as $key => $item) {
                                echo '<div class="item" data-value="' . $key . '">'. $item . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_{curentparam}_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
                    ?>
                    <div class="filter-select-box<?php echo empty($belong_dict[$house_belong]) ? '' : ' selected';?>" data-url="<?php echo $url;?>">
                        <div class="caption"><?php echo empty($belong_dict[$house_belong]) ? '产权不限' : $belong_dict[$house_belong];?></div>
                        <div class="option">
                            <div class="item" data-value="0">产权不限</div>
                            <?php
                            foreach ($belong_dict as $key => $item) {
                                echo '<div class="item" data-value="' . $key . '">'. $item . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_{curentparam}_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
                    ?>
                    <div class="filter-select-box<?php echo empty($house_age_dict[$house_age]) ? '' : ' selected';?>" data-url="<?php echo $url;?>">
                        <div class="caption"><?php echo empty($house_age_dict[$house_age]) ? '房龄不限' : $house_age_dict[$house_age];?></div>
                        <div class="option">
                            <div class="item" data-value="0">房龄不限</div>
                            <?php
                            foreach ($house_age_dict as $key => $item) {
                                echo '<div class="item" data-value="' . $key . '">'. $item . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_{curentparam}_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
                    ?>
                    <div class="filter-select-box<?php echo empty($fitment_dict[$house_fitment]) ? '' : ' selected';?>" data-url="<?php echo $url;?>">
                        <div class="caption"><?php echo empty($fitment_dict[$house_fitment]) ? '装修不限' : $fitment_dict[$house_fitment];?></div>
                        <div class="option">
                            <div class="item" data-value="0">装修不限</div>
                            <?php
                            foreach ($fitment_dict as $key => $item) {
                                echo '<div class="item" data-value="' . $key . '">'. $item . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </li>
        </ul>
    </div>
</div>
<div id="main" class="list-box <?php
echo $house_list_type == 1 ? 'list-pic-text' : 'list-text';
if ($column['column_type'] == 2 || $column['column_type'] == 3){
    echo ' list-box-2';
}
?>">
    <div id="title01">
        <span <?php if (empty($mtype)){echo ' class="on"';}?>><a href="<?php echo 'list_0_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';?>">全部</a></span>
        <span><?php
            $history_title = $column['column_type'] != 4 ? '我浏览过的房源' : '我浏览过的信息';
            if (empty($member_id)) {
                echo '<a href="javascript:void(0);" onclick="ShowLoginDialog(\'/history/list_' . $_GET['column'] . '_1.html\');">' . $history_title . '</a>';
            } else {
                echo '<a href="/history/list_' . $_GET['column'] . '_1.html">' . $history_title . '</a>';
            }
            ?></span>
        <div class="order-list">
            <div class="filter-select-box" data-url="<?php
            echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $house_price_average . '_' . $room . '_' . $totalarea . '_' . $house_toward . '_' . $house_fitment . '_' . $house_floor . '_' . $house_belong . '_' . $house_age . '_{curentparam}_1_' . $keywords . '.html';
            $dict_key = $orderby . '_' . $orderway;
            ?>">
                <div class="caption"><?php echo empty($order_dict[$dict_key]) ? '默认排序' : $order_dict[$dict_key];?></div>
                <div class="option">
                    <?php
                    foreach ($order_dict as $key => $item) {
                        $class_name = $dict_key == $key ? 'on' : '';
                        echo '<div class="item ' . $class_name . '" data-value="' . $key . '">' . $item . '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="list-type-switch">
            <div class="switch-btn<?php echo $house_list_type == 1 ? ' text-btn' : '';?>" title="<?php echo $house_list_type == 1 ? '切换到文本列表' : '切换到图文列表';?>"></div>
        </div>
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
                    ?>
                    <li class="clear-fix">
                        <div class="list-house-thumb">
                            <a href="<?php echo $item['url'];?>" target="_blank"><img src="<?php echo !empty($item['house_thumb']) ? '/images/default_picture.jpg' : '/images/no_picture.jpg';?>" <?php echo !empty($item['house_thumb']) ? 'data-original="' . $item['house_thumb'] . '"' : '';?> /></a>
                            <div class="pic-count">图</div>
                        </div>
                        <div class="list-house-info clear-fix">
                            <div class="list-house-title">
                                <a href="<?php echo $item['url'];?>" target="_blank"><?php echo $item['title'];?></a>
                                <?php
                                if ($item['user_type'] == 2) {
                                    //echo '<span class="member-type">(个人)</span>';
                                } elseif ($item['user_type'] == 1 && $item['is_cooperation'] == 1) {
                                    //echo '<span class="member-type">(合作)</span>';
                                }
                                if ($item['house_thumb']) {
                                    echo '<span class="title-tag pic-tag">图</span>';
                                }
                                if ($item['is_promote']) {
                                    echo '<span class="promote-top-icon">顶</span>';
                                }
                                ?>
                            </div>
                            <div class="desc">
                                <?php
                                if ($item['column_type'] != 4) {
                                ?>
                                <p><?php
                                    $tmp =  '';
                                    if ($item['column_type'] == 1) {
                                        $tmp .= '住宅';
                                    } elseif ($item['column_type'] == 1) {
                                        $tmp .= '写字楼';
                                    } elseif ($item['column_type'] == 1) {
                                        $tmp .= '商铺';
                                    }

                                    if ($item['column_type'] == 1) {
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
                                    if ($house_toward[$item['house_toward']]) {
                                        if ($tmp) {
                                            $tmp .= '<span class="split">|</span>';
                                        }
                                        $tmp .= $house_toward[$item['house_toward']] . '向';
                                    }
                                    if ($item['house_top_floor'] > 0 && $item['house_floor'] > 0) {
                                        if ($tmp) {
                                            $tmp .= '<span class="split">|</span>';
                                        }
                                        //$tmp .= GetHouseFloor($item['house_floor'], $item['house_top_floor']) . '（共' . $item['house_top_floor'] . '层）';
                                        $house_floor = GetHouseFloor($item['house_floor'], $item['house_top_floor']);
                                        if ($item['house_top_floor'] <= 2) {
                                            $house_floor .= '共' . $item['house_top_floor'] . '层';
                                        } else {
                                            $house_floor .= '（共' . $item['house_top_floor'] . '层）';
                                        }
                                        $tmp .= $house_floor;
                                    }
                                    if ($house_fitment[$item['house_fitment']]) {
                                        if ($tmp) {
                                            $tmp .= '<span class="split">|</span>';
                                        }
                                        $tmp .= $house_fitment[$item['house_fitment']];
                                    }
                                    echo $tmp;
                                    ?></p>
                                <?php
                                }
                                ?>
                                <p><?php
                                    $tmp = '';
                                    if ($item['cityarea_id']) {
                                        if ($tmp) {
                                            $tmp .= '<span class="split">-</span>';
                                        }
                                        $tmp .= $cityarea_option[$item['cityarea_id']]['region_name'];
                                    }
                                    if ($item['cityarea2_id']) {
                                        if ($tmp) {
                                            $tmp .= '<span class="split">-</span>';
                                        }
                                        $tmp .= $cityarea2_list[$item['cityarea2_id']]['region_name'];
                                    }
                                    if ($item['column_type'] != 4 && $item['borough_name']) {
                                        if ($tmp) {
                                            $tmp .= '<span class="split">-</span>';
                                        }
                                        $tmp .= $item['borough_name'];
                                    }
                                    if ($item['column_type'] == 4 && $item['house_totalarea']) {
                                        if ($tmp) {
                                            $tmp .= '<span class="split">|</span>';
                                        }
                                        $tmp .= $item['house_totalarea'] . '㎡';
                                    }
                                    echo $tmp;
                                    ?></p>
                                <?php
                                if ($item['company'] || $item['owner_name']) {
                                    $tmp = '';
                                    if ($item['owner_name']) {
                                        $tmp .= $item['owner_name'];
                                        if ($item['user_type'] == 1) {
                                            $tmp .= '（经纪人）';
                                        } elseif ($item['user_type'] == 2) {
                                            $tmp .= '（个人）';
                                        } elseif ($item['user_type'] == 3) {
                                            if ($item['user_type_sub'] == 1) {
                                                $tmp .= '（物业公司）';
                                            } elseif ($item['user_type_sub'] == 2) {
                                                $tmp .= '（开发商）';
                                            } elseif ($item['user_type_sub'] == 3) {
                                                $tmp .= '（拍卖机构）';
                                            } elseif ($item['user_type_sub'] == 4) {
                                                $tmp .= '（其它公司）';
                                            } else {
                                                $tmp .= '（非中介机构）';
                                            }
                                        } elseif ($item['user_type'] == 4) {
                                            $tmp .= '（品牌公寓）';
                                        }
                                    }
                                    if ($tmp) {
                                        $tmp .= '<span class="split"></span>';
                                    }
                                    if ($item['user_type'] == 1 && $item['company']) {
                                        $tmp .= $item['company'];
                                    }
                                    echo '<p>联系人： ' . $tmp . '</p>';
                                }
                                ?>
                                <?php
                                if ($item['column_type'] == 1) {
                                ?>
                                <p class="bright-spot"><?php
                                    $bright_spot =  GetDictList($item['bright_spot'], $bright_spot_dict, 5);
                                    foreach ($bright_spot as $tag) {
                                        echo '<span class="item">' . $tag . '</span>';
                                    }
                                    if ($item['parking_lot'] == 1 && count($bright_spot) < 5) {
                                        echo '<span class="item">有车位</span>';
                                    }
                                    ?></p>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div class="list-house-area"><?php
                            if ($item['house_totalarea']) {
                                echo $item['house_totalarea'] . ' ㎡';
                            }
                            ?></div>
                        <?php
                        if ($column['column_type'] == 1){
                            ?>
                            <div class="list-house-room"><?php
                                if ($item['house_room']) {
                                    echo $item['house_room'] . ' 室';
                                }
                                ?></div>
                            <?php
                        }
                        ?>
                        <div class="list-house-price"><?php
                            if ($item['house_price'] > 0){
                                echo '<strong>'.$item['house_price'].'</strong> 万元';
                            }else{
                                echo '<strong>面议</strong>';
                            }
                            ?></div>
                        <?php
                        if ($item['house_price_average'] > 0) {
                        ?>
                        <div class="list-house-price-average"><?php echo $item['house_price_average'] . '元/㎡';?></div>
                        <?php
                        }
                        ?>
                        <div class="list-time <?php echo $item['house_price_average'] > 0 ? 'list-time-2' : '';?>"><?php echo $item['updated'];?></div>
                    </li>
                    <?php
                    if ($website_list_ad_1 && $key == 4) {
                        echo '<div class="data-list-ad">' . $website_list_ad_1 . '</div>';
                    }
                    if ($website_list_ad_2 && $key == 34) {
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
                $tips = '很抱歉，没有找到与<strong>“' . $keywords . '”</strong>相符的' . $house_type_title . '，请您换个关键词试试吧~';
            } else {
                $tips = '很抱歉，当前类目还没有相关' . $house_type_title . '~';
            }*/
            $tips = '暂无匹配房源，换个搜索条件试试';
            echo '<div class="data-list-tips">' . $tips . '</div>';
        }
        if ($recommend_house) {
            ?>
            <h2 class="section-caption"><?php echo $column['column_type'] != 4 ? '推荐房源' : '推荐信息';?></h2>
            <div id="list" style="margin-top: 0;">
                <ul>
                    <?php
                    foreach($recommend_house as $key => $item){
                        ?>
                        <li class="clear-fix">
                            <div class="list-house-thumb">
                                <a href="<?php echo $item['url'];?>" target="_blank"><img src="<?php echo !empty($item['house_thumb']) ? '/images/default_picture.jpg' : '/images/no_picture.jpg';?>" <?php echo !empty($item['house_thumb']) ? 'data-original="' . $item['house_thumb'] . '"' : '';?> /></a>
                                <div class="pic-count">图</div>
                            </div>
                            <div class="list-house-info clear-fix">
                                <div class="list-house-title">
                                    <a href="<?php echo $item['url'];?>" target="_blank"><?php echo $item['title'];?></a>
                                    <?php
                                    if ($item['user_type'] == 2) {
                                        //echo '<span class="member-type">(个人)</span>';
                                    } elseif ($item['user_type'] == 1 && $item['is_cooperation'] == 1) {
                                        //echo '<span class="member-type">(合作)</span>';
                                    }
                                    if ($item['house_thumb']) {
                                        echo '<span class="title-tag pic-tag">图</span>';
                                    }
                                    if ($item['is_promote']) {
                                        echo '<span class="promote-top-icon">顶</span>';
                                    }
                                    ?>
                                </div>
                                <div class="desc">
                                    <?php
                                    if ($item['column_type'] != 4) {
                                        ?>
                                        <p><?php
                                            $tmp =  '';
                                            if ($item['column_type'] == 1) {
                                                $tmp .= '住宅';
                                            } elseif ($item['column_type'] == 1) {
                                                $tmp .= '写字楼';
                                            } elseif ($item['column_type'] == 1) {
                                                $tmp .= '商铺';
                                            }

                                            if ($item['column_type'] == 1) {
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
                                            if ($house_toward[$item['house_toward']]) {
                                                if ($tmp) {
                                                    $tmp .= '<span class="split">|</span>';
                                                }
                                                $tmp .= $house_toward[$item['house_toward']] . '向';
                                            }
                                            if ($item['house_top_floor'] > 0 && $item['house_floor'] > 0) {
                                                if ($tmp) {
                                                    $tmp .= '<span class="split">|</span>';
                                                }
                                                //$tmp .= GetHouseFloor($item['house_floor'], $item['house_top_floor']) . '（共' . $item['house_top_floor'] . '层）';
                                                $house_floor = GetHouseFloor($item['house_floor'], $item['house_top_floor']);
                                                if ($item['house_top_floor'] <= 2) {
                                                    $house_floor .= '共' . $item['house_top_floor'] . '层';
                                                } else {
                                                    $house_floor .= '（共' . $item['house_top_floor'] . '层）';
                                                }
                                                $tmp .= $house_floor;
                                            }
                                            if ($house_fitment[$item['house_fitment']]) {
                                                if ($tmp) {
                                                    $tmp .= '<span class="split">|</span>';
                                                }
                                                $tmp .= $house_fitment[$item['house_fitment']];
                                            }
                                            echo $tmp;
                                            ?></p>
                                        <?php
                                    }
                                    ?>
                                    <p><?php
                                        $tmp = '';
                                        if ($item['cityarea_id']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">-</span>';
                                            }
                                            $tmp .= $cityarea_option[$item['cityarea_id']]['region_name'];
                                        }
                                        if ($item['cityarea2_id']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">-</span>';
                                            }
                                            $tmp .= $cityarea2_list[$item['cityarea2_id']]['region_name'];
                                        }
                                        if ($item['column_type'] != 4 && $item['borough_name']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">-</span>';
                                            }
                                            $tmp .= $item['borough_name'];
                                        }
                                        if ($item['column_type'] == 4 && $item['house_totalarea']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">|</span>';
                                            }
                                            $tmp .= $item['house_totalarea'] . '㎡';
                                        }
                                        echo $tmp;
                                        ?></p>
                                    <?php
                                    if ($item['company'] || $item['owner_name']) {
                                        $tmp = '';
                                        if ($item['owner_name']) {
                                            $tmp .= $item['owner_name'];
                                            if ($item['user_type'] == 1) {
                                                $tmp .= '（经纪人）';
                                            } elseif ($item['user_type'] == 2) {
                                                $tmp .= '（个人）';
                                            } elseif ($item['user_type'] == 3) {
                                                if ($item['user_type_sub'] == 1) {
                                                    $tmp .= '（物业公司）';
                                                } elseif ($item['user_type_sub'] == 2) {
                                                    $tmp .= '（开发商）';
                                                } elseif ($item['user_type_sub'] == 3) {
                                                    $tmp .= '（拍卖机构）';
                                                } elseif ($item['user_type_sub'] == 4) {
                                                    $tmp .= '（其它公司）';
                                                } else {
                                                    $tmp .= '（非中介机构）';
                                                }
                                            } elseif ($item['user_type'] == 4) {
                                                $tmp .= '（品牌公寓）';
                                            }
                                        }
                                        if ($tmp) {
                                            $tmp .= '<span class="split"></span>';
                                        }
                                        if ($item['user_type'] == 1 && $item['company']) {
                                            $tmp .= $item['company'];
                                        }
                                        echo '<p>联系人： ' . $tmp . '</p>';
                                    }
                                    ?>
                                    <?php
                                    if ($item['column_type'] == 1) {
                                        ?>
                                        <p class="bright-spot"><?php
                                            $bright_spot =  GetDictList($item['bright_spot'], $bright_spot_dict, 5);
                                            foreach ($bright_spot as $tag) {
                                                echo '<span class="item">' . $tag . '</span>';
                                            }
                                            if ($item['parking_lot'] == 1 && count($bright_spot) < 5) {
                                                echo '<span class="item">有车位</span>';
                                            }
                                            ?></p>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="list-house-area"><?php
                                if ($item['house_totalarea']) {
                                    echo $item['house_totalarea'] . ' ㎡';
                                }
                                ?></div>
                            <?php
                            if ($column['column_type'] == 1){
                                ?>
                                <div class="list-house-room"><?php
                                    if ($item['house_room']) {
                                        echo $item['house_room'] . ' 室';
                                    }
                                    ?></div>
                                <?php
                            }
                            ?>
                            <div class="list-house-price"><?php
                                if ($item['house_price'] > 0){
                                    echo '<strong>'.$item['house_price'].'</strong> 万元';
                                }else{
                                    echo '<strong>面议</strong>';
                                }
                                ?></div>
                            <?php
                            if ($item['house_price_average'] > 0) {
                                ?>
                                <div class="list-house-price-average"><?php echo $item['house_price_average'] . '元/㎡';?></div>
                                <?php
                            }
                            ?>
                            <div class="list-time <?php echo $item['house_price_average'] > 0 ? 'list-time-2' : '';?>"><?php echo $item['updated'];?></div>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>
    </div>
    <div id="right">
        <div id="banner_right"><?php echo $website_right_ad_2;?></div>
        <div class="columns">
            <h2><?php echo $column['column_type'] != 4 ? '我浏览过的房源' : '我浏览过的信息';?><?php
                if (empty($member_id)) {
                    echo '<span class="more" onclick="ShowLoginDialog(\'/history/list_' . $_GET['column'] . '_1.html\');">更多&gt;&gt;</span>';
                } else {
                    echo '<a href="/history/list_' . $_GET['column'] . '_1.html" class="more">更多&gt;&gt;</a>';
                }
                ?></h2>
            <div class="column-house-list">
                <ul>
                    <?php
                    foreach ($history_house_list as $item) {
                        $house_price = FormatHousePrice($item['house_price']);
                        ?>
                        <li class="clear-fix">
                            <div class="list-house-thumb">
                                <a href="<?php echo $item['url'];?>" target="_blank"><img src="<?php echo !empty($item['house_thumb']) ? $item['house_thumb'] : '/images/no_picture.jpg';?>" /></a>
                            </div>
                            <div class="list-house-info clear-fix">
                                <div class="list-house-title">
                                    <a href="<?php echo $item['url'];?>" target="_blank"><?php echo $item['title'];?></a>
                                </div>
                                <div class="desc"><?php
                                    $tmp =  '';
                                    if ($item['column_type'] == 1) {
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
                                    if ($item['column_type'] == 4) {
                                        if ($item['cityarea_id']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">-</span>';
                                            }
                                            $tmp .= $cityarea_option[$item['cityarea_id']]['region_name'];
                                        }
                                        if ($item['cityarea2_id']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">-</span>';
                                            }
                                            $tmp .= $cityarea2_list[$item['cityarea2_id']]['region_name'];
                                        }
                                    }
                                    if ($item['house_totalarea']) {
                                        if ($tmp) {
                                            $tmp .= '<span class="split">|</span>';
                                        }
                                        $tmp .= $item['house_totalarea'] . '㎡';
                                    }
                                    echo $tmp;
                                    ?></div>
                                <div class="bottom-box clear-fix">
                                    <div class="price"><?php
                                        if ($item['house_price'] > 0){
                                            echo '<strong>'.$item['house_price'].'</strong>万元';
                                        }else{
                                            echo '<strong>面议</strong>';
                                        }
                                        ?></div>
                                    <div class="update-time"><?php echo $item['updated'];?></div>
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
<?php
if ($recommend_house && $show_recommend_house) {
?>
<div id="recommend-house" class="section-box picture-list-box<?php
if ($column['column_type'] == 2 || $column['column_type'] == 3){
    echo ' list-box-2';
}
?>">
    <h2><?php echo $column['column_type'] != 4 ? '推荐房源' : '推荐信息';?></h2>
    <div class="list clearfix">
        <?php
        foreach($recommend_house as $item){
            ?>
            <div class="item">
                <div class="pic"><a href="<?php echo $item['url'];?>" target="_blank"><img src="<?php echo !empty($item['house_thumb']) ? '/images/default_picture.jpg' : '/images/no_picture.jpg';?>" <?php echo !empty($item['house_thumb']) ? 'data-original="' . $item['house_thumb'] . '"' : '';?> alt="<?php echo $item['title'];?>"></a></div>
                <div class="clear-fix price-house-type-box">
                    <div class="price"><?php
                        if ($item['house_price'] > 0){
                            echo '<span class="num">' . $item['house_price'] . '</span> 万元';
                        }else{
                            echo '面议';
                        }
                        ?></div>
                    <?php
                    if ($item['house_price_average'] > 0) {
                        ?>
                        <div class="price-average">(<?php echo $item['house_price_average'] . '元/㎡';?>)</div>
                        <?php
                    }
                    ?>
                </div>
                <div class="info"><?php
                    $tmp = array();
                    if ($item['column_type'] == 1) {
                        $house_room = array();
                        if ($item['house_room']) {
                            $house_room[] = $item['house_room'] . '室';
                        }
                        if ($item['house_hall']) {
                            $house_room[] = $item['house_hall'] . '厅';
                        }
                        if ($item['house_toilet']) {
                            $house_room[] = $item['house_toilet'] . '卫';
                        }
                        if ($house_room) {
                            $tmp[] = implode(' ', $house_room);
                        }
                    }
                    if ($item['house_totalarea']) {
                        $tmp[] = $item['house_totalarea'] . '㎡';
                    }
                    if ($item['house_floor']) {
                        $tmp[] = $item['house_floor'];
                    }
                    echo implode('<span class="text-split"></span>', $tmp);
                    ?></div>
                <div class="info"><?php
                    $tmp = array();
                    $tmp_region = array();
                    if ($item['cityarea_id']) {
                        $tmp_region[] = $cityarea_option[$item['cityarea_id']]['region_name'];
                    }
                    if ($item['cityarea2_id']) {
                        $tmp_region[] =  $cityarea2_list[$item['cityarea2_id']]['region_name'];
                    }
                    if ($tmp_region) {
                        $tmp[] = implode('-', $tmp_region);
                    }
                    if ($item['borough_name']) {
                        $tmp[] = $item['borough_name'];
                    }
                    echo implode('<span class="text-split"></span>', $tmp);
                    ?></div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<?php
}
?>
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
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