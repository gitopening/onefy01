<?php
/**
 * Oricle 数据库操作类
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */


define('OCI_RETURN_ALLS',OCI_BOTH + OCI_RETURN_LOBS);
/**
 *  Oracle 数据库的DbQueryForOracle类
 *
 * @package .lib.db
 * @author 魏永增
 */
class DbQueryForOracle {
	/**
	 * select方法返回的最大记录数
	 */
	const MAX_ROW_NUM = 1000;

	/**
	 * 数据查询结果集对象
	 * @var object $dataSet
	 */
	public $dataSet			= NULL ;

	/**
	 * 数据源对象
	 * @var object $ds
	 */
	public $ds				= NULL ;

	/**
	 * 查询的SQL语句
	 * @var string $sql
	 */
	public $sql				= '' ;
	
	/**
	 * 执行查询的模式，值为 OCI_COMMIT_ON_SUCCESS 或 OCI_DEFAULT
	 * @var string $excuteMode
	 */
	public $executeMode	= OCI_COMMIT_ON_SUCCESS ;

	/**
	 * 构造函数
	 * @param object $ds 数据库
	 * @param string $sql 要初始化查询的SQL语句
	 */
	function __construct($ds=NULL , $sql=NULL) {
		if (!$ds) {
			$this->error(DbException::DB_UNCONNECTED, '数据库还未连接。');
		} else {
			$this->ds = $ds;
			if ($sql) {
				$this->open($sql);
			}
		}
	}
	
	/**
	 * 释放所占用的内存
	 * @param object $dataSet 需要释放资源的结果集
	 * @access public
	 */
	public function close($dataSet=NULL) {
		if ($dataSet) {
			@oci_free_statement($dataSet);
		} else {
			@oci_free_statement($this->dataSet);
			$this->eof = false ;
			$this->recordCount = 0 ;
			$this->recNo = -1 ;
		}
	}
	
	/**
	 * 对$pass进行数据库加密,返回加密之后的值
	 * @param string $pass 要加密的字符串
	 * @return string
	 * @access public
	 */
	public function encodePassword($pass) {
		return md5($pass);
	}
	
	/**
	 * 得到错误信息和错误代号
	 * @param integer $queryResult 查询结果
	 * @return array
	 * @access protected
	 */
	protected function errorInfo($queryResult = NULL) {
		$result = oci_error($this->ds->connect);
		return $result;
	}
	
