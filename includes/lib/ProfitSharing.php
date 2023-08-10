<?php
namespace lib;

use Exception;

class ProfitSharing
{

    //请求分账
    public static function submit($channel, $trade_no, $api_trade_no, $account, $name, $money){
        if($channel['plugin'] == 'wxpayn' || $channel['plugin'] == 'wxpaynp'){
            return self::submit_wxpay($channel, $trade_no, $api_trade_no, $account, $name, $money);
        }
        elseif($channel['plugin'] == 'alipay' || $channel['plugin'] == 'alipaysl' || $channel['plugin'] == 'alipayd'){
            return self::submit_alipay($channel, $trade_no, $api_trade_no, $account, $name, $money);
        }
    }

    //查询分账结果
    public static function query($channel, $trade_no, $api_trade_no, $settle_no){
        if($channel['plugin'] == 'wxpayn' || $channel['plugin'] == 'wxpaynp'){
            return self::query_wxpay($channel, $trade_no, $api_trade_no, $settle_no);
        }
        elseif($channel['plugin'] == 'alipay' || $channel['plugin'] == 'alipaysl' || $channel['plugin'] == 'alipayd'){
            return self::query_alipay($channel, $trade_no, $api_trade_no, $settle_no);
        }
    }

    //解冻剩余资金
    public static function unfreeeze($channel, $trade_no, $api_trade_no){
        if($channel['plugin'] == 'wxpayn' || $channel['plugin'] == 'wxpaynp'){
            return self::unfreeeze_wxpay($channel, $trade_no, $api_trade_no);
        }
        elseif($channel['plugin'] == 'alipay' || $channel['plugin'] == 'alipaysl' || $channel['plugin'] == 'alipayd'){
            return self::unfreeeze_alipay($channel, $trade_no, $api_trade_no);
        }
    }

    //分账回退
    public static function return($channel, $trade_no, $api_trade_no, $account, $money){
        if($channel['plugin'] == 'wxpayn' || $channel['plugin'] == 'wxpaynp'){
            return ['code'=>-1,'msg'=>'分账到个人账户不支持回退'];
        }
        elseif($channel['plugin'] == 'alipay' || $channel['plugin'] == 'alipaysl' || $channel['plugin'] == 'alipayd'){
            return self::return_alipay($channel, $trade_no, $api_trade_no, $account, $money);
        }
    }

    //添加分账接收方
    public static function addReceiver($channel, $account, $name = null){
        if($channel['plugin'] == 'wxpayn' || $channel['plugin'] == 'wxpaynp'){
            return self::addReceiver_wxpay($channel, $account, $name);
        }
        elseif($channel['plugin'] == 'alipay' || $channel['plugin'] == 'alipaysl' || $channel['plugin'] == 'alipayd'){
            return self::addReceiver_alipay($channel, $account, $name);
        }
    }

    //删除分账接收方
    public static function deleteReceiver($channel, $account){
        if($channel['plugin'] == 'wxpayn' || $channel['plugin'] == 'wxpaynp'){
            return self::deleteReceiver_wxpay($channel, $account);
        }
        elseif($channel['plugin'] == 'alipay' || $channel['plugin'] == 'alipaysl' || $channel['plugin'] == 'alipayd'){
            return self::deleteReceiver_alipay($channel, $account);
        }
    }

