<?php
class VisitCount{
	private $db;
	private $dayCount = 0;
	private $yesterdayCount = 0;
	private $tableName = 'fke_visit_count';
	
	public function __construct($db){
		$this->db = $db;
	}
	
	public function AddVisitCount($website_id, $columnType, $cityarea_id, $cityarea2_id){
		$column = $this->GetColumnTypeId($columnType);
		$visitCountData = $this->GetVisitData($website_id, $columnType, $cityarea_id, $cityarea2_id);
		if ($visitCountData != false){
			//增加统计信息
			$today = MyDate('Y-m-d', time());
			$visitDay = MyDate('Y-m-d', $visitCountData['update_time']);
			//如果不是同一天统计
			if ($today != $visitDay){
				//把今天统计存储到昨天,重置今天统计
				$fieldArray = array(
						'day_count' => 1,
						'yesterday_count' => $visitCountData['day_count'],
						'update_time' => time()
						);
				$this->db->Update($this->tableName, $fieldArray, "website_id='$website_id' and column_type='$column' and cityarea_id='$cityarea_id' and cityarea2_id='$cityarea2_id'");
			}else{
				//增加统计
				$sql = "update `{$this->tableName}` set day_count = day_count + 1, yesterday_count = yesterday_count + 1, total_count = total_count + 1, update_time=".time()." where website_id='$website_id' and column_type='$column' and cityarea_id='$cityarea_id' and cityarea2_id='$cityarea2_id'";
				$this->db->Execute($sql);
			}
		}
	}
	
	public function GetVisitData($website_id, $columnType, $cityarea_id, $cityarea2_id){
		$column = $this->GetColumnTypeId($columnType);
		//检测是否有记录
		$sql = "select * from `{$this->tableName}` where website_id='$website_id' and column_type='$column' and cityarea_id='$cityarea_id' and cityarea2_id='$cityarea2_id'";
		$dataInfo = $this->db->getValue($sql);
		if ($dataInfo){
			return $dataInfo;
		}else{
			//插入数据并返回
			$fieldArray = array(
					'column_type' => $column,
					'website_id' => $website_id,
					'cityarea_id' => $cityarea_id,
					'cityarea2_id' => $cityarea2_id,
					'day_count' => 1,
					'yesterday_count' => 0,
					'total_count' => 1,
					'update_time'  => time()
					);
			$resutl = $this->db->Insert($this->tableName, $fieldArray);
			return false;
		}
		return $this->db->getValue($sql);
	}
	
	public function GetVisitList($website_id, $row = 18, $columnType = ''){
		$column = $this->GetColumnTypeId($columnType);
		$whereSQL = array();
		$whereSQL[] = "website_id='$website_id'";
		if ($column){
			$whereSQL[] = "column_type = '$column'";
		}
		$whereSQL[] = 'cityarea2_id = 0 and cityarea_id <> 0';
		if ($whereSQL){
			$where = 'where '.implode(' and ', $whereSQL).' order by total_count desc limit '.$row;
		}
		$sql = "select id, column_type, cityarea_id, cityarea2_id, website_id, day_count, yesterday_count, total_count, update_time from `{$this->tableName}` $where";
		return $this->db->select($sql);
	}
	
	private function GetColumnTypeId($columnType){
		switch ($columnType){
			case 'rent':
				return 1;
			case 'sale':
				return 2;
			case 'xzlcz':
				return 3;
			case 'xzlcs':
				return 4;
			case 'spcz':
				return 5;
			case 'spcs':
				return 6;
			case 'broker':
				return 7;
			default:
				return 0;
		}
	}
	
	public function GetColumnPath($columnType){
		switch ($columnType){
			case 1:
				return '/rent/';
			case 2:
				return '/sale/';
			case 3:
				return '/xzlcz/';
			case 4:
				return '/xzlcs/';
			case 5:
				return '/spcz/';
			case 6:
				return '/spcs/';
			case 7:
				return '/broker/';
			default:
				return '';
		}
	}
	public function GetColumnName($columnType){
		switch ($columnType){
			case 1:
				return '租房';
			case 2:
				return '二手房';
			case 3:
				return '写字楼出租';
			case 4:
				return '写字楼出售';
			case 5:
				return '商铺出租';
			case 6:
				return '商铺出售';
			case 7:
				return '经纪人';
			default:
				return '';
		}
	}
}