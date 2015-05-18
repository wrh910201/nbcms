<?php
/**
 * 初始化程序
 * @author winsen
 * @version 1.0.0
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
//设置系统相关参数
/*
@ini_set('session.use_cookies', 1);
//@ini_set('session.cookie_lifetime', 1440);
//@ini_set('session.gc_maxlifetime', 1440);
 */
session_start();
define('ROOT_PATH', str_replace('library/init.inc.php', '',str_replace('\\', '/',__FILE__)));

//检查是否已安装
$lock_file = 'data/config.lock';
if(!file_exists(ROOT_PATH.$lock_file))
{
    header('Location:install/'."\n");
    exit;
}

if(!class_exists('AutoLoader'))
{
    include('AutoLoader.class.php');
}

$loader = AutoLoader::getInstance();
$configs = array('script_path'=>ROOT_PATH.'library/', 'class_path'=>ROOT_PATH.'library/');
$loader->setConfigs($configs);

$class_list = array('MySQL', 'BaseDAO', 'Smarty');
$loader->includeClass($class_list);
$script_list = array('configs','functions','lang');
$loader->includeScript($script_list);
//初始化数据库链接
global $db;
$db = new MySQL(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
//初始化smarty对象
global $smarty;
$smarty = new Smarty();
$smarty->setCompileDir(ROOT_PATH.'data/compile_');
$smarty->setTemplateDir(ROOT_PATH.'themes');
$smarty->setCacheDir(ROOT_PATH.'data/cache_');
$smarty->cache_lifetime = 1800;
$smarty->force_compile = true;

//获取系统配置信息
global $sysconf;
$getSysconf = 'select `key`,`value` from `'.DB_PREFIX.'sysconf`;';
$sysconfs = $db->fetchAll($getSysconf);
global $conf;
if($sysconfs)
{
    foreach($sysconfs as $key=>$value)
    {
        $sysconf[$value['key']] = $value['value'];
        assign($value['key'], $value['value']);
        $conf[$value['key']] = $value['value'];
    }
}

//获取导航条
$getTopNav = 'select `name`,`url`,`isOpenNew` from `'.DB_PREFIX.'nav` where `position`=\'top\' and `isShow`=1 order by `path`,`orderView` ASC';
$topNav = $db->fetchAll($getTopNav);
assign('topNav', $topNav);

$getMiddleNav = 'select `name`,`url`,`isOpenNew` from `'.DB_PREFIX.'nav` where `position`=\'middle\' and `isShow`=1 order by `path`,`orderView` ASC';
$middleNav = $db->fetchAll($getMiddleNav);
foreach($middleNav as $key=>$nav)
{
    if($_SERVER['REQUEST_URI'] == $nav['url'] || $_SERVER['REQUEST_URI'] == '/'.$nav['url'])
    {
        $nav['isActivite'] = 1;
    } else {
        $nav['isActivite'] = 0;
    }
    $middleNav[$key] = $nav;
}
assign('middleNav', $middleNav);

$getBottomNav = 'select `name`,`url`,`isOpenNew` from `'.DB_PREFIX.'nav` where `position`=\'bottom\' and `isShow`=1 order by `path`,`orderView` ASC';
$bottomNav = $db->fetchAll($getBottomNav);
assign('bottomNav', $bottomNav);

$getFriends = 'select `name`,`id`,`url`,`isFollow`,`orderView`,`type` from `'.DB_PREFIX.'friend` order by orderView asc';
$friends = $db->fetchAll($getFriends);

assign('friends', $friends);

//语言包赋值
assign('LANG', $LANG);
assign('year', date('Y'));
//是否压缩输出网页
if($conf['isCompression'] && Extension_Loaded('zlib'))
{
    ob_start('ob_gzhandler');
}
