<?php
include 'library/init.inc.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$template = 'article.phtml';
$display = 'article';

$id = $db->escape($id);
$currentTime = time();
$article = null;

if($id > 0)
{
    $getArticle  = 'select a.`author`, a.`title`, a.`keywords`, a.`description`, a.`content`, a.`publishTime`, ';
    $getArticle .= ' ac.`name`, a.`articleCatId`, ac.`parentId` ';
    $getArticle .= ' from `'.DB_PREFIX.'article` as a left join `'.DB_PREFIX.'articleCat` as ac on ';
    $getArticle .= ' a.`articleCatId`=ac.`id` ';
    $getArticle .= ' where a.`isDelete`=0 and a.`id`='.$id.' and a.`publishTime`<='.$currentTime;
    $getArticle .= ' limit 1';

    $article = $db->fetchRow($getArticle);
}

if(!$article)
{
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');

    //找不到文章,显示最近更新的5篇文章
    $getLastUpdate  = 'select `title`,`id`,`description`,`publishTime` from `'.DB_PREFIX.'article` ';
    $getLastUpdate .= ' order by `publishTime` DESC limit 5';

    $lastUpdate = $db->fetchAll($getLastUpdate);

    $display = 'notFound';

    assign('keywords', '');
    assign('description', '');
    assign('pageTitle', $LANG['notFoundArticle']);
} else {
    //获取同一分类下的上一篇和下一篇
    $getPre  = 'select `id`,`title` from `'.DB_PREFIX.'article` where `articleCatId`='.$article['articleCatId'];
    $getPre .= ' and `isDelete`=0 and `publishTime`<'.$article['publishTime'].' and `id`<>'.$id;
    $getPre .= ' order by `publishTime` DESC limit 1';

    $preArticle = $db->fetchRow($getPre);

    $getNext  = 'select `id`,`title` from `'.DB_PREFIX.'article` where `articleCatId`='.$article['articleCatId'];
    $getNext .= ' and `isDelete`=0 and `publishTime`>'.$article['publishTime'].' and `id`<>'.$id;
    $getNext .= ' order by `publishTime` ASC limit 1';

    $nextArticle = $db->fetchRow($getNext);

    assign('pre', $preArticle);
    assign('next', $nextArticle);

    $getCategory = 'select `id`,`name` from `'.DB_PREFIX.'articleCat` where `id`='.$article['articleCatId'];
    $cat = $db->fetchRow($getCategory);
    assign('cat', $cat);
    
    $getArticleCatList = 'select `id`,`name` from `'.DB_PREFIX.'articleCat` where `parentId`='.$article['articleCatId'];
    $articleCatList = $db->fetchAll($getArticleCatList);
    assign('articleCatList', $articleCatList);
    
    assign('keywords', $article['keywords']);
    assign('description', $article['description']);
    assign('pageTitle', $article['title']);
    assign('urHere', buildUrHere('article', array('id'=>$id)));
    
    $article['publishTime'] = date('Y-m-d H:i', $article['publishTime']);
    assign('article', $article);
}

assign('display', $display);
$smarty->display($template);
