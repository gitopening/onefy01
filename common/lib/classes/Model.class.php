<?php

/**
 * Created by net.
 * E-mail geow@qq.com
 * Date: 2016/6/5
 * Time: 7:36
 */
class Model
{
    protected $db = null;
    public $db_prefix = '';
    
    public function __construct($db)
    {
        $this->db = $db;
        $this->db_prefix = $this->db->_config['db_prefix'];
    }

    public function field($field)
    {
        $this->db->field($field);
        return $this;
    }

    public function table($table, $alias = '')
    {
        $this->db->table($table, $alias);
        return $this;
    }

    public function join($join, $type = 'INNER')
    {
        $this->db->join($join, $type);
        return $this;
    }

    public function parseSQL()
    {
        return $this->db->parseSQL();
    }

    public function where($where)
    {
        $this->db->where($where);
        return $this;
    }

    public function order($order)
    {
        $this->db->order($order);
        return $this;
    }

    public function limit($offset, $length = null)
    {
        $this->db->limit($offset, $length);
        return $this;
    }

    public function cache($key = null, $expire = null)
    {
        $this->db->cache($key, $expire);
        return $this;
    }

    public function one()
    {
        return $this->db->one();
    }

    public function all()
    {
        return $this->db->all();
    }

    public function data($data = array())
    {
        $this->db->data($data);
        return $this;
    }

    public function save($data = array(), $where = array())
    {
        return $this->db->save($data, $where);
    }

    public function query($sql)
    {
        return $this->db->execute($sql);
    }

    public function del($where = array())
    {
        return $this->db->del($where);
    }

    public function count($field = '*')
    {
        return $this->db->count($field);
    }

    public function sum($field)
    {
        return $this->db->sum($field);
    }

    public function setInc($field, $step = 1)
    {
        return $this->db->setInc($field, $step);
    }

    public function setDec($field, $step = 1)
    {
        return $this->db->setDec($field, $step);
    }

    public function master($type = false)
    {
        $this->db->master($type);
        return $this;
    }

    public function begin()
    {
        return $this->db->begin();
    }

    public function commit()
    {
        return $this->db->commit();
    }

    public function rollback()
    {
        return $this->db->rollback();
    }

    public function __destruct()
    {
        
    }
}