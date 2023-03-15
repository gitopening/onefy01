<?php
require_once(dirname(__FILE__) . '/path.inc.php');
require_once($cfg['path']['lib'] . 'classes/Pages.class.php');
$now_time = time();

if ($column['column_type'] == 2 && in_array($cityInfo['url_name'], array('bj', 'sh', 'gz', 'sz', 'cd', 'nj', 'hz', 'qingdao', 'wh', 'hefei', 'taiyuan', 'zhengzhou', 'cq', 'changsha', 'xian', 'tj'))) {
    $show_yuanxingdichan = true;
} else {
    $show_yuanxingdichan = false;
}

$DdObj = new Dd($query);
//房屋亮点
$bright_spot_dict = $DdObj->getItemArray(25, true, MEMCACHE_MAX_EXPIRETIME);
//装修情况
$fitment_dict = $DdObj->getItemArray(7, true, MEMCACHE_MAX_EXPIRETIME);
//朝向
$toward_dict = $DdObj->getItemArray(6, true, MEMCACHE_MAX_EXPIRETIME);

$order_money_caption = $column['column_type'] == 1 ? '租金' : '月租金';
$order_dict = array(
    '2_0' => '默认排序',
    '3_1' => $order_money_caption . '由低到高',
    '3_0' => $order_money_caption . '由高到低',
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

$BrandApartment = new BrandApartment($query);
//Sphinx搜索
$Sphinx = Sphinx::getInstance();
$Sphinx->SetFilter('column_type', array($column['column_type']));
if (in_array($column['column_type'], array(4, 16, 17))) {
    if (strpos($_GET['column'], 'zr') !== false) {
        $Sphinx->SetFilter('is_sublet', array(1));
    } else {
        $Sphinx->SetFilter('is_sublet', array(0));
    }
}
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$Sphinx->SetFilterRange('house_thumb_length', 10, 300);
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, '@random');
$list_number = 4;
$memcache_key_name = MEMCACHE_PREFIX . 'rec_rent_list_' . $cityInfo['id'] . '_' . $column['column_type']. '_' . $list_number;
$recommend_house = $Cache->get($memcache_key_name);
if (empty($recommend_house)) {
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result_promote = $Sphinx->Query('', SPHINX_MEMBER_RENT_HOME_PROMOTE_INDEX);
    $list_count = intval($result_promote['total']);
    if ($list_count < 4) {
        $house_ids = array();
        foreach ($result_promote['matches'] as $item) {
            $house_ids[] = $item['id'];
        }
        $Sphinx->SetFilter('filter_id', $house_ids, true);

        $list_number = 4 - $list_count;
        $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');
        $Sphinx->SetLimits(0, $list_number, $list_number);
        $result = $Sphinx->Query('', SPHINX_MEMBER_RENT_INDEX);
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
        $data['video'] = $item['attrs']['video'];
        $data['is_promote'] = $item['attrs']['is_promote'];
        /*if ($item['attrs']['mtype'] == 2 || $data['is_promote'] || $data['house_thumb']) {
            $data['title'] = cn_substr_utf8(str_replace(' ', '', $data['title']), 54);
            if ($item['attrs']['mtype'] == 2) {
                $data['title'] .= '(个人)';
            }
        }*/
        $data['id'] = $item['id'];
        $data['mid'] = $item['attrs']['mid'];
        $data['user_type'] = $item['attrs']['mtype'];
        $data['column_type'] = $item['attrs']['column_type'];
        $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
        $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
        $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
        $data['house_veranda'] = FormatHouseRoomType($item['attrs']['house_veranda'], 9);
        $data['house_floor'] = intval($item['attrs']['house_floor']);
        $data['house_top_floor'] = intval($item['attrs']['house_topfloor']);
        $data['house_toward'] = intval($item['attrs']['house_toward']);
        $data['house_fitment'] = intval($item['attrs']['house_fitment']);
        $data['brand_apartment_id'] = intval($item['attrs']['brand_apartment_id']);
        $data['brand_apartment_checked'] = intval($item['attrs']['brand_apartment_checked']);
        $data['rent_type'] = intval($item['attrs']['rent_type']);
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['unit_type'] = $item['attrs']['unit_type'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['house_month_price'] = FormatHousePrice($item['attrs']['house_month_price']);
        //房源链接地址
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $data['url'] = 'house_' . $item['id'] . $url_fix . '.html';
        if ($_GET['column'] == 'rent_old') {
            $data['url'] = '/rent/' . $data['url'];
        }
        $data['owner_name'] = $item['attrs']['owner_name'];
        if ($data['mid'] && in_array($data['user_type'], array(1, 3, 4))) {
            if ($item['attrs']['member_db_index'] == MEMBER_DB_INDEX) {
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
        }

        //取得品牌公寓
        if ($data['column_type'] == 1 && $data['brand_apartment_id'] > 0 && $data['brand_apartment_checked'] > -1) {
            $condition = 'id = ' . $data['brand_apartment_id'] . ' AND is_checked IN (0,1)';
            $cache_key = 'brand_apartment_id_' . $data['brand_apartment_id'];
            $brand_apartment_data = $BrandApartment->table($BrandApartment->table_name)->where($condition)->cache($cache_key, MEMCACHE_MAX_EXPIRETIME)->one();
            $data['brand_apartment_name'] = $brand_apartment_data['brand_name'];
        }

        $data['is_cooperation'] = intval($item['attrs']['is_cooperation']);
        if ($data['column_type'] == 1) {
            $data['is_owner_direct_rent'] = $item['attrs']['is_owner_direct_rent'];
            $data['bright_spot'] = $item['attrs']['bright_spot'];
            $data['parking_lot'] = intval($item['attrs']['parking_lot']);
        }
        if ($data['column_type'] == 4 && $item['attrs']['is_sublet'] == 1 && $item['attrs']['transfer_fee'] > 0) {
            $data['transfer_fee'] = floatval($item['attrs']['transfer_fee']);
            $data['is_sublet'] = intval($item['attrs']['is_sublet']);
        }
        $recommend_house[] = $data;
    }
    //存储数据到Memcache
    if ($recommend_house) {
        $Cache->set($memcache_key_name, $recommend_house, 60);
    }
}

$Sphinx->ResetFilters();

//标题
$page->title = '';
//链接参数处理list_mtype_area_area2_(sprice-eprice)_room_(sta-eta)_renttype_toward_fitment_pic_elevator_parking_orderfield_order_page_关键字.html
$param = explode('_', trim($_GET['param']));
$mtype = intval($param[0]);
$cityarea = intval($param[1]);
$cityarea2 = intval($param[2]);
$price = trim($param[3]) == '' ? '0-0' : trim($param[3]);
$room = intval($param[4]);
$totalarea = trim($param[5]) == '' ? '0-0' : trim($param[5]);
$rent_type = intval($param[6]);
$house_toward = intval($param[7]);
$house_fitment = intval($param[8]);
$has_pic = intval($param[9]);
$elevator = intval($param[10]);
$parking_lot = intval($param[11]);
$orderby = empty($param[12]) ? 2 : intval($param[12]);
$orderway = intval($param[13]);
$pageno = empty($param[14]) ? 1 : intval($param[14]);
$keywords = trim($param[15]);

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

//$keywords = trim(mb_convert_encoding($keywords, 'gbk', 'utf-8')); //编码转换

//按更新时间和发布时间排序时，默认为倒序
if (empty($orderby) || $orderby == 1 || $orderby == 2) {
    $orderway = 0;
}

$house_price_option = get_filter_array($column['price_option']);
$house_totalarea_option = get_filter_array($column['totalarea_option']);

//$Sphinx->SetFilterRange('updated', $expireTime + 1, 10000000000);
$Sphinx->SetFilter('column_type', array($column['column_type']));
if (in_array($column['column_type'], array(4, 16, 17))) {
    if (strpos($_GET['column'], 'zr') !== false) {
        $Sphinx->SetFilter('is_sublet', array(1));
    } else {
        $Sphinx->SetFilter('is_sublet', array(0));
    }
}

/*if (in_array($column['column_type'], array(2, 3))) {
    $Sphinx->SetFilter('unit_type', array(2));
}*/
if ($_GET['column'] == 'rent_old') {
    $Sphinx->SetFilterRange('updated', 0, strtotime('-11 month'));
}
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));

