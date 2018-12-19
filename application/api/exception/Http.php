<?php
namespace app\api\exception;

use think\exception\Handle;
use Exception;
use think\exception\ValidateException;

class Http extends Handle
{
    public function render(Exception $e)
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json(['code'=>422,'msg'=>$e->getError()]);
        }

        // 其他错误交给系统处理
        return json(['code'=>$e->getCode(),'msg'=>$e->getMessage()]);
    }

}