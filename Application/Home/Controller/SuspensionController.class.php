<?php
namespace Home\Controller;
use Common\Controller\BaseController;

class SuspensionController extends BaseController{


    public function index()
    {
        $this->display('index/suspensionbusiness');
    }

}