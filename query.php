<?php

include 'library/init.inc.php';
$template = 'query.phtml';
$currentTime = time();

$activeNav = get_active_nav();
assign('activeNav', $activeNav);

get_area_data();

$smarty->display($template);