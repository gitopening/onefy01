<?php

/**
 * 举报
 * author net
 * E-mail geow@qq.com
 */
class Report extends Model
{
    public $tName = 'report';

    public function saveReport($info)
    {
        $info['id'] = intval($info['id']);
        if ($info['id']) {
            $updateField = array(
                'reason' => $info['house_type']
            );
            return $this->table($this->tName)->where('id=' . $info['id'])->save($updateField);
        } else {
            $insertField = array(
                'house_type' => $info['house_type'],
                'house_id' => $info['house_id'],
                'report_target' => $info['report_target'],
                'reason' => $info['reason'],
                'status' => 0,
                'addtime' => time()
            );
            return $this->table($this->tName)->save($insertField);
        }
    }

    function getList($pageLimit, $field = '*', $where = array(), $order = 'id desc ')
    {
        return $this->field($field)->tName($this->tName)->where($where)->order('id desc')->limit($pageLimit['rowFrom'], $pageLimit['rowTo'])->all();
    }

    function deleteReport($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
            $where = 'id in (' . $ids . ')';
        } else {
            $where = 'id=' . intval($ids);
        }
        return $this->table($this->tName)->del($where);
    }

    /**
     * 修改状态
     * @param $ids
     * @param $status
     * @return mixed
     */
    function changeStatus($ids, $status)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
            $where = 'id in (' . $ids . ')';
        } else {
            $where = 'id=' . intval($ids);
        }
        return $this->table($this->tName)->where($where)->save(array('status' => $status));
    }

    /**
     * 取得详细信息
     * @access public
     * @param int $id
     * @return array
     */
    function getInfo($id, $field = '*')
    {
        return $this->table($this->tName)->field($field)->where('id = ' . $id)->one();
    }

    /**
     * 取类别总数
     * @access public
     * @return int
     */
    function getCount($where = array())
    {
        return $this->table($this->tName)->where($where)->count();
    }
}