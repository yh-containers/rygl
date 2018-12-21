<?php
namespace app\admin\controller;


class Company extends Common
{
    public function index()
    {

        $keyword = request()->get('keyword');
        $model = new \app\common\model\Company();
        $list = $model->wherelike('name',"%".$keyword."%")->paginate();
        return view('index',[
            'list' => $list,
            'page' => $list->render(),
            'count'=> count($list),
        ]);
    }

    /*
     * 管理员--添加
     * */
    public function companyAdd()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Company();
        if($this->request->isAjax()) {
            $validate = new \app\common\validate\Company();
            $validate->scene(self::VALIDATE_SCENE);

            $input_data = $this->request->param();
            return $model->actionAdd($input_data,$validate);
        }
        $model = $model->find($id);
        return view('companyAdd',[
            'model' => $model
        ]);
    }

    public function companyDel()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Company();
        return $model::destroy($id);
    }

    public function department()
    {

        $keyword = request()->get('keyword');
        $model = new \app\common\model\Department();
        $list = $model->wherelike('name',"%".$keyword."%")->paginate();
        return view('department',[
            'list' => $list,
            'page' => $list->render(),
            'count'=> count($list),
        ]);
    }

    /*
     * 管理员--添加
     * */
    public function departmentAdd()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Department();
        if($this->request->isAjax()) {
            $validate = new \app\common\validate\Department();
            $validate->scene(self::VALIDATE_SCENE);

            $input_data = $this->request->param();
            return $model->actionAdd($input_data,$validate);
        }
        $model = $model->find($id);
        return view('departmentAdd',[
            'model' => $model
        ]);
    }

    public function departmentDel()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Department();
        return $model::destroy($id);
    }
}