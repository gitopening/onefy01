<?php

/**
 * 官方网站 http://www.hictech.cn
 * Copyright (c) 2018 河北海聪科技有限公司 All rights reserved.
 * Licensed (http://www.hictech.cn/licenses)
 * Author : net <geow@qq.com>
 */
class UserLevel extends Model
{
    public $table_name = 'user_level';

    public function getUserLevelList()
    {
        return $this->table('user_level')->order('sort_order ASC, id ASC')->all();
    }
    public function getPrice($member_info, $user_level_id, $price_index = 0)
    {
        $tmp_user_level_list = $this->getUserLevelList();
        $user_level = array();
        foreach ($tmp_user_level_list as $item) {
            $item['price_rule'] = json_decode($item['price_rule'], true);
            $user_level[$item['id']] = $item;
        }

        if (empty($user_level[$user_level_id])) {
            return array(
                'error' => 1,
                'price' => 0,
                'expire_time' => '',
                'msg' => '会员级别信息不存在'
            );
        }

        //当前会员剩余过期时间
        $now_timestamp = time();
        $left_timestamp = $member_info['user_level_expire_time'] - $now_timestamp;

        if (($user_level_id < $member_info['user_level_id'] && $left_timestamp > 0) || $user_level_id < 2) {
            return array(
                'error' => 1,
                'price' => 0,
                'expire_time' => '',
                'msg' => '选择的会员等级不能低于当前会员等级'
            );
        }

        $price = 0;
        $month = 0;
        $tips = '';

        if ($user_level_id == $member_info['user_level_id'] && $left_timestamp > 0) {
            $tips = '续费';
        } elseif ($user_level_id == $member_info['user_level_id'] || $left_timestamp < 0) {
            $tips = '开通';
        } else {
            $tips = '升级';
        }
        $tips .= '“' . $user_level[$user_level_id]['level_name'] . "”" . $user_level[$user_level_id]['price_rule'][$price_index]['rule_title'];

        if ($user_level_id == $member_info['user_level_id'] || $left_timestamp < 0) {
            //开通VIP（包括过期的会员重新开通）
            $price = $user_level[$user_level_id]['price_rule'][$price_index]['rule_price'];
            $month = $user_level[$user_level_id]['price_rule'][$price_index]['rule_time'];
            //$month = $month * 2;  //每买一个月送一个月
            if ($left_timestamp < 0) {
                $expire_time = strtotime('+ ' . $month . ' month', $now_timestamp);
            } else {
                $expire_time = strtotime('+ ' . $month . ' month', $member_info['user_level_expire_time']);
            }
        } elseif ($user_level_id > $member_info['user_level_id']) {
            $upgrade_price_rule = $user_level[$user_level_id]['price_rule'];
            $month = $upgrade_price_rule[$price_index]['rule_time'];
            //$month = $month * 2;  //每买一个月送一个月

            //会员升级功能，到期时间为当前时间加上要开通的时间
            //会员升级，所选升级时间不能小于上次会员开通时剩余的时间
            if ($left_timestamp > (strtotime('+ ' . $month . ' month', $now_timestamp) - $now_timestamp)) {
                return array(
                    'error' => 1,
                    'price' => 0,
                    'expire_time' => '',
                    'msg' => '您选择的时间不能小于当前会员到期剩余时间'
                );
            }

            //剩余月份
            $user_expire_date = MyDate('Y-m-d', $member_info['user_level_expire_time']);
            $now_date = MyDate('Y-m-d', $now_timestamp);
            $date_1 = explode('-', $user_expire_date);
            $date_2 = explode('-', $now_date);
            $user_left_month = ($date_1[0] - $date_2[0]) * 12 + ($date_1[1] - $date_2[1]);

            //剩余费用
            $rule_data_count = count($user_level[$member_info['user_level_id']]['price_rule']) - 1;
            $rule_data_count = $rule_data_count < 0 ? 1 : $rule_data_count;
            foreach ($user_level[$member_info['user_level_id']]['price_rule'] as $key => $item) {
                if ($item['rule_time'] >= $user_left_month) {
                    $rule_data_count = $key;
                    break;
                }
            }
            $member_rule_data = $user_level[$member_info['user_level_id']]['price_rule'][$rule_data_count];
            $year_price = $member_rule_data['rule_price'] / $member_rule_data['rule_time'] * 12;
            $left_money = $left_timestamp / (365 * 86400) * $year_price;

            //新升级会员到有效期需要的费用
            $price = $upgrade_price_rule[$price_index]['rule_price'] - $left_money; //新开通时间价格减去上次剩余的费用
            $price = $price < 0 ? 0 : $price;
            $expire_time = strtotime('+ ' . $month . ' month', $now_timestamp);
        }

        //春节前每三个月送一个月，2022-02-01为春节日期
        if ($expire_time > 0 && time() < strtotime('2022-02-02')) {
            //$give_month = intval($month / 3);
            $give_month = $month;
            $expire_time = strtotime('+' . $give_month . ' month', $expire_time);
            if ($give_month > 0) {
                $tips .= '，赠送' . $give_month . '个月';
            }
        }

        $expire_time = MyDate('Y年m月d日', $expire_time);
        $tips .= '，购买成功后到期日为：' . $expire_time;

        /*if ($month > 0) {
            $tips .= '<span style="color: red;">(赠送' . $month / 2 . '个月有效期)</span>';
        }*/

        return array(
            'error' => 0,
            'price' => round($price, 2),
            'month' => $month,
            'level_name' => $user_level[$user_level_id]['level_name'],
            'expire_time' => $expire_time,
            'tips' => $tips
        );
    }
}