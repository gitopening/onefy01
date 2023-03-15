<?php
/**
 * Created by net.
 * User: net
 * Date: 2017/1/9
 * Time: 1:40
 */
use OSS\OssClient;
use OSS\Core\OssException;

$access_key = 'dc60IvyJ51iqnucMK';
$k = $_GET['k'];
if ($access_key != $k) {
    exit('error');
}

define('__ROOT__', dirname(dirname(__FILE__)));
set_time_limit(0);

require_once(__ROOT__ . '/common.inc.php');
require_once(__ROOT__ . '/common/lib/OSS/autoload.php');
$step = 2000;

//初始化OSS存储对象
$accessKeyId = 'LTAIsxMQJfAuA4R8';
$accessKeySecret = 'PVtbn5mAIKdc60IvyJ51iqnucMKIDe';
//$endpoint = 'https://oss-cn-hangzhou.aliyuncs.com';
$endpoint = 'https://oss-cn-hangzhou-internal.aliyuncs.com';
$bucket= 'img-01fy';
try {
    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
    $ossClient->setTimeout(3600 /* seconds */);
    $ossClient->setConnectTimeout(10 /* seconds */);
} catch (OssException $e) {
    print_r($e->getMessage());
}

//处理信息图片
/*$log_file = __ROOT__ . '/upfile/oss_log_identity.txt';
if (file_exists($log_file)) {
    $position = intval(file_get_contents($log_file));
} else {
    $position = 0;
}

//数据库中读取使用中的数据
$start_id = intval($position);
$max_house_id = 15000;

$table_name = 'broker_identity';
if ($start_id < $max_house_id) {
    $end_id = $start_id + $step;
    $condition = "id >= $start_id and id <= $end_id";
    $data_list = $member_query->table($table_name)->where($condition)->order('id asc')->limit($step)->all();
    if (empty($data_list)) {
        //记录处理位置
        file_put_contents($log_file, $end_id);
    } else {
        foreach ($data_list as $item) {
            //读取图片内容
            $pic_path = __ROOT__ . $item['idcard_pic'];
            if (file_exists($pic_path) && !empty($item['idcard_pic'])) {
                $object = str_replace('/upfile/broker/', 'user/idcard/', $item['idcard_pic']);
                $result = $ossClient->uploadFile($bucket, $object, $pic_path);
            }

            $pic_path = __ROOT__ . $item['avatar_pic'];
            if (file_exists($pic_path) && !empty($item['avatar_pic'])) {
                $object = str_replace('/upfile/broker/', 'user/face/', $item['avatar_pic']);
                $result_face = $ossClient->uploadFile($bucket, $object, $pic_path);
            }

            //记录处理位置
            file_put_contents($log_file, $item['id']);
        }
    }

    echo 'broker:' . $end_id . '<script type="text/javascript">setTimeout(function(){window.location.reload();}, 3000);</script>';
    exit();
}*/

$log_file = __ROOT__ . '/upfile/oss_log_identity.txt';
if (file_exists($log_file)) {
    $position = intval(file_get_contents($log_file));
} else {
    $position = 0;
}

//数据库中读取使用中的数据
$start_id = intval($position);

$max_house_id = 40000;

$table_name = 'broker_info';
if ($start_id < $max_house_id) {
    $end_id = $start_id + $step;
    $condition = "id > $start_id and id <= $end_id AND avatar <> ''";
    $data_list = $member_query->table($table_name)->where($condition)->order('id asc')->limit($step)->all();
    if (empty($data_list)) {
        //记录处理位置
        file_put_contents($log_file, $end_id);
    } else {
        foreach ($data_list as $item) {
            //读取图片内容
            $pic_path = __ROOT__ . str_replace('user/idcard', '/upfile/broker', $item['idcard_pic']);
            if (file_exists($pic_path) && !empty($item['idcard_pic'])) {
                $object = $item['idcard_pic'];
                $result = $ossClient->uploadFile($bucket, $object, $pic_path);
            }

            $pic_path = __ROOT__ . str_replace('user/face', '/upfile/broker', $item['avatar']);
            if (file_exists($pic_path) && !empty($item['avatar'])) {
                $object = $item['avatar'];
                $result_face = $ossClient->uploadFile($bucket, $object, $pic_path);
            }

            //记录处理位置
            file_put_contents($log_file, $item['id']);
        }
    }

    echo 'broker:' . $end_id . '<script type="text/javascript">setTimeout(function(){window.location.reload();}, 3000);</script>';
    exit();
}
exit('success');