<?php
/**
 * PHP项目组使用 - PHP项目类库
 * Copyright (c) 2006-2008 西岸网讯
 * All rights reserved.
 * 未经许可，禁止用于商业用途！
 *
 * @package    Util
 * @author     戴志君 <dzjzmj@163.com>
 * @copyright  2006-2008 Walk Watch
 * @version    v1.0
 */

/**
 * Ip Ip类
 * @package Util
 */
class Ip {

	/**
	 * 取IP
	 * @return string
	 */
	public static function get() {
		if ($_SERVER['HTTP_CLIENT_IP'] && $_SERVER['HTTP_CLIENT_IP']!='unknown') {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ($_SERVER['HTTP_X_FORWARDED_FOR'] && $_SERVER['HTTP_X_FORWARDED_FOR']!='unknown') {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	/**
	 * IP转成整形数值
	 * @param string $ip IP
	 * @return int
	 */
	public static function ipToInt($ip) {
		$ips = explode('.',$ip);
		if (count($ips)>=4) {
			$int = $ips[0]*256*256*256+$ips[1]*256*256+$ips[2]*256+$ips[3];
		} else {
			throw new Exception('ip is error');
		}
		return $int;
	}
	
	/**
	 * 判断IP是否在一个IP段内
	 * @param string $startIp 开始IP
	 * @param string $endIp 结束IP
	 * @param string $ip IP
	 * @return bool
	 */
	public static function isIn($startIp, $endIp, $ip) {
		$start = Ip::ipToInt($startIp);
		$end = Ip::ipToInt($endIp);
		$ipInt = Ip::ipToInt($ip);
		$result = false;
		if ($ipInt>=$start && $ipInt<=$end) {
			$result = true;
		}
		return $result;
	}
}
?>