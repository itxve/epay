<?php
namespace lib;

use Exception;

class MsgNotice
{
    public static function send($scene, $uid, $param){
        global $DB, $conf;
        if($uid == 0){
            $switch = self::getMessageSwitch($scene);

            if ($conf['telegram_uid'] != "") self::send_telegram_tplmsg($scene, $conf['telegram_uid'], $param); // telegram
            if($switch == 1){
                $receiver = $conf['mail_recv']?$conf['mail_recv']:$conf['mail_name'];
                return self::send_mail_msg($scene, $receiver, $param);
            }
        }else{
            $userrow = $DB->find('user', 'email,wx_uid,msgconfig,telegram', ['uid'=>$uid]);
            $userrow['msgconfig'] = unserialize($userrow['msgconfig']);
            if($scene == 'order' && $userrow['msgconfig']['order_money']>0 && $param['money']<$userrow['msgconfig']['order_money']) return false;
//            if ($userrow['telegram'] != "") self::send_telegram_tplmsg($scene, $userrow['telegram'], $param); // telegram

            if($userrow['msgconfig'][$scene] == 1 && !empty($userrow['wx_uid'])){
                return self::send_wechat_tplmsg($scene, $userrow['wx_uid'], $param);
            }elseif($userrow['msgconfig'][$scene] == 2 && !empty($userrow['email']) && self::getMessageSwitch($scene) == 1){
                return self::send_mail_msg($scene, $userrow['email'], $param);
            }elseif($userrow['msgconfig'][$scene] == 10 && !empty($userrow['telegram']) && self::getMessageSwitch($scene) == 1){
                return self::send_telegram_tplmsg($scene, $userrow['telegram'], $param);
            }
        }
        return false;
    }

    public static function send_telegram_tplmsg($scene, $tid, $param){
        global $conf;
        $content = "";
        if($scene == 'settle'){
            $type = $param['type'];
            $typeStr = "未知";
            $realmoney = $param['realmoney'];
            if ($type == 1){
                $typeStr = "支付宝";
            }elseif ($type == 2){
                $typeStr = "微信";
            }elseif ($type == 3){
                $typeStr = "QQ钱包";
            }elseif ($type == 4){
                $typeStr = "银行卡";
            }elseif ($type == 5){
                $typeStr = "USDT-trc20";
                $realmoney = round($realmoney / $conf['settle_usdt_rate'], 2) . "u";
            }elseif ($type == 6){
                $typeStr = "币安USDT-trc20";
                $realmoney = round($realmoney / $conf['settle_usdt_rate'], 2) . "u";
            }
            $content = "📢".$conf['sitename'].date('m-d', strtotime('-1 day', strtotime($param['addtime'])))."款项结算通知\n收款方式：" . $typeStr ."\n收款账号：".$param['account']."\n结算金额：".$param['money']."元\n实际到账：". $realmoney."\n\n✅已打款✅";
        }else if($scene == 'order'){
            $content = "📢📢📢新订单通知。\n系统订单号：`".$param['trade_no']."`\n商户订单号：`".$param['out_trade_no']."`\n商品名称：".$param['name']."\n订单金额：￥".$param['realmoney']."\n支付方式：".$param['type']."\n订单时间：".$param['addtime']."\n支付时间：".$param['time'];
            if($param['notify'] > 0) $content .= "\n❌通知失败:".$param['notify']."次";
        }else if($scene == 'regaudit'){
            $content = "📢".$conf['sitename']."有新注册的商户待审核，请及时前往用户列表审核处理。\n商户ID：".$param['uid']."\n注册账号：".$param['account']."\n注册时间：".$param['time'];
        }else if($scene == 'apply'){
            $content = "📢".$conf['sitename']."商户发起了手动提现申请，请及时处理。\n商户ID：".$param['uid']."\n提现方式：".$param['type']."\n提现金额：".$param['realmoney']."\n提交时间：".date('Y-m-d H:i:s');
        } else if($scene == 'order_notify'){
            $content = "⚠️⚠️订单异步通知失败。\n系统订单号：`".$param['trade_no']."`\n商户订单号：`".$param['out_trade_no']."`\n商品名称：".$param['name']."\n订单金额：￥".$param['realmoney']."\n支付方式：".$param['typeshowname']."\n订单时间：".$param['addtime']."\n支付时间：".$param['endtime']."\n❌通知失败:".$param['notify']."次\n\n[重新通知](" . $param['notify_url'] . ")";
        }
        if ($content=="") return false;
        telegramBot_SendMessage($tid, $content);
        return true;
    }

