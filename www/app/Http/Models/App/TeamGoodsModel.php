<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc:
 */

namespace App\Http\Models\App;

use Illuminate\Database\Eloquent\Model;

class TeamGoodsModel extends Model
{
    protected $table = 'team_goods';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function teamLog()
    {
        return $this->hasMany('App\Http\Models\App\TeamLogModel', 'goods_id', 'goods_id');
    }

    public function goods()
    {
        return $this->hasOne('App\Http\Models\App\GoodsModel', 'goods_id', 'goods_id');
    }
}