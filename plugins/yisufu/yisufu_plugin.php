<?php

class yisufu_plugin
{
    static public $info = [
        'name'        => 'yisufu', //支付插件英文名称，需和目录名称一致，不能有重复
        'showname'    => '易速付', //支付插件显示名称
        'author'      => '易速付', //支付插件作者
        'link'        => 'https://www.yisufu.cn/', //支付插件作者链接
        'types'       => ['alipay','wxpay','bank','jdpay'], //支付插件支持的支付方式，可选的有alipay,qqpay,wxpay,bank
        'inputs' => [ //支付插件要求传入的参数以及参数显示名称，可选的有appid,appkey,appsecret,appurl,appmchid
            'appid' => [
                'name' => '用户编号',
                'type' => 'input',
                'note' => '',
            ],
            'appkey' => [
                'name' => '接口密钥',
                'type' => 'input',
                'note' => '',
            ],
            'appmchid' => [
                'name' => '子商户号',
                'type' => 'input',
                'note' => '指定子商户号交易，留空默认轮训',
            ],
            'appswitch' => [
                'name' => '微信是否支持H5',
                'type' => 'select',
                'options' => [0=>'否',1=>'是'],
            ],
        ],
        'select' => null,
        'note' => '微信通道有扫码和小程序2种，直接选择扫码或小程序使用即可', //支付密钥填写说明
        'bindwxmp' => false, //是否支持绑定微信公众号
        'bindwxa' => false, //是否支持绑定微信小程序
    ];

    static private function make_sign($param, $key)
    {
        ksort($param);
        $signstr = '';
        foreach($param as $k => $v){
            if($k=='sign' || $v=='')continue;
            $signstr .= $k.'='.$v.'&';
        }
        $signstr = substr($signstr,0,-1);
        $sign = md5($signstr.$key);
        return $sign;
    }

    static private function addOrder($channel_type){
        global $channel, $order, $ordername, $conf, $clientip;

        session_start();

        $apiurl = 'https://openapi.yisufu.cn';

        if (empty($channel['appmchid'])) {
            $body = [
                'sub_type' => 'SYSTEM',
                'channel_type' => $channel_type,
                'total_fee' => $order['realmoney'],
                'pay_name' => $ordername,
                'pay_body' => $ordername,
                'notify_url' => $conf['localurl'] . 'pay/notify/' . TRADE_NO . '/',
                'out_trade_no' => TRADE_NO,
                'user_ip' => $clientip,
                'server_url' => $_SERVER['HTTP_HOST']
            ];
        } else {
            $body = [
                'sub_mch_id' => $channel['appmchid'],
                'channel_type' => $channel_type,
                'total_fee' => $order['realmoney'],
                'pay_name' => $ordername,
                'pay_body' => $ordername,
                'notify_url' => $conf['localurl'] . 'pay/notify/' . TRADE_NO . '/',
                'out_trade_no' => TRADE_NO,
                'user_ip' => $clientip,
                'server_url' => $_SERVER['HTTP_HOST']
            ];
        }

        ksort($body);

        $param = array(
            'open_userid' => $channel['appid'],
            'service' => 'gateway.unified.pay',
            'res_body' => json_encode($body),
            'sign_type' => 'MD5',
        );

        $param['sign'] = self::make_sign($param, $channel['appkey']);

        if($_SESSION[TRADE_NO.'_pay']){
            $data = $_SESSION[TRADE_NO.'_pay'];
        }else{
            $data = get_curl($apiurl, http_build_query($param));
            $_SESSION[TRADE_NO.'_pay'] = $data;
        }
        $result = json_decode($data, true);

        if($result["rsp_code"]=='0000'){
            $api_trade_no = $result['request_array']['system_order_id'];
            \lib\Payment::updateOrder(TRADE_NO, $api_trade_no);
            return $result['request_array'];
        }else{
            throw new Exception($result["rsp_msg"]?$result["rsp_msg"]:'接口请求失败');
        }
    }

    static public function submit(){
        global $siteurl, $channel, $order, $sitename;

        if($order['typename']=='alipay'){
            return ['type'=>'jump','url'=>'/pay/alipay/'.TRADE_NO.'/'];
        }elseif($order['typename']=='wxpay'){
            if (checkmobile()==true && $channel['appswitch'] == 1) {
                return ['type'=>'jump','url'=>'/pay/wxh5pay/'.TRADE_NO.'/'];
            }else{
                return ['type'=>'jump','url'=>'/pay/wxpay/'.TRADE_NO.'/'];
            }
        }elseif($order['typename']=='bank'){
            return ['type'=>'jump','url'=>'/pay/bank/'.TRADE_NO.'/'];
        }
        if($order['typename']=='jdpay'){
            return ['type'=>'jump','url'=>'/pay/jdpay/'.TRADE_NO.'/'];
        }
    }

