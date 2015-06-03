<?php

include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}

$action = 'add|list';
$operation = 'add';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));

if( 'add' == $opera ) {

    $type = getPOST('type');
    if( $type == 'image' ) {
        $name = getPOST('name');
        $picUrl = getPOST('picUrl');

        if (empty($name)) {
            showSystemMessage('素材名称不能为空');
        } else {
            $name = $db->escape(trim($name));
        }

        if (empty($picUrl)) {
            showSystemMessage('请选择图片');
        } else {
            if (!file_exists(realpath('../../' . $picUrl))) {
                showSystemMessage('图片不存在');
            } else {
                $key = 'picUrl';
                $value = $db->escape(trim($picUrl));
            }
        }
    }

    if( $type == 'voice' ) {
        $name = getPOST('name');
        $picUrl = getPOST('picUrl');

        if (empty($name)) {
            showSystemMessage('素材名称不能为空');
        } else {
            $name = $db->escape(trim($name));
        }

        $uploadResult = upload($_FILES['voiceUrl'], 'media');
        $key = 'voiceUrl';
        if( $uploadResult['error'] != 0 ) {
            showSystemMessage($uploadResult['msg']);
        } else {
            $value = $uploadResult['msg'];
        }


    } elseif( $type == 'video' ) {
        $name = getPOST('name');
        $picUrl = getPOST('picUrl');

        if (empty($name)) {
            showSystemMessage('素材名称不能为空');
        } else {
            $name = $db->escape(trim($name));
        }

        $uploadResult = upload($_FILES['videoUrl'], 'media');
        $key = 'videoUrl';
        if( $uploadResult['error'] != 0 ) {
            showSystemMessage($uploadResult['msg']);
        } else {
            $value = $uploadResult['msg'];
        }
    }

    $data = array(
        'type' => $type,
        'media' => realpath('../../'.$value),
    );

    $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=';
    $data = wechat_request($url, $data, false, true);

    $addMedia = 'insert into '.$db_prefix.'media (name, addTime, expiredTime, type, '.$key.', mediaId) ';
    $addMedia .= ' values (\''.$name.'\', \''.$data->created_at.'\',\''.(intval($data->created_at) + 259200).'\',\''.$type.'\',\''.$value.'\',\''.$data->media_id.'\');';
    echo $addMedia;exit;
    if( $db->insert($addMedia) ) {
        showSystemMessage('添加临时素材成功');
    } else {
        showSystemMessage('添加临时素材失败');
    }


}


if( 'list' == $act ) {
    $type = getGET('type');
    $type = empty($type) ? 'image' : $type;
    switch( $type ) {
        case 'image':
            $getMediaList = 'select * from '.$db_prefix.'media where type = \'image\'';
            break;
        case 'voice':
            $getMediaList = 'select * from '.$db_prefix.'media where type = \'voice\'';
            break;
        case 'video':
            $getMediaList = 'select * from '.$db_prefix.'media where type = \'video\'';
            break;
    }

    assign('type', $type);
    $getMediaList .= ' order by addTime desc';
    $mediaList = $db->fetchAll($getMediaList);
    if( $mediaList ) {
        foreach($mediaList as $key => $media) {
            if( $media['expiredTime'] > time() ) {
                $mediaList[$key]['status'] = '正常';
            } else {
                $mediaList[$key]['status'] = '已过期';
            }
            $mediaList[$key]['expiredTime'] = date('Y-m-d H:i:s', $media['expiredTime']);
        }
    }
    assign('mediaList', $mediaList);
}

if( 'add' == $act ) {
    $type = getGET('type');
    $type = empty($type) ? 'image' : $type;
    assign('type', $type);
}


assign('act', $act);
$smarty->display('media.phtml');