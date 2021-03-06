<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/4/6
 * Time: 21:02
 */

namespace App\Repositories\Wxapp;

use App\Contracts\AdRepositoryInterface;
use App\Facades\FileHandle;
use App\Http\Models\Wxapp\AdModel;
use App\Http\Models\Wxapp\AdPositionModel;

class AdRepository implements AdRepositoryInterface
{

    private $adModel;
    private $adPositionModel;

    public function __construct(
        AdModel $adModel,
        AdPositionModel $adPositionModel
    )
    {
        $this->adModel = $adModel;
        $this->adPositionModel = $adPositionModel;
    }

    public function getAdPositionAndAds($nav_id = 0)
    {
        $where = [['ad_terminal', '=', 'wxapp'], ['ad_type', '<>', 'cate_ads'], ['nav_id', '=', $nav_id]];
        $res = $this->adPositionModel->getPositionByAds($where, ['position_id', 'ad_type', 'ad_width', 'ad_height']);
        $ads = [];
        foreach ($res as $re) {
            $adp['type'] = $re->ad_type;
            $adp['width'] = $re->ad_width;
            $adp['height'] = $re->ad_height;
            $advs = [];
            foreach ($re->ads as $ad) {
                $adv['ad_code'] = FileHandle::getImgByOssUrl($ad->ad_code);
                $adv['ad_link'] = $ad->ad_link;
                $adv['ad_color'] = $ad->link_color;
                $adv['ad_id'] = $ad->ad_id;
                $advs[] = $adv;
            }
            if (count($advs) > 0) {
                $adp['ads'] = $advs;
                $ads[] = $adp;
            }
        }
        return $ads;
    }

    public function getAdPositionAndAdsByFaat($id)
    {
        $where['position_id'] = $id;
        $re = $this->adPositionModel->getPositionByAd($where, ['position_id', 'ad_type', 'ad_width', 'ad_height']);
        $adp['type'] = $re->ad_type;
        $adp['width'] = $re->ad_width;
        $adp['height'] = $re->ad_height;
        $ads = $adp;
        $advs = [];
        foreach ($re->ads as $ad) {
            $adv['ad_code'] = FileHandle::getImgByOssUrl($ad->ad_code);
            $adv['ad_link'] = $ad->ad_link;
            $adv['ad_color'] = $ad->link_color;
            $advs[] = $adv;
        }
        if (count($advs) > 0) {
            $adp['ads'] = $advs;
            $ads = $adp;
        }
        return $ads;
    }

    public function getgetAdPositionAndBootPages()
    {
        $where = [['ad_terminal', '=', 'app'], ['ad_type', '=', 'boot_page']];
        $res = $this->adPositionModel->getPositionByAds($where, ['position_id', 'ad_type', 'ad_width', 'ad_height']);
        foreach ($res as $re) {
            $adp['type'] = $re->ad_type;
            $adp['width'] = $re->ad_width;
            $adp['height'] = $re->ad_height;
            $advs = [];
            foreach ($re->ads as $ad) {
                $adv['ad_code'] = FileHandle::getImgByOssUrl($ad->ad_code);
                $adv['ad_link'] = $ad->ad_link;
                $adv['ad_color'] = $ad->link_color;
                $advs[] = $adv;
            }
        }
        return $advs;
    }
}