<?php
include 'library/init.inc.php';

checkAdminLogin();
createMenus();
assign('pageTitle', 'NB_CMS管理后台');
$smarty->display('main.phtml');
