<?php

//多维数组转为一维数组
function arr_foreach($arr)
{
    $tmp = [];
    if (!is_array($arr)) {
        return false;
    }
    foreach ($arr as $val) {
        if (is_array($val)) {
            $tmp = array_merge($tmp, arr_foreach($val));
        } else {
            $tmp[] = $val;
        }
    }
    return $tmp;
}

/**
 * 获得指定分类同级的所有分类以及该分类下的子分类
 *
 * @access  public
 * @param   integer $cat_id 分类编号
 * @return  array
 */
function get_categories_tree_pro($cat_id = 0, $type = '')
{
    if ($cat_id > 0) {
        $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$cat_id' LIMIT 1";
        $parent_id = $GLOBALS['db']->getOne($sql);
    } else {
        $parent_id = 0;
    }

    /*
     判断当前分类中全是是否是底级分类，
     如果是取出底级分类上级分类，
     如果不是取当前分类及其下的子分类
    */
    //$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('category') . " WHERE parent_id = '$parent_id' AND is_show = 1 ";
    $sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE parent_id = '$parent_id' AND is_show = 1 LIMIT 1 ";
    if ($GLOBALS['db']->getOne($sql) || $parent_id == 0) {
        /* 获取当前分类及其子分类 */
        $sql = 'SELECT cat_id,cat_name ,parent_id,is_show, category_links ' .
            'FROM ' . $GLOBALS['ecs']->table('category') .
            "WHERE parent_id = '$parent_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";

        $res = $GLOBALS['db']->getAll($sql);

        foreach ($res as $row) {
            $cat_id = $row['cat_id'];

            if ($row['parent_id'] == 0) {
                $cat_name = '';
                for ($i = 1; $i <= $GLOBALS['_CFG']['auction_ad']; $i++) {
                    $cat_name .= "'cat_tree_" . $row['cat_id'] . "_" . $i . "',";
                }

                $cat_name = substr($cat_name, 0, -1);

                $cat_arr[$row['cat_id']]['ad_position'] = get_ad_posti_child($cat_name);
            }

            $children = get_children($cat_id);
            $cat = $GLOBALS['db']->getRow('SELECT cat_name, keywords, cat_desc, style, grade, filter_attr, parent_id FROM ' . $GLOBALS['ecs']->table('category') .
                " WHERE cat_id = '$cat_id' LIMIT 1");

            /* 获取分类下文章 */
            $sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time, a.file_url, a.open_type FROM ' . $GLOBALS['ecs']->table('article_cat') . ' AS ac RIGHT JOIN ' . $GLOBALS['ecs']->table('article') . " AS a ON a.cat_id=ac.cat_id AND a.is_open = 1 WHERE ac.cat_name='$row[cat_name]' ORDER BY a.article_type,a.article_id DESC LIMIT 4 ";

            $articles = $GLOBALS['db']->getAll($sql);

            foreach ($articles as $key => $val) {
                $articles[$key]['url'] = $val['open_type'] != 1 ?
                    build_uri('article', ['aid' => $val['article_id']], $val['title']) : trim($val['file_url']);
            }
            /* 平台品牌筛选 */
            $sql = "SELECT b.brand_id, b.brand_name, b.brand_logo, COUNT(*) AS goods_num " . $brand_select .
                "FROM " . $GLOBALS['ecs']->table('brand') . "AS b " .
                " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 $brand_tag_where " .
                " LEFT JOIN " . $GLOBALS['ecs']->table('goods_cat') . " AS gc ON g.goods_id = gc.goods_id " .
                " WHERE $children OR " . 'gc.cat_id ' . db_create_in(array_unique(array_merge([$cat_id], array_keys(cat_list($cat_id, 0, 0))))) . " AND b.is_show = 1 " .
                "GROUP BY b.brand_id HAVING goods_num > 0 $where_having ORDER BY b.sort_order, b.brand_id ASC";

            $brands = $GLOBALS['db']->getAll($sql);

            foreach ($brands as $key => $val) {
                $temp_key = $key;
                $brands[$temp_key]['brand_name'] = $val['brand_name'];
                $brands[$temp_key]['url'] = build_uri('category', ['cid' => $cat_id, 'bid' => $val['brand_id'], 'price_min' => $price_min, 'price_max' => $price_max, 'filter_attr' => $filter_attr_str], $cat['cat_name']);

                /* 判断品牌是否被选中 */
                if ($brand == $brands[$key]['brand_id']) {
                    $brands[$temp_key]['selected'] = 1;
                } else {
                    $brands[$temp_key]['selected'] = 0;
                }
            }

            //unset($brands[0]);
            $cat_arr[$row['cat_id']]['brands'] = $brands;
            $cat_arr[$row['cat_id']]['articles'] = $articles;

            if ($row['is_show']) {
                //by guan start
                if ($row['parent_id'] == 0 && !empty($row['category_links'])) {
                    if (empty($type)) {
                        $cat_name_arr = explode('、', $row['cat_name']);
                        if (!empty($cat_name_arr)) {
                            $category_links_arr = explode("\r\n", $row['category_links']);
                        }

                        $cat_name_str = "";
                        foreach ($cat_name_arr as $cat_name_key => $cat_name_val) {
                            $link_str = $category_links_arr[$cat_name_key];

                            $cat_name_str .= '<a href="' . $link_str . '" target="_blank">' . $cat_name_val;

                            if (count($cat_name_arr) == ($cat_name_key + 1)) {
                                $cat_name_str .= '</a>';
                            } else {
                                $cat_name_str .= '</a>、';
                            }
                        }

                        $cat_arr[$row['cat_id']]['name'] = $cat_name_str;
                        $cat_arr[$row['cat_id']]['category_link'] = 1;
                        $cat_arr[$row['cat_id']]['oldname'] = $row['cat_name'];//by EcMoban-weidong   保留原生元素
                    } else {
                        $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
                        $cat_arr[$row['cat_id']]['oldname'] = $row['cat_name'];//by EcMoban-weidong   保留原生元素
                    }
                } else {
                    $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
                }
                //by guan end

                $cat_arr[$row['cat_id']]['id'] = $row['cat_id'];

                $cat_arr[$row['cat_id']]['url'] = build_uri('category', ['cid' => $row['cat_id']], $row['cat_name']);

                if (isset($row['cat_id']) != null) {
                    $cat_arr[$row['cat_id']]['cat_id'] = get_child_tree_pro($row['cat_id']);
                }
            }
        }
    }


    if (isset($cat_arr)) {
        return $cat_arr;
    }
}

/**
 * 树形分类列表
 * $table  平台分类 category, 商家分类 merchants_category
 */
function get_child_tree_pro($tree_id = 0, $level = 0, $table = 'category', $getrid = 0, $user_id = 0)
{
    $where = '';
    $select = '';
    if ($table == 'merchants_category') {
        $select = ", user_id ";

        if ($user_id) {
            $where .= " AND user_id = '$user_id'";
        }
    }

    $three_arr = [];
    $sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table($table) . " WHERE parent_id = '$tree_id' $where AND is_show = 1 LIMIT 1";
    if ($GLOBALS['db']->getOne($sql) || $tree_id == 0) {
        $child_sql = 'SELECT cat_id, cat_name, parent_id, is_show ' . $select .
            'FROM ' . $GLOBALS['ecs']->table($table) .
            "WHERE parent_id = '$tree_id' AND is_show = 1 $where ORDER BY sort_order ASC, cat_id ASC";
        $res = $GLOBALS['db']->getAll($child_sql);

        if ($res) {
            foreach ($res as $row) {
                $three_arr[$row['cat_id']]['id'] = $row['cat_id'];

                if ($getrid == 0) {
                    $three_arr[$row['cat_id']]['name'] = htmlspecialchars(addslashes(str_replace("\r\n", "", $row['cat_name'])), ENT_QUOTES);//特殊字符处理

                    if ($table == 'merchants_category') {
                        $three_arr[$row['cat_id']]['url'] = build_uri('merchants_store', ['cid' => $row['cat_id'], 'urid' => $row['user_id']], $row['cat_name']);
                    } else {
                        $three_arr[$row['cat_id']]['url'] = build_uri('category', ['cid' => $row['cat_id']], $row['cat_name']);
                    }

                    if ($table == 'merchants_category') {
                        $three_arr[$row['cat_id']]['ru_id'] = $row['user_id'];
                        $three_arr[$row['cat_id']]['seller_name'] = get_shop_name($row['user_id'], 1);
                    }

                    if ($row['parent_id'] != 0) {
                        $three_arr[$row['cat_id']]['level'] = $level + 1;
                    } else {
                        $three_arr[$row['cat_id']]['level'] = $level;
                    }

                    $three_arr[$row['cat_id']]['select'] = str_repeat('&nbsp;', $three_arr[$row['cat_id']]['level'] * 4);
                }

                if (isset($row['cat_id']) != null) {
                    if ($row['parent_id'] != 0) {
                        $three_arr[$row['cat_id']]['cat_id'] = get_child_tree_pro($row['cat_id'], $level + 1, $table, $getrid);
                    } else {
                        $three_arr[$row['cat_id']]['cat_id'] = get_child_tree_pro($row['cat_id'], $level, $table, $getrid);
                    }
                }

                if (!$three_arr[$row['cat_id']]['cat_id'] && $getrid) {
                    unset($three_arr[$row['cat_id']]['cat_id']);
                }
            }
        }
    }

    return $three_arr;
}

/**
 * 获取折扣和节省
 * @param $goods_id
 * @param int $warehouse_id
 * @param int $area_id
 * @return mixed
 */
function get_discount($goods_id, $warehouse_id = 0, $area_id = 0)
{
    $leftJoin = " left join " . $GLOBALS['ecs']->table('warehouse_goods') . " as wg on g.goods_id = wg.goods_id and wg.region_id = '$warehouse_id' ";
    $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_area_goods') . " as wag on g.goods_id = wag.goods_id and wag.region_id = '$area_id' ";

    $sql = 'SELECT g.market_price, g.promote_start_date, g.promote_end_date, ' .
        "IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * '$_SESSION[discount]'), g.shop_price * '$_SESSION[discount]')  AS shop_price, " .
        "IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price" .
        ' FROM ' . $GLOBALS['ecs']->table('goods') . " AS g" .
        $leftJoin .
        "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " . "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
        " WHERE g.goods_id = $goods_id ";

    $row = $GLOBALS['db']->getRow($sql);

    if ($row['promote_price'] > 0) {
        $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
    } else {
        $promote_price = 0;
    }

    $price = $row['market_price']; //原价
    if ($promote_price > 0) { //如果促销价大于0则现价为促销价
        $nowprice = $row['promote_price']; //现价
    } else { //否则为本店价
        $nowprice = $row['shop_price']; //现价
    }

    $jiesheng = $price - $nowprice; //节省金额

    $arr['jiesheng'] = $jiesheng;

    //$discount折扣计算
    if ($nowprice > 0) {
        $arr['discount'] = round(($nowprice / $price) * 10, 1);
    } else {
        $arr['discount'] = 0;
    }

    if ($arr['discount'] <= 0) {
        $arr['discount'] = 0;
    }

    return $arr;
}

/**
 * 获得指定的商品属性
 *
 * @access      public
 * @param       array $arr 规格、属性ID数组
 * @param       type $type 设置返回结果类型：pice，显示价格，默认；no，不显示价格
 *
 * @return      string
 */
function get_goods_attr_info_new($arr, $type = 'pice', $warehouse_id = 0, $area_id = 0)
{
    $attr = '';

    if (!empty($arr)) {
        $fmt = "%s:%s[%s] \n";

        //ecmoban模板堂 --zhuo satrt
        $leftJoin = '';

        $leftJoin .= " left join " . $GLOBALS['ecs']->table('goods') . " as g on g.goods_id = ga.goods_id";
        $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_attr') . " as wap on ga.goods_id = wap.goods_id and wap.warehouse_id = '$warehouse_id' and ga.goods_attr_id = wap.goods_attr_id ";
        $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_area_attr') . " as wa on ga.goods_id = wa.goods_id and wa.area_id = '$area_id' and ga.goods_attr_id = wa.goods_attr_id ";
        //ecmoban模板堂 --zhuo end

        $sql = "SELECT ga.goods_attr_id, a.attr_name, ga.attr_value, " .
            " IF(g.model_attr < 1, ga.attr_price, IF(g.model_attr < 2, wap.attr_price, wa.attr_price)) as attr_price " .
            "FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS ga " .
            $leftJoin .
            " left join " . $GLOBALS['ecs']->table('attribute') . " AS a " . "on a.attr_id = ga.attr_id " .
            "WHERE " . db_create_in($arr, 'ga.goods_attr_id');

        $res = $GLOBALS['db']->query($sql);

        foreach ($res as $row) {
            $attr_price = round(floatval($row['attr_price']), 2);

            $attr_price = price_format($attr_price, false); //ecmoban模板堂 --zhuo

            $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], $attr_price);
        }

        $attr = str_replace('[0]', '', $attr);
    }

    return $attr;
}

/*评论百分比*/
function comment_percent($goods_id)
{
    $sql = 'SELECT COUNT(*) AS haoping FROM ' . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '$goods_id' AND comment_type=0 AND status = 1 AND parent_id = 0 AND (comment_rank = 4 OR comment_rank = 5)";
    $haoping_count = $GLOBALS['db']->getOne($sql);

    $sql = 'SELECT COUNT(*) AS zhongping FROM ' . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '$goods_id' AND comment_type=0 AND status = 1 AND parent_id = 0 AND (comment_rank = 2 OR comment_rank = 3)";
    $zhongping_count = $GLOBALS['db']->getOne($sql);

    $sql = 'SELECT COUNT(*) AS chaping FROM ' . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '$goods_id' AND comment_type=0 AND status = 1 AND parent_id = 0 AND comment_rank = 1";
    $chaping_count = $GLOBALS['db']->getOne($sql);

    $sql = 'SELECT COUNT(*) AS comment_count FROM ' . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '$goods_id' AND comment_type=0 AND status = 1 AND parent_id = 0";
    $comment_count = $GLOBALS['db']->getOne($sql);

    if ($comment_count == 0) {
        $arr['haoping_percent'] = 100;
        $arr['zhongping_percent'] = 0;
        $arr['chaping_percent'] = 0;
    } else {
        $arr['haoping_percent'] = substr(number_format(($haoping_count / $comment_count) * 100, 2, '.', ''), 0, -1);
        $arr['zhongping_percent'] = substr(number_format(($zhongping_count / $comment_count) * 100, 2, '.', ''), 0, -1);
        $arr['chaping_percent'] = substr(number_format(($chaping_count / $comment_count) * 100, 2, '.', ''), 0, -1);
    }

    foreach ($arr as $key => $val) {
        if ($val == 0.0) {
            $arr[$key] = 0;
        }
    }

    return $arr;
}

//ecmoban模板堂 --zhuo start
function get_month_day_start_end_goods($group_buy_id, $first_month_day = 0, $last_month_day = 0)
{
    $where = '';
    $where .= "AND (order_status = '" . OS_CONFIRMED . "' OR order_status = '" . OS_UNCONFIRMED . "') AND o.extension_code = 'group_buy' ";

    $sql = "select gac.*, g.*, count(gac.act_id) as valid_goods, SUM(og.goods_number) AS v_goods_number from " . $GLOBALS['ecs']->table('goods_activity') . " as gac, " .
        $GLOBALS['ecs']->table('goods') . " as g, " .
        $GLOBALS['ecs']->table('order_goods') . " as og,  " .
        $GLOBALS['ecs']->table('order_info') . " as o " .
        "where gac.goods_id = og.goods_id and og.order_id = o.order_id and gac.goods_id = g.goods_id " .
        " and o.add_time >= " . $first_month_day . " and o.add_time <= " . $last_month_day . " AND gac.act_id <> '$group_buy_id' AND gac.review_status = 3 $where group by gac.act_id order by v_goods_number desc limit 0,10";
    $res = $GLOBALS['db']->getAll($sql);

    $arr = [];
    foreach ($res as $key => $row) {
        $arr[] = $row;

        $ext_info = unserialize($row['ext_info']);
        $arr[$key]['ext_info'] = $ext_info;

        // 处理价格阶梯
        $price_ladder = $arr[$key]['ext_info']['price_ladder'];
        if (!is_array($price_ladder) || empty($price_ladder)) {
            $price_ladder = [['amount' => 0, 'price' => 0]];
        } else {
            foreach ($price_ladder as $k => $amount_price) {
                $price_ladder[$k]['formated_price'] = price_format($amount_price['price'], false);
            }
        }
        $arr[$key]['price_ladder'] = $price_ladder;

        // 计算当前价
        $cur_price = $price_ladder[0]['price']; // 初始化

        foreach ($price_ladder as $amount_price) {
            if ($cur_amount >= $amount_price['amount']) {
                $cur_price = $amount_price['price'];
            } else {
                break;
            }
        }

        $arr[$key]['cur_price'] = price_format($cur_price, false); //现价

        /*团购节省和折扣计算 by ecmoban start*/
        $arr[$key]['market_price'] = price_format($row['market_price'], false); //原价
        $price = $row['market_price']; //原价
        $nowprice = $cur_price; //现价
        $arr[$key]['jiesheng'] = price_format($price - $nowprice, false); //节省金额
        if ($nowprice > 0) {
            $arr[$key]['zhekou'] = round(10 / ($price / $nowprice), 1);
        } else {
            $arr[$key]['zhekou'] = 0;
        }
        /*团购节省和折扣计算 by ecmoban end*/

        $arr[$key]['valid_goods'] = $row['v_goods_number'];
    }

    return $arr;
}


/*
*ecmoban模板堂 --zhuo
*获得商品评论总条数
*@param $goods_id
*return count;
*/
function ments_count_all($goods_id, $type = 'comment_rank', $count_type = 0)
{
    $count = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '$goods_id' AND status = 1 AND parent_id = 0 and " . $type . " in(1,2,3,4,5)";
    $res = $GLOBALS['db']->getOne($count);

    if ($res == 0) {
        if ($count_type == 0) {
            return $res = 1;
        } else {
            return $res = 0;
        }
    } else {
        return $res;
    }
}

/*
*ecmoban模板堂 --zhuo
*获得商品评论-$num-颗星总条数
*@param $goods_id
*return count;
*/
function ments_count_rank_num($goods_id, $num, $type = 'comment_rank')
{
    $count = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '$goods_id' AND status = 1 AND parent_id = 0 and " . $type . " = '$num'";
    $res = $GLOBALS['db']->getOne($count);

    return $res;
}

