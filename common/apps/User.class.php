<?php
/**
 * User 后台用户类
 * @package Apps
 */
class User {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	function User($db) {
		$this->db = $db;
	}
	
	/**
	 * 用户登录
	 * @param string $username 用户名
	 * @param string $passwd 密码
	 * @access public
	 * @return mixed
	 */
	function login($username, $passwd) {
		global $cfg;
		if ($username && $passwd) {
			$info = $this->db->getValue('select * from fke_users where username=\''.$username.'\'');
			if (!$info) {
				$result = '用户不存在！';
			} elseif ($info['passwd']!=md5($passwd)) {
				$result = '密码错误！';
			}elseif ($info['superuser']!='1'){
				$result = '您没有主站管理权限！';
			} else {
				setcookie(COOKIE_PREFIX . 'AUTH_STRING',authcode($info['user_id'] . "\t" . md5($passwd), 'ENCODE', $cfg['auth_key']));
				setcookie(COOKIE_PREFIX . 'ADMIN_USERID_KSY',$info['user_id'],time()+$GLOBALS['auth_time']);
				$result = true;
			}
		} else {
			$result = '用户密码必须填写！';
		}
		return $result;
	}
	
	
	/**
	 * 用户登出
	 * @access public
	 * @return mixed
	 */
	function logout() {
		global $cfg;
		setcookie(COOKIE_PREFIX . 'AUTH_STRING', 0, time()-1);
		header('location:' . $cfg['url'] . 'admin/login.php');
	}
	
	/**
	 * 取当前用户信息
	 * @access public
	 * @return array
	 */
	function getAuthInfo($field=NULL) {
		global $cfg;
		$authInfo = authcode($_COOKIE[COOKIE_PREFIX . 'AUTH_STRING'], 'DECODE', $cfg['auth_key']);
		$authInfo = explode("\t",$authInfo);
		$result['user_id'] = $authInfo[0];
		$result['passwd'] = $authInfo[1];
		if ($field) {
			if ($result[$field]) {
				return $result[$field];
			} else {
				$info = $this->db->getValue('select * from fke_users where user_id=' . intval($result['user_id']));
				return $info[$field];
			}
		}
		return $result;
	}
	
	/**
	 * 检查认证信息
	 * @access public
	 * @return NULL
	 */
	function auth() {
		$authSuc = false;//echo 1;
		if ($_COOKIE[COOKIE_PREFIX . 'AUTH_STRING']) {//echo 2;
			if (!$_COOKIE[COOKIE_PREFIX . 'AUTH_CHECKTIME']) {//echo 3;
				// 间隔AUTH_CHECKTIME时间检查一次cookie信息是否和数据库一至
				$authInfo = $this->getAuthInfo();
				$user = $this->db->getValue('select * from fke_users where user_id=\''.$authInfo['user_id'].'\' and  passwd=\''.$authInfo['passwd'].'\'');
				
				if ($user['user_id'] && $user['superuser']=='1') {//echo 4;
					$authSuc = true;
					setcookie(COOKIE_PREFIX . 'AUTH_CHECKTIME',1, time()+$GLOBALS['auth_time']);
				}
			} else {
				$authSuc = true;
			}
		}
		if ($authSuc===false) {//echo 5;//exit;
			global $cfg;
			$this->logout();
		}
	}
	
	/**
	 * 取用户列表
	 * @access public
	 * @return array
	 */
	function getList($pageLimit) {
		return $this->db->select('select * from fke_users',
					'array',$pageLimit['rowFrom'],$pageLimit['rowTo']);
	}
	
	function getListSelect ($field) {
		$str = "<select name='".$field."'><option value='0'>请选择用户</option>";
		$getArr = $this->db->select('select * from fke_users where user_id<>'.SYSTEM_ADMIN_ID);
		if ($getArr) {
			foreach ($getArr as $val) {
				$str .= "<option value='".$val['user_id']."'>".$val['username']."</option>";
			}
		}
		$str .="</select>";
		return $str;	
	}
	
