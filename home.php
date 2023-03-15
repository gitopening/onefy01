<?php
$currentColumn = 'index';
require_once(dirname(__FILE__).'/common.inc.php');
$page->title = '【'.$cityInfo['city_name'].'二手房|'.$cityInfo['city_name'].'租房|全面及时的个人房源】'.$page->titlec.$cityInfo['city_name'];
//$page->title = '【二手房|租房|全面及时的个人房源】'.$page->titlec;
$page->description = $cityInfo['city_name'].$page->titlec.'提供大量'.$cityInfo['city_name'].'二手房,'.$cityInfo['city_name'].'租房,'.$cityInfo['city_name'].'商铺,'.$cityInfo['city_name'].'写字楼,'.$cityInfo['city_name'].'合租日租等房源信息|个人二手房,个人租房,个人房源信息。';
$page->keywords = $cityInfo['city_name'].'二手房，'.$cityInfo['city_name'].'二手房出租、'.$cityInfo['city_name'].'写字楼商铺、'.$cityInfo['city_name'].'房产网';

//根据用户访问情况调整数据显示顺序
/*$browsing_history_count = array();
if ($member_id) {
    //登录用户根据历史记录统计
    $BrowsingHistory = new BrowsingHistory($member_query);

    //二手房
    $publish_type = 2;
    $column_type = 1;
    $conditon = array(
        'mid' => $member_id,
        'publish_type' => $publish_type,
        'column_type' => $column_type
    );
    $memcache_key_name = MEMCACHE_PREFIX . 'history_c_' . $member_id . '_' . $publish_type . '_' . $column_type;
    $data_sum = $BrowsingHistory->table($BrowsingHistory->tName)->where($conditon)->cache($memcache_key_name, 60)->sum('browser_count');
    $browsing_history_count['sale_all_list'] = intval($data_sum);

    //住宅出租
    $publish_type = 1;
    $column_type = 1;
    $conditon = array(
        'mid' => $member_id,
        'publish_type' => $publish_type,
        'column_type' => $column_type
    );
    $memcache_key_name = MEMCACHE_PREFIX . 'history_c_' . $member_id . '_' . $publish_type . '_' . $column_type;
    $data_sum = $BrowsingHistory->table($BrowsingHistory->tName)->where($conditon)->cache($memcache_key_name, 60)->sum('browser_count');
    $browsing_history_count['rent_all_list'] = intval($data_sum);

    //写字楼出租
    $publish_type = 1;
    $column_type = 2;
    $conditon = array(
        'mid' => $member_id,
        'publish_type' => $publish_type,
        'column_type' => $column_type
    );
    $memcache_key_name = MEMCACHE_PREFIX . 'history_c_' . $member_id . '_' . $publish_type . '_' . $column_type;
    $data_sum = $BrowsingHistory->table($BrowsingHistory->tName)->where($conditon)->cache($memcache_key_name, 60)->sum('browser_count');
    $browsing_history_count['xzlcz_pic_all_list'] = intval($data_sum);

    //商铺出租
    $publish_type = 1;
    $column_type = 3;
    $conditon = array(
        'mid' => $member_id,
        'publish_type' => $publish_type,
        'column_type' => $column_type
    );
    $memcache_key_name = MEMCACHE_PREFIX . 'history_c_' . $member_id . '_' . $publish_type . '_' . $column_type;
    $data_sum = $BrowsingHistory->table($BrowsingHistory->tName)->where($conditon)->cache($memcache_key_name, 60)->sum('browser_count');
    $browsing_history_count['spcz_pic_all_list'] = intval($data_sum);
} else {
    //根据Cookie统计
    $browsing_history_count = array(
        'sale_all_list' => intval($_COOKIE['sale_browser_count']),
        'rent_all_list' => intval($_COOKIE['rent_browser_count']),
        'xzlcz_pic_all_list' => intval($_COOKIE['xzlcz_browser_count']),
        'spcz_pic_all_list' => intval($_COOKIE['spcz_browser_count'])
    );
}*/

//根据Cookie统计
$browser_count = GetBrowserCount();
$browsing_history_count = array(
    'sale_all_list' => intval($browser_count['sale']),
    'rent_all_list' => intval($browser_count['rent']),
    'new_all_list' => intval($browser_count['new']),
    'xzlcz_pic_all_list' => intval($browser_count['xzlcz']),
    'xzlcs_pic_all_list' => intval($browser_count['xzlcs']),
    'spcz_pic_all_list' => intval($browser_count['spcz']),
    'cfck_pic_all_list' => intval($browser_count['cfck'])
);
arsort($browsing_history_count);
$default_house_list_key = array(
    'sale_all_list' => 0,
    'rent_all_list' => 0,
    'new_all_list' => 0,
    'xzlcz_pic_all_list' => 0,
    'xzlcs_pic_all_list' => 0,
    'spcz_pic_all_list' => 0,
    'cfck_pic_all_list' => 0
);
foreach ($browsing_history_count as $key => $item) {
    if ($item > 0) {
        unset($default_house_list_key[$key]);
    } else {
        unset($browsing_history_count[$key]);
    }
}
$default_house_list_key = array_merge($browsing_history_count, $default_house_list_key);

$house_type_option = Dd::getArray('house_type');
$house_rent_option = get_filter_array($cityInfo['rent_price']);
$house_sale_option = get_filter_array($cityInfo['sale_price']);
$house_sp_rent_option = get_filter_array($cityInfo['sp_cz_price']);
$house_xzl_rent_option = get_filter_array($cityInfo['xzl_cz_price']);
$cityarea_option = get_region_enum($cityInfo['city_id'], 'sort');
$parent_id_array = array();
foreach ($cityarea_option as $value){
    $parent_id_array[] = $value['region_id'];
}
$cityarea2_list = get_region_enum($parent_id_array);

$Sphinx = Sphinx::getInstance();
//排序方式
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, '@random');

$now_time = time();

//顶部幻灯
$Sphinx->ResetFilters();
//$Sphinx->SetFilter('column_type', array(1,2,3));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
//$Sphinx->SetFilter('is_banner', array(1));
$Sphinx->SetFilterRange('house_thumb_length', 10, 300);
$Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');

