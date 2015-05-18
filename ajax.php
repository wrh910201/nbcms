<?php

include 'library/init.inc.php';

//if( !check_cross_domain() ) {
//    echo json_encode(array(
//        'error' => 1,
//        'message' => '请从本站提交数据',
//    ));
//    exit;
//};
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

    $getDistributor .= 'select `authCode`,`name`,`address`,`phone`, `contact`, `DistrictName`, `CityName`, `ProvinceName`, `address`
    from `'.DB_PREFIX.'distributor` as distributor
    left join  `'.DB_PREFIX.'District` as district on distributor.DistrictID = district.DistrictID
    left join  `'.DB_PREFIX.'City` as city on district.CityID = city.CityID
    left join  `'.DB_PREFIX.'Province` as province on city.ProvinceID = province.ProvinceID';

    $content =<<<HTML
<div class="check_result">
    <img src="images/gabrielle.png" alt="" class="check_result_logo"/>
    <div class="check_result_content">
        <p class="distributor_name">%s</p>
        <p>联&nbsp;&nbsp;系&nbsp;&nbsp;人:&nbsp;%s</p>
        <p>授&nbsp;&nbsp;权&nbsp;&nbsp;码:&nbsp;%s</p>
        <p>联系电话:&nbsp;%s</p>
        <p>详细地址:&nbsp;%s</p>
    </div>
</div>
HTML;

    $distributor = $db->fetchRow($getDistributor.' where `phone` = \''.$data.'\' limit 1');
    if( $distributor ) {
        $distributor = sprintf($content, $distributor['name'], $distributor['contact'], $distributor['authCode'], $distributor['phone'], $distributor['ProvinceName'].$distributor['CityName'].$distributor['DistrictName'].$distributor['address']);
        echo json_encode(array(
            'error' => 0,
            'message' => '成功',
            'data' => $distributor,
        ));
        exit;
    }

    $distributor = $db->fetchRow($getDistributor.' where `authCode` = \''.$data.'\' limit 1');
    if( $distributor ) {
        $distributor = sprintf($content, $distributor['name'], $distributor['contact'], $distributor['authCode'], $distributor['phone'], $distributor['ProvinceName'].$distributor['CityName'].$distributor['DistrictName'].$distributor['address']);
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
