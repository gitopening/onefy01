<?php
/**
 * 网站基本配置管理类
 * @package Apps
 */

class Config {

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
	var $tName = "fke_web_config";
	
	/**
	 * 构造函数
	 *
	 * @param source $db
	 */
	function __construct($db) {
		$this->db = $db;
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
	 * 保存网站信息
	 * @access public
	 * @param int $id
	 * @return array 
	 */
	function save($fileddata,$where){
		$this->db->update($this->tName,$fileddata['webConfig'],$where);
		$this->cache();
	}
	/**
	 * 新增加一条站点信息
	 * @access public
	 * @param int $id
	 * @return array
	 */
	function insert($fileddata){
		$this->db->insert($this->tName,$fileddata['webConfig']);
// 		$this->cache();
	}
	
	/**
	 * 缓存网站基本信息
	 * @access public
	 * @return void
	 */
	public function cache() {
		$array = $this->db->getValue('select * from fke_web_config where id = 1', 'hashmap');
		$fp = fopen($GLOBALS['cfg']['path']['conf'] . 'dd/config.php', 'w');
		fputs($fp, '<?php return '.var_export($array, true) . '; ?>');
		fclose($fp);
	}
	
	/**
	 * 取站点列表
	 * @param array limit
	 * @param Enum $flag 0：全部 ， 1：已审核 ，2：未审核
	 * @access public
	 * @return array
	 */
	function getList($pageLimit,$fileld='*',$where_clouse = '',$order='') {
		$where =' where 1 = 1' ;
		if($where_clouse){
			$where .= $where_clouse;
		}
		$this->db->open('select '.$fileld.' from '.$this->tName.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	}
	/**
	 * 取总站点数
	 * @access public
	 * @return NULL
	 */
	function getCount($flag = 0,$where_clouse = '') {
		$where =" where 1 = 1";
		return $this->db->getValue('select count(*) from '.$this->tName. $where.$where_clouse );
	}
	
	/**
	 * 删除租房源信息
	 * @param mixed $ids 选择的ID
	 * @access public
	 * @return bool
	 */
	function delete($ids) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = 'id in (' . $ids . ')';
		} else {
			$where = 'id=' . intval($ids);
		}
		$this->db->execute('delete from '.$this->tName.' where '.$where);
		return true;
	}
}
?>