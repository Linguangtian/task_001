<?php
namespace Common\Controller;
use Common\Model\MemberModel;
use Think\Controller;
use Common\Model\LevelModel;
/**
 * Base基类控制器
 */
class BaseController extends Controller{

    public function _initialize(){

    }


    /**
     * 用户ID
     * @return int
     */
    public function get_member_id()
    {
        $member = session('member');
        if( isset($member['id']) && $member['id'] > 0 ) {
            return $member['id'];
        } else {
            return 0;
        }
    }

    /**
     * 用户名称
     * @return int
     */
    public function get_member_name()
    {
        $member = session('member');
        if( isset($member['nickname']) ) {
            return $member['nickname'];
        } else {
            return '';
        }
    }

   public function get_member_status()
    {
        $member = session('member');
        $status = M('member')->where(['id'=>$member['id']])->getField('user_status');
        if($status==2 ) {
            return '';
        } else {
            return true;
        }
    }

    /**
     * 用户名称
     * @return int
     */
    public function get_member_headimg()
    {
        $member = session('member');
        if( isset($member['head_img']) ) {
            return $member['head_img'];
        } else {
            return '';
        }
    }

    /**
     * 检测用户是否登录
     */
    public function is_login()
    {
        /*$member = session('member');
        if( !($this->get_member_id() > 0) ) {
            //微信登录
            if( is_wechat() ) {
                $wx = new MemberModel();
                $redirect_uri = U(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME,$_GET,'',true);
                $wx->wx_login($redirect_uri);
            }
        }*/

        if( !($this->get_member_id() > 0) ) {
            return false;
        } else {
            return true;
        }
    }

    /*
     *今日等级的注册数，是否到达上限 ，
     *
     * */
    public function limit_menber_reg($level=0){
        $level  =   intval($level);
        $member_level   = LevelModel::get_member_level();
        $today          = strtotime(date('Y-m-d',time()));
        if( $level==0 || $level<0 ){
            $total = M('member')->where('create_time >='.$today)->count();
        }else{
            $map                =   [];
            $map['p.create_time'] =   ['gt',$today];
            $map['r.level']       =    $level ;
            $total =  M('pay as p')->join(C('DB_PREFIX').'recharge as r on r.id=p.order_id','left')->where($map)->count();
        }
        return   $total>=$member_level[$level]['day_limit_member_num']?true:false;
    }


    //任务列表
    public function task_list(){
        $pageSize=10;
        $pageVal =( isset($_POST['page']) && $_POST['page'] >1)? $_POST['page']:1;
        $member_id = $this->get_member_id();
        $map = array();
        $map['t.status'] = 1;
        $map['t.end_time'] = ['gt',time()];
        $map['t.no_show'] = ['eq',0];
        $count = M('task as t')->where($map)->count();

        $pageNum =ceil($count/$pageSize);
        $page = ($pageVal-1)*$pageSize;
        if(!empty($member_id)){
            $task_apply_sql=' (select * from dt_task_apply where member_id = '.$member_id.') AS ta ON ta.task_id = t.id';
            $task_list = M('task as t')->field('t.*,t.max_num-apply_num as leftnum,ta.id as  ta_id,IF(ta.status=0,5,ta.status) as ta_status')
                ->join($task_apply_sql,'left')
                ->where($map)->order('task_on_top desc,t.id desc')->limit($page,$pageSize)->select();

        }else{
            $task_list = M('task as t')->field('t.*,t.max_num-apply_num as leftnum')  ->where($map)->order('task_on_top desc,t.id desc')->limit($page,$pageSize)->select();
        }
        $level_list = LevelModel::get_member_level();
        $tasklb_alias=C('CATEGORY');
        $status_alias=C('TASK_STATUS');
        foreach ($task_list as &$li){
            $li['level_name']   = $level_list[$li['level']]['name'];
            $li['tasklb_alias'] = $tasklb_alias[ $li['tasklb']];
            $li['task_logo'] =  '/tpl/Public/images/task_logo_'.$li['tasklb'].'.png';
            $li['ta_status']     = $status_alias[ $li['ta_status']];
        }
        $data = ['pageNum'=>$pageNum,'pageVal'=>$pageVal,'task_list'=>$task_list];

        return $data;


    }


}
