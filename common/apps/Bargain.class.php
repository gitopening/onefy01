<?php
/**
 * 成交行情
 *
 */
class Bargain {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	/**
	 * 主表
	 *
	 * @var string
	 */
	var $tName = "fke_bargain";
	
	/**
	 * 构造函数
	 *
	 * @param source $db
	 */
	function __construct($db) {
		$this->db = $db;
	}
	
	/**
	 * 保存信息
	 * @access public
	 * 
	 * @param array $memberInfo
	 * @return bool
	 **/
	 function save($info){
	 	
	 	$insertField = array(
	 		'consign_type'=>$info['consign_type'],
			'house_id'=>$info['id'],
			'owner_id'=>$info['owner_id'],
			'add_time'=>time(),
			'available_time'=>$info['available_time'],
			'consign_status'=>1,
	 	);
	 	$this->db->insert($this->tName,$insertField);
	 	$consign_id =  $this->db->getInsertId();
	 	
	 	if($info['brokerSelected']){
	 		$brokerList = explode(',',$info['brokerSelected']);
	 		array_remove_empty($brokerList);
	 		foreach ($brokerList as $item){
	 			$insertField = array(
			 		'consign_id'=>$consign_id,
					'broker_id'=>$item,
					'status'=>0
			 	);
			 	$this->db->insert($this->tNameBroker,$insertField);
	 		}
	 	}
	 	return true;
	 }
	 	 
	 /**
	 * 取得信息列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getList($pageLimit, $fileld='*' ,$where='', $order=' order by id desc ') {
	 	if($where){
			$where=' where '.$where;
		}
		$this->db->open('select * from '.$this->tName.' '.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	 }
	
	/**
	 * 删除信息
	 * @param mixed $ids ID列表
	 * @access public
	 * @return bool
	 */
	function delete($ids) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' id in (' . $ids . ')';
		} else {
			$where = ' id=' . intval($ids);
		}
		return $this->db->execute('delete from '.$this->tName.' where ' . $where);
	}
	
	/**
	 * 取得详细信息
	 * @access public
	 * @param int $id
	 * @return array 
	 */
	function getInfo($id,$field = '*'){
		return $this->db->getValue('select '.$field.' from '.$this->tName.' where id =' .$id);
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
	
}
?>