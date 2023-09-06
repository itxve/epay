<?php
class huifu_plugin
{
	static public $info = [
		'name'        => 'huifu', //支付插件英文名称，需和目录名称一致，不能有重复
		'showname'    => '汇付斗拱平台', //支付插件显示名称
		'author'      => '汇付天下', //支付插件作者
		'link'        => 'https://paas.huifu.com/', //支付插件作者链接
		'types'       => ['alipay','wxpay','bank'], //支付插件支持的支付方式，可选的有alipay,qqpay,wxpay,bank
		'inputs' => [ //支付插件要求传入的参数以及参数显示名称，可选的有appid,appkey,appsecret,appurl,appmchid
			'appid' => [
				'name' => '汇付系统号',
				'type' => 'input',
				'note' => '当主体为渠道商时填写渠道商ID，主体为直连商户时填写商户ID',
			],
			'appurl' => [
				'name' => '汇付产品号',
				'type' => 'input',
				'note' => '',
			],
			'appsecret' => [
				'name' => '商户私钥',
				'type' => 'textarea',
				'note' => '',
			],
			'appkey' => [
				'name' => '汇付公钥',
				'type' => 'textarea',
				'note' => '',
			],
			'appmchid' => [
				'name' => '汇付子商户号',
				'type' => 'input',
				'note' => '当主体为渠道商时需要填写，主体为直连商户时不需要填写',
			],
		],
		'select_wxpay' => [
			'1' => '自有公众号/小程序支付',
			'2' => '托管小程序支付',
		],
		'select' => null,
		'note' => null, //支付密钥填写说明
		'bindwxmp' => true, //是否支持绑定微信公众号
		'bindwxa' => true, //是否支持绑定微信小程序
	];

