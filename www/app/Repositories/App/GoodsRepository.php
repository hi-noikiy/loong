<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/4/6
 * Time: 21:02
 */

namespace App\Repositories\App;

use App\Contracts\GoodsRepositoryInterface;
use App\Facades\Common;
use App\Facades\FileHandle;
use App\Facades\RedisCache;
use App\Http\Models\App\CartModel;
use App\Http\Models\App\CollectGoodsModel;
use App\Http\Models\App\CommentExtModel;
use App\Http\Models\App\CommentLabelModel;
use App\Http\Models\App\CommentModel;
use App\Http\Models\App\FavourableGoodsModel;
use App\Http\Models\App\GoodsDescriptionModel;
use App\Http\Models\App\GoodsModel;
use App\Http\Models\App\ProductsModel;
use App\Http\Models\App\TransportModel;
use App\Http\Models\App\UsersModel;

class GoodsRepository implements GoodsRepositoryInterface
{
    private $goodsModel;
    private $transportModel;
    private $commentModel;
    private $commentLabelModel;
    private $commentExtModel;
    private $usersModel;
    private $goodsDescriptionModel;
    private $cartModel;
    private $favourableGoodsModel;
    private $productsModel;
    private $collectGoodsModel;

    public function __construct(
        GoodsModel $goodsModel,
        TransportModel $transportModel,
        CommentModel $commentModel,
        CommentLabelModel $commentLabelModel,
        CommentExtModel $commentExtModel,
        UsersModel $usersModel,
        GoodsDescriptionModel $goodsDescriptionModel,
        CartModel $cartModel,
        FavourableGoodsModel $favourableGoodsModel,
        ProductsModel $productsModel,
        CollectGoodsModel $collectGoodsModel
    )
    {
        $this->goodsModel = $goodsModel;
        $this->transportModel = $transportModel;
        $this->commentModel = $commentModel;
        $this->commentLabelModel = $commentLabelModel;
        $this->commentExtModel = $commentExtModel;
        $this->usersModel = $usersModel;
        $this->goodsDescriptionModel = $goodsDescriptionModel;
        $this->cartModel = $cartModel;
        $this->favourableGoodsModel = $favourableGoodsModel;
        $this->productsModel = $productsModel;
        $this->collectGoodsModel = $collectGoodsModel;
    }

    public function getBestGoods($page = 1)
    {
        $where = ['is_delete' => 0, 'is_best' => 1, 'is_on_sale' => 1];
        $column = ['goods_id', 'goods_name', 'shop_price', 'market_price', 'goods_thumb', 'goods_img', 'original_img', 'is_best', 'promote_price', 'is_promote', 'is_fullcut', 'is_volume', 'sales_volume'];
        $goodses = $this->goodsModel->getGoodses($where, $page, $column);
        foreach ($goodses as $value) {
            $value->goods_thumb = FileHandle::getImgByOssUrl($value->goods_thumb);
            $value->goods_img = FileHandle::getImgByOssUrl($value->goods_img);
            $value->original_img = FileHandle::getImgByOssUrl($value->original_img);
            $value->market_price_format = Common::priceFormat($value->market_price);
            $value->shop_price_format = Common::priceFormat($value->shop_price);
            $value->promote_price_format = Common::priceFormat($value->promote_price);
            if ($value->gvp->count() > 0) {
                $value->volume_number = $value->gvp[0]->volume_number;
                $value->volume_price = Common::priceFormat($value->gvp[0]->volume_price);
            }
        }
        return $goodses;
    }