	/**
	 * 保存用户信息
	 * @param array $userInfo 表单数组
	 * @access public
	 * @return bool
	 */
	function save($userInfo) {
		$userInfo['websiteids'] = implode(',', $userInfo['websiteid']);
		specConvert($userInfo, array('username','email'));
		$userInfo['user_id'] = intval($userInfo['user_id']);
		if ($userInfo['user_id']) {// 更新
			if ($userInfo['passwd']) {
				$updatePasswd = ',passwd=\'' . md5($userInfo['passwd']) . '\'';
			}
			$result = $this->db->execute('UPDATE fke_users SET username=\''
				 . $userInfo['username'] . '\'' . $updatePasswd . ',email=\'' . $userInfo['email'] . '\',group_id =\''
				   . $userInfo['group_id'] . '\', websiteids=\''.$userInfo['websiteids'].'\', superuser=\''.$userInfo['superuser'].'\'  WHERE user_id =' . $userInfo['user_id']);
			
			$getId = $userInfo['user_id'];
		} else {// 添加 
			$result = $this->db->execute('INSERT INTO fke_users ( username , passwd , email , group_id, websiteids,superuser )
					VALUES (
					 \'' . $userInfo['username'] . '\', \'' . md5($userInfo['passwd']) . '\', \'' . $userInfo['email'] . '\', \'' . $userInfo['group_id'] . '\',\''.$userInfo['websiteids'].'\',\''.$userInfo['superuser'].'\')');
			$getId = $this->db->getInsertId();
			
		}
		$getArr = $this->getGroupRight($userInfo['group_id']);
		$this->saveRight($getArr,$getId);
		return $result;
	}
	
	
	/**
	 * 修改用户密码
	 * @param string $pwd 未经过md5加密的新密码
	 * @access public
	 **/
	function modifyPwd($pwd,$userId=NULL)
	{
		if (!$userId) {//默认当前用户
			$authInfo = $this->getAuthInfo();
			$userId = $authInfo['user_id'];
		}else{
			$userId = intval($userId);
		}
		if($userId && $pwd){
			return $this->db->execute('update fke_users set passwd=\'' . md5($pwd) . '\' where user_id = '.$userId);
		}else{
			return -1;//参数 不足
		}
	}
	
	/**
	 * 取用户信息
	 * @param string $userId 用户ID
	 * @param string $field 字段
	 * @access public
	 * @return array
	 */
	function getInfo($userId, $field = '*') {
		$userId = intval($userId);
		return $this->db->getValue('select ' . $field . ' from fke_users where user_id=' . $userId);
	}
	
	
	
	/**
	 * 删除用户信息
	 * @param mixed $users 用户ID
	 * @access public
	 * @return bool
	 */
	function delete($users) {
		$flag = false;
		if (is_array($users)) {
			if (in_array(SYSTEM_ADMIN_ID, $users)) {
				$flag = true;
			}
			$users = implode(',',$users);
			$where = 'user_id in (' . $users . ')';
		} else {
			if (SYSTEM_ADMIN_ID==$users) {
				$flag = true;
			}
			$where = 'user_id=' . intval($users);
		}
		if ($flag) {
			throw new Exception('系统管理员不能够被删除！');
		}
		return $this->db->execute('delete from fke_users where ' . $where);
	}
	function isSA($userId=0) {
		if (!$userId) {//默认当前用户
			$authInfo = $this->getAuthInfo();
			$userId = $authInfo['user_id'];
		}
		return $userId==SYSTEM_ADMIN_ID;
	}
	/**
	 * 检查用户权限
	 * @param string $right 权限名
	 * @param string $userId 用户ID
	 * @access public
	 * @return NULL
	 */
	function allow($right, $userId=0) {
		if (!$userId) {//默认当前用户
			$authInfo = $this->getAuthInfo();
			$userId = $authInfo['user_id'];
		}
		
		if ($userId==SYSTEM_ADMIN_ID) {
			return ;
		}
		$userRight = $this->getRight($userId);
		
		if (!$userRight[$right]) {
			//echo '你没有该操作的权限！';
			exit;
		}
	}
	
	function hasRight($right, $userId=0)
	{
		if (!$userId) {//默认当前用户
			$authInfo = $this->getAuthInfo();
			$userId = $authInfo['user_id'];
		}
		if ($userId==SYSTEM_ADMIN_ID) {
			return true;
		}
		$userRight = $this->getRight($userId);
		
		return $userRight[$right];
	}
	/**
	 * 保存用户权限
	 * @param string $rights 权限列表
	 * @param string $userId 用户ID
	 * @access public
	 * @return NULL
	 */
	function saveRight($rights,$userId) {
		global $cfg;
		$fp = fopen($cfg['path']['conf'] . 'right/r' . $userId . '.cfg.php', 'w');
		fputs($fp, '<?php return ' . var_export($rights, true) . '; ?>');
		fclose($fp);
	}
	
	function saveGroupRight($rights,$groupId) {
		$arrRights = array();
		foreach ($rights as $key=>$val){
			$arrRights[]=$key;
		}
		$rightString = implode(',', $arrRights);
		$sql = "update `fke_group` set allow='$rightString' where group_id='$groupId'";
		$this->db->execute($sql);
		
		/*global $cfg;
		$fp = fopen($cfg['path']['conf'] . 'group_right/g' . $groupId . '.cfg.php', 'w');
		fputs($fp, '<?php return ' . var_export($rights, true) . '; ?>');
		fclose($fp);
		print_rr($rights);
		exit;
		*/
	}
	/**
	 * 取当前用户权限列表
	 * @param string $userId 用户ID
	 * @access public
	 * @return NULL
	 */
	function getRight($userId) {
		$userInfo = $this->getInfo($userId,'user_id,group_id');
		$userRights = $this->getGroupRightFromDatabase(intval($userInfo['group_id']));
		return $userRights;
		/*global $cfg;
		if (is_file($cfg['path']['conf'] . 'right/r' . $userId . '.cfg.php')) {
			return require($cfg['path']['conf'] . 'right/r' . $userId . '.cfg.php');
		} else {
			return array();
		}
		*/
	}
	
	
	function getGroupRight($groupId){
		global $cfg;
		if (is_file($cfg['path']['conf'] . 'group_right/g'. $groupId .'.cfg.php')) {
			return require($cfg['path']['conf'] . 'group_right/g'. $groupId .'.cfg.php');
		} else {
			return array();
		}
	}
	
	function getGroupRightFromDatabase($groupId){
		$sql = "select group_id,allow from `fke_group` where group_id='$groupId'";
		$groupRightInfo = $this->db->getValue($sql);
		if ($groupRightInfo['allow']){
			$arrRights = explode(',', $groupRightInfo['allow']);
			$rights = array();
			foreach ($arrRights as $key=>$val){
				$rights[$val] = '1';
			}
			return $rights;
		}else{
			return array();
		}
	}
	/**
	 * 取总用户数
	 * @access public
	 * @return NULL
	 */
	function getCount() {
		return $this->db->getValue('select count(*) from fke_users');
	}
	
	/**
	 * 取用户组的用户列表
	 * @param string $group_id 用户组ID
	 * @param string $type 返回类型array数组,string字符串
	 * @access public
	 * @return mixed
	 */
	function getGroupUser($group_id, $type='array') {
		if ($group_id==NULL) {
			$group_id = $this->getAuthInfo('group_id');
		}
		$list = $this->db->select('select user_id from fke_users where group_id=' . intval($group_id));
		if ($type=='array') {
			return $list;
		} else {
			return implode(',', $list);
		}
	}
	
	function getAllUsers($columns='*',$condition='',$order = ''){
		if($condition != ''){
			$condition = ' where ' .$condition;
		}
		return $this->db->select('select '.strtolower($columns) .' from fke_users '.$condition .' ' .$order);
	}
	
	/*function rightMenu($menu,$right){
		if (is_array($menu) && is_array($right)) {
			
		} else {
			return array();
		}
	}*/
}
?>