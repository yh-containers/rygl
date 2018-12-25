<?php
namespace app\admin\controller;


class Index extends Common
{
    protected $ignore_auth = 'login,companyLogin,verify';


    public function index()
    {
        $model = new \app\common\model\Node();
        list($node,$link_key) =  $model->tree($this->is_admin);
        return view('index',[
            'node'=>$node,
            'link_key' => $link_key
        ]);
    }

    public function welcome()
    {
        return view('welcome',[

        ]);
    }

    /*
     * 验证码
     * */
    public function verify()
    {
        return view('verify');
    }


    /*
     * 用户登录--管理员登录
     * */
    public function login()
    {
        //处理登录
        if($this->request->isAjax() || $this->request->isPost()){
            $model = new \app\common\model\Admin();
            $input_data = $this->request->param();
            list($state,$msg) = $model->handleLogin($input_data);
            if(!$state) {
                $this->error($msg);
            }

            $this->redirect('index/index');

        }

        return view('login',[

        ]);
    }

    /*
     * 用户登录--公司登录
     * */
    public function companyLogin()
    {
        //处理登录
        if($this->request->isAjax() || $this->request->isPost()){
            $model = new \app\common\model\Admin();
            $input_data = $this->request->param();
            list($state,$msg) = $model->handleCompanyLogin($input_data);
            if(!$state) {
                $this->error($msg);
            }

            $this->redirect('index/index');

        }

        return view('companyLogin',[

        ]);
    }

    /*
     * 退出登录
     * */
    public function logout()
    {
        // 清除session（当前作用域）
        session(null);
        $url = $this->is_admin?'index/login':'index/companylogin';
        $this->redirect($url);
    }
}