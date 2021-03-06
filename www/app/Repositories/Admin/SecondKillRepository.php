<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/4/6
 * Time: 21:02
 */

namespace App\Repositories\Admin;

use App\Contracts\SecondKillRepositoryInterface;
use App\Http\Models\Shop\GoodsModel;
use App\Http\Models\Shop\SecKillGoodsModel;
use App\Http\Models\Shop\SecKillModel;
use App\Http\Models\Shop\SecKillTimeBucketModel;

class SecondKillRepository implements SecondKillRepositoryInterface
{

    private $secKillModel;
    private $secKillTimeBucketModel;
    private $secKillGoodsModel;
    private $goodsModel;

    public function __construct(
        SecKillModel $secKillModel,
        SecKillTimeBucketModel $secKillTimeBucketModel,
        SecKillGoodsModel $secKillGoodsModel,
        GoodsModel $goodsModel
    )
    {
        $this->secKillModel = $secKillModel;
        $this->secKillTimeBucketModel = $secKillTimeBucketModel;
        $this->secKillGoodsModel = $secKillGoodsModel;
        $this->goodsModel = $goodsModel;
    }

    public function secondKillChange($data)
    {
        $req = ['code' => 5, 'msg' => '操作失败'];
        switch ($data['type']) {
            case 'delete';
                $re = $this->secKillModel->delSecondKills($data['id']);
                if (!empty($re)) {
                    $this->secKillGoodsModel->delSecKillGoodses($data['id']);
                    $req = ['code' => 1, 'msg' => '操作成功'];
                }
                return $req;
                break;
            case 'putaway';
                $where['sec_id'] = $data['id'];
                $updata['is_putaway'] = $data['value'];
                $re = $this->secKillModel->setSecondKill($where, $updata);
                if (!empty($re)) {
                    $req = ['code' => 1, 'msg' => '操作成功'];
                }
                return $req;
                break;
        }
    }

    public function getSecondKillByPage($seller, $search = [])
    {
        if ($seller == 'seller') {
            $where = [['ru_id', '<>', 0]];
        } else {
            $where = [['ru_id', '=', 0]];
        }
        $res = $this->secKillModel->getSecKillByPage($where, ['*'], $search['keywords']);
        return $res;
    }

    public function addSecondKill($data, $user)
    {
        $updata['acti_title'] = $data['acti_title'];
        $updata['ru_id'] = $user->ru_id;
        $start_end_date = explode('～', $data['use_start_end_date']);
        $updata['is_putaway'] = $data['is_putaway'];
        $updata['start_time'] = strtotime($start_end_date[0]);
        $updata['end_time'] = strtotime($start_end_date[1]);
        $updata['add_time'] = time();
        $updata['review_status'] = $data['review_status'];

        return $this->secKillModel->addSecondKill($updata);
    }

    public function getSecondKill($id)
    {
        $where['sec_id'] = $id;
        $re = $this->secKillModel->getSecondKill($where);
        return $re;
    }

    public function setSecondKill($data, $id)
    {
        $where['sec_id'] = $id;
        $updata['acti_title'] = $data['acti_title'];
        $start_end_date = explode('～', $data['use_start_end_date']);
        $updata['is_putaway'] = $data['is_putaway'];
        $updata['start_time'] = strtotime($start_end_date[0]);
        $updata['end_time'] = strtotime($start_end_date[1]);
        $updata['review_status'] = $data['review_status'];
        return $this->secKillModel->setSecondKill($where, $updata);
    }

    public function delSecondKill($id)
    {
        $req = ['code' => 5, 'msg' => '操作失败'];
        $where['sec_id'] = $id;
        $re = $this->secKillModel->delSecondKill($where);
        if (!empty($re)) {
            $this->secKillGoodsModel->delSecKillGoods($where);
            $req = ['code' => 1, 'msg' => '操作成功'];
        }
        return $req;
    }

    public function getSecondKillTimeBuckets()
    {
        return $this->secKillTimeBucketModel->getSecKillTimeBuckets();
    }

