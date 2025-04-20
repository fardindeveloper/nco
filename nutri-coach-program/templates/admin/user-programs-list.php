<?php
/**
 * قالب لیست برنامه‌های کاربران
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

// دریافت لیست برنامه‌های کاربران از دیتابیس
global $wpdb;
$table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'user_programs';

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
        $status_condition = "WHERE u.display_name LIKE %s OR u.user_email LIKE %s";
        $status_params[] = '%' . $wpdb->esc_like($search) . '%';
        $status_params[] = '%' . $wpdb->esc_like($search) . '%';
    } else {
        $status_condition .= " AND (u.display_name LIKE %s OR u.user_email LIKE %s)";
        $status_params[] = '%' . $wpdb->esc_like($search) . '%';
        $status_params[] = '%' . $wpdb->esc_like($search) . '%';
    }
}

// شمارش کل برنامه‌ها
$total_query = "SELECT COUNT(*) FROM $table_name up JOIN {$wpdb->users} u ON up.user_id = u.ID $status_condition";
$total = $wpdb->get_var($wpdb->prepare($total_query, $status_params));

// دریافت برنامه‌ها با اطلاعات کاربر
$query = "SELECT up.*, u.display_name, u.user_email FROM $table_name up JOIN {$wpdb->users} u ON up.user_id = u.ID $status_condition ORDER BY up.id DESC LIMIT %d OFFSET %d";
$params = array_merge($status_params, array($per_page, $offset));
$programs = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);

// ایجاد صفحه‌بندی
$total_pages = ceil($total / $per_page);

// حذف برنامه در صورت درخواست
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && check_admin_referer('delete_program_' . $_GET['id'])) {
    $program_id = intval($_GET['id']);
    $wpdb->delete(
        $table_name,
        array('id' => $program_id),
        array('%d')
    );
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('برنامه با موفقیت حذف شد.', 'nutri-coach-program') . '</p></div>';
    
    // بارگذاری مجدد برنامه‌ها
    $programs = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
}

// تغییر وضعیت برنامه در صورت درخواست
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id']) && check_admin_referer('toggle_program_' . $_GET['id'])) {
    $program_id = intval($_GET['id']);
    
    // دریافت وضعیت فعلی
    $current_status = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT status FROM $table_name WHERE id = %d",
            $program_id
        )
    );
    
    // تغییر وضعیت
    $new_status = ($current_status === 'active') ? 'inactive' : 'active';
    
    $wpdb->update(
        $table_name,
        array('status' => $new_status),
        array('id' => $program_id),
        array('%s'),
        array('%d')
    );
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('وضعیت برنامه با موفقیت تغییر کرد.', 'nutri-coach-program') . '</p></div>';
    
    // بارگذاری مجدد برنامه‌ها
    $programs = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
}

// دریافت جدول قوانین
$rules_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('برنامه‌های کاربران', 'nutri-coach-program'); ?></h1>
    
    <hr class="wp-header-end">
    
    <form method="get" action="">
        <input type="hidden" name="page" value="nutri-coach-user-programs">
        
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
                <label class="screen-reader-text" for="program-search-input"><?php echo esc_html__('جستجوی برنامه‌ها:', 'nutri-coach-program'); ?></label>
                <input type="search" id="program-search-input" name="s" value="<?php echo esc_attr($search); ?>">
                <input type="submit" id="search-submit" class="button" value="<?php echo esc_attr__('جستجو', 'nutri-coach-program'); ?>">
            </div>
            
            <br class="clear">
        </div>
    </form>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-title column-primary"><?php echo esc_html__('کاربر', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('قانون تمرینی', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('تاریخ شروع', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('تاریخ پایان', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('وضعیت', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('پیشرفت', 'nutri-coach-program'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($programs)) : ?>
                <tr>
                    <td colspan="6"><?php echo esc_html__('هیچ برنامه‌ای یافت نشد.', 'nutri-coach-program'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($programs as $program) : ?>
                    <?php
                    // دریافت عنوان قانون
                    $rule_title = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT title FROM $rules_table WHERE id = %d",
                            $program['rule_id']
                        )
                    );
                    
                    // محاسبه آمار پیشرفت
                    $progress_tracker = new Nutri_Coach_Progress_Tracker();
                    $stats = $progress_tracker->get_progress_stats($program['user_id'], $program['id']);
                    $completion_rate = 0;
                    
                    if (isset($stats['exercise']['completion_rate']) && isset($stats['meal']['completion_rate'])) {
                        $completion_rate = round(($stats['exercise']['completion_rate'] + $stats['meal']['completion_rate']) / 2);
                    } elseif (isset($stats['exercise']['completion_rate'])) {
                        $completion_rate = $stats['exercise']['completion_rate'];
                    } elseif (isset($stats['meal']['completion_rate'])) {
                        $completion_rate = $stats['meal']['completion_rate'];
                    }
                    ?>
                    <tr>
                        <td class="title column-title has-row-actions column-primary">
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-programs&action=view&user_id=' . $program['user_id'] . '&program_id=' . $program['id']); ?>">
                                    <?php echo esc_html($program['display_name']); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="view">
                                    <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-programs&action=view&user_id=' . $program['user_id'] . '&program_id=' . $program['id']); ?>">
                                        <?php echo esc_html__('مشاهده', 'nutri-coach-program'); ?>
                                    </a> | 
                                </span>
                                <span class="edit">
                                    <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-programs&action=edit&user_id=' . $program['user_id'] . '&program_id=' . $program['id']); ?>">
                                        <?php echo esc_html__('ویرایش', 'nutri-coach-program'); ?>
                                    </a> | 
                                </span>
                                <span class="status">
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=nutri-coach-user-programs&action=toggle_status&id=' . $program['id']), 'toggle_program_' . $program['id']); ?>">
                                        <?php echo $program['status'] === 'active' ? esc_html__('غیرفعال کردن', 'nutri-coach-program') : esc_html__('فعال کردن', 'nutri-coach-program'); ?>
                                    </a> | 
                                </span>
                                <span class="delete">
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=nutri-coach-user-programs&action=delete&id=' . $program['id']), 'delete_program_' . $program['id']); ?>" class="submitdelete" onclick="return confirm('<?php echo esc_js(__('آیا از حذف این برنامه اطمینان دارید؟', 'nutri-coach-program')); ?>');">
                                        <?php echo esc_html__('حذف', 'nutri-coach-program'); ?>
                                    </a>
                                </span>
                            </div>
                            <button type="button" class="toggle-row"><span class="screen-reader-text"><?php echo esc_html__('نمایش جزئیات بیشتر', 'nutri-coach-program'); ?></span></button>
                        </td>
                        <td>
                            <?php 
                            if ($rule_title) {
                                echo '<a href="' . admin_url('admin.php?page=nutri-coach-rules&action=edit&id=' . $program['rule_id']) . '">' . esc_html($rule_title) . '</a>';
                            } else {
                                echo esc_html__('قانون یافت نشد', 'nutri-coach-program');
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo date_i18n(get_option('date_format'), strtotime($program['start_date'])); ?>
                        </td>
                        <td>
                            <?php 
                            if (!empty($program['end_date']) && $program['end_date'] !== '0000-00-00 00:00:00') {
                                echo date_i18n(get_option('date_format'), strtotime($program['end_date']));
                            } else {
                                echo esc_html__('نامشخص', 'nutri-coach-program');
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($program['status'] === 'active') {
                                echo '<span class="status-active">' . esc_html__('فعال', 'nutri-coach-program') . '</span>';
                            } else {
                                echo '<span class="status-inactive">' . esc_html__('غیرفعال', 'nutri-coach-program') . '</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-bar-inner" style="width: <?php echo esc_attr($completion_rate); ?>%;">
                                    <?php echo esc_html($completion_rate); ?>%
                                </div>
                            </div>
                            <a href="<?php echo admin_url('admin.php?page=nutri-coach-progress-reports&user_id=' . $program['user_id']); ?>" class="button button-small">
                                <?php echo esc_html__('گزارش پیشرفت', 'nutri-coach-program'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-title column-primary"><?php echo esc_html__('کاربر', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('قانون تمرینی', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('تاریخ شروع', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('تاریخ پایان', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('وضعیت', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('پیشرفت', 'nutri-coach-program'); ?></th>
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