<?php
//根据网站名取得所在地区分类并缓存
function get_region_enum($parent_id, $orderby = 'letter')
{
    global $query;
    if (!is_array($parent_id)) {
        $parent_id = array(intval($parent_id));
    }
    $parent_id = implode(',', $parent_id);
    if (empty($parent_id)) {
        return false;
    }
    if ($orderby == 'letter') {
        $order = "first_letter asc, sort_order asc";
    } elseif ($orderby == 'sort') {
        $order = "sort_order asc, region_id asc";
    }
    $Cache = Cache::getInstance();
    $key = 'region_enum_p_' . $parent_id . '_o_' . $orderby;
    $data = $Cache->get($key);
    if (empty($data)) {
        $data = array();
        $data_list = $query->table('region')->field('region_id, region_name, first_letter')->where("parent_id in ($parent_id)")->order($order)->all();
        foreach ($data_list as $item) {
            $data[$item['region_id']] = $item;
        }
        if ($data) {
            $Cache->set($key, $data, MEMCACHE_MAX_EXPIRETIME);
        }
    }
    return $data;
}

//取得当前网站信息
function get_city_info($url_name)
{
    global $query;
    if (!empty($url_name) && $url_name != 'www') {
        $condition = array(
            'url_name' => $url_name,
            'is_open' => 1
        );
        return $query->table('city_website')->field('`id`, `city_name`, `province_id`, `city_id`, `is_hot`, `url_name`, `sale_price`, `rent_price`, `hezu_price`, `xzl_cz_price`, `xzl_cs_price`, `sp_cz_price`, `sp_cs_price`, `parking_cz_price`, `parking_cs_price`, `sale_total_area`, `rent_total_area`, `hezu_total_area`, `xzl_cz_total_area`, `xzl_cs_total_area`, `sp_cz_total_area`, `sp_cs_total_area`, `parking_cz_total_area`, `parking_cs_total_area`')->where($condition)->cache(true)->one();
    } else {
        return false;
    }
}

//取得当前网站信息
function get_city_info_by_id($site_id)
{
    global $query;
    return $query->field('`id`, `city_name`, `province_id`, `city_id`, `url_name`')->table('city_website')
        ->where('id = ' . intval($site_id))->cache(true)->one();
}

//生成指定位数的验证码
function GetAuthCode($type, $length = 32)
{
    if ($type == 'str') {
        $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    } elseif ($type == 'num') {
        $str = '0123456789';
    } else {
        $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    }
    $strLength = strlen($str);
    $tmp = '';
    for ($i = 0; $i < $length; $i++) {
        $rand = rand(0, $strLength - 1);
        $tmp .= $str[rand(0, $strLength - 1)];
    }
    return $tmp;
}

//取得热门城市
function get_hot_city()
{
    global $query;
    $condition = array(
        'is_hot' => 1,
        'is_open' => 1
    );
    return $query->field('id, city_name, url_name')->table('city_website')->where($condition)->order('is_order asc')->all();
}

//取得过滤区间数组
function get_filter_array($string)
{
    $filter_option = array();
    $filter = explode(',', $string);
    foreach ($filter as $val) {
        $array = explode('|', $val);
        $filter_option[$array[0]] = $array[1];
    }
    return $filter_option;
}

//取汉字拼音首字母
function getfirstchar($s0)
{
    $firstchar_ord = ord(strtoupper($s0{0}));
    if (($firstchar_ord >= 65 and $firstchar_ord <= 91) || ($firstchar_ord >= 48 && $firstchar_ord <= 57)) {
        return $s0{0};
    }
    $s = iconv('UTF-8', 'gb2312', $s0);
    // 	$s=$s0;
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776) return 'B';
    if ($asc >= -19775 && $asc <= -19219) return 'C';
    if ($asc >= -19218 && $asc <= -18711) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if ($asc >= -18526 && $asc <= -18240) return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if ($asc >= -17922 && $asc <= -17418) return 'H';
    if ($asc >= -17417 && $asc <= -16475) return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641) return 'L';
    if ($asc >= -15640 && $asc <= -15166) return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631) return 'P';
    if ($asc >= -14630 && $asc <= -14150) return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319) return 'S';
    if ($asc >= -13318 && $asc <= -12839) return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if ($asc >= -12556 && $asc <= -11848) return 'X';
    if ($asc >= -11847 && $asc <= -11056) return 'Y';
    if ($asc >= -11055 && $asc <= -10247) return 'Z';
    return null;
}

//组合筛选链接地址
function build_url($url, $param)
{
    return str_replace('{curentparam}', $param, $url);
}

//判断手机号是否正确
function is_mobile($str)
{
    if (strlen($str) != 11) {
        return false;
    }
    $pattern = '/^1[3456789]{1}\d{9}$/';
    return (bool)preg_match($pattern, $str);
}

function is_email($str)
{
    $pattern = '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/';
    return (bool)preg_match($pattern, $str);
}

