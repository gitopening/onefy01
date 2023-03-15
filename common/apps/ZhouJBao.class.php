<?php
/**
 * 周景宝库管理
 *
 * @author net
 * @package 1.0
 * @version $Id$
 */

/**
 * ZhouJBao 周景宝管理类
 * @package Apps
 */
class ZhouJBao
{

    /**
     * @var Object $db 数据库查询对象
     * @access private
     */
    var $db = NULL;

    /**
     *
     *
     * @var string
     */
    var $tEcmsGame = "jianzhanzj_com_ecms_game";

    /**
     * 管理员操作记录表
     *
     * @var string
     */
    var $tNameVip = "jianzhanzj_com_enewsdolog";

    var $tEcmsSoft = "jianzhanzj_com_ecms_soft";

    /**
     *phome_enewstags	TAGS表
      phome_enewstagsclass	TAGS分类表
      phome_enewstagsdata	TAGS信息表
     *
     * @var string
     */

    var $tNameBrokerInfo = "jianzhanzj_com_enewstags";
    /**
     *
     *
     * @var string
     */
    var $tNameOwnerInfo = "jianzhanzj_com_ecms_game_data_1";


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
     * 检查会员重复信息
     * 
     * select title,titleurl,onclick,classid,titlepic from jianzhanzj_com_ecms_game Union All select title,titleurl,onclick,classid,titlepic from jianzhanzj_com_ecms_soft order by onclick desc limit 10;
     * 
     * 
     * @access public
     *
     * @param string $username
     * @return bool
     **/
    function checkMemberUnique($username)
    {
        return $this->db->getValue('select id from ' . $this->$tEcmsSoft . ' where username=\'' . $username . '\'');
    }
    
    function getstringok()
    {
        return 'the test';
    }

    /**
     * 第一横栏的十条数据
     */
    function main_yxtj111()
    {
        $sql = 'select title,titleurl,onclick,classid,titlepic from ' . $this->tEcmsGame . ' Union All select title,titleurl,onclick,classid,titlepic from ' . $this->tEcmsSoft . ' order by onclick desc limit 10';
        //   $sql = 'select title,titleurl,onclick,classid,titlepic from '. $this->tEcmsGame. ' Union All select title,titleurl,onclick,classi,titlepic from '. $this->tEcmsSoft;
        // return DB::query($sql);
        return $this->db->select($sql);
            //  return $this->db->query($sql);
        // return $this->db->open($sql);
        // return  $this->db->getValue($sql);
        // return $sql;
        //  return 'the test';
        // $this->db->open($sql);
        // $result = array();
        // while ($rs = $this->db->next()) {
        //     $result[] = $rs;
        // }
        // return $result;
    }
    
    function getEcmsGame($id)
    {
        return $this->db->getValue('select title from ' . $this->tEcmsGame . ' where id=\'' . $id . '\'');
    }

    public function incLoginErrorTimes($member_id)
    {
        $sql = 'update ' . $this->tName . ' set login_error_times = login_error_times + 1, login_time = ' . time() . ' where id = ' . $member_id;
        $this->db->execute($sql);
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

    

    

}