/*
*ecmoban模板堂 --zhuo
*获得商品评论显示星星
*@param $goods_id
*return count;
*/
function get_conments_stars($all = null, $one = null, $two = null, $three = null, $four = null, $five = null)
{
    $num = 5;
    $one_num = 1;
    $two_num = 2;
    $three_num = 3;
    $four_num = 4;
    $five_num = 5;

    $allNmu = $all * 5;                                            //总星星数
    $oneAll = $one * $one_num;                                        //1颗总星星数
    $twoAll = $two * $two_num;                                        //2颗总星星数
    $threeAll = $three * $three_num;                                //3颗总星星数
    $fourAll = $four * $four_num;                                    //4颗总星星数
    $fiveAll = $five * $five_num;                                    //5颗总星星数
    $allStars = $oneAll + $twoAll + $threeAll + $fourAll + $fiveAll;        //显示总星星数

    $badReview = $one / $all;                                        //差评条数
    $middleReview = ($two + $three) / $all;                            //中评条数
    $goodReview = ($four + $five) / $all;                                //好评条数

    $badmen = $one;                                                //差评人数
    $middlemen = $two + $three;                                        //中评人数
    $goodmen = $four + $five;                                        //好评人数
    $allmen = $one + $two + $three + $four + $five;                        //全部评分人数

    $percentage = sprintf("%.2f", ($allStars / $allNmu * 100));

    $arr = [
        'score' => sprintf("%.2f", (round($percentage / 20, 2))),                    //分数
        'badReview' => round($badReview, 2) * 100,                //差评百分比
        'middlReview' => round($middleReview, 2) * 100,            //中评百分比
        'goodReview' => round($goodReview, 2) * 100,                //好评百分比
        'allReview' => $percentage,                            //总体百分比
        'badmen' => $badmen,                                    //差评人数
        'middlemen' => $middlemen,                                //中评人数
        'goodmen' => $goodmen,                                    //好评人数
        'allmen' => $allmen,                                    //全部评论人数
    ];

    if ($percentage >= 1 && $percentage < 40) {               //1颗星
        $arr['stars'] = 1;
    } elseif ($percentage >= 40 && $percentage < 60) {        //2颗星
        $arr['stars'] = 2;
    } elseif ($percentage >= 60 && $percentage < 80) {        //3颗星
        $arr['stars'] = 3;
    } elseif ($percentage >= 80 && $percentage < 100) {        //4颗星
        $arr['stars'] = 4;
    } elseif ($percentage == 100) {
        $arr['score'] = 5;
        $arr['stars'] = 5;
        $arr['badReview'] = 0;                                //差评百分比
        $arr['middlReview'] = 0;                                //中评百分比
        $arr['goodReview'] = 100;                                //好评百分比
        $arr['allReview'] = 100;                            //总体百分比
        return $arr;
    } else { //默认状态 --没有评论时
        $arr = [
            'score' => 5,                                            //分数
            'stars' => 5,                                            //星数
            'badReview' => 0,                                        //差评百分比
            'middlReview' => 0,                                    //中评百分比
            'goodReview' => 100,                                    //好评百分比
            'allReview' => 100,                                    //总体百分比
            'allmen' => 0,                                        //全部评论人数
            'badmen' => 0,                                        //差评人数
            'middlemen' => 0,                                        //中评人数
            'goodmen' => 0,                                        //好评人数
        ];
    }
    $review = $arr['badReview'] + $arr['middlReview'] + $arr['goodReview'];
    //计算判断是否超出100值，如有超出则按最大值减去超出值
    if ($review > 100) {
        $review = $review - 100;
        $maxReview = max($arr['badReview'], $arr['middlReview'], $arr['goodReview']);

        if ($maxReview == $arr['badReview']) {
            $arr['badReview'] = $arr['badReview'] - $review;
        } elseif ($maxReview == $arr['middlReview']) {
            $arr['middlReview'] = $arr['middlReview'] - $review;
        } elseif ($maxReview == $arr['goodReview']) {
            $arr['goodReview'] = $arr['goodReview'] - $review;
        }
    }

    $arr['left'] = $arr['stars'] * 18;

    return $arr;
}

/*
 * 商品评论百分比，及数量统计
 */
function get_comments_percent($goods_id)
{
    $arr = [
        'score' => 5,    //分数
        'stars' => 5,    //星数
        'badReview' => 0,    //差评百分比
        'middlReview' => 0,    //中评百分比
        'goodReview' => 100,    //好评百分比
        'allReview' => 100,    //总体百分比
        'allmen' => 0,    //全部评论人数
        'badmen' => 0,    //差评人数
        'middlemen' => 0,    //中评人数
        'goodmen' => 0,    //好评人数
    ];

    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '$goods_id' AND status = 1 AND parent_id = 0";
    $arr['allmen'] = $GLOBALS['db']->getOne($sql);

    if ($arr['allmen'] == 0) {
        return $arr;
    } else {
        $mc_one = ments_count_rank_num($goods_id, 1);        //一颗星
        $mc_two = ments_count_rank_num($goods_id, 2);        //两颗星
        $mc_three = ments_count_rank_num($goods_id, 3);    //三颗星
        $mc_four = ments_count_rank_num($goods_id, 4);        //四颗星
        $mc_five = ments_count_rank_num($goods_id, 5);        //五颗星

        $arr['goodmen'] = $mc_four + $mc_five;
        $arr['middlemen'] = $mc_two + $mc_three;
        $arr['badmen'] = $mc_one;

        $arr['goodReview'] = round(($arr['goodmen'] / $arr['allmen']) * 100, 1);
        $arr['middlReview'] = round(($arr['middlemen'] / $arr['allmen']) * 100, 1);
        $arr['badReview'] = round(($arr['badmen'] / $arr['allmen']) * 100, 1);

        return $arr;
    }
}

//获取单个商品所有评分类型之和的分数、百分比以及星数计算
function get_goods_cmt_typeAll($goods_id, $fieldType = [])
{

    //描述符合评分
    $rank_mc_all = ments_count_all($goods_id, $fieldType[0]);       //总条数
    $rank_mc_one = ments_count_rank_num($goods_id, 1, $fieldType[0]);        //一颗星
    $rank_mc_two = ments_count_rank_num($goods_id, 2, $fieldType[0]);        //两颗星
    $rank_mc_three = ments_count_rank_num($goods_id, 3, $fieldType[0]);    //三颗星
    $rank_mc_four = ments_count_rank_num($goods_id, 4, $fieldType[0]);        //四颗星
    $rank_mc_five = ments_count_rank_num($goods_id, 5, $fieldType[0]);        //五颗星

    //服务态度评分
    $server_mc_all = ments_count_all($goods_id, $fieldType[1]);       //总条数
    $server_mc_one = ments_count_rank_num($goods_id, 1, $fieldType[1]);        //一颗星
    $server_mc_two = ments_count_rank_num($goods_id, 2, $fieldType[1]);        //两颗星
    $server_mc_three = ments_count_rank_num($goods_id, 3, $fieldType[1]);    //三颗星
    $server_mc_four = ments_count_rank_num($goods_id, 4, $fieldType[1]);        //四颗星
    $server_mc_five = ments_count_rank_num($goods_id, 5, $fieldType[1]);        //五颗星

    //发货速度评分
    $delivery_mc_all = ments_count_all($goods_id, $fieldType[2]);       //总条数
    $delivery_mc_one = ments_count_rank_num($goods_id, 1, $fieldType[2]);        //一颗星
    $delivery_mc_two = ments_count_rank_num($goods_id, 2, $fieldType[2]);        //两颗星
    $delivery_mc_three = ments_count_rank_num($goods_id, 3, $fieldType[2]);    //三颗星
    $delivery_mc_four = ments_count_rank_num($goods_id, 4, $fieldType[2]);        //四颗星
    $delivery_mc_five = ments_count_rank_num($goods_id, 5, $fieldType[2]);        //五颗星

    $mc_all = $rank_mc_all + $server_mc_all + $delivery_mc_all;
    $mc_one = $rank_mc_one + $server_mc_one + $delivery_mc_one;
    $mc_two = $rank_mc_two + $server_mc_two + $delivery_mc_two;
    $mc_three = $rank_mc_three + $server_mc_three + $delivery_mc_three;
    $mc_four = $rank_mc_four + $server_mc_four + $delivery_mc_four;
    $mc_five = $rank_mc_five + $server_mc_five + $delivery_mc_five;

    $zconments = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);

    $zconments['allmen'] = $zconments['allmen'] / 3;

    return $zconments;
}

//获取商家所有商品评分类型汇总
function get_merchants_goods_comment($ru_id)
{
    $seller_cmt = dirname(ROOT_PATH) . '/data/sc_file/' . 'seller_comment_' . $ru_id . '.php';

    if (file_exists($seller_cmt)) {
        include_once($seller_cmt);
        if (empty($data)) {
            $data = [];
        }
        $arr = $data['seller_comment'];
    } else {
        $sql = "select shop_id, user_id from " . $GLOBALS['ecs']->table('merchants_shop_information') . " WHERE user_id = '$ru_id' LIMIT 1";
        $res = $GLOBALS['db']->getAll($sql);

        $arr = [];
        foreach ($res as $key => $row) {
            $arr[$key] = $row;

            //商品评分
            $arr[$key]['mc_all_Rank'] = seller_ments_count_all($row['user_id'], 'desc_rank');       //总条数
            $arr[$key]['mc_one_Rank'] = seller_ments_count_rank_num($row['user_id'], 1, 'desc_rank');  //一颗星
            $arr[$key]['mc_two_Rank'] = seller_ments_count_rank_num($row['user_id'], 2, 'desc_rank');     //两颗星
            $arr[$key]['mc_three_Rank'] = seller_ments_count_rank_num($row['user_id'], 3, 'desc_rank');    //三颗星
            $arr[$key]['mc_four_Rank'] = seller_ments_count_rank_num($row['user_id'], 4, 'desc_rank');  //四颗星
            $arr[$key]['mc_five_Rank'] = seller_ments_count_rank_num($row['user_id'], 5, 'desc_rank');  //五颗星
            //服务评分
            $arr[$key]['mc_all_Server'] = seller_ments_count_all($row['user_id'], 'service_rank');       //总条数
            $arr[$key]['mc_one_Server'] = seller_ments_count_rank_num($row['user_id'], 1, 'service_rank');  //一颗星
            $arr[$key]['mc_two_Server'] = seller_ments_count_rank_num($row['user_id'], 2, 'service_rank');     //两颗星
            $arr[$key]['mc_three_Server'] = seller_ments_count_rank_num($row['user_id'], 3, 'service_rank');    //三颗星
            $arr[$key]['mc_four_Server'] = seller_ments_count_rank_num($row['user_id'], 4, 'service_rank');  //四颗星
            $arr[$key]['mc_five_Server'] = seller_ments_count_rank_num($row['user_id'], 5, 'service_rank');  //五颗星
            //时效评分
            $arr[$key]['mc_all_Delivery'] = seller_ments_count_all($row['user_id'], 'delivery_rank');       //总条数
            $arr[$key]['mc_one_Delivery'] = seller_ments_count_rank_num($row['user_id'], 1, 'delivery_rank');  //一颗星
            $arr[$key]['mc_two_Delivery'] = seller_ments_count_rank_num($row['user_id'], 2, 'delivery_rank');     //两颗星
            $arr[$key]['mc_three_Delivery'] = seller_ments_count_rank_num($row['user_id'], 3, 'delivery_rank');    //三颗星
            $arr[$key]['mc_four_Delivery'] = seller_ments_count_rank_num($row['user_id'], 4, 'delivery_rank');  //四颗星
            $arr[$key]['mc_five_Delivery'] = seller_ments_count_rank_num($row['user_id'], 5, 'delivery_rank');  //五颗星

            $sql = "SELECT sid FROM " . $GLOBALS['ecs']->table('comment_seller') . " WHERE ru_id = '" . $row['user_id'] . "' LIMIT 1";
            $sid = $GLOBALS['db']->getOne($sql);

            if ($sid > 0) {

                //商品评分
                @$arr['commentRank']['mc_all'] += $arr[$key]['mc_all_Rank'];
                @$arr['commentRank']['mc_one'] += $arr[$key]['mc_one_Rank'];
                @$arr['commentRank']['mc_two'] += $arr[$key]['mc_two_Rank'];
                @$arr['commentRank']['mc_three'] += $arr[$key]['mc_three_Rank'];
                @$arr['commentRank']['mc_four'] += $arr[$key]['mc_four_Rank'];
                @$arr['commentRank']['mc_five'] += $arr[$key]['mc_five_Rank'];

                //服务评分
                @$arr['commentServer']['mc_all'] += $arr[$key]['mc_all_Server'];
                @$arr['commentServer']['mc_one'] += $arr[$key]['mc_one_Server'];
                @$arr['commentServer']['mc_two'] += $arr[$key]['mc_two_Server'];
                @$arr['commentServer']['mc_three'] += $arr[$key]['mc_three_Server'];
                @$arr['commentServer']['mc_four'] += $arr[$key]['mc_four_Server'];
                @$arr['commentServer']['mc_five'] += $arr[$key]['mc_five_Server'];

                //时效评分
                @$arr['commentDelivery']['mc_all'] += $arr[$key]['mc_all_Delivery'];
                @$arr['commentDelivery']['mc_one'] += $arr[$key]['mc_one_Delivery'];
                @$arr['commentDelivery']['mc_two'] += $arr[$key]['mc_two_Delivery'];
                @$arr['commentDelivery']['mc_three'] += $arr[$key]['mc_three_Delivery'];
                @$arr['commentDelivery']['mc_four'] += $arr[$key]['mc_four_Delivery'];
                @$arr['commentDelivery']['mc_five'] += $arr[$key]['mc_five_Delivery'];
            }
        }

        @$arr['cmt']['commentRank']['zconments'] = get_conments_stars($arr['commentRank']['mc_all'], $arr['commentRank']['mc_one'], $arr['commentRank']['mc_two'], $arr['commentRank']['mc_three'], $arr['commentRank']['mc_four'], $arr['commentRank']['mc_five'], 'goods');
        @$arr['cmt']['commentServer']['zconments'] = get_conments_stars($arr['commentServer']['mc_all'], $arr['commentServer']['mc_one'], $arr['commentServer']['mc_two'], $arr['commentServer']['mc_three'], $arr['commentServer']['mc_four'], $arr['commentServer']['mc_five'], 'service');
        @$arr['cmt']['commentDelivery']['zconments'] = get_conments_stars($arr['commentDelivery']['mc_all'], $arr['commentDelivery']['mc_one'], $arr['commentDelivery']['mc_two'], $arr['commentDelivery']['mc_three'], $arr['commentDelivery']['mc_four'], $arr['commentDelivery']['mc_five'], 'shipping');

        @$arr['cmt']['all_zconments']['score'] = sprintf("%.2f", ($arr['cmt']['commentRank']['zconments']['score'] + $arr['cmt']['commentServer']['zconments']['score'] + $arr['cmt']['commentDelivery']['zconments']['score']) / 3);
        @$arr['cmt']['all_zconments']['allReview'] = round((($arr['cmt']['commentRank']['zconments']['allReview'] + $arr['cmt']['commentServer']['zconments']['allReview'] + $arr['cmt']['commentDelivery']['zconments']['allReview']) / 3), 2);
        @$arr['cmt']['all_zconments']['position'] = 100 - $arr['cmt']['all_zconments']['allReview'] - 3;
    }

    return $arr;
}

/*
 * 获得订单商品评论总条数
 * @param $goods_id
 * return count;
 */

function seller_ments_count_all($ru_id, $type)
{
    $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment_seller') . " WHERE ru_id = '$ru_id' AND " . $type . " IN(1,2,3,4,5)";
    $res = $GLOBALS['db']->getOne($sql);

    return $res;
}

/*
 * 获得商品评论-$num-颗星总条数
 * @param $goods_id
 * return count;
 */

function seller_ments_count_rank_num($ru_id, $num, $type)
{
    $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment_seller') . " WHERE ru_id = '$ru_id' AND " . $type . " = '$num'";
    $res = $GLOBALS['db']->getOne($sql);

    return $res;
}

/*获取商家等级*/
function get_seller_grade($ru_id = 0, $type = 0)
{
    if ($type) {
        $ru_id = implode(',', $ru_id);
        $where = "g.ru_id IN($ru_id)";
    } else {
        $where = "g.ru_id = '$ru_id' LIMIT 1";
    }

    $sql = "SELECT s.grade_name, s.grade_img, s.grade_introduce, s.white_bar, g.grade_id, g.add_time, g.year_num, g.amount FROM " . $GLOBALS['ecs']->table('seller_grade') . " AS s " . "LEFT JOIN " . $GLOBALS['ecs']->table('merchants_grade') . " AS g ON s.id = g.grade_id WHERE $where";

    if ($type) {
        $str = 1;
        $res = $GLOBALS['db']->getAll($sql);
        foreach ($res as $k => $v) {
            if ($v['white_bar'] == 0) {
                $str = 0;
                break;
            }
        }

        return $str;
    } else {
        return $GLOBALS['db']->getRow($sql);
    }
}

/*
 * 商创开发 start
 *
 */
//消费满N金额减N减额
function get_goods_payfull($full, $reduce, $id, $goods_id, $table, $type = 0)
{
    if (count($reduce) > 0) {
        for ($i = 0; $i < count($reduce); $i++) {
            if (!empty($full[$i])) {
                $full[$i] = trim($full[$i]);
                $full[$i] = floatval($full[$i]);
                $reduce[$i] = trim($reduce[$i]);
                $reduce[$i] = floatval($reduce[$i]);

                $nid = $id[$i];

                if ($type == 0) {
                    $cs_id = isset($_POST['c_del_' . $nid]) ? $_POST['c_del_' . $nid] : 0;
                } elseif ($type == 1) {
                    $cs_id = isset($_POST['s_del_' . $nid]) ? $_POST['s_del_' . $nid] : 0;
                }

                if ($cs_id > 0 && $nid > 0) {
                    $sql = "delete from " . $GLOBALS['ecs']->table($table) . " where id = '$nid'";
                    $GLOBALS['db']->query($sql);
                } else {
                    //添加或修改 start
                    if ($id[$i] > 0) {
                        $id_where = " and id = '" . $id[$i] . "'";
                    } else {
                        $id_where = " and goods_id = '$goods_id' ";
                    }

                    if ($type == 0) {
                        $other = [
                            'cfull' => $full[$i],
                            'creduce' => $reduce[$i]
                        ];

                        $type_where = " cfull = '" . $full[$i] . "'" . $id_where;
                    } elseif ($type == 1) {
                        $other = [
                            'sfull' => $full[$i],
                            'sreduce' => $reduce[$i]
                        ];

                        $type_where = " sfull = '" . $full[$i] . "'" . $id_where;
                    }

                    $sql = "select id from " . $GLOBALS['ecs']->table($table) . " where " . $type_where;
                    $id_res = $GLOBALS['db']->getOne($sql);

                    if ($id_res < 1) {
                        $other['goods_id'] = $goods_id;
                        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $other, 'INSERT');
                    } else {
                        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $other, 'UPDATE', "id='" . $id[$i] . "'");
                    }
                    //添加或修改 end
                }
            }
        }
    }
}

