<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/5/8
 * Time: 21:09
 */

return array(
    'index' => array(
        'home' => '首页',
        'shop' => '商城',
        'finance' => '财务',
        'system' => '系统',
    ),
    'system' => array(
        'setup' => '设置',
        'advertise' => '广告',
    ),
    'setup' => array(
        'shopsetup' => array(
            'name' => '商城设置',
            'url' => 'admin/shopconf',
        ),
        'navsetup' => array(
            'name' => '自定义导航',
            'url' => 'admin/navsetup',
        ),
        'paysetup' => array(
            'name' => '支付设置',
            'url' => 'admin/pay',
        ),
        'areasetup' => array(
            'name' => '地区&快递',
            'url' => 'admin/express',
        ),
        'seosetup' => array(
            'name' => 'SEO设置',
            'url' => 'admin/seo',
        ),
        'codesetup' => array(
            'name' => '验证码设置',
            'url' => 'admin/captcha',
        ),
        'friendsetup' => array(
            'name' => '友情链接',
            'url' => 'admin/friend',
        ),
    ),
    'home' => array(
        'info' => '首页',
    ),
    'advertise' => array(
        'ad_list' => array(
            'name' => '广告列表',
            'url' => 'admin/ad_list',
        ),
        'ad_position' => array(
            'name' => '广告位置',
            'url' => 'admin/adposition',
        )
    ),
    'info' => array(
        'infocenter' => array(
            'name' => '管理中心',
            'url' => 'admin/info',
        ),
    ),
    'shop' => array(
        'commodity' => '商品',
    ),
    'commodity' => array(
        'shopsetup' => array(
            'name' => '商品设置',
            'url' => 'admin/goodsconf',
        ),
        'comcate' => array(
            'name' => '商品分类',
            'url' => 'admin/comcate',
        ),
        'goods' => array(
            'name' => '商品列表',
            'url' => 'admin/goods',
        ),
        'brandlist' => array(
            'name' => '品牌管理',
            'url' => 'admin/brand',
        ),
        'goodstype' => array(
            'name' => '商品类型',
            'url' => 'admin/goodstype',
        ),
        'gallery' => array(
            'name' => '图片库管理',
            'url' => 'admin/gallery',
        ),
    ),
    'finance' => array(
        'satistics' => '统计',
    ),
    'satistics' => array(
        'ordersatistics' => array(
            'name' => '订单统计',
            'url' => '',
        ),
    ),
);