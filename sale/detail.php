<?php
require_once(dirname(__FILE__) . '/path.inc.php');
$id = intval($_GET['id']);
$member_db = trim($_GET['member_db']);
if ($id){
    $memcache_house_key = 'house_sale_'.$id;
    $memcache_house_update_key = 'house_sale_update_'.$id;
    $memcache_house_pic_key = 'house_sale_pic_'.$id;
    if ($member_db == 'x') {
        $memcache_house_key = MEMCACHE_PREFIX . 'member_' . $memcache_house_key;
        $memcache_house_update_key = MEMCACHE_PREFIX . 'member_' . $memcache_house_update_key;
        $memcache_house_pic_key = MEMCACHE_PREFIX . 'member_' . $memcache_house_pic_key;
        $House = new HouseSell($member_query);
    } elseif ($member_db == 4) {
        $memcache_house_key = MEMCACHE_ZFY_PREFIX . 'member_' . $memcache_house_key;
        $memcache_house_update_key = MEMCACHE_ZFY_PREFIX . 'member_' . $memcache_house_update_key;
        $memcache_house_pic_key = MEMCACHE_ZFY_PREFIX . 'member_' . $memcache_house_pic_key;
        //创建第三方会员数据库连接
        $member_db_config = GetConfig('member_db_' . $member_db);
        if (empty($member_db_config)) {
            header("http/1.1 404 not found");
            header("status: 404 not found");
            require_once(dirname(dirname(__FILE__)) . '/404.php');
            exit();
        }
        $house_query = new DbQueryForMysql($member_db_config);
        $House = new HouseSell($house_query);
    } else {
        $House = new HouseSell($query);
    }
}
$condition = "house.id = '$id' AND is_checked in (0,1) AND is_delete = 0 AND is_down = 0";
$dataInfo = $House->field('house.*, extend.*')->table($House->tName, 'house')->join($House->db_prefix . $House->tNameExtend . ' AS extend ON house.id = extend.house_id', 'LEFT')->where($condition)->cache($memcache_house_key, MEMCACHE_MAX_EXPIRETIME)->one();
if(!$dataInfo || $dataInfo['city_website_id'] != $cityInfo['id'] || $dataInfo['house_status'] == 1){
    header("http/1.1 404 not found");
    header("status: 404 not found");
    require_once(dirname(dirname(__FILE__)) . '/404.php');
    exit();
}

//取得最新刷新时间
$house_info = $House->field('id, updated')->table($House->tName)->where('id = ' . $id)->cache($memcache_house_update_key, 30)->one();
if ($house_info['updated']) {
    $dataInfo['updated'] = $house_info['updated'];
}

if ($dataInfo['mid'] > 0) {
    $image_host = '';
} else {
    $image_host = IMG_HOST;
}
$cityarea_option = get_region_enum($cityInfo['city_id']);
$parent_id_array = array();
foreach ($cityarea_option as $value){
    $parent_id_array[] = $value['region_id'];
}
$cityarea2_list = get_region_enum($parent_id_array);
//房源特色
$house_feature_option = Dd::getArray('house_feature');
//房屋类型
$houseTypeLists = Dd::getArray('house_type');
//房屋产权
$belong_option = Dd::getArray('belong');

$dataInfo['house_room'] = FormatHouseRoomType($dataInfo['house_room'], 9);
$dataInfo['house_hall'] = FormatHouseRoomType($dataInfo['house_hall'], 9);
$dataInfo['house_toilet'] = FormatHouseRoomType($dataInfo['house_toilet'], 9);
$dataInfo['house_veranda'] = FormatHouseRoomType($dataInfo['house_veranda'], 9);
$dataInfo['house_price'] = FormatHousePrice($dataInfo['house_price']);
$dataInfo['house_type'] = $houseTypeLists[$dataInfo['house_type']];
$dataInfo['belong'] = $belong_option[$dataInfo['belong']];
$dataInfo['updatetime'] = MyDate('Y-m-d　H:i',$dataInfo['updated']);
$dataInfo['updated'] = time2Units(time()-$dataInfo['updated']);
$dataInfo['cityarea_name'] = $cityarea_option[$dataInfo['cityarea_id']]['region_name'];
if (!empty($dataInfo['cityarea2_id'])) {
    $dataInfo['cityarea2_name'] = $cityarea2_list[$dataInfo['cityarea2_id']]['region_name'];
}
$dataInfo['house_toward'] = Dd::getCaption('house_toward',$dataInfo['house_toward']);
if($dataInfo['house_feature']){
	$dataInfo['house_feature'] =  Dd::getCaption('rent_feature',$dataInfo['house_feature']);
}
$DdObj = new Dd($query);
if ($dataInfo['column_type'] == 1) {
    $dataInfo['bright_spot'] =  GetDictList($dataInfo['bright_spot'], $DdObj->getItemArray(27, true, MEMCACHE_MAX_EXPIRETIME));
}
if (in_array($dataInfo['column_type'], array(16, 17))) {
    $dataInfo['house_support'] =  GetStoreHouseSupportIconList($dataInfo['house_support'], $DdObj->getItemArray(28, true, MEMCACHE_MAX_EXPIRETIME));
}

$dataInfo['house_fitment'] =  Dd::getCaption('house_fitment',$dataInfo['house_fitment']);

$dataInfo['house_deposit'] =  Dd::getCaption('rent_deposittype',$dataInfo['house_deposit']);
$dataInfo['borough_name'] = strip_tags($dataInfo['borough_name']);
$dataInfo['article_title'] = get_house_title($dataInfo, $cityarea_option, $cityarea2_list, $column['house_title']);
if (in_array($dataInfo['mtype'], array(1, 3, 4)) && $dataInfo['is_cooperation'] == 1) {
    $dataInfo['article_title'] .= '(合作)';
}

$condition = "house_id = '{$id}' and is_checked in (0,1)";
$picList = $House->field('id, pic_url, pic_desc')->table($House->tNamePic)->where($condition)->order('order_sort ASC, id ASC')->cache($memcache_house_pic_key, MEMCACHE_MAX_EXPIRETIME)->all();
if ($picList && $picList[0]['is_main'] == 0) {
    $tmp_pic_list = array();
    foreach ($picList as $key => $item) {
        if ($item['pic_url'] == $dataInfo['house_thumb']) {
            array_unshift($tmp_pic_list, $item);
        } else {
            $tmp_pic_list[] = $item;
        }
    }
    $picList = $tmp_pic_list;
    unset($tmp_pic_list);
}

//浏览过的房源
$history_list = add_history_list($_GET['column'] . 'house_sale', $dataInfo, $member_db);

//增加当前栏目浏览数计数
AddBrowserCount($_GET['column']);

//添加到浏览历史记录中
if ($member_id) {
    $BrowsingHistory = new BrowsingHistory($member_query);
    $db_index = $member_db == 'x' ? WEBHOSTID : intval($member_db);
    $BrowsingHistory->Add($member_id, $db_index, 2, $dataInfo);
}

$page->title = '【'.$dataInfo['article_title'].'】'.$page->titlec.$cityInfo['city_name'];
$page->description = $dataInfo['house_address'].$dataInfo['borough_name'].$column['house_title'].'出售'.$dataInfo['cityarea_name'].$dataInfo['cityarea2_name'].$column['house_title'].$dataInfo['house_title'].$page->titlec.$cityInfo['city_name'];
if ($dataInfo['mtype'] == 2 && $column['house_title'] == '二手房') {
    $middle_title = ',个人'.$column['house_title'].'出售';
}
if ($column['house_title'] == '商铺出售') {
    $middle_title = ','.$cityInfo['city_name'].'商铺转让';
}
$page->keywords = $dataInfo['cityarea_name'].$column['house_title'].','.$dataInfo['cityarea2_name'].$column['house_title'].','.$cityInfo['city_name'].$column['house_title'].$middle_title;

//电话图片
if ($dataInfo['owner_phone_pic']) {
    $dataInfo['owner_phone_pic'] = GetPictureUrl($dataInfo['owner_phone_pic'], 0, 0);
}

if (!empty($dataInfo['owner_phone'])){
    if ($dataInfo['hide_phone'] == 1) {
        $dataInfo['owner_phone'] = substr($dataInfo['owner_phone'], 0, 3) . '<span class="mobile-split">****</span>' . substr($dataInfo['owner_phone'], 7);
    } else {
        $dataInfo['owner_phone'] = substr($dataInfo['owner_phone'], 0, 3) . '<span class="mobile-split">' . substr($dataInfo['owner_phone'], 3, 4) . '</span>' . substr($dataInfo['owner_phone'], 7);
    }
}

//增加统计
// $sql = "update fke_housesell_extend set click_num = click_num + 1 where house_id='$id'";
// $query->execute($sql);
//广告位
$website_detail_ad = GetAdList(111, $query);
$website_right_ad = GetAdList(112, $query);
$info_bottom_ad = GetAdList(120, $query);
$website_right_ad_1 = GetAdList(121, $query);
$pic_box_bottom_ad = GetAdList(180, $query);
$website_same_house_bottom_ad = GetAdList(179, $query);
$website_right_hot_search_ad = GetAdList(232, $query);
$website_right_news_ad = GetAdList(233, $query);

