<?php
ini_set('magic_quotes_runtime', 0);
$cfg_cli_time = 8;
if (PHP_VERSION > '5.1') {
    $time51 = $cfg_cli_time * -1;
    @date_default_timezone_set('Etc/GMT' . $time51);
}
require_once(dirname(__FILE__) . '/config.cfg.php');
if (!$cfg['debug']) {
    error_reporting(0);
    //ob_start('ob_gzhandler');
} else {
    //error_reporting(E_ALL ^ E_NOTICE);
    error_reporting(E_ERROR);
}
require_once($cfg['path']['common'] . '/common.func.php');

GetConfig($web_config);

if (strpos($_SERVER['SCRIPT_NAME'], 'rent') !== false || strpos($_SERVER['SCRIPT_NAME'], 'sale') !== false || strpos($_SERVER['SCRIPT_NAME'], 'qiuzu') !== false || strpos($_SERVER['SCRIPT_NAME'], 'qiugou') !== false || strpos($_SERVER['SCRIPT_NAME'], 'broker') !== false || strpos($_SERVER['SCRIPT_NAME'], 'shop') !== false || strpos($_SERVER['SCRIPT_NAME'], 'news') !== false || strpos($_SERVER['SCRIPT_NAME'], 'new') !== false) {
    $need_login_page = true;
}

header('Content-Type: text/html; Charset=' . $cfg['charset']);
//判断是否是合法域名访问
if (strpos($_SERVER['HTTP_HOST'], $cfg['domain']) === false) {
    header("HTTP/1.0 400 Bad Request");
    @header("status: 400 Bad Request");
    exit();
}

require($cfg['path']['lib'] . 'classes/Util.inc.php');
//$cfg['path']['doc'] = getRootPath();
// sql server 的程序magic_quotes_gpc必须为off
$magic_quotes_gpc = get_magic_quotes_gpc();
$_COOKIE = c_addslashes($_COOKIE);
$_POST = c_addslashes($_POST);
$_GET = c_addslashes($_GET);

/*
 * 数据库连接
*/
require_once($cfg['path']['lib'] . 'classes/db/DbQueryForMysql.class.php');
require_once($cfg['path']['lib'] . 'classes/db/exception/DbException.class.php');

//数据库连接
$query = new DbQueryForMysql(GetConfig('db'));
//创建会员数据库连接
$member_query = new DbQueryForMysql(GetConfig('member_db'));

//缓存功能
$cache_config = GetConfig('cache');
$Cache = Cache::getInstance($cache_config);

/*
 * 模版引擎
*/
require_once($cfg['path']['lib'] . 'smarty/libs/Smarty.class.php');
$tpl = new Smarty();
$tpl->template_dir = $cfg['path']['template'];
$tpl->compile_dir = $cfg['path']['root'] . 'tmp/template_c/';

$tpl->compile_check = $cfg['debug'];
$tpl->debugging = false;
$tpl->caching = 0;
$tpl->cache_lifetime = 6000;

$tpl->left_delimiter = '<!--{';
$tpl->right_delimiter = '}-->';
$tpl->force_compile = false;

/*
 * 引用赋值
*/
$tpl->assign_by_ref('cfg', $cfg);
require_once($cfg['path']['lib'] . 'classes/Page.class.php');
$page = new Page();
$page->tpl = $tpl;
$page->action = $_REQUEST['action'];
$page->version = 'HCKJ 2.0';
/*if ($_GET['request']=='ajax') {
    $_POST = charsetIconv($_POST);
}*/

/**
 * todo 自动载入apps,classes
 * @param string $name
 * @return bool
 */
function __autoload($name)
{
    global $cfg;
    if (!file_exists($cfg['path']['apps'] . $name . '.class.php')) {
        if (file_exists($cfg['path']['lib'] . 'classes/' . $name . '.class.php')) {
            require_once($cfg['path']['lib'] . 'classes/' . $name . '.class.php');
            return true;
        } else {
            return false;
        }
    }
    require_once($cfg['path']['apps'] . $name . '.class.php');
    return true;
}

$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
$page->tpl->assign('http_type', $http_type);

