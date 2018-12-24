<?php
namespace app\admin\controller;

class Industry extends Common
{
    //行业列表
    public function index()
    {
        $model = new \app\common\model\Industry();
        $list = $model->paginate();
        return view('index',[
            'list' => $list,
            'page' => $list->render(),
            'count'=> count($list),
        ]);
    }

    //行业操作-新增、编辑
    public function industryAdd()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Industry();
        if($this->request->isAjax()) {
            $validate = new \app\common\validate\Industry();
            $validate->scene(self::VALIDATE_SCENE);
            $input_data = $this->request->param();
            return $model->actionAdd($input_data,$validate);
        }
        $model = $model->find($id);
        return view('industryAdd',[
            'model' => $model
        ]);
    }

    //删除行业
    public function industryDel()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Industry();
        return $model->actionDel($id);
    }
}