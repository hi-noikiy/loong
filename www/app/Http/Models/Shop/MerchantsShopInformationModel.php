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

class MerchantsShopInformationModel extends Model
{
    protected $table = 'merchants_shop_information';
    protected $primaryKey = 'shop_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->hasOne('App\Http\Models\Shop\UsersModel', 'user_id', 'user_id');
    }

    public function category()
    {
        return $this->hasOne('App\Http\Models\Shop\CategoryModel', 'id', 'shop_categoryMain');
    }

    public function msf()
    {
        return $this->hasOne('App\Http\Models\Shop\MerchantsStepsFieldsModel', 'user_id', 'user_id');
    }

    public function getMerchantsShopsByPage($where, $search, $column = ['*'], $size = 15)
    {
        $m = $this->select($column)
            ->with(['user'])
            ->with(['category'])
            ->with(['msf'])
            ->leftJoin('merchants_grade', 'merchants_shop_information.user_id', '=', 'merchants_grade.ru_id')
            ->leftJoin('seller_grade', 'merchants_grade.grade_id', '=', 'seller_grade.id')
            ->leftJoin('seller_shop_info', 'merchants_shop_information.user_id', '=', 'seller_shop_info.ru_id')
            ->where($where);
        if (!empty($search['keywords'])) {
            $m->where(function ($query) use ($search) {
                $query->orWhere('rz_shopName', 'like', '%' . $search['keywords'] . '%');
            });
        }
        if (!empty($search['examine'])) {
            if ($search['examine'] == 1) {
                $m->where(['merchants_audit' => 0]);
            } elseif ($search['examine'] == 2) {
                $m->where(['review_status' => 1]);
            }
        }
        $m->orderBy('shop_id', 'desc');
        return $m->paginate($size);
    }

    public function getMerchantsShopInfo($where, $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->first();
    }

    public function setMerchantsShopInfo($where, $data)
    {
        return $this->where($where)
            ->update($data);
    }

    public function addMerchantsShopInfo($data)
    {
        return $this->create($data);
    }
}
