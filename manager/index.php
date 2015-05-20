<?php
include 'library/init.inc.php';

$action = 'login|forgot';
$operaction = 'login|forgot';

$opera = checkAction($operaction, getPOST('opera'));
$act = checkAction($action, getGET('act'));
$error = array();

//如果已登陆，跳转到管理后台主页
if(isset($_SESSION['purview']) && isset($_SESSION['account'])) {
    header("location: main.php");
    exit;
}


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

    $checkAccount = 'select `password`,`roleId`,`name`,`photo` from `'.DB_PREFIX.'admin` where `account`=\''.$account.'\' limit 1';
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
                $_SESSION['photo'] = $admin['photo'];
                $_SESSION['account'] = $account;
                /**
                 * 是否有微信管理权限
                 */
                if( checkPurview('pur_wechat_bind', $role['purview']) || checkPurview('pur_wechat_manager', $role['purview']) ) {
                    $getPublicAccount = 'select `account` from `wx_publicAccount` where adminUserAccount = \''.$account.'\' limit 1;';
                    $publicAccount = $db->fetchOne($getPublicAccount);
                    if( $publicAccount ) {
                        $_SESSION['public_account'] = $publicAccount;
                    }
                }

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
