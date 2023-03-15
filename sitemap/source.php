<?php
/**
 * Created by Net.
 * User: Net
 * Date: 2015/12/24
 * Time: 10:37
 */
require_once(dirname(dirname(__FILE__)) . '/common.inc.php');
set_time_limit(0);

$start_time = time();

//热门城市
$condition = array(
    'is_hot' => 1,
    'is_open' => 1
);
$city_list = $query->field('id, city_name, url_name, city_id')->table('city_website')->where($condition)->order('is_order asc')->all();

$last_modify_time = MyDate('Y-m-d', time()) . 'T' . MyDate('H:i:s', time()) . '+00:00';

//PC站首页
$file_path = dirname(dirname(__FILE__)) . '/tmp/sitemap/www_sitemap.xml';
$fp = fopen($file_path, 'w');
fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>');
fwrite($fp, '<urlset>');
//分站首页
$url_item = '<url><loc>http://www.' . $cfg['page']['basehost'] . '/</loc><priority>1.0</priority> <lastmod>' . $last_modify_time . '</lastmod><changefreq>Daily</changefreq></url>';
fwrite($fp, $url_item);

//移动站
$url_item = '<url><loc>http://m.' . $cfg['page']['basehost'] . '/</loc><priority>1.0</priority> <lastmod>' . $last_modify_time . '</lastmod><changefreq>Daily</changefreq></url>';
fwrite($fp, $url_item);

//城市分站
foreach ($city_list as $city) {
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/</loc><priority>0.8</priority> <lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
}

fwrite($fp, '</urlset>');
fclose($fp);

