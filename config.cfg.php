<?php
/*
 * 文档根路径
 */
$cfg['path']['root'] = dirname(__FILE__) . '/';
$cfg['version'] = '2.0';
$cfg['time'] = time();

/*
 * 站点信息
 */
$cfg['charset']    	= 	'utf-8';
$cfg['style']  		= 	'2010a';       // 主题
$cfg['url']   		= 	'/';   // 站点URL

$cfg['url_sale']   	= 	 $cfg['url'].'sale/';
$cfg['url_pinggu']  	= 	$cfg['url'].'pinggu/';
$cfg['url_rent']   		= 	$cfg['url'].'rent/';
$cfg['url_news']   		= 	$cfg['url'].'news/';
$cfg['url_newHouse']   	= 	$cfg['url'].'newHouse/';
$cfg['url_company']   	= 	$cfg['url'].'company/';
$cfg['url_community']   =   $cfg['url'].'community/';
$cfg['url_broker']   	= 	$cfg['url'].'broker/';
$cfg['url_university']  = 	$cfg['url'].'university/';
$cfg['url_shop']  		= 	$cfg['url'].'shop/';
$cfg['url_bbs']   		= 	$cfg['url'].'bbs/';
$cfg['url_upfile']   		= $cfg['url'].'upfile/';


$cfg['domain'] 		= 	'01fy.cn';   // 域名
$cfg['debug']  		= 	false;         //是否开启调试
$cfg['path']['conf'] 	= $cfg['path']['root'] . 'conf/';
$cfg['path']['part'] 	= $cfg['path']['root'] . 'part/';
$cfg['path']['data'] 	= $cfg['path']['root'] . 'data/';
$cfg['path']['common'] 	= $cfg['path']['root'] . 'common/';
$cfg['path']['lib']     = $cfg['path']['common'] . 'lib/';
$cfg['path']['apps'] 	= $cfg['path']['common'] . 'apps/';
$cfg['path']['themes'] 	= $cfg['path']['root'] . 'themes/' . $cfg['style'] . '/';
$cfg['path']['template'] = $cfg['path']['themes'] . 'template/';

$cfg['site']['themes'] 	= $cfg['url'] . 'themes/' .$cfg['style'] . '/';
$cfg['path']['images']	= $cfg['site']['themes'] . 'images/';
$cfg['path']['css']		= $cfg['site']['themes'] . 'css/';
$cfg['path']['js']		= $cfg['url'] . 'common/js/';
$cfg['path']['url_lib']	= $cfg['url'] . 'common/lib/';

$cfg['auth_key'] = 'search_house_code';// cookie验证hash值

$cfg['history_list_number'] = 3;
$cfg['list_number'] = 35;
$cfg['same_list_number'] = 8;

// $cfg['cityName'] = "";   //此处要与anleye.php里配置的名称要一致
// $cfg['cityCode'] = 451;       //此处为程序代码可以  也可以随便写3个数字

//在间隔时间内去数据库验证下Cookie ,设置为 0 即为每次都是在数据库中验证用户
$auth_time = 0;

//发送邮件的方法("mail", "sendmail", "qmail", or "smtp").
$cfg['mail']['mailer'] = 'smtp';  //服务器类型 例：smtp
$cfg['mail']['host'] = '';  //发信服务器地址：例：smtp.qq.com
$cfg['mail']['username'] = '';   //发信用户名
$cfg['mail']['password'] = '';   //发信密码

$cfg['tmp']['updaetime'] = 3600;		//广告和页面缓存时间
define('ROOT_PATH', dirname(__FILE__));
define('SYSTEM_ADMIN_ID', 1);
define('TABLEPREFIX','fke_');//表前缀

//当前站点ID
define('WEBHOSTID', 3);
define('MEMBER_DB_INDEX', 3);
define('IMG_HOST', '//img.01fy.cn');
define('DEFAULT_HOUSE_PICTURE', '/images/housePhotoDefault.gif');
define('COOKIE_PREFIX', 'FY_');
define('SESSION_PREFIX', 'FY_');

//memcache配置
define('MEMCACHE_EXPIRETIME', 60);
define('MEMCACHE_MAX_EXPIRETIME', 21600);
define('MEMCACHE_PREFIX', 'fyw_');

//Sphinx
define('MAX_DATA_LIMIT', 3000);

//黑关键字字典位置
define('KEYWORDS_PATH', $cfg['path']['data'].'/keywords/keywords.txt');

define('UPDATE_CACHE_KEY', 'LZvS3CBjYUHVdojR9rTStkNHCEcrH9rR');
define('MEMBER_LOGIN_UPDATE_TIME', 300);    //5分钟统计一次

