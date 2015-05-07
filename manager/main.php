<?php
include 'library/init.inc.php';

back_base_init();
assign('subTitle', '首页');
$smarty->display('main.phtml');
