<?php
/**
 * 官方网站 http://www.hictech.cn
 * Copyright (c) 2017 河北海聪科技有限公司 All rights reserved.
 * Licensed (http://www.hictech.cn/licenses)
 * Author net <geow@qq.com>
 * Desc 自动下架30天前的过期房源
 */

$access_key = 'LsDSScczJSCaBoivJwpA22w2g54PnvNBkUllcOat';
if ($access_key != $_GET['access_key']) {
    exit('Access Denied');
}
require_once(dirname(dirname(__FILE__)) . '/common.inc.php');

set_time_limit(0);
ignore_user_abort(true);
spl_autoload_register('__autoload');

$condition = 'city_website_id = 1 AND column_type = 1 AND is_down = 0 AND updated < ' . (time() - 86400 * 30);
$data = array(
    'is_down' => 1,
    'down_time' => time(),
    'data_update_time' => time()
);
//数据库连接
$query = new DbQueryForMysql(GetConfig('db'));
$result = $query->table('houserent')->where($condition)->save($data);
if ($result === false) {
    echo date('Y-m-d H:i:s', time()) . " 采集房源 失败\r";
} else {
    echo date('Y-m-d H:i:s', time()) . " 采集房源 成功\r";
}

//创建会员数据库连接
$member_query = new DbQueryForMysql(GetConfig('member_db'));
//$member_query->debug = true;
$result = $member_query->table('houserent')->where($condition)->save($data);
if ($result === false) {
    echo date('Y-m-d H:i:s', time()) . " 第一房源 失败\r";
} else {
    echo date('Y-m-d H:i:s', time()) . " 第一房源 成功\r";
}
