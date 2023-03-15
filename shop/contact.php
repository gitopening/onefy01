<?php
require_once(dirname(__FILE__) . '/path.inc.php');
$page->name = 'contact'; //页面名字,和文件名相同

if (!in_array($dataInfo['user_type'], array(1, 3, 4))) {
    header("http/1.1 404 not found");
    header("status: 404 not found");
    require_once(dirname(dirname(__FILE__)) . '/404.php');
    exit();
}

$dataInfo['cityarea2_name'] = $cityarea2_option[$dataInfo['cityarea2_id']]['region_name'];
$dataInfo['add_time'] = MyDate('Y-m-d',$dataInfo['add_time']);
$page->title = '【个人介绍-'.$middle_column.$dataInfo['realname'].'电话'.$dataInfo['mobile'].'】-'.$page->titlec.$cityInfo['city_name'];
$page->description = $dataInfo['company'].$dataInfo['outlet_addr'].',工作区域'.str_replace('|', ' ', $dataInfo['servicearea']).$page->titlec.$cityInfo['city_name'];
$page->keywords = $middle_column.'、'.$dataInfo['realname'].'、电话'.$dataInfo['mobile'].',房产经纪人';
$websiteright = GetAdList(114, $query);
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
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/f.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body id="body">
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div id="main" class="shop-main">
    <div class="broker_detail">
        <?php require_once(dirname(__FILE__).'/top.php');?>
    </div>
    <div id="art_left">
        <div class="memberdetail">
        	<dl>
            	<dt>姓　　名：</dt>
                <dd><?php echo $dataInfo['realname'];?></dd>
                <div class="clear"></div>
            </dl>
        	<dl>
            	<dt>所属公司：</dt>
                <dd><?php echo $dataInfo['company'];?></dd>
                <div class="clear"></div>
            </dl>
        	<dl>
            	<dt>现任职务：</dt>
                <dd><?php echo $dataInfo['zhiwu'];?></dd>
                <div class="clear"></div>
            </dl>
        	<dl>
            	<dt>注册时间：</dt>
                <dd><?php echo $dataInfo['add_time'];?></dd>
                <div class="clear"></div>
            </dl>
        	<dl>
            	<dt>工作范围：</dt>
                <dd><?php echo $dataInfo['cityarea_name'];?> - <?php echo $dataInfo['cityarea2_name'];?></dd>
                <div class="clear"></div>
            </dl>
        	<dl>
            	<dt>电　　话：</dt>
                <dd><?php echo $dataInfo['mobile'];?> &nbsp; <?php echo $dataInfo['com_tell'];?></dd>
                <div class="clear"></div>
            </dl>
        	<dl>
            	<dt>邮　　箱：</dt>
                <dd><?php echo $dataInfo['email'];?></dd>
                <div class="clear"></div>
            </dl>
            <?php
            if ($dataInfo['qq']) {
            ?>
        	<dl>
            	<dt>QQ：</dt>
                <dd><?php echo $dataInfo['qq'];?></dd>
                <div class="clear"></div>
            </dl>
            <?php
            }
            ?>
            <?php
            if ($dataInfo['wechat']) {
            ?>
            <dl>
                <dt>微信号码：</dt>
                <dd><?php echo $dataInfo['wechat'];?></dd>
                <div class="clear"></div>
            </dl>
            <?php
            }
            ?>
        	<dl>
            	<dt>个人介绍：</dt>
                <dd><?php echo $dataInfo['introduce'];?></dd>
                <div class="clear"></div>
            </dl>
            <div class="clear"></div>
        </div>
   </div>
   <div id="right">
       <div id="banner_right"><?php echo $websiteright;?></div>
   </div>
   <div class="clear"></div>
</div>
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
</body>
</html>
