<?php

namespace Home\Controller;

use Common\Controller\PublicBaseController;
use Common\Model\PhonecodeModel;
use Common\Model\SmsModel;
use Org\Net\Jssdk;
use Think\Model;
use Common\Model\PayModel;
class PublicController extends PublicBaseController
{


    public function complete(){
        file_put_contents('Runtime/alipay.txt', json_encode(1111) . "/r/n", FILE_APPEND);

        $order_sn=I('id');
        if($order_sn){

            $sql = "select * from  codepay_order  where `pay_id` =\"{$order_sn}\" and status=2 limit 1";
            $dao = new Model();
            $info = $dao->query($sql);
            if($info){
                $d = M('recharge')->where(array('order_no'=>$order_sn))->find();
                $pay_model = new PayModel();
                $pay_model->pay_vip_success($d['id'], 'codepay', '66');
                $this->redirect('Home/Member/index',array('success'=>1));

            }
        }

        header('Location://'.$_SERVER['SERVER_NAME']);
    }









    public function getcode()
    {
        $tel = trim($_REQUEST['tel']);
        $code = mt_rand(100000, 999999);

        if (!$this->checkMobile($tel)) {
            echo json_encode(array('msg' => '手机号码错误', 'code' => 0));
            exit;
        }
        $yzmtime = session('yzmtime');
        if ($yzmtime) {
            if ($lefttime = time() - $yzmtime < 60) {
                if (!$this->checkMobile($tel)) {
                    echo json_encode(array('msg' => $lefttime . '秒后重试', 'code' => 0));
                    exit;
                }
            }
        }





        session('yzmcode', $code);

        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $smsapi = "http://api.smsbao.com/";
        $user = "gangwan140914"; //短信平台帐号
        $pass = md5("xiaochuan140914"); //短信平台密码

        $user = "03421148"; //短信平台帐号
        $pass = md5("03421148"); //短信平台密码
        $user = "gxyd"; //短信平台帐号
        $pass = md5("gxyd++"); //短信平台密码


        $user = "a563547351"; //短信平台帐号
        $pass = md5("159951qq"); //短信平台密码

        $todaytime = date("Ymd");
        $customer_ip=get_real_ip();


        $phone_num = M('sms_num')->where(['phone'=>$tel,'sendtime'=>$todaytime])->count();
        $customer_ip_num = M('sms_num')->where(['customer_ip'=>$customer_ip,'sendtime'=>$todaytime])->count();


        $sms_log=[
            'phone'=>$tel,
            'sendtime'=>date('Y-m-d H:i:s'),
            'customer_ip'=>$customer_ip
        ];
        M('sms_log')->add($sms_log);





        if($this->limit_menber_reg()){
            echo json_encode(array('msg' => '今日注册人数已到达上限', 'code' => 0));
            exit;
        }

        if($phone_num>1||$customer_ip_num>1){
            echo json_encode(array('msg' => '您今天发送次数过多', 'code' => 0));
            exit;
        }

        $content = "【热门邦】您的验证码为{$code}，验证码5分钟内有效。";
        $phone = $tel;//要发送短信的手机号码
        $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $phone . "&c=" . urlencode($content);
        $result = file_get_contents($sendurl);
        if ($result == '0') {

            $sms_log=[
                'phone'=>$phone,
                'sendtime'=>$todaytime,
                'customer_ip'=>$customer_ip
            ];
            M('sms_num')->add($sms_log);


            echo json_encode(array('msg' => '短信发送成功', 'code' => 1));
            exit;
        } else {
            echo json_encode(array('msg' => '短信发送失败:' . $statusStr[$result], 'code' => 0));
            exit;
        }

        /*
                include dirname(__FILE__) . '/Alisms.class.php';
                $Alisms = new \Alisms('LTAI17wCRrzxDYa6', 'deln1BPGQO7onAjTA5Rnk0OqYXXDZb', '点点网', 'SMS_168590830');
                $arr = $Alisms->sendSms($tel, $code);


                if ($arr['Message'] == "OK" && $arr['Code'] == "OK") {
                    echo json_encode(array('msg' => '短信发送成功', 'code' => 1));
                    exit;
                } else {

                    echo json_encode(array('msg' => '短信发送失败:' . $arr['Message'], 'code' => 0));
                    exit;
                }*/

        /*
                header('content-type:text/html;charset=utf-8');
                $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
                $smsConf = array(
                    'key' => 'b0044997fa6c2435cb99405f6c451358', //您申请的APPKEY
                    'mobile' => $tel, //接受短信的用户手机号码
                    'tpl_id' => '147086', //您申请的短信模板ID，根据实际情况修改
                    'tpl_value' => '#code#=' . $code //您设置的模板变量，根据实际情况修改
                );
                $content = $this->juhecurl($sendUrl, $smsConf, 1); //请求发送短信

                if ($content) {
                    $result = json_decode($content, true);
                    $error_code = $result['error_code'];
                    if ($error_code == 0) {
                        //状态为0，说明短信发送成功

                        session('yzmtime', time());

                        echo json_encode(array('msg' => '短信发送成功', 'code' => 1));
                        exit;
                    } else {
                        //状态非0，说明失败
                        $msg = $result['reason'];
                        # echo "短信发送失败(".$error_code.")：".$msg; exit;

                        echo json_encode(array('msg' => '短信发送失败:' . $msg, 'code' => 0));
                        exit;

                    }
                } else {
                    //返回内容异常，以下可根据业务逻辑自行修改
                    # echo "请求发送短信失败";  exit;
                    echo json_encode(array('msg' => '请求发送短信失败:' . $msg, 'code' => 0));
                    exit;
                }*/


    }

