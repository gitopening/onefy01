<?php
/**
 * 验证码
 */
require_once(dirname(__FILE__).'/common.inc.php');
require_once($cfg['path']['lib'] . 'classes/Image.class.php');
$cookieName = $_GET['cookieName'] ? $_GET['cookieName'] : COOKIE_PREFIX . 'validString';
$image = new Image();
define('VALID_CODE_TYPE', 1);
define('VALID_CODE_LENGTH', 4);
$valid = $image->imageValidate(64, 21, VALID_CODE_LENGTH, VALID_CODE_TYPE, '#FF0000', '#FFFFFF');
//setcookie($cookieName, md5(strtolower($valid)),0,'/',$cfg['domain']); 
setcookie($cookieName, md5(strtolower($valid)),0);
ob_clean();
header("Pragma: no-cache");
header("Cache-control: no-cache");
$image->display(null);