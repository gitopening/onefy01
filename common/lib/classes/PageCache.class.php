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
 * PageCache 页面缓存类
 * @package Util
 */
class PageCache {

	/**
	 * @var string $file 缓存文件地址
	 * @access public
	 */
	public $file;
	
	/**
	 * @var int $cacheTime 缓存时间
	 * @access public
	 */
	public $cacheTime = 3600;
	
	/**
	 * 构造函数
	 * @param string $file 缓存文件地址
	 * @param int $cacheTime 缓存时间
     */
	function __construct($file, $cacheTime = 3600) {
		$this->file = $file;
		$this->cacheTime = $cacheTime;
	}
	
	/**
	 * 取缓存内容
	 * @param bool 是否直接输出，true直接转到缓存页,false返回缓存内容
	 * @return mixed
     */
	public function get($output = true) {
		if (is_file($this->file) && (time()-filemtime($this->file))<=$this->cacheTime && !$_GET['nocache']) {
			if ($output) {
				header('location:' . $this->file);
				exit;
			} else {
				return file_get_contents($this->file);
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 设置缓存内容
	 * @param $content 内容html字符串
     */
	public function set($content) {
		$fp = fopen($this->file, 'w');
		fwrite($fp, $content);
		fclose($fp);
	}
}
?>