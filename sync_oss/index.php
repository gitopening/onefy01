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
$log_file = __ROOT__ . '/upfile/oss_log_rent.txt';
if (file_exists($log_file)) {
    $position = intval(file_get_contents($log_file));
} else {
    $position = 0;
}

//数据库中读取使用中的数据
$start_id = intval($position);
$max_house_id = 3039019;

$table_name = 'houserent_pic';
if ($start_id < $max_house_id) {
    $end_id = $start_id + $step;
    $condition = "id >= $start_id and id <= $end_id and is_uploaded = 0";
    $data_list = $member_query->table($table_name)->where($condition)->order('id asc')->limit($step)->all();
    if (empty($data_list)) {
        //记录处理位置
        file_put_contents($log_file, $end_id);
    } else {
        foreach ($data_list as $item) {
            //读取图片内容
            $pic_path = __ROOT__ . '/upfile/' . $item['pic_url'];
            if (file_exists($pic_path) && !empty($item['pic_url'])) {
                $object = $item['pic_url'];
                $result = $ossClient->uploadFile($bucket, $object, $pic_path);
            } else {
                $result = false;
            }

            $pic_path = __ROOT__ . '/upfile/' . $item['pic_thumb'];
            if (file_exists($pic_path) && !empty($item['pic_thumb'])) {
                $object = $item['pic_thumb'];
                $result_thumb = $ossClient->uploadFile($bucket, $object, $pic_path);
            } else {
                $result = false;
            }

            //更新数据库标志
            if (!empty($result['oss-request-url']) && !empty($result_thumb['oss-request-url'])) {
                $data = array(
                    'is_uploaded' => 1
                );
                $member_query->table($table_name)->where('id = ' . $item['id'])->save($data);
            }

            //记录处理位置
            file_put_contents($log_file, $item['id']);
        }
    }

    echo 'rent:' . $end_id . '<script type="text/javascript">setTimeout(function(){window.location.reload();}, 3000);</script>';
    exit();
}

//处理二手房图片
$log_file = __ROOT__ . '/upfile/oss_log_sell.txt';
if (file_exists($log_file)) {
    $position = intval(file_get_contents($log_file));
} else {
    $position = 0;
}

//数据库中读取使用中的数据
$start_id = intval($position);
$max_house_id = 3905429;

$table_name = 'housesell_pic';
if ($start_id <= $max_house_id) {
    $end_id = $start_id + $step;
    $condition = "id >= $start_id and id <= $end_id and is_uploaded = 0";
    $data_list = $member_query->table($table_name)->where($condition)->order('id asc')->limit($step)->all();
    if (empty($data_list)) {
        //记录处理位置
        file_put_contents($log_file, $end_id);
    } else {
        foreach ($data_list as $item) {
            //读取图片内容
            $pic_path = __ROOT__ . '/upfile/' . $item['pic_url'];
            if (file_exists($pic_path) && !empty($item['pic_url'])) {
                $object = $item['pic_url'];
                $result = $ossClient->uploadFile($bucket, $object, $pic_path);
            } else {
                $result = false;
            }

            $pic_path = __ROOT__ . '/upfile/' . $item['pic_thumb'];
            if (file_exists($pic_path) && !empty($item['pic_thumb'])) {
                $object = $item['pic_thumb'];
                $result_thumb = $ossClient->uploadFile($bucket, $object, $pic_path);
            } else {
                $result = false;
            }

            //更新数据库标志
            if (!empty($result['oss-request-url']) && !empty($result_thumb['oss-request-url'])) {
                $data = array(
                    'is_uploaded' => 1
                );
                $member_query->table($table_name)->where('id = ' . $item['id'])->save($data);
            }

            //记录处理位置
            file_put_contents($log_file, $item['id']);
        }
    }

    echo 'sale:' . $end_id . '<script type="text/javascript">setTimeout(function(){window.location.reload();}, 3000);</script>';
    exit();
}

exit('success');