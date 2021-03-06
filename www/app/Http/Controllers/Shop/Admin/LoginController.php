<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc: 登录设置功能
 */

namespace App\Http\Controllers\Shop\Admin;

use App\Facades\Captcha;
use App\Facades\Common;
use App\Facades\FileHandle;
use App\Facades\LangConfig;
use App\Facades\RedisCache;
use App\Facades\ShopConfig;
use App\Facades\Verifiable;
use App\Helper\SnowFlake;
use App\Repositories\Admin\AdminUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class LoginController extends CommonController
{

    private $adminUserRepository;

    public function __construct(
        AdminUserRepository $adminUserRepository
    )
    {
        parent::__construct();
        $this->adminUserRepository = $adminUserRepository;
    }

    public function login(Request $request)
    {
        $lang = LangConfig::LangAdminConf();
        $captcha = ShopConfig::getConfHidden(['captcha'])['captcha'];
        if ($input = Input::except('_token')) {
            $ip = $request->getClientIp();
            $rules = ["username" => 'required', "password" => 'required'];
            if ($captcha->value[3] != 0) {
                $rules['captcha'] = 'required';
            }
            $validator = Verifiable::loginVer($input, $lang, $rules);
            if ($validator->passes()) {
                $user = $this->adminUserRepository->getAdminUser(['user_name' => $input['username']]);
                if (empty($user)) {
                    return back()->with('errors', $lang['login_faild']);
                }
                if ($user->user_name != $input['username']) {
                    return back()->with('errors', $lang['login_faild']);
                }
                if ($user->password != Common::md5Encrypt($input['password'], $user->salt)) {
                    return back()->with('errors', $lang['login_faild']);
                }
                if ($captcha->value[3] != 0) {
                    if (!Captcha::check($input['captcha'])) {
                        return back()->with('errors', $lang['login_faild']);
                    }
                }
                if ($user->ru_id != 0) {
                    return back()->with('errors', $lang['login_faild']);
                }
                $this->adminUserRepository->setAdminUser(['last_login' => time(), 'last_ip' => $ip], $user->user_id);
                $user->last_login = time();
                $user->last_ip = $ip;
                $user->admin_user_img = FileHandle::getImgByOssUrl($user->admin_user_img);
//                session(['user'=>$user]);
                RedisCache::setex('adminUser' . md5($ip) . $user->user_id, $user, 600);
                return redirect('admin/index')->cookie('user_id', $user->user_id, 720);
            } else {
                return back()->withErrors($validator);
            }
        } else {
            return view('shop.admin.login', compact('lang', 'captcha'));
        }
    }

    public function logout(Request $request)
    {
        $uid = $request->cookie('user_id');
        $ip = $request->getClientIp();
        RedisCache::del('adminUser' . md5($ip) . $uid);
        if (!RedisCache::get('adminUser' . md5($ip) . $uid)) {
            return redirect('admin/login');
        }
        $error = ['code' => 1, 'msg' => '操作失败'];
        return view('shop.admin.failed', compact('error'));
    }

    public function clearCache(Request $request)
    {
        //forever存放永久
        //flush清除所有缓存
        $uid = $request->cookie('user_id');
        $ip = $request->getClientIp();
        RedisCache::flushdb();
        if (!RedisCache::get('adminUser' . md5($ip) . $uid)) {
            return redirect('admin/login');
        }
        $error = ['code' => 1, 'msg' => '操作失败'];
        return view('shop.admin.failed', compact('error'));
    }

    public function change(Request $request)
    {
        $lang = 'zh_cn';
        $dir = 'admin';
        $filename = 'shop_config.php';
        require_once base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $filename;

        $path = base_path() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $filename;
        $str = '<?php return ' . var_export($_LANG, true) . ';';
        file_put_contents($path, $str);
    }

    public function tool()
    {
//        echo 1048575*2 >> 20;
//        return $this->adminUserRepository->test();
//        $snowId = new SnowFlake(31, 31);
//        echo $snowId->nextId();
//        dd($this->adminUserRepository->getAdminUserByPage(0));
//        dd(RedisCache::incr('gid'));
        list($msec, $sec) = explode(' ', microtime());
        $msectime = sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000000);
        echo $msectime;
    }
}