    public function getGoodsDetail($goods_id, $user_id = 0)
    {
        $where['goods_id'] = $goods_id;
        $where['is_on_sale'] = 1;
        $where['is_delete'] = 0;
        $where['review_status'] = 3;
        $column = [
            'goods_id', 'cat_id', 'user_id', 'goods_name', 'goods_sn', 'brand_id', 'freight',
            'goods_number', 'shop_price', 'market_price', 'promote_price', 'promote_start_date', 'promote_end_date',
            'desc_mobile', 'goods_desc', 'goods_id', 'goods_thumb', 'original_img', 'goods_img', 'is_on_sale',
            'is_delete', 'is_best', 'is_new', 'is_hot', 'is_promote', 'is_volume', 'is_fullcut',
            'goods_type', 'is_limit_buy', 'limit_buy_start_date', 'limit_buy_end_date', 'limit_buy_num', 'review_status',
            'sales_volume', 'comments_number', 'tid', 'goods_cause', 'goods_video', 'is_distribution',
            'pinyin_keyword', 'goods_brief'
        ];
        $goods_detail = $this->goodsModel->getGoodsAndExt($where, $column);
        if ($goods_detail) {
            $mobile_descs = unserialize($goods_detail->desc_mobile);
            $goods_detail->mobile_descs = $mobile_descs;
            $goods_detail->goods_video = FileHandle::getImgByOssUrl($goods_detail->goods_video);
            $goods_detail->goods_thumb = FileHandle::getImgByOssUrl($goods_detail->goods_thumb);
            $goods_detail->original_img = FileHandle::getImgByOssUrl($goods_detail->original_img);
            $goods_detail->goods_img = FileHandle::getImgByOssUrl($goods_detail->goods_img);
            $goods_detail->shop_price_format = Common::priceFormat($goods_detail->shop_price);
            $goods_detail->market_price_format = Common::priceFormat($goods_detail->market_price);
            $goods_detail->promote_price_format = Common::priceFormat($goods_detail->promote_price);
            $goods_detail->count_cart = $this->cartModel->countCart(['user_id' => $user_id]);

            $goods_detail->collect = $this->collectGoodsModel->countCollectGoods(['goods_id' => $goods_id, 'user_id' => $user_id, 'is_attention' => 1]);

            //大型活动
            $faats = $this->favourableGoodsModel->getFaat([['goods_id' => $goods_detail->goods_id], ['brand_id' => $goods_detail->brand_id], ['cate_id' => $goods_detail->cat_id]]);
            foreach ($faats as $faat) {
                $faat->current_time = time();
                $faat->min_amount = Common::priceFormat($faat->min_amount);
                if ($faat->act_type == 1) {
                    $faat->act_type_ext = Common::priceFormat($faat->act_type_ext);
                } elseif ($faat->act_type == 2) {
                    $faat->act_type_ext = ((float)$faat->act_type_ext * 10) . '';
                }

                $faat->gift = unserialize($faat->gift);
            }
            $goods_detail->faat = $faats;

            //退货货标志
            $goods_cause = explode(',', $goods_detail->goods_cause);
            $causes = [];
            $causeName = Common::causeName();
            if (count($goods_cause) > 0) {
                foreach ($goods_cause as $cause) {
                    if (!empty($cause)) {
                        $gcause['cause_type'] = $cause;
                        $gcause['name'] = $causeName[$cause];
                        $causes[] = $gcause;
                    }
                }
            }
            $goods_detail->goods_cause = $causes;
            if (!empty($goods_detail->brand)) {
                $goods_detail->brand->brand_logo = FileHandle::getImgByOssUrl($goods_detail->brand->brand_logo);
            }

            foreach ($goods_detail->ggallery as $gallery) {
                $gallery->img_original = FileHandle::getImgByOssUrl($gallery->img_original);
                $gallery->img_url = FileHandle::getImgByOssUrl($gallery->img_url);
            }

            foreach ($goods_detail->gvp as $gvp) {
                $gvp->volume_price_format = Common::priceFormat((int)$gvp->volume_price);
            }

            foreach ($goods_detail->fullcut as $fullcut) {
                $fullcut->cfull_format = Common::priceFormat((int)$fullcut->cfull);
                $fullcut->creduce_format = Common::priceFormat((int)$fullcut->creduce);
            }

            //快递
            if ($goods_detail->freight == 2) {
                $twhere['tid'] = $goods_detail->tid;
                $transport = $this->transportModel->getTransport($twhere);
                $goods_detail->transport = $transport;
            }

            //评价
            $goods_detail->comments = $this->commentModel->getComments($goods_id);
            foreach ($goods_detail->comments as $comment) {
                $comment->user_logo = $comment->user->logo;
                if ($comment->user_logo != '') {
                    $comment->user_logo = FileHandle::getImgByOssUrl($comment->user_logo);
                }
                unset($comment->user);
            }

            //评价统计
            $commentLabels = $this->commentLabelModel->getCommentLabels();
            foreach ($commentLabels as $commentLabel) {
                $commentLabel->count = $this->commentExtModel->countCommentExt(['id_value' => $goods_id, 'label_id' => $commentLabel->id]);
            }
            $goods_detail->comment_label = $commentLabels;

            //品牌商品
            $goods_detail->brand_goodses = $this->goodsModel->getGoodses(['brand_id' => $goods_detail->brand_id], 1, ['goods_name', 'original_img', 'shop_price', 'is_promote', 'promote_price', 'promote_start_date', 'promote_end_date'], 15);
            foreach ($goods_detail->brand_goodses as $brand_goods) {
                $brand_goods->original_img = FileHandle::getImgByOssUrl($brand_goods->original_img);
                $brand_goods->shop_price_format = Common::priceFormat($brand_goods->shop_price);
                $brand_goods->promote_price_format = Common::priceFormat($brand_goods->promote_price);
                $brand_goods->current_time = time();
            }
            //用户地址
            $uwhere['user_id'] = $user_id;
            $user = $this->usersModel->getUserByAddress($uwhere, ['user_id', 'address_id']);
            if ($user) {
                foreach ($user->addresses as $address) {
                    if ($address->address_id == $user->address_id) {
                        $user->default_address = $address;
                    }
                }
            }
            $goods_detail->user = $user;
            $goods_detail->goods_description = $this->goodsDescriptionModel->getGoodsDescriptions();
            //商品属性整理
            $goods_detail->gattr;
            $multi_attr = [];
            $single_attr = [];
            foreach ($goods_detail->gattr as $akey => $gattr) {
                $gattr->attr_name = $gattr->attr->attr_name;
                $gattr->attr_group = $gattr->attr->attr_group;
                if ($gattr->attr->attr_type == 1) {
                    if ($gattr->attr_img_flie != '') {
                        $gattr->attr_img_flie = FileHandle::getImgByOssUrl($gattr->attr_img_flie);
                    }
                    if ($gattr->attr_gallery_flie != '') {
                        $gattr->attr_gallery_flie = FileHandle::getImgByOssUrl($gattr->attr_gallery_flie);
                    }
                    $multi_attr[$gattr->attr_id][] = $gattr;
                } else {
                    $single_attr[] = $gattr;
                }
                unset($gattr->attr);
            }
            unset($goods_detail->gattr);
            $multi = [];
            foreach ($multi_attr as $mattr) {
                $multi[] = $mattr;
            }
            $goods_detail->multi_attr = $multi;
            $goods_detail->single_attr = $single_attr;
        }
        return $goods_detail;
    }

