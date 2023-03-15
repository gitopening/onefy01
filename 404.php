<?php
$currentColumn = 'index';
require_once(dirname(__FILE__).'/common.inc.php');
$page->title = '所访问的页面已删除-'.$page->titlec.$cityInfo['city_name'];

//广告位
$website_right_ad = GetAdList(221, $query);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $page->title;?></title>
    <link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
    <script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
    <script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body>
<?php require_once(dirname(__FILE__).'/header.php');?>
<div id="main" class="tips-content">
    <div class="tips">
        <div class="title">您所访问的页面已删除！</div>
        <div class="content">强烈推荐您去：<a href="/"><?php echo $cfg['page']['titlec'];?></a>查看更多房源信息</div>
    </div>
    <div class="right-ad"><?php echo $website_right_ad;?></div>
    <div class="clear"></div>
</div>
<?php require_once(dirname(__FILE__).'/footer.php');?>
</body>
</html>
