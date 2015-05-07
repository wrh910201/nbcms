<?php

include 'library/init.inc.php';
back_base_init();
assign('subTitle', '轮播管理');
$smarty->display('carousel.phtml');