//查询商品满减促销信息
function get_goods_con_list($goods_id = 0, $table, $type = 0)
{
    $sql = "select * from " . $GLOBALS['ecs']->table($table) . " where goods_id = '$goods_id'";
    $res = $GLOBALS['db']->getAll($sql);

    $arr = [];
    foreach ($res as $key => $row) {
        $arr[$key]['id'] = $row['id'];
        if ($type == 0) {
            $arr[$key]['cfull'] = $row['cfull'];
            $arr[$key]['creduce'] = $row['creduce'];
        } elseif ($type == 1) {
            $arr[$key]['sfull'] = $row['sfull'];
            $arr[$key]['sreduce'] = $row['sreduce'];
        }
    }

    if ($type == 0) {
        $arr = get_array_sort($arr, 'cfull');
    } elseif ($type == 1) {
        $arr = get_array_sort($arr, 'sfull');
    }

    return $arr;
}

//促销--商品最终金额
function get_con_goods_amount($goods_amount = 0, $goods_id = 0, $type = 0, $shipping_fee = 0, $parent_id = 0)
{
    if ($parent_id == 0) {
        if ($type == 0) {
            $table = 'goods_consumption';
        } elseif ($type == 1) {
            $table = 'goods_conshipping';

            if (empty($shipping_fee)) {
                $shipping_fee = 0;
            }
        }

        $res = get_goods_con_list($goods_id, $table, $type);

        if ($res) {
            $arr = [];
            $arr['amount'] = '';
            foreach ($res as $key => $row) {
                if ($type == 0) {
                    if ($goods_amount >= $row['cfull']) {
                        $arr[$key]['cfull'] = $row['cfull'];
                        $arr[$key]['creduce'] = $row['creduce'];
                        $arr[$key]['goods_amount'] = $goods_amount - $row['creduce'];

                        if ($arr[$key]['goods_amount'] > 0) {
                            $arr['amount'] .= $arr[$key]['goods_amount'] . ',';
                        }
                    }
                } elseif ($type == 1) {
                    if ($goods_amount >= $row['sfull']) {
                        $arr[$key]['sfull'] = $row['sfull'];
                        $arr[$key]['sreduce'] = $row['sreduce'];
                        if ($shipping_fee > 0) { //运费要大于0时才参加商品促销活动
                            $arr[$key]['shipping_fee'] = $shipping_fee - $row['sreduce'];
                            $arr['amount'] .= $arr[$key]['shipping_fee'] . ',';
                        } else {
                            $arr['amount'] = '0' . ',';
                        }
                    }
                }
            }

            if ($type == 0) {
                if (!empty($arr['amount'])) {
                    $arr['amount'] = substr($arr['amount'], 0, -1);
                } else {
                    $arr['amount'] = $goods_amount;
                }
            } elseif ($type == 1) {
                if (!empty($arr['amount'])) {
                    $arr['amount'] = substr($arr['amount'], 0, -1);
                } else {
                    $arr['amount'] = $shipping_fee;
                }
            }
        } else {
            if ($type == 0) {
                $arr['amount'] = $goods_amount;
            } elseif ($type == 1) {
                $arr['amount'] = $shipping_fee;
            }
        }

        //消费满最大金额免运费
        if ($type == 1) {
            $sql = "select largest_amount from " . $GLOBALS['ecs']->table('goods') . " where goods_id = '$goods_id'";
            $largest_amount = $GLOBALS['db']->getOne($sql);

            if ($largest_amount > 0 && $goods_amount > $largest_amount) {
                $arr['amount'] = 0;
            }
        }
    } else {
        if ($type == 0) {
            $arr['amount'] = $goods_amount;
        } elseif ($type == 1) {
            $arr['amount'] = $shipping_fee;
        }
    }

    return $arr;
}

//打印订单
function get_order_pdf_goods($order_id = 0)
{

    /* 取得订单商品及货品 */
    $goods_list = [];
    $goods_attr = [];

    $sql = "SELECT o.*, g.goods_number AS storage, o.goods_attr, g.suppliers_id, IFNULL(b.brand_name, '') AS brand_name, p.product_sn
            FROM " . $GLOBALS['ecs']->table('order_goods') . " AS o
                LEFT JOIN " . $GLOBALS['ecs']->table('products') . " AS p
                    ON p.product_id = o.product_id
                LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g
                    ON o.goods_id = g.goods_id
                LEFT JOIN " . $GLOBALS['ecs']->table('brand') . " AS b
                    ON g.brand_id = b.brand_id
            WHERE o.order_id = '$order_id'";
    $res = $GLOBALS['db']->query($sql);
    foreach ($res as $row) {
        /* 虚拟商品支持 */
        if ($row['is_real'] == 0) {
            /* 取得语言项 */
            $filename = ADDONS_PATH . $row['extension_code'] . '/languages/common_' . C('shop.lang') . '.php';
            if (file_exists($filename)) {
                include_once($filename);
                if (!empty($_LANG[$row['extension_code'] . '_link'])) {
                    $row['goods_name'] = $row['goods_name'] . sprintf($_LANG[$row['extension_code'] . '_link'], $row['goods_id'], $order['order_sn']);
                }
            }
        }

        //ecmoban模板堂 --zhuo start 商品金额促销
        $row['goods_amount'] = $row['goods_price'] * $row['goods_number'];
        $goods_con = get_con_goods_amount($row['goods_amount'], $row['goods_id'], 0, 0, $row['parent_id']);

        $goods_con['amount'] = explode(',', $goods_con['amount']);
        $row['amount'] = min($goods_con['amount']);

        $row['dis_amount'] = $row['goods_amount'] - $row['amount'];
        $row['discount_amount'] = price_format($row['dis_amount'], false);
        //ecmoban模板堂 --zhuo end 商品金额促销

        //ecmoban模板堂 --zhuo start //库存查询
        $products = get_warehouse_id_attr_number($row['goods_id'], $row['goods_attr_id'], $row['ru_id'], $row['warehouse_id'], $row['area_id'], $row['model_attr']);
        $row['goods_storage'] = $products['product_number'];

        if ($row['model_attr'] == 1) {
            $table_products = "products_warehouse";
            $type_files = " and warehouse_id = '" . $row['warehouse_id'] . "'";
        } elseif ($row['model_attr'] == 2) {
            $table_products = "products_area";
            $type_files = " and area_id = '" . $row['area_id'] . "'";
        } else {
            $table_products = "products";
            $type_files = "";
        }

        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table($table_products) . " WHERE goods_id = '" . $row['goods_id'] . "'" . $type_files . " LIMIT 0, 1";
        $prod = $GLOBALS['db']->getRow($sql);

        if (empty($prod)) { //当商品没有属性库存时
            $row['goods_storage'] = $row['storage'];
        }

        $row['goods_storage'] = !empty($row['goods_storage']) ? $row['goods_storage'] : 0;
        $row['storage'] = $row['goods_storage'];
        $row['product_sn'] = $products['product_sn'];
        //ecmoban模板堂 --zhuo end //库存查询

        $row['formated_subtotal'] = price_format($row['amount']);
        $row['formated_goods_price'] = price_format($row['goods_price']);

        $row['warehouse_name'] = $GLOBALS['db']->getOne("select region_name from " . $GLOBALS['ecs']->table('region_warehouse') . " where region_id = '" . $row['warehouse_id'] . "'");

        $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组

        if ($row['extension_code'] == 'package_buy') {
            $row['storage'] = '';
            $row['brand_name'] = '';
            $row['package_goods_list'] = get_package_goods($row['goods_id']);
        }

        $goods_list[] = $row;
    }

    return $goods_list;
}

//查找订单里面商品的商家信息
function get_order_ruid($order_id = 0)
{
    $sql = "select og.ru_id from " . $GLOBALS['ecs']->table('order_info') . " as oi left join " . $GLOBALS['ecs']->table('order_goods') . " as og on oi.order_id = og.order_id where oi.order_id = '$order_id'"
        . " group by oi.order_id";
    $ru_id = $GLOBALS['db']->getOne($sql);

    $sql = "select * from " . $GLOBALS['ecs']->table('seller_shopinfo') . " where ru_id = '$ru_id'";
    return $GLOBALS['db']->getRow($sql);
}

function get_cart_combo_goods_list($goods_id = 0, $parent = 0, $group = '')
{
    //ecmoban模板堂 --zhuo start
    if (!empty($_SESSION['user_id'])) {
        $sess_id = " user_id = '" . $_SESSION['user_id'] . "' ";
        $sess = "";
    } else {
        $sess_id = " session_id = '" . real_cart_mac_ip() . "' ";
        $sess = real_cart_mac_ip();
    }
    //ecmoban模板堂 --zhuo end

    $sql = "select goods_price, goods_number, goods_id from " . $GLOBALS['ecs']->table('cart_combo') . " where " . $sess_id .
        " and (parent_id = '$parent' or (goods_id = '$parent' and parent_id = '0')) and group_id = '$group'";
    $res = $GLOBALS['db']->getAll($sql);

    $arr = [];
    $arr['combo_amount'] = 0;
    $arr['combo_number'] = 0;
    foreach ($res as $key => $row) {
        $arr[$key]['goods_number'] = $row['goods_number'];
        $arr[$key]['goods_price'] = $row['goods_price'];
        $arr[$key]['goods_id'] = $row['goods_id'];
        $arr['combo_amount'] += $row['goods_price'] * $row['goods_number'];
        $arr['combo_number'] += $row['goods_number'];
    }

    $arr['shop_price'] = $arr['combo_amount'];
    $arr['combo_amount'] = price_format($arr['combo_amount'], false);

    return $arr;
}

//获取组合购买配件名称
function get_cfg_group_goods()
{
    $group_goods = $GLOBALS['_CFG']['group_goods'];

    $arr = [];
    if (!empty($group_goods)) {
        $group_goods = explode(',', $group_goods);

        foreach ($group_goods as $key => $row) {
            $key += 1;
            $arr[$key] = $row;
        }
    }

    return $arr;
}

function get_merge_fittings_array($fittings_index, $fittings)
{
    $arr = [];
    if ($fittings_index) {
        for ($i = 1; $i <= count($fittings_index); $i++) {
            for ($j = 0; $j <= count($fittings); $j++) {
                if ($fittings_index[$i] == $fittings[$j]['group_id']) {
                    $arr[$i][$j] = $fittings[$j];
                }
            }
        }
    }

    $arr = array_values($arr);
    return $arr;
}

function get_fittings_array_list($merge_fittings, $goods_fittings)
{
    $arr = [];
    if ($merge_fittings) {
        for ($i = 0; $i < count($merge_fittings); $i++) {
            $merge_fittings[$i] = array_merge($goods_fittings, $merge_fittings[$i]);
            $merge_fittings[$i] = array_values($merge_fittings[$i]);
            $arr[$i]['fittings_interval'] = get_choose_goods_combo_cart($merge_fittings[$i]);
        }
    }

    return $arr;
}

function get_combo_goods_list_select($goods_id = 0, $parent = 0, $group = '')
{
    //商品判断属性是否选完
    //$attr_type_list = get_goods_attr_type_list($goods_id, 1);

    //ecmoban模板堂 --zhuo start
    if (!empty($_SESSION['user_id'])) {
        $sess_id = " user_id = '" . $_SESSION['user_id'] . "' ";
        $sess = "";
    } else {
        $sess_id = " session_id = '" . real_cart_mac_ip() . "' ";
        $sess = real_cart_mac_ip();
    }
    //ecmoban模板堂 --zhuo end

    $sql = "select rec_id, goods_id, group_id, goods_attr_id from " . $GLOBALS['ecs']->table('cart_combo') . " where " . $sess_id .
        " and (parent_id = '$parent' or (goods_id = '$parent' and parent_id = '0')) and group_id = '$group'";
    $res = $GLOBALS['db']->getAll($sql);

    $arr = [];
    $arr['attr_count'] = '';
    foreach ($res as $key => $row) {
        $arr[$key]['rec_id'] = $row['rec_id'];
        $arr[$key]['goods_id'] = $row['goods_id'];
        $arr[$key]['group_id'] = $row['group_id'];
        $arr[$key]['goods_attr_id'] = $row['goods_attr_id'];
        $arr[$key]['attr_count'] = get_goods_attr_type_list($row['goods_id'], 1);

        if (!empty($arr[$key]['goods_attr_id'])) {
            $attr_count = count(explode(',', $arr[$key]['goods_attr_id']));
        } else {
            $attr_count = 0;
        }

        if ($arr[$key]['attr_count'] > 0) {
            if ($attr_count == $arr[$key]['attr_count']) {
                $arr[$key]['yes_attr'] = 1;
            } else {
                $arr[$key]['yes_attr'] = 0;
            }
        } else {
            $arr[$key]['yes_attr'] = 1;
        }

        $arr['attr_count'] .= $arr[$key]['yes_attr'] . ",";
    }

    $attr_array = 0;
    $attr_yes = explode(',', substr($arr['attr_count'], 0, -1));
    foreach ($attr_yes as $row) {
        $attr_array += $row;
    }

    $goods_count = count($res);
    if ($attr_array == $goods_count) {
        return 1;
    } else {
        return 0;
    }
}

/**
 * 取得自定义导航栏列表
 * @param   string $type 位置，如top、bottom、middle
 * @return  array         列表
 */
function get_merchants_navigator($ru_id = 0, $ctype = '', $catlist = [])
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('merchants_nav') . '
            WHERE ru_id = ' . $ru_id . ' and ifshow = \'1\' ORDER BY type, vieworder';
    $res = $GLOBALS['db']->query($sql);

    $cur_url = substr(strrchr($_SERVER['REQUEST_URI'], '/'), 1);

    if (intval($GLOBALS['_CFG']['rewrite'])) {
        if (strpos($cur_url, '-')) {
            preg_match('/([a-z]*)-([0-9]*)/', $cur_url, $matches);
            $cur_url = $matches[1] . '.php?id=' . $matches[2];
        }
    } else {
        $cur_url = substr(strrchr($_SERVER['REQUEST_URI'], '/'), 1);
    }

    $noindex = false;
    $active = 0;
    $navlist = [
        'top' => [],
        'middle' => [],
        'bottom' => []
    ];
    foreach ($res as $row) {
        $navlist[$row['type']][] = [
            'cat_id' => $row['cat_id'],
            'cat_name' => $row['name'],
            'opennew' => $row['opennew'],
            'url' => $row['url'],
            'ctype' => $row['ctype'],
            'cid' => $row['cat_id'],
            'vieworder' => $row['vieworder'],
            'child' => get_store_category_child($row['cat_id'], $row['ru_id']),
        ];
    }

    /*遍历自定义是否存在currentPage*/
    foreach ($navlist['middle'] as $k => $v) {
        $condition = empty($ctype) ? (strpos($cur_url, $v['url']) === 0) : (strpos($cur_url, $v['url']) === 0 && strlen($cur_url) == strlen($v['url']));
        if ($condition) {
            $navlist['middle'][$k]['active'] = 1;
            $noindex = true;
            $active += 1;
        }
        if (substr($v['url'], 0, 8) == 'category') {
            $cat_id = $v['cid'];
            $children = get_children($cat_id);
            $cat_list = get_categories_tree_xaphp($cat_id);
            $navlist['middle'][$k]['cat'] = 1;
            $navlist['middle'][$k]['cat_list'] = $cat_list;
        }
    }

    if (!empty($ctype) && $active < 1) {
        foreach ($catlist as $key => $val) {
            foreach ($navlist['middle'] as $k => $v) {
                if (!empty($v['ctype']) && $v['ctype'] == $ctype && $v['cid'] == $val && $active < 1) {
                    $navlist['middle'][$k]['active'] = 1;
                    $noindex = true;
                    $active += 1;
                }
            }
        }
    }

    if ($noindex == false) {
        $navlist['config']['index'] = 1;
    }

    return $navlist;
}

/**
 * 获得指定分类下的子分类的数组,商家店铺分类
 *
 * @access  public
 * @param   int $cat_id 分类的ID
 * @param   int $selected 当前选中分类的ID
 * @param   boolean $re_type 返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int $level 限定返回的级数。为0时返回所有级数
 * @param   int $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return  mix
 */
function goods_admin_store_cat_list($cat_info)
{
    $arr = [];

    if ($cat_info) {
        foreach ($cat_info as $key => $row) {
            if ($row['level'] > 2) {
                $arr[$key] = $row;
            }
        }

        $arr = get_admin_goods_cat_list_child($arr);
        foreach ($arr as $key => $row) {
            $arr[$key] = $row;
            if ($row['child_array']) {
                $arr[$key]['child_array'] = array_values($row['child_array']);
            }
        }
    }

    return $arr;
}

function get_admin_goods_cat_list_child($arr)
{
    $arr = array_values($arr);

    $newArr = [];
    for ($i = 0; $i < count($arr); $i++) {
        if ($arr[$i]['level'] == 3) {
            $newArr[$i] = $arr[$i];
            $newArr[$i]['level'] = 0;
        }
    }
    $newArr = array_values($newArr);

    for ($i = 0; $i < count($newArr); $i++) {
        for ($j = 0; $j < count($arr); $j++) {
            if ($arr[$j]['level'] == 4) {
                if ($newArr[$i]['cat_id'] == $arr[$j]['parent_id']) {
                    $newArr[$i]['child_array'][$j] = $arr[$j];
                    $newArr[$i]['child_array'][$j]['level'] = 1;
                }
            }
        }
    }

    return $newArr;
}

/**
 * 商家店铺分类
 */
function goods_store_cat_list($cat_id = 0, $cat_info = [], $merchant_id = 0)
{
    $arr = [];

    if ($cat_info) {
        $cat_info = array_values($cat_info);

        foreach ($cat_info as $key => $rows) {
            if ($rows['parent_id'] == 0) {
                $arr[$key] = $rows;
                $arr[$key]['child_array'] = get_fine_child_cat($rows['cat_id'], $cat_info);
            } elseif ($rows['cat_id'] == $cat_id) {
                $arr = get_same_level_cat($rows['level'], $rows['parent_id'], $merchant_id);
            }
        }
    }

    return $arr;
}

