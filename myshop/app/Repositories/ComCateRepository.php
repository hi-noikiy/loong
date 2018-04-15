<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2018/4/6
 * Time: 21:02
 */

namespace App\Repositories;

use App\Contracts\ComCateRepositoryInterface;
use App\Http\Models\shop\CategoryModel;


class ComCateRepository implements ComCateRepositoryInterface
{

    protected $categoryModel;

    public function __construct(CategoryModel $categoryModel)
    {
        $this->categoryModel = $categoryModel;
    }

    public function getComCates($id = 0)
    {
        return $this->categoryModel->getComCates($id);
    }

    public function getComCate($id)
    {
        return $this->categoryModel->getComCate($id);
    }

    public function addCate($data)
    {
        $updata = [];
        foreach ($data as $key => $val) {
            if ($key == 'category_name') {
                if (count($val) > 0) {
                    if ($val[count($val) - 1] == 0) {
                        $updata['parent_id'] = $val[count($val) - 2];
                    } else {
                        $updata['parent_id'] = $val[count($val) - 1];
                    }
                }
            } else {
                $updata[$key] = $val ? $val : 0;
            }
        }

        return $this->categoryModel->addCate($updata);
    }

//    public function upDateCate($data, $id)
//    {
//        return $this->categoryModel->upDateNav($data, $id);
//    }

    public function getParentCate($id, $parentCates = [])
    {
        $PCates = $parentCates;
        $re = $this->categoryModel->getComCate($id);
        $PCates[] = $re;
        if($re->parent_id != 0){
            return $this->getParentCate($re->parent_id, $PCates);
        }else{
            krsort($PCates);
            return $PCates;
        }
    }

    public function changOrder($data)
    {

        if ($data['order'] == '') {
            return ['code' => 5, 'msg' => '修改失败'];
        }
        $updata['vieworder'] = $data['order'];
        $where['id'] = $data['id'];
        $re = $this->categoryModel->setNav($updata, $where);
        if ($re) {
            return ['code' => 1, 'msg' => '修改成功'];
        } else {
            return ['code' => 5, 'msg' => '修改失败'];
        }
    }

    public function delete($id)
    {
        $req = ['code' => 5, 'msg' => '删除失败'];
        $where['id'] = $id;
        $res = $this->categoryModel->getComCates($id);
        if ($res->toArray()) {
            $req['msg'] = '存在子类删除失败';
        } else {
            $re = $this->categoryModel->deleteCate($where);
            if ($re) {
                $req['code'] = 1;
                $req['msg'] = '删除成功';
            }
        }
        return $req;
    }

    public function getRank($id)
    {
        $re = $this->categoryModel->getComCate($id);
        return $this->categoryModel->getRank($re);
    }

    public function setComCate($data)
    {
        $rep = ['code' => 5, 'msg' => '修改失败'];

        $where['id'] = $data['id'];
        $keys = array_keys($data);
        foreach ($keys as $val) {
            switch ($val) {
                case 'order':
                    $updata['sort_order'] = $data['order'];
                    break;
                case 'grade':
                    $updata['grade'] = $data['grade'];
                    break;
                case 'measure_unit':
                    $updata['measure_unit'] = $data['measure_unit'];
                    break;
                case 'commission_rate':
                    $updata['commission_rate'] = $data['commission_rate'];
                    break;
                case 'isshow':
                    $updata['is_show'] = $data['isshow'];
                    break;
                case 'shownav':
                    $updata['show_in_nav'] = $data['shownav'];
                    break;
                default:
                    break;
            }
        }
        $re = $this->categoryModel->setComCate($where, $updata);
        if ($re) {
            $rep = ['code' => 1, 'msg' => '修改成功'];
        }

        return $rep;
    }

}