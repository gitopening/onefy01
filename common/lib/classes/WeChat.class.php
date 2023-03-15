<?php

/**
 * Class Wechat
 * 微信公众平台类
 * author net
 * E-mail geow@qq.com
 */

class Wechat
{
    private $_config = array(
        'app_id' => '',
        'app_secret' => '',
        'access_token' => '',
        'wechat_token' => '', //开发者验证Token
        'wechat_encoding_aes_key' => '' //开发者加密密钥
    );
    private $db_common = null;
    private $db_member = null;
    private $log_file = '';
    public $debug = false;

    public function __construct($config = array(), $db_common = null, $db_member = null)
    {
        $this->db_common = $db_common;
        $this->db_member = $db_member;
        $this->log_file = ROOT_PATH . '/tmp/wechat.log';

        if (empty($config)) {
            //获取数据库中配置的微信参数
            $website_info = $this->db_common->table('web_config')->where('id = ' . WEBHOSTID)->one();
            if (empty($website_info['wechat_access_token']) || (time() - $website_info['wechat_get_time'] > 7000)) {
                exit('公众平台AccessToken不存在或已过期');
            }
            $config = array(
                'app_id' => $website_info['wechat_app_id'],
                'app_secret' => $website_info['wechat_app_secret'],
                'access_token' => $website_info['wechat_access_token'],
                'wechat_is_verify' => $website_info['wechat_is_verify'],
                'wechat_token' => $website_info['wechat_token'],
                'wechat_encoding_aes_key' => $website_info['wechat_encoding_aes_key']
            );
        }
        $this->_config = array_merge($this->_config, $config);
        if (empty($this->_config['app_id']) || empty($this->_config['app_secret'])) {
            exit('微信配置信息错误');
        }
    }

    public function checkSignature()
    {
        //WriteLog(var_export($_GET, true), $this->log_file);
        if ($this->_config['wechat_is_verify'] != 1) {
            $signature = trim($_GET['signature']);
            $timestamp = trim($_GET['timestamp']);
            $nonce = trim($_GET['nonce']);
            $echostr = trim($_GET['echostr']);

            $tmp_array = array($this->_config['wechat_token'], $timestamp, $nonce);
            sort($tmp_array, SORT_STRING);
            $sha1_string = sha1(implode('', $tmp_array));
            if ($signature == $sha1_string) {
                //更新数据库中的验证状态
                $data = array(
                    'wechat_is_verify' => 1
                );
                $this->db_common->table('web_config')->where('id = ' . WEBHOSTID)->save($data);

                //输出随机字符串
                echo $echostr;
            } else {
                exit('验证失败');
            }
            exit();
        }
    }

    public function request($url, $data = array(), $abort = false)
    {
        $timeout = $abort ? 1 : 2;
        $ch = curl_init();
        if ($data) {
            if (is_array($data)) {
                $form_data = http_build_query($data);
            } else {
                $form_data = $data;
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $form_data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($ch);

        //存储请求日志
        if ($this->debug == true) {
            WriteLog($result, $this->log_file);
        }

        return $result;
    }

    public function getQRCodeUrl($ticket)
    {
        if (empty($ticket)) {
            return false;
        }
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket;
    }

    /**
     * @param $scene_data
     * @param int $temp 验证码是临时还是永久
     * @param int $param_is_string 参数是否是字符串形式的参数
     * @param int $expire_seconds
     * @return bool|mixed
     */
    public function getTicket($scene_data, $temp = 0, $param_is_string = 0, $expire_seconds = 2592000)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->_config['access_token'];
        if ($temp == 1) {
            $action_name = 'QR_';
        } else {
            $action_name = 'QR_LIMIT_';
        }
        if ($param_is_string == 1) {
            $action_name .= 'STR_SCENE';
        } else {
            $action_name .= 'SCENE';
        }
        $post_data = array(
            'action_name' => $action_name,
            'expire_seconds' => $expire_seconds
        );
        $post_data['action_info']['scene'] = $scene_data;
        $ticket = $this->request($url, json_encode($post_data));
        if ($ticket) {
            return json_decode($ticket, true);
        } else {
            return false;
        }
    }

    public function parseXML($data)
    {
        $parsed_data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $parsed_data;
    }

    public function createMenu($data = array())
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->_config['access_token'];
        $data = $this->decodeUnicode(json_encode($data));
        return $this->request($url, $data);
    }

