<?php
/**
 * CMS首页
 * @author wrh
 * @version 1.0.0
 */
include 'library/init.inc.php';
$template = 'index.phtml';
$currentTime = time();

$activeNav = get_active_nav();
assign('activeNav', $activeNav);

$getAdPositions  = 'select `id`,`number`,`type`,`code`,`width`,`height` from `'.DB_PREFIX.'adPosition` ';
$getAdPositions .= ' where `targetTemplate`=\''.$template.'\'';
$adPositions = $db->fetchAll($getAdPositions);

$ads = array();

foreach($adPositions as $adPosition)
{
    $getIndexAds  = 'select `id`,`img`,`alt` from `'.DB_PREFIX.'ad` where `startTime`<='.$currentTime;
    $getIndexAds .= ' and `endTime`>='.$currentTime.' and `adPositionId`='.$adPosition['id'];
    $getIndexAds .= ' order by `endTime` limit '.$adPosition['number'];
    $ad = $db->fetchAll($getIndexAds);

    if($ad)
    {
        $item = array();
        $item['ad'] = $ad;
        $item['code'] = $adPosition['code'];
        $item['type'] = $adPosition['type'];
        $item['width'] = $adPosition['width'];
        $item['height'] = $adPosition['height'];

        $ads[$adPosition['id']] = $item;
    }
}
//轮播
$getCarousels = 'select `img` from '.DB_PREFIX.'carousel where 1 order by orderView asc;';
$carousels = $db->fetchAll($getCarousels);
//var_dump($carousels);exit;
assign('carousels', $carousels);


//$getCategories = 'select id, name from '.DB_PREFIX.'articleCat where parentId = 0 limit 3 order by id asc';
//$categories = $db->fetchAll($getCategories);
//foreach( $categories as $category ) {
//
//}

//资讯 品牌故事
$getBrandStory = 'select `title`,`id`,`publishTime`,`author`, `img`, `img_shortcut` from `'.DB_PREFIX.'article` where `isDelete`=0 and `articleCatId` = 1 order by `publishTime` DESC limit 4';
$brandStory = $db->fetchAll($getBrandStory);
$news = array();
foreach($brandStory as $key=>$article)
{
	$article['publishTime'] = date('Y-m-d', $article['publishTime']);
    $news[$key] = $article;
}
assign('brandStory', $news);

//资讯 企业新闻
$getEnterpriseNews = 'select `title`,`id`,`publishTime`,`author`, `img`, `img_shortcut` from `'.DB_PREFIX.'article`  where `isDelete`=0 and `articleCatId` = 2 order by `publishTime` DESC limit 4';
$enterpriseNews = $db->fetchAll($getEnterpriseNews);
$news = array();
foreach($enterpriseNews as $key=>$article)
{
    $article['publishTime'] = date('Y-m-d', $article['publishTime']);
    $news[$key] = $article;
}
assign('enterpriseNews', $news);

//资讯 美妆学院
$getColleges = 'select `title`,`id`,`publishTime`,`author`, `img`, `img_shortcut` from `'.DB_PREFIX.'article`  where `isDelete`=0 and `articleCatId` = 3 order by `publishTime` DESC limit 4';
$colleges = $db->fetchAll($getColleges);
$news = array();
foreach($colleges as $key=>$article)
{
    $article['publishTime'] = date('Y-m-d', $article['publishTime']);
    $news[$key] = $article;
}
assign('colleges', $news);

get_area_data();


assign('ads', $ads);
$smarty->display($template);
