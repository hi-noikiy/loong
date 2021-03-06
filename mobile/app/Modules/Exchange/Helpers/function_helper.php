<?php
/**
 * 获得指定商品属性详情
 */
function get_attr_value($goods_id, $attr_id)
{
    $sql = "select * from " . $GLOBALS['ecs']->table('goods_attr') . " where goods_id='$goods_id' and goods_attr_id='$attr_id'";
    $re = $GLOBALS['db']->getRow($sql);

    if (!empty($re)) {
        return $re;
    } else {
        return false;
    }
}

/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string $children
 * @return  array
 */
function exchange_get_goods($children, $min, $max, $ext, $size, $page, $sort, $order)
{
    $display = $GLOBALS['display'];
    $where = "eg.is_exchange = 1 AND g.is_delete = 0 AND " .
        "($children OR " . get_extension_goods($children) . ')';

    if ($min > 0) {
        $where .= " AND eg.exchange_integral >= $min ";
    }

    if ($max > 0) {
        $where .= " AND eg.exchange_integral <= $max ";
    }

    if ($sort == 'sales_volume') {
        $sort = 'volume';
    }


    $select .= ", (SELECT SUM(og.goods_number) FROM " . $GLOBALS['ecs']->table('order_info') . " AS oi, " . $GLOBALS['ecs']->table('order_goods') . " AS og " .
        " WHERE oi.order_id = og.order_id AND oi.extension_code = 'exchange_goods' AND og.goods_id = g.goods_id " .
        " AND (oi.order_status = '" . OS_CONFIRMED . "' OR  oi.order_status = '" . OS_SPLITED . "' OR oi.order_status = '" . OS_SPLITING_PART . "') " .
        " AND (oi.pay_status  = '" . PS_PAYING . "' OR  oi.pay_status  = '" . PS_PAYED . "')) AS volume ";

    /* 获得商品列表 */
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, eg.exchange_integral, ' .
        'g.goods_type, g.goods_brief, g.goods_thumb , g.goods_img, eg.is_hot ' .
        $select .
        'FROM ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
        "WHERE eg.goods_id = g.goods_id AND eg.review_status = 3 AND $where $ext ORDER BY $sort $order";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    $arr = [];
    foreach ($res as $key => $val) {
        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($val['is_hot'] != 0) {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '') {
            $arr[$key]['watermark_img'] = $watermark_img;
        }

        $arr[$key]['goods_id'] = $val['goods_id'];
        if ($display == 'grid') {
            $arr[$key]['goods_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($val['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $val['goods_name'];
        } else {
            $arr[$key]['goods_name'] = $val['goods_name'];
        }
        $arr[$key]['name'] = $val['goods_name'];
        $arr[$key]['market_price'] = price_format($val['market_price']);
        $arr[$key]['goods_brief'] = $val['goods_brief'];
        $arr[$key]['sales_volume'] = $val['volume'];
        $arr[$key]['goods_number'] = $val['goods_number'];

        $arr[$key]['goods_style_name'] = add_style($val['goods_name'], $val['goods_name_style']);
        $arr[$key]['exchange_integral'] = $val['exchange_integral'];
        $arr[$key]['type'] = $val['goods_type'];
        $arr[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
        $arr[$key]['goods_img'] = get_image_path($val['goods_img']);
        $arr[$key]['url'] = build_uri('exchange_goods', ['gid' => $val['goods_id']], $val['goods_name']);
    }
    return $arr;
}

/**
 * 获得分类下的商品总数
 *
 * @access  public
 * @param   string $cat_id
 * @return  integer
 */
function get_exchange_goods_count($children, $min = 0, $max = 0, $ext = '')
{
    $where = "eg.is_exchange = 1 AND g.is_delete = 0 AND ($children OR " . get_extension_goods($children) . ')';

    if ($min > 0) {
        $where .= " AND eg.exchange_integral >= $min ";
    }

    if ($max > 0) {
        $where .= " AND eg.exchange_integral <= $max ";
    }

    $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg, ' .
        $GLOBALS['ecs']->table('goods') . " AS g WHERE eg.goods_id = g.goods_id AND eg.review_status = 3 AND $where $ext";

    /* 返回商品总数 */
    return $GLOBALS['db']->getOne($sql);
}

/**
 * 获得积分兑换商品的详细信息
 *
 * @access  public
 * @param   integer $goods_id
 * @return  void
 */
function get_exchange_goods_info($goods_id, $warehouse_id = 0, $area_id = 0)
{
    $leftJoin = '';
    $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_goods') . " as wg on g.goods_id = wg.goods_id and wg.region_id = '$warehouse_id' ";
    $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_area_goods') . " as wag on g.goods_id = wag.goods_id and wag.region_id = '$area_id' ";

    $select .= ", (SELECT SUM(og.goods_number) FROM " . $GLOBALS['ecs']->table('order_info') . " AS oi, " . $GLOBALS['ecs']->table('order_goods') . " AS og " .
        " WHERE oi.order_id = og.order_id AND oi.extension_code = 'exchange_goods' AND og.goods_id = g.goods_id " .
        " AND (oi.order_status = '" . OS_CONFIRMED . "' OR  oi.order_status = '" . OS_SPLITED . "' OR oi.order_status = '" . OS_SPLITING_PART . "') " .
        " AND (oi.pay_status  = '" . PS_PAYING . "' OR  oi.pay_status  = '" . PS_PAYED . "')) AS volume ";

    $time = gmtime();
    $sql = 'SELECT g.*, c.measure_unit, b.brand_id, b.brand_name AS goods_brand, eg.exchange_integral, eg.is_exchange, ' .
        "IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) as goods_number " .
        $select .
        'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
        "LEFT JOIN " . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg ON g.goods_id = eg.goods_id ' .
        "LEFT JOIN " . $GLOBALS['ecs']->table('category') . ' AS c ON g.cat_id = c.cat_id ' .
        "LEFT JOIN " . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' .
        $leftJoin .
        "WHERE  g.goods_id = '$goods_id' AND g.is_delete = 0 AND eg.review_status = 3 " .
        'GROUP BY g.goods_id';

    $row = $GLOBALS['db']->getRow($sql);
    //    print_arr($row);
    if ($row !== false) {
        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($row['is_new'] != 0) {
            $watermark_img = "watermark_new";
        } elseif ($row['is_best'] != 0) {
            $watermark_img = "watermark_best";
        } elseif ($row['is_hot'] != 0) {
            $watermark_img = 'watermark_hot';
        }

        if ($watermark_img != '') {
            $row['watermark_img'] = $watermark_img;
        }

        /* 修正重量显示 */
        $row['goods_weight'] = (intval($row['goods_weight']) > 0) ?
            $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] :
            ($row['goods_weight'] * 1000) . $GLOBALS['_LANG']['gram'];

        /* 修正上架时间显示 */
        $row['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);

        /* 修正商品图片 */
        $row['goods_img'] = get_image_path($row['goods_img']);
        $row['goods_thumb'] = get_image_path($row['goods_thumb']);
        $row['goods_number'] = $row['goods_number'];

        $row['marketPrice'] = $row['market_price'];
        $row['market_price'] = price_format($row['market_price']);
        $row['goods_price'] = price_format($row['exchange_integral'] * $GLOBALS['_CFG']['integral_scale'] / 100);
        $row['rz_shopName'] = get_shop_name($row['user_id'], 1); //店铺名称
        $row['store_url'] = build_uri('merchants_store', ['urid' => $row['user_id']], $row['rz_shopName']);

        $row['shopinfo'] = get_shop_name($row['user_id'], 2);
        $row['shopinfo']['brand_thumb'] = get_image_path($row['shopinfo']['brand_thumb']);

        //买家印象
        if ($row['goods_product_tag']) {
            $impression_list = !empty($row['goods_product_tag']) ? explode(',', $row['goods_product_tag']) : '';
            foreach ($impression_list as $kk => $vv) {
                $tag[$kk]['txt'] = $vv;
                //印象数量
                $tag[$kk]['num'] = comment_goodstag_num($row['goods_id'], $vv);
            }
            $row['impression_list'] = $tag;
        }

        $row['collect_count'] = get_collect_goods_user_count($row['goods_id']);

        if ($row['user_id'] == 0) {
            $row['brand'] = get_brand_url($row['brand_id']);
        }

        return $row;
    } else {
        return false;
    }
}

function get_brand_url($brand_id = 0)
{
    $sql = "SELECT brand_id, brand_name, brand_logo FROM " . $GLOBALS['ecs']->table('brand') . " WHERE brand_id = '$brand_id'";
    $res = $GLOBALS['db']->getRow($sql);

    $res['url'] = build_uri('brand', ['bid' => $res['brand_id']], $res['brand_name']);
    $res['brand_logo'] = DATA_DIR . '/brandlogo/' . $res['brand_logo'];

    return $res;
}

/**
 * 查询商品评论
 * @param $id
 * @param string $rank
 * @param int $start
 * @param int $size
 * @return bool
 */
function get_good_comment($id, $rank = null, $hasgoods = 0, $start = 0, $size = 10)
{
    if (empty($id)) {
        return false;
    }
    $where = '';

    $rank = (empty($rank) && $rank !== 0) ? '' : intval($rank);

    if ($rank == 4) {
        //好评
        $where = ' AND  comment_rank in (4, 5)';
    } elseif ($rank == 2) {
        //中评
        $where = ' AND  comment_rank in (2, 3)';
    } elseif ($rank === 0) {
        //差评
        $where = ' AND  comment_rank in (0, 1)';
    } elseif ($rank == 1) {
        //差评
        $where = ' AND  comment_rank in (0, 1)';
    } elseif ($rank == 5) {
        $where = ' AND  comment_rank in (0, 1, 2, 3, 4,5)';
    }

    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '" . $id . "' and comment_type = 0 and status = 1 and parent_id = 0 " . $where . " ORDER BY comment_id DESC LIMIT $start, $size";

    $comment = $GLOBALS['db']->getAll($sql);
    $arr = [];
    if ($comment) {
        $ids = '';
        foreach ($comment as $key => $row) {
            $ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];
            $arr[$row['comment_id']]['id'] = $row['comment_id'];
            $arr[$row['comment_id']]['email'] = $row['email'];
            $arr[$row['comment_id']]['username'] = encrypt_username($row['user_name']);
            $arr[$row['comment_id']]['content'] = str_replace('\r\n', '<br />', $row['content']);
            $arr[$row['comment_id']]['content'] = nl2br(str_replace('\n', '<br />', $arr[$row['comment_id']]['content']));
            $arr[$row['comment_id']]['rank'] = $row['comment_rank'];
            $arr[$row['comment_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            if ($row['order_id'] && $hasgoods) {
                $sql = "SELECT o.goods_id, o.goods_name, o.goods_attr, g.goods_img FROM " . $GLOBALS['ecs']->table('order_goods') . " o LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " g ON o.goods_id = g.goods_id WHERE o.order_id = '" . $row['order_id'] . "' ORDER BY rec_id DESC";
                $goods = $GLOBALS['db']->getAll($sql);
                if ($goods) {
                    foreach ($goods as $k => $v) {
                        $goods[$k]['goods_img'] = get_image_path($v['goods_img']);
                        $goods[$k]['goods_attr'] = str_replace('\r\n', '<br />', $v['goods_attr']);
                    }
                }
                $arr[$row['comment_id']]['goods'] = $goods;
            }
            $sql = "SELECT img_thumb FROM {pre}comment_img WHERE comment_id = " . $row['comment_id'];
            $comment_thumb = $GLOBALS['db']->getCol($sql);
            if (count($comment_thumb) > 0) {
                foreach ($comment_thumb as $k => $v) {
                    $comment_thumb[$k] = get_image_path($v);
                }
                $arr[$row['comment_id']]['thumb'] = $comment_thumb;
            } else {
                $arr[$row['comment_id']]['thumb'] = 0;
            }
        }

        /* 取得已有回复的评论 */
        if ($ids) {
            $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') . " WHERE parent_id IN( $ids )";
            $res = $GLOBALS['db']->query($sql);
            foreach ($res as $row) {
                $arr[$row['parent_id']]['re_content'] = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
                $arr[$row['parent_id']]['re_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
                $arr[$row['parent_id']]['re_email'] = $row['email'];
                $arr[$row['parent_id']]['re_username'] = $row['user_name'];
            }
        }
        $arr = array_values($arr);
    }
    return $arr;
}

/**
 * 清空购物车
 * @param   int $type 类型：默认普通商品
 */
function clear_cart($type = CART_GENERAL_GOODS, $cart_value = '')
{
    //ecmoban模板堂 --zhuo start
    if (!empty($_SESSION['user_id'])) {
        $sess_id = " user_id = '" . $_SESSION['user_id'] . "' ";
    } else {
        $sess_id = " session_id = '" . real_cart_mac_ip() . "' ";
    }

    $goodsIn = '';
    if (!empty($cart_value)) {
        $goodsIn = " and rec_id in($cart_value)";
    }
    //ecmoban模板堂 --zhuo end

    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
        " WHERE " . $sess_id . " AND rec_type = '$type'" . $goodsIn;
    $GLOBALS['db']->query($sql);

    if (!empty($_SESSION['user_id'])) {
        $sess_id = " user_id = '" . $_SESSION['user_id'] . "' ";
    } else {
        $sess_id = " user_id = '" . real_cart_mac_ip() . "' ";
    }

    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart_user_info') . " WHERE " . $sess_id;
    $GLOBALS['db']->query($sql);
}
