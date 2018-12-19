<?php
namespace app\admin\controller;

use think\Controller;

class Common extends Controller
{
    const VALIDATE_SCENE = 'admin_add';
    //当前登录者用户id
    protected $admin_id = 0;

    public function initialize()
    {

    }
}