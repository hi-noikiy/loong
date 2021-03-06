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

class GoodsGalleryModel extends Model
{
    protected $table = 'goods_gallery';
    protected $primaryKey = 'img_id';
    public $timestamps = false;
    protected $guarded = [];

    public function getGoodsGallerys($where, $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->orderBy('front_cover', 'desc')
            ->orderBy('img_desc', 'asc')
            ->get();
    }

    public function getGoodsGallery($where, $column = ['*'])
    {
        return $this->select($column)
            ->where($where)
            ->orderBy('front_cover', 'desc')
            ->first();
    }

    public function addGoodsGallery($data)
    {
        return $this->create($data);
    }

    public function setGoodsGallery($where, $data)
    {
        return $this->where($where)
            ->update($data);
    }

    public function delGoodsGallery($where)
    {
        try {
            return $this->where($where)->delete();
        } catch (\Exception $e) {}
    }
}
