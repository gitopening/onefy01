<?php
require_once(dirname(dirname(__FILE__)) . '/common.inc.php');
JumpToCurrentWebsite();
$page->city = $cityInfo['city_name'];
$currentColumn = 'broker';
$page->dir  = 'shop';//目录名
$id = intval($_REQUEST['id']);
if(empty($id)){
	header("http/1.1 404 not found");
	header("status: 404 not found");
	require_once(dirname(dirname(__FILE__)) . '/404.php');
	exit();
}
$dataInfo = $member->getInfo($id,'*',true);
if(!$dataInfo || $dataInfo['account_open'] == 0 || $dataInfo['city_website_id'] != $cityInfo['id']){
//	@header("http/1.1 404 not found");
//	@header("status: 404 not found");
    $page->Rdirect('/broker/', '店铺信息不存在！');
}

$dataInfo['active_str'] = explode('|',$dataInfo['active_str']);
$dataInfo['regdate'] = date('y-m-d',$dataInfo['add_time']);
//$dataInfo['mobile'] = substr($dataInfo['mobile'], 0, 3).' '.substr($dataInfo['mobile'], 3, 4).' '.substr($dataInfo['mobile'], 7);

if ($dataInfo['user_type'] == 1) {
    $dataInfo['user_type_name'] =  '经纪人';
} elseif ($dataInfo['user_type'] == 3) {
    if ($dataInfo['user_type_sub'] == 1) {
        $dataInfo['user_type_name'] = '物业公司';
    } elseif ($dataInfo['user_type_sub'] == 2) {
        $dataInfo['user_type_name'] = '开发商';
    } elseif ($dataInfo['user_type_sub'] == 3) {
        $dataInfo['user_type_name'] = '拍卖机构';
    } elseif ($dataInfo['user_type_sub'] == 4) {
        $dataInfo['user_type_name'] = '其它公司';
    } else {
        $dataInfo['user_type_name'] = '非中介机构';
    }
} elseif ($dataInfo['user_type'] == 4) {
    $dataInfo['user_type_name'] = '品牌公寓';
} elseif ($dataInfo['user_type'] == 2) {
    $dataInfo['user_type_name'] = '个人';
}

if ($dataInfo['status'] == 0) {
    $dataInfo['avatar'] = '';
}

//手机格式化
if ($dataInfo['mobile']) {
    $dataInfo['mobile_format'] = substr($dataInfo['mobile'], 0, 3) . '<span class="mobile-split">' . substr($dataInfo['mobile'], 3, 4) . '</span>' . substr($dataInfo['mobile'], 7);
}

//广告
//$websiteright = GetAdList(70, $query);
//$websitefoot = GetAdList(65, $query);
$cityarea_option = get_region_enum($cityInfo['city_id']);
$parent_id_array = array();
foreach ($cityarea_option as $value){
	$parent_id_array[] = $value['region_id'];
}
$cityarea2_list = get_region_enum($parent_id_array);

$condition = array(
    'id' => intval($dataInfo['city_website_id'])
);
$broker_city_info = $query->field('id, url_name, province_id, city_id')->table('city_website')->where($condition)->cache(true)->one();

if ($dataInfo['user_type'] == 2) {
    $middle_column = '个人房源|租房|二手房';
} else {
    $middle_column = $dataInfo['company'] . '房产中介公司';
}

//所在区域
$region_array = [];
/*$province = $city = $query->table('region')->where('region_id = ' . $dataInfo['province_id'])->one();
if ($province['region_name']) {
    $region_array[] = $province['region_name'];
}*/
$city = $query->table('region')->where('region_id = ' . $dataInfo['city_id'])->one();
if ($city['region_name']) {
    $region_array[] = $city['region_name'];
}
$cityarea = $query->table('region')->where('region_id = ' . $dataInfo['cityarea_id'])->one();
if ($cityarea['region_name']) {
    $region_array[] = $cityarea['region_name'];
}
$cityarea2 = $query->table('region')->where('region_id = ' . $dataInfo['cityarea2_id'])->one();
if ($cityarea2['region_name']) {
    $region_array[] = $cityarea2['region_name'];
}
$region = implode(' - ', $region_array);