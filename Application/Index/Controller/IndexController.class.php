<?php
namespace Index\Controller;

use Think\Controller;
use Common\Model\PayModel;

class IndexController extends Controller
{
    public function index()
    {
        echo U('Index/Index/show');
    }

    public function show()
    {
        echo U('dd');
    }


}