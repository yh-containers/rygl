<?php
namespace app\admin\controller;

class CompanySys extends Common
{
    //签到设置
    public function setting()
    {


        $idPServerAuthUri="https://sso.szftedu.cn/connect/authorize";
        $clientId='af0af206b6d943faa278a504cf34eef8';
        $response_type=$this->request->param('response_type','code');
        $redirectUri='https://ddz.szftedu.cn/';
        $scope='openid';
//        $state=uniqid();
//        $redirectUri = urlencode($redirectUri);
        $state = 'abcdefs';
//        dump(session_id());exit;
        $url = $idPServerAuthUri . "?client_id=" . $clientId ."&response_type=" . $response_type ."&redirect_uri=" . $redirectUri."&scope=" . $scope . "&state=" . $state;
        $serialize_data = ['state'=>$state];
//        $serialize_data = json_encode(['state'=>$state]);
//        dump($serialize_data);
        $serialize = serialize($serialize_data);
//        dump($serialize);
        cookie('TempCookie',$serialize);
//        dump($url);exit;
        if($response_type=='code'){
            $url=$idPServerAuthUri."?client_id=".$clientId."&response_type=".$response_type. "&returnUrl=".$redirectUri."&scope=".$scope."&state=".$state;
            request()->withHeader(['TempCookie'=>$state]);
            $this->redirect($url);
        }else{
            dump($response_type);exit;
        }

//        echo $url;
//        dump(file_get_contents($url));exit;

        $model = new \app\common\model\Company();
        $model = $model->find($this->com_id);
        return view('setting',[
            'model'=>$model
        ]);
    }


    //保存签到信息
    public function signAction()
    {
        $sign_mac = $this->request->param('sign_mac');
        $model = new \app\common\model\Company();
        list($state,$msg) = $model->setMacSign($this->com_id,$sign_mac);

        return ['code'=>(int)$state,'msg'=>$msg];
    }
}