//todo 取得当前栏目名和栏目类型
function GetColumnData()
{
    global $cityInfo;
    $column = array();
    switch ($_GET['column']) {
        case 'rent_old':
        case 'rent':
            $column['column'] = 'rent';
            $column['column_type'] = 1;
            $column['price_option'] = $cityInfo['rent_price'];
            $column['totalarea_option'] = $cityInfo['rent_total_area'];
            $column['house_title'] = '租房';
            $column['column_title'] = '出租房';
            $column['column_position_title'] = '住宅出租';
            break;
        case 'xzlcz':
            $column['column'] = 'xzl';
            $column['column_type'] = 2;
            $column['price_option'] = $cityInfo['xzl_cz_price'];
            $column['totalarea_option'] = $cityInfo['xzl_cz_total_area'];
            $column['house_title'] = '写字楼出租';
            $column['column_title'] = '写字楼出租';
            $column['column_position_title'] = '写字楼出租';
            break;
        case 'spcz':
            $column['column'] = 'sp';
            $column['column_type'] = 3;
            $column['price_option'] = $cityInfo['sp_cz_price'];
            $column['totalarea_option'] = $cityInfo['sp_cz_total_area'];
            $column['house_title'] = '商铺出租';
            $column['column_title'] = '商铺出租';
            $column['column_position_title'] = '商铺出租';
            break;
        case 'cwcz':
            $column['column'] = 'cw';
            $column['column_type'] = 4;
            $column['price_option'] = $cityInfo['parking_cz_price'];
            $column['totalarea_option'] = $cityInfo['parking_cz_total_area'];
            $column['house_title'] = '车位出租';
            $column['column_title'] = '车位出租';
            $column['column_position_title'] = '车位出租';
            break;
        case 'cwzr':
            $column['column'] = 'cw';
            $column['column_type'] = 4;
            $column['price_option'] = $cityInfo['parking_cz_price'];
            $column['totalarea_option'] = $cityInfo['parking_cz_total_area'];
            $column['house_title'] = '车位转让';
            $column['column_title'] = '车位转让';
            $column['column_position_title'] = '车位转让';
            break;
        case 'cfcz':
            $column['column'] = 'cf';
            $column['column_type'] = 16;
            $column['price_option'] = $cityInfo['xzl_cz_price'];
            $column['totalarea_option'] = $cityInfo['parking_cz_total_area'];
            $column['house_title'] = '厂房出租';
            $column['column_title'] = '厂房出租';
            $column['column_position_title'] = '厂房出租';
            break;
        case 'cfzr':
            $column['column'] = 'cf';
            $column['column_type'] = 16;
            $column['price_option'] = $cityInfo['xzl_cz_price'];
            $column['totalarea_option'] = $cityInfo['parking_cz_total_area'];
            $column['house_title'] = '厂房转让';
            $column['column_title'] = '厂房转让';
            $column['column_position_title'] = '厂房转让';
            break;
        case 'ckcz':
            $column['column'] = 'ck';
            $column['column_type'] = 17;
            $column['price_option'] = $cityInfo['xzl_cz_price'];
            $column['totalarea_option'] = $cityInfo['parking_cz_total_area'];
            $column['house_title'] = '仓库出租';
            $column['column_title'] = '仓库出租';
            $column['column_position_title'] = '仓库出租';
            break;
        case 'ckzr':
            $column['column'] = 'ck';
            $column['column_type'] = 17;
            $column['price_option'] = $cityInfo['xzl_cz_price'];
            $column['totalarea_option'] = $cityInfo['parking_cz_total_area'];
            $column['house_title'] = '仓库转让';
            $column['column_title'] = '仓库转让';
            $column['column_position_title'] = '仓库转让';
            break;
        case 'sale':
            $column['column'] = 'sale';
            $column['column_type'] = 1;
            $column['price_option'] = $cityInfo['sale_price'];
            $column['totalarea_option'] = $cityInfo['sale_total_area'];
            $column['house_title'] = '二手房';
            $column['column_title'] = '二手房出售';
            $column['column_position_title'] = '二手房出售';
            break;
        case 'xzlcs':
            $column['column'] = 'xzl';
            $column['column_type'] = 2;
            $column['price_option'] = $cityInfo['xzl_cs_price'];
            $column['totalarea_option'] = $cityInfo['xzl_cs_total_area'];
            $column['house_title'] = '写字楼出售';
            $column['column_title'] = '写字楼出售';
            $column['column_position_title'] = '写字楼出售';
            break;
        case 'spcs':
            $column['column'] = 'sp';
            $column['column_type'] = 3;
            $column['price_option'] = $cityInfo['sp_cs_price'];
            $column['totalarea_option'] = $cityInfo['sp_cs_total_area'];
            $column['house_title'] = '商铺出售';
            $column['column_title'] = '商铺出售';
            $column['column_position_title'] = '商铺出售';
            break;
        case 'cwcs':
            $column['column'] = 'cw';
            $column['column_type'] = 4;
            $column['price_option'] = $cityInfo['parking_cs_price'];
            $column['totalarea_option'] = $cityInfo['parking_cs_total_area'];
            $column['house_title'] = '车位出售';
            $column['column_title'] = '车位出售';
            $column['column_position_title'] = '车位出售';
            break;
        case 'cfcs':
            $column['column'] = 'cf';
            $column['column_type'] = 16;
            $column['price_option'] = $cityInfo['xzl_cs_price'];
            $column['totalarea_option'] = $cityInfo['parking_cs_total_area'];
            $column['house_title'] = '厂房出售';
            $column['column_title'] = '厂房出售';
            $column['column_position_title'] = '厂房出售';
            break;
        case 'ckcs':
            $column['column'] = 'ck';
            $column['column_type'] = 17;
            $column['price_option'] = $cityInfo['xzl_cs_price'];
            $column['totalarea_option'] = $cityInfo['parking_cs_total_area'];
            $column['house_title'] = '仓库出售';
            $column['column_title'] = '仓库出售';
            $column['column_position_title'] = '仓库出售';
            break;
        case 'qiuzu':
            $column['column'] = 'rent';
            $column['column_type'] = 1;
            //$column['price_option'] = $cityInfo['rent_price'];
            //$column['totalarea_option'] = $cityInfo['rent_total_area'];
            $column['house_title'] = '房屋';
            $column['column_title'] = '房屋求租';
            $column['column_position_title'] = '住宅求租';
            break;
        case 'xzlqz':
            $column['column'] = 'xzl';
            $column['column_type'] = 2;
            //$column['price_option'] = $cityInfo['rent_price'];
            //$column['totalarea_option'] = $cityInfo['rent_total_area'];
            $column['house_title'] = '写字楼';
            $column['column_title'] = '写字楼求租';
            $column['column_position_title'] = '写字楼求租';
            break;
        case 'spqz':
            $column['column'] = 'sp';
            $column['column_type'] = 3;
            //$column['price_option'] = $cityInfo['rent_price'];
            //$column['totalarea_option'] = $cityInfo['rent_total_area'];
            $column['house_title'] = '商铺';
            $column['column_title'] = '商铺求租';
            $column['column_position_title'] = '商铺求租';
            break;
        case 'cwqz':
            $column['column'] = 'cw';
            $column['column_type'] = 4;
            $column['price_option'] = $cityInfo['parking_cz_price'];
            $column['totalarea_option'] = $cityInfo['parking_cz_total_area'];
            $column['house_title'] = '车位';
            $column['column_title'] = '车位求租';
            $column['column_position_title'] = '车位求租';
            break;
        case 'cfqz':
            $column['column'] = 'cf';
            $column['column_type'] = 16;
            $column['price_option'] = $cityInfo['parking_cz_price'];
            $column['totalarea_option'] = $cityInfo['parking_cz_total_area'];
            $column['house_title'] = '厂房';
            $column['column_title'] = '厂房求租';
            $column['column_position_title'] = '厂房求租';
            break;
        case 'ckqz':
            $column['column'] = 'ck';
            $column['column_type'] = 17;
            $column['price_option'] = $cityInfo['parking_cz_price'];
            $column['totalarea_option'] = $cityInfo['parking_cz_total_area'];
            $column['house_title'] = '仓库';
            $column['column_title'] = '仓库求租';
            $column['column_position_title'] = '仓库求租';
            break;
        case 'qiugou':
            $column['column'] = 'sale';
            $column['column_type'] = 1;
            $column['price_option'] = $cityInfo['sale_price'];
            $column['totalarea_option'] = $cityInfo['sale_total_area'];
            $column['house_title'] = '二手房';
            $column['column_title'] = '二手房求购';
            $column['column_position_title'] = '二手房求购';
            break;
        case 'xzlqg':
            $column['column'] = 'xzl';
            $column['column_type'] = 2;
            $column['price_option'] = $cityInfo['sale_price'];
            $column['totalarea_option'] = $cityInfo['sale_total_area'];
            $column['house_title'] = '写字楼';
            $column['column_title'] = '写字楼求购';
            $column['column_position_title'] = '写字楼求购';
            break;
        case 'spqg':
            $column['column'] = 'sp';
            $column['column_type'] = 3;
            $column['price_option'] = $cityInfo['sale_price'];
            $column['totalarea_option'] = $cityInfo['sale_total_area'];
            $column['house_title'] = '商铺';
            $column['column_title'] = '商铺求购';
            $column['column_position_title'] = '商铺求购';
            break;
        case 'cwqg':
            $column['column'] = 'cw';
            $column['column_type'] = 4;
            $column['price_option'] = $cityInfo['parking_cs_price'];
            $column['totalarea_option'] = $cityInfo['parking_cs_total_area'];
            $column['house_title'] = '车位';
            $column['column_title'] = '车位求购';
            $column['column_position_title'] = '车位求购';
            break;
        case 'cfqg':
            $column['column'] = 'cf';
            $column['column_type'] = 16;
            $column['price_option'] = $cityInfo['parking_cs_price'];
            $column['totalarea_option'] = $cityInfo['parking_cs_total_area'];
            $column['house_title'] = '厂房';
            $column['column_title'] = '厂房求购';
            $column['column_position_title'] = '厂房求购';
            break;
        case 'ckqg':
            $column['column'] = 'ck';
            $column['column_type'] = 17;
            $column['price_option'] = $cityInfo['parking_cs_price'];
            $column['totalarea_option'] = $cityInfo['parking_cs_total_area'];
            $column['house_title'] = '仓库';
            $column['column_title'] = '仓库求购';
            $column['column_position_title'] = '仓库求购';
            break;
        case 'new':
            $column['column'] = 'new';
            $column['column_type'] = 0;
            $column['price_option'] = $cityInfo['sale_price'];
            $column['totalarea_option'] = $cityInfo['sale_total_area'];
            $column['house_title'] = '新房';
            $column['column_title'] = '新房';
            $column['column_position_title'] = '新房';
            break;
        case 'zzx':
            $column['column'] = 'new';
            $column['column_type'] = 1;
            $column['price_option'] = $cityInfo['sale_price'];
            $column['totalarea_option'] = $cityInfo['sale_total_area'];
            $column['house_title'] = '住宅新房';
            $column['column_title'] = '住宅新房';
            $column['column_position_title'] = '住宅新房';
            break;
        case 'xzlx':
            $column['column'] = 'new';
            $column['column_type'] = 2;
            $column['price_option'] = $cityInfo['xzl_cs_price'];
            $column['totalarea_option'] = $cityInfo['xzl_cs_total_area'];
            $column['house_title'] = '写字楼新房';
            $column['column_title'] = '写字楼新房';
            $column['column_position_title'] = '写字楼新房';
            break;
        case 'spx':
            $column['column'] = 'new';
            $column['column_type'] = 3;
            $column['price_option'] = $cityInfo['sp_cs_price'];
            $column['totalarea_option'] = $cityInfo['sp_cs_total_area'];
            $column['house_title'] = '商铺新房';
            $column['column_title'] = '商铺新房';
            $column['column_position_title'] = '商铺新房';
            break;
        default:

    }
    return $column;
}

