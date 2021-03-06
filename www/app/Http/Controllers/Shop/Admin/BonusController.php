<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc: 红包设置功能
 */

namespace App\Http\Controllers\Shop\Admin;

use App\Facades\Verifiable;
use App\Repositories\Admin\BonusRepository;
use App\Repositories\Admin\GoodsRepository;
use Illuminate\Http\Request;

class BonusController extends CommonController
{
    private $bonusRepository;
    private $goodsRepository;

    public function __construct(
        BonusRepository $bonusRepository,
        GoodsRepository $goodsRepository
    )
    {
        parent::__construct();
        $this->checkPrivilege('bonus');
        $this->bonusRepository = $bonusRepository;
        $this->goodsRepository = $goodsRepository;
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
        $bonuses = $this->bonusRepository->getBonusByPage($search, $seller);
        return view('shop.admin.faat.bonus', compact('seller', 'search', 'bonuses'));
    }

    public function send($id)
    {
        $bonus = $this->bonusRepository->getBonus($id);

        if ($bonus->send_type == 0) {
            return view('shop.admin.faat.bonusSendUsers', compact('bonus'));
        } elseif ($bonus->send_type == 1) {
            $goodses = $this->goodsRepository->getGoodses(['bonus_type_id'=>$id]);
            return view('shop.admin.faat.bonusSendGoods', compact('bonus', 'goodses'));
        } elseif ($bonus->send_type == 4) {
            return view('shop.admin.faat.bonusSendReceive', compact('bonus'));
        }
    }

    public function bonusUser($id)
    {
        $bonusUser = $this->bonusRepository->getBonusUserByPage($id);
        return view('shop.admin.faat.bonusUser', compact('bonusUser'));
    }

    public function addBonusUser(Request $request)
    {
        return $this->bonusRepository->addBonusUser($request->except('_token'));
    }

    public function delBonusUser(Request $request)
    {
        return $this->bonusRepository->delBonusUser($request->except('token'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $now_date = $this->now_date;
        return view('shop.admin.faat.bonusAdd', compact('now_date'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ver = Verifiable::Validator($request->all(), ["type_name" => 'required', "type_money" => 'required', "min_goods_amount" => 'required', "use_start_end_date" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->bonusRepository->addBonus($request->except('_token'), $this->user);
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
        $bonuses = $this->bonusRepository->getBonusByPage($search, $seller);
        return view('shop.admin.faat.bonus', compact('seller', 'search', 'bonuses'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $bonus = $this->bonusRepository->getBonus($id);
        return view('shop.admin.faat.bonusEdit', compact('bonus'));
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
        $ver = Verifiable::Validator($request->all(), ["type_name" => 'required', "type_money" => 'required', "min_goods_amount" => 'required', "use_start_end_date" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->bonusRepository->setBonus($request->except('_token', '_method'), $id);
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
        return $this->bonusRepository->delBonus($id);
    }
}
