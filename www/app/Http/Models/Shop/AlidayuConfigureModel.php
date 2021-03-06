<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc: 
 */

namespace App\Http\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class AlidayuConfigureModel extends Model
{
    protected $table = 'alidayu_configure';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function getAlidayuByPage($where = [], $column = ['*'], $size = 15)
    {
        return $this->select($column)
            ->where($where)
            ->orderBy('id', 'desc')
            ->paginate($size);
    }

    public function getAlidayu($where = [], $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->first();
    }

    public function setAlidayu($where, $data)
    {
        return $this->where($where)
            ->update($data);
    }

    public function addAlidayu($data)
    {
        return $this->create($data);
    }

    public function delAlidayu($where)
    {
        return $this->where($where)
            ->delete();
    }
}
