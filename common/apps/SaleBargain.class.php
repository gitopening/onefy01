<?php
/**
 * 头像审核管理
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * 头像审核管理类
 * @package Apps
 */
class SaleBargain {

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
	var $tName = "fke_housesell_bargain";
	
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
	 	$info['id'] = intval($info['id']);
	 	if($info['id']){
	 		$updateField = array(
	 			'borough_name'=>$info['borough_name'],
	 			'house_totalarea'=>$info['house_totalarea'],
	 			'house_price'=>$info['house_price'],
				'bargain_from'=>$info['bargain_from'],
				'buyer'=>$info['buyer'],
				'buyer_tel'=>$info['buyer_tel'],
				'saler'=>$info['saler'],
				'saler_tel'=>$info['saler_tel'],
				'bargain_price'=>$info['bargain_price']
		 	);
		 	$this->db->update($this->tName,$updateField,'id='.$info['id']);
		 	return $info['id'];
	 	}else{
		 	$insertField = array(
		 		'house_id' =>$info['house_id'],
		 		'broker_id' =>$info['broker_id'],
		 		'borough_name'=>$info['borough_name'],
	 			'house_totalarea'=>$info['house_totalarea'],
	 			'house_price'=>$info['house_price'],
				'bargain_from'=>$info['bargain_from'],
				'buyer'=>$info['buyer'],
				'buyer_tel'=>$info['buyer_tel'],
				'saler'=>$info['saler'],
				'saler_tel'=>$info['saler_tel'],
				'bargain_price'=>$info['bargain_price'],
				'bargain_time'=>MyDate::transform('timestamp',$info['bargain_time']),
				'add_time'=>time()
		 	);
		 	$this->db->insert($this->tName,$insertField);
		 	return $this->db->getInsertId();
	 	}
	 }
	 /**
	 * 保存信息
	 * @access public
	 * 
	 * @param array $memberInfo
	 * @return bool
	 **/
	 function saveRemark($info){

 		$updateField = array(
 			'remark'=>$info['remark'],
	 	);
	 	return $this->db->update($this->tName,$updateField,'id='.$info['id']);
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