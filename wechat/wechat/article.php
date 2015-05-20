<?php
/*
 * 文章管理
 * @author winsen
 * @version 1.0.0
 * @date 2014-11-10
 */
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

if('add' == $opera)
{
    $title = getPOST('title');
    $author = getPOST('author');
    $publishTime = getPOST('publishTime');
    $content = getPOST('content');
    $wapContent = getPOSt('wapContent');
    $keywords = getPOST('keywords');
    $description = getPOST('description');
    $catId = intval(getPOST('catId'));
    $assocGoods = '';
    $assocArticle = '';

    if($title == '')
    {
        showSystemMessage($lang['warning']['article_title_empty']);
    } else {
        $title = addslashes($title);
    }

    if($catId <= 0)
    {
        showSystemMessage($lang['warning']['choose_article_cat_empty']);
    }

    if($author == '')
    {
        showSystemMessage($lang['warning']['author_empty']);
    } else {
        $author = addslashes($author);
    }

    if($publishTime == '')
    {
        $publishTime = time();
    } else {
        $temp = strtotime($publishTime);
        if($temp > 0)
        {
            $publishTime = strtotime($publishTime);
        } else {
            $publishTime = time();
        }
    }

    if($keywords == '')
    {
        showSystemMessage($lang['warning']['keywords_empty']);
    } else {
        $keywords = $db->escape($keywords);
    }

    if($description == '')
    {
        showSystemMessage($lang['warning']['description_empty']);
    } else {
        $description = $db->escape($description);
    }

    if($content == '')
    {
        showSystemMessage($lang['warning']['content_empty']);
    } else {
        $content = addslashes($content);
    }

    if($wapContent == '')
    {
        showSystemMessage($lang['warning']['wap_content_empty']);
    } else {
        $wapContent = addslashes($wapContent);
    }

    $addArticle = 'insert into `'.$db_prefix.'article` (`title`,`author`,`publishTime`,`content`,`wapContent`,'.
                  '`keywords`,`description`,`catId`) values (\'%s\',\'%s\',%d,\'%s\',\'%s\',\'%s\',\'%s\',%d);';
    $addArticle = sprintf($addArticle, $title, $author, $publishTime, $content, $wapContent, $keywords, $description, $catId);

    if($db->insert($addArticle))
    {
        showSystemMessage($lang['warning']['add_article_success'], array(array('alt'=>'文章列表', 'link'=>'article.php')));
    } else {
        showSystemMessage($lang['warning']['add_article_fail']);
    }
}

if('edit' == $opera)
{
    $id = intval(getPOST('eid'));
    $title = getPOST('title');
    $author = getPOST('author');
    $publishTime = getPOST('publishTime');
    $content = getPOST('content');
    $wapContent = getPOSt('wapContent');
    $keywords = getPOST('keywords');
    $description = getPOST('description');
    $catId = intval(getPOST('catId'));
    $assocGoods = '';
    $assocArticle = '';

    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    } else {
        $checkArticle = 'select `id` from `'.$db_prefix.'article` where `id`='.$id;

        if(!$db->fetchOne($checkArticle))
        {
            showSystemMessage($lang['warning']['no_article']);
        }
    }

    if($title == '')
    {
        showSystemMessage($lang['warning']['article_title_empty']);
    } else {
        $title = addslashes($title);
    }

    if($catId <= 0)
    {
        showSystemMessage($lang['warning']['choose_article_cat_empty']);
    }

    if($author == '')
    {
        showSystemMessage($lang['warning']['author_empty']);
    } else {
        $author = addslashes($author);
    }

    if($publishTime == '')
    {
        $publishTime = time();
    } else {
        $temp = strtotime($publishTime);
        if($temp > 0)
        {
            $publishTime = strtotime($publishTime);
        } else {
            $publishTime = time();
        }
    }

    if($keywords == '')
    {
        showSystemMessage($lang['warning']['keywords_empty']);
    } else {
        $keywords = $db->escape($keywords);
    }

    if($description == '')
    {
        showSystemMessage($lang['warning']['description_empty']);
    } else {
        $description = $db->escape($description);
    }

    if($content == '')
    {
        showSystemMessage($lang['warning']['content_empty']);
    } else {
        $content = addslashes($content);
    }

    if($wapContent == '')
    {
        showSystemMessage($lang['warning']['wap_content_empty']);
    } else {
        $wapContent = addslashes($wapContent);
    }

    $updateArticle = 'update `'.$db_prefix.'article` set `title`=\'%s\',`author`=\'%s\',`publishTime`=%d,`content`=\'%s\','.
                     '`wapContent`=\'%s\',`catId`=%d,`keywords`=\'%s\',`description`=\'%s\' where `id`='.$id;
    $updateArticle = sprintf($updateArticle, $title, $author, $publishTime, $content, $wapContent, $catId, $keywords, $description);

    if($db->update($updateArticle))
    {
        showSystemMessage($lang['warning']['update_article_success'], array(array('alt'=>'文章列表', 'link'=>'article.php')));
    } else {
        echo $updateArticle;
        showSystemMessage($lang['warning']['update_article_fail']);
    }
}

if('list' == $act)
{
    $getArticles = 'select * from `'.$db_prefix.'article` order by lastModify DESC';
    $articles = $db->fetchAll($getArticles);

    if($articles)
    {
        foreach($articles as $key=>$val)
        {
            $val['operation'] = '<a href="article.php?act=edit&id='.$val['id'].'">'.$lang['edit'].'</a>&nbsp;|&nbsp;'.
                                '<a href="javascript:deleteArticle('.$val['id'].');">'.$lang['delete'].'</a>';
            $articles[$key] = $val;
        }
    }

    $smarty->assign('articles', $articles);
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

    $getArticle = 'select * from `'.$db_prefix.'article` where `id`='.$id;
    $article = $db->fetchRow($getArticle);

    if(!$article)
    {
        showSystemMessage($lang['warning']['param_error']);
    } else {
        $smarty->assign('article', $article);
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
}

if('delete' == $act)
{
    $id = intval(getGET('id'));

    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    $deleteArticle = 'delete from `'.$db_prefix.'article` where `id`='.$id;

    if($db->delete($deleteArticle))
    {
        showSystemMessage($lang['warning']['delete_article_success']);
    } else {
        echo $deleteArticle;
        showSystemMessage($lang['warning']['delete_article_fail']);
    }
}

$smarty->assign('act', $act);
$smarty->display('article.phtml');
