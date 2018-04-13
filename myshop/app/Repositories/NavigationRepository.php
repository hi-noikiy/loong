<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/4/6
 * Time: 21:02
 */

namespace App\Repositories;

use App\Contracts\NavigationRepositoryInterface;
use App\Http\Models\Shop\NavigationModel;

class NavigationRepository implements NavigationRepositoryInterface
{

    protected $navigationModel;

    public function __construct(NavigationModel $navigationModel)
    {
        $this->navigationModel = $navigationModel;
    }

    public function getNavs($size = 10)
    {
        return $this->navigationModel->getNavs($size);
    }

    public function getNav($id)
    {
        return $this->navigationModel->getNav($id);
    }

    public function getNavsTop()
    {
        return $this->navigationModel->getNavsTop();
    }

    public function addNav($data)
    {
        return $this->navigationModel->addNav($data);
    }

    public function upDateNav($data, $id)
    {
        return $this->navigationModel->upDateNav($data, $id);
    }

    public function showOrOpenView($data)
    {
        if($data['type'] == 'ifshow'){
            $updata['ifshow'] = $data['isshow'];
            $where['id'] = $data['id'];
            return $this->navigationModel->setNav($updata,$where);
        }else if($data['type'] == 'opennew'){
            $updata['opennew'] = $data['isopen'];
            $where['id'] = $data['id'];
            return $this->navigationModel->setNav($updata,$where);
        }
    }

    public function changOrder($data)
    {

        if($data['order'] == ''){
            return ['code'=>5,'msg'=>'修改失败'];
        }
        $updata['vieworder'] = $data['order'];
        $where['id'] = $data['id'];
        $re = $this->navigationModel->setNav($updata,$where);
        if($re){
            return ['code'=>1,'msg'=>'修改成功'];
        }else{
            return ['code'=>5,'msg'=>'修改失败'];
        }
    }

    public function delete($id)
    {
        $where['id'] = $id;
        $re = $this->navigationModel->deleteNav($where);
        if($re){
            return ['code'=>1,'msg'=>'删除成功'];
        }else{
            return ['code'=>5,'msg'=>'删除失败'];
        }
    }

}