//相近价位的房源
$same_house_key = MEMCACHE_PREFIX . 'sl_same_' . $cityInfo['id'] . '_' . $dataInfo['column_type'] . '_' . 2 . '_' . $dataInfo['cityarea_id'] . '_' . $dataInfo['cityarea2_id'] . '_' . $dataInfo['house_price'] . '_' . $cfg['same_list_number'];
$same_house = $Cache->get($same_house_key);
if (empty($same_house)) {
    $Sphinx = Sphinx::getInstance();
    $Sphinx->ResetFilters();
    $prcent_10 = $dataInfo['house_price'] * 0.1;
    $start_price = $dataInfo['house_price'] - $prcent_10 < 0 ? 0 : $dataInfo['house_price'] - $prcent_10;
    $end_price = $dataInfo['house_price'] + $prcent_10;
    $Sphinx->SetFilter('column_type', array($dataInfo['column_type']));
    $Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
    $Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
    $Sphinx->SetFilter('is_delete', array(0));
    $Sphinx->SetFilter('is_checked', array(0, 1));
    $Sphinx->SetFilter('is_down', array(0));
    if ($dataInfo['cityarea2_id']){
        $Sphinx->SetFilter('cityarea2_id', array($dataInfo['cityarea2_id']));
    }elseif ($dataInfo['cityarea_id']){
        $Sphinx->SetFilter('cityarea_id', array($dataInfo['cityarea_id']));
    }
    $Sphinx->SetFilterRange('house_price', $start_price, $end_price);
    $Sphinx->SetFilter('filter_id', array($id), true); //去除本条记录
    $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');	//按更新时间降序
    $Sphinx->SetLimits(0, $cfg['same_list_number'], $cfg['same_list_number']);
    $result = $Sphinx->Query('', SPHINX_SEARCH_SALE_INDEX);

    $same_house = array();
    if ($result['total'] > 0) {
        $now_time = time();
        foreach ($result['matches'] as $item){
            $data = array();
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column['house_title']);
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
            $data['house_toward'] = intval($item['attrs']['house_toward']);
            $data['house_fitment'] = intval($item['attrs']['house_fitment']);
            $data['cityarea_id'] = $item['attrs']['cityarea_id'];
            $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            if ($data['house_totalarea'] > 0 && $data['house_price'] > 0) {
                $data['house_price_average'] = round($data['house_price'] * 10000 / $data['house_totalarea'], 2);
            } else {
                $data['house_price_average'] = 0;
            }
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] = 'house_' . $item['id'] . $url_fix . '.html';
            $data['owner_name'] = $item['attrs']['owner_name'];

            if ($data['mid'] && in_array($data['user_type'], array(1, 3, 4)) && $item['attrs']['member_db_index'] == MEMBER_DB_INDEX) {
                //取得经纪人公司门店
                $broker_data = $member_query->table('broker_info')->field('id, realname, company')->where('id = ' . $data['mid'])->cache('member_house_list_' . $data['mid'], MEMCACHE_MAX_EXPIRETIME)->one();
                if (!empty($broker_data['realname'])) {
                    $data['owner_name'] = $broker_data['realname'];
                    $data['company'] = $broker_data['company'];
                }
            }

            $data['is_cooperation'] = intval($item['attrs']['is_cooperation']);
            if ($data['column_type'] == 1) {
                $data['bright_spot'] = $item['attrs']['bright_spot'];
                $data['parking_lot'] = intval($item['attrs']['parking_lot']);
            }
            $same_house[] = $data;
        }
        //存储数据到Memcache
        if (!empty($same_house)) {
            $Cache->set($same_house_key, $same_house, MEMCACHE_EXPIRETIME);
        }
    }
}
$same_house_count = count($same_house);
/*$same_house_count = intval($same_house_count / 4) * 4;
$tmp_same_house = array();
for ($i = 0; $i < $same_house_count; $i++) {
    $tmp_same_house[] = $same_house[$i];
}
$same_house = $tmp_same_house;*/

//推荐房源
$recommend_house_key = MEMCACHE_PREFIX . 'sl_rec_' . $dataInfo['city_website_id'] . '_' . $dataInfo['column_type'];
$recommend_house = $Cache->get($recommend_house_key);
if (empty($recommend_house)) {
    $Sphinx = Sphinx::getInstance();
    $Sphinx->ResetFilters();
    $Sphinx->SetFilter('column_type', array($dataInfo['column_type']));
    $Sphinx->SetFilter('city_website_id', array($dataInfo['city_website_id']));
    $Sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
    $Sphinx->SetFilter('is_delete', array(0));
    $Sphinx->SetFilter('is_checked', array(0, 1));
    $Sphinx->SetFilter('is_down', array(0));
    $Sphinx->SetFilterRange('house_thumb_length', 10, 300);
    $Sphinx->SetFilter('filter_id', array($id), true); //去除本条记录
    $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');	//按更新时间降序
    $Sphinx->SetLimits(0, 4, 4);
    $result = $Sphinx->Query('', SPHINX_SEARCH_SALE_INDEX);

    $recommend_house = array();
    if ($result['total'] > 0) {
        $now_time = time();
        foreach ($result['matches'] as $item){
            $data = array();
            $data['title'] = get_house_title($item['attrs'], $cityarea_option, $cityarea2_list, $column['house_title']);
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
            $data['house_toward'] = intval($item['attrs']['house_toward']);
            $data['house_fitment'] = intval($item['attrs']['house_fitment']);
            $data['cityarea_id'] = $item['attrs']['cityarea_id'];
            $data['cityarea2_id'] = $item['attrs']['cityarea2_id'];
            $data['borough_name'] = $item['attrs']['borough_name'];
            $data['updated'] = time2Units($now_time - $item['attrs']['updated']);
            $data['house_totalarea'] = $item['attrs']['house_totalarea'];
            $data['house_price'] = FormatHousePrice($item['attrs']['house_price']);
            if ($data['house_totalarea'] > 0 && $data['house_price'] > 0) {
                $data['house_price_average'] = round($data['house_price'] * 10000 / $data['house_totalarea'], 2);
            } else {
                $data['house_price_average'] = 0;
            }
            if ($item['attrs']['member_db_index'] == 0) {
                $url_fix = '';
            } else {
                $url_fix = MEMBER_DB_INDEX == $item['attrs']['member_db_index'] ? 'x' : '_' . $item['attrs']['member_db_index'];
            }
            $data['url'] = 'house_' . $item['id'] . $url_fix . '.html';
            $data['owner_name'] = $item['attrs']['owner_name'];

            if ($data['mid'] && in_array($data['user_type'], array(1, 3, 4)) && $item['attrs']['member_db_index'] == MEMBER_DB_INDEX) {
                //取得经纪人公司门店
                $broker_data = $member_query->table('broker_info')->field('id, realname, company')->where('id = ' . $data['mid'])->cache('member_house_list_' . $data['mid'], MEMCACHE_MAX_EXPIRETIME)->one();
                if (!empty($broker_data['realname'])) {
                    $data['owner_name'] = $broker_data['realname'];
                    $data['company'] = $broker_data['company'];
                }
            }

            $data['is_cooperation'] = intval($item['attrs']['is_cooperation']);
            if ($data['column_type'] == 1) {
                $data['bright_spot'] = $item['attrs']['bright_spot'];
                $data['parking_lot'] = intval($item['attrs']['parking_lot']);
            }
            $recommend_house[] = $data;
        }
        //存储数据到Memcache
        if (!empty($recommend_house)) {
            $Cache->set($recommend_house_key, $recommend_house, MEMCACHE_EXPIRETIME);
        }
    }
}
$recommend_house_count = count($recommend_house);
$recommend_house_count = intval($recommend_house_count / 4) * 4;
$tmp_house = array();
for ($i = 0; $i < $recommend_house_count; $i++) {
    $tmp_house[] = $recommend_house[$i];
}
$recommend_house = $tmp_house;

/*//价格相同房源
$mtype = 0;
$same_price_list_key = 'stslist_' . $cityInfo['id'] . '_' . $column['column_type'] . '_' . $mtype . '_' . $dataInfo['cityarea_id'] . '_' . $dataInfo['cityarea2_id'] . '_' . $dataInfo['house_price'];
$same_price_house = $memcache->get($same_price_list_key);
if (empty($same_price_house)) {
    $sphinx->ResetFilters();
    $sphinx->SetFilter('column_type', array($column['column_type']));
    $sphinx->SetFilter('city_website_id', array($cityInfo['id']));
    $sphinx->SetFilter('house_status', array(0, 2, 3, 4, 5));
    if ($dataInfo['cityarea2_id']){
        $sphinx->SetFilter('cityarea2_id', array($dataInfo['cityarea2_id']));
    }elseif ($dataInfo['cityarea_id']){
        $sphinx->SetFilter('cityarea_id', array($dataInfo['cityarea_id']));
    }
    $sphinx->SetFilter('house_price', array($dataInfo['house_price']));
    $sphinx->SetFilter('filter_id', array($id), true); //去除本条记录
    $sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');	//按更新时间降序
    $sphinx->SetLimits(0, 20, 20);
    $result = $sphinx->Query('', 'search_house_sale,search_house_sale_delta,member_house_sale,member_house_sale_delta');
    if ($result['total'] > 0){
        $house_id = array();
        $member_house_id = array();
        foreach ($result['matches'] as $val){
            if ($value['is_use_member_db'] == 1) {
                $member_house_id[] = $val['id'];
            } else {
                $house_id[] = $val['id'];
            }
        }
        $house_ids = implode(',', $house_id);
        if ($house_ids){
            $sql = "select h.id, h.house_room, h.house_price, h.created, h.updated, h.cityarea_id, h.cityarea2_id, h.house_totalarea, h.mtype, e.house_id, e.house_hall, e.house_toilet, e.house_title, e.house_address, e.borough_name, e.owner_phone from fke_housesell as h left join fke_housesell_extend as e on h.id = e.house_id where h.id in ($house_ids) and e.house_id in ($house_ids)";
            $dataList = $query->select($sql);
            $house_list = array();
            foreach ($dataList as $val) {
                $house_list[$val['id']] = $val;
            }
        }
        $member_house_ids = implode(',', $member_house_id);
        if ($member_house_ids) {
            $sql = "select h.id, h.house_room, h.house_price, h.created, h.updated, h.cityarea_id, h.cityarea2_id, h.house_totalarea, h.mtype, e.house_id, e.house_hall, e.house_toilet, e.house_title, e.house_address, e.borough_name, e.owner_phone from fke_housesell as h left join fke_housesell_extend as e on h.id = e.house_id where h.id in ($member_house_ids) and e.house_id in ($member_house_ids)";
            $dataList = $member_query->select($sql);
            $member_house_list = array();
            foreach ($dataList as $val) {
                $member_house_list[$val['id']] = $val;
            }
        }
        $same_price_house = array();
        foreach ($result['matches'] as $item) {
            if ($value['is_use_member_db'] == 1) {
                if($member_house_list[$item['id']]){
                    //处理数据
                    $member_house_list[$item['id']]['url'] = 'house_' . $item['id'] . 'x.html';
                    $member_house_list[$item['id']]['updated'] = time2Units(time()-$item['attrs']['updated']);
                    $member_house_list[$item['id']]['title'] = get_house_title($member_house_list[$item['id']], $cityarea_option, $cityarea2_list, $column['house_title']);
                    unset($member_house_list[$item['id']]['created']);
                    unset($member_house_list[$item['id']]['cityarea_id']);
                    unset($member_house_list[$item['id']]['cityarea2_id']);
                    unset($member_house_list[$item['id']]['mtype']);
                    unset($member_house_list[$item['id']]['house_totalarea']);
                    unset($member_house_list[$item['id']]['house_hall']);
                    unset($member_house_list[$item['id']]['house_toilet']);
                    unset($member_house_list[$item['id']]['house_title']);
                    unset($member_house_list[$item['id']]['house_address']);
                    unset($member_house_list[$item['id']]['borough_name']);
                    unset($member_house_list[$item['id']]['owner_phone']);
                    unset($member_house_list[$item['id']]['house_id']);
                    $same_price_house[] = $member_house_list[$item['id']];
                }
            } else {
                if($house_list[$item['id']]){
                    //处理数据
                    $house_list[$item['id']]['url'] = 'house_' . $item['id'] . '.html';
                    $house_list[$item['id']]['updated'] = time2Units(time()-$item['attrs']['updated']);
                    $house_list[$item['id']]['title'] = get_house_title($house_list[$item['id']], $cityarea_option, $cityarea2_list, $column['house_title']);
                    unset($house_list[$item['id']]['created']);
                    unset($house_list[$item['id']]['cityarea_id']);
                    unset($house_list[$item['id']]['cityarea2_id']);
                    unset($house_list[$item['id']]['mtype']);
                    unset($house_list[$item['id']]['house_totalarea']);
                    unset($house_list[$item['id']]['house_hall']);
                    unset($house_list[$item['id']]['house_toilet']);
                    unset($house_list[$item['id']]['house_title']);
                    unset($house_list[$item['id']]['house_address']);
                    unset($house_list[$item['id']]['borough_name']);
                    unset($house_list[$item['id']]['owner_phone']);
                    unset($house_list[$item['id']]['house_id']);
                    $same_price_house[] = $house_list[$item['id']];
                }
            }
        }

        //存储到MemCache
        if ($same_price_house) {
            $result = $memcache->set($same_price_list_key, $same_price_house, MEMCACHE_COMPRESS, MEMCACHE_EXPIRETIME);
        }
    }
}*/

