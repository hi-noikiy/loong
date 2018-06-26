<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/4/6
 * Time: 21:02
 */

namespace App\Repositories;

use App\Contracts\AdminUserRepositoryInterface;
use App\Facades\Common;
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
        return $this->adminUserModel->getAdminUser($where);
    }

    public function setAdminUser($data, $id, $user)
    {
        
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
        if($re){
            return ['code' => 0, 'msg' => '操作成功'];
        }else{
            return ['code' => 1, 'msg' => '操作失败'];
        }
    }

    public function delAdminUser($id, $adminUser)
    {
        $where['user_id'] = $id;
        $user = $this->adminUserModel->getAdminUser($where);
        $re = [];
        if($user->parent_id == $adminUser->user_id){
            $re = $this->adminUserModel->delAdminUser($where);
        }
        if($re){
            return ['code' => 1, 'msg' => '操作成功'];
        }else{
            return ['code' => 5, 'msg' => '操作失败,无权限'];
        }
    }

}