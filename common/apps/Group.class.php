<?php
/**
 * 群组类
 *
 * @copyright Copyright (c) 2005 - 2009 Yanwee.net (www.anleye.com)
 * @author net geow@qq.com
 * @package Core
 * @version $Id$
 */

/**
 * Group 后台用户组类
 * @package Apps
 */
class Group {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	private $db = NULL;
	
	/**
	 * @var string $filepath 缓存文件路径
	 * @access private
	 */
	private $filepath;
	
	/**
	 * 构造函数
	 * @param Object $db 数据查询类
	 * @access public
	 * @return void
	 */
	function Group($db) {
		$this->db = $db;
		$this->filepath = $GLOBALS['cfg']['path']['conf'] . 'groups.cfg.php';
	}
	
	/**
	 * 取用户组列表
	 * @access public
	 * @return array
	 */
	function getList() {
		return $this->db->select('select * from fke_group');
	}
	
	/**
	 * 保存用户组信息
	 * @param string $info 用户组信息数组
	 * @access public
	 * @return bool
	 */
	function save($info) {
		specConvert($info, array('group_name'));
		$group_id = intval($info['group_id']);
		if ($group_id) {// 更新
			$this->db->update('fke_group',array(
						'group_name' => $info['group_name'],
						), 'group_id=' . $group_id);
		} else {// 添加 
			$this->db->insert('fke_group',array(
						'group_name' => $info['group_name'],
						));
		}
		$this->cache();
		return $result;
	}
	
	/**
	 * 取用户组信息
	 * @param string $userId 用户ID
	 * @param string $field 字段
	 * @access public
	 * @return array
	 */
	function getInfo($groupId, $field = '*') {
		return $this->db->getValue('select ' . $field . ' from fke_group where group_id=' . intval($groupId));
	}
	
	/**
	 * 删除用户组信息
	 * @param mixed $groups 用户组ID
	 * @access public
	 * @return bool
	 */
	function delete($groups) {
		$groups = implode(',',$groups);
		$where = 'group_id in (' . $groups . ')';
		$this->db->execute('delete from fke_group where ' . $where);
		$this->cache();
		return true;
	}
	
	/**
	 * 缓存用户组信息
	 * @access public
	 * @return void
	 */
	function cache() {
		$array = $this->db->select('select group_id,group_name from fke_group','hashmap');
		$fp = fopen ($this->filepath, 'w');
		fputs($fp, '<?php return ' . var_export($array, true) . ';?>');
		fclose($fp);
	}
	
	/**
	 * 取用户级选择框
	 * @param int $value 用户组ID
	 * @access public
	 * @return void
	 */
	function getSelect($value) {
		if (empty($value)){
			$value = 5;
		}
// 		$array = require($this->filepath);
		$array = array();
		$arrGroup = $this->getList();
		foreach ($arrGroup as $key=>$val){
			$array[$val['group_id']] = $val['group_name'];
		}
		$html = '<select name="group_id">';
		foreach ($array as $key => $v) {
			if ($key==$value) {
				$selected = ' selected';
			} else {
				$selected = '';
			}
			$html .= '<option value="' . $key . '"' . $selected . '>' . $v . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
}
?>