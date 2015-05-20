<?php
include 'library/init.inc.php';

$getArticle = 'select * from `'.$db_prefix.'article` order by lastModify DESC';
$article = $db->fetchRow($getArticle);

echo '<h2>电脑版</h2>';
echo $article['content'];
echo '<h2>手机版</h2>';
echo $article['wapContent'];