    public function addSecondKillTimeBucket($data)
    {
        $updata['title'] = $data['title'];
        $updata['begin_time'] = $data['begin_hour'] . ':' . $data['begin_minute'] . ':' . $data['begin_second'];
        $updata['end_time'] = $data['end_hour'] . ':' . $data['end_minute'] . ':' . $data['end_second'];
        return $this->secKillTimeBucketModel->addSecondKillTimeBucket($updata);
    }

    public function getSecondKillTimeBucket($id)
    {
        $where['id'] = $id;
        $re = $this->secKillTimeBucketModel->getSecondKillTimeBucket($where);
        $re->begin_time = explode(':', $re->begin_time);
        $re->end_time = explode(':', $re->end_time);
        return $re;
    }

    public function setSecondKillTimeBucket($data, $id)
    {
        $where['id'] = $id;
        $updata['title'] = $data['title'];
        $updata['begin_time'] = $data['begin_hour'] . ':' . $data['begin_minute'] . ':' . $data['begin_second'];
        $updata['end_time'] = $data['end_hour'] . ':' . $data['end_minute'] . ':' . $data['end_second'];
        return $this->secKillTimeBucketModel->setSecondKillTimeBucket($where, $updata);
    }

    public function delSecondKillTimeBucket($id)
    {
        $req = ['code' => 5, 'msg' => '操作失败'];
        $where['id'] = $id;
        $re = $this->secKillTimeBucketModel->delSecondKillTimeBucket($where);
        if (!empty($re)) {
            $req = ['code' => 1, 'msg' => '操作成功'];
        }
        return $req;
    }

    public function secondKillGoodsChange($data)
    {
        $req = ['code' => 5, 'msg' => '操作失败'];
        $where['id'] = $data['id'];
        $updata = [];
        switch ($data['type']) {
            case 'sec_price';
                $updata['sec_price'] = $data['value'];
                break;
            case 'sec_num';
                $updata['sec_num'] = $data['value'];
                break;
            case 'sec_limit';
                $updata['sec_limit'] = $data['value'];
                break;
        }
        $re = $this->secKillGoodsModel->setSecKillGoods($where, $updata);
        if (!empty($re)) {
            $req = ['code' => 1, 'msg' => '操作成功'];
        }
        return $req;
    }

    public function getSecondKillGoodses($sid, $stid)
    {
        $where['sec_id'] = $sid;
        $where['tb_id'] = $stid;
        return $this->secKillGoodsModel->getSecKillGoodses($where);
    }

    public function addSecondKillGoodses($data)
    {
        $where['sec_id'] = $data['sid'];
        $where['tb_id'] = $data['stid'];
        $wherein['goods_id'] = explode(',', $data['goods_ids']);
        $skGoodses = $this->secKillGoodsModel->getSecKillGoodses($where, $wherein);
        $goods_ids = [];
        foreach ($skGoodses as $skGoods) {
            $goods_ids[] = $skGoods->goods_id;
        }
        $result = array_diff($wherein['goods_id'], $goods_ids);
        $goodses = $this->goodsModel->getGoodsesByIn($result, ['goods_id', 'shop_price']);
        $updata['sec_id'] = $data['sid'];
        $updata['tb_id'] = $data['stid'];
        $updata['sec_num'] = 100;
        $updata['sec_limit'] = 1;
        foreach ($goodses as $goods) {
            $updata['goods_id'] = $goods->goods_id;
            $updata['sec_price'] = $goods->shop_price;
            $re = $this->secKillGoodsModel->addSecKillGoods($updata);
        }
        $res = $this->secKillGoodsModel->getSecKillGoodses($where, []);
        if ($res->count() > 0) {
            return ['code' => 0, 'msg' => '', 'data' => $res];
        } else {
            return ['code' => 1, 'msg' => '', 'data' => []];
        }
    }

    public function delSecondKillGoodses($id)
    {
        $req = ['code' => 5, 'msg' => '操作失败'];
        $where['id'] = $id;
        $re = $this->secKillGoodsModel->delSecKillGoods($where);
        if (!empty($re)) {
            $req = ['code' => 1, 'msg' => '操作成功'];
        }
        return $req;
    }
}