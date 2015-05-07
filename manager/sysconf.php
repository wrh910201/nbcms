<?php
include 'library/init.inc.php';

back_base_init();
assign('subTitle', '系统参数设置');

$action = 'edit|add|list';
$operation = 'edit|add';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));

if('' === $act)
{
    $act = 'list';
}

//新增系统参数
if('add' == $opera)
{
    if(!checkPurview('pur_sysconf_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

}

//修改系统参数
if('edit' == $opera)
{
    if(!checkPurview('pur_sysconf_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $key = getPOST('key');
    $value = getPOST('value');
    $type = getPOST('type');

    if('' == $key)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    switch($type)
    {
    case 'radio':
        $value = intval($value);
    case 'text':
    case 'textarea':
        $value = $db->escape(htmlspecialchars($value));
        $key = $db->escape($key);
        $updateConf = 'update `'.DB_PREFIX.'sysconf` set `value`=\''.$value.'\' where `key`=\''.$key.'\' limit 1';
        if($db->update($updateConf))
        {
            $links = array(
                array('alt'=>'查看系统参数', 'link'=>'sysconf.php')
            );
            showSystemMessage('修改系统参数成功', $links);
            exit;
        } else {
            showSystemMessage('系统繁忙，请稍后再试', array());
            exit;
        }
        break;
    case 'file':
        $response = upload($_FILES['value'], 'image');
        if($response['error'])
        {
            showSystemMessage($response['msg'], array());
            exit;
        } else {
            $key = $db->escape($key);
            $value = $response['msg'];
            $updateConf = 'update `'.DB_PREFIX.'sysconf` set `value`=\''.$value.'\' where `key`=\''.$key.'\' limit 1';
            if($db->update($updateConf))
            {
                $links = array(
                    array('alt'=>'查看系统参数', 'link'=>'sysconf.php')
                );
                showSystemMessage('修改系统参数成功', $links);
                exit;
            } else {
                showSystemMessage('系统繁忙，请稍后再试', array());
                exit;
            }    
        }
        break;
    default:
    }
}

if('list' == $act)
{
    if(!checkPurview('pur_sysconf_list', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }


    $getSysconf = 'select `key`,`name`,`type`,`value` from `'.DB_PREFIX.'sysconf`';
    assign('sysconf', $db->fetchAll($getSysconf));
}

if('edit' == $act)
{
    if(!checkPurview('pur_sysconf_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }


    $key = getGET('key');
    if('' == $key)
    {
        showSystemMessage('参数错误', array());
    } else {
        $getSysconf  = 'select `key`,`name`,`type`,`value` from `'.DB_PREFIX.'sysconf`';
        $getSysconf .= ' where `key`=\''.$key.'\'';

        assign('conf', $db->fetchRow($getSysconf));
    }
}

assign('act', $act);

$smarty->display('sysconf.phtml');
