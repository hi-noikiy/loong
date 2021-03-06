<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc:
 */

namespace App\Http\Models\Wxapp;

use function foo\func;
use Illuminate\Database\Eloquent\Model;

class UsersModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;
    protected $guarded = [];

    public function addresses()
    {
        return $this->hasMany('App\Http\Models\Wxapp\UserAddressModel', 'user_id', 'user_id');
    }

    public function real()
    {
        return $this->hasOne('App\Http\Models\Wxapp\UsersRealModel', 'user_id', 'user_id');
    }

    public function CouponsUser()
    {
        return $this->hasMany('App\Http\Models\Wxapp\CouponsUserModel', 'user_id', 'user_id');
    }

    public function BonusUser()
    {
        return $this->hasMany('App\Http\Models\Wxapp\BonusUserModel', 'user_id', 'user_id');
    }

    public function getUserByAddress($where, $column = ['*'])
    {
        return $this->select($column)
            ->with(['addresses' => function ($query) {
                $query->select(['address_id', 'user_id', 'consignee', 'country', 'province', 'city', 'district', 'address', 'mobile', 'zipcode', 'email', 'best_time', 'sign_building'])
                    ->with(['mapprovince' => function ($query) {
                        $query->select(['region_id', 'region_name']);
                    }])
                    ->with(['mapcity' => function ($query) {
                        $query->select(['region_id', 'region_name']);
                    }])
                    ->with(['mapdistrict' => function ($query) {
                        $query->select(['region_id', 'region_name']);
                    }]);
            }])
            ->where($where)
            ->first();
    }

    public function getUser($name, $column = ['*'])
    {
        return $this->select($column)
            ->orWhere(['user_id' => $name])
            ->orWhere(['email' => $name])
            ->orWhere(['mobile_phone' => $name])
            ->orWhere(['user_name' => $name])
            ->with(['real'])
            ->first();
    }

    public function getUserByCB($where, $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->with(['CouponsUser' => function ($query) {
                $query->with(['Coupons' => function ($query) {
                    $query->select(['cou_id', 'cou_name', 'cou_man', 'cou_money', 'cou_goods', 'cou_cate', 'cou_start_time', 'cou_end_time', 'cou_title'])->where([['cou_start_time', '<', time()], ['cou_end_time', '>', time()]]);
                }])->where(['is_use' => 0, 'order_id' => 0]);
            }])
            ->with(['BonusUser' => function ($query) {
                $query->with(['Bonus' => function ($query) {
                    $query->select(['bonus_id', 'type_name', 'type_money', 'min_goods_amount', 'usebonus_type', 'use_start_date', 'use_end_date'])->where([['use_start_date', '<', time()], ['use_end_date', '>', time()]]);
                }])->where(['order_id' => 0, 'is_use' => 0]);
            }])
            ->first();
    }

    public function setUsers($where, $data)
    {
        return $this->where($where)
            ->update($data);
    }

    public function addUser($data, $uid)
    {
        $this->table($uid);
        return $this->create($data);
    }

    //分表
    public function table($uid, $bit = 1, $seed = 24)
    {
        //16777216
        if($uid >> $seed != 0){
            $tb = $this->table . '_' . sprintf('%0' . $bit . 'd', ($uid >> $seed));
            $this->setTable($tb);
        }
    }

    //分库
    public function connection($uid, $bit = 1, $seed = 20)
    {
        if($uid >> $seed != 0){
            $cc = 'shop_' . sprintf('%0' . $bit . 'd', ($uid >> $seed));
            $this->setConnection($cc);
        }
    }
}
