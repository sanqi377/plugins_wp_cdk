<?php
/*
Plugin Name: ri-cdk
*/

/**
 * 后台设置菜单
 */
function ri_cdk_admin_menu()
{
    if (is_close_site_shop() || !current_user_can('manage_options')) {
        return;
    }
    add_menu_page(esc_html__('卡密系统', 'ri-cdk'), '卡密系统', 'administrator', 'ri_cdk_index_page', 'ri_cdk_index_page');
}
add_action('admin_menu', 'ri_cdk_admin_menu');

/**
 * 卡密管理
 */
function ri_cdk_index_page()
{
    date_default_timezone_set(get_option('timezone_string'));
    require_once plugin_dir_path(__FILE__) . 'inc/cdk_index.php';
}