function cn_substr_utf8($str, $length, $start = 0)
{
    if (strlen($str) < $start + 1) {
        return '';
    }
    preg_match_all("/./su", $str, $ar);
    $str = '';
    $tstr = '';

    //为了兼容mysql4.1以下版本,与数据库varchar一致,这里使用按字节截取
    for ($i = 0; isset($ar[0][$i]); $i++) {
        if (strlen($tstr) < $start) {
            $tstr .= $ar[0][$i];
        } else {
            if (strlen($str) < $length + strlen($ar[0][$i])) {
                $str .= $ar[0][$i];
            } else {
                break;
            }
        }
    }
    if (strlen($str) > $length) {
        $str .= '...';
    }
    return $str;
}

function create_letter_region_option($region, $value = 0)
{
    $tmp = '';
    if (empty($value)) {
        $tmp = '<option value="0" selected="selected">请选择</option>';
    } else {
        $tmp = '<option value="0">请选择</option>';
    }
    $first_letter = '';
    $region_count = count($region);
    foreach ($region as $key => $item) {
        if ($item['first_letter'] != $first_letter) {
            if ($key == 0) {
                $tmp .= '<optgroup label="' . $item['first_letter'] . '">';
            } else {
                $tmp .= '</optgroup><optgroup label="' . $item['first_letter'] . '">';
            }

            $first_letter = $item['first_letter'];
        }
        if ($item['region_id'] == $value) {
            $tmp .= '<option value="' . $item['region_id'] . '" selected="selected">' . $item['region_name'] . '</option>';
        } else {
            $tmp .= '<option value="' . $item['region_id'] . '">' . $item['region_name'] . '</option>';
        }
        if ($region_count == ($key + 1)) {
            $tmp .= '</optgroup>';
        }
    }
    return $tmp;
}

function update_house_memcache($house_type, $house_id)
{
    if (empty($house_type) || empty($house_id)) {
        return false;
    }
    if (!is_array($house_id)) {
        $house_id = array($house_id);
    }
    $Cache = Cache::getInstance();
    foreach ($house_id as $item) {
        $Cache->delete('house_' . $house_type . '_' . $item);
        $Cache->delete('house_' . $house_type . '_pic_' . $item);
        $Cache->delete('house_' . $house_type . '_update_' . $item);
        //删除会员当前房源缓存
        $Cache->delete(MEMCACHE_PREFIX . 'member_house_' . $house_type . '_' . $item);
        $Cache->delete(MEMCACHE_PREFIX . 'member_house_' . $house_type . '_pic_' . $item);
        $Cache->delete(MEMCACHE_PREFIX . 'member_house_' . $house_type . '_update_' . $item);
    }
    return true;
}

function update_news_memcache($ids)
{
    if (empty($ids)) {
        return false;
    }
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    $Cache = Cache::getInstance();
    foreach ($ids as $id) {
        $Cache->delete('news_' . $id);
        //删除会员当前房源缓存
        $Cache->delete(MEMCACHE_PREFIX . 'member_news_' . $id);
    }
    return true;
}

function update_member_house_memcache($house_type, $house_id)
{
    if (empty($house_type) || empty($house_id)) {
        return false;
    }
    if (!is_array($house_id)) {
        $house_id = array($house_id);
    }
    $Cache = Cache::getInstance();
    foreach ($house_id as $item) {
        $Cache->delete(MEMCACHE_PREFIX . 'member_house_' . $house_type . '_' . $item);
        $Cache->delete(MEMCACHE_PREFIX . 'member_house_' . $house_type . '_update_' . $item);
        $Cache->delete(MEMCACHE_PREFIX . 'member_house_' . $house_type . '_pic_' . $item);
    }
    return true;
}

function update_job_memcache($ids)
{
    if (empty($ids)) {
        return false;
    }
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    $Cache = Cache::getInstance();
    foreach ($ids as $id) {
        $Cache->delete(MEMCACHE_PREFIX . 'member_job_' . $id);
        $Cache->delete(MEMCACHE_PREFIX . 'member_job_update_' . $id);
        $Cache->delete(MEMCACHE_PREFIX . 'member_job_pic_' . $id);
    }
    return true;
}

function update_sphinx_cache($index, $house_id, $fields_array)
{
    $Sphinx = Sphinx::getInstance();
    $fields = array();
    $value = array();
    foreach ($fields_array as $key => $val) {
        $fields[] = $key;
        $value[] = $val;
    }
    $sphinx_values = array();
    if (is_array($house_id)) {
        foreach ($house_id as $v) {
            $sphinx_values[$v] = $value;
        }
    } else {
        $sphinx_values[$house_id] = $value;
    }
    $Sphinx->UpdateAttributes($index, $fields, $sphinx_values);
}

//todo 索引更新问题
function update_cache($house_type, $house_id, $fields_array, $member_db = 0)
{
    if ($member_db == 1) {
        //memcache
        update_member_house_memcache($house_type, $house_id);
        //sphinx
        $sphinx_index = 'member_house_' . $house_type . ', member_house_' . $house_type . '_delta';
    } else {
        //memcache
        update_house_memcache($house_type, $house_id);
        //sphinx
        $sphinx_index = 'search_house_' . $house_type . ', search_house_' . $house_type . '_delta';
    }
    update_sphinx_cache($sphinx_index, $house_id, $fields_array);
    return true;
}

function update_all_website_cache($house_type, $house_id)
{
    global $query, $cfg;
    $house_id_str = implode(', ', $house_id);
    $websiet_list = $query->field('id, basehost')->table('web_config')->where("basehost <> '{$cfg['domain']}'")->cache(true)->all();
    foreach ($websiet_list as $item) {
        //发送通知
        if ($item['basehost'] == '01fy.cn') {
            $api = '//master.' . $item['basehost'] . '/api/update_cache.php?action=updatehousecache&key=' . UPDATE_CACHE_KEY . '&house_type=' . $house_type . '&house_id=' . $house_id_str;
        } else {
            $api = '//www.' . $item['basehost'] . '/api/update_cache.php?action=updatehousecache&key=' . UPDATE_CACHE_KEY . '&house_type=' . $house_type . '&house_id=' . $house_id_str;
        }
        $result = file_get_contents($api);
    }
}

function house_source_url($house)
{
    global $cfg;
    $city = get_city_info_by_id($house['city_website_id']);
    return '//' . $city['url_name'] . '.' . $cfg['page']['basehost'];
}

function get_house_title($house_info, $cityarea_option, $cityarea2_list, $column_title)
{
    return $house_info['house_title'];

    if ($house_info['mid'] > 0) {
        $house_title = $house_info['house_title'];
    } else {
        if (empty($house_info['house_address']) && empty($house_info['borough_name'])) {
            $house_title = strip_tags($house_info['house_title']) . $cityarea_option[$house_info['cityarea_id']]['region_name'] . $cityarea2_list[$house_info['cityarea2_id']]['region_name'] . $column_title;
        } else {
            $middle_column_title = $column_title == '二手房' ? '二手房出售' : $column_title;
            $house_title = strip_tags($house_info['house_address']) . strip_tags($house_info['borough_name']) . $middle_column_title . $cityarea_option[$house_info['cityarea_id']]['region_name'] . $cityarea2_list[$house_info['cityarea2_id']]['region_name'] . $column_title;
        }
//    if ($house_info['mtype'] == 2){
//        $house_title = $house_title.'(个人)';
//    }
    }
    return $house_title;
}