if ($mtype == 4) {
    $Sphinx->SetFilter('mtype', array(1, 3, 4));
    $Sphinx->SetFilter('is_cooperation', array(1));
} elseif ($mtype == 5) {
    //$Sphinx->SetFilter('mtype', array(1, 3, 4));
    $Sphinx->SetFilter('brand_apartment_checked', array(0, 1));
    $Sphinx->SetFilterRange('brand_apartment_id', 1, 10000000000);
} elseif ($mtype > 100000000) {
    $Sphinx->SetFilter('source_id', array($mtype));
} elseif ($mtype > 0) {
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

if ($orderway == 1 && $orderby == 3 && $start == 0 && empty($keywords)) {
    if ($column['column_type'] == 1) {
        $start = 100;
    } else {
        $start = 0.001;
    }
}

if ($start && $end) {
    $Sphinx->SetFilterFloatRange('house_price', $start, $end);
} elseif (empty($start) && !empty($end)) {
    $Sphinx->SetFilterFloatRange('house_price', 0, $end);
} elseif (!empty($start) && empty($end)) {
    if (in_array($column['column_type'], array(2, 3))) {
        $Sphinx->SetFilterFloatRange('house_price', $start, 99);
    } else {
        $Sphinx->SetFilterFloatRange('house_price', $start, 10000000000);
    }
}

if ($start && $end) {
    $page->title .= $start . '~' . $end . '元';
} elseif ($start && empty($end)) {
    $page->title .= $start . '元以上';
} elseif (empty($start) && $end) {
    $page->title .= $end . '元以下';
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

if ($start && $end) {
    $page->title .= $start . '~' . $end . '㎡';
} elseif ($start && empty($end)) {
    $page->title .= $start . '㎡以上';
} elseif (empty($start) && $end) {
    $page->title .= $end . '㎡以下';
}

if ($rent_type) {
    $Sphinx->SetFilter('rent_type', array($rent_type));
}
if ($house_toward) {
    $Sphinx->SetFilter('house_toward', array($house_toward));
}
if ($house_fitment) {
    $Sphinx->SetFilter('house_fitment', array($house_fitment));
}
if ($has_pic == 1) {
    $Sphinx->SetFilterRange('house_thumb_length', 10, 300);
}
if ($elevator) {
    $Sphinx->SetFilter('elevator', array(1));
}
if ($parking_lot) {
    $Sphinx->SetFilter('parking_lot', array(1));
}

if ($orderby == 1 || empty($orderby)) {
    $orderbyfield = 'created';
} elseif ($orderby == 2) {
    $orderbyfield = 'updated';
} elseif ($orderby == 3) {
    $orderbyfield = 'house_month_price';
} elseif ($orderby == 4) {
    $orderbyfield = 'house_totalarea';
}
/*if ($orderway == 1){
    $sort_order = 'asc';
}else{
    $sort_order = 'desc';
}*/

if ($orderway == 1) {
    //$Sphinx->SetFilterRange('updated', strtotime('-3 month'), $now_time);
    //$Sphinx->SetSortMode(SPH_SORT_ATTR_ASC, $orderbyfield);
    $Sphinx->SetSortMode(SPH_SORT_EXTENDED, 'is_promote DESC,' . $orderbyfield . ' ASC,@relevance DESC,@id DESC');
} else {
    //$Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, $orderbyfield);
    $Sphinx->SetSortMode(SPH_SORT_EXTENDED, 'is_promote DESC,' . $orderbyfield . ' DESC,@relevance DESC,@id DESC');
}

$list_num = $cfg['list_number'];
//缓存Key
if (strpos($_GET['column'], 'zr') !== false) {
    $list_key_prefix = MEMCACHE_PREFIX . 'zrl_';
    $count_key_prefix = MEMCACHE_PREFIX . 'zrc_';
} else {
    $list_key_prefix = MEMCACHE_PREFIX . 'rl_';
    $count_key_prefix = MEMCACHE_PREFIX . 'rc_';
}
$list_key = $list_key_prefix . $cityInfo['id'] . '_' . $column['column_type'] . '_' . $_GET['column'] . '_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_' . $list_num . '_' . $pageno . '_' . $keywords;
$count_key = $count_key_prefix . $cityInfo['id'] . '_' . $column['column_type'] . '_' . $_GET['column'] . '_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_' . $list_num . '_' . $keywords;

//从Memcache中取数据
$dataCount = $Cache->get($count_key);
$dataList = $Cache->get($list_key);
if (empty($dataList) || empty($dataCount)) {
    $max_data_limit = MAX_DATA_LIMIT;
    //$max_data_limit = 600;
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
    if ($_GET['column'] == 'rent_old') {
        /*if ($mtype == 5) {
            $result = $Sphinx->Query($parsed_keywords, SPHINX_SEARCH_APARTMENT_RENT_OLD_INDEX);
        } else {
            $result = $Sphinx->Query($parsed_keywords, SPHINX_SEARCH_RENT_OLD_INDEX);
        }*/
        $result = $Sphinx->Query($parsed_keywords, SPHINX_SEARCH_RENT_OLD_INDEX);
    } else {
        /*if ($mtype == 5) {
            $result = $Sphinx->Query($parsed_keywords, SPHINX_SEARCH_APARTMENT_RENT_INDEX);
        } else {
            $result = $Sphinx->Query($parsed_keywords, SPHINX_SEARCH_RENT_INDEX);
        }*/
        $result = $Sphinx->Query($parsed_keywords, SPHINX_SEARCH_RENT_INDEX);
    }

    $result['total'] = intval($result['total']);
    $pages = new Pages($result['total'], $list_num);
    $dataList = array();
    if ($result['total'] > 0) {
        foreach ($result['matches'] as $item) {
            $data = array();
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column['house_title']);
            $data['title'] = cn_substr_utf8($data['title'], 48);
            $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
            $data['video'] = $item['attrs']['video'];
            $data['is_promote'] = $item['attrs']['is_promote'];
            /*if ($item['attrs']['mtype'] == 2 || $data['is_promote'] || $data['house_thumb']) {
                $data['title'] = cn_substr_utf8(str_replace(' ', '', $data['title']), 54);
                if ($item['attrs']['mtype'] == 2) {
                    $data['title'] .= '(个人)';
                }
            }*/
            $data['id'] = $item['id'];
            $data['mid'] = $item['attrs']['mid'];
            $data['user_type'] = $item['attrs']['mtype'];
            $data['column_type'] = $item['attrs']['column_type'];
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['house_veranda'] = FormatHouseRoomType($item['attrs']['house_veranda'], 9);
            $data['house_floor'] = intval($item['attrs']['house_floor']);
            $data['house_top_floor'] = intval($item['attrs']['house_topfloor']);
            $data['house_toward'] = intval($item['attrs']['house_toward']);
            $data['house_fitment'] = intval($item['attrs']['house_fitment']);
            $data['brand_apartment_id'] = intval($item['attrs']['brand_apartment_id']);
            $data['brand_apartment_checked'] = intval($item['attrs']['brand_apartment_checked']);
            $data['rent_type'] = intval($item['attrs']['rent_type']);
            $data['cityarea_id'] = $item['attrs']['cityarea_id'];
            $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['unit_type'] = $item['attrs']['unit_type'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['house_month_price'] = FormatHousePrice($item['attrs']['house_month_price']);
            //房源链接地址
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] = 'house_' . $item['id'] . $url_fix . '.html';
            if ($_GET['column'] == 'rent_old') {
                $data['url'] = '/rent/' . $data['url'];
            }
            $data['owner_name'] = $item['attrs']['owner_name'];
            if ($data['mid'] && in_array($data['user_type'], array(1, 3, 4))) {
                if ($item['attrs']['member_db_index'] == MEMBER_DB_INDEX) {
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
            }

            //取得品牌公寓
            if ($data['column_type'] == 1 && $data['brand_apartment_id'] > 0 && $data['brand_apartment_checked'] > -1) {
                $condition = 'id = ' . $data['brand_apartment_id'] . ' AND is_checked IN (0,1)';
                $cache_key = 'brand_apartment_id_' . $data['brand_apartment_id'];
                $brand_apartment_data = $BrandApartment->table($BrandApartment->table_name)->where($condition)->cache($cache_key, MEMCACHE_MAX_EXPIRETIME)->one();
                $data['brand_apartment_name'] = $brand_apartment_data['brand_name'];
            }

            $data['is_cooperation'] = intval($item['attrs']['is_cooperation']);
            if ($data['column_type'] == 1) {
                $data['is_owner_direct_rent'] = $item['attrs']['is_owner_direct_rent'];
                $data['bright_spot'] = $item['attrs']['bright_spot'];
                $data['parking_lot'] = intval($item['attrs']['parking_lot']);
            }
            if ($data['column_type'] == 4 && $item['attrs']['is_sublet'] == 1 && $item['attrs']['transfer_fee'] > 0) {
                $data['transfer_fee'] = floatval($item['attrs']['transfer_fee']);
                $data['is_sublet'] = intval($item['attrs']['is_sublet']);
            }
            if ($item['attrs']['member_db_index'] == 0 && $item['attrs']['mtype'] == 1) {
                $data['owner_name'] = '';
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
$url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_{pageno}_' . $keywords . '.html';
$pagePanel = $pages->get_pager_nav('4', $pageno, $url);

if ($mtype == 1){
	$mtypetitle = ',房产中介';
}elseif ($mtype == 2){
	$mtypetitle = ',个人房源';
}elseif ($mtype == 3){
	$mtypetitle = ',非中介机构房源';
}
if ($cityarea){
	$cityareaTitle = $cityarea_option[$cityarea]['region_name'].$cityarea2_option[$cityarea2]['region_name'];
}else{
	$cityareaTitle = $cityInfo['city_name'];
}
switch ($_GET['column']){
	case 'rent':
        if ($mtype == 5) {
            $page->title = '【品牌公寓出租—' . $cityInfo['city_name'] . '租房-' . $cityInfo['city_name'] . '房屋出租】-' . $page->titlec . $cityInfo['city_name'];
            $page->description = $cityareaTitle . '租房,' . $cityInfo['city_name'] . '房屋出租,品牌公寓,自如公寓';
            $page->keywords = '北京品牌公寓出租,' . $cityareaTitle . '租房信息,品牌公寓出租信息-自如公寓、蛋壳公寓、蘑菇公寓、' . $cityInfo['city_name'] . '房屋出租信息，' . $cityInfo['city_name'] . '租房网';
        } else {
            $page->title = '【'.$cityareaTitle.'租房—'.$cityareaTitle.'房屋出租信息】-'.$page->titlec.$cityInfo['city_name'];
            $page->description = $cityInfo['city_name'].$page->titlec.'提供'.$cityInfo['city_name'].'租房,'.$cityInfo['city_name'].'房屋出租信息,'.$cityInfo['city_name'].'个人房源信息,'.$cityInfo['city_name'].'个人租房信息';
            $page->keywords = $cityareaTitle.'租房, '.$cityareaTitle.'房屋出租信息, '.$cityareaTitle.'个人房屋出租,个人租房';
        }
		break;
	case 'xzlcz':
		$page->title = '【'.$cityInfo['city_name'].'写字楼出租|'.$cityInfo['city_name'].'写字楼网】'.$page->titlec.$cityInfo['city_name'];
		$page->description = $cityInfo['city_name'].$page->titlec.'提供大量'.$cityInfo['city_name'].'写字楼出租信息，写字楼租赁,写字楼出租,写字楼投资,写字楼租赁合同，写字楼出租网,中国写字楼网';
		$page->keywords = '写字楼、写字楼出租、'.$cityInfo['city_name'].'写字楼、写字楼租赁、写字楼出租网';
		break;
	case 'spcz':
		$page->title = '【'.$cityInfo['city_name'].'商铺出租、'.$cityInfo['city_name'].'门面出租|'.$cityInfo['city_name'].'商铺网】'.$page->titlec.$cityInfo['city_name'];
		$page->description = $cityInfo['city_name'].'商铺网提供大量'.$cityInfo['city_name'].'商铺出租、'.$cityInfo['city_name'].'门面出租信息，为您提供商铺租赁、商铺转让、商铺投资的一手房源信息，为您合理确定商铺投资回报率,顺利签订商铺租赁合同，'.$cityInfo['city_name'].'商铺出租网';
		$page->keywords = '商铺出租,门面出租,'.$cityInfo['city_name'].'商铺出租，'.$cityInfo['city_name'].'门面房出租,'.$cityInfo['city_name'].'商铺出租网';
		break;
    case 'cwcz':
        $page->title = '【'.$cityareaTitle.'车位出租—'.$cityareaTitle.'车位出租信息】-'.$page->titlec.$cityInfo['city_name'];
        $page->description = $cityInfo['city_name'].$page->titlec.'提供'.$cityInfo['city_name'].'车位出租,'.$cityInfo['city_name'].'车位出租信息,'.$cityInfo['city_name'].'个人车位信息,'.$cityInfo['city_name'].'个人车位出租信息';
        $page->keywords = $cityareaTitle.'车位出租, '.$cityareaTitle.'车位出租信息, '.$cityareaTitle.'个人车位出租,个人车位';
        break;
    case 'cwzr':
        $page->title = '【'.$cityareaTitle.'车位转让—'.$cityareaTitle.'车位转让信息】-'.$page->titlec.$cityInfo['city_name'];
        $page->description = $cityInfo['city_name'].$page->titlec.'提供'.$cityInfo['city_name'].'车位转让,'.$cityInfo['city_name'].'车位转让信息,'.$cityInfo['city_name'].'个人车位信息,'.$cityInfo['city_name'].'个人车位转让信息';
        $page->keywords = $cityareaTitle.'车位转让, '.$cityareaTitle.'车位转让信息, '.$cityareaTitle.'个人车位转让,个人车位';
        break;
    default:
        $page->title = '【'.$cityareaTitle . $column['column_title'] .'—'.$cityareaTitle. $column['column_title'] . '信息】-'.$page->titlec.$cityInfo['city_name'];
        $page->description = $cityInfo['city_name'].$page->titlec.'提供'.$cityInfo['city_name']. $column['column_title'] . ','.$cityInfo['city_name']. $column['column_title'] . '信息,'.$cityInfo['city_name'].'个人车位信息,'.$cityInfo['city_name'].'个人' . $column['column_title'] . '信息';
        $page->keywords = $cityareaTitle.$column['column_title'] . ', '.$cityareaTitle.$column['column_title'].'信息, '.$cityareaTitle.'个人' . $column['column_title'] . ',个人车位';
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
$history_list = get_history_list($_GET['column'] . 'house_rent');
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
    $memcache_house_key = 'house_rent_' . $house_id;
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
    $house_info = $house_query->field('house.*, extend.*')->table('houserent', 'house')->join($house_query->db_prefix . 'houserent_extend AS extend ON house.id = extend.house_id', 'LEFT')->where($condition)->cache($memcache_house_key, MEMCACHE_MAX_EXPIRETIME)->one();
    if (empty($house_info)) {
        continue;
    }
    $data = array();
    $data['title'] = get_house_title($house_info, $cityarea_option, $cityarea2_list, $column['house_title']);
    $data['house_thumb'] = GetPictureUrl($house_info['house_thumb'], 2, $item['db']);
    $data['is_promote'] = $house_info['is_promote'];
    $data['id'] = $item['id'];
    $data['column_type'] = $item['column_type'];
    $data['user_type'] = $house_info['mtype'];
    $data['mid'] = intval($house_info['mid']);
    $data['house_room'] = FormatHouseRoomType($house_info['house_room'], 9);
    $data['house_hall'] = FormatHouseRoomType($house_info['house_hall'], 9);
    $data['house_toilet'] = FormatHouseRoomType($house_info['house_toilet'], 9);
    $data['house_veranda'] = FormatHouseRoomType($house_info['house_veranda'], 9);
    $data['house_hall'] = intval($house_info['house_hall']);
    $data['house_toilet'] = intval($house_info['house_toilet']);
    $data['house_floor'] = intval($house_info['house_floor']);
    $data['house_top_floor'] = intval($house_info['house_topfloor']);
    $data['house_toward'] = intval($house_info['house_toward']);
    $data['house_fitment'] = intval($house_info['house_fitment']);
    $data['brand_apartment_id'] = intval($house_info['brand_apartment_id']);
    $data['brand_apartment_checked'] = intval($house_info['brand_apartment_checked']);
    $data['rent_type'] = intval($house_info['rent_type']);
    $data['cityarea_id'] = $house_info['cityarea_id'];
    $data['cityarea2_id'] = $house_info['cityarea2_id'];
    $data['borough_name'] = $house_info['borough_name'];
    $data['updated'] = time2Units($now_time - $house_info['updated']);
    $data['house_totalarea'] = $house_info['house_totalarea'];
    $data['unit_type'] = $house_info['unit_type'];
    $data['house_price'] = $house_info['house_price'];
    $data['house_month_price'] = $house_info['house_month_price'];
    if ($item['db'] == 0) {
        $url_fix = '';
    } else {
        $url_fix = MEMBER_DB_INDEX == $item['db'] ? 'x' : '_' . $item['db'];
    }
    $data['url'] = 'house_' . $item['id'] . $url_fix . '.html';
    if ($_GET['column'] == 'rent_old') {
        $data['url'] = '/rent/' . $data['url'];
    }
    $data['owner_name'] = $house_info['owner_name'];
    /*if ($data['user_type'] == 1) {
        //取得经纪人公司门店
        $member_info = $member_query->table('broker_info')->field('id, realname, company')->where('id = ' . $data['mid'])->cache(MEMCACHE_PREFIX . 'member_house_list_' . $data['mid'], MEMCACHE_MAX_EXPIRETIME)->one();
        if (!empty($member_info['realname'])) {
            $data['owner_name'] = $member_info['realname'];
            $data['company'] = $member_info['company'];
        }
        //取得品牌公寓
        if ($data['brand_apartment_id'] > 0 && $data['brand_apartment_checked'] > -1) {
            $condition = 'id = ' . $data['brand_apartment_id'] . ' AND is_checked IN (0,1)';
            $cache_key = 'brand_apartment_id_' . $data['brand_apartment_id'];
            $brand_apartment_data = $BrandApartment->table($BrandApartment->table_name)->where($condition)->cache($cache_key, MEMCACHE_MAX_EXPIRETIME)->one();
            $data['brand_apartment_name'] = $brand_apartment_data['brand_name'];
        }
    }*/
    if ($data['column_type'] == 1 && $data['brand_apartment_id'] > 0 && $data['brand_apartment_checked'] > -1) {
        $condition = 'id = ' . $data['brand_apartment_id'] . ' AND is_checked IN (0,1)';
        $cache_key = 'brand_apartment_id_' . $data['brand_apartment_id'];
        $brand_apartment_data = $BrandApartment->table($BrandApartment->table_name)->where($condition)->cache($cache_key, MEMCACHE_MAX_EXPIRETIME)->one();
        $data['brand_apartment_name'] = $brand_apartment_data['brand_name'];
    }
    $data['is_cooperation'] = intval($house_info['is_cooperation']);
    if ($data['column_type'] == 1) {
        $data['is_owner_direct_rent'] = $house_info['is_owner_direct_rent'];
        $data['bright_spot'] = $house_info['bright_spot'];
        $data['parking_lot'] = intval($house_info['parking_lot']);
    }
    if ($data['column_type'] == 4 && $house_info['is_sublet'] == 1 && $house_info['transfer_fee'] > 0) {
        $data['transfer_fee'] = floatval($house_info['transfer_fee']);
        $data['is_sublet'] = intval($house_info['is_sublet']);
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
                <form action="/rent/" method="get" id="search-form" onsubmit="return dosearch2();">
                    <input type="hidden" name="url" id="url" value="<?php echo 'list_' . $mtype . '_0_0_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_[keywords].html';?>" />
                    <input type="text" id="text" name="q" value="<?php echo $keywords;?>" autocomplete="off" placeholder="请输入区域、商圈或小区名进行搜索"/>
                    <div class="common-submit-btn search-submit-btn" onclick="dosearch2();">搜索</div>
                </form>
            </div>
            <a href="/member/houseRent.php" target="_blank" class="publish-btn">发布房源</a>
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
        } elseif ($mtype == 5 && $column['column_type'] == 1) {
            echo '<a href="list_5_0_0_0-0_0_0-0_0_2_0_1_.html">品牌公寓出租房源</a>';
        } else {
            echo '<a href="list_0.html">' . $column['column_position_title'] . '全部' . $house_type_title . '</a>';
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
                } elseif ($column['column_type'] == 2){
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
                } elseif ($column['column_type'] == 3){
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
                        $url = 'list_' . $mtype . '_{curentparam}_0_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html';
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
                        $url = 'list_' . $mtype . '_' . $cityarea . '_{curentparam}_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html';
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
                    <div class="div01">租金：</div>
                    <div class="div02">
                        <?php
                        $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_{curentparam}_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html';
                        if (empty($price) || $price == '0-0') {
                            echo '<span><a href="' . build_url($url, '0-0') . '">不限</a></span> ';
                        } else {
                            echo '<a href="' . build_url($url, '0-0') . '">不限</a> ';
                        }
                        foreach ($house_price_option as $key => $val) {
                            if ($key == $price) {
                                echo '<span><a href="' . build_url($url, $key) . '">' . $val . '</a></span> ';
                            } else {
                                echo '<a href="' . build_url($url, $key) . '">' . $val . '</a> ';
                            }
                        }
                        ?>
                        <div class="filter-input-box">
                            <?php
                            $range_tmp = explode('-', $price);
                            ?>
                            <input type="hidden" id="price_url" name="price_url" value="<?php echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_{curentparam}_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html'; ?>"/>
                            <input type="text" id="start_price" name="start_price" value="<?php echo !empty($range_tmp[0]) ? $range_tmp[0] : ''; ?>"/>
                        </div>
                        <div class="filter-split">-</div>
                        <div class="filter-input-box">
                            <input type="text" id="end_price" name="end_price" value="<?php echo !empty($range_tmp[1]) ? $range_tmp[1] : ''; ?>"/>
                        </div>
                        <div id="price-search" class="filter-search">价格筛选</div>
                    </div>
                    <div class="clear"></div>
                </li>
                <li>
                    <div class="div01">面积：</div>
                    <div class="div02">
                        <?php
                        $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_{curentparam}_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
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
                            <input type="hidden" id="totalarea_url" name="totalarea_url" value="<?php echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_{curentparam}_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html';?>" />
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
                <?php
                if ($column['column_type'] == 1){
                    ?>
                    <li>
                        <div class="div01">户型：</div>
                        <div class="div02">
                            <?php
                            $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_{curentparam}_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
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
                            $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_{curentparam}_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';
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
                    <li>
                        <div class="div01">其它：</div>
                        <div class="div02 clear-fix">
                            <?php
                            $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_{curentparam}_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html';
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
                            $url = 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_{curentparam}_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html';
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
                            <?php
                            $filter_value = $has_pic == 1 ? 0 : 1;
                            ?>
                            <div class="filter-check-box"><a class="link" href="<?php echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $filter_value . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html';?>"><em class="checkbox-icon<?php echo $has_pic == 1 ? ' checked' : '';?>"></em>只看有图</a></div>
                            <?php
                            $filter_value = $elevator == 1 ? 0 : 1;
                            ?>
                            <div class="filter-check-box"><a class="link" href="<?php echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $filter_value . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html';?>"><em class="checkbox-icon<?php echo $elevator == 1 ? ' checked' : '';?>"></em>有电梯</a></div>
                            <?php
                            $filter_value = $parking_lot == 1 ? 0 : 1;
                            ?>
                            <div class="filter-check-box"><a class="link" href="<?php echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $filter_value . '_' . $orderby . '_' . $orderway . '_1_.html';?>"><em class="checkbox-icon<?php echo $parking_lot == 1 ? ' checked' : '';?>"></em>有车位</a></div>
                            <div class="filter-check-box"><a class="link" href="<?php
                                echo $_GET['column'] == 'rent_old' ? '/rent/' : '/rent_old/';
                                echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_.html';?>"><em class="checkbox-icon<?php echo $_GET['column'] == 'rent_old' ? ' checked' : '';?>"></em>上年同期房源</a></div>
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
	<div id="main" class="list-box <?php
    echo $house_list_type == 1 ? 'list-pic-text' : 'list-text';
    if (in_array($column['column_type'], array(2, 3, 16, 17)) && stripos($_GET['column'], 'zr') === false){
        echo ' list-box-2 list-text-2';
    }
    ?>">
        <div id="title01">
            <span <?php if (empty($mtype)){echo ' class="on"';}?>><a href="<?php echo 'list_0_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';?>">全部</a></span>
            <span <?php if ($mtype == 2){echo ' class="on"';}?>><a href="<?php echo 'list_2_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';?>">个人<?php echo $currentColumn == 'cw' ? '车位' : '';?></a></span>
            <span <?php if ($mtype == 1){echo ' class="on"';}?>><a href="<?php echo 'list_1_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';?>">经纪人<?php echo $currentColumn == 'cw' ? '车位' : '';?></a></span>
            <?php
            if ($column['column_type'] == 1) {
                ?>
                <span <?php if ($mtype == 5) {
                    echo ' class="on"';
                } ?>><a href="<?php echo 'list_5_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html'; ?>">品牌公寓</a></span>
                <?php
            }
            ?>
            <span <?php if ($mtype == 3){echo ' class="on"';}?>><a href="<?php echo 'list_3_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';?>">非中介机构<?php echo $currentColumn == 'cw' ? '车位' : '';?></a></span>
            <?php
            if (!in_array($column['column_type'], array(4, 16, 17)) && $show_cooperation) {
            ?>
            <span <?php if ($mtype == 4){echo ' class="on"';}?>><a href="<?php echo 'list_4_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html';?>">合作房源</a></span>
                <?php
            }
            ?>
            <?php
            if ($show_yuanxingdichan == true) {
                ?>
                <span <?php if ($mtype == 1643095876) {
                    echo ' class="on"';
                } ?>><a href="<?php echo 'list_1643095876_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_' . $orderby . '_' . $orderway . '_1_' . $keywords . '.html'; ?>">远行地产</a></span>
                <?php
            }
            ?>
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
                echo 'list_' . $mtype . '_' . $cityarea . '_' . $cityarea2 . '_' . $price . '_' . $room . '_' . $totalarea . '_' . $rent_type . '_' . $house_toward . '_' . $house_fitment . '_' . $has_pic . '_' . $elevator . '_' . $parking_lot . '_{curentparam}_1_' . $keywords . '.html';
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
                                <a href="<?php echo $item['url'];?>" target="_blank">
                                    <img src="<?php echo !empty($item['house_thumb']) ? '/images/default_picture.jpg' : '/images/no_picture.jpg';?>" <?php echo !empty($item['house_thumb']) ? 'data-original="' . $item['house_thumb'] . '"' : '';?> />
                                    <?php
                                    if ($item['video']) {
                                        echo '<i class="icon-play"></i>';
                                    }
                                    ?>
                                </a>
                                <?php
                                if ($item['house_thumb']) {
                                    echo '<div class="pic-count">图</div>';
                                }
                                ?>
                            </div>
                            <div class="list-house-info clear-fix">
                                <div class="list-house-title">
                                    <a href="<?php echo $item['url'];?>" target="_blank"><?php
                                        if ($item['column_type'] == 1) {
                                            if ($item['rent_type'] == 1) {
                                                echo '整租';
                                            } else {
                                                echo '合租';
                                            }
                                            echo '<span class="point">·</span>';
                                            if ($item['brand_apartment_name']) {
                                                echo $item['brand_apartment_name'] . ' ';
                                            }
                                        }
                                        echo $item['title'];
                                        ?></a>
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
                                        if ($toward_dict[$item['house_toward']]) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">|</span>';
                                            }
                                            $tmp .= $toward_dict[$item['house_toward']] . '向';
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
                                        if ($fitment_dict[$item['house_fitment']]) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">|</span>';
                                            }
                                            $tmp .= $fitment_dict[$item['house_fitment']];
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
                                        if ($item['column_type'] == 4 && $item['is_sublet'] == 1 && $item['transfer_fee'] > 0) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">|</span>';
                                            }
                                            $tmp .= '转让费' . $item['transfer_fee'] . '万元';
                                        }
                                        echo $tmp;
                                        ?></p>
                                    <?php
                                    if ($item['company'] || $item['owner_name'] || $item['brand_apartment_name']) {
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
                                        if ($item['user_type'] == 1) {
                                            if ($item['brand_apartment_name']) {
                                                $tmp .= $item['brand_apartment_name'];
                                            } elseif ($item['company']) {
                                                $tmp .= $item['company'];
                                            }
                                        }
                                        echo '<p>联系人： ' . $tmp . '</p>';
                                    }
                                    ?>
                                    <?php
                                    if ($item['column_type'] == 1) {
                                    ?>
                                    <p class="bright-spot"><?php
                                        /*if ($item['user_type'] == 1 && $item['brand_apartment_id'] == 0) {
                                            echo '<span class="item">业主直租</span>';
                                            $bright_spot_length = 4;
                                        } else {
                                            $bright_spot_length = 5;
                                        }*/
                                        if ($item['is_owner_direct_rent'] == 1) {
                                            echo '<span class="item">业主直租</span>';
                                        }
                                        if ($item['brand_apartment_name']) {
                                            echo '<span class="item">品牌公寓</span>';
                                        }
                                        $bright_spot_length = 5;
                                        $bright_spot =  GetDictList($item['bright_spot'], $bright_spot_dict, $bright_spot_length);
                                        foreach ($bright_spot as $tag) {
                                            echo '<span class="item">' . $tag . '</span>';
                                        }
                                        if ($item['parking_lot'] == 1 && count($bright_spot) < $bright_spot_length) {
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
                                    /*if ($item['house_price'] > 10000) {
                                        $unit_name = '万';
                                        $house_price = round($item['house_price'] / 10000, 1);
                                    } else {
                                        $house_price = $item['house_price'];
                                        $unit_name = '';
                                    }*/
                                    echo '<strong>' . $item['house_price'] . '</strong> ';
                                    if (in_array($item['column_type'], array(2, 3))) {
                                        echo '元/㎡·天';
                                    } elseif ($item['column_type'] == 4) {
                                        echo '元/月';
                                    } else {
                                        if ($item['unit_type'] == 1){
                                            echo '元/月';
                                        }elseif ($item['unit_type'] == 2){
                                            echo '元/㎡·天';
                                        }elseif ($item['unit_type'] == 3){
                                            echo '元/天';
                                        }
                                    }
                                } else {
                                    echo '<strong>面议</strong>';
                                }
                                ?></div>
                            <?php
                            if (in_array($item['column_type'], array(2, 3, 16, 17)) && $item['house_price'] > 0 && stripos($_GET['column'], 'zr') === false) {
                                if ($item['house_month_price'] > 10000) {
                                    $house_month_price = round($item['house_month_price'] / 10000, 1);
                                    $month_unit_name = '万';
                                } else {
                                    $house_month_price = $item['house_month_price'];
                                    $month_unit_name = '';
                                }
                                echo '<div class="list-house-price-average">' . $house_month_price . $month_unit_name . '元/月</div>';
                            }
                            ?>
                            <div class="list-time <?php echo (in_array($item['column_type'], array(2, 3, 16, 17)) && $item['house_price'] > 0 && stripos($_GET['column'], 'zr') === false) ? 'list-time-2' : '';?>"><?php echo $item['updated'];?></div>
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
                        foreach ($recommend_house as $key => $item) {
                            ?>
                            <li class="clear-fix">
                                <div class="list-house-thumb">
                                    <a href="<?php echo $item['url']; ?>" target="_blank">
                                        <img
                                            src="<?php echo !empty($item['house_thumb']) ? '/images/default_picture.jpg' : '/images/no_picture.jpg'; ?>" <?php echo !empty($item['house_thumb']) ? 'data-original="' . $item['house_thumb'] . '"' : ''; ?> />
                                        <?php
                                        if ($item['video']) {
                                            echo '<i class="icon-play"></i>';
                                        }
                                        ?>
                                    </a>
                                    <?php
                                    if ($item['house_thumb']) {
                                        echo '<div class="pic-count">图</div>';
                                    }
                                    ?>
                                </div>
                                <div class="list-house-info clear-fix">
                                    <div class="list-house-title">
                                        <a href="<?php echo $item['url']; ?>" target="_blank"><?php
                                            if ($item['column_type'] == 1) {
                                                if ($item['rent_type'] == 1) {
                                                    echo '整租';
                                                } else {
                                                    echo '合租';
                                                }
                                                echo '<span class="point">·</span>';
                                                if ($item['brand_apartment_name']) {
                                                    echo $item['brand_apartment_name'] . ' ';
                                                }
                                            }
                                            echo $item['title'];
                                            ?></a>
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
                                                        $tmp .= $item['house_room'] . '室';
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
                                                if ($toward_dict[$item['house_toward']]) {
                                                    if ($tmp) {
                                                        $tmp .= '<span class="split">|</span>';
                                                    }
                                                    $tmp .= $toward_dict[$item['house_toward']] . '向';
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
                                                if ($fitment_dict[$item['house_fitment']]) {
                                                    if ($tmp) {
                                                        $tmp .= '<span class="split">|</span>';
                                                    }
                                                    $tmp .= $fitment_dict[$item['house_fitment']];
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
                                            if ($item['column_type'] == 4 && $item['is_sublet'] == 1 && $item['transfer_fee'] > 0) {
                                                if ($tmp) {
                                                    $tmp .= '<span class="split">|</span>';
                                                }
                                                $tmp .= '转让费' . $item['transfer_fee'] . '万元';
                                            }
                                            echo $tmp;
                                            ?></p>
                                        <?php
                                        if ($item['company'] || $item['owner_name'] || $item['brand_apartment_name']) {
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
                                            if ($item['user_type'] == 1) {
                                                if ($item['brand_apartment_name']) {
                                                    $tmp .= $item['brand_apartment_name'];
                                                } elseif ($item['company']) {
                                                    $tmp .= $item['company'];
                                                }
                                            }
                                            echo '<p>联系人： ' . $tmp . '</p>';
                                        }
                                        ?>
                                        <?php
                                        if ($item['column_type'] == 1) {
                                            ?>
                                            <p class="bright-spot"><?php
                                                /*if ($item['user_type'] == 1 && $item['brand_apartment_id'] == 0) {
                                                    echo '<span class="item">业主直租</span>';
                                                    $bright_spot_length = 4;
                                                } else {
                                                    $bright_spot_length = 5;
                                                }*/
                                                if ($item['is_owner_direct_rent'] == 1) {
                                                    echo '<span class="item">业主直租</span>';
                                                }
                                                if ($item['brand_apartment_name']) {
                                                    echo '<span class="item">品牌公寓</span>';
                                                }
                                                $bright_spot_length = 5;
                                                $bright_spot = GetDictList($item['bright_spot'], $bright_spot_dict, $bright_spot_length);
                                                foreach ($bright_spot as $tag) {
                                                    echo '<span class="item">' . $tag . '</span>';
                                                }
                                                if ($item['parking_lot'] == 1 && count($bright_spot) < $bright_spot_length) {
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
                                if ($column['column_type'] == 1) {
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
                                    if ($item['house_price'] > 0) {
                                        /*if ($item['house_price'] > 10000) {
                                            $unit_name = '万';
                                            $house_price = round($item['house_price'] / 10000, 1);
                                        } else {
                                            $house_price = $item['house_price'];
                                            $unit_name = '';
                                        }*/
                                        echo '<strong>' . $item['house_price'] . '</strong> ';
                                        if (in_array($item['column_type'], array(2, 3))) {
                                            echo '元/㎡·天';
                                        } elseif ($item['column_type'] == 4) {
                                            echo '元/月';
                                        } else {
                                            if ($item['unit_type'] == 1) {
                                                echo '元/月';
                                            } elseif ($item['unit_type'] == 2) {
                                                echo '元/㎡·天';
                                            } elseif ($item['unit_type'] == 3) {
                                                echo '元/天';
                                            }
                                        }
                                    } else {
                                        echo '<strong>面议</strong>';
                                    }
                                    ?></div>
                                <?php
                                if (in_array($item['column_type'], array(2, 3, 16, 17)) && $item['house_price'] > 0 && stripos($_GET['column'], 'zr') === false) {
                                    if ($item['house_month_price'] > 10000) {
                                        $house_month_price = round($item['house_month_price'] / 10000, 1);
                                        $month_unit_name = '万';
                                    } else {
                                        $house_month_price = $item['house_month_price'];
                                        $month_unit_name = '';
                                    }
                                    echo '<div class="list-house-price-average">' . $house_month_price . $month_unit_name . '元/月</div>';
                                }
                                ?>
                                <div
                                    class="list-time <?php echo (in_array($item['column_type'], array(2, 3, 16, 17)) && $item['house_price'] > 0 && stripos($_GET['column'], 'zr') === false) ? 'list-time-2' : ''; ?>"><?php echo $item['updated']; ?></div>
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
                                        <a href="<?php echo $item['url'];?>" target="_blank"><?php
                                            echo $item['title'];
                                            ?></a>
                                    </div>
                                    <div class="desc"><?php
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
                                            if ($item['house_price'] > 0) {
                                                if (in_array($item['column_type'], array(2, 3))) {
                                                    echo '<strong>' . $item['house_price'] . '</strong>元/㎡·天';
                                                } elseif ($item['column_type'] == 4) {
                                                    echo '<strong>' . $item['house_price'] . '</strong>元/月';
                                                } elseif (in_array($item['column_type'], array(16, 17))){
                                                    if ($item['is_sublet'] == 1) {
                                                        echo '<strong>' . $item['house_price'] . '</strong>元/月';
                                                    } else {
                                                        echo '<strong>' . $item['house_month_price'] . '</strong>元/月';
                                                    }
                                                } else {
                                                    echo '<strong>' . $house_price . '</strong>';
                                                    if ($item['unit_type'] == 1){
                                                        echo '元/月';
                                                    }elseif ($item['unit_type'] == 2){
                                                        echo '元/㎡·天';
                                                    }elseif ($item['unit_type'] == 3){
                                                        echo '元/天';
                                                    }
                                                }
                                            } else {
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
        <a id="recommend-house-tag"></a>
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
                        <div class="pic">
                            <a href="<?php echo $item['url'];?>" target="_blank">
                                <img src="<?php echo !empty($item['house_thumb']) ? '/images/default_picture.jpg' : '/images/no_picture.jpg';?>" <?php echo !empty($item['house_thumb']) ? 'data-original="' . $item['house_thumb'] . '"' : '';?> alt="<?php echo $item['title'];?>">
                                <?php
                                if ($item['video']) {
                                    echo '<i class="icon-play"></i>';
                                }
                                ?>
                            </a>
                        </div>
                        <div class="clear-fix price-house-type-box">
                            <div class="price"><?php
                                if ($item['house_price'] > 0) {
                                    if (in_array($item['column_type'], array(2, 3))) {
                                        echo '<span class="num">' . $item['house_price'] . '</span> 元/㎡·天';
                                    } elseif ($item['column_type'] == 4) {
                                        echo '<span class="num">' . $item['house_price'] . '</span> 元/月';
                                    } elseif (in_array($item['column_type'], array(16, 17))){
                                        if ($item['is_sublet'] == 1) {
                                            echo '<span class="num">' . $item['house_price'] . '</span> 元/月';
                                        } else {
                                            echo '<span class="num">' . $item['house_month_price'] . '</span> 元/月';
                                        }
                                    } else {
                                        echo '<span class="num">' . $item['house_price'] . '</span>';
                                        if ($item['unit_type'] == 1){
                                            echo ' 元/月';
                                        }elseif ($item['unit_type'] == 2){
                                            echo ' 元/㎡·天';
                                        }elseif ($item['unit_type'] == 3){
                                            echo ' 元/天';
                                        }
                                    }
                                } else {
                                    echo '面议';
                                }
                                ?></div>
                        </div>
                        <div class="info"><?php
                            $tmp = array();
                            if ($item['column_type'] == 1) {
                                if ($item['rent_type'] == 1) {
                                    $tmp[] = '整租';
                                } elseif ($item['rent_type'] == 2) {
                                    $tmp[] = '合租';
                                }
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
