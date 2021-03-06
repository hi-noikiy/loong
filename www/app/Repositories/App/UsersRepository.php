<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/4/6
 * Time: 21:02
 */

namespace App\Repositories\App;

use App\Contracts\UsersRepositoryInterface;
use App\Facades\Common;
use App\Facades\FileHandle;
use App\Facades\RedisCache;
use App\Http\Models\App\CartModel;
use App\Http\Models\App\CommentModel;
use App\Http\Models\App\OrderInfoModel;
use App\Http\Models\App\OrderReturnModel;
use App\Http\Models\App\UserAddressModel;
use App\Http\Models\App\UsersModel;
use App\Http\Models\App\UsersRealModel;

class UsersRepository implements UsersRepositoryInterface
{
    private $usersModel;
    private $userAddressModel;
    private $usersRealModel;
    private $orderInfoModel;
    private $orderReturnModel;
    private $cartModel;
    private $commentModel;

    public function __construct(
        UsersModel $usersModel,
        UserAddressModel $userAddressModel,
        UsersRealModel $usersRealModel,
        OrderInfoModel $orderInfoModel,
        OrderReturnModel $orderReturnModel,
        CartModel $cartModel,
        CommentModel $commentModel
    )
    {
        $this->usersModel = $usersModel;
        $this->userAddressModel = $userAddressModel;
        $this->usersRealModel = $usersRealModel;
        $this->orderInfoModel = $orderInfoModel;
        $this->orderReturnModel = $orderReturnModel;
        $this->cartModel = $cartModel;
        $this->commentModel = $commentModel;
    }

    public function login($username, $password, $type, $ip, $device_id = '')
    {
        $req = ['code' => 1, 'msg' => '账号密码错误', 'data' => '', 'token' => ''];
        $column = ['user_id', 'server_id', 'email', 'user_name', 'nick_name', 'logo', 'password', 'salt', 'mobile_phone', 'user_money', 'visit_count'];
        $user = $this->usersModel->getUser($username, $column);
        if ($user) {
            if ($type == 1) {
                //验证码登录
                $code = RedisCache::get('code_' . $username);
                if ($code == md5($password)) {
                    $req = ['code' => 0, 'msg' => '', 'data' => $user, 'token' => encrypt($user->user_id)];
                    $where['user_id'] = $user->user_id;
                    $updata = [
                        'last_login' => time(),
                        'last_time' => date(RedisCache::get('shop_config')['time_format'], time()),
                        'last_ip' => $ip,
                        'visit_count' => $user->visit_count + 1
                    ];
                    $this->usersModel->setUsers($where, $updata);
                    $this->cartModel->setCart(['session_id' => $device_id], ['user_id' => $user->user_id]);
                } else {
                    $req = ['code' => 1, 'msg' => '验证码错误', 'data' => '', 'token' => ''];
                }
                $user->is_real = '0';
                $user->logo = FileHandle::getImgByOssUrl($user->logo);
                if (!empty($user->real)) {
                    if ($user->real->review_status == 1) {
                        $user->is_real = '1';
                    }
                }
            } else {
                //密码登录
                $pass = Common::md5Encrypt($password, $user->salt);
                if ($user->password == $pass) {
                    $req = ['code' => 0, 'msg' => '', 'data' => $user, 'token' => encrypt($user->user_id)];
                    $where['user_id'] = $user->user_id;
                    $updata = [
                        'last_login' => time(),
                        'last_time' => date(RedisCache::get('shop_config')['time_format'], time()),
                        'last_ip' => $ip,
                        'visit_count' => $user->visit_count + 1
                    ];
                    $this->usersModel->setUsers($where, $updata);
                    $this->cartModel->setCart(['session_id' => $device_id], ['user_id' => $user->user_id]);
                } else {
                    $req = ['code' => 1, 'msg' => '密码错误', 'data' => '', 'token' => ''];
                }
                $user->is_real = '0';
                $user->logo = FileHandle::getImgByOssUrl($user->logo);
                if (!empty($user->real)) {
                    if ($user->real->review_status == 1) {
                        $user->is_real = '1';
                    }
                }
            }
        }
        return $req;
    }