function get_new_house_title($house_info, $cityarea_option, $cityarea2_list, $column_title, $type)
{
    return $house_info['house_title'];

    if ($house_info['mid'] > 0) {
        $house_title = $house_info['house_title'];
    } else {
        if ($type == 'qz') {
            $house_title = $house_info['house_title'] . '求租' . $cityarea_option[$house_info['cityarea_id']]['region_name'] . $cityarea2_list[$house_info['cityarea2_id']]['region_name'] . $column_title;
        } elseif ($type == 'qg') {
            $house_title = $house_info['house_title'] . $cityarea_option[$house_info['cityarea_id']]['region_name'] . $cityarea2_list[$house_info['cityarea2_id']]['region_name'] . '求购' . $column_title;
        }
    }
    return $house_title;
}

function get_range_str($start, $end, $unit, $tags = '<strong>{content}</strong>')
{
    if ($start && $end) {
        return str_replace('{content}', $start . '-' . $end, $tags) . $unit;
    } else {
        if ($start == 0 && $end > 0) {
            return str_replace('{content}', $end, $tags) . $unit . '以下';
        } elseif ($start > 0 && $end == 0) {
            return str_replace('{content}', $start, $tags) . $unit . '以上';
        } else {
            return str_replace('{content}', '不限', $tags);
        }
    }
}

function get_unit_type($unit_type)
{
    $unit = '';
    switch ($unit_type) {
        case 1:
            $unit = '元/月';
            break;
        case 2:
            $unit = '元/㎡·天';
            break;
        case 3:
            $unit = '元/天';
            break;
        case 4:
            $unit = '万元';
            break;
        default:
            $unit = '元/月';
    }
    return $unit;
}

function time_tran($the_time)
{
    $now_time = time();
    $show_time = $the_time;
    $dur = $now_time - $show_time;
    switch ($dur) {
        case $dur < 0:
            return MyDate('m-d H:i', $the_time);
            break;
        case $dur < 60:
            return $dur . '秒前';
            break;
        case $dur < 3600:
            return floor($dur / 60) . '分钟前';
            break;
        case $dur < 86400:
            return floor($dur / 3600) . '小时前';
            break;
        case $dur < 259200:
            return floor($dur / 86400) . '天前';
            break;
        default:
            return MyDate('m-d H:i', $the_time);
    }
}

function time2Units($time)
{
    if ($time < 0) {
        return '刚刚';
    }
    $year = floor($time / 60 / 60 / 24 / 365);
    $time -= $year * 60 * 60 * 24 * 365;
    $month = floor($time / 60 / 60 / 24 / 30);
    $time -= $month * 60 * 60 * 24 * 30;
    $week = floor($time / 60 / 60 / 24 / 7);
    $time -= $week * 60 * 60 * 24 * 7;
    $day = floor($time / 60 / 60 / 24);
    $time -= $day * 60 * 60 * 24;
    $hour = floor($time / 60 / 60);
    $time -= $hour * 60 * 60;
    $minute = floor($time / 60);
    $time -= $minute * 60;
    $second = $time;
    $elapse = '';

    $unitArr = array('年' => 'year', '个月' => 'month', '周' => 'week', '天' => 'day',
        '小时' => 'hour', '分钟' => 'minute', '秒' => 'second'
    );

    foreach ($unitArr as $cn => $u) {
        if ($$u > 0) {
            $elapse = $$u . $cn . '前';
            break;
        }
    }

    return $elapse;
}

//返回格林威治标准时间
function MyDate($format = 'Y-m-d H:i:s', $timest = 0)
{
    global $cfg_cli_time;
    $addtime = $cfg_cli_time * 3600;
    if (empty($format)) {
        $format = 'Y-m-d H:i:s';
    }
    return gmdate($format, $timest + $addtime);
}

//todo 读取并显示广告,缓存到内存
function GetAdList($adId, $query, $js = 0)
{
    global $cfg, $cityInfo;
    $city_website_id = intval($cityInfo['id']);
    $updateTime = $cfg['tmp']['updaetime'];    //缓存时间（秒）
    //没有缓存或缓存已过期则生成缓存文件
    $adbody = '';
    if (!empty($adId)) {
        $condition = array(
            'id' => $adId,
            'website_id' => WEBHOSTID
        );
        $adPlace = $query->table('ads_place')->where($condition)->cache(true)->one();
        $condition = array(
            'place_id' => $adId,
            'city_website_id' => $city_website_id,
            'status' => 0
        );
        $adData = $query->table('ads')->where($condition)->order('isorder desc,id desc')->cache(true)->all();
        if (empty($adData)) {
            $condition = array(
                'place_id' => $adId,
                'city_website_id' => 0,
                'status' => 0
            );
            $adData = $query->table('ads')->where($condition)->order('isorder desc,id desc')->cache(true)->all();
        }
        if (count($adData) > 0) {
            switch ($adPlace['ads_option']) {
                case '1':    //只取最后添加的广告
                    $adbody = getadbody($adData[0], $js);
                    break;
                case '2':    //取全部广告
                    $adbody = '';
                    foreach ($adData as $v) {
                        $adbody .= getadbody($v, $js);
                    }
                    break;
                case '3':    //随机抽取一个广告
                    $adCount = count($adData);
                    $randnum = rand(1, $adCount);
                    $adbody = getadbody($adData[$randnum], $js);
                    break;
            }
        }
    }
    return $adbody;
}

function getadbody($adinfo, $js = 0)
{
    $returnStr = '';
    // 	if ($adinfo['from_date']<time() && $adinfo['to_date']>time()){
    if (true) {
        switch ($adinfo['ad_type']) {
            case 'image':
                $returnStr = '<div class="adlist"><a href="' . $adinfo['link_url'] . '" target="_blank"><img alt="' . $adinfo['alt'] . '" src="//www.soufy.cn' . $adinfo['image_url'] . '" width="" height="" /></a></div>';
                $returnStr = formatStr($returnStr);
                break;
            case 'flash':
                $returnStr = '<div class="adlist"><embed type="application/x-shockwave-flash" src="//img.soufy.cn' . $adinfo['flash_url'] . '" bgcolor="#ffffff" wmode="opaque" quality="autohigh" ></embed></div>';
                $returnStr = formatStr($returnStr);
                break;
            case 'text':
                $returnStr = '<div class="adlist"><a href="' . $adinfo['link_url'] . '" target="_blank">' . $adinfo['text'] . '</a></div>';
                $returnStr = formatStr($returnStr);
                break;
            case 'code':
                $returnStr = '<div class="adlist">' . $adinfo['code'] . '</div>';
                if ($js == 1) {
                    $returnStr = formatStr($returnStr);
                }
                break;
        }
    }
    return $returnStr;
}

function formatStr($str)
{
    $str = str_replace('"', '\"', $str);
    $str = str_replace("\r", "\\r", $str);
    $str = str_replace("\n", "\\n", $str);
    $str = "<script type=\"text/javascript\">document.write(\"{$str}\");</script>";
    return $str;
}

//取得投诉原因
function GetReportReason($reasonId)
{
    switch ($reasonId) {
        case '1':
            $str = '中介冒充个人';
            break;
        case '2':
            $str = '电话被冒用';
            break;
        case '3':
            $str = '房源已成交';
            break;
        case '4':
            $str = '房源不存在';
            break;
        case '5':
            $str = '价格不真实';
            break;
        default:
            $str = '其它';
    }
    return $str;
}

//取得IP
function getclientip($type = 0, $adv = false)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