$banner_max_length = 5;
$list_number = $banner_max_length;
$memcache_key_name = MEMCACHE_PREFIX . 'home_banner_list_' . $cityInfo['id'] . '_0_' . $list_number;
$banner_list = $Cache->get($memcache_key_name);
if (empty($banner_list)) {
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result = $Sphinx->Query('', SPHINX_MEMBER_RENT_BANNER_PROMOTE_INDEX);
    $banner_list = array();
    $rent_ids = array();
    foreach ($result['matches'] as $item){
        $data = array();
        $rent_ids[] = $item['id'];
        $data['id'] = $item['id'];
        $data['type'] = 1;
        $data['column_type'] = $item['attrs']['column_type'];
        switch ($data['column_type']) {
            case 1:
                $data['url'] = '/rent/';
                $column_title = '出租';
                break;
            case 2:
                $data['url'] = '/xzlcz/';
                $column_title = '写字楼出租';
                break;
            case 3:
                $data['url'] = '/spcz/';
                $column_title = '商铺出租';
                break;
            case 4:
                if ($item['attrs']['is_sublet'] == 1) {
                    $data['url'] = '/cwzr/';
                    $column_title = '车位转让';
                } else {
                    $data['url'] = '/cwcz/';
                    $column_title = '车位出租';
                }
                break;
            case 16:
                if ($item['attrs']['is_sublet'] == 1) {
                    $data['url'] = '/cfzr/';
                    $column_title = '厂房转让';
                } else {
                    $data['url'] = '/cfcz/';
                    $column_title = '厂房出租';
                }
                break;
            case 17:
                if ($item['attrs']['is_sublet'] == 1) {
                    $data['url'] = '/ckzr/';
                    $column_title = '仓库转让';
                } else {
                    $data['url'] = '/ckcz/';
                    $column_title = '仓库出租';
                }
                break;
            default:
                $data['url'] = '/rent/';
                $column_title = '出租';
        }
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column_title);
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
        $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
        $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['unit_type'] = $item['attrs']['unit_type'];
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $data['url'] .= 'house_' . $item['id'] . $url_fix . '.html';
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 11, $item['attrs']['member_db_index']);
        $banner_list[] = $data;
    }

    $result = $Sphinx->Query('', SPHINX_MEMBER_SALE_BANNER_PROMOTE_INDEX);
    $sale_ids = array();
    foreach ($result['matches'] as $item){
        $data = array();
        $sale_ids[] = $item['id'];
        $data['id'] = $item['id'];
        $data['type'] = 2;
        $data['column_type'] = $item['attrs']['column_type'];
        switch ($data['column_type']) {
            case 1:
                $data['url'] = '/sale/';
                $column_title = '二手房';
                break;
            case 2:
                $data['url'] = '/xzlcs/';
                $column_title = '写字楼出售';
                break;
            case 3:
                $data['url'] = '/spcs/';
                $column_title = '商铺出售';
                break;
            case 4:
                $data['url'] = '/cwcs/';
                $column_title = '车位出售';
                break;
            case 16:
                $data['url'] = '/cfcs/';
                $column_title = '厂房出售';
                break;
            case 17:
                $data['url'] = '/ckcs/';
                $column_title = '仓库出售';
                break;
            default:
                $data['url'] = '/sale/';
                $column_title = '出售';
        }
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column_title);
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
        $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
        $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['unit_type'] = 4;
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        if ($data['house_totalarea'] > 0 && $data['house_price'] > 0) {
            $data['house_price_average'] = round($data['house_price'] * 10000 / $data['house_totalarea'], 0);
        } else {
            $data['house_price_average'] = 0;
        }
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $data['url'] .= 'house_' . $item['id'] . $url_fix . '.html';
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 11, $item['attrs']['member_db_index']);
        $banner_list[] = $data;
    }

    $result = $Sphinx->Query('', SPHINX_MEMBER_NEW_BANNER_PROMOTE_INDEX);
    $new_ids = array();
    foreach ($result['matches'] as $item){
        $data = array();
        $new_ids[] = $item['id'];
        $data['id'] = $item['id'];
        $data['type'] = 3;
        $data['column_type'] = $item['attrs']['column_type'];
        if ($data['column_type'] == 1) {
            $column_title = '住宅新房';
        } elseif ($data['column_type'] == 2) {
            $column_title = '写字楼新房';
        } elseif ($data['column_type'] == 3) {
            $column_title = '商铺新房';
        }
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column_title);
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
        $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
        $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['house_price_average'] = FormatHousePrice($item['attrs']['house_price_average']);
        if ($item['attrs']['house_topfloor'] > 0 && $item['attrs']['house_floor'] > 0) {
            $data['house_floor'] = GetHouseFloor($item['attrs']['house_floor'], $item['attrs']['house_topfloor']);
            if ($item['attrs']['house_topfloor'] <= 2) {
                $data['house_floor'] .= '共' . $item['attrs']['house_topfloor'] . '层';
            } else {
                $data['house_floor'] .= '(共' . $item['attrs']['house_topfloor'] . '层)';
            }
        } else {
            $data['house_floor'] = '';
        }
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 11, $item['attrs']['member_db_index']);
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
        $banner_list[] = $data;
    }

    $banner_list_count = count($banner_list);
    if ($banner_list_count < $banner_max_length) {
        $list_number = $banner_max_length - $banner_list_count;

        $Sphinx->ResetFilters();
        //$Sphinx->SetFilter('column_type', array(1,2,3));
        $Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
        $Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
        $Sphinx->SetFilter('is_delete', array(0));
        $Sphinx->SetFilter('is_checked', array(0, 1));
        $Sphinx->SetFilter('is_down', array(0));
        $Sphinx->SetFilter('is_banner', array(1));
        $Sphinx->SetFilterRange('house_thumb_length', 10, 300);
        $Sphinx->SetFilter('filter_id', $rent_ids, true);
        $Sphinx->SetLimits(0, $list_number, $list_number);
        $result = $Sphinx->Query('', SPHINX_MEMBER_RENT_INDEX);
        $banner_list_2 = array();
        foreach ($result['matches'] as $item){
            $data = array();
            $data['id'] = $item['id'];
            $data['type'] = 1;
            $data['column_type'] = $item['attrs']['column_type'];
            switch ($data['column_type']) {
                case 1:
                    $data['url'] = '/rent/';
                    $column_title = '出租';
                    break;
                case 2:
                    $data['url'] = '/xzlcz/';
                    $column_title = '写字楼出租';
                    break;
                case 3:
                    $data['url'] = '/spcz/';
                    $column_title = '商铺出租';
                    break;
                case 4:
                    if ($item['attrs']['is_sublet'] == 1) {
                        $data['url'] = '/cwzr/';
                        $column_title = '车位转让';
                    } else {
                        $data['url'] = '/cwcz/';
                        $column_title = '车位出租';
                    }
                    break;
                case 16:
                    if ($item['attrs']['is_sublet'] == 1) {
                        $data['url'] = '/cfzr/';
                        $column_title = '厂房转让';
                    } else {
                        $data['url'] = '/cfcz/';
                        $column_title = '厂房出租';
                    }
                    break;
                case 17:
                    if ($item['attrs']['is_sublet'] == 1) {
                        $data['url'] = '/ckzr/';
                        $column_title = '仓库转让';
                    } else {
                        $data['url'] = '/ckcz/';
                        $column_title = '仓库出租';
                    }
                    break;
                default:
                    $data['url'] = '/rent/';
                    $column_title = '出租';
            }
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column_title);
            $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['cityarea_id'] = $item['attrs']['cityarea_id'];
            $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['unit_type'] = $item['attrs']['unit_type'];
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] .= 'house_' . $item['id'] . $url_fix . '.html';
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 11, $item['attrs']['member_db_index']);
            $banner_list_2[] = $data;
        }

        $Sphinx->ResetFilters();
        //$Sphinx->SetFilter('column_type', array(1,2,3));
        $Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
        $Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
        $Sphinx->SetFilter('is_delete', array(0));
        $Sphinx->SetFilter('is_checked', array(0, 1));
        $Sphinx->SetFilter('is_down', array(0));
        $Sphinx->SetFilter('is_banner', array(1));
        $Sphinx->SetFilterRange('house_thumb_length', 10, 300);
        $Sphinx->SetFilter('filter_id', $sale_ids, true);
        $Sphinx->SetLimits(0, $list_number, $list_number);

        $result = $Sphinx->Query('', SPHINX_MEMBER_SALE_INDEX);
        foreach ($result['matches'] as $item){
            $data = array();
            $data['id'] = $item['id'];
            $data['type'] = 2;
            $data['column_type'] = $item['attrs']['column_type'];
            switch ($data['column_type']) {
                case 1:
                    $data['url'] = '/sale/';
                    $column_title = '二手房';
                    break;
                case 2:
                    $data['url'] = '/xzlcs/';
                    $column_title = '写字楼出售';
                    break;
                case 3:
                    $data['url'] = '/spcs/';
                    $column_title = '商铺出售';
                    break;
                case 4:
                    $data['url'] = '/cwcs/';
                    $column_title = '车位出售';
                    break;
                case 16:
                    $data['url'] = '/cfcs/';
                    $column_title = '厂房出售';
                    break;
                case 17:
                    $data['url'] = '/ckcs/';
                    $column_title = '仓库出售';
                    break;
                default:
                    $data['url'] = '/sale/';
                    $column_title = '出售';
            }
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column_title);
            $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['cityarea_id'] = $item['attrs']['cityarea_id'];
            $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['unit_type'] = 4;
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            if ($data['house_totalarea'] > 0 && $data['house_price'] > 0) {
                $data['house_price_average'] = round($data['house_price'] * 10000 / $data['house_totalarea'], 0);
            } else {
                $data['house_price_average'] = 0;
            }
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] .= 'house_' . $item['id'] . $url_fix . '.html';
            $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 11, $item['attrs']['member_db_index']);
            $banner_list_2[] = $data;
        }

        $Sphinx->ResetFilters();
        //$Sphinx->SetFilter('column_type', array(1,2,3));
        $Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
        $Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
        $Sphinx->SetFilter('is_delete', array(0));
        $Sphinx->SetFilter('is_checked', array(0, 1));
        $Sphinx->SetFilter('is_down', array(0));
        $Sphinx->SetFilter('is_banner', array(1));
        $Sphinx->SetFilterRange('house_thumb_length', 10, 300);
        $Sphinx->SetFilter('filter_id', $new_ids, true);
        $Sphinx->SetLimits(0, $list_number, $list_number);
        $result = $Sphinx->Query('', SPHINX_MEMBER_NEW_INDEX);
        foreach ($result['matches'] as $item){
            $data = array();
            $data['id'] = $item['id'];
            $data['type'] = 3;
            $data['column_type'] = $item['attrs']['column_type'];
            if ($data['column_type'] == 1) {
                $column_title = '住宅新房';
            } elseif ($data['column_type'] == 2) {
                $column_title = '写字楼新房';
            } elseif ($data['column_type'] == 3) {
                $column_title = '商铺新房';
            }
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column_title);
            $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['cityarea_id'] = $item['attrs']['cityarea_id'];
            $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['house_price_average'] = FormatHousePrice($item['attrs']['house_price_average']);
            if ($item['attrs']['house_topfloor'] > 0 && $item['attrs']['house_floor'] > 0) {
                $data['house_floor'] = GetHouseFloor($item['attrs']['house_floor'], $item['attrs']['house_topfloor']);
                if ($item['attrs']['house_topfloor'] <= 2) {
                    $data['house_floor'] .= '共' . $item['attrs']['house_topfloor'] . '层';
                } else {
                    $data['house_floor'] .= '(共' . $item['attrs']['house_topfloor'] . '层)';
                }
            } else {
                $data['house_floor'] = '';
            }
            $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 11, $item['attrs']['member_db_index']);
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
            $banner_list_2[] = $data;
        }

        if ($banner_list_2) {
            shuffle($banner_list_2);
            foreach ($banner_list_2 as $key => $item) {
                if ($key >= $list_number) {
                    break;
                }
                $banner_list[] = $item;
            }
        }
    }

    if ($banner_list) {
        shuffle($banner_list);
        $Cache->set($memcache_key_name, $banner_list, 60);
    }
}