    static public function mapi(){
        global $siteurl, $channel, $order, $device, $mdevice;

        if($order['typename']=='alipay'){
            return self::alipay();
        }elseif($order['typename']=='wxpay'){
            if ($device == 'mobile' && $channel['appswitch'] == 1) {
                return self::wxh5pay();
            }else{
                return self::wxpay();
            }
        }elseif($order['typename']=='bank'){
            return self::bank();
        }

    }



    //支付宝扫码支付
    static public function alipay(){
        try{
            $arr = self::addOrder('ALIPAY');
        }catch(Exception $ex){
            return ['type'=>'error','msg'=>'支付宝支付下单失败！'.$ex->getMessage()];
        }

        return ['type'=>'qrcode','page'=>'alipay_qrcode','url'=>$arr['pay_url']];
    }

    //微信扫码支付
    static public function wxpay(){
        try{
            $arr = self::addOrder('WECHAT_MP');
        }catch(Exception $ex){
            return ['type'=>'error','msg'=>'微信支付下单失败！'.$ex->getMessage()];
        }

        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')!==false){
            return ['type'=>'jump','url'=>$arr['pay_url']];
        } elseif (checkmobile()==true) {
            return ['type'=>'qrcode','page'=>'wxpay_wap','url'=>$arr['pay_url']];
        } else {
            return ['type'=>'qrcode','page'=>'wxpay_qrcode','url'=>$arr['pay_url']];
        }
    }

    //微信H5支付（小程序）
    static public function wxh5pay(){
        try{
            $arr = self::addOrder('WECHAT_H5');
        }catch(Exception $ex){
            return ['type'=>'error','msg'=>'微信支付下单失败！'.$ex->getMessage()];
        }

        return ['type'=>'scheme','page'=>'wxpay_h5','url'=>$arr['wechat_redirect']];
    }

    //云闪付
    static public function bank(){
        try{
            $arr = self::addOrder('UNIONPAY_QR');
        }catch(Exception $ex){
            return ['type'=>'error','msg'=>'银联云闪付下单失败！'.$ex->getMessage()];
        }

        return ['type'=>'qrcode','page'=>'bank_qrcode','url'=>$arr['pay_url']];
    }

    //京东支付
    static public function jdpay(){
        try{
            $arr = self::addOrder('JDPAY');
        }catch(Exception $ex){
            return ['type'=>'error','msg'=>'京东支付下单失败！'.$ex->getMessage()];
        }

        return ['type'=>'qrcode','page'=>'jdpay_qrcode','url'=>$arr['pay_url']];
    }



    //异步回调
    static public function notify(){
        global $channel, $order;

        if(isset($_POST['out_trade_no'])){
            $data = $_POST;
        }else{
            $data = $_GET;
        }
        //file_put_contents('logs.txt',http_build_query($data));

        $sign = self::make_sign($data, $channel['appkey']);

        if($sign===$data['sign']){
            $out_trade_no = daddslashes($data['out_trade_no']);
            $trade_no = daddslashes($data['system_order_id']);

            if ($out_trade_no == TRADE_NO) {
                processNotify($order, $trade_no);
            }
            return ['type'=>'html','data'=>'SUCCESS'];
        }else{
            return ['type'=>'html','data'=>'sign fail'];
        }
    }

    //支付返回页面
    static public function return(){
        return ['type'=>'page','page'=>'return'];
    }

    //退款
    static public function refund($order){
        global $channel;
        if(empty($order))exit();

        $apiurl = $channel['appurl'];

        $body = [
            'bank_order' => $order['api_trade_no'],
        ];
        ksort($body);

        $param = array(
            'open_userid' => $channel['appid'],
            'service' => 'order.refund.api',
            'res_body' => json_encode($body),
            'sign_type' => 'MD5',
        );

        $param['sign'] = self::make_sign($param, $channel['appkey']);

        $data = get_curl($apiurl, http_build_query($param));
        $result = json_decode($data, true);

        if($result["rsp_code"]=='0000'){
            $result = ['code'=>0, 'trade_no'=>$order['api_trade_no'], 'refund_fee'=>$order['realmoney']];
        }else{
            $result = ['code'=>-1, 'msg'=>$result["msg"]];
        }
        return $result;
    }
}