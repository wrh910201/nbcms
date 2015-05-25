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
    //$picUrl = getPOST('picUrl');

    $articleContent = getPOST('articles-content');
    $multi = getPOST('multi');

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
    case 'news':
        if($title == '')
        {
            showSystemMessage($lang['warning']['response_title_empty']);
        } else {
            $title = $db->escape($title);
        }
        if($description == '')
        {
            showSystemMessage($lang['warning']['response_description_empty']);
        } else {
            $title = $db->escape($description);
        }


        $uploadResult = upload($_FILES['picUrls'], 'image');
        if( $uploadResult['error'] != 0 ) {
            showSystemMessage($uploadResult['msg']);
        } else {
            $picUrl = $uploadResult['msg'];
        }

        if( count($multi) > 9 ) {
            showSystemMessage('多图文最大数量为10');
        }

        $addResponse = 'insert into `'.$db_prefix.'response` (`name`,`msgType`,`content`,`picUrl`,`url`,`title`,`description`) values (\'%s\',\'%s\',\'%s\',\'%s\',\'%s\',\'%s\',\'%s\')';
        $addResponse = sprintf($addResponse, $name, $msgType, $articleContent, $picUrl, $url, $title, $description);


        break;
    }

    if($db->insert($addResponse))
    {
        if( $msgType == 'news' ) {
            $mainId = $db->getLastId();
            if (count($multi) > 0) {
                $addMapping = 'insert into ' . $db_prefix . 'newsMapping (mainId, subId) values ';
                foreach ($multi as $value) {
                    if (intval($value) > 0) {
                        $tempSql = $addMapping . ' (\'' . $mainId . '\', \'' . intval($value) . '\');';
                        $db->insert($tempSql);
                    }
                }
            }
        }
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
    $picUrl = '';

    $articleContent = getPOST('articles-content');
    $multi = getPOST('multi');

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
    case 'news':
        if($title == '')
        {
            showSystemMessage($lang['warning']['response_title_empty']);
        } else {
            $title = $db->escape($title);
        }
        if($description == '')
        {
            showSystemMessage($lang['warning']['response_description_empty']);
        } else {
            $title = $db->escape($description);
        }

        $uploadResult = upload_with_choice($_FILES['picUrls'], 'image');
        if( $uploadResult['error'] != 0 ) {
            showSystemMessage($uploadResult['msg']);
        } else {
            $picUrl = $uploadResult['msg'];
        }


        if( count($multi) > 9 ) {
            showSystemMessage('多图文最大数量为10');
        }

        $updateResponse = 'update '.$db_prefix.'response set ';
        $updateResponse .= ' name =\''.$name.'\'';
        $updateResponse .= ' ,title = \''.$title.'\'';
        $updateResponse .= ' ,description = \''.$description.'\'';
        $updateResponse .= ' ,content = \''.$articleContent.'\'';
        $updateResponse .= ' ,url = \''.$url.'\'';
        $updateResponse .= ($picUrl != '' ) ? ' ,picUrl = \''.$picUrl.'\'' : '';
        $updateResponse .= ' where id = '.$id;
        break;
    }


    if($db->update($updateResponse))
    {
        if( $msgType == 'news' ) {

            $deleteMapping = 'delete from '.$db_prefix.'newsMapping where mainId = '.$id;
            $db->delete($deleteMapping);

            $mainId = $id;
            if (count($multi) > 0) {
                $addMapping = 'insert into ' . $db_prefix . 'newsMapping (mainId, subId) values ';
                foreach ($multi as $value) {
                    if (intval($value) > 0) {
                        $tempSql = $addMapping . ' (\'' . $mainId . '\', \'' . intval($value) . '\');';
                        $db->insert($tempSql);
                    }
                }
            }
        }
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
//        if( $val['msgType'] == 'news' ) {
//            echo '<img src="'.img_url_to_wechat($val['picUrl']).'">';exit;
//        }
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

    $getNews = 'select id, name from '.$db_prefix.'response where msgType=\'news\';';
//    echo $getNews;exit;
    $news = $db->fetchAll($getNews);
    assign('news', $news);
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

    $getNews = 'select id, name from '.$db_prefix.'response where msgType=\'news\' and id <> '.$id;
//    echo $getNews;exit;
    $news = $db->fetchAll($getNews);
    assign('news', $news);

    $getSubNews = 'select subId from '.$db_prefix.'newsMapping where mainId = '.$id;
    $subNews = $db->fetchAll($getSubNews);
    assign('subNews', $subNews);
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
                if( $response['msgType'] == 'news' ) {
                    $deleteMapping = 'delete from '.$db_prefix.'newsMapping where mainId = '.$id;
                    $db->delete($deleteMapping);
                }
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
