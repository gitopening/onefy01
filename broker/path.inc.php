<?php
require_once(dirname(dirname(__FILE__)) . '/common.inc.php');
JumpToCurrentWebsite();

$page->city = $cityInfo['city_name'];
$page->dir  = 'broker';//目录名
