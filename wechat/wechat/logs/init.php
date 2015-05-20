<?php
//初始化仓储型数据库的表
include '../library/init.inc.php';

$table = array();
$sql = array();

$table[] = '文章浏览日志';
$sql[] = 'create table if not exists `'.$db_prefix.'articleLogs` (
    `id` bigint not null auto_increment primary key,
    `userId` int not null default \'0\',
    `opera` varchar(255) not null,
    `addTime` int not null,
    `articleId` int unsigned not null,
    `referer` varchar(512),
    `agent` varchar(255) not null
) default charset='.$charset.';';

$table[] = '浏览日志';
$sql[] = 'create table if not exists `'.$db_prefix.'logs` (
    `id` bigint not null auto_increment primary key,
    `userId` int not null default \'0\',
    `session` varchar(255) not null,
    `url` varchar(512) not null,
    `enterTime` int not null,
    `leaveTime` int not null default \'0\',
    `referer` varchar(512),
    `agent` varchar(255) not null
) default charset='.$charset.';';


foreach($sql as $k=>$v)
{
   // if($db->runSQL($v))
   	if( $logs->query($v) )
    {
        echo '创建'.$table[$k].'...........<font color="green">success</font><br/>';
    } else {
        echo '创建'.$table[$k].'...........<font color="red">failed</font><br/>';
        echo $v.'<br/>';
        echo 'error message:'.$db->errmsg().'<br/>';
    }
}

echo '创建数据库完成。';