$broker_list = array();
if (empty($member_db) && $dataInfo['source_id'] < 100 && $dataInfo['source_id'] != 21 && $dataInfo['mtype'] == 1 && !in_array($dataInfo['house_status'], array(1, 5))) {
    $Cache = Cache::getInstance();
    $cache_key = MEMCACHE_PREFIX . 'b_s_l_' . $dataInfo['id'];
    $broker_list = $Cache->get($cache_key);

    if (empty($broker_list)) {
        $condition_sort = array();
        if ($dataInfo['cityarea2_id']) {
            $condition_sort[] = 'cityarea2_id';
        }
        if ($dataInfo['cityarea_id']) {
            $condition_sort[] = 'cityarea_id';
        }

        if ($dataInfo['city_website_id']) {
            $condition_sort[] = 'city_website_id';
        }

        $fields = 'm.id, m.user_type, m.user_type_sub, m.user_type_custom, m.account_open, b.realname, b.city_website_id, b.avatar, b.servicearea, b.company, b.outlet, b.mobile, b.status';
        foreach ($condition_sort as $value) {
            /*$condition = array(
                'm.account_open = 1',
                'b.status = 1',
                'm.user_type = 1',
                'b.' . $value . ' = ' . $dataInfo[$value],
                'm.user_level_expire_time > ' . time()
            );
            $where = implode(' AND ', $condition);
            $broker_list = $member_query->table('member', 'm')->join($member_query->db_prefix . 'broker_info AS b ON m.id = b.id', 'LEFT')->field($fields)->where($where)->order('RAND()')->limit(3)->all();*/

            if (empty($broker_list)) {
                //普通经纪人，随机排序
                $condition = array(
                    'm.account_open = 1',
                    'b.status = 1',
                    'm.user_type = 1',
                    'b.' . $value . ' = ' . $dataInfo[$value]
                );
                $where = implode(' AND ', $condition);
                $broker_list = $member_query->table('member', 'm')->join($member_query->db_prefix . 'broker_info AS b ON m.id = b.id', 'LEFT')->field($fields)->where($where)->order('RAND()')->limit(3)->all();
                if ($broker_list) {
                    break;
                }
            }

            if ($broker_list) {
                break;
            }
        }

        if (empty($broker_list)) {
            foreach ($condition_sort as $value) {
                /*$condition = array(
                    'm.account_open = 1',
                    'b.status = 0',
                    'm.user_type = 1',
                    'b.' . $value . ' = ' . $dataInfo[$value],
                    'm.user_level_expire_time > ' . time()
                );
                $where = implode(' AND ', $condition);
                $broker_list = $member_query->table('member', 'm')->join($member_query->db_prefix . 'broker_info AS b ON m.id = b.id', 'LEFT')->field($fields)->where($where)->order('RAND()')->limit(3)->all();*/

                if (empty($broker_list)) {
                    //普通经纪人，随机排序
                    $condition = array(
                        'm.account_open = 1',
                        'b.status = 0',
                        'm.user_type = 1',
                        'b.' . $value . ' = ' . $dataInfo[$value]
                    );
                    $where = implode(' AND ', $condition);
                    $broker_list = $member_query->table('member', 'm')->join($member_query->db_prefix . 'broker_info AS b ON m.id = b.id', 'LEFT')->field($fields)->where($where)->order('RAND()')->limit(3)->all();
                    if ($broker_list) {
                        break;
                    }
                }

                if ($broker_list) {
                    break;
                }
            }
        }

        if ($broker_list) {
            $Cache->set($cache_key, $broker_list, 0);
        }
    }

    foreach ($broker_list as $key => $item) {
        //取得经纪人店铺链接地址
        $member_city_info = get_city_info_by_id($item['city_website_id']);
        if (!empty($member_city_info)) {
            $item['shop_url'] = '//' . $member_city_info['url_name'] . '.' . $cfg['page']['basehost'] . '/shop/' . $item['id'];
        } else {
            $item['shop_url'] = '';
        }

        if ($item['mobile']) {
            $item['format_phone'] = substr($item['mobile'], 0, 3) . '<span class="mobile-split">' . substr($item['mobile'], 3, 4) . '</span>' . substr($item['mobile'], 7);
        }

        /*if (in_array($item['user_type'], array(1, 3, 4))) {
            $item['servicearea'] = str_replace('|', ' &nbsp; ', $item['servicearea']);
        }*/

        if ($item['status'] == 0) {
            $item['avatar'] = '';
        }
        //$item['avatar'] = GetBrokerFace($item['avatar']);

        $item['qrcode'] = '/upfile/qrcode.jpg?data=' . urlencode($http_type . 'm.' . $cfg['page']['basehost'] . '/' . $cityInfo['url_name'] . '/shop/' . $item['id'] . '?call=1');

        $broker_list[$key] = $item;
    }
} elseif ($dataInfo['mid'] && $member_db == 'x') {
    //判断经纪人和非中介机构是否已认证
    $condition = 'm.id = ' . $dataInfo['mid'] . ' AND m.account_open = 1';
    $fields = 'm.id, m.user_type, m.user_type_sub, m.user_type_custom, m.account_open, b.realname, b.city_website_id, b.avatar, b.servicearea, b.company, b.outlet, b.mobile, b.status';
    $broker_info = $member_query->table('member', 'm')->join($member_query->db_prefix . 'broker_info AS b ON m.id = b.id', 'LEFT')->field($fields)->where($condition)->cache(MEMCACHE_PREFIX . 'member_info_' . $dataInfo['mid'], MEMCACHE_EXPIRETIME)->one();
    if ($broker_info) {
        //取得经纪人店铺链接地址
        $member_city_info = get_city_info_by_id($broker_info['city_website_id']);
        if (!empty($member_city_info)) {
            $broker_info['shop_url'] = '//' . $member_city_info['url_name'] . '.' . $cfg['page']['basehost'] . '/shop/' . $dataInfo['mid'];
        } else {
            $broker_info['shop_url'] = '';
        }

        if ($broker_info['mobile']) {
            if ($broker_info['user_type'] == 2 && $dataInfo['hide_phone'] == 1) {
                $dataInfo['owner_phone'] = substr($broker_info['mobile'], 0, 3) . '<span class="mobile-split">****</span>' . substr($broker_info['mobile'], 7);
            } else {
                $dataInfo['owner_phone'] = substr($broker_info['mobile'], 0, 3) . '<span class="mobile-split">' . substr($broker_info['mobile'], 3, 4) . '</span>' . substr($broker_info['mobile'], 7);
            }
            $broker_info['format_phone'] = $dataInfo['owner_phone'];
        }

        if (in_array($broker_info['user_type'], array(1, 3, 4))) {
            $broker_info['servicearea'] = str_replace('|', ' &nbsp; ', $broker_info['servicearea']);
        }

        if ($broker_info['status'] == 0) {
            $broker_info['avatar'] = '';
        }
        //$broker_info['avatar'] = GetBrokerFace($broker_info['avatar']);
        $broker_info['qrcode'] = '/upfile/qrcode.jpg?data=' . urlencode($http_type . 'm.' . $cfg['page']['basehost'] . '/' . $cityInfo['url_name'] . $_SERVER['REQUEST_URI'] . '?call=1');
    }
    $broker_list[0] = $broker_info;
} elseif ($dataInfo['source_id'] >= 100) {
    //读取推送人信息
    $cache_key = MEMCACHE_PREFIX . 'app_' . $dataInfo['source_id'];
    $app_info = $query->table('app_list')->where('app_id = ' . $dataInfo['source_id'])->cache($cache_key, MEMCACHE_MAX_EXPIRETIME)->one();
    if ($app_info) {
        $broker_info = array(
            'realname' => $dataInfo['owner_name'] . ' ' . $app_info['app_name'],
            'user_type' => $app_info['user_type']
        );
    }
}

//取得最新新闻资讯
$News = new News($query);
$new_limit = 3;
$sphinx_index = 'search_news,search_news_delta';
$news_list = $News->getHotFromSphinx(0, $new_limit, $sphinx_index, true);

