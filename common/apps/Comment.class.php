<?php
/**
 * PHP项目组使用 - PHP项目类库
 * Copyright (c) 2008-2010 西岸网讯
 * All rights reserved.
 * 未经许可，禁止用于商业用途！
 *
 * @package    Apps
 * @author     bieye <bieye615@163.com>
 * @copyright  2008-2010 Walk Watch
 * @version    v1.0
 */

/**
 * Problem 问题列表类
 * @package Apps
 */
class Comment {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	function __construct($db) {
		$this->db = $db;
	}
	/**
	 * 取问题信息
	 * @param int $problemId 用户ID
	 * @param string $field 字段
	 * @access public
	 * @return array
	 */
	function getInfo($comment_id, $field = '*') {
		return $this->db->getValue('select ' . $field . ' from jx_questioncomment where comment_id=' . intval($comment_id));
	}
	
	 /**
	 * 取得信息列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getList($problemid,$pageLimit) {
		$this->db->open('select * from jx_questioncomment where question_id='.$problemid.' order by comment_id desc' , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	 }
	/**
	 * 取总用户数
	 * @access public
	 * @return NULL
	 */
	function getCount($problemid) {
		return $this->db->getValue('select count(*) from jx_questioncomment where question_id='.$problemid);
	}
	/**
	 * 取得所有信息列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getListAll($problemid) {
	 	$pageLimit = array(
	 		"rowFrom"=>0,
	 		"rowTo"=>$this->getCount()
	 	);
	 	
		return $this->getList($problemid,$pageLimit);
	 }
	/**
	 * 取得所有有分数的信息列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getListMarked($problemid) {
		$this->db->open('select * from jx_questioncomment where question_id='.$problemid.' and score>0 order by comment_id desc');
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	 }
	
	 /**
	  * 保存
	  */
	 function save($info){
	 	global $problem;
	 	if ($info['commentid']) {
	 		$arr = array (
				'content' => $info['plcontent'],
			);
			$this->db->update('jx_questioncomment', $arr ,'comment_id=' . intval($info['commentid']));
			return $info['commentid'];
	 	} else {
			$arr = array (
				'question_id' => $info['question_id'],
				'content' => $info['plcontent'],
				'creator' => $info['creator']
			);
			$problem->updateProblemInfo($info['question_id'],'comment_num');
			$this->db->insert('jx_questioncomment', $arr);
			return $this->db->getInsertId();
		}
		
	 }
	 /**
	 * 问题评估
	 *
	 * @param array $info post
	 * @return bool
	 */
	function updateCommentScore($score,$commentid){
		$arr = array(
			'score'=>$score,
		);
		return $this->db->update("jx_questioncomment",$arr,'comment_id='.$commentid);
	}
	/**
	 * 问题删除
	 * @param  int $commentid
	 * @return bool
	 */
	function delpl($commentid){
		return $this->db->execute('delete from jx_questioncomment where comment_id = '.$commentid);
	}
	 //start edit by 吴家庚
	 /**
	  * 取得回复者所对应的问题ID
	  * 
	  */
	 function getProblembyCommentID($rename){
	 	return $this->db->select("select question_id from jx_questioncomment where creator like '%".$rename."%'");
	 }
	 
	 /**
	  * 返回回复的积分和回复个数
	  * @param string $creator 回复者
	  * @param string $wherestring 条件
	  */
	 function getCommentCounts($creator,$wherestring){
	 	return $this->db->getValue("select sum(score) as score,count(comment_id) as commentcounts from jx_questioncomment where creator = '$creator' $wherestring");
	 }
	 //end edit ty 吴家庚
}	
?>