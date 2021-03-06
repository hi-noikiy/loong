<?php

namespace App\Modules\Community\Controllers;

use App\Modules\Base\Controllers\FrontendController;

class PostController extends FrontendController
{
    private $user_id;
    private $type;
    private $size = 10;
    private $page = 1;

    public function __construct()
    {
        parent::__construct();
        $files = [
            'order',
            'clips'
        ];
        $this->load_helper($files);
        $this->user_id = $_SESSION['user_id'];

        if (strtolower(MODULE_NAME) == 'community' && strtolower(CONTROLLER_NAME) == 'post') {
            $community = 1;
            $this->assign('community', $community);
        }

        $this->type = input('type');
        if (checkDistype($this->type) == false) {
            $this->redirect('community/index/index');
        }
    }

    /**
     * 发帖首页
     * @return
     */
    public function actionIndex()
    {
        if (IS_AJAX) {
            $goods_id = input('get.goods_id', 0, 'intval');
            $list = community_list($this->type, $this->page, $this->size, '', $goods_id);
            exit(json_encode($list));
        }

        $goods_id = input('goods_id', 0, 'intval');
        $title = input('title', '', 'trim');
        $content = input('content', '', 'trim');
        if ($goods_id > 0) {
            $postgoods = get_goods_info($goods_id);
        } else {
            $this->redirect('community/index/index');
        }
        $this->assign('type', $this->type);
        $this->assign('title', $title);
        $this->assign('content', $content);
        $this->assign('postgoods', $postgoods);
        $this->assign('page_title', '网友讨论圈');
        $this->display();
    }

    /**
     * 发帖处理 TODO
     */
    public function actionAddcom()
    {
        $this->checkLogin();
        if (IS_POST) {
            $data = I('');
            if (empty($data['goods_id'])) {
                show_message('关联商品不能为空');
            }
            if (empty($data['dis_type'])) {
                show_message('请选择帖子主题');
            }
            if (empty($data['title'])) {
                show_message('请填写标题');
            }
            if (empty($data['content'])) {
                show_message('请填写帖子内容');
            }

            $return = [
                'dis_type' => $data['dis_type'],
                'goods_id' => $data['goods_id'],
                'user_id' => $_SESSION['user_id'],
                'dis_title' => $data['title'],
                'dis_text' => $data['content'],
                'user_name' => $_SESSION['user_name'],
                'add_time' => gmtime()
            ];
            $dis_id = $this->model->table('discuss_circle')->data($return)->add();
            show_message('发帖成功', '查看帖子', url('community/index/detail', ['id' => $dis_id, 'type' => $data['dis_type']]), 'success');
        }
    }

    /**
     * 检测登录
     * @return
     */
    private function checkLogin()
    {
        if (!$this->user_id) {
            $back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];
            $this->redirect('user/login/index', ['back_act' => urlencode($back_act)]);
        }
    }

}
