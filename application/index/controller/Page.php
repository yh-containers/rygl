<?php
namespace app\index\controller;

use think\Controller;

class Page extends Controller
{
    //注册协议
    public function content($type='reg_protocol')
    {
        $model = new \app\common\model\SysCache();
        $content = $model->getContent($type);

        return view('content',[
            'content' => $content,
            'type' => $type,
            'type_info' => \app\common\model\SysCache::$type_info
        ]);
    }
}