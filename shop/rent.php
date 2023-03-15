<?php
require_once(dirname(__FILE__) . '/path.inc.php');
require($cfg['path']['lib'] . 'classes/Pages.class.php');
$page->name = 'rent'; //页面名字,和文件名相同
$page->title = '';

$DdObj = new Dd($query);
$bright_spot_dict = $DdObj->getItemArray(25, true, MEMCACHE_MAX_EXPIRETIME);

//品牌公寓
$cache_key = 'brand_apartment_option_list';
$BrandApartment = new BrandApartment($query);

$houseRent = new HouseRent($member_query);
$where = "where mid='$id' and is_checked in (0,1) and is_delete = 0 AND is_down = 0 AND house_status IN (0,2,3,4,5)";
$list_num = intval($_GET['list_num']);
if(!$list_num){
	$list_num = 10;
}
$sql = "select count(*) from fke_houserent $where";
$row_count = SaveDataToMemcache($sql, $member_query, true, MEMCACHE_EXPIRETIME, MEMCACHE_PREFIX . 'shop_rlc_' . $id);
$pageno = intval($_GET['pageno']);
$pages = new Pages($row_count, $list_num);
$pageLimit = $pages->getLimit();
//分页链接
$url = 'list_'.$id.'_{pageno}_'.$keywords.'.html';
$pagePanel = $pages->get_pager_nav('4' , $pageno, $url);
$sql = "select id, mtype, is_cooperation, cityarea_id, cityarea2_id, city_website_id, column_type, house_price, house_month_price, house_totalarea, house_room, created, updated, unit_type, rent_type, column_type, house_month_price, house_title, borough_name, house_hall, house_toilet, house_toward, house_topfloor, house_floor, house_fitment, is_sublet, transfer_fee, brand_apartment_checked, brand_apartment_id, elevator, parking_lot, bright_spot, rent_require, house_thumb from `fke_houserent` $where order by id desc  limit {$pageLimit['rowFrom']}, $list_num";
$dataList = SaveDataToMemcache($sql, $member_query, false, MEMCACHE_EXPIRETIME, MEMCACHE_PREFIX . 'shop_rl_' . $id . '_' . $pageno);
foreach ($dataList as $key=> $item){
    $dataList[$key]['url'] = $houseRent->GetHouseURL($item, 'x');
    $dataList[$key]['house_thumb'] = GetPictureUrl($item['house_thumb'], 2, MEMBER_DB_INDEX);
	$dataList[$key]['created'] = MyDate('Y-m-d H:i',$item['created']);
	$dataList[$key]['updated'] = time2Units(time()-$item['updated']);
	$dataList[$key]['house_room'] = FormatHouseRoom($item['house_room']);
    //置顶信息
    $house_promote = $houseRent->table($houseRent->tNamePromote)->where('house_id = ' . $item['id'])->cache(MEMCACHE_PREFIX . 'rent_promote_' . $item['id'], MEMCACHE_EXPIRETIME)->one();
    $dataList[$key]['is_promote'] = $house_promote['end_time'] > time() ? 1 : 0;
    //取得品牌公寓
    if (in_array($item['mtype'], array(1, 3, 4)) && $item['column_type'] == 1 && $item['brand_apartment_id'] > 0 && $item['brand_apartment_checked'] > -1) {
        $condition = 'id = ' . $item['brand_apartment_id'] . ' AND is_checked IN (0,1)';
        $cache_key = 'brand_apartment_id_' . $item['brand_apartment_id'];
        $brand_apartment_data = $BrandApartment->table($BrandApartment->table_name)->where($condition)->cache($cache_key, MEMCACHE_MAX_EXPIRETIME)->one();
        $dataList[$key]['brand_apartment_name'] = $brand_apartment_data['brand_name'];
    }
    if ($item['column_type'] == 4 && $item['is_sublet'] == 1 && $item['transfer_fee'] > 0) {
        $dataList[$key]['transfer_fee'] = floatval($item['transfer_fee']);
        $dataList[$key]['is_sublet'] = intval($item['is_sublet']);
    }
    $dataList[$key]['house_price'] = FormatHousePrice($item['house_price']);
    $dataList[$key]['house_month_price'] = FormatHousePrice($item['house_month_price']);
}

