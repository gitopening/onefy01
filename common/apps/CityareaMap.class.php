<?php
/**
 * 地图找房区域设置
 *
 * @author 阿一 ayi@yanwee.com
 * @package 2.0
 */

/**
 * 地图找房区域类
 * @package Apps
 */
class CityareaMap {

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
	var $tName = "fke_map";
	
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
	 function getList($fileld='*' ,$where='', $order=' order by sort asc ') {
	 	if($where){
			$where=' where '.$where;
		}
		//print_rr('select * from '.$this->tName.' '.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$this->db->open('select * from '.$this->tName.' '.$where.' '.$order);
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
					    	'cityarea_name' =>  $field['cityarea_name'],
						    'lat' => $field['lat'],
							'lnt' => $field['lnt'],
							'sort' => $field['sort'],
						),  'id=' . intval($field['id'])
					);
		} else {
			$this->db->insert($this->tName, array(
							'cityarea_name' =>  $field['cityarea_name'],
						    'lat' => $field['lat'],
							'lnt' => $field['lnt'],
							'sort' => $field['sort'],
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