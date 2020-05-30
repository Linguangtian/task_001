<?php
namespace Api\Controller;

use Common\Model\PayModel;
use Think\Controller;


class CodepayController extends Controller{

    const UID = "525847";//""码支付ID";
    const TOKEN = "FhhlEtcVCq1g5Hdx4eXKGpIh5e8ozmrE";//"此处填写Yipay的Token";


    public function Epay_return()
    {

        $codepay_key =self::TOKEN; //这是您的密钥
        if (empty($_POST)) { //如果GET访问
            $_POST = $_GET;  //POST访问 为服务器或软件异步通知  不需要返回HTML
        }

        ksort($_POST); //排序post参数
        reset($_POST); //内部指针指向数组中的第一个元素
        $sign = ''; //加密字符串初始化
        foreach ($_POST AS $key => $val) {
            if ($val == '' || $key == 'sign') continue; //跳过这些不签名
            if ($sign) $sign .= '&'; //第一个字符串签名不加& 其他加&连接起来参数
            $sign .= "$key=$val"; //拼接为url参数形式
        }
        $pay_id = $_POST['pay_id']; //需要充值的ID 或订单号 或用户名
        $pay_no = $_POST['pay_no'];//流水号
        if (!$_POST['pay_no'] || md5($sign . $codepay_key) != $_POST['sign']) { //不合法的数据
            file_put_contents('PayRuntime/return/'.date('Y-m-d').'.txt', json_encode($_POST) . "/r/n", FILE_APPEND);
            header('location:'.U('Home/Member/index',array('msg'=>'支付失败,请联系客服')));
            die;
        } else {
            //支付成功
                $d = M('recharge')->where(array('order_no'=>$pay_id))->find();
                $pay_model = new PayModel();
                $pay_model->pay_vip_success( $d['id'],'77', $pay_no );
        }
        header('location:'.U('Home/Member/index',array('msg'=>'支付成功')));
        die;
    }

    /**
     * notify_url接收页面
     */
    public function Copypay_notify()
    {

        $codepay_key =self::TOKEN; //这是您的密钥
        if (empty($_POST)) { //如果GET访问
            $_POST = $_GET;  //POST访问 为服务器或软件异步通知  不需要返回HTML
        }

        ksort($_POST); //排序post参数
        reset($_POST); //内部指针指向数组中的第一个元素
        $sign = ''; //加密字符串初始化
        foreach ($_POST AS $key => $val) {
            if ($val == '' || $key == 'sign') continue; //跳过这些不签名
            if ($sign) $sign .= '&'; //第一个字符串签名不加& 其他加&连接起来参数
            $sign .= "$key=$val"; //拼接为url参数形式
        }
        $pay_id = $_POST['pay_id']; //需要充值的ID 或订单号 或用户名
        $pay_no = $_POST['pay_no'];//流水号
        if (!$_POST['pay_no'] || md5($sign . $codepay_key) != $_POST['sign']) { //不合法的数据
            file_put_contents('PayRuntime/notify/'.date('Y-m-d').'.txt', json_encode($_POST) . "/r/n", FILE_APPEND);
            die;
        } else {
            //支付成功
            $d = M('recharge')->where(array('order_no'=>$pay_id))->find();
            $pay_model = new PayModel();
            $pay_model->pay_vip_success( $d['id'],'77', $pay_no );
        }
       
    }


}