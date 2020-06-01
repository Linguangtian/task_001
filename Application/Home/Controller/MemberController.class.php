<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;
use Common\Model\LevelModel;
use Common\Model\MemberModel;
use Common\Model\PhonecodeModel;
use Common\Model\PostsApplyModel;
use  Api\Controller\AlipayController;
use  Api\Controller\YipayController;
use  Api\Controller\EzfpayController;
use Think\Model;

class MemberController extends HomeBaseController{

    protected $member_id;

    /**
     * 初始化方法
     */
    public function _initialize(){
        parent::_initialize();

        if( !$this->is_login() ) {
            $this->redirect('Public/login');
            //$this->error('请先登录', U('Public/login'));
        }

        $this->member_id = $this->get_member_id();
    }

    /**
     * 个人中心首页
     */
    public function index()
    {
        $data           = M('member')->find($this->member_id);
        $member_level   = LevelModel::get_member_level();

        $level_num      =   $member_level[$data['level']]['day_limit_task_num'];
        $level_name     =   $member_level[$data['level']]['name'];

        $today_task_total   = $this->today_task_total();
        $remain_task_num    = $level_num - $today_task_total;
        $remain_task_num    =  $remain_task_num>0?$remain_task_num:0;
        $this->assign('remain_task_num',$remain_task_num);
        $this->assign('level_num',$level_num);
        $this->assign('level_name',$member_level[$data['level']]['name']);
        $this->assign('data', $data);
        //今日佣金
        $total_price['today'] = M('sale_list')->where("member_id={$this->member_id} and TO_DAYS(from_unixtime(create_time)) = TO_DAYS(now())")->sum('price');
        $total_price['month'] = M('sale_list')->where("member_id={$this->member_id} and DATE_FORMAT(from_unixtime(create_time),'%Y%m') = DATE_FORMAT(CURDATE(),'%Y%m')")->sum('price');
        $total_price['all'] = M('sale_list')->where("member_id={$this->member_id}")->sum('price');
        $this->assign('total_price', $total_price);

        //下级新增人数
        $total_team['today'] = M('member')->where(" (p1={$this->member_id} or p2={$this->member_id} or p3={$this->member_id}) and (TO_DAYS(from_unixtime(create_time)) = TO_DAYS(now())) ")->count();
        $total_team['month'] = M('member')->where(" (p1={$this->member_id} or p2={$this->member_id} or p3={$this->member_id}) and (DATE_FORMAT(from_unixtime(create_time),'%Y%m') = DATE_FORMAT(CURDATE(),'%Y%m'))")->count();
        $total_team['all'] = M('member')->where("p1={$this->member_id} or p2={$this->member_id} or p3={$this->member_id}")->count();
        $this->assign('total_team', $total_team);

        //我的推荐人
        $parent_name= M('member')->where(array('id'=>$data['p1']))->getField('username');

        $this->assign('parent_name', $parent_name);
        $this->assign('level_name', $level_name);

        $msg = I('msg');
        if($msg){
            $this->assign('msg',$msg);
        }





        //新消息条数
        if( $data['role'] == 1 ) {
            $role_type = 2;
        } else {
            $role_type = 1;
        }
        $notice_view_time = intval($data['notice_view_time']);
        $where = "(( member_id = {$this->member_id} ) or ( (is_system = 1 and role_type = 0) or ( is_system = 1 and role_type = {$role_type} ) ) ) and create_time > {$notice_view_time} ";
        $notice_num = M('notice')->where($where)->count();
        $this->assign('notice_num', intval($notice_num));

        $point_level = M('point_level')->order('point desc')->find();
        $total_point = $point_level['point'];
        $step_point = ceil($total_point / 2);
        $setStep = $data['point'] <= $step_point ? 2 : 3;

        $this->assign('setStep', $setStep);

        $this->display();
    }

