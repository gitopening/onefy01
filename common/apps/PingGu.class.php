<?php
/**
 * 房源评估管理
 * @package Apps
 */
class PingGu {

	/**
	 * @var Object $db 数据库查询对象
	 * @access private
	 */
	var $db = NULL;
	
	/**
	 * 出售评估基本信息表
	 *
	 * @var string
	 */
	var $tName = 'fke_pinggu';
	
	function PingGu($db) {
		$this->db = $db;
	}
	
	/**
	 * 取用户列表
	 * @param array limit 
	 * @param Enum $flag 0：全部 ， 1：已审核 ，2：未审核
	 * @access public
	 * @return array
	 */
	function getList($pageLimit,$fileld='*' , $where_clouse = '',$order='') {	
		if ($where_clouse){
			$where_clouse = "where ". $where_clouse;
		}
		$this->db->open('select '.$fileld.' from '.$this->tName.$where_clouse.' '.$order , $pageLimit['rowFrom'],$pageLimit['rowTo']);
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
		global $cfg,$query,$page;
		
		$borough = new Borough($query);
		if(!$fileddata['borough_id']){
			$fileddata['borough_id'] = $borough->getIdByName($fileddata['borough_name']);
			//没有找到该小区
			if(!$fileddata['borough_id']){
				$page->back('没有搜索到相关的小区，请确认你的小区名称');
			}
		}
		if($fileddata['id']){
			//编辑
			$pinggu_id = intval($fileddata['id']);
			$field_array  = array(
				'house_type'=>$fileddata['house_type'],
				'borough_id'=>intval($fileddata['borough_id']),
				'borough_name'=>$fileddata['borough_name'],
				'home_no'=>$fileddata['home_no'],
				'room_no'=>$fileddata['room_no'],
				'house_totalarea'=>$fileddata['house_totalarea'],
				'house_room'=>$fileddata['house_room'],
				'house_hall'=>$fileddata['house_hall'],
				'house_toilet'=>$fileddata['house_toilet'],
				'house_topfloor'=>$fileddata['house_topfloor'],
				'house_floor'=>$fileddata['house_floor'],
				'house_toward'=>$fileddata['house_toward'],
				'has_lift'=>intval($fileddata['has_lift']),
				'has_empty'=>intval($fileddata['has_empty']),
			);
			$this->db->update($this->tName,$field_array,'id = '.$pinggu_id);
			
		}else{
			//增加
			$field_array  = array(
				'house_type'=>intval($fileddata['house_type']),
				'borough_id'=>intval($fileddata['borough_id']),
				'borough_name'=>$fileddata['borough_name'],
				'home_no'=>$fileddata['home_no'],
				'room_no'=>$fileddata['room_no'],
				'house_totalarea'=>$fileddata['house_totalarea'],
				'house_room'=>intval($fileddata['house_room']),
				'house_hall'=>intval($fileddata['house_hall']),
				'house_toilet'=>intval($fileddata['house_toilet']),
				'house_topfloor'=>intval($fileddata['house_topfloor']),
				'house_floor'=>intval($fileddata['house_floor']),
				'house_toward'=>intval($fileddata['house_toward']),
				'has_lift'=>intval($fileddata['has_lift']),
				'has_empty'=>intval($fileddata['has_empty']),
				'creater'=>$fileddata['creater'],
				'add_time'=>time(),
			);

			$this->db->insert($this->tName,$field_array);
			$pinggu_id = $this->db->getInsertId();
		}
		$this->refreshPrice($pinggu_id);
		return $pinggu_id;
	}
	
