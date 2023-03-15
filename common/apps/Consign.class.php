<?php
/**
 * 资质审核管理
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * 头像审核管理类
 * @package Apps
 */
class Consign {

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
	var $tName = "fke_house_consign";
	
	/**
	 * 委托的经纪人表
	 *
	 * @var string
	 */
	var $tNameBroker = "fke_house_consign_broker";
	
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
	 * 操作状态 不物理删除
	 * @param mixed $members 用户ID
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
	/**
	 * 取得所有委托给我的房源
	 * 
	 */
	function getMyConsign($member_id)
	{
		$sql = "select DISTINCT consign_id from ".$this->tNameBroker." where broker_id='".$member_id."'";
		return $this->db->select($sql);
	}
	/**
	 * 取类别总数
	 * @access public
	 * @return int
	 */
	function getCountBroker($where = '') {
		if($where){
			$where=' where '.$where;
		}
		$sql ="select count(*) from ".$this->tName ." as c left join ".$this->tNameBroker." as b on c.id = b.consign_id ".$where ;
		
		return $this->db->getValue($sql);
	}
	/**
	 * 取得信息列表 经纪人察看委托给自己页面
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getListBroker($pageLimit, $fileld='*' ,$where='', $order='') {
	 	if($where){
			$where=' where '.$where;
		}
		
		$sql = "select c.id as cid ,c.*,b.id as bid,b.* from ".$this->tName ." as c 
			left join ".$this->tNameBroker." as b on c.id = b.consign_id ".$where.$order;
		$this->db->open($sql , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	 }
	 /**
	  * 删除委托给自己的记录
	  *
	  * @param int $bid
	  */
	 function deleteBroker($bid)
	 {
	 	return $this->db->execute('update '.$this->tNameBroker.' set isdel=1 where id= ' . $bid);
	 }
	 
	 /**
	  * 删除委托给自己的记录(彻底)
	  *
	  * @param int $bid
	  */
	 function deleteBrokerc($bid)
	 {
	 	return $this->db->execute('delete from '.$this->tNameBroker.' where id=' . $bid);
	 }
	 
	 /**
	  * 经纪人受理房源
	  *
	  * @param int $member_id
	  * @param int $consign_id
	  */
	 function accept($member_id,$consign_id)
	 {
	 	//1：把该房源的委托记录设置为已受理
		$this->db->execute("update ".$this->tName." set consign_status = 2 where id=".$consign_id);
	 	//2：把该经纪人关于这个房源的委托经纪人表记录设置为已受理
	 	$this->db->execute("update ".$this->tNameBroker." set status =1 where consign_id =".$consign_id." and broker_id=".$member_id);
	 }
	 /**
	  * 经纪人不受理房源
	  *
	  * @param int $member_id
	  * @param int $consign_id
	  */
	 function decline($member_id,$consign_id,$remark)
	 {
	 	//1：不受理不需要改受理状态
	 	//2：把该经纪人关于这个房源的委托经纪人表记录设置为不受理, 并记录不受理理由
	 	$this->db->execute("update ".$this->tNameBroker." set status =2,remark='".$remark."' where consign_id =".$consign_id." and broker_id=".$member_id);
	 }
	 /**
	 * 取得详细信息
	 * @access public
	 * @param int $id
	 * @return array 
	 */
	function getInfoByHouseId($house_id,$owner_id,$consign_type){
	return $this->db->getValue('select * from '.$this->tName.' where house_id =' .$house_id.' and owner_id = '.$owner_id. ' and consign_type='.$consign_type);
	}
	
	
	function getInfoByHouseIdc($house_id,$consign_type){
	return $this->db->getValue('select * from '.$this->tName.' where house_id =' .$house_id.' and consign_type='.$consign_type);
	}
	/**
	 * 经纪人察看委托情况页面
	 * @param int $consign_id
	 * @return  array
	 */
	function getConsignBroker($consign_id){
		return $this->db->select("select * from ".$this->tNameBroker." where consign_id =".$consign_id);
	}
}
?>