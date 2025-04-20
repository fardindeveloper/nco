<!-- ادامه فایل templates/admin/anthropometric-form.php -->
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
if (!is_array($anthropometric_data)) {
    $anthropometric_data = array();
}

// اطلاعات کنونی
$weight = isset($_POST['weight']) ? sanitize_text_field($_POST['weight']) : '';
$height = isset($_POST['height']) ? sanitize_text_field($_POST['height']) : '';
$neck = isset($_POST['neck']) ? sanitize_text_field($_POST['neck']) : '';
$chest = isset($_POST['chest']) ? sanitize_text_field($_POST['chest']) : '';
$waist = isset($_POST['waist']) ? sanitize_text_field($_POST['waist']) : '';
$hip = isset($_POST['hip']) ? sanitize_text_field($_POST['hip']) : '';
$arm = isset($_POST['arm']) ? sanitize_text_field($_POST['arm']) : '';
$forearm = isset($_POST['forearm']) ? sanitize_text_field($_POST['forearm']) : '';
$thigh = isset($_POST['thigh']) ? sanitize_text_field($_POST['thigh']) : '';
$calf = isset($_POST['calf']) ? sanitize_text_field($_POST['calf']) : '';
$measurement_date = isset($_POST['measurement_date']) ? sanitize_text_field($_POST['measurement_date']) : date('Y-m-d');

