<?php
namespace Api\Controller;

use Common\Model\PayModel;
use Think\Controller;

/**
 *  彩虹易支付
 */
class YipayController extends Controller{

    const UID = "b07ab35d89875abbea811e59";//"此处填写PaysApi的uid";
    const TOKEN = "34293339a02a221190c3697e66b1e700";//"此处填写PaysApi的Token";
    const POST_URL = "https://pay.bearsoftware.net.cn/?";
    const PLATFORM = "bearsoftware";

    public function pay($order_no){
        $istype = 1;
        $recharge = M('recharge')->where(array('order_no'=>$order_no))->find();
        $goodsname = "充值VIP";
        $notify_url = U('api/yipay/Yipay_notify','','',true);
        $return_url = U('api/yipay/Yipay_return','','',true);
        $orderid = $order_no;
        $orderuid = $recharge['member_id'];
        $price = $recharge['price'];
      
        $key = md5($goodsname. $istype . $notify_url . $orderid . $orderuid . $price . $return_url . self::TOKEN . self::UID);
        $data = array(
            'goodsname'=>$goodsname,
            'istype'=>$istype,
            'notify_url'=>$notify_url,
            'orderid'=>$orderid,
            'orderuid'=>$orderuid,
            'price'=>$price,
            'return_url'=>$return_url,
            'key'=>$key,
            'uid'=>self::UID
        );
;
        ksort($data);
        reset($data);
        $param = '';



        foreach ($data as $key => $val) {
            $param .= $key . '=' . urlencode($val) . '&';
        }

        $url= self::POST_URL . $param ;
        return $url;

    }


    /**
     * return_url接收页面
     */
    public function Yipay_return()
    {


          header('location:'.U('Home/Member/index',array('msg'=>'支付成功')));
    }

    /**
     * notify_url接收页面  异步
     */
    public function Yipay_notify()
    {
        $paysapi_id = $_POST["paysapi_id"];
        $orderid = $_POST["orderid"];
        $price = $_POST["price"];
        $realprice = $_POST["realprice"];
        $orderuid = $_POST["orderuid"];
        $key = $_POST["key"];
        //校验传入的参数是否格式正确，略
        $token = self::TOKEN;
        $temps = md5($orderid . $orderuid . $paysapi_id . $price . $realprice . $token);

        if ($temps != $key){
            return jsonError("key值不匹配");
        }else{
            //校验key成功
            $out_trade_no =$orderid;
            $d = M('recharge')->where(array('order_no'=>$out_trade_no))->find();

                $pay_model = new PayModel();
                $pay_model->pay_vip_success($d['id'], self::PLATFORM, $paysapi_id);
                file_put_contents('Runtime/alipay.txt', json_encode($_POST) . "/r/n", FILE_APPEND);
                return true;
        }
    }




/*
 * 充值模块
 * */

    public function online_recharge(){
        $istype = 1;

        $goodsname = "在线充值";
        $notify_url = U('api/yipay/recharge_notify','','',true);
        $return_url = U('api/yipay/recharge_return','','',true);
        $orderid = $_GET['member_id'];
        $orderuid =$_GET['member_id'];
        $price =floatval($_GET['price']);

        $key = md5($goodsname. $istype . $notify_url . $orderid . $orderuid . $price . $return_url . self::TOKEN . self::UID);
        $data = array(
            'goodsname'=>$goodsname,
            'istype'=>$istype,
            'notify_url'=>$notify_url,
            'orderid'=>$orderid,
            'orderuid'=>$orderuid,
            'price'=>$price,
            'return_url'=>$return_url,
            'key'=>$key,
            'uid'=>self::UID
        );
        ;
        ksort($data);
        reset($data);
        $param = '';



        foreach ($data as $key => $val) {
            $param .= $key . '=' . urlencode($val) . '&';
        }

        $url= self::POST_URL . $param ;
        header('Location:'.$url);

    }


    /**
     * return_url接收页面
     */
    public function recharge_return()
    {


        header('location:'.U('Home/Member/index',array('msg'=>'支付成功')));
    }

    public function recharge_notify()
    {
        file_put_contents('Runtime/alipay2.txt', json_encode($_POST) . "/r/n", FILE_APPEND);
        $paysapi_id = $_POST["paysapi_id"];
        $orderid = $_POST["orderid"];
        $price =floatval($_POST["price"]);
        $realprice = $_POST["realprice"];
        $orderuid = $_POST["orderuid"];
        $key = $_POST["key"];
        //校验传入的参数是否格式正确，略
        $token = self::TOKEN;
        $temps = md5($orderid . $orderuid . $paysapi_id . $price . $realprice . $token);

        if ($temps != $key){
            return jsonError("key值不匹配");
        }else{
                //充值
                $pay_model = new PayModel();
                $pay_model->member_recharge($orderuid, $price,self::PLATFORM, $paysapi_id);
        }
    }





    public function curlPost($url, $post_data = array(), $timeout = 5, $header = "", $data_type = "")
    {

        if (empty($url) || empty($post_data) || empty($timeout)) {
            return false;
        }
        if (!preg_match('/^(http|https)/is', $url)) {
            $url = "http://" . $url;
        }
        $header = empty($header) ? '' : $header;
        //支持json数据数据提交
        if ($data_type == 'json') {
            $post_string = json_encode($post_data);
        } elseif (is_array($post_data)) {
            $post_string = http_build_query($post_data, '', '&');
        } else {
            $post_string = $post_data;
        }
        $ch = curl_init();
        if (stripos($url, "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, 2);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));//模拟的header头
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


}