    public function cartList($request, $uid)
    {
        $column = ['rec_id', 'user_id', 'goods_id', 'goods_sn', 'product_id', 'goods_attr'
            , 'goods_number', 'goods_attr_id', 'add_time', 'ru_id', 'goods_name'
        ];
        $res = [];
        $where['user_id'] = 0;
        if ($uid != '' || !empty($uid)) {
            $where['user_id'] = $uid;
        } else {
            if (!empty($request['device_id'])) {
                $where['session_id'] = $request['device_id'];
            }
        }
        $rec_ids = [];
        if (!empty($request['rec_ids'])) {
            $rec_ids = explode(',', $request['rec_ids']);
        }
        $res = $this->cartModel->getCarts($where, $column, $rec_ids);

        $data = [];
        foreach ($res as $k => $re) {
            $arr = $re->toArray();
            foreach ($arr as $key => $value) {
                if ($key == 'goods') {
                    if ($arr['goods']['promote_end_date'] > time()) {
                        $arr['goods']['is_promote'] = '1';
                    } else {
                        $arr['goods']['is_promote'] = '0';
                    }
                    $arr['goods']['original_img'] = FileHandle::getImgByOssUrl($arr['goods']['original_img']);
                    $arr['goods']['shop_price_format'] = Common::priceFormat($arr['goods']['shop_price']);
                    $arr['goods']['market_price_format'] = Common::priceFormat($arr['goods']['market_price']);
                    $arr['goods']['promote_price_format'] = Common::priceFormat($arr['goods']['promote_price']);
                    $arr['goods']['current_time'] = time();
                } elseif ($key == 'store') {
                    $arr['store']['shop_logo'] = FileHandle::getImgByOssUrl($arr['store']['shop_logo']);
                    $data[$arr['store']['ru_id']]['store'] = $arr['store'];
                } elseif ($key == 'tax') {
                    $arr['goods']['tax'] = $value['attr_value'];
                } else {
                    $arr['goods'][$key] = $value;
                }
            }
            $data[$arr['store']['ru_id']]['goods'][] = $arr['goods'];
        }
        $data_bak = [];
        foreach ($data as $d) {
            $data_bak[] = $d;
        }
        return $data_bak;
    }

