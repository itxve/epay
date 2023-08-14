### 彩虹易支付最新开源版
[彩虹易支付官方文档](https://www.kancloud.cn/net909/epay/2590520)

安装项目到当前目录
```
git clone https://github.com/mrlihx/epay.git ./
```
国内服务器用
```
git clone https://ghproxy.com/https://github.com/mrlihx/epay.git ./
```
更新项目
```
git pull
```

Nginx 伪静态
```
location / {
 if (!-e $request_filename) {
   rewrite ^/(.[a-zA-Z0-9\-\_]+).html$ /index.php?mod=$1 last;
 }
 rewrite ^/pay/(.*)$ /pay.php?s=$1 last;
}
location ^~ /plugins {
  deny all;
}
location ^~ /includes {
  deny all;
}
```