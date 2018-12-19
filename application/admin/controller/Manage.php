<?php
namespace app\admin\controller;

class Manage extends Common
{
    /*
     * 管理员列表
     * */
    public function index()
    {
        $model = new \app\common\model\Admin();
        $list = $model->paginate();
        return view('index',[
            'list' => $list,
            'page' => $list->render(),
        ]);
    }
    /*
     * 管理员--添加
     * */
    public function add()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Admin();
        if($this->request->isAjax()) {
            $validate = new \app\common\validate\Admin();
            $validate->scene(self::VALIDATE_SCENE);

            $input_data = $this->request->param();
            if(!empty($input_data['id']) && empty($input_data['password'])){
                unset($input_data['password']);
            }
            return $model->actionAdd($input_data,$validate);
        }
        $model = $model->find($id);
        return view('add',[
            'model' => $model
        ]);
    }


    //管理员---删除
    public function del()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Admin();
        return $model->actionDel($id);
    }
}