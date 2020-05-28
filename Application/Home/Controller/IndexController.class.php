<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;
use Org\Net\Mobile;
use Common\Model\LevelModel;
use Common\Model\SystemConfigModel;

class IndexController extends HomeBaseController{

    public function index()
    {
        $data=$this->task_list();
        if (true === IS_AJAX) {
            $data=['pageNum'=>$data['pageNum'],'list'=>$data['task_list']];
            $this->ajaxReturn($data);
        }

        $notice =  M('page')->where('index_notice=1')->find();
        $this->assign ( 'notice', $notice );
        $this->assign ( 'pageNum', $data['pageNum'] );
        $this->assign ( 'pageVal', $data['pageVal'] );
        $this->assign('task_list', $data['task_list']);
        $title = sp_cfg('website');
        $this->assign('title', $title);
        $this->display();
    }

    /**
     * webuploader 上传文件
     */
    public function ajax_upload(){
        // 根据自己的业务调整上传路径、允许的格式、文件大小
        ajax_upload('/Uploads/images/');
    }

    /**
     * webuploader 上传demo
     */
    public function webuploader(){
        // 如果是post提交则显示上传的文件 否则显示上传页面
        if(IS_POST){
            p($_POST);die;
        }else{
            $this->display();
        }
    }

    /**
     * xiao5    2019年7月9日10:56:46   关于
     */
    public function about()
    {
        $info = SystemConfigModel::get();
        $this->assign('wx_qrcode', $info['wx_kf_qrcode']);

        $this->assign('wx_kf', $info['wx_kf']);

        $this->display();
    }
}

