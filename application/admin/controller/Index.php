<?php
namespace app\admin\controller;


class Index extends Common
{
    public function index()
    {
        $model = new \app\common\model\Node();
        $node =  $model->tree();
        return view('index',[
            'node'=>$node
        ]);
    }

    public function welcome(){
        return view('welcome',[

        ]);
    }
}