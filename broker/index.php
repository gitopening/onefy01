<?php
require_once(dirname(__FILE__) . '/path.inc.php');

require_once($cfg['path']['lib'] . 'classes/Pages.class.php');
$currentColumn = 'broker';
$page->title = '【'.$cityInfo['city_name'].'房产经纪人|'.$cityInfo['city_name'].'房产中介公司】'.$page->titlec.$cityInfo['city_name'];
$page->description = ''.$cityInfo['city_name'].'第一时间房源网房产经纪人栏目提供大量'.$cityInfo['city_name'].'房产经纪人信息、'.$cityInfo['city_name'].'房产中介公司大量房产中介从业人员及房产经纪公司信息。';
$page->keywords = '房产中介、'.$cityInfo['city_name'].'房产中介、'.$cityInfo['city_name'].'房产中介公司、'.$cityInfo['city_name'].'房产经纪人、'.$cityInfo['city_name'].'房产经纪公司';
$param = explode('_', trim($_GET['param']));
$user_type = intval($param[0]);
if (!in_array($user_type, array(1, 3, 4))) {
    $user_type = 0;
}
$cityarea = intval($param[1]);
$cityarea2 = intval($param[2]);
$pageno = empty($param[3]) ? 1 : intval($param[3]);
$_GET['pageno'] = $pageno;
$keywords = trim($param[4]);
$keywords = preg_replace('/(1\d{2}) (\d{4}) (\d{4})/i', '$1$2$3', $keywords);
$cityarea_option = get_region_enum($cityInfo['city_id'], 'sort');
$cityarea2_option = get_region_enum($cityarea);
$user_type_option = array(
    0 => '不限',
    1 => '经纪人',
    4 => '品牌公寓',
    3 => '非中介机构'
);

$Sphinx = Sphinx::getInstance();
//搜索参数
$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
if (empty($keywords)) {
    $Sphinx->SetFilter('status', array(1));
}
$Sphinx->SetFilter('account_open', array(1));
if ($user_type) {
    $Sphinx->SetFilter('user_type', array($user_type));
} else {
    $Sphinx->SetFilter('user_type', array(1, 3, 4));
}
if ($cityarea) {
    $Sphinx->SetFilter('cityarea_id', array($cityarea));
}
if ($cityarea2) {
    $Sphinx->SetFilter('cityarea2_id', array($cityarea2));
}
if ($cityarea || $cityarea2) {
    $page->title = $cityarea_option[$cityarea]['region_name'] . $cityarea2_option[$cityarea2]['region_name'] . " - " . $page->title;
}
//$Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'last_login');
$Sphinx->SetSortMode(SPH_SORT_EXTENDED, 'status DESC,last_login DESC');

//$q = $_GET['q']=="可输入经纪人名、门店名，或公司名称关键词" ? "":trim($_GET['q']);
//list_num
$list_num = intval($_GET['list_num']);
if (!$list_num) {
    $list_num = 18;
}
//缓存Key
$list_key = MEMCACHE_PREFIX . 'mb_' . $cityInfo['id'] . '_' . $user_type . '_' . $cityarea . '_' . $cityarea2 . '_' . $list_num . '_' . $pageno . '_' . $keywords;
$count_key = MEMCACHE_PREFIX . 'mbc_' . $cityInfo['id'] . '_' . $user_type . '_' . $cityarea . '_' . $cityarea2 . '_' . $list_num . '_' . $keywords;
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
    if ($keywords) {
        $Sphinx->SetMatchMode(SPH_MATCH_PHRASE);
    }
    $result = $Sphinx->Query($keywords, SPHINX_SEARCH_MEMBER_INDEX);
    $pages = new Pages(intval($result['total']), $list_num);
    if ($result['total'] > 0) {
        $HouseRent = new HouseRent($member_query);
        $HouseSell = new HouseSell($member_query);
        $HouseQiuzu = new HouseQiuzu($member_query);
        $HouseQiugou = new HouseQiugou($member_query);

        $member_ids = array();
        foreach ($result['matches'] as $item) {
            $member_ids[] = $item['id'];
        }

        if (!empty($member_ids)) {
            $ids_str = implode(',', $member_ids);
            $sql = "select mem.id, mem.user_type, mem.user_type_sub, mem.user_type_custom, b.id, b.realname, b.mobile, b.avatar, b.company, b.outlet, b.outlet_addr, b.servicearea, b.introduce, b.status from `fke_member` as mem left join `fke_broker_info` as b on mem.id=b.id where mem.id in ($ids_str) and b.id in ($ids_str)";
            $dataMemberList = $member_query->select($sql);
            $member_list = array();
            foreach ($dataMemberList as $val) {
                $member_list[$val['id']] = $val;
            }
        }

        $dataList = array();
        foreach ($result['matches'] as $item) {
            if ($member_list[$item['id']]) {
                $member_list[$item['id']]['introduce'] = cn_substr_utf8($member_list[$item['id']]['introduce'], 120) . '...';
                $member_list[$item['id']]['url'] = $cfg['url_shop'] . $item['id'];
//                unset($member_list[$item['id']]['user_type']);
                unset($member_list[$item['id']]['idcard']);
                //统计房源数量
                $conditon = "mid='{$item['id']}' and is_checked in (0,1) and is_delete = 0 AND is_down = 0 AND house_status IN (0,2,3,4,5)";
                $house_rent_count = $HouseRent->table($HouseRent->tName)->where($conditon)->count();
                $house_sale_count = $HouseSell->table($HouseSell->tName)->where($conditon)->count();
                $house_qiuzu_count = $HouseQiuzu->table($HouseQiuzu->tName)->where($conditon)->count();
                $house_qiugou_count = $HouseQiugou->table($HouseQiugou->tName)->where($conditon)->count();
                $member_list[$item['id']]['house_count'] = intval($house_rent_count + $house_sale_count + $house_qiuzu_count + $house_qiugou_count);

                if ($member_list[$item['id']]['status'] == 0) {
                    $member_list[$item['id']]['avatar'] = '';
                }
                $dataList[] = $member_list[$item['id']];
            }
        }
        //存储数据到Memcache
        if ($dataList && $result['total']) {
            $result = $Cache->set($count_key, $result['total'], MEMCACHE_EXPIRETIME);
            $result = $Cache->set($list_key, $dataList, MEMCACHE_EXPIRETIME);
        }
    }
} else {
    $pages = new Pages($dataCount, $list_num);
}
//分页链接
$url = 'list_' . $user_type . '_' . $cityarea . '_' . $cityarea2 . '_{pageno}_' . $keywords . '.html';
$pagePanel = $pages->get_pager_nav('4', $pageno, $url);