    /**
     * 系统消息
     */
    public function notice()
    {
        $member = M('member')->field('notice_view_time,role')->find($this->member_id);
        if( $member['role'] == 1 ) {
            $role_type = 2;
        } else {
            $role_type = 1;
        }
        $member_id = $this->member_id;
        $where = "( member_id = {$member_id} ) or ( (is_system = 1 and role_type = 0) or ( is_system = 1 and role_type = {$role_type} ) )";

        $list = M('notice')->where($where)->order('id desc')->limit(100)->select();
        foreach( $list as &$_list ) {
            if( $_list['is_system'] == 1 ) {
                $view_style = $member['notice_view_time'] < $_list['create_time'] ? 0 : 1;
            } else {
                $view_style = $_list['has_view'];
            }
            $_list['view_style'] = $view_style;
        }

        $this->assign('list', $list);

        //将消息都设置为已读
        M('notice')->where(array('member_id'=>$this->member_id))->save(array('has_view'=>1));
        M('member')->where(array('id'=>$this->member_id))->setField('notice_view_time', time());

        $this->display();
    }


    /**
     * 个人信息
     */
    public function info()
    {
        $this->display();
    }

    /**
     * 编辑个人信息
     */
    public function info_edit()
    {  $member= M('member')->where(array('id' => $this->member_id))->find();
        if( IS_POST ) {
            $field = I('field');
            $value = I('value');
            $field_only = [ 'nickname', 'phone'];
            if (in_array($field, $field_only)) {
                $field_value = M('member')->where(array('id' => $this->member_id))->getField($field);
                if (!empty($field_value))  $this->error('无法修改');
            }
            $data['id'] = $this->member_id;
            $data[$field] = $value;
            //更新银行卡信息
            if( $field == 'bank_number' ) {
                $data['bank_name'] = I('post.bank_name');
                $data['subbranch_name'] = I('post.subbranch_name');
                $data['bank_user'] = I('post.bank_user');

                if( !I('alipay_src') && !I('wechat_pay_src') ){
                    $this->error('请上传收款码');
					
                }
                if(I('alipay_src')){
                    $data['alipay_src'] = htmlspecialchars(I('alipay_src'));
					  if( !empty($member['alipay_src']) &&  $data['alipay_src']!=$member['alipay_src']   ){
                        $this->error('支付宝收款信息已存在，无法修改');
                        exit;
                    }
                }
                if(I('wechat_pay_src')){
                    $data['wechat_pay_src'] = htmlspecialchars(I('wechat_pay_src'));
					 if(!empty($member['wechat_pay_src'])&&  $data['wechat_pay_src']!=$member['wechat_pay_src'] ){
                        $this->error('微信收款信息已存在，无法修改');
                        exit;
                    }
                }
            }

            //更新地址
            if( $field == 'address' ) {
                $city_ids = explode(',',I('post.city_ids'));
                $city_names = explode(' ',I('post.city_names'));
                if( count($city_ids) != 3 || count($city_names) != 3 ) {
                    $this->error('请选择地址信息');
                }
                $data['province_id'] = $city_ids[0];
                $data['city_id'] = $city_ids[1];
                $data['area_id'] = $city_ids[2];
                $data['province'] = $city_names[0];
                $data['city'] = $city_names[1];
                $data['area'] = $city_names[2];
            }



            $res = M('member')->save($data);

            if( $res ) {
                $member = M('member')->find($this->member_id);
                $member[$field] = $value;
                session('member', $member);
                if( I('f') == 'tixian' ) {
                    $this->success('更新成功', U('Member/tixian'));
                } else {
                    $this->success('更新成功', U('info'));
                }
            } else {
                $this->error('更新失败或无修改');
            }

        } else {
            $field = I('get.field');
            $member = session('member');
            $value = $member[$field];
            $this->assign('field',$field);
            $this->assign('member',$member);
            $this->assign('value',$value);
            $this->assign('wechat_pay_src',$member['wechat_pay_src']);
            if( $field == 'address' ) {
                $city_ids = array($member['province_id'],$member['city_id'],$member['area_id']);
                if( !($member['province_id'] > 0 ) ) {
                    $city_ids[0] = M('area')->where(array('title'=>$member['province']))->getField('area_id');

                    if( !($member['city_id'] >0 ) ) {
                        $city_ids[1] = M('area')->where(array('title'=>$member['city']))->getField('area_id');
                    }
                }
                $this->assign('city_ids',$city_ids);
                $tpl = "info_edit_address";
            }elseif( $field == 'bank_number' ) {
                $banks = array( "支付宝","微信");
            
                $this->assign('banks',$member);
                $this->assign('banks',$banks);
                $tpl = 'info_edit_bank';
            } elseif( $field == 'sex' ) {
                $tpl = 'info_edit_sex';
            } else {
                $tpl = '';
            }

            $this->display($tpl);
        }
    }

