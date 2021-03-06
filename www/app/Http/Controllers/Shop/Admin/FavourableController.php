<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc: 优惠设置功能
 */

namespace App\Http\Controllers\Shop\Admin;

use App\Facades\Verifiable;
use App\Repositories\Admin\AdRepository;
use App\Repositories\Admin\FavourableActivityRepository;
use App\Repositories\Admin\UserRankRepository;
use Illuminate\Http\Request;

class FavourableController extends CommonController
{

    private $favourableActivityRepository;
    private $userRankRepository;
    private $adRepository;

    public function __construct(
        FavourableActivityRepository $favourableActivityRepository,
        UserRankRepository $userRankRepository,
        AdRepository $adRepository
    )
    {
        parent::__construct();
        $this->checkPrivilege('favourable');
        $this->favourableActivityRepository = $favourableActivityRepository;
        $this->userRankRepository = $userRankRepository;
        $this->adRepository = $adRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $seller = 'selfsale';
        $search['keywords'] = $request->get('keywords');
        $faats = $this->favourableActivityRepository->getFavourableActivityByPage($search, $seller);
        return view('shop.admin.faat.favourableActivity', compact('seller', 'search', 'faats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $now_date = $this->now_date;
        $userRanks = $this->userRankRepository->getUserRanks();
        $adGroup = $this->adRepository->getAdPosesByType('faat');
        return view('shop.admin.faat.favourableActivityAdd', compact('now_date', 'userRanks', 'adGroup'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ver = Verifiable::Validator($request->all(), ["act_name" => 'required', "start_end_date" => 'required',"act_range" => 'required', "min_amount" => 'required', "max_amount" => 'required', ]);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->favourableActivityRepository->addFavourableActivity($request->except('_token'), $this->user);
        return view('shop.admin.success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $seller = $id;
        $search['keywords'] = $request->get('keywords');
        $faats = $this->favourableActivityRepository->getFavourableActivityByPage($search, $seller);
        return view('shop.admin.faat.favourableActivity', compact('seller', 'search', 'faats'));
    }

    public function change(Request $request)
    {
        return $this->favourableActivityRepository->change($request->except('_token'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $userRanks = $this->userRankRepository->getUserRanks();
        $faat = $this->favourableActivityRepository->getFavourableActivity($id);
        $adGroup = $this->adRepository->getAdPosesByType('faat');
        return view('shop.admin.faat.favourableActivityEdit', compact('userRanks', 'faat', 'adGroup'));
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
        $ver = Verifiable::Validator($request->all(), ["act_name" => 'required', "start_end_date" => 'required',"act_range" => 'required', "min_amount" => 'required', "max_amount" => 'required', ]);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->favourableActivityRepository->setFavourableActivity($request->except('_token', '_method'), $id);
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
        return $this->favourableActivityRepository->delFavourableActivity($id);
    }
}
