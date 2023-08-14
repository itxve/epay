<?php

class eusdt_plugin
{
    static public $info = [
        'name' => 'eusdt', //支付插件英文名称，需和目录名称一致，不能有重复
        'showname' => 'USDT支付', //支付插件显示名称
        'author' => 'Enzo', //支付插件作者
        'link' => '', //支付插件作者链接
        'types' => ['usdt'], //支付插件支持的支付方式，可选的有alipay,qqpay,wxpay,bank
        'inputs' => [ //支付插件要求传入的参数以及参数显示名称，可选的有appid,appkey,appsecret,appurl,appmchid
            'appurl' => [
                'name' => '接口地址',
                'type' => 'input',
                'note' => '必须以http://或https://开头，以/结尾',
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

}