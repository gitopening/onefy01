<?php

/**
 * 出租房源信息模型
 * net geow@qq.com
 */
class PromoteRecord extends Model
{
    public $tName = 'order';
    public $tNameExtend = 'order_extend';

    public function getCount($condition)
    {
        return $this->table($this->tName)->where($condition)->count();
    }
}