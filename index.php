<?php
require_once(dirname(__FILE__).'/common.inc.php');
if ($trueCityId > 0){
    require_once(dirname(__FILE__).'/home.php');
}else{
    //上次访问的站点ID
    $cityID = isset($_COOKIE[COOKIE_PREFIX . 'cityid']) ? intval($_COOKIE[COOKIE_PREFIX . 'cityid']) : 0;
    $data_info = $query->table('city_website')->field('id, city_name, url_name')->where('id = ' . $cityID)->cache(true)->one();
    $jump_url_name = $data_info['url_name'];

    if ($url_name == 'm') {
        require_once(dirname(__FILE__) . '/mobile/index.php');
        exit();
    } elseif ($url_name == 'www' && empty($jump_url_name)){
        //定位城市
        /*$url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.getclientip();
        $result = json_decode(file_get_contents($url), true);
        $positionInfo = $result['data'];
        if ($positionInfo['city']) {
            $position_city_info = $query->table('city_website')->field('id, city_name, url_name')->where("city_name like '{$positionInfo['city']}%' and is_open = 1")->cache(true)->one();
            if (empty($_COOKIE[COOKIE_PREFIX . 'cityid']) && !empty($position_city_info)) {
                header('Location://' . $position_city_info['url_name'] . '.' . $cfg['page']['basehost'] . $_SERVER['REQUEST_URI']);
                exit();
            }
        }*/
        require_once(dirname(__FILE__).'/citylist.php');
        exit();
    }elseif ($url_name == 'www' && !empty($jump_url_name) && $jump_url_name != 'www'){
        header('Location://'.$jump_url_name.'.'.$cfg['page']['basehost'] . $_SERVER['REQUEST_URI']);
        exit();
    }elseif ($url_name != 'www' && $jump_url_name == $url_name){
        require_once(dirname(__FILE__).'/home.php');
        exit();
    }
}
