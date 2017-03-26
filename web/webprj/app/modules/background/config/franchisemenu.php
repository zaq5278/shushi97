<?php
/**
 * Register application modules
 */
return array(
    'user' => array(
        'active' => true,
        'link_action' => 'admin/franchise/franchisem',
        'name' => '加盟店中心',
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
                'name' => '系统消息',
                'font_icon' => 'fa fa-home',
            )
        )
    )
);