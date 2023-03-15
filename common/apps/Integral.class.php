<?php
/**
 * 积分分类管理
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * 头像审核管理类
 * @package Apps
 */
class Integral {

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
	var $tName = "fke_integral_rule";
	
	/**
	 * 主log表
	 *
	 * @var string
	 */
	var $tNameLog = "fke_integral_log";
	
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
	 	$info['id']=intval($info['id']);
	 	if(!$info['id']){
	 		$insertField = array(
		 		'rule_name'=>$info['rule_name'],
				'rule_class'=>$info['rule_class'],
				'rule_score'=>$info['rule_score'],
				'rule_remark'=>$info['rule_remark'],
				'rule_status' =>0,
		 	);
		 	$this->db->insert($this->tName,$insertField);
		 	return $this->db->getInsertId();
	 	}else{
	 		$updateField = array(
				'rule_name'=>$info['rule_name'],
				'rule_class'=>$info['rule_class'],
				'rule_score'=>$info['rule_score'],
				'rule_remark'=>$info['rule_remark'],
			);
	 		$this->db->update($this->tName,$updateField,'id=' . $info['id']);
	 		return $info['id'];
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
	 * 取得所有的列表
	 * @access public
	 * 
	 * @param string where
	 * @return array
	 */
	 function getAll($where='',$fileld='*',$order=' order by id desc '){
	 	if($where != ''){
			$where = ' where ' .$where;
		}
		//echo 'select '.$fileld.' from '.$this->tName.' '.$where.' '.$order;
	 	return $this->db->select('select '.$fileld.' from '.$this->tName.' '.$where.' '.$order);
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
	 * 操作用户状态 不物理删除
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
		return $this->db->execute('update '.$this->tName.' set rule_status = '.$status.' where ' . $where);
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
	function add($member_id,$rule_id){
		$member= new Member($this->db);
		$ruleInfo = $this->getInfo($rule_id);
		$member->addScore($member_id,$ruleInfo['rule_score']);
		//log
		$insertField = array(
			'member_id'=>$member_id,
			'rule_id'=>$rule_id,
			'rule_class'=>$ruleInfo['rule_class'],
			'rule_score'=>$ruleInfo['rule_score'],
			'add_time'=>time()
		);
		$this->db->insert($this->tNameLog,$insertField);
	}
	/**
	 * 察看用户是不是已经得到这个加分
	 *
	 * @param int $member_id
	 * @param int $rule_id
	 * @return array
	 */
	function getLogByRuleId($member_id,$rule_id)
	{
		return $this->db->getValue("select * from ".$this->tNameLog." where member_id=".$member_id." and rule_id=".$rule_id);
	}
	/**
	 * 取得用户的所有积分
	 * @param int $user_id 
	 */
	function getLogGroup($user_id,$where)
	{
		if($where){
			$where = " and ".$where;
		}
		$sql = "select sum(rule_score) as sum_score,rule_class from ".$this->tNameLog." where member_id=".$user_id.$where." group by rule_class ";
		$this->db->open($sql);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[$rs['rule_class']] = $rs['sum_score'];
		}
		return $result;
	}
}
?>