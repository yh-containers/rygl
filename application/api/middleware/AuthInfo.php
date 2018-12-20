<?php
namespace app\api\middleware;

class AuthInfo
{

    public function handle($request, \Closure $next)
    {
        if (preg_match('~micromessenger~i', $request->header('user-agent'))) {
            $request->InApp = 'WeChat';
        } else if (preg_match('~alipay~i', $request->header('user-agent'))) {
            $request->InApp = 'Alipay';
        }
        //登录凭证
        $user_id = 0;
        $access_token = $request->header('access-token');
        if(!empty($access_token)) {
             $data = \app\common\model\Users::tokenDecrypt($access_token);
             if($data!==false) {
                 $user_id = $data['user_id'];
             }
        }

        $request->middleware_user_id = $user_id;


        return $next($request);
    }

    /*
     * 获取用户登录信息
     * */
    private function _checkUserInfo($token)
    {
        $token = base64_decode($token);
        $auth_info = explode('.',$token);
        if(count($auth_info)===4) {
            return $auth_info[0];
        }else{
            return 0;
        }
    }
}