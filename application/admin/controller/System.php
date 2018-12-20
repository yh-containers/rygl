<?php
namespace app\admin\controller;

class System extends Common
{
    public function  quickCache($type)
    {
        $model = new \app\common\model\SysCache();

        $content = $model->getContent($type);
        return view('quickCache',[
            'content' => $content,
            'type' => $type,
            'type_info' => \app\common\model\SysCache::$type_info
        ]);
    }


    public function cacheAction()
    {
        $type = $this->request->param('type');
        $content = $this->request->param('content');
        if(is_array($content)){
            $content = json_encode($content);
        }
        $model = new \app\common\model\SysCache();
        $bool = $model->setContent($type,$content);
        return ['code'=>(int)$bool,'msg'=>$bool?'操作成功':'操作失败'];
    }
}