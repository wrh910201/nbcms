<?php
header('Content-Type: application/xml; charset=utf-8');
include 'library/init.inc.php';

$sitemap =<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>%s</loc>
        <lastmod>%s</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    %s
</urlset>
XML;

$urlentry =<<<XML
<url>
    <loc>%s</loc>
    <lastmod>%s</lastmod>
    <changefreq>%s</changefreq>
    <priority>%.1f</priority>
</url>
XML;

$getArticles  = 'select `id`,`publishTime` from `'.DB_PREFIX.'article` where `isDelete`=0 ';
$getArticles .= ' and `publishTime`<'.time().' order by `publishTime` DESC limit 200';

$articles = $db->fetchAll($getArticles);
$domain = 'http://'.$sysconf['domain'].'/';

$entry = '';

if($articles)
{
    foreach($articles as $article)
    {
        $entry .= sprintf($urlentry, $domain.buildUrl('article', array('id'=>$article['id'])), 
                          date('c', $article['publishTime']), 'weekly', 0.8);
    }
}

$getProducts = 'select `name`,`id`,`description`,`addTime` from `'.DB_PREFIX.'product` order by `addTime` DESC';
$products = $db->fetchAll($getProducts);
if($products)
{
	foreach($products as $product)
	{
		$entry .= sprintf($urlentry, $domain.buildUrl('product', array('id'=>$product['id'])),
							date('c', $product['addTime']), 'weekly', 0.9);
	}
}

$entry = sprintf($sitemap, $domain, date('Y-m-d'), $entry);
echo $entry;
