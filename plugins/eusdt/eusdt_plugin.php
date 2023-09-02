<?php

class eusdt_plugin
{
    static public $info = [
        'name' => 'eusdt', //支付插件英文名称，需和目录名称一致，不能有重复
        'showname' => 'EusdtPay', //支付插件显示名称
        'author' => 'Enzo', //支付插件作者
        'link' => '', //支付插件作者链接
        'types' => ['usdt'], //支付插件支持的支付方式，可选的有alipay,qqpay,wxpay,bank
        'inputs' => [ //支付插件要求传入的参数以及参数显示名称，可选的有appid,appkey,appsecret,appurl,appmchid
            'appurl' => [
                'name' => '接口地址',
                'type' => 'input',
                'note' => '必须以http://或https://开头，以/结尾',
            ],
            'appid' => [
                'name' => '商户ID',
                'type' => 'input',
                'note' => '1000',
            ],
            'appkey' => [
                'name' => '商户密钥',
                'type' => 'input',
                'note' => '',
            ],
            'appswitch' => [
                'name' => '是否使用mapi接口',
                'type' => 'select',
                'options' => [0 => '否', 1 => '是'],
            ],
        ],
        'select' => null,
        'note' => '', //支付密钥填写说明
        'bindwxmp' => false, //是否支持绑定微信公众号
        'bindwxa' => false, //是否支持绑定微信小程序
    ];

    static public function submit()
    {
        global $channel, $order;
        if ($channel['appswitch'] == 1) {
            return ['type' => 'jump', 'url' => '/pay/usdt/' . TRADE_NO . '/'];
        } else {
            $uOrder = self::addOrder();
            if ($uOrder['status_code'] == 200) {
                return ['type' => 'jump', 'url' => $uOrder['data']['payment_url']];
            } else {
                return ['type' => "error", 'msg' => $uOrder["message"]];
            }
        }
    }

    static public function mapi()
    {
        global $siteurl, $channel;
        if ($channel['appswitch'] == 1) {
            return self::usdt();
        } else {
            return ['type' => 'jump', 'url' => $siteurl . 'pay/submit/' . TRADE_NO . '/'];
        }
    }

    static public function addOrder()
    {
        global $siteurl, $channel, $order;
        $parameter = [
            "amount" => (float)$order['realmoney'], // RMB原价
            "order_id" => TRADE_NO,
            'redirect_url' => $siteurl . 'pay/return/' . TRADE_NO . '/',
            'notify_url' => $siteurl . 'pay/notify/' . TRADE_NO . '/'
        ];
        $parameter['signature'] = self::sign($parameter, $channel['appkey']);
        $response = self::post($channel['appurl'] . "api/v1/order/create-transaction", json_encode($parameter));
        $result = json_decode($response, true);
        return $result;
    }

    static public function usdt()
    {
        $uOrder = self::addOrder();
        if ($uOrder['status_code'] == 200) {
            $retdata = $uOrder['data'];
            $data = [
                "token" => $retdata['token'],  // 转账地址
                "usdt" => $retdata['actual_amount'],  // USDT金额
                "amount" => $retdata['amount'],  // RMB金额
                "rate" => $retdata['rate'],  // 当前汇率
                "expiration_time" => $retdata['expiration_time']  // 过期时间
            ];
            return ['type' => 'qrcode', 'page' => 'usdt_qrcode', 'url' => $data];
        } else {
            return ['type' => "error", 'msg' => $uOrder["message"] ?? "USDT支付下单失败"];
        }
    }

    //异步回调
    static public function notify()
    {
        global $channel, $order;

        //file_put_contents('eusdt_logs.txt', file_get_contents('php://input'));
        $body = json_decode(file_get_contents('php://input'), true);

        if ($body['status'] != 2) return ['type' => 'html', 'data' => 'status error'];
        if (round($body['amount'], 2) != round($order['money'], 2)) return ['type' => 'html', 'data' => 'money error'];

        $sign = self::sign($body, $channel['appkey']);
        if ($sign === $body['signature']) {
            $out_trade_no = daddslashes($body['order_id']);
            $trade_no = daddslashes($body['trade_id']);
            if ($out_trade_no == TRADE_NO) {
                processNotify($order, $trade_no);
            }
            return ['type' => 'html', 'data' => 'ok'];
        } else {
            return ['type' => 'html', 'data' => 'sign error'];
        }
    }

    //支付返回页面
    static public function return()
    {
        return ['type' => 'page', 'page' => 'return'];
    }


    static public function sign(array $parameter, string $signKey)
    {
        ksort($parameter);
        reset($parameter);
        $sign = '';
        $urls = '';
        foreach ($parameter as $key => $val) {
            if ($val == '') continue;
            if ($key != 'signature') {
                if ($sign != '') {
                    $sign .= "&";
                    $urls .= "&";
                }
                $sign .= "$key=$val";
                $urls .= "$key=" . urlencode($val);
            }
        }
        $sign = md5($sign . $signKey); //密码追加进入开始MD5签名
        return $sign;
    }


    function post($url, $params)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        $headers = [
            'Content-Type: application/json'
        ];
        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $content = curl_exec($curl);
        curl_close($curl);
        return $content;
    }

}