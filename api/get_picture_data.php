<?php
/**
 * Created by suncent.cn.
 * User: Net
 * Date: 15-7-30
 * Time: 上午9:57
 */

$url = urldecode(trim($_GET['url']));
if (empty($url)) {
    exit('');
}
/*
echo file_get_contents($url);
*/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
$output = curl_exec($ch);
curl_close($ch);
echo $output;