    private static function submit_wxpay($channel, $trade_no, $api_trade_no, $account, $name, $money){
        $wechatpay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
    
        if($wechatpay_config['ecommerce']){
            $param = [
                'transaction_id' => $api_trade_no,
                'out_order_no' => $trade_no,
                'receivers' => [
                    [
                        'type' => 'PERSONAL_OPENID',
                        'receiver_account' => $account,
                        'amount' => intval(round($money*100)),
                        'description' => '订单分账'
                    ]
                ],
                'finish' => true,
            ];
        }else{
            $param = [
                'transaction_id' => $api_trade_no,
                'out_order_no' => $trade_no,
                'receivers' => [
                    [
                        'type' => 'PERSONAL_OPENID',
                        'account' => $account,
                        'amount' => intval(round($money*100)),
                        'description' => '订单分账'
                    ]
                ],
                'unfreeze_unsplit' => true,
            ];
        }
        try{
            $client = new \WeChatPay\V3\ProfitsharingService($wechatpay_config);
            $result = $client->submit($param);
            return ['code'=>0, 'msg'=>'请求分账成功', 'settle_no'=>$result['order_id']];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    private static function query_wxpay($channel, $trade_no, $api_trade_no, $settle_no){
        $reason_desc = ['ACCOUNT_ABNORMAL'=>'分账接收账户异常', 'NO_RELATION'=>'分账关系已解除', 'RECEIVER_HIGH_RISK'=>'高风险接收方', 'RECEIVER_REAL_NAME_NOT_VERIFIED'=>'接收方未实名', 'NO_AUTH'=>'分账权限已解除', 'RECEIVER_RECEIPT_LIMIT'=>'接收方已达收款限额', 'PAYER_ACCOUNT_ABNORMAL'=>'分出方账户异常', 'INVALID_REQUEST'=>'描述参数设置失败'];

        $wechatpay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        try{
            $client = new \WeChatPay\V3\ProfitsharingService($wechatpay_config);
            $result = $client->query($trade_no, $api_trade_no);
            if($result['state'] == 'FINISHED'){
                $receiver = $result['receivers'][0];
                if($receiver['result'] == 'SUCCESS'){
                    return ['code'=>0, 'status'=>1];
                }elseif($receiver['result'] == 'CLOSED'){
                    return ['code'=>0, 'status'=>2, 'reason'=>'['.$receiver['fail_reason'].']'.$reason_desc[$receiver['fail_reason']]];
                }else{
                    return ['code'=>0, 'status'=>0];
                }
            }else{
                return ['code'=>0, 'status'=>0];
            }
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    private static function unfreeeze_wxpay($channel, $trade_no, $api_trade_no){
        $wechatpay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
    
        try{
            $client = new \WeChatPay\V3\ProfitsharingService($wechatpay_config);
            $client->unfreeze($trade_no, $api_trade_no);
            return ['code'=>0, 'msg'=>'解冻剩余资金成功'];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    private static function addReceiver_wxpay($channel, $account, $name){
        $wechatpay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
    
        try{
            $client = new \WeChatPay\V3\ProfitsharingService($wechatpay_config);
            $client->addReceiver($account, $name);
            return ['code'=>0, 'msg'=>'添加分账接收方成功'];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    private static function deleteReceiver_wxpay($channel, $account){
        $wechatpay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
    
        try{
            $client = new \WeChatPay\V3\ProfitsharingService($wechatpay_config);
            $client->deleteReceiver($account);
            return ['code'=>0, 'msg'=>'删除分账接收方成功'];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }


    private static function submit_alipay($channel, $trade_no, $api_trade_no, $account, $name, $money){
        $type = self::get_alipay_account_type($account);

        $alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        try{
            $settle = new \Alipay\AlipaySettleService($alipay_config);
            $result = $settle->order_settle($api_trade_no, $type, $account, $money);
            return ['code'=>1, 'msg'=>'分账成功', 'settle_no'=>$result['settle_no']];
        } catch (Exception $e) {
            return ['code'=>-2, 'msg'=>$e->getMessage()];
        }
    }

    private static function query_alipay($channel, $trade_no, $api_trade_no, $settle_no){
        $alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        try{
            $settle = new \Alipay\AlipaySettleService($alipay_config);
            $result = $settle->order_settle_query($settle_no);
            $receiver = $result['royalty_detail_list'][0];
            if($receiver['state'] == 'SUCCESS'){
                return ['code'=>0, 'status'=>1];
            }elseif($receiver['state'] == 'FAIL'){
                return ['code'=>0, 'status'=>2, 'reason'=>'['.$receiver['error_code'].']'.$receiver['error_desc']];
            }else{
                return ['code'=>0, 'status'=>0];
            }
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    private static function unfreeeze_alipay($channel, $trade_no, $api_trade_no){
        $alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        try{
            $settle = new \Alipay\AlipaySettleService($alipay_config);
            $settle->order_settle_unfreeze($api_trade_no);
            return ['code'=>0, 'msg'=>'解冻剩余资金成功'];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    private static function return_alipay($channel, $trade_no, $api_trade_no, $account, $money){
        $type = self::get_alipay_account_type($account);

        $alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        try{
            $settle = new \Alipay\AlipaySettleService($alipay_config);
            $settle->order_settle_refund($api_trade_no, $type, $account, $money);
            return ['code'=>0, 'msg'=>'退分账成功'];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }
    
    private static function addReceiver_alipay($channel, $account, $name){
        $type = self::get_alipay_account_type($account);

        $alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        try{
            $settle = new \Alipay\AlipaySettleService($alipay_config);
            $settle->relation_bind($type, $account, $name);
            return ['code'=>0, 'msg'=>'添加分账接收方成功'];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    private static function deleteReceiver_alipay($channel, $account){
        $type = self::get_alipay_account_type($account);
        
        $alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        try{
            $settle = new \Alipay\AlipaySettleService($alipay_config);
            $settle->relation_unbind($type, $account);
            return ['code'=>0, 'msg'=>'删除分账接收方成功'];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    private static function get_alipay_account_type($account){
        if(is_numeric($account) && substr($account,0,4)=='2088' && strlen($account)==16)$type = 'userId';
	    else $type = 'loginName';
        return $type;
    }


    public static function addReceiver_adapay($channel, $member_id, $data){
        $pay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        require(PLUGIN_ROOT.$channel['plugin'].'/inc/Build.class.php');

        $account_info = [
            'card_id' => $data['card_id'],
            'card_name' => $data['card_name'],
            'cert_id' => $data['cert_id'],
            'cert_type' => '00',
            'tel_no' => $data['tel_no'],
            'bank_acct_type' => $data['bank_type'],
        ];
    
        try{
            $adapay = \AdaPay::config($pay_config);
            $adapay->createMember($member_id);
            $result = $adapay->createSettleAccount($member_id, $account_info);
            return ['code'=>0, 'msg'=>'添加分账接收方成功', 'settleid'=>$result['id']];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    public static function editReceiver_adapay($channel, $member_id, $data, $settle_account_id){
        $pay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        require(PLUGIN_ROOT.$channel['plugin'].'/inc/Build.class.php');

        $account_info = [
            'card_id' => $data['card_id'],
            'card_name' => $data['card_name'],
            'cert_id' => $data['cert_id'],
            'cert_type' => '00',
            'tel_no' => $data['tel_no'],
            'bank_acct_type' => $data['bank_type'],
        ];
    
        try{
            $adapay = \AdaPay::config($pay_config);
            $adapay->deleteSettleAccount($member_id, $settle_account_id);
            $result = $adapay->createSettleAccount($member_id, $account_info);
            return ['code'=>0, 'msg'=>'添加分账接收方成功', 'settleid'=>$result['id']];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

    public static function deleteReceiver_adapay($channel, $member_id, $settle_account_id){
        $pay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
        require(PLUGIN_ROOT.$channel['plugin'].'/inc/Build.class.php');

        try{
            $adapay = \AdaPay::config($pay_config);
            $adapay->deleteSettleAccount($member_id, $settle_account_id);
            return ['code'=>0, 'msg'=>'删除分账接收方成功'];
        } catch (Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }
    }

}