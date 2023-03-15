<?php

/**
 * Created by net.
 * E-mail geow@qq.com
 * 新闻资讯模型类
 */
class News extends Model
{
    public $tName = 'news';
    public $tNameExtend = 'news_extend';
    public $tNamePic = 'news_pic';
    public $tNameCheckLog = 'news_check_log';

    public function getDetail($id, $use_master = false)
    {
        return $this->field('n.*, e.*')->table($this->tName, 'n')->join($this->db_prefix . $this->tNameExtend . ' AS e ON n.id = e.news_id', 'LEFT')->where('n.id = ' . $id . ' AND website_id = ' . WEBHOSTID)->master($use_master)->one();
    }

    public function checkIsExist($title, $mid)
    {
        $condition = array(
            'mid' => $mid,
            'website_id' => WEBHOSTID,
            'title_crc32' => array($title, 'CRC32'),
            'title' => $title,
            'is_delete' => 0
        );
        return $this->table($this->tName)->field('id')->where($condition)->one();
    }

    public function getHotFromSphinx($city_id, $rows = 10, $sphinx_index = SPHINX_SEARCH_NEWS, $is_cache = false)
    {
        if ($is_cache == true) {
            if ($city_id) {
                $key = 'news_hot_' . $city_id . '_' . $rows;
            } else {
                $key = 'news_hot_' . $rows;
            }
            $Cache = Cache::getInstance();
            $data_list = $Cache->get($key);
            if (empty($news_list)) {
                $Sphinx = Sphinx::getInstance();
                $Sphinx->ResetFilters();
                $Sphinx->SetFilter('is_checked', array(1));
                $Sphinx->SetFilter('is_delete', array(0));
                if ($city_id) {
                    $Sphinx->SetFilter('city_website_id', array($city_id));
                }
                $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'add_time');
                $Sphinx->SetLimits(0, $rows, $rows);
                $result = $Sphinx->Query('', $sphinx_index);
                $data_list = array();
                foreach ($result['matches'] as $item) {
                    $data_list[] = array(
                        'url' => $this->getUrl($item['attrs']['filter_id']),
                        'title' => $item['attrs']['title'],
                        'description' => $item['attrs']['description'],
                        'add_time' => $item['attrs']['add_time'],
                    );
                }
                if ($data_list) {
                    $Cache->set($key, $data_list, MEMCACHE_EXPIRETIME);
                }
            }
        } else {
            $Sphinx = Sphinx::getInstance();
            $Sphinx->ResetFilters();
            $Sphinx->SetFilter('is_checked', array(1));
            $Sphinx->SetFilter('is_delete', array(0));
            if ($city_id) {
                $Sphinx->SetFilter('city_website_id', array($city_id));
            }
            $Sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'add_time');
            $Sphinx->SetLimits(0, $rows, $rows);
            $result = $Sphinx->Query('', $sphinx_index);
            $data_list = array();
            foreach ($result['matches'] as $item) {
                $data_list[] = array(
                    'url' => $this->getUrl($item['attrs']['filter_id']),
                    'title' => $item['attrs']['title'],
                    'description' => $item['attrs']['description'],
                    'add_time' => $item['attrs']['add_time'],
                );
            }
        }
        return $data_list;
    }

    public function getUrl($id, $suffix = '')
    {
        return 'article_' . $id . $suffix . '.html';
    }

    public function addClick($id)
    {
        return $this->table($this->tName)->where('id = ' . $id)->setInc('click');
    }

    public function addCheckLog($id, $operation_type, $admin_id, $admin_name, $admin_note)
    {
        $id = !is_array($id) ? array($id) : $id;
        $now = time();
        foreach ($id as $key => $val) {
            $fields_array = array(
                'news_id' => $val,
                'operation_type' => $operation_type, //操作类型：0待审核 1通过 2不通过 3管理删除 4用户删除
                'admin_id' => $admin_id,
                'admin_name' => $admin_name,
                'admin_note' => $admin_note,
                'check_time' => $now
            );
            $this->table($this->tNameCheckLog)->save($fields_array);
        }
        //返回操作成功
        return true;
    }

    public function getClick($id)
    {
        return $this->field('id, click')->table($this->tName)->where('id = ' . intval($id))->one();
    }

    public function delete($ids, $member_id)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        if (empty($ids)) {
            return false;
        }
        $ids_str = implode(',', $ids);
        $condition = "id IN ($ids_str) AND mid = '$member_id'";
        $data = array(
            'is_delete' => 1,
            'db_update_time' => time()
        );
        $result = $this->table($this->tName)->where($condition)->save($data);
        if ($result === false) {
            return false;
        }

        //标记对应的图片为已删除状态
        $condition = "news_id IN ($ids_str) AND mid = '$member_id'";
        $data = array(
            'is_delete' => 1
        );
        $this->table($this->tNamePic)->where($condition)->save($data);
        return true;
    }
}