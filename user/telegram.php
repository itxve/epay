<?php
include("../includes/common.php");


if($islogin2==1 && $_GET['bind']==1){
    $bind_url = $conf['telegram_boturl']."?start=".rc4("bind_".$uid."_".time(), $conf['telegram_key'], true);
    exit( "<script>window.open('$bind_url', '_blank');window.history.back();</script>" );
}else if($islogin2==1 && isset($_GET['unbind'])){
    $DB->exec("update `pre_user` set `telegram` ='' where `uid`='$uid'");
    telegramBot_SendMessage($userrow['telegram'], "商户号:".$uid." 已解绑。");
    @header('Content-Type: text/html; charset=UTF-8');
    exit("<script language='javascript'>alert('您已成功解绑TelegramBot！');window.location.href='./editinfo.php';</script>");
}
