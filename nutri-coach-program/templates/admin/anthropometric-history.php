<?php
// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$user_info = get_userdata($user_id);

if (!$user_info) {
    wp_die('کاربر موردنظر یافت نشد.');
}

// دریافت اطلاعات آنتروپومتریک کاربر
$anthropometric_data = get_user_meta($user_id, 'nutri_coach_anthropometric', true);
if (!is_array($anthropometric_data) || empty($anthropometric_data)) {
    $anthropometric_data = array();
}

// حذف اندازه‌گیری اگر درخواست شده باشد
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['measurement_index'])) {
    $index = intval($_GET['measurement_index']);
    
    // بررسی امنیتی
    check_admin_referer('delete_measurement_' . $index);
    
    if (isset($anthropometric_data[$index])) {
        unset($anthropometric_data[$index]);
        $anthropometric_data = array_values($anthropometric_data); // بازسازی ایندکس‌ها
        update_user_meta($user_id, 'nutri_coach_anthropometric', $anthropometric_data);
        echo '<div class="notice notice-success is-dismissible"><p>اندازه‌گیری با موفقیت حذف شد.</p></div>';
    }
}
?>

<div class="wrap">
    <h1>تاریخچه اندازه‌گیری‌های آنتروپومتریک</h1>
    <h2>کاربر: <?php echo esc_html($user_info->display_name); ?> (<?php echo esc_html($user_info->user_email); ?>)</h2>
    
    <div class="nutri-coach-tabs">
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-program&user_id=' . $user_id); ?>" class="tab">برنامه تمرینی و غذایی</a>
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-progress-report&user_id=' . $user_id); ?>" class="tab">گزارش پیشرفت</a>
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-anthropometric&user_id=' . $user_id); ?>" class="tab">اندازه‌گیری‌های بدن</a>
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-anthropometric-history&user_id=' . $user_id); ?>" class="tab active">تاریخچه اندازه‌گیری‌ها</a>
    </div>
    
    <div class="nutri-coach-box">
        <h3>تاریخچه اندازه‌گیری‌ها</h3>
        
        <?php if (empty($anthropometric_data)): ?>
            <p>هیچ اندازه‌گیری ثبت نشده است.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>تاریخ</th>
                        <th>وزن (kg)</th>
                        <th>قد (cm)</th>
                        <th>BMI</th>
                        <th>دور گردن</th>
                        <th>دور کمر</th>
                        <th>دور باسن</th>
                        <th>درصد چربی</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($anthropometric_data as $index => $measurement): ?>
                        <tr>
                            <td><?php echo esc_html($measurement['date']); ?></td>
                            <td><?php echo esc_html($measurement['weight']); ?></td>
                            <td><?php echo esc_html($measurement['height']); ?></td>
                            <td><?php echo esc_html($measurement['bmi']); ?> (<?php echo nutri_coach_bmi_category($measurement['bmi']); ?>)</td>
                            <td><?php echo isset($measurement['neck']) ? esc_html($measurement['neck']) : '-'; ?></td>
                            <td><?php echo isset($measurement['waist']) ? esc_html($measurement['waist']) : '-'; ?></td>
                            <td><?php echo isset($measurement['hip']) ? esc_html($measurement['hip']) : '-'; ?></td>
                            <td><?php echo esc_html($measurement['body_fat']); ?>%</td>
                            <td>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=nutri-coach-anthropometric-history&user_id=' . $user_id . '&action=delete&measurement_index=' . $index), 'delete_measurement_' . $index); ?>" class="button button-small" onclick="return confirm('آیا از حذف این اندازه‌گیری اطمینان دارید؟');">حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div id="measurement-chart-container" style="width: 100%; height: 400px; margin-top: 20px;">
                <canvas id="measurement-chart"></canvas>
            </div>
            
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('measurement-chart').getContext('2d');
                    
                    // تبدیل داده‌ها به فرمت مناسب Chart.js
                    const data = <?php echo json_encode($anthropometric_data); ?>;
                    const dates = data.map(item => item.date).reverse();
                    const weights = data.map(item => item.weight).reverse();
                    const bmis = data.map(item => item.bmi).reverse();
                    const bodyFats = data.map(item => item.body_fat).reverse();
                    
                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dates,
                            datasets: [
                                {
                                    label: 'وزن (کیلوگرم)',
                                    data: weights,
                                    borderColor: 'rgb(75, 192, 192)',
                                    tension: 0.1,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'BMI',
                                    data: bmis,
                                    borderColor: 'rgb(255, 99, 132)',
                                    tension: 0.1,
                                    yAxisID: 'y1'
                                },
                                {
                                    label: 'درصد چربی',
                                    data: bodyFats,
                                    borderColor: 'rgb(54, 162, 235)',
                                    tension: 0.1,
                                    yAxisID: 'y1'
                                }
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
                                        text: 'وزن (کیلوگرم)'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'BMI و درصد چربی'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        <?php endif; ?>
    </div>
</div> 