    public function decodeUnicode($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            create_function(
                '$matches',
                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
            ),
            $str);
    }

    public function replyMessage($to_user_name, $from_user_name, $msg_type = '', $data = '')
    {
        if (empty($msg_type) || empty($data)) {
            return '';
        } else {
            //拼接xml数据
            $content = '<xml>' .
                '<ToUserName><![CDATA[' . $to_user_name . ']]></ToUserName>' .
                '<FromUserName><![CDATA[' . $from_user_name. ']]></FromUserName>' .
                '<CreateTime>' . time() . '</CreateTime>' .
                '<MsgType><![CDATA[' . $msg_type . ']]></MsgType>' .
                $data .
                '</xml>';
            echo $content;
            exit();
        }
    }

    public function replyTextMessage($to_user_name, $from_user_name, $content)
    {
        $msg_type = 'text';
        $data = '<Content><![CDATA[' . $content . ']]></Content>';
        return $this->replyMessage($to_user_name, $from_user_name, $msg_type,  $data);
    }

    public function replyPictureMessage($to_user_name, $from_user_name, $media_id)
    {
        $msg_type = 'image';
        $data = '<Image><MediaId><![CDATA[' . $media_id. ']]></MediaId></Image>';
        return $this->replyMessage($to_user_name, $from_user_name, $msg_type,  $data);
    }

    public function replyVoiceMessage($to_user_name, $from_user_name, $media_id)
    {
        $msg_type = 'voice';
        $data = '<Voice><MediaId><![CDATA[' . $media_id. ']]></MediaId></Voice>';
        return $this->replyMessage($to_user_name, $from_user_name, $msg_type,  $data);
    }

    public function replyVideoMessage($to_user_name, $from_user_name, $media_id, $title, $description)
    {
        $msg_type = 'video';
        $data = '<Video>' .
            '<MediaId><![CDATA[' . $media_id. ']]></MediaId>' .
            '<Title><![CDATA[' . $title . ']]></Title>' .
            '<Description><![CDATA[' . $description . ']]></Description>' .
            '</Video>';
        return $this->replyMessage($to_user_name, $from_user_name, $msg_type,  $data);
    }

    public function replyMusicMessage($to_user_name, $from_user_name, $thumb_media_id, $music_url, $HQ_music_url = '', $title = '', $description = '')
    {
        $msg_type = 'music';
        $data = '<Music>' .
            '<Title><![CDATA[' . $title . ']]></Title>' .
            '<Description><![CDATA[' . $description . ']]></Description>' .
            '<MusicUrl><![CDATA[' . $music_url . ']]></MusicUrl>' .
            '<HQMusicUrl><![CDATA[' . $HQ_music_url . ']]></HQMusicUrl>' .
            '<ThumbMediaId><![CDATA[' . $thumb_media_id . ']]></ThumbMediaId>' .
            '</Music>';
        return $this->replyMessage($to_user_name, $from_user_name, $msg_type,  $data);
    }

    public function replyNewsMessage($to_user_name, $from_user_name, $news_array)
    {
        $news_count = count($news_array);
        if ($news_count == 0) {
            exit('图文信息内容不能为空');
        }
        $msg_type = 'news';
        $data = '<ArticleCount>' . count($news_array) . '</ArticleCount><Articles>';
        foreach ($news_array as $item) {
            $data .= '<Articles>' .
                '<item>' .
                '<Title><![CDATA[' . $item['title'] . ']]></Title>' .
                '<Description><![CDATA[' . $item['description1'] . ']]></Description>' .
                '<PicUrl><![CDATA[' . $item['picurl'] . ']]></PicUrl>' .
                '<Url><![CDATA[' . $item['url'] . ']]></Url>' .
                '</item>';
        };
        $data .= '</Articles>';
        return $this->replyMessage($to_user_name, $from_user_name, $msg_type,  $data);
    }

    public function replyNoMessage($to_user_name, $from_user_name)
    {
        return $this->replyMessage($to_user_name, $from_user_name);
    }

    public function toUrlParamsString($params)
    {
        $params_string = "";
        foreach ($params as $k => $v) {
            if ($k != 'sign') {
                $params_string .= $k . '=' . $v . '&';
            }
        }
        $params_string = trim($params_string, '&');
        return $params_string;
    }

    public function getAuthCodeUrl($redirect_uri)
    {
        $params = array(
            'appid' => $this->_config['app_id'],
            'redirect_uri' => urlencode($redirect_uri),
            'response_type' => 'code',
            'scope' => 'snsapi_userinfo',
            'state' => 'STATE#wechat_redirect'
        );
        $params_string = $this->toUrlParamsString($params);
        return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . $params_string;
    }

    public function getUserInfo($code)
    {
        $params = array(
            'appid' => $this->_config['app_id'],
            'secret' => $this->_config['app_secret'],
            'code' => $code,
            'grant_type' => 'authorization_code'
        );
        $params_string = $this->toUrlParamsString($params);
        $request_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . $params_string;

        $data = $this->request($request_url);
        return json_decode($data, true);
    }

    public function getUserDetailInfo($access_token, $openid)
    {
        $params = array(
            'access_token' => $access_token,
            'openid' => $openid,
            'lang' => 'zh_CN'
        );
        $params_string = $this->toUrlParamsString($params);
        $request_url = 'https://api.weixin.qq.com/sns/userinfo?' . $params_string;

        $data = $this->request($request_url);
        return json_decode($data, true);
    }

    public function getUserBaseInfo($openid)
    {
        $params = array(
            'access_token' => $this->_config['access_token'],
            'openid' => $openid,
            'lang' => 'zh_CN'
        );
        $params_string = $this->toUrlParamsString($params);
        $request_url = 'https://api.weixin.qq.com/cgi-bin/user/info?' . $params_string;

        $data = $this->request($request_url);
        return json_decode($data, true);
    }

    public function refreshUserAccessToken($refresh_token)
    {
        $params = array(
            'appid' => $this->_config['app_id'],
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        );
        $params_string = $this->toUrlParamsString($params);
        $request_url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?' . $params_string;

        $data = $this->request($request_url);
        return json_decode($data, true);
    }

    public function checkUserAccessToken($access_token, $openid)
    {
        $params = array(
            'access_token' => $access_token,
            'openid' => $openid
        );
        $params_string = $this->toUrlParamsString($params);
        $request_url = 'https://api.weixin.qq.com/sns/auth?' . $params_string;

        $data = json_decode($this->request($request_url), true);
        if ($data['errcode'] == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function sendTemplateMessage($openid, $template_id, $post_data, $url = '', $miniprogram = array())
    {
        $params = array(
            'access_token' => $this->_config['access_token']
        );
        $data = array(
            'touser'=> $openid,
            'template_id' => $template_id,
            'data' => $post_data
        );
        if (!empty($url)) {
            $data['url'] = $url;
        }
        if (!empty($miniprogram)) {
            $data['miniprogram'] = $miniprogram;
        }
        $params_string = $this->toUrlParamsString($params);
        $request_url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?' . $params_string;

        $data = $this->request($request_url, json_encode($data));
        return json_decode($data, true);
    }
}