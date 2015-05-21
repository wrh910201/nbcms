<?php
include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}
$action = 'add|edit|list|delete';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));

if( 'list' == $act ) {
    $getGroups = 'select * from '.$db_prefix.'group where publicAccount = \''.$_SESSION['public_account'].'\' order by addTime asc';
    $groups = $db->fetchAll($getGroups);
    if( $groups ) {
        foreach( $groups as $key => $group ) {
            $groups[$key]['addTime'] = date('Y-m-d H:i:s', $group['addTime']);
        }
    }
    assign('groups', $groups);
}

assign('act', $act);
$smarty->display('userGroup.phtml');