//全站二手房带图
$Sphinx->ResetFilters();
//$Sphinx->SetFilter('column_type', array(1, 2, 3, 4));
$Sphinx->SetFilter('column_type', array(1));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$Sphinx->SetFilterRange('house_thumb_length', 10, 300);
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, '@random');
$list_number = 4;
//$key = 'home_sale_list_cityid_mtype_listnumber';
$memcache_key_name = MEMCACHE_PREFIX . 'home_sale_list_' . $cityInfo['id'] . '_0_' . $list_number;
$sale_all_list = $Cache->get($memcache_key_name);
if (empty($sale_all_list)) {
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result_promote = $Sphinx->Query('', SPHINX_MEMBER_SALE_HOME_PROMOTE_INDEX);
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
        $result = $Sphinx->Query('', SPHINX_MEMBER_SALE_INDEX);
    } else {
        $result = array();
    }
    $result_matches = array_merge((array)$result_promote['matches'], (array)$result['matches']);
    $sale_all_list = array();
    foreach ($result_matches as $item){
        $data = array();
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, '二手房');
        $data['id'] = $item['id'];
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
        $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
        $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        if ($data['house_totalarea'] > 0 && $data['house_price'] > 0) {
            $data['house_price_average'] = round($data['house_price'] * 10000 / $data['house_totalarea'], 0);
        } else {
            $data['house_price_average'] = 0;
        }
        if ($item['attrs']['house_topfloor'] > 0 && $item['attrs']['house_floor'] > 0) {
            $data['house_floor'] = GetHouseFloor($item['attrs']['house_floor'], $item['attrs']['house_topfloor']);
            if ($item['attrs']['house_topfloor'] <= 2) {
                $data['house_floor'] .= '共' . $item['attrs']['house_topfloor'] . '层';
            } else {
                $data['house_floor'] .= '(共' . $item['attrs']['house_topfloor'] . '层)';
            }
        } else {
            $data['house_floor'] = '';
        }
        //$data['house_thumb'] = GetHouseThumb($item['attrs']['house_thumb'], $item['attrs']['member_db_index']);
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
        $data['column_type'] = $item['attrs']['column_type'];
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $data['url'] = '/sale/house_' . $item['id'] . $url_fix . '.html';
        $sale_all_list[] = $data;
    }
    if ($sale_all_list) {
        $Cache->set($memcache_key_name, $sale_all_list, 60);
    }
}

//统计出售房源数量
/*$memcache_key_name = MEMCACHE_PREFIX . 'home_sale_p_' . $cityInfo['id'] . '_c';
$condition = array(
    'column_type' => 1,
    'mtype' => 2,
    'city_website_id' => $cityInfo['id']
);
$count_1 = $query->table('housesell')->where($condition)->cache($memcache_key_name, 86400)->count();
$count_2 = $member_query->table('housesell')->where($condition)->cache($memcache_key_name, 86400)->count();
$person_sale_count = $count_1 + $count_2;

$memcache_key_name = MEMCACHE_PREFIX . 'home_sale_hz_' . $cityInfo['id'] . '_c';
$condition = array(
    'column_type' => 1,
    'is_cooperation' => 2,
    'city_website_id' => $cityInfo['id']
);
$cooperation_sale_count = $member_query->table('housesell')->where($condition)->cache($memcache_key_name, 86400)->count();*/

//全站出租房带图
$Sphinx->ResetFilters();
$Sphinx->SetFilter('column_type', array(1));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$Sphinx->SetFilterRange('house_thumb_length', 10, 300);
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, '@random');
$list_number = 4;
$memcache_key_name = MEMCACHE_PREFIX . 'home_rent_list_' . $cityInfo['id'] . '_0_' . $list_number;
$rent_all_list = $Cache->get($memcache_key_name);
if (empty($rent_all_list)) {
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
    $rent_all_list = array();
    foreach ($result_matches as $item){
        $data = array();
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, '租房');
        $data['id'] = $item['id'];
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['rent_type'] = $item['attrs']['rent_type'];
        $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
        $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
        $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['unit_type'] = $item['attrs']['unit_type'];
        if ($item['attrs']['house_topfloor'] > 0 && $item['attrs']['house_floor'] > 0) {
            $data['house_floor'] = GetHouseFloor($item['attrs']['house_floor'], $item['attrs']['house_topfloor']);
            if ($item['attrs']['house_topfloor'] <= 2) {
                $data['house_floor'] .= '共' . $item['attrs']['house_topfloor'] . '层';
            } else {
                $data['house_floor'] .= '(共' . $item['attrs']['house_topfloor'] . '层)';
            }
        } else {
            $data['house_floor'] = '';
        }
        //$data['house_thumb'] = GetHouseThumb($item['attrs']['house_thumb'], $item['attrs']['member_db_index']);
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $data['url'] = '/rent/house_' . $item['id'] . $url_fix . '.html';
        $rent_all_list[] = $data;
    }
    //存储数据到Memcache
    if ($rent_all_list) {
        $Cache->set($memcache_key_name, $rent_all_list, 60);
    }
}

//统计出租房源数量
/*$memcache_key_name = MEMCACHE_PREFIX . 'home_rent_p_' . $cityInfo['id'] . '_c';
$condition = array(
    'column_type' => 1,
    'mtype' => 2,
    'city_website_id' => $cityInfo['id']
);
$count_1 = $query->table('houserent')->where($condition)->cache($memcache_key_name, 86400)->count();
$count_2 = $member_query->table('houserent')->where($condition)->cache($memcache_key_name, 86400)->count();
$person_rent_count = $count_1 + $count_2;

$memcache_key_name = MEMCACHE_PREFIX . 'home_rent_hz_' . $cityInfo['id'] . '_c';
$condition = array(
    'column_type' => 1,
    'is_cooperation' => 2,
    'city_website_id' => $cityInfo['id']
);
$cooperation_rent_count = $member_query->table('houserent')->where($condition)->cache($memcache_key_name, 86400)->count();*/

//全站新房带图
$Sphinx->ResetFilters();
//$Sphinx->SetFilter('column_type', array(1, 2, 3, 4));
//$Sphinx->SetFilter('column_type', array(1, 2, 3));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$Sphinx->SetFilterRange('house_thumb_length', 10, 300);
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, '@random');
$list_number = 4;
//$key = 'home_sale_list_cityid_mtype_listnumber';
$memcache_key_name = MEMCACHE_PREFIX . 'home_new_list_' . $cityInfo['id'] . '_0_' . $list_number;
$new_all_list = $Cache->get($memcache_key_name);
if (empty($new_all_list)) {
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
    $new_all_list = array();
    foreach ($result_matches as $item){
        $data = array();
        $data['column_type'] = $item['attrs']['column_type'];
        if ($data['column_type'] == 1) {
            $column_title = '住宅新房';
        } elseif ($data['column_type'] == 2) {
            $column_title = '写字楼新房';
        } elseif ($data['column_type'] == 3) {
            $column_title = '商铺新房';
        }
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column_title);
        $data['id'] = $item['id'];
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
        $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
        $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['house_price_average'] = FormatHousePrice($item['attrs']['house_price_average']);
        if ($item['attrs']['house_topfloor'] > 0 && $item['attrs']['house_floor'] > 0) {
            $data['house_floor'] = GetHouseFloor($item['attrs']['house_floor'], $item['attrs']['house_topfloor']);
            if ($item['attrs']['house_topfloor'] <= 2) {
                $data['house_floor'] .= '共' . $item['attrs']['house_topfloor'] . '层';
            } else {
                $data['house_floor'] .= '(共' . $item['attrs']['house_topfloor'] . '层)';
            }
        } else {
            $data['house_floor'] = '';
        }
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
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
        $new_all_list[] = $data;
    }
    if ($new_all_list) {
        $Cache->set($memcache_key_name, $new_all_list, 60);
    }
}

//全站写字楼出租带图
$Sphinx->ResetFilters();
$Sphinx->SetFilter('column_type', array(2));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$Sphinx->SetFilterRange('house_thumb_length', 10, 300);
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, '@random');
$memcache_key_name = MEMCACHE_PREFIX . 'h_xzlcz_pic_l_' . $cityInfo['id'] . '_0_' . $list_number;
$xzlcz_pic_all_list = $Cache->get($memcache_key_name);
if (empty($xzlcz_pic_all_list)) {
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
        $Sphinx->SetLimits(0, $list_number, $list_number);
        $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');
        $result = $Sphinx->Query('', SPHINX_MEMBER_RENT_INDEX);
    } else {
        $result = array();
    }

    $result_matches = array_merge((array)$result_promote['matches'], (array)$result['matches']);
    $xzlcz_pic_all_list = array();
    foreach ($result_matches as $item){
        $data = array();
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, '写字楼出租');
        $data['id'] = $item['id'];
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['unit_type'] = $item['attrs']['unit_type'];
        if ($item['attrs']['house_topfloor'] > 0 && $item['attrs']['house_floor'] > 0) {
            $data['house_floor'] = GetHouseFloor($item['attrs']['house_floor'], $item['attrs']['house_topfloor']);
            if ($item['attrs']['house_topfloor'] <= 2) {
                $data['house_floor'] .= '共' . $item['attrs']['house_topfloor'] . '层';
            } else {
                $data['house_floor'] .= '(共' . $item['attrs']['house_topfloor'] . '层)';
            }
        } else {
            $data['house_floor'] = '';
        }
        //$data['house_thumb'] = GetHouseThumb($item['attrs']['house_thumb'], $item['attrs']['member_db_index']);
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
        $data['column_type'] = intval($item['attrs']['column_type']);
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $data['url'] = '/xzlcz/house_' . $item['id'] . $url_fix . '.html';
        $xzlcz_pic_all_list[] = $data;
    }
    //存储数据到Memcache
    if ($xzlcz_pic_all_list) {
        $Cache->set($memcache_key_name, $xzlcz_pic_all_list, 60);
    }
}

