<?php
/**
 * 房价走势图处理
 *
 * @author net geow@qq.com
 * @package 1.4
 * @version $Id$
 */

/**
 * 房价走势类
 * @package Apps
 */
class Trend {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	/**
	 * 出售房源走势图表
	 *
	 * @var string
	 */
	var $tName = "fke_housesell_trend";
	
	/**
	 * 出租房源走势图表
	 *
	 * @var string
	 */
	var $tNameRent = "fke_houserent_trend";
	
	/**
	 * 构造函数
	 *
	 * @param source $db
	 */
	function __construct($db) {
		$this->db = $db;
	}
	

     /**
	 * 取得信息列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getList($pageLimit, $fileld='*' ,$where='', $order=' order by time desc ') {
	 	if($where){
			$where=' where '.$where;
		}
		//print_rr('select * from '.$this->tName.' '.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$this->db->open('select * from '.$this->tName.' '.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	 }
	 
	 /**
	 * 取类别总数
	 * @access public
	 * @return int
	 */
	function getCount($where = '') {
		if($where){
			$where=' where '.$where;
		}
		return $this->db->getValue('select count(*) from '.$this->tName.' '.$where );
	}
	
	/**
	 * 取信息
	 * @param string $id 
	 * @param string $field
	 * @access public
	 * @return array
	 */
	function getInfo($id, $field = '*') {
		return  $this->db->getValue('select ' . $field . ' from '.$this->tName.' where id=' . $id);
	}
	
	/**
	 * 保存信息
	 * @param string $field
	 * @access public
	 * @return array
	 */
	function save($field) {
		if ($field['id']) {
			$this->db->update($this->tName, array (
					    	'time' =>  $field['timestr'],
						    'price' => $field['price'],
							'area' => $field['area'],
							'number' => $field['number'],
						),  'id=' . intval($field['id'])
					);
		} else {
			$this->db->insert($this->tName, array(
							'time' =>  $field['timestr'],
						    'price' => $field['price'],
							'area' => $field['area'],
							'number' => $field['number'],
						)
					);
		}
	}
	
	/**
	 * 删除信息
	 * @param mixed $ids 选择的ID
	 * @access public
	 * @return bool
	 */
	function delete($ids) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' where id in (' . $ids . ')';
		} else {
			$where = ' where id=' . intval($ids);
		}
		 return $this->db->execute('delete from '.$this->tName.$where);
		}

	
}
?>