//PC城市分站
foreach ($city_list as $city) {
    $file_path = dirname(dirname(__FILE__)) . '/tmp/sitemap/' . $city['url_name'] . '_sitemap.xml';
    $fp = fopen($file_path, 'w');
    fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>');
    fwrite($fp, '<urlset>');
    //分站首页
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/</loc><priority>1.0</priority> <lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //一级地区列表
    $region_list = $query->field('region_id')->table('region')->where('parent_id in (' . $city['city_id'] . ')')->order('sort_order asc, region_id asc')->cache(true)->all();

    //出租列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/rent/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/rent/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //二手房列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/sale/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/sale/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //写字楼出租列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/xzlcz/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/xzlcz/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //写字楼出售列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/xzlcs/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/xzlcs/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //商铺出租列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/spcz/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/spcz/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //商铺出售列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/spcs/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/spcs/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //住宅求租列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/qiuzu/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/qiuzu/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //住宅求购列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/qiugou/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/qiugou/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //写字楼求租列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/xzlqz/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //写字楼求购列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/xzlqg/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //商铺求租列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/spqz/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //商铺求购列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/spqg/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //出租详细页
    $house_list = $query->field('id, column_type')->table('houserent')->where('city_website_id = ' . $city['id'])->order('id desc')->limit(5000)->all();
    foreach ($house_list as $house) {
        switch ($house['column_type']) {
            case 2:
                $column_type = '/xzlcz/';
                break;
            case 3:
                $column_type = '/spcz/';
                break;
            case 1:
            default:
                $column_type = '/rent/';
        }
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . $column_type . 'house_' . $house['id'] . '.html</loc><priority>0.6</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //出售详细页
    $house_list = $query->field('id, column_type')->table('housesell')->where('city_website_id = ' . $city['id'])->order('id desc')->limit(5000)->all();
    foreach ($house_list as $house) {
        switch ($house['column_type']) {
            case 2:
                $column_type = '/xzlcs/';
                break;
            case 3:
                $column_type = '/spcs/';
                break;
            case 1:
            default:
                $column_type = '/sale/';
        }
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . $column_type . 'house_' . $house['id'] . '.html</loc><priority>0.6</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //求租详细页
    $house_list = $query->field('id, column_type')->table('qiuzu')->where('city_website_id = ' . $city['id'])->order('id desc')->limit(5000)->all();
    foreach ($house_list as $house) {
        switch ($house['column_type']) {
            case 2:
                $column_type = '/xzlqz/';
                break;
            case 3:
                $column_type = '/spqz/';
                break;
            case 1:
            default:
                $column_type = '/qiuzu/';
        }
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . $column_type . 'house_' . $house['id'] . '.html</loc><priority>0.6</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //求购详细页
    $house_list = $query->field('id, column_type')->table('qiugou')->where('city_website_id = ' . $city['id'])->order('id desc')->limit(5000)->all();
    foreach ($house_list as $house) {
        switch ($house['column_type']) {
            case 2:
                $column_type = '/xzlqg/';
                break;
            case 3:
                $column_type = '/spqg/';
                break;
            case 1:
            default:
                $column_type = '/qiugou/';
        }
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . $column_type . 'house_' . $house['id'] . '.html</loc><priority>0.6</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //经纪人列表
    $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/broker/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    foreach ($region_list as $region) {
        $url_item = '<url><loc>http://' . $city['url_name'] . '.' . $cfg['page']['basehost'] . '/broker/list_' . $region['region_id'] . '_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //新闻列表和新闻详细
    $url_item = '<url><loc>http://www.' . $cfg['page']['basehost'] . '/news/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    $news_list = $query->field('id')->table('news')->order('id desc')->limit(1000)->all();
    foreach ($news_list as $news) {
        $url_item = '<url><loc>http://www.' . $cfg['page']['basehost'] . '/news/article_' . $news['id'] . '.html</loc><priority>0.6</priority><lastmod>' . $time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    fwrite($fp, '</urlset>');
    fclose($fp);
}

//手机移动站
/*$condition = array(
    'is_open' => 1
);
$city_list = $query->field('id, city_name, url_name, city_id')->table('city_website')->where($condition)->order('is_order asc')->all();*/
$file_path = dirname(dirname(__FILE__)) . '/tmp/sitemap/m_sitemap.xml';
$fp = fopen($file_path, 'w');
fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>');
fwrite($fp, '<urlset>');
$url_item = '<url><loc>http://m.' . $cfg['page']['basehost'] . '/</loc><priority>1.0</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
fwrite($fp, $url_item);
foreach ($city_list as $city) {
    $city_url = 'http://m.' . $cfg['page']['basehost'] . '/' . $city['url_name'] . '/';
    //城市首页
    $url_item = '<url><loc>' . $city_url . '</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //一级地区列表
    $region_list = $query->field('region_id')->table('region')->where('parent_id in (' . $city['city_id'] . ')')->order('sort_order asc, region_id asc')->cache(true)->all();

    //分站出租
    $url_item = '<url><loc>' . $city_url . 'rent/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>' . $city_url . 'rent/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //分站出售
    $url_item = '<url><loc>' . $city_url . 'sale/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>' . $city_url . 'sale/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //分站写字楼出租
    $url_item = '<url><loc>' . $city_url . 'xzlcz/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>' . $city_url . 'xzlcz/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //分站写字楼出售
    $url_item = '<url><loc>' . $city_url . 'xzlcs/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>' . $city_url . 'xzlcs/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //分站商铺出租
    $url_item = '<url><loc>' . $city_url . 'spcz/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>' . $city_url . 'spcz/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //分站商铺出售
    $url_item = '<url><loc>' . $city_url . 'spzs/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>' . $city_url . 'spcs/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //住宅求租
    $url_item = '<url><loc>' . $city_url . 'qiuzu/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>' . $city_url . 'qiuzu/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //住宅求购
    $url_item = '<url><loc>' . $city_url . 'qiugou/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    foreach ($region_list as $region) {
        $url_item = '<url><loc>' . $city_url . 'qiugou/list_0_' . $region['region_id'] . '_0_0-0_0_0-0_0_2_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //分站写字楼求租
    $url_item = '<url><loc>' . $city_url . 'xzlqz/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //分站写字楼求购
    $url_item = '<url><loc>' . $city_url . 'xzlqg/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //分站商铺求租
    $url_item = '<url><loc>' . $city_url . 'spqz/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //分站商铺求购
    $url_item = '<url><loc>' . $city_url . 'spqg/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);

    //分站经纪人列表
    $url_item = '<url><loc>' . $city_url . 'broker/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    foreach ($region_list as $region) {
        $url_item = '<url><loc>' . $city_url . 'broker/list_' . $region['region_id'] . '_0_1_.html</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }

    //分站新闻列表
    $url_item = '<url><loc>' . $city_url . 'news/</loc><priority>0.8</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
    fwrite($fp, $url_item);
    $news_list = $query->field('id')->table('news')->order('id desc')->limit(1000)->all();
    foreach ($news_list as $news) {
        $url_item = '<url><loc>' . $city_url . 'news/article_' . $news['id'] . '.html</loc><priority>0.6</priority><lastmod>' . $last_modify_time . '</lastmod><changefreq>Always</changefreq></url>';
        fwrite($fp, $url_item);
    }
}
fwrite($fp, '</urlset>');
fclose($fp);

echo time() - $start_time;