	/**
	 * 保存小区信息
	 * @param array $borough 基本表单数组
	 * @param array $boroughInfo 详细表单数组
	 * @access public
	 * @return bool
	 */
	function saveMore($fileddata) {
		if($fileddata['id']){
			//编辑
			$pinggu_id = intval($fileddata['id']);
			$field_array  = array(
				'house_fitment'=>intval($fileddata['house_fitment']),
				'fitment_price'=>intval($fileddata['fitment_price']),
				'fitment_year'=>intval($fileddata['fitment_year']),
				'house_place'=>intval($fileddata['house_place']),
				'house_view'=>intval($fileddata['house_view']),
				'house_light'=>intval($fileddata['house_light']),
				'house_noise'=>intval($fileddata['house_noise']),
				'house_quality'=>intval($fileddata['house_quality']),
				'is_detail'=>1,
			);
			$this->db->update($this->tName,$field_array,'id = '.$pinggu_id);
			$this->refreshPrice($pinggu_id);
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 更新价格
	 *
	 */
	function refreshPrice($pinggu_id)
	{
		$di_quotiety = 0;
		$pingGuDd = new PingGuDd($this->db);
		$pinggu_info = $this->getInfo($pinggu_id);
		//print_rr($pinggu_info);
		if(!$pinggu_info){
			return ;
		}
		//评估价
		$borough = new Borough($this->db);
		$evaluate_price = $borough->getInfo($pinggu_info['borough_id'],'borough_evaluate');
		if(!$evaluate_price){
			return ;
		}
		//类型
		$house_type_dd = $pingGuDd->getItemByValue('house_type',$pinggu_info['house_type']);
		if($house_type_dd['di_quotiety']){
			$di_quotiety += $house_type_dd['di_quotiety'];
		}
//		print_rr($di_quotiety);
		//朝向
		$house_toward_dd = $pingGuDd->getItemByValue('house_toward',$pinggu_info['house_toward']);
		if($house_toward_dd['di_quotiety']){
			$di_quotiety += $house_toward_dd['di_quotiety'];
		}
//		print_rr($di_quotiety);
		//面积
		$house_totalarea_dd = $pingGuDd->getItemListByName('house_totalarea');

		foreach ($house_totalarea_dd as $item){
			//print_rr($item);
			$tmp = explode('-',$item['di_caption']);
			if($tmp[1]){
				if($tmp[0]<=$pinggu_info['house_totalarea'] && $tmp[1]>=$pinggu_info['house_totalarea'] ){
					$di_quotiety += $item['di_quotiety'];
					break;
				}
			}else{
				if($tmp[0]<$pinggu_info['house_totalarea']){
					$di_quotiety += $item['di_quotiety'];
				}
			}
		}
//		print_rr($di_quotiety);
		//位置
		if($pinggu_info['house_place']){
			$house_place_dd = $pingGuDd->getItemByValue('house_place',$pinggu_info['house_place']);
			if($house_place_dd['di_quotiety']){
				$di_quotiety += $house_place_dd['di_quotiety'];
			}
		}
//		print_rr($di_quotiety);
		//同风
		if($pinggu_info['house_light']){
			$house_light_dd = $pingGuDd->getItemByValue('house_light',$pinggu_info['house_light']);
			if($house_place_dd['di_quotiety']){
				$di_quotiety += $house_light_dd['di_quotiety'];
			}
		}

		//景观
		if($pinggu_info['house_view']){
			$house_view_dd = $pingGuDd->getItemByValue('house_view',$pinggu_info['house_view']);
			if($house_view_dd['di_quotiety']){
				$di_quotiety += $house_view_dd['di_quotiety'];
			}
		}

		//噪音情况
		if($pinggu_info['house_noise']){
			$house_noise_dd = $pingGuDd->getItemByValue('house_noise',$pinggu_info['house_noise']);
			if($house_noise_dd['di_quotiety']){
				$di_quotiety += $house_noise_dd['di_quotiety'];
			}
		}

		//建筑质量
		if($pinggu_info['house_quality']){
			$tmp = explode(",",$pinggu_info['house_quality']);
			foreach ($tmp as $item){
				if($item==''){
					continue;
				}
				$house_quality_dd = $pingGuDd->getItemByValue('house_quality',$item);
				if($house_quality_dd['di_quotiety']){
					$di_quotiety += $house_quality_dd['di_quotiety'];
				}
			}
		}

		//楼层 ， 有电梯 
		if($pinggu_info['has_lift']){
			$has_lift_dd = $pingGuDd->getItemByValue('house_floorlift',$pinggu_info['house_floor']);
			if($has_lift_dd){
				$di_quotiety += $has_lift_dd['di_quotiety'];
			}else{
				// 6层以上每3层递增0.02
				$di_quotiety +=(intval(($pinggu_info['house_floor']-6)/3)+1)*0.02;
			}
		}
		//别墅不判断楼层	
		if($pinggu_info['house_type'] == 1 || $pinggu_info['house_type'] == 2 || $pinggu_info['house_type'] == 3){
			//楼层 ， 无电梯 。有架空 
			if(!$pinggu_info['has_lift'] && $pinggu_info['has_empty']){
				$house_floornoliftempty_dd = $pingGuDd->getItemByValue('house_floornoliftempty',$pinggu_info['house_floor']);
				if($house_floornoliftempty_dd){
					if($house_floornoliftempty_dd['di_quotiety']){
						$di_quotiety += $house_floornoliftempty_dd['di_quotiety'];
					}
				}else{
					$house_floornoliftempty_dd = $pingGuDd->getLast('house_floornoliftempty');
					if($house_floornoliftempty_dd['di_quotiety']){
						$di_quotiety += $house_floornoliftempty_dd['di_quotiety'];
					}
				}			
			}
			//楼层 ， 无电梯 ，无架空
			if(!$pinggu_info['has_lift'] && !$pinggu_info['has_empty']){
				$house_floornoliftnoempty_dd = $pingGuDd->getItemByValue('house_floornoliftnoempty',$pinggu_info['house_floor']);
				if($house_floornoliftnoempty_dd){
					if($house_floornoliftnoempty_dd['di_quotiety']){
						$di_quotiety += $house_floornoliftnoempty_dd['di_quotiety'];
					}
				}else{
					$house_floornoliftnoempty_dd = $pingGuDd->getLast('house_floornoliftnoempty');
					if($house_floornoliftnoempty_dd['di_quotiety']){
						$di_quotiety += $house_floornoliftnoempty_dd['di_quotiety'];
					}
				}	
			}
		}
		//更新数据库
		$evaluate_price = $evaluate_price*(1+$di_quotiety);
/*		print_rr($di_quotiety);
		print_rr($evaluate_price);
		exit;*/
		$house_pgprice = round($evaluate_price*$pinggu_info['house_totalarea']/10000,2);
		$house_avgprice = round($evaluate_price/0.78);
		$house_avgpgprice = round($evaluate_price);
		$house_totalprice = round($house_avgprice*$pinggu_info['house_totalarea']/10000,2);
		//装修 ，只补交易价格
		if($pinggu_info['fitment_price'] && $pinggu_info['fitment_year']){
			$house_totalprice = $house_totalprice +round((8-$pinggu_info['fitment_year'])/8*$pinggu_info['fitment_price'],2);
		}
		$house_avgprice = round($house_totalprice*10000/$pinggu_info['house_totalarea']);
		$updateField = array(
			'house_totalprice'=>$house_totalprice,
			'house_avgprice'=>$house_avgprice,
			'house_pgprice'=> $house_pgprice,
			'house_avgpgprice'=> $house_avgpgprice
		);
		$this->db->update($this->tName,$updateField,"id = ".intval($pinggu_id));
	}
	
	/**
	 * 取房源信息
	 * @param string $id 小区ID
	 * @param string $field 主表字段
	 * @access public
	 * @return array
	 */
	function getInfo($id, $field = '*') {
		return $this->db->getValue('select ' . $field . ' from '.$this->tName.'  where id=' . $id);
	}
	
	
	/**
	 * 删除房源信息
	 * @param mixed $ids 选择的ID
	 * @access public
	 * @return bool
	 */
	function delete($ids) {
		
		if (is_array($ids)) {
			$ids = implode(',',$ids);
			$where = ' where id in (' . $ids . ')';
		} else {
			$where = ' where id=' . intval($ids);
		}
		
		return $this->db->execute('delete from '.$this->tName.$where);

	}

	/**
	 * 取总用户数
	 * @access public
	 * @return NULL
	 */
	function getCount($where_clouse = '') {
		if($where_clouse){
			$where_clouse = " where ". $where_clouse;
		}
		return $this->db->getValue('select count(*) from '.$this->tName. $where_clouse );
	}

	/**
	 * 取得所有符合条件的房源
	 *
	 * @param unknown_type $columns
	 * @param unknown_type $condition
	 * @param unknown_type $order
	 * @return unknown
	 */
	function getAll($columns='*',$condition='',$order = ''){
		if($condition != ''){
			$condition = ' where ' .$condition;
		}
		return $this->db->select('select '.strtolower($columns).' from '.$this->tName.$condition.' '.$order);
	}
}
?>