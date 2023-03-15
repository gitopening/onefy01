<?php

/**
 * Created by net.
 * E-mail: geow@qq.com
 * Date: 2016/6/11
 * Time: 15:35
 */
require_once(dirname(dirname(__FILE__)) . '/lib/sphinxapi.php');

class Sphinx extends SphinxClient
{
    private static $_instance = null;

    public static function getInstance($config = array())
    {
        $config = empty($config) ? GetConfig('sphinx') : $config;
        if (empty($config)) {
            exit('搜索服务器连接失败');
        }
        if (is_null(self::$_instance)) {
            self::$_instance = new SphinxClient();
            self::$_instance->SetServer($config['server'], $config['port']);
            self::$_instance->SetMatchMode(SPH_MATCH_ALL);
            self::$_instance->SetArrayResult(true);
        }
        return self::$_instance;
    }
}