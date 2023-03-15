<?php
require_once(dirname(__FILE__) . '/path.inc.php');
$id = intval($_GET['id']);
$member_db = trim($_GET['member_db']);
if ($id){
    $memcache_house_key = 'house_qiugou_'.$id;
    $memcache_house_update_key = 'house_qiugou_update_'.$id;
    if ($member_db == 'x') {
        $memcache_house_key = MEMCACHE_PREFIX . 'member_' . $memcache_house_key;
        $memcache_house_update_key = MEMCACHE_PREFIX . 'member_' . $memcache_house_update_key;
        $House = new HouseQiugou($member_query);
    } elseif ($member_db == 4) {
        $memcache_house_key = MEMCACHE_ZFY_PREFIX . 'member_' . $memcache_house_key;
        $memcache_house_update_key = MEMCACHE_ZFY_PREFIX . 'member_' . $memcache_house_update_key;
        //创建第三方会员数据库连接
        $member_db_config = GetConfig('member_db_' . $member_db);
        if (empty($member_db_config)) {
            header("http/1.1 404 not found");
            header("status: 404 not found");
            require_once(dirname(dirname(__FILE__)) . '/404.php');
            exit();
        }
        $house_query = new DbQueryForMysql($member_db_config);
        $House = new HouseQiugou($house_query);
    } else {
        $House = new HouseQiugou($query);
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

/*
$parent_id_array = array();
foreach ($cityarea_option as $value){
	$parent_id_array[] = $value['region_id'];
}
$paretnt_ids = implode(',', $parent_id_array);
$cityarea2_list = get_region_enum($paretnt_ids);
*/

//房源特色
$house_feature_option = Dd::getArray('house_feature');
//房屋类型
$houseTypeLists = Dd::getArray('house_type');

$dataInfo['house_room'] = FormatHouseRoomType($dataInfo['house_room'], 9);
$dataInfo['house_hall'] = FormatHouseRoomType($dataInfo['house_hall'], 9);
$dataInfo['house_toilet'] = FormatHouseRoomType($dataInfo['house_toilet'], 9);
$dataInfo['house_veranda'] = FormatHouseRoomType($dataInfo['house_veranda'], 9);
$dataInfo['house_price'] = FormatHousePrice($dataInfo['house_price']);
$dataInfo['start_price'] = FormatHousePrice($dataInfo['start_price']);
$dataInfo['end_price'] = FormatHousePrice($dataInfo['end_price']);
$dataInfo['house_type'] = $houseTypeLists[$dataInfo['house_type']];
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
$dataInfo['house_fitment'] =  Dd::getCaption('house_fitment',$dataInfo['house_fitment']);
$dataInfo['house_support'] =  Dd::getCaption('house_installation',$dataInfo['house_support']);
$dataInfo['house_deposit'] =  Dd::getCaption('rent_deposittype',$dataInfo['house_deposit']);
$dataInfo['borough_name'] = strip_tags($dataInfo['borough_name']);
$dataInfo['article_title'] = get_new_house_title($dataInfo, $cityarea_option, $cityarea2_list, $column['house_title'], 'qg');
if (in_array($dataInfo['mtype'], array(1, 3, 4)) && $dataInfo['is_cooperation'] == 1) {
    $dataInfo['article_title'] .= '(合作)';
}

//浏览过的房源
$history_list = add_history_list($_GET['column'] . 'house_qiugou', $dataInfo, $member_db);

//增加当前栏目浏览数计数
AddBrowserCount($_GET['column']);

//添加到浏览历史记录中
if ($member_id) {
    $BrowsingHistory = new BrowsingHistory($member_query);
    $db_index = $member_db == 'x' ? WEBHOSTID : intval($member_db);
    $BrowsingHistory->Add($member_id, $db_index, 4, $dataInfo);
}

$page->title = '【'.$dataInfo['article_title'].'】'.$page->titlec.$cityInfo['city_name'];
$page->description = '最新最全大量' . $cityInfo['city_name'] . $dataInfo['cityarea_name'] . '求购' . $column['house_title'] . '信息,个人求购' . $column['house_title'] . '信息，找' . $column['house_title'] . '求购信息、发布求购' . $column['house_title'] . '信息就上' . $page->titlec;
$page->keywords = '求购' . $dataInfo['cityarea_name'] . $column['house_title'] . '、个人求购' . $column['house_title'] . '、' . $column['house_title'] . '求购信息';

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
$website_right_hot_search_ad = GetAdList(232, $query);
$website_right_news_ad = GetAdList(233, $query);

//相近价位的房源
/*
$mtype = 0;
$same_list_key = 'qglist_' . $cityInfo['id'] . '_' . $column['column_type'] . '_' . $mtype . '_' . $dataInfo['cityarea_id'] . '_' . $dataInfo['cityarea2_id'] . '_' . $dataInfo['house_price'];
$same_house = $memcache->get($same_list_key);
if (empty($same_house)) {
    if ($dataInfo['house_price']) {
        $prcent_10 = $dataInfo['house_price'] * 0.1;
        $start_price = $dataInfo['house_price'] - $prcent_10 < 0 ? 0 : $dataInfo['house_price'] - $prcent_10;
        $end_price = $dataInfo['house_price'] + $prcent_10;
		$sphinx->SetFilterRange('house_price', $start_price, $end_price);
    } else {
        $prcent_10 = $dataInfo['end_price'] * 0.1;
        $start_price = $dataInfo['end_price'] - $prcent_10 < 0 ? 0 : $dataInfo['end_price'] - $prcent_10;
        $end_price = $dataInfo['end_price'] + $prcent_10;
		$sphinx->SetFilterRange('end_price', $start_price, $end_price);
    }

    $sphinx->SetFilter('column_type', array($column['column_type']));
    $sphinx->SetFilter('city_website_id', array($cityInfo['id']));
    if ($dataInfo['cityarea2_id']){
        $sphinx->SetFilter('cityarea2_id', array($dataInfo['cityarea2_id']));
    }elseif ($dataInfo['cityarea_id']){
        $sphinx->SetFilter('cityarea_id', array($dataInfo['cityarea_id']));
    }
    $sphinx->SetFilter('filter_id', array($id), true); //去除本条记录
    $sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'updated');	//按更新时间降序
    $sphinx->SetLimits(0, 20, 20);
    $result = $sphinx->Query('', 'search_house_qiugou,search_house_qiugou_delta,member_house_qiugou,member_house_qiugou_delta');
    if ($result['total'] > 0){
        $house_id = array();
        $member_house_id = array();
        foreach ($result['matches'] as $val){
            if ($val['attrs']['is_use_member_db'] == 1) {
                $member_house_id[] = $val['id'];
            } else {
                $house_id[] = $val['id'];
            }
        }
        $house_ids = implode(',', $house_id);
        if ($house_ids){
            $sql = "select h.id, h.house_room, h.house_price, h.start_price, h.end_price, h.created, h.updated, h.cityarea_id, h.cityarea2_id, h.house_totalarea, h.start_totalarea, h.end_totalarea, h.mtype, e.house_id, e.house_hall, e.house_toilet, e.house_title, e.house_address, e.borough_name, e.owner_phone from fke_qiugou as h left join fke_qiugou_extend as e on h.id = e.house_id where h.id in ($house_ids) and e.house_id in ($house_ids)";
            $dataList = $query->select($sql);
            $house_list = array();
            foreach ($dataList as $val) {
                $house_list[$val['id']] = $val;
            }
        }
        $member_house_ids = implode(',', $member_house_id);
        if ($member_house_ids) {
            $sql = "select h.id, h.house_room, h.house_price, h.start_price, h.end_price, h.created, h.updated, h.cityarea_id, h.cityarea2_id, h.house_totalarea, h.start_totalarea, h.end_totalarea, h.mtype, e.house_id, e.house_hall, e.house_toilet, e.house_title, e.house_address, e.borough_name, e.owner_phone from fke_qiugou as h left join fke_qiugou_extend as e on h.id = e.house_id where h.id in ($member_house_ids) and e.house_id in ($member_house_ids)";
            $dataList = $member_query->select($sql);
            $member_house_list = array();
            foreach ($dataList as $val) {
                $member_house_list[$val['id']] = $val;
            }
        }
        $same_house = array();
        foreach ($result['matches'] as $item) {
            if ($item['attrs']['is_use_member_db'] == 1) {
                if($member_house_list[$item['id']]){
                    //处理数据
                    $member_house_list[$item['id']]['url'] = 'house_' . $item['id'] . 'x.html';
                    $member_house_list[$item['id']]['updated'] = time2Units(time()-$item['attrs']['updated']);
                    $member_house_list[$item['id']]['title'] = get_new_house_title($member_house_list[$item['id']], $cityarea_option, $cityarea2_list, $column['house_title'], 'qg');
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
                    $same_house[] = $member_house_list[$item['id']];
                }
            } else {
                if($house_list[$item['id']]){
                    //处理数据
                    $house_list[$item['id']]['url'] = 'house_' . $item['id'] . '.html';
                    $house_list[$item['id']]['updated'] = time2Units(time()-$item['attrs']['updated']);
                    $house_list[$item['id']]['title'] = get_new_house_title($house_list[$item['id']], $cityarea_option, $cityarea2_list, $column['house_title'], 'qg');
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
                    $same_house[] = $house_list[$item['id']];
                }
            }
        }

        //存储到MemCache
        if ($same_house) {
            $result = $memcache->set($same_list_key, $same_house, MEMCACHE_COMPRESS, MEMCACHE_EXPIRETIME);
        }
    }
}
$same_house_count = count($same_house);
*/
//判断经纪人和非中介机构是否已认证
if ($dataInfo['mid'] && $member_db == 'x') {
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
        }

        if (in_array($broker_info['user_type'], array(1, 3, 4))) {
            $broker_info['servicearea'] = str_replace('|', ' &nbsp; ', $broker_info['servicearea']);
        }

        if ($broker_info['status'] == 0) {
            $broker_info['avatar'] = '';
        }
        $broker_info['avatar'] = GetBrokerFace($broker_info['avatar']);
    }
}

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
<meta name="format-detection" content="telephone=no" />
<title><?php echo $page->title;?></title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<script type="text/javascript" src="/js/jquery-1.11.0.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body>
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div id="main">
    <div id="left">
        <div id="content">
            <div class="position">
                <a href="/"><?php echo $page->titlec . $cityInfo['city_name'];?></a> &gt;
                <a href="/<?php echo $_GET['column'];?>/"><?php echo $cityInfo['city_name'] . '求购' . $column['house_title'];?></a> &gt;
                <?php
                if ($dataInfo['cityarea_id']) {
                    ?>
                    <a href="list_<?php echo $dataInfo['mtype'];?>_<?php echo $dataInfo['cityarea_id'];?>.html"><?php echo $dataInfo['cityarea_name'] . '求购' . $column['house_title'];?></a> &gt;
                <?php
                }
                if ($dataInfo['cityarea_id'] && $dataInfo['cityarea2_id']) {
                    ?>
                    <a href="list_<?php echo $dataInfo['mtype'];?>_<?php echo $dataInfo['cityarea_id'];?>_<?php echo $dataInfo['cityarea2_id'];?>.html"><?php echo $dataInfo['cityarea2_name'] . '求购' . $column['house_title'];?></a> &gt;
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
            <h1><?php
                echo $dataInfo['article_title'];
                echo $dataInfo['mtype'] == 2 ? '(个人)' : '';
                if ($dataInfo['mtype'] == 1 && $dataInfo['is_cooperation'] == 1) {
                    echo '(合作)';
                }
                ?></h1>
            <div class="l_fy">
                <p>房源编号：qg<?php echo $dataInfo['id'];?> &nbsp;&nbsp;&nbsp;&nbsp;更新时间：<?php echo $dataInfo['updatetime'];?> &nbsp;&nbsp;&nbsp;&nbsp;<span id="click-number"></span></p>
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
            <div class="main-content">
                <div class="cr_left">
                    <dl>
                        <dt>求购价格：</dt>
                        <dd><?php
                            if ($dataInfo['house_price'] == 0) {
                                if ($dataInfo['start_price'] > 0 || $dataInfo['end_price'] > 0) {
                                    echo get_range_str($dataInfo['start_price'], $dataInfo['end_price'], ' 万元', '<strong class="price">{content}</strong>');
                                } else {
                                    echo '<strong class="price">面议</strong>';
                                }
                            } else {
                                echo '<strong class="price">' . $dataInfo['house_price'] . '</strong> ' . ' 万元';
                            }
                            ?></dd>
                    </dl>
                    <?php
                    if ($dataInfo['column_type'] == 1) {
                        ?>
                        <dl>
                            <dt>期望户型：</dt>
                            <dd><?php
                                if ($dataInfo['rent_type'] == 2) {
                                    echo '合租';
                                } else {
                                    if ($dataInfo['house_room']) {
                                        echo $dataInfo['house_room'] . '室 ';
                                        if ($dataInfo['house_hall']) {
                                            echo $dataInfo['house_hall'] . '厅 ';
                                        }
                                        if ($dataInfo['house_toilet']) {
                                            echo $dataInfo['house_toilet'] . '卫';
                                        }
                                    } else {
                                        echo '不限';
                                    }
                                }
                                ?></dd>
                        </dl>
                        <?php
                    }
                    ?>
                    <dl>
                        <dt>期望面积：</dt>
                        <dd><?php
                            if ($dataInfo['house_totalarea'] > 0) {
                                echo $dataInfo['house_totalarea'] . ' ㎡';
                            } else {
                                echo get_range_str($dataInfo['start_totalarea'], $dataInfo['end_totalarea'], ' ㎡', '{content}');
                            }
                            ?>
                        </dd>
                    </dl>
                    <?php
                    if ($dataInfo['borough_name'] && $dataInfo['column_type'] == 1) {
                        ?>
                        <dl>
                            <dt>期望小区：</dt>
                            <dd><?php echo $dataInfo['borough_name']; ?></dd>
                        </dl>
                        <?php
                    }
                    ?>
                    <dl>
                        <dt>期望地址：</dt>
                        <dd><?php
                            echo $cityInfo['city_name'];
                            if ($dataInfo['cityarea_name']) {
                                echo ' &nbsp; ' . $dataInfo['cityarea_name'];
                            }
                            if ($dataInfo['cityarea2_name']) {
                                echo ' &nbsp; ' . $dataInfo['cityarea2_name'];
                            }
                            if ($dataInfo['house_address']) {
                                echo ' &nbsp; ' . $dataInfo['house_address'];
                            }
                            ?></dd>
                    </dl>
                    <?php
                    if ($dataInfo['house_diduan']) {
                        ?>
                        <dl>
                            <dt>期望地段：</dt>
                            <dd><?php echo $dataInfo['house_diduan']; ?></dd>
                        </dl>
                        <?php
                    }
                    ?>
                    <?php
                    if ($broker_info['status'] != 1 || $broker_info['account_open'] == 0) {
                        ?>
                        <dl>
                            <dt>联<span class="letter-center">系</span>人：</dt>
                            <dd><?php
                                if ($broker_info) {
                                    if ($broker_info['shop_url']) {
                                        echo '<a href="' . $broker_info['shop_url'] . '" target="_blank" class="contact-link">' . $broker_info['realname'] . '</a>';
                                    } else {
                                        if ($broker_info['realname']) {
                                            echo $broker_info['realname'];
                                        } else {
                                            echo $dataInfo['owner_name'];
                                        }
                                    }

                                    if ($broker_info['user_type'] == 1) {
                                        echo '（经纪人）';
                                    } elseif ($broker_info['user_type'] == 2) {
                                        echo '（个人）';
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
                                        echo '个人';
                                    } else {
                                        echo $dataInfo['owner_name'];
                                    }

                                    if ($dataInfo['mtype'] == 1) {
                                        echo '（经纪人）';
                                    } elseif ($dataInfo['mtype'] == 2 && !empty($dataInfo['owner_name'])) {
                                        echo '（个人）';
                                    } elseif ($dataInfo['mtype'] == 3) {
                                        echo '（非中介机构）';
                                    } elseif ($dataInfo['mtype'] == 4) {
                                        echo '（品牌公寓）';
                                    }
                                }
                                ?></dd>
                        </dl>
                        <?php
                        if ($broker_info['company'] && in_array($broker_info['user_type'], array(1, 3, 4))) {
                            ?>
                            <dl>
                                <dt>所属公司：</dt>
                                <dd><?php echo $broker_info['company']; ?></dd>
                            </dl>
                            <?php
                        }
                        if ($dataInfo['house_status'] == 1) {
                            echo '<dl><dd class="house-done">该房源已删除</dd></dl>';
                        } elseif ($dataInfo['house_status'] == 5) {
                            echo '<dl><dd class="house-done">房源已成交</dd></dl>';
                        }
                    }
                    ?>
                    <?php
                    //$is_login = $member->checkLogin();
                    if (!in_array($dataInfo['house_status'], array(1, 5))) {
                        ?>
                        <dl>
                            <dt>联系电话：</dt>
                            <dd>
                                <?php
                                if (!empty($dataInfo['source_url']) && empty($dataInfo['owner_phone']) && empty($dataInfo['owner_phone_pic'])) {
                                    if ($dataInfo['source_id'] == 1) {
                                        $source_name = '58同城网';
                                    } elseif ($dataInfo['source_id'] == 2) {
                                        $source_name = '赶集网';
                                    } elseif ($dataInfo['source_id'] == 5) {
                                        $source_name = '房天下网';
                                    } else {
                                        $source_name = '其它来源';
                                    }
                                    echo '<a href="' . $dataInfo['source_url'] . '" target="_blank" rel="nofollow">查看联系方式</a><br />该房源来自' . $source_name;
                                } elseif (!empty($dataInfo['owner_phone'])) {
                                    echo '<span class="telephone">' . $dataInfo['owner_phone'] . '</span>';
                                } elseif (!empty($dataInfo['owner_phone_pic'])) {
                                    echo '<img src="' . $dataInfo['owner_phone_pic'] . '">';
                                }
                                ?>
                            </dd>
                        </dl>
                        <?php
                        if ($dataInfo['hide_phone'] == 1 && $dataInfo['mtype'] == 2) {
                            ?>
                            <dl>
                                <dt></dt>
                                <dd>（该用户已设置隐藏电话，您可以通过微信联系）</dd>
                            </dl>
                            <?php
                        }
                        ?>
                        <?php
                        if ($dataInfo['wechat']) {
                            ?>
                            <dl>
                                <dt>微　　信：</dt>
                                <dd class="font16"><?php echo $dataInfo['wechat']; ?></dd>
                            </dl>
                            <?php
                        }
                        ?>
                        <?php
                        if ($dataInfo['qq']) {
                            ?>
                            <dl>
                                <dt>QQ：</dt>
                                <dd class="font16"><?php echo $dataInfo['qq']; ?></dd>
                            </dl>
                            <?php
                        }
                        ?>
                        <?php
                    }
                    ?>
                    <?php
                    if ($broker_info['status'] == 1 && $broker_info['account_open'] == 1) {
                        ?>
                        <div class="broker-info-box clear-fix">
                            <div class="broker-left">
                                <div class="broker-face">
                                    <?php
                                    if ($broker_info['shop_url']) {
                                        ?>
                                        <a href="<?php echo $broker_info['shop_url']; ?>" target="_blank"><img src="<?php echo $broker_info['avatar']; ?>"/></a>
                                        <?php
                                    } else {
                                        ?>
                                        <img src="<?php echo $broker_info['avatar']; ?>"/>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                                if ($broker_info['shop_url']) {
                                    ?>
                                    <div class="broker-name"><a href="<?php echo $broker_info['shop_url']; ?>" target="_blank">进入他的店铺</a></div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="broker-right">
                                <div class="contact-name"><?php
                                    if ($broker_info['shop_url']) {
                                        echo '<strong><a href="' . $broker_info['shop_url'] . '" target="_blank">' . $broker_info['realname'] . '</a></strong>';
                                    } else {
                                        echo '<strong>' . $broker_info['realname'] . '</strong>';
                                    }
                                    if ($broker_info['user_type'] == 1) {
                                        echo '（经纪人）';
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
                                    ?></div>
                                <div class="company"><?php echo $broker_info['company'] . ' &nbsp; ' . $broker_info['outlet']; ?></div>
                                <div class="service-area"><?php echo $broker_info['servicearea'];?></div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <?php
                    if (!in_array($dataInfo['house_status'], array(1, 5)) && empty($memberInfo['wechat_openid']) && $show_wechat_btn == true) {
                        ?>
                        <div class="show-phone-box" id="show-phone-box">
                            <div class="show-phone-btn clear-fix">
                                <div class="icon-phone"></div>
                                <div class="title">用微信扫码查看联系方式</div>
                            </div>
                            <div class="wechat-qrcode-container clear-fix" id="wechat-qrcode-container">
                                <div class="qrcode-container" id="qrcode-container">
                                    <div class="message"></div>
                                </div>
                                <div class="tips">
                                    用微信扫码查看联系方式<br/>
                                    (需关注公众号后查看)
                                </div>
                            </div>
                        </div>
                        <script type="text/javascript" src="../js/wechat.js?v=<?php echo $webConfig['static_version']; ?>"></script>
                        <script type="text/javascript">
                            var Wechat = Wechat.create({
                                container: '#wechat-qrcode-container',
                                type: 2,
                                memberDB: 1,
                                callback: function (data) {
                                    if (data.error == 0) {
                                        layer.msg('扫码登录成功', {
                                            icon: 1,
                                            time: 2000
                                        }, function () {
                                            window.location.reload();
                                        });
                                    } else {
                                        layer.alert('系统错误，请联系管理员');
                                    }
                                }
                            });

                            $('#show-phone-box').hover(function () {
                                $(this).find('.wechat-qrcode-container').show();
                                Wechat.showQrcode();
                            }, function () {
                                $(this).find('.wechat-qrcode-container').hide();
                            });
                        </script>
                        <?php
                    }
                    ?>
                </div>
                <div class="cr_right"><?php echo $website_detail_ad; ?></div>
                <div class="clear"></div>
            </div>
            <div class="house-notice"><i class="notice-icon"><img src="/images/notice-icon.png"/></i>郑重提示：请您认真查看房产证、身份证等证件信息，在签订合同前请勿支付任何形式的费用，以免上当受骗！
            </div>
            <div class="infoitem">
                <h2 id="house-desc"><?php echo $dataInfo['column_type'] != 4 ? '房源' : ''; ?>描述</h2>

                <div class="des"><?php echo $dataInfo['house_desc']; ?></div>
                <div class="content-tips">联系我时，请说是在第一时间房源网上看到的，谢谢！</div>
                <a id="same-price-house-tag"></a>
                <?php
                if ($info_bottom_ad) {
                    echo '<div class="info-bottom-ad">' . $info_bottom_ad . '</div>';
                }
                ?>
            </div>
        </div>
        <?php
        if ($same_house) {
            ?>
            <div id="same-price-house">
                <h2><?php echo $dataInfo['column_type'] != 4 ? '该地区价格相近的信息' : '价格相近的信息';?></h2>
                <ul class="data-list">
                    <?php
                    for ($i = 0; $i < $same_house_count; $i++) {
                        $house_price = FormatHousePrice($same_house[$i]['house_price']);
                        $start_price = FormatHousePrice($same_house[$i]['start_price']);
                        $end_price = FormatHousePrice($same_house[$i]['end_price']);
                        ?>
                        <li>
                            <div class="house-title"><a href="<?php echo $same_house[$i]['url'];?>" target="_blank"><?php echo $same_house[$i]['title'];?></a></div>
                            <div class="house-room"><?php echo $same_house[$i]['house_room'];?> 室</div>
                            <div class="house-price"><?php
                                if ($house_price) {
                                    if ($start_price > 0 || $end_price > 0) {
                                        echo get_range_str($start_price, $end_price, ' 万元', '<strong class="blues">{content}</strong>');
                                    } else {
                                        echo '<strong class="blues">面议</strong>';
                                    }
                                } else {
                                    echo '<strong class="blues">' . $house_price . '</strong> ' . ' 万元';
                                }
                                ?></div>
                            <div class="update-time"><?php echo $same_house[$i]['updated']; ?></div>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
        <?php
        }
        if ($website_same_house_bottom_ad) {
            echo '<div class="same-house-bottom-ad">' . $website_same_house_bottom_ad . '</div>';
        }
        ?>
    </div>
    <div id="right">
        <div id="top-ad"><?php echo $website_right_ad_1;?></div>
        <?php
        if ($same_house) {
            ?>
            <div class="columns">
                <h2><a href="#same-price-house-tag"><?php echo $dataInfo['column_type'] != 4 ? '该地区价格相近的信息' : '价格相近的信息';?></a><a href="#same-price-house-tag" class="more">更多&gt;&gt;</a></h2>
                <ul class="data-list">
                    <?php
                    $length = $same_house_count > 5 ? 5 : $same_house_count;
                    for ($i = 0; $i < $length; $i++) {
                        $house_price = FormatHousePrice($same_house[$i]['house_price']);
                        $start_price = FormatHousePrice($same_house[$i]['start_price']);
                        $end_price = FormatHousePrice($same_house[$i]['end_price']);
                        ?>
                        <li>
                            <div class="house-title"><a href="<?php echo $same_house[$i]['url'];?>" target="_blank"><?php echo $same_house[$i]['title'];?></a></div>
                            <div class="house-room"><?php echo $same_house[$i]['house_room'];?> 室</div>
                            <div class="house-price"><?php
                                if ($house_price == 0) {
                                    if ($start_price > 0 || $end_price > 0) {
                                        echo get_range_str($start_price, $end_price, ' 万元', '<strong class="blues">{content}</strong>');
                                    } else {
                                        echo '<strong class="blues">面议</strong>';
                                    }
                                } else {
                                    echo '<strong class="blues">' . $house_price . '</strong> ' . ' 万元';
                                }
                                ?></div>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
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
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
<script type="text/javascript">
    jQuery(function($) {
        $(document).ready( function() {
            var houseType = 4;
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
            AddClick('<?php echo $member_db;?>', <?php echo $dataInfo['id'];?>, houseType);
        });
    });
</script>
</body>
</html>