    public function register($username, $password, $ip, $qrcode, $device_id)
    {
        $code = RedisCache::get('code_' . $username);
        $column = ['user_id', 'email', 'user_name', 'mobile_phone'];
        $user = $this->usersModel->getUser($username, $column);
        if (empty($user)) {
            if ($code == md5($qrcode)) {
                $salt = Common::randStr(6);
                $time = time();
                $user_id = RedisCache::incrby('user_id');
                $userData = [
                    'user_id' => $user_id,
                    'salt' => $salt,
                    'password' => Common::md5Encrypt($password, $salt),
                    'user_name' => $username,
                    'last_login' => $time,
                    'mobile_phone' => $username,
                    'nick_name' => APPNAME . substr(md5($username), 8, -8),
                    'reg_time' => $time,
                    'last_ip' => $ip,
                    'logo' => '',
                    'server_id' => 0,
                    'user_money' => 0,
                ];
                $user = $this->usersModel->addUser($userData, $user_id);
                $this->usersRealModel->addUsersReal(['user_id' => $user_id]);
                $user->user_id = $user_id;
                $this->cartModel->setCart(['session_id' => $device_id], ['user_id' => $user->user_id]);
                $user->is_real = '0';
                $req = ['code' => 0, 'msg' => '', 'data' => $user, 'token' => encrypt($user->user_id)];
            } else {
                $req = ['code' => 1, 'msg' => '验证码错误', 'data' => ''];
            }
        } else {
            $req = ['code' => 1, 'msg' => '账号已存在', 'data' => ''];
        }
        return $req;
    }

    public function getUserInfo($uid)
    {
        $column = ['user_id', 'server_id', 'email', 'is_email', 'nick_name', 'sex', 'birthday', 'user_money', 'frozen_money', 'bonus_money', 'pay_points',
            'rank_points', 'address_id', 'user_rank', 'mobile_phone', 'is_phone', 'credit_line', 'logo', 'qq', 'union_id'];
        $user = $this->usersModel->getUser($uid, $column);
        $user->is_real = '0';
        $user->logo = FileHandle::getImgByOssUrl($user->logo);

        if (!empty($user->real)) {
            if ($user->real->review_status == 1) {
                $user->is_real = '1';
            }
            unset($user->real);
        }

        //待付款
        $order_orwhere = [];
        $order_where = [
            ['order_status', '<>', OS_CANCELED],
            ['order_status', '<>', OS_INVALID],
            ['order_status', '<>', OS_RETURNED],
            ['order_status', '<>', OS_ONLY_REFOUND],
            ['order_status', '<>', OS_RETURNED_PART],
            ['pay_status', '=', PS_UNPAYED],
            ['shipping_status', '=', SS_UNSHIPPED],
            ['user_id', '=', $uid],
        ];

        $order_unpayed_count = $this->orderInfoModel->countOrder($order_where, $order_orwhere);
        $user->order_unpayed_count = $order_unpayed_count;

        //待发货
        $order_where = [
            ['order_status', '=', OS_CONFIRMED],
            ['pay_status', '=', PS_PAYED],
            ['shipping_status', '=', SS_UNSHIPPED],
            ['user_id', '=', $uid],
        ];
        $order_unship_count = $this->orderInfoModel->countOrder($order_where);
        $user->order_unship_count = $order_unship_count;

        //待收货
        $order_where = [
            ['order_status', '=', OS_CONFIRMED],
            ['shipping_status', '=', SS_SHIPPED],
            ['user_id', '=', $uid],
        ];
        $order_shipped_count = $this->orderInfoModel->countOrder($order_where);
        $user->order_shipped_count = $order_shipped_count;

        //待评价
        $order_where = [
            ['order_status', '=', OS_CONFIRMED],
            ['shipping_status', '=', SS_RECEIVED],
            ['comment_status', '=', CS_UNCOMMENT],
            ['user_id', '=', $uid],
        ];
        $order_comment_count = $this->orderInfoModel->countOrder($order_where);
        $user->order_comment_count = $order_comment_count;

        //退换货
        $order_where = [
            ['return_status', '<>', RS_CHANGE_END],
            ['refound_status', '=', RS_NOREFOUND],
            ['user_id', '=', $uid],
        ];
        $order_return_count = $this->orderReturnModel->countOrderReturn($order_where);
        $user->order_return_count = $order_return_count;

        return $user;
    }

    public function setUserInfo($data, $uid)
    {
        $where['user_id'] = $uid;
        if (!empty($data['nickname'])) {
            $updata['nick_name'] = $data['nickname'];
            $this->commentModel->setComment($where, ['user_name' => $data['nickname']]);
        } elseif (!empty($data['sex'])) {
            $updata['sex'] = $data['sex'];
        } elseif (!empty($data['file_0'])) {
            $path = 'user_logo';
            if ($data['file_0']->isValid()) {
                $uri = FileHandle::upLoadImage($data['file_0'], $path);
                $updata['logo'] = $uri;
                if (!empty($data['logo']) && strpos($uri, 'http') === false) {
                    FileHandle::deleteFile($data['logo']);
                }
            }
        } elseif (!empty($data['phone']) && !empty($data['qrcode'])) {
            if ($data['qrcode'] == RedisCache::get('code_' . $data['phone'])) {
                $updata['mobile_phone'] = $data['phone'];
            } else {
                return '验证码错误';
            }
        }
        $re = $this->usersModel->setUsers($where, $updata);
        if ($re) {
            return 0;
        } else {
            return '设置失败';
        }
    }

