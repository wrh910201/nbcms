<?php
include 'library/init.inc.php';

back_base_init();

$operation = 'add|edit';
$action = 'list|add|edit|delete';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));
if('' == $act)
{
    $act = 'list';
}

//添加导航栏
if('add' == $opera)
{
    if(!checkPurview('pur_articleCat_add', $_SESSION['purview']))
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
    $img = '';
    $response = upload_with_choice($_FILES['img'], 'image');

    if($response['error']) {
        showSystemMessage($response['msg'], array());
        exit;
    } else {
        $img = $response['msg'];
    }

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

    $addArticleCat  = 'insert into `'.DB_PREFIX.'articleCat` (`name`,`parentId`,`keywords`,`description`,`locked`, `img`) ';
    $addArticleCat .= 'values(\''.$name.'\','.$parentId.',\''.$keywords.'\',\''.$description.'\', 0, \''.$img.'\')';

    if($db->insert($addArticleCat))
    {
        $id = $db->getLastId();
        $path = $id;
        if(0 < $parentId)
        {
            $getParentPath = 'select `path` from `'.DB_PREFIX.'articleCat` where `id`='.$parentId;
            if($parentCat = $db->fetchRow($getParentPath))
            {
                $path = $parentCat['path'].','.$path;
            }
        }

        $updateArticleCat = 'update `'.DB_PREFIX.'articleCat` set `path`=\''.$path.'\' where `id`='.$id.' limit 1';
        if($db->update($updateArticleCat))
        {
            $links = array(
                array('alt'=>'查看资讯分类', 'link'=>'articleCat.php?act=list'),
                array('alt'=>'继续添加分类', 'link'=>'articleCat.php?act=add')
            );
            showSystemMessage('添加资讯分类成功', $links);
            exit;
        } else {
            showSystemMessage('更新资讯信息失败', array());
            exit;
        }
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

//编辑导航栏
if('edit' == $opera)
{
    if(!checkPurview('pur_articleCat_edit', $_SESSION['purview']))
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
    $img = '';
    $response = upload_with_choice($_FILES['img'], 'image');

    if($response['error']) {
        showSystemMessage($response['msg'], array());
        exit;
    } else {
        $img = $response['msg'];
    }

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
        $getParentCat = 'select `path` from `'.DB_PREFIX.'articleCat` where `id`='.$parentId.' limit 1';
        if($parentId > 0 && $parent = $db->fetchRow($getParentCat))
        {
            $path = $parent['path'].','.$id;
        } else {
            $path = $id;
        }
    }

    $updateArticleCat  = 'update `'.DB_PREFIX.'articleCat` set `name`=\''.$name.'\',`parentId`='.$parentId.',';
    $updateArticleCat .= '`keywords`=\''.$keywords.'\',`description`=\''.$description.'\',`path`=\''.$path.'\'';
    $updateArticleCat .= ($img != '') ? (',`img`=\''.$img.'\'') : '';
    $updateArticleCat .= ' where `id`='.$id.' limit 1';

    if($db->update($updateArticleCat))
    {
        $links = array(
            array('alt'=>'查看资讯分类', 'link'=>'articleCat.php?act=list')
        );
        showSystemMessage('更新资讯分类信息成功', $links);
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

if('list' == $act)
{
    if(!checkPurview('pur_articleCat_list', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getArticleCats = 'select `name`,`id`,`path`, `keywords`, `description` from `'.DB_PREFIX.'articleCat` order by `path` ASC';
    $articleCats = $db->fetchAll($getArticleCats);

    foreach($articleCats as $key=>$cat)
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
            $articleCats[$key] = $cat;
        }
    }

    assign('articleCats', $articleCats);
}

if('add' == $act)
{
    if(!checkPurview('pur_articleCat_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getArticleCats = 'select `name`,`id`,`path` from `'.DB_PREFIX.'articleCat` order by `path` ASC';
    $articleCats = $db->fetchAll($getArticleCats);

    foreach($articleCats as $key=>$cat)
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
            $articleCats[$key] = $cat;
        }
    }

    assign('articleCats', $articleCats);
}

if('edit' == $act)
{
    if(!checkPurview('pur_articleCat_edit', $_SESSION['purview']))
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

    $getArticleCats = 'select `name`,`id`,`path` from `'.DB_PREFIX.'articleCat` where `id`<>'.$id.' order by `path` ASC';
    $articleCats = $db->fetchAll($getArticleCats);

    foreach($articleCats as $key=>$cat)
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
            $articleCats[$key] = $cat;
        }
    }

    assign('articleCats', $articleCats);

    $getArticleCat  = 'select `id`,`name`,`parentId`,`keywords`,`description`, `img` from `'.DB_PREFIX.'articleCat`';
    $getArticleCat .= ' where `id`='.$id.' limit 1';
    $articleCat = $db->fetchRow($getArticleCat);

    assign('articleCat', $articleCat);
}

if('delete' == $act)
{
    if(!checkPurview('pur_articleCat_delete', $_SESSION['purview']))
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

    $checkArticleCat = 'select `id` from `'.DB_PREFIX.'articleCat` where `parentId`='.$id;
    $articleCat = $db->fetchAll($checkArticleCat);
    if($articleCat)
    {
        showSystemMessage('当前分类下有子分类，请先删除或移走子分类', array());
        exit;
    }

    $checkArticleCat = 'select `id` from `'.DB_PREFIX.'articleCat` where `id`='.$id.' and `locked`=0';
    $articleCat = $db->fetchRow($checkArticleCat);
    if(!$articleCat)
    {
        showSystemMessage('系统保留分类，不允许删除', array());
        exit;
    }

    $deleteArticleCat = 'delete from `'.DB_PREFIX.'articleCat` where `id`='.$id.' limit 1';
    if($db->delete($deleteArticleCat))
    {
        showSystemMessage('删除分类成功', array());
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

assign('act', $act);
assign('subTitle', '资讯分类管理');
$smarty->display('articleCat.phtml');
