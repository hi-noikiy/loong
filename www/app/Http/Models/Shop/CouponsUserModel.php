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

class CouponsUserModel extends Model
{
    protected $table = 'coupons_user';
    protected $primaryKey = 'cu_id';
    public $timestamps = false;
    protected $guarded = [];

    public function delCouponsUser($where)
    {
        return $this->where($where)
            ->delete();
    }

    public function addCouponsUser($data)
    {
        return $this->create($data);
    }

    public function delCouponsUserByIn($where)
    {
        return $this->whereIn('cou_id', $where)
            ->delete();
    }

    public function countCouponsUser($where)
    {
        return $this->where($where)
            ->count();
    }
}