function print_rr($var)
{
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

//刷新房源信息
function RefreshHoseInfo($ids, $userID)
{
    global $member_query, $webConfig, $page;
    //取得今天所刷新次数和总合
    $memberInfo = $member_query->field('id, user_type, day_refresh, count_datetime, sum_refresh')->table('member')->where('id = ' . $userID)->one();
    if ($memberInfo['user_type'] == 1) {
        $dayMaxRefresh = 30;
    } else {
        $dayMaxRefresh = 3;
    }
    $now_time = time();
    if (MyDate('Y-m-d', $now_time) != MyDate('Y-m-d', $memberInfo['count_datetime'])) {
        $data = array(
            'day_refresh' => 0,
            'count_datetime' => $now_time
        );
        $member_query->table('member')->where('id = ' . $userID)->save($data);
        $leftDayRefresh = $dayMaxRefresh;
    } else {
        $leftDayRefresh = $dayMaxRefresh - $memberInfo['day_refresh'];
    }
    if ($leftDayRefresh <= 0) {
        $page->back('您今日已达到最大发布/刷新/编辑次数，可以开通/升级“房源推广套餐”发布更多房源！');
        exit;
    }

    if (is_array($ids)) {
        $nAddCount = count($ids);
    } else {
        $arrID = explode(',', $ids);
        $nAddCount = count($arrID);
    }
    if ($nAddCount > $leftDayRefresh) {
        $page->back('刷新不成功，您选择刷新的房源条数大于今天剩余次数');
        exit;
    } else {
        $data = array(
            'day_refresh' => array('day_refresh + ' . $nAddCount),
            'sum_refresh' => array('sum_refresh + ' . $nAddCount),
            'count_datetime' => $now_time
        );
        $member_query->table('member')->where('id = ' . $userID)->save($data);
    }
}

//判断是否还能发布房源
function CheckAddHouseInfo($userID)
{
    global $member_query, $page;
    //取得今天所刷新次数和总合
    $memberInfo = $member_query->field('id, user_type, day_refresh, count_datetime, sum_refresh')->table('member')->where('id = ' . $userID)->one();
    // 	$leftSumRefresh = $webConfig['sum_refresh'] - $memberInfo['sum_refresh'];
    if ($memberInfo['user_type'] == 1) {
        $dayMaxRefresh = 30;
    } else {
        $dayMaxRefresh = 3;
    }
    /*$houseSale = new HouseSell($member_query);
    $houseRent = new HouseRent($member_query);
    $houseQiuzu = new HouseQiuzu($member_query);
    $houseQiugou = new HouseQiugou($member_query);
    $houseSaleCount = $houseSale->GetNoCacheCount('mid=' . $userID);
    $houserentCount = $houseRent->GetNoCacheCount('mid=' . $userID);
    $houseqiuzuCount = $houseQiuzu->GetNoCacheCount('mid=' . $userID);
    $houseqiugouCount = $houseQiugou->GetNoCacheCount('mid=' . $userID);
    $leftSumRefresh = $webConfig['sum_refresh'] - $houseSaleCount - $houserentCount - $houseqiuzuCount - $houseqiugouCount;
    if ($leftSumRefresh <= 0) {
        $page->back('您的房源信息库存数量已达上限，请删除出售中或已下架房源。');
        exit;
    }*/
    $now_time = time();
    if (MyDate('Y-m-d', $now_time) != MyDate('Y-m-d', $memberInfo['count_datetime'])) {
        $data = array(
            'day_refresh' => 0,
            'count_datetime' => $now_time
        );
        $member_query->table('member')->where('id = ' . $userID)->save($data);
        $leftDayRefresh = $dayMaxRefresh;
    } else {
        $leftDayRefresh = $dayMaxRefresh - $memberInfo['day_refresh'];
    }
    if ($leftDayRefresh <= 0) {
        $page->back('您今日已达到最大发布/刷新/编辑次数，可以开通/升级“房源推广套餐”发布更多房源！');
        exit;
    }
}

//utf-8编码转gbk编码
function utf8_to_gbk($arr)
{
    if (is_array($arr) && count($arr)) {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arrRs[mb_convert_encoding($key, 'GBK', 'UTF-8')] = utf8_to_gbk($value);
            } else {
                $arrRs[mb_convert_encoding($key, 'GBK', 'UTF-8')] = mb_convert_encoding($value, 'GBK', 'UTF-8');
            }
        }
        return $arrRs;
    }
    return 0;
}

//检测是否有非法信息
function CheckIllegalWords($str, $filterWords)
{
    foreach ($filterWords as $val) {
        if (strpos($str, $val) !== false) {
            return true;
        }
    }
    return false;
}

//根据网站链接取得当前网站ID
function GetCityWebsiteIdByURL($url_name, $city_name = '')
{
    global $page, $cfg;
    $domain = explode(':', $cfg['page']['basehost']);

    //手机端域名访问
    if ($url_name == 'm') {
        $cityInfo = get_city_info($city_name);
        if ($cityInfo['id']) {
            setcookie(COOKIE_PREFIX . 'cityid', $cityInfo['id'], (time() + 31536000), '/', $domain[0]);
            return $cityInfo;
        } elseif (!empty($city_name)) {
            header('Location://m.' . $cfg['page']['basehost'] . '/index.htm');
            exit();
        }
    } elseif (!empty($url_name) && $url_name != 'www') {
        $cityInfo = get_city_info($url_name);
        if ($cityInfo == false) {
            setcookie(COOKIE_PREFIX . 'cityid', '', -1, '/', $domain[0]);
            @header("http/1.1 404 not found");
            @header("status: 404 not found");
            $page->Rdirect('//www.' . $cfg['page']['basehost']);
            exit();
        }
        setcookie(COOKIE_PREFIX . 'cityid', $cityInfo['id'], (time() + 31536000), '/', $domain[0]);
        return $cityInfo;
    }
}

//跳转到当前站点
function JumpToCurrentWebsite()
{
    global $cfg, $page, $query;
    $url_name = str_replace('.' . $cfg['page']['basehost'], '', $_SERVER['HTTP_HOST']);
    $cityID = intval($_COOKIE[COOKIE_PREFIX . 'cityid']);
    $data_info = $query->table('city_website')->field('id, url_name')->where('id = ' . $cityID)->cache(true)->one();
    $jump_url_name = $data_info['url_name'];

    if ($url_name == 'www') {
        if (empty($jump_url_name)) {
            $page->Rdirect('//www.' . $cfg['page']['basehost'] . '/index.htm');
        } elseif (!empty($jump_url_name) && $jump_url_name != $url_name) {
            //跳到对应栏目
            $page->Rdirect('//' . $jump_url_name . '.' . $cfg['page']['basehost'] . $_SERVER['REQUEST_URI']);
        }
    } elseif ($url_name == 'm') {
        $city_name = $url_name == 'm' ? trim($_GET['city_name']) : '';
        if (empty($jump_url_name) && empty($city_name)) {
            $page->Rdirect('//m.' . $cfg['page']['basehost'] . '/index.htm');
        } elseif (!empty($jump_url_name) && empty($city_name)) {
            //跳到对应栏目
            $page->Rdirect('//m.' . $cfg['page']['basehost'] . '/' . $jump_url_name . $_SERVER['REQUEST_URI']);
        }
    } else {
        if (empty($jump_url_name) && empty($url_name)) {
            $page->Rdirect('//www.' . $cfg['page']['basehost'] . '/index.htm');
        }
    }
}

//添加到历史记录
function add_history_list($key, $datainfo, $member_db)
{
    global $cityInfo, $cfg;
    if (empty($datainfo)) {
        return false;
    }
    $_COOKIE[$key] = stripcslashes($_COOKIE[$key]);
    $data_list = empty($_COOKIE[$key]) ? array() : json_decode(gzuncompress($_COOKIE[$key]), true);
    //检测是否已存在同一信息
    foreach ($data_list as $item) {
        if ($item['id'] == $datainfo['id']) {
            return $data_list;
        }
    }
    if ($member_db == 'x') {
        $member_db = MEMBER_DB_INDEX;
    } else {
        $member_db = intval($member_db);
    }
    array_unshift($data_list, array(
        'id' => $datainfo['id'],
        'db' => $member_db,
        'mid' => $datainfo['mid']
    ));

    //限制最大数量
    $new_list = array();
    $length = count($data_list) < $cfg['history_list_number'] ? count($data_list) : $cfg['history_list_number'];
    for ($i = 0; $i < $length; $i++) {
        $new_list[] = $data_list[$i];
    }
    setcookie($key, gzcompress(json_encode($new_list), 9), (time() + 31536000), '/', $cityInfo['url_name'] . '.' . $cfg['page']['basehost']);
    return $new_list;
}

