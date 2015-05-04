<?php
/**
 * CMS首页
 * @author winsen
 * @version 1.0.0
 */
include 'library/init.inc.php';
$template = 'index.phtml';
$currentTime = time();

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

//最新资讯
$getNews = 'select `title`,`id`,`publishTime` from `'.DB_PREFIX.'article` order by `publishTime` DESC limit 10';
$news = $db->fetchAll($getNews);
foreach($news as $key=>$article)
{
	$article['publishTime'] = date('Y-m-d', $article['publishTime']);
	$news[$key] = $article;
}
assign('news', $news);

$getProducts = 'select `id`,`name`,`description` from `'.DB_PREFIX.'product` order by `addTime`';
$product = $db->fetchAll($getProducts);
foreach($product as $key=>$p)
{
	$getGallery = 'select `normal` from `'.DB_PREFIX.'gallery` where `productId`='.$p['id'].' and `isDefault`=1';
	$gallery = $db->fetchRow($getGallery);
	$product[$key]['img'] = $gallery['normal'];
}

assign('product', $product);
assign('ads', $ads);
$smarty->display($template);
