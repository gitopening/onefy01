<?php
/**
 * 出售房源信息管理
 * @package Apps
 */
class Shop {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	/**
	 * 出售房源基本信息表
	 *
	 * @var string
	 */
	var $tNameConf = 'fke_shop_conf';
	
	function Shop($db) {
		$this->db = $db;
	}
	/**
	 * 网店配置
	 *
	 * @param int $broker_id
	 * @return array
	 */
	function getShopConf($broker_id)
	{
		return $this->db->getValue('select * from '.$this->tNameConf.' where broker_id='.$broker_id);
	}
	
	/**
	 * 网店配置
	 *
	 * @param int $broker_id
	 * @return array
	 */
	function saveConf($fileddata)
	{
		if($this->getShopConf($fileddata['broker_id'])){
			$updateField = array(
				'shop_name'=>$fileddata['shop_name'],
				'shop_notice'=>$fileddata['shop_notice'],
			);
			$this->db->update($this->tNameConf,$updateField,' broker_id='.$fileddata['broker_id']);
			return $fileddata['broker_id'];
		}else{
			$insertField = array(
				'broker_id'=>$fileddata['broker_id'],
				'shop_name'=>$fileddata['shop_name'],
				'shop_notice'=>$fileddata['shop_notice'],
				'shop_style'=>'shopStyleDefault.css',
				'created'=>time()
			);
			$this->db->insert($this->tNameConf,$insertField);
			return $this->db->getInsertId();
		}
		
	}
	/**
	 * 网店配置
	 *
	 * @param int $broker_id
	 * @return array
	 */
	function saveShopStyle($fileddata)
	{
		if($this->getShopConf($fileddata['broker_id'])){
			$updateField = array(
				'shop_style'=>$fileddata['shop_style'],
			);
			$this->db->update($this->tNameConf,$updateField,' broker_id='.$fileddata['broker_id']);
			return $fileddata['broker_id'];
		}else{
			$insertField = array(
				'broker_id'=>$fileddata['broker_id'],
				'shop_style'=>$fileddata['shop_style'],
				'created'=>time()
			);
			$this->db->insert($this->tNameConf,$insertField);
			return $this->db->getInsertId();
		}
		
	}
	
	/**
	 * 点击数
	 * @param int $id
	 * @return bool
	 * 
	 */
	function addClick($id)
	{
		return $this->db->execute('update '.$this->tNameConf.' set click_num=click_num+1 where broker_id = '.$id );
	}	
}
?>