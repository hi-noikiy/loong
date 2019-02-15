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

class TeamGoodsModel extends Model
{
    protected $table = 'team_goods';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function teamLog()
    {
        return $this->hasMany('App\Http\Models\Wxapp\TeamLogModel', 'goods_id', 'goods_id');
    }

    public function goods()
    {
        return $this->hasOne('App\Http\Models\Wxapp\GoodsModel', 'goods_id', 'goods_id');
    }
}
