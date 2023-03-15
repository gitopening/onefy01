<?php

/**
 * 浏览历史记录
 * net geow@qq.com
 */
class BrowsingHistory extends Model
{
    public $tName = 'browsing_history';

    public function getList($pageLimit, $field = '*', $flag = 0, $where_clouse = '', $order = '')
    {
        $where = '';
        if ($where_clouse) {
            $where .= $where_clouse;
        }
        if ($flag == 1) {
            $where .= " and is_checked = 1 ";
        } elseif ($flag == 2) {
            $where .= " and is_checked = 0 ";
        } elseif ($flag == 3) {
            $where .= " and (is_checked = 0 or is_checked = 1 )";
        } elseif ($flag == 4) {
            $where .= " and is_checked = 2 ";
        } elseif ($flag == 5) {
            $where .= " and is_index = 1";
        } elseif ($flag == 6) {
            $where .= " and status = 1";
        } elseif ($flag == 7) {
            $where .= " and status = 2";
        } elseif ($flag == 8) {
            $where .= " and status = 3";
        } elseif ($flag == 9) {
            $where .= " and (status = 4 or status = 7)";
        } elseif ($flag == 10) {
            $where .= " and is_top = 1";
        } elseif ($flag == 11) {
            $where .= " and owner_phone<>''";
        } elseif ($flag == 12) {
            $where .= " and owner_phone=''";
        }

        $nrows = $pageLimit['rowTo'] - $pageLimit['rowFrom'] + 1;
        $result = $this->field($field)->table($this->tName)->where($where)->order($order)->limit($pageLimit['rowFrom'] . ',' . $nrows)->all();
        return $result;
    }

    function Add($member_id, $db_index, $publish_type, $data_info){
        $timestamp = time();

        //检测历史记录中是否已存在
        $conditon = array(
            'mid' => intval($member_id),
            'db_index' => $db_index,
            'publish_type' => intval($publish_type),
            'house_id' => intval($data_info['id'])
        );
        $is_exist = $this->table($this->tName)->where($conditon)->one();

        $data = array(
            'db_index' => intval($db_index),
            'publish_mid' => intval($data_info['mid']),
            'column_type' => intval($data_info['column_type']),
            'house_type' => intval($data_info['house_type']),
            'price' => floatval($data_info['house_price']),
            'unit_type' => intval($data_info['unit_type']),
            'month_price' => floatval($data_info['house_month_price']),
            'original_price' => floatval($data_info['original_price']),
            'start_price' => floatval($data_info['start_price']),
            'end_price' => floatval($data_info['end_price']),
            'house_title' => $data_info['article_title'],
            'house_thumb' => $data_info['house_thumb'],
            'rent_type' => intval($data_info['rent_type']),
            'house_room' => intval($data_info['house_room']),
            'house_hall' => intval($data_info['house_hall']),
            'house_toilet' => intval($data_info['house_toilet']),
            'house_veranda' => intval($data_info['house_veranda']),
            'house_totalarea' => floatval($data_info['house_totalarea']),
            'start_totalarea' => floatval($data_info['start_totalarea']),
            'end_totalarea' => floatval($data_info['end_totalarea']),
            'city_website_id' => intval($data_info['city_website_id']),
            'cityarea_id' => intval($data_info['cityarea_id']),
            'cityarea2_id' => intval($data_info['cityarea2_id']),
            'is_sublet' => intval($data_info['is_sublet']),
            'borough_name' => $data_info['borough_name'],
            'house_address' => $data_info['house_address'],
            'update_time' => $timestamp
        );

        if ($is_exist) {
            $data['browser_count'] = array(
                'browser_count + 1'
            );
            $result = $this->table($this->tName)->where($conditon)->save($data);
        } else {
            $data['mid'] = intval($member_id);
            $data['publish_type'] = intval($publish_type);
            $data['house_id'] = intval($data_info['id']);
            $data['browser_count'] = 1;
            $data['add_time'] = $timestamp;
            $result = $this->table($this->tName)->save($data);
        }
        return $result;
    }
}