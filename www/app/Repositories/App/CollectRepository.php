<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/4/6
 * Time: 21:02
 */

namespace App\Repositories\App;

use App\Contracts\CollectRepositoryInterface;
use App\Facades\FileHandle;
use App\Facades\RedisCache;
use App\Http\Models\App\BrowseGoodsModel;
use App\Http\Models\App\CartModel;
use App\Http\Models\App\CollectBrandModel;
use App\Http\Models\App\CollectGoodsModel;
use App\Http\Models\App\CollectStoreModel;

class CollectRepository implements CollectRepositoryInterface
{

    private $collectGoodsModel;
    private $collectBrandModel;
    private $collectStoreModel;
    private $cartModel;
    private $browseGoodsModel;

    public function __construct(
        CollectGoodsModel $collectGoodsModel,
        CollectBrandModel $collectBrandModel,
        CollectStoreModel $collectStoreModel,
        CartModel $cartModel,
        BrowseGoodsModel $browseGoodsModel
    )
    {
        $this->collectGoodsModel = $collectGoodsModel;
        $this->collectBrandModel = $collectBrandModel;
        $this->collectStoreModel = $collectStoreModel;
        $this->cartModel = $cartModel;
        $this->browseGoodsModel = $browseGoodsModel;
    }

    public function colloectsByGoods($data, $uid)
    {
        $where['user_id'] = $uid;
        $where['is_attention'] = 1;
        $page = $data['page'];
        $res = $this->collectGoodsModel->getColloectsByGoods($where, $page);
        foreach ($res as $re) {
            $re->add_time_format = date(RedisCache::get('shop_config')['time_format'], $re->add_time);
            $re->goods->current_time = time();
            $re->goods->original_img = FileHandle::getImgByOssUrl($re->goods->original_img);
        }
        return $res;
    }

    public function collectGoods($request, $uid)
    {
        $goods_ids = explode(',', $request['goods_id']);
        $data = [];
        foreach ($goods_ids as $goods_id) {
            $where['goods_id'] = $goods_id;
            $where['user_id'] = $uid;
            $re = $this->collectGoodsModel->getCollectGoods($where);
            if (!$re) {
                $data['goods_id'] = $request['goods_id'];
                $data['user_id'] = $uid;
                $data['add_time'] = time();
                $data['is_attention'] = 1;
                $this->collectGoodsModel->addCollectGoods($data);
            } else {
                if ($re->is_attention == 1) {
                    $data['is_attention'] = 0;
                } else {
                    $data['is_attention'] = 1;
                }
                $this->collectGoodsModel->setCollectGoods($where, $data);
            }
        }
        return $data;
    }

    public function collectGoodsToCart($request, $uid)
    {
        $rec_ids = explode(',', $request['rec_ids']);
        $data = [];

        $carts = $this->cartModel->getCarts([], ['*'], $rec_ids);
        foreach ($carts as $cart) {
            $where['goods_id'] = $cart->goods_id;
            $where['user_id'] = $uid;
            $re = $this->collectGoodsModel->getCollectGoods($where);
            if (!$re) {
                $data['goods_id'] = $request['goods_id'];
                $data['user_id'] = $uid;
                $data['add_time'] = time();
                $data['is_attention'] = 1;
                $this->collectGoodsModel->addCollectGoods($data);
            } else {
                if ($re->is_attention == 1) {
                    $data['is_attention'] = 0;
                } else {
                    $data['is_attention'] = 1;
                }
                $this->collectGoodsModel->setCollectGoods($where, $data);
            }
        }
        $this->cartModel->delCarts($rec_ids);
        return $data;
    }

    public function collectBrand($request, $uid)
    {
        $where['brand_id'] = $request['brand_id'];
        $where['ru_id'] = $request['store_id'];
        $where['user_id'] = $uid;
        $re = $this->collectBrandModel->getCollectBrand($where);
        if (!$re) {
            $data['brand_id'] = $request['brand_id'];
            $data['ru_id'] = $request['store_id'];
            $data['user_id'] = $uid;
            $data['add_time'] = time();
            $data['is_attention'] = 1;
            return $this->collectBrandModel->addCollectBrand($data);
        } else {
            if ($re->is_attention == 1) {
                $data['is_attention'] = 0;
            } else {
                $data['is_attention'] = 1;
            }
            return $this->collectBrandModel->setCollectBrand($where, $data);
        }
    }

    public function collectStore($request, $uid)
    {
        $where['ru_id'] = $request['store_id'];
        $where['user_id'] = $uid;
        $re = $this->collectStoreModel->getCollectStore($where);
        if (!$re) {
            $data['ru_id'] = $request['store_id'];
            $data['user_id'] = $uid;
            $data['add_time'] = time();
            $data['is_attention'] = 1;
            return $this->collectStoreModel->addCollectStore($data);
        } else {
            if ($re->is_attention == 1) {
                $data['is_attention'] = 0;
            } else {
                $data['is_attention'] = 1;
            }
            return $this->collectStoreModel->setCollectStore($where, $data);
        }
    }

    public function browseByGoods($data, $uid)
    {
        $where['user_id'] = $uid;
        $where['is_attention'] = 1;
        $page = $data['page'];
        $res = $this->browseGoodsModel->getBrowseGoodses($where, $page);
        $time = strtotime(date('Y-m-d', time()));
        foreach ($res as $re) {
            if($re->add_time > $time){
                $re->group = 1;
            }elseif($re->add_time > $time-1*86400){
                $re->group = 2;
            }elseif($re->add_time > $time-2*86400){
                $re->group = 3;
            }elseif($re->add_time > $time-3*86400){
                $re->group = 4;
            }elseif($re->add_time > $time-4*86400){
                $re->group = 5;
            }elseif($re->add_time > $time-5*86400){
                $re->group = 6;
            }elseif($re->add_time > $time-6*86400){
                $re->group = 7;
            }elseif($re->add_time > $time-6*86400){
                $re->group = 7;
            }
            $re->add_time_format = date('Y-m-d', $re->add_time);
            $re->goods->current_time = time();
            $re->goods->original_img = FileHandle::getImgByOssUrl($re->goods->original_img);
        }
        return $res;
    }

    public function setBowse($request, $uid)
    {
        $where['user_id'] = $uid;
        $browse_ids = explode(',', $request['browse_ids ']);
        $updata = [
            'is_attention' => 0
        ];
        return $this->browseGoodsModel->setBrowseGoods($where, $updata, $browse_ids);
    }
}