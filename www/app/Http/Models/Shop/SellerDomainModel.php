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

class SellerDomainModel extends Model
{
    protected $table = 'seller_domain';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function getSellerDomainsByPage($size = 15)
    {
        return $this->orderBy('id', 'desc')
            ->paginate($size);
    }

    public function getSellerDomain($where)
    {
        return $this->where($where)
            ->first();
    }

    public function setSellerDomain($where, $data)
    {
        return $this->where($where)
            ->update($data);
    }
}
