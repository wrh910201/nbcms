<?php
include 'library/init.inc.php';

checkAdminLogin();

$action = 'list|add|edit|delete';
$operation = 'add|edit|ajax';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));

if('' === $act)
{
    $act = 'list';
}

//Ajax校验程序
if('ajax' == $opera)
{
}
//新增导航条
if('add' == $opera)
{
    if(!checkPurview(0x10, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $name = getPOST('name');
    $parentId = getPOST('parentId');
    $url = getPOST('url');
    $orderView = getPOST('orderView');
    $position = getPOST('position');
    $isOpenNew = getPOST('isOpenNew');
    $isShow = getPOST('isShow');
    $path = '';
    //新增导航条
    $error = '';
    if('' == $name)
    {
        $error .= '-导航栏名称不能为空'."\n";
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if(0 > $parentId)
    {
        $error .= '-父级导航栏参数错误'."\n";
    } else {
        $parentId = intval($parentId);
    }

    if('' == $url)
    {
        $error .= '-URL不能为空'."\n";
    } else {
        $url = $db->escape(htmlspecialchars($url));
    }

    if(false === strpos('middle|top|bottom', $position))
    {
        $position = 'middle';
    } else {
        $position = $db->escape($position);
    }

    $orderView = intval($orderView);
    $isOpenNew = intval($isOpenNew);
    $isShow = intval($isShow);

    if('' != $error)
    {
        showSystemMessage($error, array());
        exit;
    }

    $addNav  = 'insert into `'.DB_PREFIX.'nav` (`name`,`url`,`isShow`,`orderView`,`position`,`parentId`,`isOpenNew`,`path`) ';
    $addNav .= ' values (\''.$name.'\',\''.$url.'\','.$isShow.','.$orderView.',\''.$position.'\','.$parentId.','.$isOpenNew.',\' \')';
    if(!$db->insert($addNav))
    {
        showSystemMessage($db->errno.':新增导航栏失败，请联系管理员', array(), 5);
        exit;
    }

    //更新导航条path
    $path = $db->getLastId();
    $id = $path;

    if($parentId > 0)
    {
        $getParentPath = 'select `path` from `'.DB_PREFIX.'nav` where `id`='.$parentId.' limit 1';
        $parentNav = $db->fetchRow($getParentPath);

        if($parentNav)
        {
            $path = $parentNav['path'].','.$path;
        }
    }

    $updateNav = 'update `'.DB_PREFIX.'nav` set `path`=\''.$path.'\' where `id`='.$id.' limit 1';
    if($db->update($updateNav))
    {
        $links = array(
            array('alt'=>'查看导航条', 'link'=>'nav.php?act=list'),
            array('alt'=>'继续新增导航条', 'link'=>'nav.php?act=add')
        );
        showSystemMessage('新增导航条成功', $links);
        exit;
    } else {
        showSystemMessage($db->errno.':系统繁忙，请稍后再试', array());
        exit;
    }
}
//编辑导航条
if('edit' == $opera)
{
    if(!checkPurview(0x40, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $name = getPOST('name');
    $parentId = getPOST('parentId');
    $url = getPOST('url');
    $orderView = getPOST('orderView');
    $position = getPOST('position');
    $isOpenNew = getPOST('isOpenNew');
    $isShow = getPOST('isShow');
    $path = '';
    $id = getPOST('id');
    //新增导航条
    $error = '';
    if(0 >= $id)
    {
        $error .= '-参数错误'."\n";
    } else {
        $id = intval($id);
    }

    if('' == $name)
    {
        $error .= '-导航栏名称不能为空'."\n";
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if(0 > $parentId)
    {
        $error .= '-父级导航栏参数错误'."\n";
    } else {
        $parentId = intval($parentId);
    }

    if('' == $url)
    {
        $error .= '-URL不能为空'."\n";
    } else {
        $url = $db->escape(htmlspecialchars($url));
    }

    if(false === strpos('middle|top|bottom', $position))
    {
        $position = 'middle';
    } else {
        $position = $db->escape($position);
    }

    $orderView = intval($orderView);
    $isOpenNew = intval($isOpenNew);
    $isShow = intval($isShow);

    if('' != $error)
    {
        showSystemMessage($error, array());
        exit;
    }

    if($parentId == 0)
    {
        $path = $id;
    } else {
        $getParentPath = 'select `path` from `'.DB_PREFIX.'nav` where `id`='.$parentId.' limit 1';
        $parentNav = $db->fetchRow($getParentPath);
        if($parentNav)
        {
            $path = $parentNav['path'].','.$id;
        } else {
            $path = $id;
        }
    }

    $updateNav  = 'update `'.DB_PREFIX.'nav` set `name`=\''.$name.'\',`url`=\''.$url.'\',`orderView`='.$orderView.',';
    $updateNav .= '`position`=\''.$position.'\',`parentId`='.$parentId.',`isOpenNew`='.$isOpenNew.',';
    $updateNav .= '`isShow`='.$isShow.',`path`=\''.$path.'\' where `id`='.$id.' limit 1';

    if($db->update($updateNav))
    {
        $links = array(
            array('alt'=>'查看导航条', 'link'=>'nav.php')
        );
        showSystemMessage('更新导航栏成功', $links);
        exit;
    } else {
        showSystemMessage($db->errno.':更新导航栏失败', array());
        exit;
    }
}

if('list' == $act)
{
    if(!checkPurview(0x20, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getNavs  = 'select `url`,`path`,`id`,`name`,`isShow`,`orderView`,`position`,`isOpenNew` from `'.DB_PREFIX.'nav`';
    $getNavs .= ' order by `position` DESC, `orderView` ASC, `path` ASC';
    $navs = $db->fetchAll($getNavs);

    foreach($navs as $key=>$nav)
    {
        $count = count(explode(',', $nav['path']));
        
        if($count > 1)
        {
            $temp = '|--';

            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $nav['name'] = $temp.$nav['name'];
        }

        $navs[$key] = $nav;
    }

    assign('navs', $navs);
}

if('add' == $act)
{
    if(!checkPurview(0x10, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getNavs  = 'select `position`,`id`,`name`,`path` from `'.DB_PREFIX.'nav` ';
    $getNavs .= 'order by `position` DESC, `path` ASC, `orderView` ASC';

    $navs = $db->fetchAll($getNavs);

    foreach($navs as $key=>$nav)
    {
        $count = count(explode(',', $nav['path']));
        
        if($count > 1)
        {
            $temp = '|--';

            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $nav['name'] = $temp.$nav['name'];
        }

        $navs[$key] = $nav;
    }

    assign('navs', $navs);
}

if('edit' == $act)
{
    if(!checkPurview(0x40, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');
    if('' == $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }
    $getNavs  = 'select `position`,`id`,`name`,`path` from `'.DB_PREFIX.'nav` ';
    $getNavs .= ' where `id`<>'.$id;
    $getNavs .= ' order by `position` DESC, `path` ASC, `orderView` ASC';

    $navs = $db->fetchAll($getNavs);

    foreach($navs as $key=>$nav)
    {
        $count = count(explode(',', $nav['path']));
        
        if($count > 1)
        {
            $temp = '|--';

            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $nav['name'] = $temp.$nav['name'];
        }

        $navs[$key] = $nav;
    }

    assign('navs', $navs);

    $getNav  = 'select `id`,`position`,`name`,`orderView`,`isShow`,`isOpenNew`,`parentId`,`url` from `'.DB_PREFIX.'nav`';
    $getNav .= ' where `id`='.$id;
    $nav = $db->fetchRow($getNav);

    if(!$nav)
    {
        showSystemMessage('参数错误', array());
    }

    assign('nav', $nav);
}

if('delete' == $act)
{
    if(!checkPurview(0x80, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');
    $id = $db->escape($id);
    if('' == $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $checkNav = 'select `id` from `'.DB_PREFIX.'nav` where `parentId`='.$id;
    if($db->fetchRow($checkNav))
    {
        showSystemMessage('当前导航条还有子栏目，请先删除子栏目', array());
        exit;
    } else {
        $deleteNav = 'delete from `'.DB_PREFIX.'nav` where `id`='.$id.' limit 1';
        if($db->delete($deleteNav))
        {
            showSystemMessage('删除成功', array());
            exit;
        } else {
            showSystemMessage('删除导航条失败，请稍后再试', array());
            exit;
        }
    }
}

assign('act', $act);
assign('pageTitle', '导航条管理');
$smarty->display('nav.phtml');
