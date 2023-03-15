<?php
require_once(dirname(__FILE__) . '/path.inc.php');
require_once($cfg['path']['lib'] . 'classes/Pages.class.php');
$page->title = '【房产资讯|房产新闻】'.$page->titlec;
$page->description = '提供最新最全的二手房动态、二手房价格变动等二手房资讯，二手房交易合同下载、二手房交易法律规定等,让您了解二手房交易流程、二手房交易税费,二手房过户等。';
$page->keywords = '房产资讯,二手房交易流程,二手房过户,二手房交易税,二手房交易税费';

$param = explode('_', trim($_GET['param']));
$pageno = intval($param[0]) <= 0 ? 1 : intval($param[0]);
$keywords = trim($param[1]);

//$Sphinx = Sphinx::getInstance();
//$Sphinx->SetFilter('city_website_id', array($cityInfo['id']));
//$Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'add_time');
$list_num = 60;
//缓存Key
$list_key = MEMCACHE_PREFIX . 'nl_' . $list_num . '_' . $pageno . '_' . $keywords;
$count_key = MEMCACHE_PREFIX . 'nc_' . $list_num . '_' . $keywords;
//从Memcache中取数据
$dataCount = $Cache->get($count_key);
$dataList = $Cache->get($list_key);

if (empty($dataList) || empty($dataCount)) {
    //$max_data_limit = MAX_DATA_LIMIT;
    $max_data_limit = 480;
    $cur_page = $pageno;
    $max_page = ceil($max_data_limit / $list_num);
    if ($cur_page <= 0) {
        $cur_page = 1;
    }
    if ($cur_page >= $max_page) {
        $cur_page = $max_page;
    }
    $row_from = intval(($cur_page - 1) * $list_num);
    $Sphinx = Sphinx::getInstance();
    $Sphinx->SetFilter('is_checked', array(1));
    $Sphinx->SetFilter('is_delete', array(0));
    $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'add_time');
    $Sphinx->SetLimits($row_from, $list_num, $max_data_limit);
    $result = $Sphinx->Query($keywords, 'search_news,search_news_delta');
    $pages = new Pages($result['total'], $list_num);
    $dataList = array();
    if ($result['total'] > 0) {
        $News = new News($query);
        foreach ($result['matches'] as $item) {
            $dataList[] = array(
                'url' => $News->getUrl($item['attrs']['filter_id']),
                'title' => $item['attrs']['title'],
                'thumb_url' => $item['attrs']['thumb_url'],
                'description' => $item['attrs']['description'],
                'add_time' => MyDate('Y-m-d H:i', $item['attrs']['add_time'])
            );
        }
    }
    if ($dataList && $result['total']) {
        $Cache->set($list_key, $dataList, MEMCACHE_EXPIRETIME);
        $Cache->set($count_key, $result['total'], MEMCACHE_EXPIRETIME);
    }
} else {
    $pages = new Pages($dataCount, $list_num);
}

//分页链接
$url = 'list_{pageno}_' . $keywords . '.html';
$pagePanel = $pages->get_pager_nav('4', $pageno, $url);
//新闻排行榜
//缓存Key
/*
$hot_list_key = 'hot_news_list';
$hot_news = $Cache->get($hot_list_key);
if (empty($hot_news)) {
    $Sphinx->SetLimits(0, 10, 10);
    $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'click');
    $Sphinx->SetFilterRange('add_time', (time() - 604800), time());
    $result = $Sphinx->Query('', 'search_news, search_news_delta');
    $result['total'] = intval($result['total']);
    if ($result['total'] > 0){
        $news_ids = array();
        foreach ($result['matches'] as $item) {
            $news_ids[] = $item['id'];
        }
        if (!empty($news_ids)) {
            $news_ids_str = implode(',', $news_ids);
            $sql = "select id, title from fke_news where id in ($news_ids_str)";
            $hot_news = $query->select($sql);
            $news_list = array();
            foreach ($hot_news as $val) {
                $news_list[$val['id']] = $val;
            }
        }

        $hot_news = array();
        foreach ($result['matches'] as $item) {
            if ($news_list[$item['id']]) {
                $hot_news[] = $news_list[$item['id']];
            }
        }
        //存储数据到Memcache
        if ($hot_news) {
            $result = $Cache->set($hot_list_key, $hot_news, MEMCACHE_COMPRESS, MEMCACHE_EXPIRETIME);
        }
    }
}
*/

//广告位
$websiteright = GetAdList(115, $query);
$website_header_ad = GetAdList(185, $query);
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
<script type="text/javascript">
    $(document).ready(function () {
        $('#search-btn').click(function () {
            SearchNews();
        });
        $('#news-keywords').keyup(function (e) {
            var keycode = e.keyCode || e.which;
            if (keycode == 13) {
                SearchNews();
            }
        });
    });
</script>
</head>

<body>
	<?php require_once(dirname(dirname(__FILE__)).'/header.php');?>
    <div id="main" class="list-box">
        <div id="art_left">
            <div class="art_c">
                <div class="position art_position">您当前的位置：<a href="/"><?php echo $page->titlec;?></a> &gt; <a href="/news/">房产资讯</a></div>
                <ul class="art_list">
                    <?php
                    foreach ($dataList as $key => $item) {
                        ?>
                        <li>
                            <h4><a target="_blank"
                                   href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a></h4>

                            <div class="time2"><?php echo $item['add_time']; ?></div>
                        </li>
                        <?php
                        if (($key + 1) % 6 == 0 && $key != ($list_num - 1)) {
                            echo '<li></li>';
                        }
                        ?>
                        <?php
                    }
                    ?>
                </ul>
                <div class="pager">
                    <ul>
                        <?php echo $pagePanel; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div id="right">
            <div class="search-box">
                <div class="search-title">搜索新闻</div>
                <div class="key-text">
                    <input type="text" name="keywords" id="news-keywords" value="<?php echo $keywords;?>" />
                </div>
                <div id="search-btn">搜索</div>
                <div class="clear"></div>
            </div>
			<?php
			if ($hot_news) {
			?>
            <div class="hot-box">
                <div class="box-title">新闻排行榜</div>
                <div class="hot-list">
                    <ul>
                        <?php
                        foreach ($hot_news as $key => $item) {
                            echo '<li><span>' . ($key + 1) . '</span><a href="article_' . $item['id'] . '.html" target="_blank">' . $item['title'] . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
			<?php
			}
			?>
            <div class="art_rb"><?php echo $websiteright;?></div>
        </div>
        <div class="clear"></div>
    </div>
	<?php require_once(dirname(dirname(__FILE__)).'/footer.php');?>
</body>
</html>

