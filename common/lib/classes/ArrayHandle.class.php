<?php
/**
 * PHP项目组使用 - PHP项目类库
 * Copyright (c) 2006-2008 西岸网讯
 * All rights reserved.
 * 未经许可，禁止用于商业用途！
 *
 * @package    Util
 * @author     戴志君 <dzjzmj@163.com>
 * @copyright  2006-2008 Walk Watch
 * @version    v1.0
 */

/**
 * ArrayHandle 数组操作类
 * @package Util
 */
class ArrayHandle {

	/**
	 * @var array $arrayData 数组内容
	 * @access public
	 */
	public $arrayData = array();
	
	/**
	 * 构造函数
	 * @param array $array 数组
     */
	public function __construct (& $array) {
		if ($array) {
			if (!is_array($array)) {
				throw new Exception('param is not an array');
			} else {
				$this->arrayData = &$array;
			} 
		}
	}
	
	/**
	 * 查询数组,如果有多个结果返回key数组
	 * @param array $array 操作的数组
	 * @param mixed $value 要查询的值
	 * @return mixed
	 */
	public static function search(&$array, $value, $field = NULL, $once = false) {
		$index = array();
		foreach ($array as $key => $arrayValue) {
			if ($field) {
				if ($arrayValue[$field]==$value) {
					$index[] = $key;
					if ($once) {
						break;
					}
				}
			} else {
				if ($value==$arrayValue) {
					$index[] = $key;
					if ($once) {
						break;
					}
				}
			}
		}
		$indexCount = count($index);
		if ($indexCount<1) {
			return -1;
		} elseif ($indexCount==1) {
			return $index[0];
		} else {
			return $index;
		}
	}
	
	/**
	 * 插入指定的key值前插入数据
	 * @param array $array 操作的数组
	 * @param string $key 键值
	 * @param mixed $value 值
	 * @param bool $before 是否插入在指定key之前
	 * @return void
	 */
	public static function insert(&$array, $key, $newValue, $before = true) {
		$result = false;
		$size = sizeof($array);
		for ($i=0; $i<$size; $i++) {
			$value = array_shift($array);
			if ($i==$key) {
				if ($before) {
					array_push($array, $newValue);
					array_push($array, $value);
				} else {
					array_push($array, $value);
					array_push($array, $newValue);
				}
				$result = true;
			} else {
				array_push($array, $value);
			}
		}
		if (!$result) {
			array_push($array, $newValue);
		}
		return;
	}
	
	/**
	 * 删除key为指定的key里的值
	 * @param array $array 操作的数组
	 * @param string $key 键值可以是数组
	 * @return void
	 */
	public static function delete(&$array, $key) {
		if (!is_array($key)) {
			$key = array($key);
		}
		foreach ($key as $k) {
			unset($array[$k]);
		}
		$array = array_values($array);
	}
	
	/**
	 * 对数组排序
	 * @param array $array 操作的数组
	 * @param string $type key按键排序，value按值排序
	 * @param string $field 字段名
	 * @param string $order 排序方式asc顺序desc逆序
	 * @return void
	 */
	public static function sort(&$array, $type = 'value', $field = NULL, $order = 'asc') {
		if ($field) {
			foreach ($array as $key => $value) {
				$temp[$key] = $value[$field];
			}
			if ($order=='asc') {
				asort($temp);
			} else {
				arsort($temp);
			}
			$newarray = array();
			foreach ($temp as $key => $value) {
				$newarray[] = $array[$key];
			}
			$array = $newarray;
		} else {
			if ($type=='key') {
				if ($order=='asc') {
					ksort($array);
				} else {
					krsort($array);
				}
			} else {
				if ($order=='asc') {
					asort($array);
				} else {
					arsort($array);
				}
			}
		}
		
	}
}
?>