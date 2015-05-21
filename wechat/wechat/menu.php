<?php
/*
 * 自定义菜单功能
 * @author winsen
 * @version 1.0.0
 * @date 2014-11-18
 */
include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}

$action = 'add|edit|list|delete';
$operation = 'post|save|remove';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));

if('save' == $opera)
{
    header("Content-Type: text/html;charset=utf-8");
    $response = array('msg' => '', 'error' => 1, 'data' => null);

    $item = array();
    $data = getPOST('data');
    if($data == '' || count($data) == 0) {
        $response['msg'] = $lang['warning']['param_error'];
    } else {
        $name = $data['name'];
        $type = $data['type'];
        $parentId = intval($data['parentId']);
        $id = intval($data['id']);
        $key = $data['key'];
        $path = '';

        if($name == '') {
            $response['msg'] = $lang['warning']['menu_name_empty'];
        } else {
            $name = $db->escape($name);
        }

        if($type == '') {
            $response['msg'] = $lang['warning']['param_error'];
        } else {
            if($type != 'click' && $type != 'view') {
                $type = 'view';
            } else {
                $type = $db->escape($type);
            }
        }

        if($key == '') {
            $response['msg'] = $lang['warning']['menu_key_empty'];
        } else {
            $key = $db->escape($key);
        }

        if($parentId > 0) {
            $checkMenu = 'select `id`,`path` from `'.$db_prefix.'menu` where `publicAccount`=\''.$_SESSION['public_account'].'\' and `id`='.$parentId;
            $parent = $db->fetchRow($checkMenu);
            if($parent['id'] > 0)
            {
                $path = $parent['path'].',';
                $parentId = $parent['id'];
            } else {
                $parentId = 0;
            }
        } else {
            $parentId = 0;
        }

        if($response['msg'] == '')
        {
            $sql = '';
            if($id > 0) {
                $checkMenu = 'select `id` from `'.$db_prefix.'menu` where `publicAccount`=\''.$_SESSION['public_account'].'\' and `id`='.$id;
                $id = $db->fetchOne($checkMenu);

                if($id > 0) {
                    $item['name'] = $name;
                    $item['id'] = $id;
                    $item['parentId'] = $parentId;
                    $item['type'] = $type;
                    $item['key'] = $key;

                    $sql = 'update `'.$db_prefix.'menu` set `name`=\'%s\',`key`=\'%s\',`type`=\'%s\',`parentId`=%d,`path`=\'%s\' where `id`=%d';
                    $sql = sprintf($sql, $name, $key, $type, $parentId, $path.$id, $id);

                    if($db->update($sql))
                    {
                        $response['msg'] = $lang['warning']['menu_save_success'];
                        $response['error'] = 0;
                        $response['data'] = $item;
                    } else {
                        $response['msg'] = $lang['warning']['menu_save_fail'];
                    }
                } else {
                    $response['msg'] = $lang['warning']['param_error'];
                }
            } else {
                $sql = 'insert into `'.$db_prefix.'menu` (`publicAccount`,`name`,`key`,`type`,`parentId`,`path`) values (\'%s\',\'%s\',\'%s\',\'%s\',%d, \'%s\')';
                $sql = sprintf($sql, $_SESSION['public_account'], $name, $key, $type, $parentId, '');

                if($db->insert($sql))
                {
                    $id = $db->getLastId();
                    $updatePath = 'update `'.$db_prefix.'menu` set `path`=\''.$path.$id.'\' where `id`='.$id;
                    $db->update($updatePath);

                    $item['name'] = $name;
                    $item['id'] = $id;
                    $item['parentId'] = $parentId;
                    $item['type'] = $type;
                    $item['key'] = $key;
                    $response['data'] = $item;
                    $response['error'] = 0;

                    $response['msg'] = $lang['warning']['menu_save_success'];
                } else {
                    $response['msg'] = $lang['warning']['menu_save_fail'];
                }
            }
        }
    }

    echo json_encode($response);
    exit;
}

