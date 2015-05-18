<?php

include 'library/init.inc.php';
back_base_init();

$operation = 'add|edit';
$action = 'list|add|edit|delete';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));

if( '' == $act ) {
    $act = 'list';
}

if( 'add' == $opera ) {
    if(!checkPurview('pur_carousel_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $img = '';
    $orderView = getPOST('orderView');
    $orderView = intval($orderView);
    $add_time = time();

    if( $orderView <= 0 ) {
        $orderView = 50;
    } else {
        $orderView = $db->escape(htmlspecialchars($orderView));
    }
    $alt = getPOST('alt');
    $alt = empty($alt) ? '' : $db->escape(htmlspecialchars($alt));

    $response = upload_with_choice($_FILES['img'], 'image', true);
    if($response['error']) {
        showSystemMessage($response['msg'], array());
        exit;
    } else {
        $img = $response['msg'];
        $type = $response['type'];
        $img_shortcut = resize_image($img, $type, 192, 45);
    }
    $addCarousel = 'insert into '.DB_PREFIX.'carousel (`img`, `orderView`, `img_shortcut`, `addTime`, `alt`) values
    (\''.$img.'\', \''.$orderView.'\', \''.$img_shortcut.'\', '.$add_time.', \''.$alt.'\');';
    if( $db->insert($addCarousel) ) {
        showSystemMessage('新增轮播图片成功', array(array('alt'=>'查看轮播列表','link'=>'carousel.php'), array('alt' => '继续添加', 'link' => 'carousel.php?act=add')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试'.$db->errmsg(), array());
        exit;
    }
}

if( 'edit' == $opera ) {
    if(!checkPurview('pur_carousel_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }
    $id = getGET('id');
    $id = intval($id);
    if( $id <= 0 ) {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $id = $db->escape(htmlspecialchars($id));
    }
    $img = '';
    $img_shortcut = '';
    $orderView = getPOST('orderView');
    $orderView = intval($orderView);

    if( $orderView <= 0 ) {
        $orderView = 50;
    } else {
        $orderView = $db->escape(htmlspecialchars($orderView));
    }

    $alt = getPOST('alt');
    $alt = empty($alt) ? '' : $db->escape(htmlspecialchars($alt));

    $response = upload_with_choice($_FILES['img'], 'image');
    if($response['error']) {
        showSystemMessage($response['msg'], array());
        exit;
    } else {
        $img = $response['msg'];
        if( $img != '' ) {
            $type = $response['type'];
            $img_shortcut = resize_image($img, $type);
        }
    }

    $getCarousel = 'select * from '.DB_PREFIX.'carousel where id = '.$id;
    $carousel = $db->fetchRow($getCarousel);

    $updateCarousel = 'update '.DB_PREFIX.'carousel set orderView = \''.$orderView.'\' ';
    $updateCarousel .= ',`alt`=\''.$alt.'\' ';
    $updateCarousel .= ($img != '') ? (',`img`=\''.$img.'\'') : '';
    $updateCarousel .= ($img_shortcut != '') ? (',`img_shortcut`=\''.$img_shortcut.'\'') : '';
    $updateCarousel .= ' where `id`='.$id.' limit 1';

    if($db->update($updateCarousel))
    {
        if( $img != '' ) {
            remove_file($carousel['img']);
            remove_file($carousel['img_shortcut']);

        }
        $links = array(
            array('alt'=>'查看轮播图列表', 'link'=>'carousel.php?act=list')
        );
        showSystemMessage('更新轮播图成功', $links);
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}


if( $act == 'list' ) {

    if(!checkPurview('pur_carousel_list', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getCarousels = 'select * from '.DB_PREFIX.'carousel where 1 order by orderView asc';
    $carousels = $db->fetchAll($getCarousels);
    if( $carousels ) {
        foreach( $carousels as $key => $carousel ) {
            $carousels[$key]['add_time'] = date('Y-m-d', $carousel['addTime']);
        }
    }
    assign('carousels', $carousels);
}

if( $act == 'add' ) {
    if(!checkPurview('pur_carousel_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }
}

if( 'edit' == $act ) {
    if(!checkPurview('pur_carousel_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }
    $id = getGET('id');
    $id = intval($id);
    if( $id <= 0 ) {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $id = $db->escape(htmlspecialchars($id));
    }
    $getCarousel = 'select * from '.DB_PREFIX.'carousel where id = '.$id;
    $carousel = $db->fetchRow($getCarousel);
    assign('carousel', $carousel);
}

if( 'delete' == $act ) {
    if(!checkPurview('pur_carousel_delete', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }
    $id = getGET('id');
    $id = intval($id);
    if( $id <= 0 ) {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $id = $db->escape(htmlspecialchars($id));
    }
    $getCarousel = 'select * from '.DB_PREFIX.'carousel where id = '.$id;
    $carousel = $db->fetchRow($getCarousel);
    $deleteCarousel = 'delete from '.DB_PREFIX.'carousel where id = '.$id;
    if( $db->delete($deleteCarousel) ) {
        remove_file($carousel['img']);
        remove_file($carousel['img_shortcut']);
        showSystemMessage('删除轮播图片成功', array(array('alt'=>'查看轮播列表','link'=>'carousel.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

assign('act', $act);
assign('subTitle', '轮播管理');
$smarty->display('carousel.phtml');
