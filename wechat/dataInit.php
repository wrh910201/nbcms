<?php
include 'library/init.inc.php';

$table = array();
$sql = array();

$db_prefix = 'wx_';
$charset = 'utf8';

//公众号表
$table[] = '公众号';
$sql[] = 'create table if not exists `'.$db_prefix.'publicAccount` (
    `id` bigint not null auto_increment unique,
    `name` varchar(255) not null comment \'公众号名称\',
    `account` varchar(255) not null primary key comment \'微信号\',
    `adminUserAccount` varchar(255) not null comment \'管理员编号\',
    `token` varchar(255) not null unique comment \'微信接入token\',
    `appID` varchar(255) comment \'公众号appID\',
    `appsecret` varchar(255) comment \'公众号appsecret\',
    `accessToken` varchar(512) comment \'获得的access_token\',
    `expireTime` int not null default \'0\' comment \'access_token超时时间,7200秒\',
    `accountType` tinyint not null default \'0\' comment \'账号类型\'
) default charset='.$charset.';';

//管理员表
$table[] = '管理员';
$sql[] = 'create table if not exists `'.$db_prefix.'adminUser` (
    `id` bigint not null auto_increment unique,
    `account` varchar(255) not null primary key,
    `password` varchar(255) not null,
    `phone` varchar(255) not null,
    `email` varchar(255),
    `purview` varchar(255) not null default \'0\',
    `name` varchar(255),
    `sex` char(2),
    `enabled` tinyint(1) default \'0\'
) default charset='.$charset.';';

//被动回复策略表
$table[] = '被动回复策略';
$sql[] = 'create table if not exists `'.$db_prefix.'rules` (
    `id` bigint not null auto_increment primary key,
    `publicAccount` varchar(255) not null,
    `enabled` tinyint(1) not null default \'1\',
    `rule` varchar(255) not null,
    `matchMode` int not null default \'0\',
    `responseId` int not null,
    `orderView` int not null default \'50\',
    `isDefault` tinyint(1) not null default \'0\',
    `name` varchar(255) not null
) default charset='.$charset.';';

//回复内容表
$table[] = '回复内容';
$sql[] = 'create table if not exists `'.$db_prefix.'response` (
    `id` bigint not null auto_increment primary key,
    `name` varchar(255) not null,
    `msgType` varchar(255) not null,
    `content` text,
    `title` text,
    `description` varchar(255),
    `musicUrl` varchar(255),
    `HQMusicUrl` varchar(255),
    `url` text,
    `picUrl` text,
    `mediaId` int,
    `thumbMediaId` int
) default charset='.$charset.';';

//接收消息表
$table[] = '接收消息';
$sql[] = 'create table if not exists `'.$db_prefix.'request` (
    `id` bigint not null auto_increment primary key,
    `publicAccount` varchar(255) not null,
    `fromUserName` varchar(255) not null,
    `content` text,
    `locationX` varchar(255),
    `locationY` varchar(255),
    `picUrl` varchar(255),
    `format` varchar(255),
    `createTime` int not null,
    `title` varchar(255),
    `msgType` varchar(255) not null,
    `thumbMediaId` int,
    `mediaId` int,
    `url` text,
    `label` varchar(255),
    `description` text,
    `msgId` int,
    `event` varchar(255),
    `eventKey` varchar(255),
    `ticket` varchar(255),
    `precision` varchar(255),
    `latitude` varchar(255),
    `longitude` varchar(255),
    `recognition` text,
    `scale` varchar(255)
) default charset='.$charset.';';

//发送记录
$table[] = '发送记录';
$sql[] = 'create table if not exists `'.$db_prefix.'responseLogs` (
    `id` bigint not null auto_increment primary key,
    `publicAccount` varchar(255) not null,
    `toUserName` varchar(255) not null,
    `response` text not null,
    `createTime` int not null,
    `requestId` int not null
) default charset='.$charset.';';

//自定义菜单
$table[] = '自定义菜单';
$sql[] = 'create table if not exists `'.$db_prefix.'menu` (
    `id` bigint not null auto_increment primary key,
    `publicAccount` varchar(255) not null,
    `name` varchar(255) not null,
    `key` varchar(255) not null,
    `type` varchar(255) not null,
    `parentId` int not null default \'0\',
    `path` varchar(255) not null
) default charset='.$charset.';';

//微信粉丝
$table[] = '关注用户';
$sql[] = 'create table if not exists `'.$db_prefix.'user` (
    `id` bigint not null auto_increment unique,
    `openId` varchar(255) not null primary key,
    `addTime` int not null,
    `unsubscribed` tinyint(1) not null default \'0\',
    `publicAccount` varchar(255) not null default \'\',
    `leaveTime` int,
    `name` varchar(255),
    `mobile` varchar(255),
    `card` varchar(255),
    `sex` char(2),
    `password` varchar(255),
    `integral` int not null default \'0\',
    `level` int,
    `experience` int,
    `unionid` varchar(512),
    `salt` char(6) not null default \'\',
    `parentId` varchar(255),
    `path` varchar(255)
) default charset='.$charset.';';

//CMS模块
$table[] = '文章分类';
$sql[] = 'create table if not exists `'.$db_prefix.'articleCat` (
    `id` bigint not null auto_increment primary key,
    `name` varchar(255) not null,
    `parentId` int not null default \'0\',
    `path` varchar(255),
    `description` varchar(255) not null,
    `keywords` varchar(255) not null
) default charset='.$charset.';';

$table[] = '文章列表';
$sql[] = 'create table if not exists `'.$db_prefix.'article` (
    `id` bigint not null auto_increment primary key,
    `title` varchar(255) not null,
    `author` varchar(255) not null,
    `lastModify` timestamp not null,
    `content` text not null,
    `wapContent` text not null,
    `readCount` int not null default \'0\',
    `favouriteCount` int not null default \'0\',
    `publishTime` int not null,
    `assocGoods` varchar(512),
    `assocArticle` varchar(512),
    `catId` bigint not null,
    `keywords` varchar(255) not null,
    `description` varchar(255) not null
) default charset='.$charset.';';

foreach($sql as $k=>$v)
{
   // if($db->runSQL($v))
   	if( $db->query($v) )
    {
        echo '创建'.$table[$k].'...........<font color="green">success</font><br/>';
    } else {
        echo '创建'.$table[$k].'...........<font color="red">failed</font><br/>';
        echo $v.'<br/>';
        echo 'error message:'.$db->errmsg().'<br/>';
    }
}

echo '创建数据库完成。<a href="/wechat">管理入口</a>';
