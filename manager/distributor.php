<?php
include 'library/init.inc.php';

back_base_init();

$operation = 'add|edit';
$action = 'list|add|edit|delete';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));
if('' == $act)
{
    $act = 'list';
}

//编辑经销商
if('edit' == $opera)
{
    if(!checkPurview('pur_distributor_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $data = check_distributor_input(true);
    $editDistributor = "update ".DB_PREFIX."distributor set
        name = '".$data['name']."',
        contact = '".$data['contact']."',
        phone = '".$data['phone']."',
        DistrictID = '".$data['district']."',
        address = '".$data['address']."',
        lat = '".$data['lat']."',
        lng = '".$data['lng']."',
        authCode = '".$data['authCode']."'
        where id = '".$data['id']."' limit 1";


    if($db->update($editDistributor))
    {
        showSystemMessage('修改经销商成功', array(array('alt'=>'查看经销商列表','link'=>'distributor.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}


//添加经销商
if('add' == $opera)
{
    if(!checkPurview('pur_distributor_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $data = check_distributor_input();
    $addDistributor = "insert into ".DB_PREFIX."distributor
        (id, name, contact, phone, DistrictID, address, lat, lng, add_time, status, authCode) values
        (null, '".$data['name']."', '".$data['contact']."', '".$data['phone']."', '".$data['district']."', '".$data['address']."', ".$data['lat'].", ".$data['lng'].", ".time().", 1, '".$data['authCode']."')";

//    echo $addDistributor;exit;

    if($db->insert($addDistributor))
    {
        showSystemMessage('新增经销商成功', array(array('alt'=>'查看经销商列表','link'=>'distributor.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}



if('list' == $act)
{
    if(!checkPurview('pur_distributor_list', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getDistributors = 'select `id`,`name`,`address`, `phone`, `contact`, `DistrictName`, `CityName`, `ProvinceName`
        from `'.DB_PREFIX.'distributor` as distributor
        left join  `'.DB_PREFIX.'District` as district on distributor.DistrictID = district.DistrictID
        left join  `'.DB_PREFIX.'City` as city on district.CityID = city.CityID
        left join  `'.DB_PREFIX.'Province` as province on city.ProvinceID = province.ProvinceID
        order by `id` DESC';
    $distributors = $db->fetchAll($getDistributors);
    if( $distributors ) {
        foreach ($distributors as $key => $distributor) {
            if( $distributor['ProvinceName'] == $distributor['CityName'] ) {
                $distributors[$key]['area'] = $distributor['ProvinceName'] .  '&nbsp;' . $distributor['DistrictName'];
            } else {
                $distributors[$key]['area'] = $distributor['ProvinceName'] . '&nbsp;' . $distributor['CityName'] . '&nbsp;' . $distributor['DistrictName'];
            }

        }
    }

    assign('distributors', $distributors);
}
//添加经销商
if( 'add' == $act ) {

    if(!checkPurview('pur_distributor_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }
    assign('map_init', json_encode(false));
    get_area_data();

}

if( 'edit' == $act ) {
    if(!checkPurview('pur_distributor_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');
    $id = intval($id);
    if( 0 >= $id ) {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $id = $db->escape(htmlspecialchars($id));
    }

    $getDistributor = 'select `id`,`name`,`address`, `phone`, `contact`, district.`DistrictID`, `DistrictName`, city.`CityID`, `CityName`,province.`ProvinceID`, `ProvinceName`, `authCode`, `lng`, `lat`
        from `'.DB_PREFIX.'distributor` as distributor
        left join  `'.DB_PREFIX.'District` as district on distributor.DistrictID = district.DistrictID
        left join  `'.DB_PREFIX.'City` as city on district.CityID = city.CityID
        left join  `'.DB_PREFIX.'Province` as province on city.ProvinceID = province.ProvinceID
        where `id` = '.$id.'
        order by `id` DESC
        limit 1';

    $distributor = $db->fetchRow($getDistributor);
    assign('distributor', $distributor);
    //百度地图初始化的标志
    assign('map_init', json_encode(true));
    get_area_data();
}

if( 'delete' == $act ) {

    if(!checkPurview('pur_distributor_delete', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }
    $id = getGET('id');
    $id = intval($id);
    if( 0 >= $id ) {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $id = $db->escape(htmlspecialchars($id));
    }
    $getDistributor = 'select `id`,`name`,`address`, `phone`, `contact`, district.`DistrictID`, `DistrictName`, city.`CityID`, `CityName`,province.`ProvinceID`, `ProvinceName`, `authCode`
        from `'.DB_PREFIX.'distributor` as distributor
        left join  `'.DB_PREFIX.'District` as district on distributor.DistrictID = district.DistrictID
        left join  `'.DB_PREFIX.'City` as city on district.CityID = city.CityID
        left join  `'.DB_PREFIX.'Province` as province on city.ProvinceID = province.ProvinceID
        where `id` = '.$id.'
        order by `id` DESC
        limit 1';
    $distributor = $db->fetchRow($getDistributor);
    if( empty($distributor) ) {
        showSystemMessage('该代理商不存在', array(array('alt'=>'查看经销商列表','link'=>'distributor.php')));
        exit;
    }


    $deleteDistributor = "delete from ".DB_PREFIX."distributor  where id = ".$id." limit 1";
    if($db->delete($deleteDistributor))
    {
        showSystemMessage('删除经销商成功', array(array('alt'=>'查看经销商列表','link'=>'distributor.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }

}

assign('act', $act);
assign('subTitle', '经销商管理');
$smarty->display('distributor.phtml');