//取得历史记录
function get_history_list($key)
{
    $_COOKIE[$key] = stripcslashes($_COOKIE[$key]);
    $data_list = json_decode(gzuncompress($_COOKIE[$key]), true);
    return empty($data_list) ? array() : $data_list;
}

function AddBrowserCount($key)
{
    if (!in_array($key, array('sale', 'rent', 'xzlcz', 'xzlcs', 'spcz', 'cfcz', 'cfzr', 'cfcs', 'ckcz', 'ckzr', 'ckcs', 'new', 'xzlx', 'spx'))) {
        return false;
    }

    if (in_array($key, array('cfcz', 'cfzr', 'cfcs', 'ckcz', 'ckzr', 'ckcs'))) {
        $key = 'cfck';
    }

    if (in_array($key, array('new', 'zzx', 'xzlx', 'spx'))) {
        $key = 'new';
    }

    global $cfg;
    $now_time = time();
    $day_start_time = strtotime(MyDate('Y-m-d', $now_time));
    $expire_time = strtotime('+3 month', $day_start_time);
    $day_count_key = 'day_b_c';
    $day_browser_count = json_decode(gzuncompress(stripcslashes($_COOKIE[$day_count_key])), true);

    if (empty($day_browser_count) || (($now_time - $day_browser_count['time']) >= 86400)) {
        $day_browser_count = array(
            'time' => $day_start_time
        );
    }

    $day_browser_count[$key]++;
    setcookie($day_count_key, gzcompress(json_encode($day_browser_count), 9), $expire_time, '/', $cfg['page']['basehost']);
    return true;
}

function GetBrowserCount()
{
    $day_count_key = 'day_b_c';
    $day_browser_count = json_decode(gzuncompress(stripcslashes($_COOKIE[$day_count_key])), true);
    return $day_browser_count;
}

//保存搜索历史
function add_search_keyword_history($data, $length = 8, $key = 'search_list')
{
    global $cityInfo, $cfg;
    if (empty($data)) {
        return false;
    }
    $_COOKIE[$key] = stripcslashes($_COOKIE[$key]);
    $data_list = empty($_COOKIE[$key]) ? array() : json_decode(gzuncompress($_COOKIE[$key]), true);
    //检测是否已存在同一信息

    $new_list = array($data);
    if ($length > 1) {
        foreach ($data_list as $item) {
            if ($item == $data) {
                continue;
            }
            $new_list[] = $item;
            if (count($new_list) >= $length) {
                break;
            }
        }
    }

    //setcookie($key, gzcompress(json_encode($new_list), 9), (time() + 31536000), '/', $cityInfo['url_name'] . '.' . $cfg['page']['basehost']);
    setcookie($key, gzcompress(json_encode($new_list), 9), (time() + 31536000), '/', $cfg['page']['basehost']);
    return $new_list;
}

function get_search_history_list($key = 'search_list')
{
    $_COOKIE[$key] = stripcslashes($_COOKIE[$key]);
    $data_list = json_decode(gzuncompress($_COOKIE[$key]), true);
    return empty($data_list) ? array() : $data_list;
}
function remove_search_history_list($id, $key = 'search_list')
{
    global $cityInfo, $cfg;
    $_COOKIE[$key] = stripcslashes($_COOKIE[$key]);
    $data_list = json_decode(gzuncompress($_COOKIE[$key]), true);
    //移除元素
    array_splice($data_list, $id, 1);
    setcookie($key, gzcompress(json_encode($data_list), 9), (time() + 31536000), '/', $cfg['page']['basehost']);
    return empty($data_list) ? array() : $data_list;
}

function GetConfig($name = '')
{
    static $_config = array();
    if (empty($name)) {
        return $_config;
    }
    if (is_string($name)) {
        return $_config[$name];
    } elseif (is_array($name)) {
        $_config = array_merge($_config, $name);
        return true;
    }
}

function SaveDataToMemcache($sql, $db_query, $is_one = false, $cache_time = 0, $key = '')
{
    if (empty($key)){
        $key = md5($sql);
    }
    $Cache = Cache::getInstance();
    $data = $Cache->get($key);
    if (empty($data)){
        if ($is_one == true){
            $data = $db_query->getValue($sql);
        }else{
            $data = $db_query->select($sql);
        }
        if ($data){
            $cache_time = empty($cache_time) ? MEMCACHE_MAX_EXPIRETIME : $cache_time;
            $Cache->set($key, $data, $cache_time);
        }
    }
    return $data;
}

function GetHouseFloor($number, $max_number = 0)
{
    $str = '';
    if ($number == 10000) {
        $str = '低层';
    } elseif ($number == 10001) {
        $str = '中层';
    } elseif ($number == 10002) {
        $str = '高层';
    } else {
        if ($max_number > 2) {
            $average = $max_number / 3;
            if ($number <= $average) {
                $str = '低层';
            } elseif ($number > $average && $number <= $average * 2) {
                $str = '中层';
            } elseif ($number > $average * 2 && $number <= $max_number) {
                $str = '高层';
            } else {
                $str = '';
            }
        } else {
            $str = '';
        }
    }
    return $str;
}

function GetHouseThumb($house_thumb, $is_member = 0, $default_pic = '')
{
    global $webConfig;
    if (empty($house_thumb)) {
        return empty($default_pic) ? DEFAULT_HOUSE_PICTURE : $default_pic;
    } else {
        return $is_member == 0 ? IMG_HOST . '/upfile/'. $house_thumb :  '//www.' . $webConfig['basehost'] . '/upfile/'  . $house_thumb;
    }
}

function GetBrokerFace($broker_face, $default_pic = '/images/demoPhoto.jpg')
{
    if (empty($broker_face)) {
        return $default_pic;
    } else {
        return GetPictureUrl($broker_face, 6, MEMBER_DB_INDEX);
    }
}

//判断是否手机访问
function IsMobileRequest()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

function GetHouseTruthDegree($num = 0)
{
    $degree = 0;
    switch ($num) {
        case 0:
            $degree = rand(90, 95);
            break;
        case 1:
            $degree = rand(85, 89);
            break;
        case 2:
            $degree = rand(80, 84);
            break;
        case 3:
            $degree = rand(75, 79);
            break;
        case 4:
            $degree = rand(70, 74);
            break;
        case 5:
            $degree = rand(65, 69);
            break;
        case 6:
            $degree = rand(60, 64);
            break;
        default:
            $degree = 59;
    }
    return $degree;
}

function GetDayRefreshInfo($member_info)
{
    //房源信息统计,计算剩余多少房源
    if (in_array($member_info['user_type'], array(1, 3, 4))) {
        $dayMaxRefresh = 30;
    } else {
        $dayMaxRefresh = 3;
    }

    $now_time = time();
    if (MyDate('Y-m-d', $now_time) != MyDate('Y-m-d', $member_info['count_datetime'])) {
        $data = array(
            'day_refresh' => 0,
            'left_refresh' => $dayMaxRefresh
        );
    } else {
        $data = array(
            'day_refresh' => $member_info['day_refresh'],
            'left_refresh' => $dayMaxRefresh - $member_info['day_refresh']
        );
    }
    return $data;
}

function gmt_iso8601($time)
{
    $dtStr = date('c', $time);
    $mydatetime = new DateTime($dtStr);
    $expiration = $mydatetime->format(DateTime::ISO8601);
    $pos = strpos($expiration, '+');
    $expiration = substr($expiration, 0, $pos);
    return $expiration . 'Z';
}

function ajax_return($data)
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function GetPictureTypeFromString($image_data) {
    $hex_data = bin2hex(substr($image_data, 0, 2));
    switch ($hex_data) {
        case 'ffd8':
            $ext = 'jpg';
            break;
        case '8950':
            $ext = 'png';
            break;
        case '4749':
            $ext = 'gif';
            break;
        default :
            $ext = '';
    }
    return $ext;
}

