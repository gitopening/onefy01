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
 * UtilException 异常类
 * @package Util
 */
class UtilException  extends Exception{

	function __toString() {
		$string = '<table width="100%" border="1" cellspacing="0" cellpadding="3">';
		$string .= '<tr><td>出现异常 '.$this->getCode().'</td><td>'.$this->getMessage().'</td></tr>';
		$string .= '<tr><td>文件名</td><td>'.$this->getFile().'</td></tr>';
		$string .= '<tr><td>行号</td><td>'.$this->getLine().'</td></tr>';
		$string .= '<tr><td>相关信息</td><td>'.$this->getTraceAsString().'</td></tr>';
		$string .= '</table>';
		return $string;
	}
	
}
?>