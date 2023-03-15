<?php
/**
 * 上传文件
 * to : uploadBoroughThumb|borough|picture
 * to : 函数名|目录分类|图片类型
 * 
 */
require_once(dirname(__FILE__) . '/common.inc.php');
//判断用户是否已登录
$member = new Member($member_query);
$member_auth = $member->getAuthInfo();
$mid = intval($member_auth['id']);

if (empty($mid)) {
	$result['error'] = 1;
	$result['msg'] = '请先登录会员中心';
	echo json_encode($result);
	exit();
}

$to = $_GET["to"];
$action = $_GET['action'];
if($action==""){
	$action = "form";
}
if($action=="doupload") {
	echo '<html>';
	echo '<head>';
	echo '<title>上传成功</title>';
	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
	echo '</head>';

	$store_info = explode('|', $to);
	$js_func = $store_info[0];

	/*  判断特殊字符 */
	if ($store_info[1]) {
		if (!preg_match("/^[A-Za-z]+$/", $store_info[1])) {
			exit;
		}
	}
	if ($store_info[2]) {
		if (!preg_match("/^[A-Za-z]+$/", $store_info[2])) {
			exit;
		}
	}

	$upload_conf = require($cfg['path']['conf'] . 'upload.cfg.php');

	$this_config = (array)$upload_conf[$store_info[1]][$store_info[2]];
	if (empty($this_config)) {
		exit;
	}
	$upload = new UploadFile();//实例化上传对象
	//设置可以上传文件的类型
	$upload->setAllowFileType($this_config['allowType']);
	foreach ($_FILES as $a_file) {
		if ($a_file['error'] != UPLOAD_ERR_NO_FILE) {
			try {
				$fileName = $upload->upload($a_file, $cfg['path']['root'] . 'upfile/' . $this_config['originalPath'], 1);
				$f_path['url'] = $this_config['originalPath'] . $fileName;
				$f_path['name'] = $a_file['name'];
				$attach_file[] = $f_path;
				if (in_array(strtolower(FileSystem::fileExt($f_path['name'])), array('gif', 'jpeg', 'jpg', 'png')) && !$this_config['noResize']) {
					//先缩略到指定大小
					$image = new Image($cfg['path']['root'] . 'upfile/' . $this_config['originalPath'] . $fileName);
					$image->resizeImage($this_config['width'], $this_config['height'], $this_config['resizeType']);
					$image->save();
					//加水印
					if ($this_config['watermark']) {
						$image = new Image($cfg['path']['root'] . 'upfile/' . $this_config['originalPath'] . $fileName);
						$image->waterMark($this_config['watermarkPic'], $this_config['watermarkPos']);
						$image->save();
					}
					//如果需要再生成缩略图
					if ($this_config['thumb']) {
						$image = new Image($cfg['path']['root'] . 'upfile/' . $this_config['originalPath'] . $fileName);
						$image->resizeImage($this_config['thumbWidth'], $this_config['thumbHeight'], $this_config['thumbResizeType']);
						if ($this_config['originalPath'] == $this_config['thumbDir']) {
							//防止存储目录相同时覆盖原有的图片，不存储缩略图直接设置 thumb 属性为空
							$image->save(2, $cfg['path']['root'] . 'upfile/' . $this_config['thumbDir'], '_thumb');
							$thumb_path = $this_config['thumbDir'] . FileSystem::getBasicName($fileName, false) . '_thumb' . FileSystem::fileExt($fileName, true);
						} else {
							$image->save(1, $cfg['path']['root'] . 'upfile/' . $this_config['thumbDir']);
							$thumb_path = $this_config['thumbDir'] . $fileName;
						}
					}
				}
				//回传参数
				echo '<script type="text/javascript">window.parent.document.getElementById("' . $js_func . '").value="/upfile/' . $f_path['url'] . '";window.parent.document.getElementById("' . $store_info[2] . 'pic").src="/upfile/' . $f_path['url'] . '";</script>';

			} catch (Exception $e) {
				$page->back($e->getMessage());
			}
		} else {
			echo "<script  type='text/javascript'>
					alert('请先浏览文件后点击上传');
					history.back();
			</script>";
			exit;
		}
		echo '上传成功 <a href="' . $_SERVER['HTTP_REFERER'] . '">返回</a>';
	}
	echo '</body>';
	echo '</html>';
} elseif ($action=="upload_idcard"){
	$store_info = explode('|',$to);
	$js_func = $store_info[0];

	/*  判断特殊字符 */
	if($store_info[1]){
		if(!preg_match("/^[A-Za-z]+$/",$store_info[1])){
			echo json_encode(array(
				'error' => 1,
				'msg' => '上传参数错误'
			));
			exit;
		}
	}
	if($store_info[2]){
		if(!preg_match("/^[A-Za-z]+$/",$store_info[2])){
			echo json_encode(array(
				'error' => 1,
				'msg' => '上传参数错误'
			));
			exit;
		}
	}

	$upload_conf = require($cfg['path']['conf'].'upload.cfg.php');

	$this_config = (array)$upload_conf[$store_info[1]][$store_info[2]];
	if(empty($this_config)){
		echo json_encode(array(
			'error' => 1,
			'msg' => '上传配置文件不存在'
		));
		exit;
	}
	$upload = new UploadFile();//实例化上传对象
	//设置可以上传文件的类型
	$upload->setAllowFileType($this_config['allowType']);
	foreach ($_FILES as $a_file){
		if($a_file['error']!=UPLOAD_ERR_NO_FILE) {
			try{
				$fileName = $upload->upload($a_file,$cfg['path']['root'].'upfile/'.$this_config['originalPath'], 1);
				$f_path['url'] = $this_config['originalPath'].$fileName;
				$f_path['name'] = $a_file['name'];
				$attach_file[] = $f_path;
				if(in_array(strtolower(FileSystem::fileExt($f_path['name'])),array('gif','jpeg','jpg','png')) && !$this_config['noResize']){
					//先缩略到指定大小
					$image = new Image($cfg['path']['root'].'upfile/'.$this_config['originalPath'].$fileName);
					$image->resizeImage($this_config['width'],$this_config['height'],$this_config['resizeType']);
					$image->save();
					//加水印
					if($this_config['watermark']){
						$image = new Image($cfg['path']['root'].'upfile/'.$this_config['originalPath'].$fileName);
						$image->waterMark($this_config['watermarkPic'],$this_config['watermarkPos']);
						$image->save();
					}
					//如果需要再生成缩略图
					if($this_config['thumb']){
						$image = new Image($cfg['path']['root'].'upfile/'.$this_config['originalPath'].$fileName);
						$image->resizeImage($this_config['thumbWidth'],$this_config['thumbHeight'],$this_config['thumbResizeType']);
						if($this_config['originalPath']==$this_config['thumbDir']){
							//防止存储目录相同时覆盖原有的图片，不存储缩略图直接设置 thumb 属性为空
							$image->save(2,$cfg['path']['root'].'upfile/'.$this_config['thumbDir'],'_thumb');
							$thumb_path = $this_config['thumbDir'].FileSystem::getBasicName($fileName, false).'_thumb'.FileSystem::fileExt($fileName, true);
						}else{
							$image->save(1,$cfg['path']['root'].'upfile/'.$this_config['thumbDir']);
							$thumb_path = $this_config['thumbDir'].$fileName;
						}
					}
				}
				//回传参数
				echo json_encode(array(
					'error' => 0,
					'msg' => '上传成功',
					'url' => '/upfile/' . $f_path['url']
				));
				exit();
			}catch(Exception $e){
				echo json_encode(array(
					'error' => 1,
					'msg' => $e->getMessage()
				));
				exit;
			}
		}else{
			echo json_encode(array(
				'error' => 1,
				'msg' => '请选择要上传的文件'
			));
			exit;
		}
	}
}elseif ($action == 'uploadavatar'){
	$result = array();
	$upload_conf = require_once($cfg['path']['conf'].'upload.cfg.php');
	
	$this_config = (array)$upload_conf['broker']['avatar'];
	if(empty($this_config)){
		exit;
	}
	$upload = new UploadFile();//实例化上传对象
	//设置可以上传文件的类型
	$upload->setAllowFileType($this_config['allowType']);
	foreach ($_FILES as $a_file){
		if($a_file['error']!=UPLOAD_ERR_NO_FILE) {
			try{
				$fileName = $upload->upload($a_file,$cfg['path']['root'].'upfile/'.$this_config['originalPath'], 1);
				$f_path['url'] = $this_config['originalPath'].$fileName;
				$f_path['name'] = $a_file['name'];
				$attach_file[] = $f_path;
				if(in_array(strtolower(FileSystem::fileExt($f_path['name'])),array('gif','jpeg','jpg','png')) && !$this_config['noResize']){
					//先缩略到指定大小
					$image = new Image($cfg['path']['root'].'upfile/'.$this_config['originalPath'].$fileName);
					$image->resizeImage($this_config['width'],$this_config['height'],$this_config['resizeType']);
					$image->save();
					//加水印
					if($this_config['watermark']){
						$image = new Image($cfg['path']['root'].'upfile/'.$this_config['originalPath'].$fileName);
						$image->waterMark($this_config['watermarkPic'],$this_config['watermarkPos']);
						$image->save();
					}
					//如果需要再生成缩略图
					if($this_config['thumb']){
						$image = new Image($cfg['path']['root'].'upfile/'.$this_config['originalPath'].$fileName);
						$image->resizeImage($this_config['thumbWidth'],$this_config['thumbHeight'],$this_config['thumbResizeType']);
						if($this_config['originalPath']==$this_config['thumbDir']){
							//防止存储目录相同时覆盖原有的图片，不存储缩略图直接设置 thumb 属性为空
							$image->save(2,$cfg['path']['root'].'upfile/'.$this_config['thumbDir'],'_thumb');
							$thumb_path = $this_config['thumbDir'].FileSystem::getBasicName($fileName, false).'_thumb'.FileSystem::fileExt($fileName, true);
						}else{
							$image->save(1,$cfg['path']['root'].'upfile/'.$this_config['thumbDir']);
							$thumb_path = $this_config['thumbDir'].$fileName;
						}
					}
				}
				//回传参数
				$result['error'] = 0;
				$result['url'] = '/upfile/'.$f_path['url'];
				$result['msg'] = '头像保存成功';
			}catch(Exception $e){
				$result['error'] = 1;
				$result['msg'] = $e->getMessage();
			}
		}else{
			$result['error'] = 1;
			$result['msg'] = '头像保存失败';
		}
	}
	$result['error'] = 0;
	$result['msg'] = '头像保存成功';
	echo json_encode($result);
	exit;
}elseif($action=="form"){
	echo '<html>';
	echo '<head>';
	echo '<title>上传文件</title>';
	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
	echo '</head>';
	echo "<body leftmargin=\"0\" topmargin=\"0\">";
	echo "<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\" height=\"100%\" align=\"left\">";
	echo "<form action='upload.php?action=doupload&to=".$to."' method='post' enctype='multipart/form-data'>";
	echo "<tr ><td  valign='middle'>";
	echo "<input type='file' name='uploadfile'>";
	echo "<input name='submit' type='submit' value='上传'>";
	echo "</td></tr>";
	echo "</form>";
	echo "</table";
	echo "</body>";
	echo '</html>';
} elseif ($action = 'upload_house_pic_list') {
	exit('error');
	$store_info = explode('|', $to);
	$js_func = $store_info[0];

	/*  判断特殊字符 */
	if ($store_info[1]) {
		if (!preg_match("/^[A-Za-z]+$/", $store_info[1])) {
			exit;
		}
	}
	if ($store_info[2]) {
		if (!preg_match("/^[A-Za-z]+$/", $store_info[2])) {
			exit;
		}
	}

	$upload_conf = require($cfg['path']['conf'] . 'upload.cfg.php');

	$this_config = (array)$upload_conf[$store_info[1]][$store_info[2]];
	if (empty($this_config)) {
		echo json_encode(array(
			'error' => 1,
			'msg' => '上传参数配置文件不存在'
		));
		exit();
	}
	$upload = new UploadFile();//实例化上传对象
//设置可以上传文件的类型
	$upload->setAllowFileType($this_config['allowType']);

	foreach ($_FILES as $a_file) {

		try {
			$fileName = $upload->upload($a_file, $cfg['path']['root'] . 'upfile/' . $this_config['originalPath'], 1);


			$f_path['url'] = $this_config['originalPath'] . $fileName;
			$f_path['name'] = $a_file['name'];
			$attach_file[] = $f_path;
			if (in_array(strtolower(FileSystem::fileExt($f_path['name'])), array('gif', 'jpeg', 'jpg', 'png')) && !$this_config['noResize']) {
				//先缩略到指定大小
				$image = new Image($cfg['path']['root'] . 'upfile/' . $this_config['originalPath'] . $fileName);
				$image->resizeImage($this_config['width'], $this_config['height'], $this_config['resizeType']);
				$image->save();
				//加水印
				if ($this_config['watermark']) {
					$image = new Image($cfg['path']['root'] . 'upfile/' . $this_config['originalPath'] . $fileName);
					$image->waterMark($this_config['watermarkPic'], $this_config['watermarkPos']);
					$image->save();
				}
				//如果需要再生成缩略图
				if ($this_config['thumb']) {
					$image = new Image($cfg['path']['root'] . 'upfile/' . $this_config['originalPath'] . $fileName);
					$image->resizeImage($this_config['thumbWidth'], $this_config['thumbHeight'], $this_config['thumbResizeType']);
					if ($this_config['originalPath'] == $this_config['thumbDir']) {
						//防止存储目录相同时覆盖原有的图片，不存储缩略图直接设置 thumb 属性为空
						$image->save(2, $cfg['path']['root'] . 'upfile/' . $this_config['thumbDir'], '_thumb');
						$thumb_path = $this_config['thumbDir'] . FileSystem::getBasicName($fileName, false) . '_thumb' . FileSystem::fileExt($fileName, true);
					} else {
						$image->save(1, $cfg['path']['root'] . 'upfile/' . $this_config['thumbDir']);
						$thumb_path = $this_config['thumbDir'] . $fileName;
					}
				}
			}

			// Check the upload

			if (!isset($_SESSION["file_info"])) {
				$_SESSION["file_info"] = array();
			}

			$fileName = md5($fileName) . ".jpg";
			$file_id = md5(rand() * 10000000);
			$_SESSION["file_info"][$file_id] = $fileName;                    //回传参数

		} catch (Exception $e) {
			$page->back($e->getMessage());
		}

	}

	echo json_encode(array(
		'error' => 0,
		'FILEID' => $file_id,
		'pic_url' => $f_path['url'],
		'name' => $f_path['name'],
		'pic_thumb_url' => $thumb_path
	));
	exit();
}