 
<?php
/**
 * قالب گزارش پیشرفت کاربر
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$user_info = get_userdata($user_id);

if (!$user_info) {
    wp_die('کاربر موردنظر یافت نشد.');
}

// دریافت برنامه و گزارش‌های پیشرفت کاربر
global $nutri_coach_progress;
$user_program = $nutri_coach_progress->get_user_program($user_id);
$progress_data = $nutri_coach_progress->get_user_progress($user_id);

// تنظیم تاریخ فعلی و محدوده تاریخ‌های نمودار
$current_date = current_time('Y-m-d');
$start_date = isset($user_program['start_date']) ? $user_program['start_date'] : $current_date;
$days_passed = max(0, floor((strtotime($current_date) - strtotime($start_date)) / (60 * 60 * 24)));
$completion_percentage = $nutri_coach_progress->calculate_program_completion($user_id);

// دریافت دوره‌های زمانی برای فیلتر
$period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'week';
$period_start = $current_date;

switch ($period) {
    case 'week':
        $period_start = date('Y-m-d', strtotime('-7 days', strtotime($current_date)));
        break;
    case 'month':
        $period_start = date('Y-m-d', strtotime('-30 days', strtotime($current_date)));
        break;
    case 'program':
        $period_start = $start_date;
        break;
    case 'all':
        // همه زمان‌ها
        break;
    case 'custom':
        $custom_start = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $custom_end = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
        
        if (!empty($custom_start)) {
            $period_start = $custom_start;
        }
        
        if (!empty($custom_end)) {
            $current_date = $custom_end;
        }
        break;
}

// دریافت آمار پیشرفت برای دوره انتخاب شده
$filtered_stats = $nutri_coach_progress->get_progress_stats($user_id, $period_start, $current_date);

// دریافت اندازه‌گیری‌های آنتروپومتریک
$anthropometric_data = get_user_meta($user_id, 'nutri_coach_anthropometric', true);
if (!is_array($anthropometric_data)) {
    $anthropometric_data = array();
}
?>

<div class="wrap">
    <h1>گزارش پیشرفت کاربر</h1>
    <h2>کاربر: <?php echo esc_html($user_info->display_name); ?> (<?php echo esc_html($user_info->user_email); ?>)</h2>
    
    <div class="nutri-coach-tabs">
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-program&user_id=' . $user_id); ?>" class="tab">برنامه تمرینی و غذایی</a>
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-progress-report&user_id=' . $user_id); ?>" class="tab active">گزارش پیشرفت</a>
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-anthropometric&user_id=' . $user_id); ?>" class="tab">اندازه‌گیری‌های بدن</a>
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-anthropometric-history&user_id=' . $user_id); ?>" class="tab">تاریخچه اندازه‌گیری‌ها</a>
    </div>
    
    <div class="nutri-coach-box">
        <h3>خلاصه برنامه</h3>
        
        <?php if (empty($user_program)) : ?>
            <p>هیچ برنامه‌ای برای این کاربر تعریف نشده است.</p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <th>تاریخ شروع:</th>
                    <td><?php echo esc_html($start_date); ?></td>
                    <th>روزهای سپری شده:</th>
                    <td><?php echo esc_html($days_passed); ?> روز</td>
                </tr>
                <tr>
                    <th>مدت کل برنامه:</th>
                    <td><?php echo esc_html(isset($user_program['program_duration']) ? $user_program['program_duration'] : 0); ?> روز</td>
                    <th>درصد پیشرفت کلی:</th>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: <?php echo esc_attr($completion_percentage); ?>%;">
                                <span><?php echo esc_html($completion_percentage); ?>%</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>وضعیت برنامه:</th>
                    <td colspan="3">
                        <?php
                        $status = isset($user_program['status']) ? $user_program['status'] : 'active';
                        switch ($status) {
                            case 'active':
                                echo '<span class="status-active">فعال</span>';
                                break;
                            case 'completed':
                                echo '<span class="status-completed">تکمیل شده</span>';
                                break;
                            case 'paused':
                                echo '<span class="status-paused">متوقف شده</span>';
                                break;
                            default:
                                echo esc_html($status);
                        }
                        ?>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="nutri-coach-box">
        <h3>گزارش پیشرفت</h3>
        
        <form method="get" action="" class="nutri-coach-filters">
            <input type="hidden" name="page" value="nutri-coach-progress-report">
            <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
            
            <div class="filter-item">
                <label for="period">دوره زمانی:</label>
                <select name="period" id="period" class="auto-submit">
                    <option value="week" <?php selected($period, 'week'); ?>>هفته اخیر</option>
                    <option value="month" <?php selected($period, 'month'); ?>>ماه اخیر</option>
                    <option value="program" <?php selected($period, 'program'); ?>>کل برنامه</option>
                    <option value="all" <?php selected($period, 'all'); ?>>همه زمان‌ها</option>
                    <option value="custom" <?php selected($period, 'custom'); ?>>دوره سفارشی</option>
                </select>
            </div>
            
            <div id="custom-period-container" style="<?php echo $period === 'custom' ? 'display: flex;' : 'display: none;'; ?>" class="filter-item">
                <div style="margin-right: 10px;">
                    <label for="start_date">از تاریخ:</label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr($period_start); ?>">
                </div>
                <div>
                    <label for="end_date">تا تاریخ:</label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr($current_date); ?>">
                </div>
            </div>
            
            <div class="filter-item">
                <button type="submit" class="button">اعمال فیلتر</button>
            </div>
        </form>
        
        <div class="nutri-coach-metrics">
            <div class="nutri-coach-metric-card">
                <h3>تعداد جلسات تمرین</h3>
                <div class="value"><?php echo esc_html($filtered_stats['workout_sessions']); ?></div>
            </div>
            
            <div class="nutri-coach-metric-card">
                <h3>درصد تکمیل تمرین‌ها</h3>
                <div class="value"><?php echo esc_html($filtered_stats['workout_completion']); ?>%</div>
            </div>
            
            <div class="nutri-coach-metric-card">
                <h3>تعداد وعده‌های غذایی</h3>
                <div class="value"><?php echo esc_html($filtered_stats['diet_sessions']); ?></div>
            </div>
            
            <div class="nutri-coach-metric-card">
                <h3>درصد تکمیل رژیم‌ها</h3>
                <div class="value"><?php echo esc_html($filtered_stats['diet_completion']); ?>%</div>
            </div>
        </div>
        
        <div class="nutri-coach-progress-chart">
            <canvas id="progress-chart"></canvas>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('progress-chart').getContext('2d');
                
                // تبدیل داده‌های پیشرفت به فرمت مناسب Chart.js
                const progressData = <?php echo json_encode($nutri_coach_progress->get_chart_data($user_id, $period_start, $current_date)); ?>;
                
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: progressData.dates,
                        datasets: [
                            {
                                label: 'پیشرفت تمرینی',
                                data: progressData.workout,
                                borderColor: 'rgb(75, 192, 192)',
                                tension: 0.1,
                                fill: false
                            },
                            {
                                label: 'پیشرفت رژیمی',
                                data: progressData.diet,
                                borderColor: 'rgb(255, 99, 132)',
                                tension: 0.1,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'درصد تکمیل'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'تاریخ'
                                }
                            }
                        }
                    }
                });
                
                // نمایش/مخفی کردن فیلتر دوره سفارشی
                document.getElementById('period').addEventListener('change', function() {
                    const customContainer = document.getElementById('custom-period-container');
                    customContainer.style.display = this.value === 'custom' ? 'flex' : 'none';
                });
            });
        </script>
    </div>
    
    <div class="nutri-coach-box">
        <h3>تاریخچه پیشرفت</h3>
        
        <?php if (empty($progress_data)) : ?>
            <p>هیچ رکوردی از پیشرفت کاربر یافت نشد.</p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>تاریخ</th>
                        <th>نوع برنامه</th>
                        <th>درصد تکمیل</th>
                        <th>توضیحات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($progress_data as $record) : ?>
                        <tr>
                            <td><?php echo esc_html($record['progress_date']); ?></td>
                            <td>
                                <?php
                                if ($record['program_type'] === 'workout') {
                                    echo 'تمرینی';
                                } elseif ($record['program_type'] === 'diet') {
                                    echo 'غذایی';
                                } else {
                                    echo esc_html($record['program_type']);
                                }
                                ?>
                            </td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-bar-fill" style="width: <?php echo esc_attr($record['completed_percentage']); ?>%;">
                                        <span><?php echo esc_html($record['completed_percentage']); ?>%</span>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo esc_html($record['notes']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($anthropometric_data)) : ?>
    <div class="nutri-coach-box">
        <h3>اندازه‌گیری‌های اخیر</h3>
        
        <table class="wp-list-table widefat fixed striped">
            <tr>
                <th>تاریخ:</th>
                <td><?php echo esc_html($anthropometric_data[0]['date']); ?></td>
                <th>وزن:</th>
                <td><?php echo esc_html($anthropometric_data[0]['weight']); ?> کیلوگرم</td>
            </tr>
            <tr>
                <th>BMI:</th>
                <td><?php echo esc_html($anthropometric_data[0]['bmi']); ?> (<?php echo nutri_coach_bmi_category($anthropometric_data[0]['bmi']); ?>)</td>
                <th>درصد چربی بدن:</th>
                <td><?php echo esc_html($anthropometric_data[0]['body_fat']); ?>% (<?php echo nutri_coach_body_fat_category($anthropometric_data[0]['body_fat'], get_user_meta($user_id, 'gender', true)); ?>)</td>
            </tr>
            <tr>
                <th>دور کمر:</th>
                <td><?php echo isset($anthropometric_data[0]['waist']) ? esc_html($anthropometric_data[0]['waist']) . ' سانتی‌متر' : '-'; ?></td>
                <th>دور باسن:</th>
                <td><?php echo isset($anthropometric_data[0]['hip']) ? esc_html($anthropometric_data[0]['hip']) . ' سانتی‌متر' : '-'; ?></td>
            </tr>
        </table>
        
        <p>
            <a href="<?php echo admin_url('admin.php?page=nutri-coach-anthropometric-history&user_id=' . $user_id); ?>" class="button">مشاهده تاریخچه کامل اندازه‌گیری‌ها</a>
        </p>
    </div>
    <?php endif; ?>
    
    <div class="nutri-coach-box">
        <h3>ثبت پیشرفت جدید</h3>
        
        <form method="post" action="" class="record-progress-form">
            <?php wp_nonce_field('nutri_coach_record_progress', 'nutri_coach_progress_nonce'); ?>
            <input type="hidden" name="action" value="nutri_coach_record_progress">
            <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
            
            <table class="form-table">
                <tr>
                    <th><label for="progress_date">تاریخ:</label></th>
                    <td>
                        <input type="date" id="progress_date" name="progress_date" value="<?php echo esc_attr(current_time('Y-m-d')); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="program_type">نوع برنامه:</label></th>
                    <td>
                        <select id="program_type" name="program_type" class="regular-text" required>
                            <option value="workout">برنامه تمرینی</option>
                            <option value="diet">برنامه غذایی</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="completed_percentage">درصد تکمیل:</label></th>
                    <td>
                        <input type="range" id="completed_percentage" name="completed_percentage" min="0" max="100" value="0" step="5" oninput="updateProgressValue(this.value)">
                        <span id="progress_value">0%</span>
                    </td>
                </tr>
                <tr>
                    <th><label for="notes">توضیحات:</label></th>
                    <td>
                        <textarea id="notes" name="notes" rows="4" class="regular-text"></textarea>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary">ثبت پیشرفت</button>
            </p>
            
            <div class="message"></div>
        </form>
        
        <script>
            function updateProgressValue(value) {
                document.getElementById('progress_value').textContent = value + '%';
            }
        </script>
    </div>
</div>