//用户登录信息
$member = new Member($member_query);
$member_id = $member->getAuthInfo('id');
/*if (!$member_id && $not_need_login_page != true) {
    //跳到登录注册页面
    //$page->Rdirect('/member/login.php', '请先登录后再查看');
    header('Location:/member/login.php');
    exit();
}*/
if ($member_id) {
    $need_login_page = false;
    $memberInfo = $member->getInfo($member_id, '*', true);
    if (!in_array($page_name, array('auth', 'suggest', 'ajax'))) {
        if ($memberInfo['disabled'] == 1 || $memberInfo['account_open'] === 0) {
            //跳到验证页面
            header('Location:/auth.php');
            exit();
        }
    }

    $page->tpl->assign('member_id', $member_id);
    //取得会员信息
    $realname = empty($memberInfo['realname']) ? $memberInfo['username'] : $memberInfo['realname'];
    $page->tpl->assign('username', $realname);

    if ($memberInfo['status'] == 0) {
        $memberInfo['avatar'] = '';
    } else {
        $memberInfo['avatar'] = GetBrokerFace($memberInfo['avatar']);
    }
    $memberInfo['first_name'] = mb_substr($memberInfo['realname'], 0, 1, 'utf-8');
    //会员用户名处理成中间四位星号的显示方式
    if (is_mobile($memberInfo['username'])) {
        $header_username = substr($memberInfo['username'], 0, 3) . '****' . substr($memberInfo['username'], 7);
    } else {
        $header_username = $memberInfo['username'];
    }
    $page->tpl->assign('memberInfo', $memberInfo);
    $page->tpl->assign('header_username', $header_username);
    $user_type = $memberInfo['user_type'];
    $page->tpl->assign('user_type', $user_type);
    //更新用户登录时间
    if ($member_id  && ($memberInfo['last_login'] + MEMBER_LOGIN_UPDATE_TIME) < $cfg['time']) {
        $data = array(
            'last_login' => $cfg['time']
        );
        $member_query->table('member')->where('id = ' . $member_id)->save($data);
        //更新Sphinx索引
        $Sphinx = Sphinx::getInstance();
        $Sphinx->UpdateAttributes(SPHINX_SEARCH_MEMBER_INDEX, array('last_login'), array($member_id => array($cfg['time'])));
    }
} elseif (strpos($_SERVER['SCRIPT_NAME'], 'api') === false && strpos($_SERVER['SCRIPT_NAME'], 'ajax') === false && strpos($_SERVER['SCRIPT_NAME'], 'auth') === false && strpos($_SERVER['SCRIPT_NAME'], 'suggest') === false) {

}

$webConfig = $query->table('web_config')->where('id = ' . WEBHOSTID)->cache(true)->one();

//投诉举报及过期房源时间以真房源网为准
$webInfo = $query->table('web_config')->field('id, filter_keywords, report_max, report_interval, day_report_max, expiration_time, nextstarttime')->where('id = 1')->cache(true)->one();
$webConfig['report_max'] = $webInfo['report_max'];
$webConfig['report_interval'] = $webInfo['report_interval'];
$webConfig['day_report_max'] = $webInfo['day_report_max'];
$webConfig['expiration_time'] = $webInfo['expiration_time'];
$webConfig['nextstarttime'] = $webInfo['nextstarttime'];
$webConfig['filter_keywords'] = $webInfo['filter_keywords'];
unset($webInfo);

$page->title = $webConfig['base_title'];   //网站名称
$page->basehost = $webConfig['basehost'];    //当前站点域名
$page->sinaapp = $webConfig['sinaapp'];   //新浪微博关注ID
$page->titlec = $webConfig['base_titlec'];   //网站名称  一定要与上面的名称一直
$page->city = $webConfig['base_city'];   //所在的城市，也就是说网站经营范围
$page->newsOpen = $webConfig['newsOpen'];   //是否开启新闻频道  1：开启  2：不开启
$page->bbsOpen = $webConfig['bbsOpen'];   //是否开启论坛频道  1：开启  2：不开启
$page->mapcity = $webConfig['base_mapcity'];   //地图归属地区 （例如：哈尔滨市 一定要加“市”）
$page->citySwitch = $webConfig['base_city_switch'];   //是否开启城市切换标签  1：是  2：不是
$page->shop = $webConfig['base_is_shop'];   //经纪人是否需要实名认证才开通网店  1：是  2：不是
$page->guest = $webConfig['base_is_guest'];   //是否开通游客发布功能  1：是  2：不是
$page->memberOpen = $webConfig['base_member_open'];   //是否开通免费经纪人注册  1：是  2：不是
$page->googlekey = $webConfig['base_googlekey'];   //google地图KEY
$page->tongji = $webConfig['base_tongji'];  //统计代码
$page->beian = $webConfig['base_beian'];  //网站备案号
$page->email = $webConfig['email'];    //网站邮箱
$page->contact_rexian = $webConfig['contact_rexian']; //客服热线
$page->contact_dianhua = $webConfig['contact_dianhua']; //网站电话
$page->uc = $webConfig['base_uc'];  //是否开启uc     1：是    2：不是
$page->lat = $webConfig['lat'];
$page->lnt = $webConfig['lnt'];
$page->gongsi = $webConfig['contact_gongsi'];  //公司名称
$page->dizhi = $webConfig['contact_dizhi'];  //公司地址
$page->youbian = $webConfig['contact_youbian'];  //邮编
$page->chuanzhen = $webConfig['contact_chuanzhen'];  //传真
$page->phone = $webConfig['contact_dianhua'];  //联系电话
$page->rexian = $webConfig['contact_rexian'];  //客服热线
$page->qq = $webConfig['contact_qq'];   //QQ号码

