<?php

/**
 * Template Name: CDK 兑换
 */
get_header();
global $current_user;
$user_id = $current_user->ID;
?>

<style>
    input {
        -webkit-writing-mode: horizontal-tb !important;
        text-rendering: auto;
        color: -internal-light-dark(black, white);
        letter-spacing: normal;
        word-spacing: normal;
        line-height: normal;
        text-transform: none;
        text-indent: 0px;
        text-shadow: none;
        display: inline-block;
        text-align: start;
        appearance: auto;
        -webkit-rtl-ordering: logical;
        cursor: text;
        background-color: -internal-light-dark(rgb(255, 255, 255), rgb(59, 59, 59));
        margin: 0em;
        padding: 1px 2px;
        border-width: 2px;
        border-style: inset;
        border-color: -internal-light-dark(rgb(118, 118, 118), rgb(133, 133, 133));
        border-image: initial;
        border-radius: 2px;
    }

    .content {
        background-color: #fff;
        padding: 15px;
        border-radius: 8px;
        margin-top: 30px;
    }

    .card {
        padding: 15px;
        border: none;
        background: rgb(125 125 125 / 5%);
    }

    .card input {
        display: block;
        width: 100%;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375 rem0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        padding-left: 15px;
    }

    .row {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
        margin-right: 1px;
    }

    .row button {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        vertical-align: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-color: transparent;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        cursor: pointer;
        color: #fff;
        background-color: #323a46;
        border-color: #323a46;
    }
</style>
<div class="container content">
    <div class="card">
        <p class="text-muted">卡密兑换</p>
        <input type="text" id="cdk" placeholder="输入cdk">
    </div>
    <div class="row">
        <button type="button" class="btn" user_id="<?php echo $user_id ?>">立即兑换</button>
    </div>
</div>

<script>
    jQuery(function($) {
        var info = {}
        $('.row button').click(function() {
            info.user_id = $('.row button').attr('user_id')
            info.code = $('#cdk').val()
            if (!info.code) {
                alert('请输入卡密')
                return
            }
            $.post('/wp-admin/admin-ajax.php', {
                    action: "useCdk",
                    data: info,
                },
                function(a) {
                    var data = JSON.parse(a)
                    alert(data.msg)
                    location.reload()
                })
        })
    })
</script>

<?php get_footer(); ?>