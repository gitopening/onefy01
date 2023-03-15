<?php

/**
 * Class SMS
 * 短信发送类，能记录短信发送记录，并进行发送时间，发送次数统计判断
 * todo 多个发送服务器和数据库操作对象，多单例模式处理
 * author net
 * E-mail geow@qq.com
 */

class SMS
{
    private static $_instance = null;
    private $_config = array(
        'access_key_id' => '',
        'access_key_secret' => '',
        'sign_name' => '',
        //'username' => '',
        //'password' => '',
        'max_day_sms_send' => 5,
        'interval' => 180
    );
    private $db = null;
    private $tableName = 'sms_record';

    private function __construct($config = array(), $db = null)
    {
        if (empty($config)) {
            $config = GetConfig('sms');
        }
        $this->_config = array_merge($this->_config, $config);
        if (empty($this->_config['access_key_id']) || empty($this->_config['access_key_secret'])) {
            exit('未配置接口参数');
        }
        $this->db = $db;
    }

    public static function getInstance($config = array(), $db = null)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new SMS($config, $db);
        }
        return self::$_instance;
    }

    public function SendSMS($data)
    {
        $mobile = trim($data['mobile']);
        if (is_mobile($mobile) == false) {
            return array(
                'error' => 1,
                'msg' => '手机号码格式不正确'
            );
        }
        //检测今日是否还能发短信
        $result = $this->checkIsAllowSend($mobile);
        if ($result['error'] == 1) {
            return $result;
        }

        $params = array ();

        // *** 需用户填写部分 ***
        // fixme 必填：是否启用https
        $security = false;

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $access_key_id = $this->_config['access_key_id'];
        $access_key_secret = $this->_config['access_key_secret'];

        // fixme 必填: 短信接收号码
        $params['PhoneNumbers'] = $mobile;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        if ($data['sign_name']) {
            $params['SignName'] = $data['sign_name'];
        } else {
            $params['SignName'] = $this->_config['sign_name'];
        }

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params['TemplateCode'] = $data['template_code'];

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = array (
            'code' => $data['code']
        );

        // fixme 可选: 设置发送短信流水号
        if ($data['out_id']) {
            $params['OutId'] = $data['out_id'];
        } else {
            $params['OutId'] = $this->_config['out_id_prefix'] . '-' . MyDate('YmdHis', time()) . rand(10000,9999);
        }

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        //$params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            //$params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
            $params["TemplateParam"] = json_encode($params["TemplateParam"]);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        require_once(dirname(dirname(__FILE__)) . '/aliyun-dysms-php-sdk-lite/SignatureHelper.php');
        $helper = new Aliyun\DySDKLite\SignatureHelper();
        // 此处可能会抛出异常，注意catch
        $result = $helper->request(
            $access_key_id,
            $access_key_secret,
            $this->_config['domain'],
            array_merge($params, array(
                'RegionId' => $this->_config['region_id'],
                'Action' => 'SendSms',
                'Version' => '2017-05-25'
            )),
            $security
        );

        //记录短信发送日志
        $content = "时间：" . MyDate('Y-m-d H:i:s', time()) . " 手机号：" . $mobile ." 返回结果：" . $result->Code . '-' . $result->Message . "\n\r";
        $log_file = dirname(dirname(dirname(dirname(__FILE__)))) . '/upfile/sms/sms_' . MyDate('Ymd', time()) . '.log';
        $fp = fopen($log_file, 'a+');
        fwrite($fp, $content);
        fclose($fp);

        if (empty($result)) {
            $returnData['error'] = 0;
            $returnData['msg'] = '服务器没有返回值';
        } else {
            if ($result->Code == 'OK') {
                $returnData['error'] = 0;
                $returnData['msg'] = '短信发送成功';
                $returnData['errormsg'] = $result->Message;
                $this->saveRecord($mobile);
            } else {
                $returnData['error'] = 1;
                $returnData['msg'] = '短信发送失败，请联系管理员';
                $returnData['errormsg'] = $result->Message;

                if ($result->Code == 'isv.BUSINESS_LIMIT_CONTROL') {
                    $returnData['msg'] = '获取动态码次数已达今日上限，请明日再试';
                }
            }
        }
        return $returnData;
    }

    public function HttpRequest($url, $data = array(), $abort = false)
    {
        $returnData = array();
        if (!function_exists('curl_init')) {
            $returnData['error'] = 1;
            $returnData['msg'] = '缺少CURL组件';
            return $returnData;
        }
        $timeout = $abort ? 1 : 2;
        $ch = curl_init();
        if (is_array($data) && $data) {
            $formdata = http_build_query($data);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $formdata);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($ch);
        if (strpos($result, '<code>2</code>') !== false) {
            //header('content-Type: text/html; charset=utf-8');
            $returnData['error'] = 0;
            return $returnData;
        } elseif (strpos($result, '<code>8048</code>') !== false) {
            $returnData['error'] = 1;
            $returnData['msg'] = '短信发送失败，达到今日最大发送次数';
        } else {
            //header('content-Type: text/html; charset=utf-8');
            if (empty($result)) {
                $returnData['error'] = 0;
                $returnData['msg'] = '服务器没有返回值';
            } else {
                $returnData['error'] = 1;
                $returnData['msg'] = '短信发送失败，请联系管理员';
                $returnData['errormsg'] = $result;
            }
            //发送失败记录写入日志
            $content = "短信发送记录：\n时间：" . MyDate('Y-m-d H:i:s', time()) . "\n返回结果：" . $result . "\n\r";
            $log_file = dirname(dirname(dirname(dirname(__FILE__)))) . '/upfile/sms.log';
            $fp = fopen($log_file, 'a+');
            fwrite($fp, $content);
            fclose($fp);
            return $returnData;
        }
    }

    public function saveRecord($mobile)
    {
        //检测发送记录是否存在
        $data_info = $this->getRecord($mobile);
        $now_time = time();
        if ($data_info) {
            //更新发送记录
            $data = array(
                'send_time' => $now_time,
                'count' => array('`count` + 1')
            );
            $today = MyDate('Y-m-d', time());
            $send_day = MyDate('Y-m-d', $data_info['send_time']);
            if ($today != $send_day) {
                $data['day_count'] = 1;
            } else {
                $data['day_count'] = array('`day_count` + 1');
            }
            $result = $this->db->table($this->tableName)->where('id = ' . $data_info['id'])->save($data);
        } else{
            //插入新记录
            $data = array(
                'mobile' => $mobile,
                'mobile_crc32' => array($mobile, 'CRC32'),
                'send_time' => $now_time,
                'day_count' => 1,
                'count' => 1
            );
            $result = $this->db->table($this->tableName)->save($data);
        }
        return $result;
    }

    public function getRecord($mobile)
    {
        if (empty($mobile)) {
            return false;
        }
        $condition = array(
            'mobile' => $mobile,
            'mobile_crc32' => array($mobile, 'CRC32')
        );
        return $this->db->table('sms_record')->where($condition)->one();
    }

    public function checkIsAllowSend($mobile)
    {
        $data_info = $this->getRecord($mobile);
        //检测是否有记录
        if (empty($data_info)) {
            return array(
                'error' => 0,
                'msg' => '还没有发送记录'
            );
        }
        //检测今日发送次数
        $today = MyDate('Y-m-d', time());
        $send_day = MyDate('Y-m-d', $data_info['send_time']);
        if ($send_day != $today) {
            return array(
                'error' => 0,
                'msg' => '今日还没有发送短信'
            );
        }
        //检测短信发送时间间隔
        if (time() - $data_info['send_time'] < $this->_config['interval']) {
            return array(
                'error' => 1,
                'msg' => '发送时间间隔太短，请过一会儿再发'
            );
        }
        //检测今日发送次数
        if ($data_info['day_count'] >= $this->_config['max_day_sms_send']) {
            return array(
                'error' => 1,
                'msg' => '获取动态码次数已达今日上限，请明日再试'
            );
        }
        return array(
            'error' => 0,
            'msg' => '今日还可以发送' . ($this->_config['max_day_sms_send'] - $data_info['day_count']) . '条'
        );
    }
}