    /**
     * 我的申请
     */
    public function apply()
    {
        $map = array();
        $map['member_id'] = $this->member_id;

        $model = M('task_apply');
        $count = $model->where($map)->count();
        $page = sp_get_page_m($count, 10);//分页
        $firstRow = $page->firstRow;
        $listRows = $page->listRows;

        $list = $model->alias('a')
            ->join(C('DB_PREFIX').'task as b on a.task_id = b.id','left')
            ->where(array('a.member_id'=>$this->member_id))
            ->field('a.*,b.title')
            ->order('a.id desc')->limit("$firstRow , $listRows")
            ->select();

        $this->assign("Page", $page->show());
        $this->assign("list", $list);
        $this->assign('count', $count);

        //已经申请的状态
        $apply_status = C('APPLY_STATUS');
        $this->assign ( 'apply_status', $apply_status );

        $this->display();
    }

    /**
     * 申请详情
     */
    public function apply_show()
    {
        $id = I('get.id');
        $apply_data = M('task_apply')->find($id);

        $task_id = $apply_data['task_id'];
        $apply_data['task_title'] = M('task')->where(array('id'=>$task_id))->getField('title');
        $apply_status = C('APPLY_STATUS');
        $apply_data['apply_status'] = $apply_status[$apply_data['status']];
        $this->assign("apply_data", $apply_data);

        $this->display();
    }

    /**
     * 账单
     */
    public function bill()
    {
        $weekarray=array("日","一","二","三","四","五","六");

        $map['member_id'] = $this->member_id;
        //$map['is_pay'] = 1;
        $list = $this->_list('member_price_log', $map, '', 'id desc', 500);
        $news_list = array();
        foreach( $list as $k=>$v ) {
            $day = date('Y-m', $v['create_time']);
            $news_list[$day][] = $v;
        }
        foreach( $news_list as &$_list ) {
            foreach( $_list as &$_list2 ) {
                $_list2['w'] = $weekarray[date('w', $_list2['create_time'])];
            }
        }
        $this->assign('news_list', $news_list);
        $this->display();
    }

    /**
     * 余额
     */
    public function balance()
    {
        $data = $this->get_member_data();
        $balance = $data['price'];
        $this->assign('balance', $balance);
        $this->display();
    }

