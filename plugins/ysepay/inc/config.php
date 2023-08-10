<?php
$config = array (
	//服务商商户号
	'partner_id' => $channel['appid'],

	//银盛公钥
	'ysepay_public_key' => $channel['appkey'],

	//商户私钥
	'merchant_private_key' => $channel['appsecret'],

	//收款商户号
	'seller_id' => $channel['appmchid'],
);

if(empty($config['seller_id'])) $config['seller_id'] = $config['partner_id'];
