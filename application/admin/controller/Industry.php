<?php
namespace app\admin\controller;


class Industry extends Common
{
    public function index()
    {
        $model = new \app\common\model\Industry();
        $list = $model->paginate();
        return view('index',[
            'list' => $list,
            'page' => $list->render(),
        ]);
    }
}