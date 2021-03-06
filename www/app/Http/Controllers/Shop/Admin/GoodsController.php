<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc: 商品设置功能
 */

namespace App\Http\Controllers\Shop\Admin;

use App\Facades\Verifiable;
use App\Repositories\Admin\BrandRepository;
use App\Repositories\Admin\ComCateRepository;
use App\Repositories\Admin\GalleryRepository;
use App\Repositories\Admin\GoodsAttrRepository;
use App\Repositories\Admin\GoodsRepository;
use App\Repositories\Admin\GoodsTypeRepository;
use App\Repositories\Admin\TransportRepository;
use App\Repositories\Admin\UserRankRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsController extends CommonController
{

    private $goodsRepository;
    private $comCateRepository;
    private $brandRepository;
    private $galleryRepository;
    private $transportRepository;
    private $goodsTypeRepository;
    private $goodsAttrRepository;
    private $userRankRepository;

    public function __construct(
        GoodsRepository $goodsRepository,
        ComCateRepository $comCateRepository,
        BrandRepository $brandRepository,
        GalleryRepository $galleryRepository,
        TransportRepository $transportRepository,
        GoodsTypeRepository $goodsTypeRepository,
        GoodsAttrRepository $goodsAttrRepository,
        UserRankRepository $userRankRepository
    )
    {
        parent::__construct();
        $this->checkPrivilege('goods');
        $this->goodsRepository = $goodsRepository;
        $this->comCateRepository = $comCateRepository;
        $this->brandRepository = $brandRepository;
        $this->galleryRepository = $galleryRepository;
        $this->transportRepository = $transportRepository;
        $this->goodsTypeRepository = $goodsTypeRepository;
        $this->goodsAttrRepository = $goodsAttrRepository;
        $this->userRankRepository = $userRankRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $navType = 'normal';
        $seller = 'selfsale';
        $keywords = '';
        $goodsList = $this->goodsRepository->getGoodsPage($request->get('keywords'));
        $goodsNav = $this->goodsRepository->getGoodsNav();
        return view('shop.admin.goods.goods', compact('goodsList', 'goodsNav', 'navType', 'keywords', 'seller'));
    }

    public function change(Request $request)
    {
        return $this->goodsRepository->setGoods($request->except('_token'));
    }

    public function changes(Request $request)
    {
        return $this->goodsRepository->setGoodsMore($request->except('_token'));
    }

    //商品审核窗口
    public function examine($id)
    {
        $goods = $this->goodsRepository->getGoods($id);
        return view('shop.admin.goods.modal.examine', compact('goods'));
    }

    //商品权重排序页
    public function weightOrder($id)
    {
        $goods = $this->goodsRepository->getGoods($id);
        $goodsWeight = $this->goodsRepository->getGoodsByWeightOrder($id);
        return view('shop.admin.goods.modal.weightOrder', compact('goodsWeight', 'goods'));
    }

    //商品扩展分类窗口
    public function cateExtend($id)
    {
        $comCates = $this->comCateRepository->getComCates();
        $cateExtend = $this->goodsRepository->getCateExtend($id);
        $selectedCates = [];
        foreach ($cateExtend as $key => $value) {
            $selectedCates[] = $this->comCateRepository->getParentCate($value->cat_id);
        }
        return view('shop.admin.goods.modal.goodsCateExtend', compact('comCates', 'id', 'selectedCates'));
    }

    //添加商品扩展分类
    public function addCateExtend(Request $request)
    {
        return $this->goodsRepository->addCateExtend($request->except('_token'));
    }

    //删除商品扩展分类
    public function delCateExtend($id)
    {
        return $this->goodsRepository->delCateExtend($id);
    }

    //删除回收站里的商品
    public function thoroughDel($id)
    {
        return view('shop.admin.success');
    }

    //从回收站还原
    public function backTo($id)
    {
        $data['id'] = $id;
        $data['type'] = 'is_delete';
        $data['val'] = 0;
        $this->goodsRepository->setGoods($data);
        return view('shop.admin.success');
    }

    //商品图册编辑选择窗口
    public function imageLibrary($type, $goods_id)
    {
        $gallerys = $this->galleryRepository->getGallerys();
        $galleryPics = $this->galleryRepository->getGalleryPicsByPage(['album_id' => $gallerys[0]->album_id]);
        return view('shop.admin.goods.modal.imgLibrary', compact('galleryPics', 'gallerys', 'type', 'goods_id'));
    }

    //相册目录编辑窗口
    public function addGalleryShow()
    {
        $gallerys = $this->galleryRepository->getGallerys();
        return view('shop.admin.goods.modal.galleryAdd', compact('gallerys'));
    }

    //添加商品相册
    public function addGallery(Request $request)
    {
        return $this->galleryRepository->addGallery($request->except('_token'));
    }

    //添加商品轮播图册api
    public function addGoodsGallery(Request $request)
    {
        return $this->galleryRepository->addGoodsGallery($request->except('_token'));
    }

    //上传商品轮播图api
    public function upGoodsGalleryPic(Request $request)
    {
        return $this->galleryRepository->upGoodsGalleryPic($request->except('_token'));
    }

    public function changeGoodsGallery(Request $request)
    {
        return $this->galleryRepository->setGoodsGallery($request->except('_token'));
    }

    //删除商品轮播图api
    public function delGoodsGalleryPic(Request $request)
    {
        return $this->galleryRepository->delGoodsGalleryPic($request->except('_token'));
    }

    //添加自定义属性窗口
    public function customAttrWin($id, $gid)
    {
        return view('shop.admin.goods.modal.customAttr', compact('id', 'gid'));
    }

    public function getGoodsAttr($id)
    {
        return $this->goodsAttrRepository->getGoodsAttr($id);
    }

    //添加属性
    public function addGoodsAttr(Request $request)
    {
        return $this->goodsAttrRepository->addGoodsAttr($request->except('_token'));
    }

    //更新商品属性
    public function setGoodsAttr(Request $request)
    {
        return $this->goodsAttrRepository->setGoodsAttr($request->except('_token'));
    }

    //获取商品属性货品
    public function getGoodsByProduct(Request $request, $id)
    {
        return $this->goodsRepository->getProductAndGallerys($request->except('_token'), $id, $this->user->ru_id);
    }

    //商品sku窗口
    public function getGoodsByProductSku($id)
    {
        $products = $this->goodsRepository->getProductByPage($id);
        return view('shop.admin.goods.modal.sku', compact('products'));
    }

    //修改商品sku
    public function changeProductSku(Request $request)
    {
        return $this->goodsRepository->changeProductSku($request->all());
    }

    //删除SKU商品
    public function delProduct(Request $request, $id)
    {
        return $this->goodsRepository->delProduct($id);
    }

    public function delVolumePrice($id)
    {
        return $this->goodsRepository->delVolumePrice($id);
    }

    public function delFullCut($id)
    {
        return $this->goodsRepository->delFullCut($id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $comCates = $this->comCateRepository->getComCates();
        $brands = $this->brandRepository->search([], true);
        $transports = $this->transportRepository->getTransportByRuId($this->user->ru_id);
        $goodsTypeCates = $this->goodsTypeRepository->getTypeCates();
        $goodsGallerys = $this->galleryRepository->getGoodsGallerys();
        $userRanks = $this->userRankRepository->getUserRanks();
        $now_date = date('Y-m-d', time());
        return view('shop.admin.goods.goodsAdd', compact('comCates', 'brands', 'transports', 'goodsTypeCates', 'goodsGallerys', 'now_date', 'userRanks'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ver = Verifiable::Validator($request->all(), ["goods_name" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->goodsRepository->addGoods($request->except('_token'), $this->user->ru_id);
        return view('shop.admin.success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $seller = $request->get('seller');
        if ($id == 'seller' || $id == 'selfsale') {
            $navType = 'normal';
            $seller = $id;
        } else {
            $navType = $id;
        }
        $keywords = $request->get('keywords');
        $goodsList = $this->goodsRepository->getGoodsPage($keywords, [$navType, $seller]);
        $goodsNav = $this->goodsRepository->getGoodsNav();
        return view('shop.admin.goods.goods', compact('goodsList', 'goodsNav', 'navType', 'keywords', 'seller'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $transports = $this->transportRepository->getTransportByRuId($this->user->ru_id);
        $goodsTypeCates[] = $this->goodsTypeRepository->getTypeCates();
        $goodsGallerys = $this->galleryRepository->getGoodsGallerys();
        $brands = $this->brandRepository->search([], true);
        $now_date = date('Y-m-d', time());
        $userRanks = $this->userRankRepository->getUserRanks();

        $goodsInfo = $this->goodsRepository->getGoods($id);
        $brandName = $this->brandRepository->getBrand($goodsInfo->brand_id);
        $comCates = $this->comCateRepository->getParentCateBySelect($goodsInfo->cat_id);
        if (empty($comCates)) {
            $comCates[] = $this->comCateRepository->getComCates();
        }
        $comCateSelect = $this->comCateRepository->getParentCate($goodsInfo->cat_id);

        return view('shop.admin.goods.goodsEdit', compact('comCates', 'brands', 'goodsInfo', 'transports', 'goodsGallerys', 'comCateSelect', 'brandName', 'userRanks', 'now_date'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $ver = Verifiable::Validator($request->all(), ["goods_name" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->goodsRepository->updateGoods($request->except('_token', '_method'), $id, $this->user->ru_id);
        $back_url = $this->success('admin/goods/');
        return view('shop.admin.success', compact('back_url'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->goodsRepository->delGoods($id);
    }
}