function GetPictureUrl($url, $style = 1, $member_db = 0)
{
    if (empty($url)) {
        return '';
    }
    //取得链接后缀
    $tmp = explode('.', $url);
    $ext = end($tmp);
    unset($tmp[count($tmp) - 1]);
    $url = implode('.', $tmp);
    $url = str_replace('_thumb', '', $url);
    return IMG_HOST . '/upfile/' . $url . '-' . intval($style) . '-' . intval($member_db) . '.' . $ext;
}

function http_request($url, $data = array(), $timeout = 3)
{
    $ch = curl_init();
    if (is_array($data) && $data) {
        $formdata = http_build_query($data);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formdata);
    }

    $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
    if ($ssl) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($ch);
    return $result;
}

function FormatHousePrice($price, $decimals = 2)
{
    /*$pos = stripos($price, '.');
    if ($pos === false) {
        return $price;
    }
    return substr($price, 0, $pos + $decimals);*/
    return round($price, $decimals);
}

function str_length($str, $encoding = 'utf-8')
{
    return mb_strlen($str, $encoding);
}

function FormatHouseRoom($val)
{
    $val = intval($val);
    if ($val > 9) {
        return '9+';
    } else {
        return $val;
    }
}

function FormatHouseRoomType($val, $max = 5)
{
    $val = intval($val);
    if ($val > $max) {
        return $max . '+';
    } else {
        return $val;
    }
}

function CheckUserName($str)
{
    $pattern = '/^[a-zA-Z\d_\.,@]+$/i';
    return (bool)preg_match($pattern, $str);
}

function MobileHouseLink($url)
{
    //判断URL是电脑还是手机
    $parsed_url = parse_url($url);
    $regex = '/(\w+)\.(.*)/i';
    preg_match($regex, $parsed_url['host'], $matches);
    $url_name = $matches[1];
    if ($url_name == 'm') {
        return $url;
    } else {
        return $parsed_url['scheme'] . '://m.' . $matches[2] . '/' . $matches[1] . $parsed_url['path'];
    }
}

function PCHouseLink($url)
{
    //判断URL是电脑还是手机
    $parsed_url = parse_url($url);
    $regex = '/(\w+)\.(.*)/i';
    preg_match($regex, $parsed_url['host'], $matches);
    $url_name = $matches[1];
    $host = $matches[2];
    if ($url_name == 'm') {
        $regex = '/\/(\w+)\/(.*)/i';
        preg_match($regex, $parsed_url['path'], $matches);
        $url_name = $matches[1];
        return $parsed_url['scheme'] . '://' . $url_name . '.' . $host . '/' . $matches[2];
    } else {
        return $url;
    }
}

function WriteLog($content, $file = '')
{
    global $cfg;
    $file = empty($file) ? $cfg['path']['root'] . 'tmp/log.txt' : $file;
    $content = MyDate('Y-m-d H:i:s', time()) . '：' . $content . "\r\n";
    $fp = fopen($file, 'a');
    fwrite($fp, $content);
    fclose($fp);
    return true;
}

function stripcslashes_all($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $item) {
            $string[$key] = stripcslashes_all($item);
        }
    } else {
        $string = stripslashes($string);
    }
    return $string;
}

function ShowNoticePage($type, $title, $content = '')
{
    $msg = array(
        'type' => $type,
        'title' => $title,
        'content' => $content
    );
    header('Location:/notice.php?m=' . base64_encode(json_encode($msg)));
    exit();
}

function GetPaymentName($payment)
{
    switch ($payment) {
        case 1:
            $payment = '微信';
            break;
        case 2:
            $payment = '支付宝';
            break;
        default:
            $payment = '其它';
    }
    return $payment;
}

function GetSphinxIndex()
{
    $sphinx_config = GetConfig('sphinx');
    $prefix = 'search_house_';
    $m_prefix = $sphinx_config['member_index_prefix'] . 'member_house_';
    $m_zfy_prefix = $sphinx_config['zfy_index_prefix'] . 'member_house_';
    $index_array = array(
        'member_rent' => $m_prefix . 'rent,' . $m_prefix . 'rent_delta',
        'member_rent_old' => $m_prefix . 'rent_old',
        'member_sale' => $m_prefix . 'sale,' . $m_prefix . 'sale_delta',
        'member_new' => $m_prefix . 'new,' . $m_prefix . 'new_delta',
        'member_qiuzu' => $m_prefix . 'qiuzu,' . $m_prefix . 'qiuzu_delta',
        'member_qiugou' => $m_prefix . 'qiugou,' . $m_prefix . 'qiugou_delta',
        'member_job' => $sphinx_config['member_index_prefix'] . 'member_job,' . $sphinx_config['member_index_prefix'] . 'member_job_delta',
        'member' => $sphinx_config['member_index_prefix'] . 'member,' . $sphinx_config['member_index_prefix'] . 'member_delta',
        'news' => 'search_news,search_news_delta'
    );

    $index_array['search_rent'] = $prefix . 'rent,' . $prefix . 'rent_delta,' . $index_array['member_rent'];
    $index_array['search_rent_old'] = $prefix . 'rent_old,' . $index_array['member_rent_old'];
    $index_array['search_sale'] = $prefix . 'sale,' . $prefix . 'sale_delta,' . $index_array['member_sale'];
    $index_array['search_new'] = $index_array['member_new'];
    $index_array['search_qiuzu'] = $prefix . 'qiuzu,' . $prefix . 'qiuzu_delta,' . $index_array['member_qiuzu'];
    $index_array['search_qiugou'] = $prefix . 'qiugou,' . $prefix . 'qiugou_delta,' . $index_array['member_qiugou'];
    $index_array['search_job'] = $index_array['member_job'];

    $index_array['member_rent_home_promote'] = $m_prefix . 'rent_home_promote';
    $index_array['member_sale_home_promote'] = $m_prefix . 'sale_home_promote';
    $index_array['member_new_home_promote'] = $m_prefix . 'new_home_promote';

    $index_array['member_rent_banner_promote'] = $m_prefix . 'rent_banner_promote';
    $index_array['member_sale_banner_promote'] = $m_prefix . 'sale_banner_promote';
    $index_array['member_new_banner_promote'] = $m_prefix . 'new_banner_promote';

    return $index_array;
}

/**
 * session管理函数
 * @param string|array $name session名称 如果为数组则表示进行session设置
 * @param mixed $value session值
 * @return mixed
 */
function session($name = '', $value = '')
{
    $prefix = SESSION_PREFIX;
    if ('' === $value) {
        if ('' === $name) {
            // 获取全部的session
            return $prefix ? $_SESSION[$prefix] : $_SESSION;
        } elseif (0 === strpos($name, '[')) { // session 操作
            if ('[pause]' == $name) { // 暂停session
                session_write_close();
            } elseif ('[start]' == $name) { // 启动session
                session_start();
            } elseif ('[destroy]' == $name) { // 销毁session
                $_SESSION = array();
                session_unset();
                session_destroy();
            } elseif ('[regenerate]' == $name) { // 重新生成id
                session_regenerate_id();
            }
        } elseif (0 === strpos($name, '?')) { // 检查session
            $name = substr($name, 1);
            if (strpos($name, '.')) { // 支持数组
                list($name1, $name2) = explode('.', $name);
                return $prefix ? isset($_SESSION[$prefix][$name1][$name2]) : isset($_SESSION[$name1][$name2]);
            } else {
                return $prefix ? isset($_SESSION[$prefix][$name]) : isset($_SESSION[$name]);
            }
        } elseif (is_null($name)) { // 清空session
            if ($prefix) {
                unset($_SESSION[$prefix]);
            } else {
                $_SESSION = array();
            }
        } elseif ($prefix) { // 获取session
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                return isset($_SESSION[$prefix][$name1][$name2]) ? $_SESSION[$prefix][$name1][$name2] : null;
            } else {
                return isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;
            }
        } else {
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                return isset($_SESSION[$name1][$name2]) ? $_SESSION[$name1][$name2] : null;
            } else {
                return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
            }
        }
    } elseif (is_null($value)) { // 删除session
        if (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                unset($_SESSION[$prefix][$name1][$name2]);
            } else {
                unset($_SESSION[$name1][$name2]);
            }
        } else {
            if ($prefix) {
                unset($_SESSION[$prefix][$name]);
            } else {
                unset($_SESSION[$name]);
            }
        }
    } else { // 设置session
        if (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                $_SESSION[$prefix][$name1][$name2] = $value;
            } else {
                $_SESSION[$name1][$name2] = $value;
            }
        } else {
            if ($prefix) {
                $_SESSION[$prefix][$name] = $value;
            } else {
                $_SESSION[$name] = $value;
            }
        }
    }
    return null;
}

