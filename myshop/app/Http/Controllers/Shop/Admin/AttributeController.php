<?php

namespace App\Http\Controllers\Shop\Admin;

use App\Facades\Verifiable;
use App\Repositories\AttributeRepository;
use App\Repositories\GoodsTypeRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AttributeController extends CommonController
{

    private $attributeRepository;
    private $goodsTypeRepository;

    public function __construct(AttributeRepository $attributeRepository, GoodsTypeRepository $goodsTypeRepository)
    {
        parent::__construct();
        $this->attributeRepository = $attributeRepository;
        $this->goodsTypeRepository = $goodsTypeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function change($id)
    {
        
    }

    public function deleteAll($ids)
    {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $goodsTypes = $this->goodsTypeRepository->getGoodsTypes(['user_id' => session('user')->ru_id]);
        return view('shop.admin.attribute.attributeAdd', compact('goodsTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ver = Verifiable::Validator($request->all(), ["attr_name" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->attributeRepository->addAttribute($request->except('_token'));
        return view('shop.admin.success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $attributes = $this->attributeRepository->getAttributePage($id);
        $goodsType = $this->goodsTypeRepository->getGoodsTypeAll();
        return view('shop.admin.attribute.attribute', compact('attributes', 'goodsType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $goodsTypes = $this->goodsTypeRepository->getGoodsTypes(['user_id' => session('user')->ru_id]);
        $attribute = $this->attributeRepository->getAttribute($id);
        return view('shop.admin.attribute.attributeEdit', compact('goodsTypes', 'attribute'));
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
        $ver = Verifiable::Validator($request->all(), ["attr_name" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->attributeRepository->setAttribute($request->except('_token', '_method'), $id);
        return view('shop.admin.success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}