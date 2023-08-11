<?php

function create_ordernum() {
    global $conf;
    $prefix = $conf['orderprefix'];
    $prefix = str_replace(' ', '', $prefix);
    if(strlen($prefix) < 1) return date("YmdHis").rand(11111,99999);

    $datetime = date('ymdHis');
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomPart = '';
    while (strlen($randomPart) < 19 - strlen($datetime) - strlen($prefix)) {
        $randomPart .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $prefix . $datetime . $randomPart;
}
function dwz($url){
    $dwzapi = "http://3oe.cn/api.php";
    $p = "?type=toShort";
    $p .= "&kind=3oe.cn";
    $p .= "&url=$url";
    $p .= "&endtime=15";
    $p .= "&key=123456";
    $return = json_decode(curl_get($dwzapi.$p), true);
    $dwz = $return['url'];
    if(empty($dwz)) $dwz = $url;
    return $dwz;
}

function curl_get($url)
{
	global $conf;
	$ch=curl_init($url);
	if($conf['proxy'] == 1){
		$proxy_server = $conf['proxy_server'];
		$proxy_port = intval($conf['proxy_port']);
		$proxy_userpwd = $conf['proxy_user'].':'.$conf['proxy_pwd'];
		if($conf['proxy_type'] == 'https'){
			$proxy_type = CURLPROXY_HTTPS;
		}elseif($conf['proxy_type'] == 'sock4'){
			$proxy_type = CURLPROXY_SOCKS4;
		}elseif($conf['proxy_type'] == 'sock5'){
			$proxy_type = CURLPROXY_SOCKS5;
		}else{
			$proxy_type = CURLPROXY_HTTP;
		}
		curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_PROXY, $proxy_server);
		curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
		curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_userpwd);
		curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy_type);
	}
	$httpheader[] = "Accept: */*";
	$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
	$httpheader[] = "Connection: close";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36');
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$content=curl_exec($ch);
	curl_close($ch);
	return $content;
}
function get_curl($url, $post=0, $referer=0, $cookie=0, $header=0, $ua=0, $nobaody=0, $addheader=0)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$httpheader[] = "Accept: */*";
	$httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
	$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
	$httpheader[] = "Connection: close";
	if($addheader){
		$httpheader = array_merge($httpheader, $addheader);
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	if ($post) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	if ($header) {
		curl_setopt($ch, CURLOPT_HEADER, true);
	}
	if ($cookie) {
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	}
	if($referer){
		curl_setopt($ch, CURLOPT_REFERER, $referer);
	}
	if ($ua) {
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	}
	else {
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.0.4; es-mx; HTC_One_X Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0");
	}
	if ($nobaody) {
		curl_setopt($ch, CURLOPT_NOBODY, 1);
	}
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
}
function real_ip($type=0){
$ip = $_SERVER['REMOTE_ADDR'];
if($type<=0 && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
	foreach ($matches[0] AS $xip) {
		if (filter_var($xip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			$ip = $xip;
			break;
		}
	}
} elseif ($type<=0 && isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
	$ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif ($type<=1 && isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
	$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif ($type<=1 && isset($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
	$ip = $_SERVER['HTTP_X_REAL_IP'];
}
return $ip;
}
function get_ip_city($ip)
{
    $url = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip=';
    $city = get_curl($url . $ip);
	$city = mb_convert_encoding($city, "UTF-8", "GB2312");
    $city = json_decode($city, true);
    if ($city['city']) {
        $location = $city['pro'].$city['city'];
    } else {
        $location = $city['pro'];
    }
	if($location){
		return $location;
	}else{
		return false;
	}
}
function send_mail($to, $sub, $msg) {
	global $conf;
	if($conf['mail_cloud']==1){
		$mail = new \lib\mail\Sendcloud($conf['mail_apiuser'], $conf['mail_apikey']);
		return $mail->send($to, $sub, $msg, $conf['mail_name2'], $conf['sitename']);
	}elseif($conf['mail_cloud']==2){
		$mail = new \lib\mail\Aliyun($conf['mail_apiuser'], $conf['mail_apikey']);
		return $mail->send($to, $sub, $msg, $conf['mail_name2'], $conf['sitename']);
	}else{
		if(!$conf['mail_name'] || !$conf['mail_port'] || !$conf['mail_smtp'] || !$conf['mail_pwd'])return false;
		$port = intval($conf['mail_port']);
		$mail = new \lib\mail\PHPMailer\PHPMailer(true);
		try{
			$mail->SMTPDebug = 0;
			$mail->CharSet = 'UTF-8';
			$mail->Timeout = 5;
			$mail->isSMTP();
			$mail->Host = $conf['mail_smtp'];
			$mail->SMTPAuth = true;
			$mail->Username = $conf['mail_name'];
			$mail->Password = $conf['mail_pwd'];
			if($port == 587) $mail->SMTPSecure = 'tls';
			else if($port >= 465) $mail->SMTPSecure = 'ssl';
			else $mail->SMTPAutoTLS = false;
			$mail->Port = intval($conf['mail_port']);
			$mail->setFrom($conf['mail_name'], $conf['sitename']);
			$mail->addAddress($to);
			$mail->addReplyTo($conf['mail_name'], $conf['sitename']);
			$mail->isHTML(true);
			$mail->Subject = $sub;
			$mail->Body = $msg;
			$mail->send();
			return true;
		} catch (Exception $e) {
			return $mail->ErrorInfo;
		}
	}
}
function send_sms($phone, $code, $scope='reg'){
	global $conf;
	if($scope == 'reg'){
		$moban = $conf['sms_tpl_reg'];
	}elseif($scope == 'login'){
		$moban = $conf['sms_tpl_login'];
	}elseif($scope == 'find'){
		$moban = $conf['sms_tpl_find'];
	}elseif($scope == 'edit'){
		$moban = $conf['sms_tpl_edit'];
	}
	if($conf['sms_api']==1){
		$sms = new \lib\sms\Qcloud($conf['sms_appid'], $conf['sms_appkey']);
		$arr = $sms->send($phone, $moban, [$code], $conf['sms_sign']);
		if(isset($arr['result']) && $arr['result']==0){
			return true;
		}else{
			return $arr['errmsg'];
		}
	}elseif($conf['sms_api']==2){
		$sms = new \lib\sms\Aliyun($conf['sms_appid'], $conf['sms_appkey']);
		$arr = $sms->send($phone, $code, $moban, $conf['sms_sign'], $conf['sitename']);
		if(isset($arr['Code']) && $arr['Code']=='OK'){
			return true;
		}else{
			return $arr['Message'];
		}
	}elseif($conf['sms_api']==3){
		$app=$conf['sitename'];
		$url = 'https://api.topthink.com/sms/send';
		$param = ['appCode'=>$conf['sms_appkey'], 'signId'=>$conf['sms_sign'], 'templateId'=>$moban, 'phone'=>$phone, 'params'=>json_encode(['code'=>$code])];
		$data=get_curl($url, http_build_query($param));
		$arr=json_decode($data,true);
		if(isset($arr['code']) && $arr['code']==0){
			return true;
		}else{
			return $arr['message'];
		}
	}elseif($conf['sms_api']==4){
		$sms = new \lib\sms\SmsBao($conf['sms_appid'], $conf['sms_appkey']);
		return $sms->send($phone, $code, $moban, $conf['sms_sign']);
	}else{
		$app=$conf['sitename'];
		$url = 'http://sms.php.gs/sms/send/yzm';
		$param = ['appkey'=>$conf['sms_appkey'], 'phone'=>$phone, 'moban'=>$moban, 'code'=>$code, 'app'=>$app];
		$data=get_curl($url, http_build_query($param));
		$arr=json_decode($data,true);
		if($arr['status']=='200'){
			return true;
		}else{
			return $arr['error_msg_zh'];
		}
	}
}
function daddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = daddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

function dstrpos($string, $arr) {
	if(empty($string)) return false;
	foreach((array)$arr as $v) {
		if(strpos($string, $v) !== false) {
			return true;
		}
	}
	return false;
}

function checkmobile() {
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$ualist = array('android', 'midp', 'nokia', 'mobile', 'iphone', 'ipod', 'blackberry', 'windows phone');
	if((dstrpos($useragent, $ualist) || strexists($_SERVER['HTTP_ACCEPT'], "VND.WAP") || strexists($_SERVER['HTTP_VIA'],"wap")))
		return true;
	else
		return false;
}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if(((int)substr($result, 0, 10) == 0 || (int)substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}
function showmsg($content = '未知的异常',$type = 4,$back = false)
{
switch($type)
{
case 1:
	$panel="success";
break;
case 2:
	$panel="info";
break;
case 3:
	$panel="warning";
break;
case 4:
	$panel="danger";
break;
}

echo '<div class="panel panel-'.$panel.'">
      <div class="panel-heading">
        <h3 class="panel-title">提示信息</h3>
        </div>
        <div class="panel-body">';
echo $content;

if ($back) {
	echo '<hr/><a href="'.$back.'"><< 返回上一页</a>';
}
else
    echo '<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a>';

echo '</div>
    </div>';
	exit;
}
function sysmsg($msg = '未知的异常',$title = '站点提示信息') {
    ?>  
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title?></title>
        <style type="text/css">
html{background:#eee}body{background:#fff;color:#333;font-family:"微软雅黑","Microsoft YaHei",sans-serif;margin:2em auto;padding:1em 2em;max-width:700px;-webkit-box-shadow:10px 10px 10px rgba(0,0,0,.13);box-shadow:10px 10px 10px rgba(0,0,0,.13);opacity:.8}h1{border-bottom:1px solid #dadada;clear:both;color:#666;font:24px "微软雅黑","Microsoft YaHei",sans-serif;margin:30px 0 0 0;padding:0;padding-bottom:7px}#error-page{margin-top:50px}h3{text-align:center}#error-page p{font-size:9px;line-height:1.5;margin:25px 0 20px}#error-page code{font-family:Consolas,Monaco,monospace}ul li{margin-bottom:10px;font-size:9px}a{color:#21759B;text-decoration:none;margin-top:-10px}a:hover{color:#D54E21}.button{background:#f7f7f7;border:1px solid #ccc;color:#555;display:inline-block;text-decoration:none;font-size:9px;line-height:26px;height:28px;margin:0;padding:0 10px 1px;cursor:pointer;-webkit-border-radius:3px;-webkit-appearance:none;border-radius:3px;white-space:nowrap;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;-webkit-box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);vertical-align:top}.button.button-large{height:29px;line-height:28px;padding:0 12px}.button:focus,.button:hover{background:#fafafa;border-color:#999;color:#222}.button:focus{-webkit-box-shadow:1px 1px 1px rgba(0,0,0,.2);box-shadow:1px 1px 1px rgba(0,0,0,.2)}.button:active{background:#eee;border-color:#999;color:#333;-webkit-box-shadow:inset 0 2px 5px -3px rgba(0,0,0,.5);box-shadow:inset 0 2px 5px -3px rgba(0,0,0,.5)}table{table-layout:auto;border:1px solid #333;empty-cells:show;border-collapse:collapse}th{padding:4px;border:1px solid #333;overflow:hidden;color:#333;background:#eee}td{padding:4px;border:1px solid #333;overflow:hidden;color:#333}
        </style>
    </head>
    <body id="error-page">
        <?php echo '<h3>'.$title.'</h3>';
        echo $msg; ?>
    </body>
    </html>
    <?php
    exit;
}
function returnTemplate($return_url) {
	$url = base64_encode($return_url);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>支付成功跳转页面</title>
        <style type="text/css">
body{margin:0;padding:0}
p{position:absolute;left:50%;top:50%;height:35px;margin:-35px 0 0 -160px;padding:20px;font:bold 16px/30px "宋体",Arial;background:#f9fafc url(/assets/img/loading.gif) no-repeat 20px 20px;text-indent:40px;border:1px solid #c5d0dc}
#waiting{font-family:Arial}
        </style>
    </head>
    <body id="return-page">
        <p>支付成功，正在跳转请稍候...</p>
    </body>
	<script>window.location.href=window.atob("<?php echo $url?>");</script>
    </html>
    <?php
    exit;
}
function submitTemplate($html_text){
	?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>正在为您跳转到支付页面，请稍候...</title>
	<style type="text/css">
body{margin:0;padding:0}
#waiting{position:absolute;left:50%;top:50%;height:35px;margin:-35px 0 0 -160px;padding:20px;font:bold 16px/30px "宋体",Arial;background:#f9fafc url(/assets/img/loading.gif) no-repeat 20px 20px;text-indent:40px;border:1px solid #c5d0dc}
	</style>
</head>
<body>
<p id="waiting">正在为您跳转到支付页面，请稍候...</p>
<?php echo $html_text?>
</body>
</html>
	<?php
	exit;
}
function getSid() {
    return md5(uniqid(mt_rand(), true) . microtime());
}
function getMd5Pwd($pwd, $salt=null) {
    return md5(md5($pwd) . md5('1277180438'.$salt));
}

/**
 * 取中间文本
 * @param string $str
 * @param string $leftStr
 * @param string $rightStr
 */
function getSubstr($str, $leftStr, $rightStr)
{
	$left = strpos($str, $leftStr);
	$start = $left+strlen($leftStr);
	$right = strpos($str, $rightStr, $start);
	if($left < 0) return '';
	if($right>0){
		return substr($str, $start, $right-$start);
	}else{
		return substr($str, $start);
	}
}

function getSetting($k, $force = false){
	global $DB,$CACHE;
	if($force) return $DB->getColumn("SELECT v FROM pre_config WHERE k=:k LIMIT 1", [':k'=>$k]);
	$cache = $CACHE->get($k);
	return $cache[$k];
}
function saveSetting($k, $v){
	global $DB;
	return $DB->exec("REPLACE INTO pre_config SET v=:v,k=:k", [':v'=>$v, ':k'=>$k]);
}
function checkGroupSettings($str){
	foreach(explode(',',$str) as $row){
		if(!strpos($row,':'))return false;
	}
	return true;
}

function creat_callback($data){
	global $DB,$conf;
	$key=$DB->getColumn("SELECT `key` FROM pre_user WHERE uid='{$data['uid']}' LIMIT 1");
	$type=$DB->getColumn("SELECT name FROM pre_type WHERE id='{$data['type']}' LIMIT 1");
	$array=array('pid'=>$data['uid'],'trade_no'=>$data['trade_no'],'out_trade_no'=>$data['out_trade_no'],'type'=>$type,'name'=>$data['name'],'money'=>(float)$data['money'],'trade_status'=>'TRADE_SUCCESS');
	if(!empty($data['param']))$array['param']=$data['param'];
	if($conf['notifyordername']==1)$array['name']='product';
	$array['sign'] = \lib\Payment::makeSign($array, $key);
	$array['sign_type'] = 'MD5';
	$query_str = http_build_query($array);
	if(strpos($data['notify_url'],'?'))
		$url['notify']=$data['notify_url'].'&'.$query_str;
	else
		$url['notify']=$data['notify_url'].'?'.$query_str;
	if(strpos($data['return_url'],'?'))
		$url['return']=$data['return_url'].'&'.$query_str;
	else
		$url['return']=$data['return_url'].'?'.$query_str;
	if($data['tid']>0){
		$url['return']=$data['return_url'];
	}
	return $url;
}

function getdomain($url){
	$arr=parse_url($url);
	$host = $arr['host'];
	if(isset($arr['port']) && $arr['port']!=80 && $arr['port']!=443)$host .= ':'.$arr['port'];
	return $host;
}
function get_host($url){
	$arr=parse_url($url);
	return $arr['host'];
}
function get_main_host($url){
	$arr=parse_url($url);
	$host = $arr['host'];
	if(filter_var($host, FILTER_VALIDATE_IP))return $host;
	if(substr_count($host, '.')>1){
		$host = substr($host, strpos($host, '.')+1);
	}
	return $host;
}

function do_notify($url){
	$return = curl_get($url);
	if(strpos($return,'success')!==false || strpos($return,'SUCCESS')!==false || strpos($return,'Success')!==false){
		return true;
	}else{
		return false;
	}
}

function checkBlockUser($openid, $trade_no){
	global $DB;
	$DB->update('order', ['buyer'=>$openid], ['trade_no'=>$trade_no]);
	$black = $DB->find('blacklist', '*', ['type'=>0, 'content'=>$openid], null, 1);
	if($black){
		return ['type'=>'error','msg'=>'系统异常无法完成付款'];
	}
	return false;
}

function processReturn($order, $api_trade_no=null, $buyer=null){
	\lib\Payment::processOrder(false, $order, $api_trade_no, $buyer);
}

function processNotify($order, $api_trade_no=null, $buyer=null){
	\lib\Payment::processOrder(true, $order, $api_trade_no, $buyer);
}

function processOrder($srow,$notify=true){
	global $DB,$CACHE,$conf,$channel;
	$addmoney = $srow['getmoney'];
	$reducemoney = round($srow['realmoney']-$srow['getmoney'], 2);
	if($reducemoney<0)$reducemoney=0;
	if($srow['tid']==1){ //商户注册
		changeUserMoney($srow['uid'], $addmoney, true, '订单收入', $srow['trade_no']);
		$info = unserialize($CACHE->read('reg_'.$srow['trade_no']));
		if($info){
			$key = random(32);
			$paystatus = $conf['user_review']==1?2:1;
			$sds=$DB->exec("INSERT INTO `pre_user` (`upid`, `key`, `money`, `email`, `phone`, `addtime`, `pay`, `settle`, `keylogin`, `apply`, `status`) VALUES (:upid, :key, '0.00', :email, :phone, :addtime, :paystatus, 1, 0, 0, 1)", [':upid'=>$info['upid'], ':key'=>$key, ':email'=>$info['email'], ':phone'=>$info['phone'], ':addtime'=>$info['addtime'], ':paystatus'=>$paystatus]);
			$uid=$DB->lastInsertId();
			$pwd = getMd5Pwd($info['pwd'], $uid);
			$DB->exec("UPDATE `pre_user` SET `pwd` ='{$pwd}' WHERE `uid`='$uid'");
			if($sds && !empty($info['email'])){
				$sub = $conf['sitename'].' - 注册成功通知';
				$msg = '<h2>商户注册成功通知</h2>感谢您注册'.$conf['sitename'].'！<br/>您的登录账号：'.$info['email'].'<br/>您的商户ID：'.$uid.'<br/>您的商户秘钥：'.$key.'<br/>'.$conf['sitename'].'官网：<a href="http://'.$_SERVER['HTTP_HOST'].'/" target="_blank">'.$_SERVER['HTTP_HOST'].'</a><br/>【<a href="http://'.$_SERVER['HTTP_HOST'].'/user/" target="_blank">商户管理后台</a>】';
				$result = send_mail($info['email'], $sub, $msg);
			}
		}
	}else if($srow['tid']==2){ //充值余额
		changeUserMoney($srow['uid'], $addmoney, true, '余额充值', $srow['trade_no']);
	}else if($srow['tid']==3){ //聚合收款码
		if($channel['mode']==1){
			if($reducemoney>0)
				changeUserMoney($srow['uid'], $reducemoney, false, '在线收款服务费', $srow['trade_no']);
		}else{
			changeUserMoney($srow['uid'], $addmoney, true, '在线收款', $srow['trade_no']);
		}
	}else if($srow['tid']==4){ //购买用户组
		$param = json_decode($srow['param'], true);
		changeUserGroup($srow['uid'], $param['gid'], $param['endtime']);
	}else{
		if($channel['mode']==1){
			if($reducemoney>0)
				changeUserMoney($srow['uid'], $reducemoney, false, '订单服务费', $srow['trade_no']);
		}else{
			changeUserMoney($srow['uid'], $addmoney, true, '订单收入', $srow['trade_no']);
		}
		/*if(preg_match('/X(\d+)\!(\d+)X/',$srow['name'],$match)){
			if($match[1] >= 1000 && $match[2] > 0 && $match[2] < 100){
				$addmoney = round($srow['money']*$match[2]/100,2);
				changeUserMoney($match[1], $addmoney, true, '订单分成', $srow['trade_no']);
			}
		}*/
		$url=creat_callback($srow);
		if(do_notify($url['notify'])){
			$DB->exec("UPDATE pre_order SET notify=0 WHERE trade_no='{$srow['trade_no']}'");
		}elseif($notify==true){
			//通知时间：1分钟，3分钟，20分钟，1小时，2小时
			$DB->exec("UPDATE pre_order SET notify=1,notifytime=date_add(now(), interval 1 minute) WHERE trade_no='{$srow['trade_no']}'");
		}
	}
	if($channel['daytop']>0){
		$cachekey = 'daytop'.$channel['id'].date("Ymd");
		$nowmoney = $CACHE->read($cachekey);
		if(!$nowmoney)$nowmoney=0;
		$nowmoney=$nowmoney+$srow['money'];
		$CACHE->save($cachekey, $nowmoney, 86400);
		if($nowmoney>=$channel['daytop']){
			$DB->exec("UPDATE pre_channel SET daystatus=1 WHERE id='{$channel['id']}'");
		}
	}
	if($srow['profits']>0){ //订单分账处理
		$psreceiver = $DB->find('psreceiver', '*', ['id'=>$srow['profits']]);
		if($psreceiver){
			$psmoney = round(floor($srow['realmoney'] * $psreceiver['rate']) / 100, 2);
			$DB->insert('psorder', ['rid'=>$psreceiver['id'], 'trade_no'=>$srow['trade_no'], 'api_trade_no'=>$srow['api_trade_no'], 'money'=>$psmoney, 'status'=>0, 'addtime'=>'NOW()']);
		}
	}
}

function changeUserMoney($uid, $money, $add=true, $type=null, $orderid=null){
	global $DB;
	if($money<=0)return;
	if($type=='订单退款'){
		$isrefund = $DB->getColumn("SELECT id FROM pre_record WHERE type='订单退款' AND trade_no='{$orderid}' LIMIT 1");
		if($isrefund)return;
	}
	$DB->beginTransaction();
	$oldmoney = $DB->getColumn("SELECT money FROM pre_user WHERE uid='{$uid}' LIMIT 1 FOR UPDATE");
	if($add == true){
		$action = 1;
		$newmoney = round($oldmoney+$money, 2);
	}else{
		$action = 2;
		$newmoney = round($oldmoney-$money, 2);
	}
	$res = $DB->exec("UPDATE pre_user SET money='{$newmoney}' WHERE uid='{$uid}'");
	$DB->exec("INSERT INTO `pre_record` (`uid`, `action`, `money`, `oldmoney`, `newmoney`, `type`, `trade_no`, `date`) VALUES (:uid, :action, :money, :oldmoney, :newmoney, :type, :orderid, NOW())", [':uid'=>$uid, ':action'=>$action, ':money'=>$money, ':oldmoney'=>$oldmoney, ':newmoney'=>$newmoney, ':type'=>$type, ':orderid'=>$orderid]);
	$DB->commit();
	return $res;
}

function changeUserGroup($uid, $gid, $endtime = null){
	global $DB;
	return $DB->update('user', ['gid'=>$gid, 'endtime'=>$endtime], ['uid'=>$uid]);
}

function checkIfActive($string) {
	$array=explode(',',$string);
	$php_self=substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1,strrpos($_SERVER['REQUEST_URI'],'.')-strrpos($_SERVER['REQUEST_URI'],'/')-1);
	if (in_array($php_self,$array)){
		return 'active';
	}else
		return null;
}

//通用转账
//type alipay:支付宝,wxpay:微信,qqpay:QQ钱包,bank:银行卡
function transfer_do($type, $channel, $out_trade_no, $payee_account, $payee_real_name, $money){
	global $conf;
	if($type == 'alipay'){
		if($channel['plugin'] == 'jeepay'){
			return transferJeepay($channel, 'alipay', $out_trade_no, $payee_account, $payee_real_name, $money);
		}else{
			return transferToAlipay($channel, $out_trade_no, $payee_account, $payee_real_name, $money);
		}
	}elseif($type == 'wxpay'){
		if($channel['plugin'] == 'jeepay'){
			return transferJeepay($channel, 'wxpay', $out_trade_no, $payee_account, $payee_real_name, $money);
		}elseif($channel['plugin'] == 'wxpayn'){
			return transferToWeixinNew($channel, $out_trade_no, $payee_account, $payee_real_name, $money);
		}else{
			return transferToWeixin($channel, $out_trade_no, $payee_account, $payee_real_name, $money);
		}
	}elseif($type == 'qqpay'){
		return transferToQQ($channel, $out_trade_no, $payee_account, $payee_real_name, $money);
	}elseif($type == 'bank'){
		return transferToBank($channel, $out_trade_no, $payee_account, $payee_real_name, $money);
	}
	return false;
}

//单笔转账到支付宝
function transferToAlipay($channel, $out_trade_no, $payee_account, $payee_real_name, $money){
	global $conf;

	if(is_numeric($payee_account) && substr($payee_account,0,4)=='2088')$is_userid = 1;
	elseif(strpos($payee_account, '@')!==false || is_numeric($payee_account))$is_userid = 0;
	else $is_userid = 2;

	$data = array();
	$alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
	try{
		$transfer = new \Alipay\AlipayTransferService($alipay_config);
		$result = $transfer->transferToAccount($out_trade_no, $money, $is_userid, $payee_account, $payee_real_name, $conf['transfer_name']);

		$data['code']=0;
		$data['ret']=1;
		$data['msg']='success';
		$data['orderid']=$result['order_id'];
		$data['paydate']=$result['trans_date'];
	}catch(\Alipay\Aop\AlipayResponseException $e){
		$result = $e->getResponse();
		if ($result['code'] == 40004) {
			$data['code']=0;
			$data['ret']=0;
			$data['msg']=$e->getMessage();
			$data['sub_code']=$result['sub_code'];
			$data['sub_msg']=$result['sub_msg'];
		} else {
			$data['code']=-1;
			$data['msg']=$e->getMessage();
		}
	}catch(Exception $e){
		$data['code']=-1;
		$data['msg']=$e->getMessage();
	}
	return $data;
}

//微信企业付款
function transferToWeixin($channel, $out_trade_no, $payee_account, $payee_real_name, $money){
	global $conf;

	$money = strval($money * 100);
	$data=[];
	$wechatpay_config = require(PLUGIN_ROOT.'wxpay/inc/config.php');
	try{
		$client = new \WeChatPay\TransferService($wechatpay_config);
		$result = $client->transfer($out_trade_no, $payee_account, $payee_real_name, $money, $conf['transfer_desc']);
		$data['code']=0;
		$data['ret']=1;
		$data['msg']='success';
		$data['orderid']=$result["payment_no"];
		$data['paydate']=$result["payment_time"];
	}catch(\WeChatPay\WeChatPayException $e){
		$result = $e->getResponse();
		if($result["result_code"]=='FAIL' && ($result["err_code"]=='OPENID_ERROR'||$result["err_code"]=='NAME_MISMATCH'||$result["err_code"]=='MONEY_LIMIT'||$result["err_code"]=='V2_ACCOUNT_SIMPLE_BAN')) {
			$data['code']=0;
			$data['ret']=0;
			$data['msg']=$e->getMessage();
			$data['sub_code']=$result["err_code"];
			$data['sub_msg']=$result["err_code_des"];
		} else {
			$data['code']=-1;
			$data['msg']=$e->getMessage();
		}
	}catch(Exception $e){
		$data['code']=-1;
		$data['msg']=$e->getMessage();
	}
	return $data;
}

//微信商家转账
function transferToWeixinNew($channel, $out_trade_no, $payee_account, $payee_real_name, $money){
	global $conf;
	$fail_reason_desc = ['ACCOUNT_FROZEN'=>'该用户账户被冻结', 'REAL_NAME_CHECK_FAIL'=>'收款人未实名认证', 'NAME_NOT_CORRECT'=>'收款人姓名校验不通过', 'OPENID_INVALID'=>'Openid校验失败', 'TRANSFER_QUOTA_EXCEED'=>'超过用户单笔收款额度', 'DAY_RECEIVED_QUOTA_EXCEED'=>'超过用户单日收款额度', 'MONTH_RECEIVED_QUOTA_EXCEED'=>'超过用户单月收款额度', 'DAY_RECEIVED_COUNT_EXCEED'=>'超过用户单日收款次数', 'PRODUCT_AUTH_CHECK_FAIL'=>'未开通该权限或权限被冻结', 'OVERDUE_CLOSE'=>'超过系统重试期，系统自动关闭', 'ID_CARD_NOT_CORRECT'=>'收款人身份证校验不通过', 'ACCOUNT_NOT_EXIST'=>'该用户账户不存在', 'TRANSFER_RISK'=>'该笔转账可能存在风险，已被微信拦截', 'OTHER_FAIL_REASON_TYPE'=>'其它失败原因', 'REALNAME_ACCOUNT_RECEIVED_QUOTA_EXCEED'=>'用户账户收款受限，请引导用户在微信支付查看详情', 'RECEIVE_ACCOUNT_NOT_PERMMIT'=>'未配置该用户为转账收款人', 'PAYER_ACCOUNT_ABNORMAL'=>'商户账户付款受限，可前往商户平台获取解除功能限制指引', 'PAYEE_ACCOUNT_ABNORMAL'=>'用户账户收款异常，请引导用户完善身份信息', 'TRANSFER_REMARK_SET_FAIL'=>'转账备注设置失败，请调整后重新再试','TRANSFER_SCENE_UNAVAILABLE'=>'该转账场景暂不可用，请确认转账场景ID是否正确','TRANSFER_SCENE_INVALID'=>'你尚未获取该转账场景，请确认转账场景ID是否正确','RECEIVE_ACCOUNT_NOT_CONFIGURE'=>'请前往商户平台-商家转账到零钱-前往功能-转账场景中添加','BLOCK_B2C_USERLIMITAMOUNT_MONTH'=>'用户账户存在风险收款受限，本月不支持继续向该用户付款','MERCHANT_REJECT'=>'转账验密人已驳回转账','MERCHANT_NOT_CONFIRM'=>'转账验密人超时未验密'];

	$wechatpay_config = require(PLUGIN_ROOT.'wxpayn/inc/config.php');
	$out_batch_no = $out_trade_no;
	try{
		$client = new \WeChatPay\V3\TransferService($wechatpay_config);
	} catch (Exception $e) {
		return ['code'=>-1, 'msg'=>$e->getMessage()];
	}

	$transfer_detail = [
		'out_detail_no' => $out_trade_no,
		'transfer_amount' => intval(round($money*100)),
		'transfer_remark' => $conf['transfer_desc'],
		'openid' => $payee_account,
	];
	if(!empty($payee_real_name)){
		$transfer_detail['user_name'] = $client->rsaEncrypt($payee_real_name);
	}
	$param = [
		'out_batch_no' => $out_batch_no,
		'batch_name' => '转账给'.$payee_real_name,
		'batch_remark' => date("YmdHis"),
		'total_amount' => intval(round($money*100)),
		'total_num' => 1,
		'transfer_detail_list' => [
			$transfer_detail
		],
	];

	try{
		$result = $client->transfer($param);
	} catch (Exception $e) {
		$errorMsg = $e->getMessage();
		if(!strpos($errorMsg, '对应的订单已经存在')){
			return ['code'=>-1, 'msg'=>$errorMsg];
		}
	}
	$batch_id = $result['batch_id'];

	usleep(500000);

	try{
		$result = $client->transferoutdetail($out_batch_no, $out_trade_no);
	} catch (Exception $e) {
		return ['code'=>-1, 'msg'=>$e->getMessage()];
	}
	if($result['detail_status'] == 'PROCESSING'){
		return ['code'=>0, 'ret'=>0, 'msg'=>'转账中，可稍后重试查看结果', 'sub_code'=>'', 'sub_msg'=>'转账中，可稍后重试查看结果'];
	}elseif($result['detail_status'] == 'FAIL'){
		if($result['fail_reason'] == 'TRANSFER_REMARK_SET_FAIL' || $result['fail_reason'] == 'PAYER_ACCOUNT_ABNORMAL' || $result['fail_reason'] == 'PRODUCT_AUTH_CHECK_FAIL' || $result['fail_reason'] == 'TRANSFER_SCENE_UNAVAILABLE' || $result['fail_reason'] == 'TRANSFER_SCENE_INVALID' || $result['fail_reason'] == 'RECEIVE_ACCOUNT_NOT_CONFIGURE'){
			return ['code'=>-1, 'msg'=>'['.$result['fail_reason'].']'.$fail_reason_desc[$result['fail_reason']]];
		}else{
			return ['code'=>0, 'ret'=>0, 'msg'=>'['.$result['fail_reason'].']'.$fail_reason_desc[$result['fail_reason']], 'sub_code'=>$result['fail_reason'], 'sub_msg'=>$fail_reason_desc[$result['fail_reason']]];
		}
	}elseif($result['detail_status'] == 'SUCCESS'){
		return ['code'=>0, 'ret'=>1, 'msg'=>'success', 'orderid'=>$result['detail_id'], 'paydate'=>$result['update_time']];
	}else{
		return ['code'=>-1, 'msg'=>'转账状态未知'];
	}
}

//QQ钱包企业付款
function transferToQQ($channel, $out_trade_no, $payee_account, $payee_real_name, $money){
	global $conf;

	$money = strval($money * 100);
	$data=[];
	$qqpay_config = require(PLUGIN_ROOT.'qqpay/inc/config.php');
	try{
		$client = new \QQPay\TransferService($qqpay_config);
		$result = $client->transfer($out_trade_no, $payee_account, $payee_real_name, $money, $conf['transfer_desc']);
		$data['code']=0;
		$data['ret']=1;
		$data['msg']='success';
		$data['orderid']=$result["transaction_id"];
		$data['paydate']=date('Y-m-d H:i:s');
	}catch(\QQPay\QQPayException $e){
		$result = $e->getResponse();
		if($result['err_code']=='TRANSFER_FEE_LIMIT_ERROR' || $result['err_code']=='TRANSFER_FAIL' || $result['err_code']=='NOTENOUGH' || $result['err_code']=='APPID_OR_OPENID_ERR' || $result['err_code']=='TOTAL_FEE_OUT_OF_LIMIT' || $result['err_code']=='REALNAME_CHECK_ERROR' || $result['err_code']=='RE_USER_NAME_CHECK_ERROR') {
			$data['code']=0;
			$data['ret']=0;
			$data['msg']=$e->getMessage();
			$data['sub_code']=$result["err_code"];
			$data['sub_msg']=$result["err_code_des"];
		} else {
			$data['code']=-1;
			$data['msg']=$e->getMessage();
		}
	}catch(Exception $e){
		$data['code']=-1;
		$data['msg']=$e->getMessage();
	}
	return $data;
}

//单笔转账到银行卡
function transferToBank($channel, $out_trade_no, $payee_account, $payee_real_name, $money){
	global $conf;
	$data = array();
	$alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
	try{
		$transfer = new \Alipay\AlipayTransferService($alipay_config);
		$result = $transfer->transferToBankCard($out_trade_no, $money, $payee_account, $payee_real_name, $conf['transfer_name']);

		$data['code']=0;
		$data['ret']=1;
		$data['msg']='success';
		$data['orderid']=$result['order_id'];
		$data['paydate']=$result['trans_date'];
	}catch(\Alipay\Aop\AlipayResponseException $e){
		$result = $e->getResponse();
		if ($result['code'] == 40004) {
			$data['code']=0;
			$data['ret']=0;
			$data['msg']=$e->getMessage();
			$data['sub_code']=$result['sub_code'];
			$data['sub_msg']=$result['sub_msg'];
		} else {
			$data['code']=-1;
			$data['msg']=$e->getMessage();
		}
	}catch(Exception $e){
		$data['code']=-1;
		$data['msg']=$e->getMessage();
	}
	return $data;
}

function transferJeepay($channel, $type, $out_trade_no, $payee_account, $payee_real_name, $money){
	define("PAY_ROOT", PLUGIN_ROOT.'jeepay/');
	require_once PAY_ROOT."jeepay_plugin.php";
	$data = jeepay_plugin::trasfer($channel, $type, $out_trade_no, $payee_account, $payee_real_name, $money);
	return $data;
}

function ordername_replace($name,$oldname,$uid,$order){
	global $DB;
	if(strpos($name,'[name]')!==false){
		$name = str_replace('[name]', $oldname, $name);
	}
	if(strpos($name,'[order]')!==false){
		$name = str_replace('[order]', $order, $name);
	}
	if(strpos($name,'[qq]')!==false){
		$qq = $DB->getColumn("SELECT qq FROM pre_user WHERE uid='{$uid}' limit 1");
		$name = str_replace('[qq]', $qq, $name);
	}
	if(strpos($name,'[time]')!==false){
		$name = str_replace('[time]', time(), $name);
	}
    if(strpos($name,'[sorder]')!==false){
        $sorder = $DB->getColumn("SELECT out_trade_no FROM pre_order WHERE trade_no='{$order}' LIMIT 1");
        $name = str_replace('[sorder]', $sorder, $name);
    }
	return $name;
}

function is_idcard( $id )
{
	$id = strtoupper($id);
	$regx = "/(^\d{17}([0-9]|X)$)/";
	$arr_split = array();
	if(strlen($id)!=18 || !preg_match($regx, $id))
	{
		return false;
	}
	$regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
	@preg_match($regx, $id, $arr_split);
	$dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
	if(!strtotime($dtm_birth)) //检查生日日期是否正确
	{
		return false;
	}
	else
	{
		//检验18位身份证的校验码是否正确。
		//校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
		$arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
		$arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
		$sign = 0;
		for ( $i = 0; $i < 17; $i++ )
		{
			$b = (int) $id[$i];
			$w = $arr_int[$i];
			$sign += $b * $w;
		}
		$n = $sign % 11;
		$val_num = $arr_ch[$n];
		if ($val_num != substr($id,17, 1))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}

function checkRefererHost(){
	if(!$_SERVER['HTTP_REFERER'])return false;
	$url_arr = parse_url($_SERVER['HTTP_REFERER']);
	$http_host = $_SERVER['HTTP_HOST'];
	if(strpos($http_host,':'))$http_host = substr($http_host, 0, strpos($http_host, ':'));
	return $url_arr['host'] === $http_host;
}
function randFloat($min=0, $max=1){
	return $min + mt_rand()/mt_getrandmax() * ($max-$min);
}

function check_cert($idcard, $name, $phone){
	global $conf;
	$appcode = $conf['cert_appcode'];
	$url = 'http://phone3.market.alicloudapi.com/phonethree';
	$post = ['idcard'=>$idcard, 'phone'=>$phone, 'realname'=>$name];
	$data = get_curl($url.'?'.http_build_query($post), 0,0,0,0,0,0, ['Authorization: APPCODE '.$appcode, 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8']);
	$arr=json_decode($data,true);
	if(array_key_exists('code',$arr) && $arr['code']==200){
		return ['code'=>0, 'msg'=>$arr['msg']];
	}elseif(array_key_exists('msg',$arr)){
		return ['code'=>-1, 'msg'=>$arr['msg']];
	}else{
		return ['code'=>-2, 'msg'=>'返回结果解析失败'];
	}
}
function check_corp_cert($companyName, $creditNo, $legalPerson){
	global $conf;
	$appcode = $conf['cert_appcode2'];
	$url = 'http://companythree.shumaidata.com/companythree/check';
	$post = ['companyName'=>$companyName, 'creditNo'=>$creditNo, 'legalPerson'=>$legalPerson];
	$data = get_curl($url.'?'.http_build_query($post), 0, 0,0,0,0,0, ['Authorization: APPCODE '.$appcode, 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8']);
	$arr=json_decode($data,true);
	if(array_key_exists('code',$arr) && $arr['code']==200){
		if($arr['data']['result']==0){
			return ['code'=>0, 'msg'=>$arr['data']['desc']];
		}else{
			return ['code'=>-1, 'msg'=>$arr['data']['desc']=='不一致'?'公司与法人信息不一致':$arr['data']['desc']];
		}
	}elseif(array_key_exists('msg',$arr)){
		return ['code'=>-1, 'msg'=>$arr['msg']];
	}else{
		return ['code'=>-2, 'msg'=>'返回结果解析失败'];
	}
}
function show_cert_type($certtype){
	if($certtype == 1){
		return '企业实名认证';
	}else{
		return '个人实名认证';
	}
}
function show_cert_method($certmethod){
	if($certmethod == 1){
		return '微信快捷认证';
	}elseif($certmethod == 2){
		return '手机号三要素认证';
	}elseif($certmethod == 3){
		return '人工审核认证';
	}else{
		return '支付宝快捷认证';
	}
}
/**
 * 获取指定用户支付成功率
 *
 * @param $uid
 *
 * @return float|int
 */
function getSuccRateByUser($uid)
{
    global $DB, $conf;

    $minute   = intval($conf['pay_succ_range_minute']);
    $add_time = date('Y-m-d H:i:s', strtotime("-{$minute} minute"));
    $states   = $DB->getAll("SELECT status, COUNT(*) as num FROM `pay_order` WHERE `uid` = '{$uid}' and addtime >= '{$add_time}' GROUP by status");
    $succ     = 0;
    $fail     = 0;
    $total    = 0;
    foreach ($states as $state) {
        $total += $state['num'];
        if ($state['status'] == 1) {
            $succ += $state['num'];

            continue;
        }

        $fail += $state['num'];
    }

    // 如果请求频率低于两秒一个，则认为成功率及格
    if ($total <= $minute * 60 * 0.5) {

        return 100;
    }

    return round($succ / $total, 4) * 100;
}

function randomFloat($min = 0, $max = 1) {
	$num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
	return sprintf("%.2f",$num);
}

function wx_get_access_token($appid, $secret) {
	global $DB;
	$row = $DB->getRow("SELECT id FROM pre_weixin WHERE appid='{$appid}' LIMIT 1");
	if($row) return $row['id'];
	return false;
}

function wxminipay_jump_scheme($wid, $orderid){
	global $conf, $order, $siteurl;
	if($conf['wxminipay_path']) {
		$path = $conf['wxminipay_path'];
		$query = 'orderid='.$orderid.'&sign='.md5(SYS_KEY.$orderid.SYS_KEY);
	}else{
		$jump_url = $siteurl.'pay/wxminipay/'.$orderid.'/';
		$path = 'pages/pay/pay';
		$query = 'money='.$order['realmoney'].'&url='.$jump_url;
	}
	$wechat = new \lib\wechat\WechatAPI($wid);
	return $wechat->generate_scheme($path, $query);
}

function checkDomain($domain){
	if(empty($domain) || !preg_match('/^[-$a-z0-9_*.]{2,512}$/i', $domain) || (stripos($domain, '.') === false) || substr($domain, -1) == '.' || substr($domain, 0 ,1) == '.' || substr($domain, 0 ,1) == '*' && substr($domain, 1 ,1) != '.' || substr_count($domain, '*')>1 || strpos($domain, '*')>0 || strlen($domain)<4) return false;
	return true;
}

//微信合单支付，返回所有子单金额
function combinepay_submoneys($money){
	global $conf;
	if(!$conf['wxcombine_open'] || !$conf['wxcombine_minmoney']) return false;
	if($money >= intval($conf['wxcombine_minmoney']*100)){
		$subnum = 3;
		$submoney = intval($money/$subnum);
		while($submoney > intval($conf['wxcombine_submoney']*100)){
			$subnum++;
			$submoney = intval($money/$subnum);
			if($subnum==50)break;
		}
		$submoneys = [];
		for($i=0;$i<$subnum;$i++){
			$submoneys[] = $submoney;
		}
		$mod = $money%$subnum;
		if($mod > 0){
			for($i=0;$i<$mod;$i++){
				$submoneys[$i] += 1;
			}
		}
		return $submoneys;
	}
	return false;
}

function get_invite_code($uid){
	$str = (string)$uid;
	$tmp = '';
	for($i=0;$i<strlen($str);$i++){
		$tmp.=substr($str,$i,1) ^ substr(SYS_KEY,$i,1);
	}
	return str_replace('=','',base64_encode($tmp));
}

function get_invite_uid($code){
	$str = base64_decode($code);
	$tmp = '';
	for($i=0;$i<strlen($str);$i++){
		$tmp.=substr($str,$i,1) ^ substr(SYS_KEY,$i,1);
	}
	return $tmp;
}

function profitSharing_item($row){
	global $DB;
	$id = $row['id'];
	$channel = \lib\Channel::get($row['channel']);
	if(!$channel) return;
	// status:0-待分账,1-已提交,2-成功,3-失败
	if($row['status']==0){
		$result = \lib\ProfitSharing::submit($channel, $row['trade_no'], $row['api_trade_no'], $row['account'], $row['name'], $row['money']);
		if($result['code'] == 0){
			$DB->update('psorder', ['status'=>1,'settle_no'=>$result['settle_no']], ['id'=>$id]);
		}elseif($result['code'] == 1){
			$DB->update('psorder', ['status'=>2,'settle_no'=>$result['settle_no']], ['id'=>$id]);
		}elseif($result['code'] == -2){
			$DB->update('psorder', ['status'=>3,'result'=>$result['msg']], ['id'=>$id]);
		}
		echo $row['trade_no'].' '.$result['msg'].'<br/>';
	}elseif($row['status']==1){
		$result = \lib\ProfitSharing::query($channel, $row['trade_no'], $row['api_trade_no'], $row['settle_no']);
		if($result['code']==0){
			if($result['status']==1){
				$DB->update('psorder', ['status'=>2], ['id'=>$id]);
				$result = '分账成功';
			}elseif($result['status']==2){
				$DB->update('psorder', ['status'=>3,'result'=>$result['reason']], ['id'=>$id]);
				$result = '分账失败:'.$result['reason'];
			}else{
				$result = '正在分账';
			}
			echo $row['trade_no'].' '.$result.'<br/>';
		}else{
			echo $row['trade_no'].' 查询失败:'.$result['msg'].'<br/>';
		}
	}
}

function currency_convert($from, $to, $amount){
	$param = [
		'from' => $from,
		'to' => $to,
		'amount' => $amount
	];
	$url = 'https://api.exchangerate.host/convert?'.http_build_query($param);
	$data = get_curl($url);
	$arr = json_decode($data, true);
	if($arr['success']===true){
		return $arr['result'];
	}else{
		throw new Exception('汇率转换失败');
	}
}
