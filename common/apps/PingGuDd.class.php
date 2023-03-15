<?php
/**
 * 评估字典类
 */

class PingGuDd {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	var $tName = "fke_pinggu_dd";
	
	var $tNameItem = "fke_pinggu_dd_item";
	/**
	 * 构造函数
	 * @param Object $db 数据查询类
	 * @access public
	 * @return void
	 */
	function __construct($db) {
		$this->db = $db;
	}
	
	/**
	 * 返回字典列表
	 * @access public
	 * @return array
	 */
	public function getList() {
		return $this->db->select('select * from '.$this->tName);
	}
	
	/**
	 * 返回字典项列表
	 * @param int $dd_id 字典ID
	 * @access public
	 * @return array
	 */
	public function getItemList($dd_id) {
		return $this->db->select('select * from '.$this->tNameItem.' where dd_id=' . intval($dd_id).' order by list_order ASC');
	}
	
	/**
	 * 返回字典项列表
	 * @param int $dd_name 字典名字
	 * @access public
	 * @return array
	 */
	public function getItemListByName($dd_name) {
		$dd_id = $this->db->getValue('select dd_id from '.$this->tName.' where dd_name=\'' . $dd_name.'\'');
		return $this->db->select('select * from '.$this->tNameItem.' where dd_id=' . intval($dd_id).' order by list_order ASC');
	}
	
	/**
	 * 检查唯一性
	 * @param int $info 字典项信息
	 * @access public
	 * @return array
	 */
	public function checkUnique($info) {
		return $this->db->getValue('select count(*) from '.$this->tNameItem.' where (di_value=\''
				 . $info['di_value'] . '\' or di_caption=\''.$info['di_caption'] 
				 . '\') and dd_id=' . intval($info['dd_id'])
				  . ' and di_id!=' . intval($info['di_id']));
	}
	/**
	 * 保存字典项信息
	 * @param array $info 字典项信息
	 * @access public
	 * @return void
	 */
	public function save($info) {
		if ($this->checkUnique($info)) {
			throw new Exception('值或名称已经存在！');
		}
		if ($info['di_id']) {
			$this->db->update($this->tNameItem, array (
						'di_value' => $info['di_value'],
						'di_caption' => $info['di_caption'],
						'di_quotiety' => $info['di_quotiety'],
						),'di_id=' . intval($info['di_id'])
					);
		} else {
			$this->db->insert($this->tNameItem, array(
						'dd_id' => $info['dd_id'],
						'di_value' => $info['di_value'],
						'di_caption' => $info['di_caption'],
						'di_quotiety' => $info['di_quotiety'],
						)
					);
		}
		$this->cache($info['dd_id']);
	} 
	
	/**
	 * 删除字典项
	 * @param mixed $users 字典项ID
	 * @access public
	 * @return bool
	 */
	public function delete($dds) {
		if (is_array($dds)) {
			$dds = implode(',',$dds);
			$where = 'di_id in (' . $dds . ')';
		} else {
			$where = 'di_id=' . intval($dds);
		}
		$dd_id = $this->db->getValue('select dd_id from '.$this->tNameItem.' where ' . $where);
		$this->db->execute('delete from '.$this->tNameItem.' where ' . $where);
		$this->cache($dd_id);
	}
	
	/**
	 * 缓存字典信息
	 * @param int $dd_id 字典ID
	 * @access public
	 * @return void
	 */
	public function cache($dd_id) {
		$dd_id = intval($dd_id);
		$dd_name = $this->db->getValue('select dd_name from '.$this->tName.' where dd_id=' . $dd_id);
		$array = $this->db->select('select di_value,di_caption,di_quotiety from '.$this->tNameItem.' where dd_id=' . $dd_id.' order by list_order asc', 'hashmap');
		
		$fp = fopen($GLOBALS['cfg']['path']['conf'] . 'pinggu/' . $dd_name . '.php', 'w');
		fputs($fp, '<?php return '.var_export($array, true) . '; ?>');
		fclose($fp);
	}
	
	/**
	 * 取字典项信息
	 * @param int $di_id 字典项ID
	 * @access public
	 * @return array
	 */
	public function getDiInfo($di_id) {
		return $this->db->getValue('select * from '.$this->tNameItem.' where di_id='  . intval($di_id));
	}
	
