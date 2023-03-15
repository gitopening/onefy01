<?php
/**
 * 小区信息类
 * @package Apps
 */

class Borough {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	/**
	 * 小区基本信息表
	 *
	 * @var string
	 */
	var $tName = 'fke_borough';
	/**
	 * 小区详细信息表
	 *
	 * @var string
	 */
	var $tNameInfo = 'fke_borough_info';
	/**
	 * 小区图片 ， 实景图
	 *
	 * @var string
	 */
	var $tNamePic = 'fke_borough_pic';
	
	/**
	 * 新盘购买意向
	 *
	 * @var string
	 */
	var $tNameIntention = 'fke_borough_intention';
	
	/**
	 * 新盘动态
	 *
	 * @var string
	 */
	var $tNameNews = 'fke_borough_news';
	
	
	/**
	 * 小区图片 ， 户型图
	 *
	 * @var string
	 */
	var $tNameDraw = 'fke_borough_draw';
	
	/**
	 * 小区专家
	 *
	 * @var string
	 */
	var $tNameAdviser = 'fke_borough_adviser';
	/**
	 * 小区更新
	 *
	 * @var string
	 */
	var $tNameUpdate = 'fke_borough_update';
	/**
	 * 用户
	 *
	 * @var string
	 */
	var $tNameMember = 'fke_member';
	/**
	 * 详细信息
	 *
	 * @var string
	 */
	var $tNameMemberInfo = 'fke_broker_info';
	
	/**
	 * 小区评估价日志
	 *
	 * @var string
	 */
	var $tNameEvaluate = 'fke_borough_evaluate';
	
	
	function Borough($db) {
		$this->db = $db;
	}
	
