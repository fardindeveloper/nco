<?php
/**
 * قالب نمایش پیشرفت کاربر
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

// دریافت پارامترهای شورت‌کد
$user_id = $atts['user_id'];
$type = $atts['type']; // workout, diet, both
$period = $atts['period']; // week, month, year, all

// دریافت اطلاعات کاربر
$user_data = apply_filters('nutri_coach_get_user_data', array(), $user_id);

// بررسی آیا کاربر برنامه فعال دارد
if (empty($user_data['has_program']) || !$user_data['has_program']) {
    echo '<div class="nutri-coach-notice">';
    echo '<p>' . __('شما هنوز برنامه فعالی ندارید. لطفاً با مربی خود تماس بگیرید.', 'nutri-coach-program') . '</p>';
    echo '</div>';
    return;
}

$program_id = $user_data['program_id'];

// دریافت آمار پیشرفت
$progress_tracker = new Nutri_Coach_Progress_Tracker();
$stats = $progress_tracker->get_progress_stats($user_id, $program_id);
$chart_data = $progress_tracker->get_chart_data($user_id, $program_id, $period);

// تنظیم متن دوره زمانی
$period_text = '';
switch ($period) {
    case 'week':
        $period_text = __('هفته گذشته', 'nutri-coach-program');
        break;
    case 'month':
        $period_text = __('ماه گذشته', 'nutri-coach-program');
        break;
    case 'year':
        $period_text = __('سال گذشته', 'nutri-coach-program');
        break;
    case 'all':
        $period_text = __('همه زمان‌ها', 'nutri-coach-program');
        break;
}

?>

<div class="nutri-coach-progress-container">
    <h2><?php echo __('گزارش پیشرفت', 'nutri-coach-program'); ?></h2>
    
    <div class="nutri-coach-period-selector">
        <form method="get">
            <?php foreach ($_GET as $key => $value) : ?>
                <?php if ($key !== 'period') : ?>
                    <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            
            <label for="period-select"><?php echo __('دوره زمانی:', 'nutri-coach-program'); ?></label>
            <select name="period" id="period-select" onchange="this.form.submit()">
                <option value="week" <?php selected($period, 'week'); ?>><?php echo __('هفته گذشته', 'nutri-coach-program'); ?></option>
                <option value="month" <?php selected($period, 'month'); ?>><?php echo __('ماه گذشته', 'nutri-coach-program'); ?></option>
                <option value="year" <?php selected($period, 'year'); ?>><?php echo __('سال گذشته', 'nutri-coach-program'); ?></option>
                <option value="all" <?php selected($period, 'all'); ?>><?php echo __('همه زمان‌ها', 'nutri-coach-program'); ?></option>
            </select>
        </form>
    </div>
    
    <div class="nutri-coach-progress-summary">
        <div class="nutri-coach-card">
            <h3><?php echo __('خلاصه پیشرفت', 'nutri-coach-program'); ?></h3>
            <p><?php echo sprintf(__('آخرین به‌روزرسانی: %s', 'nutri-coach-program'), wp_date(get_option('date_format'))); ?></p>
            
            <div class="nutri-coach-stats-grid">
                <div class="nutri-coach-stat">
                    <div class="nutri-coach-stat-title"><?php echo __('پیشرفت تمرین‌ها', 'nutri-coach-program'); ?></div>
                    <div class="nutri-coach-stat-value">
                        <div class="progress-bar">
                            <div class="progress-bar-inner" style="width: <?php echo esc_attr($stats['exercise']['completion_rate']); ?>%;">
                                <?php echo esc_html($stats['exercise']['completion_rate']); ?>%
                            </div>
                        </div>
                    </div>
                    <div class="nutri-coach-stat-detail">
                        <?php echo sprintf(
                            __('%d از %d تمرین تکمیل شده', 'nutri-coach-program'),
                            $stats['exercise']['completed'],
                            $stats['exercise']['total']
                        ); ?>
                    </div>
                </div>
                
                <div class="nutri-coach-stat">
                    <div class="nutri-coach-stat-title"><?php echo __('پیشرفت برنامه غذایی', 'nutri-coach-program'); ?></div>
                    <div class="nutri-coach-stat-value">
                        <div class="progress-bar">
                            <div class="progress-bar-inner" style="width: <?php echo esc_attr($stats['meal']['completion_rate']); ?>%;">
                                <?php echo esc_html($stats['meal']['completion_rate']); ?>%
                            </div>
                        </div>
                    </div>
                    <div class="nutri-coach-stat-detail">
                        <?php echo sprintf(
                            __('%d از %d وعده تکمیل شده', 'nutri-coach-program'),
                            $stats['meal']['completed'],
                            $stats['meal']['total']
                        ); ?>
                    </div>
                </div>
                
                <?php if (!empty($stats['weight_trend'])) : ?>
                    <div class="nutri-coach-stat">
                        <div class="nutri-coach-stat-title"><?php echo __('تغییرات وزن', 'nutri-coach-program'); ?></div>
                        <div class="nutri-coach-stat-value">
                            <?php
                            // نمایش تغییرات وزن
                            $first_weight = end($stats['weight_trend']);
                            $last_weight = reset($stats['weight_trend']);
                            
                            if ($first_weight && $last_weight) {
                                $weight_change = $last_weight['weight'] - $first_weight['weight'];
                                
                                if ($weight_change > 0) {
                                    echo '<span class="weight-increase">+' . number_format_i18n($weight_change, 1) . ' ' . __('کیلوگرم', 'nutri-coach-program') . '</span>';
                                } elseif ($weight_change < 0) {
                                    echo '<span class="weight-decrease">' . number_format_i18n($weight_change, 1) . ' ' . __('کیلوگرم', 'nutri-coach-program') . '</span>';
                                } else {
                                    echo '<span class="weight-unchanged">0 ' . __('کیلوگرم', 'nutri-coach-program') . '</span>';
                                }
                            } else {
                                echo __('اطلاعات ناکافی', 'nutri-coach-program');
                            }
                            ?>
                        </div>
                        <div class="nutri-coach-stat-detail">
                            <?php
                            if ($first_weight && $last_weight) {
                                echo sprintf(
                                    __('از %s تا %s', 'nutri-coach-program'),
                                    wp_date(get_option('date_format'), strtotime($first_weight['date'])),
                                    wp_date(get_option('date_format'), strtotime($last_weight['date']))
                                );
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="nutri-coach-progress-charts">
        <div class="nutri-coach-card">
            <h3><?php echo sprintf(__('نمودار پیشرفت - %s', 'nutri-coach-program'), $period_text); ?></h3>
            
            <div class="chart-container">
                <canvas id="progressChart"></canvas>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var ctx = document.getElementById('progressChart').getContext('2d');
                
                // تبدیل تاریخ‌ها به فرمت مناسب
                var labels = <?php echo json_encode(array_map(function($date) {
                    return wp_date(get_option('date_format'), strtotime($date));
                }, $chart_data['labels'])); ?>;
                
                var workoutData = <?php echo json_encode($chart_data['exercise']['completion_rate']); ?>;
                var mealData = <?php echo json_encode($chart_data['meal']['completion_rate']); ?>;
                var weightData = <?php echo json_encode($chart_data['weight']); ?>;
                
                var progressChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: '<?php echo __('تمرین‌ها (%)', 'nutri-coach-program'); ?>',
                                data: workoutData,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: false,
                                yAxisID: 'y'
                            },
                            {
                                label: '<?php echo __('برنامه غذایی (%)', 'nutri-coach-program'); ?>',
                                data: mealData,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: false,
                                yAxisID: 'y'
                            },
                            <?php if (!empty($stats['weight_trend'])) : ?>
                            {
                                label: '<?php echo __('وزن (کیلوگرم)', 'nutri-coach-program'); ?>',
                                data: weightData,
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: false,
                                yAxisID: 'y1'
                            }
                            <?php endif; ?>
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: '<?php echo __('درصد تکمیل', 'nutri-coach-program'); ?>'
                                },
                                min: 0,
                                max: 100
                            },
                            <?php if (!empty($stats['weight_trend'])) : ?>
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: '<?php echo __('وزن (کیلوگرم)', 'nutri-coach-program'); ?>'
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                            }
                            <?php endif; ?>
                        }
                    }
                });
            });
            </script>
        </div>
    </div>
    
    <?php if ($type === 'workout' || $type === 'both') : ?>
        <div class="nutri-coach-workout-progress">
            <div class="nutri-coach-card">
                <h3><?php echo __('پیشرفت تمرین‌های اخیر', 'nutri-coach-program'); ?></h3>
                
                <?php
                // دریافت پیشرفت تمرین‌های اخیر
                $recent_exercises = $progress_tracker->get_exercise_progress($user_id, $program_id, 'week');
                
                if (empty($recent_exercises)) {
                    echo '<p>' . __('هنوز هیچ تمرینی ثبت نشده است.', 'nutri-coach-program') . '</p>';
                } else {
                    // گروه‌بندی بر اساس تاریخ
                    $grouped_exercises = array();
                    foreach ($recent_exercises as $exercise) {
                        $date = wp_date(get_option('date_format'), strtotime($exercise['date']));
                        if (!isset($grouped_exercises[$date])) {
                            $grouped_exercises[$date] = array();
                        }
                        $grouped_exercises[$date][] = $exercise;
                    }
                    
                    // نمایش تمرین‌ها به تفکیک روز
                    foreach ($grouped_exercises as $date => $day_exercises) {
                        echo '<div class="exercise-day">';
                        echo '<h4>' . $date . '</h4>';
                        
                        echo '<table class="nutri-coach-exercise-table">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>' . __('تمرین', 'nutri-coach-program') . '</th>';
                        echo '<th>' . __('ست', 'nutri-coach-program') . '</th>';
                        echo '<th>' . __('تکرار', 'nutri-coach-program') . '</th>';
                        echo '<th>' . __('وزنه (کیلوگرم)', 'nutri-coach-program') . '</th>';
                        echo '<th>' . __('وضعیت', 'nutri-coach-program') . '</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($day_exercises as $exercise) {
                            echo '<tr>';
                            echo '<td>' . esc_html($exercise['exercise_id']) . '</td>';
                            echo '<td>' . esc_html($exercise['sets']) . '</td>';
                            echo '<td>' . esc_html($exercise['reps']) . '</td>';
                            echo '<td>' . esc_html($exercise['weight']) . '</td>';
                            echo '<td>';
                            if ($exercise['completed']) {
                                echo '<span class="status-completed">' . __('انجام شده', 'nutri-coach-program') . '</span>';
                            } else {
                                echo '<span class="status-incomplete">' . __('انجام نشده', 'nutri-coach-program') . '</span>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($type === 'diet' || $type === 'both') : ?>
        <div class="nutri-coach-meal-progress">
            <div class="nutri-coach-card">
                <h3><?php echo __('پیشرفت وعده‌های غذایی اخیر', 'nutri-coach-program'); ?></h3>
                
                <?php
                // دریافت پیشرفت وعده‌های غذایی اخیر
                $recent_meals = $progress_tracker->get_meal_progress($user_id, $program_id, 'week');
                
                if (empty($recent_meals)) {
                    echo '<p>' . __('هنوز هیچ وعده غذایی ثبت نشده است.', 'nutri-coach-program') . '</p>';
                } else {
                    // گروه‌بندی بر اساس تاریخ
                    $grouped_meals = array();
                    foreach ($recent_meals as $meal) {
                        $date = wp_date(get_option('date_format'), strtotime($meal['date']));
                        if (!isset($grouped_meals[$date])) {
                            $grouped_meals[$date] = array();
                        }
                        $grouped_meals[$date][] = $meal;
                    }
                    
                    // نمایش وعده‌ها به تفکیک روز
                    foreach ($grouped_meals as $date => $day_meals) {
                        echo '<div class="meal-day">';
                        echo '<h4>' . $date . '</h4>';
                        
                        echo '<table class="nutri-coach-meal-table">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>' . __('وعده', 'nutri-coach-program') . '</th>';
                        echo '<th>' . __('یادداشت', 'nutri-coach-program') . '</th>';
                        echo '<th>' . __('وضعیت', 'nutri-coach-program') . '</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($day_meals as $meal) {
                            echo '<tr>';
                            echo '<td>' . esc_html($meal['meal_id']) . '</td>';
                            echo '<td>' . esc_html($meal['notes']) . '</td>';
                            echo '<td>';
                            if ($meal['completed']) {
                                echo '<span class="status-completed">' . __('انجام شده', 'nutri-coach-program') . '</span>';
                            } else {
                                echo '<span class="status-incomplete">' . __('انجام نشده', 'nutri-coach-program') . '</span>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</div> 
