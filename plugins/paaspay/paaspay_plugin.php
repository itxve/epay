<?php

class paaspay_plugin
{
	static public $info = [
		'name'        => 'paaspay', //支付插件英文名称，需和目录名称一致，不能有重复
		'showname'    => '精秀支付', //支付插件显示名称
		'author'      => '精秀', //支付插件作者
		'link'        => 'https://www.jxpays.com/', //支付插件作者链接
		'types'       => ['alipay','wxpay','bank','jdpay'], //支付插件支持的支付方式，可选的有alipay,qqpay,wxpay,bank
		'inputs' => [ //支付插件要求传入的参数以及参数显示名称，可选的有appid,appkey,appsecret,appurl,
			'appurl' => [
				'name' => 'API接口地址',
				'type' => 'input',
				'note' => '以http://或https://开头，以/结尾',
			],
			'appid' => [
				'name' => '商户编号',
				'type' => 'input',
				'note' => '',
			],
			'appkey' => [
				'name' => '商户私钥',
				'type' => 'input',
				'note' => '',
			],
			'appsecret' => [
				'name' => '平台公钥',
				'type' => 'input',
				'note' => '',
			],
			'appmchid' => [
				'name' => '通道ID',
				'type' => 'input',
				'note' => '不填写将进行子商户号轮训',
			],
			'appswitch' => [
				'name' => '微信是否支持H5',
				'type' => 'select',
				'options' => [0=>'否',1=>'是'],
			],
		],
		'select' => null,
		'note' => '', //支付密钥填写说明
		'bindwxmp' => false, //是否支持绑定微信公众号
		'bindwxa' => false, //是否支持绑定微信小程序
	];

	static public function submit(){
		global $siteurl, $channel, $order, $sitename;

		if($order['typename']=='alipay'){
			return ['type'=>'jump','url'=>'/pay/alipay/'.TRADE_NO.'/'];
		}elseif($order['typename']=='wxpay'){
            if (checkmobile()==true && $channel['appswitch'] == 1) {
				return ['type'=>'jump','url'=>'/pay/wxwappay/'.TRADE_NO.'/'];
            }else{
				return ['type'=>'jump','url'=>'/pay/wxpay/'.TRADE_NO.'/'];
			}
		}
	}

	static public function mapi(){
		global $siteurl, $channel, $order, $conf, $device, $mdevice;

		if($order['typename']=='alipay'){
			return self::alipay();
		}elseif($order['typename']=='wxpay'){
			if($mdevice=='wechat'){
				return self::wxjspay();
			}elseif($device=='mobile'){
				return self::wxwappay();
			}else{
				return self::wxpay();
			}
		}elseif($order['typename']=='qqpay'){
			return self::qqpay();
		}elseif($order['typename']=='bank'){
			return self::bank();
		}
	}


	//通用创建订单
	static private function addOrder($type, $extra = null){
		global $siteurl, $channel, $order, $ordername, $conf, $clientip;
		$data = [
            'trade_type'  => $type,
            'pay_channel_id' => $channel['appmchid'],
            'out_trade_no' => TRADE_NO,
            'total_amount' => $order['realmoney'],
            "subject"  => $ordername,
            "attach"  => $order['typename'],
            'notify_url'  => $conf['localurl'].'pay/notify/'.TRADE_NO.'/',
            'return_url' => $siteurl.'pay/ok/'.TRADE_NO.'/',
            'client_ip'  => $clientip,
            
        ];
        
        $commonConfigs = [
            'mchid' => $channel['appid'],
            'method' => 'pay.order/create',
            'charset'=> 'utf-8',
            'sign_type'=>'RSA2',
            'timestamp'=> time(),
            'version'=>'1.0',
            'biz_content'=>json_encode($data),
        ];
        $Rsa = new RsaService('', $channel['appkey']);
        $commonConfigs["sign"] = $Rsa->generateSign($commonConfigs, 'RSA2');
        $result = $Rsa->curlPost($channel['appurl'].'/pay.order/create',$commonConfigs);
        $result = json_decode($result,true);
// 		var_dump('<pre>',$result);die;
		if(isset($result['code']) && $result['code'] == 1){
			\lib\Payment::updateOrder(TRADE_NO, $result['data']["trade_no"]);
			$code_url = $result['data']['payurl'];
		}else{
			throw new Exception('渠道下单失败:'. $result['msg'] ?? '未知错误');
		}
		return $code_url;
	}

	//支付宝扫码支付
	static public function alipay(){
		try{
			$code_url = self::addOrder('alipayQr');
		}catch(Exception $ex){
			return ['type'=>'error','msg'=>'支付宝支付下单失败！'.$ex->getMessage()];
		}

		return ['type'=>'qrcode','page'=>'alipay_qrcode','url'=>$code_url];
	}