// پردازش فرم ارسالی
if (isset($_POST['submit_anthropometric'])) {
    // بررسی نانس امنیتی
    check_admin_referer('nutri_coach_anthropometric_form', 'nutri_coach_anthropometric_nonce');
    
    // اضافه کردن داده جدید به آرایه اندازه‌گیری‌ها
    $new_measurement = array(
        'weight' => $weight,
        'height' => $height,
        'neck' => $neck,
        'chest' => $chest,
        'waist' => $waist,
        'hip' => $hip,
        'arm' => $arm,
        'forearm' => $forearm,
        'thigh' => $thigh,
        'calf' => $calf,
        'date' => $measurement_date,
        'bmi' => nutri_coach_calculate_bmi($weight, $height),
        'body_fat' => nutri_coach_calculate_body_fat($weight, $height, $neck, $waist, $hip, get_user_meta($user_id, 'gender', true))
    );
    
    $anthropometric_data[] = $new_measurement;
    
    // مرتب‌سازی بر اساس تاریخ (جدیدترین اول)
    usort($anthropometric_data, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // ذخیره در دیتابیس
    update_user_meta($user_id, 'nutri_coach_anthropometric', $anthropometric_data);
    
    echo '<div class="notice notice-success is-dismissible"><p>اطلاعات آنتروپومتریک با موفقیت ذخیره شد.</p></div>';
}
?>

<div class="wrap">
    <h1>اندازه‌گیری‌های آنتروپومتریک</h1>
    <h2>کاربر: <?php echo esc_html($user_info->display_name); ?> (<?php echo esc_html($user_info->user_email); ?>)</h2>
    
    <div class="nutri-coach-tabs">
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-program&user_id=' . $user_id); ?>" class="tab">برنامه تمرینی و غذایی</a>
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-progress-report&user_id=' . $user_id); ?>" class="tab">گزارش پیشرفت</a>
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-anthropometric&user_id=' . $user_id); ?>" class="tab active">اندازه‌گیری‌های بدن</a>
        <a href="<?php echo admin_url('admin.php?page=nutri-coach-anthropometric-history&user_id=' . $user_id); ?>" class="tab">تاریخچه اندازه‌گیری‌ها</a>
    </div>
    
    <div class="nutri-coach-box">
        <form method="post" action="">
            <?php wp_nonce_field('nutri_coach_anthropometric_form', 'nutri_coach_anthropometric_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="measurement_date">تاریخ اندازه‌گیری</label></th>
                    <td>
                        <input type="date" id="measurement_date" name="measurement_date" value="<?php echo esc_attr($measurement_date); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="weight">وزن (کیلوگرم)</label></th>
                    <td>
                        <input type="number" id="weight" name="weight" value="<?php echo esc_attr($weight); ?>" class="regular-text" step="0.1" min="30" max="250" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="height">قد (سانتی‌متر)</label></th>
                    <td>
                        <input type="number" id="height" name="height" value="<?php echo esc_attr($height); ?>" class="regular-text" step="0.1" min="100" max="220" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="neck">دور گردن (سانتی‌متر)</label></th>
                    <td>
                        <input type="number" id="neck" name="neck" value="<?php echo esc_attr($neck); ?>" class="regular-text" step="0.1" min="20" max="80">
                    </td>
                </tr>
                <tr>
                    <th><label for="chest">دور سینه (سانتی‌متر)</label></th>
                    <td>
                        <input type="number" id="chest" name="chest" value="<?php echo esc_attr($chest); ?>" class="regular-text" step="0.1" min="50" max="180">
                    </td>
                </tr>
                <tr>
                    <th><label for="waist">دور کمر (سانتی‌متر)</label></th>
                    <td>
                        <input type="number" id="waist" name="waist" value="<?php echo esc_attr($waist); ?>" class="regular-text" step="0.1" min="40" max="200">
                    </td>
                </tr>
                <tr>
                    <th><label for="hip">دور باسن (سانتی‌متر)</label></th>
                    <td>
                        <input type="number" id="hip" name="hip" value="<?php echo esc_attr($hip); ?>" class="regular-text" step="0.1" min="50" max="200">
                    </td>
                </tr>
                <tr>
                    <th><label for="arm">دور بازو (سانتی‌متر)</label></th>
                    <td>
                        <input type="number" id="arm" name="arm" value="<?php echo esc_attr($arm); ?>" class="regular-text" step="0.1" min="15" max="60">
                    </td>
                </tr>
                <tr>
                    <th><label for="forearm">دور ساعد (سانتی‌متر)</label></th>
                    <td>
                        <input type="number" id="forearm" name="forearm" value="<?php echo esc_attr($forearm); ?>" class="regular-text" step="0.1" min="15" max="50">
                    </td>
                </tr>
                <tr>
                    <th><label for="thigh">دور ران (سانتی‌متر)</label></th>
                    <td>
                        <input type="number" id="thigh" name="thigh" value="<?php echo esc_attr($thigh); ?>" class="regular-text" step="0.1" min="30" max="100">
                    </td>
                </tr>
                <tr>
                    <th><label for="calf">دور ساق پا (سانتی‌متر)</label></th>
                    <td>
                        <input type="number" id="calf" name="calf" value="<?php echo esc_attr($calf); ?>" class="regular-text" step="0.1" min="20" max="70">
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_anthropometric" class="button button-primary" value="ذخیره اندازه‌گیری‌ها">
            </p>
        </form>
    </div>
    
    <?php if (!empty($anthropometric_data)): ?>
    <div class="nutri-coach-box">
        <h3>آخرین اندازه‌گیری</h3>
        <table class="wp-list-table widefat fixed striped">
            <tr>
                <th>تاریخ</th>
                <td><?php echo esc_html($anthropometric_data[0]['date']); ?></td>
            </tr>
            <tr>
                <th>وزن</th>
                <td><?php echo esc_html($anthropometric_data[0]['weight']); ?> کیلوگرم</td>
            </tr>
            <tr>
                <th>قد</th>
                <td><?php echo esc_html($anthropometric_data[0]['height']); ?> سانتی‌متر</td>
            </tr>
            <tr>
                <th>BMI</th>
                <td><?php echo esc_html($anthropometric_data[0]['bmi']); ?> (<?php echo nutri_coach_bmi_category($anthropometric_data[0]['bmi']); ?>)</td>
            </tr>
            <tr>
                <th>درصد چربی بدن</th>
                <td><?php echo esc_html($anthropometric_data[0]['body_fat']); ?>% (<?php echo nutri_coach_body_fat_category($anthropometric_data[0]['body_fat'], get_user_meta($user_id, 'gender', true)); ?>)</td>
            </tr>
        </table>
    </div>
    <?php endif; ?>
</div> 
