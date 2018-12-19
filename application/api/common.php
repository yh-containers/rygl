<?php

/*
 * 响应输出
 * */
function jsonOut($msg='', $state_code=1, $data = [], $code = 200, $header = [])
{
    $options = [
        'msg'           =>  $msg,
        'state_code'    =>  $state_code,
    ];
    return \think\Response::create($data, '\app\api\response\JsonOut', $code, $header,$options);
}