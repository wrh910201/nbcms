<?php
include '../library/init.inc.php';

$table = array();
$sql = array();

$table[] = '系统信息表';
$sql[] = 'create table if not exists `'.DB_PREFIX.'sysconf` (
    `key` varchar(255) not null primary key,
    `name` varchar(255) not null,
    `value` varchar(255) not null,
    `type` varchar(255) not null
) default charset='.CHARSET.';';

$table[] = '初始化系统信息';
$sql[] = 'insert into `'.DB_PREFIX.'sysconf` values (\'siteName\', \'站点名称\', \'NB_CMS\', \'text\'),(\'domain\', \'域名\', \'localhost/cms\', \'text\'),(\'logo\', \'LOGO\', \'upload/image/logo.jpg\', \'file\'),(\'icp\', \'ICP备案号\', \'\', \'text\'),(\'owner\', \'网站主体\', \'君穗信息科技\', \'text\'), (\'siteKeywords\', \'站点关键词\', \'\', \'text\'), (\'siteDescription\', \'站点描述\', \'\',\'textarea\'), (\'isStatic\', \'伪静态化\', \'0\', \'radio\');';

$table[] = '导航条';
$sql[] = 'create table if not exists `'.DB_PREFIX.'nav` (
    `id` bigint not null auto_increment primary key,
    `name` varchar(255) not null,
    `isShow` tinyint(1) not null default \'1\',
    `url` varchar(255) not null,
    `orderView` int not null default \'50\',
    `position` varchar(255) not null,
    `parentId` int not null default \'0\',
    `path` varchar(255) not null,
    `isOpenNew` tinyint(1) not null default \'0\'
) default charset='.CHARSET.';';

$table[] = '广告位置';
$sql[] = 'create table if not exists `'.DB_PREFIX.'adPosition` (
    `id` bigint not null auto_increment primary key,
    `targetTemplate` varchar(255) not null,
    `name` varchar(255) not null,
    `number` int not null default \'3\',
    `type` varchar(255) not null default \'js\',
    `code` varchar(255) not null,
    `width` int not null,
    `height` int not null
) default charset='.CHARSET.';';

$table[] = '广告';
$sql[] = 'create table if not exists `'.DB_PREFIX.'ad` (
    `id` bigint not null auto_increment primary key,
    `img` varchar(255) not null,
    `clickTime` int not null default \'0\',
    `alt` varchar(255) not null,
    `url` varchar(255) not null,
    `startTime` int not null,
    `endTime` int not null,
    `adPositionId` int not null
) default charset='.CHARSET.';';

$table[] = '文章分类';
$sql[] = 'create table if not exists `'.DB_PREFIX.'articleCat` (
    `id` bigint not null auto_increment primary key,
    `name` varchar(255) not null,
    `parentId` int not null default \'0\',
    `keywords` varchar(255) not null,
    `description` varchar(255) not null,
    `locked` tinyint(1) not null default \'0\',
    `img` varchar(255) commit \'分类图片\',
    `path` varchar(255)
) default charset='.CHARSET.';';

$talbe[] = '初始化系统分类';
$sql[] = 'insert into `'.DB_PREFIX.'articleCat` values (1, \'系统分类\', 0, \'\', \'\', 1, \'1\');';

$table[] = '文章内容';
$sql[] = 'create table if not exists `'.DB_PREFIX.'article` (
    `id` bigint not null auto_increment primary key,
    `author` varchar(255) not null,
    `title` varchar(255) not null,
    `articleCatId` int not null,
    `keywords` varchar(255) not null,
    `description` varchar(255) not null,
    `isDelete` tinyint(1) not null default \'0\',
    `content` text,
    `addTime` int not null,
    `publishTime` int not null,
    `isAutoPublish` tinyint(1) default \'0\',
    `img` varchar(255) not null comment \'文章封面图片\',
    `img_shortcut` varchar(255) not null comment \'文章封面小图\'
) default charset='.CHARSET.';';

$table[] = '管理员';
$sql[] = 'create table if not exists `'.DB_PREFIX.'admin` (
    `account` varchar(255) not null primary key,
    `password` varchar(255) not null,
    `roleId` int not null,
    `name` varchar(255) not null,
    `sex` char(2) not null,
    `email` varchar(255) not null unique,
    `mobile` varchar(255) not null unique,
    `photo` varchar(255)
) default charset='.CHARSET.';';

$table[] = '初始化管理员表';
$sql[] = 'insert into `'.DB_PREFIX.'admin` values (\'admin\', \''.md5('admin'.PASSWORD_END).'\', 1, \'管理员\', \'男\', \'airplace1@gmail.com\', \'13929564894\', \'\');';

$table[] = '管理员角色表';
$sql[] = 'create table if not exists `'.DB_PREFIX.'adminRole` (
    `id` bigint not null auto_increment primary key,
    `name` varchar(255) not null,
    `purview` bigint not null
) default charset='.CHARSET.';';

$table[] = '初始化管理员角色表';
$sql[] = 'insert into `'.DB_PREFIX.'adminRole` values (1, \'超级管理员\', 4294967295);';

$table[] = '友情链接';
$sql[] = 'create table if not exists `'.DB_PREFIX.'friend` (
    `id` bigint not null auto_increment primary key,
    `url` varchar(255) not null,
    `name` varchar(255) not null,
    `type` varchar(255) not null default \'text\',
    `img` varchar(255),
    `isFollow` tinyint(1) not null default \'0\',
    `orderView` int not null default \'50\'
) default charset='.CHARSET.';';
$flag = true;
foreach($table as $key=>$value)
{
    if($db->query($sql[$key]))
    {
        echo $value.'..................<font color="green">OK</font><br/>';
    } else {
        echo $value.'..................<font color="red">Fail</font><br/>';
        echo mysql_error();
        $flag = false;
    }
}