	/**
	 * 取用户列表
	 * @param array limit 
	 * @param Enum $flag 0：全部 ， 1：已审核 ，2：未审核
	 * @access public
	 * @return array
	 */
	function getList($pageLimit,$flag = 0,$where_clouse = '',$order='') {
		$where =' where 1 = 1' ;
		if($where_clouse){
			$where .= $where_clouse;
		}
		if($flag == 1){
			$where .= " and is_checked = 1";
		}
		if($flag == 2){
			$where .= " and is_priceoff = 1 and is_checked = 1";
		}
		if($flag == 3){
			$where .= " and is_promote = 1";
		}
		if($flag == 4){
			$where .= " and (sell_time>'".date('Y-m-d')."' or sell_time ='' )";
		}
		if($flag == 5){
			$where .= " and is_checked = 0";
		}
		$this->db->open('select * from '.$this->tName.$where.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	}
	
	/**
	 * 取得小区购买意向列表
	 * @param array limit 
	 * @param Enum $flag 0：全部 ， 1：已审核 ，2：未审核
	 * @access public
	 * @return array
	 */
	function getIntentionList($where_clouse = '',$order='') {
		$where =' where ' ;
		if($where_clouse){
			$where .= $where_clouse;
		}	
		$this->db->open('select * from '.$this->tNameIntention.$where.' '.$order);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	}

	/**
	 * 保存小区信息
	 * @param array $borough 基本表单数组
	 * @param array $boroughInfo 详细表单数组
	 * @access public
	 * @return bool
	 */
	function save($fileddata) {
		global $cfg,$page;
		$fileddata['boroughInfo']['borough_support'] = implode(',',(array)$fileddata['boroughInfo']['borough_support'] );
		$fileddata['boroughInfo']['borough_sight'] = implode(',',(array)$fileddata['boroughInfo']['borough_sight'] );
		
		$fileddata['borough']['borough_letter'] = GetPinyin($fileddata['borough']['borough_name'].$fileddata['borough']['borough_alias'],1);
		$fileddata['borough']['updated'] = $cfg['time'];
		if(!$fileddata['borough']['borough_thumb']){
			//没有上传缩略图
			if(is_array($fileddata['borough_picture_thumb'])){
				$fileddata['borough']['borough_thumb']  = $fileddata['borough_picture_thumb'][0];
			}
		}
		if($fileddata['id']){
			//编辑
			$borough_id= intval($fileddata['id']);
			if($fileddata['borough']['layout_map']) {
				$this->updateMap($borough_id,$fileddata['borough']['layout_map']);
			}
			$this->db->update($this->tName,$fileddata['borough'],'id = '.$borough_id);
			$this->db->update($this->tNameInfo,$fileddata['boroughInfo'],'id = '.$borough_id);
			
			if($borough['cityarea_id']!=$fileddata['borough']['cityarea_id']||$borough['cityarea2_id']!=$fileddata['borough']['cityarea2_id']){
			$this->db->update('fke_houserent',array('cityarea_id'=>$fileddata['borough']['cityarea_id'],'cityarea2_id'=>$fileddata['borough']['cityarea2_id']),'borough_id='.$borough_id);
			
			$this->db->update('fke_housesell',array('cityarea_id'=>$fileddata['borough']['cityarea_id'],'cityarea2_id'=>$fileddata['borough']['cityarea2_id']),'borough_id='.$borough_id);			
			}
			//照片
			$this->db->execute('delete from '.$this->tNamePic.' where borough_id ='.$borough_id);
			if(is_array($fileddata['borough_picture_url'])){
				foreach($fileddata['borough_picture_url'] as $key => $pic_url){
					$imgField = array(
						'pic_url'=>$pic_url,
						'pic_thumb'=>$fileddata['borough_picture_thumb'][$key],
						'pic_desc'=>$fileddata['borough_picture_desc'][$key],
						'borough_id'=>$borough_id,
						'creater'=>$fileddata['creater'],
						'addtime'=>$cfg['time'],
					);
					$this->db->insert($this->tNamePic,$imgField);
				}
			}
			//户型图
			$this->db->execute('delete from '.$this->tNameDraw.' where borough_id ='.$borough_id);
			if(is_array($fileddata['borough_drawing_url'])){
				foreach($fileddata['borough_drawing_url'] as $key => $pic_url){
					$imgField = array(
						'pic_url'=>$pic_url,
						'pic_thumb'=>$fileddata['borough_drawing_thumb'][$key],
						'pic_desc'=>$fileddata['borough_drawing_desc'][$key],
						'borough_id'=>$borough_id,
						'creater'=>$fileddata['creater'],
						'addtime'=>$cfg['time'],
					);
					$this->db->insert($this->tNameDraw,$imgField);
				}
			}
		}else{
			//增加
			
			 //判断是否已经存在该小区
			$boroughId = $this->getIdByName($fileddata['borough']['borough_name']);
			if($boroughId){
				$page->back('该小区已存在数据库中，请不要重复添加');
		    	}
				
			$fileddata['borough']['is_checked'] = 1;
			$fileddata['borough']['creater'] = $fileddata['creater'];
			$fileddata['borough']['created'] = $cfg['time'];
			$this->db->insert($this->tName,$fileddata['borough']);
			$borough_id = $this->db->getInsertId();
			
			$fileddata['boroughInfo']['id'] = $borough_id;
			$this->db->insert($this->tNameInfo,$fileddata['boroughInfo']);
			if(is_array($fileddata['borough_picture_url'])){
				foreach($fileddata['borough_picture_url'] as $key => $pic_url){
					$imgField = array(
						'pic_url'=>$pic_url,
						'pic_thumb'=>$fileddata['borough_picture_thumb'][$key],
						'pic_desc'=>$fileddata['borough_picture_desc'][$key],
						'borough_id'=>$borough_id,
						'creater'=>$fileddata['creater'],
						'addtime'=>$cfg['time'],
					);
					$this->db->insert($this->tNamePic,$imgField);
				}
			}
			if(is_array($fileddata['borough_drawing_url'])){
				foreach($fileddata['borough_drawing_url'] as $key => $pic_url){
					$imgField = array(
						'pic_url'=>$pic_url,
						'pic_thumb'=>$fileddata['borough_drawing_thumb'][$key],
						'pic_desc'=>$fileddata['borough_drawing_desc'][$key],
						'borough_id'=>$borough_id,
						'creater'=>$fileddata['creater'],
						'addtime'=>$cfg['time'],
					);
					$this->db->insert($this->tNameDraw,$imgField);
				}
			}
		}
		//图片数量
		$borough_pic_num = $this->db->getValue("select count(*) as num from ".$this->tNamePic." where borough_id = ".$borough_id);
		$borough_draw_num = $this->db->getValue("select count(*) as num from ".$this->tNameDraw." where borough_id = ".$borough_id);
		$this->db->execute("update ".$this->tName." set layout_picture=".$borough_pic_num.",layout_drawing=".$borough_draw_num." where id=".$borough_id);
		
		return true;
	}
	/**
	 * 检测是否有重复的小区名字
	 *
	 * @param array $borough_name
	 * @return bool
	 */
	function checkNameUnique($borough_name)
	{
		$borough_info = $this->db->getValue("select id from ".$this->tName." where borough_name = '".$borough_name."'");
		if($borough_info){
			return $borough_info;
		}
		return false;
	}
	
	/**
	 * 前台添加小区 , 信息比较简单
	 * @param  array  $fielddata
	 * @return bool 
	 * 
	 */
	function addBorough($field_data){
		global $cfg;
		$field_data['borough_letter'] = GetPinyin($field_data['borough_name'],1);
		$field_data['updated'] =  $cfg['time'];
		$field_data['is_checked'] = 0;
		$field_data['creater'] = $field_data['creater'];
		$field_data['created'] = $cfg['time'];
		
		$insertField = array(
			'borough_name'=>$field_data['borough_name'],
			'borough_letter'=>$field_data['borough_letter'],
			'cityarea_id'=>$field_data['cityarea_id'],
			'cityarea2_id'=>$field_data['cityarea2_id'],
			'borough_address'=>$field_data['borough_address'],
			'borough_type'=>$field_data['borough_type'],
			'is_checked'=>$field_data['is_checked'],
			'creater'=>$field_data['creater'],
			'created'=>$field_data['created'],
			'updated'=>$field_data['updated'],
		);
		
		$this->db->insert($this->tName,$insertField);
		$borough_id = $this->db->getInsertId();
		$insertField = array(
			'id'=>$borough_id
		);
		$this->db->insert($this->tNameInfo,$insertField);
		return $borough_id;
	}

	/**
	 * 增加房源数 ()
	 * @param $type sell_num | rent_num
	 *
	 * @param int $borough_id
	 */
	function  increase($borough_id,$type = 'sell_num')
	{
		return $this->db->execute("update ".$this->tName." set ".$type."=".$type."+1 where id=".$borough_id);
	}
	/**
	 * 修改收集地图数据
	 * @param $type sell_num | rent_num
	 *
	 * @param int $borough_id
	 */
function  updateMap($borough_id,$mapPoint){
$mapPoint1=str_ireplace(array('(',')','',''),'',$mapPoint);
$data=explode(',',$mapPoint1);
return $this->db->execute('update '.$this->tName." set layout_map='".$mapPoint."' , lat = '".$data[0]."',lng='".$data[1]."'  where id=".$borough_id);

}
	
	/**
	 * 修改缩略图数据
	 * @param $type sell_num | rent_num
	 *
	 * @param int $borough_id
	 */
	function  updateThumb($borough_id,$borough_thumb)
	{
		return $this->db->execute("update ".$this->tName." set borough_thumb='".$borough_thumb."' where id=".$borough_id);
	}
	
	/**
	 * 修改数据
	 * @param $type sell_num | rent_num
	 *
	 * @param int $borough_id
	 */
	function  update($borough_id,$datafield)
	{
		return $this->db->update($this->tName ,$datafield, " id=".$borough_id);
	}
	
	/**
	 * 更新某个字段
	 * @param mixed $ids ID
	 * @access public
	 * @return bool
	 */
	function updateNewHouse($ids,$field,$value) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' id in (' . $ids . ')';
		} else {
			$where = ' id=' . intval($ids);
		}
		return $this->db->execute('update '.$this->tName.' set '.$field.' = \''.$value.'\' where ' . $where);
	}
	
	/**
	 * 取小区信息
	 * @param string $id 小区ID
	 * @param string $field 主表字段
	 * @param enum $more_info  是否取出详细的信息
	 * @access public
	 * @return array
	 */
	function getInfo($id, $field = '*',$more_info = 0,$merge=false) {
		$borough = $this->db->getValue('select ' . $field . ' from '.$this->tName.'  where id=' . $id);
		if($more_info){
			$boroughInfo = $this->db->getValue('select * from '.$this->tNameInfo.' where id=' . $id);
			if($merge){
				return array_merge((array)$borough,(array)$boroughInfo);
			}else{
				return array('borough'=>$borough,'boroughInfo'=>$boroughInfo);
			}
		}else{
			return $borough;
		}
	}
	/**
	 * 通过小区名字取小区ID  。 下拉选择小区使用
	 * @param string $borough_name 小区名字
	 * @access public
	 * @return int id
	 */
	function getIdByName($borough_name) {
		return $this->db->getValue("select id from ".$this->tName."  where borough_name like '%".$borough_name."%'");
	}
	
	
	/**
	 * 删除小区信息
	 * @param mixed $ids 选择的ID
	 * @access public
	 * @return bool
	 */
	function delete($ids) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = 'id in (' . $ids . ')';
		    $where2 = 'borough_id in (' . $ids . ')';
		} else {
			$where = 'id=' . intval($ids);
			$where2 = 'borough_id=' . intval($ids);
		}
		$nohouseid = $this->db->select('select id from '.$this->tName.' where '.$where .' and sell_num =0 and rent_num = 0 ');
		if(!empty($nohouseid)){
			$nohouseid = implode(',',$nohouseid);
			$deletewhere = ' where id in (' . $nohouseid . ')';
			$deletewhere1 = ' where borough_id in (' . $nohouseid . ')';
			$this->db->execute('delete from '.$this->tName. $deletewhere);
			$this->db->execute('delete from '.$this->tNameInfo.$deletewhere);
			$this->db->execute('delete from '.$this->tNamePic.$deletewhere1);
			$this->db->execute('delete from '.$this->tNameDraw.$deletewhere1);
			$this->db->execute('delete from '.$this->tNameUpdate.$deletewhere1);
			return true;
		}else{
			return false;
		}
	}
	
	
	/**
	 * 删除购买意向
	 * @param mixed $ids 选择的ID
	 * @access public
	 * @return bool
	 */
	function intentionDelete($ids) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' where id in (' . $ids . ')';
		} else {
			$where = ' where id=' . intval($ids);
		}
			return $this->db->execute('delete from '.$this->tNameIntention. $where);
	}


	/**
	 * 删除小区信息
	 * @param mixed $ids 选择的ID
	 * @access public
	 * @return bool
	 */
	function logicDelete($ids) {
		
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = 'id in (' . $ids . ')';
		} else {
			$where = 'id=' . intval($ids);
		}
		
		return $this->db->execute('update '.$this->tName.' set isdel = 1 where '. $where);
	}
	
	/**
	 * 审核小区信息
	 * @param mixed $ids 选择的ID
	 * @access public
	 * @return bool
	 */
	function check($ids) {
		
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = 'id in (' . $ids . ')';
		} else {
			$where = 'id=' . intval($ids);
		}
		return $this->db->execute("update ".$this->tName." set is_checked = 1  where ".$where );
	}
	
	/**
	 * 添加楼盘购买意向
	 * @param array $Intention 基本表单数组
	 * @access public
	 * @return bool
	 */
	function saveIntention($fileddata) {
		if($fileddata['link_type']){
			$fileddata['link_type'] = ','.implode(',',$fileddata['link_type']).',';
		}
		$field_array = array(
						'borough_id'=>intval($fileddata['borough_id']),
						'link_type'=>$fileddata['link_type'],
						'content'=>$fileddata['content'],
						'nickname'=>$fileddata['nickname'],
						'gender'=>intval($fileddata['gender']),
						'mobile'=>$fileddata['mobile'],
						'age'=>intval($fileddata['age']),
						'qq'=>$fileddata['qq'],
						'tel'=>$fileddata['tel'],
						'email'=>$fileddata['email'],
						'postcode'=>$fileddata['postcode'],
						'address'=>$fileddata['address'],
						'addtime'=>time(),
					);
		return $this->db->insert($this->tNameIntention,$field_array);
	}
	
	
	/**
	 * 取总用户数
	 * @access public
	 * @return NULL
	 */
	function getCount($flag = 0,$where_clouse = '') {
		$where =" where 1 = 1";
	if($where_clouse){
			$where .= $where_clouse;
		}
		if($flag == 1){
			$where .= " and is_checked = 1";
		}
		if($flag == 2){
			$where .= " and is_priceoff = 1";
		}
		if($flag == 3){
			$where .= " and is_promote = 1";
		}
		if($flag == 4){
			$where .= " and (sell_time>'".date('Y-m-d')."' or sell_time ='' )";
		}
		if($flag == 5){
			$where .= " and is_checked = 0";
		}
		
		return $this->db->getValue('select count(*) from '.$this->tName. $where );
	}

	/**
	 * 取得所有符合条件的用户
	 *
	 * @param string $columns
	 * @param string $condition
	 * @param string $order
	 * @return array
	 */
	function getAll($columns='*',$condition='',$order = ''){
		if($condition != ''){
			$condition = ' where ' .$condition;
		}
		return $this->db->select('select '.strtolower($columns).' from '.$this->tName.$condition.' '.$order);
	}

	/**
	 * 图片的列表
	 *
	 * @param string 小区ID $borough_id
	 * @param bool 图片类型true:draw,false:pic $getType
	 */
	function getImgList($borough_id,$imgType,$num = 0)
	{
		if($imgType){
			if($num){
				return $this->db->select('select * from '.$this->tNameDraw.' where borough_id = '.$borough_id." limit ".$num);
			}else{
				return $this->db->select('select * from '.$this->tNameDraw.' where borough_id = '.$borough_id);
			}
		}else{
			if($num){
				return $this->db->select('select * from '.$this->tNamePic.' where borough_id = '.$borough_id." limit ".$num);
			}else{
				return $this->db->select('select * from '.$this->tNamePic.' where borough_id = '.$borough_id);
			}
		}
	}
	/**
	 * 图片数量
	 *
	 * @param int ID $borough_id
	 * @return number
	 */
	function getPicNum($borough_id)
	{
		return $this->db->getValue('select count(*) from '.$this->tNamePic.' where borough_id = '.$borough_id);
	}
	/**
	 * 户型图数量
	 *
	 * @param int ID $borough_id
	 * @return number
	 */
	function getDrawNum($borough_id)
	{
		return $this->db->getValue('select count(*) from '.$this->tNameDraw.' where borough_id = '.$borough_id);
	}
	/**
	 *插入户型图 ，从房源中上传
	 *
	 * @param string 小区ID $borough_id
	 * @param bool 图片类型 $getType
	 */
	function insertDrawing($fileddata)
	{
		$this->db->insert($this->tNameDraw,$fileddata);
		return $this->db->getInsertId();
	}
	
	/**
	 *插入户型图 ，从房源中上传
	 *
	 * @param string 小区ID $borough_id
	 * @param bool 图片类型 $getType
	 */
	function insertPic($fileddata)
	{
		$this->db->insert($this->tNamePic,$fileddata);
		return $this->db->getInsertId();
	}
	/**
	 * 随即取一个专家
	 *
	 * @param int $borough_id
	 * @return int
	 */
	function getRandomAdviser($borough_id)
	{
		return $this->db->getValue('select member_id from '.$this->tNameAdviser.' where borough_id='.$borough_id .' and status=1 order by rand()');
	}
	/**
	 * 取专家列表
	 * 排序在小区房价有体现
	 * @param int $borough_id
	 * @return int
	 */
	function getAdviserList($borough_id,$status = 1)
	{
		if($status){
			$where= " and status =1"; 
		}
		return $this->db->select('select * from '.$this->tNameAdviser.' where borough_id='.$borough_id .$where.' order by rand()');
	}
	/**
	 * 检测是否已申请某小区专家
	 *
	 * @param int $borough_id
	 * @param int $member_id
	 * 
	 * @return int
	 */
	function checkExpertUnique($member_id)
	{
		return $this->db->getValue("select status,borough_id from ".$this->tNameAdviser." where status <>2 and member_id='".$member_id."'");
	}
	/**
	 * 增加小区专家
	 *
	 * @param int $borough_id
	 * @param int $member_id
	 * @return int
	 */
	function addBoroughExpert($borough_id,$member_id){
		$fileddata = array(
			'borough_id'=>$borough_id,
			'member_id'=>$member_id,
			'add_time'=>time(),
		);
		$this->db->insert($this->tNameAdviser,$fileddata);
		return $this->db->getInsertId();
	}
	/**
	 * 按某个字段计算数量
	 *
	 * @param string $field
	 * @param string $where
	 * @return array
	 */
	function getCountGroupBy($field,$where='',$having_count=2)
	{
		if($where != ''){
			$where = ' where 1=1 ' .$where;
		}
		if($having_count){
			$having = ' having house_num>'.$having_count;
		}
		
		return $this->db->select('select '.$field.',count(*) as house_num from '.$this->tName.$where." group by ".$field. $having);
	}
	
	/**
	 * 取用户列表
	 * @param array limit 
	 * @param Enum $flag 0：全部 ， 1：已审核 ，2：未审核
	 * @access public
	 * @return array
	 */
	function getListDetail($pageLimit,$flag = 0,$where_clouse = '',$order='') {
		$where =' where 1 = 1' ;
		if($where_clouse){
			$where .= $where_clouse;
		}
		if($flag == 1){
			$where .= " and b.is_checked = 1 ";
		}elseif($flag == 2){
			$where .= " and b.is_checked = 0 ";
		}
		$sql = "select * from ".$this->tName." as b 
			left join ".$this->tNameInfo." as i 
			on b.id = i.id 
			".$where.' '.$order;
		$this->db->open($sql , $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	}
	/**
	 * 按条件取得小区专家列表
	 *
	 */
	function getExpertCount($where)
	{
		if($where){
			$where=' where '.$where;
		}
		$sql = "select count(*) from ".$this->tNameAdviser." as a
			 left join ".$this->tName." as b ".
			" on a.borough_id = b.id". $where ;
		return $this->db->getValue($sql);
	}
	/**
	 * 按条件取得小区专家列表
	 *
	 */
	function getExpertList($pageLimit,$where_clouse = '',$order='')
	{
		if($where_clouse){
			$where = " where " .$where_clouse;
		}
		$sql = "select a.id as aid,a.status as astatus ,a.*,b.id as bid ,b.*,m.id as mid ,m.*,i.* from ".$this->tNameAdviser." as a
			 left join ".$this->tName." as b on a.borough_id = b.id 
			 left join ".$this->tNameMember." as m on a.member_id = m.id 
			 left join ".$this->tNameMemberInfo." as i on a.member_id = i.id 
			 ". $where.$order ;
		
		$this->db->open($sql, $pageLimit['rowFrom'],$pageLimit['rowTo']);
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	}
	/**
	 *  取得小区专家ID列表
	 *  在专家房源中使用
	 */
	function getExpertIdList()
	{
		return $this->db->select("select member_id from ".$this->tNameAdviser." where status =1 ");
	}
	/**
	 * 修改小区专家的表
	 *
	 * @param array/int $ids
	 * @param int $status
	 * @return bool
	 */
	function expertStatus($ids,$status)
	{
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = 'id in (' . $ids . ')';
		} else {
			$where = 'id=' . intval($ids);
		}
		return $this->db->execute("update ".$this->tNameAdviser." set status = ".$status."  where ".$where );
	}
	 /**
	 * 删除小区专家
	 *
	 * @param array/int $ids
	 * @param int $status
	 * @return bool
	 */
	function deleteexpert($ids)
	{
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = 'id in (' . $ids . ')';
		} else {
			$where = 'id=' . intval($ids);
		}
		return $this->db->execute("delete from " .$this->tNameAdviser."  where ".$where );
	}
	
	/**
	 * 取得小区专家申请纪录信息
	 *
	 * @param unknown_type $id
	 */
	function getExpertInfo($id)
	{
		return $this->db->getValue("select * from ".$this->tNameAdviser." where id = ".$id );
	}
	/**
	 * 取最后一条的评估记录
	 *
	 * @param int $borough_id
	 */
	function getLastEvaluateLog($borough_id)
	{
		return $this->db->getValue("select * from ".$this->tNameEvaluate." where borough_id = ".$borough_id." order by add_time desc");
	}
	/**
	 * 保存评估日志
	 *
	 * @param array $dataField
	 * @return bool
	 */
	function saveEvaluteLog($dataField)
	{
		$data = $this->db->getValue("select * from ".$this->tNameEvaluate." where borough_id = ".$dataField['borough_id']." and add_time = ".$dataField['add_time']);
		if($data){
			$fileddata = array(
				'borough_evaluate'=>$dataField['borough_evaluate'],
				'creater'=>$dataField['creater']
			);
			$this->db->update($this->tNameEvaluate,$fileddata,'id = '.$data['id']);
		}else{
			$fileddata = array(
				'borough_id'=>$dataField['borough_id'],
				'borough_evaluate'=>$dataField['borough_evaluate'],
				'creater'=>$dataField['creater'],
				'add_time'=>$dataField['add_time']
			);
			$this->db->insert($this->tNameEvaluate,$fileddata);
		}
		return true;
	}
	
	/**
	 * 保存新盘动态
	 * @param string $field
	 * @access public
	 * @return array
	 */
	function saveNews($field) {
		if ($field['id']) {
			$this->db->update($this->tNameNews, array (
					    	'time' =>  time(),
						    'title' => $field['title'],
							'type' => $field['type'],
							'borough_id' => $field['borough_id'],
					       	)
					);
			
		} else {
			$this->db->insert($this->tNameNews, array(
							'time' =>  time(),
						    'title' => $field['title'],
							'type' => $field['type'],
							'borough_id' => $field['borough_id'],
					       	)
					);
		}
	}
	  /**
	 * 取得新盘动态列表
	 * @access public
	 * 
	 * @param array $pageLimit
	 * @return array
	 **/
	 function getNewsList($boroughId) {
		$this->db->open('select * from '.$this->tNameNews.' where borough_id='.$boroughId.' order by time desc ');
		$result = array();
		while ($rs = $this->db->next()) {
			$result[] = $rs;
		}
		return $result;
	 }
	 
	 /**
	 * 删除信息
	 * @param mixed $ids 选择的ID
	 * @access public
	 * @return bool
	 */
	function deleteNews($ids) {
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' where id in (' . $ids . ')';
		} else {
			$where = ' where id=' . intval($ids);
		}
		 return $this->db->execute('delete from '.$this->tNameNews.$where);
		}
		
	
	 /**
	 * 取意向登记人数
	 * @param mixed $ids 选择的ID
	 * @access public
	 * @return bool
	 */
	function getIntentionCount($where_clouse = '') {
		$where =" where 1 = 1";
	if($where_clouse){
			$where .= $where_clouse;
		}
		return $this->db->getValue('select count(*) from '.$this->tNameIntention. $where );
	}

}
?>