<?php

/**
 * 官方网站 http://www.hictech.cn
 * Copyright (c) 2017 河北海聪科技有限公司 All rights reserved.
 * Licensed (http://www.hictech.cn/licenses)
 * Author : net <geow@qq.com>
 */
class HouseRefreshPlan extends Model
{
    public $tName = 'house_refresh_plan';

    public function getCount($condition, $master = false)
    {
        return $this->table($this->tName)->where($condition)->master($master)->count();
    }

    public function delete($condition)
    {
        return $this->table($this->tName)->where($condition)->del();
    }

    public function getAll($condition, $master = false)
    {
        return $this->table($this->tName)->where($condition)->master($master)->all();
    }

    /**
     * @param $member_id 用户ID
     * @param $house_day_refresh_count 日最大刷新数
     * @param $member_refreshed_count 用户已刷新的房源数
     * @return bool
     */
    public function updateRefreshPlan($member_id, $house_day_refresh_count, $member_refreshed_count)
    {
        //需要启用的房源刷新计划任务
        $enable_refresh_count = $house_day_refresh_count - $member_refreshed_count;
        $data = array(
            'is_enable' => 1,
        );
        $condition = array(
            'mid' => $member_id,
            'is_enable' => 0
        );
        $order = 'refresh_plan_time ASC, id ASC';
        $result = $this->table($this->tName)->where($condition)->order($order)->limit($enable_refresh_count)->save($data);
        if ($result === false) {
            return false;
        }

        //需要禁止的房源刷新计划任务
        $condition = array(
            'mid' => $member_id
        );
        $house_refresh_plan_count = $this->getCount($condition, true);
        $unable_refresh_count = $house_refresh_plan_count - $enable_refresh_count;
        if ($unable_refresh_count > 0) {
            $data = array(
                'is_enable' => 0
            );
            $order = 'refresh_plan_time DESC, id DESC';
            $condition = array(
                'mid' => $member_id,
                'is_enable' => 1
            );
            $result = $this->table($this->tName)->where($condition)->order($order)->limit($unable_refresh_count)->save($data);
            if ($result === false) {
                return false;
            }
        }
        return true;
    }

    public function refresh($member_id, $house_day_refresh_count, $member_refreshed_count, $add_refresh_count = 0)
    {
        if ($house_day_refresh_count <= $member_refreshed_count) {
            return array(
                'error' => 1,
                'msg' => '您今日已达到最大发布/刷新/编辑次数，可以开通/升级“房源推广套餐”发布更多房源！'
            );
        }

        if ($add_refresh_count > ($house_day_refresh_count - $member_refreshed_count)) {
            return array(
                'error' => 1,
                'msg' => '刷新失败，您选择刷新的房源条数大于今天剩余次数！'
            );
        }

        $now_time = time();
        $data = array(
            'house_refresh_count' => array('house_refresh_count + ' . $add_refresh_count),
            'house_refresh_time' => $now_time
        );
        $result = $this->db->table('member')->where('id = ' . $member_id)->save($data);
        if ($result === false) {
            return array(
                'error' => 1,
                'msg' => '刷新失败'
            );
        } else {
            return array(
                'error' => 0,
                'msg' => '刷新成功'
            );
        }
    }

    public function refreshJob($member_id, $day_refresh_count, $member_refreshed_count, $add_refresh_count = 0)
    {
        if ($day_refresh_count <= $member_refreshed_count) {
            return array(
                'error' => 1,
                'msg' => '您今日已达到最大发布/刷新/编辑次数！'
            );
        }

        if ($add_refresh_count > ($day_refresh_count - $member_refreshed_count)) {
            return array(
                'error' => 1,
                'msg' => '您已达到最大招聘信息库存量，删除部分招聘信息后发布！'
            );
        }

        $now_time = time();
        $data = array(
            'job_refresh_count' => array('job_refresh_count + ' . $add_refresh_count),
            'job_refresh_time' => $now_time
        );
        $result = $this->db->table('member')->where('id = ' . $member_id)->save($data);
        if ($result === false) {
            return array(
                'error' => 1,
                'msg' => '刷新失败'
            );
        } else {
            return array(
                'error' => 0,
                'msg' => '刷新成功'
            );
        }
    }

    public function getRefreshPlan($refresh_times, $start_time, $end_time, $now_timestamp = 0)
    {
        if ($end_time <= $start_time) {
            return false;
        }
        $now_timestamp = $now_timestamp == 0 ? time() : $now_timestamp;
        $interval_seconds = round(($end_time - $start_time) / $refresh_times);       //刷新周期

        //生成刷新方案
        $today_seconds = $now_timestamp - strtotime(MyDate('Y-m-d', $now_timestamp));  //今日从0点开始到现在的秒数
        $seconds = $today_seconds - intval(MyDate('H', $now_timestamp)) * 3600;  //当前时间去除小时后剩余的秒数
        $refresh_plan_time = array();
        for ($i = 0; $i < $refresh_times; $i++) {
            //生成的时间
            $refresh_time = $start_time + $interval_seconds * $i + $seconds;
            //如果当时时间已过，则延长1天过期时间
            if ($refresh_time < $today_seconds) {
                $expired = 1;
            } else {
                $expired = 0;
            }
            $refresh_plan_time[] = array(
                'seconds' => $refresh_time,
                'time' => $this->getTime($refresh_time),
                'expired' => $expired
            );
        }

        //生成刷新时间
        /*for ($i = 0; $i < $refresh_times; $i++) {
            $refresh_time = rand(($start_time + $i * $interval_seconds), ($start_time + ($i + 1) * $interval_time));
            $refresh_plan_time[] = array(
                'seconds' => $refresh_time,
                'time' => $this->getTime($refresh_time)
            );
        }*/

        return $refresh_plan_time;
    }

    public function getRandRefreshPlan($refresh_times, $start_time, $end_time)
    {
        if ($end_time <= $start_time) {
            return false;
        }
        $now_timestamp = time();
        $interval_seconds = round(($end_time - $start_time) / $refresh_times);       //刷新周期

        //生成刷新方案
        $today_seconds = $now_timestamp - strtotime(MyDate('Y-m-d', $now_timestamp));  //今日从0点开始到现在的秒数
        $seconds = rand(0, 3600);  //每小时的秒数随机
        $refresh_plan_time = array();
        for ($i = 0; $i < $refresh_times; $i++) {
            //生成的时间
            $refresh_time = $start_time + $interval_seconds * $i + $seconds;
            //如果当时时间已过，则延长1天过期时间
            if ($refresh_time < $today_seconds) {
                $expired = 1;
            } else {
                $expired = 0;
            }
            $refresh_plan_time[] = array(
                'seconds' => $refresh_time,
                'time' => $this->getTime($refresh_time),
                'expired' => $expired
            );
        }
        return $refresh_plan_time;
    }

    public function getTime($seconds)
    {
        $hour = 0;
        $minute = 0;
        if ($seconds > 3600) {
            $hour = intval($seconds / 3600);
            $seconds = $seconds - $hour * 3600;
        }
        if ($seconds > 60) {
            $minute = intval($seconds / 60);
        }
        return sprintf('%02d', $hour) . ':' . sprintf('%02d', $minute);
    }
}