<?php

namespace App\Repositories\Goods;

use App\Models\Category;
use App\Models\GoodsCat;
use App\Models\Users as User;
use App\Models\Goods;
use App\Models\Comment;
use App\Models\Products;
use App\Models\Attribute;
use App\Models\GoodsAttr;
use App\Models\GoodsGallery;
use App\Models\GoodsTransport;
use App\Models\ProductsArea;
use App\Models\WarehouseAttr;
use App\Services\AuthService;
use App\Models\WarehouseGoods;
use App\Models\ProductsWarehouse;
use App\Models\WarehouseAreaAttr;
use App\Models\WarehouseAreaGoods;
use App\Models\StoreGoods;
use App\Models\StoreProducts;
use App\Repositories\User\UserRankRepository;
use App\Repositories\User\MemberPriceRepository;
use App\Repositories\ShopConfig\ShopConfigRepository;
use App\Contracts\Repositories\Goods\GoodsRepositoryInterface;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class GoodsRepository implements GoodsRepositoryInterface
{
    protected $goods;
    private $field;
    private $userRankRepository;
    private $authService;
    private $memberPriceRepository;
    private $goodsAttrRepository;
    private $volumePriceRepository;
    private $shopConfigRepository;

    public function __construct(
        UserRankRepository $userRankRepository,
        AuthService $authService,
        MemberPriceRepository $memberPriceRepository,
        GoodsAttrRepository $goodsAttrRepository,
        VolumePriceRepository $volumePriceRepository,
        ShopConfigRepository $shopConfigRepository
    )
    {
        $this->setField();
        $this->userRankRepository = $userRankRepository;
        $this->authService = $authService;
        $this->memberPriceRepository = $memberPriceRepository;
        $this->goodsAttrRepository = $goodsAttrRepository;
        $this->volumePriceRepository = $volumePriceRepository;
        $this->shopConfigRepository = $shopConfigRepository;
    }

    /**
     * 新增单个商品
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
    }

    /**
     * 获取单个商品信息
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
    }

    /**
     * 更新商品信息
     * @param array $data
     * @return mixed
     */
    public function update(array $data)
    {
    }

    /**
     * 根据商品Id删除商品
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
    }

    /**
     * 商品搜索
     * @param array $data
     * @return mixed
     */
    public function search(array $data)
    {
    }

    /**
     * 获取商品SKU列表
     * @param $id
     * @return mixed
     */
    public function sku($id)
    {
    }

    /**
     * @return mixed
     */
    public function skuAdd()
    {
    }

    /**
     * 设置字段
     */
    public function setField()
    {
        $this->field = [
            'category' => 'cat_id'
        ];
    }

    /**
     * 获取字段
     * @param $field
     * @return mixed
     */
    public function getField($field)
    {
        return $this->field[$field];
    }

    /**
     * 获取单个商品
     * @param $goods_id
     * @return mixed
     */
    public function find($goods_id)
    {
        return Goods::select('*')
            ->where('goods_id', $goods_id)
            ->first()
            ->toArray();
    }

    /**
     * 商品列表
     * @param $field
     * @param $value
     * @param int $page
     * @param int $size
     * @param array $columns
     * @param string $keywords
     * @param string $sortKey
     * @param string $sortVal
     * @return mixed
     */
    public function findBy($field, $value, $page = 1, $size = 10, $columns = ['*'], $keywords = '', $sortKey = '', $sortVal = '')
    {
        $field = $this->getField($field);
        $begin = ($page - 1) * $size;
        $goods = Goods::select($columns);
        // 分类ID
        if ($value != 0) {
            $goods->where($field, $value);
        }

        // 关键词
        if (!empty($keywords)) {
            $goods->where('goods_name', 'like', "%{$keywords}%");
        }
        // 排序
        $sort = ['ASC', 'DESC'];
        if (!empty($sortKey)) {
            switch ($sortKey) {
                // 默认
                case 0:
                    $goods->orderby('goods_id', 'ASC');
                    break;
                // 销量
                case 1:
                    $goods->orderby('sales_volume', in_array($sortVal, $sort) ? $sortVal : 'ASC');
                    $goods->orderby('goods_id', in_array($sortVal, $sort) ? $sortVal : 'ASC');
                    break;
                // 价格
                case 2:
                    $goods->orderby('shop_price', in_array($sortVal, $sort) ? $sortVal : 'ASC');
                    $goods->orderby('goods_id', in_array($sortVal, $sort) ? $sortVal : 'ASC');
                    break;
            }
        }

        $res = $goods->where('is_on_sale', 1)
            ->where('is_delete', 0)
            //->offset($begin)//自己加的
            ->limit($size)//$begin,
            ->get()
            ->toArray();
        return $res;
    }

    //cyc-start
    //分词搜索
    public function searchBy($keywords = [], $page = 1, $size = 10, $sortKey = '', $sortVal = '', $columns = ['*'])
    {
        if (!empty($keywords)) {
            $begin = ($page - 1) * $size;
            $goods = Goods::select($columns);
            $attribut = Attribute::select(['cat_id', 'attr_values']);
            $attribut->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyVal) {
                    $query->orWhere('attr_values', 'LIKE', '%' . $keyVal . '%');
                }
            })
                ->where([['attr_index', '=', 1]]);
            $attr = $attribut->get();

            $catIds = [];
            $attrValues = [];

            if ($attr) {
                $attr = $attr->toArray();
                foreach ($attr as $attrVal) {
                    $catIds[] = $attrVal['cat_id'];
                    $attrValues[] = $attrVal['attr_values'];
                }
            }

            $goods->orWhere(function ($query) use ($catIds) {
                foreach ($catIds as $catId) {
                    $query->orWhere('cat_id', '=', $catId);
                }
            });

            $goods->orWhere(function ($query) use ($keywords) {
                foreach ($keywords as $keyVal) {
                    $query->orWhere('goods_name', 'LIKE', '%' . $keyVal . '%')
                        ->orWhere('keywords', 'LIKE', '%' . $keyVal . '%');
                }
            });
            switch ($sortKey) {
                case 0:
                    $goods->orderBy('sort_order', 'asc');
                    break;
                case 1:
                    if ($sortVal == 1) {
                        $goods->orderBy('sales_volume', 'desc');
                    } else {
                        $goods->orderBy('sales_volume', 'asc');
                    }
                    break;
                case 2:
                    if ($sortVal == 1) {
                        $goods->orderBy('shop_price', 'desc');
                    } else {
                        $goods->orderBy('shop_price', 'asc');
                    }
                    break;
                default:
                    break;
            }
            $goods->skip($begin)->take($size);
            $re = $goods->get();
            if ($re) {
                $re = $re->toArray();
            }
            return $re;
        }
    }

    //分类查询
    public function cateBy($cateId, $page = 1, $size = 10, $sortKey = '', $sortVal = '', $columns = ['*'])
    {
        $begin = ($page - 1) * $size;

        $catIdArr = $this->getSubCategoryAll($cateId);

        $goodsIds = GoodsCat::select(['goods_id'])->whereIn('cat_id', $catIdArr)->get();
        $goodsIdsArr = [];
        if ($goodsIds) {
            $goodsIds = $goodsIds->toArray();
            foreach ($goodsIds as $val) {
                $goodsIdsArr[] = $val['goods_id'];
            }
        }

        $goods = Goods::select(['goods.goods_id', 'goods.goods_thumb', 'goods.goods_name', 'goods.shop_price', 'goods.market_price', 'goods.sort_order'])
            ->leftJoin('goods_cat', 'goods.cat_id', '=', 'goods_cat.cat_id')
            ->distinct();
        if (!empty($goodsIdsArr)) {
            $goods->where(function ($query) use ($catIdArr, $goodsIdsArr) {
                $query->orWhere(function ($query) use ($catIdArr) {
                    $query->whereIn('goods.cat_id', $catIdArr);
                })
                    ->orWhere(function ($query) use ($goodsIdsArr) {
                        $query->whereIn('goods.goods_id', $goodsIdsArr);
                    });
            });
        } else {
            $goods->whereIn('goods.cat_id', $catIdArr);
        }

        switch ($sortKey) {
            case 0:
                if ($sortVal == 1) {
                    $goods->orderBy('goods.sort_order', 'asc');
                }else{
                    $goods->orderBy('goods.add_time', 'desc');
                }
                break;
            case 1:
                if ($sortVal == 1) {
                    $goods->orderBy('goods.sales_volume', 'desc');
                } else {
                    $goods->orderBy('goods.sales_volume', 'asc');
                }
                break;
            case 2:
                if ($sortVal == 1) {
                    $goods->orderBy('goods.shop_price', 'desc');
                } else {
                    $goods->orderBy('goods.shop_price', 'asc');
                }
                break;
            default:
                break;
        }

        $goods->skip($begin)->take($size);
        $re = $goods->get();

        if ($re) {
            $re = $re->toArray();
        }
        return $re;

    }

    private function getSubCategoryAll($cateId)
    {
        $category = Category::where([['parent_id', '=', $cateId]])->get();
        $catIdArr = [$cateId];
        if ($categorys = $category->toArray()) {
            foreach ($categorys as $val) {
                $catIdArr .= $this->getSubCategoryAll($val['cat_id']);
            }
        }
        return $catIdArr;
    }

    //cyc-end

    //这个是自己加的。就改成了in  其他不变。limit($size)  可以有两个参数。

    public function findByIn($field, $value, $page = 1, $size = 10, $columns = ['*'], $keywords = '', $sortKey = '', $sortVal = '')
    {
        //打印出都对，但是结果错。
        $begin = ($page - 1) * $size;

        $field = $this->getField($field);
        $begin = ($page - 1) * $size;//这个没用?
        $goods = Goods::select($columns);
        // 分类ID
        if ($value != 0) {
            $goods->wherein($field, [$value]);//[350,351]
        }

        // 关键词
        if (!empty($keywords)) {
            $goods->where('goods_name', 'like', "%{$keywords}%");
        }

        $goods->orderby('is_best', 'DESC');
        $goods->orderby('is_hot', 'DESC');
        $goods->orderby('is_new', 'DESC');


        $res = $goods->where('is_on_sale', 1)
            ->where('is_delete', 0)
            ->offset($begin)//自己加的
            ->limit($size)//$begin,
            ->get()
            ->toArray();

        //echo $goods->toSql(); echo "<BR/>"; echo "<BR/>"; echo "<BR/>"; echo "<BR/>"; echo "<BR/>"; echo "<BR/>";

        return $res;

        //print_r( $goods->getBindings());

        /*
        $sql = "select `goods_id`, `goods_name`, `shop_price`, `goods_thumb`, `goods_number`, `market_price`, `sales_volume`, `goods_brief` from `dsc_goods`
        where `cat_id` in ($value) and `is_on_sale` = 1 and `is_delete` = 0 order by `is_best` desc, `is_hot` desc, `is_new` desc limit $begin,$size";

        //echo $sql;

        $sql2 = "select goods_id from dsc_goods limit 0,1 ";

        $list = DB::select($sql2);

       return  $list;
       */
    }

    /**
     * 查询热门、新品、推荐等商品
     * @param string $type
     * @param integer $size
     * @return mixed
     */
    public function findByType($type = 'best', $size = 10, $page = 1)
    {
        // 判断类型
        switch ($type) {
            case 'hot':
                $type = 'is_hot';
                break;
            case 'new':
                $type = 'is_new';
                break;
            default:
                $type = 'is_best';
                break;
        }

        $begin = ($page - 1) * $size;

        //
        $goods = Goods::select('goods_id', 'cat_id', 'user_cat', 'user_id', 'goods_sn', 'goods_name', 'click_count', 'brand_id', 'provider_name', 'goods_number', 'goods_weight', 'default_shipping', 'market_price', 'cost_price', 'shop_price', 'promote_price', 'promote_start_date', 'promote_end_date', 'warn_number', 'keywords', 'goods_brief', 'goods_desc', 'desc_mobile', 'goods_thumb', 'goods_img', 'original_img', 'is_real', 'extension_code', 'is_on_sale', 'is_alone_sale', 'is_shipping', 'integral', 'add_time', 'sort_order', 'is_delete', 'is_best', 'is_new', 'is_hot', 'is_promote', 'is_volume', 'is_fullcut', 'bonus_type_id', 'last_update', 'goods_type', 'seller_note', 'give_integral', 'rank_integral', 'suppliers_id', 'is_check', 'store_hot', 'store_new', 'store_best', 'group_number', 'is_xiangou', 'xiangou_start_date', 'xiangou_end_date', 'xiangou_num', 'review_status', 'review_content', 'goods_shipai', 'comments_number', 'sales_volume', 'comment_num', 'model_price', 'model_inventory', 'model_attr', 'largest_amount', 'pinyin_keyword', 'goods_product_tag', 'goods_tag', 'stages', 'stages_rate', 'freight', 'shipping_fee', 'tid', 'goods_unit', 'goods_cause', 'dis_commission', 'is_distribution')
            ->where($type, 1)
            ->where('is_on_sale', 1)
            ->where('is_delete', 0)
            ->orderby('goods_id', 'desc')
            ->offset($begin)
            ->limit($size)//$begin,$begin,
            ->get()
            ->toArray();

        return $goods;
    }


    /**
     * 商品详情
     * @param $id
     * @return mixed
     */
    public function goodsInfo($id)
    {
        $res = Goods::select('goods_id', 'goods_name', 'shop_price as goods_price', 'market_price', 'goods_number as stock', 'goods_desc', 'desc_mobile', 'sales_volume as sales', 'goods_thumb', 'model_attr', 'goods_type', 'user_id', 'is_on_sale', 'promote_price', 'product_price', 'product_promote_price', 'promote_start_date', 'promote_end_date')
            ->where('goods_id', $id)
            ->where('is_delete', 0)
            ->first();
        if ($res === null) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 商品属性
     * @param $goods_id
     * @param int $warehouse_id
     * @param int $area_id
     * @return array
     */
    public function goodsProperties($goods_id, $warehouse_id = 0, $area_id = 0)
    {
        $res = $this->goodsAttrRepository->goodsAttr($goods_id);    // 商品属性

        $group = $this->goodsAttrRepository->attrGroup($goods_id);   // 获取商品属性组
        if (!empty($group)) {
            $groups = explode('\n', $group);
        }

        //属性类型
        $attrTypeDesc = ['唯一属性', '单选属性'];
        $properties = [];
        foreach ($res as $k => $v) {
            $v['attr_value'] = str_replace("\n", '<br />', $v['attr_value']);   //换行

            if ($v['attr_type'] == 0) {
                // 规格
                $group = (isset($groups[$v['attr_group']])) ? $groups[$v['attr_group']] : '';

                $properties['spe'][$group][$v['attr_id']]['name'] = $v['attr_name'];
                $properties['spe'][$group][$v['attr_id']]['value'] = $v['attr_value'];
            } else {
                //
                $properties['pro'][$v['attr_id']]['attr_type'] = $attrTypeDesc[$v['attr_type']];
                $properties['pro'][$v['attr_id']]['name'] = $v['attr_name'];

                $properties['pro'][$v['attr_id']]['values'][] = [
                    'label' => $v['attr_value'],
                    'attr_sort' => $v['attr_sort'],
                    'price' => $v['attr_price'],
                    'format_price' => price_format(abs($v['attr_price']), false),
                    'id' => $v['goods_attr_id']
                ];
            }
        }

        return $properties;
    }

    /**
     * 商品相册
     * @param $id
     * @return mixed
     */
    public function goodsGallery($id)
    {
        //图片
        return GoodsGallery::select('img_url')
            ->where('goods_id', $id)
            ->get()
            ->toArray();
    }

    /**
     * 商品评论
     * @param $id
     * @return mixed
     */
    public function goodsComment($id)
    {
        //评论
        $res = Comment::select('comment_id as id', 'user_id', 'content', 'add_time', 'comment_rank')
            ->where('id_value', $id)
            ->orderby('comment_id', 'DESC')
            ->get()
            ->toArray();

        return $res;
    }

    /**
     * 得到评论用户昵称
     * @param $user_id
     * @return
     */
    public function getGoodsCommentUser($user_id)
    {
        $user = User::select('nick_name', 'user_name')
            ->where('user_id', $user_id)
            ->first()
            ->toArray();

        if ($user === null) {
            return [];
        }

        $user['nick_name'] = !empty($user['nick_name']) ? $user['nick_name'] : $user['user_name'];

        return $user['nick_name'];
    }

    /**
     * 根据商品 获取货品信息
     * @param $goodsId
     * @param $goodsAttr
     * @return mixed
     */
    public function getProductByGoods($goodsId, $goodsAttr)
    {
        $product = Products::select('product_id as id', 'product_sn')
            ->where('goods_id', $goodsId)
            ->where('goods_attr', $goodsAttr)
            ->first();

        if ($product === null) {
            return [];
        }

        return $product->toArray();
    }

    /**
     * 获取购物车 商品信息
     * @param $rec_id
     * @return array
     */
    public function cartGoods($rec_id)
    {
        $goods = Goods::join('cart', 'goods.goods_id', '=', 'cart.goods_id')
            ->where('cart.rec_id', $rec_id)
            ->select('goods.goods_name', 'goods.goods_number', 'cart.product_id')
            ->first();

        if ($goods === null) {
            return [];
        }

        return $goods->toArray();
    }

    /**
     * 取得商品最终使用价格
     *
     * @param   string $goods_id 商品编号
     * @param   string $goods_num 购买数量
     * @param   boolean $is_spec_price 是否加入规格价格
     * @param   mix $property 规格ID的数组或者逗号分隔的字符串
     *
     * @return  商品最终购买价格
     */
    public function getFinalPrice($goods_id, $goods_num = '1', $is_spec_price = false, $property = [], $warehouse_id = 0, $area_id = 0)
    {
        $final_price = 0; //商品最终购买价格
        $volume_price = 0; //商品优惠价格
        $promote_price = 0; //商品促销价格
        $user_price = 0; //商品会员价格
        $spec_price = 0;

        //如果需要加入规格价格
        if ($is_spec_price) {
            $spec_price = $this->goodsPropertyPrice($goods_id, $property, $warehouse_id, $area_id);
        }

        //取得商品优惠价格列表
        $price_list = $this->getVolumePriceList($goods_id, '1');
        if (!empty($price_list)) {
            foreach ($price_list as $value) {
                if ($goods_num >= $value['number']) {
                    $volume_price = $value['price'];
                }
            }
        }

        $goods = Goods::from('goods as g')
            ->select('g.promote_price', 'g.promote_start_date', 'g.promote_end_date', 'mp.user_price')
            ->leftjoin('member_price as mp', 'mp.goods_id', '=', 'g.goods_id')
            ->where('g.goods_id', $goods_id)
            ->where('g.is_delete', 0)
            ->first()
            ->toArray();

        $member_price = $this->userRankRepository->getMemberRankPriceByGid($goods_id);
        $uid = $this->authService->authorization();
        $user_rank = User::select('user_rank')
            ->where('user_id', $uid)
            ->first();

        if (!empty($user_rank)) {
            $user_rank = $user_rank->user_rank;
            $user_price = $this->memberPriceRepository->getMemberPriceByUid($user_rank, $goods_id);
            $goods['user_price'] = $user_price;
        }

        $goods['shop_price'] = (isset($user_price) && !empty($user_price)) ? $user_price : $member_price;
        /* 计算商品的促销价格 */
        if ($this->shopConfigRepository->getShopConfigByCode('add_shop_price') == 0) {
            $goods['promote_price'] = $this->goodsPropertyPrice($goods_id, $property, $warehouse_id, $area_id, 'product_promote_price');
        }
        if (is_array($goods) && array_key_exists('promote_price', $goods) && $goods['promote_price'] > 0) {
            $promote_price = $this->bargainPrice($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
        } else {
            $promote_price = 0;
        }

        //取得商品会员价格列表
        $user_price = $goods['shop_price'];

        //比较商品的促销价格，会员价格，优惠价格
        if (empty($volume_price) && empty($promote_price)) {
            //如果优惠价格，促销价格都为空则取会员价格
            $final_price = $user_price;
        } elseif (!empty($volume_price) && empty($promote_price)) {
            //如果优惠价格为空时不参加这个比较。
            $final_price = min($volume_price, $user_price);
        } elseif (empty($volume_price) && !empty($promote_price)) {
            //如果促销价格为空时不参加这个比较。
            $final_price = min($promote_price, $user_price);
        } elseif (!empty($volume_price) && !empty($promote_price)) {
            //取促销价格，会员价格，优惠价格最小值
            $final_price = min($volume_price, $promote_price, $user_price);
        } else {
            $final_price = $user_price;
        }

        //如果需要加入规格价格
        if ($is_spec_price) {
            if (!empty($property)) {
                if ($this->shopConfigRepository->getShopConfigByCode('add_shop_price') == 1) {
                    $final_price += $spec_price;
                }
            }
        }

        if ($this->shopConfigRepository->getShopConfigByCode('add_shop_price') == 0) {
            if ($promote_price == 0) {
                //返回商品属性价
                $final_price = $spec_price;
            }
        }

        //返回商品最终购买价格
        return $final_price;

    }

    /**
     * 取得商品优惠价格列表
     *
     * @param   string $goods_id 商品编号
     * @param   string $price_type 价格类别(0为全店优惠比率，1为商品优惠价格，2为分类优惠比率)
     *
     * @return  优惠价格列表
     */
    public function getVolumePriceList($goods_id, $price_type = '1')
    {
        $volume_price = [];
        $temp_index = '0';

        $res = $this->volumePriceRepository->allVolumes($goods_id, $price_type);

        foreach ($res as $k => $v) {
            $volume_price[$temp_index] = [];
            $volume_price[$temp_index]['number'] = $v['volume_number'];
            $volume_price[$temp_index]['price'] = $v['volume_price'];
            $volume_price[$temp_index]['format_price'] = price_format($v['volume_price']);
            $temp_index++;
        }
        return $volume_price;
    }

    /**
     * 判断某个商品是否正在特价促销期
     *
     * @access  public
     * @param   float $price 促销价格
     * @param   string $start 促销开始日期
     * @param   string $end 促销结束日期
     * @return  float   如果还在促销期则返回促销价，否则返回0
     */
    public function bargainPrice($price, $start, $end)
    {
        if ($price == 0) {
            return 0;
        } else {
            $time = local_gettime();
            if ($time >= $start && $time <= $end) {
                return $price;
            } else {
                return 0;
            }
        }
    }

    /**
     * 根据商品ID查找品牌ID
     * @param $goodsId
     * @return mixed
     */
    public function getBrandIdByGoodsId($goodsId)
    {
        $brandId = Goods::where('goods_id', $goodsId)->pluck('brand_id');

        return !empty($brandId) ? $brandId['0'] : 0;
    }

    /**
     * 获取商品属性库存
     * @param $goods_id
     * @param $attr_id
     * @param $warehouse_id
     * @param $area_id
     * @param $store_id 门店id
     * @return $attr_number
     */
    public function goodsAttrNumber($goods_id, $attr_id, $warehouse_id = 0, $area_id = 0, $store_id = 0)
    {
        $goods = $this->goodsInfo($goods_id);//商品详情
        $products = $this->getProductsAttrNumber($goods_id, $attr_id, $warehouse_id, $area_id, $goods['model_attr'], $store_id); //获取有属性库存

        $prod = $this->goodsWarehouseNumber($goods_id, $warehouse_id, $area_id, $goods['model_attr'], $store_id);//无属性库存
        if (empty($products)) {
            if (empty($prod)) {
                $attr_number = !empty($goods['stock']) ? $goods['stock'] : 0;
            } else {
                $attr_number = $prod['product_number'];
            }
        } else {
            $attr_number = $products['product_number'];
        }

        return !empty($attr_number) ? $attr_number : 0;
    }

    /**
     * 查询属性商品仓库库存
     * @param $goods_id
     * @param $attr_id
     * @param $warehouse_id
     * @param $area_id
     * @param $model_attr
     * @param $store_id 门店id
     * @return mixed
     */
    public function getProductsAttrNumber($goods_id, $attr_id, $warehouse_id, $area_id, $model_attr = 0, $store_id = 0)
    {
        if (empty($attr_id)) {
            $attr_id = 0;
        } else {
            //去掉复选属性by wu start
            if (is_string($attr_id)) {
                $attr_arr = explode(',', $attr_id);
            } else {
                $attr_arr = $attr_id;
            }
            foreach ($attr_arr as $key => $val) {
                $attr_type = $this->getGoodsAttrId($val);

                if (($attr_type == 0 || $attr_type == 2) && $attr_arr[$key]) {
                    unset($attr_arr[$key]);
                }
            }
            $attr_id = implode('|', $attr_arr);
            //去掉复选属性by wu end
        }

        if ($store_id > 0) {
            $product_number = StoreProducts::select('product_number')
                ->where('goods_id', $goods_id)
                ->where('goods_attr', $attr_id)
                ->where('store_id', $store_id)
                ->first();
        } else {
            if ($model_attr == 1) {
                $product_number = ProductsWarehouse::select('product_number')
                    ->where('goods_id', $goods_id)
                    ->where('goods_attr', $attr_id)
                    ->where('warehouse_id', $warehouse_id)
                    ->first();
            } elseif ($model_attr == 2) {
                $product_number = ProductsArea::select('product_number')
                    ->where('goods_id', $goods_id)
                    ->where('goods_attr', $attr_id)
                    ->where('area_id', $area_id)
                    ->first();
            } else {
                $product_number = Products::select('product_number')
                    ->where('goods_id', $goods_id)
                    ->where('goods_attr', $attr_id)
                    ->first();
            }
        }

        if ($product_number === null) {
            return [];
        }

        return $product_number->toArray();
    }

    /**
     * 无属性库存
     * @param $goods_id
     * @param $warehouse_id
     * @param $area_id
     * @param $model_attr
     * @param $store_id 门店id
     * @return mixed
     */
    public function goodsWarehouseNumber($goods_id, $warehouse_id, $area_id, $model_attr = 0, $store_id = 0)
    {
        if ($store_id > 0) {
            $product_number = StoreGoods::select('goods_number')
                ->where('goods_id', $goods_id)
                ->where('goods_attr', $attr_id)
                ->where('store_id', $store_id)
                ->first();
        } else {
            if ($model_attr == 1) {
                $product_number = WarehouseGoods::select('region_number as product_number')
                    ->where('goods_id', $goods_id)
                    ->where('region_id', $warehouse_id)
                    ->first();
            } elseif ($model_attr == 2) {
                $product_number = WarehouseAreaGoods::select('region_number as product_number')
                    ->where('goods_id', $goods_id)
                    ->where('region_id', $area_id)
                    ->first();
            } else {
                $product_number = Goods::select('goods_number as product_number')
                    ->where('goods_id', $goods_id)
                    ->first();
            }
        }

        if ($product_number === null) {
            return [];
        }

        return $product_number->toArray();
    }

    /**
     * 商品属性价格、促销价格
     * @param  $goods_id
     * @param  $attr_id
     * @param  $warehouse_id
     * @param  $area_id
     * @param  $field 调用字段
     * @return $attr_price
     */
    public function goodsPropertyPrice($goods_id, $attr_id, $warehouse_id = 0, $area_id = 0, $field = '')
    {
        $goods = $this->goodsInfo($goods_id);//商品详情
        $products = $this->getProductsAttrPrice($goods_id, $attr_id, $warehouse_id, $area_id, $goods['model_attr']); //获取有属性价格

        $prod = $this->goodsWarehousePrice($goods_id, $warehouse_id, $area_id, $goods['model_attr']);//无属性价格

        if ($field == 'product_promote_price') {
            if (empty($products) || $products['product_promote_price'] <= 0) {
                if (empty($prod) || $prod['product_promote_price'] <= 0) {
                    $attr_price = !empty($goods['promote_price']) ? $goods['promote_price'] : 0;
                } else {
                    $attr_price = $prod['product_promote_price'];
                }
            } else {
                $attr_price = $products['product_promote_price'];
            }
        } else {
            if (empty($products) || $products['product_price'] <= 0) {
                if (empty($prod) || $prod['product_price'] <= 0) {
                    $attr_price = !empty($goods['shop_price']) ? $goods['shop_price'] : 0;
                } else {
                    $attr_price = $prod['product_price'];
                }
            } else {
                $attr_price = $products['product_price'];
            }
        }

        return !empty($attr_price) ? $attr_price : 0;
    }

    /**
     * 商品市场价格（多模式下）
     * @param  $goods_id
     * @param  $attr_id
     * @param  $warehouse_id
     * @param  $area_id
     * @return $market_price
     */
    public function goodsMarketPrice($goods_id, $attr_id, $warehouse_id = 0, $area_id = 0)
    {
        $goods = $this->goodsInfo($goods_id);//商品详情
        $products = $this->getProductsAttrPrice($goods_id, $attr_id, $warehouse_id, $area_id, $goods['model_attr']); //获取有属性价格

        if (empty($products) || $products['product_price'] <= 0) {
            $market_price = !empty($goods['market_price']) ? $goods['market_price'] : 0;
        } else {
            $attr_price = $products['product_price'];

            // SKU价格模式： 商品价格 + 属性货品价格 时， 市场价 = 原市场价 + 属性货品价格
            if ($this->shopConfigRepository->getShopConfigByCode('add_shop_price') == 1) {
                $market_price = $attr_price + $goods['market_price'];
            } else {
                // SKU价格模式： 属性货品价格 时， 市场价 = 属性市场价格
                $market_price = !empty($products['product_market_price']) ? $products['product_market_price'] : 0;
            }
        }

        return !empty($market_price) ? $market_price : 0;
    }

    /**
     * 查询属性商品仓库价格
     * @param $goods_id
     * @param $attr_id
     * @param $warehouse_id
     * @param $area_id
     * @param $model_attr
     * @return mixed
     */
    public function getProductsAttrPrice($goods_id, $attr_id, $warehouse_id, $area_id, $model_attr = 0)
    {
        if (empty($attr_id)) {
            $attr_id = 0;
        } else {
            //去掉复选属性by wu start
            if (is_string($attr_id)) {
                $attr_arr = explode(',', $attr_id);
            } else {
                $attr_arr = $attr_id;
            }
            foreach ($attr_arr as $key => $val) {
                $attr_type = $this->getGoodsAttrId($val);
                if (($attr_type == 0 || $attr_type == 2) && $attr_arr[$key]) {
                    unset($attr_arr[$key]);
                }
            }
            $attr_id = implode('|', $attr_arr);
            //去掉复选属性by wu end
        }

        //商品属性价格模式,货品模式
        if ($this->shopConfigRepository->getShopConfigByCode('goods_attr_price') == 1) {
            if ($model_attr == 1) {
                $product_price = ProductsWarehouse::select('product_price', 'product_promote_price', 'product_market_price')
                    ->where('goods_id', $goods_id)
                    ->where('goods_attr', $attr_id)
                    ->where('warehouse_id', $warehouse_id)
                    ->first();
            } elseif ($model_attr == 2) {
                $product_price = ProductsArea::select('product_price', 'product_promote_price', 'product_market_price')
                    ->where('goods_id', $goods_id)
                    ->where('goods_attr', $attr_id)
                    ->where('area_id', $area_id)
                    ->first();
            } else {
                $product_price = Products::select('product_price', 'product_promote_price', 'product_market_price')
                    ->where('goods_id', $goods_id)
                    ->where('goods_attr', $attr_id)
                    ->first();
            }

            if ($product_price === null) {
                return [];
            }

            return $product_price->toArray();

        } else {
            // $attr_id = explode('|', $attr_id);
            // //商品属性价格模式,单一模式
            // if ($model_attr == 1) {
            //     //仓库属性价格
            //     $price = WarehouseAttr::wherein('goods_attr_id', $attr_id)
            //         ->where('goods_id', $goods_id)
            //         ->where('warehouse_id', $warehouse_id)
            //         ->sum('attr_price');
            // } elseif ($model_attr == 2) {
            //     //地区属性价格
            //     $price = WarehouseAreaAttr::wherein('goods_attr_id', $attr_id)
            //         ->where('goods_id', $goods_id)
            //         ->where('area_id', $area_id)
            //         ->sum('attr_price');
            // } else {
            //     //普通属性价格
            //     $price = GoodsAttr::wherein('goods_attr_id', $attr_id)
            //         ->sum('attr_price');
            // }

            // if (floatval($price) == null) {
            //     return [];
            // }
            // $product_price = [
            //     'product_price' => $price
            // ];

            // return $product_price;
        }
    }

    /**
     * 无属性商品价格
     * @param $goods_id
     * @param $warehouse_id
     * @param $area_id
     * @param $model_attr
     * @return mixed
     */
    public function goodsWarehousePrice($goods_id, $warehouse_id, $area_id, $model_attr = 0)
    {
        if ($model_attr == 1) {
            $product_price = WarehouseGoods::select('warehouse_price as product_price', 'warehouse_promote_price as product_promote_price')
                ->where('goods_id', $goods_id)
                ->where('region_id', $warehouse_id)
                ->first();
        } elseif ($model_attr == 2) {
            $product_price = WarehouseAreaGoods::select('region_price as product_price', 'region_promote_price as product_promote_price')
                ->where('goods_id', $goods_id)
                ->where('region_id', $area_id)
                ->first();
        } else {
            $product_price = Goods::select('shop_price as product_price', 'promote_price as product_promote_price')
                ->where('goods_id', $goods_id)
                ->first();
        }

        if ($product_price === null) {
            return [];
        }

        return $product_price->toArray();
    }

    /**
     * 验证属性是多选，单选
     * @param $goods_attr_id
     * @return mixed
     */
    public function getGoodsAttrId($goods_attr_id)
    {
        $res = GoodsAttr::from('goods_attr as ga')
            ->select('a.attr_type')
            ->join('attribute as a', 'ga.attr_id', '=', 'a.attr_id')
            ->where('ga.goods_attr_id', $goods_attr_id)
            ->first();
        if ($res === null) {
            return [];
        }

        return $res['attr_type'];
    }

    /**
     * 商品属性图片
     * @param $goods_id
     * @return mixed
     */
    public function getAttrImgFlie($goods_id, $attr_id = 0)
    {
        $attr_id = !empty($attr_id) ? $attr_id['0'] : 0;
        $res = GoodsAttr::select('attr_img_flie')
            ->where('goods_id', $goods_id)
            ->where('goods_attr_id', $attr_id)
            ->first();
        if ($res === null) {
            return [];
        }

        return $res->toArray();
    }


    /**
     * 查询单个运费模板
     */
    public function getGoodsTransport($tid)
    {
        $res = $this->getTransport($tid);
        if (count($res) > 0) {
            return $res[0];
        }
        return [];
    }

    /**
     * 查询运费模板
     */
    public function getTransport($tid)
    {
        $transportList = Cache::get('goods_transport_' . $tid);

        if (empty($transportList)) {
            $transportList = GoodsTransport::where('tid', $tid)
                ->get()
                ->toArray();

            Cache::put('goods_transport_' . $tid, $transportList, 60);
        }

        return $transportList;
    }


    /*
     * 重定义商品价格
     * 商品价格 + 属性价格
     * start
     * 获取商品列表第一组属性价格
     */
    public function getGoodsOneAttrPrice($goods_id)
    {
        $goods = $this->goodsInfo($goods_id);//商品详情

        $goods_product = array(
            'product_price' => $goods['product_price'],
            'product_promote_price' => $goods['product_promote_price']
        );

        $products = array();
        $shop_price = $goods['goods_price'];
        $promote_price = $goods['promote_price'];

        if ($this->shopConfigRepository->getShopConfigByCode('add_shop_price') == 0 && $goods['model_attr'] == 0) {
            $time = gmtime();
            if ($goods_product && $goods_product['product_price'] > 0) {
                $user_rank = $this->userRankRepository->getUserRankByUid();
                if ($user_rank && $user_rank['discount']) {
                    $shop_price = $goods_product['product_price'] * $user_rank['discount'];
                } else {
                    $shop_price = $goods_product['product_price'];
                }
                if ($time >= $goods['promote_start_date'] && $time <= $goods['promote_end_date']) {
                    $promote_price = $goods_product['product_promote_price'];
                }
            }
        }

        return $promote_price > 0 ? $promote_price : $shop_price;

    }

}
