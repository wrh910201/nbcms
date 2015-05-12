<?php
include 'library/init.inc.php';
$template = 'articleCat.phtml';
$display = 'articleCat';



$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id = $db->escape($id);

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = $page <= 0 ? 1 : $page;
$pageNum = 5;
$offset = ($page - 1) * $pageNum;

$activeNav = get_active_nav();
$activeNav .= '?id='.$id;
assign('activeNav', $activeNav);

$cat = null;
if($id > 0)
{
    $getCategory  = 'select `id`,`parentId`,`name`,`description`,`keywords`,`path` from `'.DB_PREFIX.'articleCat`';
    $getCategory .= ' where `id`='.$id;
    $cat = $db->fetchRow($getCategory);
}

if(!$cat)
{
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');
    $display = 'category';
    $getCategoryList = 'select `id`,`name`  from `'.DB_PREFIX.'articleCat` order by `path` ASC';
    $categoryList = $db->fetchAll($getCategoryList);

    $display = 'notFount';
    assign('categoryList', $categoryList);
} else {
    //获取分类下的文章数量

    $getTotal = 'select count(id) from '.DB_PREFIX.'article where articleCatId = '.$id.' and `isDelete`=0';
    $total = $db->fetchOne($getTotal);

    $totalPage = ceil($total/$pageNum);

    //获得分类下的文章
    $getArticles  = 'select `id`,`title`,`publishTime`,`keywords`,`description` from `'.DB_PREFIX.'article`';
    $getArticles .= ' where `articleCatId`='.$id.' and `isDelete`=0 order by`publishTime` DESC';
    $getArticles .= ' limit '.$offset.','.$pageNum;
    $articles = $db->fetchAll($getArticles);
    if($articles)
    {
	    foreach($articles as $key=>$a)
	    {
	    	$articles[$key]['publishTime'] = date('Y-m-d', $a['publishTime']);
	    }
    }

    assign('articles', $articles);


    //获取下级的文章分类
    $getCategoryList = 'select `id`,`name` from `'.DB_PREFIX.'articleCat` where `parentId`='.$cat['id'];
    $categoryList = $db->fetchAll($getCategoryList);

    if($categoryList)
    {
        assign('categoryList', $categoryList);
    }

    create_pager($page, $totalPage, $total);

    assign('cat', $cat);
    assign('pageTitle', $cat['name']);
    assign('keywords', $cat['keywords']);
    assign('description', $cat['description']);
    assign('urHere', buildUrHere('articleCat', array('id'=>$id)));


}

assign('cid', $id);
assign('display', $display);
$smarty->display($template);
