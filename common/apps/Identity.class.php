<?php
/**
 * 身份认证审核管理
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * 身份认证审核管理类
 * @package Apps
 */

class Identity {

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
	var $tName = "fke_broker_identity";

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
	 		'realname'=>$info['realname'],
	 		'idcard'=>$info['idcard'],
	 		'idcard_pic'=>$info['idcard_pic'],
			'business_license'=>$info['business_license'],
			'business_license_pic'=>$info['business_license_pic'],
	 		'broker_id'=>$info['broker_id'],
			'avatar_pic'=>$info['avatar_pic'],
			'status'=>1,
			'checkmsg'=>'',
			'city_website_id' => $info['city_website_id'],
	 		'add_time'=>time()
			
		);
		$this->db->insert($this->tName,$insertField);
		return $this->db->getInsertId();
	}

	/**
	 * 取得信息列表
	 * @access public
	 *
	 * @param array $pageLimit
	 * @return array
	 **/
	function getList($pageLimit, $field='*' ,$where='', $order=' order by id desc ') {
		if($where){
			$where=' where '.$where;
		}
		$this->db->open('select ' . $field . ' from '.$this->tName.' '.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
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
		return $this->db->execute('update '.$this->tName.' set status = '.$status.' where ' . $where);
	}
	
	function GetStatus($status){
		switch ($status){
			case -1:
				return '审核失败';
			case 0:
				return '未审核';
			case 1:
				return '审核中';
			case 2:
				return '通过审核';
		}
	}
	/**
	 * 取得详细信息
	 * @access public
	 * @param int $id
	 * @return array
	 */
	function getInfo($id,$field = '*'){
		return $this->db->getValue('select '.$field.' from '.$this->tName.' where id =' .$id . ' order by id desc');
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
	/*
	 * 通过经纪人ID取得最新一条信息
	* @access public
	* param int $brokerId
	* param string $filed
	* return array
	*/
	function GetInfoByBrokerId($brokerId,$field='*'){
		return $this->db->getValue('select '.$field.' from '.$this->tName.' where broker_id =' .$brokerId.' order by id desc');
	}
}