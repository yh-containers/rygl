<?php
namespace app\admin\controller;


class Index extends Common
{
    public function index()
    {
        return view('index',[

        ]);
    }

    public function welcome(){
        return view('welcome',[

        ]);
    }
}