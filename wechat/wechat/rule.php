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

if( 'add' == $opera ) {

    $name = getPOST('name');
    $rule = getPOST('rule');
    $matchMode = getPOST('matchMode');
    $orderView = getPOST('orderView');
    $isDefault = getPOST('isDefault');
    $kfId = getPOST('kfId');
    $responseId = getPOST('responseId');

    if( empty($name) ) {
        showSystemMessage('规则名不能为空');
    } else {
        $name = $db->escape($name);
    }

    if( empty($rule) ) {
        showSystemMessage('规则不能为空');
    } else {
        $rule = $db->escape($rule);
    }

    $matchMode = intval($matchMode);
    $matchMode = ( $matchMode == 0 ) ? 0 : 1;

    $orderView = intval($orderView);
    $orderView = ( $orderView <= 0 ) ? 50 : $orderView;

    $isDefault = intval($isDefault);
    $isDefault = ( $isDefault == 0 ) ? 0 : 1;

    $kfId = intval($kfId);
    $kfId  = ( $kfId < 0 ) ? 0 : $kfId;

    $responseId = intval($responseId);
    $responseId = ( $responseId < 0 ) ? 0 : $responseId;

    $addRule = 'insert into '.$db_prefix.'rules (id, name, rule, matchMode, orderView, isDefault, transfer_customer_service, kfId, responseId, publicAccount) ';
    $addRule .= ' values (null, \''.$name.'\',\''.$rule.'\',\''.$matchMode.'\',';
    $addRule .= $orderView.',';
    $addRule .= $isDefault.',';
    $addRule .= ( $kfId > 0 ) ? '1,' : '0,';
    $addRule .= $kfId.',';
    $addRule .= $responseId.',';
    $addRule .= '\''.$_SESSION['public_account'].'\'';
    $addRule .= ');';

    if( $db->insert($addRule) ) {
        showSystemMessage('添加规则成功');
    } else {
        showSystemMessage('添加规则失败');
    }

}

if( 'edit' == $opera ) {

    $id = intval(getPOST('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    $getRule = 'select * from '.$db_prefix.'rules where id = '.$id.' and publicAccount = \''.$_SESSION['public_account'].'\'';
    $rule = $db->fetchRow($getRule);

    if( !$rule ) {
        showSystemMessage('该规则不存在');
    }

    $name = getPOST('name');
    $rule = getPOST('rule');
    $matchMode = getPOST('matchMode');
    $orderView = getPOST('orderView');
    $isDefault = getPOST('isDefault');
    $kfId = getPOST('kfId');
    $responseId = getPOST('responseId');

    if( empty($name) ) {
        showSystemMessage('规则名不能为空');
    } else {
        $name = $db->escape($name);
    }

    if( empty($rule) ) {
        showSystemMessage('规则不能为空');
    } else {
        $rule = $db->escape($rule);
    }

    $matchMode = intval($matchMode);
    $matchMode = ( $matchMode == 0 ) ? 0 : 1;

    $orderView = intval($orderView);
    $orderView = ( $orderView <= 0 ) ? 50 : $orderView;

    $isDefault = intval($isDefault);
    $isDefault = ( $isDefault == 0 ) ? 0 : 1;

    $kfId = intval($kfId);
    $kfId  = ( $kfId < 0 ) ? 0 : $kfId;

    $responseId = intval($responseId);
    $responseId = ( $responseId < 0 ) ? 0 : $responseId;

    $updateRule = 'update '.$db_prefix.'rules set ';
    $updateRule .= 'name = \''.$name.'\',';
    $updateRule .= 'rule = \''.$rule.'\',';
    $updateRule .= 'matchMode = \''.$matchMode.'\',';
    $updateRule .= 'orderView = \''.$orderView.'\',';
    $updateRule .= 'isDefault = \''.$isDefault.'\',';
    $updateRule .= 'kfId = \''.$kfId.'\',';
    $updateRule .= 'responseId = \''.$responseId.'\'';
    $updateRule .= ($kfId == 0) ? ', transfer_customer_service = 0' : ', transfer_customer_service = 1';

    $updateRule .= ' where id = '.$id.' limit 1';
    if( $db->update($updateRule) ) {
        showSystemMessage('更新规则成功');
    } else {
        showSystemMessage('更新规则失败');
    }
}


if('list' == $act)
{
    $getRules = 'select `id`,`name`,`rule`,`matchMode`,`orderView`,`isDefault`,`transfer_customer_service` from `'.$db_prefix.'rules` where `publicAccount`=\''.$_SESSION['public_account'].'\' order by `id` ASC';
    $rules = $db->fetchAll($getRules);

    if($rules == '')
    {
        $rules = array();
    }


    $smarty->assign('rules', $rules);
}

if( 'add' == $act ) {
    $getResponse = 'select * from `'.$db_prefix.'response`';
    $responses = $db->fetchAll($getResponse);

    assign('responses', $responses);

    $getKfAccount = 'select `id`, `kf_account` from `'.$db_prefix.'kfAccount`';
    $getKfAccount .= ' where `publicAccount` = \''.$_SESSION['public_account'].'\'';
    $getKfAccount .=  ' order by `addTime` asc';
    $kfAccounts = $db->fetchAll($getKfAccount);

    assign('kfAccounts', $kfAccounts);
}

if( 'edit' == $act ) {

    $id = intval(getGET('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    $getRule = 'select * from '.$db_prefix.'rules where id = '.$id.' and publicAccount = \''.$_SESSION['public_account'].'\'';
    $rule = $db->fetchRow($getRule);
    if( $rule ) {
        assign('rule', $rule);
    } else {
        showSystemMessage('该规则不存在');
    }

    $getResponse = 'select * from `'.$db_prefix.'response`';
    $responses = $db->fetchAll($getResponse);

    assign('responses', $responses);

    $getKfAccount = 'select `id`, `kf_account` from `'.$db_prefix.'kfAccount`';
    $getKfAccount .= ' where `publicAccount` = \''.$_SESSION['public_account'].'\'';
    $getKfAccount .=  ' order by `addTime` asc';
    $kfAccounts = $db->fetchAll($getKfAccount);

    assign('kfAccounts', $kfAccounts);
}


if('delete' == $act)
{
    $id = intval(getGET('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }
    $getRule = 'select * from '.$db_prefix.'rules where id = '.$id.' and publicAccount = \''.$_SESSION['public_account'].'\'';
    $rule = $db->fetchRow($getRule);
    if( $rule ) {
        assign('rule', $rule);
    } else {
        showSystemMessage('该规则不存在');
    }

    $deleteRule = 'delete from `'.$db_prefix.'rules` where `id`='.$id.' and `publicAccount` = \''.$_SESSION['public_account'].'\'';

    if($db->delete($deleteRule))
    {
        showSystemMessage('删除规则成功');
    } else {
        showSystemMessage('删除规则失败');
    }
}

$smarty->assign('act', $act);
$smarty->display('rule.phtml');