<?php
/**
 * 数据库封装类
 * email geow@qq.com
 * @package 2.0
 */


require_once('exception/DbException.class.php');

/**
 * DataSource 数组库连接类
 * @package Util
 */
class DataSource
{
    /**
     * 打开数据库连接
     * @return bool
     * @access public
     */
    public static function open($config)
    {
        switch (strtolower($config['db_type'])) {
            /*case 'oracle':
                $result = $this->connect = @oci_pconnect($config['db_user'], $config['db_password'], $config['db_name']);
                require_once('DbQueryForOracle.class.php');
                break;
            case 'mssql':
                $result = $this->connect = @mssql_connect($config['db_host'], $config['db_user'], $config['db_password']);
                if ($result) {
                    if ($config['db_name'] != '') {
                        if (!@mssql_select_db($config['db_name'])) {
                            throw new DbException('数据库不存在', DbException::DB_OPEN_FAILED);
                        }
                    }

                }
                require_once('DbQueryForMssql.class.php');
                break;*/
            case 'mysql':
                require_once('DbQueryForMysql.class.php');
                $result = new DbQueryForMysql($config);
                return $result;
                break;
            default :
                $result = false;
        }
        if (!$result) {
            $errorMessage = '连接数据库服务器<font color="#FF0000">' . $config['db_host'] . '</font></b>失败' . '或数据库“<b><font color="#FF0000">' . $config['db_name'] . '</font></b>”不存在！';
            throw new DbException($errorMessage, DbException::DB_OPEN_FAILED);
        }
        return ($result !== false);
    }
}