$page->title = '【出租房源-'.$middle_column.$dataInfo['realname'].'电话'.$dataInfo['mobile'].'】-'.$page->titlec.$cityInfo['city_name'];
$page->description = $dataInfo['company'].$dataInfo['outlet_addr'].',工作区域'.str_replace('|', ' ', $dataInfo['servicearea']).$page->titlec.$cityInfo['city_name'];
$page->keywords = $middle_column.'、'.$dataInfo['realname'].'、电话'.$dataInfo['mobile'].',房产经纪人';
$websiteright = GetAdList(114, $query);
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
<script type="text/javascript" src="/js/f.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.list-house-thumb img').lazyload({effect: "fadeIn", threshold: 180});
    });
</script>
</head>

<body id="body">
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div id="main" class="shop-main">
    <div class="broker_detail">
        <?php require_once(dirname(__FILE__).'/top.php');?>
    </div>
    <div id="art_left">
       <div class="houselist list-box list-pic-text">
            <div id="list">
                <ul>
                    <?php
                    foreach($dataList as $item){
                        ?>
                        <li class="clear-fix">
                            <div class="list-house-thumb">
                                <a href="<?php echo $item['url'];?>" target="_blank"><img src="<?php echo !empty($item['house_thumb']) ? '/images/default_picture.jpg' : '/images/no_picture.jpg';?>" <?php echo !empty($item['house_thumb']) ? 'data-original="' . $item['house_thumb'] . '"' : '';?> /></a>
                            </div>
                            <div class="list-house-info clear-fix">
                                <div class="list-house-title">
                                    <a href="<?php echo $item['url'];?>" target="_blank"><?php echo $item['house_title'];?></a>
                                    <?php
                                    if ($item['mtype'] == 2) {
                                        //echo '<span class="member-type">(个人)</span>';
                                    } elseif ($item['mtype'] == 1 && $item['is_cooperation'] == 1) {
                                        echo '<span class="member-type">(合作)</span>';
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
                                        if ($house_toward[$item['house_toward']]) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">|</span>';
                                            }
                                            $tmp .= $house_toward[$item['house_toward']] . '向';
                                        }
                                        if ($item['house_topfloor'] > 0 && $item['house_floor'] > 0) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">|</span>';
                                            }
                                            //$tmp .= GetHouseFloor($item['house_floor'], $item['house_topfloor']) . '（共' . $item['house_topfloor'] . '层）';
                                            $house_floor = GetHouseFloor($item['house_floor'], $item['house_topfloor']);
                                            if ($item['house_topfloor'] <= 2) {
                                                $house_floor .= '共' . $item['house_topfloor'] . '层';
                                            } else {
                                                $house_floor .= '（共' . $item['house_topfloor'] . '层）';
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
                                        if ($item['column_type'] == 4 && $item['is_sublet'] == 1 && $item['transfer_fee'] > 0) {
                                            if ($tmp) {
                                                $tmp .= '<span class="split">|</span>';
                                            }
                                            $tmp .= '转让费' . $item['transfer_fee'] . '万元';
                                        }
                                        echo $tmp;
                                        ?></p>
                                    <?php
                                    if ($dataInfo['company'] || $dataInfo['realname'] || $item['brand_apartment_name']) {
                                        $tmp = $dataInfo['realname'] . '（' . $dataInfo['user_type_name']. '）';
                                        if ($tmp) {
                                            $tmp .= '<span class="split"></span>';
                                        }
                                        if ($item['brand_apartment_name']) {
                                            $tmp .= $item['brand_apartment_name'];
                                        } elseif ($item['company']) {
                                            $tmp .= $item['company'];
                                        }
                                        echo '<p>联系人： ' . $tmp . '</p>';
                                    }
                                    ?>
                                    <?php
                                    if ($item['column_type'] == 1) {
                                    ?>
                                    <p class="bright-spot"><?php
                                        /*if ($item['brand_apartment_id'] == 0) {
                                            echo '<span class="item">业主直租</span>';
                                            $bright_spot_length = 4;
                                        } else {
                                            $bright_spot_length = 5;
                                        }*/
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
                            if (in_array($item['column_type'], array(2, 3)) && $item['house_price'] > 0) {
                                echo '<div class="list-house-price-average">'.$item['house_month_price'].'元/月</div>';
                            }
                            ?>
                            <div class="list-time <?php echo (in_array($item['column_type'], array(2, 3, 4)) && $item['house_price'] > 0) ? 'list-time-2' : '';?>"><?php echo $item['updated'];?></div>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <div id="page">
                <div class="pager">
                <?php echo $pagePanel;?>
                </div>
            </div>
        </div>
   </div>
   <div id="right">
       <div id="banner_right"><?php echo $websiteright;?></div>
   </div>
   <div class="clear"></div>
</div>
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
</body>
</html>