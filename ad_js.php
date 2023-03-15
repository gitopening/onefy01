<?php
require_once(dirname(__FILE__) . '/path.inc.php');
$id=intval($_GET['id']);
if (!empty($id)){
	echo GetAdList($id,$query,1);
}