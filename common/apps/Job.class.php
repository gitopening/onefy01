<?php

/**
 * 求租房源信息模型
 * net geow@qq.com
 */
class Job extends Model
{
    public $tName = 'job';
    public $tNameAddress = 'job_address';
    public $tNameCheckLog = 'job_check_log';
    public $tNameMobleAuth = 'job_mobile_auth';

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

    /**
     * 保存房源信息
     * @param array $fieldData 数据数组
     * @return int
     */
    public function saveJob($fieldData)
    {
        $cityarea_id = intval($fieldData['cityarea_id']);
        $cityarea2_id = intval($fieldData['cityarea2_id']);

        $fieldData['salary_min'] = floatval($fieldData['salary_min']);
        $fieldData['salary_max'] = floatval($fieldData['salary_max']);

        $now_time = time();
        $fieldData['owner_name'] = trim($fieldData['owner_name']);
        $fieldData['owner_phone'] = trim($fieldData['owner_phone']);

        $field_array = array(
            'mid' => intval($fieldData['mid']),
            'mtype' => intval($fieldData['mtype']),
            'title' => $fieldData['title'],
            'title_crc32' => array($fieldData['title'], 'CRC32'),
            'category' => intval($fieldData['category']),
            'quantity' => intval($fieldData['quantity']),
            'salary_min' => intval($fieldData['salary_min']),
            'salary_max' => intval($fieldData['salary_max']),
            'welfare' => $fieldData['welfare'],
            'education' => intval($fieldData['education']),
            'working_years' => intval($fieldData['working_years']),
            'content' => $fieldData['content'],
            'status' => 0,
            'is_checked' => intval($fieldData['is_checked']),
            'is_delete' => 0,
            'is_down' => 0,         //发布和新编辑的房源设置状态为上架状态
            'down_time' => 0,
            'cityarea_id' => $cityarea_id,
            'cityarea2_id' => $cityarea2_id,
            'cityarea2_name' => $fieldData['cityarea2_name'],
            'hide_phone' => intval($fieldData['hide_phone']),
            'keywords' => $fieldData['keywords'],
            'company' => $fieldData['company'],
            'outlet' => $fieldData['outlet'],
            'owner_name' => $fieldData['owner_name'],
            'owner_phone' => $fieldData['owner_phone'],
            'owner_phone_crc32' => array($fieldData['owner_phone'], 'CRC32'),
            'address' => $fieldData['address'],
            'wechat' => $fieldData['wechat'],
            'qq' => $fieldData['qq'],
            'check_type' => intval($fieldData['check_type']),
            'check_note' => $fieldData['check_note'],
            'words' => $fieldData['words'],
            'owner_notes' => '',
            'data_update_time' => $now_time
        );

        //开始事务
        $this->begin();
        //判断是修改还是增加
        if ($fieldData['id']) {
            //编辑
            $field_array['updated'] = $now_time;
            $id = intval($fieldData['id']);

            $condition = array(
                'id' => $id,
                'mid' => intval($fieldData['mid'])
            );
            $result = $this->table($this->tName)->where($condition)->save($field_array);
            if ($result == false) {
                $this->rollback();
                return false;
            }
        } else {
            $field_array['created'] = $now_time;
            $field_array['updated'] = $now_time;
            $field_array['city_website_id'] = $fieldData['city_website_id'];
            $field_array['click_virtual'] = mt_rand(1, 5);

            //插入数据
            $result = $this->table($this->tName)->save($field_array);
            if ($result == false) {
                $this->rollback();
                return false;
            }
            $id = $result;
        }
        $this->commit();
        return $id;
    }

    /**
     * 取房源信息
     * @param string $id ID
     * @param string $field 主表字段
     * @access public
     * @return array
     */
    public function getInfo($id, $field = '*')
    {
        return $this->table($this->tName)->field($field)->where('id = ' . $id)->one();
    }


    public function GetDetail($id, $use_master = false)
    {
        return $this->table($this->tName)->where('id = ' . $id)->master($use_master)->one();
    }

