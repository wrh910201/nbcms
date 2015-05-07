<?php
include 'library/init.inc.php';

back_base_init();

$action = 'list|add|edit|delete';
$operation = 'add|edit';
$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));
if('' == $act)
{
    $act = 'list';
}

//添加管理员
if('add' == $opera)
{
    if(!checkPurview('pur_admin_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $account = getPOST('account');
    $password = getPOST('password');
    $roleId = getPOST('roleId');
    $roleId = intval($roleId);
    $name = getPOST('name');
    $sex = getPOST('sex');
    $email = getPOST('email');
    $mobile = getPOST('mobile');
    $photo = '';

    if('' == $account)
    {
        showSystemMessage('请填写账号', array());
        exit;
    } else {
        $account = $db->escape(htmlspecialchars($account));
        $checkAccount = 'select `account` from `'.DB_PREFIX.'admin` where `account`=\''.$account.'\'';
        if($db->fetchRow($checkAccount))
        {
            showSystemMessage('该账号已被注册，请使用其他账号进行注册', array());
            exit;
        }
    }

    if('' == $password)
    {
        showSystemMessage('请填写密码', array());
        exit;
    } else {
        $password = md5($password.PASSWORD_END);
    }

    if(0 >= $roleId)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    if('' == $name)
    {
        $name = $account;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if('' == $sex)
    {
        $sex = 'M';
    } else {
        if(strpos('M|F', $sex) !== false)
        {
            $sex = $db->escape($sex);
        } else {
            $sex = 'M';
        }
    }

    if('' == $email)
    {
        showSystemMessage('电子邮箱不能为空', array());
        exit;
    } else {
        $email = $db->escape(htmlspecialchars($email));
        $checkEmail = 'select `account` from `'.DB_PREFIX.'admin` where `email`=\''.$email.'\'';
        if($db->fetchRow($checkEmail))
        {
            showSystemMessage('电子邮箱已被占用，请使用其他邮箱', array());
            exit;
        }
    }

    if('' == $mobile)
    {
        showSystemMessage('手机号码不能为空', array());
        exit;
    } else {
        $mobile = $db->escape(htmlspecialchars($mobile));
        $checkMobile = 'select `account` from `'.DB_PREFIX.'admin` where `mobile`=\''.$mobile.'\'';
        if($db->fetchRow($checkMobile))
        {
            showSystemMessage('手机号码已被占用，请使用其他号码', array());
            exit;
        }
    }

    if(isset($_FILES['photo']))
    {
        $photo = upload($_FILES['photo']);
        if($photo['error'] == 0)
        {
            $photo = $photo['msg'];
        } else {
            showSystemMessage($photo['msg'], array());
            exit;
        }
    }

    $addAdmin  = 'insert into `'.DB_PREFIX.'admin` (`account`,`password`,`roleId`,`name`,`sex`,`email`,`mobile`';
    $addAdmin .= ',`photo`) values (\''.$account.'\',\''.$password.'\','.$roleId.',\''.$name.'\',\''.$sex.'\',';
    $addAdmin .= '\''.$email.'\',\''.$mobile.'\',\''.$photo.'\')';
    if($db->insert($addAdmin))
    {
        showSystemMessage('新增管理员成功', array(array('alt'=>'查看管理员列表', 'link'=>'adminUser.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

//编辑管理员
if('edit' == $opera)
{
    if(!checkPurview('pur_admin_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $account = getPOST('account');
    $password = getPOST('password');
    $roleId = getPOST('roleId');
    $roleId = intval($roleId);
    $name = getPOST('name');
    $sex = getPOST('sex');
    $email = getPOST('email');
    $mobile = getPOST('mobile');
    $photo = '';

    if('' == $account)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $account = $db->escape(htmlspecialchars($account));
    }

    if('' != $password)
    {
        $password = md5($password.PASSWORD_END);
    }

    if(0 >= $roleId)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    if('' == $name)
    {
        $name = $account;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if('' == $sex)
    {
        $sex = 'M';
    } else {
        if(strpos('M|F', $sex) !== false)
        {
            $sex = $db->escape($sex);
        } else {
            $sex = 'M';
        }
    }

    if('' == $email)
    {
        showSystemMessage('电子邮箱不能为空', array());
        exit;
    } else {
        $email = $db->escape(htmlspecialchars($email));
        $checkEmail = 'select `account` from `'.DB_PREFIX.'admin` where `email`=\''.$email.'\' and `account`<>\''.$account.'\'';
        if($db->fetchRow($checkEmail))
        {
            showSystemMessage('电子邮箱已被占用，请使用其他邮箱', array());
            exit;
        }
    }

    if('' == $mobile)
    {
        showSystemMessage('手机号码不能为空', array());
        exit;
    } else {
        $mobile = $db->escape(htmlspecialchars($mobile));
        $checkMobile = 'select `account` from `'.DB_PREFIX.'admin` where `mobile`=\''.$mobile.'\' and `account`<>\''.$account.'\'';
        if($db->fetchRow($checkMobile))
        {
            showSystemMessage('手机号码已被占用，请使用其他号码', array());
            exit;
        }
    }

    if(isset($_FILES['photo']))
    {
        $photo = upload($_FILES['photo']);
        if($photo['error'] == 0)
        {
            $photo = $photo['msg'];
        } else {
            showSystemMessage($photo['msg'], array());
            exit;
        }
    }

    $updateAdmin  = 'update `'.DB_PREFIX.'admin` set `name`=\''.$name.'\',';
    $updateAdmin .= '`roleId`='.$roleid.',`sex`=\''.$sex.'\',`email`=\''.$email.'\',`mobile`=\''.$mobile.'\'';
    if('' != $photo)
    {
        $updateAdmin .= ',`photo`=\''.$photo.'\'';
    }

    if('' != $password)
    {
        $updateAdmin .= ',`password`=\''.$password.'\'';
    }
    $updateAdmin .= ' where `account`=\''.$account.'\' limit 1';

    if($db->update($updateAdmin))
    {
        showSystemMessage('修改管理员成功', array(array('alt'=>'查看管理员列表', 'link'=>'adminUser.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

if('list' == $act)
{
    if(!checkPurview('pur_admin_list', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getAdmins = 'select `account`,`name`,`mobile`,`email`,`roleId` from `'.DB_PREFIX.'admin`';
    $admins = $db->fetchAll($getAdmins);

    assign('admins', $admins);
}

if('add' == $act)
{
    if(!checkPurview('pur_admin_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getRoles = 'select `id`,`name` from `'.DB_PREFIX.'adminRole` where `id`<>1';
    $roles = $db->fetchAll($getRoles);

    assign('roles', $roles);
}

if('edit' == $act)
{
    if(!checkPurview('pur_admin_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $account = getGET('account');

    if('' == $account)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $account = $db->escape(htmlspecialchars($account));
    }

    $getRoles = 'select `id`,`name` from `'.DB_PREFIX.'adminRole` where `id`<>1';
    $roles = $db->fetchAll($getRoles);

    assign('roles', $roles);

    $getAdmin  = 'select `account`,`name`,`sex`,`email`,`mobile`,`photo`,`roleId` from `'.DB_PREFIX.'admin` ';
    $getAdmin .= 'where `account`=\''.$account.'\' limit 1';
    $admin = $db->fetchRow($getAdmin);

    if($admin)
    {
        assign('admin', $admin);
    } else {
        showSystemMessage('该管理员不存在或者已被删除', array());
        exit;
    }
}

if('delete' == $act)
{
    if(!checkPurview('pur_admin_delete', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $account = getGET('account');

    if('' == $account)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $account = $db->escape(htmlspecialchars($account));
    }

    $getAdmin = 'select `photo`,`roleId` from `'.DB_PREFIX.'admin` where `account`=\''.$account.'\' limit 1';
    $admin = $db->fetchRow($getAdmin);

    if($admin['roleId'] === 1)
    {
        showSystemMessage('超级管理员不允许被删除', array());
        exit;
    }

    if($admin['photo'] != '')
    {
        unlink('../'.$admin['photo']);
    }

    $deleteAdmin = 'delete from `'.DB_PREFIX.'admin` where `account`=\''.$account.'\' limit 1';
    if($db->delete($deleteAdmin))
    {
        showSystemMessage('删除管理员成功', array(array('alt'=>'查看管理员列表', 'link'=>'adminUser.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

assign('act', $act);
assign('subTitle', '管理员管理');
$smarty->display('adminUser.phtml');
