<?php
/**
 * mysql 数据操作类
 * todo 自动去除失败的服务器
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * Mysql 数据库的dbQuery类
 * @package Util
 */
class DbQueryForMysql
{
    /**
     * select方法返回的最大记录数
     */
    const MAX_ROW_NUM = 1000;

    /**
     * 数据查询结果集对象
     * @var object $dataSet
     */
    public $dataSet = null;

    /**
     * 数据源对象
     * @var object $ds
     */
    public $ds = null;

    public $sql = '';

    public $transCnt = 0;

    private $_config = array(
        'db_type' => 'mysql',
        'db_host' => '127.0.0.1',
        'db_port' => '3306',
        'db_user' => 'root',
        'db_password' => '',
        'db_name' => '',
        'db_prefix' => '',
        'db_charset' => 'utf8',
        'db_deploy_type' => 0, //0单一服务器  1分布式服务器
        'db_rw_separate' => false, //是否读写分离
        'db_master_num' => 1, //写服务器数量，默认为1
        'db_slave_no' => '' //指定从服务器序号，服务器编号从0开始
    );

    private $_read_link = null;

    private $_write_link = null;

    private $_error_link = array();

    public $debug = false;

    public $db_prefix = '';

    //数据查询表达式
    protected $options = array();

    /**
     * 构造函数
     * @param array $config
     */
    public function __construct($config = array())
    {
        if (empty($config)) {
            //通用数据库连接设置
            $config = GetConfig('db');
        }
        $this->_config = array_merge($this->_config, $config);
        $this->db_prefix = $this->_config['db_prefix'];
        $result = $this->multiConnect();
        if (!$result) {
            $this->getError();
            exit();
        }
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    private function connect($db_config)
    {
        if (empty($db_config['host']) || empty($db_config['port']) || empty($db_config['user'])) {
            $this->_error_link[] = '服务器' . $db_config['host'] . '数据库' . $db_config['db_name'] . '连接参数配置错误';
            return false;
        }
        $link = mysql_connect($db_config['host'] . ':' . $db_config['port'], $db_config['user'], $db_config['password'], $db_config['new_link']);
        if (!$link) {
            $this->_error_link[] = '数据库服务器' . $db_config['host'] . '连接失败';
            return false;
        }
        $result = mysql_select_db($db_config['db_name'], $link);
        if (!$result) {
            $this->_error_link[] = '服务器' . $db_config['host'] . '数据库' . $db_config['db_name'] . '不存在';
            return false;
        }
        $sql = "SET NAMES " . $this->_config['db_charset'];
        mysql_query($sql, $link);
        return $link;
    }

    private function multiConnect()
    {
        $db_host = explode(',', $this->_config['db_host']);
        $db_port = explode(',', $this->_config['db_port']);
        $db_user = explode(',', $this->_config['db_user']);
        $db_password = explode(',', $this->_config['db_password']);
        $db_name = explode(',', $this->_config['db_name']);

        //单一服务器
        if ($this->_config['db_deploy_type'] == 0 || count($db_host) == 1) {
            $db_host_config = array(
                'host' => trim($db_host[0]),
                'port' => trim($db_port[0]),
                'user' => trim($db_user[0]),
                'password' => trim($db_password[0]),
                'db_name' => trim($db_name[0]),
                'new_link' => true
            );
            $this->_write_link = $this->connect($db_host_config);
            $this->_read_link = $this->_write_link;
            return $this->_write_link;
        } else {
            //判断是否读写分离，分布式服务器
            //从服务器索引编号
            $db_slave_no = empty($this->_config['db_slave_no']) ? array() : explode(',', $this->_config['db_slave_no']);
            //把服务器连接信息保存到数组
            $read_host_config = array();
            $write_host_config = array();
            foreach ($db_host as $key => $item) {
                $host = trim($item) ? trim($item) : '';
                $port = isset($db_port[$key]) ? trim($db_port[$key]) : trim($db_port[0]);
                $user = isset($db_user[$key]) ? trim($db_user[$key]) : trim($db_user[0]);
                $password = isset($db_password[$key]) ? trim($db_password[$key]) : trim($db_password[0]);
                $database = isset($db_name[$key]) ? trim($db_name[$key]) : trim($db_name[0]);
                if (empty($host) || empty($port) || empty($user)) {
                    $this->_error_link[] = $host . $database . '数据库连接参数配置错误';
                    continue;
                }
                $connect_config = array(
                    'host' => $host,
                    'port' => $port,
                    'user' => $user,
                    'password' => $password,
                    'db_name' => $database,
                    'new_link' => true
                );

                if ($this->_config['db_rw_separate'] == true) {
                    if (empty($db_slave_no)) {
                        if ($this->_config['db_master_num'] > count($write_host_config)) {
                            $write_host_config[] = $connect_config;
                        } else {
                            $read_host_config[] = $connect_config;
                        }
                    } else {
                        if ($this->_config['db_master_num'] > count($write_host_config) && !in_array($key, $db_slave_no)) {
                            $write_host_config[] = $connect_config;
                        } else {
                            $read_host_config[] = $connect_config;
                        }
                    }
                } else {
                    $write_host_config[] = $connect_config;
                    $read_host_config[] = $connect_config;
                }
            }

            //至少要有一台写服务器
            if (count($write_host_config) == 0) {
                $this->_error_link[] = '没有配置写数据库信息';
            } else {
                //连接写服务器
                $rand_index = mt_rand(0, count($write_host_config) - 1);
                $this->_write_link = $this->connect($write_host_config[$rand_index]);
                while (!$this->_write_link) {
                    array_splice($write_host_config, $rand_index, 1);
                    if (count($write_host_config) == 0) {
                        break;
                    }
                    //连接其它写数据库
                    $rand_index = mt_rand(0, count($write_host_config) - 1);
                    $this->_write_link = $this->connect($write_host_config[$rand_index]);
                }
            }

            if (count($read_host_config) == 0) {
                $this->_error_link[] = '没有配置读数据库信息';
            } else {
                //连接读数据库
                $rand_index = mt_rand(0, count($read_host_config) - 1);
                $this->_read_link = $this->connect($read_host_config[$rand_index]);
                while (!$this->_read_link) {
                    array_splice($read_host_config, $rand_index, 1);
                    if (count($read_host_config) == 0) {
                        break;
                    }
                    //连接其它读数据库
                    $rand_index = mt_rand(0, count($read_host_config) - 1);
                    $this->_read_link = $this->connect($read_host_config[$rand_index]);
                }
            }

            if (!$this->_write_link && !$this->_read_link) {
                return false;
            }
            if (!$this->_read_link) {
                $this->_read_link = $this->_write_link;
            }
        }
        return true;
    }

    public function getError()
    {
        foreach ($this->_error_link as $item) {
            echo $item . '<br />';
        }
    }

    /**
     * 释放所占用的内存
     * @param object $dataSet 需要释放资源的结果集
     * @access public
     */
    public function close($dataSet = null)
    {
        if ($dataSet) {
            @mysql_free_result($dataSet);
        } else {
            @mysql_free_result($this->dataSet);
            $this->eof = false;
            $this->recordCount = 0;
            $this->recNo = -1;
        }
    }

    /**
     * 对$pass进行数据库加密,返回加密之后的值
     * @param string $pass 要加密的字符串
     * @return string
     * @access public
     */
    public function encodePassword($pass)
    {
        return $this->getValue("SELECT password('$pass') AS pass");
    }

    /**
     * 得到错误信息和错误代号
     * @param integer $queryResult 查询结果
     * @return array
     * @access protected
     */
    protected function errorInfo($link = '')
    {
        $result['message'] = @mysql_error($link);
        $result['code'] = @mysql_errno($link);
        return $result;
    }

    /**
     * 执行SQL语句
     * @param string $sql SQL语句
     * @return object
     * @param int $rowFrom 启始行号，行号从1开始
     * @param int $rowTo 结束行号，值为0表示
     * @access public
     * @see DbQuery::open
     */
    public function execute($sql = '', $rowFrom = 0, $rowTo = self::MAX_ROW_NUM, $error = false)
    {
        //判断是否是读取语句
        if (preg_match("/^(\s*)SELECT/i", $sql) && $sql != 'SELECT LAST_INSERT_ID()' && $this->options['master'] != true) {
            $link = $this->_read_link;
        } else {
            $link = $this->_write_link;
        }

        if (($rowTo != self::MAX_ROW_NUM || $rowFrom != 0) && !preg_match('/ LIMIT /i', $sql)) {
            $nrows = $rowTo - $rowFrom + 1;
            $start = $rowFrom - 1;
            $start = ($start >= 0) ? ((integer)$start) . ',' : '';
            $sql .= ' LIMIT ' . $start . $nrows;
        }

        if ($this->debug == true) {
            echo $sql . '<br />';
        }

        $dataSet = @mysql_query($sql, $link);

        //把错误sql存储到日志中
        if (!$dataSet) {
            global $cfg;
            $sqlError = $this->errorInfo($link);
            $errorMessage = '执行[' . $sql . '出错！[' . $sqlError['code'] . ']: ' . $sqlError['message'];
            WriteLog($errorMessage, $cfg['path']['root'] . 'tmp/mysql_error.log');
        }

        //todo 调试信息
        if (!$dataSet && $error) {
            $sqlError = $this->errorInfo($link);
            $errorMessage = '执行[<b style="color:#F00;">' . $sql . '</b>]出错！<br /> <span style="color:#F00;"> [' . $sqlError['code'] . ']: ' . $sqlError['message'] . '</span><br />';
            echo $errorMessage;
        }

        return $dataSet;
    }

    public function getUpdateAffectedRows()
    {
        return @mysql_affected_rows($this->_write_link);
    }

    /**
     * 执行SQL语句，结果集保存到属性$dataSet中
     * @param string $sql SQL语句
     * @param int $rowFrom 启始行号，行号从1开始
     * @param int $rowTo 结束行号，值为0表示
     * @return object
     * @access public
     * @see DbQuery::execute
     */
    public function open($sql = '', $rowFrom = 0, $rowTo = self::MAX_ROW_NUM)
    {
        $this->dataSet = $this->execute($sql, $rowFrom, $rowTo);
        $this->sql = $sql;
        return $this->dataSet;
    }

    /**
     * 将一行的各字段值拆分到一个数组中
     * @param object $dataSet 结果集
     * @param integer $resultType 返回类型，OCI_ASSOC、OCI_NUM 或 OCI_BOTH
     * @return array
     */
    public function fetchRecord($dataSet = NULL, $resultType = MYSQL_BOTH)
    {
        $result = @mysql_fetch_array(($dataSet) ? $dataSet : $this->dataSet, $resultType);
        return $result;
    }

    /**
     * 取得字段数量
     * @param object $dataSet 结果集
     * @return integer
     */
    public function getFieldCount($dataSet = NULL)
    {

        return mysql_num_fields(($dataSet) ? $dataSet : $this->dataSet);
    }

    /**
     * 取得下一条记录。返回记录号，如果到了记录尾，则返回FALSE
     * @return integer
     * @access public
     * @see getPrior()
     */
    public function next()
    {
        return $this->fetchRecord();
    }

    /**
     * 得到当前数据库时间，格式为：yyyy-mm-dd hh:mm:ss
     * @return string
     * @access public
     */
    public function getNow()
    {
        return $this->getValue('SELECT NOW() AS dateOfNow');
    }

    /**
     * 根据SQL语句从数据表中取数据，只取第一条记录的值，
     * 如果记录中只有一个字段，则只返回字段值。
     * 未找到返回 FALSE
     * @param string $sql SQL语句
     * @return array
     * @access public
     */
    public function getValue($sql = '', $result_type = MYSQL_BOTH)
    {
        $dataSet = $this->execute($sql, 1, 1);
        if ($result = $this->fetchRecord($dataSet, $result_type)) {
            if ($result_type != MYSQLI_ASSOC) {
                $fieldCount = $this->getFieldCount($dataSet);
                $result = ($fieldCount <= 1) ? $result[0] : $result;
            }
            $this->close($dataSet);
            return $result;
        } else {
            return false;
        }
    }

    public function getInsertId()
    {
        return $this->getValue('SELECT LAST_INSERT_ID()');
    }

    public function getSeq($seq = '')
    {
        $this->execute('BEGIN TRANSACTION adodbseq');
        $ok = $this->execute("update $seq with (tablock,holdlock) set id = id + 1", 0, self::MAX_ROW_NUM, false);
        if (!$ok) {
            $this->execute("create table $seq (id float(53))");
            $ok = $this->execute("insert into $seq with (tablock,holdlock) values(1)", 0, self::MAX_ROW_NUM, false);
            if (!$ok) {
                $this->execute('ROLLBACK TRANSACTION adodbseq');
                return false;
            }
            $this->execute('COMMIT TRANSACTION adodbseq');
            return 1;
        }
        $num = $this->getValue("select id from $seq");
        $this->execute('COMMIT TRANSACTION adodbseq');
        return $num;
    }

    /**
     * 表是否存在，返回true
     * @param string $tableName 要查询的表名
     * @return bool
     * @access public
     */
    public function tableIsExists($tableName)
    {
        $sql = 'SELECT * FROM ' . $tableName . ' LIMIT 0,1';
        $result = $this->getValue($sql);
        return $result !== false;
    }

    /**
     * 开始事务
     * @access public
     */
    public function begin()
    {
        $this->transCnt += 1;
        return $this->execute('START TRANSACTION');
    }

    /**
     * 提交事务
     * @access public
     */
    public function commit()
    {
        if ($this->transCnt) {
            $this->transCnt -= 1;
        }
        return $this->execute('COMMIT');
    }

    /**
     * 回滚事务
     * @access public
     */
    public function rollback()
    {
        if ($this->transCnt) {
            $this->transCnt -= 1;
        }
        return $this->execute('ROLLBACK');
    }

    /**
     * 插入新记录
     * @param $tableName
     * @param $fieldArray
     * @param null $whereForUnique
     * @return object
     * @throws DbException
     * @throws Exception
     */
    public function insert($tableName, $fieldArray, $whereForUnique = NULL)
    {
        if (!$tableName || !$fieldArray || !is_array($fieldArray)) {
            throw new Exception('参数 $tableName 或 $fieldArray 的值不合法！');
        }
        if ($whereForUnique) {
            $where = ' WHERE ' . $whereForUnique;
            $isExisted = $this->getValue('SELECT COUNT(*) FROM ' . $tableName . $where);
            if ($isExisted) {
                throw new DbException('记录已经存在！', DbException::DB_RECORD_IS_EXISTED);
            }
        }
        $fieldNameList = array();
        $fieldValueList = array();
        foreach ($fieldArray as $fieldName => $fieldValue) {
            if (!is_int($fieldName)) {
                $fieldNameList[] = $fieldName;
                if (is_array($fieldValue)) {
                    //todo 重新编写，待验证其它地方是否在使用
                    $fieldValueList[] = $fieldValue[0] . '(\'' . $fieldValue[1] . '\')';
                } else {
                    $fieldValueList[] = '\'' . $fieldValue . '\'';
                }
            }
        }
        $fieldName = implode(',', $fieldNameList);
        $fieldValue = implode(',', $fieldValueList);
        $sql = 'INSERT INTO ' . $tableName . '('
            . $fieldName . ') VALUES (' . $fieldValue . ')';
        return $this->execute($sql);
    }

    /**
     * 更新一条记录
     * @param string $tableName 表名
     * @param array $fieldArray 字段数组
     * @param string $whereForUpdate 查询条件
     * @param string $whereForUnique 唯一性条件
     * @return int
     * @access public
     */
    public function update($tableName, $fieldArray, $whereForUpdate = NULL, $whereForUnique = NULL)
    {
        if (!$tableName || !$fieldArray || !is_array($fieldArray)) {
            throw new Exception('参数 $tableName 或 $fieldArray 的值不合法！');
        }
        if ($whereForUnique) {
            $where = ' WHERE ' . $whereForUnique;
            $isExisted = $this->getValue('SELECT COUNT(*) FROM ' . $tableName . $where);
            if ($isExisted) {
                throw new DbException('记录已经存在！', DbException::DB_RECORD_IS_EXISTED);
            }
        }
        $fieldNameValueList = array();
        foreach ($fieldArray as $fieldName => $fieldValue) {
            if (!is_int($fieldName)) {
                if (is_array($fieldValue)) {
                    $fieldNameValueList[] = $fieldName . '=' . $fieldValue[0] . '(\'' . $fieldValue[1] . '\')';
                } else {
                    $fieldNameValueList[] = $fieldName . '=\'' . $fieldValue . '\'';
                }
            }
        }
        $fieldNameValue = implode(',', $fieldNameValueList);
        if ($whereForUpdate) {
            $whereForUpdate = ' WHERE ' . $whereForUpdate;
        }
        $sql = 'UPDATE ' . $tableName
            . ' SET ' . $fieldNameValue . $whereForUpdate;
        return $this->execute($sql);
    }

    /**
     * 选择一条记录
     * @param string $sql sql语句
     * @param string $dataFormat 返回数据格式, 值有"array","hashmap","hashmap_str","dataset"
     * @param int $rowFrom 启始行号，行号从1开始
     * @param int $rowTo 结束行号，值为0表示
     * @result array
     * @access public
     */
    public function select($sql, $dataFormat = 'array', $rowFrom = 0, $rowTo = self::MAX_ROW_NUM)
    {
        $dataSet = $this->execute($sql, $rowFrom, $rowTo);
        switch ($dataFormat) {
            case 'array': //数组
                $result = array();
                while ($data = $this->fetchRecord($dataSet, MYSQLI_ASSOC)) {
                    $result[] = $data;
                }
                $this->close($dataSet);
                break;

            case 'hashmap': //散列表
                $result = array();
                while ($data = $this->fetchRecord($dataSet)) {
                    $result[$data[0]] = $data[1];
                }
                $this->close($dataSet);
                break;

            case 'hashmap_str': //散列表字符串
                $result = array();
                while ($data = $this->fetchRecord($dataSet, OCI_NUM)) {
                    $result[] = $data[0] . '=' . $data[1];
                }
                $result = implode('|', $result);
                $this->close($dataSet);
                break;

            default: //dataset 数据集，当返回数据格式为数据集时，select方法的功能与execute方法相同
                $result = $dataSet;
        }
        return $result;
    }

    /**
     * 返回最大值
     * @param string $tableName 表名
     * @param string $idField 字段名
     * @param string $where 查询条件
     * @return int
     * @access public
     */
    public function getMax($tableName, $idField, $where = NULL)
    {
        $where = ($where) ? (' WHERE ' . $where) : '';
        return $this->getValue('SELECT MAX(' . $idField . ') FROM ' . $tableName . $where);
    }

    public function field($field)
    {
        $this->options['field'] = empty($field) ? '*' : $field;
        return $this;
    }

    public function table($table, $alias = '')
    {
        $this->options['table'] = array($table, $alias);
        return $this;
    }

    /**
     * Join方法，表名必需为全名
     * @param string $join Join条件
     * @param string $type Join方式
     * @return $this
     */
    public function join($join, $type = 'INNER')
    {
        $this->options['join'][] = array($join, $type);
        return $this;
    }

    public function where($where)
    {
        if (isset($this->options['where'])) {
            if (is_array($where)) {
                $this->options['where'] = array_merge($this->options['where'], $where);
            } else {
                $this->options['where'] = array_merge($this->options['where'], array($where));
            }
        } else {
            if (is_array($where)) {
                $this->options['where'] = $where;
            } else {
                $this->options['where'] = array($where);
            }
        }
        return $this;
    }

    public function order($order)
    {
        $this->options['order'] = $order;
        return $this;
    }

    /**
     * 限制搜索的起始值
     * @param mixed $offset 开始位置
     * @param null $length 读取长度
     * @return $this
     */
    public function limit($offset, $length = null)
    {
        if (is_null($length) && strpos($offset, ',')) {
            list($offset, $length) = explode(',', $offset);
        }
        $this->options['limit'] = intval($offset) . ($length ? ',' . intval($length) : '');
        return $this;
    }

    /**
     * 是否启用数据缓存
     * @param null $key  key为true时根据sql生成对应的key，并进行数据缓存，不为true时根据指定的key保存数据
     * @param null $expire 数据缓存有效期
     * @return $this
     */
    public function cache($key = null, $expire = null)
    {
        // 增加快捷调用方式 cache(10) 等同于 cache(true, 10)
        if(is_numeric($key) && is_null($expire)){
            $expire = $key;
            $key = true;
        }
        if(false !== $key) {
            $this->options['cache'] = array(
                'key' => $key,
                'expire' => $expire
            );
        }
        return $this;
    }

    /**
     * 搜索单条数据
     * @return array 返回数据详细信息
     */
    public function one()
    {
        $this->options['operation'] = 'SELECT';
        $this->options['limit'] = 1;
        $sql = $this->parseSQL();
        //判断是否开启缓存
        if (isset($this->options['cache'])) {
            //如果没有指定key则根据查询条件生成
            if ($this->options['cache']['key'] === true) {
                $key = md5($sql);
            } else {
                $key = $this->options['cache']['key'];
            }
            $Cache = Cache::getInstance();
            $data = $Cache->get($key);
            if ($data !== false) {
                //清除条件
                $this->options = array();
                return $data;
            }
        }
        $data = $this->getValue($sql, MYSQL_ASSOC);
        if (isset($this->options['cache'])) {
            $Cache->set($key, $data, $this->options['cache']['expire']);
        }
        //清除条件
        $this->options = array();
        //返回数据
        return $data;
    }

    /**
     * 执行列表搜索
     * @return array 返回数据数组
     */
    public function all()
    {
        $this->options['operation'] = 'SELECT';
        if (empty($this->options['limit'])) {
            $this->options['limit'] = self::MAX_ROW_NUM;
        }
        $sql = $this->parseSQL();
        //判断是否开启缓存
        if (isset($this->options['cache'])) {
            //如果没有指定key则根据查询条件生成
            if ($this->options['cache']['key'] === true) {
                $key = md5($sql);
            } else {
                $key = $this->options['cache']['key'];
            }
            $Cache = Cache::getInstance();
            $data = $Cache->get($key);
            if ($data !== false) {
                //清除条件
                $this->options = array();
                return $data;
            }
        }
        $data = $this->select($sql);
        if (isset($this->options['cache'])) {
            $Cache->set($key, $data, $this->options['cache']['expire']);
        }
        //清除条件
        $this->options = array();
        //返回数据
        return $data;
    }

    /**
     * 插入或更新数据
     * @param array $data 要操作的数据数组
     * @param array $where $where = true时强制进行数据更新，否则如果$where为空执行数据插入操作
     * @return array|object 更新影响的行数或是执行操作是否成功
     */
    public function save($data = array(), $where = array())
    {
        if (!empty($data)) {
            $this->options['data'] = $data;
        }
        if (empty($this->options['data'])) {
            exit('要添加的数据不能为空');
        }
        if (!empty($where)) {
            $this->options['where'] = $where;
        }
        if (!empty($this->options['where']) || $this->options['where'] === true) {
            //更新操作
            $this->options['operation'] = 'UPDATE';
        } else {
            //添加操作
            $this->options['operation'] = 'INSERT';
        }
        $sql = $this->parseSQL();
        $result = $this->execute($sql);
        if (empty($this->options['where'])) {
            $result = $this->getInsertId();
        }
        $this->options = array();
        return $result;
    }

    public function insertAll($data = array())
    {
        if (empty($data)) {
            exit('要添加的数据不能为空');
        }
        $this->options['data'] = $data;
        $this->options['operation'] = 'INSERT';
        $sql = $this->parseSQL();
        $result = $this->execute($sql);
        $this->options = array();
        return $result;
    }

    /**
     * 删除数据
     * @param array $where 删除数据时的条件，条件为空时不删除数据，当$where = true 时可以强制删除数据
     * @return int 返回影响的行数，操作失败返回false，操作成功但未删除数据，返回true
     */
    public function del($where = array())
    {
        if (!empty($where)) {
            $this->options['where'] = $where;
        }
        if (empty($this->options['where']) && $this->options['where'] !== true) {
            return false;
        }
        $this->options['operation'] = 'DELETE';
        $sql = $this->parseSQL();
        $result = $this->execute($sql);
        $this->options = array();
        return $result;
    }

    /**
     * 设置data数组用于数据更新和插入
     * @param array $data 字段值对应的数据
     * @return $this
     */
    public function data($data = array())
    {
        $this->options['data'] = $data;
        return $this;
    }

    /**
     * 统计数据
     * @param string $field 要统计的字段，默认统计全部
     * @return int 数据总数
     */
    public function count($field = '*')
    {
        $this->options['operation'] = 'SELECT';
        $this->options['field'] = 'count(' . $field . ')';
        $this->options['limit'] = 1;
        $sql = $this->parseSQL();
        //判断是否开启缓存
        if (isset($this->options['cache'])) {
            //如果没有指定key则根据查询条件生成
            if ($this->options['cache']['key'] === true) {
                $key = md5($sql);
            } else {
                $key = $this->options['cache']['key'];
            }
            $Cache = Cache::getInstance();
            $data_count = $Cache->get($key);
            if ($data_count !== false) {
                //清除条件
                $this->options = array();
                return $data_count;
            }
        }
        $data_count = $this->getValue($sql, MYSQL_NUM);
        if (isset($this->options['cache'])) {
            $Cache->set($key, $data_count, $this->options['cache']['expire']);
        }
        //清除条件
        $this->options = array();
        //返回数据
        return $data_count;
    }

    public function sum($field)
    {
        if (empty($field)) {
            return false;
        }
        $this->options['operation'] = 'SELECT';
        $this->options['field'] = 'sum(' . $field . ')';
        $this->options['limit'] = 1;
        $sql = $this->parseSQL();
        //判断是否开启缓存
        if (isset($this->options['cache'])) {
            //如果没有指定key则根据查询条件生成
            if ($this->options['cache']['key'] === true) {
                $key = md5($sql);
            } else {
                $key = $this->options['cache']['key'];
            }
            $Cache = Cache::getInstance();
            $data = $Cache->get($key);
            if ($data !== false) {
                //清除条件
                $this->options = array();
                return $data;
            }
        }
        $data = $this->getValue($sql, MYSQL_NUM);
        if (isset($this->options['cache'])) {
            $Cache->set($key, $data, $this->options['cache']['expire']);
        }
        //清除条件
        $this->options = array();
        //返回数据
        return $data;
    }

    public function setInc($field, $step = 1)
    {
        if (empty($field)) {
            return false;
        }
        $this->options['operation'] = 'UPDATE';
        $data = array(
            $field => array('`' . $field . '` + ' . $step)
        );
        return $this->save($data);
    }

    public function setDec($field, $step = 1)
    {
        if (empty($field)) {
            return false;
        }
        $this->options['operation'] = 'UPDATE';
        $data = array(
            $field => array('`' . $field . '` - ' . $step)
        );
        return $this->save($data);
    }

    public function master($type = false)
    {
        $this->options['master'] = $type;
        return $this;
    }

    /**
     * 解析条件生成sql语句
     * @return string 返回生成的sql语句
     */
    public function parseSQL()
    {
        if (!isset($this->options['table'])) {
            exit('数据表名称不能为空');
        }
        if (empty($this->options['field'])) {
            $this->options['field'] = !empty($this->options['table'][1]) ? $this->options['table'][1] . '.*' : '*';
        }
        $table = '`' . $this->db_prefix . $this->options['table'][0] . '`';
        if (!empty($this->options['table'][1])) {
            $table .= ' AS `'. $this->options['table'][1].'`';
        }
        //where条件处理
        $where_sql = '';
        if (!empty($this->options['where']) && $this->options['where'] !== true) {
            if (is_array($this->options['where'])) {
                $where = array();
                foreach ($this->options['where'] as $key => $val) {
                    //AND连接查询
                    //todo 以后增加OR查询
                    if (is_string($key)) {
                        //判断$val是否是数组
                        if (is_array($val)) {
                            $where[] = '`' . $key . '` = ' . (empty($val[1]) ? $val[0] : $val[1] . '(\'' . $val[0] . '\')');
                        } else {
                            $where[] = '`' . $key . '` = \'' . $val . '\'';
                        }
                    } else {
                        $where[] = $val;
                    }
                }
                if ($where) {
                    $where_sql = ' WHERE ' . implode(' AND ', $where);
                }
            } else {
                $where_sql = ' WHERE ' . $this->options['where'];
            }
        }
        //判断操作类型
        $operation = isset($this->options['operation']) ? trim($this->options['operation']) : 'SELECT';
        $sql = '';
        switch ($operation) {
            case 'SELECT':
                $sql = 'SELECT ' . $this->options['field'] . ' FROM ' . $table;
                if (isset($this->options['join'])) {
                    foreach ($this->options['join'] as $val) {
                        $join = $val[0];
                        $type = $val[1];
                        if (empty($join)) {
                            continue;
                        }
                        if (!stripos($join, 'JOIN') !== false) {
                            $join = $type . ' JOIN ' . $join;
                        }
                        $sql .= ' ' . $join;
                    }
                }
                if ($where_sql) {
                    $sql .= $where_sql;
                }
                if (!empty($this->options['order'])) {
                    $sql .= ' ORDER BY ' . $this->options['order'];
                }
                if (!empty($this->options['limit'])) {
                    $sql .= ' LIMIT ' . $this->options['limit'];
                }
                break;
            case 'INSERT':
                $field_name = array();
                $field_value = array();
                $is_multi_data = false;
                foreach ($this->options['data'] as $key => $val) {
                    if (is_numeric($key) == true) {
                        $is_multi_data = true;
                        $field = array();
                        foreach ($val as $k => $v) {
                            if ($key == 0) {
                                $field_name[] = '`' . $k . '`';
                            }
                            if (is_array($v)) {
                                //数组：val[0] 值或直接表达式
                                //val[1]为空直接设置表达式或值，val[1]不为空时做为函数名，只支持单参数函数
                                $field[] = empty($v[1]) ? $v[0] : $v[1] . '(\'' . $v[0] . '\')';
                            } else {
                                $field[] = '\'' . $v . '\'';
                            }
                        }
                        $field_value[] = $field;
                    } else {
                        $field_name[] = '`' . $key . '`';
                        if (is_array($val)) {
                            //数组：val[0] 值或直接表达式
                            //val[1]为空直接设置表达式或值，val[1]不为空时做为函数名，只支持单参数函数
                            $field_value[] = empty($val[1]) ? $val[0] : $val[1] . '(\'' . $val[0] . '\')';
                        } else {
                            $field_value[] = '\'' . $val . '\'';
                        }
                    }
                }
                if (empty($field_name) || empty($field_value)) {
                    return false;
                }
                if ($is_multi_data == true) {
                    $sql = array();
                    foreach ($field_value as $key => $field) {
                        $sql[] = '(' . implode(', ', $field) . ')';
                    }
                    $sql = implode(', ', $sql);
                } else {
                    $sql = '(' . implode(', ', $field_value) . ')';
                }
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $field_name) . ') VALUES ' . $sql;
                break;
            case 'UPDATE':
                $field_item = array();
                foreach ($this->options['data'] as $key => $val) {
                    if (is_array($val)) {
                        if (empty($val[1])) {
                            $field_item[] = '`' . $key . '` = ' . $val[0];
                        } else {
                            $field_item[] = '`' . $key . '` = ' . $val[1] . '(\'' . $val[0] . '\')';
                        }
                    } else {
                        $field_item[] = '`' . $key . '` = \'' . $val . '\'';
                    }
                }
                $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $field_item) . $where_sql;
                if (!empty($this->options['order'])) {
                    $sql .= ' ORDER BY ' . $this->options['order'];
                }
                if (!empty($this->options['limit'])) {
                    $sql .= ' LIMIT ' . $this->options['limit'];
                }
                break;
            case 'DELETE';
                $sql = 'DELETE FROM ' . $table . $where_sql;
                if (!empty($this->options['order'])) {
                    $sql .= ' ORDER BY ' . $this->options['order'];
                }
                if (!empty($this->options['limit'])) {
                    $sql .= ' LIMIT ' . $this->options['limit'];
                }
                break;
            default:

        }
        return $sql . ';';
    }

    public function __destruct()
    {
        //释放连接
        if (!$this->_read_link && $this->_read_link === $this->_write_link) {
            mysql_close($this->_read_link);
        } else {
            if (!$this->_read_link) {
                mysql_close($this->_read_link);
            }
            if (!$this->_write_link) {
                mysql_close($this->_write_link);
            }
        }
    }
}