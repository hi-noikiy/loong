<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc: 店铺设置功能
 */

namespace App\Http\Controllers\Shop\Admin;

use App\Facades\Html;
use App\Facades\ShopConfig;
use App\Facades\Verifiable;
use App\Repositories\Admin\MerchantsRepository;
use App\Repositories\Admin\StoreRepository;
use Illuminate\Http\Request;

class StoreController extends CommonController
{

    private $storeRepository;
    private $merchantsRepository;

    public function __construct(
        StoreRepository $storeRepository,
        MerchantsRepository $merchantsRepository
    )
    {
        parent::__construct();
        $this->checkPrivilege('store');
        $this->storeRepository = $storeRepository;
        $this->merchantsRepository = $merchantsRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $nav = 'store';
        $configs = $this->storeRepository->getGroupsConfig('seller');
        $conf = Html::StoreConfHtml($configs);
        return view('shop.admin.store.storeSetup', compact('conf', 'nav'));
    }

    //编辑初始化权限
    public function privilegeEdit()
    {
        $nav = 'privilege';
        $sellernavs = $this->sellerPrivilege();
        $gradeprivilege = $this->merchantsRepository->getSellerGradesByPri();
        return view('shop.admin.store.privilege', compact('sellernavs', 'nav', 'gradeprivilege'));
    }

    public function searchSellerGradesByPri(Request $request)
    {
        return $this->merchantsRepository->getSellerGradeByPri($request->except('_token'));
    }

    //分配初始化权限
    public function allot(Request $request)
    {
        $ver = Verifiable::Validator($request->all(), ["grade" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->merchantsRepository->setSellerGrade($request->except('_token'));
        return view('shop.admin.success');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ShopConfig::setConf($request->all(), 'seller');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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
