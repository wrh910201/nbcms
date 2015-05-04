<?php
include 'library/init.inc.php';

checkAdminLogin();

$operation = 'add|edit';
$action = 'add|list|delete|edit|cycle|remove';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));

if('' == $act)
{
    $act = 'list';
}

//新增资讯
if('add' == $opera)
{
    if(!checkPurview(0x100000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $title = getPOST('title');
    $author = getPOST('author');
    $articleCatId = getPOST('articleCatId');
    $keywords = getPOST('keywords');
    $description = getPOST('description');
    $content = getPOST('content');
    $publishTime = getPOST('publishTime');
    $isAutoPublish = getPOST('isAutoPublish');
    $addTime = time();

    if('' == $title)
    {
        showSystemMessage('资讯标题不能为空', array());
        exit;
    } else {
        $title = $db->escape(htmlspecialchars($title));
    }

    if('' == $author)
    {
        $author = '管理员';
    } else {
        $author = $db->escape(htmlspecialchars($author));
    }

    if('' == $keywords)
    {
        showSystemMessage('出于SEO的考虑，请务必填写关键词', array());
        exit;
    } else {
        $keywords = $db->escape(htmlspecialchars($keywords));
    }

    if('' == $description)
    {
        showSystemMessage('出于SEO的考虑，请务必填写资讯简介', array());
        exit;
    } else {
        $description = $db->escape(htmlspecialchars($description));
    }

    if('' == $articleCatId || 0 >= $articleCatId)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $articleCatId = intval($articleCatId);
    }

    $content = $db->escape($content);

    if('' == $isAutoPublish || 0 == $isAutoPublish)
    {
        $isAutoPublish = 0;
        $publishTime = $addTime;
    } else {
        if(preg_match('^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)\ \d{1,2}:\d{1,2}:\d{1,2}$', $publishTime))
        {
            $dateTime = explode(' ', $publishTime);
            $date = explode('-', $dateTime[0]);
            $time = explode(':', $dateTime[1]);

            $publishTime = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
        } else {
            showSystemMessage('发布时间格式不正确', array());
            exit;
        }
    }

    $addArticle  = 'insert into `'.DB_PREFIX.'article` (`author`,`title`,`articleCatId`,`keywords`,`description`,';
    $addArticle .= '`isDelete`,`content`,`addTime`,`publishTime`,`isAutoPublish`) values (\''.$author.'\',';
    $addArticle .= '\''.$title.'\','.$articleCatId.',\''.$keywords.'\',\''.$description.'\',0,\''.$content.'\',';
    $addArticle .= $addTime.','.$publishTime.','.$isAutoPublish.')';

    if($db->insert($addArticle))
    {
        showSystemMessage('新增资讯成功', array());
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

//编辑资讯
if('edit' == $opera)
{
    if(!checkPurview(0x400000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $title = getPOST('title');
    $author = getPOST('author');
    $articleCatId = getPOST('articleCatId');
    $keywords = getPOST('keywords');
    $description = getPOST('description');
    $content = getPOST('content');
    $publishTime = getPOST('publishTime');
    $isAutoPublish = getPOST('isAutoPublish');
    $id = getPOST('id');
    $id = intval($id);

    if('' == $id || 0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    if('' == $title)
    {
        showSystemMessage('资讯标题不能为空', array());
        exit;
    } else {
        $title = $db->escape(htmlspecialchars($title));
    }

    if('' == $author)
    {
        $author = '管理员';
    } else {
        $author = $db->escape(htmlspecialchars($author));
    }

    if('' == $keywords)
    {
        showSystemMessage('出于SEO的考虑，请务必填写关键词', array());
        exit;
    } else {
        $keywords = $db->escape(htmlspecialchars($keywords));
    }

    if('' == $description)
    {
        showSystemMessage('出于SEO的考虑，请务必填写资讯简介', array());
        exit;
    } else {
        $description = $db->escape(htmlspecialchars($description));
    }

    if('' == $articleCatId || 0 >= $articleCatId)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $articleCatId = intval($articleCatId);
    }

    $content = $db->escape($content);

    if('' == $isAutoPublish || 0 == $isAutoPublish)
    {
        $isAutoPublish = 0;
        $publishTime = time();
    } else {
        if(preg_match('^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)\ \d{1,2}:\d{1,2}:\d{1,2}$', $publishTime))
        {
            $dateTime = explode(' ', $publishTime);
            $date = explode('-', $dateTime[0]);
            $time = explode(':', $dateTime[1]);

            $publishTime = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
        } else {
            showSystemMessage('发布时间格式不正确', array());
            exit;
        }
    }

    $updateArticle  = 'update `'.DB_PREFIX.'article` set `title`=\''.$title.'\', `author`=\''.$author.'\',';
    $updateArticle .= '`keywords`=\''.$keywords.'\', `description`=\''.$description.'\', ';
    $updateArticle .= '`articleCatId`='.$articleCatId.', `content`=\''.$content.'\', `publishTime`='.$publishTime.',';
    $updateArticle .= '`isAutoPublish`='.$isAutoPublish.' where `id`='.$id.' limit 1';

    if($db->update($updateArticle))
    {
        $links = array(
            array('alt'=>'查看资讯列表', 'link'=>'article.php?act=list')
        );
        showSystemMessage('更新资讯成功', $links);
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

if('list' == $act)
{
    if(!checkPurview(0x200000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getArticles  = 'select a.`id`,a.`title`,a.`publishTime`,ac.`name` as articleCat from ';
    $getArticles .= '`'.DB_PREFIX.'article` as a left join ';
    $getArticles .= '`'.DB_PREFIX.'articleCat` as ac on a.`articleCatId`=ac.`id`';
    $getArticles .= ' where a.`isDelete`=0 order by a.`publishTime` DESC';

    assign('articles', $db->fetchAll($getArticles));
}

if('cycle' == $act)
{
    if(!checkPurview(0x800000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getArticles  = 'select a.`id`,a.`title`,a.`publishTime`,ac.`name` as articleCat from ';
    $getArticles .= '`'.DB_PREFIX.'article` as a left join ';
    $getArticles .= '`'.DB_PREFIX.'articleCat` as ac on a.`articleCatId`=ac.`id`';
    $getArticles .= ' where a.`isDelete`=1 order by a.`publishTime` DESC';

    assign('articles', $db->fetchAll($getArticles));
}

if('add' == $act)
{
    if(!checkPurview(0x100000, $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getArticleCat = 'select `name`,`id`,`path` from `'.DB_PREFIX.'articleCat` order by `path`';
    $articleCat = $db->fetchAll($getArticleCat);

    foreach($articleCat as $key=>$cat)
    {
        $count = count(explode(',', $cat['path']));

        if($count > 1)
        {
            $temp = '|--';
            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $cat['name'] = $temp.$cat['name'];
        }

        $articleCat[$key] = $cat;
    }

    assign('articleCat', $articleCat);
}

if('edit' == $act)
{
    if(!checkPurview(0x400000, $_SESSION['purview']))
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

    $id = intval($id);

    $getArticle = 'select `id`,`author`,`title`,`articleCatId`,`keywords`,`description`,`content`,`publishTime`,`isAutoPublish` from `'.DB_PREFIX.'article` where `id`='.$id.' limit 1';
    $article = $db->fetchRow($getArticle);

    if($article)
    {
        assign('article', $article);
    } else {
        showSystemMessage('参数错误', array());
        exit;
    }
    $getArticleCat = 'select `name`,`id`,`path` from `'.DB_PREFIX.'articleCat` order by `path`';
    $articleCat = $db->fetchAll($getArticleCat);

    foreach($articleCat as $key=>$cat)
    {
        $count = count(explode(',', $cat['path']));

        if($count > 1)
        {
            $temp = '|--';
            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $cat['name'] = $temp.$cat['name'];
        }

        $articleCat[$key] = $cat;
    }

    assign('articleCat', $articleCat);
}

if('delete' == $act)
{
    if(!checkPurview(0x800000, $_SESSION['purview']))
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

    $id = intval($id);
    $deleteArticle = 'update `'.DB_PREFIX.'article` set `isDelete`=1 where `id`='.$id.' limit 1';

    if($db->update($deleteArticle))
    {
        showSystemMessage('该资讯已放入回收站', array(array('alt'=>'查看资讯列表', 'link'=>'article.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

if('remove' == $act)
{
    if(!checkPurview(0x800000, $_SESSION['purview']))
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

    $id = intval($id);
    $deleteArticle = 'delete from `'.DB_PREFIX.'article` where `id`='.$id.' limit 1';

    if($db->update($deleteArticle))
    {
        showSystemMessage('删除资讯成功', array(array('alt'=>'查看资讯列表', 'link'=>'article.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

assign('pageTitle', '资讯管理');
assign('act', $act);
$smarty->display('article.phtml');
