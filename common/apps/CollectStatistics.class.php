<?php
/**
 * 采集数据统计
 * geow@qq.com
 */
class CollectStatistics extends Model
{
	public $tName = 'collect_statistics';

	public function set($key_name, $fields_array)
	{
        $row = $this->getDetailByKeyName($key_name);
		if ($row) {
			//已存在则更新
            return $this->update($row['id'], $fields_array);
		} else {
			//添加新记录
            return $this->add($fields_array);
		}
	}

	public function update($id, $fields_array)
	{
        $new_fields = array(
            'last_id' => $fields_array['last_id'],
            'updated' => $fields_array['updated']
        );
        return $this->table($this->tName)->where('id = ' . $id)->save($new_fields);
	}

	public function add($fields_array)
	{
        return $this->table($this->tName)->save($fields_array);
	}

	public function getDetailByKeyName($key_name, $fields = '*')
	{
        $condition = array(
            'key_name_crc32' => array($key_name, 'CRC32'),
            'key_name' => $key_name
        );
        return $this->table($this->tName)->field($fields)->where($condition)->one();
	}

    public function getKeyName($type_id, $column_type, $source_id, $city_website_id)
    {
        return 'tj_' . $type_id . '_' . $column_type . '_' . $source_id . '_' . $city_website_id;
    }
}