//写字楼出售
$Sphinx->ResetFilters();
$Sphinx->SetFilter('column_type', array(2));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$Sphinx->SetFilterRange('house_thumb_length', 10, 300);
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, '@random');
$memcache_key_name = MEMCACHE_PREFIX . 'h_xzlcs_pic_l_' . $cityInfo['id'] . '_0_' . $list_number;
$xzlcs_pic_all_list = $Cache->get($memcache_key_name);
if (empty($xzlcs_pic_all_list)) {
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result_promote = $Sphinx->Query('', SPHINX_MEMBER_SALE_HOME_PROMOTE_INDEX);
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
        $result = $Sphinx->Query('', SPHINX_MEMBER_SALE_INDEX);
    } else {
        $result = array();
    }
    $result_matches = array_merge((array)$result_promote['matches'], (array)$result['matches']);
    $xzlcs_pic_all_list = array();
    foreach ($result_matches as $item){
        $data = array();
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, '写字楼出售');
        $data['id'] = $item['id'];
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        if ($data['house_totalarea'] > 0 && $data['house_price'] > 0) {
            $data['house_price_average'] = round($data['house_price'] * 10000 / $data['house_totalarea'], 0);
        } else {
            $data['house_price_average'] = 0;
        }
        if ($item['attrs']['house_topfloor'] > 0 && $item['attrs']['house_floor'] > 0) {
            $data['house_floor'] = GetHouseFloor($item['attrs']['house_floor'], $item['attrs']['house_topfloor']);
            if ($item['attrs']['house_topfloor'] <= 2) {
                $data['house_floor'] .= '共' . $item['attrs']['house_topfloor'] . '层';
            } else {
                $data['house_floor'] .= '(共' . $item['attrs']['house_topfloor'] . '层)';
            }
        } else {
            $data['house_floor'] = '';
        }
        //$data['house_thumb'] = GetHouseThumb($item['attrs']['house_thumb'], $item['attrs']['member_db_index']);
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
        $data['column_type'] = $item['attrs']['column_type'];
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $data['url'] = '/xzlcs/house_' . $item['id'] . $url_fix . '.html';
        $xzlcs_pic_all_list[] = $data;
    }
    if ($xzlcs_pic_all_list) {
        $Cache->set($memcache_key_name, $xzlcs_pic_all_list, 60);
    }
}

//全站商铺出租带图
$Sphinx->ResetFilters();
$Sphinx->SetFilter('column_type', array(3));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$Sphinx->SetFilterRange('house_thumb_length', 10, 300);
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, '@random');
$memcache_key_name = MEMCACHE_PREFIX . 'h_spcz_pic_l_' . $cityInfo['id'] . '_0_' . $list_number;
$spcz_pic_all_list = $Cache->get($memcache_key_name);
if (empty($spcz_pic_all_list)) {
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
        $Sphinx->SetLimits(0, $list_number, $list_number);
        $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');
        $result = $Sphinx->Query('', SPHINX_MEMBER_RENT_INDEX);
    } else {
        $result = array();
    }

    $result_matches = array_merge((array)$result_promote['matches'], (array)$result['matches']);
    $spcz_pic_all_list = array();
    foreach ($result_matches as $item){
        $data = array();
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, '商铺出租');
        $data['id'] = $item['id'];
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['unit_type'] = $item['attrs']['unit_type'];
        if ($item['attrs']['house_topfloor'] > 0 && $item['attrs']['house_floor'] > 0) {
            $data['house_floor'] = GetHouseFloor($item['attrs']['house_floor'], $item['attrs']['house_topfloor']);
            if ($item['attrs']['house_topfloor'] <= 2) {
                $data['house_floor'] .= '共' . $item['attrs']['house_topfloor'] . '层';
            } else {
                $data['house_floor'] .= '(共' . $item['attrs']['house_topfloor'] . '层)';
            }
        } else {
            $data['house_floor'] = '';
        }
        //$data['house_thumb'] = GetHouseThumb($item['attrs']['house_thumb'], $item['attrs']['member_db_index']);
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
        $data['column_type'] = intval($item['attrs']['column_type']);
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $data['url'] = '/spcz/house_' . $item['id'] . $url_fix . '.html';
        $spcz_pic_all_list[] = $data;
    }
    //存储数据到Memcache
    if ($spcz_pic_all_list) {
        $Cache->set($memcache_key_name, $spcz_pic_all_list, 60);
    }
}

//全站厂房仓库出租带图
$memcache_key_name = MEMCACHE_PREFIX . 'h_cfck_pic_l_' . $cityInfo['id'] . '_0_' . $list_number;
$cfck_pic_all_list = $Cache->get($memcache_key_name);
if (empty($cfck_pic_all_list)) {
    $cfck_pic_all_list = array();
    $Sphinx->ResetFilters();
    $Sphinx->SetFilter('column_type', array(16, 17));
    $Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
    $Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
    $Sphinx->SetFilter('is_delete', array(0));
    $Sphinx->SetFilter('is_checked', array(0, 1));
    $Sphinx->SetFilter('is_down', array(0));
    $Sphinx->SetFilterRange('house_thumb_length', 10, 300);
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result = $Sphinx->Query('', SPHINX_MEMBER_RENT_HOME_PROMOTE_INDEX);
    foreach ($result['matches'] as $item){
        $data = array();
        $data['type'] = 1;
        $data['id'] = $item['id'];
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['unit_type'] = $item['attrs']['unit_type'];
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
        $data['column_type'] = intval($item['attrs']['column_type']);
        $data['is_sublet'] = intval($item['attrs']['is_sublet']);
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $column_url = '';
        $house_type_title = '';
        if ($data['column_type'] == 16) {
            $column_url = 'cf';
            $house_type_title .= '厂房';
        } elseif ($data['column_type'] == 17) {
            $column_url = 'ck';
            $house_type_title .= '仓库';
        }
        if ($data['is_sublet'] == 1) {
            $column_url .= 'zr';
            $house_type_title .= '转让';
        } else {
            $column_url .= 'cz';
            $house_type_title .= '出租';
        }
        $data['url'] = '/' . $column_url . '/house_' . $item['id'] . $url_fix . '.html';
        $data['is_sublet'] = intval($item['attrs']['is_sublet']);
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $house_type_title);
        $data['house_type_title'] = $house_type_title;
        $cfck_pic_all_list[] = $data;
    }

    $result = $Sphinx->Query('', SPHINX_MEMBER_SALE_HOME_PROMOTE_INDEX);
    foreach ($result['matches'] as $item){
        $data = array();
        $data['type'] = 2;
        $data['id'] = $item['id'];
        $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
        $data['cityarea_id'] = $item['attrs']['cityarea_id'];
        $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
        $data['borough_name'] = $item['attrs']['borough_name'];
        $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
        $data['house_totalarea'] = $item['attrs']['house_totalarea'];
        $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
        $data['unit_type'] = $item['attrs']['unit_type'];
        $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
        $data['column_type'] = intval($item['attrs']['column_type']);
        $data['is_sublet'] = intval($item['attrs']['is_sublet']);
        if ($item['attrs']['member_db_index'] == 0) {
            $url_fix = '';
        } else {
            $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
        }
        $column_url = '';
        $house_type_title = '';
        if ($data['column_type'] == 16) {
            $column_url = 'cf';
            $house_type_title .= '厂房';
        } elseif ($data['column_type'] == 17) {
            $column_url = 'ck';
            $house_type_title .= '仓库';
        }
        $column_url .= 'cs';
        $house_type_title .= '出售';
        $data['url'] = '/' . $column_url . '/house_' . $item['id'] . $url_fix . '.html';
        $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $house_type_title);
        $data['house_type_title'] = $house_type_title;
        $cfck_pic_all_list[] = $data;
    }
    shuffle($cfck_pic_all_list);

    $list_count = count($cfck_pic_all_list);
    if ($list_count < 4) {
        $exclude_id = array();
        foreach ($cfck_pic_all_list as $item) {
            $exclude_id[] = $item['id'];
        }
        $Sphinx->SetFilter('filter_id', $exclude_id, true);

        $list_number_2 = 4 - $list_count;
        $Sphinx->SetLimits(0, $list_number_2, $list_number_2);
        $result = $Sphinx->Query('', SPHINX_MEMBER_RENT_INDEX);
        $cfck_pic_all_list_2 = array();
        foreach ($result['matches'] as $item){
            $data = array();
            $data['type'] = 1;
            $data['id'] = $item['id'];
            $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
            $data['cityarea_id'] = $item['attrs']['cityarea_id'];
            $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['unit_type'] = $item['attrs']['unit_type'];
            $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
            $data['column_type'] = intval($item['attrs']['column_type']);
            $data['is_sublet'] = intval($item['attrs']['is_sublet']);
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $column_url = '';
            $house_type_title = '';
            if ($data['column_type'] == 16) {
                $column_url = 'cf';
                $house_type_title .= '厂房';
            } elseif ($data['column_type'] == 17) {
                $column_url = 'ck';
                $house_type_title .= '仓库';
            }
            if ($data['is_sublet'] == 1) {
                $column_url .= 'zr';
                $house_type_title .= '转让';
            } else {
                $column_url .= 'cz';
                $house_type_title .= '出租';
            }
            $data['url'] = '/' . $column_url . '/house_' . $item['id'] . $url_fix . '.html';
            $data['is_sublet'] = intval($item['attrs']['is_sublet']);
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $house_type_title);
            $data['house_type_title'] = $house_type_title;
            $cfck_pic_all_list_2[] = $data;
        }

        $result = $Sphinx->Query('', SPHINX_MEMBER_SALE_INDEX);
        foreach ($result['matches'] as $item){
            $data = array();
            $data['type'] = 2;
            $data['id'] = $item['id'];
            $data['house_type'] = $house_type_option[$item['attrs']['house_type']];
            $data['cityarea_id'] = $item['attrs']['cityarea_id'];
            $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['unit_type'] = $item['attrs']['unit_type'];
            $data['house_thumb'] = GetPictureUrl($item['attrs']['house_thumb'], 2, $item['attrs']['member_db_index']);
            $data['column_type'] = intval($item['attrs']['column_type']);
            $data['is_sublet'] = intval($item['attrs']['is_sublet']);
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $column_url = '';
            $house_type_title = '';
            if ($data['column_type'] == 16) {
                $column_url = 'cf';
                $house_type_title .= '厂房';
            } elseif ($data['column_type'] == 17) {
                $column_url = 'ck';
                $house_type_title .= '仓库';
            }
            $column_url .= 'cs';
            $house_type_title .= '出售';
            $data['url'] = '/' . $column_url . '/house_' . $item['id'] . $url_fix . '.html';
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $house_type_title);
            $data['house_type_title'] = $house_type_title;
            $cfck_pic_all_list_2[] = $data;
        }

        if ($cfck_pic_all_list_2) {
            shuffle($cfck_pic_all_list_2);
            foreach ($cfck_pic_all_list_2 as $key => $item) {
                if ($key >= $list_number_2) {
                    break;
                }
                $cfck_pic_all_list[] = $item;
            }
        }
    } elseif ($list_count > 4) {
        $new_data_list = array();
        foreach ($cfck_pic_all_list as $key => $item) {
            if ($key >= $list_number) {
                break;
            }
            $new_data_list[] = $item;
        }
        $cfck_pic_all_list = $new_data_list;
    }

    //存储数据到Memcache
    if ($cfck_pic_all_list) {
        $Cache->set($memcache_key_name, $cfck_pic_all_list, 60);
    }
}

