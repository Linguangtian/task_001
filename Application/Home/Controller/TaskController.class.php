<?php

namespace Home\Controller;

use Common\Controller\HomeBaseController;
use Common\Model\LevelModel;
use Common\Model\MemberModel;

class TaskController extends HomeBaseController
{
    public function index()
    {
        $list = M('news')->field('id,title,create_time')->order('sort asc,id asc')->limit(10)->select();
        $this->assign('list', $list);

        $this->assign('title', '任务大厅');
        $this->display();
    }

    public function lists_sub()
    {
        $data=$this->task_list();
        if (true === IS_AJAX) {
            $data=['pageNum'=>$data['pageNum'],'list'=>$data['task_list']];
            $this->ajaxReturn($data);
        }
        $this->assign ( 'pageNum', $data['pageNum'] );
        $this->assign ( 'pageVal', $data['pageVal'] );
        $this->assign('task_list', $data['task_list']);
        $this->assign('title', '任务大厅');

        $this->display();
    }

    public function lists_lb()
    {
        $level = I('get.lb');
        //供应信息
        $task_list['type_0'] = M('task')->where(array('type' => 0, 'status' => 1, 'tasklb' => $level))->limit(15)->order('id desc')->select();
        //需求信息
        $task_list['type_1'] = M('task')->where(array('type' => 1, 'status' => 1, 'tasklb' => $level))->limit(15)->order('id desc')->select();
        $this->assign('task_list', $task_list);
        $lb = $level == 1 ? '抖音' : '快手';
        $this->assign('title', $lb . '-任务大厅');
        $this->display();
    }


    public function lists()
    {
        $map = array();
        $level = I('get.level');
        if (!empty($cid)) {
            $map['level'] = $level;
        }
        $type = I('get.type');
        if ($type != '') $map['type'] = $type;
        $this->_list('task', $map);

        if ($type == 0) {
            $title = "发布的供应信息";
        } elseif ($type == 1) {
            $title = "发布的需求信息";
        } else {
            $title = "任务大厅";
        }
        $this->assign('title', $title);
        $this->display();
    }

    public function show()
    {
        $id = intval(I('get.id'));
        $show = M('task')->find($id);
        $this->assign('show', $show);


        //检测是否已领取
        $member_id = $this->get_member_id();

        $check_apply = M('task_apply')->where(array('task_id' => $id, 'member_id' => $member_id))->find();

        if ($show['status'] == 0 && $show['end_time'] < time()) {
            $this->handle_deteriortion_task();
            $check_apply['status'] = -3;
        }

        $no_link = 0;
        if ($show['end_time'] > time()) {
            if (empty($check_apply)) {
                if ($show['apply_num'] < $show['max_num']) {
                    $btn_status = 1;
                    $status_text = "领取任务";
                    $is_ling = 0;
                } else {
                    $btn_status = 0;
                    $status_text = "任务名额已满";
                    $is_ling = 0;
                    $no_link = 1;
                }
            } elseif ($check_apply['status'] == 2) {
                $btn_status = 0;
                $status_text = "已完成";
                $is_ling = 1;
            } elseif ($check_apply['status'] == -2) {
                $btn_status = 0;
                $status_text = "已放弃";
                $is_ling = 0;
            } elseif ($check_apply['status'] == -3) {
                $btn_status = 0;
                $status_text = "已过期";
                $is_ling = 0;
                $no_link = 1;
            } else {
                $btn_status = 0;
                $status_text = "已领取，点击提交";
                $is_ling = 1;
            }
        } else {
            $btn_status = 0;
            $status_text = "任务已过期";
            $is_ling = 0;
        }

        if ($check_apply['status'] == 0) {
            $redirect_url = U('submission_task_do', ['id' => $check_apply['id']]);
        } else {
            $redirect_url = U('Member/apply_show', ['id' => $check_apply['id']]);
        }
        $this->assign('redirect_url', $redirect_url);
        $this->assign('is_ling', $is_ling);
        $this->assign('no_link', $no_link);
        $this->assign('btn_status', $btn_status);
        $this->assign('status_text', $status_text);
        $this->assign('member_client_info', session('member_client_info'));
        $this->display();
    }

