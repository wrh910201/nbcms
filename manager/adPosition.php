<?php
include 'library/init.inc.php';

back_base_init();

$action = 'add|edit|list|delete';
$operation = 'add|edit';

$act = getGET('act');
$opera = getPOST('opera');
$act = checkAction($action, $act);
$opera = checkAction($operation, $opera);

if('' == $act)
{
    $act = 'list';
}

//新增广告位置
if('add' == $opera)
{
    if(!checkPurview('pur_adPosition_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $name = getPOST('name');
    $targetTemplate = getPOST('targetTemplate');
    $number = getPOST('number');
    $type = getPOST('type');
    $code = getPOST('code');
    $width = getPOST('width');
    $height = getPOST('height');

    if('' == $name)
    {
        showSystemMessage('广告位置名称不能为空', array());
        exit;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if('' == $targetTemplate)
    {
        showSystemMessage('请选择对应的模版', array());
        exit;
    } else {    
        $targetTemplate = $db->escape($targetTemplate);
    }

    if('' == $type)
    {
        $type = 'js';
    } else {
        $type = $db->escape($type);
    }

    $number = intval($number);

    if('' == $code)
    {
        $code = '';
    } else {
        $code = $db->escape($code);
    }

    $width = intval($width);
    $height = intval($height);

    $addAdPosition  = 'insert into `'.DB_PREFIX.'adPosition` (`name`,`targetTemplate`,`number`,`type`,`code`,`width`,';
    $addAdPosition .= '`height`) values (\''.$name.'\',\''.$targetTemplate.'\','.$number.',\''.$type.'\',\''.$code.'\',';
    $addAdPosition .= $width.','.$height.')';

    if($db->insert($addAdPosition))
    {
        $links = array(
            array('alt'=>'查看广告位置', 'link'=>'adPosition.php?act=list'),
            array('alt'=>'继续添加广告位置', 'link'=>'adPosition.php?act=add')
        );
        showSystemMessage('添加广告位置成功', $links);
        exit;
    } else {
        showSystemMessage($db->errno.':系统繁忙，请稍后再试', array());
        exit;
    }
}

//编辑广告位置
if('edit' == $opera)
{
    if(!checkPurview('pur_adPosition_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $name = getPOST('name');
    $targetTemplate = getPOST('targetTemplate');
    $number = getPOST('number');
    $type = getPOST('type');
    $code = getPOST('code');
    $width = getPOST('width');
    $height = getPOST('height');
    $id = getPOST('id');
    $id = intval($id);

    if(0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    if('' == $name)
    {
        showSystemMessage('广告位置名称不能为空', array());
        exit;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if('' == $targetTemplate)
    {
        showSystemMessage('请选择对应的模版', array());
        exit;
    } else {
        $targetTemplate = $db->escape($targetTemplate);
    }

    if('' == $type)
    {
        $type = 'js';
    } else {
        $type = $db->escape($type);
    }

    $number = intval($number);

    if('' == $code)
    {
        $code = '';
    } else {
        $code = $db->escape($code);
    }

    $width = intval($width);
    $height = intval($height);

    $updateAdPosition  = 'update `'.DB_PREFIX.'adPosition` set `name`=\''.$name.'\',`targetTemplate`=\''.$targetTemplate;
    $updateAdPosition .= '\',`number`='.$number.',`type`=\''.$type.'\',`code`=\''.$code.'\',`width`='.$width.',';
    $updateAdPosition .= '`height`='.$height.' where `id`='.$id.' limit 1';

    if($db->update($updateAdPosition))
    {
        $links = array(
            array('alt'=>'查看广告位置', 'link'=>'adPosition.php?act=list')
        );
        showSystemMessage('更新广告位置成功', $links);
        exit;
    } else {
        showSystemMessage($db->errno.':系统繁忙，请稍后再试', array());
        exit;
    }
}

if('list' == $act)
{
    if(!checkPurview('pur_adPosition_list', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getAdPositions = 'select `id`,`name`,`width`,`height`,`targetTemplate` from `'.DB_PREFIX.'adPosition`';
    $adPositions = $db->fetchAll($getAdPositions);
    assign('adPositions', $adPositions);
}

if('add' == $act)
{
    if(!checkPurview('pur_adPosition_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $templateDir = dir(ROOT_PATH.'themes/');
    $templates = array();
    while($file = $templateDir->read())
    {
        if(preg_match('/[a-zA-Z0-9_\.]+\.phtml$/', $file))
        {
            $templates[] = $file;
        }
    }

    assign('templates', $templates);
}

if('edit' == $act)
{
    if(!checkPurview('pur_adPosition_edit', $_SESSION['purview']))
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

    $templateDir = dir(ROOT_PATH.'themes/');
    $templates = array();
    while($file = $templateDir->read())
    {
        if(preg_match('/[a-zA-Z0-9_\.]+\.phtml$/', $file))
        {
            $templates[] = $file;
        }
    }

    assign('templates', $templates);

    $getAdPosition  = 'select `targetTemplate`,`id`,`name`,`number`,`type`,`code`,`width`,`height` from `';
    $getAdPosition .= DB_PREFIX.'adPosition` where `id`='.$id.' limit 1';

    $adPosition = $db->fetchRow($getAdPosition);
    if($adPosition)
    {
        assign('adPosition', $adPosition);
    } else {
        showSystemMessage('该广告位置不存在或已删除', array());
        exit;
    }
}

if('delete' == $act)
{
    if(!checkPurview('pur_adPosition_delete', $_SESSION['purview']))
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

    $checkAdPosition = 'select `id` from `'.DB_PREFIX.'ad` where `adPositionId`='.$id;
    $ads = $db->fetchRow($checkAdPosition);

    if($ads)
    {
        showSystemMessage('当前广告位置下放有广告，请先移走或删除广告', array());
        exit;
    } else {
        $deleteAdPosition = 'delete from `'.DB_PREFIX.'adPosition` where `id`='.$id.' limit 1';
        if($db->delete($deleteAdPosition))
        {
            showSystemMessage('删除广告位置成功', array());
            exit;
        } else {
            showSystemMessage('系统繁忙，请稍后再试', array());
            exit;
        }
    }
}


assign('act', $act);
assign('subTitle', '广告位置管理');
$smarty->display('adPosition.phtml');