if('remove' == $opera)
{
    $id = intval(getPOST('id'));
    $response = array('error'=>1, 'msg'=>'');

    $checkMenu = 'select `id` from `'.$db_prefix.'menu` where `id`='.$id;
    if($db->fetchOne($checkMenu))
    {
        $checkChild = 'select count(*) from `'.$db_prefix.'menu` where `parentId`='.$id;
        if(!$db->fetchOne($checkChild))
        {
            $deleteMenu = 'delete from `'.$db_prefix.'menu` where `id`='.$id;

            if($db->delete($deleteMenu))
            {
                $response['msg'] = $lang['warning']['delete_menu_success'];
                $response['error'] = 0;
            } else {
                $response['msg'] = $lang['warning']['delete_menu_fail'];
            }
        } else {
            $response['msg'] = $lang['warning']['has_submenu'];
        }
    } else {
        $response['msg'] = $lang['warning']['param_error'];
    }

    echo json_encode($response);
    exit;
}

if('post' == $opera)
{
    $response = array('msg'=>'');
    $getMenu = 'select `id`,`name`,`key`,`type` from `'.$db_prefix.'menu` where `parentId`=0 and `publicAccount`=\''.$_SESSION['public_account'].'\'';
    $menus = $db->fetchAll($getMenu);

    if(!$menus)
    {
        $response['msg'] = $lang['warning']['no_menus'];
    } else {
        //构造格式化数据
        $format = array();
        foreach($menus as $item)
        {
            $getChildren = 'select `name`,`type`,`key` from `'.$db_prefix.'menu` where `parentId`='.$item['id'];
            $children = $db->fetchAll($getChildren);

            if($children)
            {
                $subButton = array();

                foreach($children as $button)
                {
                    if($button['type'] == 'click')
                    {
                        $subButton[] = array('type'=>$button['type'], 'name'=>urlencode($button['name']), 'key'=>urlencode($button['key']));
                    } else {
                        $subButton[] = array('type'=>$button['type'], 'name'=>urlencode($button['name']), 'url'=>urlencode($button['key']));
                    }

                }
                $format[] = array('name'=>urlencode($item['name']), 'sub_button'=>$subButton);
            } else {
                if($item['type'] == 'click')
                {
                    $format[] = array('type'=>$item['type'], 'name'=>urlencode($item['name']), 'key'=>urlencode($item['key']));
                } else {
                    $format[] = array('type'=>$item['type'], 'name'=>urlencode($item['name']), 'url'=>urlencode($item['key']));
                }
            }
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

            if($accessToken)
            {
                $updateAccessToken = 'update `'.$db_prefix.'publicAccount` set `accessToken`=\''.$accessToken.'\',`expireTime`='.(time()+7200).' where `account`=\''.$_SESSION['public_account'].'\'';
                $db->update($updateAccessToken);
                //2.发送创建菜单请求
                $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$accessToken;
                $curl = curl_init();
                $this_header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
                curl_setopt($curl, CURLOPT_HTTPHEADER, $this_header);
                curl_setopt($curl, CURLOPT_URL, $url);
//                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(json_encode(array('button'=>$format))));
                if( defined('CURL_SSLVERSION_TLSv1') ) {
                    curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
                }
                $data = curl_exec($curl);
                curl_close($curl);

                $data = json_decode($data);
                //3.判断状态码
                if($data->errcode == 0)
                {
                    $response['msg'] = $lang['warning']['create_menu_success'];
                } else {
                    $response['msg'] = $errors[$data->errcode];
                }
            } else {
                $response['msg'] = $lang['warning']['get_access_token_fail'];
            }
        } else {
            $response['msg'] = $lang['warning']['param_error'];
        }
    }

//  OAuth url https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect
    echo json_encode($response);
    exit;
}

if('list' == $act)
{
    $getMenu = 'select `id`,`name`,`key`,`type`,`parentId` from `'.$db_prefix.'menu` where `publicAccount`=\''.$_SESSION['public_account'].'\' order by `path` ASC';
    $menu = $db->fetchAll($getMenu);

    if($menu == '')
    {
        $menu = array();
    }

    $menus = array();
    foreach($menu as $key=>$m)
    {
        $menus[$m['id']] = $m;
    }

    if(count($menus) == 0)
    {
        $menus = '';
    }

    $smarty->assign('menus', json_encode($menus));
}

$smarty->assign('act', $act);
$smarty->display('menuManager.phtml');