//搜索参数设置
$curArea=isset($_GET['cityarea'])?intval($_GET['cityarea']):0;
$website_right_ad = GetAdList(113, $query);
$website_header_ad = GetAdList(184, $query);
//数据统计
//$write_query = create_write_query();
//$visitCount = new VisitCount($write_query);
//$visitCount->AddVisitCount($cityInfo['id'], 'broker', $cityarea, 0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page->title;?></title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<style type="text/css">
    .isStuck{
        border-bottom: 1px solid #e4e4e4;
        box-shadow: #efefef 2px 2px 2px;
    }
</style>
<script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/jquery.cookie.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body id="body">
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div class="header-search-wrap">
    <div class="header-search-box clear-fix">
        <div class="search-form">
            <form action="/broker/" method="get" id="rentsearch" onsubmit="return dosearch2();">
                <input type="hidden" name="url" id="url" value="<?php echo 'list_' . $user_type . '_'.$cityarea.'_'.$cityarea2.'_1_[keywords].html';?>" />
                <input type="text" id="text" name="q" value="<?php echo $keywords;?>" placeholder="请输入区域、商圈、小区、姓名或电话进行搜索"/>
                <div class="common-submit-btn search-submit-btn" onclick="dosearch2();">搜索</div>
                <div class="clear"></div>
            </form>
        </div>
        <div class="clear"></div>
    </div>
</div>
<div class="section-box">
    <div id="Region">
        <ul>
            <li>
                <div class="div01">区域：</div>
                <div class="div02">
                    <?php
                    $url = 'list_'  . $user_type. '_{curentparam}_0_1_.html';
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
                    $url = 'list_' . $user_type . '_' . $cityarea . '_{curentparam}_1_.html';
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
                <div class="div01">类型：</div>
                <div class="div02">
                    <?php
                    $url = 'list_{curentparam}_' . $cityarea . '_' . $cityarea2 . '_1_.html';
                    foreach ($user_type_option as $key => $val) {
                        if ($key == $user_type) {
                            echo '<span><a href="' . build_url($url, $key) . '">' . $val . '</a></span> ';
                        } else {
                            echo '<a href="' . build_url($url, $key) . '">' . $val . '</a> ';
                        }
                    }
                    ?>
                </div>
                <div class="clear"></div>
            </li>
        </ul>
    </div>
</div>
<div id="main" class="list-box broker-list-box">
    <div class="broker_left" id="art_left">
        <div class="broker-list-box">
            <?php
            foreach ($dataList as $key => $item) {
                ?>
                <a href="<?php echo $item['url']; ?>" target="_blank">
                    <div class="list-item">
                        <div class="broker_face">
                            <?php
                            if ($item['avatar']) {
                                ?>
                                <div class="image" style="background-image: url(<?php echo GetBrokerFace($item['avatar']); ?>);"></div>
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
                        <div class="right_info">
                            <div class="info_title">
                                <div class="broker_name">
                                    <span><?php echo $item['realname']; ?></span>
                                    <?php
                                    switch ($item['user_type']) {
                                        case 1:
                                            echo '(经纪人)';
                                            break;
                                        case 3:
                                            if ($item['user_type_sub'] == 1) {
                                                echo '(物业公司)';
                                            } elseif ($item['user_type_sub'] == 2) {
                                                echo '(开发商)';
                                            } elseif ($item['user_type_sub'] == 3) {
                                                echo '(拍卖机构)';
                                            } elseif ($item['user_type_sub'] == 4) {
                                                echo '(其它公司)';
                                            } else {
                                                echo '(非中介机构)';
                                            }
                                            break;
                                        case 4:
                                            echo '(品牌公寓)';
                                            break;
                                        default:

                                    }
                                    if ($item['house_count'] > 0) {
                                        echo '<span class="count">房源量：' . $item['house_count'] . '</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="info">
                                <p><?php echo $item['company']; ?> - <?php echo $item['outlet']; ?></p>

                                <p class="service"><?php echo str_replace('|', '　', $item['servicearea']); ?></p>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </a>
                <?php
            }
            ?>
            <div class="clear"></div>
        </div>
        <div id="page">
            <div class="pager">
                <?php echo $pagePanel; ?>
            </div>
        </div>
    </div>
    <div id="right">
        <div id="banner_right"><?php echo $website_right_ad;?></div>
    </div>
    <div class="clear"></div>
</div>
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
