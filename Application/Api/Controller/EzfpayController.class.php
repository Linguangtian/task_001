<?php
namespace Api\Controller;

use Common\Model\PayModel;
use Think\Controller;

/**
 *  ezhifu.org
 */
class EzfpayController extends Controller{

    const APPID = "82032131";//"此处填写PaysApi的uid";
    const APPKEY = "3KU7BA415AAB5XVCZK7L08I";//"此处填写PaysApi的Token";
    const POST_URL = "http://pay.epayok.xyz/?";
    const PLATFORM = "bearsoftware";

    public function pay($order_no){

        $istype = 1;
        $recharge = M('recharge')->where(array('order_no'=>$order_no))->find();

        $notify_url = U('api/Ezfpay/pay_notify','','',true);
        $return_url = U('api/Ezfpay/pay_return','','',true);
        $orderid = $order_no;

        $price = $recharge['price'];

        $data = array(
            'appid'=>self::APPID,
            'type'=>$istype,
            'money'=>$price,
            'back'=>$return_url,
            'return_url'=>$return_url,
            'callback'=>$notify_url,
            'orderid'=>$orderid,

        );

        ksort($data);
        reset($data);
        $param = '';
        $sign = '';

        $none_include=['back','returnurl'];

        foreach ($data as $key => $val) {
            $param .= $key . '=' . urlencode($val) . '&';
            if(in_array($val,$none_include)) continue;
            $sign .= $key . '=' . $val . '&';
        }
        $param = substr($param, 0, -1);
        $sign = substr($sign, 0, -1) . self::APPKEY;
        $url= self::POST_URL . $param.'&sign=' . md5($sign) ;
        return $url;

    }


    /**
     * return_url接收页面
     */
    public function pay_return()
    {
        file_put_contents('PayRuntime/return/'.date('Y-m-d').'.txt', json_encode($_POST) . "/r/n", FILE_APPEND);
          header('location:'.U('Home/Member/index',array('msg'=>'支付成功')));
    }

    /**
     * notify_url接收页面  异步
     */
    public function pay_notify()
    {

        file_put_contents('PayRuntime/notify/'.date('Y-m-d').'.txt', json_encode($_POST) . "/r/n", FILE_APPEND);

        if (empty($_POST)) { //如果GET访问
            $_POST = $_GET;  //POST访问 为服务器或软件异步通知  不需要返回HTML
        }

        $sign = $_GET['sign'];
        unset($_GET['sign']);
        ksort($_GET);
        if(md5(http_build_query($_GET).self::APPKEY)==$sign){
            $orderid = $_POST["orderid"];
            $paysapi_id = $_POST["appid"];
            $out_trade_no =$orderid;
            $d = M('recharge')->where(array('order_no'=>$out_trade_no))->find();
            $pay_model = new PayModel();
            $pay_model->pay_vip_success($d['id'], self::PLATFORM, $paysapi_id);

        }

    }

















}