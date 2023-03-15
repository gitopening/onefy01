<?php
/**
 * 字典类
 *
 * @copyright Copyright (c) 2005 - 2009 Yanwee.net (www.anleye.com)
 * @author net geow@qq.com
 * @package Core
 * @version $Id$
 */

class Dd {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
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
		return $this->db->select('select * from fke_dd');
	}
	
	/**
	 * 返回字典项列表
	 * @param int $dd_id 字典ID
	 * @access public
	 * @return array
	 */
	public function getItemList($dd_id) {
		return $this->db->select('select * from fke_dd_item where dd_id=' . intval($dd_id).' and p_id=0 order by list_order ASC');
	}

	public function getItemArray($dd_id, $cache = false, $cache_expire_time = 60)
	{
		$cache_key = $cache == false ? $cache : 'dict_cache_' . $dd_id;
		$data_list = $this->db->table('dd_item')->where('dd_id=' . intval($dd_id) . ' AND p_id = 0')->order('list_order ASC, di_id ASC')->cache($cache_key, $cache_expire_time)->all();
		$dd = array();
		foreach ($data_list as $item) {
			$dd[$item['di_value']] = $item['di_caption'];
		}
		return $dd;
	}

	/**
	 * 返回字典子项列表
	 * @param int $dd_id 字典ID
	 * @access public
	 * @return array
	 */
	public function getSonList($di_value) { 
		$p_id=$this->db->getValue('select di_id from fke_dd_item where di_value=' . intval($di_value).' and p_id=0 and dd_id=1 order by list_order ASC');
		return $this->db->select('select * from fke_dd_item where p_id=' . intval($p_id).' and dd_id=1 order by list_order ASC');
	}	
	
	public function getArea() {
		$query_sql='select * from fke_dd_item where dd_id=1 and p_id=0 order by list_order ASC';
		$query = mysql_query($query_sql);
		$i=0;
	while ($row = mysql_fetch_array($query)) {
    	$class[]=$row;
    	$query2_sql="select * from fke_dd_item where dd_id=1 and p_id='".$row['di_id']."' order by list_order ASC";
    	$query2 = mysql_query($query2_sql);
		$m=0;
    	while ($row2 = mysql_fetch_array($query2)) {
        	$class[$i]['son'][$m]=$row2;
			$m++;
    	}
	$i++;
	}
	return $class;
	}
	
	/**
	 * 检查唯一性
	 * @param int $info 字典项信息
	 * @access public
	 * @return array
	 */
	public function checkUnique($info) {
		return $this->db->getValue('select count(*) from fke_dd_item where (di_value=\''
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
			$this->db->update('fke_dd_item', array (
						'di_value' => $info['di_value'],
						'di_caption' => $info['di_caption'],
						),'di_id=' . intval($info['di_id'])
					);
		} else {
			$this->db->insert('fke_dd_item', array(
						'dd_id' => $info['dd_id'],
						'di_value' => $info['di_value'],
						'di_caption' => $info['di_caption'],
						)
					);
		}
		$this->cache($info['dd_id']);
		$this->cache2($info['dd_id']);		
	} 
	
	
	
	public function save2($info) {
		if ($this->checkUnique($info)) {
			throw new Exception('值或名称已经存在！');
		}
		if ($info['di_id']) {
			$this->db->update('fke_dd_item', array (
						'di_value' => $info['di_value'],
						'di_caption' => $info['di_caption'],
						'p_id' => $info['p_id'],						
						),'di_id=' . intval($info['di_id'])
					);
		} else {
			$this->db->insert('fke_dd_item', array(
						'dd_id' => $info['dd_id'],
						'di_value' => $info['di_value'],
						'di_caption' => $info['di_caption'],
						'p_id' => $info['p_id'],
						)
					);
		}
		$this->cache($info['dd_id']);
		$this->cache2($info['dd_id']);
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
		$dd_id = $this->db->getValue('select dd_id from fke_dd_item where ' . $where);
		$this->db->execute('delete from fke_dd_item where ' . $where);
		$this->cache($dd_id);
		$this->cache2($dd_id);
	}
	
	/**
	 * 缓存字典信息
	 * @param int $dd_id 字典ID
	 * @access public
	 * @return void
	 */
	public function cache($dd_id) {
		$dd_id = intval($dd_id);
		$dd_name = $this->db->getValue('select dd_name from fke_dd where dd_id=' . $dd_id);
		$array = $this->db->select('select di_value,di_caption from fke_dd_item where dd_id=' . $dd_id.' and p_id=0 order by list_order asc', 'hashmap');
		
		$fp = fopen($GLOBALS['cfg']['path']['conf'] . 'dd/' . $dd_name . '.php', 'w');
		fputs($fp, '<?php return '.var_export($array, true) . '; ?>');
		fclose($fp);
	}
	
	public function cache2($dd_id) {
		$dd_id = intval($dd_id);
		$array = $this->db->select('select di_value,di_caption from fke_dd_item where dd_id=' .$dd_id.' and p_id!=0 order by list_order asc', 'hashmap');
		
		$fp = fopen($GLOBALS['cfg']['path']['conf'] . 'dd/cityarea2.php', 'w');
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
		return $this->db->getValue('select * from fke_dd_item where di_id='  . intval($di_id));
	}
	
	
	/**
	 * 生成字典select框
	 * @param string $name 字典名
	 * @param int $value 字典值
	 * @param string $cur 自定义属性
	 * @param string $firstWord 定义第一个选项显示。no：不显示
	 * @access public
	 * @return bool
	 */
	public static function getSelect($name, $value=0, $cur=NULL,$firstWord=NULL,$right=array()) {
		$array = Dd::getArray($name);		
		if ($firstWord==NULL) {
			$fWord = '<option value="0">选择</option>';
		} elseif ($firstWord=='no')  {
			$fWord = '';
		} else {
			$fWord = '<option value="0">'.$firstWord.'</option>';
		}
		$html = '<select name="'.$name.'" id="'.$name.'" ' . $cur . '>'.$fWord ;
		foreach ($array as $k => $v) {
			if ($k==$value) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			if ($right) {
				if (in_array($k,$right)) {
					$html .= '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
				}
			} else {
				$html .= '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}
	
	public function insertDd($dd_name,$dd_id) {
	    
	    if (!$this->db->getValue('select * from fke_dd_item where di_caption=\''.$dd_name.'\' and dd_id='.$dd_id.'')) {
		    $newId = $this->db->getValue('select max(di_value) temp_id from fke_dd_item where dd_id='.$dd_id);
		    $this->db->execute('insert into fke_dd_item 
			    (dd_id,di_value,di_caption) values ('.$dd_id.','.intval($newId+1).',\''.$dd_name.'\')');
				
		    $this->cache(intval($newId+1));
			  $this->cache2(intval($newId+1));
		    return intval($newId+1);	
		} else {
		    $info = $this->db->getValue('select di_value from fke_dd_item where di_caption=\''.$dd_name.'\' and dd_id='.$dd_id.'');
		    return $info;
		}
		
		    
	}
	
	/**
	 * 生成字典radio框
	 * @param string $name 字典名
	 * @param int $value 字典值
	 * @param string $cur 自定义属性
	 * @access public
	 * @return bool
	 */
	public static function getRadio($name, $value=0, $cur=NULL) {
		$array = Dd::getArray($name);
		$cruValue = intval($value)?intval($value):"";
        $html = '';
		foreach ($array as $k => $v) {
			if ( $k == $cruValue ) {
				$checked = ' checked="checked"';
			} else {
				$checked = '';
			}
			$html.='<input'.$checked.' type="radio" name="' . $name . '" value="'.$k.'" '.$cur.' />' . $v;
		}
		
		return $html;
	}
	
	/**
	 * 生成字典checked/radio框
	 * @param string $name 字典名
	 * @param int $value 字典值
	 * @param string $cur 自定义属性
	 * @access public
	 * @return bool
	 */
	public static function getChecked($name, $value=0, $cur=NULL) {
		$array = Dd::getArray($name);
		if ($value) {
			$cruValue = explode(',', $value);
		} else {
			$cruValue = array();
		}
        $html = '';
		foreach ($array as $k => $v) {
			if (in_array($k, $cruValue)) {
				$checked = ' checked="checked"';
			} else {
				$checked = '';
			}
			$html.='<input'.$checked.' type="checkbox" name="' . $name . '[]" value="'.$k.'" />' . $v;
		}
		
		return $html;
	}
	
	/**
	 * 取字典项数组
	 * @param string $name 字典名
	 * @access public
	 * @return array
	 */
	public static function getArray($name) {
		return require($GLOBALS['cfg']['path']['conf'] . 'dd/' . $name . '.php');
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
		$array = Dd::getArray($name);
		if (strpos($value,',')===false) {
			return $array[$value];
		} else {
			$values = explode(',',$value);
			$result = '';
			foreach ($values as $v) {
				if ($v) {
					$result .= $array[$v] . ' &nbsp; ';
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
			$this->db->execute('update fke_dd_item set list_order = '.$a_order.' where di_id = '.$key);
		}
		$this->cache($dd_id);
		$this->cache2($dd_id);
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
			$this->db->execute('update fke_dd_item set list_group = '.$a_order.' where di_id = '.$key);
		}
		$this->cache($dd_id);
		$this->cache2($dd_id);
		return true;
	}
	
	/**
	 * 取字典项数组 分组
	 * @param string $name 字典名
	 * @access public
	 * @return array
	 */
	function getArrayGrouped($dd_name) {
		$dd_id = $this->db->getValue('select dd_id from fke_dd where dd_name=\'' . $dd_name.'\'');
		$dd_array = $this->db->select('select * from fke_dd_item where dd_id=\'' . $dd_id.'\' order by list_order ASC');
		
		if(is_array($dd_array)){
			foreach ($dd_array as $a_dditem){
				$grouped_array[$a_dditem['list_group']][$a_dditem['di_value']] = $a_dditem['di_caption'];			
			}
			ksort($grouped_array);
		}
		return $grouped_array;
		
	}
	
	
}