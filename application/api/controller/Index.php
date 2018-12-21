<?php
namespace app\api\controller;

class Index extends Common
{
    public function index()
    {
        return jsonOut('获取成功',1,['foo'=>'abc']);
    }
}

