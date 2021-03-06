<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc: 商城设置功能
 */

namespace App\Http\Controllers\Shop\Admin;

use App\Facades\Html;
use App\Facades\LangConfig;
use App\Facades\ShopConfig;
use App\Repositories\Admin\RegionsRepository;
use App\Repositories\Admin\SellerRepository;
use Illuminate\Http\Request;

class ShopConfController extends CommonController
{

    private $sellerRepository;
    private $regionsRepository;

    public function __construct(
        SellerRepository $sellerRepository,
        RegionsRepository $regionsRepository
    )
    {
        parent::__construct();
        $this->checkPrivilege('shopsetup');
        $this->sellerRepository = $sellerRepository;
        $this->regionsRepository = $regionsRepository;
    }

    public function index(Request $request)
    {
        $lang = LangConfig::LangAdminConf();
        $lang += LangConfig::LangAdminShopConf();
        $conf = ShopConfig::getConf();
        $conf = Html::shopConfHtml($conf, $lang);
        return view('shop.admin.shopConf', compact('lang', 'conf'));
    }

    public function store(Request $request)
    {
        ShopConfig::setConf($request->all());
        return view('shop.admin.success');
    }

    public function selfShopConf(Request $request)
    {
        $self = $this->sellerRepository->getSellerShopInfo(['ru_id' => $this->user->ru_id]);
        return view('shop.admin.self.selfShopConf', compact('self'));
    }

    public function update(Request $request, $id)
    {
        $re = $this->sellerRepository->setSellerShopInfo($request->except('_token', '_method'), $this->user->ru_id);
        return view('shop.admin.success');
    }
}