    /**
     * 删除房源信息
     * @param mixed $ids 选择的ID
     * @access public
     * @return bool
     */
    public function deleteJob($ids, $member_id = 0)
    {
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
        $no_id = $this->table($this->tName)->where($condition)->all();

        if (!empty($no_id)) {
            $job_id = array();
            foreach ($no_id as $item) {
                $job_id[] = $item['id'];
            }
            $no_id = implode(',', $job_id);
            $this->table($this->tName)->del('id in (' . $no_id . ')');
            $this->table($this->tNameMobleAuth)->del('job_id in (' . $no_id . ')');
            $this->table($this->tNameCheckLog)->del('job_id in (' . $no_id . ')');

            //缓存更新
            update_job_memcache($job_id);

            return true;
        } else {
            return false;
        }
    }

    /*
     * 更改状态
     */
    public function updatePhoneStatus($ids, $value)
    {
        return $this->updateJob($ids, array('status' => $value));
    }

    public function updateJob($ids, $fields_array)
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
            update_job_memcache($ids);

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
                    $sphinx_index = 'fyw_member_job_' . $index_name . ',fyw_member_job_delta';
                    $sphinx->UpdateAttributes($sphinx_index, $fields, $sphinx_values);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function addCheckLog($id, $operation_type, $admin_id, $admin_name, $admin_note)
    {
        $ids = !is_array($id) ? array($id) : $id;
        $now = time();
        foreach ($ids as $key => $val) {
            $fields_array = array(
                'job_id' => $val,
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

    public function getCheckLog($id)
    {
        return $this->table($this->tNameCheckLog)->where('job_id in (' . $id . ')')->order('id asc')->all();
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
        $job_id = $ids;
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
            update_job_memcache($job_id);
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

        }

        $data = array(
            'is_checked' => $flag
        );
        return $this->updateJob($ids, $data);
    }

    /**
     * 刷新房源
     * @param mixed $ids ID
     * @access public
     * @return bool
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
            'updated' => $current_time,
            'data_update_time' => $current_time
        );
        $result = $this->table($this->tName)->where($where)->save($data);
        if ($result) {
            update_job_memcache($ids);

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
                    $sphinx_index = 'fyw_member_job_' . $index_name . ',fyw_member_job_delta';
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

        update_job_memcache($ids);

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
                $sphinx_index = 'fyw_member_job_' . $index_name . ',fyw_member_job_delta';
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

        update_job_memcache($ids);

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
                $sphinx_index = 'fyw_member_job_' . $index_name . ',fyw_member_job_delta';
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
        } elseif ($flag == 11) {
            $where .= " and owner_phone<>''";
        } elseif ($flag == 12) {
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

    public function GetReportCount($id)
    {
        $condition = array(
            'house_id' => intval($id),
            'house_type' => 7
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

    public function GetURL($dataInfo, $suffix = '')
    {
        $domain_url = house_source_url($dataInfo);
        return $domain_url . '/job/detail_' . $dataInfo['id'] . $suffix . '.html';
    }


    public function NotSale($ids, $member_id)
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

        update_job_memcache($ids);

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
                $sphinx_index = 'fyw_member_job_' . $index_name . ',fyw_member_job_delta';
                $sphinx->UpdateAttributes($sphinx_index, $fields, $sphinx_values);
            }
        }
        return true;
    }

    public function OnSale($ids, $member_id)
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

        update_job_memcache($ids);

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
                $sphinx_index = 'fyw_member_job_' . $index_name . ',fyw_member_job_delta';
                $sphinx->UpdateAttributes($sphinx_index, $fields, $sphinx_values);
            }
        }
        return true;
    }

    public function CheckIsExist($title, $mid)
    {
        $condition = array(
            'mid' => $mid,
            'title_crc32' => array($title, 'CRC32'),
            'title' => $title,
            'is_delete' => 0
        );
        return $this->table($this->tName)->field('id')->where($condition)->one();
    }

    public function addClick($id)
    {
        return $this->table($this->tName)->where('id = ' . intval($id))->setInc('click_num');
    }

    public function getClick($id)
    {
        return $this->field('id, click_num, click_virtual, created')->table($this->tName)->where('id = ' . intval($id))->one();
    }
}