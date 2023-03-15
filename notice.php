<?php
require_once(dirname(__FILE__) . '/common.inc.php');
$message = json_decode(base64_decode($_GET['m']), true);
if (empty($message)) {
    $message = array(
        'type' => 1,
        'title' => '错误',
        'content' => '请求参数错误'
    );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page->title;?> - 操作提示</title>
<meta name="description" content="<?php echo $page->description;?>" />
<meta name="keywords" content="<?php echo $page->keywords;?>" />
<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?php echo $webConfig['static_version'];?>" />
<link rel="stylesheet" type="text/css" href="/themes/2010a/css/member.css?v=<!--{$static_version}-->">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body>
<?php require_once(dirname(__FILE__).'/header.php');?>
<div class="main">
	<div class="notice-box clearfix">
        <div class="icon <?php echo $message['type'] == 0 ? 'success-icon' : 'error-icon';?>"></div>
        <div class="content">
            <h3><?php echo $message['title'];?></h3>
            <div class="tips"><?php echo $message['content'];?></div>
        </div>
    </div>
</div>
<?php require_once(dirname(__FILE__).'/footer.php');?>
</body>
</html>