    /**
     * 充值界面
     */
    public function recharge_do()
    {
        if( IS_POST ) {
            $price = I('price');
            if( !is_numeric($price) || !($price > 0) ) {
                $this->error('价格必须为数字，且大于0');
            }

            if( $price%10 != 0 ) {
                $this->error('充值金额必须为10的倍数');
            }

            $data = array();
            $order_no = $this->create_order_no();
            $data['order_no'] = $order_no;
            $data['member_id'] = $this->member_id;
            $data['price'] = $price;
            $data['create_time'] = time();
            $data['is_pay'] = 0;
            $data['payment_id'] = 1;
            $data['key_type'] = '2';//充值
            $insert_id = M('recharge')->add($data);
            if( $insert_id ) {
                //跳转到微信支付
                $yipay   =  new YipayController();
                $url =  $yipay->pay($order_no);
                $data=['error'=>0,'url'=>$url];
                $this->ajaxReturn($data);
                exit;



            }
        } else {
            $member= M('member')->where(array('id' => $this->member_id))->find();
            $this->assign('member', $member);
            $this->display();
        }
    }
    //生成订单号
    private function create_order_no() {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2017] . $this->member_id . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }

    /**
     * 申请提现
     */
    public function tixian()
    {
        $map = ['member_id' => $this->member_id];
        $map['price'] = ['elt', '50'];
        $tixian_limit = M('member_tixian')->where($map)->find();
        $tixian_limit =$tixian_limit?1:0;
        if( IS_POST ) {
            $member_data = M('member')->field('price,bank_name,bank_user,bank_number,alipay_src,wechat_pay_src')->find($this->member_id);
            if( empty($member_data['bank_name']) || empty($member_data['bank_user']) || empty($member_data['bank_number']) ) {
                $this->error('请先完善您的提现信息');
            }
            $member_price = $member_data['price'];
            $price = floatval(I('post.price'));
            if( !($price >= 10) ) {
                $this->error('提现金额不能少于10');
            }
            if( !($member_price > 0) ) {
                $this->error('没有可提现的余额');
            }
            if( $price > $member_price ) {
                $this->error('余额不足');
            }
            if( $price%10 != 0 ) {
                $this->error('提现金额必须为10的倍数');
            }
            if($tixian_limit==1&&$price<=50){
                $this->error('无小额提现权限，提现金额必须大于50');
            }
            $map =[
                'member_id'=>$this->member_id,
                'price'=>$price,
            ];
            $today = strtotime(date('Y-m-d',time()));
            $map['create_time'] = ['gt', $today];
            /*每日每次提现额只有一次机会*/
            $tixian_limit = M('member_tixian')->where($map)->find();
            if($tixian_limit>0){
                $this->error('今日'.$price.'的提现额机会已使用，请另选其他额度');
            }

            $data = array();
            $data['member_id'] = $this->member_id;
            $data['price'] = $price;
            $data['create_time'] = time();
            $data['status'] = 0;
            //   $data['charge'] = sp_cfg('charge');
            $data['charge'] = 2;
            $data['actual_price'] = $price > 50 ? $price - $data['charge'] : $price;
            $data['payment_code'] = $member_data['bank_name'] == '微信' ? $member_data['wechat_pay_src'] : $member_data['alipay_src']; //收款码
            $data['extract_way'] = $member_data['bank_name'];
            $data['account_username'] = $member_data['bank_user'];
            $data['account_number'] = $member_data['bank_number'];
            $result = M('member_tixian')->add($data);

            if( $result ) {
                $model = new MemberModel();
                $res = $model->decPrice($this->member_id, $price, 100, '申请提现');
                if( $res ) {
                    $this->success('提交申请成功，等待管理员审核', U('index'));
                } else {
                    $this->error('提交失败');
                }
            } else {
                $this->error('提交失败');
            }
        } else {
            $data = M('member')->find($this->member_id);
            $data['bank_number_last'] = substr($data['bank_number'],-4);

            $today  = strtotime(date('Y-m-d',time()));
            $map    = ['member_id'=>$this->member_id];
            $map['create_time'] = ['gt', $today];
            $limit_num = M('member_tixian')->field('price')->where($map)->select();
            $extract_money_level=C('EXTRACT_MONEY_LEVEL');
            if($tixian_limit==1){
                $extract_money_level['10']['actived']='actived';
                $extract_money_level['50']['actived']='actived';
            }
            foreach ($limit_num as $li){
                $extract_money_level[intval($li['price'])]['actived']='actived';
            }

            $this->assign('extract_money_level', $extract_money_level);
            $this->assign('data', $data);
            $this->display();
        }
    }

    public function tixian_log()
    {
        $TIXIAN_STATUS = C('TIXIAN_STATUS');

        $status = I('get.status');
        $start_date = I('get.start_date');
        $end_date = I('get.end_date');

        $map = array();
        $map['member_id'] = $this->member_id;

        if( $status != '' ) $map['a.status'] = $status;

        //搜索时间
        if( !empty($start_date) && !empty($end_date) ) {
            $start_date = strtotime($start_date . "00:00:00");
            $end_date = strtotime($end_date . "23:59:59");
            $map['_string'] = "( a.create_time >= {$start_date} and a.create_time < {$end_date} )";
        }

        $model = M('member_tixian')->alias('a');
        $count = $model->where($map)->count();
        $page = sp_get_page($count, 50);//分页
        $firstRow = $page->firstRow;
        $listRows = $page->listRows;

        $list = M('member_tixian')->alias('a')->join(C('DB_PREFIX').'member as c on a.member_id = c.id','left')
            ->where($map)
            ->field('a.*,c.nickname,c.phone,c.bank_name,c.bank_user,c.bank_number')
            ->order('a.id desc')->limit("$firstRow , $listRows")
            ->select();
        foreach( $list as &$_list ) {
            $_list['status_text'] = $TIXIAN_STATUS[$_list['status']];
            /*$price = abs($_list['price']);
            $_list['price'] = $price;
            $_list['charge'] = sp_cfg('charge');
            $_list['actual_price'] = $price - $price * sp_cfg('charge')/100;*/
        }
        $this->assign('list',$list);
        $this->assign('tixian_status',$TIXIAN_STATUS);
        $this->assign('get',$_GET);
        $this->display();
    }

    /**
     * 生成二维码
     */
    public function qrcode(){
        $url  = U('Index/index',array('smid'=>$this->member_id),'',true);
        qrcode($url,6);
    }

    /**
     * 修改密码
     */
    public function password()
    {
        if( IS_POST ) {
            $d = I('post.');

            if( empty($d['old_password']) ) {
                $this->error('请输入当前密码。');
            }
            if(strlen($d['password'])<6){
                $this->error('密码长度不小于6位');
            }

            if( empty($d['password']) ) {
                $this->error('请输入当前密码。');
            }
            if( empty($d['repassword']) ) {
                $this->error('请再次输入新密码。');
            }
            if( $d['password'] != $d['repassword'] ) {
                $this->error('两次密码不一致。');
            }
            //短信验证码
            /*if( !PhonecodeModel::check_phone_code($code_type, $d['phone'], $code) ) {
                $this->error('短信验证码错误。');
            }*/

            $password = sp_encry($d['password']);

            $old_password = M('member')->where(array('id'=>$this->member_id))->getField('password');
            if( $old_password != sp_encry($d['old_password']) ) {
                $this->error('当前密码不正确。');
            }


            $res = M('member')->where(array('id'=>$this->member_id))->setField('password', $password);
            if( $res ) {
                $this->success('修改成功', U('index'));
            } else {
                $this->error('密码修改失败');
            }
        } else {
            $this->display();
        }
    }

    /**
     * 升级VIP
     */
    public function vip()
    {


        $member = $this->get_member_data();
        $member_level = LevelModel::get_member_level();
        $member_vip_price = $member_level[$member['level']]['price'];


        $last=array_slice($member_level,-1,1);
        $is_best_level=0;
        if($member['level']==$last['level']){
         $is_best_level=1;
        }


        if( IS_POST ) {
            $level = intval(I('post.level'));
            $price = $member_level[$level]['price'];
            $price  = $price - $member_vip_price;
            if( !($level > 0) ) {
                $this->error('请选择要升级的级别');
            }
            if($this->limit_menber_reg($level)){
                $this->error('今日升级'.$member_level[$level]['name'].'已到达上限！');
            }
            if( !($price > 0) ) {
                $this->error('价格参数错误');
            }
            $payment_type = I('payment_type');
            $data = array();
            $order_no = 'VIP'.$this->create_order_no();
            $data['order_no'] = $order_no;
            $data['member_id'] = $this->member_id;
            $data['price'] = $price;
            $data['create_time'] = time();
            $data['is_pay'] = 0;
            $data['level'] = $level;
            $data['payment_type'] = $payment_type;
            $insert_id = M('recharge')->add($data);
            if( $insert_id ) {
                $pay_method= sp_cfg('pay_method');
                if($pay_method=='other_method'){
                   /* $url=U('api/pay96/pay',array('order_no'=>$order_no));
                    $data=['error'=>0,'url'=>$url];
                    $this->ajaxReturn($data);*/


              /*      $yipay   =  new EzfpayController();
                    $url =  $yipay->pay($order_no);
                    $data=['error'=>0,'url'=>$url];
                    $this->ajaxReturn($data);*/


                 //在线支付 个人免签
                    $yipay   =  new YipayController();
                    $url =  $yipay->pay($order_no);
                    $data=['error'=>0,'url'=>$url];
                    $this->ajaxReturn($data);



                }else{
                    //线下扫码转账
                    //$this->success('提交成功，前往支付',U('pay',array('out_trade_no'=>$order_no)));
                    $order_info = M('recharge')->where(array('order_no'=>$order_no))->find();
                    $order_info['order_sn'] =$order_no;
                    $order_info['order_amount'] =$order_info['price'];
                    $order_info['consignee'] ='1111111111';
                    $alipay   =  new AlipayController();
                    $url =  $alipay->get_code($order_info);
                    $data=['error'=>0,'url'=>$url];
                    $this->ajaxReturn($data);
                }
            }
        } else {
            $member['level_name'] = $this->get_level_name($member['level']);


            $this->assign('member_level',$member_level);
            $this->assign('is_best_level',$is_best_level);
            $this->assign('member_vip_price',$member_vip_price);
            $this->assign('pay_method',sp_cfg('pay_method'));
            $this->assign('member',$member);
            $this->display();
        }
    }

    public function pay()
    {
        $out_trade_no = I('get.out_trade_no');
        $data = M('recharge')->where(array('order_no'=>$out_trade_no))->find();
        if( empty($data) ) {
            $this->error('单号不存在');
        }
        $this->assign('data',$data);
        $this->assign('out_trade_no',$out_trade_no);
        $this->display();
    }

    public function pay_screenshot()
    {
        if( IS_POST ) {
            $order_no = I('post.order_no');
            if( empty($order_no) ) {
                $this->error('请选择要提交的订单');
            }

            if( $_FILES ) {
                $file = ajax_upload('/Uploads/pay_image/',1,$this->member_id);
            } else {
                $this->error('请上传任务截图');
            }

            $data['file'] = $file;
            $data['order_no'] = $order_no;
            $data['member_id'] = $this->member_id;
            $data['create_time'] = time();
            $data['status'] = 0;//未处理
            $result = M('recharge_screenshot')->add($data);
            if($result) {
                $this->success('提交成功，等待管理员审核',U('index'));
            } else {
                $this->error('提交失败');
            }
        } else {
            //待支付记录
            $pay_list = M('recharge')->where(array('member_id'=>$this->member_id,'is_pay'=>0))->group('price')->select();
            $this->assign('pay_list',$pay_list);
            $this->display();
        }
    }

    /**
     * 我的团队
     */
    public function team()
    {
        $map = array();
        $rank = intval(I('get.rank',1));

        if( $rank == 2 ) {
            $map['p2'] = $this->member_id;
        } elseif( $rank == 3 ) {
            $map['p3'] = $this->member_id;
        } else {
            $map['p1'] = $this->member_id;
        }

        $model = M('member');
        $count = $model->where($map)->count();
        $page = sp_get_page_m($count, 50);//分页
        $firstRow = $page->firstRow;
        $listRows = $page->listRows;
        $list = $model->field('id,username,head_img,create_time,level')
            ->where($map)
            ->order('level desc,id desc')->limit("$firstRow , $listRows")
            ->select();
        $member_level = LevelModel::get_member_level();


        foreach($list as &$_list) {
            $where = array();
            $where['p1'] = $_list['id'];
            $_list['number'] = M('member')->where($where)->count();
            $_list['level_name'] = $member_level[$_list['level']]['name'];
        }



        $this->assign("Page", $page->show());
        $this->assign("list", $list);
        $this->assign('count', intval($count));
        $this->assign("rank", $rank);

        $this->assign('count_1', M('member')->where(array('p1'=>$this->member_id))->count());
        $this->assign('count_2', M('member')->where(array('p2'=>$this->member_id))->count());
        $this->assign('count_3', M('member')->where(array('p3'=>$this->member_id))->count());

        $this->display();
    }

    //销售提成记录
    public function sale()
    {
        $day = date('Y-m-d');
        $start_date = I('get.start_date');
        $end_date = I('get.end_date');

        $type = 1;
        $member_id = $this->member_id;

        $where = '';
        //搜索时间
        if( !empty($start_date) && !empty($end_date) ) {
            $_start_date = strtotime($start_date . "00:00:00");
            $_end_date = strtotime($end_date . "23:59:59");
            $where = " and create_time >= {$_start_date} and create_time < {$_end_date} ";
        }
        $where .=' and type='.$type;
        //日期内的收入
        $sql = "select SUM(price) as total from `dt_sale_list` where `member_id` = {$member_id} {$where} ";
        $dao = new Model();
        $sum_data = $dao->query($sql);
        $today_total_price = $sum_data[0]['total'];
        $this->assign("today_total_price", floatval($today_total_price));


        $map = array();
        $map['a.member_id'] = $member_id;
        $map['a.type'] = $type;

        $model = M('sale_list');
        $count = $model->alias('a')->where($map)->count();
        $page = sp_get_page_m($count, 99);//分页

        $firstRow = $page->firstRow;
        $listRows = $page->listRows;



        $list = $model->alias('a')
            ->join(C('DB_PREFIX').'member as b on a.from_member_id = b.id','left')
            ->where($map)
            ->field('a.*,b.username,b.phone')
            ->order('a.id desc')->limit("$firstRow , $listRows")
            ->select();

        $this->assign("Page", $page->show());



        $this->assign("list", $list);
        $this->assign('count', intval($count));
        $this->assign("type", $type);
        $this->assign("day", $day);

        $this->display();
    }



    //团队收益
    public function teamSale(){
        $type = I('get.type')==3?3:2;
        $member_id = $this->member_id;



        $where =' and type in (2,3)';
        //日期内的收入
        $sql = "select SUM(price) as total from `dt_sale_list` where `member_id` = {$member_id} {$where} ";
        $dao = new Model();
        $sum_data = $dao->query($sql);
        $today_total_price = $sum_data[0]['total'];
        $this->assign("today_total_price", floatval($today_total_price));




        $map = array();
        $map['a.type'] = $type;
        $map['a.member_id'] = $member_id;
        $model = M('sale_list');

        $count = $model->alias('a')->where($map)->count();
        $page = sp_get_page_m($count, 50);//分页



        $firstRow = $page->firstRow;
        $listRows = $page->listRows;
        $list = $model->alias('a')
            ->join(C('DB_PREFIX').'member as b on a.from_member_id = b.id','left')
            ->where($map)
            ->field('a.*,b.username,b.phone')
            ->order('a.id desc')->limit("$firstRow , $listRows")
            ->select();

        $this->assign("Page", $page->show());


        $this->assign("list", $list);
        $this->assign('count', intval($count));
        $this->assign("type", $type);

        $this->display();

    }



    //用户信息
    private function get_member_data()
    {
        $data = M('member')->find($this->member_id);
        return $data;
    }

    /**
     * 会员等级名称
     * @param $level
     */
    private function get_level_name($level) {
        $member_level = LevelModel::get_member_level();
        return $member_level[$level]['name'];
    }


    //图片上传
    public function uploadImage(){
        $file_name_front  = I('field');
        $file_name_front  = $file_name_front?$file_name_front:'upload'; //重命名文件前缀名

        $allow_file_types = '|GIF|JPG|PNG|BMP|JPEG|';
        $file = $_FILES['img'];



        
        if( !strrpos($file['name'], '.')||strrpos($file['name'], '.')===false){
            $file['name']=$file['name'].'.png';
        }



        if (!$file) {

            $data = array('error' => 1, 'msg' => '上传文件为空');
            ajaxReturn($data);
        }
        if ((isset($file['error']) && ($file['error'] == 0)) || (!isset($file['error']) && ($file['tmp_name'] != 'none'))) {
            if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {


                $data = array('error' => 1, 'msg' => '您上传了一个非法的文件类型');
                ajaxReturn($data);
            }
            else {
                $ext_file_type=explode('.', $file['name']);
                $ext = array_pop($ext_file_type);                           //提取文件的后缀
                $file_dir = './Uploads/images/pay' ;               //生成一个新的文件夹存放

                if (!is_dir($file_dir)) {
                    mkdir($file_dir,0777,true);
                }

                $file_name = $file_dir . '/'.$file_name_front.'_' . gmtime() . '.' . $ext;   //临时存放在内存上传的文件  复制到我们需要的目录下

                if (move_upload_file($file['tmp_name'], $file_name)) {
                    $img_url = $file_name;
                    $oss_img_url = str_replace('./', '/', $img_url);
                    $data = array('error' => 0, 'msg' => '图片替换成功', 'path' => $oss_img_url);
                    ajaxReturn($data);
                }
                else {
                    $data = array('error' => 1, 'msg' => '上传失败');
                    ajaxReturn($data);
                }
            }
        }
    }

}

