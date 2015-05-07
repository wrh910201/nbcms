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
    `img` varchar(255) comment \'分类图片\',
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
    `purview` text not null
) default charset='.CHARSET.';';

$table[] = '初始化管理员角色表';
$purview = array(
    'pur_sysconf' => array(
        'pur_sysconf_add',
        'pur_sysconf_list',
        'pur_sysconf_edit',
        'pur_sysconf_delete',
    ),
    'pur_nav' => array(
        'pur_nav_add',
        'pur_nav_list',
        'pur_nav_edit',
        'pur_nav_delete',
    ),
    'pur_adPosition' => array(
        'pur_adPosition_add',
        'pur_adPosition_list',
        'pur_adPosition_edit',
        'pur_adPosition_delete',
    ),
    'pur_ad' => array(
        'pur_ad_add',
        'pur_ad_list',
        'pur_ad_edit',
        'pur_ad_delete',
    ),
    'pur_articleCat' => array(
        'pur_articleCat_add',
        'pur_articleCat_list',
        'pur_articleCat_edit',
        'pur_articleCat_delete',
    ),
    'pur_article' => array(
        'pur_article_add',
        'pur_article_list',
        'pur_article_edit',
        'pur_article_delete',
    ),
    'pur_admin' => array(
        'pur_admin_add',
        'pur_admin_list',
        'pur_admin_edit',
        'pur_admin_delete',
    ),
    'pur_adminRole' => array(
        'pur_adminRole_add',
        'pur_adminRole_list',
        'pur_adminRole_edit',
        'pur_adminRole_delete',
    ),

    'pur_friend' => array(
        'pur_friend_add',
        'pur_friend_list',
        'pur_friend_edit',
        'pur_friend_delete',
    ),

    'pur_category' => array(
        'pur_category_add',
        'pur_category_list',
        'pur_category_edit',
        'pur_category_delete',
    ),
    'pur_product' => array(
        'pur_product_add',
        'pur_product_list',
        'pur_product_edit',
        'pur_product_delete'
    ),
    'pur_distributor' => array(
        'pur_distributor_add',
        'pur_distributor_list',
        'pur_distributor_edit',
        'pur_distributor_delete',
    ),
    'pur_carousel' => array(
        'pur_carousel_add',
        'pur_carousel_list',
        'pur_carousel_edit',
        'pur_carousel_delete',
    ),
);
$sql[] = 'insert into `'.DB_PREFIX.'adminRole` values (1, \'超级管理员\', \''.json_encode($purview).'\');';

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

$table[] = '轮播';
$sql[] = "create table if not exists `".DB_PREFIX."carousel` (
        `id` int not null auto_increment primary key,
        `img` varchar(255) not null comment '图片url',
        `img_shortcut` varchar(255) not null comment '图片缩略图',
        `viewOrder` int not null default '1' comment '图片顺序',
        `addTime` int not null comment '添加时间'
) default charset=".CHARSET.";";

$table[] = '轮播初始化';
$sql[] = 'insert into `'.DB_PREFIX.'sysconf` values (\'carousel_on\', \'开启轮播\', \'1\', \'radio\')';

$table[] = '经销商';
$sql[] = "create table if not exists `".DB_PREFIX."distributor` (
        `id` int not null auto_increment primary key,
        `name` varchar(255) not null comment '经销商名称',
        `address` varchar(255) not null comment '地址',
        `phone` varchar(20) not null comment '手机',
        `authCode` varchar(255) not null comment '授权码',
        `DistrictID` int not null comment '地区id',
        `contact` varchar(255) not null comment '联系人',
        `add_time` int not null comment '添加时间',
        `status` tinyint not null default '1' comment '状态1：正常；0：xx',
        `lat` decimal(7,4) not null default 0 comment '纬度',
        `lng` decimal(7,4) not null default 0 comment '经度'
) default charset=".CHARSET.";";


$flag = true;
foreach($table as $key=>$value)
{
    //echo $sql[$key].'<br />';continue;
    if($db->query($sql[$key]))
    {
        echo $value.'..................<font color="green">OK</font><br/>';
    } else {
        echo $value.'..................<font color="red">Fail</font><br/>';
        echo mysql_error();
        $flag = false;
    }
}
