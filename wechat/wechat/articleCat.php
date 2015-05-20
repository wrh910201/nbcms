<?php
//文章分类
include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}

$action = 'list|add|edit|delete';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));

if('add' == $opera)
{
    $name = getPOST('name');
    $parentId = intval(getPOST('parentId'));
    $path = '';
    $description = getPOST('description');
    $keywords = getPOST('keywords');

    if($name == '')
    {
        showSystemMessage($lang['warning']['article_cat_empty']);
    } else {
        $name = addslashes($name);
    }

    if($parentId <= 0)
    {
        $parentId = 0;
    }

    if($description == '')
    {
        showSystemMessage($lang['warning']['description_empty']);
    } else {
        $description = $db->escape($description);
    }

    if($keywords == '')
    {
        showSystemMessage($lang['warning']['keywords_empty']);
    } else {
        $keywords = str_replace('，', ',', $keywords);
        $keywords = $db->escape($keywords);
    }

    $addCat = 'insert into `'.$db_prefix.'articleCat` (`name`,`parentId`,`description`,`keywords`) values ('.
              '\'%s\',%d,\'%s\',\'%s\')';
    $addCat = sprintf($addCat, $name, $parentId, $description, $keywords);

    if($db->insert($addCat))
    {
        $id = $db->getLastId();

        if($parentId > 0)
        {
            $getPath = 'select `path` from `'.$db_prefix.'articleCat` where `id`='.$parentId;
            $path = $db->fetchOne($getPath);
            $path .= ',';
        }

        $path .= $id;

        $updateCat = 'update `'.$db_prefix.'articleCat` set `path`=\''.$path.'\' where `id`='.$id;

        if($db->update($updateCat))
        {
            showSystemMessage($lang['warning']['add_article_cat_success']);
        } else {
            showSystemMessage($lang['warning']['update_article_cat_fail']);
        }
    } else {
        echo $addCat;
        showSystemMessage($lang['warning']['add_article_cat_fail']);
    }
}

if('edit' == $opera)
{
    $name = getPOST('name');
    $parentId = intval(getPOST('parentId'));
    $path = '';
    $description = getPOST('description');
    $keywords = getPOST('keywords');
    $id = intval(getPOST('eid'));

    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    if($name == '')
    {
        showSystemMessage($lang['warning']['article_cat_empty']);
    } else {
        $name = addslashes($name);
    }

    if($parentId <= 0)
    {
        $parentId = 0;
    }

    if($description == '')
    {
        showSystemMessage($lang['warning']['description_empty']);
    } else {
        $description = $db->escape($description);
    }

    if($keywords == '')
    {
        showSystemMessage($lang['warning']['keywords_empty']);
    } else {
        $keywords = str_replace('，', ',', $keywords);
        $keywords = $db->escape($keywords);
    }

    $updateCat = 'update `'.$db_prefix.'articleCat` set `name`=\'%s\',`parentId`=%d,`description`=\'%s\',`keywords`=\'%s\' '.
                 ' where `id`=%d';
    $updateCat = sprintf($updateCat, $name, $parentId, $description, $keywords, $id);

    if($db->update($updateCat))
    {
        if($parentId > 0)
        {
            $getPath = 'select `path` from `'.$db_prefix.'articleCat` where `id`='.$parentId;
            $path = $db->fetchOne($getPath);
            $path .= ',';
        }

        $path .= $id;

        $updateCat = 'update `'.$db_prefix.'articleCat` set `path`=\''.$path.'\' where `id`='.$id;

        if($db->update($updateCat))
        {
            showSystemMessage($lang['warning']['update_article_cat_success']);
        } else {
            showSystemMessage($lang['warning']['update_article_cat_fail']);
        }
    } else {
        showSystemMessage($lang['warning']['update_article_cat_fail']);
    }
}

if('list' == $act)
{
    $getArticleCat = 'select * from `'.$db_prefix.'articleCat` order by path ASC';
    $articleCat = $db->fetchAll($getArticleCat);

    if(!$articleCat)
    {
        $articleCat = array();
    }

    foreach($articleCat as $key=>$cat)
    {
        $count = explode(',', $cat['path']);
        $count = count($count);
        $count--;

        $cat['show_name'] = '|--';
        while($count--)
        {
            $cat['show_name'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$cat['show_name'];
        }
        $cat['show_name'] .= $cat['name'];

        $cat['operation'] = '<a href="articleCat.php?act=edit&id='.$cat['id'].'">'.$lang['edit'].'</a>&nbsp;|&nbsp;'.
                            '<a href="javascript:deleteCat('.$cat['id'].')">'.$lang['delete'].'</a>';

        $articleCat[$key] = $cat;
    }

    $smarty->assign('articleCat', $articleCat);
}

if('add' == $act)
{
    $getArticleCat = 'select * from `'.$db_prefix.'articleCat` order by path ASC';
    $articleCat = $db->fetchAll($getArticleCat);

    if(!$articleCat)
    {
        $articleCat = array();
    }

    foreach($articleCat as $key=>$cat)
    {
        $count = explode(',', $cat['path']);
        $count = count($count);
        $count--;

        $cat['show_name'] = '|--';
        while($count--)
        {
            $cat['show_name'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$cat['show_name'];
        }
        $cat['show_name'] .= $cat['name'];

        $articleCat[$key] = $cat;
    }

    $smarty->assign('articleCat', $articleCat);
}

if('edit' == $act)
{
    $id = intval(getGET('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    $getArticleCat = 'select * from `'.$db_prefix.'articleCat` order by path ASC';
    $articleCat = $db->fetchAll($getArticleCat);

    if(!$articleCat)
    {
        $articleCat = array();
    }

    foreach($articleCat as $key=>$cat)
    {
        $count = explode(',', $cat['path']);
        $count = count($count);
        $count--;

        $cat['show_name'] = '|--';
        while($count--)
        {
            $cat['show_name'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$cat['show_name'];
        }
        $cat['show_name'] .= $cat['name'];

        $articleCat[$key] = $cat;
    }

    $smarty->assign('articleCat', $articleCat);

    $getCat = 'select * from `'.$db_prefix.'articleCat` where `id`='.$id;
    $cat = $db->fetchRow($getCat);

    $smarty->assign('cat', $cat);
}

if('delete' == $act)
{
    $id = intval(getGET('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    $checkArticle = 'select count(*) from `'.$db_prefix.'article` where `catId`='.$id;
    $count = $db->fetchOne($checkArticle);

    if($count > 0)
    {
        showSystemMessage('该分类下还有文章,请先删除或转移分类下的文章再进行删除操作');
    }

    $checkCat = 'select count(*) from `'.$db_prefix.'articleCat` where `parentId`='.$id;
    $count = $db->fetchOne($checkCat);

    if($count > 0)
    {
        showSystemMessage('该分类下还有子分类,请先删除或转移子分类再进行删除操作');
    }

    $deleteCat = 'delete from `'.$db_prefix.'articleCat` where `id`='.$id;

    if($db->delete($deleteCat))
    {
        showSystemMessage('删除分类成功');
    } else {
        showSystemMessage('删除分类失败');
    }
}

$smarty->assign('act', $act);
$smarty->display('articleCat.phtml');
