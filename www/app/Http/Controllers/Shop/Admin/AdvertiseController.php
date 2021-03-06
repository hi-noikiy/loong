<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc: 广告设置功能
 */

namespace App\Http\Controllers\Shop\Admin;

use App\Facades\Verifiable;
use App\Repositories\Admin\AdRepository;
use App\Repositories\Admin\ComCateRepository;
use Illuminate\Http\Request;

class AdvertiseController extends CommonController
{
    private $adRepository;
    private $comCateRepository;

    public function __construct(
        AdRepository $adRepository,
        ComCateRepository $comCateRepository
    )
    {
        parent::__construct();
        $this->checkPrivilege('ad_list');
        $this->adRepository = $adRepository;
        $this->comCateRepository = $comCateRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = 'pc';
        $search['keywords'] = $request->get('keywords');
        $search['advance_date'] = $request->get('advance_date');
        $ads = $this->adRepository->getAdByPage($type, $search);
        return view('shop.admin.ads.ads', compact('type', 'search', 'ads'));
    }

    public function change(Request $request)
    {
        return $this->adRepository->adChange($request->except('_token'));
    }

    public function adShow($type, $id)
    {
        $type = $type ? $type : 'pc';
        $search['keywords'] = '';
        $ads = $this->adRepository->getAds($type, $id);
        return view('shop.admin.ads.ads', compact('type', 'search', 'ads'));
    }

    public function adAdd($id)
    {
        $type = $id;
        $adsposes = $this->adRepository->getAdPoses($id);
        $cates = $this->comCateRepository->getComCates();
        $now_date = $this->now_date;
        return view('shop.admin.ads.adsAdd', compact('adsposes', 'now_date', 'cates', 'type'));
    }

    public function adEdit($id, $ad_type)
    {
        $type = $ad_type;
        $adsposes = $this->adRepository->getAdPoses($type);
        $adInfo = $this->adRepository->getAd($id);
        $cates = $this->comCateRepository->getComCates();
        $parentCates = $this->comCateRepository->getParentCate($adInfo->cate_id);
        return view('shop.admin.ads.adsEdit', compact('adsposes', 'type', 'cates', 'parentCates', 'adInfo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ver = Verifiable::Validator($request->all(), ["ad_name" => 'required', "position_id" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->adRepository->addAd($request->except('_token'));
        $back_url = $this->success('admin/ad/', $request->get('ad_terminal'), 'admin/ad/add/' . $request->get('ad_terminal'));
        return view('shop.admin.success', compact('back_url'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $type = $id ? $id : 'pc';
        $search['keywords'] = $request->get('keywords');
        $search['advance_date'] = $request->get('advance_date');
        $ads = $this->adRepository->getAdByPage($type, $search);
        return view('shop.admin.ads.ads', compact('type', 'search', 'ads'));
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
        $ver = Verifiable::Validator($request->all(), ["ad_name" => 'required', "position_id" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->adRepository->setAd($request->except('_token', '_method'), $id);
        $back_url = $this->success('admin/ad/', $request->get('ad_terminal'), 'admin/ad/add/' . $request->get('ad_terminal'));
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
        return $this->adRepository->delAd($id);
    }
}
