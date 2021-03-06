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

class MemberPriceModel extends Model
{
    protected $table = 'member_price';
    protected $primaryKey = 'price_id';
    public $timestamps = false;
    protected $guarded = [];

    public function getMemberPrice($where, $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->get();
    }

    public function setMemberPrice($where, $data)
    {
        return $this->where($where)
            ->update($data);
    }

    public function addMemberPrice($data)
    {
        return $this->create($data);
    }

    public function countMemberPrice($where)
    {
        return $this->where($where)->count();
    }

    public function delMemberPrice($where)
    {
        try {
            return $this->where($where)->delete();
        } catch (\Exception $e) {
        }
    }

}
