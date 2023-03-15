<?php
require_once(dirname(__FILE__).'/common.inc.php');
$page->name = 'link'; //页面名字,和文件名相同	
$page->title = $cfg['page']['title'];   //网站名称

//友情链接
$sql="select * from `fke_outlink` where status='1' and website_id='".WEBHOSTID."' and  link_class<>'5' order by list_order ASC";
$flink=$query->select($sql);
$page->tpl->assign('flink',$flink);
//广告位
$page->tpl->assign('websiteright',GetAdList(68, $query));
$page->tpl->assign('websitefoot',GetAdList(65, $query));
$page->show();
?>