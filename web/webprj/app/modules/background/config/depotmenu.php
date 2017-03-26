<?php
/**
 * Register application modules
 */
return array(
    'depot' => array(
        'active' => false,
        'link_action' => 'admin/depot/depotm',
        'name' => '仓库主页',
        'font_icon' => 'fa fa-home'
    ),
    'depotorder' => array(
        'active' => false,
        'link_action' => 'admin/depot/depotorder',
        'name' => '订单管理',
        'font_icon' => 'fa fa-home'
    ),
    'rbac' => array(
        'active' => false,
        'name' => '系统管理',
        'font_icon' => 'fa fa-home',
        'sub_menu' => array(
            'mangers' => array(
                'active' => false,
                'link_action' => 'admin/member/modifyPwd',
                'name' => '修改密码',
                'font_icon' => 'fa fa-home',
            ),
            'news' => array(
                'active' => false,
                'link_action' => 'admin/message/getMessage',
                'name' => '消息管理',
                'font_icon' => 'fa fa-home'
            )
        )
    )
);