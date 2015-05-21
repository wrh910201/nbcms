<?php
include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}
$action = 'add|edit|list|delete|sync';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));


if( 'list' == $act ) {

    $getUsers = 'select u.*, g.name as groupName from '.$db_prefix.'user as u ';
    $getUsers .= ' left join '.$db_prefix.'group as g on u.groupId = g.id';
    $getUsers .= ' where u.publicAccount = \''.$_SESSION['public_account'].'\' order by unsubscribed asc;';
    $users = $db->fetchAll($getUsers);
    if( $users ) {
        foreach ($users as $key => $user) {
            $users[$key]['addTime'] = date('Y-m-s H:i:s', $user['addTime']);
            if ($user['unsubscribed'] == 0) {
                $users[$key]['unsubscribed'] = '是';
            } else {
                $users[$key]['unsubscribed'] = '否';
            }
            if( empty($user['groupName']) ) {
                $users[$key]['groupName'] = '默认分组';
            }

        }
    }
    assign('users', $users);
}

assign('act', $act);
$smarty->display('user.phtml');