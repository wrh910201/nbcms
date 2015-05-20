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
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

define('ROOT_PATH', str_replace('wechat/library/init.inc.php', '', str_replace('\\', '/', __FILE__)));
if(!class_exists('AutoLoader'))
{
    include(ROOT_PATH.'library/AutoLoader.class.php');
}

$loader = AutoLoader::getInstance();
$configs = array('script_path'=>ROOT_PATH.'library/', 'class_path'=>ROOT_PATH.'library/');
$loader->setConfigs($configs);

$class_list = array('WechatResponse', 'Wechat', 'Smarty', 'MySQL', 'Logs');
$loader->includeClass($class_list);
$script_list = array('configs','functions', 'lang');
$loader->includeScript($script_list);
//初始化数据库链接
global $db;
$db = new MySQL(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
//$logs = new Logs(DB_HOST, DB_USERNAME, DB_PASSWORD, 'wxlogs');
//初始化smarty对象
global $smarty;
$smarty = new Smarty();
$smarty->setCompileDir(ROOT_PATH.'wechat/data/compile_');
$smarty->setTemplateDir(ROOT_PATH.'wechat/themes');
$smarty->setCacheDir(ROOT_PATH.'wechat/data/cache_');
$smarty->cache_lifetime = 2;
$smarty->force_compile = true;
global $lang;
$smarty->assign('lang', $lang);