	//微信扫码支付
	static public function wxpay(){
		try{
			$code_url = self::addOrder('wechatQr');
		}catch(Exception $ex){
			return ['type'=>'error','msg'=>'微信支付下单失败！'.$ex->getMessage()];
		}

		if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')!==false){
			return ['type'=>'jump','url'=>$code_url];
		} elseif (checkmobile()==true) {
// 			return ['type'=>'scheme','page'=>'wxpay_mini','url'=>$code_url];
// 		} else {
// 			return ['type'=>'qrcode','page'=>'wxpay_qrcode','url'=>$code_url];
            return ['type'=>'qrcode','page'=>'wxpay_wap','url'=>$code_url];
		} else {
			return ['type'=>'qrcode','page'=>'wxpay_qrcode','url'=>$code_url];
		}
	}
	
	//微信手机支付
	static public function wxwappay(){
		global $siteurl, $channel, $order;

		if(in_array('4',$channel['apptype'])){
			try{
				$result = self::addOrder('wechatWap');
			}catch(Exception $ex){
				return ['type'=>'error','msg'=>'微信支付下单失败！'.$ex->getMessage()];
			}
			return ['type'=>'jump','url'=>$code_url];
		}elseif(in_array('3',$channel['apptype'])){
			try{
				$result = self::addOrder('wechatLiteH5');
			}catch(Exception $ex){
				return ['type'=>'error','msg'=>'微信支付下单失败！'.$ex->getMessage()];
			}
			return ['type'=>'scheme','page'=>'wxpay_mini','url'=>$code_url];
		}elseif ($channel['appwxa']>0) {
            $wxinfo = \lib\Channel::getWeixin($channel['appwxa']);
			if(!$wxinfo) return ['type'=>'error','msg'=>'支付通道绑定的微信小程序不存在'];
            try {
                $code_url = wxminipay_jump_scheme($wxinfo['id'], TRADE_NO);
            } catch (Exception $e) {
                return ['type'=>'error','msg'=>$e->getMessage()];
            }
            return ['type'=>'scheme','page'=>'wxpay_mini','url'=>$code_url];
        }elseif($channel['appwxmp']>0 || in_array('2',$channel['apptype'])){
			$code_url = $siteurl.'pay/wxjspay/'.TRADE_NO.'/';
			return ['type'=>'qrcode','page'=>'wxpay_wap','url'=>$code_url];
		}else{
			return self::wxpay();
		}
	}




	//云闪付扫码支付
	static public function bank(){
		try{
			$code_url = self::addOrder('quickPay');
		}catch(Exception $ex){
			return ['type'=>'error','msg'=>'云闪付下单失败！'.$ex->getMessage()];
		}

		return ['type'=>'jump','url'=>$code_url];
	}

	//异步回调
	static public function notify(){
		global $channel, $order;
		$param = $_POST;
        $Rsa = new RsaService($channel['appsecret']);
        if ($Rsa->rsaCheck($param, $param['sign_type'])  == false) {
            echo '验签失败';
        } else {
            if ($param['order_status'] == 'SUCCESS') {
				$out_trade_no = $param['out_trade_no'];
				$trade_no = $param['trade_no'];
				if ($out_trade_no == TRADE_NO) {
					processNotify($order, $trade_no);
					echo 'success';
    			}else{
    			    echo 'fail';
    			}
            } else {
                echo 'error';
            }
        }
        
	}

	//支付返回页面
	static public function return(){
		return ['type'=>'page','page'=>'return'];
	}

	//支付成功页面
	static public function ok(){
		return ['type'=>'page','page'=>'ok'];
	}

	//退款
	static public function refund($order){
		global $channel;
		if(empty($order))exit();

		$param = [
			'appid' => $channel['appid'],
			'nonce' => random(10),
			'outTradeNo' => $order['trade_no'],
		];

		$result = self::sendRequest('/gateway/pay.order/refundQuery', $param, $channel['appkey']);

		if(isset($result["errcode"]) && $result["errcode"]==0 && $result["refund_state"]=='SUCCESS'){
			return ['code'=>0, 'trade_no'=>$order['api_trade_no'], 'refund_fee'=>$order['realmoney']];
		}else{
			return ['code'=>-1, 'msg'=>$result["errmsg"]?$result["errmsg"]:'返回数据解析失败'];
		}
	}
}
/**
 * 交易乐
 * Class OrderLogic
 * @package app\shopapi\logic
 */
class RsaService
{

    protected $PublicKey;
    protected $PrivateKey;
    protected $charset;
    protected $error = null;

    /**
     * 初始化配置
     */
    public function __construct($PublicKey = '', $PrivateKey = '')
    {
        $this->charset = 'utf-8';
        $this->PublicKey = $PublicKey ?? '';
        $this->PrivateKey = $PrivateKey ?? '';
    }

    /**
     * @notes 获取错误信息
     * @return mixed
     * @author Tab
     * @date 2021/8/19 14:42
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     *  验证签名
     **/
    public function rsaCheck($params)
    {
        $sign = $params['sign'] ?? '';
        $signType = $params['sign_type'] ?? 'RSA2';
        unset($params['sign']);
        return $this->verify($this->getSignContent($params), $sign, $signType);
    }

    public function generateSign($params, $signType = "RSA2")
    {
        return $this->sign($this->getSignContent($params), $signType);
    }

    protected function sign($data, $signType = "RSA2")
    {
        $priKey = $this->PrivateKey;
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
           
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        try {
            if ("RSA2" == $signType) {
                openssl_sign($data, $sign, $res, version_compare(PHP_VERSION, '5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
            } else {
                openssl_sign($data, $sign, $res);
            }
        } catch (Exception $e) {
            $this->error = '加签失败:' . $e->getMessage();
            return false;
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    function verify($data, $sign, $signType = 'RSA2')
    {
        $pubKey = $this->PublicKey;
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        try {
            //调用openssl内置方法验签，返回bool值
            if ("RSA2" == $signType) {
                $result = (bool)openssl_verify($data, base64_decode($sign), $res, version_compare(PHP_VERSION, '5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256);
            } else {
                $result = (bool)openssl_verify($data, base64_decode($sign), $res);
            }
        } catch (Exception $e) {
            $this->error = '签名效验失败:' . $e->getMessage();
            return false;
        }
        return $result;

    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value)
    {
        if (is_array($value))
            return true;
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

    public function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, $this->charset);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }
    
    public function curlPost($url = '', $postData = '', $options = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        
        curl_close($ch);
        return $data;
    }
   
    
	
    
    
}