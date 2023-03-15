<?php
/**
 * smarty insert 的方式插入广告条目
 *
 * @param int $id
 */
function smarty_insert_ads($params)
{
	global $query,$cfg;
	if (empty($params['id'])) { 
        $smarty->trigger_error("insert ads: missing 'id' parameter"); 
        return; 
    }
	$ads_place = new AdsPlace($query);
	$placeInfo = $ads_place->getInfo($params['id']);
	if(!$placeInfo){
		return; 
	}
	$ads = new Ads($query);
	switch ($placeInfo['template']){
		case "single":
			//只有一张
			if($placeInfo['option'] == "2"){
				$placeInfo['option'] =1;
			}
			if($placeInfo['option'] == "1"){
				//最后发表的项目
				$where = ' place_id = '.$params['id'].' and status =1 and from_date<='.$cfg['time'].' and ( to_date >='.$cfg['time'].' or  to_date=0 )' ;
				$itemList = $ads->getList(array("rowFrom"=>0,"rowTo"=>1),'*',$where,' order by add_time desc');
			}
			if($placeInfo['option'] == "3"){
				//随机抽取
				if(!$params['num']){
					$params['num'] = 1;
				}
				$itemList = $ads->getList(array("rowFrom"=>0,"rowTo"=>$params['num']),'*',$where,' order by rand()');
			}
			foreach ($itemList as $key => $item){
				$linkTo = (strpos($item['link_url'], 'http://') === false && strpos($item['link_url'], 'https://') === false) ? 'http://'.$item['link_url']: $item['link_url'];
                if($item['ad_type']=="image"){
               		$src = $cfg['url']."upfile/".$item['image_url'];
                	$ads[] = "<a href='".$linkTo."' target='_blank'>
                	  	<img src='".$src."' width='" .$placeInfo['ads_width']. "' height='".$placeInfo['ads_height']."' border='0' />
                	  </a>";
                }elseif($item['ad_type']=="flash"){
                	$src = $cfg['url']."upfile/".$item['flash_url'];
                	  $ads[] = "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" " .
                     "codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0\"  " .
                       "width='".$placeInfo['ads_width']."' height='".$placeInfo['ads_height']."'>
                       <param name='movie' value='$src'>
                       <param name='quality' value='high'>
                       <embed src='$src' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer'
                       type='application/x-shockwave-flash' width='".$placeInfo['ads_width']."'
                       height='".$placeInfo['ads_height']."'></embed>
                     </object>";
                }elseif($item['ad_type']=="text"){
                	$ads[] =  "<a href='".$linkTo."' target='_blank'>".htmlspecialchars($item['text'])."</a>";
                }elseif($item['ad_type']=="code"){
                	$ads[] =  "<script>".$item['code']."</script>";
                }
			}
			break;
		case "flashBox":
			$ads[] = "
				<SCRIPT>                     
				var widths=".$placeInfo['ads_width'].";    /*显示高度*/                  
				var heights=".$placeInfo['ads_height'].";   /*显示宽度*/                   
				var counts=6;              
				img1=new Image ();img1.src='http://bbs.jcwcn.com/attachments/month_0709/20070913_4ee8cb60b22fa9dd0279oTIV6fmTgIXw.jpg';
				img2=new Image ();img2.src='http://bbs.jcwcn.com/attachments/month_0709/20070913_bcfb127543dc1d08802d9KP0DGXyr9jT.jpg';
				img3=new Image ();img3.src='http://bbs.jcwcn.com/attachments/month_0709/20070913_cbc2b6daadb1cbe3e702j0H7isUi1QeX.jpg'; 
				img4=new Image ();img4.src='http://bbs.jcwcn.com/attachments/month_0709/20070913_ae854ce1c7892251fb03pRi4Ha91Ew7i.jpg'; 
				img5=new Image ();img5.src='http://bbs.jcwcn.com/attachments/month_0709/20070910_51df0edb78224d6370612fzMNYqX0tej.jpg';
				img6=new Image ();img6.src='http://bbs.jcwcn.com/attachments/month_0709/20070907_ba0162f23d630dfd9ca1TesqosYf60X6.jpg';        
				url1=new Image ();url1.src='http://www.jcwcn.com/html/PhotoShopImageReady/19_07_11_206.htm';
				url2=new Image ();url2.src='http://www.jcwcn.com/html/PhotoShopImageReady/19_36_05_750.htm';
				url3=new Image ();url3.src='http://www.jcwcn.com/html/PhotoShopImageReady/18_44_26_734.htm';  
				url4=new Image ();url4.src='http://www.jcwcn.com/html/PhotoShopImageReady/10_57_01_754.htm'; 
				url5=new Image ();url5.src='http://www.jcwcn.com/html/3dsmax/23_55_48_619.htm';                                         
				url6=new Image ();url6.src='http://www.jcwcn.com/html/PhotoShopImageReady/15_09_28_8.htm';       
				/* 建议将这块放置于JS中 下面的部分几乎是不需要任何改动*/
				var nn=1;
				var key=0;
				function change_img(){
					if(key==0){
						key=1;
					}else if(document.all){
						document.getElementById('pic').filters[0].Apply();
						document.getElementById('pic').filters[0].Play(duration=2);
					}
					eval('document.getElementById('pic').src=img'+nn+'.src');
					eval('document.getElementById('url').href=url'+nn+'.src');
					for (var i=1;i<=counts;i++){
						document.getElementById('xxjdjj'+i).className='axx';
					}
					document.getElementById('xxjdjj'+nn).className='bxx';
					nn++;
					if(nn>counts){
						nn=1;
					}
					tt=setTimeout('change_img()',4000);
				}
				function changeimg(n){
					nn=n;
					window.clearInterval(tt);
					change_img();
				}
				document.write('<style>');
				document.write('.axx{float:left; padding:0px 7px 3px;*padding:2px 6px;border:#ccc 1px solid; margin-right:5px; margin-bottom:5px;}');
				document.write('a.axx:link,a.axx:visited{text-decoration:none;color:#fff;line-height:12px;font:9px verdana;background-color:#666;}');
				document.write('a.axx:active,a.axx:hover{text-decoration:none;color:#fff;line-height:12px;font:9px verdana;background-color:#999;}');
				document.write('.bxx{float:left; padding:0px 7px 3px;*padding:2px 6px;border:#ccc 1px solid;margin-right:5px; margin-bottom:5px;}');
				document.write('a.bxx:link,a.bxx:visited{text-decoration:none;color:#fff;line-height:12px;font:9px verdana;background-color:#D34600;}');
				document.write('a.bxx:active,a.bxx:hover{text-decoration:none;color:#fff;line-height:12px;font:9px verdana;background-color:#D34600;}');
				document.write('</style>');
				document.write('<div style=\"width:'+widths+'px;height:'+heights+'px;overflow:hidden;text-overflow:clip;\">');
				document.write('<div><a id=\"url\" target=\"_blank\"><img id=\"pic\" style=\"border:0px;filter:progid:dximagetransform.microsoft.wipe(gradientsize=1.0,wipestyle=4, motion=forward)\" width='+widths+' height='+heights+' /></a></div>');
				document.write('<div style=\"float:right;text-align:right;top:-16px;position:relative;margin:1px;height:0px;padding:0px;margin-top:-5px;border:0px;\">');
				for(var i=1;i<counts+1;i++){
					document.write('<a href=\"javascript:changeimg('+i+');\" id=\"xxjdjj'+i+'\" class=\"axx\" target=\"_self\">'+i+'</a>');
				}
				document.write('</div></div>');
				change_img();
				</SCRIPT>";
			break;
		case "picShow":
			if($placeInfo['option'] == "1"){
				//最后发表的项目
				$where = ' place_id = '.$params['id'].' and status =1 and from_date<='.$cfg['time'].' and ( to_date >='.$cfg['time'].' or  to_date=0 )' ;
				$itemList = $ads->getList(array("rowFrom"=>0,"rowTo"=>1),'*',$where,' order by add_time desc');
			}
			if($placeInfo['option'] == "2"){
				//全部项目
				if(!$params['num']){
					$params['num'] = 5;
				}
				$where = ' place_id = '.$params['id'].' and status =1 and from_date<='.$cfg['time'].' and ( to_date >='.$cfg['time'].' or  to_date=0 )' ;
				$itemList = $ads->getList(array("rowFrom"=>0,"rowTo"=>$params['num']),'*',$where,' order by add_time desc');
			}
			if($placeInfo['option'] == "3"){
				//随机抽取
				if(!$params['num']){
					$params['num'] = 1;
				}
				$itemList = $ads->getList(array("rowFrom"=>0,"rowTo"=>$params['num']),'*',$where,' order by rand()');
			}
			foreach ($itemList as $key => $item){
				$linkTo = (strpos($item['link_url'], 'http://') === false && strpos($item['link_url'], 'https://') === false) ? 'http://'.$item['link_url']: $item['link_url'];
                if($item['ad_type']=="image"){
               		$src = $cfg['url']."upfile/".$item['image_url'];
                	$ads[] = "<a href='".$linkTo."' target='_blank'>
                	  	<img src='".$src."' width='" .$placeInfo['ads_width']. "' height='".$placeInfo['ads_height']."' border='0' />
                	  </a>";
                }elseif($item['ad_type']=="flash"){
                	$src = $cfg['url']."upfile/".$item['flash_url'];
                	  $ads[] = "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" " .
                     "codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0\"  " .
                       "width='".$placeInfo['ads_width']."' height='".$placeInfo['ads_height']."'>
                       <param name='movie' value='$src'>
                       <param name='quality' value='high'>
                       <embed src='$src' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer'
                       type='application/x-shockwave-flash' width='".$placeInfo['ads_width']."'
                       height='".$placeInfo['ads_height']."'></embed>
                     </object>";
                }elseif($item['ad_type']=="text"){
                	$ads[] =  "<a href='".$linkTo."' target='_blank'>".htmlspecialchars($item['text'])."</a>";
                }elseif($item['ad_type']=="code"){
                	$ads[] =  "<script>".$item['code']."</script>";
                }
			}
			break;
	}
}
?>