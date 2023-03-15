<?php
/**
 * PHP项目组使用 - PHP项目类库
 * Copyright (c) 2006-2008 西岸网讯
 * All rights reserved.
 * 未经许可，禁止用于商业用途！
 *
 * @package    Apps
 * @author     李辉 <nicho-li@163.com>
 * @copyright  2006-2008 Walk Watch
 * @version    v1.0
 */

/**
 * Flashdata 存取FLASH数据类
 * @package Apps
 */

class Flashdata {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	function __construct($db) {
		$this->db = $db;
	}
	
	
	/**
	 * 存FLASH数据
	 * @access publlic
	 * @param 表单数组 $dataInfo
	 * @return bool
	 */
	function savedata($dataInfo) {
		$fp = fopen('data.php', 'w');
		fputs($fp, 'draw_string='.$dataInfo['draw_string'].'&time_string='.$dataInfo['time_string']);
		fclose($fp);
	}
	/*function savedata($dataInfo) {
		if (!$this->db->getValue('select * from edu_flashdata')) {
			return $this->db->execute('insert into edu_flashdata (draw_string,time_string) 
		        values (\''.$dataInfo['draw_string'].'\',\''.$dataInfo['time_string'].'\')');
		} else {
			return $this->db->execute('update edu_flashdata set draw_string=\''.$dataInfo['draw_string'].'\',
			    time_string=\''.$dataInfo['time_string'].'\'');
		}
	}*/
	
	/**
	 * 读FLASH数据
	 * @access publlic
	 * @param string $field
	 * @return bool
	 */
	/*function readdata($field='*') {
		return $this->db->getValue('select '.$field.' from edu_flashdata');
	}*/
	function readdata() {
		$data = file_get_contents('data.php');
		return $data;
	}
}
?>