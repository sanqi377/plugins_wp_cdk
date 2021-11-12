<?php
/*
Plugin Name: Ripro-v2 CDK 插件
Plugin URI: https://www.qblog.cc
Description: 给Ripro-v2 添加 cdk 兑换会员功能
Author: 叁柒
Version: 1.0
Author URI: https://www.qblog.cc
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
    ri_table_install();
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


/**
 * 随机生成 cdk
 */
function randomcdk($length = 10)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * cdk 插入数据库
 */
function addcdk()
{
    date_default_timezone_set('Asia/Shanghai');
    global $wpdb;

    //优惠码类型 vips = 永久会员优惠码 = 1   vip = 普通会员优惠码 = 2
    if ($_GET['lx'] == 'vips') {
        $code_type = '1';
        $day = '9999-09-09';
    } elseif ($_GET['lx'] == 'vip') {
        $code_type = '2';
        $day = time() + 86400 * $_POST['day'];
    }


    for ($x = 0; $x < $_POST['num']; $x++) {
        $data_array = array(
            'code'        => randomcdk(10),
            'code_type'   => $code_type,
            'create_time' => time(),
            'status'      => 0,
            'day'         => $day
        );
        $sql = $wpdb->insert($wpdb->prefix . "cdk", $data_array);
    }

    return !empty($sql);
}

/**
 * 新增数据库表
 */
function ri_table_install()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "cdk";  //获取表前缀，并设置新表的名称
    $sql = "CREATE TABLE " . $table_name . " (
          id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          create_time int,
          status int,
          code varchar(255),
          code_type varchar(255),
          user_id int,
          apply_time int,
          day varchar(255)
          );";
    require_once(ABSPATH . "wp-admin/includes/upgrade.php");  //引用wordpress的内置方法库
    dbDelta($sql);
}


/**
 * 生成页面方法
 */
function ri_add_one_page($title, $slug, $page_template = '', $post_content = '')
{
    $allPages = get_pages(); //获取所有页面
    $exists = false;
    foreach ($allPages as $page) {
        //通过页面别名来判断页面是否已经存在
        if (strtolower($page->post_name) == strtolower($slug)) {
            $exists = true;
        }
    }

    if ($exists == false) {
        $new_page_id = wp_insert_post(
            array(
                'post_title'    => $title,
                'post_type'    => 'page',
                'post_name'    => $slug,
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_content'    => $post_content,
                'post_status'    => 'publish',
                'post_author'    => 1,
                'menu_order'    => 0
            )
        );
        //如果插入成功 且设置了模板   
        if ($new_page_id && $page_template != '') {
            //保存页面模板信息
            update_post_meta($new_page_id, '_wp_page_template',  $page_template);
        }
        $file = plugin_dir_path(__FILE__) . $page_template;
        $newFile = get_stylesheet_directory() . '/' . $page_template;

        copy($file, $newFile); //拷贝到新目录
    }
}

/**
 * 启用插件生成页面
 */
register_activation_hook(__FILE__, 'ri_add_pages');
function ri_add_pages()
{
    ri_add_one_page('cdk 兑换', 'cdk', 'pages/cdk-index.php', '');
}

/**
 * 前端使用卡密
 */
