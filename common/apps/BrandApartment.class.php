<?php
/**
 * Author : net <geow@qq.com>
 */

class BrandApartment extends Model {
    public $table_name = 'brand_apartment';

    public function checkIsExist($id, $brand_name)
    {
        if (empty($brand_name)) {
            return false;
        }
        $condition = array(
            'brand_name' => $brand_name
        );
        $result = $this->table($this->table_name)->field('id')->where($condition)->one();
        if ($result['id'] != $id && $result['id'] > 0) {
            return true;
        } else {
            return false;
        }
    }
}