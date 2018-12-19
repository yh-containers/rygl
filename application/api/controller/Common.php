<?php
namespace app\api\controller;

use think\App;
use think\Container;

class Common
{
    protected $app;
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    protected $user_id=0;

    /*
     * 否需要登录
     * */
    protected $is_need_auth = false;

    /*
     * 动作是否忽略登录-当且仅当 $this->$is_need_auth = true 有效
     * 每个动作均用小写，用逗号分割
     * */
    protected $ignore_auth = '';

    public function __construct(App $app = null)
    {
        $this->app = $app?:Container::get('app');
        $this->request = $this->app['request'];
        $this->user_id = $this->request->middleware_user_id; //中间件处理返回用户id

        if($this->is_need_auth===true){
            //当前动作
            $current_action = $this->request->action();
            $ignore_auth = strtolower($this->ignore_auth);
            if(strpos($ignore_auth, $current_action)===false && empty($this->user_id)){
                abort(-1,'请先登录');
            }
        }
    }
}