	public function insertDd($dd_name,$dd_id) {
	    
	    if (!$this->db->getValue('select * from '.$this->tNameItem.' where di_caption=\''.$dd_name.'\' and dd_id='.$dd_id.'')) {
		    $newId = $this->db->getValue('select max(di_value) temp_id from '.$this->tNameItem.' where dd_id='.$dd_id);
		    $this->db->execute('insert into '.$this->tNameItem.' 
			    (dd_id,di_value,di_caption) values ('.$dd_id.','.intval($newId+1).',\''.$dd_name.'\')');
				
		    $this->cache(intval($newId+1));
		    return intval($newId+1);	
		} else {
		    $info = $this->db->getValue('select di_value from '.$this->tNameItem.' where di_caption=\''.$dd_name.'\' and dd_id='.$dd_id.'');
		    return $info;
		}
		
		    
	}
	
	/**
	 * 取字典项数组
	 * @param string $name 字典名
	 * @access public
	 * @return array
	 */
	public static function getArray($name) {
		return require($GLOBALS['cfg']['path']['conf'] . 'pinggu/' . $name . '.php');
	}
	
	/**
	 * 取字典项名称
	 * @param string $name 字典名
	 * @param string $value 值
	 * @access public
	 * @return array
	 */
	public static function getCaption($name, $value) {
		if($value ==''){
			return '';
		}
		$array = PingGuDd::getArray($name);
		if (strpos($value,',')===false) {
			return $array[$value];
		} else {
			$values = explode(',',$value);
			$result = '';
			foreach ($values as $v) {
				if ($v) {
					$result .= $array[$v] . ' ';
				}
			}
		}
		return $result;
	}
	/**
	 * 排序
	 *
	 * @param array $order_arr
	 * @return bool
	 */
	function order($order_arr,$dd_id)
	{
		foreach ($order_arr as $key=> $a_order){
			$this->db->execute('update '.$this->tNameItem.' set list_order = '.$a_order.' where di_id = '.$key);
		}
		$this->cache($dd_id);
		return true;
	}
	/**
	 * 分组操作
	 *
	 * @param array $order_arr
	 * @return bool
	 */
	function group($order_arr,$dd_id)
	{
		foreach ($order_arr as $key=> $a_order){
			$this->db->execute('update '.$this->tNameItem.' set list_group = '.$a_order.' where di_id = '.$key);
		}
		$this->cache($dd_id);
		return true;
	}
	
	/**
	 * 取字典项数组 分组
	 * @param string $name 字典名
	 * @access public
	 * @return array
	 */
	function getArrayGrouped($dd_name) {
		$dd_id = $this->db->getValue('select dd_id from '.$this->tName.' where dd_name=\'' . $dd_name.'\'');
		$dd_array = $this->db->select('select * from '.$this->tNameItem.' where dd_id=\'' . $dd_id.'\' order by list_order ASC');
		
		if(is_array($dd_array)){
			foreach ($dd_array as $a_dditem){
				$grouped_array[$a_dditem['list_group']][$a_dditem['di_value']] = $a_dditem['di_caption'];			
			}
			ksort($grouped_array);
		}
		return $grouped_array;
		
	}
	/**
	 * 根据值取
	 *
	 * @param string $dd_name
	 * @param int $di_value
	 * @return array
	 */
	function getItemByValue($dd_name,$di_value)
	{
		$dd_id = $this->db->getValue('select dd_id from '.$this->tName.' where dd_name=\'' . $dd_name.'\'');
		return  $this->db->getValue('select * from '.$this->tNameItem.' where dd_id=\'' . $dd_id.'\' and di_value = \''.$di_value.'\'');
	}
	/**
	 * 最后一个，默认
	 *
	 * @param string $dd_name
	 * @return array
	 */
	function getLast($dd_name)
	{
		$dd_id = $this->db->getValue('select dd_id from '.$this->tName.' where dd_name=\'' . $dd_name.'\'');
		return  $this->db->getValue('select * from '.$this->tNameItem.' where dd_id=\'' . $dd_id.'\' order by di_value desc');
	}
}
?>