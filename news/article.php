<?php
require_once(dirname(__FILE__) . '/path.inc.php');

$id = intval($_GET['id']);
$News = new News($query);
$condition = 'id = ' . $id . ' AND is_delete = 0 AND is_checked = 1';
$memcache_key = 'news_' . $id;
$dataInfo = $News->field('n.*, e.*')->table($News->tName, 'n')->join($News->db_prefix . $News->tNameExtend . ' AS e ON n.id = e.news_id', 'LEFT')->where($condition)->cache($memcache_key, MEMCACHE_MAX_EXPIRETIME)->one();
if (empty($dataInfo)){
    header('http/1.1 404 not found');
    header('status: 404 not found');
    require_once(dirname(dirname(__FILE__)) . '/404.php');
    exit();
}
//更新访问量
$News->addClick($id);
$dataInfo['update_time'] = MyDate('Y-m-d H:i', $dataInfo['update_time']);

$pattern = '/<img.*?src=[\"\'](.+?)[\"\'].*?>/im';
preg_match_all($pattern, $dataInfo['content'], $matches);
foreach ($matches[0] as $key => $item) {
    if (empty($matches[1][$key])) {
        continue;
    }
    $img_content = '<img src="' . $matches[1][$key] . '" style="width: auto; max-width: 100%; height: auto; margin: 20px auto;" />';
    $dataInfo['content'] = str_replace($item, $img_content, $dataInfo['content']);
}

//如果是经纪人，取得经纪人昵称姓名
if ($dataInfo['website_id'] == WEBHOSTID && $dataInfo['mid']) {
    $condition = 'm.id = ' . $dataInfo['mid'] . ' AND m.account_open = 1';
    $fields = 'm.id, m.user_type, m.user_type_sub, m.user_type_custom, m.account_open, b.realname, b.city_website_id, b.avatar, b.servicearea, b.company, b.outlet, b.mobile, b.status';
    $broker_info = $member_query->table('member', 'm')->join($member_query->db_prefix . 'broker_info AS b ON m.id = b.id', 'LEFT')->field($fields)->where($condition)->cache(MEMCACHE_PREFIX . 'member_info_' . $dataInfo['mid'], MEMCACHE_EXPIRETIME)->one();
    if ($broker_info) {
        //取得经纪人店铺链接地址
        $member_city_info = get_city_info_by_id($broker_info['city_website_id']);
        if (!empty($member_city_info)) {
            $broker_info['shop_url'] = '//' . $member_city_info['url_name'] . '.' . $cfg['page']['basehost'] . '/shop/' . $dataInfo['mid'];
        } else {
            $broker_info['shop_url'] = '';
        }
    }
}

//更新Sphinx索引
//$Sphinx = Sphinx::getInstance();
//$click = $dataInfo['click'] + 1;
//$Sphinx->UpdateAttributes('search_news,search_news_delta', array('click'), array($id => array($click)));

$page->title = '【'.$dataInfo['title'].$cityInfo['city_name'].'房产新闻】'.$page->titlec.$cityInfo['city_name'];
$page->description = $dataInfo['title'].$cityInfo['city_name'].'房产新闻'.$cityInfo['city_name'].'房产资讯'.$cityInfo['city_name'].'二手房资讯';
$page->keywords = ''.$cityInfo['city_name'].'房产新闻,'.$cityInfo['city_name'].'房产资讯,'.$cityInfo['city_name'].'二手房资讯';
//广告位
$website_right_ad = GetAdList(116, $query);
$website_left_detail = GetAdList(117, $query);
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
<script type="text/javascript" src="/js/jquery.cookie.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/layer/layer.js?v=<?php echo $webConfig['static_version'];?>"></script>
<script type="text/javascript" src="/js/function.js?v=<?php echo $webConfig['static_version'];?>"></script>
</head>

<body>
<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
<div id="main" class="news-main">
    <div id="art_left">
        <div class="art_c">
            <div class="position art_position">您当前的位置：<a href="/"><?php echo $page->titlec;?></a> &gt; <a href="/news/">房产资讯</a> &gt; <?php echo $dataInfo['title'];?></div>
            <h1><?php echo $dataInfo['title'];?></h1>
            <div class="art_ly">
                <p><?php
                    if ($broker_info) {
                        if ($broker_info['user_type'] == 2) {
                            echo '<span>发布人：<a href="' . $broker_info['shop_url']. '" target="_blank">' . $broker_info['realname'] . '</a></span>';
                        } else {
                            echo '<span>发布人：<a href="' . $broker_info['shop_url']. '" target="_blank">' . $broker_info['realname'] . '</a> - ' . $broker_info['company'] . '</span>';
                        }
                    } else if ($dataInfo['writer']) {
                        echo '<span>发布人：' . $dataInfo['writer'] . '</span>';
                    }
                    if ($dataInfo['source']) {
                        echo '<span>来源：' . $dataInfo['source'] . '</span>';
                    }
                    ?><span>发布时间：<?php echo $dataInfo['update_time'];?></span></p>
            </div>
            <div class="art_body">
                <div id="content_ad"><?php echo $website_left_detail;?></div>
                <?php
                if ($info_bottom_ad) {
                    echo '<div class="ad-pos-wrap"></div><div class="article-middle-ad">' . $info_bottom_ad . '</div>';
                }
                ?>
                <?php echo trip_indent($dataInfo['content']);?>
                <div class="notice-content">声明：本文由本站作者上传并发布，本站仅提供信息发布平台。文章仅代表作者个人观点，不代表本站立场。本站仅提供信息存储空间服务。</div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <div id="right">
        <div class="art_rb"><?php echo $website_right_ad;?></div>
    </div>
    <div class="clear"></div>
</div>
<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
</body>
</html>
