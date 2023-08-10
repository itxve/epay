<?php
/*
 * 微信点金计划iframe页面
*/
$nosession = true;
include("./includes/common.php");

@header('Content-Type: text/html; charset=UTF-8');

$sub_mch_id = $_GET['sub_mch_id'];
$out_trade_no = $_GET['out_trade_no'];
$check_code = $_GET['check_code'];

if(!$out_trade_no)exit('订单号不能为空');

$order = $DB->getRow("SELECT * FROM pre_order WHERE trade_no=:trade_no limit 1", [':trade_no'=>$out_trade_no]);
if(!$order)$order = $DB->getRow("SELECT * FROM pre_order WHERE api_trade_no=:trade_no limit 1", [':trade_no'=>$out_trade_no]);
if(!$order)exit('订单号不存在');
$trade_no = $order['trade_no'];

$jump_url = $siteurl.'pay/return/'.$trade_no.'/';
?><!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>商户中心</title>
    <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" name="viewport"/>
    <meta content="yes" name="apple-mobile-web-app-capable"/>
    <meta content="black" name="apple-mobile-web-app-status-bar-style"/>
    <meta content="telephone=no" name="format-detection"/>
    <script type="text/javascript" charset="UTF-8" src="https://wx.gtimg.com/pay_h5/goldplan/js/jgoldplan-1.0.0.js"></script>
</head>
<body>
<script>
var mchData = {action:'jumpOut', jumpOutUrl:'<?php echo $jump_url?>'};
var postData = JSON.stringify(mchData);
parent.postMessage(postData,'https://payapp.weixin.qq.com');
mchData = {action:'onIframeReady', displayStyle:'SHOW_OFFICIAL_PAGE'};
postData = JSON.stringify(mchData);
parent.postMessage(postData,'https://payapp.weixin.qq.com');
</script>
</body>
</html>