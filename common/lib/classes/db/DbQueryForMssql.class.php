<?php
/**
 * sql server 链接操作参数
 *
 * @author net geow@qq.com
 * @package 1.0
 * @version $Id$
 */

/**
 * Sql Server 数据库的dbQuery类
 * @package Util
 */
class DbQueryForMssql {
	/**
	 * select方法返回的最大记录数
	 */
	const MAX_ROW_NUM = 100000;

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
	
	public $transCnt 		= 0;
	
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
			@mssql_free_statement($dataSet);
		} else {
			@mssql_free_statement($this->dataSet);
			$this->eof = false ;
			$this->recordCount = 0 ;
			$this->recNo = -1 ;
		}
	}
	function __destruct()
	{
		@mssql_free_result($this->dataSet);
		@mssql_free_statement($this->dataSet);
		@mssql_close($this->ds->connect);
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
		$result['message'] = mssql_get_last_message();
		@mssql_select_db($this->ds->name,$this->ds->connect);
		/*if (!@mysql_select_db($this->ds->name)) {
			throw new DbException('数据库不存在', DbException::DB_OPEN_FAILED);
		}*/
		$id = @mssql_query("select @@ERROR", $this->ds->connect);
		if (!$id) {
			return false;
		}
		$arr = mssql_fetch_array($id);
		@mssql_free_result($id);
		if (is_array($arr)) {
			$result['code'] = $arr[0];
	    } else {
			return $result['code'] = -1;
		}
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
	public function execute($sql = '', $rowFrom = 0, $rowTo = self::MAX_ROW_NUM, $error = true) {
		//echo $this->ds->name;
		if ($rowTo != self::MAX_ROW_NUM) {
			$nrows = $rowTo - $rowFrom + 1; 
		}
		$offset = $rowFrom;
		if ($nrows > 0) {
			$nn = $nrows + $offset - 1;
			$sql = preg_replace('/(^\s*select\s+(distinctrow|distinct)?)/i', '\\1 top ' . $nn . ' ', $sql);
		}
		
		@mssql_select_db($this->ds->name,$this->ds->connect);
		/*if (!@mysql_select_db($this->ds->name)) {
			throw new DbException('数据库不存在', DbException::DB_OPEN_FAILED);
		}*/
		$dataSet = @mssql_query($sql,  $this->ds->connect);
		//echo $sql .'<br/><br/><br/><br/>';
		if (!$dataSet && $error) {
			$sqlError = $this->errorInfo();
			$errorMessage = '执行[<b><font color="#FF0000">' . $sql 
					. '</font></b>]出错！<br> <font color=#FF0000> ['
					. $sqlError['code'] . ']: '
					. $sqlError['message'] . '</font>' ;
			$this->error(DbException::DB_QUERY_ERROR, $errorMessage);
		}
	
		if ($offset) {
			$offset = $offset-1;//var_dump($dataSet);echo 'abc';
			$resultNum = mssql_num_rows($dataSet);
			if ($resultNum<$offset) {
				@mssql_data_seek($dataSet, $resultNum-1);
			} else {
				@mssql_data_seek($dataSet, $offset);
			}
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
	public function fetchRecord($dataSet=NULL, $resultType=MSSQL_BOTH) {
		$result = @mssql_fetch_array(($dataSet) ? $dataSet : $this->dataSet, $resultType);
		if (is_array($result)) {
			foreach ($result as $key => $value) {
				if (!is_numeric($key)) {
					$result[strtolower($key)] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * 取得字段数量
	 * @param object $dataSet 结果集
	 * @return integer
	 */
	public function getFieldCount($dataSet = NULL) {
	
		return mssql_num_fields(($dataSet) ? $dataSet : $this->dataSet);
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
	public function getValue($sql = '',$dataFormat=MSSQL_BOTH) { 
		$dataSet = $this->execute($sql, 1, 1);

		if ($result = $this->fetchRecord($dataSet,$dataFormat)) {
			$fieldCount = $this->getFieldCount($dataSet);
			$idx = 0;
			if($dataFormat == MSSQL_ASSOC){//如果使用MSSQL_ASSOC,且只有一列时,则需要知道第一列的列名.
				$firstColumnInfo = mssql_fetch_field ($dataSet ,0 );
				$idx = $firstColumnInfo->name;//column name
			}
			$this->close($dataSet);//print_r($result);
			return ($fieldCount<=1) ? $result[$idx] : $result;
		} else {
			return false ;
		}
	}
	
	/**
	 * 取ID自递增值
	 *
	 * @return int
	 * @access public
	 */	
	public function getInsertId() {
		return $this->getValue('select @@identity');
	}
	
	/**
	 * 取序列
	 * @param $seq 序列名
	 * @return int
	 * @access public
	 */	
	public function getSeq($seq = '') { 
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
	public function tableIsExists($tableName) {
		return false;
	}
	
	/**
	 * 开始事务
	 * @access public
	 */
	public function begin() {
		$this->transCnt += 1;
	   	$this->execute('BEGIN TRAN');
	   	return true;
	}
	
	/**
	 * 提交事务
	 * @access public
	 */
	public function commit() {
		if ($this->transCnt) {
			$this->transCnt -= 1;
		}
		$this->execute('COMMIT TRAN');
		return true;
	}
	
	/**
	 * 回滚事务
	 * @access public
	 */
	public function rollback() {
		if ($this->transCnt){
			$this->transCnt -= 1;
		}
		$this->execute('ROLLBACK TRAN');
		return true;
	}
	
	/**
	 * 插入一条记录
	 * @param string $tableName 表名
	 * @param array $fieldArray 字段数组
	 * @param string $whereForUnique 唯一性条件
	 * @return int
	 * @access public
	 */
	public function insert($tableName, $fieldArray, $whereForUnique = NULL) {
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
				$fieldValueList[] = '\'' . $fieldValue . '\'';
			}
		}
		$fieldName = implode(',', $fieldNameList);
		$fieldValue = implode(',', $fieldValueList);
		$sql = 'INSERT INTO ' . $tableName . '('
					. $fieldName . ') VALUES (' . $fieldValue . ')';
		//return $sql;
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
	public function update($tableName, $fieldArray, $whereForUpdate=NULL, $whereForUnique=NULL) {
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
				$fieldNameValueList[] = $fieldName . '=\'' . $fieldValue . '\'';
			}
		}
		$fieldNameValue = implode(',', $fieldNameValueList);
		if ($whereForUpdate) {
			$whereForUpdate = ' WHERE ' . $whereForUpdate;
		}
		$sql = 'UPDATE ' . $tableName 
				. ' SET ' . $fieldNameValue . $whereForUpdate;
		return $this->execute($sql);
		//return $sql;
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
	public function select($sql, $dataFormat = 'array', $rowFrom = 0, $rowTo = self::MAX_ROW_NUM) {
		$dataSet = $this->execute($sql, $rowFrom, $rowTo);
		switch ($dataFormat) {
		case 'array': //数组
			$result = array();
			$isMultiField = ($this->getFieldCount($dataSet) > 1);
			$i = 0;
			while ($data = $this->fetchRecord($dataSet)) {
				$result[$i] = ($isMultiField) ? $data : $data[0];
				$i++;
			}
			$this->close($dataSet);
			break;

		case 'arrayassoc': //数组,有BUG,这里面需要用列名为索引!当只有一列有时候
			$result = array();
			$isMultiField = ($this->getFieldCount($dataSet) > 1);
			$i = 0;
			while ($data = $this->fetchRecord($dataSet,MSSQL_ASSOC)) {
				$idx = 0;
				if(!$isMultiField){//只有一列的话
					if($dataFormat == MSSQL_ASSOC){//用MSSQL_ASSOC,且只有一列时,则需要知道第一列的列名.
					$firstColumnInfo = mssql_fetch_field ($dataSet ,0 );
					$idx = $firstColumnInfo->name;//column name
					}
				}
				$result[$i] = ($isMultiField) ? $data : $data[$idx];
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
