<?php
include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_bind', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}
$action = 'edit';
$operation = 'bind|edit';

$opera = checkAction($operation, getPOST('opera'));
$act = checkAction($action, getGET('act'));

if('bind' == $opera)
{
    $name = getPOST('name');
    $account = getPOST('account');
    $token = getPOST('token');
    $appID = getPOST('appID');
    $appsecret = getPOST('appsecret');
    $accountType = getPOST('accountType', 0);

    if($name == '')
    {
        showSystemMessage($lang['warning']['publicAccount_empty']);
    } else {
        $name = $db->escape($name);
    }

    if($account == '')
    {
        showSystemMessage($lang['warning']['originalId_empty']);
    } else {
        $account = $db->escape($account);
    }

    if($token == '')
    {
        showSystemMessage($lang['warning']['token_empty']);
    } else {
        $token = $db->escape($token);
    }

    if($accountType == 1)
    {
        if($appID == '')
        {
            showSystemMessage($lang['warning']['appID_empty']);
        } else {
            $appID = $db->escape($appID);
        }

        if($appsecret == '')
        {
            showSystemMessage($lang['warning']['appsecret_empty']);
        } else {
            $appsecret = $db->escape($appsecret);
        }
    } else {
        $accountType = 0;
        $appID = '';
        $appsecret = '';
    }

    $addPublicAccount = 'insert into `'.$db_prefix.'publicAccount` (`name`,`account`,`adminUserAccount`,`token`,'.
                        '`appID`,`appsecret`,`accountType`) values (\'%s\',\'%s\',\'%s\',\'%s\',\'%s\',\'%s\',\'%s\')';
    $addPublicAccount = sprintf($addPublicAccount, $name, $account, $_SESSION['account'], $token, $appID, $appsecret, $accountType);
    if($db->insert($addPublicAccount))
    {
        $_SESSION['public_account'] = $account;
        if( $$accountType == 1 ) {
            sync_user_group();
        }
        showSystemMessage($lang['warning']['bind_success']);
    } else {
        showSystemMessage($lang['warning']['bind_fail']);
    }
}

if('edit' == $opera)
{
    $eid = intval(getPOST('eid'));
    $name = getPOST('name');
    $account = getPOST('account');
    $token = getPOST('token');
    $appID = getPOST('appID');
    $appsecret = getPOST('appsecret');
    $accountType = getPOST('accountType', 0);

    if($eid <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    } else {
        //只允许修改绑定到自己帐号下的公众号
        $checkPublicAccount = 'select id from `'.$db_prefix.'publicAccount` where `id`=%d and `adminUserAccount`=\'%s\'';
        $checkPublicAccount = sprintf($checkPublicAccount, $eid, $_SESSION['account']);

        $id = $db->fetchOne($checkPublicAccount);
        if(!$id)
        {
            showSystemMessage($lang['warning']['not_allow_modify']);
        }
    }

    if($name == '')
    {
        showSystemMessage($lang['warning']['publicAccount_empty']);
    } else {
        $name = $db->escape($name);
    }

    if($account == '')
    {
        showSystemMessage($lang['warning']['originalId_empty']);
    } else {
        $account = $db->escape($account);
    }

    if($token == '')
    {
        showSystemMessage($lang['warning']['token_empty']);
    } else {
        $token = $db->escape($token);
    }

    if($accountType == 1)
    {
        if($appID == '')
        {
            showSystemMessage($lang['warning']['appID_empty']);
        } else {
            $appID = $db->escape($appID);
        }

        if($appsecret == '')
        {
            showSystemMessage($lang['warning']['appsecret_empty']);
        } else {
            $appsecret = $db->escape($appsecret);
        }
    } else {
        $accountType = 0;
        $appID = '';
        $appsecret = '';
    }

    $updatePublicAccount = 'update `'.$db_prefix.'publicAccount` set `name`=\'%s\',`account`=\'%s\','.
                           '`token`=\'%s\',`appID`=\'%s\',`appsecret`=\'%s\',`accountType`=\'%s\' where `id`=%d';
    $updatePublicAccount = sprintf($updatePublicAccount, $name, $account, $token, $appID, $appsecret, $accountType, $eid);
    if($db->update($updatePublicAccount))
    {
        $_SESSION['public_account'] = $account;
        showSystemMessage($lang['warning']['update_success'], array(array('alt'=>'查看公众号信息', 'link'=>'main.php')));
    } else {
        showSystemMessage($lang['warning']['update_fail']);
    }
}

//检查是否已绑定公众号
$getPublicAccount = 'select * from `'.$db_prefix.'publicAccount` where `adminUserAccount`=\''.$_SESSION['account'].'\'';
$publicAccount = $db->fetchRow($getPublicAccount);

if(!$publicAccount)
{
    $smarty->assign('step', 'init');
    $act = 'init';
} else {
    //获取公众号概况信息
    $smarty->assign('step', 'info');
    $smarty->assign('account', $publicAccount);
}

if('edit' == $act)
{
    $smarty->assign('step', 'edit');
}

$smarty->display('main.phtml');
