<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc:
 */

namespace App\Http\Models\Wxapp;

use Illuminate\Database\Eloquent\Model;

class SellerShopInfoModel extends Model
{
    protected $table = 'seller_shop_info';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
