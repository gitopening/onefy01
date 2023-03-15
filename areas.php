<?php
require_once(dirname(__FILE__).'/common.inc.php');
header("Cache-Control: no-cache");    
$id=$_GET["id"];   

 
$dd = new Dd($query);
$res=$dd->getSonList($id);

$rt='<select name="cityarea2_id" id="cityarea2_id">';   
foreach($res as $rs){   
$rt.='<option value="'.$rs["di_value"].'">'.$rs["di_caption"].'</option>';   
}   
$rt.='</select>';   
echo $rt;