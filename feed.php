<?php
header('Content-Type: application/xml; charset=utf-8');
include 'library/init.inc.php';

$feed =<<<XML
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
    <channel>
        <title>%s</title>
        <link>%s</link>
        <description>%s</description>
        %s
    </channel>
</rss>
XML;

$item =<<<XML
<item>
    <title>%s</title>
    <link>%s</link>
    <author>%s</author>
    <description>%s</description>
    <pubDate>%s</pubDate>
</item>    
XML;

$getArticles  = 'select `title`,`author`,`id`,`description`,`publishTime` from `'.DB_PREFIX.'article` ';
$getArticles .= ' order by `publishTime` DESC limit 15';

$articles = $db->fetchAll($getArticles);
$items = '';

if($articles)
{
    foreach($articles as $article)
    {
        $items .= sprintf($item, $article['title'], buildUrl('article', array('id'=>$article['id'])), $article['author'], 
                      $article['description'], date('D, d M Y H:i:s T', $article['publishTime']));
    }
}

$getProducts = 'select `name`,`id`,`description`,`addTime` from `'.DB_PREFIX.'product` order by `addTime` DESC';
$products = $db->fetchAll($getProducts);

if($products)
{
	foreach($products as $product)
	{
		$items .= sprintf($item, $product['name'], buildUrl('product', array('id'=>$product['id'])), '管理员',
						$product['description'], date('D, d M Y H:i:s T', $product['addTime']));
	}
}

$rss = sprintf($feed, $sysconf['siteName'], 'http://'.$sysconf['domain'], $sysconf['siteDescription'], $items);

echo $rss;
