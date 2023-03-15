<?php
/**
 * 用户管理
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * Member 会员管理类
 * @package Apps
 */
class Member
{

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
    var $tName = "fke_member";

    /**
     * VIP用户时间表
     *
     * @var string
     */
    var $tNameVip = "fke_member_vip";

    /**
     * 经纪人的详细信息
     *
     * @var string
     */

    var $tNameBrokerInfo = "fke_broker_info";
    /**
     * 业主的详细信息
     *
     * @var string
     */
    var $tNameOwnerInfo = "fke_owner_info";
    /**
     * 用户小区专家表
     *
     * @var string
     */
    var $tNameAdviser = "fke_borough_adviser";

    /**
     * 用户充值记录
     *
     * @var string
     */
    var $tNameMoneyLog = "fke_money_log";

    /**
     * 用户登陆日志
     *
     * @var string
     */
    var $tNameLoginlog = "fke_member_loginlog ";

    /**
     * 构造函数
     *
     * @param source $db
     */
    function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $passwd 密码
     * @access public
     * @return mixed
     */
    function login($username, $passwd, $notForget = 0)
    {
        global $cfg;
        //取得当前用户验证码状态，判断是否需要输入验证码
        $username = trim($username);
        $passwd = trim($passwd);
        if (empty($username)) {
            return array(
                'error' => 1,
                'validate' => 0,
                'msg' => '登录用户名不能为空！'
            );
        }
        if (empty($passwd)) {
            return array(
                'error' => 1,
                'validate' => 0,
                'msg' => '登录密码不能为空！'
            );
        }

        $sql = 'select id, username, passwd, account_open, login_error_times, login_time from ' . $this->tName . ' where username = \'' . $username . '\'';
        $info = $this->db->getValue($sql);
        if (empty($info)) {
            if (is_mobile($username)) {
                $sql = 'select id from ' . $this->tNameBrokerInfo . ' where mobile = \'' . $username . '\' and mobile_checked = 1';
                $info = $this->db->getValue($sql);
                if ($info['id']) {
                    $sql = 'select id, username, passwd, account_open, login_error_times, login_time from ' . $this->tName . ' where id = ' . $info['id'];
                    $info = $this->db->getValue($sql);
                } else {
                    $info = array();
                }
            } elseif (is_email($username)) {
                $sql = 'select id, username, passwd, account_open, login_error_times, login_time from ' . $this->tName . ' where email = \'' . $username . '\' and email_checked = 1';
                $info = $this->db->getValue($sql);
            }
        }

        if (empty($info)) {
            return array(
                'error' => 1,
                'validate' => 0,
                'msg' => '用户不存在！'
            );
        }

        if ($info['account_open'] == 0) {
            return array(
                'error' => 1,
                'validate' => 0,
                'msg' => '您的账户已被管理员停用！'
            );
        }

        if ($info['passwd'] != md5($passwd)) {
            $this->incLoginErrorTimes($info['id']);
            if ($info['login_error_times'] >= 4 && $info['login_time'] + 86400 > time()) {
                return array(
                    'error' => 2,
                    'validate' => 1,
                    'msg' => '登录密码错误！'
                );
            } else {
                return array(
                    'error' => 2,
                    'validate' => 0,
                    'msg' => '登录密码错误！'
                );
            }

        }

        if ($info['login_time'] + 86400 > time() && $info['login_error_times'] > 4) {
            require_once(dirname(dirname(__FILE__)) . '/lib/classes/Verify.class.php');
            $Verify = new Verify();
            $verify_code = trim($_POST['valid']);
            $check_result = $Verify->check($verify_code);
            if ($check_result == false) {
                return array(
                    'error' => 3,
                    'validate' => 1,
                    'msg' => '验证码错误！'
                );
            }
        }

        $this->setLoginData($info['id'], $notForget);

        //更新最后登陆时间和登陆次数
        $last_login_ip = getclientip(1);
        $this->db->execute("update " . $this->tName . " set last_login ='" . $cfg['time'] . "', logins=logins+1, last_login_ip = '" . $last_login_ip ."' where id = " . $info['id']);
        $this->resetLoginErrorTimes($info['id']);

        //更新索引中的排序
        $Sphinx = Sphinx::getInstance();
        $mid = intval($info['id']);
        $Sphinx->UpdateAttributes(SPHINX_SEARCH_MEMBER_INDEX, array('last_login'), array($mid => array($cfg['time'])));

        //重新计算用户活跃度
        //1:记录loginlog
        $this->addLoginLog($username);
        //2:计算活跃度
        if ($info['user_type'] == 1) {
            //7天前
            $dateNow = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $dateBefore = $dateNow - 518400;
            $loginlog = $this->db->select("select add_time from " . $this->tNameLoginlog . " where username = '" . $username . "' and add_time >= " . $dateBefore . " order by add_time asc");
            $active_arr = array_fill(0, 7, 0);
            $activeRate = 0;
            foreach ($loginlog as $item) {
                for ($i = 0; $i <= 6; $i++) {
                    if ($dateBefore + $i * 86400 == $item) {
                        $activeRate += 1000 + pow(2, $i);
                        $active_arr[$i] = 1;
                        break;
                    }
                }
            }

            if ($active_arr) {
                $active_str = implode('|', $active_arr);
            } else {
                $active_str = '';
            }
            $this->db->execute("update " . $this->tName . " set active_str ='" . $active_str . "' , active_rate='" . $activeRate . "' where id = " . $info['id']);

            //3：总活跃度每天统计一次，这里不做统计

        }
        return array(
            'error' => 0,
            'validate' => 0,
            'msg' => '登录成功'
        );
    }

