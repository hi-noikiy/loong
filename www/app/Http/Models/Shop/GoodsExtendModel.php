<?php

namespace App\Http\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class GoodsExtendModel extends Model
{
    protected $table = 'goods_extend';
    protected $primaryKey = 'extend_id';
    public $timestamps = false;
    protected $guarded = [];

    public function getGoodsExtend($where, $columns = ['*'])
    {
        return $this->select($columns)
            ->where($where)
            ->first();
    }

    public function getGoodsExtendAll($columns = ['*'])
    {
        return $this->select($columns)
            ->get();
    }

    public function addGoodsExtend($data)
    {
        return $this->create($data);
    }
}
