<?php
/**
 * Created by suncent.cn.
 * User: Net
 * Date: 15-2-10
 * Time: 下午11:03
 */
require_once(dirname(dirname(__FILE__)).'/common.inc.php');
$action = trim($_GET['action']);
$key = trim($_GET['key']);

//判断提交请求来源是否合法
if ($key != 'LZvS3CBjYUHVdojR9rTStkNHCEcrH9rR') {
    exit('Bad Request');
}

switch ($action) {
    case 'updatehousecache';
        $house_type = trim($_GET['house_type']);
        $house_id = explode(',', $_GET['house_id']);
        update_house_memcache($house_type, $house_id);
        echo 'success';
        break;
    case 'updateall':
        //清除所有缓存
        $memcache->flush();
        echo 'success';
        break;
}