    function mobileLogin($info, $notForget = 0)
    {
        global $cfg;
        $username = trim($info['username']);
        $passwd = trim($info['passwd']);

        //验证成功，用户登录
        //$auth_code = authcode($info['id'] . "\t" . $passwd . "\t" . $info['user_type'], 'ENCODE', $cfg['auth_key']);
        $this->setLoginData($info['id'], $notForget);

        //更新最后登陆时间和登陆次数
        $last_login_ip = getclientip(1);
        $this->db->execute("update " . $this->tName . " set last_login ='" . $cfg['time'] . "', logins=logins+1, last_login_ip = '" . $last_login_ip ."' where id = " . $info['id']);

        //更新索引中的排序
        $Sphinx = Sphinx::getInstance();
        $mid = intval($info['id']);
        $Sphinx->UpdateAttributes(SPHINX_SEARCH_MEMBER_INDEX, array('last_login'), array($mid => array($cfg['time'])));

        //重新计算用户活跃度
        //1:记录loginlog
        $this->addLoginLog($username);
        //2:计算活跃度
        if ($info['user_type'] == 1) {
            //7天前
            $dateNow = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $dateBefore = $dateNow - 518400;
            $loginlog = $this->db->select("select add_time from " . $this->tNameLoginlog . " where username = '" . $username . "' and add_time>=" . $dateBefore . " order by add_time asc");
            $active_arr = array_fill(0, 7, 0);
            $activeRate = 0;
            foreach ($loginlog as $item) {
                for ($i = 0; $i <= 6; $i++) {
                    if ($dateBefore + $i * 86400 == $item) {
                        $activeRate += 1000 + pow(2, $i);
                        $active_arr[$i] = 1;
                        break;
                    }
                }
            }

            if ($active_arr) {
                $active_str = implode('|', $active_arr);
            } else {
                $active_str = '';
            }
            $this->db->execute("update " . $this->tName . " set active_str ='" . $active_str . "' , active_rate='" . $activeRate . "' where id = " . $info['id']);

        }
        //3：总活跃度每天统计一次，这里不做统计


        return array(
            'error' => 0,
            'msg' => '登录成功！'
        );
    }

    //ajax登录
    function AjaxLogin($username, $passwd, $notForget = 0, $loginType)
    {
        global $cfg;
        $result = array();
        $username = strtolower($username);
        if (isset($_POST['valid'])) {
            if (md5(strtolower($_POST['valid'])) != $_COOKIE[COOKIE_PREFIX . 'validString']) {
                $result['msg'] = '验证码错误！';
                $result['state'] = 0;
                return $result;
            }
        }
        if (empty($username)) {
            $result['state'] = 0;
            $result['msg'] = '用户名不能为空，请输入用户名！';
            return $result;
        }
        if (empty($passwd)) {
            $result['state'] = 0;
            $result['msg'] = '密码不能为空，请输入密码！';
            return $result;
        }
        //username 是手机号
        if ($loginType == 'email') {
            $sql = "select * from `" . $this->tName . "` where email='" . $username . "' and passwd='" . md5($passwd) . "' and user_type='1'";
        } elseif ($loginType == 'mobile') {
            $sql = "select * from `" . $this->tName . "` where username='" . $username . "' and passwd='" . md5($passwd) . "' and user_type='1'";
        }
        $info = $this->db->getValue($sql);
        if (!$info) {
            $result['msg'] = '用户名或密码错误！';
            $result['state'] = 0;
            return $result;
        } else {
            $this->setLoginData($info['id'], $notForget);

            //更新最后登陆时间和登陆次数
            $this->db->execute("update " . $this->tName . " set last_login ='" . $cfg['time'] . "' , logins=logins+1 where id = " . $info['id']);

            //重新计算用户活跃度
            //1:记录loginlog
            $this->addLoginLog($username);
            //2:计算活跃度
            if ($info['user_type'] == 1) {
                //7天前
                $dateNow = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $dateBefore = $dateNow - 518400;
                $loginlog = $this->db->select("select add_time from " . $this->tNameLoginlog . " where username = '" . $username . "' and add_time>=" . $dateBefore . " order by add_time asc");
                $active_arr = array_fill(0, 7, 0);
                $activeRate = 0;
                foreach ($loginlog as $item) {
                    for ($i = 0; $i <= 6; $i++) {
                        if ($dateBefore + $i * 86400 == $item) {
                            $activeRate += 1000 + pow(2, $i);
                            $active_arr[$i] = 1;
                            break;
                        }
                    }
                }

                if ($active_arr) {
                    $active_str = implode('|', $active_arr);
                } else {
                    $active_str = '';
                }
                $this->db->execute("update " . $this->tName . " set active_str ='" . $active_str . "' , active_rate='" . $activeRate . "' where id = " . $info['id']);

            }
            //3：总活跃度每天统计一次，这里不做统计
            $result['state'] = 1;
            $result['msg'] = '登录成功！';
            return $result;
        }
    }

    public function setLoginData($member_id, $not_forget = false)
    {
        global $cfg;
        /*$expire_time = 0;
        $not_forget = true; //默认记录登录状态
        if ($not_forget == true) {
            $expire_time = time() + 31536000;
        }*/
        $expire_time = time() + 31536000;

        $member_info = $this->getInfo($member_id, 'id, username, token, token_expire_time');
        if ($member_info['token_expire_time'] < time()) {
            $token = GetAuthCode('', 32);
            $token_expire_time = time() + 31536000;
            $sql = "update {$this->tName} set token = '{$token}', token_expire_time = '{$token_expire_time}' where id = {$member_id}";
            $this->db->execute($sql);
        } else {
            $token = $member_info['token'];
        }
        $token = md5($member_info['username'] . $token);
        setcookie(COOKIE_PREFIX . 'AUTH_MEMBER_ID', $member_info['id'], $expire_time, '/', $cfg['domain']);
        setcookie(COOKIE_PREFIX . 'AUTH_MEMBER_NAME', $member_info['username'], $expire_time, '/', $cfg['domain']);
        setcookie(COOKIE_PREFIX . 'AUTH_MEMBER_TOKEN', $token, $expire_time, '/', $cfg['domain']);
    }

