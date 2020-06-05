<?php
namespace Api\Controller;

use Common\Model\PayModel;
use Think\Controller;
/*
 *
 *http://pay.r3o.cn/
 * */

class EpayController extends Controller{

    const UID = "904163";//"此处填写Yipay的uid";
    const TOKEN = "2794274BA32106BC77B94192AC5C98F5";//"此处填写Yipay的Token";
    const POST_URL = "http://pay.r3o.cn/";
    const PLATFORM = "r3o";
    public function pay(){

        $order_no = I('get.order_no');
        $type = I('get.payment_type')=='alipay' ? 'alipay' : 'wxpay';
        $type ='alipay';
        $recharge = M('recharge')->where(array('order_no'=>$order_no))->find();
        $notify_url = U('Epay_notify','','',true);
        $return_url = U('Epay_return','','',true);
		$orderid = $order_no;
        $orderuid = $recharge['member_id'];
        $price = $recharge['price'];

      	$alipay_config = array();
		$alipay_config['partner']		= self::UID;
		$alipay_config['key']			= self::TOKEN;
		$alipay_config['sign_type']    = strtoupper('MD5');
		$alipay_config['input_charset']= strtolower('utf-8');
		$alipay_config['transport']    = 'http';
		$alipay_config['apiurl']    =  self::POST_URL;
      
		require_once(dirname(__FILE__)."/ep/epay_submit.class.php");

		$parameter = array
		(
				"pid" => self::UID ,
				"type" => $type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"out_trade_no"	=> $order_no,
				"name"	=> $orderid,
				"money"	=> $price,
				"sign_type"	=> "MD5",

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
    public function Epay_return()
    {
        $this->redirect('Home/Member/vip',array('success'=>1));
    }











    /**
     * notify_url接收页面
     */
    public function Epay_notify()
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
            $trade_status = $_GET['trade_status'];
            $type = $_GET['type'];
            if ($_GET['trade_status'] == 'TRADE_SUCCESS') 
            {
              	  $d = M('recharge')->where(array('order_no'=>$out_trade_no))->find();
                 
                  $pay_model = new PayModel();
                  $pay_model->pay_vip_success( $d['id'], $d['payment_type'], $trade_no );
            }
        }


       
    }





    /*
 * 充值模块
 * */

    public function online_recharge(){
        $istype = 1;


        $type ='alipay';

        $notify_url = U('recharge_notify','','',true);
        $return_url = U('recharge_return','','',true);
        $orderid = $_GET['member_id'];
        $order_no = $_GET['member_id'];
        $price = floatval($_GET['price']);

        $alipay_config = array();
        $alipay_config['partner']		= self::UID;
        $alipay_config['key']			= self::TOKEN;
        $alipay_config['sign_type']    = strtoupper('MD5');
        $alipay_config['input_charset']= strtolower('utf-8');
        $alipay_config['transport']    = 'http';
        $alipay_config['apiurl']    =  self::POST_URL;

        require_once(dirname(__FILE__)."/ep/epay_submit.class.php");

        $parameter = array
        (
            "pid" => self::UID ,
            "type" => $type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "out_trade_no"	=> $order_no,
            "name"	=> $orderid,
            "money"	=> $price,
            "sign_type"	=> "MD5",
            #"sitename"	=> '星推宝'
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter);
        echo $html_text;
        exit;
        header('Location:'.$url);

    }




    public function recharge_return()
    {



        $this->redirect('Home/Member/index',array('success'=>1));

    }

    public function recharge_notify()
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
            $trade_status = $_GET['trade_status'];
            $price = $_GET['money'];
            if ($_GET['trade_status'] == 'TRADE_SUCCESS')
            {
                //充值
                $pay_model = new PayModel();
                $pay_model->member_recharge($out_trade_no, $price,self::PLATFORM, $trade_no);
            }
        }
    }







    //认证模块
   public function rzpay(){
        $istype = 1;

        $type ='alipay';

        $notify_url = U('rzpay_notify','','',true);
        $return_url = U('rzpay_return','','',true);
        $orderid = $_GET['member_id'];
        $order_no =rand(111111111,999999999);


        $alipay_config = array();
        $alipay_config['partner']		= self::UID;
        $alipay_config['key']			= self::TOKEN;
        $alipay_config['sign_type']    = strtoupper('MD5');
        $alipay_config['input_charset']= strtolower('utf-8');
        $alipay_config['transport']    = 'http';
        $alipay_config['apiurl']    =  self::POST_URL;

        require_once(dirname(__FILE__)."/ep/epay_submit.class.php");

        $parameter = array
        (
            "pid" => self::UID ,
            "type" => $type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "out_trade_no"	=> $order_no,
            "name"	=> $orderid,
            "money"	=> 0.1,
            "sign_type"	=> "MD5",
            "sitename"	=> '认证费'
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter);
        echo $html_text;
        exit;


    }




    public function rzpay_return()
    {

        $this->redirect('Home/Member/index',array('success'=>1));

    }

    public function rzpay_notify()
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
            $user_id = $_GET['name'];
            $trade_no = $_GET['trade_no'];
            $price = $_GET['money'];
            if ($_GET['trade_status'] == 'TRADE_SUCCESS')
            {

                $pay_model = new PayModel();
                $pay_model->auto_recharge($user_id, $price,self::PLATFORM, $trade_no);
            }
        }

    }












}