    /**
     * 任务列表
     * staus 0 已领取
     * status 2 已完成
     * status -2 放弃
     *is_ens 1 放弃
     */
    public function submission_task()
    {
        if (!$this->is_login()) {
            $this->assign('is_login', 0);
            $this->redirect('Public/login');
        }
        $member_id = $this->get_member_id();

        //过期了任务更新状态
        $this->handle_deteriortion_task();


        $pageSize = 40;
        $pageVal = (isset($_POST['page']) && $_POST['page'] > 1) ? $_POST['page'] : 1;
        $map = array();
        $map['a.member_id'] = $member_id;
        $map['a.status'] = ['in', '0,2'];

        $model = M('task_apply');
        $count = $model->alias('a')->where($map)->count();

        $pageNum = ceil($count / $pageSize);
        $page = ($pageVal - 1) * $pageSize;


        $near_time = time() - 24 * 3600;
        $where_str = "  not exists ( select id from  dt_task_apply where status = 2 and member_id=$member_id  and update_time < $near_time  and id=a.id ) ";

        $list = $model->alias('a')
            ->join(C('DB_PREFIX') . 'task as b on a.task_id = b.id', 'left')
            ->where($map)
            ->where($where_str)
            ->field('a.*,b.title,b.tasklb,b.level')
            ->order('a.status asc')->limit("$page , $pageSize")
            ->select();

        $level_list = LevelModel::get_member_level();
        $tasklb_alias=C('CATEGORY');
        foreach ($list as &$item) {
            $item['level_name']   = $level_list[$item['level']]['name'];
            $item['tasklb_alias'] = $tasklb_alias[ $item['tasklb']];
            $item['task_logo'] =  '/tpl/Public/images/task_logo_'.$item['tasklb'].'.png';
            $item['cha_time'] = $item['end_time'] - time();
            $item['update_time'] = date('Y-m-d', $item['update_time']);
        }


        if (true === IS_AJAX) {
            $data = ['pageNum' => $pageNum, 'list' => $list];
            $this->ajaxReturn($data);
        }


        $this->assign('pageVal', $pageVal);
        $this->assign('pageNum', $pageNum);
        $this->assign("list", $list);

        $this->assign('title', '选择提交的任务');
        $this->display();
    }

    //提交任务
    public function submission_task_do()
    {

        if (!$this->is_login()) {
            $this->assign('is_login', 0);
            $this->redirect('Public/login');
        }
        $member_id = $this->get_member_id();

        $id = I('id');


        $apply_data = M('task_apply')->find($id);


        if ($member_id != $apply_data['member_id']) {
            $this->error('没有权限');
        }

        if ($apply_data['status'] != 0) {
            $this->error('请勿重复提交');
        }

        if ($apply_data['end_time'] < time()) {
            $this->error('任务已过期');
        }

        if (IS_POST) {
            $file = I('post.file');
            if (empty($file)) {
                $this->error('请上传任务截图');
            }

            $data['id'] = $id;
            $data['dianzan'] = I('post.dianzan') ? 1 : 0;
            $data['guanzhu'] = I('post.guanzhu') ? 1 : 0;
            $data['pinglun'] = I('post.pinglun') ? 1 : 0;
            if ($data['dianzan'] == 0 && $data['guanzhu'] == 0 && $data['pinglun'] == 0) {
                $this->error('请勾选操作类型');
            }


            $data['file'] = $file;
            $data['update_time'] = time();
            $data['status'] = 2;//状态改为已提交
            $result = M('task_apply')->save($data);
            if ($result) {
                $inc_point = M('point_set')->getField('day_point');
                if ($inc_point) {
                    if (!M('member_point_log')->where(['member_id' => $member_id, 'type' => 3, 'create_time' => ['gt', strtotime(date('Y-m-d'))]])->find()) {
                        $model_member = new MemberModel();
                        $model_member->incPoint($member_id, $inc_point, 3, '完成当日首次任务', $no = '');
                    }
                }
                $this->add_task_price($id);
                $this->success('任务提交成功', U('submission_task'));
            } else {
                $this->error('任务提交失败');
            }
        } else {

            $task_id = $apply_data['task_id'];
            $apply_data['task_title'] = M('task')->where(array('id' => $task_id))->getField('title');
            $apply_status = C('APPLY_STATUS');
            $apply_data['apply_status'] = $apply_status[$apply_data['status']];
            $this->assign("apply_data", $apply_data);
            $this->display();
        }
    }

