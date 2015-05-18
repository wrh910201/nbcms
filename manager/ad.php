<?php
include 'library/init.inc.php';

back_base_init();
assign('subTitle', '广告管理');

$action = 'add|edit|list|delete';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));
if('' == $act)
{
    $act = 'list';
}

//新增广告
if('add' == $opera)
{
    if(!checkPurview('pur_ad_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }
    $alt = getPOST('alt');
    $url = getPOST('url');
    $startTime = getPOST('startTime');
    $endTime = getPOST('endTime');
    $adPositionId = getPOST('adPositionId');
    $adPositionId = intval($adPositionId);
    $img = '';


    if(isset($_FILES['img']))
    {
        $img = upload($_FILES['img']);
        if($img['error'] == 0)
        {
            $img = $img['msg'];
        } else {
            showSystemMessage($img['msg'], array());
            exit;
        }
    }

    if('' == $img)
    {
        showSystemMessage('请上传图片', array());
        exit;
    }

    if(0 >= $adPositionId)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    if('' == $startTime)
    {
        $startTime = time();
    } else {
//        if(preg_match('/^\d{4}-\d{1,2}-\d{1,2}\ \d{1,2}:\d{1,2}:\d{1,2}$/', $startTime))
        if(preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $startTime))
        {
//            $dateTime = explode(' ', $startTime);
//            $date = explode('-', $dateTime[0]);
//            $time = explode(':', $dateTime[1]);
            $date = explode('-', $startTime);

            $startTime = mktime(null, null, null, $date[1], $date[2], $date[0]);
        } else {
            showSystemMessage('开始时间格式不正确', array());
            exit;
        }
    }

    if('' == $endTime)
    {
        $endTime = time()+3600*24*7;
    } else {
//        if(preg_match('/^\d{4}-\d{1,2}-\d{1,2}\ \d{1,2}:\d{1,2}:\d{1,2}$/', $endTime))
        if(preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $endTime))
        {
//            $dateTime = explode(' ', $endTime);
//            $date = explode('-', $dateTime[0]);
//            $time = explode(':', $dateTime[1]);
            $date = explode('-', $endTime);

            $endTime = mktime(null, null, null, $date[1], $date[2], $date[0]);
        } else {
            showSystemMessage('结束时间格式不正确', array());
            exit;
        }
    }

    if('' == $alt)
    {
        showSystemMessage('出于SEO考虑，请务必填写替换文字', array());
        exit;
    } else {
        $alt = $db->escape(htmlspecialchars($alt));
    }

    if('' == $url)
    {
        $url = 'index.php';
    } else {
        $url = $db->escape(htmlspecialchars($url));
    }

    $addAd  = 'insert into `'.DB_PREFIX.'ad` (`img`,`alt`,`url`,`startTime`,`endTime`,`adPositionId`) values (';
    $addAd .= '\''.$img.'\',\''.$alt.'\',\''.$url.'\','.$startTime.','.$endTime.','.$adPositionId.')';

    if($db->insert($addAd))
    {
        showSystemMessage('增加广告成功', array(array('alt'=>'查看广告列表', 'link'=>'ad.php')));
        exit;
    } else {
        showSystemMessage($db->errno.':系统繁忙，请稍后再试', array());
        exit;
    }
}

