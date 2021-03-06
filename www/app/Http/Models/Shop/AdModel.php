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

class AdModel extends Model
{
    protected $table = 'ad';
    protected $primaryKey = 'ad_id';
    public $timestamps = false;
    protected $guarded = [];

    public function getAdByPage($where, $search, $column = ['*'], $size = 15)
    {
        $m = $this->select($column)
            ->join('ad_position', 'ad_position.position_id', '=', 'ad.position_id')
            ->where($where);
        if (!empty($search['keywords'])) {
            $m->where('ad_name', 'like', '%' . $search['keywords'] . '%');
        }
        if (!empty($search['advance_date']) && $search['advance_date'] > 0) {
            if ($search['advance_date'] == 1) {
                $m->where([['end_time', '>', time()], ['end_time', '<', time() + 86400]]);
            } elseif ($search['advance_date'] == 2) {
                $m->where('end_time', '<', time());
            }
        }
        return $m->orderBy('sort_order', 'DESC')
            ->paginate($size);
    }

    public function getAds($where, $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->get();
    }

    public function getAd($where, $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->first();
    }

    public function setAd($where, $data)
    {
        return $this->where($where)
            ->update($data);
    }

    public function addAd($data)
    {
        return $this->create($data);
    }

    public function delAd($where)
    {
        try {
            return $this->where($where)
                ->delete();
        } catch (\Exception $e) {

        }
    }
}