    public static function send_wechat_tplmsg($scene, $openid, $param){
        global $conf, $siteurl, $CACHE;
        $wid = $conf['login_wx'];
        if($scene == 'order'){
            $template_id = $conf['wxnotice_tpl_order'];
            if(strlen($param['out_trade_no']) > 32) $param['out_trade_no'] = substr($param['out_trade_no'], 0, 32);
            if(mb_strlen($param['name']) > 20) $param['name'] = mb_substr($param['name'], 0, 20);
            $data = [];
            if($conf['wxnotice_tpl_order_no']) $data[$conf['wxnotice_tpl_order_no']] = ['value'=>$param['trade_no']];
            if($conf['wxnotice_tpl_order_name']) $data[$conf['wxnotice_tpl_order_name']] = ['value'=>$param['name']];
            if($conf['wxnotice_tpl_order_money']) $data[$conf['wxnotice_tpl_order_money']] = ['value'=>'￥'.$param['money']];
            if($conf['wxnotice_tpl_order_time']) $data[$conf['wxnotice_tpl_order_time']] = ['value'=>$param['time']];
            if($conf['wxnotice_tpl_order_outno']) $data[$conf['wxnotice_tpl_order_outno']] = ['value'=>$param['out_trade_no']];
            $jumpurl = $siteurl.'user/order.php';
        }elseif($scene == 'settle'){
            $template_id = $conf['wxnotice_tpl_settle'];
            $data = [];
            if($conf['wxnotice_tpl_settle_type']) $data[$conf['wxnotice_tpl_settle_type']] = ['value'=>'结算成功'];
            if($conf['wxnotice_tpl_settle_account']) $data[$conf['wxnotice_tpl_settle_account']] = ['value'=>$param['account']];
            if($conf['wxnotice_tpl_settle_money']) $data[$conf['wxnotice_tpl_settle_money']] = ['value'=>'￥'.$param['money']];
            if($conf['wxnotice_tpl_settle_realmoney']) $data[$conf['wxnotice_tpl_settle_realmoney']] = ['value'=>'￥'.$param['realmoney']];
            if($conf['wxnotice_tpl_settle_time']) $data[$conf['wxnotice_tpl_settle_time']] = ['value'=>$param['time']];
            $jumpurl = $siteurl.'user/settle.php';
        }elseif($scene == 'login'){
            $template_id = $conf['wxnotice_tpl_login'];
            $data = [];
            if($conf['wxnotice_tpl_login_user']) $data[$conf['wxnotice_tpl_login_user']] = ['value'=>$param['user']];
            if($conf['wxnotice_tpl_login_time']) $data[$conf['wxnotice_tpl_login_time']] = ['value'=>$param['time']];
            if($conf['wxnotice_tpl_login_name']) $data[$conf['wxnotice_tpl_login_name']] = ['value'=>$conf['sitename']];
            if($conf['wxnotice_tpl_login_ip']) $data[$conf['wxnotice_tpl_login_ip']] = ['value'=>$param['clientip']];
            if($conf['wxnotice_tpl_login_iploc']) $data[$conf['wxnotice_tpl_login_iploc']] = ['value'=>$param['ipinfo']];
            $jumpurl = $siteurl.'user/';
        }elseif($scene == 'complain'){
            $template_id = $conf['wxnotice_tpl_complain'];
            $data = [];
            if(mb_strlen($param['name']) > 20) $param['name'] = mb_substr($param['name'], 0, 20);
            if(mb_strlen($param['reason']) > 20) $param['reason'] = mb_substr($param['reason'], 0, 20);
            if($conf['wxnotice_tpl_complain_order_no']) $data[$conf['wxnotice_tpl_complain_order_no']] = ['value'=>$param['trade_no']];
            if($conf['wxnotice_tpl_complain_time']) $data[$conf['wxnotice_tpl_complain_time']] = ['value'=>$param['time']];
            if($conf['wxnotice_tpl_complain_reason']) $data[$conf['wxnotice_tpl_complain_reason']] = ['value'=>$param['content']];
            if($conf['wxnotice_tpl_complain_type']) $data[$conf['wxnotice_tpl_complain_type']] = ['value'=>$param['type']];
            if($conf['wxnotice_tpl_complain_name']) $data[$conf['wxnotice_tpl_complain_name']] = ['value'=>$param['name']];
            $jumpurl = $siteurl.'user/';
        }
        if(empty($template_id) || empty($wid)) return false;
    
        $wechat = new \lib\wechat\WechatAPI($wid);
        try{
            return $wechat->sendTemplateMessage($openid, $template_id, $jumpurl, $data);
        }catch(Exception $e){
            $errmsg = $e->getMessage();
            $CACHE->save('wxtplerrmsg', ['errmsg'=>$errmsg, 'time'=>date('Y-m-d H:i:s')], 86400);
            //echo $errmsg;
            return false;
        }
    }