//修改广告
if('edit' == $opera)
{
    if(!checkPurview('pur_ad_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $alt = getPOST('alt');
    $url = getPOST('url');
    $startTime = getPOST('startTime');
    $endTime = getPOST('endTime');
    $adPositionId = getPOST('adPositionId');
    $adPositionId = intval($adPositionId);
    $img = '';
    $id = getPOST('id');
    $id = intval($id);


    if(0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $response = upload_with_choice($_FILES['img'], 'image');
    if($response['error']) {
        showSystemMessage($response['msg'], array());
        exit;
    } else {
        $img = $response['msg'];
        if( $img != '' ) {
            $type = $response['type'];
        }

    }

    if(0 >= $adPositionId)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    if('' == $startTime)
    {
        $startTime = time();
    } else {
//        if(preg_match('/^\d{4}-\d{1,2}-\d{1,2}\ \d{1,2}:\d{1,2}:\d{1,2}$/', $startTime))
        if(preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $startTime))
        {
//            $dateTime = explode(' ', $startTime);
//            $date = explode('-', $dateTime[0]);
//            $time = explode(':', $dateTime[1]);
            $date = explode('-', $startTime);

            $startTime = mktime(null, null, null, $date[1], $date[2], $date[0]);
        } else {
            showSystemMessage('开始时间格式不正确', array());
            exit;
        }
    }

    if('' == $endTime)
    {
        $endTime = time()+3600*24*7;
    } else {
//        if(preg_match('/^\d{4}-\d{1,2}-\d{1,2}\ \d{1,2}:\d{1,2}:\d{1,2}$/', $endTime))
        if(preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $endTime))
        {
//            $dateTime = explode(' ', $endTime);
//            $date = explode('-', $dateTime[0]);
//            $time = explode(':', $dateTime[1]);
            $date = explode('-', $endTime);

            $endTime = mktime(null, null, null, $date[1], $date[2], $date[0]);
        } else {
            showSystemMessage('结束时间格式不正确', array());
            exit;
        }
    }

    if('' == $alt)
    {
        showSystemMessage('出于SEO考虑，请务必填写替换文字', array());
        exit;
    } else {
        $alt = $db->escape(htmlspecialchars($alt));
    }

    if('' == $url)
    {
        $url = 'index.php';
    } else {
        $url = $db->escape(htmlspecialchars($url));
    }

    $getAd = 'select `img` from `'.DB_PREFIX.'ad` where `id`='.$id.' limit 1';
    $ad = $db->fetchRow($getAd);

    if($ad)
    {
        $updateAd  = 'update `'.DB_PREFIX.'ad` set `alt`=\''.$alt.'\',`url`=\''.$url.'\',';
        $updateAd .= '`startTime`='.$startTime.',`endTime`='.$endTime.',`adPositionId`='.$adPositionId;
        $updateAd .= ( $img != '' ) ? ', `img`=\''.$img.'\'' : '';
        $updateAd .= ' where `id`='.$id.' limit 1';

        if($db->update($updateAd))
        {
            unlink('../'.$ad['img']);
            showSystemMessage('更新广告成功', array(array('alt'=>'查看广告列表', 'link'=>'ad.php')));
            exit;
        } else {
            showSystemMessage('系统繁忙，请稍后再试', array());
            exit;
        }
    } else {
        showSystemMessage('要修改的广告已被删除或不存在', array(array('alt'=>'查看广告列表', 'link'=>'ad.php')));
        exit;
    }
}

if('list' == $act)
{
    if(!checkPurview('pur_ad_list', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getAds  = 'select a.`id`,a.`img`,a.`clickTime`,a.`url`,a.`alt`,a.`startTime`,a.`endTime`,b.`name`';
    $getAds .= ' from `'.DB_PREFIX.'ad` as a ';
    $getAds .= ' left join `'.DB_PREFIX.'adPosition` as b on a.`adPositionId`=b.`id` order by a.`adPositionId`';

    $ads = $db->fetchAll($getAds);
    foreach($ads as $key => $ad) {
        $ads[$key]['startTime'] = date('Y-m-d', $ad['startTime']);
        $ads[$key]['endTime'] = date('Y-m-d', $ad['endTime']);
    }

    assign('ads', $ads);
}

if('add' == $act)
{
    if(!checkPurview('pur_ad_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getAdPositions = 'select `id`,`name` from `'.DB_PREFIX.'adPosition`';
    $adPositions = $db->fetchAll($getAdPositions);
    assign('adPositions', $adPositions);
}

if('edit' == $act)
{
    if(!checkPurview('pur_ad_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');
    $id = intval($id);
    if(0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $getAdPositions = 'select `id`,`name` from `'.DB_PREFIX.'adPosition`';
    $adPositions = $db->fetchAll($getAdPositions);
    assign('adPositions', $adPositions);

    $getAd  = 'select `id`,`img`,`alt`,`url`,`startTime`,`endTime`,`adPositionId` from `'.DB_PREFIX.'ad` ';
    $getAd .= 'where `id`='.$id.' limit 1';

    $ad = $db->fetchRow($getAd);

    if($ad)
    {
        $ad['startTime'] = date('Y-m-d', $ad['startTime']);
        $ad['endTime'] = date('Y-m-d', $ad['endTime']);
        assign('ad', $ad);
    } else {
        showSystemMessage('该广告不存在或已被删除', array());
        exit;
    }
}

if('delete' == $act)
{
    if(!checkPurview('pur_ad_delete', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');
    $id = intval($id);

    if(0 >= $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $getAd = 'select `img` from `'.DB_PREFIX.'ad` where `id`='.$id.' limit 1';
    $ad = $db->fetchRow($getAd);
    if($ad)
    {
        unlink('../'.$ad['img']);
    }

    $deleteAd = 'delete from `'.DB_PREFIX.'ad` where `id`='.$id.' limit 1';

    if($db->delete($deleteAd))
    {
        showSystemMessage('删除广告成功', array());
        exit;
    } else {
        showSystemMessage($db->errno.':系统繁忙，请稍后再试', array());
        exit;
    }
}

assign('act', $act);
$smarty->display('ad.phtml');
