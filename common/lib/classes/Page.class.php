<?php

/**
 * Created by net.
 * E-mail: geow@qq.com
 * Date: 2016/6/12
 * Time: 8:26
 */
require_once(dirname(__FILE__) . '/Html.class.php');

/**
 * 页面信息类
 */
class Page extends Html
{

    public $tpl;

    public $cache;


    public function __construct()
    {
        $GLOBALS['cfg']['page'] = array();
    }

    public function __set($key, $value)
    {
        $GLOBALS['cfg']['page'][$key] = $value;
    }

    public function __get($key)
    {
        return $GLOBALS['cfg']['page'][$key];
    }


    /**
     * 添加一个JS文件包含
     * @param string $file 文件名
     * @access public
     * @return void
     */
    public function addJs($file, $btm = NULL)
    {
        if (strpos($file, '/') == false) {
            $file = $GLOBALS['cfg']['path']['js'] . $file;
        }
        if ($btm == NULL) {
            $GLOBALS['cfg']['page']['jsfiles'][$file] = $file;
        } else {
            $GLOBALS['cfg']['page']['jsbtmfiles'][$file] = $file;
        }
    }


    /**
     * 取生成的包含JS HTML
     * @access public
     * @return string
     */
    public function getJsHtml($btm = NULL)
    {
        $html = '';
        if (!$GLOBALS['cfg']['page']['jsfiles']) {
            return;
        }
        $jsFile = $btm ? 'jsbtmfiles' : 'jsfiles';
        if ($GLOBALS['cfg']['page'][$jsFile]) {
            foreach ($GLOBALS['cfg']['page'][$jsFile] as $value) {
                $html .= $this->jsInclude($value, true) . "\n";
            }
            return $html;
        } else {
            return;
        }
    }

    /**
     * 添加一个CSS文件包含
     * @param string $file 文件名
     * @access public
     * @return void
     */
    public function addCss($file)
    {
        if (strpos($file, '/') == false) {
            $file = $GLOBALS['cfg']['path']['css'] . $file;
        }
        $GLOBALS['cfg']['page']['cssfiles'][$file] = $file;
    }

    /**
     * 取生成的包含CSS HTML
     * @access public
     * @return string
     */
    public function getCssHtml()
    {
        if (!$GLOBALS['cfg']['page']['cssfiles']) {
            return;
        }
        $html = '';
        foreach ($GLOBALS['cfg']['page']['cssfiles'] as $value) {
            $html .= $this->cssInclude($value, true);
        }
        return $html;
    }

    /**
     * 显示输出页面
     * @access public
     * @return string
     */
    public function show()
    {
        $path = '';
        if ($this->dir) {
            $path = $this->dir . '/';
        }
        $path .= $this->name . '.tpl';

        $this->tpl->assign('jsFiles', $this->getJsHtml());
        $this->tpl->assign('jsFiles1', $this->getJsHtml(1));
        $this->tpl->assign('cssFiles', $this->getCssHtml());
        $this->tpl->display($path);
    }

    /**
     * 转到URL,并提示信息
     * @param string $url URL
     * @param string $msg 提示信息
     * @access public
     * @return void
     */
    public function Rdirect($url, $msg = NULL)
    {
        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ajax_return(array('error' => 0, 'msg' => $msg, 'url' => $url));
        } else {
            if ($msg) {
                $this->jsAlert($msg);
            }
            $this->js('window.location.href = "' . $url . '";');
            $this->output(true);
        }
        exit;
    }

    /**
     * replace方式转到URL,并提示信息
     * @param string $url URL
     * @param string $msg 提示信息
     * @access public
     * @return void
     */
    public function replace($url, $msg = NULL)
    {
        if ($msg) {
            $this->jsAlert($msg);
        }
        $this->js('location.replace("' . $url . '");');
        $this->output(true);
        exit;
    }

    /**
     * 返回,并提示信息
     * @param string $msg 提示信息
     * @access public
     * @return void
     */
    public function back($msg)
    {
        global $domain;
        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ajax_return(array('error' => 1, 'msg' => $msg));
        } else {
            setcookie(COOKIE_PREFIX . 'BACK_URL', $_SERVER['REQUEST_URI'], (time() - 3600), '/', $domain[0]);
            $this->jsAlert($msg);
            $this->js('history.back();');
            $this->output(true);
        }
        exit;
    }

    /**
     * 开始页面缓存
     * @param string $file 文件名
     * @param string $time 有效时间
     * @param string $output 是否输出
     * @access public
     * @return void
     */
    public function cache($file, $time, $output)
    {
        global $cfg;
        require_once($cfg['path']['lib'] . 'classes/PageCache.class.php');
        $this->cache = new PageCache($file, $time);
        $this->cache->get($output);
    }

    /**
     * 保存页面缓存
     * @access public
     * @return void
     */
    public function save()
    {
        $path = '';
        if ($this->dir) {
            $path = $this->dir . '/';
        }
        $path .= $this->name . '.tpl';

        $this->tpl->assign('jsFiles', $this->getJsHtml());
        $this->tpl->assign('cssFiles', $this->getCssHtml());
        $content = $this->tpl->fetch($path);
        if ($this->cache) {
            $this->cache->set($content);
        }
    }

    public function GetContent()
    {
        $path = '';
        if ($this->dir) {
            $path = $this->dir . '/';
        }
        $path .= $this->name . '.tpl';

        $this->tpl->assign('jsFiles', $this->getJsHtml());
        $this->tpl->assign('cssFiles', $this->getCssHtml());
        return $this->tpl->fetch($path);
    }
}