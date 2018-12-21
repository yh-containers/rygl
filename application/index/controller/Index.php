<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        phpinfo();
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }

    public function test()
    {
        dump(model('Admin')->select());
    }

    public function checkCurl()
    {
        dump(go_curl('http://app.uumhome.com/api.php/Public/startPage'));
    }
}
