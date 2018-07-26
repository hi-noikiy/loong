<?php

namespace App\Http\Models\Shop;

use function foo\func;
use Illuminate\Database\Eloquent\Model;

class OrderInfoModel extends Model
{
    protected $table = 'order_info';
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    protected $guarded = [];

    public function Goods()
    {
        //GoodsModel前必须加App\Http\Models\Shop, 第二个参数是表名，无需前缀，第三个参数是本类的字段，第四个参数是要查找的字段
        return $this->belongsToMany('App\Http\Models\Shop\GoodsModel', 'order_goods', 'order_id', 'goods_id');
    }

    public function TradeSnapshot()
    {
        return $this->hasOne('App\Http\Models\Shop\TradeSnapshotModel', 'order_sn','order_sn');
    }

    public function User()
    {
        return $this->hasOne('App\Http\Models\Shop\UsersModel', 'user_id','user_id');
    }

    public function getOrderInfoByPage($where, $orWhere, $search, $column = ['*'], $size = 15)
    {
        $m = $this->select($column)
            ->with(['Goods'=>function($query){
                $query->select(['*'])->get();
            }])
            ->with(['TradeSnapshot'=>function($query){
                $query->select(['*'])->get();
            }])
            ->with(['User'=>function($query){
                $query->select(['*'])->get();
            }])
            ->where($where);
        if (!empty($orWhere)) {
            $m->where(function ($query) use ($orWhere) {
                foreach ($orWhere as $value) {
                    $query->orWhere($value);
                }
            });
        }
        if (!empty($search)) {
            $m->where(function ($query) use ($search) {
                if (!empty($search['keywords'])) {
//                    $query->orWhere('order_goods.goods_name', 'like', '%'.$search['keywords'].'%');
                    $query->orWhere('goods_sn', 'like', '%' . $search['keywords'] . '%');
                }
            });
        }
//        dd($m->toSql());
        return $m->paginate($size);
    }

    public function getOrderInfo($where, $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->first();
    }

    public function setOrderInfo($where = [], $data, $whereIn = [])
    {
        $m = $this->where($where);
        if (!empty($whereIn)) {
            $m->whereIn('order_id', $whereIn);
        }
        return $m->update($data);
    }

    public function countOrder($where, $orWhere = [], $seller = 'selfsale')
    {
        $m = $this->where($where);

        if ($seller == 'selfsale') {
            $m->where('user_id', '=', '0');
        } else {
            $m->where('user_id', '<>', '0');
        }

        if (!empty($orWhere)) {
            $m->where(function ($query) use ($orWhere) {
                foreach ($orWhere as $value) {
                    $query->orWhere($value);
                }
            });
        }
        return $m->count();
    }

    public function delOrderInfo($where = [], $whereIn = [])
    {
        return $this->where($where)->delete();
    }
}
