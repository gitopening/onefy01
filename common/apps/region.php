<?php
class Region{
	private $db;
	private $memcache;
	
	public function __construct($db, $memcache){
		$this->db = $db;
		$this->memcache = $memcache;
		
	}
	
	public function GetSubRegion($parentId){
		$sql = "select region_id, region_name, first_letter from `fke_region` where parent_id = '$parentId' order by first_letter asc";
		$key = md5($sql);
		$data = $this->memcache->get($key);
		if (empty($data)){
			$data = array();
			$dataSet = $this->db->execute($sql);
			while ($arr = $this->db->fetchRecord($dataSet)) {
				$data[$arr['region_id']] = $arr;
			}
			$this->db->close($dataSet);
			if ($data){
				$result = $this->memcache->set($key, $data, MEMCACHE_COMPRESS, MEMCACHE_MAX_EXPIRETIME);
			}
		}
		return $data;
	}
}