    public function clearLoginData()
    {
        global $cfg;
        setcookie(COOKIE_PREFIX . 'AUTH_MEMBER_ID', '', -1, '/', $cfg['domain']);
        setcookie(COOKIE_PREFIX . 'AUTH_MEMBER_NAME', '', -1, '/', $cfg['domain']);
        setcookie(COOKIE_PREFIX . 'AUTH_MEMBER_TOKEN', '', -1, '/', $cfg['domain']);
    }


    public function resetLoginErrorTimes($member_id)
    {
        $sql = 'update ' . $this->tName . ' set login_error_times = 0, login_time = ' . time() . ' where id = ' . $member_id;
        $this->db->execute($sql);
    }

    public function incLoginErrorTimes($member_id)
    {
        $sql = 'update ' . $this->tName . ' set login_error_times = login_error_times + 1, login_time = ' . time() . ' where id = ' . $member_id;
        $this->db->execute($sql);
    }

    /**
     * 注册数据
     *
     * @param unknown_type $postInfo
     */
    function register($postInfo)
    {
        global $cfg, $page;
        $result = array();
        $user_type = intval($postInfo['user_type']);
        /*if ($user_type != 1 && $user_type != 2) {
            $result['state'] = 0;
            $result['msg'] = '请选择正确的用户类型！';
            return $result;
        }*/

        $username = $postInfo['username'];
        /*$sql = "select * from {$this->tName} where username='{$postInfo['username']}'";
        $dataInfo = $this->db->getValue($sql);
        if ($dataInfo) {
            $result['state'] = 0;
            $result['msg'] = '用户已存在！';
            return $result;
        }*/


        if (!empty($postInfo['password'])) {
            if (strlen($postInfo['password']) < 6 || strlen($postInfo['password']) > 20) {
                return array(
                    'state' => 0,
                    'msg' => '密码为6到20个字符！'
                );
            }
            /*if ($postInfo['password'] != $postInfo['password2']) {
                return array(
                    'state' => 0,
                    'msg' => '两次输入密码不相同！'
                );
            }*/
            $password = md5($postInfo['password']);
        } else {
            $password = '';
        }

        //后台开启经纪人是否免费注册的检测
        /*if ($page->memberOpen == 1) {
            $postInfo['status'] = 0;
        } else {
            $postInfo['status'] = 1;
        }*/
        //注册默认没有开店
        $postInfo['status'] = 0;
        $insertInfo = array(
            'username' => $postInfo['username'],
            'passwd' => $password,
            'email' => $postInfo['email'],
            'user_type' => $user_type,
            'logins' => 1,
            'last_login' => time(),
            'add_time' => time(),
            'last_login_ip' => getclientip(1),
            'leader_id' => intval($postInfo['leader_id']),
            'device_id' => intval($postInfo['device_id']),
            'web_host_id' => intval($postInfo['web_host_id']),
            'wechat_openid' => $postInfo['wechat_openid'],
            'wechat_unionid' => $postInfo['wechat_unionid']
        );

        try {
            $this->db->insert($this->tName, $insertInfo);
            $user_id = $this->db->getInsertId();

            if (empty($user_id)) {
                return array(
                    'state' => 0,
                    'msg' => '注册失败！'
                );
            }

            if ($postInfo['mobile']) {
                $mobile_checked = 1;
            } else {
                $mobile_checked = 0;
            }
            //插入一条用户详细信息
            $insertInfo = array(
                'realname' => $postInfo['realname'],
                'id' => $user_id,
                'mobile' => $postInfo['mobile'],
                'mobile_checked' => $mobile_checked,
                'province_id' => intval($postInfo['province_id']),
                'city_id' => intval($postInfo['city_id']),
                'cityarea_id' => intval($postInfo['cityarea_id']),
                'cityarea2_id' => intval($postInfo['cityarea2_id']),
                'city_website_id' => intval($postInfo['city_website_id']),
                'gender' => intval($postInfo['gender']),
                'company' => '',
                'outlet' => '',
                'introduce' => '',
                'outlet_first' => '',
                'outlet_last' => '',
                'status' => $postInfo['status']
            );
            $this->db->insert($this->tNameBrokerInfo, $insertInfo);
            //自动登录
            $this->setLoginData($user_id, true);

            //重新计算用户活跃度
            //1:记录loginlog
            $this->addLoginLog($username);
            //2:计算活跃度
            //7天前
            $dateNow = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $dateBefore = $dateNow - 604800;
            $loginlog = $this->db->select("select add_time from " . $this->tNameLoginlog . " where username = '" . $postInfo['username'] . "' and add_time>=" . $dateBefore . " order by add_time asc");
            $active_arr = array_fill(0, 7, 0);
            $activeRate = 0;
            foreach ($loginlog as $item) {
                for ($i = 0; $i <= 6; $i++) {
                    if ($dateBefore + $i * 86400 == $item) {
                        $activeRate += 1000 + pow(2, $i);
                        $active_arr[$i] = 1;
                        break;
                    }
                }
            }

            if ($active_arr) {
                $active_str = implode('|', $active_arr);
            } else {
                $active_str = '';
            }
            $this->db->execute("update " . $this->tName . " set active_str ='" . $active_str . "' , active_rate='" . $activeRate . "' where id = " . $user_id);
            //3：总活跃度每天统计一次，这里不做统计

            return array(
                'state' => 1,
                'userid' => $user_id,
                'msg' => '注册成功！'
            );
        } catch (Exception $e) {
            return array(
                'state' => 0,
                'msg' => '注册失败！'
            );
        }
    }

