<?php
include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}
$action = 'add|edit|list|delete|sync';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));

if( 'add' == $opera ) {
    $name = getPOST('name');
    if( empty($name) ) {
        showSystemMessage('分组名不能为空');
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }
    //调用接口创建用户分组
    $data = json_encode(array(
        'group' => array(
            'name' => urlencode($name)
        )
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

        if($accessToken) {
            $updateAccessToken = 'update `' . $db_prefix . 'publicAccount` set `accessToken`=\'' . $accessToken . '\',`expireTime`=' . (time() + 7200) . ' where `account`=\'' . $_SESSION['public_account'] . '\'';
            $db->update($updateAccessToken);
            //2.发送创建菜单请求
            $url = 'https://api.weixin.qq.com/cgi-bin/groups/create?access_token='.$accessToken;
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
            if(empty($data->errcode)) {
                $wechatId = $data->group->id;
                $addGroup = 'insert into '.$db_prefix.'group (id, wechatId, name, count, addTime, publicAccount) values ';
                $addGroup .= ' (null, \''.$wechatId.'\', \''.$name.'\', 0, '.time().', \''.$_SESSION['public_account'].'\');';
                if( $db->insert($addGroup) ) {
                    $response['msg'] ='创建分组成功';
                } else {
                    $response['msg'] = '创建分组失败';
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


if( 'edit' == $opera ) {
    $id = intval(getPOST('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }
    $name = getPOST('name');
    if( empty($name) ) {
        showSystemMessage('分组名不能为空');
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }
    $getWechatId = 'select wechatId from '.$db_prefix.'group where id = \''.$id.'\' limit 1';
    $wechatId = $db->fetchOne($getWechatId);
    if( empty($wechatId) ) {
        showSystemMessage('该分组不存在');
    }
    //调用接口创建用户分组
    $data = json_encode(array(
        'group' => array(
            'id' => $wechatId,
            'name' => urlencode($name)
        )
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

        if($accessToken) {
            $updateAccessToken = 'update `' . $db_prefix . 'publicAccount` set `accessToken`=\'' . $accessToken . '\',`expireTime`=' . (time() + 7200) . ' where `account`=\'' . $_SESSION['public_account'] . '\'';
            $db->update($updateAccessToken);
            //2.发送创建菜单请求
            $url = 'https://api.weixin.qq.com/cgi-bin/groups/update?access_token='.$accessToken;
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
            if( $data->errcode == 0 ) {
                $updateGroup = 'update '.$db_prefix.'group set name = \''.$name.'\' where id = '.$id.' limit 1;';
                if( $db->update($updateGroup) ) {
                    $response['msg'] ='修改分组成功';
                } else {
                    $response['msg'] = '修改分组失败';
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
    $getGroups = 'select * from '.$db_prefix.'group where publicAccount = \''.$_SESSION['public_account'].'\' order by addTime asc';
    $groups = $db->fetchAll($getGroups);
    if( $groups ) {
        foreach( $groups as $key => $group ) {
            $groups[$key]['addTime'] = date('Y-m-d H:i:s', $group['addTime']);
        }
    }
    assign('groups', $groups);
}

if( 'add' == $act ) {

}

if( 'edit' == $act ) {
    $id = intval(getGET('id'));
    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }
    $getGroup = 'select * from '.$db_prefix.'group where id = '.$id.' limit 1';
    $group = $db->fetchRow($getGroup);
    assign('group', $group);
}

if( 'delete' == $act ) {
    $wechatId = intval(getGET('id'));
    if($wechatId <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }
    $getId = 'select id from '.$db_prefix.'group where wechatId = \''.$wechatId.'\' limit 1';
    $id = $db->fetchOne($getId);
    if( empty($id) ) {
        showSystemMessage('该分组不存在');
    }
    $data = json_encode(array(
        'group' => array(
            'id' => $wechatId,
        )
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
            //2.发送删除分组请求
            $url = 'https://api.weixin.qq.com/cgi-bin/groups/delete?access_token='.$accessToken;
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
                $deleteGroup = 'delete from '.$db_prefix.'group where id = '.$id.' limit 1';
                if( $db->delete($deleteGroup) ) {
                    $response['msg'] = '删除分组成功';
                } else {
                    $response['msg'] = '删除分组失败';
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

if( 'sync' == $act ) {
    sync_user_group();
}

assign('act', $act);
$smarty->display('userGroup.phtml');