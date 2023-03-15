<?php

/**
 * 出租房源信息模型
 * net geow@qq.com
 */
class PushTask extends Model
{
    public $tName = 'push_task';
    public $tNameLog = 'push_log';

    /**
     * 保存房源信息
     * @param array $fieldData 数据数组
     * @return int
     */
    public function addTask($db_index, $publish_type, $column_type, $house_id, $is_sublet, $house_title, $source_id = 0)
    {
        $data = array(
            'db_index' => intval($db_index),
            'publish_type' => intval($publish_type),
            'column_type' => intval($column_type),
            'house_id' => intval($house_id),
            'is_sublet' => intval($is_sublet),
            'source_id' => $source_id,
            'house_title' => $house_title,
            'add_time' => time(),
            'is_pushed' => 0,
            'push_time' => 0
        );
        return $this->table($this->tName)->save($data);
    }
}