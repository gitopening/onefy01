<?php
require_once(dirname(dirname(__FILE__)) . '/common.inc.php');
JumpToCurrentWebsite();
$column = GetColumnData();
$currentColumn = $column['column'];

//取得所有参数
$id = intval($_GET['id']);
$column = trim($_GET['column']);
$member_db = trim($_REQUEST['member_db']);

if (empty($column) || empty($id)) {
    header("http/1.1 404 not found");
    header("status: 404 not found");
    require_once(dirname(dirname(__FILE__)) . '/404.php');
    exit();
    //$page->Rdirect('/', '您访问的页面不存在或已被删除');
}
//设置当前栏目变量
switch ($column) {
    case 'rent':
        $currentColumn = 'rent';
        break;
    case 'xzlcz':
        $currentColumn = 'xzl';
        break;
    case 'spcz':
        $currentColumn = 'sp';
        break;
    case 'cwcz':
    case 'cwzr':
        $currentColumn = 'cw';
        break;
    case 'cfcz':
    case 'cfzr':
        $currentColumn = 'cf';
        break;
    case 'ckcz':
    case 'ckzr':
        $currentColumn = 'ck';
        break;
    case 'sale':
        $currentColumn = 'sale';
        break;
    case 'xzlcs':
        $currentColumn = 'xzl';
        break;
    case 'spcs':
        $currentColumn = 'sp';
        break;
    case 'cwcs':
        $currentColumn = 'cw';
        break;
    case 'cfcs':
        $currentColumn = 'cf';
        break;
    case 'ckcs':
        $currentColumn = 'ck';
        break;
    case 'qiuzu':
        $currentColumn = 'rent';
        break;
    case 'spqz':
        $currentColumn = 'sp';
        break;
    case 'xzlqz':
        $currentColumn = 'xzl';
        break;
    case 'cwqz':
        $currentColumn = 'cw';
        break;
    case 'cfqz':
        $currentColumn = 'cf';
        break;
    case 'ckqz':
        $currentColumn = 'ck';
        break;
    case 'qiugou':
        $currentColumn = 'sale';
        break;
    case 'spqg':
        $currentColumn = 'sp';
        break;
    case 'xzlqg':
        $currentColumn = 'xzl';
        break;
    case 'cwqg':
        $currentColumn = 'cw';
        break;
    case 'cfqg':
        $currentColumn = 'cf';
        break;
    case 'ckqg':
        $currentColumn = 'ck';
        break;
    case 'new':
        $currentColumn = 'new';
        break;
    case 'zzx':
        $currentColumn = 'new';
        break;
    case 'xzlx':
        $currentColumn = 'new';
        break;
    case 'spx':
        $currentColumn = 'new';
        break;
    case 'job':
        $currentColumn = 'job';
        break;
    default:
        header("http/1.1 404 not found");
        header("status: 404 not found");
        require_once(dirname(dirname(__FILE__)) . '/404.php');
        exit();
        //$page->Rdirect('/', '您访问的页面不存在或已被删除');
}

if ($member_db == 'x') {
    $house_query = $member_query;
} elseif ($member_db > 0) {
    $house_query = new DbQueryForMysql(GetConfig('member_db_' . $member_db));
} else {
    $house_query = $query;
}

if (in_array($column, array('rent', 'xzlcz', 'spcz', 'cwcz', 'cwzr', 'cfcz', 'cfzr', 'ckcz', 'ckzr'))) {
    $memcache_house_key = 'house_rent_' . $id;
    $memcache_house_pic_key = 'house_rent_pic_' . $id;
    $house_type = 1;
    $House = new HouseRent($house_query);
} elseif (in_array($column, array('sale', 'xzlcs', 'spcs', 'cwcs', 'cfcs', 'ckcs'))) {
    $memcache_house_key = 'house_sale_' . $id;
    $memcache_house_pic_key = 'house_sale_pic_' . $id;
    $house_type = 2;
    $House = new HouseSell($house_query);
} elseif (in_array($column, array('qiuzu', 'xzlqz', 'spqz', 'cwqz', 'cfqz', 'ckqz'))) {
    $memcache_house_key = 'house_qiuzu_' . $id;
    $memcache_house_pic_key = 'house_qiuzu_pic_' . $id;
    $house_type = 3;
    $House = new HouseQiuzu($house_query);
} elseif (in_array($column, array('qiugou', 'xzlqg', 'spqg', 'cwqg', 'cfqg', 'ckqg'))) {
    $memcache_house_key = 'house_qiugou_' . $id;
    $memcache_house_pic_key = 'house_qiugou_pic_' . $id;
    $house_type = 4;
    $House = new HouseQiugou($house_query);
} elseif (in_array($column, array('new', 'zzx', 'xzlx', 'spx'))) {
    $memcache_house_key = 'house_new_' . $id;
    $memcache_house_pic_key = 'house_new_pic_' . $id;
    $house_type = 5;
    $House = new HouseNew($house_query);
} elseif (in_array($column, array('job'))) {
    $memcache_house_key = 'job_' . $id;
    $memcache_house_pic_key = 'job_pic_' . $id;
    $house_type = 7;
    $House = new Job($house_query);
}

//会员房源缓存Key
if ($member_db == 'x') {
    $memcache_house_key = MEMCACHE_PREFIX . 'member_' . $memcache_house_key;
    $memcache_house_pic_key = MEMCACHE_PREFIX . 'member_' . $memcache_house_pic_key;
} elseif ($member_db == 4) {
    $memcache_house_key = MEMCACHE_ZFY_PREFIX . 'member_' . $memcache_house_key;
    $memcache_house_pic_key = MEMCACHE_ZFY_PREFIX . 'member_' . $memcache_house_pic_key;
}

//处理数据
if ($column == 'job') {
    $condition = "id = '$id' and city_website_id = '{$cityInfo['id']}' and is_checked in (0,1) and is_delete = 0 AND is_down = 0";
    $dataInfo = $House->table($House->tName)->where($condition)->cache($memcache_house_key, MEMCACHE_MAX_EXPIRETIME)->one();
} else {
    $condition = "house.id = '$id' and house.city_website_id = '{$cityInfo['id']}' and is_checked in (0,1) and is_delete = 0 AND is_down = 0";
    $dataInfo = $House->field('house.*, extend.*')->table($House->tName, 'house')->join($House->db_prefix . $House->tNameExtend . ' AS extend ON house.id = extend.house_id', 'LEFT')->where($condition)->cache($memcache_house_key, MEMCACHE_MAX_EXPIRETIME)->one();
}