    /**
     * 用户登出
     * @access public
     * @return mixed
     */
    function logout($wap = 0)
    {
        $this->clearLoginData();
    }

    /**
     * 取当前用户信息
     * @access public
     * @return array
     */
    function getAuthInfo($field = '')
    {
        $member_id = $_COOKIE[COOKIE_PREFIX . 'AUTH_MEMBER_ID'];
        $member_token = $_COOKIE[COOKIE_PREFIX . 'AUTH_MEMBER_TOKEN'];
        //取得用户信息
        $member_info = $this->getInfo($member_id, '*', true);
        $token = md5($member_info['username'] . $member_info['token']);
        if (empty($member_info) || $member_token != $token || $member_info['token_expire_time'] < time()) {
            $this->clearLoginData();
            return false;
        }

        if ($field) {
            return $member_info[$field];
        } else {
            return $member_info;
        }
    }

    /**
     * VIP用户到期脚本执行
     * @return array
     */
    function doVipTime()
    {
        $today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $dataList = $this->getVipDoTimeList('*', 'to_time<' . $today);
        foreach ($dataList as $key => $value) {
            $this->deleteVip($dataList[$key]['member_id']);  //删除会员VIP时间表
            $this->update($dataList[$key]['member_id'], 'vip', 0);  //在member表中将vip更新为0
            $this->update($dataList[$key]['member_id'], 'vexation', 0);  //在member表中将vexation更新为0
        }

    }

    /**
     * 取得符合到期时间的VIP用户
     * @access public
     *
     * @param array $pageLimit
     * @return array
     **/
    function getVipDoTimeList($fileld = '*', $where = '')
    {
        $this->db->open('select * from ' . $this->tNameVip . ' where ' . $where);
        $result = array();
        while ($rs = $this->db->next()) {
            $result[] = $rs;
        }
        return $result;
    }

    /**
     * 升级vip
     * @param array $days 天数
     * @access public
     * @return bool
     */
    function vipSave($fileddata, $value)
    {
        global $page;
        $field_array = array(
            'member_id' => $fileddata['member_id'],
            'add_time' => time(),
            'to_time' => $fileddata['to_time'],
        );

        //增加急售标签
        if ($value == 1) {
            $this->update($fileddata['member_id'], 'vexation', $page->vip1Vexation);
        }
        if ($value == 2) {
            $this->update($fileddata['member_id'], 'vexation', $page->vip2Vexation);
        }


        //如果有之前有会员vip记录 要删除
        if ($this->getVipTime($fileddata['member_id'], '*')) {
            $this->deleteVip($fileddata['member_id']);
        }
        $this->update($fileddata['member_id'], 'vip', $value);
        return $this->db->insert($this->tNameVip, $field_array);
    }

    /**
     * 减少加急房源条数
     * @access public
     * @return bool
     */
    function deleteVexation($id, $value)
    {
        return $this->db->execute('update ' . $this->tName . ' set vexation = vexation -' . $value . ' where id=' . $id);
    }

    /**
     * 删除vip记录
     * @param array $days 天数
     * @access public
     * @return bool
     */
    function deleteVip($id)
    {
        return $this->db->execute('delete from ' . $this->tNameVip . ' where member_id=' . $id);
    }