    /**
     * 领取任务
     */
    public function get_task()
    {
        if (!$this->is_login()) {
            $this->error("请先登录");
        }

        $member_id = $this->get_member_id();
        $id = intval(I('post.id'));
        if (!($id > 0)) {
            $this->error("参数错误");
        }
        $member_level = M('member')->where(array('id' => $this->get_member_id()))->getField('level');
        $task_data = M('task')->field('level,price,max_num,apply_num,end_time')->where(array('id' => $id))->find();
        if ($task_data['end_time'] < time()) {
            $this->error('该任务已过期');
        }
        $level_list = LevelModel::get_member_level();
        if ($member_level < $task_data['level']) {
            $this->error("您的会员等级不能领取{$level_list[$task_data['level']]['name']}的任务。");
        }

        //检测是否已领取
        $check_apply = M('task_apply')->where(array('task_id' => $id, 'member_id' => $member_id))->find();
        if ($check_apply) {
            $this->error('您已经领取过该任务了');
        }

        //检测名额
        if ($task_data['apply_num'] >= $task_data['max_num']) {
            $this->error("任务名额已满");
        }

        $task_limit = M('level')->where(array('level' => $member_level))->getField('day_limit_task_num');

        $task_limit_count = $this->today_task_total();


        if ($task_limit_count >= $task_limit && $task_limit > 0) {
            $this->error('您当前的会员等级每天只能领取' . $task_limit_count . '个任务，您今日已经达到上限了哦！');
        }



       /* $point_limit_count = $task_limit_count;
        $point_set = M('point_set')->field('renwu_count, renwu_point')->find();
        $member_point = M('member')->where(['id' => $member_id])->getField('point');

        if ($member_point < $point_set['renwu_point'] && $point_limit_count >= $point_set['renwu_count']) {
            $this->error('信用分低于' . $point_set['renwu_point'] . '，每天只能报名完成' . $point_set['renwu_count'] . '次任务');
        }*/

        //写入数据
        $time = time();
        $data['task_id'] = $id;
        $data['member_id'] = $member_id;
        $data['price'] = $task_data['price'];
        $data['status'] = 0;
        $data['create_time'] = $time;
        $data['update_time'] = $time;
        $data['end_time'] = $task_data['end_time'];
        $result = M('task_apply')->add($data);
        if ($result) {
            M('task')->where(array('id' => $id))->setInc('apply_num', 1);
            $id = M('task')->getLastInsID();
            $this->success($id);
        } else {
            $this->error("领取失败");
        }
    }

    public function showw()
    {
        $id = I('id');
        $show = M('page')->where(['id' => $id])->find();
        $this->assign('title', $show['title']);
        $this->assign('show', $show);
        $this->display();
    }

    /**
     * xiao5    2019年7月10日13:43:31  放弃任务
     */
    public function abandon()
    {
        $id = I('id');

        $res = M('task_apply')->where(['id' => $id])->save(['status' => -2, 'is_end' => 1]);
        if ($res) {
            $this->ajaxReturn(['status' => 1, 'msg' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => 0, 'msg' => '操作失败']);
        }
    }


