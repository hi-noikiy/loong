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

class FavourableActivityModel extends Model
{
    protected $table = 'favourable_activity';
    protected $primaryKey = 'act_id';
    public $timestamps = false;
    protected $guarded = [];
}
