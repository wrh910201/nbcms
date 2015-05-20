<?php
/**
 * 初始化程序
 * @author winsen
 * @version 1.0.0
 */
//设置系统相关参数
/*
ini_set('session.use_cookies', 1);
ini_set('session.cookie_lifetime', 1440);
ini_set('session.gc_maxlifetime', 1440);
session_start();
 */
define('ROOT_PATH', str_replace('library/init.inc.php', '',str_replace('\\', '/', __FILE__)));
if(!class_exists('AutoLoader'))
{
    include('AutoLoader.class.php');
}

$loader = AutoLoader::getInstance();
$configs = array('script_path'=>ROOT_PATH.'library/', 'class_path'=>ROOT_PATH.'library/');
$loader->setConfigs($configs);

$class_list = array('WechatResponse', 'Wechat', 'MySQL');
$loader->includeClass($class_list);
$script_list = array('configs','functions');
$loader->includeScript($script_list);
//初始化数据库链接
global $db;
//$db = new SaeMysql();
$db = new MySQL(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
//初始化smarty对象
/*
global $smarty;
$smarty = new Smarty();
$smarty->setCompileDir(ROOT_PATH.'data/compile');
$smarty->setTemplateDir(ROOT_PATH.'themes/default');
$smarty->setCacheDir(ROOT_PATH.'data/cache');
$smarty->cache_lifetime = 2;
$smarty->force_compile = true;
 */
