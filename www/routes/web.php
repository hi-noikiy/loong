<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('foo', function () {
    return 'Hello World';
});

/*
 **********
 *
 * shop前端路由
 *
 **********
*/
Route::group(['namespace' => 'Shop\Home'], function () {
    Route::any('/', 'IndexController@index');
    Route::any('test', 'IndexController@test');
});
/*
 **********
 *
 * shop后台路由
 *
 **********
*/

Route::group(['prefix' => 'admin', 'namespace' => 'Shop\Admin'], function () {
    Route::any('login', 'LoginController@login');
    Route::get('change', 'LoginController@change');
    Route::get('tool', 'LoginController@tool');
});

Route::group(['middleware' => ['admin.login'], 'prefix' => 'admin', 'namespace' => 'Shop\Admin'], function () {
    Route::get('index', 'IndexController@index');
    Route::get('info', 'IndexController@info');

//    Route::get('shopsetup', 'ShopConfController@index');
    Route::resource('shopconf', 'ShopConfController');

    Route::post('express/install/{id}', 'ExpressController@install');
    Route::post('express/changes', 'ExpressController@changes');
    Route::resource('express', 'ExpressController');

    Route::post('transport/changes', 'TransportController@changes');
    Route::get('transport/regions/{id}/{tid}', 'TransportController@regions');
    Route::get('transport/express/{id}/{tid}', 'TransportController@express');
    Route::resource('transport', 'TransportController');

    Route::get('regions/nextregions/{id}/{tid}', 'RegionsController@nextRegions');
    Route::get('regions/addregion/{id}/{tid}', 'RegionsController@addRegion');
    Route::post('regions/changes', 'RegionsController@changes');
    Route::resource('regions', 'RegionsController');

    Route::post('navsetup/show/or/view', 'NavigationController@showOrView');
    Route::post('navsetup/change/order', 'NavigationController@changeOrder');
    Route::resource('navsetup', 'NavigationController');

    Route::post('goodsconf/change', 'GoodsConfigController@change');
    Route::resource('goodsconf', 'GoodsConfigController');

    Route::post('comcate/change', 'ComCateController@change');
    Route::post('comcate/getcates/{id}', 'ComCateController@getCates');
    Route::any('comcate/add/cate/{id}', 'ComCateController@addCate');
    Route::resource('comcate', 'ComCateController');

    Route::post('brand/change', 'BrandController@change');
    Route::post('brand/search', 'BrandController@search');
    Route::post('brand/firstchar', 'BrandController@getFirstChar');
    Route::resource('brand', 'BrandController');


    Route::get('goods/backto/{id}', 'GoodsController@backTo');
    Route::get('goods/del/{id}', 'GoodsController@thoroughDel');
    Route::get('goods/examine/{id}', 'GoodsController@examine');
    Route::get('goods/weight/order/{id}', 'GoodsController@weightOrder');
    Route::get('goods/cateextend/{id}', 'GoodsController@cateExtend');
    Route::get('goods/imagelibrary/{type}/{id}', 'GoodsController@imageLibrary');
    Route::get('goods/addgalleryshow', 'GoodsController@addGalleryShow');
    Route::get('goods/customattrwin/{id}/{gid}', 'GoodsController@customAttrWin');
    Route::post('goods/addgallery', 'GoodsController@addGallery');
    Route::post('goods/change', 'GoodsController@change');
    Route::post('goods/changes', 'GoodsController@changes');
    Route::post('goods/addcateext', 'GoodsController@addCateExtend');
    Route::post('goods/delcateext/{id}', 'GoodsController@delCateExtend');
    Route::post('goods/addgoodsgallery', 'GoodsController@addGoodsGallery');
    Route::post('goods/upgoodsgallery', 'GoodsController@upGoodsGalleryPic');
    Route::post('goods/delgoodsgallery', 'GoodsController@delGoodsGalleryPic');
    Route::post('goods/addgoodsattr', 'GoodsController@addGoodsAttr');
    Route::post('goods/setgoodsattr', 'GoodsController@setGoodsAttr');
    Route::post('goods/product/{id}', 'GoodsController@getGoodsByProduct');
    Route::resource('goods', 'GoodsController');

    Route::get('goodstype/goodstype/modal', 'GoodsTypeController@goodsTypeByModal');
    Route::post('goodstype/addgoodstype', 'GoodsTypeController@addGoodsType');
    Route::post('goodstype/gettypes/{id}', 'GoodsTypeController@getTypes');
    Route::resource('goodstype', 'GoodsTypeController');

    Route::get('typecate/typecate/modal', 'GoodsTypeCateController@goodsTypeCateByModal');
    Route::post('typecate/addgoods/typecate', 'GoodsTypeCateController@addGoodsTypeCate');
    Route::post('typecate/change', 'GoodsTypeCateController@change');
    Route::post('typecate/getcates/{id}', 'GoodsTypeCateController@getCates');
    Route::resource('typecate', 'GoodsTypeCateController');

    Route::get('attribute/attribut/modal', 'AttributeController@attributeModal');
    Route::post('attribute/addattribute', 'AttributeController@addAttribute');
    Route::post('attribute/getattributes/{id}', 'AttributeController@getAttributes');
    Route::resource('attribute', 'AttributeController');

    Route::resource('captcha', 'CaptchaController');

    Route::get('seo/brand', 'SeoController@brand');
    Route::get('seo/goods', 'SeoController@goods');
    Route::resource('seo', 'SeoController');

    Route::post('pay/install', 'PayConfigController@install');
    Route::post('pay/changes', 'PayConfigController@changes');
    Route::resource('pay', 'PayConfigController');

    Route::post('friend/changes', 'FriendController@changes');
    Route::resource('friend', 'FriendController');

    Route::get('gallery/galleryview/{id}', 'GalleryController@galleryView');
    Route::get('gallery/transferpic/{id}', 'GalleryController@transferGalleryPic');
    Route::get('gallery/uppicview/{id}', 'GalleryController@upPicView');
    Route::post('gallery/changes', 'GalleryController@changes');
    Route::post('gallery/getgallerys/{id}', 'GalleryController@getGallerys');
    Route::post('gallery/setgallerypic/', 'GalleryController@setGalleryPic');
    Route::post('gallery/delgallerypic', 'GalleryController@delGalleryPic');
    Route::post('gallery/upgallerypic', 'GalleryController@upGalleryPic');
    Route::post('gallery/getgallerypics', 'GalleryController@getGalleryPics');
    Route::resource('gallery', 'GalleryController');

    Route::get('users/info/{id}', 'UsersController@userInfo');
    Route::get('users/address/{id}', 'UsersController@userAddress');
    Route::get('users/userorder/{id}', 'UsersController@userOrder');
    Route::get('users/baitiao/{id}', 'UsersController@userBaitiao');
    Route::get('users/account/{id}', 'UsersController@userAccount');
    Route::post('users/changes', 'UsersController@changes');
    Route::resource('users', 'UsersController');

    Route::resource('address', 'UserAddressController');

    Route::post('userrank/changes', 'UserRankController@changes');
    Route::resource('userrank', 'UserRankController');

    Route::post('regfields/changes', 'RegFieldsController@changes');
    Route::resource('regfields', 'RegFieldsController');

    Route::post('usersreal/changes', 'UsersRealController@changes');
    Route::resource('usersreal', 'UsersRealController');

    Route::get('uaccount/recharge', 'UserAccountController@recharge');
    Route::resource('uaccount', 'UserAccountController');

    Route::resource('privilege', 'PrivilegeController');

    Route::resource('order', 'OrderController');
});