function useCdk()
{
    global $wpdb;
    $data   = !empty($_POST['data']) ? $_POST['data'] : null;
    if ($data['code']) {
        $xinxi_cdk = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cdk WHERE code=" . "'" . $data['code'] . "'");
        if (!$xinxi_cdk) {
            echo json_encode(array('status' => '0', 'msg' => 'CDK 不存在'));
            exit;
        }

        if ($xinxi_cdk[0]->status == 1) {
            echo json_encode(array('status' => '0', 'msg' => 'CDK 已被使用'));
            exit;
        }

        if ($xinxi_cdk[0]->code_type == 1) {
            // 更新优惠码记录
            $wpdb->update($wpdb->prefix . 'cdk', array('status' => 1, 'apply_time' => time(), 'user_id' => $data['user_id']), array('code' => $data['code']));

            // 更细会员
            $wpdb->update($wpdb->prefix . 'usermeta', array('meta_value' => 'vip'), array('user_id' => $data['user_id'], 'meta_key' => 'cao_user_type'));
            $wpdb->update($wpdb->prefix . 'usermeta', array('meta_value' => '9999-09-09'), array('user_id' => $data['user_id'], 'meta_key' => 'cao_vip_end_time'));
            echo json_encode(array('status' => '0', 'msg' => '已成功兑换终身 vip'));
            exit;
        } else {
            // 更新优惠码记录
            $wpdb->update($wpdb->prefix . 'cdk', array('status' => 1, 'apply_time' => time(), 'user_id' => $data['user_id']), array('code' => $data['code']));
            $wpdb->update($wpdb->prefix . 'usermeta', array('meta_value' => 'vip'), array('user_id' => $data['user_id'], 'meta_key' => 'cao_user_type'));
            $wpdb->update($wpdb->prefix . 'usermeta', array('meta_value' => date('Y-m-d', $xinxi_cdk[0]->day)), array('user_id' => $data['user_id'], 'meta_key' => 'cao_vip_end_time'));
            echo json_encode(array('status' => '0', 'msg' => '已成功兑换 vip 至 ' . date('Y-m-d', $xinxi_cdk[0]->day)));
            exit;
        }
    }
}
add_action('wp_ajax_useCdk', 'useCdk');
add_action('wp_ajax_nopriv_useCdk', 'useCdk');

/**
 * 自定义分页
 */
function ri_admin_pagenavi($total_count, $number_per_page = 20)
{

    $current_page = isset($_GET['paged']) ? $_GET['paged'] : 1;

    if (isset($_GET['paged'])) {
        unset($_GET['paged']);
    }

    $base_url = add_query_arg($_GET, admin_url('admin.php'));

    @$total_pages = ceil($total_count / $number_per_page);

    $first_page_url = $base_url . '&amp;paged=1';
    $last_page_url  = $base_url . '&amp;paged=' . $total_pages;

    if ($current_page > 1 && $current_page < $total_pages) {
        $prev_page     = $current_page - 1;
        $prev_page_url = $base_url . '&amp;paged=' . $prev_page;

        $next_page     = $current_page + 1;
        $next_page_url = $base_url . '&amp;paged=' . $next_page;
    } elseif ($current_page == 1) {
        $prev_page_url  = '#';
        $first_page_url = '#';
        if ($total_pages > 1) {
            $next_page     = $current_page + 1;
            $next_page_url = $base_url . '&amp;paged=' . $next_page;
        } else {
            $next_page_url = '#';
        }
    } elseif ($current_page == $total_pages) {
        $prev_page     = $current_page - 1;
        $prev_page_url = $base_url . '&amp;paged=' . $prev_page;
        $next_page_url = '#';
        $last_page_url = '#';
    }
?>
    <div class="tablenav">
        <div class="tablenav-pages">
            <span class="displaying-num ">每页 <?php echo $number_per_page; ?> 共 <?php echo $total_count; ?></span>
            <span class="pagination-links">
                <a class="first-page button <?php if ($current_page == 1) {
                                                echo 'disabled';
                                            }
                                            ?>" title="前往第一页" href="<?php echo $first_page_url; ?>">«</a>
                <a class="prev-page button <?php if ($current_page == 1) {
                                                echo 'disabled';
                                            }
                                            ?>" title="前往上一页" href="<?php echo $prev_page_url; ?>">‹</a>
                <span class="paging-input ">第 <?php echo $current_page; ?> 页，共 <span class="total-pages"><?php echo $total_pages; ?></span> 页</span>
                <a class="next-page button <?php if ($current_page == $total_pages) {
                                                echo 'disabled';
                                            }
                                            ?>" title="前往下一页" href="<?php echo $next_page_url; ?>">›</a>
                <a class="last-page button <?php if ($current_page == $total_pages) {
                                                echo 'disabled';
                                            }
                                            ?>" title="前往最后一页" href="<?php echo $last_page_url; ?>">»</a>
            </span>
        </div>
        <br class="clear">
    </div>
<?php
}
