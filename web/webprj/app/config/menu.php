<?php
/**
 * Register application modules
 */
return array(
    'user' => array(
        'active' => false,
        'link_action' => 'admin/user',
        'name' => '用户管理',
        'font_icon' => 'fa fa-home'
    ),
    'goods' =>array(
        'active' => false,
        'name' => '商品管理',
        'font_icon' => 'fa fa-home',
        'sub_menu' => array(
            'category' => array(
                'active' => false,
                'link_action' => 'admin/category',
                'name' => '商品分类管理',
                'font_icon' =>'fa fa-home'
            ),
            'goods' =>array(
                'active' =>false,
                'link_action' => 'admin/goods',
                'name' => '商品管理',
                'font_icon' => 'fa fa-home'
            )
        )
    ),
    'franchise' => array(
        'active' => false,
        'link_action' => 'admin/franchise',
        'name' => '加盟店管理',
        'font_icon' =>'fa fa-home'
    ),
    'order' => array(
        'active' => false,
        'link_action' => 'admin/order',
        'name' => '订单管理',
        'font_icon' =>'fa fa-home'
    ),
    'indeSet' => array(
        'active' => false,
        'link_action' => 'admin/indeSet',
        'name' => '首页管理',
        'font_icon' =>'fa fa-home',
        "sub_menu"=>array(
            "banner"=>array(
                'active'=>false,
                "link_action"=>"admin/banner/index?type=2",
                "name"=>"轮播图管理",
            )
        )
    ),
    'depot' => array(
        'active' => false,
        'link_action' => 'admin/depot',
        'name' => '仓库管理',
        'font_icon' => 'fa fa-home'
    ),
    'statistics' => array(
        'active' => false,
        'link_action' => 'admin/statistics',
        'name' => '统计管理',
        'font_icon' =>'fa fa-home',
        'sub_menu' => array(
            'sellSta' => array(
                'active' => false,
                'link_action' => 'admin/statistics/sale',
                'name' => '销售统计',
            ),
            'franchSta' => array(
                'active' => false,
                'link_action' => 'admin/statistics/franchise',
                'name' => '加盟店收入统计',
            )

        )
    ),
    'reflect' => array(
        'active' => false,
        'link_action' => 'admin/reflect',
        'name' => '提现管理',
        'font_icon' =>'fa fa-home'
    ),
    'version' => array(
        'active' => false,
        'link_action' => 'admin/version',
        'name' => '版本管理',
        'font_icon' => 'fa fa-comments-o'
    ),
    'rbac' => array(
        'active' => false,
        'name' => '系统管理',
        'font_icon' => 'fa fa-home',
        'sub_menu' => array(
            'news' => array(
                'active' => false,
                'link_action' => 'admin/message',
                'name' => '系统消息管理',
                'font_icon' => 'fa fa-home',
            ),
            'mangers' => array(
                'active' => false,
                'link_action' => 'admin/managers',
                'name' => '用户管理',
                'font_icon' => 'fa fa-home',
            ),
            'permissions' => array(
                'active' => false,
                'link_action' => 'admin/permissions',
                'name' => '权限管理',
                'font_icon' => 'fa fa-home'
            ),
            'roles' => array(
                'active' => false,
                'link_action' => 'admin/roles',
                'name' => '角色管理',
                'font_icon' => 'fa fa-home'
            ),
            'area' => array(
                'active' => false,
                'link_action' => 'admin/area',
                'name' => '城市管理',
                'font_icon' => 'fa fa-home'
            )
        )
    )
);