<?php
$action = $_REQUEST['action'];
if (in_array($action, array('getdeletecode', 'login_sms', 'sendregsms', 'sendsms', 'send_sms', 'send_unsubscribe_code'))) {
    session_start();
}
require_once(dirname(dirname(__FILE__)).'/common.inc.php');
$returnData = array();
switch ($action){
    case 'checkyzm':
        $yzm = $_POST['param'];
        if (md5(strtolower($yzm)) != $_COOKIE[COOKIE_PREFIX . 'validString']) {
            $returnData['info'] = '验证码错误';
            $returnData['status'] = 'n';
        } else {
            $returnData['info'] = '';
            $returnData['status'] = 'y';
        }
        break;
	case 'sendsms':
        //检测验证码
        /*require_once(dirname(dirname(__FILE__)) . '/common/lib/classes/Verify.class.php');
        $Verify = new Verify();
        $verify_code = trim($_GET['verify_code']);
        $check_result = $Verify->check($verify_code);
        if ($check_result == false) {
            $returnData['msg'] = '验证码错误';
            $returnData['error'] = 3;
            break;
        }*/

        //验证码检测
        require_once(dirname(dirname(__FILE__)) . '/common/lib/aliyun-php-sdk-afs/aliyun-php-sdk-core/Config.php');

        //YOUR ACCESS_KEY、YOUR ACCESS_SECRET请替换成您的阿里云accesskey id和secret
        $afs_config = GetConfig('afs');
        $iClientProfile = DefaultProfile::getProfile($afs_config['region_id'], $afs_config['access_key_id'], $afs_config['access_key_secret']);
        $client = new DefaultAcsClient($iClientProfile);
        $iClientProfile->addEndpoint($afs_config['endpoint_name'], $afs_config['region_id'], $afs_config['product'], $afs_config['domain']);

        $request = new afs\Request\V20180112\AnalyzeNvcRequest();
        $request->setData($_GET['verify_code']);    // 必填参数，从前端获取getNVCVal函数的值
        $json_data = array(
            '200' => 'PASS',
            '400' => 'NC',
            '600' => 'SC',
            '800' => 'BLOCK'
        );
        $request->setScoreJsonStr(json_encode($json_data));// 根据自己需求设置，跟前端一致

        $response = $client->getAcsResponse($request);
        $returnData['code'] = intval($response->BizCode);
        //$returnData['code'] = 600;
        if ($returnData['code'] != 100 && $returnData['code'] != 200) {
            $returnData['msg'] = '验证信息错误';
            $returnData['error'] = 1;
            break;
        }

        $mobile = trim($_GET['mobile']);
        if (is_mobile($mobile) == false) {
            $returnData['msg'] = '手机号码格式不正确';
            $returnData['error'] = 1;
            break;
        }
        $member = new Member($member_query);
        $member_id = intval($member->getAuthInfo('id'));
        $memberInfo = $member->getInfo($member_id, '*', true);
        //检测手机号是否已被占用
        $sql = "select id from fke_broker_info where id <> $member_id and mobile = '{$mobile}' and mobile_checked = 1";
        $mem_id = $member_query->getValue($sql);
        if ($mem_id) {
            $returnData['msg'] = '该手机已被占用';
            $returnData['error'] = 1;
            break;
        }

        if (time() - $memberInfo['sms_send_time'] < $sms_config['interval']) {
            $returnData['msg'] = '发送时间间隔太短，请过一会儿再发';
            $returnData['error'] = 1;
            break;
        }

        $sms_config = GetConfig('sms');
        require_once(dirname(dirname(__FILE__)) . '/common/lib/classes/SMS.class.php');
        $SMS = SMS::getInstance($sms_config, $query);
        $authCode = GetAuthCode('num', 6);
        $send_data = array(
            'mobile' => $mobile,
            'template_code' => $sms_config['template_code_register'],
            'code' => $authCode
        );
        $result = $SMS->SendSMS($send_data);
        $returnData['error'] = $result['error'];
        $returnData['msg'] = $result['msg'];
        if ($returnData['error'] == 1) {
            break;
        }

        //更新验证码取得返回值
        $upfileds = array(
            'new_mobile' => $mobile,
            'mobile_auth_code' => $authCode,
            'sms_send_time' => time()
        );
        $res = $member_query->table('broker_info')->where('id = ' . intval($memberInfo['id']))->save($upfileds);
        if ($result['error'] == 0 && $res) {
            $returnData['error'] = 0;
            $returnData['msg'] = '已发送验证码到您的手机';
        }
        break;
    case 'getsubregion':
        $id = intval($_GET['id']);
        $region = get_region_enum($id);
        foreach ($region as $key => $item) {
            $returnData[] = $item;
        }
		break;
    case 'get_all_cityarea':
        $city_id = intval($_GET['city_id']);
        if (empty($city_id)) {
            $returnData = array();
            break;
        }
        $cityarea_list = get_region_enum($city_id, 'sort');
        foreach ($cityarea_list as $key => $cityarea) {
            $cityarea2_list = get_region_enum($cityarea['region_id']);
            $region_list = array();
            foreach ($cityarea2_list as $k => $val) {
                $region_list[$val['region_id']] = $val['region_name'];
            }
            $index = $cityarea['region_id'];
            $returnData[$index]['region_name'] = $cityarea['region_name'];
            $returnData[$index]['list'] = $region_list;
        }
        break;
    case 'getarealettergroup':
        $id = intval($_GET['id']);
        $region = get_region_enum($id);
        echo create_letter_region_option($region);
        exit();
		break;
	case 'getcitywebsitelist':
        $city_id = intval($_GET['city_id']);
        $item = $query->field('id, city_name')->table('city_website')->where('city_id = ' . $city_id)->order('id asc')->cache(true)->all();
        $returnData = $item;
		break;
    case 'get_province_website':
        $province_id = intval($_GET['province_id']);
        $returnData =  $query->field('id, city_name')->table('city_website')->where('province_id = ' . $province_id)->order('is_order asc, id asc')->cache(true)->all();
        break;
    case 'search_city':
        $city_name = trim($_GET['q']);
        $limit = intval($_GET['limit']);
        if (empty($city_name)) {
            $returnData = array(
                'error' => 1,
                'msg' => '请输入要搜索的关键字'
            );
            break;
        }
        $data_list = $query->field('id, city_name, url_name')->table('city_website')->where("city_name like '{$city_name}%'")->order('id desc')->limit($limit)->cache(true)->all();
        foreach ($data_list as &$item) {
            $item['url'] = '//' . $item['url_name'] . '.' . $cfg['page']['basehost'];
        }
        $returnData = array(
            'error' => 0,
            'data' => $data_list
        );
        break;
    case 'get_all_city_website':
        $data_list = $query->field('id, city_name, url_name')->table('city_website')->cache(true)->all();
        $returnData = array(
            'error' => 0,
            'data' => $data_list
        );
        break;
	case 'getdeletecode':
        $ip = sprintf("%u", ip2long(getclientip()));
        //特殊IP段禁止提交访问203.208.60.0  -  203.208.60.255
        if ($ip >= 3419421696 && $ip <= 3419421951) {
            $returnData['msg'] = '验证码发送成功';
            $returnData['error'] = 0;
            break;
        }

        //判断验证码是否正确
        require_once(dirname(dirname(__FILE__)) . '/common/lib/classes/Verify.class.php');
        $Verify = new Verify();
        $verify_code = trim($_GET['vcode']);
        $check_result = $Verify->check($verify_code);
        if ($check_result == false) {
            $returnData['msg'] = '验证码错误';
            $returnData['error'] = 1;
            break;
        }

        $id = intval($_GET['id']);
        $house_type = intval($_GET['type']);
        $member_db = trim($_GET['member_db']);

        if ($member_db == 'x') {
            $house_query = $member_query;
        } elseif ($member_db > 0) {
            $house_query = new DbQueryForMysql(GetConfig('member_db_' . $member_db));
        } else {
            $house_query = $query;
        }

        if ($house_type == 1) {
            $House = new HouseRent($house_query);
        } elseif ($house_type == 2) {
            $House = new HouseSell($house_query);
        } elseif ($house_type == 3) {
            $House = new HouseQiuzu($house_query);
        } elseif ($house_type == 4) {
            $House = new HouseQiugou($house_query);
        } elseif ($house_type == 5) {
            $House = new HouseNew($house_query);
        } elseif ($house_type == 7) {
            $House = new Job($house_query);
        } else {
            $returnData['msg'] = '参数错误';
            $returnData['error'] = 1;
            break;
        }

        //检察房源信息是否存在
        $condition = array('id' => $id, 'is_checked in (0,1)', 'is_delete = 0');
        $dataInfo = $House->table($House->tName)->where($condition)->one();
        if ($house_type == 7) {
            $column_title = '招聘信息';
            $field_id_name = 'job_id';
        } else {
            $column_title = '房源';
            $field_id_name = 'house_id';
        }
        if (empty($dataInfo)) {
            $returnData['msg'] = $column_title . '不存在或已被删除';
            $returnData['error'] = 1;
            break;
        }

        if ($house_type == 7) {
            if ($dataInfo['status'] == 1) {
                $returnData['msg'] = $column_title . '已删除';
                $returnData['error'] = 1;
                break;
            }
            if ($dataInfo['status'] == 5) {
                $returnData['msg'] = $column_title . '已失效，不允许删除';
                $returnData['error'] = 1;
                break;
            }
        } else {
            if ($dataInfo['house_status'] == 1) {
                $returnData['msg'] = $column_title . '已删除';
                $returnData['error'] = 1;
                break;
            }
            if ($dataInfo['house_status'] == 5) {
                $returnData['msg'] = $column_title . '已成交，不允许删除';
                $returnData['error'] = 1;
                break;
            }
        }

        $mobile = $dataInfo['owner_phone'];
        if (empty($mobile)) {
            if ($member_db == 'x') {
                $Report = new Report($member_query);
            } else {
                $Report = new Report($query);
            }
            //提交手机到后台进行手动录入电话号码
            $condition = array(
                'house_id' => $id,
                'house_type' => $house_type,
                'reason' => 4
            );
            //判断是否已存在
            $res = $Report->field('id')->table($Report->tName)->where($condition)->order('addtime desc')->one();
            if ($res) {
                $returnData['msg'] = '验证码发送成功';
                $returnData['error'] = 0;
                break;
            }

            if ($house_type == 1) {
                if ($dataInfo['column_type'] == 1) {
                    $column_type = 'rent';
                } elseif ($dataInfo['column_type'] == 2) {
                    $column_type = 'xzlcz';
                } elseif ($dataInfo['column_type'] == 3) {
                    $column_type = 'spcz';
                }
            } elseif ($house_type == 2) {
                if ($dataInfo['column_type'] == 1) {
                    $column_type = 'sale';
                } elseif ($dataInfo['column_type'] == 2) {
                    $column_type = 'xzlcs';
                } elseif ($dataInfo['column_type'] == 3) {
                    $column_type = 'spcs';
                }
            } elseif ($house_type == 3) {
                if ($dataInfo['column_type'] == 1) {
                    $column_type = 'qiuzu';
                } elseif ($dataInfo['column_type'] == 2) {
                    $column_type = 'xzlqz';
                } elseif ($dataInfo['column_type'] == 3) {
                    $column_type = 'spqz';
                }
            } elseif ($house_type == 4) {
                if ($dataInfo['column_type'] == 1) {
                    $column_type = 'qiugou';
                } elseif ($dataInfo['column_type'] == 2) {
                    $column_type = 'xzlqg';
                } elseif ($dataInfo['column_type'] == 3) {
                    $column_type = 'spqg';
                }
            } elseif ($house_type == 5) {
                if ($dataInfo['column_type'] == 1) {
                    $column_type = 'new';
                } elseif ($dataInfo['column_type'] == 2) {
                    $column_type = 'xzlx';
                } elseif ($dataInfo['column_type'] == 3) {
                    $column_type = 'spx';
                }
            } elseif ($house_type == 7) {
                $column_type = 'job';
            } else {
                $returnData['msg'] = '参数错误';
                $returnData['error'] = 1;
                break;
            }
            $fields_array = array(
                'house_type' => $house_type,
                'house_id' => $id,
                'column_type' => $column_type,
                'report_target' => '0',
                'reason' => '4',
                'reporter' => 0,
                'status' => 0,
                'addtime' => time(),
                'ip' => sprintf("%u", ip2long(getclientip())),
                'city_website_id' => intval($cityInfo['id']),
                'is_text_phone' => 0,
                'is_owner_phone_pic' => 1,
                'owner_phone' => '',
                'website_id' => WEBHOSTID
            );
            $res = $Report->table('report')->save($fields_array);
            $returnData['msg'] = '验证码发送成功';
            $returnData['error'] = 0;
            break;
        }

        //发送短信
        $sms_config = GetConfig('sms');
        $SMS = SMS::getInstance($sms_config, $query);
        $authCode = GetAuthCode('num', 6);
        $send_data = array(
            'mobile' => $mobile,
            'template_code' => $sms_config['template_code_register'],
            'code' => $authCode
        );
        $result = $SMS->SendSMS($send_data);
        if ($result['error'] == 1) {
            $returnData['error'] = 1;
            $returnData['msg'] = $result['msg'];
            break;
        }

        //发送短信成功后处理
        //取得上次验证码发送的信息，如果没有则添加新项目
        $mobile_auth = $House->table($House->tNameMobleAuth)->where($field_id_name . ' = ' . $id)->one();
        if (!empty($mobile_auth)) {
            $fields_array = array(
                'mobile_auth_code' => $authCode,
                'sms_send_time' => time()
            );
            $today = MyDate('Y-m-d', time());
            $send_day = MyDate('Y-m-d', $mobile_auth['sms_send_time']);
            if ($today != $send_day) {
                $fields_array['sms_send_count'] = 1;
            } else {
                $fields_array['sms_send_count'] = array('`sms_send_count` + 1');
            }
            $result = $House->table($House->tNameMobleAuth)->where('house_id = ' . $id)->save($fields_array);
        } else {
            $fields_array = array(
                $field_id_name => $id,
                'mobile_auth_code' => $authCode,
                'sms_send_time' => time(),
                'sms_send_count' => 1
            );
            $result = $House->table($House->tNameMobleAuth)->save($fields_array);
        }

        $returnData['error'] = 0;
        $returnData['msg'] = '验证码发送成功';
		break;
	case 'deletehouse':
        $auth_code = trim($_GET['auth_code']);
        if (strlen($auth_code) != 6) {
            $returnData['msg'] = '请填写正确的6位验证码';
            $returnData['error'] = 1;
            break;
        }

        $id = intval($_GET['id']);
        $house_type = intval($_GET['type']);
        $member_db = trim($_GET['member_db']);

        if ($member_db == 'x') {
            $house_query = $member_query;
        } elseif ($member_db > 0) {
            $house_query = new DbQueryForMysql(GetConfig('member_db_' . $member_db));
        } else {
            $house_query = $query;
        }

        if ($house_type == 1) {
            $House = new HouseRent($house_query);
        } elseif ($house_type == 2) {
            $House = new HouseSell($house_query);
        } elseif ($house_type == 3) {
            $House = new HouseQiuzu($house_query);
        } elseif ($house_type == 4) {
            $House = new HouseQiugou($house_query);
        } elseif ($house_type == 5) {
            $House = new HouseNew($house_query);
        } elseif ($house_type == 7) {
            $House = new Job($house_query);
        } else {
            $returnData['msg'] = '参数错误';
            $returnData['error'] = 1;
            break;
        }

        //是否是已删除或已成交的房源
        $condition = array('id' => $id, 'is_checked in (0,1)', 'is_delete = 0');
        $dataInfo = $House->table($House->tName)->where($condition)->one();
        if ($house_type == 7) {
            $column_title = '招聘信息';
            $field_id_name = 'job_id';
        } else {
            $column_title = '房源';
            $field_id_name = 'house_id';
        }
        if (empty($dataInfo)) {
            $returnData['msg'] = $column_title . '不存在或已被删除';
            $returnData['error'] = 1;
            break;
        }

        if ($house_type == 7) {
            if ($dataInfo['status'] == 1) {
                $returnData['msg'] = $column_title . '已删除';
                $returnData['error'] = 1;
                break;
            }
            if ($dataInfo['status'] == 5) {
                $returnData['msg'] = $column_title . '已失效，不允许删除';
                $returnData['error'] = 1;
                break;
            }
        } else {
            if ($dataInfo['house_status'] == 1) {
                $returnData['msg'] = $column_title . '已删除';
                $returnData['error'] = 1;
                break;
            }
            if ($dataInfo['house_status'] == 5) {
                $returnData['msg'] = $column_title . '已成交，不允许删除';
                $returnData['error'] = 1;
                break;
            }
        }
        $sms_config = GetConfig('sms');
        $mobile_auth = $House->table($House->tNameMobleAuth)->where($field_id_name . ' = ' . $id)->one();
        if (time() - $mobile_auth['sms_send_time'] > $sms_config['expire_time']) {
            $returnData['msg'] = '验证码已过有效期，请重新获取';
            $returnData['error'] = 1;
            break;
        }
        if ($mobile_auth['mobile_auth_code'] != $auth_code) {
            $returnData['msg'] = '验证码不正确，请重新填写';
            $returnData['error'] = 1;
            break;
        }
        //设置房源为电话删除状态
        if ($house_type == 7) {
            $fields_array = array(
                'status' => 1,
                'is_delete' => 1
            );
        } else {
            $fields_array = array(
                'house_status' => 1,
                'is_delete' => 1
            );
        }
        //$result = $House->updatePhoneStatus($id, 1);
        $result = $House->updateHouse($id, $fields_array);
        if ($result) {
            //todo 设置验证码为无效状态，考虑是否清除数据
            $data = array(
                'mobile_auth_code' => '',
                'sms_send_time' => 0
            );
            $House->table($House->tNameMobleAuth)->del($field_id_name . ' = ' . $id);

            //更新投诉举报列表状态
            if ($member_db = 'x') {
                $Report = new Report($member_query);
            } else {
                $Report = new Report($query);
            }
            $condition = array(
                'house_id' => $id,
                'house_type' => $house_type
            );
            $data = array(
                'report_status' => 1,
                'admin_name' => '用户删除',
                'check_time' => time()
            );
            $Report->table($Report->tName)->where($condition)->save($data);
            $House->addCheckLog($id, 1, 0, '用户删除', '');

            //房源刷新计划更新
            if ($member_db == 'x') {
                //取得房源会员信息
                $member_info = $member_query->table('member')->field('id, house_refresh_count, user_level_id')->where('id = ' . $dataInfo['mid'])->one();

                /*if ($member_info) {
                    //房源已经自动刷新的次数
                    $HouseRefreshPlan = new HouseRefreshPlan($member_query);
                    $condition = array(
                        'house_id' => $id,
                        'house_type' => $house_type,
                        'is_refreshed' => 1
                    );
                    $auto_refreshed_count = $HouseRefreshPlan->getCount($condition);
                }

                //删除对应的房源刷新计划
                $condition = array(
                    'house_id' => $id,
                    'house_type' => $house_type
                );
                $HouseRefreshPlan->delete($condition);

                if ($member_info) {
                    //会员等级信息
                    $user_level_info = $member_query->table('user_level')->where('id = ' . $member_info['user_level_id'])->cache(true)->one();

                    //记录已自动刷新次数到会员刷新次数中
                    if ($auto_refreshed_count > 0) {
                        $HouseRefreshPlan->refresh($dataInfo['mid'], $user_level_info['house_day_refresh'], $member_info['house_refresh_count'], $auto_refreshed_count);
                    }
                    //刷新房源计划任务
                    $HouseRefreshPlan->updateRefreshPlan($member_id, $user_level_info['house_day_refresh'], ($member_info['house_refresh_count'] + $auto_refreshed_count));
                }*/
            }
            $returnData['msg'] = '成功删除';
            $returnData['error'] = 0;
        } else {
            $returnData['msg'] = '删除失败，请联系管理员';
            $returnData['error'] = 1;
        }
		break;
    case 'check_is_collected':
        $member_db = trim($_POST['member_db']);
        if ($member_db == 'x') {
            $member_db = WEBHOSTID;
        } else {
            $member_db = intval($member_db);
        }
        $house_id = intval($_POST['house_id']);
        $house_type = intval($_POST['house_type']);

        if (empty($house_id) || !in_array($house_type, array(1, 2, 3, 4, 5, 7))) {
            $returnData['is_collected'] = 0;
            break;
        }

        //判断用户是否已登录
        $member = new Member($member_query);
        $member_id = $member->getAuthInfo('id');
        if (empty($member_id)) {
            $returnData['is_collected'] = 0;
            break;
        }

        //判断用户是否已收藏
        $condition = array(
            'mid' => $member_id,
            'is_member' => $member_db,
            'house_id' => $house_id,
            'house_type' => $house_type
        );
        $collect_info = $member_query->table('member_collect')->where($condition)->one();
        if ($collect_info) {
            $returnData['is_collected'] = 1;
        } else {
            $returnData['is_collected'] = 0;
        }
        break;
    case 'send_sms':
        //判断验证码是否正确
        /*require_once(dirname(dirname(__FILE__)) . '/common/lib/classes/Verify.class.php');
        $Verify = new Verify();
        $verify_code = trim($_GET['verify_code']);
        $check_result = $Verify->check($verify_code);
        if ($check_result == false) {
            $returnData['msg'] = '验证码错误';
            $returnData['error'] = 3;
            break;
        }*/

        //验证码检测
        require_once(dirname(dirname(__FILE__)) . '/common/lib/aliyun-php-sdk-afs/aliyun-php-sdk-core/Config.php');

        //YOUR ACCESS_KEY、YOUR ACCESS_SECRET请替换成您的阿里云accesskey id和secret
        $afs_config = GetConfig('afs');
        $iClientProfile = DefaultProfile::getProfile($afs_config['region_id'], $afs_config['access_key_id'], $afs_config['access_key_secret']);
        $client = new DefaultAcsClient($iClientProfile);
        $iClientProfile->addEndpoint($afs_config['endpoint_name'], $afs_config['region_id'], $afs_config['product'], $afs_config['domain']);

        $request = new afs\Request\V20180112\AnalyzeNvcRequest();
        $request->setData($_GET['verify_code']);    // 必填参数，从前端获取getNVCVal函数的值
        $json_data = array(
            '200' => 'PASS',
            '400' => 'NC',
            '600' => 'SC',
            '800' => 'BLOCK'
        );
        $request->setScoreJsonStr(json_encode($json_data));// 根据自己需求设置，跟前端一致

        $response = $client->getAcsResponse($request);
        $returnData['code'] = intval($response->BizCode);
        //$returnData['code'] = 600;
        if ($returnData['code'] != 100 && $returnData['code'] != 200) {
            $returnData['msg'] = '验证信息错误';
            $returnData['error'] = 1;
            break;
        }

        //手机号不能为空
        $mobile = trim($_GET['mobile']);
        if (is_mobile($mobile) == false) {
            $returnData['msg'] = '手机号码不正确';
            $returnData['error'] = 1;
            break;
        }

        //检测是否已发送过短信
        $sql = "select * from fke_auth_mobile where mobile = '$mobile' order by id desc";
        $mobile_info = $query->getValue($sql);
        $auth_code = GetAuthCode('num', 6);
        if ($mobile_info) {
            //短信发送配置
            $sms_config = GetConfig('sms');
            if (time() - $mobile_info['send_time'] < $sms_config['interval']) {
                $returnData['msg'] = '发送时间间隔太短，请过一会儿再发';
                $returnData['error'] = 1;
                break;
            } else {
                //更新新信息
                $fields_array = array(
                    'authcode' => $auth_code,
                    'is_checked' => 0,
                    'check_error_times' => 0,
                    'check_time' => 0,
                    'send_time' => time()
                );
                $result = $query->update('fke_auth_mobile', $fields_array, 'id=' . $mobile_info['id']);
            }
        } else {
            //插入新信息
            $fields_array = array(
                'mobile' => $mobile,
                'authcode' => $auth_code,
                'is_checked' => 0,
                'check_error_times' => 0,
                'check_time' => 0,
                'send_time' => time()
            );
            $result = $query->insert('fke_auth_mobile', $fields_array);
        }
        //发送短信
        $sms_config = GetConfig('sms');
        require_once(dirname(dirname(__FILE__)) . '/common/lib/classes/SMS.class.php');
        $SMS = SMS::getInstance($sms_config, $query);
        $send_data = array(
            'mobile' => $mobile,
            'template_code' => $sms_config['template_code_register'],
            'code' => $auth_code
        );
        $result = $SMS->SendSMS($send_data);
        $returnData['error'] = $result['error'];
        $returnData['msg'] = $result['msg'];
        if ($returnData['error'] == 1) {
            break;
        }
        $returnData['msg'] = '已发送验证码到您的手机';
        $returnData['error'] = 0;
        break;
    case 'check_wechat':
        //检测用户是否已登录
        $member_id = $member->getAuthInfo('id');
        if (empty($member_id)) {
            $returnData['msg'] = '您还未登录，请先登录';
            $returnData['error'] = 1;
            break;
        }
        $wechat = trim($_GET['wechat']);
        if (CheckUserName($wechat) == false) {
            $returnData['msg'] = '请填写正确的微信号码';
            $returnData['error'] = 1;
            break;
        }
        $memberInfo = $member->getInfo($member_id, '*', true);
        if (is_mobile($wechat) && $memberInfo['mobile'] != $wechat) {
            $returnData['msg'] = '您填写的手机号码与注册的手机号码不一致';
            $returnData['error'] = 1;
            break;
        }
        $returnData['msg'] = '通过验证';
        $returnData['error'] = 0;
        break;
    case 'check_qq':
        //检测用户是否已登录
        $member_id = $member->getAuthInfo('id');
        if (empty($member_id)) {
            $returnData['msg'] = '您还未登录，请先登录';
            $returnData['error'] = 1;
            break;
        }
        $qq = trim($_GET['qq']);
        if (CheckUserName($qq) == false) {
            $returnData['msg'] = '请填写正确的QQ号码';
            $returnData['error'] = 1;
            break;
        }
        $memberInfo = $member->getInfo($member_id, '*', true);
        if (is_mobile($qq) && $memberInfo['mobile'] != $qq) {
            $returnData['msg'] = '您填写的手机号码与注册的手机号码不一致！';
            $returnData['error'] = 1;
            break;
        }
        $returnData['msg'] = '通过验证';
        $returnData['error'] = 0;
        break;
    case 'delete_search_history':
        $id = intval($_POST['id']);
        remove_search_history_list($id);
        $returnData['error'] = 0;
        $returnData['msg'] = '删除成功';
        break;
    case 'clear_search_history':
        setcookie('search_list', null, 0, '/', $cfg['page']['basehost']);
        $returnData['error'] = 0;
        $returnData['msg'] = '清除成功';
        break;
    case 'add_click':
        $member_db = trim($_POST['member_db']);
        $type = intval($_POST['type']);
        $id = intval($_POST['id']);
        if ($member_db == 'x') {
            $db_query = $member_query;
            $cache_prefix = MEMCACHE_PREFIX;
        } elseif ($member_db == 4) {
            $db_query = new DbQueryForMysql(GetConfig('member_db_' . $member_db));
            $cache_prefix = MEMCACHE_ZFY_PREFIX;
        } elseif ($member_db > 0) {
            $db_query = new DbQueryForMysql(GetConfig('member_db_' . $member_db));
            $cache_prefix = 'click_' . $member_db;
        } else {
            $db_query = $query;
            $cache_prefix = '';
        }

        if ($type == 1) {
            $Obj = new HouseRent($db_query);
            $cache_key = $cache_prefix . 'h_r_click_' . $id;
        } elseif ($type == 2) {
            $Obj = new HouseSell($db_query);
            $cache_key = $cache_prefix . 'h_s_click_' . $id;
        } elseif ($type == 3) {
            $Obj = new HouseQiuzu($db_query);
            $cache_key = $cache_prefix . 'h_qz_click_' . $id;
        } elseif ($type == 4) {
            $Obj = new HouseQiugou($db_query);
            $cache_key = $cache_prefix . 'h_qg_click_' . $id;
        } elseif ($type == 5) {
            $Obj = new HouseNew($db_query);
            $cache_key = $cache_prefix . 'h_n_click_' . $id;
        } elseif ($type == 6) {
            $Obj = new News($query);
            $cache_key = 'news_click_' . $id;
        } elseif ($type == 7) {
            $Obj = new Job($db_query);
            $cache_key = 'job_click_' . $id;
        } else {
            $returnData['error'] = 1;
            $returnData['msg'] = '参数错误';
            break;
        }

        $result = $Obj->addClick($id);
        if ($result === false) {
            $returnData['error'] = 1;
            $returnData['msg'] = '计数错误';
        } else {
            //判断是存在缓存
            $Cache = $Cache::getInstance();
            $click = $Cache->get($cache_key);
            if ($click) {
                $Cache->increment($cache_key, 1);
                $click++;
            } else {
                $click_data = $Obj->getClick($id);
                if ($type == 6) {
                    $click = intval($click_data['click']);
                } else {
                    if ($member_db > 0) {
                        $click = GetClick($click_data['click_num'], $click_data['click_virtual'], $click_data['created'], 600);
                    } else {
                        $click = GetClick($click_data['click_num'], $click_data['click_virtual']);
                    }
                    $Sphinx = Sphinx::getInstance();
                    $Sphinx->UpdateAttributes('search_news,search_news_delta', array('click'), array($id => array($click)));
                }
                $Cache->set($cache_key, $click, MEMCACHE_MAX_EXPIRETIME);
            }

            $returnData['error'] = 0;
            $returnData['click'] = intval($click);
        }
        break;
    case 'map_point':
        $member_db = trim($_POST['member_db']);
        $house_id = intval($_POST['house_id']);
        $house_type = intval($_POST['house_type']);
        $lng = trim($_POST['lng']);
        $lat = trim($_POST['lat']);

        if (empty($lng) || empty($lat)) {
            $returnData['error'] = 1;
            $returnData['msg'] = 'parameter error';
            break;
        }

        if ($member_db == 'x') {
            $house_query = $member_query;
        } elseif ($member_db > 0) {
            $house_query = new DbQueryForMysql(GetConfig('member_db_' . $member_db));
        } else {
            $house_query = $query;
        }

        if ($house_type == 1) {
            $House = new HouseRent($house_query);
        } elseif ($house_type == 2) {
            $House = new HouseSell($house_query);
        } elseif ($house_type == 5) {
            $House = new HouseNew($house_query);
        } else {
            $returnData['error'] = 1;
            $returnData['msg'] = 'type error';
            break;
        }

        //取得房源信息
        //$query->debug = true;
        $house_info = $House->getInfo($house_id);
        if (empty($house_info['borough_name']) || empty($house_info['cityarea_id']) || empty($house_info['cityarea2_id'])) {
            $returnData['error'] = 1;
            $returnData['msg'] = 'not found house';
            break;
        }

        //判断当前小区是否已存储了坐标
        $condition = array(
            'region_id' =>$house_info['cityarea_id'],
            //'street_id' =>$house_info['cityarea2_id'],
            'title' => $house_info['borough_name'],
            'title_crc32' => array(
                $house_info['borough_name'], 'CRC32'
            )
        );

        $residential_quarters_data = $query->table('residential_quarters')->where($condition)->one();
        if (!empty($residential_quarters_data)) {
            $returnData['error'] = 1;
            break;
        }

        //取得省市ID信息
        if ($house_info['cityarea_id']) {
            $region_key = 'region_' . $house_info['cityarea_id'];
            $region_info = $query->table('region')->where('region_id = ' . intval($house_info['cityarea_id']))->cache($region_key)->one();
        }
        $city_id = intval($region_info['parent_id']);

        if ($city_id) {
            $region_key = 'region_' . $city_id;
            $region_info = $query->table('region')->where('region_id = ' . intval($city_id))->cache($region_key)->one();
        }
        $province_id = $region_info['parent_id'];

        //存储到数据库
        $data = array(
            'title' => $house_info['borough_name'],
            'title_crc32' => array(
                $house_info['borough_name'], 'CRC32'
            ),
            'province_id' => $province_id,
            'city_id' => $city_id,
            'region_id' => $house_info['cityarea_id'],
            'street_id' => $house_info['cityarea2_id'],
            'address' => $house_info['house_address'],
            'lng' => $lng,
            'lat' => $lat,
            'add_time' => time()
        );
        $query->table('residential_quarters')->save($data);
        $returnData['error'] = 0;
        $returnData['msg'] = 'success';
        break;
}
echo json_encode($returnData, JSON_UNESCAPED_UNICODE);