    public function userAddresses($uid)
    {
        $where['user_id'] = $uid;
        $res = $this->userAddressModel->userAddresses($where);
        $user = $this->usersModel->getUser($uid);
        foreach ($res as $re) {
            $re->country_name = $re->mapcountry->region_name;
            $re->province_name = $re->mapprovince->region_name;
            $re->city_name = $re->mapcity->region_name;
            $re->district_name = $re->mapdistrict->region_name;
            if ($re->address_id == $user->address_id) {
                $re->def = 1;
            } else {
                $re->def = 0;
            }
        }
        return $res;
    }

    public function getAddress($data)
    {
        $where['address_id'] = $data['address_id'];
        $re = $this->userAddressModel->getAddress($where);
        $re->country_name = $re->mapcountry->region_name;
        $re->province_name = $re->mapprovince->region_name;
        $re->city_name = $re->mapcity->region_name;
        $re->district_name = $re->mapdistrict->region_name;
        return $re;
    }

    public function setDefaultAddress($data, $uid)
    {
        $where['user_id'] = $uid;
        $updata['address_id'] = $data['address_id'];
        return $this->usersModel->setUsers($where, $updata);
    }

    public function setAddress($data)
    {
        $where['address_id'] = $data['address_id'];
        $updata = [];
        return $this->userAddressModel->setAddress($where, $updata);
    }

    public function addAddress($data, $uid)
    {
        $count = $this->userAddressModel->countAddress(['user_id' => $uid]);
        if ($count >= 10) {
            return '限定十个地址，已添加满';
        }
        $updata['user_id'] = $uid;
        $updata['consignee'] = $data['consignee'];
        $updata['mobile'] = $data['phone'];
        $updata['country'] = $data['country'];
        $updata['province'] = $data['province'];
        $updata['city'] = $data['city'];
        $updata['district'] = $data['district'];
        $updata['address'] = $data['address_info'];
        $updata['update_time'] = time();
        if (empty($data['address_id'])) {
            return $this->userAddressModel->addAddress($updata);
        } else {
            $where['address_id'] = $data['address_id'];
            return $this->userAddressModel->setAddress($where, $updata);
        }
    }

    public function delAddress($data, $uid)
    {
        $where['user_id'] = $uid;
        $where['address_id'] = $data['address_id'];
        return $this->userAddressModel->delAddress($where);
    }

    public function getUsersReal($uid)
    {
        $where['user_id'] = $uid;
        $explain = '根据海关规定,任何出入关口的商品都必须实名登记并且缴纳关税,根据海关规定,根据海关规定,根据海关规定根据海关规定根据海关规定，根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定根据海关规定，根据海关规定';
        $re = $this->usersRealModel->getUsersReal($where);
        if ($re) {
            $re->explain = $explain;
        } else {
            $re['explain'] = $explain;
        }
        $re->front_of_id_card = FileHandle::getImgByOssUrl($re->front_of_id_card);
        $re->reverse_of_id_card = FileHandle::getImgByOssUrl($re->reverse_of_id_card);
        return $re;
    }

    public function setUsersReal($data, $uid)
    {
        $path = 'user_card';
        $where['user_id'] = $uid;
        $re = $this->usersRealModel->getUsersReal($where);
        $updata = [];
        foreach ($data as $key => $value) {
            if ($key == 'file_0') {
                if ($value->isValid()) {
                    $uri = FileHandle::upLoadImage($value, $path);
                    $updata['front_of_id_card'] = $uri;
                    if ($re) {
                        FileHandle::deleteFile($re->front_of_id_card);
                    }
                }
            } elseif ($key == 'file_1') {
                if ($value->isValid()) {
                    $uri = FileHandle::upLoadImage($value, $path);
                    $updata['reverse_of_id_card'] = $uri;
                    if ($re) {
                        FileHandle::deleteFile($re->reverse_of_id_card);
                    }
                }
            } elseif ($key == 'card_name') {
                $updata['real_name'] = $value;
            } elseif ($key == 'card_num') {
                $updata['self_num'] = $value;
            }
        }
        $updata['add_time'] = time();
        $updata['review_status'] = 3;
        if ($re) {
            $req = $this->usersRealModel->setUsersReal($where, $updata);
        } else {
            $updata['user_id'] = $uid;
            $req = $this->usersRealModel->addUsersReal($updata);
        }
        return ['review_status' => 3];
    }
}