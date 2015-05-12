<?php

include 'library/init.inc.php';

if( !check_cross_domain() ) {
    echo json_encode(array(
        'error' => 1,
        'message' => '请从本站提交数据',
    ));
    exit;
};
$opera = getPOST('opera');

if( $opera == 'get_distributors' ) {
    $id = getPOST('DistrictID');
    $id = intval($id);
    if( 0 >= $id ) {
        echo json_encode(array(
            'error' => 1,
            'message' => '参数错误',
        ));
        exit;
    } else {
        $id = $db->escape(htmlspecialchars($id));
    }
    $getDistributors = "select name, address, lat, lng from ".DB_PREFIX."distributor
        where DistrictID = ".$id." and status = 1
        order by add_time desc";

    $distributors = $db->fetchAll($getDistributors);
    if( ($distributors) ) {
        echo json_encode(array(
            'error' => 0,
            'message' => '成功',
            'data' => $distributors,
        ));
        exit;
    } else {
        echo json_encode(array(
            'error' => 1,
            'message' => '该地区暂无经销商',
        ));
        exit;
    }


}


if( $opera == 'get_distributor' ) {
    $data = getPost('data');
    if( $data == '' ) {
        echo json_encode(array(
            'error' => 1,
            'message' => '请输入手机号码或授权码',
        ));
        exit;
    } else {
        $data = $db->escape(htmlspecialchars($data));
    }
    $getDistributor = '';

    $getDistributor .= 'select `name`,`address`,`phone`, `contact`, `DistrictName`, `CityName`, `ProvinceName`
    from `'.DB_PREFIX.'distributor` as distributor
    left join  `'.DB_PREFIX.'District` as district on distributor.DistrictID = district.DistrictID
    left join  `'.DB_PREFIX.'City` as city on district.CityID = city.CityID
    left join  `'.DB_PREFIX.'Province` as province on city.ProvinceID = province.ProvinceID';


    $distributor = $db->fetchRow($getDistributor.' where `phone` = \''.$data.'\' limit 1');
    if( $distributor ) {
        echo json_encode(array(
            'error' => 0,
            'message' => '成功',
            'data' => $distributor,
        ));
        exit;
    }

    $distributor = $db->fetchRow($getDistributor.' where `authCode` = \''.$data.'\' limit 1');
    if( $distributor ) {
        echo json_encode(array(
            'error' => 0,
            'message' => '成功',
            'data' => $distributor,
        ));
        exit;
    } else {
        echo json_encode(array(
            'error' => 1,
            'message' => '不存在该经销商',
        ));
        exit;
    }
}