    /**
     * 检查认证信息
     * @access public
     * @return NULL
     */
    function auth()
    {
        if ($this->checkLogin() == false) {
            $this->clearLoginData();
            header('Location:/member/login.php?backurl=' . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
    }

    public function checkLogin()
    {
        $member_id = $_COOKIE[COOKIE_PREFIX . 'AUTH_MEMBER_ID'];
        $member_token = $_COOKIE[COOKIE_PREFIX . 'AUTH_MEMBER_TOKEN'];
        //取得用户信息
        $member_info = $this->getInfo($member_id, 'id, username, token, token_expire_time');
        $token = md5($member_info['username'] . $member_info['token']);
        if (empty($member_info) || $token != $member_token || $member_info['token_expire_time'] < time()) {
            $this->clearLoginData();
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查密码
     * @access public
     *
     * @param string $pwd
     * @param int $memberId
     * @return bool
     **/
    function checkPwd($pwd, $memberId)
    {
        $member_info = $this->getInfo($memberId, 'id, passwd');
        if (empty($member_info)) {
            return false;
        }

        if (md5($pwd) != $member_info['passwd']) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 修改会员密码
     *
     * @param  string $password
     *
     * @return bool
     */
    function updatePasswd($id, $email, $pssword)
    {
        return $this->db->execute('update ' . $this->tName . ' set passwd =\'' . md5($pssword) . '\' where username = \'' . $username . '\' and email=\'' . $email . '\'');
    }

    /**
     * 取回密码的修改密码
     *
     * @param string $username
     * @param string $email
     * @param  string $password
     *
     * @return bool
     */
    function updateFromGetPsw($username, $email, $pssword)
    {
        return $this->db->execute('update ' . $this->tName . ' set passwd =\'' . md5($pssword) . '\' where username = \'' . $username . '\' and email=\'' . $email . '\'');
    }

    /**
     * 保存经纪人会员信息
     * @access public
     *
     * @param array $memberInfo
     * @return bool
     **/
    function saveBroker($memberInfo)
    {
        $memberInfo['id'] = intval($memberInfo['id']);

        //修改用户信息
        $updateField = array();
        if (!empty($memberInfo['email'])) {
            $updateField = array(
                'email' => $memberInfo['email'],
                'email_checked' => 0
            );
        }

        //如果是切换会员类型
        if (in_array($memberInfo['user_type'], array(1, 2, 4))) {
            $updateField['user_type'] = intval($memberInfo['user_type']);
            $updateField['user_type_sub'] = 0;
            $updateField['user_type_custom'] = '';
        } elseif ($memberInfo['user_type'] == 3 && in_array($memberInfo['user_type_sub'], array(1, 2, 3, 4))) {
            $updateField['user_type'] = 3;
            $updateField['user_type_sub'] = intval($memberInfo['user_type_sub']);
            $updateField['user_type_custom'] = '';
        }

        if (!empty($updateField)) {
            $this->db->update($this->tName, $updateField, 'id=' . $memberInfo['id']);
        }

        if ($this->db->getValue('select id from ' . $this->tNameBrokerInfo . ' where id=' . $memberInfo['id'])) {
            //update
            $updateField = array(
                'province_id' => intval($memberInfo['province_id']),
                'city_id' => intval($memberInfo['city_id']),
                'cityarea_id' => intval($memberInfo['cityarea_id']),
                'cityarea2_id' => intval($memberInfo['cityarea2_id']),
                'address' => trim($memberInfo['address']),
                'signed' => $memberInfo['signed'],
                'company' => $memberInfo['company'],
                'outlet' => $memberInfo['outlet_first'],
                'outlet_addr' => $memberInfo['outlet_addr'],
                'com_tell' => $memberInfo['com_tell'],
                'com_fax' => $memberInfo['com_fax'],
                //'gender' => intval($memberInfo['gender']),
                //'birthday' => $memberInfo['birthday'],
                'borough_id' => intval($memberInfo['borough_id']),
                //'msn' => $memberInfo['msn'],
                'introduce' => $memberInfo['introduce'],
                //'broker_type' => intval($memberInfo['broker_type']),
                //'zhiwu' => $memberInfo['zhiwu'],
                'servicearea' => $memberInfo['servicearea'],
                'brand_apartment_id' => intval($memberInfo['brand_apartment_id']),
                'status' => intval($memberInfo['status'])
            );
            if ($memberInfo['qq']) {
                $updateField['qq'] = $memberInfo['qq'];
            }
            if ($memberInfo['wechat']) {
                $updateField['wechat'] = $memberInfo['wechat'];
            }
            if ($memberInfo['city_website_id']) {
                $updateField['city_website_id'] = $memberInfo['city_website_id'];
            }
            //真实姓名修改
            if (!empty($memberInfo['realname'])) {
                $updateField['realname'] = $memberInfo['realname'];
            }
            //手机修改
            if (!empty($memberInfo['mobile'])) {
                $updateField['mobile'] = $memberInfo['mobile'];
                $updateField['mobile_checked'] = 0;
            }
            $result = $this->db->update($this->tNameBrokerInfo, $updateField, 'id=' . $memberInfo['id']);
        } else {
            //insert
            $insertField = array(
                'id' => $memberInfo['id'],
                'province_id' => intval($memberInfo['province_id']),
                'city_id' => intval($memberInfo['city_id']),
                'cityarea_id' => intval($memberInfo['cityarea_id']),
                'cityarea2_id' => intval($memberInfo['cityarea2_id']),
                'address' => trim($memberInfo['address']),
                'realname' => $memberInfo['realname'],
                'signed' => $memberInfo['signed'],
                'company' => $memberInfo['company'],
                'outlet' => $memberInfo['outlet_first'],
                'outlet_addr' => $memberInfo['outlet_addr'],
                'com_tell' => $memberInfo['com_tell'],
                'com_fax' => $memberInfo['com_fax'],
                //'birthday' => $memberInfo['birthday'],
                'borough_id' => intval($memberInfo['borough_id']),
                'qq' => $memberInfo['qq'],
                'msn' => $memberInfo['msn'],
                'introduce' => $memberInfo['introduce'],
                'broker_type' => intval($memberInfo['broker_type']),
                'zhiwu' => $memberInfo['zhiwu'],
                'brand_apartment_id' => intval($memberInfo['brand_apartment_id']),
                'status' => $memberInfo['status']
            );
            $result = $this->db->insert($this->tNameBrokerInfo, $insertField);
        }
        return $result;

    }


    /**
     * 检查会员重复信息
     * @access public
     *
     * @param string $username
     * @return bool
     **/
    function checkMemberUnique($username)
    {
        return $this->db->getValue('select id from ' . $this->tName . ' where username=\'' . $username . '\'');
    }

    function CheckMemberUniqueFields($field, $value)
    {
        return $this->db->getValue('select id from ' . $this->tNameBrokerInfo . ' where ' . $field . '=\'' . $value . '\'');
    }

    function CheckMemberEmailUnique($email, $mid)
    {
        if ($mid) {
            $whereSQL = " and id<>'$mid'";
        }
        return $this->db->getValue('select id from ' . $this->tName . ' where email=\'' . $email . '\' and email_checked = 1 ' . $whereSQL);
    }

    function CheckMemberMobileUnique($mobile, $mid)
    {
        if ($mid) {
            $whereSQL = " and id<>'$mid'";
        }
        return $this->db->getValue('select id from ' . $this->tNameBrokerInfo . ' where mobile=\'' . $mobile . '\' and mobile_checked = 1 ' . $whereSQL);
    }

    /**
     * 检查是否有重复的身份证，认证审核时使用
     * @access public
     *
     * @param string $username
     * @return bool
     **/
    function checkIdcardUnique($idcard)
    {
        return $this->db->getValue('select id from ' . $this->tNameBrokerInfo . ' where idcard =\'' . $idcard . '\'');
    }

    function checkBusinessLicenseUnique($business_license)
    {
        return $this->db->getValue('select id from ' . $this->tNameBrokerInfo . ' where business_license =\'' . $business_license . '\'');
    }

    function checkIdcardUnique1($idcard, $mid)
    {
        return $this->db->getValue('select id from ' . $this->tNameBrokerInfo . ' where id != ' . $mid . ' and idcard =\'' . $idcard . '\'');
    }

    /**
     * 检查用户输入的用户名和邮件地址 ， 取回密码时使用
     * @access public
     *
     * @param string $username
     * @return bool
     **/
    function checkMemberEmail($username, $email)
    {
        return $this->db->getValue('select id from ' . $this->tName . ' where username=\'' . $username . '\' and email =\'' . $email . '\'');
    }

    /**
     * 取得member信息列表
     * @access public
     *
     * @param array $pageLimit
     * @return array
     **/
    function getList($pageLimit, $fileld = '*', $where = '', $order = ' order by id desc ')
    {
        if ($where) {
            $where = ' where ' . $where;
        }
        $this->db->open('select * from ' . $this->tName . ' ' . $where . ' ' . $order, $pageLimit['rowFrom'], $pageLimit['rowTo']);
        $result = array();
        while ($rs = $this->db->next()) {
            $result[] = $rs;
        }
        return $result;
    }

    /**
     * 取用户信息
     * @param string $memberId 用户ID
     * @param string $field 字段
     * @access public
     * @return array
     */
    function getInfo($memberId, $field = '*', $moreInfo = false)
    {
        $memberId = intval($memberId);
        if ($moreInfo) {
            $userInfo = $this->db->getValue('select ' . $field . ' from ' . $this->tName . ' where id=' . $memberId);

            $detailInfo = $this->db->getValue('select * from ' . $this->tNameBrokerInfo . ' where id=' . $memberId);

            return array_merge((array)$userInfo, (array)$detailInfo);

        } else {
            return $this->db->getValue('select ' . $field . ' from ' . $this->tName . ' where id=' . $memberId);
        }

    }

    /**
     * 查询VIP表字段
     * @param string $memberId 用户ID
     * @param string $field 字段
     * @access public
     * @return array
     */
    function getVipTime($memberId, $field = '*')
    {
        return $this->db->getValue('select ' . $field . ' from ' . $this->tNameVip . ' where member_id=' . $memberId);
    }


    /**
     * 取用户信息
     * @param string $memberId 用户ID
     * @param string $user_type 用户类型
     * @access public
     * @return array
     */
    function getRealName($memberId, $user_type)
    {

        return $this->db->getValue('select realname from ' . $this->tNameBrokerInfo . ' where id=' . $memberId);

    }

    /**
     * 修改用户信息 ，区别于直接保存全部信息，这里如果需要修改用户的基本信息和详细信息，需要执行两次的操作
     * 例如：
     * $member->updateInfo($id , $basicArray, $isInfo=false ,$user_type=1);
     * $member->updateInfo($id , $extentArray, $isInfo=true ,$user_type=1);
     *
     * @param int $id
     * @param array $fieldArray
     * @param bool $isInfo
     * @param 1 or 2 $user_type
     */
    function updateInfo($id, $fieldArray, $isInfo = false, $user_type = 1)
    {
        if ($isInfo) {

            $this->db->update($this->tNameBrokerInfo, $fieldArray, ' id =' . $id);
        } else {
            $this->db->update($this->tName, $fieldArray, ' id =' . $id);
        }
    }

    /**
     * 插入扩展用户信息
     * 例如：
     * $member->insertInfo($id , $basicArray, $isInfo=false ,$user_type=1);
     *
     * @param array $fieldArray
     * @param 1 or 2 $user_type
     */
    function insertInfo($fieldArray, $isInfo = false, $user_type = 1)
    {

        $this->db->insert($this->tNameBrokerInfo, $fieldArray);

    }

    /**
     * 删除用户信息
     * @param mixed $members 用户ID
     * @access public
     * @return bool
     */
    function delete($members)
    {
        if (is_array($members)) {
            $members = implode(',', $members);
            $where = ' id in (' . $members . ')';
            $shopwhere = ' broker_id in (' . $members . ')';
            $memberwhere = ' member_id in (' . $members . ')';
            $houseWhere = ' mid in (' . $members . ')';

        } else {
            $where = ' id=' . intval($members);
            $shopwhere = ' broker_id=' . intval($members);
            $memberwhere = ' member_id=' . intval($members);
            $houseWhere = ' mid = ' . intval($members);

        }
        $username = $this->db->select('select username from ' . $this->tName . ' where ' . $where);

        //删除出售房源记录
        //取得房源记录ID
        $sql = "select id, mid from fke_housesell where $houseWhere";
        $result = $this->db->select($sql);
        $house_id = array();
        foreach ($result as $val) {
            $house_id[] = $val['id'];
        }
        $house_ids = implode(',', $house_id);
        $this->db->execute('delete from fke_housesell where ' . $houseWhere);
        if ($house_ids) {
            $this->db->execute('delete from fke_housesell_extend where house_id in (' . $house_ids . ')');
        }

        //删除出租房源记录
        //取得房源记录ID
        $sql = "select id, mid from fke_houserent where $houseWhere";
        $result = $this->db->select($sql);
        $house_id = array();
        foreach ($result as $val) {
            $house_id[] = $val['id'];
        }
        $house_ids = implode(',', $house_id);
        $this->db->execute('delete from fke_houserent where ' . $houseWhere);
        if ($house_ids) {
            $this->db->execute('delete from fke_houserent_extend where house_id in (' . $house_ids . ')');
        }

        //删除fke_member_loginlog会员登录记录
        foreach ($username as $value) {
            $this->db->execute('delete from fke_member_loginlog where username = "' . $value . '"');
        }

        //删除会员fke_borker_info 经纪人表信息
        $this->db->execute('delete from fke_broker_info where ' . $where);

        //删除会员fke_borker_identity 经纪人身份认证表信息
        $this->db->execute('delete from fke_broker_identity where ' . $shopwhere);

        //删除会员fke_borker_friends 经纪人好友表信息
        // 		$this->db->execute('delete from fke_broker_friends where ' . $shopwhere);

        //删除会员fke_broker_avatar 经纪人头像表信息
        $this->db->execute('delete from fke_broker_avatar where ' . $shopwhere);

        //删除会员fke_broker_aptitude 经纪人执业认证表信息
        $this->db->execute('delete from fke_broker_aptitude where ' . $shopwhere);

        //删除fke_innernote会员站内信接收记录
        foreach ($username as $value) {
            $this->db->execute('delete from fke_innernote where msg_to = "' . $value . '"');
        }

        //删除fke_innernote会员站内信发送记录
        foreach ($username as $value) {
            $this->db->execute('delete from fke_innernote where msg_from = "' . $value . '"');
        }

        //删除会员fke_integral_log 积分记录
        $this->db->execute('delete from fke_integral_log where ' . $memberwhere);

        //如果是经纪人删除fke_shop_conf经纪人网店信息
        $this->db->execute('delete from fke_shop_conf where ' . $shopwhere);

        //删除店铺fke_shop_viewlog 好友信息
        $this->db->execute('delete from fke_shop_viewlog where ' . $shopwhere);

        //删除会员fke_member 表信息
        $this->db->execute('delete from ' . $this->tName . ' where ' . $where);

        return true;


    }

    /**
     * 操作用户状态 不物理删除
     * @param mixed $members 用户ID
     * @access public
     * @return bool
     */
    function changeStatus($members, $status)
    {
        if (is_array($members)) {
            $members = implode(',', $members);
            $where = ' id in (' . $members . ')';
        } else {
            $where = ' id=' . intval($members);
        }
        return $this->db->execute('update ' . $this->tName . ' set status = ' . $status . ' where ' . $where);
    }

    /**
     * 更新某个字段
     * @param mixed $ids ID
     * @access public
     * @return bool
     */
    function update($ids, $field, $value)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
            $where = ' id in (' . $ids . ')';
        } else {
            $where = ' id=' . intval($ids);
        }
        return $this->db->execute('update ' . $this->tName . ' set ' . $field . ' = \'' . $value . '\' where ' . $where);
    }


    public function changeMemberType($mid, $user_type_value)
    {
        if (empty($mid)) {
            return false;
        }
        $result = $this->update($mid, 'user_type', $user_type_value);
        if ($result) {
            //出租信息更新
            $houserent = new HouseRent($this->db);
            $houseList = $houserent->getAll('id', 'mid=' . $mid);
            if ($houseList) {
                $id = array();
                foreach ($houseList as $item) {
                    $id[] = $item['id'];
                }
                $houserent->updateHouse($id, array('mtype' => $user_type_value));
            }

            //出售信息更新
            $housesell = new HouseSell($this->db);
            $houseList = $housesell->getAll('id', 'mid=' . $mid);
            if ($houseList) {
                $id = array();
                foreach ($houseList as $item) {
                    $id[] = $item['id'];
                }
                $housesell->updateHouse($id, array('mtype' => $user_type_value));
            }

            //求租信息更新
            $houseqiuzu = new HouseQiuzu($this->db);
            $houseList = $houseqiuzu->getAll('id', 'mid=' . $mid);
            if ($houseList) {
                $id = array();
                foreach ($houseList as $item) {
                    $id[] = $item['id'];
                }
                $houseqiuzu->updateHouse($id, array('mtype' => $user_type_value));
            }

            //求购信息更新
            $houseqiugou = new HouseQiugou($this->db);
            $houseList = $houseqiugou->getAll('id', 'mid=' . $mid);
            if ($houseList) {
                $id = array();
                foreach ($houseList as $item) {
                    $id[] = $item['id'];
                }
                $houseqiugou->updateHouse($id, array('mtype' => $user_type_value));
            }
        }
        return $result;
    }

    /**
     * 用户充值
     * @access public
     * @return bool
     */
    function updatemoney($id, $value)
    {
        return $this->db->execute('update ' . $this->tName . ' set money = money +' . $value . ' where id=' . $id);
    }

    /**
     * 扣费处理
     * @access public
     * @return bool
     */
    function deletemoney($id, $value)
    {
        return $this->db->execute('update ' . $this->tName . ' set money = money -' . $value . ' where id=' . $id);
    }

    /**
     * 用户充值记录
     * @param $id 用户ID
     * @param $money 充值金额
     * @access public
     * @return bool
     */
    function moneylog($id, $money)
    {
        $insertField = array(
            'member_id' => intval($id),
            'money' => $money,
            'time' => time(),
        );
        return $this->db->insert($this->tNameMoneyLog, $insertField);
    }

    /**
     * 购买条数
     * @access public
     * @return bool
     */
    function updateNum($flag, $id, $value)
    {
        if ($flag == 0) {
            return $this->db->execute('update ' . $this->tName . ' set addsale = addsale +' . $value . ' where id=' . $id);
        } else {
            return $this->db->execute('update ' . $this->tName . ' set addrent = addrent +' . $value . ' where id=' . $id);
        }

    }

    /**
     * 取类别总数
     * @access public
     * @return int
     */
    function getCount($where = '')
    {
        if ($where) {
            $where = ' where ' . $where;
        }
        return $this->db->getValue('select count(*) from ' . $this->tName . ' ' . $where);
    }

    /**
     * 取得所有类型的用户
     *
     * @param int $user_type 1：经纪人；2：业主 0：所有
     */
    function getAll($user_type = 0, $field = '*')
    {
        if ($user_type) {
            $where = ' where user_type = ' . $user_type;
        }
        return $this->db->select('select ' . $field . ' from ' . $this->tName . ' ' . $where);
    }

    /**
     * 用户是否是小区专家
     *
     * @param int $member_id
     * @return bool
     */
    function is_expert($member_id)
    {
        return $this->db->getValue('select count(*) from ' . $this->tNameAdviser . ' where member_id = ' . $member_id . ' and status =1');
    }

    /**
     * 取类别总数
     * @access public
     * @return int
     */
    function getCountBroker($where = '')
    {
        if ($where) {
            $where = ' where ' . $where;
        }
        $sql = "select count(*) from " . $this->tName . " as m
			 left join " . $this->tNameBrokerInfo . " as b " .
            " on m.id = b.id" . $where;
        return $this->db->getValue($sql);
    }

    /**
     * 取得member信息列表
     * @access public
     *
     * @param array $pageLimit
     * @return array
     **/
    function getListBroker($pageLimit, $fileld = '*', $where = '', $order = ' order by m.id desc ')
    {
        if ($where) {
            $where = ' where ' . $where;
        }
        $sql = "select m.id as mid,m.*,b.* from " . $this->tName . " as m
			 left join " . $this->tNameBrokerInfo . " as b " .
            " on m.id = b.id " . $where . ' ' . $order;
        $this->db->open($sql, $pageLimit['rowFrom'], $pageLimit['rowTo']);
        $result = array();
        while ($rs = $this->db->next()) {
            $result[] = $rs;
        }
        return $result;
    }


    /**
     * 判断用户是否为中介(多选)
     * @access public
     *
     * @param array $pageLimit
     * @return array
     **/
    function isbroker($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
            $where = ' id in (' . $ids . ')';
        } else {
            $where = ' id=' . intval($ids);
        }

        $this->db->open("select user_type from " . $this->tName . " where " . $where);
        $result = array();
        while ($rs = $this->db->next()) {
            $result[] = $rs;
        }
        return $result;
    }


    /**
     * 增加loginLog
     *
     * @param string $username
     * @return bool
     */
    function addLoginLog($username)
    {
        $today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $loginLogId = $this->db->getValue("select id from " . $this->tNameLoginlog . " where username = '" . $username . "' and add_time = '" . $today . "'");
        if ($loginLogId) {
            $this->db->execute("update " . $this->tNameLoginlog . " set login_times =login_times+1 where id =" . $loginLogId);
        } else {
            $this->db->execute("insert into  " . $this->tNameLoginlog . " (username ,login_times,add_time) values ('" . $username . "','1','" . $today . "')");
        }
        return true;
    }

    /**
     * 增加积分
     */
    function addScore($member_id, $scores)
    {
        return $this->db->execute("update " . $this->tName . " set scores = scores +" . $scores . " where id=" . $member_id);
    }

    /**
     * 修改积分
     */
    function updateScore($member_id, $scores)
    {
        return $this->db->execute("update " . $this->tName . " set scores = " . $scores . " where id=" . $member_id);
    }

    /**
     * 根据用户名取用户ID
     *
     * @param string $username
     * @return int
     */
    function getIdByUsername($username)
    {
        return $this->db->getValue('select id from ' . $this->tName . ' where username=\'' . $username . '\'');
    }

    /**
     * 根据字段来搜索用户名信息 ， 返回array id
     *
     * @param string $field
     * @param string $keyword
     * @return array
     */
    function searchMember($field, $keyword)
    {
        switch ($field) {
            case "realname":
                $sql = "select id from " . $this->tNameBrokerInfo . " where realname like '%" . $keyword . "%' union select id  from " . $this->tNameOwnerInfo . " where concat(first_name,last_name) like '%" . $keyword . "%'";
                return $this->db->select($sql);
                break;
            case "tel":
                $sql = "select id from " . $this->tNameBrokerInfo . " where mobile like '%" . $keyword . "%' or com_tell like '%" . $keyword . "%' union select id  from " . $this->tNameOwnerInfo . " where mobile like '%" . $keyword . "%' or tell like '%" . $keyword . "%'";
                return $this->db->select($sql);
                break;
            case "idcard":
                $sql = "select id from " . $this->tNameBrokerInfo . " where idcard like '%" . $keyword . "%'";
                return $this->db->select($sql);
                break;
            case "com":
                $sql = "select id from " . $this->tNameBrokerInfo . " where company like '%" . $keyword . "%' or outlet like '%" . $keyword . "%' or outlet_addr like '%" . $keyword . "%' ";
                return $this->db->select($sql);
                break;
            case "avatar":
                $sql = "select id from " . $this->tNameBrokerInfo . " where avatar <> '' ";
                return $this->db->select($sql);
                break;
            case "identity":
                $sql = "select id from " . $this->tNameBrokerInfo . " where idcard<>'' ";
                return $this->db->select($sql);
                break;
            default:
                break;
        }
    }

    /**
     * 取的用户扩展信息
     *
     * @param int $memberId
     * @param int $user_type
     * @return array
     */
    function getMoreInfo($memberId, $user_type)
    {

        return $this->db->getValue('select * from ' . $this->tNameBrokerInfo . ' where id=' . $memberId);

    }

    function GetRefreshNum($mid)
    {
        $sql = "select `day_refresh`, `count_datetime` from {$this->tName} where id='$mid'";
        return $this->db->getValue($sql);
    }

    function checkProfileCompleted($member_info)
    {
        if (in_array($member_info['user_type'], array(1, 3, 4))
            && (empty($member_info['realname']) || empty($member_info['mobile'])
                || empty($member_info['servicearea']) || empty($member_info['company'])
                || empty($member_info['outlet']) || empty($member_info['cityarea_id'])
                || empty($member_info['introduce']))) {
            return false;
        } else {
            return true;
        }
    }
}