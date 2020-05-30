<?php
namespace Api\Controller;

use Common\Model\PayModel;
use Think\Controller;

/**
 *  96支付
 */
class Pay96Controller extends Controller{

    const UID = "19366";//"此处填写PaysApi的uid";
    const TOKEN = "AMV5mf5CctH54A9qEH15f6f6m7MhEvQH";//"此处填写PaysApi的Token";
    const POST_URL = "http://pay.96yzf.com/";
    const PLATFORM = "96pay";

    const pay_rul = "http://app.mxrenwu.top/";
    const return_url = "http://kfc123.top/";

    public function pay(){

        $order_no = I('get.order_no');


        $recharge = M('recharge')->where(array('order_no'=>$order_no))->find();
     /*   $notify_url = U('pay_notify','','',true);
      $return_url = U('pay_return','','',true);
     */
        $notify_url = self::pay_rul.'index.php/Api/Pay96/pay_notify';
        $return_url = self::pay_rul.'index.php/Api/Pay96/pay_return';


        $orderid = $order_no;
        $type ='alipay';
        $price = $recharge['price'];


        $alipay_config = array();
        $alipay_config['partner']		= self::UID;
        $alipay_config['key']			= self::TOKEN;
        $alipay_config['sign_type']    = strtoupper('MD5');
        $alipay_config['input_charset']= strtolower('utf-8');
        $alipay_config['transport']    = 'http';
        $alipay_config['apiurl']    =  self::POST_URL;

        require_once(dirname(__FILE__)."/ep/epay_submit.class.php");


        //构造要请求的参数数组，无需改动
        $parameter = array(
            "pid" => trim($alipay_config['partner']),
            "type" => $type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "out_trade_no"	=> $order_no,
            "name"	=> $orderid,
            "money"	=> $price,

        );
        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter);
        echo $html_text;
        exit;



    }


    /**
     * return_url接收页面
     */
    public function pay_return()
    {
        error_reporting( 0 );
        $alipay_config = array();
        $alipay_config['partner']		= self::UID;
        $alipay_config['key']			= self::TOKEN;
        $alipay_config['sign_type']    = strtoupper('MD5');
        $alipay_config['input_charset']= strtolower('utf-8');
        $alipay_config['transport']    = 'http';
        $alipay_config['apiurl']    =  self::POST_URL;
        require_once(dirname(__FILE__)."/ep/epay_notify.class.php");
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();





        if($verify_result)
        {
            $out_trade_no = $_GET['out_trade_no'];
            $trade_no = $_GET['trade_no'];
            if ($_GET['trade_status'] == 'TRADE_SUCCESS')
            {
                $d = M('recharge')->where(array('order_no'=>$out_trade_no))->find();
                $pay_model = new PayModel();
                $pay_model->pay_vip_success( $d['id'], $d['payment_type'], $trade_no );
                $url =self::return_url.'index.php/Home/Member/index/msg/支付成功';;
                header('location:'.$url);
                die;
            }
        }else{
            $url =self::return_url.'index.php/Home/Member/index/msg/失败';;
            header('location:'.$url);
            die;

        }


    }

    /**
     * notify_url接收页面
     */
    public function pay_notify()
    {

        error_reporting( 0 );
        $alipay_config = array();
        $alipay_config['partner']		= self::UID;
        $alipay_config['key']			= self::TOKEN;
        $alipay_config['sign_type']    = strtoupper('MD5');
        $alipay_config['input_charset']= strtolower('utf-8');
        $alipay_config['transport']    = 'http';
        $alipay_config['apiurl']    =  self::POST_URL;
        require_once(dirname(__FILE__)."/ep/epay_notify.class.php");
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result)
        {
            $out_trade_no = $_GET['out_trade_no'];
            $trade_no = $_GET['trade_no'];
            if ($_GET['trade_status'] == 'TRADE_SUCCESS')
            {
                $d = M('recharge')->where(array('order_no'=>$out_trade_no))->find();
                $pay_model = new PayModel();
                $pay_model->pay_vip_success( $d['id'], $d['payment_type'], $trade_no );
            }
        }
    }





}