<?php
namespace app\admin\controller;

use think\Controller;

class Common extends Controller
{
    const VALIDATE_SCENE = 'admin_add';

    //当前登录者用户id
    protected $admin_id = 0;

    //是否是系统管理员登录
    protected $is_admin = false;

    //是否需要认证
    protected $is_need_auth = true;

    /*
     * 动作是否忽略登录-当且仅当 $this->$is_need_auth = true 有效
     * 每个动作均用小写，用逗号分割
     * */
    protected $ignore_auth = '';

    public function initialize()
    {
        if(session('?admin_info')){
            $this->admin_id = session('admin_info.admin_id');
            $this->is_admin = (bool)session('admin_info.is_admin'); //是否是管理员登录
        }

        if($this->is_need_auth===true){
            //当前动作
            $current_action = $this->request->action();
            $ignore_auth = strtolower($this->ignore_auth);
            if(strpos($ignore_auth, $current_action)===false && empty($this->admin_id)){
                if($this->request->isAjax()){
                    abort(403,json_encode(['code'=>0,'msg'=>'请先登录']));
                }else{
                    $this->error('请先登录',$this->is_admin?'index/login':'index/companylogin');
                }
            }
        }

        //绑定容器
        bind('current_login_is_admin',function(){
            return $this->is_admin;
        });
    }
}