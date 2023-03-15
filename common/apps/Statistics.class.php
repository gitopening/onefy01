<?php
/**
 * 数据统计类
 *
 * @copyright Copyright (c) 2005 - 2009 Yanwee.net (www.anleye.com)
 * @author net geow@qq.com
 * @package Core
 * @version $Id$
 */

class Statistics {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	/**
	 * 表名字
	 *
	 * @var string
	 */
	var $tName = 'fke_statistics';
	
	/**
	 * 构造函数
	 *
	 * @param link $db
	 */
	
	function __construct($db) {
		$this->db = $db;
	}
	/**
	 * 取所有统计信息
	 * @access public
	 * @return array
	 */
	function getAll($classname='') {
		if($classname){
			return $this->db->select('select * from '.$this->tName.' where stat_class =\''.$classname.'\'');
		}else{
			return $this->db->select('select * from '.$this->tName);
		}
	}
	/**
	 * 取得数量
	 *
	 * @param string $index
	 * @return num
	 */
	function getNum($index){
		return $this->db->getValue("select stat_value from ".$this->tName." where stat_index='".$index."'");
	}
	/**
	 * 取得单条记录
	 *
	 * @param string $index
	 * @return array
	 */
	function getInfo($index){
		return $this->db->getValue("select * from ".$this->tName." where stat_index='".$index."'");
	}
	/**
	 * 自动增加1
	 *
	 * @param string $index
	 * @return bool
	 */
	function add($index)
	{
		return $this->db->execute("update ".$this->tName." set stat_value = stat_value+1 where stat_index='".$index."'");
	}
}	
?>