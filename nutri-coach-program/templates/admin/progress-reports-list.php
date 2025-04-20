<?php
/**
 * قالب لیست گزارش‌های پیشرفت
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

// اطمینان از دسترسی به کلاس Progress_Tracker
if (!isset($nutri_coach_progress) || is_null($nutri_coach_progress)) {
    // اگر متغیر تعریف نشده، یک نمونه جدید ایجاد می‌کنیم
    require_once NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'includes/class-progress-tracker.php';
    $nutri_coach_progress = new Nutri_Coach_Progress_Tracker();
}

// پارامترهای صفحه‌بندی
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// فیلترها
$user_search = isset($_GET['user_search']) ? sanitize_text_field($_GET['user_search']) : '';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
$program_type = isset($_GET['program_type']) ? sanitize_text_field($_GET['program_type']) : '';

// دریافت گزارش‌های پیشرفت
// به جای استفاده از متد get_progress_reports، از متد get_user_progress استفاده می‌کنیم
// یا هر متد دیگری که در کلاس Progress_Tracker وجود دارد
$progress_reports = $nutri_coach_progress->get_user_progress(0, $user_search, $date_from, $date_to, $program_type, $offset, $per_page);
$total_reports = $nutri_coach_progress->get_total_progress(); // این متد را نیز اصلاح کنید بر اساس متدهای موجود

// ایجاد صفحه‌بندی
$total_pages = ceil($total_reports / $per_page);

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('گزارش‌های پیشرفت', 'nutri-coach-program'); ?></h1>
    
    <hr class="wp-header-end">
    
    <form method="get" action="">
        <input type="hidden" name="page" value="nutri-coach-progress-reports">
        
        <div class="nutri-coach-filters">
            <div class="filter-item">
                <label for="user_search"><?php echo esc_html__('جستجوی کاربر:', 'nutri-coach-program'); ?></label>
                <input type="text" id="user_search" name="user_search" value="<?php echo esc_attr($user_search); ?>" placeholder="نام یا ایمیل کاربر">
            </div>
            
            <div class="filter-item">
                <label for="date_from"><?php echo esc_html__('از تاریخ:', 'nutri-coach-program'); ?></label>
                <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>">
            </div>
            
            <div class="filter-item">
                <label for="date_to"><?php echo esc_html__('تا تاریخ:', 'nutri-coach-program'); ?></label>
                <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>">
            </div>
            
            <div class="filter-item">
                <label for="program_type"><?php echo esc_html__('نوع برنامه:', 'nutri-coach-program'); ?></label>
                <select id="program_type" name="program_type">
                    <option value=""><?php echo esc_html__('همه', 'nutri-coach-program'); ?></option>
                    <option value="workout" <?php selected($program_type, 'workout'); ?>><?php echo esc_html__('تمرینی', 'nutri-coach-program'); ?></option>
                    <option value="diet" <?php selected($program_type, 'diet'); ?>><?php echo esc_html__('غذایی', 'nutri-coach-program'); ?></option>
                </select>
            </div>
            
            <div class="filter-item">
                <button type="submit" class="button"><?php echo esc_html__('فیلتر', 'nutri-coach-program'); ?></button>
            </div>
        </div>
    </form>
    
    <div class="tablenav top">
        <div class="tablenav-pages">
            <?php if ($total_pages > 1) : ?>
                <span class="displaying-num">
                    <?php printf(
                        __('%s مورد', 'nutri-coach-program'),
                        number_format_i18n($total_reports)
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
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-primary"><?php echo esc_html__('کاربر', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('تاریخ', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('نوع برنامه', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('درصد تکمیل', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('توضیحات', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('عملیات', 'nutri-coach-program'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($progress_reports)) : ?>
                <tr>
                    <td colspan="6"><?php echo esc_html__('هیچ گزارشی یافت نشد.', 'nutri-coach-program'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($progress_reports as $report) : ?>
                    <?php $user_info = get_userdata($report['user_id']); ?>
                    <tr>
                        <td class="column-primary">
                            <?php if ($user_info) : ?>
                                <strong><a href="<?php echo admin_url('admin.php?page=nutri-coach-progress-report&user_id=' . $report['user_id']); ?>"><?php echo esc_html($user_info->display_name); ?></a></strong>
                                <div class="row-actions">
                                    <span class="view"><a href="<?php echo admin_url('admin.php?page=nutri-coach-progress-report&user_id=' . $report['user_id']); ?>"><?php echo esc_html__('مشاهده گزارش', 'nutri-coach-program'); ?></a> | </span>
                                    <span class="edit"><a href="<?php echo admin_url('admin.php?page=nutri-coach-user-program&user_id=' . $report['user_id']); ?>"><?php echo esc_html__('مشاهده برنامه', 'nutri-coach-program'); ?></a></span>
                                </div>
                            <?php else : ?>
                                <?php echo esc_html__('کاربر حذف شده', 'nutri-coach-program'); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($report['progress_date']); ?></td>
                        <td>
                           <?php
                            if ($report['program_type'] === 'workout') {
                                echo esc_html__('تمرینی', 'nutri-coach-program');
                            } elseif ($report['program_type'] === 'diet') {
                                echo esc_html__('غذایی', 'nutri-coach-program');
                            } else {
                                echo esc_html($report['program_type']);
                            }
                            ?>
                        </td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: <?php echo esc_attr($report['completed_percentage']); ?>%;">
                                    <span><?php echo esc_html($report['completed_percentage']); ?>%</span>
                                </div>
                            </div>
                        </td>
                        <td><?php echo esc_html($report['notes']); ?></td>
                        <td>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=nutri-coach-progress-reports&action=delete&report_id=' . $report['id']), 'delete_report_' . $report['id']); ?>" class="button button-small delete" onclick="return confirm('<?php echo esc_js(__('آیا از حذف این گزارش اطمینان دارید؟', 'nutri-coach-program')); ?>');"><?php echo esc_html__('حذف', 'nutri-coach-program'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-primary"><?php echo esc_html__('کاربر', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('تاریخ', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('نوع برنامه', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('درصد تکمیل', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('توضیحات', 'nutri-coach-program'); ?></th>
                <th scope="col" class="manage-column"><?php echo esc_html__('عملیات', 'nutri-coach-program'); ?></th>
            </tr>
        </tfoot>
    </table>
    
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php if ($total_pages > 1) : ?>
                <span class="displaying-num">
                    <?php printf(
                        __('%s مورد', 'nutri-coach-program'),
                        number_format_i18n($total_reports)
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