$page->tpl->assign('static_version', $webConfig['static_version']);

//todo 已开通城市列表
/*$condition = array(
    'is_open' => 1,
    'is_hot' => 1
);
$cityList = $query->field('id, city_name, url_name, first_letter, is_hot, is_recommend')->table('city_website')->where($condition)->order('is_order asc')->cache(true)->all();
$page->tpl->assign('citylist', $cityList);*/

//过滤关键字
$filterWords = explode('|', $webConfig['filter_keywords']);
//todo 可以在伪静态中配置直接跳转
if ($cfg['page']['basehost'] == $_SERVER['HTTP_HOST']) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location://www.' . $cfg['page']['basehost']);
    exit();
}

//上次访问的站点ID
$cityID = isset($_COOKIE[COOKIE_PREFIX . 'cityid']) ? intval($_COOKIE[COOKIE_PREFIX . 'cityid']) : 0;
$data_info = $query->table('city_website')->field('id, city_name, url_name')->where('id = ' . $cityID)->cache(true)->one();
$jump_url_name = $data_info['url_name'];
$jump_city_name = $data_info['city_name'];

//取得当前网站的ID
$url_name = str_replace('.' . $cfg['page']['basehost'], '', $_SERVER['HTTP_HOST']);
$city_name = $url_name == 'm' ? trim($_GET['city_name']) : '';

