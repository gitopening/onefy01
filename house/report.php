<?php
require_once(dirname(__FILE__) . '/path.inc.php');
if (empty($dataInfo)) {
    header("http/1.1 404 not found");
    header("status: 404 not found");
    require_once(dirname(dirname(__FILE__)) . '/404.php');
    exit();
}
if ($member_db == 'x') {
    if ($column == 'job') {
        $house_url = $House->GetURL($dataInfo, 'x');
    } else {
        $house_url = $House->GetHouseURL($dataInfo, 'x');
    }
} elseif ($member_db > 0) {
    if ($column == 'job') {
        $house_url = $House->GetURL($dataInfo, $member_db);
    } else {
        $house_url = $House->GetHouseURL($dataInfo, $member_db);
    }
} else {
    if ($column == 'job') {
        $house_url = $House->GetURL($dataInfo);
    } else {
        $house_url = $House->GetHouseURL($dataInfo);
    }
}
if ($column == 'job') {
    if ($dataInfo['status'] == 1) {
        $page->Rdirect($house_url, '招聘信息已删除，不能举报！');
    } elseif ($dataInfo['status'] == 5) {
        $page->Rdirect($house_url, '招聘信息已失效，不能举报！');
    }
} else {
    if ($dataInfo['house_status'] == 1) {
        $page->Rdirect($house_url, '房源已删除，不能举报！');
    } elseif ($dataInfo['house_status'] == 5) {
        $page->Rdirect($house_url, '房源已成交，不能举报！');
    }
}

