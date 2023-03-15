<?php

/**
 * 求购房源信息模型
 * net geow@qq.com
 */
class HouseQiugou extends Model
{
    public $tName = 'qiugou';
    public $tNameExtend = 'qiugou_extend';
    public $tNameCheckLog = 'qiugou_check_log';
    public $tNameMobleAuth = 'qiugou_mobile_auth';
    public $tNamePic = 'qiugou_pic';
    public $tNameShortData = 'qiugou_short_data';
    public $tNamePromote = 'qiugou_promote';
    public $tNameRefreshPlan = 'house_qiugou_refresh_plan';

    /**
     * 取数据列表
     * @param $pageLimit
     * @param string $field
     * @param int $flag
     * @param string $where_clouse
     * @param string $order
     * @return mixed
     */
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
            $where .= " and is_checked = 2";
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

    /**
     * 保存房源信息
     * @param array $fieldData 数据数组
     * @return int
     */
    public function saveHouse($fieldData)
    {
        $cityarea_id = intval($fieldData['cityarea_id']);
        $cityarea2_id = intval($fieldData['cityarea2_id']);
        //判断房源所属栏目
        if ($fieldData['house_type'] == 5) {
            $column_type = 2;
        } elseif ($fieldData['house_type'] == 7) {
            $column_type = 3;
        } elseif ($fieldData['house_type'] == 15) {
            $column_type = 4;
        } elseif ($fieldData['house_type'] == 16) {
            $column_type = 16;
        } elseif ($fieldData['house_type'] == 17) {
            $column_type = 17;
        } else {
            $column_type = 1;
        }
        if ($column_type != 1) {
            $fieldData['house_room'] = 0;
            $fieldData['house_hall'] = 0;
            $fieldData['house_toilet'] = 0;
            $fieldData['house_veranda'] = 0;
        }

        $now_time = time();
        $fieldData['owner_name'] = trim($fieldData['owner_name']);
        $fieldData['owner_phone'] = trim($fieldData['owner_phone']);
        $field_array = array(
            'cityarea_id' => $cityarea_id,
            'cityarea2_id' => $cityarea2_id,
            'column_type' => $column_type,
            'house_type' => intval($fieldData['house_type']),
            'house_price' => 0,
            'start_price' => floatval($fieldData['start_price']),
            'end_price' => floatval($fieldData['end_price']),
            'house_totalarea' => floatval($fieldData['house_totalarea']),
            'start_totalarea' => floatval($fieldData['start_totalarea']),
            'end_totalarea' => floatval($fieldData['end_totalarea']),
            'house_room' => intval($fieldData['house_room']),
            'house_floor' => intval($fieldData['house_floor']),
            'mid' => intval($fieldData['mid']),
            'mtype' => intval($fieldData['mtype']),
            'is_checked' => intval($fieldData['is_checked']),
            'is_delete' => 0,
            'is_down' => 0, //发布和新编辑的房源设置状态为上架状态
            'house_status' => 0,
            'house_hall' => intval($fieldData['house_hall']),
            'house_toilet' => intval($fieldData['house_toilet']),
            'house_veranda' => intval($fieldData['house_veranda']),
            'house_topfloor' => intval($fieldData['house_topfloor']),
            'belong' => intval($fieldData['belong']),
            'house_title' => $fieldData['house_title'],
            'house_title_crc32' => array($fieldData['house_title'], 'CRC32'),
            'house_thumb' => '',
            'house_age' => $fieldData['house_age'],
            'owner_name' => $fieldData['owner_name'],
            'owner_phone' => $fieldData['owner_phone'],
            'owner_phone_crc32' => array($fieldData['owner_phone'], 'CRC32'),
            'owner_phone_pic' => '',
            'keywords' => $fieldData['keywords'],
            'borough_name' => $fieldData['borough_name'],
            'house_diduan' => $fieldData['house_diduan'],
            'house_address' => $fieldData['house_address'],
            'hide_phone' => intval($fieldData['hide_phone']),
            'wechat' => $fieldData['wechat'],
            'qq' => $fieldData['qq'],
            'is_cooperation' => intval($fieldData['is_cooperation']),
            'data_update_time' => $now_time
        );

        $field_extend_array = array(
            'cityarea2_name' => $fieldData['cityarea2_name'],
            'source_url' => '',
            'check_type' => intval($fieldData['check_type']),
            'check_note' => $fieldData['check_note'],
            'words' => $fieldData['words'],
            'house_desc' => $fieldData['house_desc']
        );

        //开始事务
        $this->begin();
        if ($fieldData['id']) {
            $field_array['updated'] = $now_time;
            $house_id = intval($fieldData['id']);
            $condition = array(
                'id' => $house_id,
                'mid' => intval($fieldData['mid'])
            );
            $result = $this->table($this->tName)->where($condition)->save($field_array);
            if ($result == false) {
                $this->rollback();
                return false;
            }
            //更新附加表信息
            $result = $this->table($this->tNameExtend)->where('house_id = ' . $house_id)->save($field_extend_array);
            if ($result == false) {
                $this->rollback();
                return false;
            }
            //检测房源是否在推广中，推广中的房源暂停推广
            $house_promote = $this->getPromoteInfo($house_id);
            if (!empty($house_promote)) {
                if ($fieldData['is_checked'] == -1 && $house_promote['pause_time'] == 0 && $house_promote['end_time'] > $now_time) {
                    $data = array(
                        'pause_time' => $now_time
                    );
                    $result = $this->table($this->tNamePromote)->where('house_id = ' . $house_id)->save($data);
                    if ($result === false) {
                        $this->rollback();
                        return false;
                    }
                } elseif ($fieldData['is_checked'] > -1 && $house_promote['pause_time'] > 0) {
                    $data = array(
                        'pause_time' => 0,
                        'end_time' => $now_time - $house_promote['pause_time'] + $house_promote['end_time']
                    );
                    $result = $this->table($this->tNamePromote)->where('house_id = ' . $house_id)->save($data);
                    if ($result === false) {
                        $this->rollback();
                        return false;
                    }
                }
            }
        } else {
            $field_array['created'] = $now_time;
            $field_array['updated'] = $now_time;
            $field_array['source_id'] = 0;
            $field_array['city_website_id'] = $fieldData['city_website_id'];
            $field_array['click_virtual'] = mt_rand(1, 5);

            //插入数据
            $result = $this->table($this->tName)->save($field_array);
            if ($result == false) {
                $this->rollback();
                return false;
            }
            $house_id = $result;
            $field_extend_array['house_id'] = $house_id;
            $result = $this->table($this->tNameExtend)->save($field_extend_array);
            if ($result == false) {
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return $house_id;
    }

    /**
     * 取房源信息
     * @param string $id 小区ID
     * @param string $field 主表字段
     * @access public
     * @return array
     */
    public function getInfo($id, $field = '*')
    {
        return $this->table($this->tName)->field($field)->where('id = ' . $id)->one();
    }

    public function GetMoreInfo($id, $field = '*')
    {
        return $this->table($this->tNameExtend)->field($field)->where('house_id = ' . $id)->one();
    }

    public function GetDetail($id, $use_master = false)
    {
        return $this->field('h.*, e.*')->table($this->tName, 'h')->join($this->db_prefix . $this->tNameExtend . ' AS e ON h.id = e.house_id', 'LEFT')->where('h.id = ' . $id)->master($use_master)->one();
    }

    /**
     * 删除房源信息
     * @param mixed $ids 选择的ID
     * @access public
     * @return bool
     */
    public function deleteHouse($ids, $member_id = 0)
    {
        $house_id = $ids;
        $condition = array();
        if (is_array($ids)) {
            $ids = implode(',', $ids);
            $condition[] = 'id in (' . $ids . ')';
        } else {
            $condition[] = 'id = ' . intval($ids);
        }

        if ($member_id) {
            $condition[] = 'mid = ' . intval($member_id);

        }
        if (empty($condition)) {
            return false;
        }
        $nohouseid = $this->table($this->tName)->where($condition)->all();

        if (!empty($nohouseid)) {
            $house_ids = array();
            foreach ($nohouseid as $item) {
                $house_ids[] = $item['id'];
            }
            $nohouseid = implode(',', $house_ids);
            $this->table($this->tName)->del('id in (' . $nohouseid . ')');
            $this->table($this->tNameExtend)->del('house_id in (' . $nohouseid . ')');
            $this->table($this->tNameMobleAuth)->del('house_id in (' . $nohouseid . ')');
            $this->table($this->tNameCheckLog)->del('house_id in (' . $nohouseid . ')');
            $this->table($this->tNamePromote)->del('house_id in (' . $nohouseid . ')');

            //缓存更新
            update_house_memcache('qiugou', $house_id);

            return true;
        } else {
            return false;
        }
    }

    /*
     * 删除房源电话号码
     */
    public function updatePhoneStatus($ids, $value)
    {
        return $this->updateHouse($ids, array('house_status' => $value));
    }

    public function updateHouse($ids, $fields_array)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $ids_str = implode(',', $ids);
        $where = 'id in (' . $ids_str . ')';

        if (empty($fields_array)) {
            return false;
        }
        $fields_array['data_update_time'] = time();
        $result = $this->table($this->tName)->where($where)->save($fields_array);
        if ($result) {
            //更新缓存
            update_house_memcache('qiugou', $ids);

            $fields = array();
            $value = array();
            foreach ($fields_array as $key => $val) {
                $fields[] = $key;
                $value[] = $val;
            }
            $sphinx_values = array();
            $sphinx_config = GetConfig('sphinx');
            $sphinx = Sphinx::getInstance($sphinx_config);
            foreach ($ids as $id) {
                $condition = array(
                    'id' => intval($id)
                );
                $data_info = $this->table($this->tName)->field('id, city_website_id')->where($condition)->master(true)->one();
                if ($data_info) {
                    $city_info = get_city_info_by_id($data_info['city_website_id']);
                    $index_name = $city_info['url_name'];
                    //根据当前所在城市调用对应的索引
                    if (!in_array($index_name, $sphinx_config['index_group'])) {
                        $index_name = 'other_all';
                    }
                    //更新Sphinx状态
                    $sphinx_values[$id] = $value;
                    $sphinx_index = 'fyw_member_house_qiugou_' . $index_name . ',fyw_member_house_qiugou_delta,fyw_member_house_qiugou_promote';
                    $sphinx->UpdateAttributes($sphinx_index, $fields, $sphinx_values);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function addCheckLog($house_id, $operation_type, $admin_id, $admin_name, $admin_note)
    {
        $house_ids = !is_array($house_id) ? array($house_id) : $house_id;
        $now = time();
        foreach ($house_ids as $key => $val) {
            $fields_array = array(
                'house_id' => $val,
                'operation_type' => $operation_type,
                'admin_id' => $admin_id,
                'admin_name' => $admin_name,
                'admin_note' => $admin_note,
                'add_time' => $now
            );
            $this->table($this->tNameCheckLog)->save($fields_array);
        }
        //返回操作成功
        return true;
    }

    public function getCheckLog($house_id)
    {
        return $this->table($this->tNameCheckLog)->where('house_id in (' . $house_id . ')')->order('id asc')->all();
    }

    /**
     * 操作状态 不物理删除
     * @param mixed $ids ID
     * @access public
     * @return bool
     */
    public function changeStatus($ids, $status)
    {
        return $this->update($ids, 'status', $status);
    }

    /**
     * 更新某个字段
     * @param mixed $ids ID
     * @access public
     * @return bool
     */
    public function update($ids, $field, $value)
    {
        $house_id = $ids;
        if (is_array($ids)) {
            $ids = implode(',', $ids);
            $where = ' id in (' . $ids . ')';
        } else {
            $where = ' id=' . intval($ids);
        }
        if (empty($field) || empty($value)) {
            return false;
        }
        $data = array(
            $field => $value,
            'data_update_time' => time()
        );
        $result = $this->table($this->tName)->where($where)->save($data);
        if ($result) {
            update_house_memcache('qiugou', $house_id);
        }
        return $result;
    }

    /**
     * 审核房源
     * @param mixed $ids 选择的ID
     * @access public
     * @return bool
     */
    public function check($ids, $flag, $admin_name = '')
    {
        if ($admin_name) {
            //todo 添加操作日志
            //$this->table($this->tNameExtend)->where('house_id in (' . $ids . ')')->save(array('admin_name' => $admin_name));
        }

        $data = array(
            'is_checked' => $flag
        );
        return $this->updateHouse($ids, $data);
    }

    /**
     * 刷新房源
     * @param $ids
     * @param int $member_id
     * @return int
     */
    public function refresh($ids, $member_id = 0)
    {
        $current_time = time();
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $ids_str = implode(',', $ids);
        $where = 'id in (' . $ids_str . ') and mid=' . $member_id;
        $data = array(
            'refresh_count' => array('`refresh_count` + 1'),
            'updated' => $current_time,
            'data_update_time' => $current_time
        );
        $result = $this->table($this->tName)->where($where)->save($data);
        if ($result) {
            update_house_memcache('qiugou', $ids);

            //发送Sphinx通知更新搜索状态
            $sphinx_config = GetConfig('sphinx');
            $sphinx = Sphinx::getInstance($sphinx_config);
            $fields = array('updated');
            $value = array($current_time);
            $sphinx_values = array();
            foreach ($ids as $id) {
                $condition = array(
                    'id' => intval($id),
                    'mid' => $member_id
                );
                $data_info = $this->table($this->tName)->field('id, city_website_id')->where($condition)->master(true)->one();
                if ($data_info) {
                    $city_info = get_city_info_by_id($data_info['city_website_id']);
                    $index_name = $city_info['url_name'];
                    //根据当前所在城市调用对应的索引
                    if (!in_array($index_name, $sphinx_config['index_group'])) {
                        $index_name = 'other_all';
                    }
                    //更新Sphinx状态
                    $sphinx_values[$id] = $value;
                    $sphinx_index = 'fyw_member_house_qiugou_' . $index_name . ',fyw_member_house_qiugou_delta,fyw_member_house_qiugou_promote';
                    $sphinx->UpdateAttributes($sphinx_index, $fields, $sphinx_values);
                }
            }
        }
        return $result;
    }

    public function down($ids, $member_id = 0)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        if (empty($ids)) {
            return false;
        }
        $ids_str = implode(',', $ids);
        $condition = "id in ($ids_str) AND mid = '$member_id'";
        $data = array(
            'is_down' => 1,
            'down_time' => time(),
            'data_update_time' => time()
        );
        $result = $this->table($this->tName)->where($condition)->save($data);
        if ($result === false) {
            return false;
        }

        update_house_memcache('qiugou', $ids);

        //发送Sphinx通知更新搜索状态
        $sphinx_config = GetConfig('sphinx');
        $sphinx = Sphinx::getInstance($sphinx_config);
        $fields = array('is_down');
        $value = array(1);
        $sphinx_values = array();
        foreach ($ids as $id) {
            $condition = array(
                'id' => intval($id),
                'mid' => $member_id
            );
            $data_info = $this->table($this->tName)->field('id, city_website_id')->where($condition)->master(true)->one();
            if ($data_info) {
                $city_info = get_city_info_by_id($data_info['city_website_id']);
                $index_name = $city_info['url_name'];
                //根据当前所在城市调用对应的索引
                if (!in_array($index_name, $sphinx_config['index_group'])) {
                    $index_name = 'other_all';
                }
                //更新Sphinx状态
                $sphinx_values[$id] = $value;
                $sphinx_index = 'fyw_member_house_qiugou_' . $index_name . ',fyw_member_house_qiugou_delta,fyw_member_house_qiugou_promote';
                $sphinx->UpdateAttributes($sphinx_index, $fields, $sphinx_values);
            }
        }
        return true;
    }

    public function recover($ids, $member_id = 0)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        if (empty($ids)) {
            return false;
        }
        $ids_str = implode(',', $ids);
        $condition = "id in ($ids_str) AND mid = '$member_id'";
        $data = array(
            'is_down' => 0,
            'data_update_time' => time()
        );
        $result = $this->table($this->tName)->where($condition)->save($data);
        if ($result === false) {
            return false;
        }

        update_house_memcache('qiugou', $ids);

        //发送Sphinx通知更新搜索状态
        $sphinx_config = GetConfig('sphinx');
        $sphinx = Sphinx::getInstance($sphinx_config);
        $fields = array('is_down');
        $value = array(0);
        $sphinx_values = array();
        foreach ($ids as $id) {
            $condition = array(
                'id' => intval($id),
                'mid' => $member_id
            );
            $data_info = $this->table($this->tName)->field('id, city_website_id')->where($condition)->master(true)->one();
            if ($data_info) {
                $city_info = get_city_info_by_id($data_info['city_website_id']);
                $index_name = $city_info['url_name'];
                //根据当前所在城市调用对应的索引
                if (!in_array($index_name, $sphinx_config['index_group'])) {
                    $index_name = 'other_all';
                }
                //更新Sphinx状态
                $sphinx_values[$id] = $value;
                $sphinx_index = 'fyw_member_house_qiugou_' . $index_name . ',fyw_member_house_qiugou_delta,fyw_member_house_qiugou_promote';
                $sphinx->UpdateAttributes($sphinx_index, $fields, $sphinx_values);
            }
        }
        return true;
    }

    /**
     * 取总用户数
     * @access public
     * @return NULL
     */
    public function GetNoCacheCount($where = '')
    {
        return $this->table($this->tName)->where($where)->count();
    }

    public function getCount($flag = 0, $where_clouse = '')
    {
        $where = '';
        if ($flag == 1) {
            $where .= " and is_checked = 1";
        }
        if ($flag == 2) {
            $where .= " and is_checked = 0";
        }
        if ($flag == 3) {
            $where .= " and (is_checked = 0 or is_checked = 1 )";
        }
        if ($flag == 5) {
            $where .= " and is_index= 1";
        }
        if ($flag == 6) {
            $where .= " and status = 1";
        }
        if ($flag == 7) {
            $where .= " and status = 2";
        }
        if ($flag == 8) {
            $where .= " and status = 3";
        }
        if ($flag == 9) {
            $where .= " and (status = 4 or status = 7)";
        }
        if ($flag == 10) {
            $where .= " and is_top = 1";
        }
        if ($flag == 11) {
            $where .= " and owner_phone<>''";
        }
        if ($flag == 12) {
            $where .= " and owner_phone=''";
        }
        if ($where_clouse) {
            $where .= $where_clouse;
        }
        $result = $this->table($this->tName)->where($where)->cache(true, MEMCACHE_EXPIRETIME)->count();
        return $result;
    }

    /**
     * 取得所有数据
     * @param string $columns
     * @param string $condition
     * @param string $order
     * @return mixed
     */
    public function getAll($columns = '*', $condition = '', $order = '')
    {
        return $this->table($this->tName)->field($columns)->where($condition)->order($order)->all();
    }

    /*
     * 取得被举报数量
    * @param int $houseId
    * @return int
    */
    public function GetReportCount($houseId)
    {
        $condition = array(
            'house_id' => intval($houseId),
            'house_type' => 4
        );
        return $this->table('report')->where($condition)->count();
    }

    public function GetStatus($state)
    {
        switch ($state) {
            case -1:
                $str = '审核失败';
                break;
            case 0:
                $str = '正在审核';
                break;
            case 1:
                $str = '通过审核';
                break;
            case 2:
                $str = '已下架';
                break;
            default:
                $str = '';
        }
        return $str;
    }

    public function GetHouseURL($dataInfo, $suffix = '')
    {
        $domain_url = house_source_url($dataInfo);
        switch ($dataInfo['column_type']) {
            case 1:
                $url = $domain_url . '/qiugou/house_' . $dataInfo['id'] . $suffix . '.html';
                break;
            case 2:
                $url = $domain_url . '/xzlqg/house_' . $dataInfo['id'] . $suffix . '.html';
                break;
            case 3:
                $url = $domain_url . '/spqg/house_' . $dataInfo['id'] . $suffix . '.html';
                break;
            case 4:
                $url = $domain_url . '/cwqg/house_' . $dataInfo['id'] . $suffix . '.html';
                break;
            case 16:
                $url = $domain_url . '/cfqg/house_' . $dataInfo['id'] . $suffix . '.html';
                break;
            case 17:
                $url = $domain_url . '/ckqg/house_' . $dataInfo['id'] . $suffix . '.html';
                break;
            default:
                $url = '';
        }
        return $url;
    }


    public function NotSaleHouse($ids, $member_id)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        if (empty($ids)) {
            return false;
        }
        $ids_str = implode(',', $ids);
        $condition = "id in ($ids_str) AND mid = '$member_id'";
        $data = array(
            'is_delete' => 1,
            'down_time' => time(),
            'data_update_time' => time()
        );
        $result = $this->table($this->tName)->where($condition)->save($data);
        if ($result === false) {
            return false;
        }

        update_house_memcache('qiugou', $ids);

        //发送Sphinx通知更新搜索状态
        $sphinx_config = GetConfig('sphinx');
        $sphinx = Sphinx::getInstance($sphinx_config);
        $fields = array('is_delete');
        $value = array(1);
        $sphinx_values = array();
        foreach ($ids as $id) {
            $condition = array(
                'id' => intval($id),
                'mid' => $member_id
            );
            $data_info = $this->table($this->tName)->field('id, city_website_id')->where($condition)->master(true)->one();
            if ($data_info) {
                $city_info = get_city_info_by_id($data_info['city_website_id']);
                $index_name = $city_info['url_name'];
                //根据当前所在城市调用对应的索引
                if (!in_array($index_name, $sphinx_config['index_group'])) {
                    $index_name = 'other_all';
                }
                //更新Sphinx状态
                $sphinx_values[$id] = $value;
                $sphinx_index = 'fyw_member_house_qiugou_' . $index_name . ',fyw_member_house_qiugou_delta,fyw_member_house_qiugou_promote';
                $sphinx->UpdateAttributes($sphinx_index, $fields, $sphinx_values);
            }
        }
        return true;
    }

    public function OnSaleHouse($ids, $member_id)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        if (empty($ids)) {
            return false;
        }
        $ids_str = implode(',', $ids);
        $condition = "id in ($ids_str) AND mid = '$member_id'";
        $data = array(
            'is_delete' => 0,
            'down_time' => 0,
            'data_update_time' => time()
        );
        $this->table($this->tName)->where($condition)->save($data);

        update_house_memcache('qiugou', $ids);

        //发送Sphinx通知更新搜索状态
        $sphinx_config = GetConfig('sphinx');
        $sphinx = Sphinx::getInstance($sphinx_config);
        $fields = array('is_delete');
        $value = array(0);
        $sphinx_values = array();
        foreach ($ids as $id) {
            $condition = array(
                'id' => intval($id),
                'mid' => $member_id
            );
            $data_info = $this->table($this->tName)->field('id, city_website_id')->where($condition)->master(true)->one();
            if ($data_info) {
                $city_info = get_city_info_by_id($data_info['city_website_id']);
                $index_name = $city_info['url_name'];
                //根据当前所在城市调用对应的索引
                if (!in_array($index_name, $sphinx_config['index_group'])) {
                    $index_name = 'other_all';
                }
                //更新Sphinx状态
                $sphinx_values[$id] = $value;
                $sphinx_index = 'fyw_member_house_qiugou_' . $index_name . ',fyw_member_house_qiugou_delta,fyw_member_house_qiugou_promote';
                $sphinx->UpdateAttributes($sphinx_index, $fields, $sphinx_values);
            }
        }
        return true;
    }

    public function CheckIsExist($house_title, $mid)
    {
        $condition = array(
            'mid' => $mid,
            'house_title_crc32' => array($house_title, 'CRC32'),
            'house_title' => $house_title,
            'is_delete' => 0
        );
        return $this->table($this->tName)->field('id')->where($condition)->one();
    }


    public function getPromoteInfo($house_id)
    {
        return $this->table($this->tNamePromote)->where('house_id = ' . intval($house_id))->one();
    }

    public function addClick($house_id)
    {
        return $this->table($this->tName)->where('id = ' . intval($house_id))->setInc('click_num');
    }

    public function getClick($id)
    {
        return $this->field('id, click_num, click_virtual, created')->table($this->tName)->where('id = ' . intval($id))->one();
    }
}