//判断是否手机端访问，跳转到手机主页
$client = trim($_GET['client']);
$domain = explode(':', $cfg['page']['basehost']);
if ($client == 'pc' && $url_name != 'm') {
    setcookie(COOKIE_PREFIX . '_client', 'pc', 0, '/', $domain[0]);
} else {
    $client = trim($_COOKIE[COOKIE_PREFIX . '_client']);
    if (IsMobileRequest()) {
        //如果没有先前访问城市记录同时定位城市成功，则跳到定位的城市
        if (empty($jump_url_name) && (($url_name == 'm' && empty($city_name)) || $url_name == 'www')) {
            //取得当前所在城市信息
            /*$url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip='.getclientip();
            $positionInfo = json_decode(file_get_contents($url), true);
            if ($positionInfo['city']) {
                $position_city = $query->table('city_website')->field('id, city_name, url_name')->where("city_name like '{$positionInfo['city']}%' and is_open = 1")->cache(true)->one();
                if ($position_city) {
                    setcookie(COOKIE_PREFIX . 'cityid', $position_city['id'], (time() + 31536000), '/', $domain[0]);
                    header('Location://m.' . $cfg['page']['basehost'] . '/' . $position_city['url_name'] . $_SERVER['REQUEST_URI']);
                    exit();
                }
            }*/
            if ($jump_url_name) {
                header('Location://m.' . $cfg['page']['basehost'] . '/' . $jump_url_name . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                header('Location://m.' . $cfg['page']['basehost'] . $_SERVER['REQUEST_URI']);
                exit();
            }
        } elseif ($url_name != 'm' && $client != 'pc') {
            setcookie(COOKIE_PREFIX . '_client', '', -1, '/', $domain[0]);
            if (!empty($url_name) && $url_name != 'www') {
                $url = '/' . $url_name . $_SERVER['REQUEST_URI'];
                /* } elseif ($url_name == 'www') {
                     $url = $_SERVER['REQUEST_URI'] . '/';*/
            } else {
                $url = $_SERVER['REQUEST_URI'];
            }
            header('Location://m.' . $cfg['domain'] . $url);
            exit();
        }
    }
}

if ($currentColumn != 'citylist') {
    $cityInfo = GetCityWebsiteIdByURL($url_name, $city_name);
    $trueCityId = intval($cityInfo['id']);
}

if ($url_name != 'www') {
    $sphinx_index_array = GetSphinxIndex();
    define('SPHINX_MEMBER_RENT_INDEX', $sphinx_index_array['member_rent']);
    define('SPHINX_MEMBER_SALE_INDEX', $sphinx_index_array['member_sale']);
    define('SPHINX_MEMBER_NEW_INDEX', $sphinx_index_array['member_new']);
    define('SPHINX_MEMBER_QIUZU_INDEX', $sphinx_index_array['member_qiuzu']);
    define('SPHINX_MEMBER_QIUGOU_INDEX', $sphinx_index_array['member_qiugou']);
    define('SPHINX_MEMBER_JOB_INDEX', $sphinx_index_array['member_job']);

    define('SPHINX_SEARCH_RENT_INDEX', $sphinx_index_array['search_rent']);
    define('SPHINX_SEARCH_SALE_INDEX', $sphinx_index_array['search_sale']);
    define('SPHINX_SEARCH_NEW_INDEX', $sphinx_index_array['search_new']);
    define('SPHINX_SEARCH_QIUZU_INDEX', $sphinx_index_array['search_qiuzu']);
    define('SPHINX_SEARCH_QIUGOU_INDEX', $sphinx_index_array['search_qiugou']);
    define('SPHINX_SEARCH_JOB_INDEX', $sphinx_index_array['search_job']);

    define('SPHINX_SEARCH_RENT_OLD_INDEX', $sphinx_index_array['search_rent_old']);

    define('SPHINX_SEARCH_APARTMENT_RENT_INDEX', $sphinx_index_array['member_rent']);
    define('SPHINX_SEARCH_APARTMENT_RENT_OLD_INDEX', $sphinx_index_array['search_rent_old']);

    define('SPHINX_MEMBER_RENT_HOME_PROMOTE_INDEX', $sphinx_index_array['member_rent_home_promote']);
    define('SPHINX_MEMBER_SALE_HOME_PROMOTE_INDEX', $sphinx_index_array['member_sale_home_promote']);
    define('SPHINX_MEMBER_NEW_HOME_PROMOTE_INDEX', $sphinx_index_array['member_new_home_promote']);

    define('SPHINX_MEMBER_RENT_BANNER_PROMOTE_INDEX', $sphinx_index_array['member_rent_banner_promote']);
    define('SPHINX_MEMBER_SALE_BANNER_PROMOTE_INDEX', $sphinx_index_array['member_sale_banner_promote']);
    define('SPHINX_MEMBER_NEW_BANNER_PROMOTE_INDEX', $sphinx_index_array['member_new_banner_promote']);

    define('SPHINX_SEARCH_MEMBER_INDEX', $sphinx_index_array['member']);
    define('SPHINX_SEARCH_NEWS', $sphinx_index_array['news']);
}

//网站头部和底部通用广告
$website_header_ad = GetAdList(73, $query);
$website_footer_ad = GetAdList(118, $query);
$website_footer_ad_2 = GetAdList(186, $query);
$mobile_website_footer_ad = GetAdList(195, $query);
$page->tpl->assign('websiteFooterAd', $website_footer_ad);
$page->tpl->assign('website_footer_ad_2', $website_footer_ad_2);

//所有页面顶部广告位
$website_all_top_ad = GetAdList(229, $query);
$page->tpl->assign('website_all_top_ad', $website_all_top_ad);

//关于我们
$aboutList = $query->table('about')->field('id, title')->where('website_id = ' . WEBHOSTID)->order('id asc')->cache(true)->all();
$page->tpl->assign('aboutlist', $aboutList);

//当前页面URL地址
$page_url = $http_type . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$page->tpl->assign('page_url', $page_url);
// $newMsgCount = 0;
// if($_COOKIE[COOKIE_PREFIX.'AUTH_MEMBER_NAME']){
// 	$innernote = new Innernote($query);
// 	$newMsgCount = $innernote->getCount(' is_new = 1 and msg_to = \''.$_COOKIE[COOKIE_PREFIX.'AUTH_MEMBER_NAME'].'\'');
// }
//$page->tpl->assign('msgCount',$newMsgCount);

$cityInfo['id'] = intval($cityInfo['id']);

$room_option = array(
    1 => '一室',
    2 => '二室',
    3 => '三室',
    4 => '四室',
    5 => '五室',
    6 => '六室',
    7 => '七室',
    8 => '八室',
    9 => '九室',
    10 => '九室以上'
);


$is_pc_wechat_browser = IsPCWechatBrowser();
$page->tpl->assign('is_pc_wechat_browser', $is_pc_wechat_browser);

$disable_header_ad_count = intval($_COOKIE['disable_header_ad_count']) + 1;
$page->tpl->assign('disable_header_ad_count', $disable_header_ad_count);