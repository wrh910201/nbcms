<?php

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

if( 'add' == $opera ) {
    $msgtype = getPOST('msgtype');

    switch( $msgtype ) {
        case 'mpnews':
            $mediaId = getPOST('mediaId');
            $is_to_all = getPOST('is_to_all');
            $groupId = getPOST('groupId');

//            $data = array('media_id' => $mediaId);
//            $data = json_encode($data);
//            $url = 'https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=';
//            $data = wechat_request($url, $data, false);
//            var_dump($data);exit;



            //图文是否存在
            $getNews = 'select mediaId from '.$db_prefix.'material where mediaId = \''.$mediaId.'\';';
            $news = $db->fetchOne($getNews);
            if( empty($news) ) {
                showSystemMessage('图文不存在');
            }

            if( $is_to_all == 1 ) {
                $groupId = -1;
            } else {
                $is_to_all = 0;
                $groupId = intval($groupId);
                $getGroup = 'select wechatId, count from '.$db_prefix.'group where wechatId = '.$groupId;
                $group = $db->fetchRow($getGroup);
                if( empty($group) ) {
                    showSystemMessage('分组不存在');
                }
                if( $group['count'] <= 0 ) {
                    showSystemMessage('该分组下无用户');
                }
            }
            $data = array(
                'filter' => array(
                    'is_to_all' => ($is_to_all) == 1 ? true : false,
                    'group_id' => $groupId,
                ),
                'wpnews' => array(
                    'media_id' => $mediaId,
                ),
                'msgtype' => 'mpnews'
            );
            $data = json_encode($data);
            $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=';
            $data = wechat_request($url, $data);
            $addBroadcast = 'insert into '.$db_prefix.'broadcast (msg_id, is_to_all, group_id, msg_type, mediaId) values';
            $addBroadcast .= ' (\''.$data->msg_id.'\', '.$is_to_all.','.$groupId.', \'mpnews\', \''.$mediaId.'\');';
            if( $db->insert($addBroadcast) ) {
                showSystemMessage('群发成功');
            } else {
                showSystemMessage('群发失败');
            }

            break;
    }



}


if( 'list' == $act ) {

}

if( 'add' == $act ) {
    $type = getGET('type');
    if( empty($type) ) {
        $type = 'news';
    }

    switch($type){
        case 'news':
            $getNewsList = 'select mediaId, name from '.$db_prefix.'material where type = \'news\' order by addTime desc';
            $newsList = $db->fetchAll($getNewsList);
            assign('newsList', $newsList);

            $getGroupList = 'select wechatId, name from '.$db_prefix.'group where 1 order by wechatId asc';
            $groupList = $db->fetchAll($getGroupList);
            assign('groupList', $groupList);
            break;
        case 'text':

            break;
        case 'image':

            break;
        case 'voice':

            break;
        case 'video':

            break;
    }


    assign('type', $type);
}

assign('act', $act);
$smarty->display('broadcast.phtml');