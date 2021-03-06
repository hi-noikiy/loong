<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/4/6
 * Time: 21:02
 */

namespace App\Repositories\Admin;

use App\Contracts\AdminUserRepositoryInterface;
use App\Facades\Common;
use App\Facades\FileHandle;
use App\Http\Models\Shop\AdminUserModel;

class AdminUserRepository implements AdminUserRepositoryInterface
{
    private $adminUserModel;

    public function __construct(
        AdminUserModel $adminUserModel
    )
    {
        $this->adminUserModel = $adminUserModel;
    }

    public function getAdminUserByPage($id)
    {
        $where['ru_id'] = $id;
        $res = $this->adminUserModel->getAdminUserByPage($where);
        $adminName = '';
        foreach ($res as $value) {
            if ($value->parent_id == 0) {
                $adminName = $value->user_name;
            }
        }
        foreach ($res as $key => $value) {
            if ($value->parent_id != 0) {
                $res[$key]->parent_name = $adminName;
            } else {
                $res[$key]->parent_name = '';
            }
        }
        return $res;
    }

    public function getAdminUser($where)
    {
        $req = $this->adminUserModel->getAdminUser($where);
        if ($req) {
            $action_list = explode(',', $req->action_list);
            foreach ($action_list as $value) {
                $action_list[$value] = $value;
            }
            $req->action_list = $action_list;
        }
        return $req;
    }

    public function setAdminUser($data, $id)
    {
        $req = ['code' => 1, 'msg' => '操作失败'];
        $where['user_id'] = $id;
        $str = '';
        $updata = [];

        if(!empty($data['last_login'])){
            $updata['last_login'] = $data['last_login'];
        }

        if(!empty($data['last_ip'])){
            $updata['last_ip'] = $data['last_ip'];
        }

        foreach ($data as $key => $value) {
            if (!empty($value['code'])) {
                $str .= $value['code'] . ',';
            }
        }
        if (!empty($data['new_password']) && !empty($data['confirm_password'])) {
            if ($data['new_password'] == $data['confirm_password']) {
                $updata['salt'] = Common::randStr(6);
                $password = Common::md5Encrypt($data['confirm_password'], $updata['salt']);
                $updata['password'] = $password;
            }
        }
        $preg_email = '/^[a-z0-9]+([._-][a-z0-9]+)*@([0-9a-z]+\.[a-z]{2,14}(\.[a-z]{2})?)$/i';
        if (!empty($data['email']) && is_string($data['email']) && preg_match($preg_email, $data['email'])) {
            $updata['email'] = $data['email'];
        }
        if (!empty($data['user_name'])) {
            $user = $this->getAdminUser(['user_name' => $data['user_name']]);
            if (!$user) {
                $updata['user_name'] = $data['user_name'];
            }
        }
        $str = substr($str, 0, -1);
        if ($str != '') {
            $updata['action_list'] = $str;
        }
        $re = $this->adminUserModel->setAdminUser($where, $updata);
        if ($re) {
            $req = ['code' => 0, 'msg' => '操作成功'];
        }
        return $req;
    }

    public function addAdminUser($data, $user)
    {
        if ($data['new_password'] != $data['confirm_password']) {
            return ['code' => 1, 'msg' => '密码不一致'];
        }
        $updata['salt'] = Common::randStr(6);
        $updata['password'] = Common::md5Encrypt($data['new_password'], $updata['salt']);
        if ($this->adminUserModel->countAdminUser(['user_name' => $data['user_name']])) {
            return ['code' => 1, 'msg' => '用户名已存在'];
        }
        $updata['user_name'] = $data['user_name'];
        $updata['ru_id'] = $user->ru_id;
        $updata['parent_id'] = $user->user_id;
        $updata['add_time'] = time();
        $updata['email'] = $data['email'];
        $updata['last_ip'] = '';
        $re = $this->adminUserModel->addAdminUser($updata);
        if ($re) {
            return ['code' => 0, 'msg' => '操作成功'];
        } else {
            return ['code' => 1, 'msg' => '操作失败'];
        }
    }

    public function delAdminUser($id, $adminUser)
    {
        $where['user_id'] = $id;
        $user = $this->adminUserModel->getAdminUser($where);
        $re = [];
        if ($user->parent_id == $adminUser->user_id) {
            $re = $this->adminUserModel->delAdminUser($where);
        }
        if ($re) {
            return ['code' => 1, 'msg' => '操作成功'];
        } else {
            return ['code' => 5, 'msg' => '操作失败,无权限'];
        }
    }

    public function modifyAdminUser($data)
    {
        $req = ['code' => '5', 'msg' => '操作失败'];
        $updata['user_name'] = $data['login_name'];
        unset($data['login_name']);
        if (empty($data['auid'])) {
            if ($this->adminUserModel->getAdminUser($updata)) {
                return ['code' => '5', 'msg' => '用户名已经存在'];
            }
        }
        if (!empty($data['password'])) {
            $salt = Common::randStr(6);
            $updata['salt'] = $salt;
            $updata['password'] = Common::md5Encrypt($data['password'], $salt);
            unset($data['password']);
        }
        $action_list = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (!empty($value['code'])) {
                    $action_list[] = $value['code'];
                }
            }
        }
        if (count($action_list) > 0) {
            $updata['action_list'] = implode(',', $action_list);
        }
        if (empty($data['auid'])) {
            $updata['parent_id'] = 0;
            $updata['ru_id'] = $data['ru_id'];
            $updata['add_time'] = time();
            $re = $this->adminUserModel->addAdminUser($updata);
        } else {
            $where['user_id'] = $data['auid'];
            $re = $this->adminUserModel->setAdminUser($where, $updata);
        }
        if ($re) {
            return ['code' => '1', 'msg' => '操作成功'];
        }
        return $req;
    }

    public function checkAdminUserName($data)
    {
        $req = ['code' => '5', 'msg' => '操作失败'];
        $user = $this->getAdminUser($data);
        if (!$user) {
            return ['code' => '1', 'msg' => '操作成功'];
        }
        return $req;
    }

    public function changeLogo($data, $user)
    {
        $logo = $data['pic'];
        $path = 'admin_logo';
        $url = FileHandle::upLoadImage($logo, $path);
        $where['user_id'] = $user->user_id;
        $updata['admin_user_img'] = $url;
        $this->adminUserModel->setAdminUser($where, $updata);
        return FileHandle::getImgByOssUrl($url);
    }

    public function test()
    {
        return $this->adminUserModel->test(['uid' => 1048576]);
    }
}