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
class BrokerFriend{

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
	var $tName = "fke_broker_friends";
	
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
	 /*function getList($pageLimit, $fileld='*' ,$where='', $order=' order by id desc ') {
	 	if($where){
			$where=' where '.$where;
		}
		$this->db->open('select * from '.$this->tName.' '.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	 }*/
	
	 /**
	 * 取得信息列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
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
				//$rs['friend_info']=$member->getInfo($rs['friend_id'],'*',1); 
			}
			$result[] = $rs;
		}
		return $result;
	 }
	
	 
	/**
	 * 删除好友
	 * @param mixed $ids ID列表
	 * @access public
	 * @return bool
	 */
	function delete($ids) {
		$friendInfo = $this->getInfo($ids);
		$this->db->execute("delete from ".$this->tName." where status =1 and broker_id = '".$friendInfo['friend_id']."' and friend_id = '".$friendInfo['broker_id']."'");
		return $this->db->execute('delete from '.$this->tName.' where id =' . $ids);
	}
	/**
	 * 标志
	 * @param mixed $ids ID列表
	 * @access public
	 * @return bool
	 */
	function changeStatus($ids,$status=1) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' id in (' . $ids . ')';
		} else {
			$where = ' id=' . intval($ids);
		}
		return $this->db->execute('update '.$this->tName.' set status ='.$status.' where ' . $where);
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
	 * 发送邀请
	 * 
	 */
	function save($fieldset)
	{
		$insertArray = array(
	 		'broker_id'=>$fieldset['broker_id'],
			'friend_id'=>$fieldset['friend_id'],
			'add_time'=>time(),
			'status'=>0,
	 	);
	 	$this->db->insert($this->tName,$insertArray);
		return $this->db->getInsertId();
	}
	/**
	 * 是否已邀请
	 * 
	 */
	function isAdded($fieldset)
	{
		return $this->db->getValue("select id from ".$this->tName." where broker_id = '".$fieldset['broker_id']."' and friend_id = '".$fieldset['friend_id']."' and status <> 2");
	}
	/**
	 * 添加好友审核通过的时候把好友也添加为自己的好友
	 *
	 * @param int $ids
	 */
	function confirm($ids)
	{
		$inviteLog = $this->db->getValue("select * from ".$this->tName." where id =".$ids);
		$insertArray = array(
	 		'broker_id'=>$inviteLog['friend_id'],
			'friend_id'=>$inviteLog['broker_id'],
			'add_time'=>time(),
			'status'=>1,
	 	);
	 	$this->db->insert($this->tName,$insertArray);
		return $this->db->getInsertId();
	}
	
}
?>