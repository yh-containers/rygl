<?php
namespace app\admin\controller;

class Users extends Common
{
    public function index()
    {
        $model = new \app\common\model\Users();
        $list = $model->paginate();
        return view('index',[
            'list' => $list,
            'page' => $list->render(),
        ]);
    }
}