if ($_POST['action'] == 'save'){
    //判断验证码是否正确
    if (md5(strtolower($_POST['vcode'])) != $_COOKIE[COOKIE_PREFIX . 'validString'] && !empty($_COOKIE[COOKIE_PREFIX . 'validString'])) {
        //清空验证码
        setcookie(COOKIE_PREFIX . 'validString', '', -3600);
        //返回消息
        $page->back('验证码错误！');
    }

    $reason = intval($_POST['reporttype']);
    $content = trim(stripslashes($_POST['content']));
    $content = strip_tags($content);
    if ($dataInfo['mtype'] == 1) {
        //经纪人类型
        if (!in_array($reason, array(6, 7))) {
            $page->back('请选择投诉举报类型！');
        }
    } else {
        //个人会员类型
        if (!in_array($reason, array(1, 2, 3, 5, 6))) {
            $page->back('请选择投诉举报类型！');
        }
    }

    $content_length = mb_strlen($content, 'utf8');
    if ($content_length < 5 || $content_length > 50) {
        $page->back('情况说明为5~50个字！');
    }
    
	$ip=sprintf("%u", ip2long(getclientip()));
	$addtime=time();

	$starttime = strtotime(date('Y-m-d',$addtime));
	$endtime = $starttime + 3600*24;

    if ($member_db == 'x') {
        $Report = new Report($member_query);
    } elseif ($member_db == 4) {
        $member_db_config = GetConfig('member_db_' . $member_db);
        if (empty($member_db_config)) {
            header("http/1.1 404 not found");
            header("status: 404 not found");
            require_once(dirname(dirname(__FILE__)) . '/404.php');
            exit();
        }
        $house_query = new DbQueryForMysql($member_db_config);
        $Report = new Report($house_query);
    } else {
        $Report = new Report($query);
    }

    $condition = array(
        'house_id' => $id,
        'house_type' => $house_type,
        'ip' => $ip,
        'reason in (1,2,3,5,6,7)'
    );
    $res = $Report->table($Report->tName)->where($condition)->order('addtime desc')->one();
    if ($res) {
        $page->back('您已经举报投诉过该房源信息，我们正在处理中！');
    }

    $condition = array(
        'ip' => $ip,
        'reason in (1,2,3,5,6,7)',
        'addtime > ' . $starttime
    );
    $res = $Report->getCount($condition);
    if ($res >= $webConfig['day_report_max']) {
        $page->back('您举报投诉房源信息过多，请确认房源信息是否是虚假错误信息！');
    }

    $condition = array(
        'ip' => $ip,
        'reason in (1,2,3,5,6,7)'
    );
    $res = $Report->table($Report->tName)->where($condition)->order('addtime desc')->one();
    if (($addtime - intval($res['addtime'])) <= $webConfig['report_interval']) {
        $page->back('举报投诉间隔时间太短，请过一会儿再举报投诉！');
    }

    //判断当前房源是否已录入文本电话
    $house_info = $House->getInfo($id, 'id, mid, owner_phone, owner_phone_pic');
    $is_text_phone = empty($house_info['owner_phone']) ? 0 : 1;
    $is_owner_phone_pic = empty($house_info['owner_phone_pic']) ? 0 : 1;

    $field_array = array(
        'house_type' => $house_type,
        'house_id' => $id,
        'column_type' => $_GET['column'],
        'report_target' => 0,
        'reason' => $reason,
        'reporter' => 0,
        'status' => 0,
        'addtime' => $addtime,
        'ip' => $ip,
        'city_website_id' => $cityInfo['id'],
        'is_text_phone' => $is_text_phone,
        'is_owner_phone_pic' => $is_owner_phone_pic,
        'owner_phone' => $house_info['owner_phone'],
        'content' => addslashes($content)
    );

    $res = $Report->table($Report->tName)->save($field_array);
    if ($res) {
        //提交成功后，超过投诉次数的房源进行删除
        $condition = array(
            'house_id' => $id,
            'house_type' => $house_type,
            'reason in (1,2,3,5,6,7)'
        );
        $res = $Report->getCount($condition);
        if ($res >= $webConfig['report_max']) {
            $House->deleteHouse($id);
            $condition = array(
                'house_id' => $id,
                'house_type' => $house_type
            );
            $Report->table($Report->tName)->del($condition);
        } else {
            //计算房源真实度
            $report_count = $res + 1;
            $truth_degree = GetHouseTruthDegree($report_count);
            $data = array(
                'report_count' => array('report_count + 1'),
                'truth_degree' => $truth_degree
            );
            $House->table($House->tName)->where('id = ' . $id)->save($data);

            //更新其它投诉信息为待审核
            $condition = array(
                'house_id' => $id,
                'house_type' => $house_type
            );
            $data = array(
                'report_status' => 0
            );
            $Report->table($Report->tName)->where($condition)->save($data);
            //如果投诉为中介房源
            if ($reason == 1 && $is_text_phone == 1) {
                //检测电话是否是中介电话
                $BrokerData = new BrokerData($query);
                $phone_is_exist = $BrokerData->checkPhoneIsExist($house_info['owner_phone']);
                if ($phone_is_exist) {
                    $condition = array(
                        'house_id' => $id,
                        'house_type' => $house_type
                    );
                    $data =  array(
                        'report_status' => 4,
                        'admin_name' => '系统自动',
                        'check_time' => time()
                    );
                    $Report->table($Report->tName)->where($condition)->save($data);
                    if ($house_info['mid'] > 0) {
                        //把房源设置为中介房源，如果是会员发布，则会员转为中介会员
                        $Member = new Member($member_query);
                        $result = $Member->changeMemberType($house_info['mid'], 1);
                    } else {
                        $House->updateHouse($id, array('mtype' => 1));
                    }
                    //添加操作为系统审核为中介房源
                    $House->addCheckLog($id, 4, 0, '系统自动', '');
                }
            }
        }

        //更新房源缓存
        $Cache = Cache::getInstance();
        $Cache->delete($memcache_house_key);
        $Cache->delete($memcache_house_pic_key);

        $back_url = empty($_POST['back_url']) ? '/' : $_POST['back_url'];
        $page->Rdirect($back_url, '您的投诉已受理，我们会尽快核实该信息并进行处理！');
	}else{
		$page->back('投诉举报信息提交失败，请重试或是联系网站管理员！');
	}
	exit();
}
$page->title = '举报投诉信息-'.$page->titlec;
$page->keywords = '';
$page->description='';
//广告位
$website_right_ad = GetAdList(112, $query);
$info_bottom_ad = GetAdList(120, $query);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page->title;?></title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/jquery.cookie.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/validform_v5.3.2_min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript">
    $(function(){
        var validForm = $("#report").Validform({
            tiptype:3,
            showAllError:true
        });
    });
    function RefreshValidCode() {
        $('#valid_pic').attr('src', $('#valid_pic').attr('src') + '?t=' + new Date().getTime());
    }
