<?php
/**
 * @author winsen
 * @version 1.0.0
 */
global $purview;
$purview = array(
    'pur_sysconf' => array(
        'pur_sysconf_add',
        'pur_sysconf_list',
        'pur_sysconf_edit',
        'pur_sysconf_delete',
    ),
    'pur_nav' => array(
        'pur_nav_add',
        'pur_nav_list',
        'pur_nav_edit',
        'pur_nav_delete',
    ),
    'pur_adPosition' => array(
        'pur_adPosition_add',
        'pur_adPosition_list',
        'pur_adPosition_edit',
        'pur_adPosition_delete',
    ),
    'pur_ad' => array(
        'pur_ad_add',
        'pur_ad_list',
        'pur_ad_edit',
        'pur_ad_delete',
    ),
    'pur_articleCat' => array(
        'pur_articleCat_add',
        'pur_articleCat_list',
        'pur_articleCat_edit',
        'pur_articleCat_delete',
    ),
    'pur_article' => array(
        'pur_article_add',
        'pur_article_list',
        'pur_article_edit',
        'pur_article_delete',
    ),
    'pur_admin' => array(
        'pur_admin_add',
        'pur_admin_list',
        'pur_admin_edit',
        'pur_admin_delete',
    ),
    'pur_adminRole' => array(
        'pur_adminRole_add',
        'pur_adminRole_list',
        'pur_adminRole_edit',
        'pur_adminRole_delete',
    ),

    'pur_friend' => array(
        'pur_friend_add',
        'pur_friend_list',
        'pur_friend_edit',
        'pur_friend_delete',
    ),

    'pur_category' => array(
        'pur_category_add',
        'pur_category_list',
        'pur_category_edit',
        'pur_category_delete',
    ),
    'pur_product' => array(
        'pur_product_add',
        'pur_product_list',
        'pur_product_edit',
        'pur_product_delete'
    ),
    'pur_distributor' => array(
        'pur_distributor_add',
        'pur_distributor_list',
        'pur_distributor_edit',
        'pur_distributor_delete',
    ),
    'pur_carousel' => array(
        'pur_carousel_add',
        'pur_carousel_list',
        'pur_carousel_edit',
        'pur_carousel_delete',
    ),
    'pur_wechat' => array(
        'pur_wechat_bind',
        'pur_wechat_manager',
    ),
);

global $L_purview;
$L_purview = array(
    'pur_sysconf_add'=>'添加系统参数',
    'pur_sysconf_list'=>'查看系统参数',
    'pur_sysconf_edit'=>'修改系统参数',
    'pur_sysconf_delete'=>'删除系统参数',
    'pur_nav_add'=>'添加导航栏',
    'pur_nav_list'=>'查看导航栏',
    'pur_nav_edit'=>'编辑导航栏',
    'pur_nav_delete'=>'删除导航栏',
    'pur_adPosition_add'=>'添加广告位置',
    'pur_adPosition_list'=>'查看广告位置',
    'pur_adPosition_edit'=>'编辑广告位置',
    'pur_adPosition_delete'=>'删除广告位置',
    'pur_ad_add'=>'添加广告',
    'pur_ad_list'=>'查看广告列表',
    'pur_ad_edit'=>'编辑广告',
    'pur_ad_delete'=>'删除广告',
    'pur_articleCat_add'=>'添加资讯分类',
    'pur_articleCat_list'=>'查看资讯分类',
    'pur_articleCat_edit'=>'编辑资讯分类',
    'pur_articleCat_delete'=>'删除资讯分类',
    'pur_article_add'=>'添加资讯',
    'pur_article_list'=>'查看资讯',
    'pur_article_edit'=>'编辑资讯',
    'pur_article_delete'=>'删除资讯',
    'pur_admin_add'=>'添加管理员',
    'pur_admin_list'=>'查看管理员',
    'pur_admin_edit'=>'编辑管理员',
    'pur_admin_delete'=>'删除管理员',
    'pur_adminRole_add'=>'添加管理员角色',
    'pur_adminRole_list'=>'查看管理员角色',
    'pur_adminRole_edit'=>'编辑管理员角色',
    'pur_adminRole_delete'=>'删除管理员角色',
    'pur_friend_add'=>'添加友情链接',
    'pur_friend_list'=>'查看友情链接',
    'pur_friend_edit'=>'编辑友情链接',
    'pur_friend_delete'=>'删除友情链接',
    'pur_category_add'=>'添加产品分类',
    'pur_category_list'=>'查看产品分类',
    'pur_category_edit'=>'编辑产品分类',
    'pur_category_delete'=>'删除产品分类',
    'pur_product_add'=>'添加产品',
    'pur_product_list'=>'查看产品',
    'pur_product_edit'=>'编辑产品',
    'pur_product_delete'=>'删除产品',
    'pur_distributor_add'=>'添加经销商',
    'pur_distributor_list'=>'查看经销商',
    'pur_distributor_edit'=>'编辑经销商',
    'pur_distributor_delete'=>'删除经销商',
    'pur_carousel_add'=>'添加轮播',
    'pur_carousel_list'=>'查看轮播',
    'pur_carousel_edit'=>'编辑轮播',
    'pur_carousel_delete'=>'删除轮播',
    'pur_wechat_bind' => '微信绑定',
    'pur_wechat_manager' => '微信管理',
);

global $menus;
$menus = array(
    //array('url' => 'main.php', 'title' => '首页', 'icon' => 'fa fa-home'),
    'pur_sysconf' => array('url'=>'sysconf.php', 'title'=>'系统参数管理', 'icon' => 'fa fa-laptop'),
    'pur_nav' => array('url'=>'nav.php', 'title'=>'导航栏管理', 'icon' => 'fa fa-envelope-o'),
    'pur_adPosition' => array('url'=>'adPosition.php', 'title'=>'广告位置管理', 'icon' => 'fa fa-check'),
    'pur_ad' => array('url'=>'ad.php', 'title'=>'广告管理', 'icon' => 'fa fa-search-plus'),
    'pur_articleCat' => array('url'=>'articleCat.php', 'title'=>'资讯分类管理', 'icon' => 'fa fa-times'),
    'pur_article' => array('url'=>'article.php', 'title'=>'资讯管理', 'icon' => 'fa fa-suitcase'),
    'pur_admin' => array('url'=>'adminUser.php', 'title'=>'管理员管理', 'icon' => 'fa fa-user'),
    'pur_adminRole' => array('url'=>'adminRole.php', 'title'=>'管理员角色管理', 'icon' => 'fa fa-bug'),
    'pur_friend' => array('url'=>'friend.php', 'title'=>'友情链接管理', 'icon' => 'fa fa-map-marker'),
    //'pur_category' => array('url'=>'category.php', 'title'=>'产品分类管理', 'icon' => 'fa fa-file-text'),
    //'pur_product' => array('url'=>'product.php', 'title'=>'产品管理', 'icon' => 'fa fa-power-off'),
    'pur_distributor' => array('url' => 'distributor.php', 'title' => '经销商管理', 'icon' => 'fa fa-th-list'),
    'pur_carousel' => array('url' => 'carousel.php', 'title' => '轮播管理', 'icon' => 'fa fa-edit'),
    'pur_wechat' => array('url' => '../wechat/wechat/main.php', 'title' => '公众号', 'icon' => 'fa fa-power-off'),
);