//统计写字楼商铺出租房源数量
/*$memcache_key_name = MEMCACHE_PREFIX . 'home_xzl_cz_p_' . $cityInfo['id'] . '_c';
$condition = array(
    'column_type' => array('IN (2,3)'),
    'mtype' => 2,
    'city_website_id' => $cityInfo['id']
);
$count_1 = $member_query->table('houserent')->where($condition)->cache($memcache_key_name, 86400)->count();
$count_2 = $member_query->table('houserent')->where($condition)->cache($memcache_key_name, 86400)->count();
$person_xzl_sp_rent_count = $count_1 + $count_2;

$memcache_key_name = MEMCACHE_PREFIX . 'home_xzl_cz_hz_' . $cityInfo['id'] . '_c';
$condition = array(
    'column_type' => array('IN (2,3)'),
    'is_cooperation' => 2,
    'city_website_id' => $cityInfo['id']
);
$cooperation_xzl_sp_rent_count = $member_query->table('houserent')->where($condition)->cache($memcache_key_name, 86400)->count();*/

//最新二手房
$Sphinx->ResetFilters();
//排序方式
$Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');
$Sphinx->SetFilter('column_type', array(1));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
//$Sphinx->SetFilter('mtype', array(2));
$list_number = 5;
//$key = 'home_sale_list_cityid_mtype_listnumber';
$memcache_key_name = MEMCACHE_PREFIX . 'home_sale_list_' . $cityInfo['id'] . '_2_' . $list_number;
$sale_list = $Cache->get($memcache_key_name);
if (empty($sale_list)) {
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result = $Sphinx->Query('', SPHINX_SEARCH_SALE_INDEX);
    $sale_list = array();
    if ($result['total'] > 0){
        foreach ($result['matches'] as $item){
            $data = array();
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, '二手房');
            $data['id'] = $item['id'];
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['borough_name'] = $item['attrs']['borough_name'];
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] = '/sale/house_' . $item['id'] . $url_fix . '.html';
            $sale_list[] = $data;
        }
        if ($sale_list) {
            $Cache->set($memcache_key_name, $sale_list, 120);
        }
    }
}

//最新出租房
$list_number = 5;
$memcache_key_name = MEMCACHE_PREFIX . 'home_rent_list_' . $cityInfo['id'] . '_2_' . $list_number;
$rent_list = $Cache->get($memcache_key_name);
if (empty($rent_list)) {
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result = $Sphinx->Query('', SPHINX_SEARCH_RENT_INDEX);
    $rent_list = array();
    if ($result['total'] > 0){
        foreach ($result['matches'] as $item){
            $data = array();
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, '租房');
            $data['id'] = $item['id'];
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['unit_type'] = $item['attrs']['unit_type'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] = '/rent/house_' . $item['id'] . $url_fix . '.html';
            $rent_list[] = $data;
        }
        //存储数据到Memcache
        if ($rent_list) {
            $Cache->set($memcache_key_name, $rent_list, 120);
        }
    }
}

//最新写字楼出租
$Sphinx->ResetFilters();
$Sphinx->SetFilter('column_type', array(2));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
//$Sphinx->SetFilter('mtype', array(2));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$list_number = 5;
$memcache_key_name = MEMCACHE_PREFIX . 'home_xzl_list_' . $cityInfo['id'] . '_0_' . $list_number;
$xzlcz_list = $Cache->get($memcache_key_name);
if (empty($xzlcz_list)) {
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result = $Sphinx->Query('', SPHINX_SEARCH_RENT_INDEX);
    $xzlcz_list = array();
    if ($result['total'] > 0){
        foreach ($result['matches'] as $item){
            $data = array();
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, '写字楼出租');
            $data['id'] = $item['id'];
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['unit_type'] = $item['attrs']['unit_type'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] = '/xzlcz/house_' . $item['id'] . $url_fix . '.html';
            $xzlcz_list[] = $data;
        }
        if ($xzlcz_list) {
            $result = $Cache->set($memcache_key_name, $xzlcz_list, 120);
        }
    }
}

//最新商铺出租
$Sphinx->ResetFilters();
$Sphinx->SetFilter('column_type', array(3));
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
//$Sphinx->SetFilter('mtype', array(2));
$Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
$Sphinx->SetFilter('is_delete', array(0));
$Sphinx->SetFilter('is_checked', array(0, 1));
$Sphinx->SetFilter('is_down', array(0));
$list_number = 5;
$memcache_key_name = MEMCACHE_PREFIX . 'home_sp_list_' . $cityInfo['id'] . '_0_' . $list_number;
$spcz_list = $Cache->get($memcache_key_name);
if (empty($spcz_list)) {
    $Sphinx->SetLimits(0, $list_number, $list_number);
    $result = $Sphinx->Query('', SPHINX_SEARCH_RENT_INDEX);
    $spcz_list = array();
    if ($result['total'] > 0){
        foreach ($result['matches'] as $item){
            $data = array();
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, '商铺出租');
            $data['id'] = $item['id'];
            $data['house_room'] = FormatHouseRoomType($item['attrs']['house_room'], 9);
            $data['house_hall'] = FormatHouseRoomType($item['attrs']['house_hall'], 9);
            $data['house_toilet'] = FormatHouseRoomType($item['attrs']['house_toilet'], 9);
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            $data['unit_type'] = $item['attrs']['unit_type'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] = '/spcz/house_' . $item['id'] . $url_fix . '.html';
            $spcz_list[] = $data;
        }
        if ($spcz_list) {
            $result = $Cache->set($memcache_key_name, $spcz_list, 120);
        }
    }
}

//房产资讯
$News = new News($query);
$sphinx_index = 'search_news,search_news_delta';
$news_list = $News->getHotFromSphinx(0, 10, $sphinx_index, true);

