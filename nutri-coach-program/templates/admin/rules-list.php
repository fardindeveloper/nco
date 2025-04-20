<?php
/**
 * قالب لیست قوانین تمرینی
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

// دریافت لیست قوانین از دیتابیس
global $wpdb;
$table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';

// پارامترهای صفحه‌بندی
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// بررسی وضعیت فعال/غیرفعال
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$status_condition = '';
$status_params = array();

if ($status_filter !== '') {
    $status_condition = "WHERE status = %s";
    $status_params[] = $status_filter;
}

// جستجو
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
if (!empty($search)) {
    if (empty($status_condition)) {
        $status_condition = "WHERE title LIKE %s";
    } else {
        $status_condition .= " AND title LIKE %s";
    }
    $status_params[] = '%' . $wpdb->esc_like($search) . '%';
}

// شمارش کل قوانین
$total_query = "SELECT COUNT(*) FROM $table_name $status_condition";
$total = $wpdb->get_var($wpdb->prepare($total_query, $status_params));

// دریافت قوانین
$query = "SELECT * FROM $table_name $status_condition ORDER BY id DESC LIMIT %d OFFSET %d";
$params = array_merge($status_params, array($per_page, $offset));
$rules = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);

// ایجاد صفحه‌بندی
$total_pages = ceil($total / $per_page);

// حذف قانون در صورت درخواست
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && check_admin_referer('delete_rule_' . $_GET['id'])) {
    $rule_id = intval($_GET['id']);
    $wpdb->delete(
        $table_name,
        array('id' => $rule_id),
        array('%d')
    );
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('قانون با موفقیت حذف شد.', 'nutri-coach-program') . '</p></div>';
    
    // بارگذاری مجدد قوانین
    $rules = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
}

// تغییر وضعیت قانون در صورت درخواست
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id']) && check_admin_referer('toggle_rule_' . $_GET['id'])) {
    $rule_id = intval($_GET['id']);
    
    // دریافت وضعیت فعلی
    $current_status = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT status FROM $table_name WHERE id = %d",
            $rule_id
        )
    );
    
    // تغییر وضعیت
    $new_status = ($current_status === 'active') ? 'inactive' : 'active';
    
    $wpdb->update(
        $table_name,
        array('status' => $new_status),
        array('id' => $rule_id),
        array('%s'),
        array('%d')
    );
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('وضعیت قانون با موفقیت تغییر کرد.', 'nutri-coach-program') . '</p></div>';
    
    // بارگذاری مجدد قوانین
    $rules = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('قوانین تمرینی', 'nutri-coach-program'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=nutri-coach-rules&action=add'); ?>" class="page-title-action"><?php echo esc_html__('افزودن قانون جدید', 'nutri-coach-program'); ?></a>
    
    <hr class="wp-header-end">
    
    <form method="get" action="">
        <input type="hidden" name="page" value="nutri-coach-rules">
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="status">
                    <option value=""><?php echo esc_html__('همه وضعیت‌ها', 'nutri-coach-program'); ?></option>
                    <option value="active" <?php selected($status_filter, 'active'); ?>><?php echo esc_html__('فعال', 'nutri-coach-program'); ?></option>
                    <option value="inactive" <?php selected($status_filter, 'inactive'); ?>><?php echo esc_html__('غیرفعال', 'nutri-coach-program'); ?></option>
                </select>
                <input type="submit" class="button" value="<?php echo esc_attr__('فیلتر', 'nutri-coach-program'); ?>">
            </div>
            
            <div class="tablenav-pages">
                <?php if ($total_pages > 1) : ?>
                    <span class="displaying-num">
                        <?php printf(
                            __('%s مورد', 'nutri-coach-program'),
                            number_format_i18n($total)
                        ); ?>
                    </span>
                    <span class="pagination-links">
                        <?php
                        // لینک صفحه اول
                        if ($current_page > 1) {
                            echo '<a class="first-page button" href="' . add_query_arg('paged', 1) . '"><span class="screen-reader-text">' . __('صفحه اول', 'nutri-coach-program') . '</span><span aria-hidden="true">&laquo;</span></a>';
                        } else {
                            echo '<span class="first-page button disabled"><span class="screen-reader-text">' . __('صفحه اول', 'nutri-coach-program') . '</span><span aria-hidden="true">&laquo;</span></span>';
                        }
                        
                        // لینک صفحه قبل
                        if ($current_page > 1) {
                            echo '<a class="prev-page button" href="' . add_query_arg('paged', max(1, $current_page - 1)) . '"><span class="screen-reader-text">' . __('صفحه قبل', 'nutri-coach-program') . '</span><span aria-hidden="true">&lsaquo;</span></a>';
                        } else {
                            echo '<span class="prev-page button disabled"><span class="screen-reader-text">' . __('صفحه قبل', 'nutri-coach-program') . '</span><span aria-hidden="true">&lsaquo;</span></span>';
                        }
                        
                        // شماره صفحه فعلی
                        echo '<span class="paging-input">' . sprintf(
                            '<span class="current-page">%s</span> ' . __('از', 'nutri-coach-program') . ' <span class="total-pages">%s</span>',
                            $current_page,
                            $total_pages
                        ) . '</span>';
                        
                        // لینک صفحه بعد
                        if ($current_page < $total_pages) {
                            echo '<a class="next-page button" href="' . add_query_arg('paged', min($total_pages, $current_page + 1)) . '"><span class="screen-reader-text">' . __('صفحه بعد', 'nutri-coach-program') . '</span><span aria-hidden="true">&rsaquo;</span></a>';
                        } else {
                            echo '<span class="next-page button disabled"><span class="screen-reader-text">' . __('صفحه بعد', 'nutri-coach-program') . '</span><span aria-hidden="true">&rsaquo;</span></span>';
                        }
                        
                        // لینک صفحه آخر
                        if ($current_page < $total_pages) {
                            echo '<a class="last-page button" href="' . add_query_arg('paged', $total_pages) . '"><span class="screen-reader-text">' . __('صفحه آخر', 'nutri-coach-program') . '</span><span aria-hidden="true">&raquo;</span></a>';
                        } else {
                            echo '<span class="last-page button disabled"><span class="screen-reader-text">' . __('صفحه آخر', 'nutri-coach-program') . '</span><span aria-hidden="true">&raquo;</span></span>';
                        }
                        ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="search-box">
                <label class="screen-reader-text" for="rule-search-input"><?php echo esc_html__('جستجوی قوانین:', 'nutri-coach-program'); ?></label>
                <input type="search" id="rule-search-input" name="s" value="<?php echo esc_attr($search); ?>">
                <input type="submit" id="search-submit" class="button" value="<?php echo esc_attr__('جستجو', 'nutri-coach-program'); ?>">
            </div>
            
            <br class="clear">
        </div>
    </form>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-title column-primary"><?php echo esc_html__('عنوان', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('جنسیت', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('سن', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('هدف ورزشی', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('شدت تمرین', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('وضعیت', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('تاریخ ایجاد', 'nutri-coach-program'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rules)) : ?>
                <tr>
                    <td colspan="7"><?php echo esc_html__('هیچ قانونی یافت نشد.', 'nutri-coach-program'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($rules as $rule) : ?>
                    <tr>
                        <td class="title column-title has-row-actions column-primary">
                            <strong><a href="<?php echo admin_url('admin.php?page=nutri-coach-rules&action=edit&id=' . $rule['id']); ?>"><?php echo esc_html($rule['title']); ?></a></strong>
                            <div class="row-actions">
                                <span class="edit"><a href="<?php echo admin_url('admin.php?page=nutri-coach-rules&action=edit&id=' . $rule['id']); ?>"><?php echo esc_html__('ویرایش', 'nutri-coach-program'); ?></a> | </span>
                                <span class="status"><a href="<?php echo wp_nonce_url(admin_url('admin.php?page=nutri-coach-rules&action=toggle_status&id=' . $rule['id']), 'toggle_rule_' . $rule['id']); ?>"><?php echo $rule['status'] === 'active' ? esc_html__('غیرفعال کردن', 'nutri-coach-program') : esc_html__('فعال کردن', 'nutri-coach-program'); ?></a> | </span>
                                <span class="delete"><a href="<?php echo wp_nonce_url(admin_url('admin.php?page=nutri-coach-rules&action=delete&id=' . $rule['id']), 'delete_rule_' . $rule['id']); ?>" class="submitdelete" onclick="return confirm('<?php echo esc_js(__('آیا از حذف این قانون اطمینان دارید؟', 'nutri-coach-program')); ?>');"><?php echo esc_html__('حذف', 'nutri-coach-program'); ?></a></span>
                            </div>
                        </td>
                        <td>
                            <?php
                            if (empty($rule['gender'])) {
                                echo esc_html__('همه', 'nutri-coach-program');
                            } elseif ($rule['gender'] === 'male') {
                                echo esc_html__('مرد', 'nutri-coach-program');
                            } elseif ($rule['gender'] === 'female') {
                                echo esc_html__('زن', 'nutri-coach-program');
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($rule['age_min'] == 0 && $rule['age_max'] == 999) {
                                echo esc_html__('همه سنین', 'nutri-coach-program');
                            } elseif ($rule['age_min'] == 0) {
                                printf(__('تا %d سال', 'nutri-coach-program'), $rule['age_max']);
                            } elseif ($rule['age_max'] == 999) {
                                printf(__('از %d سال', 'nutri-coach-program'), $rule['age_min']);
                            } else {
                                printf(__('%d تا %d سال', 'nutri-coach-program'), $rule['age_min'], $rule['age_max']);
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (empty($rule['fitness_goal'])) {
                                echo esc_html__('همه اهداف', 'nutri-coach-program');
                            } elseif ($rule['fitness_goal'] === 'weight_loss') {
                                echo esc_html__('کاهش وزن', 'nutri-coach-program');
                            } elseif ($rule['fitness_goal'] === 'muscle_gain') {
                                echo esc_html__('افزایش عضله', 'nutri-coach-program');
                            } elseif ($rule['fitness_goal'] === 'maintenance') {
                                echo esc_html__('حفظ وزن فعلی', 'nutri-coach-program');
                            } elseif ($rule['fitness_goal'] === 'overall_health') {
                                echo esc_html__('سلامت عمومی', 'nutri-coach-program');
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (empty($rule['intensity'])) {
                                echo esc_html__('همه شدت‌ها', 'nutri-coach-program');
                            } elseif ($rule['intensity'] === 'low') {
                                echo esc_html__('کم', 'nutri-coach-program');
                            } elseif ($rule['intensity'] === 'medium') {
                                echo esc_html__('متوسط', 'nutri-coach-program');
                            } elseif ($rule['intensity'] === 'high') {
                                echo esc_html__('زیاد', 'nutri-coach-program');
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($rule['status'] === 'active') {
                                echo '<span class="status-active">' . esc_html__('فعال', 'nutri-coach-program') . '</span>';
                            } else {
                                echo '<span class="status-inactive">' . esc_html__('غیرفعال', 'nutri-coach-program') . '</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo date_i18n(get_option('date_format'), strtotime($rule['date_created'])); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-title column-primary"><?php echo esc_html__('عنوان', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('جنسیت', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('سن', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('هدف ورزشی', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('شدت تمرین', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('وضعیت', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('تاریخ ایجاد', 'nutri-coach-program'); ?></th>
            </tr>
        </tfoot>
    </table>
    
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php if ($total_pages > 1) : ?>
                <span class="displaying-num">
                    <?php printf(
                        __('%s مورد', 'nutri-coach-program'),
                        number_format_i18n($total)
                    ); ?>
                </span>
                <span class="pagination-links">
                    <?php
                    // لینک صفحه اول
                    if ($current_page > 1) {
                        echo '<a class="first-page button" href="' . add_query_arg('paged', 1) . '"><span class="screen-reader-text">' . __('صفحه اول', 'nutri-coach-program') . '</span><span aria-hidden="true">&laquo;</span></a>';
                    } else {
                        echo '<span class="first-page button disabled"><span class="screen-reader-text">' . __('صفحه اول', 'nutri-coach-program') . '</span><span aria-hidden="true">&laquo;</span></span>';
                    }
                    
                    // لینک صفحه قبل
                    if ($current_page > 1) {
                        echo '<a class="prev-page button" href="' . add_query_arg('paged', max(1, $current_page - 1)) . '"><span class="screen-reader-text">' . __('صفحه قبل', 'nutri-coach-program') . '</span><span aria-hidden="true">&lsaquo;</span></a>';
                    } else {
                        echo '<span class="prev-page button disabled"><span class="screen-reader-text">' . __('صفحه قبل', 'nutri-coach-program') . '</span><span aria-hidden="true">&lsaquo;</span></span>';
                    }
                    
                    // شماره صفحه فعلی
                    echo '<span class="paging-input">' . sprintf(
                        '<span class="current-page">%s</span> ' . __('از', 'nutri-coach-program') . ' <span class="total-pages">%s</span>',
                        $current_page,
                        $total_pages
                    ) . '</span>';
                    
                    // لینک صفحه بعد
                    if ($current_page < $total_pages) {
                        echo '<a class="next-page button" href="' . add_query_arg('paged', min($total_pages, $current_page + 1)) . '"><span class="screen-reader-text">' . __('صفحه بعد', 'nutri-coach-program') . '</span><span aria-hidden="true">&rsaquo;</span></a>';
                    } else {
                        echo '<span class="next-page button disabled"><span class="screen-reader-text">' . __('صفحه بعد', 'nutri-coach-program') . '</span><span aria-hidden="true">&rsaquo;</span></span>';
                    }
                    
                    // لینک صفحه آخر
                    if ($current_page < $total_pages) {
                        echo '<a class="last-page button" href="' . add_query_arg('paged', $total_pages) . '"><span class="screen-reader-text">' . __('صفحه آخر', 'nutri-coach-program') . '</span><span aria-hidden="true">&raquo;</span></a>';
                    } else {
                        echo '<span class="last-page button disabled"><span class="screen-reader-text">' . __('صفحه آخر', 'nutri-coach-program') . '</span><span aria-hidden="true">&raquo;</span></span>';
                    }
                    ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>