	static public function submit(){
		global $siteurl, $channel, $order, $sitename;

		if($order['typename']=='alipay'){
			return ['type'=>'jump','url'=>'/pay/alipay/'.TRADE_NO.'/'];
		}elseif($order['typename']=='wxpay'){
			if(in_array('1',$channel['apptype']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')!==false){
				return ['type'=>'jump','url'=>'/pay/wxjspay/'.TRADE_NO.'/?d=1'];
			}elseif(checkmobile()==true){
				return ['type'=>'jump','url'=>'/pay/wxwappay/'.TRADE_NO.'/'];
            }else{
				return ['type'=>'jump','url'=>'/pay/wxpay/'.TRADE_NO.'/'];
			}
		}elseif($order['typename']=='bank'){
			return ['type'=>'jump','url'=>'/pay/bank/'.TRADE_NO.'/'];
		}
	}

	static public function mapi(){
		global $siteurl, $channel, $order, $conf, $device, $mdevice;

		if($order['typename']=='alipay'){
			return self::alipay();
		}elseif($order['typename']=='wxpay'){
			if(in_array('1',$channel['apptype']) && $mdevice=='wechat'){
				return ['type'=>'jump','url'=>$siteurl.'pay/wxjspay/'.TRADE_NO.'/?d=1'];
			}elseif($device=='mobile'){
				return self::wxwappay();
			}else{
				return self::wxpay();
			}
		}elseif($order['typename']=='bank'){
			return self::bank();
		}
	}

	//统一下单
	static private function addOrder($trade_type, $sub_appid=null, $sub_openid=null){
		global $siteurl, $channel, $order, $ordername, $conf, $clientip;

		require_once PAY_ROOT."inc/HuifuClient.php";
		$config_info = [
			'sys_id' =>  $channel['appid'],
			'product_id' => $channel['appurl'],
			'merchant_private_key' => $channel['appsecret'],
			'huifu_public_key' => $channel['appkey'],
		];
		$client = new HuifuClient($config_info);

		$param = [
			'req_date' => substr(TRADE_NO,0,8),
			'req_seq_id' => TRADE_NO,
			'huifu_id' => $channel['appmchid']?$channel['appmchid']:$channel['appid'],
			'trade_type' => $trade_type,
			'trans_amt' => $order['realmoney'],
			'goods_desc' => $ordername,
			'notify_url' => $conf['localurl'] . 'pay/notify/' . TRADE_NO . '/',
			'risk_check_data' => json_encode(['ip_addr' => $clientip]),
		];
		if($trade_type == 'T_JSAPI' || $trade_type == 'T_MINIAPP'){
			$param['wx_data'] = json_encode(['sub_appid' => $sub_appid, 'sub_openid' => $sub_openid, 'device_info' => '4', 'spbill_create_ip' => $clientip]);
		}elseif($trade_type == 'A_JSAPI'){
			$param['alipay_data'] = json_encode(['buyer_id' => $sub_openid]);
		}

		if($_SESSION[TRADE_NO.'_pay']){
			$result = $_SESSION[TRADE_NO.'_pay'];
		}else{
			
			$_SESSION[TRADE_NO.'_pay'] = $result;
		}

		return \lib\Payment::lockPayData(TRADE_NO, function() use($client, $param, $trade_type) {
			$result = $client->requestApi('/v2/trade/payment/jspay', $param);
			if(isset($result['resp_code']) && $result['resp_code']=='00000100') {
				if($trade_type == 'T_JSAPI' || $trade_type == 'T_MINIAPP' || $trade_type == 'A_JSAPI'){
					return $result['pay_info'];
				}else{
					return $result['qr_code'];
				}
			}elseif(isset($result['resp_desc'])){
				throw new Exception($result['resp_desc'].($result['bank_message']?' '.$result['bank_message']:''));
			}else{
				throw new Exception('返回数据解析失败');
			}
		});
	}

	//支付宝扫码支付
	static public function alipay(){
		try{
			$code_url = self::addOrder('A_NATIVE');
		}catch(Exception $ex){
			return ['type'=>'error','msg'=>'支付宝支付下单失败！'.$ex->getMessage()];
		}

		return ['type'=>'qrcode','page'=>'alipay_qrcode','url'=>$code_url];
	}

	//微信扫码支付
	static public function wxpay(){
		global $siteurl, $channel;
		if(in_array('2',$channel['apptype']) && !in_array('1',$channel['apptype'])){
			$code_url = $siteurl.'pay/wxwappay/'.TRADE_NO.'/';
		}else{
			$code_url = $siteurl.'pay/wxjspay/'.TRADE_NO.'/';
		}

		if (checkmobile()==true) {
			return ['type'=>'qrcode','page'=>'wxpay_wap','url'=>$code_url];
		} else {
			return ['type'=>'qrcode','page'=>'wxpay_qrcode','url'=>$code_url];
		}
	}

	//微信公众号支付
	static public function wxjspay(){
		global $siteurl, $channel, $order, $ordername, $conf;

		if(!$channel['appwxmp']){
			try{
				$jump_url = self::hostingOrder();
			}catch(Exception $ex){
				return ['type'=>'error','msg'=>'微信支付下单失败！'.$ex->getMessage()];
			}
			return ['type'=>'jump','url'=>$jump_url];
		}
		//①、获取用户openid
		$wxinfo = \lib\Channel::getWeixin($channel['appwxmp']);
		if(!$wxinfo) return ['type'=>'error','msg'=>'支付通道绑定的微信公众号不存在'];
		try{
			$tools = new \WeChatPay\JsApiTool($wxinfo['appid'], $wxinfo['appsecret']);
			$openid = $tools->GetOpenid();
		}catch(Exception $e){
			return ['type'=>'error','msg'=>$e->getMessage()];
		}
		$blocks = checkBlockUser($openid, TRADE_NO);
		if($blocks) return $blocks;

		//②、统一下单
		try{
			$jsApiParameters = self::addOrder('T_JSAPI', $wxinfo['appid'], $openid);
		}catch(Exception $ex){
			return ['type'=>'error','msg'=>'微信支付下单失败！'.$ex->getMessage()];
		}
		
		if($_GET['d']==1){
			$redirect_url='data.backurl';
		}else{
			$redirect_url='\'/pay/ok/'.TRADE_NO.'/\'';
		}
		return ['type'=>'page','page'=>'wxpay_jspay','data'=>['jsApiParameters'=>$jsApiParameters, 'redirect_url'=>$redirect_url]];
	}

	//微信小程序支付
	static public function wxminipay(){
		global $siteurl, $channel, $order, $ordername, $conf;

		$code = isset($_GET['code'])?trim($_GET['code']):exit('{"code":-1,"msg":"code不能为空"}');
		
		//①、获取用户openid
		$wxinfo = \lib\Channel::getWeixin($channel['appwxa']);
		if(!$wxinfo)exit('{"code":-1,"msg":"支付通道绑定的微信小程序不存在"}');
		try{
			$tools = new \WeChatPay\JsApiTool($wxinfo['appid'], $wxinfo['appsecret']);
			$openid = $tools->AppGetOpenid($code);
		}catch(Exception $e){
			exit('{"code":-1,"msg":"'.$e->getMessage().'"}');
		}
		$blocks = checkBlockUser($openid, TRADE_NO);
		if($blocks)exit('{"code":-1,"msg":"'.$blocks['msg'].'"}');

		//②、统一下单
		try{
			$jsApiParameters = self::addOrder('T_MINIAPP', $wxinfo['appid'], $openid);
		}catch(Exception $ex){
			exit('{"code":-1,"msg":"'.$ex->getMessage().'"}');
		}

		exit(json_encode(['code'=>0, 'data'=>json_decode($jsApiParameters, true)]));
	}

	//微信手机支付
	static public function wxwappay(){
		global $siteurl,$channel, $order, $ordername, $conf, $clientip;

		if($channel['appwxa']>0){
			$wxinfo = \lib\Channel::getWeixin($channel['appwxa']);
			if(!$wxinfo) return ['type'=>'error','msg'=>'支付通道绑定的微信小程序不存在'];
			try{
				$code_url = wxminipay_jump_scheme($wxinfo['id'], TRADE_NO);
			}catch(Exception $e){
				return ['type'=>'error','msg'=>$e->getMessage()];
			}
			return ['type'=>'scheme','page'=>'wxpay_mini','url'=>$code_url];
		}elseif(in_array('2',$channel['apptype'])){
			try{
				$code_url = self::wxapphosting();
			}catch(Exception $ex){
				return ['type'=>'error','msg'=>'微信支付下单失败！'.$ex->getMessage()];
			}
			return ['type'=>'scheme','page'=>'wxpay_mini','url'=>$code_url];
		}else{
			$code_url = $siteurl.'pay/wxjspay/'.TRADE_NO.'/';
			return ['type'=>'qrcode','page'=>'wxpay_wap','url'=>$code_url];
		}
	}

	//微信托管小程序下单
	static private function wxapphosting(){
		global $siteurl, $channel, $order, $ordername, $conf, $clientip;

		require_once PAY_ROOT."inc/HuifuClient.php";
		$config_info = [
			'sys_id' =>  $channel['appid'],
			'product_id' => $channel['appurl'],
			'merchant_private_key' => $channel['appsecret'],
			'huifu_public_key' => $channel['appkey'],
		];
		$client = new HuifuClient($config_info);

		$param = [
			'pre_order_type' => '3',
			'req_date' => substr(TRADE_NO,0,8),
			'req_seq_id' => TRADE_NO,
			'huifu_id' => $channel['appmchid']?$channel['appmchid']:$channel['appid'],
			'trans_amt' => $order['realmoney'],
			'goods_desc' => $ordername,
			'notify_url' => $conf['localurl'] . 'pay/notify/' . TRADE_NO . '/',
		];

		return \lib\Payment::lockPayData(TRADE_NO, function() use($client, $param) {
			$result = $client->requestApi('/v2/trade/hosting/payment/preorder', $param);

			if(isset($result['resp_code']) && $result['resp_code']=='00000100') {
				return $result['miniapp_data']['scheme_code'];
			}elseif(isset($result['resp_desc'])){
				throw new Exception($result['resp_desc'].($result['bank_message']?' '.$result['bank_message']:''));
			}else{
				throw new Exception('返回数据解析失败');
			}
		});
	}

	//H5、PC预下单
	static private function hostingOrder(){
		global $siteurl, $channel, $order, $ordername, $conf, $clientip;

		require_once PAY_ROOT."inc/HuifuClient.php";
		$config_info = [
			'sys_id' =>  $channel['appid'],
			'product_id' => $channel['appurl'],
			'merchant_private_key' => $channel['appsecret'],
			'huifu_public_key' => $channel['appkey'],
		];
		$client = new HuifuClient($config_info);

		$param = [
			'req_date' => substr(TRADE_NO,0,8),
			'req_seq_id' => TRADE_NO,
			'huifu_id' => $channel['appmchid']?$channel['appmchid']:$channel['appid'],
			'trans_amt' => $order['realmoney'],
			'goods_desc' => $ordername,
			'pre_order_type' => '1',
			'hosting_data' => json_encode(['project_title'=>$conf['sitename'], 'project_id'=>'', 'callback_url'=>$siteurl. 'pay/return/' . TRADE_NO . '/']),
			'notify_url' => $conf['localurl'] . 'pay/notify/' . TRADE_NO . '/',
		];

		return \lib\Payment::lockPayData(TRADE_NO, function() use($client, $param) {
			$result = $client->requestApi('/v2/trade/hosting/payment/preorder', $param);

			if(isset($result['resp_code']) && $result['resp_code']=='00000100') {
				return $result['jump_url'];
			}elseif(isset($result['resp_desc'])){
				throw new Exception($result['resp_desc'].($result['bank_message']?' '.$result['bank_message']:''));
			}else{
				throw new Exception('返回数据解析失败');
			}
		});
	}

	//云闪付扫码支付
	static public function bank(){
		try{
			$code_url = self::addOrder('U_NATIVE');
		}catch(Exception $ex){
			return ['type'=>'error','msg'=>'云闪付下单失败！'.$ex->getMessage()];
		}

		return ['type'=>'qrcode','page'=>'bank_qrcode','url'=>$code_url];
	}

	//异步回调
	static public function notify(){
		global $channel, $order;

		require_once PAY_ROOT."inc/HuifuClient.php";
		$config_info = [
			'sys_id' =>  $channel['appid'],
			'product_id' => $channel['appurl'],
			'merchant_private_key' => $channel['appsecret'],
			'huifu_public_key' => $channel['appkey'],
		];
		$client = new HuifuClient($config_info);

		$data = json_decode($_POST['resp_data'], true);
		if(!$data)return ['type'=>'html','data'=>'no data'];

		if($client->checkNotifySign($_POST['resp_data'], $_POST['sign'])){
			if ($data['trans_stat'] == 'S') {
				if($data['req_seq_id'] == TRADE_NO){
					$api_trade_no = $data['hf_seq_id'];
					if(isset($data['alipay_response'])){
						$buyer = $data['alipay_response']['buyer_id'];
					}elseif(isset($data['wx_response'])){
						$buyer = $data['wx_response']['sub_openid'];
					}
					processNotify($order, $api_trade_no, $buyer);
				}
				return ['type'=>'html','data'=>'RECV_ORD_ID_'.TRADE_NO];
			}
			return ['type'=>'html','data'=>'resp_code fail'];
		}
		else {
			return ['type'=>'html','data'=>'sign fail'];
		}
	}

	//支付成功页面
	static public function ok(){
		return ['type'=>'page','page'=>'ok'];
	}

	//支付返回页面
	static public function return(){
		return ['type'=>'page','page'=>'return'];
	}

	//退款
	static public function refund($order){
		global $channel;
		if(empty($order))exit();

		require_once PAY_ROOT."inc/HuifuClient.php";
		$config_info = [
			'sys_id' =>  $channel['appid'],
			'product_id' => $channel['appurl'],
			'merchant_private_key' => $channel['appsecret'],
			'huifu_public_key' => $channel['appkey'],
		];
		$client = new HuifuClient($config_info);

		$param = [
			'req_date' => date("Ymd"),
			'req_seq_id' => 'REF'.$order['trade_no'],
			'huifu_id' => $channel['appmchid']?$channel['appmchid']:$channel['appid'],
			'ord_amt' => $order['refundmoney'],
			'org_req_date' => substr($order['trade_no'], 0, 8),
			'org_req_seq_id' => $order['trade_no']
		];
		try{
			$result = $client->requestApi('/v2/trade/payment/scanpay/refund', $param);
		}catch(Exception $e){
			return ['code'=>-1, 'msg'=>$e->getMessage()];
		}

		if($result['resp_code'] == '00000000' || $result['resp_code'] == '00000100'){
			return ['code'=>0, 'trade_no'=>$result['org_req_seq_id'], 'refund_fee'=>$result['ord_amt']];
		}else{
			return ['code'=>-1, 'msg'=>$result['resp_desc']];
		}
	}

}