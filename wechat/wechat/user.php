<?php
include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}
$action = 'remark|list|move|sync';
$operation = 'remark|move';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));

if( 'move' == $opera ) {
    $id = intval(getPOST('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }
    $groupId = getPOST('groupId');

    $getUser = 'select groupId, openId from '.$db_prefix.'user where id = '.$id;
    $user = $db->fetchRow($getUser);
    if( empty($user) ) {
        showSystemMessage('该用户不存在');
    }
    $openId = $user['openId'];
    $old_groupId = $user['groupId'];

    $getWechatId = 'select wechatId from '.$db_prefix.'group where id = '.$groupId;
    $wechatId = $db->fetchOne($getWechatId);

    $data = json_encode(array(
        'openid' => $openId,
        'to_groupid' => intval($wechatId)
    ));
    //发送请求
    //1.获得access_token
    $getInfo = 'select `appID`,`appsecret`,`expireTime`,`accessToken` from `'.$db_prefix.'publicAccount` where `account`=\''.$_SESSION['public_account'].'\'';
    $info = $db->fetchRow($getInfo);

    if($info)
    {
        $accessToken = '';
        if($info['expireTime'] >= time())
        {
            $accessToken = $info['accessToken'];
        } else {
            $accessToken = getAccessToken($info['appID'], $info['appsecret']);
        }

        if($accessToken)
        {
            $updateAccessToken = 'update `'.$db_prefix.'publicAccount` set `accessToken`=\''.$accessToken.'\',`expireTime`='.(time()+7200).' where `account`=\''.$_SESSION['public_account'].'\'';
            $db->update($updateAccessToken);
            //2.发送更新备注请求
            $url = 'https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token='.$accessToken;
            $curl = curl_init();
            $this_header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this_header);
            curl_setopt($curl, CURLOPT_URL, $url);
//                curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode($data));
            if( defined('CURL_SSLVERSION_TLSv1') ) {
                curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            }
            $data = curl_exec($curl);
            curl_close($curl);

            $data = json_decode($data);
            //3.判断状态码
            if($data->errcode == 0)
            {
                $updateGroupId = 'update '.$db_prefix.'user set groupId = \''.$groupId.'\' where id = '.$id.' limit 1';
                if( $db->update($updateGroupId) ) {
                    $updateOldGroup = 'update '.$db_prefix.'group set count = count - 1 where id = '.$old_groupId.' limit 1';
                    $db->update($updateOldGroup);
                    $updateNewGroup = 'update '.$db_prefix.'group set count = count + 1 where id = '.$groupId.' limit 1';
                    $db->update($updateNewGroup);
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

if( 'remark' == $opera ) {
    $id = intval(getPOST('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }
    $remark = getPOST('remark');
    if( empty($remark) ) {
        showSystemMessage('备注不能为空');
    }
    $remark = $db->escape(htmlspecialchars($remark));
    $getOpenId = 'select openId from '.$db_prefix.'user where id = '.$id;
    $openId = $db->fetchOne($getOpenId);
    if( empty($openId) ) {
        showSystemMessage('该用户不存在');
    }
    $data = json_encode(array(
        'openid' => $openId,
        'remark' => urlencode($remark)
    ));
    //发送请求
    //1.获得access_token
    $getInfo = 'select `appID`,`appsecret`,`expireTime`,`accessToken` from `'.$db_prefix.'publicAccount` where `account`=\''.$_SESSION['public_account'].'\'';
    $info = $db->fetchRow($getInfo);

    if($info)
    {
        $accessToken = '';
        if($info['expireTime'] >= time())
        {
            $accessToken = $info['accessToken'];
        } else {
            $accessToken = getAccessToken($info['appID'], $info['appsecret']);
        }

        if($accessToken)
        {
            $updateAccessToken = 'update `'.$db_prefix.'publicAccount` set `accessToken`=\''.$accessToken.'\',`expireTime`='.(time()+7200).' where `account`=\''.$_SESSION['public_account'].'\'';
            $db->update($updateAccessToken);
            //2.发送更新备注请求
            $url = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token='.$accessToken;
            $curl = curl_init();
            $this_header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this_header);
            curl_setopt($curl, CURLOPT_URL, $url);
//                curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode($data));
            if( defined('CURL_SSLVERSION_TLSv1') ) {
                curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            }
            $data = curl_exec($curl);
            curl_close($curl);

            $data = json_decode($data);
            //3.判断状态码
            if($data->errcode == 0)
            {
                $updateRemark = 'update '.$db_prefix.'user set remark = \''.$remark.'\' where id = '.$id.' limit 1';
                if( $db->update($updateRemark) ) {
                    $response['msg'] = '更新备注成功';
                } else {
                    $response['msg'] = '更新备注失败';
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

    $getUsers = 'select u.*, g.name as groupName from '.$db_prefix.'user as u ';
    $getUsers .= ' left join '.$db_prefix.'group as g on u.groupId = g.id';
    $getUsers .= ' where u.publicAccount = \''.$_SESSION['public_account'].'\' order by unsubscribed asc;';
    $users = $db->fetchAll($getUsers);
    if( $users ) {
        foreach ($users as $key => $user) {
            $users[$key]['addTime'] = date('Y-m-s H:i:s', $user['addTime']);
            if ($user['unsubscribed'] == 0) {
                $users[$key]['unsubscribed'] = '是';
            } else {
                $users[$key]['unsubscribed'] = '否';
            }

        }
    }
    assign('users', $users);
}

if( 'move' == $act ) {
    $id = intval(getGET('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }
    $getUser = 'select id, groupId from '.$db_prefix.'user where id = '.$id;
    $user = $db->fetchRow($getUser);
    assign('user', $user);

    $getGroups = 'select id, name from '.$db_prefix.'group where publicAccount = \''.$_SESSION['public_account'].'\' order by wechatId asc';
    $groups = $db->fetchAll($getGroups);
    assign('groups', $groups);
}

if( 'remark' == $act ) {
    $id = intval(getGET('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }
    $getUser = 'select id, remark from '.$db_prefix.'user where id = '.$id.' limit 1';
    $user = $db->fetchRow($getUser);
    assign('user', $user);

}

if( 'sync' == $act ) {
    $id = intval(getGET('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    $getUser = 'select unsubscribed, openId, unionid from '.$db_prefix.'user where id = '.$id.' limit 1';
    $user = $db->fetchRow($getUser);
    if( $user['unsubscribed'] == 1 ) {
        showSystemMessage('用户已取消关注');
    }

    //发送请求
    //1.获得access_token
    $getInfo = 'select `appID`,`appsecret`,`expireTime`,`accessToken` from `'.$db_prefix.'publicAccount` where `account`=\''.$_SESSION['public_account'].'\'';
    $info = $db->fetchRow($getInfo);

    if($info)
    {
        $accessToken = '';
        if($info['expireTime'] >= time())
        {
            $accessToken = $info['accessToken'];
        } else {
            $accessToken = getAccessToken($info['appID'], $info['appsecret']);
        }

        if($accessToken) {
            $updateAccessToken = 'update `' . $db_prefix . 'publicAccount` set `accessToken`=\'' . $accessToken . '\',`expireTime`=' . (time() + 7200) . ' where `account`=\'' . $_SESSION['public_account'] . '\'';
            $db->update($updateAccessToken);

            $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$accessToken;
            $params = '&openid='.$user['openId'].'&lang=zh_CN';
            $url .= $params;
            $curl = curl_init();
            $this_header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this_header);
            curl_setopt($curl, CURLOPT_URL, $url);
//                curl_setopt($curl, CURLOPT_HEADER, 0);
//            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode($data));
            if( defined('CURL_SSLVERSION_TLSv1') ) {
                curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            }
            $data = curl_exec($curl);
            curl_close($curl);

            $data = json_decode($data);

            //3.判断状态码
            if( empty($data->errcode) )
            {
                $syncUser = array();
                foreach( $userFormat as $key => $value ) {
                    if( property_exists($data, $key)  )
                        $syncUser[$key] = $data->$key;
                }
                assign('syncUser', $syncUser);
                assign('userFormat', $userFormat);
            } else {
                $response['msg'] = $errors[$data->errcode];
            }
        } else {
            $response['msg'] = $lang['warning']['get_access_token_fail'];
        }
    } else {
        $response['msg'] = $lang['warning']['param_error'];
    }
    !empty($response['msg']) ? showSystemMessage($response['msg']) : true;
}

assign('act', $act);
$smarty->display('user.phtml');