<?php
//  print "<script language=\"JavaScript\">
//  alert(\"运行结束\");</script>";
//  die;
require_once(dirname(__FILE__) . '/path.inc.php');
require_once($cfg['path']['lib'] . 'classes/Pages.class.php');
$now_time = time();

//  print "<script language=\"JavaScript\">
//  alert(\"运行结束10\");</script>";
//  die;
$member_query = new DbQueryForMysql(GetConfig('member_db'));
$member = new Member($member_query);
$member_id = $member->getAuthInfo('id');

// print "<script language=\"JavaScript\">
// alert(\"运行结束15\");</script>";
        if (empty($member_query)) {
            header("http/1.1 404 not found");
            header("status: 404 not found");
            require_once(dirname(dirname(__FILE__)) . '/404.php');
            exit();
        }
        print_r($member_id.'<br><br><br>');

          print_r("----96----".$member_id);
        
        print "<script language=\"JavaScript\">
                    alert(\"运行结束27\");</script>";
                    die;