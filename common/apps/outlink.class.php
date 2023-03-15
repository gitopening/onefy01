<?php

/**
 * 站内信息管理类
 * @package Apps
 */
class outlink{

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
	var $tName = "fke_outlink";
	
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
	 * 取得首页信息列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getLists($fileld='*' ,$where='', $order=' order by id desc ') {
	 	if($where){
			$where=' where '.$where;
		}
		$this->db->open('select * from '.$this->tName.' '.$where.' '.$order);
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
	 * 
	 * @param mixed $ids ID列表
	 * @access public
	 * @return bool
	 */
	function status($ids,$status) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' id in (' . $ids . ')';
		} else {
			$where = ' id=' . intval($ids);
		}
		return $this->db->execute('update '.$this->tName.' set status ='.$status.' where ' . $where);
	}
	/**
	 *
	 * @param mixed $ids ID列表
	 * @access public
	 * @return bool
	 */
	function shouye($ids,$status) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' id in (' . $ids . ')';
		} else {
			$where = ' id=' . intval($ids);
		}
		return $this->db->execute('update '.$this->tName.' set shouye ='.$status.' where ' . $where);
	}
	/**
	 * 排序
	 *
	 */
	function order($list_order)
	{
		foreach ($list_order as $key=> $item){
			$this->db->execute('update '.$this->tName.' set list_order = '.$item.' where id = '.$key);
		}
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
	 function getAll($where='',$fileld='*', $order=' order by id desc ') {
	 	if($where != ''){
			$where = ' where ' .$where;
		}
	 	return $this->db->select('select '.$fileld.' from '.$this->tName.' '.$where.' '.$order);
	 }
	 /**
	  * 保存分类信息
	  *
	  * @param array $fieldData
	  */
	function save($info)
	{
		if (empty($info['province_id'])){
			$info['city_id'] = 0;
			$info['city_website_id'] = 0;
		}
		if (empty($info['city_id'])){
			$info['city_website_id'] = 0;
		}
		$id = intval($info['id']);
		if ($id) {// 更新
			$this->db->update($this->tName,array(
						'link_name' => $info['link_name'],
						'link_type' => $info['link_type'],
						'link_class' => $info['link_class'],
						'link_url' => $info['link_url'],
						'link_logo' => $info['link_logo'],
						'link_text' => $info['link_text'],
						'website_id' => $info['website_id'],	
						'province_id' => intval($info['province_id']),
			 			'city_id' => intval($info['city_id']),
						'admin_name' => $info['admin_name'],
			 			'city_website_id' => intval($info['city_website_id'])
						), 'id=' . $id);
		} else {// 添加 
			$this->db->insert($this->tName,array(
						'link_name' => $info['link_name'],
						'link_type' => $info['link_type'],
						'link_class' => $info['link_class'],
						'link_url' => $info['link_url'],
						'link_logo' => $info['link_logo'],
						'link_text' => $info['link_text'],
						'website_id' => $info['website_id'],
						'status' => '1',
						'shouye' => '1',
						'add_time' => time(),
						'province_id' => intval($info['province_id']),
						'city_id' => intval($info['city_id']),
						'admin_name' => $info['admin_name'],
						'city_website_id' => intval($info['city_website_id'])
						));
			$id = $this->db->getInsertId();
		}
		return $id;
	}
	
	function add_click($id)
	{
		return $this->db->execute('update '.$this->tName.' set click_num = click_num+1 where id ='.$id);
		
	}
}
?>