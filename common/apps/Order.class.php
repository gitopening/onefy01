<?php

/**
 * 官方网站 http://www.hictech.cn
 * Copyright (c) 2017 河北海聪科技有限公司 All rights reserved.
 * Licensed (http://www.hictech.cn/licenses)
 * Author : net <geow@qq.com>
 */
class Order extends Model
{
    public $tName = 'order';
    public $tNameExtend = 'order_extend';

    public function getCount($condition)
    {
        return $this->table($this->tName)->where($condition)->count();
    }

    public function getOrderTypeName($order_type, $order_sub_type = 0, $vip_type_name = '')
    {
        $order_type_name = '';
        switch ($order_type) {
            case 1:
                $order_type_name = '房源置顶';
                break;
            case 2:
                $order_type_name = '房源自动刷新';
                break;
            case 3:
                switch ($order_sub_type) {
                    case 1:
                        $order_type_name = '开通' . $vip_type_name;
                        break;
                    case 2:
                        $order_type_name = '续期' . $vip_type_name;
                        break;
                    case 3:
                        $order_type_name = '升级' . $vip_type_name;
                        break;
                    case 4:
                        $order_type_name = '升级' . $vip_type_name;
                        break;
                    default:
                        $order_type_name = '开通' . $vip_type_name;
                }
                break;
            case 4:
                $order_type_name = '首页推荐';
                break;
            case 5:
                $order_type_name = '首页封面';
                break;
            default:
                $order_type_name = '其它订单';
        }

        return $order_type_name;
    }

    public function payFinished($out_trade_no, $total_amount)
    {
        //取得订单信息，验证订单信息是否正确
        $order_info = $this->table($this->tName)->where('order_no = ' . $out_trade_no)->master(true)->one();
        if (empty($order_info)) {
            return array(
                'error' => 1,
                'msg' => '订单不存在'
            );
        }

        //判断金额是否正确
        if ($order_info['total_fee'] != $total_amount) {
            return array(
                'error' => 1,
                'msg' => '支付金额与订单金额不相符'
            );
        }

        //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
        if ($order_info['pay_status'] == -1 || $order_info['pay_status'] == 2) {
            return array(
                'error' => 1,
                'msg' => '已关闭和交易完成订单不能修改'
            );
        }

        //订单修改成交易完成
        $data = array(
            'payment' => 2,
            'pay_status' => 2 //交易完成，不能再修改订单状态
        );
        $result = $this->table($this->tName)->where('id = ' . $order_info['id'])->save($data);
        if ($result === false) {
            return array(
                'error' => 1,
                'msg' => '更新订单状态失败'
            );
        } else {
            return array(
                'error' => 0,
                'msg' => '订单状态更新成功'
            );
        }
    }

    //处理订单要执行的计划
    public function doOrderPlan($order_id)
    {

    }
}