//求出子分类
function get_fine_child_cat($paren_id = 0, $cat_info = [])
{
    $cat_info = array_values($cat_info);

    $arr = [];
    foreach ($cat_info as $key => $rows) {
        if ($rows['parent_id'] == $paren_id) {
            $arr[$key] = $rows;
        }
    }

    return $arr;
}

function get_same_level_cat($level = 0, $parent_id = 0, $merchant_id = 0)
{
    $arr = [];
    $cat_info = cat_list(0, 0, false, 0, true, 'category', 0, $merchant_id);

    foreach ($cat_info as $key => $rows) {
        if ($rows['level'] == $level && $rows['parent_id'] == $parent_id) {
            $arr[$key] = $rows;
            $arr[$key]['child_array'] = get_fine_child_cat($rows['cat_id'], $cat_info);
        }
    }

    return $arr;
}

//退换货的--换货属性查询
function get_user_attr_checked($goods_attr, $attr_id)
{
    $arr['class'] = 'catcolor';
    $arr['attr_val'] = '';
    if ($goods_attr) {
        foreach ($goods_attr as $key => $grow) {
            if ($grow == $attr_id) {
                $arr['class'] = 'cattsel';
                $arr['attr_val'] = $grow;
                return $arr;
            }
        }
    }

    return $arr;
}

/**
 * 获取地址 by Leah
 * @param type $country
 * @param type $province
 * @param type $city
 * @param type $district
 * @return type
 */
function get_consignee_info($order_id, $return_address)
{

    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
        "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
        "FROM " . $GLOBALS['ecs']->table('order_return') . " AS o " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS c ON o.country = c.region_id " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS p ON o.province = p.region_id " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS t ON o.city = t.region_id " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS d ON o.district = d.region_id " .
        "WHERE o.order_id = '$order_id'";
    $address = $GLOBALS['db']->getOne($sql);

    $address = "[" . $address . "]&nbsp;" . $return_address;

    return $address;
}


/**
 * 取得用户等级信息
 * @access   public
 * @author   Xuan Yan
 *
 * @return array
 */
function get_rank_info()
{
    global $db, $ecs;

    if (!empty($_SESSION['user_id'])) {

        $sql = "SELECT rank_points, user_rank FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = " . $_SESSION['user_id'];
        $row = $GLOBALS['db']->getRow($sql);

        $sql = 'SELECT rank_id, rank_name, discount, special_rank FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE rank_id = '" .$row['user_rank']. "' LIMIT 1";
        $rank = $GLOBALS['db']->getRow($sql);
        // 特殊等级
        if (!empty($rank) && $rank['special_rank'] == 1) {
            return ['user_rank' => $rank];
        } else {
            // 非特殊等级
            $sql = "SELECT rank_id, rank_name, discount, min_points" .
                " FROM " . $GLOBALS['ecs']->table('user_rank') .
                " WHERE special_rank = '0' AND min_points <= " . intval($row['rank_points']) . " ORDER BY min_points DESC";
            $rank = $GLOBALS['db']->getRow($sql);
            if ($rank) {
                $next_rank_name = $rank['rank_name'];
                $next_rank = $rank['min_points'] - $row['rank_points'];
                $rank['rank_points'] = $row['rank_points'];
                return ['next_rank_name' => $next_rank_name, 'next_rank' => $next_rank, 'user_rank' => $rank];
            } else {
                return [];
            }
        }
    } else {
        return [];
    }
}

/**
 * 获取用户中心默认页面所需的数据
 *
 * @access  public
 * @param   int $user_id 用户ID
 *
 * @return  array       $info               默认页面所需资料数组
 */
function get_user_default($user_id = 0)
{
    $user_bonus = get_user_bonus();

    $sql = "SELECT email, mobile_phone,sex, pay_points,nick_name, user_money, credit_line, last_login, is_validated, user_picture, rank_points,user_rank, birthday FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'";
    $row = $GLOBALS['db']->getRow($sql);

    $info = [];
    if (!empty($row)) {
        /* 会员等级 */
        $sql = 'SELECT rank_id, rank_name, discount, special_rank FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE rank_id = '" .$row['user_rank']. "' LIMIT 1";
        $rank = $GLOBALS['db']->getRow($sql);
        // 特殊等级
        if (!empty($rank) && $rank['special_rank'] == 1) {
            $info['rank_name'] = $rank['rank_name'];
            $info['user_rank'] = $rank['rank_id'];
            $info['discount'] = $rank['discount'] / 100.00;
        } else {
            // 非特殊等级
            $sql = "SELECT rank_id, rank_name, discount, min_points" .
                " FROM " . $GLOBALS['ecs']->table('user_rank') .
                " WHERE special_rank = '0' AND min_points <= " . intval($row['rank_points']) . " ORDER BY min_points DESC";
            $rank = $GLOBALS['db']->getRow($sql);
            if ($rank) {
                $info['rank_name'] = $rank['rank_name'];
                $info['user_rank'] = $rank['rank_id'];
                $info['discount'] = $rank['discount'] / 100.00;
            } else {
                $info['rank_name'] = $GLOBALS['_LANG']['undifine_rank'];
                $info['user_rank'] = 0;
                $info['discount'] = 1;
            }
        }

        /* 时时更新会员等级 */
        if ($info['user_rank'] != $_SESSION['user_rank']) {
            $_SESSION['user_rank'] = $info['user_rank'];
            $_SESSION['discount'] = $info['discount'];
        }
    }

    $info['shop_name'] = $GLOBALS['_CFG']['shop_name'];
    $info['integral'] = $row['pay_points'] . $GLOBALS['_CFG']['integral_name'];
    /* 增加是否开启会员邮件验证开关 */
    $info['is_validate'] = ($GLOBALS['_CFG']['member_email_validate'] && !$row['is_validated']) ? 0 : 1;
    $info['credit_line'] = $row['credit_line'];
    $info['formated_credit_line'] = price_format($info['credit_line'], false);
    // 微信用户头像和昵称
    $wechat_user = get_wechat_user_info($user_id);
    $info['user_picture'] = get_image_path($wechat_user['user_picture'], '', elixir('img/user_default.png'));
    $info['nick_name'] = $wechat_user['nick_name'];

    //如果$_SESSION中时间无效说明用户是第一次登录。取当前登录时间。
    $last_time = !isset($_SESSION['last_time']) ? $row['last_login'] : $_SESSION['last_time'];
    if ($last_time == 0) {
        $_SESSION['last_time'] = $last_time = gmtime();
    }
    $info['last_time'] = local_date($GLOBALS['_CFG']['time_format'], $last_time);
    $info['surplus'] = price_format($row['user_money'], false);
    $info['bonus'] = sprintf(L('user_bonus_info'), $user_bonus['bonus_count'], price_format($user_bonus['bonus_value'], false));
    $info['email'] = $row['email'];
    $info['mobile_phone'] = $row['mobile_phone'];
    $info['user_money'] = $row['user_money'];
    if ($row['sex'] == 1) {
        $info['sex'] = "男";
    } elseif ($row['sex'] == 2) {
        $info['sex'] = "女";
    } else {
        $info['sex'] = "保密";
    }
    $info['birthday'] = (empty(strtotime($row['birthday'])) || strtotime($row['birthday']) <= 0) ? '' : $row['birthday'];
    // 用户订单数量
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') .
        " WHERE user_id = '" . $user_id . "' AND add_time > '" . local_strtotime('-1 months') . "'";
    $info['order_count'] = $GLOBALS['db']->getOne($sql);
    // 用户收货订单数量
    include_once(BASE_PATH . 'Helpers/order_helper.php');
    $sql = "SELECT order_id, order_sn " .
        " FROM " . $GLOBALS['ecs']->table('order_info') .
        " WHERE user_id = '" . $user_id . "' AND shipping_time > '" . $last_time . "'" . order_query_sql('shipped');
    $info['shipped_order'] = $GLOBALS['db']->getAll($sql);
    return $info;
}

//晒单回复ajax
function single_show_reply_list($parent_id, $page)
{
    require_once('includes/cls_newPage.php'); //ecmoban模板堂 --zhuo

    $record_count = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('comment') . "WHERE parent_id = " . $parent_id . " AND single_id > 0");

    $reply_comment = new Page($record_count, 5, '', $parent_id, 0, $page, 'single_reply_gotoPage', 1, 1);
    $limit = $reply_comment->limit;
    $reply_paper = $reply_comment->fpage([0, 4, 5, 6, 9]);

    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('comment') . " WHERE parent_id='$parent_id' AND single_id > 0 AND status=1 ORDER BY add_time DESC " . $limit;
    $comment = $GLOBALS['db']->getAll($sql);

    $comment_list = [];
    $replay_comment = [];
    foreach ($comment as $key => $comm) {

        //判断引用的那个评论
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('comment') . " WHERE comment_id='$comm[parent_id]'";
        $child_comment = $GLOBALS['db']->getRow($sql);
        if ($child_comment) {
            $comment_list[$key]['quote_username'] = $child_comment['user_name'];
            $comment_list[$key]['quote_content'] = $child_comment['content'];
        }
        $comment_list[$key]['comment_id'] = $comm['comment_id'];
        $comment_list[$key]['content'] = $comm['content'];
        if (!empty($comm['add_time'])) {
            $comment_list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $comm['add_time']);
        }
        if (!empty($comm['user_name'])) {
            $comment_list[$key]['user_name'] = $comm['user_name'];
        }
    }

    $cmt = ['comment_list' => $comment_list, 'reply_paper' => $reply_paper, 'record_count' => $record_count];

    return $cmt;
}

//查询市下面是否还有区域
function get_isHas_area($parent_id = 0, $type = 0)
{
    if ($type == 0) {
        $where = "parent_id = '$parent_id' order by region_id asc limit 0, 1";
        $sql = "select region_id, region_name from " . $GLOBALS['ecs']->table('region') . " where 1 AND $where";

        return $GLOBALS['db']->getAll($sql);
    } elseif ($type == 1) {
        $where = "r1.region_id = '$parent_id'";
        $sql = "select r1.parent_id, r2.region_name from " . $GLOBALS['ecs']->table('region') . " as r1, " . $GLOBALS['ecs']->table('region') . " as r2 " . " where 1 AND r1.parent_id = r2.region_id AND $where";

        return $GLOBALS['db']->getRow($sql);
    }
}

//判断商品是否被编辑，如有编辑，则设置为未审核
function get_goods_file_content($goods_id, $arr = '', $ru_id, $review_goods)
{
    if ($ru_id > 0) {
        if (!empty($arr)) {
            $arr = explode('-', $arr);
            $arr1 = $arr[0]; //商品信息
            $arr2 = $arr[1]; //仓库商品信息

            $arr1 = explode(',', $arr1);

            for ($i = 0; $i < count($arr1); $i++) {
                if ($arr1[$i] == 'promote_price') {
                    $contents = floatval($_POST[$arr1[$i]]);
                } else {
                    $contents = $_POST[$arr[$i]];
                }

                $sql = "SELECT " . $arr1[$i] . " FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = '$goods_id'";
                $res = $GLOBALS['db']->getOne($sql);

                if ($contents <> $res) {
                    $review_status = 1;

                    if ($GLOBALS['_CFG']['review_goods'] == 0) {
                        $review_status = 5;
                    } else {
                        if ($review_goods == 0) {
                            $review_status = 5;
                        }
                    }

                    $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . " SET review_status = $review_status " . " WHERE goods_id = '$goods_id' AND user_id > 0";
                    $GLOBALS['db']->query($sql);
                    break;
                }
            }
        } else {
            $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . " SET review_status = 3 " . " WHERE goods_id = '$goods_id'";
            $GLOBALS['db']->query($sql);
        }
    }
}

/* 生成随机字符 */
function mc_random($length, $char_str = '[]abcdefghijklmnopqrstuvwxyz*/|0123456789{}')
{
    $hash = '';
    $chars = $char_str;
    $max = strlen($chars);
    for ($i = 0; $i < $length; $i++) {
        $hash .= substr($chars, (rand(0, 1000) % $max), 1);
    }
    return $hash;
}

/**
 *  获取用户指定范围的订单列表
 *
 * @access  public
 * @param   int $user_id 用户ID号
 * @param   int $num 列表最大数量
 * @param   int $start 列表起始位置
 * @return  array       $order_list     订单列表
 */