</script>
</head>

<body>
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div id="main">
	<div id="left" class="rent_r">
        <div class="d_table">
                <h1>投诉举报该信息</h1>
                <ul class="r_t">
                <form name="report" id="report" method="post" action="">
                	<input type="hidden" name="action" value="save">
                	<input type="hidden" name="id" value="<?php echo $dataInfo['id'];?>">
                    <input type="hidden" name="member_db" value="<?php echo $member_db;?>">
                    <input type="hidden" name="back_url" value="<?php echo $_SERVER['HTTP_REFERER'];?>">
                    <li>
                        <span>信息编号：</span>
                        <p><?php echo $dataInfo['id'];?></p>
                        <div class="clear"></div>
                    </li>
                    <li>
                        <span>举报类型：</span>
                        <p class="radio-option">
                            <?php
                            if ($dataInfo['mtype'] == 2) {
                                ?>
                                <input name="reporttype" type="radio" value="1" datatype="*" sucmsg=" " nullmsg="请选择举报类型" errormsg="请选择举报类型">中介冒充个人 &nbsp;
                            <input name="reporttype" type="radio" value="2" datatype="*" sucmsg=" " nullmsg="请选择举报类型" errormsg="请选择举报类型">电话被冒用 &nbsp;
                            <input name="reporttype" type="radio" value="3" datatype="*" sucmsg=" " nullmsg="请选择举报类型" errormsg="请选择举报类型">房源已成交 &nbsp;
                            <input name="reporttype" type="radio" value="5" datatype="*" sucmsg=" " nullmsg="请选择举报类型" errormsg="请选择举报类型">电话号码不正确 &nbsp;
                            <?php
                            }
                            ?>
                            <input name="reporttype" type="radio" value="6" datatype="*" sucmsg=" " nullmsg="请选择举报类型" errormsg="请选择举报类型">违法信息 &nbsp;
                            <?php
                            if ($dataInfo['mtype'] == 1) {
                                echo '<input name="reporttype" type="radio" value="7" datatype="*" sucmsg=" " nullmsg="请选择举报类型" errormsg="请选择举报类型">虚假信息';
                            }
                            ?>
                        </p>
                        <div class="clear"></div>
                    </li>
                    <li>
                        <span>情况说明：</span>
                        <p>
                            <textarea name="content" id="content" datatype="*5-50" sucmsg=" " nullmsg="请您说明举报原因，字数在5~50字中间。" errormsg="请您说明举报原因，字数在5~50字中间。"></textarea>
                            <span class="Validform_checktip" style="margin-left: 0;display: block;clear:both;">请您说明举报原因，字数在5~50字中间。</span>
                        </p>
                        <div class="clear"></div>
                    </li>
                    <li>
                        <span>验证码：</span>
                        <p>
                            <input type="text" maxlength="4" id="vcode" name="vcode" datatype="s4-4" sucmsg=" " nullmsg="请输入验证码" errormsg="请输入验证码">
                            <img src="/valid.php" id="valid_pic" onclick="RefreshValidCode();">
                            <a href="javascript:;" onclick="RefreshValidCode();">看不清？</a>
                        </p>
                        <div class="clear"></div>
                    </li>
                    <li>
                        <span>&nbsp;</span>
                        <p><input type="submit" value="提交投诉" class="submit"></p>
                        <div class="clear"></div>
                    </li>
                    <div class="clear"></div>
                </form>
                </ul>
            </div>
        <?php
        if ($info_bottom_ad) {
            echo '<div class="info-bottom-ad">' . $info_bottom_ad . '</div>';
        }
        ?>
    </div>
    <div id="right">
        <div id="banner_right"><?php echo $website_right_ad;?></div>
    </div>
    <div class="clear"></div>
</div>
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
</body>
</html>
