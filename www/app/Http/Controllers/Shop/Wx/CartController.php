<?php

namespace App\Http\Controllers\Shop\Wx;

use App\Facades\Verifiable;
use App\Repositories\Wxapp\GoodsRepository;
use Illuminate\Http\Request;

class CartController extends CommonController
{
    private $goodsRepository;

    public function __construct(
        GoodsRepository $goodsRepository
    )
    {
        parent::__construct();
        $this->goodsRepository = $goodsRepository;
    }

    public function index(Request $request)
    {
        $uid = Verifiable::authorization($request);
        $re = $this->goodsRepository->cartList($request->all(), $uid);
        return ['code' => 0, 'msg' => '', 'data' => $re];
    }

    public function addCart(Request $request)
    {
        $uid = Verifiable::authorization($request);
        $re = $this->goodsRepository->addCart($request->all(), $uid);
        if ($re) {
            return ['code' => 0, 'msg' => '添加成功', 'data' => $re];
        } else {
            return ['code' => 1, 'msg' => '购物车已满', 'data' => ''];
        }
    }

    public function setCart(Request $request)
    {
        $re = $this->goodsRepository->setCart($request->all());
        if ($re) {
            return ['code' => 0, 'msg' => ''];
        } else {
            return ['code' => 1, 'msg' => ''];
        }
    }

    public function delCart(Request $request)
    {
        $re = $this->goodsRepository->delCart($request->all());
        if ($re) {
            return ['code' => 0, 'msg' => ''];
        } else {
            return ['code' => 1, 'msg' => ''];
        }
    }
}
