<?php
/**
 * 广告管理
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * 头像审核管理类
 * @package Apps
 */
class Ads {

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
	var $tName = "fke_ads";
	
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
		 		'ads_name'=>$info['ads_name'],
				'introduce'=>$info['introduce'],
				'place_id'=>$info['place_id'],
				'ad_type'=>$info['ad_type'],
				'link_url'=>$info['link_url'],
				'image_url'=>$info['image_url'],
				'image_thumb'=>$info['image_thumb'],
				'alt'=>$info['alt'],
				'flash_url'=>$info['flash_url'],
				'text'=>$info['text'],
				'code'=>$info['code'],
				'from_date'=>MyDate::transform('timestamp',$info['from_date']),
				'to_date'=>MyDate::transform('timestamp',$info['to_date']),
				'creater'=>$info['creater'],
				'add_time'=>$info['add_time'],
				'status'=>0,
	 			'isorder'=>intval($info['isorder']),
	 			'province_id' => intval($info['province_id']),
	 			'city_id' => intval($info['city_id']),
	 			'city_website_id' => intval($info['city_website_id'])
		 	);
		 	$this->db->insert($this->tName,$insertField);
		 	return $this->db->getInsertId();
	 	}else{
	 		$updateField  = array(
		 		'ads_name'=>$info['ads_name'],
				'introduce'=>$info['introduce'],
				'place_id'=>$info['place_id'],
				'ad_type'=>$info['ad_type'],
				'link_url'=>$info['link_url'],
				'image_url'=>$info['image_url'],
				'image_thumb'=>$info['image_thumb'],
				'alt'=>$info['alt'],
				'flash_url'=>$info['flash_url'],
				'text'=>$info['text'],
				'code'=>$info['code'],
				'from_date'=>MyDate::transform('timestamp',$info['from_date']),
				'to_date'=>MyDate::transform('timestamp',$info['to_date']),
	 			'isorder'=>intval($info['isorder']),
 				'province_id' => intval($info['province_id']),
 				'city_id' => intval($info['city_id']),
	 			'city_website_id' => intval($info['city_website_id'])
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
		//print_rr('select * from '.$this->tName.' '.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
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