    public function addCart($request, $uid = 0)
    {
        $uwhere = [];
        $goods_id = $request['goods_id'];
        $session_id = !empty($request['device_id']) ? $request['device_id'] : 0;
        $where['goods_id'] = $goods_id;
        $where['goods_attr_id'] = !empty($request['goods_attr_ids']) ? $request['goods_attr_ids'] : '';
        if (!empty($uid)) {
            $count = $this->cartModel->countCart(['user_id' => $uid]);
        } else {
            $count = $this->cartModel->countCart(['session_id' => $session_id]);
        }
        if ($count < 30) {
            if ($uid != 0) {
                $where['user_id'] = $uid;
                $uwhere['user_id'] = $uid;
            } else {
                $where['session_id'] = $session_id;
                $uwhere['session_id'] = $session_id;
            }
            $count = $this->cartModel->countCart($where);
            if ($count == 0) {
                $goods = $this->goodsModel->getGoods(['goods_id' => $goods_id]);
                $goods_attr = [];
                $attr_value = [];
                if (!empty($request['goods_attr_ids'])) {
                    $goods_attr_ids = explode(',', $request['goods_attr_ids']);
                    $goods_attr = $this->productsModel->getProdcutAndAttr($goods_attr_ids);
                    foreach ($goods_attr->attrs as $attr) {
                        $attr_value[] = $attr->attr_value;
                    }
                }
                $cart = [
                    'user_id' => $uid,
                    'session_id' => $session_id,
                    'goods_id' => $goods_id,
                    'goods_sn' => $goods->goods_sn,
                    'goods_name' => $goods->goods_name,
                    'market_price' => $goods->market_price,
                    'goods_price' => $goods->shop_price,
                    'goods_number' => 1,
                    'is_real' => $goods->is_real,
                    'goods_attr_id' => !empty($request['goods_attr_ids']) ? $request['goods_attr_ids'] : '',
                    'ru_id' => $goods->user_id,
                    'shopping_fee' => 0,
                    'warehouse_id' => 0,
                    'area_id' => 0,
                    'add_time' => time(),
                    'freight' => $goods->freight,
                    'tid' => $goods->tid,
                    'shipping_fee' => $goods->shipping_fee,
                    'take_time' => date(RedisCache::get('shop_config')['time_format'], time() + 86400 * 15),
                    'product_id' => !empty($goods_attr) ? $goods_attr->product_id : 0,
                    'goods_attr' => implode(',', $attr_value)
                ];
                $this->cartModel->addCart($cart);
            } else {
                $this->cartModel->incrementCartGoodsNumber($where);
            }

            return ['count_cart' => $this->cartModel->countCart($uwhere)];
        }
        return 0;
    }

    public function setCart($request)
    {
        $where['rec_id'] = !empty($request['rec_id']) ? $request['rec_id'] : 0;
        if (!empty($request['goods_number'])) {
            $update['goods_number'] = $request['goods_number'];
            return $this->cartModel->setCart($where, $update);
        }
    }

    public function delCart($request)
    {
        $where = explode(',', !empty($request['rec_ids']) ? $request['rec_ids'] : 0);
        return $this->cartModel->delCarts($where);
    }
}