<?php
namespace Api\Controller;

use Common\Model\PayModel;
use Think\Controller;

/**
 * 支付宝
 */
class AlipayController extends Controller{

    const PLATFORM = 'alipay';

    //支付请求
    /*    order
     *  'order_sn'=>'  订单号
     * 'order_amount'   金额
     * 'consignee'=>'   自定义唯一标识
     * */
    public function get_code($order){
        $config=C('ALIPAY_CONFIG');
        $payment=[
            'alipay_account'=>$config['seller_email'],
            'alipay_key'=>$config['key'],
            'alipay_partner'=>$config['partner'],
            'return_url'=>$config['return_url'],
            'notify_url'=>$config['notify_url'],
            'merchant_url'=>$config['merchant_url'],
        ];


        if (!defined('CHARSET')) {
            $charset = 'utf-8';
        } else {
            $charset = CHARSET;
        }


        $gateway = 'http://wappaygw.alipay.com/service/rest.htm?';
        $req_data = '<direct_trade_create_req>'
            . '<subject>' . $order['order_sn'] . '</subject>'
            . '<out_trade_no>' . $order['order_sn'] .'</out_trade_no>'
            . '<total_fee>' . $order['order_amount'] . '</total_fee>'
            . '<seller_account_name>' . $payment['alipay_account'] . '</seller_account_name>'
            . '<call_back_url>' .  $payment['return_url'] . '</call_back_url>'
            . '<notify_url>' .  $payment['notify_url'] . '</notify_url>'
            . '<out_user>' . $order['consignee'] . '</out_user>'
            . '<merchant_url>' . $payment['merchant_url'] . '</merchant_url>'
            . '<pay_expire>3600</pay_expire>'
            . '</direct_trade_create_req>';


        $parameter = array(
            'service' => 'alipay.wap.trade.create.direct',
            'format' => 'xml', 'v' => '2.0',
            'partner' => $payment['alipay_partner'],
            'req_id' => $order['order_sn'],
            'sec_id' => 'MD5',
            'req_data' => $req_data,
            '_input_charset' => $charset
        );
        ksort($parameter);
        reset($parameter);
        $param = '';
        $sign = '';

        foreach ($parameter as $key => $val) {
            $param .= $key . '=' . urlencode($val) . '&';
            $sign .= $key . '=' . $val . '&';
        }

        $param = substr($param, 0, -1);
        $sign = substr($sign, 0, -1) . $payment['alipay_key'];
        $result = $this->curlPost($gateway, $param . '&sign=' . md5($sign));

        if (!$result) {
            $result = file_get_contents($gateway . $param . '&sign=' . md5($sign));
        }

        $result = urldecode($result);
        $result_array = explode('&', $result);
        $new_result_array = $temp_item = array();

        if (is_array($result_array)) {
            foreach ($result_array as $vo) {
                $temp_item = explode('=', $vo, 2);
                $new_result_array[$temp_item[0]] = $temp_item[1];
            }
        }

        $xml = simplexml_load_string($new_result_array['res_data']);
        $request_token = (array) $xml->request_token;
        $parameter = array('service' => 'alipay.wap.auth.authAndExecute', 'format' => 'xml', 'v' => $new_result_array['v'], 'partner' => $new_result_array['partner'], 'sec_id' => $new_result_array['sec_id'], 'req_data' => '<auth_and_execute_req><request_token>' . $request_token[0] . '</request_token></auth_and_execute_req>', 'request_token' => $request_token[0], 'app_pay' => 'Y', '_input_charset' => $charset);
        ksort($parameter);
        reset($parameter);
        $param = '';
        $sign = '';

        foreach ($parameter as $key => $val) {
            $param .= $key . '=' . urlencode($val) . '&';
            $sign .= $key . '=' . $val . '&';
        }

        $param = substr($param, 0, -1);
        $sign = substr($sign, 0, -1) . $payment['alipay_key'];
        $button = '<a  type="button" class="box-flex btn-submit min-two-btn" onclick="javascript:_AP.pay(\'' . $gateway . $param . '&sign=' . md5($sign) . '\')">支付宝支付</a>';
        $url=$gateway . $param . '&sign=' . md5($sign);
        return $url;
    }



















    /**
     * return_url接收页面
     */
    public function alipay_return()
    {
        if (!empty($_GET)) {
            $config=C('ALIPAY_CONFIG');
            $payment=[
                'alipay_account'=>$config['seller_email'],
                'alipay_key'=>$config['key'],
                'alipay_partner'=>$config['partner'],
                'return_url'=>$config['return_url'],
                'notify_url'=>$config['notify_url'],
                'merchant_url'=>$config['merchant_url'],
            ];


            ksort($_GET);
            reset($_GET);
            $sign = '';

            foreach ($_GET as $key => $val) {
                if (($key != 'sign') && ($key != 'sign_type') && ($key != 'code')) {
                    $sign .= $key . '=' . $val . '&';
                }
            }

            $sign = substr($sign, 0, -1) . $payment['alipay_key'];

            if (md5($sign) != $_GET['sign']) {
                file_put_contents('/Runtime/alipay.log', json_encode($_GET) . "/r/n", FILE_APPEND);
                return false;
            }

            if ($_GET['result'] == 'success') {

                //结算提成
                $out_trade_no = $_GET['out_trade_no'];
                $d = M('recharge')->where(array('order_no'=>$out_trade_no))->find();
                $trade_no = $_GET['trade_no'];
                $pay_model = new PayModel();
                $pay_model->pay_vip_success($d['id'], self::PLATFORM, $trade_no);
                $this->success('支付成功',U('Home/Member/index',array('id'=>$d['post_id'])));
            }
            else {
                file_put_contents('/Runtime/alipay.log', json_encode($_GET) . "/r/n", FILE_APPEND);
            }
        }
        else {
            echo "支付失败";
            exit;
        }
    }

   
   
   
        public function alipay_notify()
    {


        if (!empty($_POST)) {
            $config=C('ALIPAY_CONFIG');
            $payment=[
                'alipay_account'=>$config['seller_email'],
                'alipay_key'=>$config['key'],
                'alipay_partner'=>$config['partner'],
                'return_url'=>$config['return_url'],
                'notify_url'=>$config['notify_url'],
                'merchant_url'=>$config['merchant_url'],
            ];
            $parameter['service'] = $_POST['service'];
            $parameter['v'] = $_POST['v'];
            $parameter['sec_id'] = $_POST['sec_id'];
            $parameter['notify_data'] = $_POST['notify_data'];
            $sign = '';
            foreach ($parameter as $key => $val) {
                    $sign .= $key . '=' . $val . '&';
            }

            $sign = substr($sign, 0, -1) . $payment['alipay_key'];

            if (md5($sign) != $_POST['sign']) {
                file_put_contents('/Runtime/alipay.log', json_encode($_POST) . "/r/n", FILE_APPEND);
                return false;
            }
            $data = (array) simplexml_load_string($parameter['notify_data']);

                //结算提成
                $out_trade_no =  $data['out_trade_no'];
                $d = M('recharge')->where(array('order_no'=>$out_trade_no))->find();
                $trade_no = $_POST['trade_no'];
                $pay_model = new PayModel();
                $pay_model->pay_vip_success($d['id'], self::PLATFORM, $trade_no);
                $this->success('支付成功',U('Home/Member/index',array('id'=>$d['post_id'])));

        }
        else {
            echo "支付失败";
            exit;
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