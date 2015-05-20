<?php
/**
 * 自动回复管理
 * @author winsen
 * @date 2014-12-2
 */
include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}

$action = 'add|edit|list|delete';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));

if('add' == $opera)
{
    $name = getPOST('name');
    $title = getPOST('title');
    $description = getPOST('description');
    $content = getPOST('content');
    $msgType = getPOST('msgType');
    $url = getPOST('url');
    $picUrl = getPOST('picUrl');

    if($msgType == '')
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    if($name == '')
    {
        showSystemMessage($lang['warning']['response_name_empty']);
    }

    $addResponse = '';
    switch($msgType)
    {
    case 'themes':
    case 'text':
        if($content == '')
        {
            showSystemMessage($lang['warning']['response_content_empty']);
        } else {
            $content = $db->escape($content);
        }

        $addResponse = 'insert into `'.$db_prefix.'response` (`name`,`msgType`,`content`) values (\'%s\',\'%s\',\'%s\')';
        $addResponse = sprintf($addResponse, $name, $msgType, $content);
        break;
    }


    if($db->insert($addResponse))
    {
        showSystemMessage($lang['warning']['add_response_success'], array(array('alt'=>'回复列表', 'link'=>'response.php')));
    } else {
        showSystemMessage($lang['warning']['add_response_fail']);
    }
}

if('edit' == $opera)
{
    $id = intval(getPOST('eid'));
    $name = getPOST('name');
    $title = getPOST('title');
    $description = getPOST('description');
    $content = getPOST('content');
    $msgType = getPOST('msgType');
    $url = getPOST('url');
    $picUrl = getPOST('picUrl');

    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    $checkResponse = 'select `id` from `'.$db_prefix.'response` where `id`='.$id;

    if(!$db->fetchRow($checkResponse))
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    if($msgType == '')
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    if($name == '')
    {
        showSystemMessage($lang['warning']['response_name_empty']);
    }

    $addResponse = '';
    switch($msgType)
    {
    case 'themes':
    case 'text':
        if($content == '')
        {
            showSystemMessage($lang['warning']['response_content_empty']);
        } else {
            $content = $db->escape($content);
        }

        $updateResponse = 'update `'.$db_prefix.'response` set `name`=\'%s\',`msgType`=\'%s\',`content`=\'%s\' where `id`=%d';
        $updateResponse = sprintf($updateResponse, $name, $msgType, $content, $id);
        break;
    }


    if($db->update($updateResponse))
    {
        showSystemMessage($lang['warning']['update_response_success'], array(array('alt'=>'回复列表', 'link'=>'response.php')));
    } else {
        showSystemMessage($lang['warning']['update_response_fail']);
    }
}

if('list' == $act)
{
    $getResponse = 'select * from `'.$db_prefix.'response`';
    $responses = $db->fetchAll($getResponse);

    foreach($responses as $k=>$val)
    {
        $val['operation'] = '<a href="response.php?act=edit&id='.$val['id'].'">'.$lang['edit'].'</a>&nbsp;|&nbsp;'.
                            '<a href="javascript:deleteResponse('.$val['id'].');">'.$lang['delete'].'</a>';
        $responses[$k] = $val;
    }

    $smarty->assign('responses', $responses);
}

if('add' == $act)
{
    $xml = file_get_contents('library/themes.xml');
    $xml = simplexml_load_string($xml);

    $smarty->assign('themes', $xml->item);
}

if('edit' == $act)
{
    $id = intval(getGET('id'));

    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    } 

    $getResponse = 'select * from `'.$db_prefix.'response` where `id`='.$id;

    $response = $db->fetchRow($getResponse);

    if(!$response)
    {
        showSystemMessage($lang['warning']['param_error']);
    }

    $xml = file_get_contents('library/themes.xml');
    $xml = simplexml_load_string($xml);

    $smarty->assign('themes', $xml->item);

    $smarty->assign('response', $response);
}

if('delete' == $act)
{
    $id = intval(getGET('id'));

    if($id <= 0)
    {
        showSystemMessage($lang['warning']['param_error']);
    } 

    $getResponse = 'select * from `'.$db_prefix.'response` where `id`='.$id;

    $response = $db->fetchRow($getResponse);

    if($response)
    {
        $checkRule = 'select count(*) from `'.$db_prefix.'rules` where `responseId`='.$id;
        if($db->fetchOne($checkRule))
        {
            showSystemMessage($lang['warning']['response_has_rules']);
        } else {
            $deleteResponse = 'delete from `'.$db_prefix.'response` where `id`='.$id;

            if($db->delete($deleteResponse))
            {
                showSystemMessage($lang['warning']['delete_response_success']);
            } else {
                showSystemMessage($lang['warning']['delete_response_fail']);
            }
        }
    } else {
        showSystemMessage($lang['warning']['param_error']);
    }
}

$smarty->assign('act', $act);
$smarty->display('response.phtml');
