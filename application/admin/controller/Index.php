<?php
namespace app\admin\controller;


class Index extends Common
{
    protected $ignore_auth = 'login,companyLogin';


    public function index()
    {
        $model = new \app\common\model\Node();
        $node =  $model->tree($this->is_admin);
        return view('index',[
            'node'=>$node
        ]);
    }

    public function welcome()
    {
        return view('welcome',[

        ]);
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
        $this->redirect('index/login');
    }
}