    private static function send_mail_msg($scene, $receiver, $param){
        global $conf, $siteurl, $CACHE;
        if($scene == 'regaudit'){
            $title = '新注册商户待审核提醒';
            $content = '尊敬的'.$conf['sitename'].'管理员，网站有新注册的商户待审核，请及时前往用户列表审核处理。<br/>商户ID：'.$param['uid'].'<br/>注册账号：'.$param['account'].'<br/>注册时间：'.date('Y-m-d H:i:s');
        }elseif($scene == 'apply'){
            $title = '新的提现待处理提醒';
            $content = '尊敬的'.$conf['sitename'].'管理员，商户'.$param['uid'].'发起了手动提现申请，请及时处理。<br/>商户ID：'.$param['uid'].'<br/>提现方式：'.$param['type'].'<br/>提现金额：'.$param['realmoney'].'<br/>提交时间：'.date('Y-m-d H:i:s');
        }elseif($scene == 'domain'){
            $title = '新的授权支付域名待审核提醒';
            $content = '尊敬的'.$conf['sitename'].'管理员，商户'.$param['uid'].'提交了新的授权支付域名，请及时审核处理。<br/>商户ID：'.$param['uid'].'<br/>授权域名：'.$param['domain'].'<br/>提交时间：'.date('Y-m-d H:i:s');
        }elseif($scene == 'order'){
            $title = '新订单通知 - '.$conf['sitename'];
            $content = '尊敬的商户，您有一条新订单通知。<br/>商品名称：'.$param['name'].'<br/>订单金额：￥'.$param['money'].'<br/>支付方式：'.$param['type'].'<br/>商户订单号：'.$param['out_trade_no'].'<br/>系统订单号：'.$param['trade_no'].'<br/>支付完成时间：'.$param['time'];
        }elseif($scene == 'settle'){
            $title = '结算完成通知 - '.$conf['sitename'];
            $content = '尊敬的商户，今日结算已完成，请查收。<br/>结算金额：￥'.$param['money'].'<br/>实际到账：￥'.$param['realmoney'].'<br/>结算账号：'.$param['account'].'<br/>结算完成时间：'.$param['time'];
        }elseif($scene == 'complain'){
            $title = '支付交易投诉通知 - '.$conf['sitename'];
            $content = '尊敬的商户，'.$param['type'].'！<br/>系统订单号：'.$param['trade_no'].'<br/>投诉原因：'.$param['title'].'<br/>投诉详情：'.$param['content'].'<br/>商品名称：'.$param['ordername'].'<br/>订单金额：￥'.$param['money'].'<br/>投诉时间：'.$param['time'];
        }
        $result = send_mail($receiver, $title, $content);
        if($result === true) return true;

        if(!empty($result)){
            $CACHE->save('mailerrmsg', ['errmsg'=>$result, 'time'=>date('Y-m-d H:i:s')], 86400);
        }
        return false;
    }

    private static function getMessageSwitch($scene){
        global $conf;
        if(isset($conf['msgconfig_'.$scene])){
            return $conf['msgconfig_'.$scene];
        }
        return false;
    }
}