function get_default_user_orders($user_id, $record_count, $where = '', $page = 1)
{
    /* 取得订单列表 */
    $arr = [];
    $sql = "SELECT og.ru_id, oi.main_order_id, oi.consignee, oi.order_id, oi.order_sn, oi.order_status, oi.shipping_status, oi.pay_status, oi.add_time, oi.shipping_time, oi.auto_delivery_time, oi.sign_time, " .
        "(oi.goods_amount + oi.shipping_fee + oi.insure_fee + oi.pay_fee + oi.pack_fee + oi.card_fee + oi.tax - oi.discount) AS total_fee, og.goods_id, " .
        "oi.invoice_no, oi.shipping_name, oi.tel, oi.email, oi.address, oi.province, oi.city, oi.district " .
        " FROM " . $GLOBALS['ecs']->table('order_info') . " as oi" .
        " left join " . $GLOBALS['ecs']->table('order_goods') . " as og on oi.order_id = og.order_id" .
        " WHERE oi.user_id = '$user_id' and oi.is_delete = 0 " . $where .
        " and (select count(*) from " . $GLOBALS['ecs']->table('order_info') . " as oi2 where oi2.main_order_id = oi.order_id) = 0 " .  //主订单下有子订单时，则主订单不显示
        " group by oi.order_id ORDER BY oi.add_time DESC limit 0,5";

    $res = $GLOBALS['db']->getAll($sql);

    $arr = [];
    foreach ($res as $key => $row) {
        $row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . ',' .
            $GLOBALS['_LANG']['ps'][$row['pay_status']] . ',' .
            $GLOBALS['_LANG']['ss'][$row['shipping_status']];

        $arr[$key]['order_id'] = $row['order_id'];
        $arr[$key]['order_sn'] = $row['order_sn'];
        $arr[$key]['consignee'] = $row['consignee'];
        $arr[$key]['total_fee'] = price_format($row['total_fee'], false);
        $arr[$key]['order_status'] = $row['order_status'];
        $arr[$key]['order_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
    }

    return $arr;
}

/**
 *  获取指定用户的收藏商品列表
 *
 * @access  public
 * @param   int $user_id 用户ID
 * @param   int $num 列表最大数量
 * @param   int $start 列表其实位置
 *
 * @return  array   $arr
 */
function get_default_collection_goods($user_id)
{
    //ecmoban模板堂 --zhuo start
    if (!isset($_COOKIE['province'])) {
        $area_array = get_ip_area_name();

        if ($area_array['county_level'] == 2) {
            $date = ['region_id', 'parent_id', 'region_name'];
            $where = "region_name = '" . $area_array['area_name'] . "' AND region_type = 2";
            $city_info = get_table_date('region', $where, $date, 1);

            $date = ['region_id', 'region_name'];
            $where = "region_id = '" . $city_info[0]['parent_id'] . "'";
            $province_info = get_table_date('region', $where, $date);

            $where = "parent_id = '" . $city_info[0]['region_id'] . "' order by region_id asc limit 0, 1";
            $district_info = get_table_date('region', $where, $date, 1);
        } elseif ($area_array['county_level'] == 1) {
            $area_name = $area_array['area_name'];

            $date = ['region_id', 'region_name'];
            $where = "region_name = '$area_name'";
            $province_info = get_table_date('region', $where, $date);

            $where = "parent_id = '" . $province_info['region_id'] . "' order by region_id asc limit 0, 1";
            $city_info = get_table_date('region', $where, $date, 1);

            $where = "parent_id = '" . $city_info[0]['region_id'] . "' order by region_id asc limit 0, 1";
            $district_info = get_table_date('region', $where, $date, 1);
        }
    }

    $province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : $province_info['region_id'];
    $city_id = isset($_COOKIE['city']) ? $_COOKIE['city'] : $city_info[0]['region_id'];
    $district_id = isset($_COOKIE['district']) ? $_COOKIE['district'] : $district_info[0]['region_id'];

    cookie('province', $province_id);
    cookie('city', $city_id);
    cookie('district', $district_id);

    $area_info = get_area_info($province_id);
    $area_id = $area_info['region_id'];

    $region_where = "regionId = '$province_id'";
    $date = ['parent_id'];
    $region_id = get_table_date('region_warehouse', $region_where, $date, 2);

    $leftJoin = '';

    $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_goods') . " as wg on g.goods_id = wg.goods_id and wg.region_id = '$region_id' ";
    $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_area_goods') . " as wag on g.goods_id = wag.goods_id and wag.region_id = '$area_id' ";
    //ecmoban模板堂 --zhuo end

    $sql = 'SELECT g.goods_thumb, g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, ' .
        "IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * '$_SESSION[discount]'), g.shop_price * '$_SESSION[discount]')  AS shop_price, " .
        "IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, " .
        'g.promote_start_date,g.promote_end_date, c.rec_id, c.is_attention, c.add_time' .
        ' FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' AS c' .
        " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g " .
        "ON g.goods_id = c.goods_id " .
        $leftJoin .
        " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
        "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
        " WHERE c.user_id = '$user_id' ORDER BY c.rec_id DESC limit 0,5";
    $res = $GLOBALS['db']->query($sql, $num, $start);

    $goods_list = [];
    foreach ($res as $row) {
        if ($row['promote_price'] > 0) {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        } else {
            $promote_price = 0;
        }

        $goods_list[$row['goods_id']]['rec_id'] = $row['rec_id'];
        $goods_list[$row['goods_id']]['is_attention'] = $row['is_attention'];
        $goods_list[$row['goods_id']]['goods_id'] = $row['goods_id'];
        $goods_list[$row['goods_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);;
        $goods_list[$row['goods_id']]['goods_name'] = $row['goods_name'];
        $goods_list[$row['goods_id']]['market_price'] = price_format($row['market_price']);
        $goods_list[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
        $goods_list[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
        $goods_list[$row['goods_id']]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
        $goods_list[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
    }

    return $goods_list;
}

function get_user_helpart()
{
    $article_id = $GLOBALS['_CFG']['user_helpart'];
    $arr = [];

    $new_article = substr($article_id, -1);
    if ($new_article == ',') {
        $article_id = substr($article_id, 0, -1);
    }

    if (!empty($article_id)) {
        $sql = "SELECT article_id, title FROM " . $GLOBALS['ecs']->table('article') . " where article_id in($article_id) order by article_id DESC";
        $res = $GLOBALS['db']->getAll($sql);

        foreach ($res as $key => $row) {
            $arr[$key]['article_id'] = $row['article_id'];
            $arr[$key]['title'] = $row['title'];
            $arr[$key]['url'] = build_uri('article', ['aid' => $row['article_id']], $row['title']);
        }
    }

    return $arr;
}

//ecmoban模板堂 --zhuo end

//划分商家或平台运费 start
function get_cart_goods_ru_list($goods, $type = 0)
{ //商家划分

    $ru_id_list = get_cart_goods_ru_id($goods);
    $ru_id_list = array_values(array_unique($ru_id_list));

    $arr = [];
    foreach ($ru_id_list as $wkey => $ru) {
        foreach ($goods as $gkey => $row) {
            if ($ru == $row['ru_id']) {
                $arr[$ru][$gkey] = $row;
            }
        }
    }

    if ($type == 1) { //购物车显示
        return $arr;
    } else {
        $new_arr = [];
        foreach ($arr as $key => $row) {
            $new_arr[$key] = get_cart_goods_warehouse_list($row);
        }

        return $new_arr;
    }
}

function get_cart_goods_ru_id($goods)
{
    $arr = [];
    foreach ($goods as $key => $row) {
        $arr[$key] = $row['ru_id'];
    }

    return $arr;
}

function get_cart_goods_warehouse_list($goods)
{ //仓库划分

    $warehouse_id_list = get_cart_goods_warehouse_id($goods);
    $warehouse_id_list = array_values(array_unique($warehouse_id_list));

    $arr = [];
    foreach ($warehouse_id_list as $wkey => $warehouse) {
        foreach ($goods as $gkey => $row) {
            if ($warehouse == $row['warehouse_id']) {
                $arr[$warehouse][$gkey] = $row;
            }
        }
    }


    return $arr;
}

function get_cart_goods_warehouse_id($goods)
{
    $arr = [];
    foreach ($goods as $key => $row) {
        $arr[$key] = $row['warehouse_id'];
    }

    return $arr;
}

/*
 *合计运费
 *购物车显示
 *订单分单
 *$type
*/
function get_cart_goods_combined_freight($goods, $type = 0, $region = '', $ru_id = 0, $shipping_id = 0)
{
    $arr = [];
    $new_arr = [];
    $shipping_type = isset($_SESSION['merchants_shipping'][$ru_id]['shipping_type']) ? intval($_SESSION['merchants_shipping'][$ru_id]['shipping_type']) : 0;

    if ($type == 1) { //购物提交订单页面显示
        foreach ($goods as $key => $row) {
            foreach ($row as $warehouse => $rows) {
                foreach ($rows as $gkey => $grow) {
                    if ($grow['extension_code'] == 'package_buy' || $grow['is_shipping'] == 0) {
                        @$arr[$key][$warehouse]['weight'] += $grow['goodsweight'] * $grow['goods_number']; //商品总重量
                        @$arr[$key][$warehouse]['goods_price'] += $grow['goods_price'] * $grow['goods_number']; //商品总金额
                        @$arr[$key][$warehouse]['ru_id'] = $key; //商家ID
                        // 如果不是虚拟商品 不加入计算运费
                        if ($grow['is_real'] == 1) {
                            @$arr[$key][$warehouse]['number'] += $grow['goods_number']; //商品总数量
                        }
                        @$arr[$key][$warehouse]['warehouse_id'] = $warehouse; //仓库ID
                        @$arr[$key][$warehouse]['warehouse_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM " . $GLOBALS['ecs']->table("region_warehouse") . " WHERE region_id = '$warehouse'"); //仓库名称
                    }
                }
            }
        }

        foreach ($arr as $key => $row) {
            if (!empty($shipping_id)) {
                $shipping_info = get_shipping_code($shipping_id);
                $shipping_code = $shipping_info['shipping_code'];
            } else {
                $seller_shipping = get_seller_shipping_type($key);
                $shipping_code = $seller_shipping['shipping_code']; //配送代码
            }
            foreach ($row as $warehouse => $rows) {
                @$arr[$key][$warehouse]['shipping'] = get_goods_freight($rows, $rows['warehouse_id'], $region, $rows['goods_number'], $shipping_code);
            }
        }

        $new_arr['shipping_fee'] = 0;
        foreach ($arr as $key => $row) {
            foreach ($row as $warehouse => $rows) {
                //自营--自提时--运费清0
                if (isset($rows['shipping_code']) && $rows['shipping_code'] == 'cac') {
                    $rows['shipping']['shipping_fee'] = 0;
                }
                $new_arr['shipping_fee'] += $rows['shipping']['shipping_fee'];
            }
        }

        $arr = ['ru_list' => $arr, 'shipping' => $new_arr];
        return $arr;
    } elseif ($type == 2) { //订单分单
        $arr = get_cart_goods_warehouse_list($goods);

        foreach ($arr as $warehouse => $row) {
            foreach ($row as $gw => $grow) {
                if ($grow['extension_code'] == 'package_buy' || $grow['is_shipping'] == 0) {
                    @$new_arr[$warehouse]['weight'] += $grow['goodsweight'] * $grow['goods_number']; //商品总重量
                    @$new_arr[$warehouse]['goods_price'] += $grow['goods_price'] * $grow['goods_number']; //商品总金额
                    @$new_arr[$warehouse]['ru_id'] = $grow['ru_id']; //商家ID
                    // 如果不是虚拟商品 不加入计算运费
                    if ($grow['is_real'] == 1) {
                        @$new_arr[$warehouse]['number'] += $grow['goods_number']; //商品总数量
                    }
                    @$new_arr[$warehouse]['warehouse_id'] = $warehouse; //仓库ID
                    @$new_arr[$warehouse]['order_id'] = $grow['order_id']; //订单ID
                    @$new_arr[$warehouse]['warehouse_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM " . $GLOBALS['ecs']->table("region_warehouse") . " WHERE region_id = '$warehouse'"); //仓库名称
                }
            }
        }

        foreach ($new_arr as $key => $row) {
            $sql = "SELECT country, province, city, district, shipping_id FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_id = '" . $row['order_id'] . "'";
            $order = $GLOBALS['db']->getRow($sql);
            $shipping_arr = explode(",", $order['shipping_id']);
            if (is_array($shipping_arr)) {
                foreach ($shipping_arr as $kk => $vv) {
                    $ruid_shipping = explode("|", $vv);
                    if ($ruid_shipping[0] == $ru_id) {
                        $shipping_info = get_shipping_code($ruid_shipping[1]);
                        $shipping_code = $shipping_info['shipping_code'];
                        continue;
                    }
                }
            }

            @$new_arr[$key]['shipping'] = get_goods_freight($row, $row['warehouse_id'], $order, $row['number'], $shipping_code);
            //自营--自提时--运费清0
            if ($ru_id == 0 && $shipping_type == 1) {
                $new_arr[$key]['shipping']['shipping_fee'] = 0;
            }
            $new_arr['shipping_fee'] += $new_arr[$key]['shipping']['shipping_fee'];
        }
        //get_print_r($new_arr);
        $arr = $new_arr;
    }

    return $arr;
}

function get_warehouse_cart_goods_info($goods, $type, $region, $shipping_id = 0)
{
    if ($type == 1) {
        $goods = get_cart_goods_ru_list($goods);
    } else {
        $goods = get_cart_goods_warehouse_list($goods);
    }
    //总运费
    $shipping_fee = get_cart_goods_combined_freight($goods, $type, $region, 0, $shipping_id);

    return $shipping_fee;
}

//列出商家运费详细信息
function get_ru_info_list($ru_list)
{
    $arr = [];
    if (empty($ru_list)) {
        $ru_list = [];
    }
    foreach ($ru_list as $key => $row) {
        if ($key == 0) {
            $shop_name = $GLOBALS['db']->getOne("SELECT shop_name FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . " WHERE ru_id = '$key'");
        } else {
            $shop_information = $GLOBALS['db']->getRow("SELECT shoprz_brandName, shopNameSuffix FROM " . $GLOBALS['ecs']->table('merchants_shop_information') . " WHERE user_id = '$key'");
            $shop_name = $shop_information['shoprz_brandName'] . $shop_information['shopNameSuffix'];
        }

        $arr[$key]['ru_name'] = $shop_name;
        $arr[$key]['ru_shipping'] = $row;
        foreach ($row as $warehouse => $rows) {
            $arr[$key]['shipping_fee'] += $rows['shipping']['shipping_fee'];
        }

        $arr[$key]['shippingFee'] = $arr[$key]['shipping_fee'];
        $arr[$key]['shipping_fee'] = price_format($arr[$key]['shipping_fee'], false);
    }

    //get_print_r($arr);
    return $arr;
}

//划分商家或平台运费 end

//查询该商品关联地区
function get_goods_link_area_list($goods_id = 0, $ru_id = 0)
{
    $sql = "SELECT goods_id, region_id, ru_id FROM " . $GLOBALS['ecs']->table('link_area_goods') . " WHERE goods_id = '$goods_id' AND ru_id = '$ru_id'";
    $res = $GLOBALS['db']->getAll($sql);

    $arr = [];
    $arr['goods_area'] = '';

    if ($res) {
        foreach ($res as $key => $row) {
            $arr['goods_area'] .= $row['region_id'] . ",";
        }

        $arr['goods_area'] = explode(',', substr($arr['goods_area'], 0, -1));
    }

    return $arr;
}

/*
 * 合计全场通用优惠活动折扣金额
 */
function get_single_order_fav($discount_all = '', $orderFavourable = [], $type = 0)
{
    $discount = 0;
    $has_terrace = '';
    foreach ($orderFavourable as $key => $row) {
        $discount += $row['compute_discount']['discount'];
        $has_terrace .= $key . ",";
    }

    if ($has_terrace != '') {
        $has_terrace = substr($has_terrace, 0, -1);
        $has_terrace = explode(",", $has_terrace);
    }

    if (in_array(0, $has_terrace)) {
        $has_terrace = 1; //有平台商品
    } else {
        $has_terrace = 0; //无平台商品
    }

    $discount_all = number_format(($discount_all), 2, '.', '');
    $discount = number_format(($discount), 2, '.', '');
    $commonuse_discount = $discount_all - $discount;

    return ['discount' => $commonuse_discount, 'has_terrace' => $has_terrace];
}

//调取店铺名称
function get_shop_name($ru_id = 0, $type = 0)
{
    $sql = "SELECT shop_name, check_sellername, shopname_audit, shop_logo, logo_thumb, brand_thumb FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . " WHERE ru_id = '$ru_id'";
    $shopinfo = $GLOBALS['db']->getRow($sql);

    $sql = "SELECT concat(shoprz_brandName, shopNameSuffix) as shop_name, shoprz_brandName, shopNameSuffix, rz_shopName, is_IM, self_run FROM " . $GLOBALS['ecs']->table('merchants_shop_information') . " WHERE user_id = '$ru_id'";
    $shop_information = $GLOBALS['db']->getRow($sql);
    $shopinfo['self_run'] = $shop_information['self_run'];//自营店铺传值
    if (empty($shop_information)) {
        $shop_information['shop_name'] = $shopinfo['shop_name'];
    }
    if ($type == 3) { //搜索店铺
        $shop_information['shop_name'] = $shop_information['shoprz_brandname'];
        $shop_information['rz_shopName'] = str_replace(['旗舰店', '专卖店', '专营店'], '', $shop_information['rz_shopname']);
    }

    if ($shopinfo['shopname_audit'] == 1) {
        if ($shopinfo['check_sellername'] == 1) { //期望店铺名称
            $shop_name = $shop_information['rz_shopname'];
        } elseif ($shopinfo['check_sellername'] == 2) {
            $shop_name = $shopinfo['shop_name'];
        } else {
            if ($ru_id > 0) {
                $shop_name = $shop_information['shop_name'];
            } else {
                $shop_name = $shopinfo['shop_name'];
            }
        }
    } else {
        $shop_name = $shop_information['rz_shopname']; //默认店铺名称
    }

    if ($type == 1) {
        return $shop_name;
    } elseif ($type == 2) {
        return $shopinfo;
    } elseif ($type == 3) {
        return ['shop_name' => $shop_name, 'shopNamesuffix' => $shop_information['shopNamesuffix']];
    } else {
        return $shop_information;
    }
}

/*
 *读取缓存文件
 */
function get_cache_site_file($file = '', $var_arr = [])
{
    static $arr = null;
    if ($arr === null) {
        $data = read_static_cache($file);
        if ($data === false) {
            if ($file == 'category_tree' || $file == 'category_tree1' || $file == 'category_tree2') {
                if (empty($var_arr)) {
                    $arr = get_categories_tree_pro();
                } else {
                    $arr = get_categories_tree_pro($var_arr[0], $var_arr[1]);
                }
            } else {
                $arr = $var_arr;
            }

            write_static_cache($file, $arr);
        } else {
            $arr = $data;
        }
    }

    return $arr;
}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */
/**
 * 获得指定品牌的详细信息
 *
 * @access  private
 * @param   integer $id
 * @return  void
 */
function get_brand_info($id_name, $act = '', $selType = 0)
{
    if ($act == 'merchants_brands') {
        $select = "bid as brand_id, brandName as brand_name, bank_name_letter as brand_letter, brandLogo as brand_logo, brand_desc, user_id";
        $idType = "bid";
        $nameType = "brandName";
        $table = "merchants_shop_brand";
    } else {
        $select = "*";
        $idType = "brand_id";
        $nameType = "brand_name";
        $table = "brand";
    }

    $where = '1';
    if ($selType == 1) {
        $where = $nameType . " = '$id_name' AND audit_status = 1";
    } else {
        $where = $idType . " = '$id_name'";
    }

    $sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . " WHERE " . $where;

    return $GLOBALS['db']->getRow($sql);
}

//店铺列表
function get_common_store_list()
{
    $sql = "SELECT shop_id, user_id FROM " . $GLOBALS['ecs']->table('merchants_shop_information') . " WHERE merchants_audit = 1";
    $res = $GLOBALS['db']->getAll($sql);

    $arr = [];
    foreach ($res as $key => $row) {
        $arr[$key]['shop_id'] = $row['shop_id'];
        $arr[$key]['ru_id'] = $row['user_id'];
        $arr[$key]['store_name'] = get_shop_name($row['user_id'], 1); //店铺名称
    }

    return $arr;
}

//获取当前位置区域
function get_current_region_list($province_id = 1, $region_type = 1)
{
    $where = " AND region_type = '$region_type'";

    $sql = "SELECT region_id, region_name FROM " . $GLOBALS['ecs']->table('region') . " WHERE parent_id = '$province_id' $where";
    return $GLOBALS['db']->getAll($sql);
}

function get_seller_shopinfo($ru_id)
{
    $sql = "select province, city, kf_type, kf_ww, kf_qq, shop_name from " . $GLOBALS['ecs']->table('seller_shopinfo') . " where ru_id='$ru_id'";
    return $GLOBALS['db']->getRow($sql);
}

//供货商家名称
function get_suppliers_name($suppliers_id = 0)
{
    $sql = "SELECT suppliers_id, suppliers_name FROM " . $GLOBALS['ecs']->table('suppliers') . " WHERE suppliers_id = '$suppliers_id'";
    return $GLOBALS['db']->getRow($sql);
}

//查询商品上架下架时间
function get_auto_manage_info($goods_id, $type = '')
{
    $sql = "SELECT starttime, endtime FROM " . $GLOBALS['ecs']->table('auto_manage') . " WHERE type = '$type' AND item_id = '$goods_id'";
    return $GLOBALS['db']->getRow($sql);
}

//查询会员购买商品订单信息
/*
 * 购买商品的属性
 * 购买时间
 */
function get_user_buy_goods_order($goods_id, $user_id, $order_id)
{
    $sql = "SELECT og.goods_attr_id, oi.add_time FROM " . $GLOBALS['ecs']->table('order_goods') . " AS og, " . $GLOBALS['ecs']->table('order_info') . " AS oi" .
        " WHERE og.order_id = oi.order_id AND oi.user_id = '$user_id' AND og.goods_id = '$goods_id' AND oi.order_id = '$order_id' limit 0,1";
    $buy_goods = $GLOBALS['db']->getRow($sql);

    $buy_goods['goods_attr'] = get_goods_attr_order($buy_goods['goods_attr_id']);
    $buy_goods['add_time'] = !empty($buy_goods['add_time']) ? local_date($GLOBALS['_CFG']['time_format'], $buy_goods['add_time']) : '';

    return $buy_goods;
}

//查询属性名称
function get_goods_attr_order($goods_attr_id)
{
    if ($goods_attr_id) {
        $attr = '';

        if (!empty($goods_attr_id)) {
            $fmt = "%s：%s <br/>";

            $sql = "SELECT ga.goods_attr_id, a.attr_name, ga.attr_value " .
                "FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS ga " .
                " left join " . $GLOBALS['ecs']->table('attribute') . " AS a " . "on a.attr_id = ga.attr_id " .
                "WHERE " . db_create_in($goods_attr_id, 'ga.goods_attr_id');

            $res = $GLOBALS['db']->query($sql);
            foreach ($res as $row) {
                $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], '');
            }

            $attr = str_replace('[0]', '', $attr);
        }

        return $attr;
    }
}

//查询会员回复信息列表
function get_reply_list($goods_id, $comment_id, $type = 0, $reply_page = 1, $libType = 0, $reply_size = 2)
{
    if ($type == 1) {
        $sql = "SELECT c.user_id, c.content, c.add_time, c.user_name FROM " . $GLOBALS['ecs']->table('comment') . " AS c " .
            " WHERE c.id_value = '$goods_id' AND c.parent_id = '$comment_id' AND c.user_id = '" . $_SESSION['user_id'] . "' AND status = 0 ORDER BY c.comment_id DESC";

        $reply_list = $GLOBALS['db']->getAll($sql);
    } else {
        require_once('includes/cls_pager.php');
        $sql = "SELECT count(*) FROM " . $GLOBALS['ecs']->table('comment') . " AS c " .
            " WHERE c.id_value = '$goods_id' AND c.parent_id = '$comment_id' AND c.user_id > 0 AND status = 1";
        $reply_count = $GLOBALS['db']->getOne($sql);

        $id = '"' . $goods_id . "|" . $comment_id . '"';

        $reply_comment = new Pager($reply_count, $reply_size, '', $id, 0, $reply_page, 'reply_comment_gotoPage', 1, $libType, 1);
        $limit = $reply_comment->limit;
        $reply_pager = $reply_comment->fpage([0, 4, 5, 6, 9]);

        //楼层编号
        $setFloorMax = $reply_comment->setFloorMax;

        if ($setFloorMax > $reply_size) {
            $setFloorMax += 1;
        } else {
            $setFloorMax = $reply_comment->pageCurrent + 1;
        }

        $sql = "SELECT @rownum:=@rownum-1 AS floor, c.user_id, c.content, c.add_time, c.user_name FROM (SELECT @rownum:=$setFloorMax) r, " . $GLOBALS['ecs']->table('comment') . " AS c " .
            " WHERE c.id_value = '$goods_id' AND c.parent_id = '$comment_id' AND c.user_id > 0 AND status = 1 ORDER BY c.comment_id DESC " . $limit;
        $reply_list = $GLOBALS['db']->getAll($sql);

        foreach ($reply_list as $key => $row) {
            $reply_list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            $reply_list[$key]['content'] = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
        }
    }

    $arr = ['reply_list' => $reply_list, 'reply_pager' => $reply_pager, 'reply_count' => $reply_count, 'reply_size' => $reply_size];

    return $arr;
}

//讨论圈信息列表
function get_discuss_all_list($goods_id = 0, $dis_type = 0, $reply_page = 1, $size = 40, $revType = 0, $sort = 'add_time', $did = 0)
{
    require_once('includes/cls_pager.php');

    $where = "1";
    if ($dis_type == 4) {//晒单贴

        $sql = "SELECT count(*) FROM " . $GLOBALS['ecs']->table('comment') . " AS cmt " .
            "LEFT JOIN (SELECT comment_id,goods_id,comment_img FROM " . $GLOBALS['ecs']->table('comment_img') . " GROUP BY comment_id) cmt2 ON (cmt2.comment_id = cmt.comment_id) " .
            "WHERE cmt.id_value = '$goods_id' AND cmt2.comment_img != ''";
        $record_count = $GLOBALS['db']->getOne($sql);

        $discuss = new Pager($record_count, $size, '', $goods_id, 0, $reply_page, 'discuss_list_gotoPage', 0);
        $limit = $discuss->limit;
        $pager = $discuss->fpage([0, 4, 5, 6, 9]);

        $sql = "SELECT cmt.comment_id AS dis_id,cmt.id_value,cmt.useful,cmt.content,cmt.add_time,cmt.user_name,cmt2.comment_img FROM " . $GLOBALS['ecs']->table('comment') . " AS cmt " .
            "LEFT JOIN (SELECT comment_id,goods_id,comment_img FROM " . $GLOBALS['ecs']->table('comment_img') . " GROUP BY comment_id) cmt2 ON (cmt2.comment_id = cmt.comment_id) " .
            "WHERE cmt.id_value = '$goods_id' AND cmt2.comment_img != '' AND cmt.comment_id <> '$did' " . $limit;
        $res = $GLOBALS['db']->getAll($sql);

        $arr = [];
        foreach ($res as $key => $row) {
            $arr[$key] = $row;
            $arr[$key]['dis_title'] = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
            $arr[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            $arr[$key]['reply_num'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('discuss_circle') . "WHERE parent_id = '" . $row['dis_id'] . "'");
            $arr[$key]['dis_browse_num'] = $row['useful'];
            $arr[$key]['dis_type'] = 4;
        }
    } else {
        if ($dis_type > 0) {
            $where .= " AND DC1.dis_type = '$dis_type'";
        }

        $id = '"' . $goods_id . "|" . $dis_type . "|" . $revType . "|" . $sort . '"';

        $record_count = get_discuss_type_count($goods_id, $dis_type);

        $discuss = new Pager($record_count, $size, '', $id, 0, $reply_page, 'discuss_list_gotoPage', 1);
        $limit = $discuss->limit;
        $pager = $discuss->fpage([0, 4, 5, 6, 9]);

        if ($sort != 'reply_num') {
            $sort = "DC1." . $sort;
        }

        $sql = "SELECT DC1.dis_id, DC1.dis_type, DC1.dis_title, DC1.user_name, DC1.add_time, DC1.dis_browse_num, " .
            "(SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('discuss_circle') . " AS DC2" . " WHERE DC2.parent_id = DC1.dis_id) AS reply_num" .
            " FROM " . $GLOBALS['ecs']->table('discuss_circle') . " AS DC1" . " WHERE $where AND DC1.parent_id = 0 AND DC1.goods_id = '$goods_id' AND DC1.dis_id <> '$did' ORDER BY $sort DESC " . $limit;
        $res = $GLOBALS['db']->getAll($sql);

        $arr = [];
        foreach ($res as $key => $row) {
            $arr[$key] = $row;
            $arr[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            $arr[$key]['reply_num'] = $row['reply_num'];
        }
    }

    return ['list' => $arr, 'pager' => $pager, 'record_count' => $record_count];
}

//论坛信息数量
function get_discuss_type_count($goods_id, $dis_type = 0)
{
    $where = "1";
    if ($dis_type > 0) {
        $where .= " AND dis_type = '$dis_type'";
    }

    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('discuss_circle') . " WHERE $where AND parent_id = 0 AND goods_id = '$goods_id'";
    return $GLOBALS['db']->getOne($sql);
}

/**
 * 晒单贴有图数量
 * @param type $goods_id
 * @return type
 */
function get_commentImg_count($goods_id)
{
    $sql = "SELECT count(*) FROM " . $GLOBALS['ecs']->table('comment') . " AS cmt " .
        "LEFT JOIN (SELECT comment_id,goods_id,comment_img FROM " . $GLOBALS['ecs']->table('comment_img') . " GROUP BY comment_id) cmt2 ON (cmt2.comment_id = cmt.comment_id) " .
        "WHERE cmt.id_value = '$goods_id' AND cmt2.comment_img != ''";
    $num = $GLOBALS['db']->getOne($sql);
    return $num;
}

/**
 * 调用浏览历史 //ecmoban模板堂 --zhuo
 *
 * @access  public
 * @return  string
 */
function get_history_goods($goods_id = 0, $warehouse_id = 0, $area_id = 0)
{
    $arr = [];
    if (!empty($_COOKIE['ECS']['history'])) {
        $where = db_create_in($_COOKIE['ECS']['history'], 'g.goods_id');
        if ($GLOBALS['_CFG']['review_goods'] == 1) {
            $where .= ' AND g.review_status > 2 ';
        }
        $leftJoin = '';

        $shop_price = "wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ";
        $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_goods') . " as wg on g.goods_id = wg.goods_id and wg.region_id = '$warehouse_id' ";
        $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_area_goods') . " as wag on g.goods_id = wag.goods_id and wag.region_id = '$area_id' ";

        if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
            $leftJoin .= " left join " . $GLOBALS['ecs']->table('link_area_goods') . " as lag on g.goods_id = lag.goods_id ";
            $where .= " and lag.region_id = '$area_id' ";
        }

        if ($goods_id > 0) {
            $where .= " AND g.goods_id <> '$goods_id' ";
        }

        $sql = 'SELECT g.goods_id, g.user_id, g.goods_name, g.goods_thumb, g.goods_img, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' .
            "IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * '$_SESSION[discount]'), g.shop_price * '$_SESSION[discount]')  AS shop_price, " .
            'g.market_price, g.sales_volume, ' .
            'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' .
            'g.promote_start_date, g.promote_end_date' .
            ' FROM ' . $GLOBALS['ecs']->table('goods') . " as g " .
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            $leftJoin .
            " WHERE $where AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 order by INSTR('" . $_COOKIE['ECS']['history'] . "',g.goods_id) limit 0,10";

        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $row) {
            if ($row['promote_price'] > 0) {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }
            /**
             * 重定义商品价格
             * 商品价格 + 属性价格
             * start
             */
            $price_other = [
                'market_price' => $row['market_price'],
                'org_price' => $row['org_price'],
                'shop_price' => $row['shop_price'],
                'promote_price' => $promote_price,
            ];

            $price_info = get_goods_one_attr_price($row['goods_id'], $warehouse_id, $area_id, $price_other);
            $row = !empty($row) ? array_merge($row, $price_info) : $row;
            $promote_price = $row['promote_price'];
            /**
             * 重定义商品价格
             * end
             */
            $arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
            $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
            $arr[$row['goods_id']]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
            $arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_img']);
            $arr[$row['goods_id']]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
            $arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
            $arr[$row['goods_id']]['shop_name'] = get_shop_name($row['user_id'], 1); //店铺名称
            $arr[$row['goods_id']]['shopUrl'] = build_uri('merchants_store', ['urid' => $row['user_id']]);

            $arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
            $arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
            $arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
        }
    }

    return $arr;
}

/**
 * 随机生成用户名
 * @param   int $user_id 用户编号
 * @return  string  唯一的编号
 */
function generate_user_sn($user_id)
{
    $user_sn = "SC" . str_repeat('0', 6 - strlen($user_id)) . $user_id;

    $sql = "SELECT user_name FROM " . $GLOBALS['ecs']->table('users') .
        " WHERE user_name LIKE '" . mysql_like_quote($user_id) . "%' AND user_id <> '$user_id' " .
        " ORDER BY LENGTH(user_name) DESC";
    $sn_list = $GLOBALS['db']->getCol($sql);
    if (in_array($user_sn, $sn_list)) {
        $max = pow(10, strlen($sn_list[0]) - strlen($user_sn) + 1) - 1;
        $new_sn = $user_sn . mt_rand(0, $max);
        while (in_array($new_sn, $sn_list)) {
            $new_sn = $user_sn . mt_rand(0, $max);
        }
        $user_sn = $new_sn;
    }

    return $user_sn;
}


/**
 * 获得指定分类下的子分类的数组
 *
 * @access  public
 * @param   int $cat_id 分类的ID
 * @param   int $selected 当前选中分类的ID
 * @param   boolean $re_type 返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int $level 限定返回的级数。为0时返回所有级数
 * @param   int $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return  mix
 */
function presale_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
{
    static $res = null;

    if ($res === null) {
        $data = read_static_cache('presale_cat_releate');
        if ($data === false) {
            $sql = "SELECT p.cid, p.c_name,p.parent_cid, p.sort_order, COUNT(s.cid) AS has_children " .
                'FROM ' . $GLOBALS['ecs']->table('presale_cat') . " AS p " .
                "LEFT JOIN " . $GLOBALS['ecs']->table('presale_cat') . " AS s ON s.parent_cid=p.cid " .
                "GROUP BY p.cid " .
                'ORDER BY p.parent_cid, p.sort_order ASC';
            $res = $GLOBALS['db']->getAll($sql);

            $sql = "SELECT pa.cid, COUNT(*) AS goods_num " .
                "FROM " . $GLOBALS['ecs']->table('goods') . " AS g " .
                "LEFT JOIN " . $GLOBALS['ecs']->table('presale_activity') . " AS pa ON pa.goods_id = g.goods_id " .
                " WHERE is_delete = 0 AND is_on_sale = 0 AND pa.review_status = 3 " .
                " GROUP BY pa.cid";
            $res2 = $GLOBALS['db']->getAll($sql);

            $sql = "SELECT pc.cid, COUNT(*) AS goods_num FROM " . $GLOBALS['ecs']->table('presale_cat') . " AS pc " .
                "LEFT JOIN " . $GLOBALS['ecs']->table('presale_activity') . " AS pa ON pc.cid = pa.cid " .
                "LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON g.goods_id = pa.goods_id " .
                "WHERE g.is_delete = 0 AND g.is_on_sale = 0 AND pa.review_status = 3 GROUP BY pa.cid";
            $res3 = $GLOBALS['db']->getAll($sql);

            $newres = [];
            foreach ($res2 as $k => $v) {
                $newres[$v['cid']] = $v['goods_num'];
                foreach ($res3 as $ks => $vs) {
                    if ($v['cid'] == $vs['cid']) {
                        $newres[$v['cid']] = $v['goods_num'] + $vs['goods_num'];
                    }
                }
            }

            foreach ($res as $k => $v) {
                $res[$k]['goods_num'] = !empty($newres[$v['cid']]) ? $newres[$v['cid']] : 0;
            }
            //如果数组过大，不采用静态缓存方式
            if (count($res) <= 1000) {
                write_static_cache('presale_cat_releate', $res);
            }
        } else {
            $res = $data;
        }
    }

    if (empty($res) == true) {
        return $re_type ? '' : [];
    }

    $options = presale_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

    $children_level = 99999; //大于这个分类的将被删除
    if ($is_show_all == false) {
        foreach ($options as $key => $val) {
            if ($val['level'] > $children_level) {
                unset($options[$key]);
            } else {
                if ($val['is_show'] == 0) {
                    unset($options[$key]);
                    if ($children_level > $val['level']) {
                        $children_level = $val['level']; //标记一下，这样子分类也能删除
                    }
                } else {
                    $children_level = 99999; //恢复初始值
                }
            }
        }
    }

    /* 截取到指定的缩减级别 */
    if ($level > 0) {
        if ($cat_id == 0) {
            $end_level = $level;
        } else {
            $first_item = reset($options); // 获取第一个元素
            $end_level = $first_item['level'] + $level;
        }

        /* 保留level小于end_level的部分 */
        foreach ($options as $key => $val) {
            if ($val['level'] >= $end_level) {
                unset($options[$key]);
            }
        }
    }

    if ($re_type == true) {
        $select = '';
        foreach ($options as $var) {
            $select .= '<option value="' . $var['cid'] . '" ';
            $select .= ($selected == $var['cid']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0) {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['c_name']), ENT_QUOTES) . '</option>';
        }

        return $select;
    } else {
        foreach ($options as $key => $value) {
            //$options[$key]['url'] = build_uri('category', array('cid' => $value['cid']), $value['c_name']);
        }

        return $options;
    }
}

/**
 * 过滤和排序所有分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param   int $cat_id 上级分类ID
 * @param   array $arr 含有所有分类的数组
 * @param   int $level 级别
 * @return  void
 */
function presale_cat_options($spec_cat_id, $arr)
{
    static $cat_options = [];

    if (isset($cat_options[$spec_cat_id])) {
        return $cat_options[$spec_cat_id];
    }

    if (!isset($cat_options[0])) {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = [];
        $data = read_static_cache('presale_cat_option_static');
        if ($data === false) {
            while (!empty($arr)) {
                foreach ($arr as $key => $value) {
                    $cat_id = $value['cid'];
                    if ($level == 0 && $last_cat_id == 0) {
                        if ($value['parent_cid'] > 0) {
                            break;
                        }

                        $options[$cat_id] = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id'] = $cat_id;
                        $options[$cat_id]['name'] = $value['c_name'];
                        unset($arr[$key]);

                        if ($value['has_children'] == 0) {
                            continue;
                        }
                        $last_cat_id = $cat_id;
                        $cat_id_array = [$cat_id];
                        $level_array[$last_cat_id] = ++$level;
                        continue;
                    }

                    if ($value['parent_cid'] == $last_cat_id) {
                        $options[$cat_id] = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id'] = $cat_id;
                        $options[$cat_id]['name'] = $value['c_name'];
                        unset($arr[$key]);

                        if ($value['has_children'] > 0) {
                            if (end($cat_id_array) != $last_cat_id) {
                                $cat_id_array[] = $last_cat_id;
                            }
                            $last_cat_id = $cat_id;
                            $cat_id_array[] = $cat_id;
                            $level_array[$last_cat_id] = ++$level;
                        }
                    } elseif ($value['parent_cid'] > $last_cat_id) {
                        break;
                    }
                }

                $count = count($cat_id_array);
                if ($count > 1) {
                    $last_cat_id = array_pop($cat_id_array);
                } elseif ($count == 1) {
                    if ($last_cat_id != end($cat_id_array)) {
                        $last_cat_id = end($cat_id_array);
                    } else {
                        $level = 0;
                        $last_cat_id = 0;
                        $cat_id_array = [];
                        continue;
                    }
                }

                if ($last_cat_id && isset($level_array[$last_cat_id])) {
                    $level = $level_array[$last_cat_id];
                } else {
                    $level = 0;
                }
            }
            //如果数组过大，不采用静态缓存方式
            if (count($options) <= 2000) {
                write_static_cache('presale_cat_option_static', $options);
            }
        } else {
            $options = $data;
        }
        $cat_options[0] = $options;
    } else {
        $options = $cat_options[0];
    }

    if (!$spec_cat_id) {
        return $options;
    } else {
        if (empty($options[$spec_cat_id])) {
            return [];
        }

        $spec_cat_id_level = $options[$spec_cat_id]['level'];

        foreach ($options as $key => $value) {
            if ($key != $spec_cat_id) {
                unset($options[$key]);
            } else {
                break;
            }
        }

        $spec_cat_id_array = [];
        foreach ($options as $key => $value) {
            if (($spec_cat_id_level == $value['level'] && $value['cid'] != $spec_cat_id) ||
                ($spec_cat_id_level > $value['level'])
            ) {
                break;
            } else {
                $spec_cat_id_array[$key] = $value;
            }
        }
        $cat_options[$spec_cat_id] = $spec_cat_id_array;

        return $spec_cat_id_array;
    }
}

/**
 * 取得预售活动信息
 * @param   int $presale_id 预售活动id
 * @param   int $current_num 本次购买数量（计算当前价时要加上的数量）
 * @return  array
 * status          状态：
 */
function presale_info($presale_id, $current_num = 0, $user_id = 0)
{
    /* 取得预售活动信息 */
    $presale_id = intval($presale_id);
    $sql = "SELECT b.*, b.cat_id AS pa_catid,g.goods_name, g.shop_price, g.user_id, g.goods_id, g.goods_product_tag, g.cat_id, " .
        "g.xiangou_start_date, g.xiangou_end_date, g.xiangou_end_date, g.xiangou_num, g.is_xiangou " .
        "FROM " . $GLOBALS['ecs']->table('presale_activity') . " AS b " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON b.goods_id = g.goods_id " .
        "WHERE act_id = '$presale_id' AND b.review_status = 3 ";
    $presale = $GLOBALS['db']->getRow($sql);

    /* 如果为空，返回空数组 */
    if (empty($presale)) {
        return [];
    }

    /* 格式化时间 */
    $presale['formated_start_date'] = local_date('Y-m-d H:i', $presale['start_time']);
    $presale['formated_end_date'] = local_date('Y-m-d H:i', $presale['end_time']);
    $presale['formated_pay_start_date'] = local_date('Y-m-d H:i', $presale['pay_start_time']);
    $presale['formated_pay_end_date'] = local_date('Y-m-d H:i', $presale['pay_end_time']);
    /* 格式化保证金 */
    $presale['formated_deposit'] = price_format($presale['deposit'], false);
    /* 尾款 */
    $presale['final_payment'] = $presale['shop_price'] - $presale['deposit'];
    $presale['formated_final_payment'] = price_format($presale['final_payment'], false);
    /* 统计信息 */
    $stat = presale_stat($presale_id, $presale['deposit'], $user_id);
    $presale = array_merge($presale, $stat);

    /* 状态 */
    $presale['status'] = presale_status($presale);
    if (isset($GLOBALS['_LANG']['gbs'][$presale['status']])) {
        $presale['status_desc'] = $GLOBALS['_LANG']['gbs'][$presale['status']];
    }

    $presale['start_time'] = $presale['formated_start_date'];
    $presale['end_time'] = $presale['formated_end_date'];
    $presale['pay_start_time'] = $presale['formated_pay_start_date'];
    $presale['pay_end_time'] = $presale['formated_pay_end_date'];

    //买家印象
    if ($presale['goods_product_tag']) {
        $impression_list = !empty($presale['goods_product_tag']) ? explode(',', $presale['goods_product_tag']) : '';
        foreach ($impression_list as $kk => $vv) {
            $tag[$kk]['txt'] = $vv;
            //印象数量
            $tag[$kk]['num'] = comment_goodstag_num($presale['goods_id'], $vv);
        }
        $presale['impression_list'] = $tag;
    }
    $presale['collect_count'] = get_collect_goods_user_count($presale['goods_id']);
    return $presale;
}

/*
 * 取得某预售活动统计信息
 * @param   int     $group_buy_id   预售活动id
 * @param   float   $deposit        保证金
 * @return  array   统计信息
 *                  total_order     总订单数
 *                  total_goods     总商品数
 *                  valid_order     有效订单数
 *                  valid_goods     有效商品数
 */
function presale_stat($presale_id, $deposit)
{
    $presale_id = intval($presale_id);

    /* 取得预售活动商品ID */
    $sql = "SELECT goods_id " .
        "FROM " . $GLOBALS['ecs']->table('presale_activity') .
        "WHERE act_id = '$presale_id' AND review_status = 3 ";
    $goods_id = $GLOBALS['db']->getOne($sql);

    /* 取得总订单数和总商品数 */
    $sql = "SELECT COUNT(*) AS total_order, SUM(g.goods_number) AS total_goods " .
        "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o, " .
        $GLOBALS['ecs']->table('order_goods') . " AS g " .
        " WHERE o.order_id = g.order_id " .
        "AND o.extension_code = 'presale' " .
        "AND o.extension_id = '$presale_id' " .
        "AND g.goods_id = '$goods_id' " .
        "AND (order_status = '" . OS_CONFIRMED . "' OR order_status = '" . OS_UNCONFIRMED . "')";
    $stat = $GLOBALS['db']->getRow($sql);
    if ($stat['total_order'] == 0) {
        $stat['total_goods'] = 0;
    }

    /* 取得有效订单数和有效商品数 */
    $deposit = floatval($deposit);
    if ($deposit > 0 && $stat['total_order'] > 0) {
        $sql .= " AND (o.money_paid + o.surplus) >= '$deposit'";
        $row = $GLOBALS['db']->getRow($sql);
        $stat['valid_order'] = $row['total_order'];
        if ($stat['valid_order'] == 0) {
            $stat['valid_goods'] = 0;
        } else {
            $stat['valid_goods'] = $row['total_goods'];
        }
    } else {
        $stat['valid_order'] = $stat['total_order'];
        $stat['valid_goods'] = $stat['total_goods'];
    }

    return $stat;
}

/**
 * 获得预售的状态
 *
 * @access  public
 * @param   array
 * @return  integer
 */
function presale_status($presale)
{
    $now = gmtime();
    if ($presale['is_finished'] == 0) {
        /* 未处理 */
        if ($now < $presale['start_time']) {
            $status = GBS_PRE_START;
        } elseif ($now > $presale['end_time']) {
            $status = GBS_FINISHED;
        } else {
            if ($presale['is_finished'] == 0) {
                $status = GBS_UNDER_WAY;
            } else {
                $status = GBS_FINISHED;
            }
        }
    } elseif ($presale['is_finished'] == GBS_SUCCEED) {
        /* 已处理，预售成功 */
        $status = GBS_SUCCEED;
    } elseif ($presale['is_finished'] == GBS_FAIL) {
        /* 已处理，预售失败 */
        $status = GBS_FAIL;
    }

    return $status;
}

//查询购买过的商品列表
function get_order_goods_buy_list($warehouse_id = 0, $area_id = 0)
{
    $where = '1';
    $leftJoin = '';

    $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_goods') . " as wg on g.goods_id = wg.goods_id and wg.region_id = '$warehouse_id' ";
    $leftJoin .= " left join " . $GLOBALS['ecs']->table('warehouse_area_goods') . " as wag on g.goods_id = wag.goods_id and wag.region_id = '$area_id' ";

    $where .= " AND (select count(*) from " . $GLOBALS['ecs']->table('order_info') . " as oi2 where oi2.main_order_id = oi.order_id) = 0 ";  //主订单下有子订单时，则主订单不显示
    $sql = "SELECT g.goods_id, g.goods_name, g.user_id AS ru_id, g.sales_volume, g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img, " .
        "IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) as goods_number, " .
        "IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * '$_SESSION[discount]'), g.shop_price * '$_SESSION[discount]')  AS shop_price, " .
        "IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price," .
        "IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * '$_SESSION[discount]') AS rank_price " .
        "FROM " . $GLOBALS['ecs']->table('order_goods') . " AS og " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON og.goods_id = g.goods_id " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('order_info') . " AS oi ON og.order_id = oi.order_id " .
        " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
        "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]'" .
        $leftJoin .
        " WHERE $where AND oi.user_id = '" . $_SESSION['user_id'] . "' GROUP BY og.goods_id ORDER BY g.sales_volume DESC LIMIT 0,18";
    $res = $GLOBALS['db']->getAll($sql);

    $arr = [];
    foreach ($res as $key => $row) {
        $arr[$key]['goods_id'] = $row['goods_id'];
        $arr[$key]['goods_name'] = $row['goods_name'];

        /* 修正商品图片 */
        $arr[$key]['goods_img'] = get_image_path($row['goods_img']);
        $arr[$key]['goods_thumb'] = get_image_path($row['goods_thumb']);

        /* 修正促销价格 */
        if ($row['promote_price'] > 0) {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        } else {
            $promote_price = 0;
        }

        $arr[$key]['sales_volume'] = $row['sales_volume'];
        $arr[$key]['shop_price'] = price_format($row['shop_price']);
        $arr[$key]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
        $arr[$key]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
        $arr[$key]['shop_name'] = get_shop_name($row['ru_id'], 1); //店铺名称
        $arr[$key]['store_url'] = build_uri('merchants_store', ['urid' => $row['ru_id']], $arr[$key]['shop_name']);
    }

    return $arr;
}

/**
 * 猜你你喜欢---从订单商品中获取该分类的其他商品
 * @param type $user_id
 * @param type $history 1 从浏览记录中获取
 */
function get_guess_goods($user_id, $history = 0, $page = 1, $limit = 5)
{
    $order_idArr = $finished_goods = $link_cats = [];
    $start = (($page > 1) ? ($page - 1) : 0) * $limit;

    if (empty($history)) {//用户中心
        $sql = "SELECT order_id FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE user_id = '$user_id' ORDER BY order_id DESC LIMIT 5";
        $order_arr = $GLOBALS['db']->getAll($sql);

        if ($order_arr) {
            foreach ($order_arr as $key => $val) {
                $order_idArr[] = $val['order_id'];
            }
            $order_str = db_create_in($order_idArr, "og.order_id");

            //分类
            $sql = "SELECT g.goods_id, g.cat_id FROM " . $GLOBALS['ecs']->table('order_goods') . " AS og " .
                "LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON g.goods_id = og.goods_id " .
                "WHERE $order_str GROUP BY g.goods_id DESC";
            $cat_All = $GLOBALS['db']->getAll($sql);

            foreach ($cat_All as $kk => $vv) {
                $finished_goodsStr .= "'" . $vv['goods_id'] . "',";
                $link_cats[] = $vv['cat_id'];
            }
            $finished_goodsStr = substr($finished_goodsStr, 0, -1);
            $link_cats = array_unique($link_cats);
            $link_cats_str = db_create_in($link_cats, "cat_id");

            $sql = 'SELECT goods_id, goods_name, goods_thumb, shop_price,sales_volume FROM ' . $GLOBALS['ecs']->table('goods') .
                " WHERE $link_cats_str AND goods_id NOT IN ($finished_goodsStr) AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 ORDER BY sales_volume DESC  LIMIT 8";
            $query = $GLOBALS['db']->query($sql);
        }
    } else {
        //商品详情页
        if (!empty($_COOKIE['ECS']['history'])) {
            $where = db_create_in($_COOKIE['ECS']['history'], 'goods_id');
            $sql = "SELECT cat_id,goods_id FROM " . $GLOBALS['ecs']->table('goods') . " WHERE $where AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0";
            $query = $GLOBALS['db']->query($sql);
            $goods_str = '';
            foreach ($query as $row) {
                $cat_arr[] = $row['cat_id'];
                $goods_str .= "'" . $row['goods_id'] . "',";
            }
            //历史商品、分类
            $goods_str = substr($goods_str, 0, -1);
            $where_cat = db_create_in(array_unique($cat_arr), "cat_id");

            if (!empty($goods_str)) {
                $goods_str = "AND goods_id NOT IN ($goods_str)";
            }

            $sql = "SELECT goods_id, goods_name, goods_thumb, shop_price,sales_volume,user_id FROM " . $GLOBALS['ecs']->table('goods') .
                " WHERE $where_cat $goods_str AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 AND sales_volume > 0 LIMIT $start, $limit";
            $query = $GLOBALS['db']->query($sql);
        }
    }
    //默认
    if ((empty($guess_goods) || count($guess_goods) < $limit) && $history == 1) {
        $guess_goods = [];
        $sql = "SELECT goods_id, goods_name, goods_thumb, shop_price, user_id, sales_volume FROM " . $GLOBALS['ecs']->table('goods') .
            " WHERE  (sales_volume > 0 OR is_hot = 1) AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 ORDER BY sales_volume DESC LIMIT $start, $limit";
        $query = $GLOBALS['db']->query($sql);
    }
    foreach ($query as $row) {
        $guess_goods[$row['goods_id']]['goods_id'] = $row['goods_id'];
        $guess_goods[$row['goods_id']]['goods_name'] = $row['goods_name'];
        $guess_goods[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
        $guess_goods[$row['goods_id']]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $guess_goods[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
        $guess_goods[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
        $guess_goods[$row['goods_id']]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

        $guess_goods[$row['goods_id']]['shop_name'] = get_shop_name($row['user_id'], 1);
        $guess_goods[$row['goods_id']]['shopUrl'] = build_uri('merchants_store', ['urid' => $row['user_id']]);
        //好评率
        $sql = "SELECT AVG(comment_rank) FROM " . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '" . $row['goods_id'] . "'";
        $comment_rank = $GLOBALS['db']->getOne($sql);

        if ($comment_rank) {
            $guess_goods[$row['goods_id']]['comment_percent'] = round(($comment_rank / 5) * 100, 1);
        } else {
            $guess_goods[$row['goods_id']]['comment_percent'] = 100;
        }
    }

    return $guess_goods;
}

/**
 * 猜你喜欢的店铺
 * @param type $user_id
 * @param type $limit
 */
function get_guess_store($user_id, $limit)
{
    $store_list = [];
    if ($user_id) {
        $sql = "SELECT ru_id FROM " . $GLOBALS['ecs']->table('collect_store') . " WHERE user_id = '$user_id' LIMIT $limit";
        $ruId = $GLOBALS['db']->getAll($sql);

        foreach ($ruId as $key => $val) {
            $sql = "SELECT ru_id,shop_name,street_thumb,brand_thumb FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . "  WHERE ru_id = '$val[ru_id]'";
            $store_list[$val['ru_id']] = $GLOBALS['db']->getRow($sql);
        }
    }
    if (empty($store_list) || count($store_list) < 2) {
        $store_list = [];
        $sql = "SELECT SUM(goods_number) AS total,ru_id FROM " . $GLOBALS['ecs']->table('order_goods') . " WHERE ru_id > 0 GROUP BY ru_id ORDER BY total DESC LIMIT $limit";
        $row = $GLOBALS['db']->getAll($sql);

        foreach ($row as $key => $val) {
            $sql = "SELECT ru_id,shop_name,street_thumb,brand_thumb FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . "  WHERE ru_id = '$val[ru_id]'";
            $shopinfo = $GLOBALS['db']->getRow($sql);

            $shopinfo['shop_name'] = get_shop_name($shopinfo['ru_id'], 1); //店铺名称
            $store_list[$val['ru_id']] = $shopinfo;
        }
    }
    return $store_list;
}

//查询商品评论数
function get_goods_comment_count($goods_id, $cmtType = 0)
{
    /* 取得评论列表 */
    if ($cmtType == 1) { //好评
        $where = " AND comment_rank in(5,4)";
    } elseif ($cmtType == 2) { //中评
        $where = " AND comment_rank in(3,2)";
    } elseif ($cmtType == 3) { //差评
        $where = " AND comment_rank = 1";
    } else {
        $where = "";
    }

    /* 取得评论列表 */
    $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') .
        " WHERE id_value = '$goods_id' AND comment_type = '0' AND status = 1 AND parent_id = 0 $where");

    return $count;
}

/**
 * 获取评价买家对商品印象词的个数
 * @param type $goods_id
 * @param type $txt --印象词
 */
function comment_goodstag_num($goods_id, $txt)
{
    $sql = "SELECT goods_tag FROM " . $GLOBALS['ecs']->table('comment') . " WHERE id_value = '$goods_id'";
    $res = $GLOBALS['db']->query($sql);

    $str = "";
    foreach ($res as $row) {
        if ($row['goods_tag']) {
            $str .= $row['goods_tag'] . ",";
        }
    }
    if ($str) {
        $str = substr($str, 0, -1);
        $num = substr_count($str, $txt);
    } else {
        $num = 0;
    }

    return $num;
}

//获取收藏商品的用户数量
function get_collect_goods_user_count($goods_id)
{
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('collect_goods') . " WHERE goods_id = '$goods_id'";
    return $GLOBALS['db']->getOne($sql);
}

//获取收藏商品的用户数量
function get_collect_user_goods($goods_id)
{
    $user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    if ($user_id) {
        $sql = "SELECT rec_id FROM " . $GLOBALS['ecs']->table('collect_goods') . " WHERE goods_id = '$goods_id' AND user_id = '$user_id'";
        $rec_id = $GLOBALS['db']->getOne($sql, true);
    } else {
        $rec_id = 0;
    }

    return $rec_id;
}

/**
 *  区域获得自提点
 * @param type $district
 */
function get_self_point($district, $point_id = 0, $limit = 100, $ru_id = 0){
    $where = "";
    $shipping_dateStr = isset($_SESSION['flow_consignee']['shipping_dateStr']) ? trim($_SESSION['flow_consignee']['shipping_dateStr']) : '';
    if ($point_id > 0) {
        $where = "sp.id = '$point_id'";
    } else {
        $where = "ar.region_id = '$district'";
    }
    //$where .= " AND sa.ru_id = '$ru_id'";商家门店自提预留

    $sql = "SELECT ar.shipping_area_id,ar.region_id ,sp.id as point_id,sp.name,sp.mobile,sp.address,sp.anchor,sa.shipping_id,ss.shipping_code,cr.parent_id as city FROM " . $GLOBALS['ecs']->table('shipping_point') . " AS sp " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('area_region') . " AS ar ON ar.shipping_area_id = sp.shipping_area_id " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('shipping_area') . " AS sa ON sa.shipping_area_id = sp.shipping_area_id " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('shipping') . " AS ss ON ss.shipping_id = sa.shipping_id " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS cr ON cr.region_id = ar.region_id " .
        "WHERE $where LIMIT $limit";

    $list = $GLOBALS['db']->getAll($sql);
    foreach ($list as $key => $val) {
        if ($point_id > 0 && $val['point_id'] == $point_id) {
            $list[$key]['is_check'] = 1;
        }
        if ($shipping_dateStr) {
            $list[$key]['shipping_dateStr'] = $shipping_dateStr;
        } else {
            $list[$key]['shipping_dateStr'] = date("m", strtotime(' +1day')) . "月" . date("d", strtotime(' +1day')) . "日&nbsp;【周" . transition_date(date('Y-m-d', strtotime(' +1day'))) . "】";
        }
    }

    return $list;
}

/*
* 功能：获取指定年月日是星期几
* 传参：年月日格式：2010-01-01的字符串
* 返回值：计算出来的星期值
*@author guan
*/

function transition_date($date)
{
    $arr_week = ["日", "一", "二", "三", "四", "五", "六"];
    $datearr = explode("-", $date);     //将传来的时间使用“-”分割成数组

    $year = $datearr[0];       //获取年份

    $month = sprintf('%02d', $datearr[1]);  //获取月份

    $day = sprintf('%02d', $datearr[2]);      //获取日期

    $hour = $minute = $second = 0;   //默认时分秒均为0

    $dayofweek = mktime($hour, $minute, $second, $month, $day, $year);    //将时间转换成时间戳

    $week = date("w", $dayofweek);      //获取星期值
    return $arr_week[$week];
}

//分单的订单发票税率
function get_order_invoice($goods_amount, $flow_type)
{
    /* 如果能开发票，取得发票内容列表 */
    if ((!isset($GLOBALS['_CFG']['can_invoice']) || $GLOBALS['_CFG']['can_invoice'] == '1')
        && isset($GLOBALS['_CFG']['invoice_content'])
        && trim($GLOBALS['_CFG']['invoice_content']) != '' && $flow_type != CART_EXCHANGE_GOODS
    ) {
        //默认发票计算
        $need_inv = 1;
        $inv_type = $GLOBALS['_CFG']['invoice_type']['type'][0];
    }

    /* 税额 */
    if (!empty($need_inv) && $inv_type != '') {
        $rate = 0;
        foreach ($GLOBALS['_CFG']['invoice_type']['type'] as $key => $type) {
            if ($type == $inv_type) {
                $rate = floatval($GLOBALS['_CFG']['invoice_type']['rate'][$key]) / 100;
            }
        }

        if ($rate > 0) {
            $tax = $rate * $goods_amount;
        }
    }

    return number_format($tax, 2, '.', '');
}

/**
 * 获取服务器端IP地址
 * @return string
 */
function get_server_ip()
{
    if (isset($_SERVER)) {
        if ($_SERVER['SERVER_ADDR']) {
            $server_ip = $_SERVER['SERVER_ADDR'];
        } else {
            $server_ip = $_SERVER['LOCAL_ADDR'];
        }
    } else {
        $server_ip = getenv('SERVER_ADDR');
    }
    return $server_ip;
}

function unique_arr($arr, $step = 0)
{
    $new = [];
    $u_arr = [];
    foreach ($arr as $k1 => $r1) {
        if (isset($r1['user_id'])) {
            $u_arr[] = $r1;
            array_push($new, $r1);
        }
    }

    if ($u_arr) {
        $new_arr = [];
        foreach ($u_arr as $k3 => $r3) {
            foreach ($arr as $k2 => $r2) {
                if ($r2['brand_id'] == $r3['brand_id']) {
                    unset($arr[$k2]);
                }
            }
        }
    }

    foreach ($arr as $r1) {
        $new[] = $r1;
    }

    if ($step > 0) {
        $new = array_slice($new, 0, $step);
    }

    return $new;
}

function get_shipping_code($shipping_id = 0)
{
    $sql = "SELECT shipping_id, shipping_code FROM " . $GLOBALS['ecs']->table('shipping') . " WHERE shipping_id = '$shipping_id'";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 订单账单记录
 */
function get_order_bill_log($other)
{
    $sql = "SELECT id FROM " . $GLOBALS['ecs']->table('seller_bill_order') . " WHERE bill_id = '" . $other['bill_id'] . "' AND order_id = '" . $other['order_id'] . "'";

    if ($GLOBALS['db']->getOne($sql, true)) {
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_bill_order'), $other, 'UPDATE', "bill_id = '" . $other['bill_id'] . "' AND order_id = '" . $other['order_id'] . "'");
    } else {
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_bill_order'), $other, 'INSERT');
    }

    $sql = "SELECT rec_id, order_id, goods_id, goods_price, goods_number, goods_attr, drp_money, commission_rate FROM " . $GLOBALS['ecs']->table('order_goods') . " WHERE order_id = '" . $other['order_id'] . "'";
    $goods_list = $GLOBALS['db']->getAll($sql);

    foreach ($goods_list as $key => $row) {

        $parent_id = $GLOBALS['db']->getOne("SELECT parent_id FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_id = '" . $row['order_id'] . "'", true);

        //商品金额促销 start
        $goods_amount = $row['goods_price'] * $row['goods_number'];
        $goods_con = get_con_goods_amount($goods_amount, $row['goods_id'], 0, 0, $parent_id);

        $amount = explode(',', $goods_con['amount']);
        $amount = min($amount);

        $row['dis_amount'] = $goods_amount - $amount;
        //商品金额促销 end

        $row['cat_id'] = $GLOBALS['db']->getOne("SELECT cat_id FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = '" . $row['goods_id'] . "'", true);
        $proportion = get_order_goods_commission($row['order_id'], 1);

        if ($proportion['cat']) {
            foreach ($proportion['cat'] as $gkey => $grow) {
                if ($row['goods_id'] == $gkey) {
                    $row['proportion'] = $grow['commission_rate'];
                    $row['cat_id'] = $grow['cat_id'];
                    break;
                }
            }
        }

        $row['commission_rate'] = !empty($row['commission_rate']) ? $row['commission_rate'] / 100 : 0;

        $goods = $row;

        $sql = "SELECT id FROM " . $GLOBALS['ecs']->table('seller_bill_goods') . " WHERE rec_id = '" . $row['rec_id'] . "' AND order_id = '" . $row['order_id'] . "'";

        if ($GLOBALS['db']->getOne($sql, true)) {
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_bill_goods'), $goods, 'UPDATE', "rec_id = '" . $row['rec_id'] . "' AND order_id = '" . $row['order_id'] . "'");
        } else {
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_bill_goods'), $goods, 'INSERT');
        }
    }
}

/**
 * 获取订单退款金额
 */
function get_order_return_amount($order_id = 0)
{
    $sql = "SELECT SUM(actual_return) AS return_amount FROM " . $GLOBALS['ecs']->table('order_return') . " WHERE order_id = '$order_id' AND return_type IN(1, 3) AND refound_status = 1";
    return $GLOBALS['db']->getOne($sql);
}


/**
 * 获取当前位置店铺
 */
function get_goods_user_area_position($ru_id = 0, $city_id = 0, $spec_arr = '', $goods_id = 0, $provinces_id = 0, $district_id = 0, $type = 0, $store_id = 0, $limit = 0)
{
    $where = "";
    if ($goods_id > 0) {
        $where .= "AND s.goods_id ='$goods_id'";
    }
    if ($provinces_id > 0) {
        $where .= " AND o.province = " . $provinces_id;
    }
    if ($city_id > 0) {
        $where .= " AND o.city = " . $city_id;
    }
    if ($district_id > 0) {
        $where .= " AND o.district = " . $district_id;
    }
    if ($store_id > 0) {
        $where .= " AND o.id = " . $store_id;
    } else {
        $where .= " AND o.ru_id = '$ru_id'";
    }

    if ($limit == 1) {
        $limit = " LIMIT 1";
    } else {
        $limit = '';
    }

    $sql = "SELECT o.id,s.goods_id,s.goods_number,o.ru_id,o.stores_name, o.province, o.city, o.district, o.stores_address, o.stores_tel, o.stores_opening_hours FROM "
        . $GLOBALS['ecs']->table('offline_store') . " AS o LEFT JOIN " . $GLOBALS['ecs']->table('store_goods') . " AS s ON o.id = s.store_id "
        . "WHERE  o.is_confirm=1 " . $where . " GROUP BY o.id $limit";
    $store_list = $GLOBALS['db']->getAll($sql);
    if ($store_list) {
        if ($spec_arr) {
            $is_spec = explode(',', $spec_arr);
        }

        foreach ($store_list as $key => $row) {
            if (is_spec($is_spec) == true) {
                $products = get_warehouse_id_attr_number($row['goods_id'], $spec_arr, $row['ru_id'], 0, 0, '', $row['id']); //获取属性库存
                $store_list[$key]['goods_number'] = $products['product_number'];

                if ($products['product_number'] == 0) {
                    unset($store_list[$key]);
                }
            }
            if ($type == 0) {
                $region = [
                    'province' => $row['province'],
                    'city' => $row['city'],
                    'district' => $row['district'],
                ];
                $store_list[$key]['area_info'] = get_area_region_info($region);
            }
        }
    }

    return $store_list;
}

/**
 * 获取地区信息
 */
function get_area_region_info($region)
{

    $where = "1";
    $left = '';
    $select = '';
    if (isset($region['province']) && $region['province']) {
        $where .= " AND p.region_id = '" . $region['province'] . "'";
    }

    if (isset($region['street']) && $region['street']) {

        $select .= ", ' ', IFNULL(d.region_name, ''), ' ', IFNULL(s.region_name, '')";

        $left .= $GLOBALS['ecs']->table('region') . " AS p, ";
        $left .= $GLOBALS['ecs']->table('region') . " AS t, ";
        $left .= $GLOBALS['ecs']->table('region') . " AS d, ";
        $left .= $GLOBALS['ecs']->table('region') . " AS s ";

        $where .= " AND t.region_id = '" . $region['city'] . "'";
        $where .= " AND d.region_id = '" . $region['district'] . "'";
        $where .= " AND s.region_id = '" . $region['street'] . "'";
    } else {
        if (isset($region['district']) && $region['district']) {

            $select .= ", ' ', IFNULL(t.region_name, ''), ' ', IFNULL(d.region_name, '')";

            $left .= $GLOBALS['ecs']->table('region') . " AS p, ";
            $left .= $GLOBALS['ecs']->table('region') . " AS t, ";
            $left .= $GLOBALS['ecs']->table('region') . " AS d ";

            $where .= " AND t.region_id = '" . $region['city'] . "'";
            $where .= " AND d.region_id = '" . $region['district'] . "'";
        } else {
            if (isset($region['city']) && $region['city']) {

                $select .= ", ' ', IFNULL(t.region_name, '')";


                $left .= $GLOBALS['ecs']->table('region') . " AS p, ";
                $left .= $GLOBALS['ecs']->table('region') . " AS t ";

                $where .= " AND t.region_id = '" . $region['city'] . "'";
            } else {
                $left .= $GLOBALS['ecs']->table('region') . " AS p ";
            }
        }
    }

    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(p.region_name, '') $select) AS region " .
        "FROM " . $left .
        "WHERE $where";

    return $GLOBALS['db']->getOne($sql);
}


/**
 * 注册送优惠券 bylu
 * @param $user_id 用户ID
 */
function register_coupons($user_id)
{
    //获取注册类型的优惠券信息;
    $res = get_coupons_type_info2(1);

    if (!empty($res)) {
        foreach ($res as $k => $v) {
            //获取当前的注册券已被发放的数量(防止发放数量超过设定发放数量)
            $num = $GLOBALS['db']->getOne(" SELECT COUNT(uc_id) FROM " . $GLOBALS['ecs']->table('coupons_user') . " WHERE cou_id='" . $v['cou_id'] . "'");
            if ($v['cou_total'] <= $num) {
                continue;
            }
            //注册送注册券
            $cou_id = $v['cou_id'];
            $uc_sn = $v['uc_sn'];
            $sql = "INSERT INTO " . $GLOBALS['ecs']->table('coupons_user') . " VALUES (NULL,'$user_id','$cou_id',0,$uc_sn,0,0)";
            $GLOBALS['db']->query($sql);
        }
    }
}

/* * *获取优惠券类型信息(不带分页) bylu
 * @param string $cou_type 优惠券类型 1:注册送,2:购物送,3:全场送,4:会员送  默认返回所有类型数据
 * @return array
 */

function get_coupons_type_info2($cou_type = '1,2,3,4')
{
    //获取格林尼治时间戳(用于判断优惠券是否已过期)
    $time = gmtime();
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('coupons') .
        "WHERE cou_type IN($cou_type) AND $time<cou_end_time ";

    $arr = $GLOBALS['db']->getAll($sql);
    //生成优惠券编号
    foreach ($arr as $k => $v) {
        $arr[$k]['uc_sn'] = $time . rand(10, 99);
    }
    return $arr;
}

/***
 * 获取用户拥有的优惠券 默认返回所有用户所拥有的优惠券; bylu
 * @param string $user_id 用户ID;
 * @param bool|false $is_use 找出当前用户可以使用的
 * @param bool|false $total 订单总价
 * @param bool|false $cart_goods 商品信息
 * @param bool|false $user 用于区分是否会员中心里取数据(会员中心里的优惠券不能分组)
 * @return mixed 优惠券数组
 */
function get_user_coupons_list($user_id = '', $is_use = false, $total = '', $cart_goods = false, $user = true, $cart_ru_id = -1, $act_type = 'user')
{

    $time = gmtime();

    $cart_where = '';
    if ($cart_ru_id > -1) {
        $cart_where .= " AND cu.is_use = 0";
    }

    //可使用的(平台用平台发的,商家用商家发的,当订单中混合了平台与商家的商品时,各自计算各自的商品总价是否达到各自发放的优惠券门槛,达到的话当前整个订单即可使用该优惠券)
    if ($is_use && isset($total) && $cart_goods) {

        foreach ($cart_goods as $k => $v) {
            $res[$v['ru_id']]['order_total'] += $v['goods_price'] * $v['goods_number'];
            $res[$v['ru_id']]['seller_id'] = $v['ru_id'];
            $res[$v['ru_id']]['goods_id'] .= $v['goods_id'] . ",";
            $res[$v['ru_id']]['cat_id'] .= $v['cat_id'] . ",";
            $res[$v['ru_id']]['goods'][$v['goods_id']] = $v;
        }

        $arr = [];
        $couarr = [];

        foreach ($res as $key => $row) {
            $row['goods_id'] = get_del_str_comma($row['goods_id']);
            $row['cat_id'] = get_del_str_comma($row['cat_id']);

            $cart_where .= " AND c.ru_id = '" . $row['seller_id'] . "'";

            $sql = "SELECT c.*, cu.uc_id FROM " . $GLOBALS['ecs']->table("coupons_user") . " AS cu " .
                " LEFT JOIN " . $GLOBALS['ecs']->table("coupons") . " AS c ON cu.cou_id = c.cou_id " .
                " WHERE c.review_status = 3 AND c.cou_end_time > $time AND $time > c.cou_start_time" .
                " AND " . $row['order_total'] . " >= c.cou_man" .
                " AND cu.order_id = 0 AND cu.user_id = '$user_id'" . $cart_where . " GROUP BY cu.uc_id";
            $couarr[$key] = $GLOBALS['db']->getAll($sql);

            if ($couarr[$key]) {
                foreach ($couarr[$key] as $ckey => $crow) {
                    $couarr[$key][$ckey]['shop_name'] = get_shop_name($crow['ru_id'], 1);
                }
            }

            $goods_ids = [];
            if (isset($row['goods_id']) && $row['goods_id'] && !is_array($row['goods_id'])) {
                $goods_ids = explode(",", $row['goods_id']);
                $goods_ids = array_unique($goods_ids);
            }

            $goods_cats = [];
            if (isset($row['cat_id']) && $row['cat_id'] && !is_array($row['cat_id'])) {
                $goods_cats = explode(",", $row['cat_id']);
                $goods_cats = array_unique($goods_cats);
            }

            if (($goods_ids || $goods_cats) && $couarr[$key]) {
                foreach ($couarr[$key] as $rk => $rrow) {
                    if ($rrow['cou_goods']) {
                        $cou_goods = explode(",", $rrow['cou_goods']); //可使用优惠券商品
                        $cou_goods_prices = 0;
                        foreach ($goods_ids as $m => $n) {
                            if (in_array($n, $cou_goods)) {
                                $cou_goods_prices += $row['goods'][$n]['subtotal'];
                                if ($cou_goods_prices > $rrow['cou_man']) {
                                    $arr[] = $rrow;
                                    break;
                                }
                            }
                        }
                    } elseif ($rrow['spec_cat']) {
                        $spec_cat = get_cou_children($rrow['spec_cat']);
                        $spec_cat = explode(",", $spec_cat);
                        $cou_goods_prices = 0;
                        foreach ($goods_cats as $m => $n) {
                            if (in_array($n, $spec_cat)) {
                                foreach ($row['goods'] as $key => $val) {
                                    if ($n == $val['cat_id']) {
                                        $cou_goods_prices += $val['subtotal'];
                                    }
                                }
                                if ($cou_goods_prices > $rrow['cou_man']) {
                                    $arr[] = $rrow;
                                    continue;
                                }
                            }
                        }
                    } else {
                        $arr[] = $rrow;
                    }
                }
            }
        }

        return $arr;
    } else {

        if (!empty($user_id) && $user) {
            $where = " WHERE cu.user_id IN(" . $user_id . ") AND c.review_status = 3";
        } else if (!empty($user_id)) {
            $where = " WHERE cu.user_id IN(" . $user_id . ") AND c.review_status = 3";
        }

        $select = "";
        $leftjoin = "";
        if ($act_type == 'cart') {
            $where .= " AND c.cou_end_time > $time AND $time";
        } else {
            $select = ", o.order_sn, o.add_time";
            $leftjoin = " LEFT JOIN " . $GLOBALS['ecs']->table('order_info') . " AS o ON cu.order_id = o.order_id ";
        }

        $sql = " SELECT c.*, cu.* $select FROM " .
            $GLOBALS['ecs']->table('coupons_user') . " AS cu " .
            " LEFT JOIN " . $GLOBALS['ecs']->table('coupons') .
            " AS c ON c.cou_id = cu.cou_id " .
            $leftjoin .
            $where . $cart_where . " GROUP BY cu.uc_id";

        $res = $GLOBALS['db']->getAll($sql);

        if ($res) {
            foreach ($res as $key => $row) {
                $res[$key]['shop_name'] = get_shop_name($row['ru_id'], 1);
            }
        }

        return $res;
    }
}

/**
 * 获取当前优惠券的不包邮地区
 * @param $cou_id 优惠券ID
 * @return $cou_region 不包邮地区
 */
function get_coupons_region($cou_id = 0)
{
    $sql = "SELECT region_list FROM " . $GLOBALS['ecs']->table('coupons_region') . " WHERE cou_id = '$cou_id'";
    return $GLOBALS['db']->getOne($sql, true);
}


/**
 * 获取当前商品可用的优惠券
 * @param $goods_id 商品ID
 */
function get_goods_coupons_list($goods_id = '')
{

    if (!$goods_id)
        return false;

    $time = gmtime();

    $ru_id = $GLOBALS['db']->getOne("SELECT user_id FROM " . $GLOBALS['ecs']->table('goods') . "
              WHERE goods_id =$goods_id");

    $sql = " SELECT cu.*,c.* FROM " . $GLOBALS['ecs']->table('coupons') . " c
            LEFT JOIN " . $GLOBALS['ecs']->table('coupons_user') . " cu ON c.cou_id=cu.cou_id
            WHERE  c.cou_end_time > $time
            AND (c.cou_goods =0 OR FIND_IN_SET($goods_id,c.cou_goods))
            AND c.cou_type IN (3,4)
            AND c.ru_id = '" . $ru_id . "'
            GROUP BY c.cou_id";

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 通过 用户优惠券ID 获取该条优惠券详情 bylu
 * @param $uc_id 用户优惠券ID
 * @return mixed
 */
function get_coupons($uc_id = 0, $select = array())
{
    $time = gmtime();

    if ($select && is_array($select)) {
        $select = implode(",", $select);
    } else {
        $select = "c.*, cu.*";
    }

    $sql = " SELECT $select FROM " . $GLOBALS['ecs']->table('coupons_user') . " cu " .
        " LEFT JOIN " . $GLOBALS['ecs']->table('coupons') . " c ON c.cou_id = cu.cou_id " .
        " WHERE cu.uc_id = '$uc_id' AND cu.user_id = '" . $_SESSION['user_id'] . "' AND c.cou_end_time > $time GROUP BY cu.uc_id LIMIT 1 ";
    return $GLOBALS['db']->getRow($sql);
}


/* 优惠券分类 */
function get_cou_children($cat = '')
{
    $catlist = '';
    if ($cat) {
        $cat = explode(",", $cat);
        foreach ($cat as $key => $row) {
            $catlist .= get_children($row, 2) . ",";
        }

        $catlist = get_del_str_comma($catlist, 0, -1);
        $catlist = array_unique(explode(",", $catlist));
        $catlist = implode(",", $catlist);
        $cat = implode(",", $cat);
        $catlist = !empty($catlist) ? $catlist . "," . $cat : $cat;
    }

    return $catlist;
}

/**
 * 购物车商家ID
 * $ru_id
 */
function get_cart_seller($cart_value)
{
    $sql = "SELECT GROUP_CONCAT(ru_id) AS ru_id FROM " . $GLOBALS['ecs']->table('cart') . " WHERE rec_id " . db_create_in($cart_value);
    return $GLOBALS['db']->getOne($sql);
}