//最新经纪人
//$sql = "select mem.id, b.realname, b.avatar, b.company, b.outlet, b.servicearea, b.introduce from `fke_member` as mem left join `fke_broker_info` as b on mem.id=b.id  where b.city_website_id = '{$cityInfo['id']}'  and b.status=1 and mem.user_type = 1 and mem.account_open = 1 order by mem.last_login desc,mem.id desc limit 5";
$sql = "select mem.id, b.realname, b.avatar, b.company, b.outlet, b.outlet_addr, b.servicearea, b.status from `fke_member` as mem left join `fke_broker_info` as b on mem.id=b.id  where b.city_website_id = '{$cityInfo['id']}'  and b.status=1 and mem.user_type = 1 and mem.account_open = 1 order by mem.last_login desc,mem.id desc limit 5";
$brokerList = SaveDataToMemcache($sql, $member_query, false, 90);
foreach ($brokerList as $key=> $item){
    $brokerList[$key]['url'] = '/shop/' . $item['id'];
    $brokerList[$key]['servicearea'] = str_replace('|', ' ', $item['servicearea']);
    //$brokerList[$key]['introduce'] = cn_substr_utf8($item['introduce'], 82).'...';
    //$brokerList[$key]['company'] = cn_substr_utf8($item['company'], 18);

    if ($item['status'] == 0) {
        $item['avatar'] = '';
    }
    $brokerList[$key]['avatar'] = $item['avatar'];
}
//广告内容
//$website_left_ad_1 = GetAdList(106, $query);
//$website_left_ad_2 = GetAdList(107, $query);
//$website_right_ad = GetAdList(108, $query);

$website_center_ad_1 = GetAdList(173, $query);
$website_center_ad_2 = GetAdList(174, $query);
$website_center_ad_3 = GetAdList(175, $query);
$website_center_ad_4 = GetAdList(176, $query);
$website_center_ad_5 = GetAdList(177, $query);
$website_header_ad = GetAdList(182, $query);
//图片住宅出租下部广告
$website_rent_bottom_ad = GetAdList(225, $query);
//图片写字楼出租下部广告
//$website_xzlcz_bottom_ad = GetAdList(226, $query);
//图片商铺出租下部广告
//$website_spzc_bottom_ad = GetAdList(182, $query);

//页面顶部广告位
$website_all_top_ad = GetAdList(228, $query);

$search_item_list = array(
    'sale' => array(
        'caption' => '二手房',
        'url' => '/sale/list_0_0_0_0-0_0_0-0_0_0_0-0_0_0-0_2_0_1_{$keywords}.html'
    ),
    'rent' => array(
        'caption' => '租房',
        'url' => '/rent/list_0_0_0_0-0_0_0-0_0_0_0_0_0_0_2_0_1_{$keywords}.html'
    ),
    'new' => array(
        'caption' => '新房',
        'url' => '/new/list_0_0_0_0-0_0-0_0_0-0_0_0_0-0_0_0-0_2_0_1_{$keywords}.html'
    ),
    'xzlcz' => array(
        'caption' => '写字楼出租',
        'url' => '/xzlcz/list_0_0_0_0-0_0_0-0_0_0_0_0_0_0_2_0_1_{$keywords}.html'
    ),
    'spcz' => array(
        'caption' => '商铺出租',
        'url' => '/spcz/list_0_0_0_0-0_0_0-0_0_0_0_0_0_0_2_0_1_{$keywords}.html'
    ),
    'cwcz' => array(
        'caption' => '车位出租',
        'url' => '/cwcz/list_0_0_0_0-0_0_0-0_0_0_0_0_0_0_2_0_1_{$keywords}.html'
    ),
    'cfcz' => array(
        'caption' => '厂房出租',
        'url' => '/cfcz/list_0_0_0_0-0_0_0-0_0_0_0_0_0_0_2_0_1_{$keywords}.html'
    ),
    'ckcz' => array(
        'caption' => '仓库出租',
        'url' => '/ckcz/list_0_0_0_0-0_0_0-0_0_0_0_0_0_0_2_0_1_{$keywords}.html'
    ),
    'broker' => array(
        'caption' => '房产经纪人',
        'url' => '/broker/list_1_0_0_1_{$keywords}.html'
    )
);
//设置默认搜索Cookie
$home_search_type = empty($_COOKIE['home_search_type']) ? 'sale' : $_COOKIE['home_search_type'];
//区县链接
//$region_list = $query->field('region_id, region_name')->table('region')->where('parent_id = ' . intval($cityInfo['city_id']))->order('sort_order asc, region_id asc')->cache(true, MEMCACHE_MAX_EXPIRETIME)->all();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $page->title;?></title>
    <meta name="description" content="<?php echo $page->description;?>" />
    <meta name="keywords" content="<?php echo $page->keywords;?>" />
    <link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
    <script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/jquery.cookie.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/jquery.lazyload.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/flash_banner_scroll.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/picture_scroll.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.pic img').lazyload({effect: "fadeIn", threshold: 180});
            var HouseBannerScroll = FlashBannerScroll.create({container: '#flash-banner'});
            <?php
            if (count($sale_all_list) > 4) {
            ?>
            var HouseSaleScroll = PictureScroll.create({container: '#scroll-sale-list'});
            <?php
            }
            if (count($rent_all_list) > 4){
             ?>
            var HouseRentScroll = PictureScroll.create({container: '#scroll-rent-list'});
            <?php
            }
            if (count($xzlspcz_all_list) > 4) {
            ?>
            var HouseXzlSpRentScroll = PictureScroll.create({container: '#scroll-xzlsp-rent-list'});
            <?php
            }
            ?>
        });
    </script>
</head>

