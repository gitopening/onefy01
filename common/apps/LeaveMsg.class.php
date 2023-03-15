<?php
/**
 * 出售房源信息管理
 * @package Apps
 */
class LeaveMsg {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	/**
	 * 投诉建议数据表
	 *
	 * @var string
	 */
	var $tName = 'fke_msg';
	/**
	 * 初始化
	 * @var string
	 */
	
	function __construct($db) {
		$this->db = $db;
	}
	
	
	/**
	 * 取数据列表
	 * @param array limit 
	 * @access public
	 * @return array
	 */
	function getList($pageLimit,$fileld='*' , $flag = 0,$where_clouse = '',$order='') {
		$where =' where 1 = 1' ;
		if ($where_clouse){
			$where .= $where_clouse;
		}
		$this->db->open('select '.$fileld.' from '.$this->tName.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	}
	
	
	/**
	 * 读取详细信息
	 * @param string $id ID
	 * @param string $field 主表字段
	 * @access public
	 * @return array
	 */
	function getInfo($id, $field = '*') {
		return $this->db->getValue('select ' . $field . ' from '.$this->tName.'  where id=' . $id);
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
			$where = 'id in (' . $ids . ')';
		} else {
			$where = 'id=' . intval($ids);
		}
		
		$sql="delete from {$this->tName} where $where";
		if ($this->db->execute($sql)){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 更新某个字段
	 * @param mixed $ids ID
	 * @access public
	 * @return bool
	 */
	function update($ids,$field,$value) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' id in (' . $ids . ')';
		} else {
			$where = ' id=' . intval($ids);
		}
		return $this->db->execute('update '.$this->tName.' set '.$field.' = \''.$value.'\' where ' . $where);
	}
	
	/**
	 * 取总记录数
	 * @access public
	 * @return NULL
	 */
	function getCount($where_clouse = '') {
		$where =" where 1 = 1";		
		if ($where_clouse){
			$where .= $where_clouse;
		}
		return $this->db->getValue('select count(*) from '.$this->tName. $where );
	}
	
	/**
	 * 取得所有符合条件的数据
	 *
	 * @param unknown_type $columns
	 * @param unknown_type $condition
	 * @param unknown_type $order
	 * @return unknown
	 */
	function getAll($columns='*',$condition='',$order = ''){
		if($condition != ''){
			$condition = ' where ' .$condition;
		}
		return $this->db->select('select '.strtolower($columns).' from '.$this->tName.$condition.' '.$order);
	}
}
?>