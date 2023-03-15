<?php
require_once(dirname(__FILE__) . '/path.inc.php');

if (empty($dataInfo) || ($column != 'job' && $dataInfo['house_status'] == 1) || ($column == 'job' && $dataInfo['status'] == 1)) {
    if ($column == 'job') {
        $page->title = '招聘信息已删除-' . $page->titlec;
    } else {
        $page->title = '房源已删除-' . $page->titlec;
    }
} elseif (($column != 'job' && $dataInfo['house_status'] == 5) || ($column == 'job' && $dataInfo['status'] == 5)) {
    if ($column == 'job') {
        $page->title = '招聘信息已失效-' . $page->titlec;
    } else {
        $page->title = '房源已成交-' . $page->titlec;
    }
} else {
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
	header('Location:' . $house_url);
	exit();
}

$page->keywords = '';
$page->description='';
//广告位
$website_right_ad = GetAdList(112, $query);
$website_center_ad = GetAdList(178, $query);
$info_bottom_ad = GetAdList(120, $query);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page->title;?></title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link href="/css/password.css?v=<?php echo $webConfig['static_version'];?>" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/jquery.cookie.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/validform_v5.3.2_min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script src="/js/function.js?v=<?php echo $webConfig['static_version'];?>" type="text/javascript"></script>
</head>

<body>
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div id="main">
	<div id="left" class="rent_r">
	    <div class="d_table delete-tip">
			<?php
			if (empty($dataInfo) || ($column != 'job' && $dataInfo['house_status'] == 1) || ($column == 'job' && $dataInfo['status'] == 1)) {
				?>
				<div class="delete-success"><?php echo $column == 'job' ? '招聘信息已删除！' : '房源已删除！';?></div>
				<div class="close-button submit_btn" onclick="window.close();">关闭本页</div>
				<?php
			} elseif (($column != 'job' && $dataInfo['house_status'] == 5) || ($column == 'job' && $dataInfo['status'] == 5)) {
				?>
				<div class="delete-success"><?php echo $column == 'job' ? '招聘信息已失效！' : '房源已成交！';?></div>
				<div class="close-button submit_btn" onclick="window.location.href='<?php echo $house_url;?>';">查看房源</div>
				<?php
			}
			?>
	    </div>
		<?php
		if ($website_center_ad) {
			echo '<div class="done-bottom-ad">' . $website_center_ad . '</div>';
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
