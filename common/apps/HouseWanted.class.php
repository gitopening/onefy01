<?php
/**
 * 求租和求购管理
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * 求租和求购管理类
 * @package Apps
 */
class HouseWanted {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	/**
	 * 求租求购
	 *
	 * @var string
	 */
	var $tName = "fke_house_wanted";
	
	/**
	 * 织梦新闻
	 *
	 * @var string
	 */
	var $dede = "dede_archives";
	
	/**
	 * 求租求购回复表
	 *
	 * @var string
	 */
	var $tNameReply = "fke_house_wantedreply";
	
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
	 function save($dataField){
	 	specConvert($dataField,array('linkman','link_tell','requirement'));
	 	$dataField['id'] = intval($dataField['id']);
	 	if($dataField['id']){
	 		$updateField = array(
		 		'linkman'=>$dataField['linkman'],
		 		'link_tell'=>$dataField['link_tell'],
		 		'open_tell'=>intval($dataField['open_tell']),
		 		'requirement'=>$dataField['requirement'],
		 	);
		 	$this->db->update($this->tName,$updateField,' id='.$dataField['id']);
		 	return $dataField['id'];
	 	}else{
		 	$insertField = array(
		 		'wanted_type'=>$dataField['wanted_type'],
		 		'house_no'=>$dataField['house_no'],
		 		'linkman'=>$dataField['linkman'],
		 		'link_tell'=>$dataField['link_tell'],
		 		'open_tell'=>$dataField['open_tell'],
		 		'requirement'=>$dataField['requirement'],
		 		'add_time'=>time(),
		 	);
		 	$this->db->insert($this->tName,$insertField);
		 	return $this->db->getInsertId();
	 	}
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
	 * 取得织梦的新闻列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getnews($pageLimit, $fileld='*' ,$where='', $order=' order by id desc ') {
	 	if($where){
			$where=' where '.$where;
		}
		$this->db->open('select * from '.$this->dede.' '.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
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
	 * 删除信息回复
	 * @param mixed $ids ID列表
	 * @access public
	 * @return bool
	 */
	function deleterep($ids) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' wanted_id in (' . $ids . ')';
		} else {
			$where = ' wanted_id = ' . intval($ids);
		}
		return $this->db->execute('delete from '.$this->tNameReply.' where ' . $where);
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
	 * 取得信息列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getReplyList($wanted_id) {
		return $this->db->select('select * from '.$this->tNameReply.' where wanted_id = '.$wanted_id.' order by add_time desc');
	 }
	 /**
	  * 回复
	  *
	  * @param array $dataField
	  */
	 function saveReply($dataField)
	 {
	 	specConvert($dataField,array('linkman','content'));
	 	$insertField = array(
	 		'wanted_id'=>$dataField['wanted_id'],
	 		'linkman'=>$dataField['linkman'],
	 		'content'=>$dataField['content'],
	 		'add_time'=>time(),
	 	);
	 	$this->db->insert($this->tNameReply,$insertField);
	 }
	 /**
	  * 保存委托人
	  * @param id
	  * @param atring
	  * 
	  */
	 function saveExpert($id,$ids)
	 {
	 	return $this->db->execute('update '.$this->tName.' set expert_id = \''.$ids.'\' where  id=' . $id);
	 }
}
?>