<?php
require_once(dirname(__FILE__).'/common.inc.php');
if ($url_name == 'm') {
    require_once(dirname(__FILE__) . '/mobile/about.php');
    exit();
}
$id = intval($_GET['id']);
$about = $query->table('about')->where('id = ' . $id)->cache(true)->one();
$page->title = $about['title'] . '-' . $cfg['page']['title'];   //网站名称
if (empty($about)){
    @header("http/1.1 404 not found");
    @header("status: 404 not found");
    $page->Rdirect('/','访问的记录不存在或已被删除！');
    exit();
}
//关于我们列表
//$dataList = $query->table('about')->field('id, title')->where('website_id = ' . WEBHOSTID)->order('id asc')->cache(true)->all();
//广告位
//$websiteright = GetAdList(26, $query);
$website_right_ad = GetAdList(112, $query);
//$info_bottom_ad = GetAdList(120, $query);
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
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body>
<?php require_once(dirname(__FILE__).'/header.php');?>
<div id="main">
    <div class="single-page">
        <div id="content">
            <h1 style="text-align:center;"><?php echo $about['title'];?></h1>
            <div class="clear"></div>
            <div id="body2">
                <div><?php echo $about['content'];?></div>
            </div>
            <?php
            if ($info_bottom_ad) {
                echo '<div class="info-bottom-ad">' . $info_bottom_ad . '</div>';
            }
            ?>
        </div>
    </div>
</div>
<?php require_once(dirname(__FILE__).'/footer.php');?>
</body>
</html>
