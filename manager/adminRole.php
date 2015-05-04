<?php
include 'library/init.inc.php';

checkAdminLogin();

$action = 'list|add|edit|delete';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));
if('' == $act)
{
    $act = 'list';
}

//添加管理员角色
if('add' == $opera)
{
    if(!checkPurview(0x10000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $name = getPOST('name');
    $purviews = getPOST('purviews');

    if('' == $name)
    {
        showSystemMessage('请填写角色名', array());
        exit;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    $purview_value = 0;
    foreach($purviews as $pur)
    {
        $bit = array_search($pur, $purview);
        $temp = 0x1;
        $temp = $temp<<$bit;
        $purview_value |= $temp;
    }

    $addAdminRole = 'insert into `'.DB_PREFIX.'adminRole` (`name`,`purview`) values (\''.$name.'\','.$purview_value.')';
    if($db->insert($addAdminRole))
    {
        showSystemMessage('新增管理员角色成功', array(array('alt'=>'查看管理员角色列表','link'=>'adminRole.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

//编辑管理员角色
if('edit' == $opera)
{
    if(!checkPurview(0x40000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getPOST('id');
    $id = intval($id);
    $name = getPOST('name');
    $purviews = getPOST('purviews');

    if(0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    if('' == $name)
    {
        showSystemMessage('请填写角色名', array());
        exit;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    $purview_value = 0;
    foreach($purviews as $pur)
    {
        $bit = array_search($pur, $purview);
        $temp = 0x1;
        $temp = $temp<<$bit;
        $purview_value |= $temp;
    }

    $updateAdminRole  = 'update `'.DB_PREFIX.'adminRole` set `name`=\''.$name.'\',`purview`='.$purview_value;
    $updateAdminRole .= ' where `id`='.$id.' limit 1';
    if($db->update($updateAdminRole))
    {
        showSystemMessage('修改管理员角色成功', array(array('alt'=>'查看管理员角色列表','link'=>'adminRole.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }

}

if('list' == $act)
{
    if(!checkPurview(0x20000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getAdminRole = 'select `name`,`id` from `'.DB_PREFIX.'adminRole`';
    $adminRoles = $db->fetchAll($getAdminRole);

    assign('adminRoles', $adminRoles);
}

if('add' == $act)
{
    if(!checkPurview(0x10000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

}

if('edit' == $act)
{
    if(!checkPurview(0x40000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');
    $id = intval($id);

    if(0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $getAdminRole = 'select `id`,`name`,`purview` from `'.DB_PREFIX.'adminRole` where `id`='.$id;
    $adminRole = $db->fetchRow($getAdminRole);

    assign('adminRole', $adminRole);

    $purviewC = array();
    foreach($purview as $key=>$pur)
    {
        $temp = 0x01;
        $temp = $temp<<$key;
        if($temp & $adminRole['purview'])
        {
            $purviewC[$pur] = 1;
        }
    }
    assign('purviewC', $purviewC);
}

if('delete' == $act)
{
    if(!checkPurview(0x80000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');
    $id = intval($id);

    if(0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $checkAdminRole = 'select `account` from `'.DB_PREFIX.'admin` where `roleId`='.$id;
    if($db->fetchAll($checkAdminRole))
    {
        showSystemMessage('该角色还有管理员在使用，不能删除', array());
        exit;
    } else {
        $deleteAdminRole = 'delete from `'.DB_PREFIX.'adminRole` where `id`='.$id.' limit 1';
        if($db->delete($deleteAdminRole))
        {
            showSystemMessage('删除管理员角色成功', array(array('alt'=>'查看管理员角色', 'link'=>'adminRole.php')));
            exit;
        } else {
            showSystemMessage('系统繁忙，请稍后再试', array());
            exit;
        }
    }
}

assign('purview', $purview);
assign('purviewL', $L_purview);
assign('act', $act);
assign('pageTitle', '管理员角色管理');
$smarty->display('adminRole.phtml');
