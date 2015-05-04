<?php
include 'library/init.inc.php';

checkAdminLogin();

$operation = 'add|edit';
$action = 'list|add|edit|delete';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));
if('' == $act)
{
    $act = 'list';
}

//添加产品分类
if('add' == $opera)
{
    if(!checkPurview(0x1000000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $name = getPOST('name');
    $parentId = getPOST('parentId');
    $keywords = getPOST('keywords');
    $description = getPOST('description');
    $locked = 0;
    $path = '';

    if('' == $name)
    {
        showSystemMessage('分类名称不能为空', array());
        exit;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if('' == $keywords)
    {
        showSystemMessage('出于SEO考虑，请务必填写关键词', array());
        exit;
    } else {
        $keywords = $db->escape(htmlspecialchars($keywords));
    }

    if('' == $description)
    {
        showSystemMessage('出于SEO考虑，请务必填写简要介绍', array());
        exit;
    } else {
        $description = $db->escape(htmlspecialchars($description));
    }

    $parentId = intval($parentId);
    if(0 > $parentId)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $addcategory  = 'insert into `'.DB_PREFIX.'category` (`name`,`parentId`,`keywords`,`description`) ';
    $addcategory .= 'values(\''.$name.'\','.$parentId.',\''.$keywords.'\',\''.$description.'\')';

    if($db->insert($addcategory))
    {
        $id = $db->getLastId();
        $path = $id;
        if(0 < $parentId)
        {
            $getParentPath = 'select `path` from `'.DB_PREFIX.'category` where `id`='.$parentId;
            if($parentCat = $db->fetchRow($getParentPath))
            {
                $path = $parentCat['path'].','.$path;
            }
        }

        $updatecategory = 'update `'.DB_PREFIX.'category` set `path`=\''.$path.'\' where `id`='.$id.' limit 1';
        if($db->update($updatecategory))
        {
            $links = array(
                array('alt'=>'查看产品分类', 'link'=>'category.php?act=list'),
                array('alt'=>'继续添加分类', 'link'=>'category.php?act=add')
            );
            showSystemMessage('添加产品分类成功', $links);
            exit;
        } else {
            showSystemMessage('更新产品信息失败', array());
            exit;
        }
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

//编辑产品分类
if('edit' == $opera)
{
    if(!checkPurview(0x4000000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $name = getPOST('name');
    $parentId = getPOST('parentId');
    $keywords = getPOST('keywords');
    $description = getPOST('description');
    $id = getPOST('id');
    $path = '';

    $id = intval($id);
    if(0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    if('' == $name)
    {
        showSystemMessage('导航栏名称不能为空', array());
        exit;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if('' == $keywords)
    {
        showSystemMessage('出于SEO考虑，请务必填写关键词', array());
        exit;
    } else {
        $keywords = $db->escape(htmlspecialchars($keywords));
    }

    if('' == $description)
    {
        showSystemMessage('出于SEO考虑，请务必填写简要介绍', array());
        exit;
    } else {
        $description = $db->escape(htmlspecialchars($description));
    }

    $parentId = intval($parentId);
    if(0 > $parentId)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $getParentCat = 'select `path` from `'.DB_PREFIX.'category` where `id`='.$parentId.' limit 1';
        if($parentId > 0 && $parent = $db->fetchRow($getParentCat))
        {
            $path = $parent['path'].','.$id;
        } else {
            $path = $id;
        }
    }

    $updatecategory  = 'update `'.DB_PREFIX.'category` set `name`=\''.$name.'\',`parentId`='.$parentId.',';
    $updatecategory .= '`keywords`=\''.$keywords.'\',`description`=\''.$description.'\',`path`=\''.$path.'\'';
    $updatecategory .= ' where `id`='.$id.' limit 1';

    if($db->update($updatecategory))
    {
        $links = array(
            array('alt'=>'查看产品分类', 'link'=>'category.php?act=list')
        );
        showSystemMessage('更新产品分类信息成功', $links);
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

if('list' == $act)
{
    if(!checkPurview(0x2000000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getcategorys = 'select `name`,`id`,`path` from `'.DB_PREFIX.'category` order by `path` ASC';
    $categorys = $db->fetchAll($getcategorys);

    foreach($categorys as $key=>$cat)
    {
        $count = count(explode(',', $cat['path']));
        if($count > 1)
        {
            $temp = '|--'.$cat['name'];
            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $cat['name'] = $temp;
            $categorys[$key] = $cat;
        }
    }

    assign('categorys', $categorys);
}

if('add' == $act)
{
    if(!checkPurview(0x1000000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getcategorys = 'select `name`,`id`,`path` from `'.DB_PREFIX.'category` order by `path` ASC';
    $categorys = $db->fetchAll($getcategorys);

    foreach($categorys as $key=>$cat)
    {
        $count = count(explode(',', $cat['path']));
        if($count > 1)
        {
            $temp = '|--'.$cat['name'];
            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $cat['name'] = $temp;
            $categorys[$key] = $cat;
        }
    }

    assign('categorys', $categorys);
}

if('edit' == $act)
{
    if(!checkPurview(0x4000000000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');
    $id = intval($id);
    if('' == $id || 0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $getcategorys = 'select `name`,`id`,`path` from `'.DB_PREFIX.'category` where `id`<>'.$id.' order by `path` ASC';
    $categorys = $db->fetchAll($getcategorys);

    foreach($categorys as $key=>$cat)
    {
        $count = count(explode(',', $cat['path']));
        if($count > 1)
        {
            $temp = '|--'.$cat['name'];
            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $cat['name'] = $temp;
            $categorys[$key] = $cat;
        }
    }

    assign('categorys', $categorys);

    $getcategory  = 'select `id`,`name`,`parentId`,`keywords`,`description` from `'.DB_PREFIX.'category`';
    $getcategory .= ' where `id`='.$id.' limit 1';
    $category = $db->fetchRow($getcategory);

    assign('category', $category);
}

if('delete' == $act)
{
    if(!checkPurview(0x8000000000, $_SESSION['purview']))
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

    $checkcategory = 'select `id` from `'.DB_PREFIX.'category` where `parentId`='.$id;
    $category = $db->fetchAll($checkcategory);
    if($category)
    {
        showSystemMessage('当前分类下有子分类，请先删除或移走子分类', array());
        exit;
    }

    $checkcategory = 'select `id` from `'.DB_PREFIX.'product` where `categoryId`='.$id;
    $category = $db->fetchRow($checkcategory);
    if(!$category)
    {
        showSystemMessage('当前分类下有产品，请先删除或移走产品', array());
        exit;
    }

    $deletecategory = 'delete from `'.DB_PREFIX.'category` where `id`='.$id.' limit 1';
    if($db->delete($deletecategory))
    {
        showSystemMessage('删除分类成功', array());
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

assign('act', $act);
assign('pageTitle', '产品分类管理');
$smarty->display('category.phtml');