    function juhecurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }


    function checkMobile($str)
    {
        $pattern = "/^(13|15|14|16|17|18|19)\d{9}$/";
        if (preg_match($pattern, $str)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 注册
     */
    public function reg()
    {

        if (IS_POST) {
            if($this->limit_menber_reg()){
                $this->error('今日注册人数已到达上限！');
            }

            $member_model = M('member');
            $time = time();
            $d = I('post.');
            $username = trim($d['username']);
            $nickname = trim($d['nickname']);
            $password = trim($d['password']);
            $repassword = trim($d['repassword']);
            $invite_code = intval($d['invite_code']);
            $code = trim($d['code']);
            if ($username == '') {
                $this->error('请输入要注册的账户。');
            }


            if (empty($code)) {
                $this->error('请输入短信验证码。');
            }

            if ($code != session('yzmcode')) {
                $this->error('短信验证码不正确。');
            }
            if ($invite_code  == '') {
                $this->error('请输入邀请码。');
            }


            if ($nickname == '') {
                $this->error('请输入您的姓名。');
            }
            if ($password == '') {
                $this->error('请输入密码。');
            }
            if ($repassword == '') {
                $this->error('请出入确认密码。');
            }
            if ($password != $repassword) {
                $this->error('两次密码输入不相同。');
            }
            if (strlen($password)<6) {
                $this->error('密码长度不小于6位。');
            }
            if ($invite_code != '') {
                $check_code = $member_model->where(array('id' => $invite_code))->find();
                if (empty($check_code)) {
                    $this->error('邀请码不存在。');
                }
            }

            $check_username = $member_model->where(array('username' => $username))->find();
            if ($check_username) {
                $this->error('该账号已被注册。');
            }
            $data = array();
            $data['username'] = $username;
            $data['nickname'] = $nickname;
            $data['password'] = sp_encry($password);
            $data['p1'] = $invite_code;
            $data['create_time'] = $time;
            $data['last_login_time'] = $time;


            if (!empty($invite_code)) {
                $parent_pids = $member_model->where(array('id' => $invite_code))->getField('pids');
                $pids = !empty($parent_pids) ? $parent_pids . ',' . $invite_code : $invite_code;
                //保留10层关系
                $pids = explode(',', $pids);
                $pids = array_slice($pids, -10);
                $pids = implode(',', $pids);
                $data['pids'] = $pids;

                //推荐人的团队总数加1
                $data1 = array(
                    'id' => $invite_code,
                    'p1_num' => array('exp', "`p1_num`+1"),
                    'p_num' => array('exp', "`p_num`+1")
                );
                $member_model->save($data1);

                //二级
                $p2 = $member_model->where(array('id' => $invite_code))->getField('p1');
                if (intval($p2) > 0) {
                    $data['p2'] = intval($p2);

                    //推荐人团队总数加1
                    $data2 = array(
                        'id' => $p2,
                        'p2_num' => array('exp', "`p2_num`+1"),
                        'p_num' => array('exp', "`p_num`+1")
                    );
                    $member_model->save($data2);
                }

                //往上3级 （1级）
                if (intval($p2) > 0) {
                    $p3 = $member_model->where(array('id' => $p2))->getField('p1');
                    if (intval($p3) > 0) {
                        $data['p3'] = intval($p3);

                        //推荐人团队总数加1
                        $data3 = array(
                            'id' => $p3,
                            'p3_num' => array('exp', "`p3_num`+1"),
                            'p_num' => array('exp', "`p_num`+1")
                        );
                        $member_model->save($data3);
                    }
                }
            }

            $res = $member_model->add($data);
            $id = $res;
            if ($res) {
                $user = $member_model->find($id);
                session('member', $user);

                // xiao5    2019年7月8日17:38:54   注册送信用分
                $point_set = M('point_set')->find();
                if (!empty($point_set['reg_point'])) {
                    M('member')->where(['id' => $id])->save(['point' => $point_set['reg_point'], 'jia_point' => $point_set['reg_point']]);
                    M('member_point_log')->add(['member_id' => $id, 'point' => $point_set['reg_point'], 'remark' => '注册赠送信用分', 'create_time' => time(), 'type' => 1]);
                }

                // xiao5    2019年7月8日18:23:26   加推荐关系表
                if (!empty($invite_code)) {
                    $this->loopGuanXi($id, $invite_code, 1);
                }

                $this->success('注册成功', U('task/appxiazai',array('is_app'=>$d['is_app'])));
            } else {
                $this->error('注册失败');
            }
        } else {

            if ($this->is_login()) {
                $this->redirect('index/index');
            }

            $protocol_content = '';
            $reg_protocol_id = sp_cfg('reg_protocol_id');

            if ($reg_protocol_id) {
                $protocol_content = M('page')->where(['id' => $reg_protocol_id])->getField('content');

            }
            $this->assign('protocol_content', $protocol_content);

            $invite_code = I('get.smid');
            $this->assign('invite_code', $invite_code);
            $title = '用户注册';
            $this->assign('title', $title);
            $this->display();
        }
    }

    /**
     * 忘记密码
     */
    public function forget_pwd()
    {
        if (IS_POST) {
            $member_model = M('member');
            $time = time();
            $d = I('post.');
            $username = trim($d['username']);
            $password = trim($d['password']);
            $repassword = trim($d['repassword']);
            $code = trim($d['code']);
            if ($username == '') {
                $this->error('请输入要注册的账户。');
            }

            if (empty($code)) {
                $this->error('请输入短信验证码。');
            }

            if ($code != session('yzmcode')) {
                $this->error('短信验证码不正确。');
            }

            if ($password == '') {
                $this->error('请输入密码。');
            }

            if (strlen($password)<6 ) {
                $this->error('密码最少6位。');
            }

            if ($repassword == '') {
                $this->error('请出入确认密码。');
            }
            if ($password != $repassword) {
                $this->error('两次密码输入不相同。');
            }

            $check_username = $member_model->where(array('username' => $username))->find();
            if (!$check_username) {
                $this->error('没有此账号');
            }
            $data = array();
            $data['password'] = sp_encry($password);
            $data['last_login_time'] = time();

            $res = $member_model->where(['username' => $username])->save($data);
            if ($res) {
                $this->success('修改成功', U('login'));
            } else {
                $this->error('修改失败');
            }
        } else {

            $title = '用户注册';
            $this->assign('title', $title);
            $this->display();
        }
    }

    /**
     * 添加关系表
     */
    function loopGuanXi($u_id, $p_id, $dai)
    {
        if (!$u_id or !$p_id or !$dai) {
            return 0;
        }
        $p_user = M('member')->where(['id' => $p_id])->find(); // 上一代的节点人
        if ($p_user) {

            $guanxi['member_id'] = $u_id;
            $guanxi['p_id'] = $p_id;
            $guanxi['dai'] = $dai;
            $guanxi['level'] = 0;
            $guanxi['p_level'] = $p_user['level'];
            $guanxi['create_time'] = time();

            M('member_guanxi')->add($guanxi);

            $dai++;
            return $this->loopGuanXi($u_id, $p_user['p1'], $dai);
        } else {
            return 1;
        }
    }

    /**
     * 登录
     */
    public function login()
    {
        if (IS_POST) {
            $referer = I('post.referer');
            if (empty($referer)) {
                $referer = base64_encode(U('Index/index'));
            }
            $referer = base64_decode($referer);

            $username = trim(I('post.username'));
            $password = trim(I('post.password'));
            if ($username == '' && $password == '') {
                $this->error('请输入登录账户和密码。');
            }
            if ($username == '') {
                $this->error('请输入登录账户。');
            }
            if ($password == '') {
                $this->error('请输入密码。');
            }
//            echo sp_encry($password);exit;
            $result = M('member')->where(array('username' => $username, 'password' => sp_encry($password)))->find();
            if ($result) {

                if ($result['user_status'] == 2) {
                    $this->error('已被封号');
                    exit;
                }

                session('member', $result);
                setcookie('user_login_token', '', time()-1);
                setcookie('user_login_token', $result['id'], time() + (3600 * 24 * 7),'/');
                echo json_encode(array('status' => 1, 'info' => '登录成功', 'url' => $referer));
                exit;
            } else {
                setcookie('user_login_token', '', time()-1,'/');


                $this->error('登录失败，用户名或密码错误！');
            }
        } else {
            $referer = I('get.referer');
            if (empty($referer)) {
                if (strpos($referer, 'logout') !== false) {
                    $referer = $_SERVER['HTTP_REFERER'];
                }
            }
            if (!empty($referer)) {
                $referer = base64_encode($referer);
            }

            $this->assign('title', '登录');
            $this->assign('referer', $referer);
            $this->display();
        }
    }






    /*
     * 微信授权登录
     * */

    public function wechatLogin(){
        $result = array('code'=>1,'msg'=>'','redirect_url'=>'');

        $wx_detail =I('post.wehct_data');

        file_put_contents($wx_detail, $wx_detail);

        $wx_info = json_decode($wx_detail,true);
        $openid = $wx_info['openid'];


        $user_info = get_connect_user_new($openid, $wx_info['unionid']);



    }





    /**
     * 注销
     */
    public function logout()
    {
        if (isset($_SESSION['member'])) {
            unset($_SESSION['member']);
            unset($_SESSION['member_client_info']);
            session_destroy();
            setcookie('user_login_token', '', time()-1,'/');
            $this->redirect('Index/index');
            //$this->success('退出成功！', U('Public/login'));
        } else {
            $this->error('已经登出！', U('Public/login'));
        }
    }

    /**
     * 检测手机号
     * @param $phone
     * @param $tip
     * @return bool
     */
    public function check_phone($phone, &$tip)
    {
        if (empty($phone)) {
            $phone = I('phone');
        }
        if (empty($phone)) {
            $tip = '手机号码不能为空。';
            return false;
        }
        if (!is_phone($phone)) {
            $tip = '手机号码格式不正确。';
            return false;
        }
        $count = M('member')->where(array('phone' => $phone))->count();
        if (intval($count) > 0) {
            $tip = '该手机号码已被注册。';
            return false;
        }
        return true;
    }

    /**
     * 发送短信
     */
    public function send_phone_code()
    {

        $has_send_phone_code = session('has_send_phone_code');
        if (isset($has_send_phone_code['expire']) && $has_send_phone_code['expire'] > time()) {
            $this->error('短信发送过于频繁');
        }

        $code_type = I('code_type');
        $phone = I('phone');

        if (!is_phone($phone)) {
            $this->error('手机号码不正确');
        }

        //如果是注册类型先检测手机号码是否还能注册
        if ($code_type == 'reg') {
            if (!$this->check_phone($phone, $tip)) {
                $this->error($tip);
            }
        }

        $code = rand(1000, 9999);
        $content = "您的验证码为" . $code;
        //发送短信
        $error_msg = '';
        $smsModel = new SmsModel();
        $has_send_sms = $smsModel->send($phone, $content, $error_msg);
        //$has_send_sms = Sms2Model::send($phone,$content,$error_msg);
        if ($has_send_sms) {
            PhonecodeModel::set_phone_code($code_type, $phone, $code);
            session('has_send_phone_code', array('has' => 'yes', 'expire' => time() + 10));
            $this->success('验证码发送成功');
        } else {
            $this->error($error_msg);
        }
    }

    /**
     * 推广记录
     */
    public function share()
    {
        if( !$this->is_login() ) {
            $this->assign('is_login', 0);
            $this->redirect('Public/login');
        }


        $member_id = I('get.smid');
        if (empty($member_id)) {
            $member_id = $this->get_member_id();
        }
        if (is_wechat()) {
            $config = C('WEIXINPAY_CONFIG');
            $jssdk = new Jssdk($config['APPID'], $config['APPSECRET']);
            # 生成分享签名等数据
            $signPackage = $jssdk->GetSignPackage();
            $this->assign('signPackage', $signPackage);

            //share_info
            $nickname = M('member')->where(array('id' => $member_id))->getField('nickname');
            $share_link = U('Public/reg', array("smid" => $member_id), '', true);
            $share_title = '帮帮-遇见最好的自己';
            $share_desc = "让科技实现价值 让共享创造财富。我是帮帮推广合伙人{$nickname} 加入我们开启财富之旅";
            $share_logo = "http://" . $_SERVER['HTTP_HOST'] . "/Upload/qrcode/share_{$member_id}.png";
            $this->assign('share_link', $share_link);
            $this->assign('share_title', $share_title);
            $this->assign('share_desc', $share_desc);
            $this->assign('share_logo', $share_logo);

            $this->assign('is_wechat', 1);
        } else {
            $this->assign('is_wechat', 0);
        }
        $this->assign('member_client_info', session('member_client_info'));
        $this->display();
    }

    /**
     * 关注微信公众号
     */
    public function follow_weixin()
    {
        //退出登录
        unset($_SESSION['member']);

        $this->display();
    }

    public function app()
    {
        $dev = new \Org\Net\Mobile();
        if ($dev->is('iOS')) {
            $app_url = sp_cfg('app_ios');
        } else {
            $app_url = sp_cfg('app_android');
        }
        header("Location: $app_url");
        exit;
        //AndroidOS
        /*if( $dev->is('iOS') ) {
            $client_dev = 'ios';
        } else {
            $client_dev = 'android';
        }
        $this->assign('client_dev', $client_dev);*/

        //$this->display();
    }

    public function app_download()
    {
        $type = I('get.type');
        if ($type == 'ios') {
            $file = sp_cfg('app_ios');
        } else {
            $file = sp_cfg('app_android');
        }

        $data['type'] = $type;
        $data['member_id'] = $this->get_member_id();
        $data['ip'] = get_client_ip();
        $data['create_time'] = time();
        M('app_download')->add($data);

        header("Location: $file");
    }


    /**
     * @地址      bdwx
     * @说明      绑定微信
     * @user     fang 1044766678@qq.com
     * @参数       @参数 @参数
     */
    public function bdwx()
    {

        if (IS_POST) {
            $member_model = M('member');
            $d = I('post.');
            $weixin = trim($d['weixin']);

            if ($weixin == '') {
                $this->error('请输入要修改的微信号。');
            }
            $data['weixin'] = $weixin;

            $res = $member_model->where(['id' => $this->get_member_id()])->save($data);
            if ($res) {
                $this->success('修改成功', U('bdwx'));
            } else {
                $this->error('修改失败');
            }
        } else {

            $member_model = M('member');
            $username = $member_model->find($this->get_member_id());
            $weixin =$username['weixin'];

            $title = '绑定微信';
            $this->assign('title', $title);
            $this->assign('weixin', $weixin);
            $this->display();
        }
    }

    /**
     * @地址      bdwx
     * @说明      绑定账号
     * @user     fang 1044766678@qq.com
     * @参数       @参数 @参数
     */
    public function bdzh()
    {
        $member_model = M('member');
        $userModel = $member_model->find($this->get_member_id());

        if (IS_POST) {
            $member_model = M('member');
            $d = I('post.');
            $douiyin = trim($d['douyin']);
            $kuaishou = trim($d['kuaishou']);
            $huoshan = trim($d['huoshan']);
            $xiaohongshu = trim($d['xiaohongshu']);


            $data = $userModel;

            if ($douiyin != '') {
                $data['douyin'] = $douiyin;
            }
            if ($kuaishou != '') {
                $data['kuaishou'] = $kuaishou;
            }
            if ($huoshan != '') {
                $data['huoshan'] = $huoshan;
            }
            if ($xiaohongshu != '') {
                $data['xiaohongshu'] = $xiaohongshu;
            }


            $res = $member_model->where(['id' => $this->get_member_id()])->save($data);



            $this->assign('user', $userModel);
            if ($res) {
                $this->success('修改成功', U('bdzh'));
            } else {
                $this->error('修改失败');
            }
        } else {

            $member_model = M('member');
            $username = $member_model->find($this->get_member_id());
            $weixin =$username['weixin'];

            $title = '绑定账号';
            $this->assign('title', $title);
            $this->assign('user', $userModel);
            $this->display();
        }
    }




    /**
     * @地址      getcode_email
     * @说明      邮箱注册
     * @user     fang 1044766678@qq.com
     * @参数       @参数 @参数
     */
    public function getcode_email(){
        $email = trim($_REQUEST['tel']);
        $code = mt_rand(100000, 999999);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array('msg' => '邮箱错误', 'code' => 0));
            exit;
        }

        //判断邮箱是否已经注册
        $isForget = isset($_REQUEST['forget']) ? $_REQUEST['forget'] : 0;
        $member_model = M('member');
        $uname = $member_model->where(array('username' => $email))->getField('username');
        if (!$isForget && !empty($uname)){
            echo json_encode(array('msg' => '该账号已注册,请直接登录!', 'code' => 0));
            exit();
        }


        $yzmtime = session('yzmtime');
        if ($yzmtime) {
            if ($lefttime = time() - $yzmtime < 60) {
                echo json_encode(array('msg' => $lefttime . '秒后重试', 'code' => 0));
                exit;
            }
        }
        session('yzmcode', $code);
        $message = "您的邮箱验证码为".$code." ， \r\n"."感谢您的支持! ";

        $result = send_email($email,$title="邮箱注册验证码通知",$message);


        if ($result['error'] == '0') {
            echo json_encode(array('msg' => '邮件发送成功,如果未收到请查看垃圾箱', 'code' => 1));
            exit;
        } else {
            echo json_encode(array('msg' => '邮件发送失败:'.$result['message'] , 'code' => 0));
            exit;
        }
    }




}