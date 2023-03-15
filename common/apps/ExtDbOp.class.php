<?php
/**
 * PHP项目组使用 - PHP项目类库
 * Copyright (c) 2006-2008 西岸网讯
 * All rights reserved.
 * 未经许可，禁止用于商业用途！
 *
 * @package    Apps
 * @author     林少伟 (singlecanoe@126.com)
 * @copyright  2006-2008 Walk Watch
 * @version    v2.0
 */
 
 class ExtDbOp {
 	/**
 	 * 数据库操作对象
 	 *
 	 * @var unknown_type
 	 */
 	var $db = null;
 	/**
 	 * 表名
 	 *
 	 * @var unknown_type
 	 */
	var $tName = '';
	/**
	 * 表前缀
	 *
	 * @var unknown_type
	 */
	var $pre = '';
	function __construct($db)
	{
		$this->db = $db;
	}
	
	/**
	 * 查找相应的结果列表
	 * @param string $columns 	查找的列,用","隔开
	 * @param string $condition 条件,不需要where
	 * @param string $order 	排序顺序
	 * @param string $top		前几条,eg:'top 5'
	 * @param string $table		表名,默认空,但实际默认为$this->tName
	 * @access public
	 * @return array
	 */
	function findAll($columns='*',$condition='',$order='',$top='',$table = null)
	{
		if($table == '' || is_null($table)){$table = $this->tName;}
		if($condition && strtolower(substr(trim($condition),0,3)) != 'and' && strtolower(substr(trim($condition),0,5)) != 'order' && strtolower(substr(trim($condition),0,6)) != 'count('){
			$condition = ' and '.$condition;
		}
		
		if(trim($order)){
			if(strtolower(substr(trim($order),0,6)) != 'order '){
				$order = ' order by '.$order; 
			}
		}
		
		$sql = "select $top $columns from $table where 1=1 $condition $order";
//		echo $sql . '<br/>';
		if(strtolower(substr(trim($columns),0,6)) == 'count('){ 
			return $this->getValue($sql);
		}else{
			return $this->db->select($sql,'array');
		}	
	}
	
	/**
	 * 查找指定条件的单个结果
	 * @param string $sql 	
	 * @param string MSSQL_BOTH MSSQL_NUM MSSQL_ASSOC
	 * @access public
	 * @return array
	 */
	function getValue($sql,$dateFormat=MSSQL_ASSOC)
	{
		return $this->db->getValue($sql,$dateFormat);
	}
	
	/**
	 * 传入分页对象的结果集对象
	 * @param array $pageLimit 分页始终记录位置
	 * @param string $search 查询条件,默认为''
	 * @param string $columns 列名,默认为"*"
	 * @access public
	 * @return array
	 */
	function getList($pageLimit,$search='',$columns='*',$order='',$table=null) {
		if($table == '' || is_null($table)){$table = $this->tName;}
		if(strlen(trim($search))>0 && substr(trim($search),0,3)!='and' && substr(trim($search),0,5)!='order')
		{//echo substr(0,5,trim($search));
			$search  = 'and '.trim($search);
		} 
		$this->db->open('select '.$table.'.'.$columns.' from '.$table.' where 1=1 '.$search . ' '.$order, $pageLimit['rowFrom'],$pageLimit['rowTo']);
		//echo('select '.$table.'.'.$columns.' from '.$table.' where 1=1 '.$search . ' '.$order);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	}
	
	/**
	 * 通过语句及分页对象直接返回指定分页的记录数组
	 * 2008-07-25
	 * @param array $pageLimit
	 * @param string $sql
	 * @return mixed
	 */
	function getListBySql($pageLimit,$sql){
		$result = array();
		if(!empty($pageLimit) && is_array($pageLimit) && trim($sql) != ''){
			$this->db->open($sql, $pageLimit['rowFrom'],$pageLimit['rowTo']);
			while ($rs = $this->db->next()) {
				$result[] = $rs;
			}
			return $result;
			
		}else {
			return $result;
		}
	}
	
	/**
	 * 对当前表添加一个新的元组
	 * @Param array $newItem 要添加的新元组(数组形式)
	 * @param string $table  要操作的表名,可选
	 * @access public
	 * @return int  添加成功:返回新增的Identity.添加失败返回0
	 */
	function add($newItem,$table=null)
	{
		if($table == '' || is_null($table)){$table = $this->tName;}
		try{
			if($this->db->insert($table,$newItem)){
				return $this->db->getInsertId();
			}else{return 0;}
		}
		catch(DbException $ex){
			echo $ex;
			return 0;
		}
	}
	
	/**
	 * 删除符合条件的元组
	 * @Param string $where 要删除指定条目的条件
	 * @param string $table  要操作的表名,可选
	 * @access public
	 * @return boolean
	 */
	function delete($where,$table=null)
	{
		if($table == '' || is_null($table)){$table = $this->tName;}
		if(!empty($where)){
			$delsql =  'delete from '.$table.' where '.$where;
			try{
				return $this->db->execute($delsql);
			}catch(DbException $ex){
				return false;
			}
		}
		else{
			return false;
		}
	}
	

	/**
	 * 更新操作
	 * @Param array $newItem 要更新的键值对
	 * @Param string $where 要更新指定的条件
	 * @Param string $whereForUnique 要保证不重复记录指定的条件
	 * @param string $table  要操作的表名,可选
	 * @access public
	 * @return boolean 或者-2 参数不足
	 */	
	function update($newItem,$where,$whereForUnique=NULL,$table=null)
	{
		if($table == '' || is_null($table)){$table = $this->tName;}
		if(!empty($newItem)&&!empty($where)){
			try{;
				$this->db->update($table,$newItem,$where,$whereForUnique);
				return true;
			}catch(DbException $ex){
				return false;
			}
		}
		else{
			return -2; //参数不足
		}
	}
 }
?>