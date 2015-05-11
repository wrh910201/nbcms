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