//地图信息
if (!empty($dataInfo['borough_name'])) {
    $map_title = $dataInfo['borough_name'];
} elseif (empty($dataInfo['borough_name']) && !empty($dataInfo['cityarea2_name'])) {
    $map_title = $dataInfo['cityarea2_name'];
} else {
    $map_title = '';
}
//$house_address = $cityInfo['city_name'] . $dataInfo['cityarea_name'] . $dataInfo['cityarea2_name'] . $dataInfo['house_address'] . $dataInfo['borough_name'];
if (empty($dataInfo['house_address'])) {
    $house_address = $dataInfo['cityarea_name'] . $dataInfo['cityarea2_name'] . $dataInfo['borough_name'];
} else {
    $house_address = $dataInfo['cityarea_name'] . $dataInfo['cityarea2_name'] . $dataInfo['house_address'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="format-detection" content="telephone=no" />
<title><?php echo $page->title;?></title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<script type="text/javascript" src="//api.map.baidu.com/api?v=3.0&ak=KGf0A7gkqTowCrwG7N7RyFLtYYhCCVVv"></script>
<script type="text/javascript" src="/js/jquery-1.11.0.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/jquery.lazyload.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/picture.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body>
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div id="main">
    <div class="position">
        <a href="/"><?php echo $page->titlec . $cityInfo['city_name'];?></a> &gt;
        <a href="/<?php echo $_GET['column'];?>/"><?php echo $cityInfo['city_name'] . $column['house_title'];?></a> &gt;
        <?php
        if ($dataInfo['cityarea_id']) {
            ?>
            <a href="list_<?php echo $dataInfo['mtype'];?>_<?php echo $dataInfo['cityarea_id'];?>.html"><?php echo $dataInfo['cityarea_name'] .  $column['house_title'];?></a> &gt;
            <?php
        }
        if ($dataInfo['cityarea_id'] && $dataInfo['cityarea2_id']) {
            ?>
            <a href="list_<?php echo $dataInfo['mtype'];?>_<?php echo $dataInfo['cityarea_id'];?>_<?php echo $dataInfo['cityarea2_id'];?>.html"><?php echo $dataInfo['cityarea2_name'] . $column['house_title'];?></a> &gt;
            <?php
        }

        if ($dataInfo['mtype'] == 2) {
            echo '<a href="list_2.html">个人' . $column['column_position_title'] . '</a>';
        } elseif ($dataInfo['mtype'] == 4 && $dataInfo['is_cooperation'] == 1) {
            echo '<a href="list_4.html">合作' . $column['column_position_title'] . '</a>';
        } elseif ($dataInfo['mtype'] == 1 && $dataInfo['is_cooperation'] != 1) {
            echo '<a href="list_1.html">经纪人' . $column['column_position_title'] . '</a>';
        } elseif ($dataInfo['mtype'] == 3 && $dataInfo['is_cooperation'] != 1) {
            echo '<a href="list_3.html">非中介机构' . $column['column_position_title'] . '</a>';
        } else {
            echo '<a href="list_0.html">全部' . $column['column_position_title'] . '</a>';
        }
        ?>
    </div>
    <div class="house-base-info">
        <h1 class="house-title"><?php
            echo $dataInfo['article_title'];
            echo $dataInfo['mtype'] == 2 ? '(个人)' : '';
            if ($dataInfo['mtype'] == 1 && $dataInfo['is_cooperation'] == 1) {
                echo '(合作)';
            }
            ?></h1>
        <div class="l_fy">
            <p>房源编号：<?php echo $dataInfo['id'];?> &nbsp;&nbsp;&nbsp;&nbsp;更新时间：<?php echo $dataInfo['updatetime'];?> &nbsp;&nbsp;&nbsp;&nbsp;<span id="click-number"></span></p>
            <?php
            if ($dataInfo['house_status'] != 1 && $dataInfo['house_status'] != 5) {
                ?>
                <span><div id="share-btn"><i class="icon icon-share"></i>分享<div class="wechat-qrcode">
                            <img src="/upfile/qrcode.jpg?data=<?php echo urlencode($page_url);?>" />
                            <div class="qrcode-title share-title">
                                微信扫一扫<br />分享到好友或朋友圈
                            </div>
                        </div>
                    </div><a href="/house/report_<?php
                    if ($member_db == 'x') {
                        $url_fix = 'x';
                    } elseif ($member_db > 0) {
                        $url_fix = '_' . $member_db;
                    } else {
                        $url_fix = '';
                    }
                    echo $_GET['column'] . '_' . $dataInfo['id'] . $url_fix;
                    ?>.html"><i class="icon icon-report"></i>投诉举报</a><a href="javascript:void(0);" id="add-collect-btn"><i class="icon icon-collect"></i>收藏该信息</a></span>
                <?php
            }
            ?>
            <div class="clear"></div>
        </div>
    </div>
    <div id="left" class="pic-container-left">
        <div class="main-content clear-fix">
            <?php
            $db_index = $member_db == 'x' ? MEMBER_DB_INDEX : intval($member_db);
            ?>
            <div class="pic-show-box" id="pic-show-box">
                <div class="big-pic-show"<?php echo !empty($picList[0]['pic_url']) ? ' style="background-image:none"' : '';?>>
                    <img src="<?php
                    $big_pic = GetPictureUrl($picList[0]['pic_url'], 18, $db_index);
                    if ($big_pic) {
                        echo $big_pic;
                    } else {
                        echo '/images/no_picture.jpg';
                    }
                    ?>"/>

                    <div class="image-index"></div>
                </div>
                <div class="pic-thumb-list-box clear-fix">
                    <div class="thumb-list">
                        <ul class="clear-fix">
                            <?php
                            if ($picList) {
                                foreach ($picList as $key => $item) {
                                    $pic_url = GetPictureUrl($item['pic_url'], 2, $db_index);
                                    $thumb_pic = GetPictureUrl($item['pic_url'], 18, $db_index);
                                    $big_pic = GetPictureUrl($item['pic_url'], 1, $db_index);
                                    $li_style = $key == 0 ? ' class="active"' : '';
                                    echo '<li' . $li_style . '><img src="' . $pic_url . '" thumb-pic="' . $thumb_pic . '" big-pic="' . $big_pic . '" alt="' . $dataInfo['article_title'] . '"/></li>';
                                }
                            } else {
                                echo '<li class="active"><img src="/images/no_picture.jpg" big-pic="/images/no_picture.jpg"/></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="prev-arrow"><i></i></div>
                    <div class="next-arrow"><i></i></div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="house-notice"><i class="notice-icon"><img src="/images/notice-icon.png"/></i>郑重提示：请您认真查看房产证、身份证等证件信息，在签订合同前请勿支付任何形式的费用，以免上当受骗！</div>
        <div id="content">
            <?php
            $nav_parts = array('general-info');
            ?>
            <div class="navbar-wrapper clearfix">
                <ul>
                    <li><a href="#general-info-tag" class="navbar-menu-item active">房源概况</a>
                    <?php
                    if (in_array($dataInfo['column_type'], array(1, 16, 17)) && $dataInfo['house_support']) {
                        $nav_parts[] = 'house-support';
                        ?>
                        <li><a href="#house-support-tag" class="navbar-menu-item<?php echo count($nav_parts) == 1 ? ' active' : ''; ?>"><?php echo $dataInfo['column_type'] == 1 ? '房屋配套' : '配套设施';?></a>
                        </li>
                        <?php
                    }
                    $nav_parts[] = 'house-desc';
                    ?>
                    <li><a href="#house-desc-tag" class="navbar-menu-item"><?php echo $dataInfo['column_type'] != 4 ? '房源' : ''; ?>描述</a></li>
                    <?php
                    if ($picList) {
                        $nav_parts[] = 'house-pic-list';
                        ?>
                        <li><a href="#house-pic-list-tag" class="navbar-menu-item"><?php echo $dataInfo['column_type'] != 4 ? '房源' : ''; ?>图片</a></li>
                        <?php
                    }
                    if ($house_address && $map_title) {
                        $nav_parts[] = 'address';
                        ?>
                        <li><a href="#address-tag" class="navbar-menu-item">位置周边</a></li>
                        <?php
                    }
                    if ($same_house_count) {
                        $nav_parts[] = 'same-price-house';
                        ?>
                        <li><a href="#same-price-house-tag" class="navbar-menu-item"><?php echo $dataInfo['column_type'] != 4 ? '该地区价格相近的房源' : '价格相近的信息'; ?></a>
                        </li>
                        <?php
                    }
                    if ($recommend_house) {
                        $nav_parts[] = 'recommend-house';
                        ?>
                        <li><a href="#recommend-house-tag" class="navbar-menu-item"><?php echo $dataInfo['column_type'] != 4 ? '推荐房源' : '推荐信息'; ?></a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <div class="infoitem">
                <div id="general-info-tag" class="tag-position"></div>
                <h2 id="general-info">房源概况</h2>
                <div class="general-info-list clear-fix">
                    <?php
                    if (in_array($dataInfo['column_type'], array(1))) {
                        ?>
                        <dl>
                            <dt>房屋总价：</dt>
                            <dd id="general-price-box"><?php
                                if ($dataInfo['house_price'] > 0) {
                                    echo $dataInfo['house_price'] . '万元 ';
                                    if ($dataInfo['house_totalarea'] > 0) {
                                        echo '(单价' . round($dataInfo['house_price'] * 10000 / $dataInfo['house_totalarea'], 1) . '元/㎡)';
                                    }
                                } else {
                                    echo '面议';
                                }
                                ?></dd>
                        </dl>
                        <?php
                        if ($dataInfo['house_topfloor'] > 0 || $dataInfo['house_floor'] > 0) {
                        ?>
                            <dl>
                                <dt>所在楼层：</dt>
                                <dd><?php
                                    $tmp = array();
                                    $house_floor = array();
                                    if ($dataInfo['house_floor'] > 0 && $dataInfo['house_topfloor'] > 0) {
                                        $floor = GetHouseFloor($dataInfo['house_floor'], $dataInfo['house_topfloor']);
                                    } elseif ($dataInfo['house_floor'] < 0) {
                                        $floor = '第' . $dataInfo['house_floor'] . '层';
                                    }

                                    if ($floor) {
                                        $house_floor[] = $floor;
                                    }

                                    if ($dataInfo['house_topfloor'] > 0) {
                                        $house_floor[] = '共' . $dataInfo['house_topfloor'] . '层';
                                    }
                                    if ($house_floor) {
                                        $tmp[] = implode('/', $house_floor);
                                    }

                                    if ($dataInfo['elevator'] == 1) {
                                        $tmp[] = '有电梯';
                                    } elseif ($dataInfo['elevator'] == -1) {
                                        $tmp[] = '无电梯';
                                    }
                                    echo implode(' &nbsp; ', $tmp);
                                    ?></dd>
                            </dl>
                        <?php
                        }
                        if (in_array($dataInfo['parking_lot'], array(-1, 1))) {
                            ?>
                            <dl>
                                <dt>车　　位：</dt>
                                <dd><?php
                                    if ($dataInfo['parking_lot'] == 1) {
                                        echo '有车位';
                                    } elseif ($dataInfo['parking_lot'] == -1) {
                                        echo '无车位';
                                    }
                                    ?></dd>
                            </dl>
                            <?php
                        }
                        ?>
                        <dl>
                            <dt>房屋户型：</dt>
                            <dd><?php
                                if ($dataInfo['house_room']) {
                                    echo $dataInfo['house_room'] . '室';
                                }
                                if ($dataInfo['house_hall']) {
                                    echo $dataInfo['house_hall'] . '厅';
                                }
                                if ($dataInfo['house_toilet']) {
                                    echo $dataInfo['house_toilet'] . '卫';
                                }
                                ?></dd>
                        </dl>
                        <?php
                        if ($dataInfo['house_fitment']) {
                            ?>
                            <dl>
                                <dt>装修情况：</dt>
                                <dd><?php echo $dataInfo['house_fitment'];?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_totalarea'] > 0) {
                        ?>
                        <dl>
                            <dt>房本面积：</dt>
                            <dd><?php echo $dataInfo['house_totalarea']; ?>㎡</dd>
                        </dl>
                            <?php
                        }
                        ?>
                        <?php
                        if ($dataInfo['belong']) {
                            ?>
                            <dl>
                                <dt>产权年限：</dt>
                                <dd><?php echo $dataInfo['belong']; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_toward']) {
                            ?>
                            <dl>
                                <dt>房屋朝向：</dt>
                                <dd><?php echo $dataInfo['house_toward'];?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_type']) {
                            ?>
                            <dl>
                                <dt>房屋类型：</dt>
                                <dd><?php echo $dataInfo['house_type'];?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_age']) {
                            ?>
                            <dl>
                                <dt>建筑年代：</dt>
                                <dd><?php echo $dataInfo['house_age']; ?>年</dd>
                            </dl>
                            <?php
                        }
                    } elseif (in_array($dataInfo['column_type'], array(2))) {
                        ?>
                        <dl>
                            <dt>售　　价：</dt>
                            <dd><?php
                                if ($dataInfo['house_price'] > 0) {
                                    echo $dataInfo['house_price'] . '万元 ';
                                } else {
                                    echo '面议';
                                }
                                ?></dd>
                        </dl>
                        <dl>
                            <dt>楼盘类型：</dt>
                            <dd><?php echo $dataInfo['house_type'];?></dd>
                        </dl>
                        <?php
                        if ($dataInfo['house_price'] > 0 && $dataInfo['house_totalarea'] > 0) {
                            ?>
                            <dl>
                                <dt>单　　价：</dt>
                                <dd><?php echo round($dataInfo['house_price'] * 10000 / $dataInfo['house_totalarea'], 1) . '元/㎡'; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_totalarea'] > 0) {
                            ?>
                            <dl>
                                <dt>面　　积：</dt>
                                <dd><?php echo $dataInfo['house_totalarea']; ?>㎡</dd>
                            </dl>
                            <?php
                        }
                        ?>
                        <?php
                        if ($dataInfo['house_topfloor'] > 0 || $dataInfo['house_floor'] > 0) {
                            ?>
                            <dl>
                                <dt>楼　　层：</dt>
                                <dd><?php
                                    $tmp = array();
                                    $house_floor = array();
                                    if ($dataInfo['house_floor'] > 0 && $dataInfo['house_topfloor'] > 0) {
                                        $floor = GetHouseFloor($dataInfo['house_floor'], $dataInfo['house_topfloor']);
                                    } elseif ($dataInfo['house_floor'] < 0) {
                                        $floor = '第' . $dataInfo['house_floor'] . '层';
                                    }

                                    if ($floor) {
                                        $house_floor[] = $floor;
                                    }

                                    if ($dataInfo['house_topfloor'] > 0) {
                                        $house_floor[] = '共' . $dataInfo['house_topfloor'] . '层';
                                    }
                                    if ($house_floor) {
                                        $tmp[] = implode('/', $house_floor);
                                    }

                                    if ($dataInfo['elevator'] == 1) {
                                        $tmp[] = '有电梯';
                                    } elseif ($dataInfo['elevator'] == -1) {
                                        $tmp[] = '无电梯';
                                    }
                                    echo implode(' &nbsp; ', $tmp);
                                    ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_fitment']) {
                            ?>
                            <dl>
                                <dt>装修情况：</dt>
                                <dd><?php echo $dataInfo['house_fitment']; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_toward']) {
                            ?>
                            <dl>
                                <dt>房屋朝向：</dt>
                                <dd><?php echo $dataInfo['house_toward']; ?></dd>
                            </dl>
                            <?php
                        }
                        if (in_array($dataInfo['parking_lot'], array(-1, 1))) {
                            ?>
                            <dl>
                                <dt>车　　位：</dt>
                                <dd><?php
                                    if ($dataInfo['parking_lot'] == 1) {
                                        echo '有车位';
                                    } elseif ($dataInfo['parking_lot'] == -1) {
                                        echo '无车位';
                                    }
                                    ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['borough_name']) {
                            ?>
                            <dl>
                                <dt>楼　　盘：</dt>
                                <dd><?php echo $dataInfo['borough_name']; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_address']) {
                            ?>
                            <dl>
                                <dt>地　　址：</dt>
                                <dd><?php echo $dataInfo['house_address'];?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['belong']) {
                            ?>
                            <dl>
                                <dt>产权年限：</dt>
                                <dd><?php echo $dataInfo['belong']; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_age']) {
                            ?>
                            <dl>
                                <dt>建筑年代：</dt>
                                <dd><?php echo $dataInfo['house_age']; ?>年</dd>
                            </dl>
                            <?php
                        }
                    } elseif (in_array($dataInfo['column_type'], array(3))) {
                        ?>
                        <dl>
                            <dt>总　　价：</dt>
                            <dd><?php
                                if ($dataInfo['house_price'] > 0) {
                                    echo $dataInfo['house_price'] . '万元 ';
                                } else {
                                    echo '面议';
                                }
                                ?></dd>
                        </dl>
                        <?php
                        if ($dataInfo['house_price'] > 0 && $dataInfo['house_totalarea'] > 0) {
                            ?>
                            <dl>
                                <dt>单　　价：</dt>
                                <dd><?php echo round($dataInfo['house_price'] * 10000 / $dataInfo['house_totalarea'], 1) . '元/㎡'; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_totalarea'] > 0) {
                            ?>
                            <dl>
                                <dt>建筑面积：</dt>
                                <dd><?php echo $dataInfo['house_totalarea']; ?>㎡</dd>
                            </dl>
                            <?php
                        }
                        ?>
                        <?php
                        if ($dataInfo['house_topfloor'] > 0 || $dataInfo['house_floor'] > 0) {
                            ?>
                            <dl>
                                <dt>楼　　层：</dt>
                                <dd><?php
                                    $tmp = array();
                                    $house_floor = array();
                                    if ($dataInfo['house_floor'] > 0 && $dataInfo['house_topfloor'] > 0) {
                                        $floor = GetHouseFloor($dataInfo['house_floor'], $dataInfo['house_topfloor']);
                                    } elseif ($dataInfo['house_floor'] < 0) {
                                        $floor = '第' . $dataInfo['house_floor'] . '层';
                                    }

                                    if ($floor) {
                                        $house_floor[] = $floor;
                                    }

                                    if ($dataInfo['house_topfloor'] > 0) {
                                        $house_floor[] = '共' . $dataInfo['house_topfloor'] . '层';
                                    }
                                    if ($house_floor) {
                                        $tmp[] = implode('/', $house_floor);
                                    }

                                    if ($dataInfo['elevator'] == 1) {
                                        $tmp[] = '有电梯';
                                    } elseif ($dataInfo['elevator'] == -1) {
                                        $tmp[] = '无电梯';
                                    }
                                    echo implode(' &nbsp; ', $tmp);
                                    ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_fitment']) {
                            ?>
                            <dl>
                                <dt>装修情况：</dt>
                                <dd><?php echo $dataInfo['house_fitment']; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_toward']) {
                            ?>
                            <dl>
                                <dt>房屋朝向：</dt>
                                <dd><?php echo $dataInfo['house_toward']; ?></dd>
                            </dl>
                            <?php
                        }
                        if (in_array($dataInfo['parking_lot'], array(-1, 1))) {
                            ?>
                            <dl>
                                <dt>车　　位：</dt>
                                <dd><?php
                                    if ($dataInfo['parking_lot'] == 1) {
                                        echo '有车位';
                                    } elseif ($dataInfo['parking_lot'] == -1) {
                                        echo '无车位';
                                    }
                                    ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_type']) {
                            ?>
                            <dl>
                                <dt>楼盘类型：</dt>
                                <dd><?php echo $dataInfo['house_type']; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['borough_name']) {
                            ?>
                            <dl>
                                <dt>楼　　盘：</dt>
                                <dd><?php echo $dataInfo['borough_name']; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_address']) {
                            ?>
                            <dl>
                                <dt>地　　址：</dt>
                                <dd><?php echo $dataInfo['house_address'];?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['belong']) {
                            ?>
                            <dl>
                                <dt>产权年限：</dt>
                                <dd><?php echo $dataInfo['belong']; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_age']) {
                            ?>
                            <dl>
                                <dt>建筑年代：</dt>
                                <dd><?php echo $dataInfo['house_age']; ?>年</dd>
                            </dl>
                            <?php
                        }
                    } elseif (in_array($dataInfo['column_type'], array(4))) {
                        ?>
                        <dl>
                            <dt>类　　型：</dt>
                            <dd>车位出售</dd>
                        </dl>
                        <?php
                        if ($dataInfo['house_totalarea'] > 0) {
                            ?>
                            <dl>
                                <dt>建筑面积：</dt>
                                <dd><?php echo $dataInfo['house_totalarea']; ?>㎡</dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['down_payment'] > 0) {
                            ?>
                            <dl>
                                <dt>首　　付：</dt>
                                <dd><?php echo round($dataInfo['down_payment'], 1) . '成起'; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['parking_type']) {
                            ?>
                            <dl>
                                <dt>车位类型：</dt>
                                <dd><?php echo GetParkingType($dataInfo['parking_type']); ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_address']) {
                            ?>
                            <dl>
                                <dt>地　　址：</dt>
                                <dd><?php echo $dataInfo['house_address']; ?></dd>
                            </dl>
                        <?php
                        }
                    } elseif (in_array($dataInfo['column_type'], array(16, 17))) {
                        ?>
                        <dl>
                            <dt>类　　型：</dt>
                            <dd><?php
                                if ($dataInfo['column_type'] == 16) {
                                    echo '厂房出售';
                                } elseif ($dataInfo['column_type'] == 17) {
                                    echo '仓库出售';
                                }
                                ?></dd>
                        </dl>
                        <?php
                        if ($dataInfo['house_totalarea'] > 0) {
                            ?>
                            <dl>
                                <dt>建筑面积：</dt>
                                <dd><?php echo $dataInfo['house_totalarea']; ?>㎡</dd>
                            </dl>
                            <?php
                        }
                        ?>
                        <dl>
                            <dt>首　　付：</dt>
                            <dd><?php echo $dataInfo['down_payment'] > 0 ? round($dataInfo['down_payment'], 1) . '成起' : '暂无数据'; ?></dd>
                        </dl>
                    <?php
                        if ($dataInfo['first_floor_height'] > 0) {
                            ?>
                            <dl>
                                <dt>首层层高：</dt>
                                <dd><?php echo $dataInfo['first_floor_height'];?>米</dd>
                            </dl>
                            <?php
                        }
                        if (in_array($dataInfo['floor_type'], array(1, 2))) {
                            ?>
                            <dl>
                                <dt>楼　　层：</dt>
                                <dd><?php
                                    if ($dataInfo['floor_type'] == 1) {
                                        echo '单层';
                                    } elseif ($dataInfo['floor_type'] == 2) {
                                        echo '多层';
                                    }
                                    ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_address']) {
                            ?>
                            <dl>
                                <dt>地　　址：</dt>
                                <dd><?php echo $dataInfo['house_address']; ?></dd>
                            </dl>
                            <?php
                        }
                    }
                    if ($dataInfo['report_count'] > 0 && $dataInfo['truth_degree'] > 0 && $show_truth == true) {
                        ?>
                        <dl>
                            <dt>真<span class="letter-center">实</span>度：</dt>
                            <dd class="truth-degree"><?php echo $dataInfo['truth_degree']; ?>%</dd>
                        </dl>
                        <?php
                    }
                    ?>
                </div>
                <?php
                if (in_array($dataInfo['column_type'], array(1, 16, 17)) && $dataInfo['house_support']) {
                    ?>
                    <div id="house-support-tag" class="tag-position"></div>
                    <h2 id="house-support"><?php echo !in_array($dataInfo['column_type'], array(4, 16, 17)) ? '房屋配套' : '配套设施'; ?></h2>
                    <?php
                    if ($dataInfo['house_support']) {
                        echo '<div class="house-support-list clear-fix">';
                        if (in_array($dataInfo['column_type'], array(16, 17))) {
                            $icon_class =  'icon-2';
                        } else {
                            $icon_class =  'icon';
                        }
                        foreach ($dataInfo['house_support'] as $item) {
                            echo '<div class="item"><div class="' . $icon_class . ' ' . $item['class'] . '"></div><div class="caption">' . $item['caption'] . '</div></div>';
                        }
                        echo '</div>';
                    }
                }
                ?>
                <div id="house-desc-tag" class="tag-position"></div>
                <h2 id="house-desc"><?php echo $dataInfo['column_type'] != 4 ? '房源' : ''; ?>描述</h2>

                <div class="des"><?php echo $dataInfo['house_desc']; ?></div>
                <?php
                if ($dataInfo['column_type'] == 1 && $dataInfo['bright_spot']) {
                    ?>
                    <div class="house-tags-box clear-fix">
                        <div class="caption">房屋亮点：</div>
                        <div class="tag-list clear-fix">
                            <?php
                            foreach ($dataInfo['bright_spot'] as $item) {
                                echo '<span class="item">' . $item . '</span>';
                            }
                            if ($dataInfo['parking_lot'] == 1) {
                                echo '<span class="item">有车位</span>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="content-tips">联系我时，请说是在第一时间房源网上看到的，谢谢！</div>
                <?php
                if ($info_bottom_ad) {
                    echo '<div class="info-bottom-ad">' . $info_bottom_ad . '</div>';
                }
                if ($picList) {
                    ?>
                    <div id="house-pic-list-tag" class="tag-position"></div>
                    <h2 id="house-pic-list"><?php echo $dataInfo['column_type'] != 4 ? '房源' : ''; ?>图片</h2>
                    <div class="pic-list clear-fix">
                        <?php
                        $db_index = $member_db == 'x' ? MEMBER_DB_INDEX : intval($member_db);
                        foreach ($picList as $key => $item) {
                            $pic_url = GetPictureUrl($item['pic_url'], 18, $db_index);
                            $even_class = $key % 2 == 0 ? ' even' : '';
                            echo '<div class="desc-image' . $even_class . ' "><img src="/images/default_picture.jpg" data-original="' . $pic_url . '" alt="' . $item['pic_desc'] . '" />';
                            if ($item['pic_desc']) {
                                echo '<div class="desc-image-title">' . $item['pic_desc'] . '</div>';
                            }
                            echo '</div>';
                            if ($key == 0 && $pic_box_bottom_ad) {
                                echo '<div class="pic-box-ad">' . $pic_box_bottom_ad . '</div>';
                            }
                        }
                        if (empty($picList) && $pic_box_bottom_ad) {
                            echo '<div class="pic-box-ad">' . $pic_box_bottom_ad . '</div>';
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div id="right" class="pic-container-right">
        <div class="house-base-info clear-fix">
            <div class="right-info">
                <div class="price" id="main-price">
                    <?php
                    if ($dataInfo['house_price'] > 0) {
                        echo '<b>' . $dataInfo['house_price'] . '</b>万元 ';
                        if ($dataInfo['house_totalarea'] > 0) {
                            echo '<span class="text">(' . round($dataInfo['house_price'] * 10000 / $dataInfo['house_totalarea'], 1) . '元/㎡)</span>';
                        }
                    } else {
                        echo '<b>面议</b>';
                    }
                    ?>
                </div>
                <div class="base-list clear-fix">
                    <?php
                    if ($dataInfo['column_type'] == 1) {
                        ?>
                        <div class="item">
                            <div class="sup"><?php
                                if ($dataInfo['house_room']) {
                                    echo $dataInfo['house_room'] . '室';
                                }
                                if ($dataInfo['house_hall']) {
                                    echo $dataInfo['house_hall'] . '厅';
                                }
                                if ($dataInfo['house_toilet']) {
                                    echo $dataInfo['house_toilet'] . '卫';
                                }
                                ?></div>
                            <div class="sub"><?php
                                $tmp = array();
                                if ($dataInfo['house_toward']) {
                                    $tmp[] = $dataInfo['house_toward'];
                                }
                                $house_floor = array();
                                if ($dataInfo['house_floor'] > 0 && $dataInfo['house_topfloor'] > 0) {
                                    $floor = GetHouseFloor($dataInfo['house_floor'], $dataInfo['house_topfloor']);
                                } elseif ($dataInfo['house_floor'] < 0) {
                                    $floor = '第' . $dataInfo['house_floor'] . '层';
                                }
                                if ($floor) {
                                    $house_floor[] = $floor;
                                }
                                if ($dataInfo['house_topfloor'] > 0) {
                                    $house_floor[] = '(共' . $dataInfo['house_topfloor'] . '层)';
                                }
                                $house_floor = implode('', $house_floor);
                                echo $house_floor ? $house_floor : '暂无信息';
                                ?></div>
                        </div>
                        <div class="item">
                            <div class="sup"><?php echo $dataInfo['house_totalarea'] > 0 ? $dataInfo['house_totalarea'] . '㎡': '面积(暂无信息)'?></div>
                            <div class="sub"><?php echo $dataInfo['house_fitment'] ? $dataInfo['house_fitment'] : '装修(暂无信息)';?></div>
                        </div>
                        <div class="item">
                            <div class="sup"><?php echo $dataInfo['house_toward'] ? '朝向' . $dataInfo['house_toward'] : '朝向(暂无信息)';?></div>
                            <div class="sub"><?php echo $dataInfo['house_age'] ? $dataInfo['house_age'] . '年' : '建筑年代(暂无信息)'; ?></div>
                        </div>
                        <?php
                    } elseif (in_array($dataInfo['column_type'], array(2, 3))) {
                        ?>
                        <div class="item">
                            <div class="sup"><?php echo $dataInfo['house_totalarea'] > 0 ? $dataInfo['house_totalarea'] . '㎡': '暂无信息';?></div>
                            <div class="sub">建筑面积</div>
                        </div>
                        <div class="item">
                            <div class="sup"><?php echo $dataInfo['house_type'] ? $dataInfo['house_type'] : '暂无信息';?></div>
                            <div class="sub">楼盘类型</div>
                        </div>
                        <div class="item">
                            <div class="sup"><?php echo $dataInfo['house_age'] ? $dataInfo['house_age'] . '年' : '暂无信息'; ?></div>
                            <div class="sub">建筑年代</div>
                        </div>
                        <?php
                    } elseif (in_array($dataInfo['column_type'], array(4, 16, 17))) {
                        ?>
                        <div class="item">
                            <div class="sup"><?php echo $dataInfo['house_totalarea'] > 0 ? $dataInfo['house_totalarea'] . '㎡': '暂无信息';?></div>
                            <div class="sub">建筑面积</div>
                        </div>
                        <div class="item">
                            <div class="sup"><?php
                                if ($dataInfo['column_type'] == 4) {
                                    $parking_type = GetParkingType($dataInfo['parking_type']);
                                    echo $parking_type ? $parking_type : '暂无信息';
                                }elseif ($dataInfo['column_type'] == 16) {
                                    echo '厂房出售';
                                } elseif ($dataInfo['column_type'] == 17) {
                                    echo '仓库出售';
                                }
                                ?></div>
                            <div class="sub"><?php echo $dataInfo['column_type'] == 4 ? '车位类型' : '出售类型';?></div>
                        </div>
                        <div class="item">
                            <div class="sup"><?php echo $dataInfo['down_payment'] > 0 ? round($dataInfo['down_payment'], 1) . '成起' : '面议'; ?></div>
                            <div class="sub">首付</div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
                if (in_array($dataInfo['column_type'], array(1, 2))) {
                    ?>
                    <div class="base-list-2">
                        <?php
                        if ($dataInfo['borough_name'] && in_array($dataInfo['column_type'], array(1, 2, 3))) {
                            ?>
                            <dl>
                                <dt><?php
                                    if ($dataInfo['column_type'] == 1) {
                                        echo '小区：';
                                    } else {
                                        echo '楼盘：';
                                    }
                                    ?></dt>
                                <dd><?php
                                    echo $dataInfo['borough_name'];
                                    if ($house_address && $map_title) {
                                        echo '<a href="#address-tag" class="map-link">地图</a>';
                                    }
                                    ?></dd>
                            </dl>
                            <?php
                        }
                        ?>
                        <dl>
                            <dt>位置：</dt>
                            <dd><?php
                                //                                echo $cityInfo['city_name'];
                                if ($dataInfo['cityarea_name']) {
                                    echo $dataInfo['cityarea_name'] . ' &nbsp; ';
                                }
                                if ($dataInfo['cityarea2_name']) {
                                    echo $dataInfo['cityarea2_name'] . ' &nbsp; ';
                                }
                                if ($dataInfo['house_address']) {
                                    echo $dataInfo['house_address'];
                                }
                                ?></dd>
                        </dl>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="base-list-2">
                        <dl>
                            <dt>区域：</dt>
                            <dd><?php
                                //                                echo $cityInfo['city_name'];
                                if ($dataInfo['cityarea_name']) {
                                    echo $dataInfo['cityarea_name'] . ' &nbsp; ';
                                }
                                if ($dataInfo['cityarea2_name']) {
                                    echo $dataInfo['cityarea2_name'] . ' &nbsp; ';
                                }
                                if ($house_address && $map_title) {
                                    echo '<a href="#address-tag" class="map-link">地图</a>';
                                }
                                ?></dd>
                        </dl>
                        <dl>
                            <dt>地址：</dt>
                            <dd><?php echo $dataInfo['house_address'];?></dd>
                        </dl>
                    </div>
                    <?php
                }
                ?>
                <?php
                if (!empty($broker_list)) {
                    ?>
                    <div class="detail-broker-list">
                        <?php
                        if (empty($member_db)) {
                            ?>
                            <div class="caption">联系经纪人</div>
                            <?php
                        }
                        ?>
                        <?php
                        foreach ($broker_list as $item) {
                            ?>
                            <div class="item clear-fix">
                                <?php
                                if ($item['account_open'] == 1) {
                                    ?>
                                    <div class="broker-left">
                                        <div class="broker-face">
                                            <a href="<?php echo $item['shop_url'];?>" target="_blank">
                                                <?php
                                                if ($item['avatar']) {
                                                    ?>
                                                    <img src="<?php echo GetBrokerFace($item['avatar']);?>">
                                                    <?php
                                                } elseif ($item['realname']) {
                                                    ?>
                                                    <div class="first-name-box"><div class="text"><?php echo mb_substr($item['realname'], 0, 1, 'utf-8');?></div></div>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <img src="<?php echo GetBrokerFace('');?>">
                                                    <?php
                                                }
                                                ?>
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="broker-right<?php
                                if ($item['account_open'] != 1) {
                                    echo ' broker-right-2';
                                }
                                ?>">
                                    <div class="contact-name">
                                        <?php
                                        if ($item['account_open'] == 1 && $item['shop_url']) {
                                            echo '<strong><a href="' . $item['shop_url'] .'" target="_blank">' . $item['realname'] . '</a></strong>';
                                        } else {
                                            echo '<strong>' . $item['realname'] . '</strong>';
                                        }

                                        if ($item['user_type'] == 1) {
                                            echo '（经纪人）';
                                        } elseif ($item['user_type'] == 2) {
                                            if ($dataInfo['is_sublet'] == 1) {
                                                echo '（个人转租）';
                                            } else {
                                                echo '（个人）';
                                            }
                                        } elseif ($item['user_type'] == 3) {
                                            if ($item['user_type_sub'] == 1) {
                                                echo '（物业公司）';
                                            } elseif ($item['user_type_sub'] == 2) {
                                                echo '（开发商）';
                                            } elseif ($item['user_type_sub'] == 3) {
                                                echo '（拍卖机构）';
                                            } elseif ($item['user_type_sub'] == 4) {
                                                echo '（其它公司）';
                                            } else {
                                                echo '（非中介机构）';
                                            }
                                        } elseif ($item['user_type'] == 4) {
                                            echo '（品牌公寓）';
                                        }
                                        ?></div>
                                    <?php
                                    if ($item && in_array($item['user_type'], array(1, 3, 4))) {
                                        if ($item['company']) {
                                            ?>
                                            <div class="company"><?php echo $item['company'] . ' &nbsp; ' . $item['outlet']; ?></div>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <div class="mobile">手机：<?php echo $item['format_phone'];?></div>
                                </div>
                                <?php
                                if ($item['qrcode']) {
                                    ?>
                                    <div class="qrcode">
                                        <img src="<?php echo $item['qrcode'];?>"/>
                                        <p>微信扫码拨号</p>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="broker-info-box clear-fix">
                        <?php
                        if ($broker_info['account_open'] == 1) {
                            ?>
                            <div class="broker-left">
                                <div class="broker-face">
                                    <a href="<?php echo $broker_info['shop_url'];?>" target="_blank">
                                        <?php
                                        if ($broker_info['avatar']) {
                                            ?>
                                            <img src="<?php echo GetBrokerFace($broker_info['avatar']);?>">
                                            <?php
                                        } elseif ($broker_info['realname']) {
                                            ?>
                                            <div class="first-name-box"><div class="text"><?php echo mb_substr($broker_info['realname'], 0, 1, 'utf-8');?></div></div>
                                            <?php
                                        } else {
                                            ?>
                                            <img src="<?php echo GetBrokerFace('');?>">
                                            <?php
                                        }
                                        ?>
                                    </a>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="broker-right<?php
                        if ($broker_info['account_open'] != 1) {
                            echo ' broker-right-2';
                        }
                        ?>">
                            <div class="contact-name">
                                <?php
                                if ($broker_info) {
                                    if ($broker_info['account_open'] == 1 && $broker_info['shop_url']) {
                                        echo '<strong><a href="' . $broker_info['shop_url'] .'" target="_blank">' . $broker_info['realname'] . '</a></strong>';
                                    } else {
                                        if ($broker_info['realname']) {
                                            echo '<strong>' . $broker_info['realname'] . '</strong>';
                                        } else {
                                            echo '<strong>' . $dataInfo['owner_name'] . '</strong>';
                                        }
                                    }

                                    if ($broker_info['user_type'] == 1) {
                                        echo '（经纪人）';
                                    } elseif ($broker_info['user_type'] == 2) {
                                        if ($dataInfo['is_sublet'] == 1) {
                                            echo '（个人转租）';
                                        } else {
                                            echo '（个人）';
                                        }
                                    } elseif ($broker_info['user_type'] == 3) {
                                        if ($broker_info['user_type_sub'] == 1) {
                                            echo '（物业公司）';
                                        } elseif ($broker_info['user_type_sub'] == 2) {
                                            echo '（开发商）';
                                        } elseif ($broker_info['user_type_sub'] == 3) {
                                            echo '（拍卖机构）';
                                        } elseif ($broker_info['user_type_sub'] == 4) {
                                            echo '（其它公司）';
                                        } else {
                                            echo '（非中介机构）';
                                        }
                                    } elseif ($broker_info['user_type'] == 4) {
                                        echo '（品牌公寓）';
                                    }
                                } else {
                                    if (empty($dataInfo['owner_name'])) {
                                        echo '<strong>个人</strong>';
                                        if ($dataInfo['is_sublet'] == 1) {
                                            echo '（转租）';
                                        }
                                    } else {
                                        echo '<strong>' . $dataInfo['owner_name'] . '</strong>';
                                    }

                                    if ($dataInfo['mtype'] == 1) {
                                        echo '（经纪人）';
                                    } elseif ($dataInfo['mtype'] == 2 && !empty($dataInfo['owner_name'])) {
                                        if ($dataInfo['is_sublet'] == 1) {
                                            echo '（个人转租）';
                                        } else {
                                            echo '（个人）';
                                        }
                                    } elseif ($dataInfo['mtype'] == 3) {
                                        echo '（非中介机构）';
                                    } elseif ($dataInfo['mtype'] == 4) {
                                        echo '（品牌公寓）';
                                    }
                                }
                                ?></div>
                            <?php
                            if ($broker_info && in_array($broker_info['user_type'], array(1, 3, 4))) {
                                if ($broker_info['company']) {
                                    ?>
                                    <div class="company"><?php echo $broker_info['company'] . ' &nbsp; ' . $broker_info['outlet']; ?></div>
                                    <?php
                                }
                                if ($broker_info['servicearea']) {
                                    ?>
                                    <div class="service-area"><?php echo $broker_info['servicearea'];?></div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <?php
                        if ($broker_info['qrcode']) {
                            ?>
                            <div class="qrcode">
                                <img src="<?php echo $broker_info['qrcode'];?>"/>
                                <p>微信扫码拨号</p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                if (in_array($dataInfo['house_status'], array(1, 5))) {
                    ?>
                    <div class="phone-box clear-fix">
                        <div class="common-submit-btn"><?php
                            if ($dataInfo['house_status'] == 1) {
                                echo '该房源已删除';
                            } elseif ($dataInfo['house_status'] == 5) {
                                echo '房源已成交';
                            }
                            ?></div>
                    </div>
                    <?php
                } elseif (empty($broker_list)) {
                    ?>
                    <div class="phone-box clear-fix">
                        <?php
                        if (!empty($dataInfo['source_url']) && empty($dataInfo['owner_phone']) && empty($dataInfo['owner_phone_pic']) && $dataInfo['mtype'] != 1) {
                            echo '<a href="' . $dataInfo['source_url'] . '" target="_blank" rel="nofollow" class="common-submit-btn">查看联系方式</a>';
                        } elseif (!empty($dataInfo['owner_phone'])) {
                            if ($broker_info['account_open'] == 1 && $broker_info['shop_url']) {
                                echo '<a class="common-submit-btn" href="' . $broker_info['shop_url'] . '" target="_blank">' . $dataInfo['owner_phone'];
                                if ($dataInfo['hide_phone'] != 1 || $broker_info['user_type'] != 2) {
                                    echo '<span class="phone-tips">扫码拨号</span>';
                                }
                                echo '</a>';
                            } else {
                                echo '<div class="common-submit-btn">' . $dataInfo['owner_phone'];
                                if ($dataInfo['hide_phone'] != 1 || $dataInfo['mtype'] != 2) {
                                    echo '<span class="phone-tips">扫码拨号</span>';
                                }
                                echo '</div>';
                            }
                        } elseif (!empty($dataInfo['owner_phone_pic'])) {
                            echo '<img src="' . $dataInfo['owner_phone_pic'] . '" class="phone-pic">';
                        }
                        ?>
                        <?php
                        if (!empty($dataInfo['owner_phone']) && ($dataInfo['hide_phone'] != 1 || $dataInfo['mtype'] != 2)) {
                            $url = $http_type . 'm.' . $cfg['page']['basehost'] . '/' . $cityInfo['url_name'] . $_SERVER['REQUEST_URI'] . '?call=1';
                            ?>
                            <div class="qrcode">
                                <img src="/upfile/qrcode.jpg?data=<?php echo urlencode($url);?>"/>
                                <p>微信扫码拨号</p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                if (!empty($dataInfo['source_url']) && empty($dataInfo['owner_phone']) && empty($dataInfo['owner_phone_pic']) && $dataInfo['mtype'] != 1) {
                    if ($dataInfo['source_id'] == 1) {
                        $source_name = '58同城网';
                    } elseif ($dataInfo['source_id'] == 2) {
                        $source_name = '赶集网';
                    } elseif ($dataInfo['source_id'] == 5) {
                        $source_name = '房天下网';
                    } else {
                        $source_name = '其它来源';
                    }
                    ?>
                    <div class="house-info-tips">该房源来自<?php echo $source_name;?></div>
                    <?php
                } elseif ($dataInfo['hide_phone'] == 1 && $dataInfo['mtype'] == 2) {
                    echo '<div class="house-info-tips">（该用户已设置隐藏电话，您可以通过微信联系）</div>';
                }
                ?>
                <div class="base-list-2 base-list-3">
                    <?php
                    if ($dataInfo['wechat']) {
                        ?>
                        <dl>
                            <dt>微信：</dt>
                            <dd><?php echo $dataInfo['wechat']; ?></dd>
                        </dl>
                        <?php
                    }
                    ?>
                    <?php
                    if ($dataInfo['qq']) {
                        ?>
                        <dl>
                            <dt>QQ：</dt>
                            <dd><?php echo $dataInfo['qq']; ?></dd>
                        </dl>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <div id="top-ad"><?php echo $website_right_ad_1;?></div>
        <?php
        if ($same_house) {
            ?>
            <div class="columns">
                <h2><a href="#same-price-house-tag"><?php echo $dataInfo['column_type'] != 4 ? '该地区价格相近的房源' : '价格相近的信息';?></a><a href="#same-price-house-tag" class="more">更多&gt;&gt;</a></h2>
                <div class="same-house-list">
                    <ul>
                        <?php
                        $length = $same_house_count > 3 ? 3 : $same_house_count;
                        for ($i = 0; $i < $length; $i++) {
                        $house_price = FormatHousePrice($same_house[$i]['house_price']);
                        ?>
                        <li class="clear-fix">
                            <div class="list-house-thumb">
                                <a href="<?php echo $same_house[$i]['url'];?>" target="_blank"><img src="<?php echo !empty($same_house[$i]['house_thumb']) ? $same_house[$i]['house_thumb'] : '/images/no_picture.jpg';?>" /></a>
                            </div>
                            <div class="list-house-info clear-fix">
                                <div class="list-house-title">
                                    <a href="<?php echo $same_house[$i]['url'];?>" target="_blank"><?php echo $same_house[$i]['title'];?></a>
                                </div>
                                <div class="desc"><?php
                                    $tmp =  '';
                                    if ($same_house[$i]['column_type'] == 1) {
                                        if ($same_house[$i]['house_room']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">|</span>';
                                            }
                                            $tmp .=  $same_house[$i]['house_room'] . '室';
                                            if ($same_house[$i]['house_hall']) {
                                                $tmp .= $same_house[$i]['house_hall'] . '厅';
                                            }
                                            if ($same_house[$i]['house_toilet']) {
                                                $tmp .= $same_house[$i]['house_toilet'] . '卫';
                                            }
                                        }
                                    } else {
                                        if ($same_house[$i]['cityarea_id']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">-</span>';
                                            }
                                            $tmp .= $cityarea_option[$same_house[$i]['cityarea_id']]['region_name'];
                                        }
                                        if ($same_house[$i]['cityarea2_id']) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">-</span>';
                                            }
                                            $tmp .= $cityarea2_list[$same_house[$i]['cityarea2_id']]['region_name'];
                                        }
                                    }
                                    if ($same_house[$i]['house_totalarea']) {
                                        if ($tmp) {
                                            $tmp .= '<span class="split">|</span>';
                                        }
                                        $tmp .= $same_house[$i]['house_totalarea'] . '㎡';
                                    }
                                    echo $tmp;
                                    ?></div>
                                <div class="bottom-box clear-fix">
                                    <div class="price"><?php
                                        if ($same_house[$i]['house_price'] > 0){
                                            echo '<strong>'.$same_house[$i]['house_price'].'</strong>万元';
                                        }else{
                                            echo '<strong>面议</strong>';
                                        }
                                        ?></div>
                                    <div class="update-time"><?php echo $same_house[$i]['updated'];?></div>
                                </div>
                            </div>
                        </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
        <?php
        }
        ?>
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
if ($house_address && $map_title) {
    ?>
    <div id="address-tag" class="tag-position"></div>
    <div class="address-section">
        <h2 id="address">位置周边</h2>
        <div class="map-box" id="map-box">
            <div class="container" id="map-container"></div>
            <div class="search-result-box">
                <div class="search-type-list clear-fix"></div>
                <div class="data-list" id="result-data-list"></div>
            </div>
        </div>
        <script type="text/javascript" src="/js/map.js?v=<?php echo $webConfig['static_version'];?>"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                var map = InitMap('<?php echo $cityInfo['city_name'];?>');
                GetPoint(map, '<?php echo $member_db;?>', <?php echo $dataInfo['id'];?>, 2, '<?php echo $map_title;?>', '<?php echo $cityInfo['city_name'];?>', '<?php echo $house_address;?>');
            });
        </script>
    </div>
    <?php
}
?>
<?php
if ($same_house) {
    ?>
    <a id="same-price-house-tag"></a>
    <div id="same-price-house" class="section-box picture-list-box<?php
    if ($column['column_type'] == 2 || $column['column_type'] == 3){
        echo ' list-box-2';
    }
    ?>">
        <h2><?php echo $dataInfo['column_type'] != 4 ? '该地区价格相近的房源' : '价格相近的信息';?></h2>
        <div class="list clearfix">
            <?php
            foreach($same_house as $item){
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
if ($website_same_house_bottom_ad) {
    echo '<div class="same-house-bottom-ad">' . $website_same_house_bottom_ad . '</div>';
}
if ($recommend_house) {
    ?>
    <a id="recommend-house-tag"></a>
    <div id="recommend-house" class="section-box picture-list-box<?php
    if ($column['column_type'] == 2 || $column['column_type'] == 3){
        echo ' list-box-2';
    }
    ?>">
        <h2><?php echo $dataInfo['column_type'] != 4 ? '推荐房源' : '推荐信息';?></h2>
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
<?php
$nav_parts_length = count($nav_parts);
if($nav_parts_length > 0) {
    ?>
    <script type="text/javascript" src="/js/stickUp.min.js?v=<?php echo $webConfig['static_version']; ?>"></script>
    <?php
}
?>
<script type="text/javascript">
    jQuery(function($) {
        $(document).ready( function() {
            $('.desc-image img').lazyload({effect: "fadeIn", threshold: 180});
            $('.list-house-thumb img').lazyload({effect: "fadeIn", threshold: 180});
            $('#same-price-house img').lazyload({effect: "fadeIn", threshold: 180});
            $('#recommend-house img').lazyload({effect: "fadeIn", threshold: 180});
            <?php
            if($nav_parts_length > 0){
            ?>
            $('.navbar-wrapper').width($('.infoitem').width());
            $(window).resize(function () {
                $('.navbar-wrapper').width($('.infoitem').width());
            });
            $('.navbar-wrapper').stickUp({
                parts: {
                    <?php
                    foreach ($nav_parts as $key => $item) {
                        echo $key . ': \'' . $item . '\'';
                        if($key < ($nav_parts_length - 1)){
                            echo ',';
                        }
                    }
                    ?>
                },
                itemClass: 'navbar-menu-item',
                itemHover: 'active',
                marginTop: 0
            });
            <?php
            }
            ?>
            var houseType = 2;
            //添加到收藏按钮绑定事件
            $('#add-collect-btn').click(function () {
                if ($(this).hasClass('collected')) {
                    CancelCollect('<?php echo $member_db;?>', <?php echo $dataInfo['id'];?>, houseType);
                } else {
                    AddCollect('<?php echo $member_db;?>', <?php echo $dataInfo['id'];?>, houseType, '<?php echo $dataInfo['article_title'];?>');
                }
            });

            //判断是否已收藏当前信息
            CheckIsCollected('<?php echo $member_db;?>', <?php echo $dataInfo['id'];?>, houseType);

            //房源图片切换展示
            var PictureObj = Picture.create({
                container: '#pic-show-box',
                leftArrow: '.prev-arrow',
                rightArrow: '.next-arrow'
            });

            AddClick('<?php echo $member_db;?>', <?php echo $dataInfo['id'];?>, houseType);

            <?php
            if($dataInfo['house_price'] == 0 && $dataInfo['column_type'] == 1 && empty($member_db)){
            ?>
            GetHouseData(2, <?php echo $dataInfo['id'];?>);
            <?php
            }
            ?>
        });
    });
</script>
</body>
</html>
