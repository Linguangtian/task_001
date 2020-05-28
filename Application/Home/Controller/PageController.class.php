<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;
use Common\Model\ArticleModel;
use Common\Model\SystemConfigModel;

class PageController extends HomeBaseController{

    public function index()
    {
        $list = M('page')->field('id,title,create_time')->where('recommend=1')->order('sort asc,id asc')->select();
        $this->assign('title', '会员介绍');
        $this->assign('list', $list);
        $this->display();
    }

    public function show()
    {
        $id = intval(I('get.id'));
        $show = M('page')->find($id);
        $this->assign('title', $show['title']);
        $this->assign('show', $show);
        $this->display();
    }

    //发送客服留言
    function  kefuSmg(){

        if( !$this->is_login() ) {
            $data=['msg'=>'请先登录'];
            $this->ajaxReturn($data);
        }
        $member_id = $this->get_member_id();
        $member =  M('member')->where('id = '.$member_id)->find();



        $data=[];
        $data['msg']=I('post.msg','htmlspecialchars');
        $data['member_id']=$member_id;
        $data['is_read']=0;
        $data['create_time']=date('Y-m-d H:i:s',time());
        $data['member_name']=$member['username'];
         if(M('member_message')->add($data)){
             $data=['msg'=>'发送成功'];
             $this->ajaxReturn($data);
         }else{
             $data=['msg'=>'发送失败'];
             $this->ajaxReturn($data);
         }





    }


    /**
     * xiao5    2019年7月10日09:15:06  微信客服二维码
     */
    public function kefu()
    {
        $info = SystemConfigModel::get();
        $this->assign('wx_qrcode', $info['wx_kf_qrcode']);
        $this->display();
    }
}

