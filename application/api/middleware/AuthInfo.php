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

        $token = $request->param('token');
        $request->middleware_user_id = $this->_checkUserInfo($token);


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