    function appxiazai()
    {
       $is_app = intval(I('is_app'));
        if($is_app==1){
            $this->redirect('Home/Index/index');
            exit;
        }
        header('Content-Type: text/html; charset=UTF-8');
        header("Cache-Control: no-store, no-cache");
        $app_download_url = '';
        $dev = new \Org\Net\Mobile();
        if ($dev->is('iOS')) {
            $app_download_url = sp_cfg('app_ios_download');
        } else {
            $app_download_url = sp_cfg('app_download');
        }


        $target =$app_download_url?$app_download_url:U('/Home/Index/index');
         if ((strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) || (strpos($_SERVER['HTTP_USER_AGENT'], 'QQ') !== false)) {
            echo
            <<<Eof
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" style="font-size: 100px;">
<head id="Head1"><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>站点提示</title>
    <!--禁止全屏缩放-->
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <!--不显示成手机号-->
    <meta name="format-detection" content="telephone=no" />
    <!--删除默认的苹果工具栏和菜单栏-->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <!--解决UC手机字体变大的问题-->
    <meta name="wap-font-scale" content="no" />
    <!--控制状态栏显示样式-->
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<link href="/tpl/Public/css/tager-index.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="//cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript">
$(function ($) {
    setRootFontSize();
});
window.onresize = function () {
    setRootFontSize();
}
function setRootFontSize() {
    $('html').css('font-size', document.body.clientWidth / 15 +'px');
}
    </script>
</head>
<body style="background-color: #f5f5f5;">
    <div id="Pan_WX">
        <!--微信访问-->
        <div class="fc_jt">
            <img src="/tpl/Public/images/jt.png"></div>
               <div class="fc_wz">
                点击屏幕右上角[...]<br />
                用 浏览器 打开 
            </div>  
          <div class="fc_tp">
            <img src="/tpl/Public/images/wx_az.png"></div>
		</div>
</body>
</html>
Eof;
        } else {
            exit('
            <script>window.location.href="' . $target . '";</script>
            ');
        }

    }


    private function add_task_price($id)
    {
        $_data = M('task_apply')->where(array('id' => $id))->find();
        //$model_member = new MemberModel();

        //任务人收入
        $this->add_sale($id, $_data['price'], $_data['member_id'], 1, '任务收入');
        //$model_member->incPrice($_data['member_id'],$_data['price'],1,'任务收入',$id);

        $member_data = M('member')->field('id,username,level,p1,p2,p3')->where(array('id' => $_data['member_id']))->find();

        //是否开启等级高低返佣规则
        $open_level_rule = intval(sp_cfg('open_level_rule'));

        //给直接上级返利
        if ($member_data['p1'] > 0) {
            $bfb_1 = floatval(sp_cfg('bfb_1'));
            if ($bfb_1 > 0) {
                $price_1 = $_data['price'] * $bfb_1 / 100;
                $price_1 = sprintf("%.2f", $price_1);
                if ($open_level_rule == 1) {
                    $p1_level = M('member')->where(array('id' => $member_data['p1']))->getField('level');
                    if ($p1_level >= $member_data['level']) {
                        $this->add_sale($id, $price_1, $member_data['p1'], 2, '一级提成，来源用户' . $member_data['username'], $member_data['id']);
                    }
                } else {
                    $this->add_sale($id, $price_1, $member_data['p1'], 2, '一级提成，来源用户' . $member_data['username'], $member_data['id']);
                }
            }
        }

        //二级返利
        if ($member_data['p2'] > 0) {
            $bfb_2 = floatval(sp_cfg('bfb_2'));
            if ($bfb_2 > 0) {
                $price_2 = $_data['price'] * $bfb_2 / 100;
                $price_2 = sprintf("%.2f", $price_2);
                if ($open_level_rule == 1) {
                    $p2_level = M('member')->where(array('id' => $member_data['p2']))->getField('level');
                    if ($p2_level >= $member_data['level']) {
                        $this->add_sale($id, $price_2, $member_data['p2'], 2, '二级提成，来源用户' . $member_data['username'], $member_data['id']);
                    }
                } else {
                    $this->add_sale($id, $price_2, $member_data['p2'], 2, '二级提成，来源用户' . $member_data['username'], $member_data['id']);
                }
            }
        }

        //三级返利
        if ($member_data['p3'] > 0) {
            $p3_level = M('member')->where(array('id' => $member_data['p3']))->getField('level');
            $ji_3 = intval(sp_cfg('ji_3'));
            if (($ji_3 == 0) || ($ji_3 == 2 && $p3_level >= 2)) {
                $bfb_3 = floatval(sp_cfg('bfb_3'));
                if ($bfb_3 > 0) {
                    $price_3 = $_data['price'] * $bfb_3 / 100;
                    $price_3 = sprintf("%.2f", $price_3);
                    if ($open_level_rule == 1) {
                        if ($p3_level >= $member_data['level']) {
                            $this->add_sale($id, $price_3, $member_data['p3'], 2, '三级提成，来源用户' . $member_data['username'], $member_data['id']);
                        }
                    } else {
                        $this->add_sale($id, $price_3, $member_data['p3'], 2, '三级提成，来源用户' . $member_data['username'], $member_data['id']);
                    }
                }
            }
        }
    }

    private function add_sale($apply_id, $price, $member_id, $type, $remark, $from_member_id = 0)
    {
        //添加直销收入记录
        $data['member_id'] = $member_id;
        $data['from_member_id'] = $from_member_id;
        $data['order_id'] = $apply_id;
        $data['price'] = $price;
        $data['remark'] = $remark;
        $data['create_time'] = time();
        $data['type'] = $type;
        $result = M('sale_list')->add($data);
        if ($result) {
            //添加金额变动记录
            $model_member = new MemberModel();
            $model_member->incPrice($member_id, $price, $type, $remark, $result);
        } else {
            throw_exception('添加收益失败');
        }
    }

    function task_list_index(){

        $this->display('task_list');
    }

}