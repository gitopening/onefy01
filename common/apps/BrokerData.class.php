<?php
/**
 * Author : net <geow@qq.com>
 */

class BrokerData {
    private $db = NULL;
    private $table_name = 'fke_broker_data';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function checkPhoneIsExist($phone)
    {
        if (empty($phone)) {
            return false;
        }
        $sql = "select id from `{$this->table_name}` where phone_crc32 = crc32('$phone') and phone = '$phone'";
        $phone_id = $this->db->getValue($sql);
        return $phone_id;
    }

    public function save($id, $fields_array)
    {
        if ($id) {
            $sql = "UPDATE `{$this->table_name}` SET broker_name='{$fields_array['broker_name']}', broker_name_crc32=crc32('{$fields_array['broker_name']}'), phone='{$fields_array['phone']}', phone_crc32=crc32('{$fields_array['phone']}'), company_name='{$fields_array['company_name']}', company_name_crc32=crc32('{$fields_array['company_name']}'), province_id='{$fields_array['province_id']}', city_id='{$fields_array['city_id']}',cityarea_id='{$fields_array['cityarea_id']}',cityarea2_id='{$fields_array['cityarea2_id']}' WHERE id='$id'";
            $result = $this->db->execute($sql);
        } else {
            $now = time();
            $sql = "insert into `{$this->table_name}` (broker_name, broker_name_crc32, phone, phone_crc32, company_name, company_name_crc32, province_id, city_id, cityarea_id, cityarea2_id, source_id, add_time) values ('{$fields_array['broker_name']}', crc32('{$fields_array['broker_name']}'), '{$fields_array['phone']}', crc32('{$fields_array['phone']}'), '{$fields_array['company_name']}', crc32('{$fields_array['company_name']}'), '{$fields_array['province_id']}', '{$fields_array['city_id']}', '{$fields_array['cityarea_id']}', '{$fields_array['cityarea2_id']}', '{$fields_array['source_id']}', '$now')";
            $result = $this->db->execute($sql);
        }
        return $result;
    }

    public function getOne($where, $fields = '*')
    {

    }

    public function deleteByPhone($phone)
    {
        if (empty($phone)) {
            return false;
        }
        $sql = "delete from `{$this->table_name}` where phone_crc32 = crc32('$phone') and phone = '$phone'";
        $result = $this->db->execute($sql);
        return $result;
    }
}