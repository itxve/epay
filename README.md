## 彩虹易支付2023.8.31最新版
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

### Nginx 伪静态
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

## IIS 伪静态
```
	<rule name="payrule1_rewrite" stopProcessing="true">
		<match url="^(.[a-zA-Z0-9-_]+).html"/>
		<conditions logicalGrouping="MatchAll">
			<add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true"/>
			<add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true"/>
		</conditions>
		<action type="Rewrite" url="index.php?mod={R:1}"/>
	</rule>
	<rule name="payrule2_rewrite" stopProcessing="true">
		<match url="^pay/(.*)"/>
		<conditions logicalGrouping="MatchAll">
			<add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true"/>
			<add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true"/>
		</conditions>
		<action type="Rewrite" url="pay.php?s={R:1}"/>
	</rule>

```