	/**
	 * 错误信息处理
	 * @param string $errorId 错误ID
	 * @param string $errorMessage 错误信息
	 * @access protected
	 */
	protected function error($errorId, $errorMessage) {
		throw new DbException($errorMessage, $errorId);
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
	public function execute($sql = '', $rowFrom = 0, $rowTo = self::MAX_ROW_NUM) {
		if ($rowFrom != 0 || $rowTo != self::MAX_ROW_NUM) {
			$sql = 'select * from (select row_.*, rownum rownum_ from ('
					. $sql . ') row_ where rownum <= '
					. $rowTo . ') where rownum_ >= ' . $rowFrom;
		}
		//echo $sql . '<br>';
		//$start = microtime(true);
		$dataSet = @oci_parse($this->ds->connect, $sql);
		$executeSucceed = @oci_execute($dataSet, $this->executeMode);
		//echo 'sql:'. ((string)(microtime(true)-$start)) . '<br>';
		if (!$dataSet || !$executeSucceed) {
			$sqlError = $this->errorInfo();
			$errorMessage = '执行[<b><font color="#FF0000">' . $sql 
					. '</font></b>]出错！<br> <font color=#FF0000> ['
					. $sqlError['code'] . ']: '
					. $sqlError['message'] . '</font>' ;
			$this->error(DbException::DB_QUERY_ERROR, $errorMessage);
		}
		return $dataSet;
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
	public function open($sql='', $rowFrom = 0, $rowTo = self::MAX_ROW_NUM) {
		$this->dataSet = $this->execute($sql, $rowFrom, $rowTo);
		$this->sql = $sql ;
		return $this->dataSet;
	}

	/**
	 * 将一行的各字段值拆分到一个数组中
	 * @param object $dataSet 结果集
	 * @param integer $resultType 返回类型，OCI_ASSOC、OCI_NUM 或 OCI_BOTH
	 * @return array
	 */
	public function fetchRecord($dataSet=NULL, $resultType=OCI_BOTH) {
		$result = @oci_fetch_array(($dataSet) ? $dataSet : $this->dataSet, $resultType);
		if (is_array($result)) {
			foreach ($result as $key => $value) {
				if (!is_numeric($key)) {
					$result[strtolower($key)] = $value;
				}
			}
		}
		return $result;
	}


	public function snext() {
		$result = @oci_fetch_array($this->dataSet, OCI_BOTH);
		return $result;
	}
	/**
	 * 取得字段数量
	 * @param object $dataSet 结果集
	 * @return integer
	 */
	public function getFieldCount($dataSet = NULL) {
		return oci_num_fields(($dataSet) ? $dataSet : $this->dataSet);
	}
	
	/**
	 * 取得下一条记录。返回记录号，如果到了记录尾，则返回FALSE
	 * @return integer
	 * @access public
	 * @see getPrior()
	 */
	public function next() {
		return $this->fetchRecord();
	}
	
	/**
	 * 得到当前数据库时间，格式为：yyyy-mm-dd hh:mm:ss
	 * @return string
	 * @access public
	 */
	public function getNow() {
		return $this->getValue('SELECT TO_CHAR(SYSDATE, \'YYYY-MM-DD HH24:MI:SS\') dateOfNow FROM DUAL');
	}
	
	/**
	 * 根据SQL语句从数据表中取数据，只取第一条记录的值，
	 * 如果记录中只有一个字段，则只返回字段值。
	 * 未找到返回 FALSE
	 *
	 * @param string $sql SQL语句
	 * @return array
	 * @access public
	 */
	public function getValue($sql = '', $hasClob = false) { 
		$dataSet = $this->execute($sql, 1, 1);
		if ($hasClob) {
			$returnType = OCI_RETURN_ALLS;
		} else {
			$returnType = OCI_BOTH;
		}
		if ($result = $this->fetchRecord($dataSet,$returnType)) {
			$fieldCount = $this->getFieldCount($dataSet);
			$this->close($dataSet);
			return ($fieldCount<=2) ? $result[0] : $result;
		} else {
			return false ;
		}
	}

	public function getSeq($seqName = '') { 
		$dataSet = $this->execute('SELECT '.$seqName.'.nextval from sys.dual');
		if ($result = $this->fetchRecord($dataSet)) {
			$fieldCount = $this->getFieldCount($dataSet);
			$this->close($dataSet);
			return ($fieldCount<=2) ? $result[0] : $result;
		} else {
			return false ;
		}
	}
	/**
	 * 表是否存在，返回true
	 * @param string $tableName 要查询的表名
	 * @return bool
	 * @access public
	 */
	public function tableIsExists($tableName) {
		return false;
	}
	
	/**
	 * 开始事务
	 * @access public
	 */
	public function begin() {
		$this->executeMode = OCI_DEFAULT;
	}
	
	/**
	 * 提交事务
	 * @access public
	 */
	public function commit() {
		oci_commit($this->ds->connect);
		$this->executeMode = OCI_COMMIT_ON_SUCCESS;
	}
	
	/**
	 * 回滚事务
	 * @access public
	 */
	public function rollback() {
		oci_rollback($this->ds->connect);
		$this->executeMode = OCI_COMMIT_ON_SUCCESS;
	}
	
	/**
	 * 插入一条记录
	 * @param string $tableName 表名
	 * @param array $fieldArray 字段数组
	 * @param string $whereForUnique 唯一性条件
	 * @return int
	 * @access public
	 */
	public function insert($tableName, $fieldArray, $whereForUnique = NULL, $clobField = NULL ) {
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
				if ($clobField && $clobField==$fieldName) {
					$fieldValueList[] = 'EMPTY_CLOB()';
					$hasClob = true;
					$clobStr = str_replace("''","'",$fieldValue);
				} else {
					$fieldValueList[] = '\'' . $fieldValue . '\'';
				}
			}
		}
		$fieldName = implode(',', $fieldNameList);
		$fieldValue = implode(',', $fieldValueList);
		$sql = 'INSERT INTO ' . $tableName . '('
					. $fieldName . ') VALUES (' . $fieldValue . ')';
		if ($hasClob) {
			$sql .= ' RETURNING content INTO :clob_string';
			$dataSet = oci_parse($this->ds->connect, $sql);
			$clob = oci_new_descriptor($this->ds->connect, OCI_D_LOB);
			oci_bind_by_name($dataSet, ':clob_string', $clob, -1, OCI_B_CLOB);
			oci_execute($dataSet, OCI_DEFAULT);
			$clob->save($clobStr);
			oci_commit($this->ds->connect);
			return $dataSet;
		} else {
			return $this->execute($sql);
		}
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
	public function update($tableName, $fieldArray, $whereForUpdate=NULL, $whereForUnique=NULL, $clobField = NULL) {
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
				if ($clobField && $clobField==$fieldName) {
					$fieldNameValueList[] = $fieldName . '=EMPTY_CLOB()';
					$hasClob = true;
					$clobStr = str_replace("''","'",$fieldValue);
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
		if ($hasClob) {
			$sql .= ' RETURNING content INTO :clob_string';
			$dataSet = oci_parse($this->ds->connect, $sql);
			$clob = oci_new_descriptor($this->ds->connect, OCI_D_LOB);
			oci_bind_by_name($dataSet, ':clob_string', $clob, -1, OCI_B_CLOB);
			oci_execute($dataSet, OCI_DEFAULT);
			$clob->save($clobStr);
			oci_commit($this->ds->connect);
			return $dataSet;
		} else {
			return $this->execute($sql);
		}
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
	public function select($sql, $dataFormat = 'array', $rowFrom = 0, $rowTo = self::MAX_ROW_NUM, $hasClob = false) {
		$dataSet = $this->execute($sql, $rowFrom, $rowTo);
		switch ($dataFormat) {
		case 'array': //数组
			$result = array();
			$isMultiField = ($this->getFieldCount($dataSet) > 1);
			$i = 0;
			if ($hasClob) {
				$returnType = OCI_RETURN_ALLS;
			} else {
				$returnType = OCI_BOTH;
			}
			while ($data = $this->fetchRecord($dataSet,$returnType)) {
				$result[$i] = ($isMultiField) ? $data : $data[0];
				$i++;
			}
			$this->close($dataSet);
			break;

		case 'hashmap': //散列表
			$result = array();
			while ($data = $this->fetchRecord($dataSet)) {
				$result[ $data[0] ] = $data[1];
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
	public function getMax($tableName, $idField, $where = NULL) {
		$where = ($where) ? (' WHERE ' . $where) : '';
		return $this->getValue('SELECT MAX(' . $idField . ') FROM ' . $tableName . $where);
	}
}
?>
