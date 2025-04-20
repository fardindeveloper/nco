<?php
// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>داشبورد نوتری کوچ</h1>
    
    <div class="nutri-coach-dashboard">
        <div class="nutri-coach-stats">
            <div class="nutri-coach-stat-card">
                <div class="stat-icon dashicons dashicons-groups"></div>
                <div class="stat-content">
                    <h3>کاربران</h3>
                    <div class="stat-value"><?php echo esc_html($total_users['total_users']); ?></div>
                </div>
            </div>
            
            <div class="nutri-coach-stat-card">
                <div class="stat-icon dashicons dashicons-calendar-alt"></div>
                <div class="stat-content">
                    <h3>برنامه‌های فعال</h3>
                    <div class="stat-value"><?php echo esc_html($active_programs); ?></div>
                </div>
            </div>
            
            <div class="nutri-coach-stat-card">
                <div class="stat-icon dashicons dashicons-list-view"></div>
                <div class="stat-content">
                    <h3>کل برنامه‌ها</h3>
                    <div class="stat-value"><?php echo esc_html($total_programs); ?></div>
                </div>
            </div>
            
            <div class="nutri-coach-stat-card">
                <div class="stat-icon dashicons dashicons-media-spreadsheet"></div>
                <div class="stat-content">
                    <h3>قوانین تعریف شده</h3>
                    <div class="stat-value"><?php echo esc_html($total_rules); ?></div>
                </div>
            </div>
            
            <div class="nutri-coach-stat-card">
                <div class="stat-icon dashicons dashicons-chart-line"></div>
                <div class="stat-content">
                    <h3>رکوردهای پیشرفت</h3>
                    <div class="stat-value"><?php echo esc_html($total_progress_logs); ?></div>
                </div>
            </div>
        </div>
        
        <div class="nutri-coach-boxes">
            <div class="nutri-coach-box">
                <h2>آمار فعالیت‌ها</h2>
                <div id="activity-chart-container" style="height: 300px;">
                    <canvas id="activity-chart"></canvas>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('activity-chart').getContext('2d');
                        
                        // داده‌های نمودار
                        const chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'],
                                datasets: [
                                    {
                                        label: 'برنامه‌های جدید',
                                        data: [12, 19, 8, 15, 20, 22, 25, 18, 23, 15, 18, 10],
                                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                        borderColor: 'rgba(54, 162, 235, 1)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'رکوردهای پیشرفت',
                                        data: [50, 65, 40, 55, 70, 75, 80, 60, 75, 50, 60, 45],
                                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    });
                </script>
            </div>
            
            <div class="nutri-coach-box">
                <h2>فعالیت‌های اخیر</h2>
                <?php if (empty($recent_activities)): ?>
                    <p>هیچ فعالیتی یافت نشد.</p>
                <?php else: ?>
                    <ul class="nutri-coach-activity-list">
                        <?php foreach ($recent_activities as $activity): ?>
                            <li class="activity-item">
                               <div class="activity-icon dashicons <?php echo esc_attr($activity['icon']); ?>"></div>
                <div class="activity-content">
                    <div class="activity-description"><?php echo esc_html($activity['description']); ?></div>
                    <div class="activity-meta">
                        <span class="activity-time"><?php echo esc_html($activity['time']); ?></span>
                        <span class="activity-user"><?php echo esc_html($activity['user']); ?></span>
                    </div>
                </div>
            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <p class="activity-more">
                    <a href="<?php echo admin_url('admin.php?page=nutri-coach-progress-reports'); ?>" class="button">مشاهده همه فعالیت‌ها</a>
                </p>
            </div>
        </div>
        
        <div class="nutri-coach-boxes">
            <div class="nutri-coach-box">
                <h2>آخرین برنامه‌های کاربران</h2>
                <?php
                // دریافت آخرین برنامه‌های کاربران
                $latest_programs = $nutri_coach_progress->get_user_programs(0, 5);
                ?>
                
                <?php if (empty($latest_programs)): ?>
                    <p>هیچ برنامه‌ای یافت نشد.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>کاربر</th>
                                <th>تاریخ شروع</th>
                                <th>مدت برنامه</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latest_programs as $program): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $user_info = get_userdata($program['user_id']);
                                        echo esc_html($user_info ? $user_info->display_name : 'کاربر نامشخص');
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($program['start_date']); ?></td>
                                    <td><?php echo esc_html($program['program_duration']); ?> روز</td>
                                    <td>
                                        <?php
                                        switch ($program['status']) {
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
                                                echo esc_html($program['status']);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-program&user_id=' . $program['user_id']); ?>" class="button button-small">مشاهده</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <p>
                        <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-programs'); ?>" class="button">مشاهده همه برنامه‌ها</a>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="nutri-coach-box">
                <h2>اطلاعات سیستم</h2>
                <table class="widefat" cellspacing="0">
                    <tbody>
                        <tr>
                            <td>نسخه وردپرس:</td>
                            <td><?php echo get_bloginfo('version'); ?></td>
                        </tr>
                        <tr>
                            <td>نسخه PHP:</td>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <td>نسخه MySQL:</td>
                            <td><?php echo $wpdb->db_version(); ?></td>
                        </tr>
                        <tr>
                            <td>نسخه افزونه نوتری کوچ:</td>
                            <td><?php echo NUTRI_COACH_PROGRAM_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td>محیط اجرا:</td>
                            <td><?php echo defined('WP_DEBUG') && WP_DEBUG ? 'توسعه' : 'تولید'; ?></td>
                        </tr>
                        <tr>
                            <td>حافظه PHP:</td>
                            <td><?php echo WP_MEMORY_LIMIT; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* استایل داشبورد */
.nutri-coach-dashboard {
    margin-top: 20px;
}

.nutri-coach-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.nutri-coach-stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    padding: 15px;
    border-radius: 3px;
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 200px;
}

.nutri-coach-stat-card .stat-icon {
    font-size: 36px;
    color: #0073aa;
    margin-right: 15px;
}

.nutri-coach-stat-card .stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #555;
}

.nutri-coach-stat-card .stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.nutri-coach-boxes {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.nutri-coach-boxes .nutri-coach-box {
    flex: 1;
    min-width: 45%;
}

.nutri-coach-activity-list {
    margin: 0;
    padding: 0;
    list-style: none;
}

.nutri-coach-activity-list .activity-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: flex-start;
}

.nutri-coach-activity-list .activity-item:last-child {
    border-bottom: none;
}

.nutri-coach-activity-list .activity-icon {
    margin-right: 10px;
    color: #0073aa;
}

.nutri-coach-activity-list .activity-content {
    flex: 1;
}

.nutri-coach-activity-list .activity-description {
    margin-bottom: 5px;
}

.nutri-coach-activity-list .activity-meta {
    color: #777;
    font-size: 12px;
}

.nutri-coach-activity-list .activity-meta span {
    margin-right: 10px;
}

.activity-more {
    margin-top: 15px;
    text-align: center;
}

/* وضعیت‌ها */
.status-active {
    color: #00a32a;
    font-weight: bold;
}

.status-completed {
    color: #3582c4;
    font-weight: bold;
}

.status-paused {
    color: #dba617;
    font-weight: bold;
}

@media screen and (max-width: 782px) {
    .nutri-coach-boxes .nutri-coach-box {
        min-width: 100%;
    }
    
    .nutri-coach-stat-card {
        min-width: 100%;
    }
}
</style>