<body id="body">
<?php require_once(dirname(__FILE__).'/header.php');?>
<div class="search-banner-box">
    <?php
    if ($banner_list) {
    ?>
    <div class="flash-banner" id="flash-banner">
        <div class="pic-list-box">
            <div class="list clear-fix">
                <?php
                foreach ($banner_list as $key => $item) {
                    if ($key >= $banner_max_length) {
                        break;
                    }
                ?>
                <div class="pic-item" style="background-image: url(<?php echo $item['house_thumb'];?>);">
                    <div class="house-info">
                        <div class="house-price"><?php
                            if ($item['house_price'] > 0){
                                echo '<strong>' . $item['house_price'] . '</strong>';
                                if ($item['type'] == 2 || $item['type'] == 3) {
                                    echo '万元';
                                    if ($item['house_price_average']) {
                                        echo '<span class="average">(' . $item['house_price_average'] . '/㎡)</span>';
                                    }
                                } elseif ($item['type'] == 1) {
                                    if (in_array($item['column_type'], array(2, 3))) {
                                        echo '元/㎡·天';
                                    } elseif ($item['column_type'] == 4) {
                                        echo '元/月';
                                    } else {
                                        if ($item['unit_type'] == 1) {
                                            echo '元/月';
                                        }elseif ($item['unit_type'] == 2) {
                                            echo '元/㎡·天';
                                        }elseif ($item['unit_type'] == 3) {
                                            echo '元/天';
                                        }
                                    }
                                }
                            } else {
                                echo '面议';
                            }
                            ?></div>
                        <?php
                        if ($item['column_type'] == 1) {
                            ?>
                            <div class="situation"><?php
                                if ($item['house_room']) {
                                    echo $item['house_room'] . '室 ';
                                }
                                if ($item['house_hall']) {
                                    echo $item['house_hall'] . '厅 ';
                                }
                                if ($item['house_toilet']) {
                                    echo $item['house_toilet'] . '卫 ';
                                }
                                if ($item['house_totalarea']) {
                                    echo ' &nbsp; ' . $item['house_totalarea'] . '㎡ ';
                                }
                                ?></div>
                        <?php
                        }
                        ?>
                        <div class="house-title"><?php echo $item['title'];?></div>
                        <a href="<?php echo $item['url'];?>" target="_blank" class="detail-btn">查看详情</a>
                        <?php
                        if (empty($member_id)) {
                            echo '<a href="javascript:void(0);" onclick="ShowLoginDialog(\'/member/jump.php\');" class="publish-link">我要出现在这里</a>';
                        } else {
                            echo '<a href="/member/jump.php" class="publish-link">我要出现在这里</a>';
                        }
                        ?>
                    </div>
                </div>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="prev-arrow"><div class="icon"></div></div>
        <div class="next-arrow"><div class="icon"></div></div>
    </div>
    <?php
    } else {
        ?>
        <div class="flash-banner" id="flash-banner">
            <div class="pic-list-box">
                <div class="list clear-fix">
                    <div class="pic-item" style="background-image: url(/images/banner/default_banner.jpg);"></div>
                </div>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="main-search">
        <div class="border-wrap">
            <div class="house-type">
                <div class="down-arrow"><img src="/images/down-arrow.png" /></div>
                <div class="selected-type" data-url="<?php echo $search_item_list[$home_search_type]['url'];?>"><?php echo $search_item_list[$home_search_type]['caption'];?></div>
                <div class="house-type-list">
                    <?php
                    foreach ($search_item_list as $key => $item) {
                        echo '<div class="item" data-type="' . $key . '" data-url="' . $item['url'] . '"">' . $item['caption'] . '</div>';
                    }
                    ?>
                </div>
            </div>
            <div class="input-box">
                <div class="search-icon"><img src="/images/search-icon.png" /></div>
                <div class="control-wrap">
                    <input type="text" name="keywords" id="keywords" value="" autocomplete="off" placeholder="请输入关键字（小区名或地址）" />
                </div>
                <div class="search-btn" id="home-search-btn">搜索</div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="mask"></div>
</div>
<?php
$i = 0;
foreach ($default_house_list_key as $house_key => $browsing_count) {
    $i++;
    if ($house_key == 'sale_all_list' && count($sale_all_list) >= 4) {
        ?>
        <div class="section-box home-pic-box<?php echo $i == 1 ? ' sale-pic-box' : '';?>">
            <div class="scroll-list-box" id="scroll-sale-list">
                <div class="pic-list-box">
                    <div class="list clearfix">
                        <?php
                        foreach ($sale_all_list as $item) {
                            ?>
                            <div class="item">
                                <div class="pic"><a href="<?php echo $item['url'];?>" target="_blank"><img alt="<?php echo $item['title'];?>" src="/images/default_picture.jpg" data-original="<?php echo $item['house_thumb'];?>" /></a></div>
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
                <div class="prev-arrow"></div>
                <div class="next-arrow"></div>
            </div>
            <div class="house-list-link"><a href="/sale/" target="_blank">更多二手房房源&gt;&gt;</a></div>
        </div>
        <?php
        continue;
    }

    if ($website_center_ad_1) {
        echo '<div class="section-box"><div class="ad-list">' . $website_center_ad_1 . '</div></div>';
    }

    if ($house_key == 'rent_all_list' && count($rent_all_list) >= 4) {
        ?>
        <div class="section-box home-pic-box">
            <div class="scroll-list-box" id="scroll-rent-list">
                <div class="pic-list-box">
                    <div class="list clearfix">
                        <?php
                        foreach ($rent_all_list as $item) {
                            ?>
                            <div class="item">
                                <div class="pic"><a href="<?php echo $item['url'];?>" target="_blank"><img alt="<?php echo $item['title'];?>" src="/images/default_picture.jpg" data-original="<?php echo $item['house_thumb'];?>" /></a></div>
                                <div class="clear-fix price-house-type-box">
                                    <div class="price"><?php
                                        if ($item['house_price'] > 0){
                                            echo '<span class="num">' . $item['house_price'] . '</span> 元/月';
                                        }else{
                                            echo '面议';
                                        }
                                        ?></div>
                                    <div class="house-type-name">
                                        <?php
                                        if ($item['rent_type'] == 1) {
                                            echo '整租';
                                        } elseif ($item['rent_type'] == 2) {
                                            echo '合租';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="info"><?php
                                    $tmp = array();
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
                <div class="prev-arrow"></div>
                <div class="next-arrow"></div>
            </div>
            <div class="house-list-link"><a href="/rent/" target="_blank">更多出租房源&gt;&gt;</a></div>
        </div>
        <?php
        continue;
    }
    if ($website_rent_bottom_ad) {
        echo '<div class="section-box"><div class="ad-list">' . $website_rent_bottom_ad . '</div></div>';
    }

    if ($house_key == 'new_all_list' && count($new_all_list) >= 4) {
        ?>
        <div class="section-box home-pic-box<?php echo $i == 0 ? ' sale-pic-box' : '';?>">
            <div class="scroll-list-box" id="scroll-sale-list">
                <div class="pic-list-box">
                    <div class="list clearfix">
                        <?php
                        foreach ($new_all_list as $item) {
                            ?>
                            <div class="item">
                                <div class="pic"><a href="<?php echo $item['url'];?>" target="_blank"><img alt="<?php echo $item['title'];?>" src="/images/default_picture.jpg" data-original="<?php echo $item['house_thumb'];?>" /></a></div>
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
                <div class="prev-arrow"></div>
                <div class="next-arrow"></div>
            </div>
            <div class="house-list-link"><a href="/new/" target="_blank">更多新房房源&gt;&gt;</a></div>
        </div>
        <?php
        continue;
    }

    if ($house_key == 'xzlcz_pic_all_list' && count($xzlcz_pic_all_list) >= 4) {
        ?>
        <div class="section-box home-pic-box">
            <div class="scroll-list-box" id="scroll-xzlsp-rent-list">
                <div class="pic-list-box">
                    <div class="list clearfix">
                        <?php
                        foreach ($xzlcz_pic_all_list as $item) {
                            ?>
                            <div class="item">
                                <div class="pic"><a href="<?php echo $item['url'];?>" target="_blank"><img alt="<?php echo $item['title'];?>" src="/images/default_picture.jpg" data-original="<?php echo $item['house_thumb'];?>" /></a></div>
                                <div class="clear-fix price-house-type-box">
                                    <div class="price"><?php
                                        if ($item['house_price'] > 0){
                                            echo '<span class="num">' . $item['house_price'] . '</span> 元/㎡·天';
                                        }else{
                                            echo '面议';
                                        }
                                        ?></div>
                                    <div class="house-type-name">
                                        <?php
                                        if ($item['column_type'] == 2) {
                                            echo '写字楼出租';
                                        } elseif ($item['column_type'] == 3) {
                                            echo '商铺出租';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="info"><?php
                                    $tmp = array();
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
                <div class="prev-arrow"></div>
                <div class="next-arrow"></div>
            </div>
            <div class="house-list-link"><a href="/xzlcz/" target="_blank">更多写字楼出租房源&gt;&gt;</a></div>
        </div>
        <?php
        continue;
    }

    if ($house_key == 'xzlcs_pic_all_list' && count($xzlcs_pic_all_list) >= 4) {
        ?>
        <div class="section-box home-pic-box<?php echo $i == 0 ? ' sale-pic-box' : '';?>">
            <div class="scroll-list-box">
                <div class="pic-list-box">
                    <div class="list clearfix">
                        <?php
                        foreach ($xzlcs_pic_all_list as $item) {
                            ?>
                            <div class="item">
                                <div class="pic"><a href="<?php echo $item['url'];?>" target="_blank"><img alt="<?php echo $item['title'];?>" src="/images/default_picture.jpg" data-original="<?php echo $item['house_thumb'];?>" /></a></div>
                                <div class="clear-fix price-house-type-box">
                                    <div class="price"><?php
                                        if ($item['house_price'] > 0){
                                            echo '<span class="num">' . $item['house_price'] . '</span> 万元';
                                        }else{
                                            echo '面议';
                                        }
                                        ?></div>
                                    <div class="house-type-name">
                                        <?php
                                        if ($item['column_type'] == 2) {
                                            echo '写字楼出售';
                                        } elseif ($item['column_type'] == 3) {
                                            echo '商铺出售';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="info"><?php
                                    $tmp = array();
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
                <div class="prev-arrow"></div>
                <div class="next-arrow"></div>
            </div>
            <div class="house-list-link"><a href="/xzlcs/" target="_blank">更多写字楼出售房源&gt;&gt;</a></div>
        </div>
        <?php
        continue;
    }

    if ($website_center_ad_2) {
        echo '<div class="section-box"><div class="ad-list">' . $website_center_ad_2 . '</div></div>';
    }

    if ($house_key == 'spcz_pic_all_list' && count($spcz_pic_all_list) >= 4) {
        ?>
        <div class="section-box home-pic-box">
            <div class="scroll-list-box" id="scroll-xzlsp-rent-list">
                <div class="pic-list-box">
                    <div class="list clearfix">
                        <?php
                        foreach ($spcz_pic_all_list as $item) {
                            ?>
                            <div class="item">
                                <div class="pic"><a href="<?php echo $item['url'];?>" target="_blank"><img alt="<?php echo $item['title'];?>" src="/images/default_picture.jpg" data-original="<?php echo $item['house_thumb'];?>" /></a></div>
                                <div class="clear-fix price-house-type-box">
                                    <div class="price"><?php
                                        if ($item['house_price'] > 0){
                                            echo '<span class="num">' . $item['house_price'] . '</span> 元/㎡·天';
                                        }else{
                                            echo '面议';
                                        }
                                        ?></div>
                                    <div class="house-type-name">
                                        <?php
                                        if ($item['column_type'] == 2) {
                                            echo '写字楼出租';
                                        } elseif ($item['column_type'] == 3) {
                                            echo '商铺出租';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="info"><?php
                                    $tmp = array();
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
                <div class="prev-arrow"></div>
                <div class="next-arrow"></div>
            </div>
            <div class="house-list-link"><a href="/spcz/" target="_blank">更多商铺房源&gt;&gt;</a></div>
        </div>
        <?php
        continue;
    }

    if ($house_key == 'cfck_pic_all_list' && count($cfck_pic_all_list) >= 4) {
        ?>
        <div class="section-box home-pic-box">
            <div class="scroll-list-box" id="scroll-xzlsp-rent-list">
                <div class="pic-list-box">
                    <div class="list clearfix">
                        <?php
                        foreach ($cfck_pic_all_list as $item) {
                            ?>
                            <div class="item">
                                <div class="pic"><a href="<?php echo $item['url'];?>" target="_blank"><img alt="<?php echo $item['title'];?>" src="/images/default_picture.jpg" data-original="<?php echo $item['house_thumb'];?>" /></a></div>
                                <div class="clear-fix price-house-type-box">
                                    <div class="price"><?php
                                        if ($item['house_price'] > 0){
                                            echo '<span class="num">' . $item['house_price'] . '</span> ';
                                            if ($item['type'] == 1) {
                                                echo '元/㎡·天';
                                            } elseif ($item['type'] == 2) {
                                                echo '万元';
                                            }
                                        }else{
                                            echo '面议';
                                        }
                                        ?></div>
                                    <div class="house-type-name">
                                        <?php echo $item['house_type_title'];?>
                                    </div>
                                </div>
                                <div class="info"><?php
                                    if ($item['house_totalarea']) {
                                        echo $item['house_totalarea'] . '㎡';
                                    }
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
                <div class="prev-arrow"></div>
                <div class="next-arrow"></div>
            </div>
            <div class="house-list-link"><a href="/cfcz/" target="_blank">更多厂房仓库房源&gt;&gt;</a></div>
        </div>
        <?php
        continue;
    }
}
?>
<div class="section-box section-box-home clear-fix">
    <h2><a href="/broker/" target="_blank">房产专业人士</a></h2>
    <div class="list list-broker clearfix">
        <?php
        foreach ($brokerList as $item) {
        ?>
        <a href="<?php echo $item['url'];?>" target="_blank">
            <div class="item">
                <div class="pic">
                    <?php
                    if ($item['avatar']) {
                        ?>
                        <div class="image" style="background-image: url(<?php echo GetBrokerFace($item['avatar']);?>);"></div>
                        <?php
                    } elseif ($item['realname']) {
                        ?>
                        <div class="image first-name-box"><div class="text"><?php echo mb_substr($item['realname'], 0, 1, 'utf-8');?></div></div>
                        <?php
                    } else {
                        ?>
                        <div class="image"></div>
                    <?php
                    }
                    ?>
                </div>
                <div class="name"><?php echo $item['realname'];?></div>
                <div class="info"><?php echo $item['company'];?> - <?php echo $item['outlet'];?></div>
                <div class="info"><?php echo str_replace('|', '　', $item['servicearea']);?></div>
            </div>
        </a>
        <?php
        }
        ?>
    </div>
</div>
<?php
if ($website_center_ad_3) {
    echo '<div class="section-box"><div class="ad-list">' . $website_center_ad_3 . '</div></div>';
}
?>
<div class="section-box section-box-home clear-fix">
    <div class="column">
        <h2><a href="/sale/" target="_blank">最新二手房</a></h2>
        <div class="list list-text clearfix">
            <?php
            foreach ($sale_list as $item) {
                ?>
                <div class="item">
                    <div class="title"><a href="<?php echo $item['url'];?>" target="_blank"><?php echo $item['title'];?></a></div>
                    <div class="info"><?php
                        if ($item['house_price'] > 0){
                            echo $item['house_price'] . '万元 ';
                        }else{
                            echo '面议 ';
                        }
                        ?><?php
                        if ($item['house_room']) {
                            echo $item['house_room'] . '室 ';
                        }
                        if ($item['house_hall']) {
                            echo $item['house_hall'] . '厅 ';
                        }
                        if ($item['house_toilet']) {
                            echo $item['house_toilet'] . '卫 ';
                        }
                        if ($item['house_totalarea']) {
                            echo $item['house_totalarea'] . '㎡ ';
                        }
                        echo $item['house_type'] . ' ';
                        if ($item['borough_name']) {
                            echo $item['borough_name'] . ' ';
                        }
                        echo '<span class="uptime">' . $item['updated'] . '</span>';
                        ?></div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="column">
        <h2><a href="/rent/" target="_blank">最新出租房</a></h2>
        <div class="list list-text clearfix">
            <?php
            foreach ($rent_list as $item) {
                ?>
                <div class="item">
                    <div class="title"><a href="<?php echo $item['url'];?>" target="_blank"><?php echo $item['title'];?></a></div>
                    <div class="info"><?php
                        if ($item['house_price'] > 0){
                            echo $item['house_price'] . '元/月 ';
                        }else{
                            echo '面议 ';
                        }
                        if ($item['house_room']) {
                            echo $item['house_room'] . '室 ';
                        }
                        if ($item['house_hall']) {
                            echo $item['house_hall'] . '厅 ';
                        }
                        if ($item['house_toilet']) {
                            echo $item['house_toilet'] . '卫 ';
                        }
                        if ($item['house_totalarea']) {
                            echo $item['house_totalarea'] . '㎡ ';
                        }
                        echo $item['house_type'] . ' ';
                        if ($item['borough_name']) {
                            echo $item['borough_name'] . ' ';
                        }
                        echo '<span class="uptime">' . $item['updated'] . '</span>';
                        ?></div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php
if ($website_center_ad_4) {
    echo '<div class="section-box"><div class="ad-list">' . $website_center_ad_4 . '</div></div>';
}
?>
<div class="section-box section-box-home clear-fix">
    <div class="column">
        <h2><a href="/xzlcz/" target="_blank">最新写字楼出租</a></h2>
        <div class="list list-text clearfix">
            <?php
            foreach ($xzlcz_list as $item) {
                ?>
                <div class="item">
                    <div class="title"><a href="<?php echo $item['url'];?>" target="_blank"><?php echo $item['title'];?></a></div>
                    <div class="info"><?php
                        if ($item['house_price'] > 0){
                            echo $item['house_price'] . '元/㎡·天 ';
                        }else{
                            echo '面议 ';
                        }
                        if ($item['house_room']) {
                            echo $item['house_room'] . '室 ';
                        }
                        if ($item['house_hall']) {
                            echo $item['house_hall'] . '厅 ';
                        }
                        if ($item['house_toilet']) {
                            echo $item['house_toilet'] . '卫 ';
                        }
                        if ($item['house_totalarea']) {
                            echo $item['house_totalarea'] . '㎡ ';
                        }
                        echo $item['house_type'] . ' ';
                        if ($item['borough_name']) {
                            echo $item['borough_name'] . ' ';
                        }
                        echo '<span class="uptime">' . $item['updated'] . '</span>';
                        ?></div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="column">
        <h2><a href="/spcz/" target="_blank">最新商铺出租</a></h2>
        <div class="list list-text clearfix">
            <?php
            foreach ($spcz_list as $item) {
                ?>
                <div class="item">
                    <div class="title"><a href="<?php echo $item['url'];?>" target="_blank"><?php echo $item['title'];?></a></div>
                    <div class="info"><?php
                        if ($item['house_price'] > 0){
                            echo $item['house_price'] . '元/㎡·天 ';
                        }else{
                            echo '面议 ';
                        }
                        if ($item['house_room']) {
                            echo $item['house_room'] . '室 ';
                        }
                        if ($item['house_hall']) {
                            echo $item['house_hall'] . '厅 ';
                        }
                        if ($item['house_toilet']) {
                            echo $item['house_toilet'] . '卫 ';
                        }
                        if ($item['house_totalarea']) {
                            echo $item['house_totalarea'] . '㎡ ';
                        }
                        echo $item['house_type'] . ' ';
                        if ($item['borough_name']) {
                            echo $item['borough_name'] . ' ';
                        }
                        echo '<span class="uptime">' . $item['updated'] . '</span>';
                        ?></div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php
if ($website_center_ad_5) {
    echo '<div class="section-box"><div class="ad-list">' . $website_center_ad_5 . '</div></div>';
}
?>
<div class="section-box section-box-home clear-fix">
    <h2><a href="//www.<?php echo $cfg['page']['basehost'];?>/news/" target="_blank">最新房产资讯</a></h2>
    <div class="column-box clear-fix">
        <div class="column">
            <div class="list list-text clearfix">
                <?php
                $new_count = count($news_list) > 5 ? 5 : count($news_list);
                for ($i = 0; $i < $new_count; $i++) {
                    ?>
                    <div class="item">
                        <div class="title"><a href="//www.<?php echo $cfg['page']['basehost'];?>/news/<?php echo $news_list[$i]['url'];?>" target="_blank"><?php echo $news_list[$i]['title'];?></a></div>
                        <div class="info clearfix"><span class="text"><?php echo $news_list[$i]['description'];?></span><div class="time"><?php echo MyDate('m-d', $news_list[$i]['add_time']);?></div></div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="column">
            <div class="list list-text clearfix">
                <?php
                $new_count = count($news_list);
                for ($i = 5; $i < $new_count; $i++) {
                    ?>
                    <div class="item">
                        <div class="title"><a href="//www.<?php echo $cfg['page']['basehost'];?>/news/<?php echo $news_list[$i]['url'];?>" target="_blank"><?php echo $news_list[$i]['title'];?></a></div>
                        <div class="info clearfix"><span class="text"><?php echo $news_list[$i]['description'];?></span><div class="time"><?php echo MyDate('m-d', $news_list[$i]['add_time']);?></div></div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php require_once(dirname(__FILE__).'/footer.php');?>
</body>
</html>