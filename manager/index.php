<?php
include 'library/init.inc.php';

$action = 'login|forgot';
$operaction = 'login|forgot';

$opera = checkAction($operaction, getPOST('opera'));
$act = checkAction($action, getGET('act'));
$error = array();

if('' === $act)
{
    $act = 'login';
}

//管理员登录
if('login' == $opera)
{
    $account = getPOST('account');
    $password = getPOST('password');

    if('' == $account)
    {
        $error['account'] = '账号不能为空';
    } else {
        $account = $db->escape(htmlspecialchars($account));
    }

    if('' == $password)
    {
        $error['password'] = '密码不能为空';
    } else {
        $password = md5($password.PASSWORD_END);
    }

    $checkAccount = 'select `password`,`roleId`,`name` from `'.DB_PREFIX.'admin` where `account`=\''.$account.'\' limit 1';
    $admin = $db->fetchRow($checkAccount);

    if($admin)
    {
        if($password == $admin['password'])
        {
            $getRole = 'select `purview` from `'.DB_PREFIX.'adminRole` where `id`='.$admin['roleId'].' limit 1';
            if($role = $db->fetchRow($getRole))
            {
                $_SESSION['purview'] = $role['purview'];
                $_SESSION['name'] = $admin['name'];
                $_SESSION['account'] = $account;
                showSystemMessage('登录成功', array(array('alt'=>'进入管理后台', 'link'=>'main.php')));
                exit;
            } else {
                $error['account'] = '账号错误';
            }
        } else {
            $error['password'] = '密码错误';
        }
    } else {
        $error['account'] = '账号不存在';
    }
}
//忘记密码
if('forgot' == $opera)
{
}

assign('error', $error);
assign('act', $act);
assign('pageTitle', 'NB_CMS管理后台');
$smarty->display('index.phtml');
