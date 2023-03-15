<?php
/**
 * User 后台用户类
 * @package Apps
 */
class Company {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;

	/**
	 * 中介公司表
	 *
	 * @var string
	 */
	var $tName = 'fke_company';
	
	
	function Company($db) {
		$this->db = $db;
	}
	
	/**
	 * 存储中介公司
	 * @param array $fileddata
	 * @access public
	 * @return bool
	 */
	 function save($fileddata){
	 if($fileddata['id']){
		 //编辑
		  $this->db->update($this->tName,$fileddata['company'],'id = '.$fileddata['id']);
		 } else{
			 //增加
			   $this->db->insert($this->tName,$fileddata['company']);
			 }
	   return true;
	 }
	 /**
	 * 取得中介公司信息列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	function getList($pageLimit,$fileld='*' ,$where_clouse = '',$order='') {
		$where =' where 1 = 1' ;
		if($where_clouse){
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
	 * 取中介公司总数
	 * @access public
	 * @return NULL
	 */
	function getCount($where_clouse = '') {
		$where =" where 1 = 1";
		
		if($where_clouse){
			$where.= $where_clouse;
		}
		
		return $this->db->getValue('select count(*) from '.$this->tName. $where );
	}
	
	/**
	 * 取中介公司详细信息
	 * @param string $id 中介公司ID
	 * @param string $field 主表字段
	 * @access public
	 * @return array
	 */
	function getInfo($id, $field = '*') {
		return $this->db->getValue('select ' . $field . ' from '.$this->tName.'  where id=' . $id);
	}
	/**
	 * 删除
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
		$this->db->execute('delete from '.$this->tName. $where);
		return true;
		
	}
	
	/**
	 * 操作中介公司状态
	 * @param mixed $ids 中介公司ID
	 * @access public
	 * @return bool
	 */
	function changeStatus($ids,$status) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' id in (' . $ids . ')';
		} else {
			$where = ' id=' . intval($ids);
		}
		return $this->db->execute('update '.$this->tName.' set status = '.$status.' where ' . $where);
	}
	
}
?>