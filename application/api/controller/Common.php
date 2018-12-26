<?php
namespace app\api\controller;

use app\common\model\Base;
use think\App;
use think\Container;

class Common
{
    const SCENE = 'api_opt';

    protected $app;
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;
    //
    protected $user_id=0;
    //公司id
    protected $company_id = 0;


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
        $this->company_id = $this->request->middleware_company_id; //中间件处理返回用户id

        if($this->is_need_auth===true){
            //当前动作
            $current_action = $this->request->action();
            $ignore_auth = strtolower($this->ignore_auth);
            if(strpos($ignore_auth, $current_action)===false && empty($this->user_id)){
                abort(-1,'请先登录');
            }
        }
    }


    protected function checkUserAuth(Base $model=null,array $auth=[])
    {
        //验证操作权限
        $auth = $auth?$auth:$model::AUTH_FIELD;

        $user_model = new \app\common\model\Users();
        $user_model = $user_model->findOrEmpty($this->user_id);
        //检测权限
        $bool = $user_model->checkMenuAuth($auth);
        return [ $bool,$user_model];
    }
}