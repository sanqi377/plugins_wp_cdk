<?php
date_default_timezone_set('Asia/Shanghai');
global $wpdb;
$table_name = $wpdb->prefix . 'cdk';
$no_add = (!empty($_GET['action']) && $_GET['action'] == 'add') ? false : true;
$is_delete = (!empty($_GET['action']) && $_GET['action'] == 'delete') ? true : false;
$id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$is_vip = (!empty($_GET['lx']) && $_GET['lx'] == 'vip') ? true : false;
$is_vips = (!empty($_GET['lx']) && $_GET['lx'] == 'vips') ? true : false;
if ($is_delete) {
    # 删除操作...
    $deletesql = $wpdb->query("DELETE FROM $table_name WHERE id = $id ");
    if ($deletesql) {
        echo '<div id="message" class="updated notice is-dismissible"><p>删除成功</p></div>';
    }
}
?>

<?php if ($no_add) : ?>
    <?php
    // 主页面PHP
    $perpage = 20; // 每页数量
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;  //当前页
    $offset = $perpage * ($paged - 1); //偏移页
    //////// 构造SQL START ////////
    $sql = "select * FROM " . $table_name;

    $where = ' WHERE 1=1';

    if (isset($_GET['lx']) && is_numeric($_GET['lx'])) {
        if ($_GET['lx'] == 1) {
            $where .= ' AND code_type=2';
        } else {
            $where .= ' AND code_type=1';
        }
    }

    if (isset($_GET['status']) && is_numeric($_GET['status'])) {
        // 当前时间
        $this_time = time();
        if ($_GET['status'] == 1) {
            $where .= ' AND e.status=1 OR e.end_time<' . $this_time;
        } elseif ($_GET['status'] == 0) {
            $where .= ' AND e.status=0 AND e.end_time>' . $this_time;
        }
    }



    if (!empty($_GET['code'])) {
        $where .= ' AND e.code="' . esc_sql($_GET['code']) . '"';
    }

    $orderlimti = ' ORDER BY create_time DESC';
    $orderlimti .= ' LIMIT ' . esc_sql($offset . ',' . $perpage);
    $result = $wpdb->get_results($sql . $where . $orderlimti);
    $total   = $wpdb->get_var("SELECT COUNT(id) FROM $table_name e {$where}");
    //////// 构造SQL END ////////
    ?>

    <!-- 主页面 -->
    <div class="wrap">
        <h1 class="wp-heading-inline">所有资源订单</h1>
        <?php $add_url = add_query_arg(array('page' => $_GET['page'], 'action' => 'add', 'lx' => 'vips'), admin_url('admin.php')); ?>
        <a href="<?php echo $add_url ?>" class="page-title-action">添加永久会员优惠码</a>
        <?php $add_url = add_query_arg(array('page' => $_GET['page'], 'action' => 'add', 'lx' => 'vip'), admin_url('admin.php')); ?>
        <a href="<?php echo $add_url ?>" class="page-title-action">添加会员优惠码</a>
        <hr class="wp-header-end">

        <form id="order-filter" method="get">
            <!-- 初始化页面input -->
            <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
            <!-- 筛选 -->
            <div class="wp-filter">
                <div class="filter-items">
                    <div class="view-switch">
                        <a class="view-list current"></a>
                    </div>
                    <div class="actions">
                        <select class="postform" id="status" name="status">
                            <option selected="selected" value="">优惠码状态</option>
                            <option value="0">正常</option>
                            <option value="1">失效</option>
                        </select>
                        <select class="postform" id="lx" name="lx">
                            <option selected="selected" value="">优惠码类型</option>
                            <option value="1">普通会员优惠码</option>
                            <option value="2">永久会员优惠码</option>
                        </select>
                        <input class="button" id="post-query-submit" name="filter_action" type="submit" value="筛选"></input>
                    </div>
                </div>
                <div class="search-form">
                    <span class="">共<?php echo $total ?>个项目 </span>
                    <input class="search" id="media-search-input" name="code" placeholder="输入优惠码搜索,回车确定…" type="search" value="" />
                </div>
                <br class="clear">
            </div>
            <!-- 筛选END -->

            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <th class="column-primary">CDK-CODE</th>
                        <th>优惠码类型</th>
                        <th>创建时间</th>
                        <th>优惠码状态</th>
                        <th>优惠码时长</th>
                        <th>使用时间</th>
                        <th>用户ID</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="the-list">

                    <?php

                    if ($result) {
                        // 当前时间
                        $the_time = time();
                        foreach ($result as $item) {
                            echo '<tr id="order-info">';
                            echo '<td class="has-row-actions column-primary"><span class="badge badge-radius">' . $item->code . '</span><button type="button" class="toggle-row"><span class="screen-reader-text">显示详情</span></button></td>';

                            if ($item->code_type == '2') {
                                echo '<td data-colname="类型">普通会员优惠码</td>';
                            } else if ($item->code_type == '1') {
                                echo '<td data-colname="类型">永久会员优惠码</td>';
                            } else {
                                echo '<td data-colname="类型">未知类型</td>';
                            }

                            echo '<td data-colname="创建时间">' . date('Y-m-d H:i:s', $item->create_time) . '</td>';

                            if ($item->status !=  NULL) {
                                if ($item->status == 1) {
                                    echo '<td data-colname="优惠码状态"><span class="badge badge-radius badge-danger">已使用</span></td>';
                                } else {
                                    echo '<td data-colname="优惠码状态"><span class="badge badge-radius badge-danger">未使用</span></td>';
                                }
                            }

                            if ($item->day) {
                                if ($item->day == '9999-09-09') {
                                    echo '<td data-colname="优惠码时间">永久会员</td>';
                                } else {
                                    echo '<td data-colname="优惠码时间">' . date('Y-m-d H:i:s', $item->day) . '</td>';
                                }
                            } else {
                                echo '<td data-colname="使用时间">/</td>';
                            }

                            if ($item->apply_time) {
                                echo '<td data-colname="使用时间">' . date('Y-m-d H:i:s', $item->apply_time) . '</td>';
                            } else {
                                echo '<td data-colname="使用时间">/</td>';
                            }

                            if ($item->user_id != NULL) {
                                $user_loginName = ($item->user_id > 0) ? get_user_by('id', $item->user_id)->user_login : '游客';
                                echo '<td data-colname="用户ID">' . $user_loginName . '</td>';
                            } else {
                                echo '<td data-colname="用户ID">/</td>';
                            }

                            //编辑操作
                            $edit_url = add_query_arg(array('page' => $_GET['page'], 'action' => 'delete', 'id' => $item->id,), admin_url('admin.php'));
                            echo '<td data-colname="编辑/删除">
                        <a href="' . $edit_url . '" onclick="javascript:if(!confirm(\'确定删除？\')) return false;">删除</a>
                    </td>';

                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="12" align="center"><strong>没有数据</strong></td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </form>
        <?php echo ri_admin_pagenavi($total, $perpage); ?>
        <script>
            jQuery(document).ready(function($) {

            });
        </script>
    </div>

<?php else : ?>
    <!-- 编辑页 -->
    <?php


    // POST data
    $is_add = (!empty($_POST['action']) && $_POST['action'] == 'add') ? true : false;
    $is_addvip = (!empty($_POST['action']) && $_POST['action'] == 'addvip') ? true : false;
    $is_addvips = (!empty($_POST['action']) && $_POST['action'] == 'addvips') ? true : false;
    $num = (!empty($_POST['num'])) ? $_POST['num'] : 0;
    $day = (!empty($_POST['day'])) ? $_POST['day'] : 0;
    if ($is_add || $is_addvip || $is_addvips) {
        if (!$is_add) {
            if (addcdk($is_addvip, $num, $day)) {
                echo '<div id="message" class="updated notice is-dismissible"><p>添加成功</p></div>';
            } else {
                echo '<div id="message" class="error notice is-dismissible"><p>添加失败</p></div>';
            }
        }
    }

    ?>
    <div class="wrap">
        <?php if (!$is_vips) : ?>
            <?php $add_url = add_query_arg(array('page' => $_GET['page'], 'action' => 'add', 'lx' => 'vips'), admin_url('admin.php')); ?>
            <a href="<?php echo $add_url ?>" class="page-title-action">添加永久会员优惠码</a>
        <?php endif; ?>
        <?php if (!$is_vip) : ?>
            <?php $add_url = add_query_arg(array('page' => $_GET['page'], 'action' => 'add', 'lx' => 'vip'), admin_url('admin.php')); ?>
            <a href="<?php echo $add_url ?>" class="page-title-action">添加会员优惠码</a>
        <?php endif; ?>
        <form action="" id="poststuff" method="post" name="post">
            <input name="action" type="hidden" value="add<?php echo $is_vip ? "vip" : ""; ?><?php echo $is_vips ? "vips" : ""; ?>"></input>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="num">
                                <?php echo $is_vip ? "普通会员优惠码" : "" ?>
                                <?php echo $is_vips ? "终身会员优惠码" : "" ?>
                                优惠码数量
                            </label>
                        </th>
                        <td><input class="small-text" id="num" min="1" name="num" step="1" type="number" value="1"> 个</input></td>
                    </tr>
                    <?php if ($is_vip) : ?>
                        <tr>
                            <th scope="row"><label for="day">会员兑换天数</label></th>
                            <td><input class="small-text" id="day" min="1" name="day" step="1" type="number" value="1"> 天</input></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="立即添加"></p>
        </form>
    </div>

<?php endif; ?>