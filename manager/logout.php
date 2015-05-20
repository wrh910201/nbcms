<?php

include 'library/init.inc.php';

checkAdminLogin();

unset($_SESSION['purview']);
unset($_SESSION['name']);
unset($_SESSION['photo']);
unset($_SESSION['account']);

showSystemMessage('您已退出登陆', array(array('alt'=>'管理后台登录', 'link'=>'index.php')));
exit;