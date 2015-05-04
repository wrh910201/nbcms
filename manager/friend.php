<?php
include 'library/init.inc.php';

checkAdminLogin();

$action = 'add|edit|delete|list';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));

if('' == $act)
{
    $act = 'list';
}

//添加友情链接
if('add' == $opera)
{
    if(!checkPurview(0x100000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $url = getPOST('url');
    $name = getPOST('name');
    $type = getPOST('type');
    $img = '';
    $isFollow = getPOST('isFollow');
    $orderView = getPOST('orderView');
    $isFollow = intval($isFollow);
    $orderView = intval($orderView);

    if('' == $url)
    {
        showSystemMessage('请填写友情链接的URL', array());
        exit;
    } else {
        $url = $db->escape(htmlspecialchars($url));
    }

    if('' == $name)
    {
        showSystemMessage('请填写友情链接名称', array());
        exit;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if('' == $type)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        if('image' == $type)
        {
            $img = upload($_FILES['img']);
            if($img['error'] == 0)
            {
                $img = $img['msg'];
            } else {
                showSystemMessage($img['msg'], array());
                exit;
            }
        }

        $addFriend  = 'insert into `'.DB_PREFIX.'friend` (`url`,`name`,`type`,`img`,`isFollow`,`orderView`) ';
        $addFriend .= 'values(\''.$url.'\',\''.$name.'\',\''.$type.'\',\''.$img.'\','.$isFollow.','.$orderView.')';
        if($db->insert($addFriend))
        {
            showSystemMessage('添加友情链接成功', array(array('alt'=>'查看友情链接列表', 'link'=>'friend.php')));
            exit;
        } else {
            showSystemMessage('系统繁忙，请稍后再试', array());
            exit;
        }
    }
}

//修改友情链接
if('edit' == $opera)
{
    if(!checkPurview(0x400000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $url = getPOST('url');
    $name = getPOST('name');
    $type = getPOST('type');
    $img = '';
    $isFollow = getPOST('isFollow');
    $orderView = getPOST('orderView');
    $isFollow = intval($isFollow);
    $orderView = intval($orderView);
    $id = getPOST('id');
    $id = intval($id);

    if(0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    if('' == $url)
    {
        showSystemMessage('请填写友情链接的URL', array());
        exit;
    } else {
        $url = $db->escape(htmlspecialchars($url));
    }

    if('' == $name)
    {
        showSystemMessage('请填写友情链接名称', array());
        exit;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if('' == $type)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        if('image' == $type)
        {
            if('' != $img)
            {
                $img = upload($_FILES['img']);
                if($img['error'] == 0)
                {
                    $img = $img['msg'];
                } else {
                    showSystemMessage($img['msg'], array());
                    exit;
                }
            }
        }

        $updateFriend  = 'update `'.DB_PREFIX.'friend` set `url`=\''.$url.'\',`name`=\''.$name.'\',';
        $updateFriend .= '`type`=\''.$type.'\',`isFollow`='.$isFollow.',`orderView`='.$orderView;
        if('' != $img)
        {
            $updateFriend .= ',`img`=\''.$img.'\'';
        }
        $updateFriend .= ' where `id`='.$id.' limit 1';
        if($db->insert($updateFriend))
        {
            showSystemMessage('修改友情链接成功', array(array('alt'=>'查看友情链接列表', 'link'=>'friend.php')));
            exit;
        } else {
            showSystemMessage('系统繁忙，请稍后再试', array());
            exit;
        }
    }
}

if('list' == $act)
{
    if(!checkPurview(0x200000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getFriends = 'select `name`,`id`,`url`,`isFollow`,`orderView`,`type` from `'.DB_PREFIX.'friend`';
    $friends = $db->fetchAll($getFriends);

    assign('friends', $friends);
}

if('add' == $act)
{
    if(!checkPurview(0x100000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

}

if('edit' == $act)
{
    if(!checkPurview(0x400000000, $_SESSION['purview']))
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

    $getFriend  = 'select `id`,`url`,`name`,`type`,`img`,`isFollow`,`orderView` from `'.DB_PREFIX.'friend` ';
    $getFriend .= 'where `id`='.$id.' limit 1';

    if($friend = $db->fetchRow($getFriend))
    {
        assign('friend', $friend);
    } else {
        showSystemMessage('该友情链接不能存在或已被删除', array());
        exit;
    }
}

if('delete' == $act)
{
    if(!checkPurview(0x800000000, $_SESSION['purview']))
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

    $getFriend = 'select `img` from `'.DB_PREFIX.'friend` where `id`='.$id;
    $friend = $db->fetchRow($getFriend);

    if($friend)
    {
        if('' != $friend['img'])
        {
            unlink('../'.$friend['img']);
        }

        $deleteFriend = 'delete from `'.DB_PREFIX.'friend` where `id`='.$id.' limit 1';
        if(!$db->delete($deleteFriend))
        {
            showSystemMessage('系统繁忙，请稍后再试', array());
            exit;
        }
    }

    showSystemMessage('删除友情链接成功', array(array('alt'=>'查看友情链接列表', 'link'=>'friend.php')));
    exit;
}

assign('pageTitle', '友情链接管理');
assign('act', $act);
$smarty->display('friend.phtml');
