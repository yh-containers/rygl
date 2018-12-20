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
        $list = $model->with(['linkRole'])->paginate();
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

        //角色列表
        $model_role = new \app\common\model\SysRole();
        $role_list = $model_role->where('status',1)->select();

        return view('add',[
            'model' => $model,
            'role_list' => $role_list
        ]);
    }


    //管理员---删除
    public function del()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Admin();
        return $model->actionDel($id);
    }

    //角色--列表
    public function roles()
    {
        $model = new \app\common\model\SysRole();
        $list = $model->paginate();
        return view('roles',[
            'list' => $list,
            'page' => $list->render(),
        ]);
    }

    //角色--新增/编辑
    public function rolesAdd()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\SysRole();
        if($this->request->isAjax()) {
            $validate = new \app\common\validate\SysRole();
            $validate->scene(self::VALIDATE_SCENE);

            $input_data = $this->request->param();

            return $model->actionAdd($input_data,$validate);
        }
        $model = $model->find($id);
        return view('rolesAdd',[
            'model' => $model
        ]);
    }

    //角色---删除
    public function rolesDel()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\SysRole();
        return $model->actionDel($id);
    }
}