<?php

/**
 * Created by net.
 * E-mail geow@qq.com
 * Date: 2016/6/5
 * Time: 9:48
 * 自动缓存数据到不同数据服务器中，如发现某服务器异常，自动处理
 */

class Cache
{
    private static $_instance = null;
    private $_config = array(
        'type' => 'memcached',
        'servers' => array(
            0 => array(
                'host' => '127.0.0.1',
                'port' => '11211',
                'weight' => 1
            )
        ),
        'prefix' => '',
        'compress' => false,
        'expire_time' => 60
    );
    private $_cache = null; //缓存对象

    private function __construct($config = array())
    {
        if (empty($config)) {
            $config = GetConfig('cache');
        }
        $this->_config = array_merge($this->_config, $config);
        return $this->connect();
    }

    public function connect()
    {
        switch ($this->_config['type']) {
            case 'memcached':
                if (!extension_loaded('memcached') ) {
                    exit('PHP环境没有安装Memcached扩展');
                }
                $this->_cache = new Memcached();
                $this->_cache->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);  //一致性Hash算法
                $this->_cache->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, 3);   //指定连接失败的次数，达到后将从服务器列表中去除
                $this->_cache->setOption(Memcached::OPT_COMPRESSION, $this->_config['compress']);
                $this->_cache->setOption(Memcached::OPT_REMOVE_FAILED_SERVERS, true);   //从列表中移除失效的服务器
                //$this->_cache->setOption(Memcached::OPT_RETRY_TIMEOUT, 1);  //等待失败的连接重试时间
                if ($this->_config['prefix']) {
                    $this->_cache->setOption(Memcached::OPT_PREFIX_KEY, $this->_config['prefix']);  //存储数据时的前缀
                }
                $result = $this->_cache->addServers($this->_config['servers']);
                if ($result == false) {
                    exit('Memcached连接失败');
                }
                break;
            case 'memcache':
                if (!extension_loaded('memcache')) {
                    exit('PHP环境没有安装Memcache扩展');
                }
                $this->_cache = new Memcache();
                //成功添加的数量
                $connent_count = 0;
                foreach ($this->_config['servers'] as $key => $val) {
                    if (empty($val['host'])) {
                        continue;
                    }
                    $val['port'] = empty($val['port']) ? 11211 : intval($val['port']);
                    $val['weight'] = empty($val['weight']) ? 1 : intval($val['weight']);
                    $result = $this->_cache->addserver($val['host'], $val['port'], false, $val['weight']);
                    if ($result) {
                        $connent_count++;
                    }
                }
                if ($connent_count == 0) {
                    exit('Memcached连接失败');
                }
                break;
            default:

        }
        return is_null($this->_cache) ? false : true;
    }

    public static function getInstance($config = array())
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Cache($config);
        }
        return self::$_instance;
    }

    public function add($key, $data, $expire_time = 0)
    {
        $expire_time = intval($expire_time) < 0 ? intval($this->_config['expire_time']) : intval($expire_time);
        switch ($this->_config['type']) {
            case 'memcached':
                $result = $this->_cache->add($key, $data, $expire_time);
                break;
            case 'memcache':
                $result = $this->_cache->add($this->_config['prefix'] . $key, $data, $this->_config['compress'], $expire_time);
                break;
            default:
                $result = false;
        }
        return $result;
    }

    public function set($key, $data, $expire_time = null)
    {
        $expire_time = is_null($expire_time) ? intval($this->_config['expire_time']) : intval($expire_time);
        switch ($this->_config['type']) {
            case 'memcached':
                $result = $this->_cache->set($key, $data, $expire_time);
                break;
            case 'memcache':
                $result = $this->_cache->set($this->_config['prefix'] . $key, $data, $this->_config['compress'], $expire_time);
                break;
            default:
                $result = false;
        }
        return $result;
    }

    public function get($key)
    {
        switch ($this->_config['type']) {
            case 'memcached':
                break;
            case 'memcache':
                $key = $this->_config['prefix'] . $key;
                break;
            default:

        }
        return $this->_cache->get($key);
    }

    public function delete($key, $time = 0)
    {
        switch ($this->_config['type']) {
            case 'memcached':
                break;
            case 'memcache':
                $key = $this->_config['prefix'] . $key;
                break;
            default:

        }
        return $this->_cache->delete($key, $time);
    }

    public function flush() {
        return $this->_cache->flush();
    }

    public function decrement($key, $value = 1)
    {
        return $this->_cache->decrement($key, $value);
    }

    public function increment($key, $value = 1)
    {
        return $this->_cache->increment($key, $value);
    }

    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
        return false;
    }

    public function __destruct()
    {
        switch ($this->_config['type']) {
            case 'memcached':
                $this->_cache->quit();
                break;
            case 'memcache':
                $this->_cache->close();
                break;
            default:

        }
        self::$_instance = null;
    }
}