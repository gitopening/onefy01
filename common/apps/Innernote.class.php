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
class Innernote{

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
	var $tName = "fke_innernote";
	
	/**
	 * 构造函数
	 *
	 * @param source $db
	 */
	function __construct($db) {
		$this->db = $db;
	}
	
	/**
	 * 发送信息
	 * @access public
	 * 
	 * @param string $msg_from
	 * @param string $msg_to
	 * @param string $title
	 * @param string $content
	 * @return bool
	 **/
	 function send($msg_from,$msg_to,$title,$content,$reply_to=0,$belongs_to=0){
	 	$insertArray = array(
	 		'msg_from'=>$msg_from,
			'msg_to'=>$msg_to,
			'msg_title'=>$title,
			'msg_content'=>$content,
			'replay_to'=>$reply_to,
			'belongs_to'=>$belongs_to,
			'add_time'=>time()
	 	);
	 	$this->db->insert($this->tName,$insertArray);
	 	return $this->db->getInsertId();
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
	 * 删除附属信息
	 * @param mixed $ids ID列表
	 * @access public
	 * @return bool
	 */
	function deleteBelongsTo($ids) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' belongs_to in (' . $ids . ')';
		} else {
			$where = ' belongs_to =' . intval($ids);
		}
		return $this->db->execute('delete from '.$this->tName.' where ' . $where);
	}
	/**
	 * 操作状态 不物理删除
	 * @param mixed $members 用户ID
	 * @access public
	 * @return bool
	 */
	function fromDel($ids,$status=1) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' ( id in ('.$ids.') or belongs_to in ('.$ids.') )';
		} else {
			$where = ' ( id= ' . intval($ids).' or belongs_to = ' . intval($ids) . ')';
		}
		return $this->db->execute('update '.$this->tName.' set from_del = '.$status.' where ' . $where);
	}
	/**
	 * 操作状态 不物理删除
	 * @param mixed $members 用户ID
	 * @access public
	 * @return bool
	 */
	function toDel($ids,$status=1) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' id in (' . $ids . ')';
		} else {
			$where = ' id=' . intval($ids);
		}
		return $this->db->execute('update '.$this->tName.' set to_del = '.$status.' where ' . $where);
	}
	

	/**
	 * 操作状态 不物理删除
	 * @param mixed $members 用户ID
	 * @access public
	 * @return bool
	 */
	function allDel($ids,$member_name) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' id in (' . $ids . ')';
		} else {
			$where = ' id=' . intval($ids);
		}
		$this->db->execute("update ".$this->tName." set to_del = 2 where " . $where." and msg_to ='".$member_name."'");
		$this->db->execute("update ".$this->tName." set from_del = 2 where " . $where." and msg_from='".$member_name."'");
		return true;
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
	 * 取得详细信息
	 * @access public
	 * @param int $id
	 * @return array 
	 */
	function doRead($id){
		return $this->db->execute("update ".$this->tName." set is_new = 0 where id=" . $id );
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