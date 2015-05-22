<?php
include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}
$action = 'list|add|edit|check|delete';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));

if( 'add' == $opera ) {
    $kf_account = getPOST('kf_account');
    $kf_nick = getPOST('kf_nick');
    $password = getPOST('password');

    if( empty($kf_account) ) {
        showSystemMessage('客服帐号不能为空');
    } else {
//        $kf_account = $db->escape(htmlspecialchars($kf_account)).'@'.$_SESSION['public_account'];
//        $kf_account = $db->escape(htmlspecialchars($kf_account)).'@gh_21270838f9a5';
        $kf_account = $db->escape(htmlspecialchars($kf_account)).'@wrh32709394';
    }
    if( empty($kf_nick) ) {
        $kf_nick = $kf_account;
    }
    if( empty($password) ) {
        showSystemMessage('密码不能为空');
    } else {
        $password = $db->escape(htmlspecialchars($password));
        $password = md5($password);
    }
    $data = json_encode(array(
        'kf_account' => $kf_account,
        'nickname' => urlencode($kf_nick),
        'password' => $password,
    ));
    //发送请求
    //1.获得access_token
    $getInfo = 'select `appID`,`appsecret`,`expireTime`,`accessToken` from `'.$db_prefix.'publicAccount` where `account`=\''.$_SESSION['public_account'].'\'';
    $info = $db->fetchRow($getInfo);

    if($info)
    {
        $accessToken = '';
        if($info['expireTime'] >= time() )
        {
            $accessToken = $info['accessToken'];
        } else {
            $accessToken = getAccessToken($info['appID'], $info['appsecret']);
        }

        if($accessToken) {
            $updateAccessToken = 'update `' . $db_prefix . 'publicAccount` set `accessToken`=\'' . $accessToken . '\',`expireTime`=' . (time() + 7200) . ' where `account`=\'' . $_SESSION['public_account'] . '\'';
            $db->update($updateAccessToken);

            $url = 'https://api.weixin.qq.com/customservice/kfaccount/add?access_token='.$accessToken;
            $data = rawPost($url, $data);
            $data = json_decode($data);
            if($data->errcode == 0)
            {
                $addKfAccount = 'insert into '.$db_prefix.'wx_kfAccount (id, kf_account, kf_headimgurl, kf_id, kf_nick, password, auto_accept, addTime, publicAccout) values';
                $addKfAccount .= ' (null, \''.$kf_account.'\', \'\', \'\', \''.$kf_nick.'\', \''.$password.'\', 0, \''.time().'\', \''.$_SESSION['public_account'].'\');';
                if( $db->insert($addKfAccount) ) {
                    $response['msg'] = '移动分组成功';
                } else {
                    $response['msg'] = '移动分组失败';
                }
            } else {
                $response['msg'] = $errors[$data->errcode];
            }
        } else {
            $response['msg'] = $lang['warning']['get_access_token_fail'];
        }
    } else {
        $response['msg'] = $lang['warning']['param_error'];
    }
    showSystemMessage($response['msg']);
}


if( 'list' == $act ) {

}

if( 'add' == $act ) {
    assign('wechatAccount', $_SESSION['public_account']);
}

assign('act', $act);
$smarty->display('custService.phtml');