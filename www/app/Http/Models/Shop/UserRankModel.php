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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class UserRankModel extends Model
{
    protected $table = 'user_rank';
    protected $primaryKey = 'rank_id';
    public $timestamps = false;
    protected $guarded = [];

    public function getUserRanks($where = [], $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->orderBy('discount', 'desc')
            ->orderBy('rank_id', 'asc')
            ->get();
    }

    public function getUserRank($where = [], $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->first();
    }

    public function getUserRanksByIn($where = [], $column = ['*'])
    {
        return $this->select($column)
            ->whereIn('rank_id', $where)
            ->get();
    }

    public function setUserRank($where, $data)
    {
        return $this->where($where)->update($data);
    }

    public function addUserRank($data)
    {
        return $this->create($data);
    }

    public function searchUserRanks($search, $column = ['*'])
    {
        $m = $this->select($column);
        foreach ($search as $key => $value) {
            $m->orWhere($key, 'like', '%' . $value . '%');
        }
        return $m->get();
    }

    public function getUserRankByUser()
    {
        $prefix = Config::get('database.connections.mysql.prefix');
        return DB::table('user_rank')->select('*', DB::raw("(select count(*) from `{$prefix}users` where `rank_points` between {$prefix}user_rank.min_points and {$prefix}user_rank.max_points) as `user_count`"))->get();
    }

    public function delUserRank($where)
    {
        return $this->where($where)->delete();
    }
}
