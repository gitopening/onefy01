<?php
/**
 * 站内信息管理
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * 站内信息管理类
 * @package Apps
 */
class ShopViewLog{

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	/**
	 * 主用户表
	 *
	 * @var string
	 */
	var $tName = "fke_shop_viewlog";
	
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
	/* function getList($pageLimit, $fileld='*' ,$where='', $order=' order by id desc ') {
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
	*/
	  function getList($pageLimit, $fileld='*' ,$where='', $order=' order by id desc ',$more_info = false) {
	 	if($where){
			$where=' where '.$where;
		}
		$this->db->open('select * from '.$this->tName.' '.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		$member = new Member($this->db);
		while ($rs = $this->db->next()) {
			if($more_info){
				$member_info = $member->getInfo($rs['friend_id'],'*',true); 
				$rs = array_merge($member_info,$rs);
			}
			$result[] = $rs;
		}
		return $result;
	 }
	 /**
	  * 记录访问LOG
	  *
	  * @param Int $broker_id
	  * @param int $friend_id
	  */
	 function addLog($broker_id,$friend_id)
	 {
	 	$timeBefore = mktime(0,0,0,date('m'),date('d'),date('Y'));
	 	$timeEnd = mktime(0,0,0,date('m'),date('d')+1,date('Y'));
	 	if($logId =$this->db->getValue("select id from ".$this->tName." where broker_id =".$broker_id." and friend_id =".$friend_id." and  add_time>".$timeBefore." and add_time<".$timeEnd)){
		 	return $this->db->execute("update ".$this->tName." set add_time = ".time().", click_num=click_num+1 where id = ".$logId);
	 	}else{
	 		$insertField = array(
		 		'broker_id' => $broker_id,
		 		'friend_id' =>$friend_id,
		 		'click_num'=>1,
		 		'add_time' =>time()
		 	);
		 	return $this->db->insert($this->tName,$insertField);
	 	}
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