//加密字符串
function DataEncrypt($data, $key = '', $iv = '')
{
    if (empty($iv)) {
        $iv = md5($data);
    }
    $encrypted_data = array(
        'd' => openssl_encrypt($data, 'AES-256-CBC', $key, 0, substr($iv, 5, 16)),
        'iv' => $iv
    );
    return base64_encode(serialize($encrypted_data));
}

//解密字符串
function DataDecrypt($encrypted_data, $key = '', $iv = '')
{
    $encrypted_data = unserialize(base64_decode($encrypted_data));
    if (empty($iv)) {
        $iv = substr($encrypted_data['iv'], 5, 16);
    }
    $data = openssl_decrypt($encrypted_data['d'], 'AES-256-CBC', $key, 0, $iv);
    return ($data);
}

function GetHouseSupportIconList($house_support, $option)
{
    $house_support_array = explode(',', $house_support);
    $icon_array = array(
        1 => 'icon-air-conditioner',
        2 => 'icon-water-heater',
        3 => 'icon-tv',
        //6 => 'icon-elevator',
        8 => 'icon-bed',
        9 => 'icon-sofa',
        10 => 'icon-refrigerator',
        12 => 'icon-broadband',
        16 => 'icon-washing-machine',
        17 => 'icon-balcony',
        18 => 'icon-wardrobe',
        19 => 'icon-heating',
        20 => 'icon-cooking',
    );
    $support = array();
    foreach ($house_support_array as $item) {
        if (empty($icon_array[$item]) || empty($option[$item])) {
            continue;
        }
        $support[] = array(
            'caption' => $option[$item],
            'class' => $icon_array[$item]
        );
    }
    return $support;
}

function GetStoreHouseSupportIconList($house_support, $option)
{
    $house_support_array = explode(',', $house_support);
    $icon_array = array(
        1 => 'icon-broadband-2',
        2 => 'icon-tv-2',
        3 => 'icon-water',
        4 => 'icon-electric',
        5 => 'icon-phone',
        6 => 'icon-air-conditioner-2',
        7 => 'icon-heating-2',
        8 => 'icon-coal-gas',
    );
    $support = array();
    foreach ($house_support_array as $item) {
        if (empty($icon_array[$item]) || empty($option[$item])) {
            continue;
        }
        $support[] = array(
            'caption' => $option[$item],
            'class' => $icon_array[$item]
        );
    }
    return $support;
}

function GetDictList($value, $dict, $length = 0)
{
    if (!is_array($value)) {
        $value = explode(',', $value);
    }
    $list = array();
    $i = 0;
    foreach ($value as $item) {
        if (empty($dict[$item])) {
            continue;
        }
        $list[] = $dict[$item];
        $i++;
        if ($length > 0 && $i >= $length) {
            break;
        }
    }
    return $list;
}

function GetParkingType($type)
{
    switch ($type) {
        case 1:
            $type_name = '地上露天车位';
            break;
        case 2:
            $type_name = '地上车库';
            break;
        case 3:
            $type_name = '地下车库';
            break;
        default:
            $type_name = '';

    }
    return $type_name;
}

function GetHouseRentPrice($unit, $price, $month_price, $area)
{
    $data = array(
        'price' => $price,
        'month_price' => 0,
        'average_price' => 0
    );
    if ($price <= 0 || $month_price <= 0) {
        return $data;
    }
    switch ($unit) {
        case 1:
            //元/月
            $data['month_price'] = $month_price > 0 ? $month_price : $price;
            if ($area > 0) {
                $data['average_price'] = $month_price > 0 ? ($month_price / 30 / $area) : ($price / 30 / $area);
            }
            break;
        case 2:
            //元/㎡·天
            if ($area > 0) {
                $data['month_price'] = $month_price > 0 ? $month_price : ($price * 30 * $area);;
            }
            $data['average_price'] = $price;
            break;
        case 3:
            //元/天
            $data['month_price'] = $month_price > 0 ? $month_price : $price * 30;
            if ($area > 0) {
                $data['average_price'] = $month_price > 0 ? $month_price / $area : ($price / $area);
            }
            break;
        default:

    }
    foreach ($data as $key => $item) {
        $data[$key] = round($item, 2);
    }
    return $data;
}

function trip_indent($str)
{
    $str = str_ireplace('text-indent:2em', '', $str);
    $str = str_ireplace('text-indent: 2em', '', $str);
    $str = str_ireplace('　　', '', $str);
    return $str;
}

function GetClick($true_click, $virtual_click, $publish_time = 0, $virtual_click_use_time = 0)
{
    return $true_click + $virtual_click;
    /*if (time() - $publish_time >= $virtual_click_use_time) {
        return $true_click + $virtual_click;
    } else {
        return $true_click;
    }*/
}

function GetPictureExt($image_data) {
    $hex_data = bin2hex(substr($image_data, 0, 2));
    switch ($hex_data) {
        case 'ffd8':
            $ext = 'jpg';
            break;
        case '8950':
            $ext = 'png';
            break;
        case '4749':
            $ext = 'gif';
            break;
        default :
            $ext = '';
    }
    return $ext;
}

function IsWechatBrowser()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    } else {
        return false;
    }
}

function IsPCWechatBrowser()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'WindowsWechat') !== false) {
        return true;
    } else {
        return false;
    }
}

function GetUsedHouseStock($query, $member_id, $is_delete = '', $is_down = '')
{
    $condition = array(
        'mid' => $member_id,
    );

    if ($is_down !== '') {
        $condition['is_down'] = intval($is_down);
    }

    if ($is_delete !== '') {
        $condition['is_delete'] = intval($is_delete);
    }

    $house_new_count = $query->table('house_new')->where($condition)->count();
    $house_rent_count = $query->table('houserent')->where($condition)->count();
    $house_sale_count = $query->table('housesell')->where($condition)->count();
    $house_qiuzu_count = $query->table('qiuzu')->where($condition)->count();
    $house_qiugou_count = $query->table('qiugou')->where($condition)->count();

    return array(
        'count' => intval($house_new_count + $house_rent_count + $house_sale_count + $house_qiuzu_count + $house_qiugou_count),
        'new' => $house_new_count,
        'rent' => $house_rent_count,
        'sale' => $house_sale_count,
        'qiuzu' => $house_qiuzu_count,
        'qiugou' => $house_qiugou_count
    );
}

function GetUsedJobStock($query, $member_id, $is_down = 0, $is_delete = 0)
{
    $condition = array(
        'mid' => $member_id,
        'is_delete' => $is_delete,
        'is_down' => $is_down
    );
    return $query->table('job')->where($condition)->count();
}

function HTMLContentToText($content)
{
    $content = trim($content);
    $content = str_replace('<p>', "\r\n", $content);
    $content = str_replace('<br/>', "\r\n", $content);
    $content = str_replace('<br />', "\r\n", $content);
    $content = str_replace('</div>', "\r\n", $content);
    $content = str_replace('&nbsp;', ' ', $content);
    $content = strip_tags($content);
    return $content;
}

function TextContentToHTML($content)
{
    $content = trim($content);
    $content = str_replace(' ', '&nbsp;', $content);
    $content = str_replace("\r\n", '<br />', $content);
    $content = str_replace("\n\r", '<br />', $content);
    $content = str_replace("\n", '<br />', $content);
    return $content;
}