//系统通用配置文件
$web_config = array(
    'db' => array(
        'db_type' => 'mysql',
        'db_host' => '127.0.0.1',
        'db_port' => '3306',
        'db_user' => 'soufy',
        'db_password' => '8TGEnDs8b53Y42i7',
        'db_name' => 'soufy',
        'db_prefix' => 'fke_',
        'db_charset' => 'utf8mb4',
        'db_deploy_type' => 1, //0单一服务器  1分布式服务器
        'db_rw_separate' => true, //是否读写分离
        'db_master_num' => 1, //写服务器数量，默认为1
        'db_slave_no' => '' //指定从服务器序号，服务器编号从0开始
    ),
    'member_db' => array(
        'db_type' => 'mysql',
        'db_host' => '127.0.0.1',
        'db_port' => '3306',
        'db_user' => 'fyw_member',
        'db_password' => 'H75ZDremn2HCPPnW',
        'db_name' => 'fyw_member',
        'db_prefix' => 'fke_',
        'db_charset' => 'utf8mb4',
        'db_deploy_type' => 1, //0单一服务器  1分布式服务器
        'db_rw_separate' => true, //是否读写分离
        'db_master_num' => 1, //写服务器数量，默认为1
        'db_slave_no' => '' //指定从服务器序号，服务器编号从0开始
    ),
    'zhoujb_db' => array(
            'db_type' => 'mysql',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_user' => 'zhoujingbao_com',
            'db_password' => 'FLm8JkPnrpjJsxCf',
            'db_name' => 'zhoujingbao_com',
            'db_prefix' => 'jianzhanzj_com_',
            'db_charset' => 'utf8mb4',
            'db_deploy_type' => 1, //0单一服务器  1分布式服务器
            'db_rw_separate' => true, //是否读写分离
            'db_master_num' => 1, //写服务器数量，默认为1
            'db_slave_no' => '' //指定从服务器序号，服务器编号从0开始
        ),
    'cache' => array(
        'type' => 'memcached',
        'servers' => array(
            0 => array(
                'host' => '127.0.0.1', //主数据库和个人房源搜索
                'port' => '2058',
                'weight' => 1
            )
        ),
        'prefix' => '',
        'compress' => false,
        'expire_time' => 21600,
    ),
    'sphinx' => array(
        'server' => '98.126.207.154',
        'port' => 1367,
        'max_data_limit' => 400,
        'member_index_prefix' => 'fyw_',
        'zfy_index_prefix' => 'zfy_',
        'index_group_enable' => false
    ),
    'sms' => array(
        'access_key_id' => '',
        'access_key_secret' => '',
        'region_id' => 'cn-hangzhou',
        'domain' => 'dysmsapi.aliyuncs.com',
        'sign_name' => '第一时间房源网',
        'out_id_prefix' => 'fyw-pc',
        'template_code_register' => '',
        //'username' => 'cf_bjlm',
        //'password' => 'bjlm@123',
        'max_day_sms_send' => 5,
        'interval' => 60,
        'expire_time' => 600
    ),
    //阿里云人机验证配置
    'afs' => array(
        'access_key_id' => '',
        'access_key_secret' => '',
        'region_id' => 'cn-hangzhou',
        'endpoint_name' => 'cn-hangzhou',
        'product' => 'afs',
        'domain' => 'afs.aliyuncs.com',
    ),
    'oss' => array(
       'access_key_id' => 'LTAI5tGESQ2fnKbcXMaXZfr5',
        'access_key_secret' => 'mJ9U2iH9hBYhUZ0ZgQPrj2A5i62xxn',
        //'end_point_internal' => 'oss-cn-hangzhou-internal.aliyuncs.com',
        'end_point_internal' => 'oss-cn-hangzhou.aliyuncs.com',
        'end_point' => 'oss-cn-hangzhou.aliyuncs.com',
        'bucket' => '0user',
        'bucket_common' => '01fy',
        'expire_time' => 60,
        'dir' => 'house/',
        'file_max_length' => 1048576000,
        'callback_url' => 'https://www.' . $cfg['domain'] . '/ajax/oss_callback.php'
        //'callback_url' => 'http://120.26.63.165/ajax/oss_callback.php'
        //'callback_url' => 'http://www.soufy.cn/ajax/oss_callback.php'
    ),
    'redis' => array(
        'host' => '172.16.244.54',
        'port' => 3379,
        'password' => 'W6myh5avpWwrjTfV',
        //'password' => '',
        'prefix' => 'net_',
        'disabled_count_limit' => 3
    )
);
