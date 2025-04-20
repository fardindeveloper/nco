 
<?php
/**
 * قالب فرم قانون تمرینی
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

// بررسی اکشن (افزودن/ویرایش)
$rule_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_edit = $rule_id > 0;

// دریافت اطلاعات قانون در صورت ویرایش
$rule = array(
    'title' => '',
    'gender' => '',
    'age_min' => 0,
    'age_max' => 999,
    'waist_min' => 0,
    'waist_max' => 999,
    'hip_min' => 0,
    'hip_max' => 999,
    'neck_min' => 0,
    'neck_max' => 999,
    'fitness_goal' => '',
    'session_duration_min' => 0,
    'session_duration_max' => 999,
    'intensity' => '',
    'workout_program' => '',
    'diet_program' => '',
    'status' => 'active',
);

if ($is_edit) {
    global $wpdb;
    $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
    
    $db_rule = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $rule_id
        ),
        ARRAY_A
    );
    
    if ($db_rule) {
        $rule = array_merge($rule, $db_rule);
    }
}

// ذخیره فرم
if (isset($_POST['save_rule']) && check_admin_referer('save_rule_nonce')) {
    $rule['title'] = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $rule['gender'] = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
    $rule['age_min'] = isset($_POST['age_min']) ? intval($_POST['age_min']) : 0;
    $rule['age_max'] = isset($_POST['age_max']) ? intval($_POST['age_max']) : 999;
    $rule['waist_min'] = isset($_POST['waist_min']) ? floatval($_POST['waist_min']) : 0;
    $rule['waist_max'] = isset($_POST['waist_max']) ? floatval($_POST['waist_max']) : 999;
    $rule['hip_min'] = isset($_POST['hip_min']) ? floatval($_POST['hip_min']) : 0;
    $rule['hip_max'] = isset($_POST['hip_max']) ? floatval($_POST['hip_max']) : 999;
    $rule['neck_min'] = isset($_POST['neck_min']) ? floatval($_POST['neck_min']) : 0;
    $rule['neck_max'] = isset($_POST['neck_max']) ? floatval($_POST['neck_max']) : 999;
    $rule['fitness_goal'] = isset($_POST['fitness_goal']) ? sanitize_text_field($_POST['fitness_goal']) : '';
    $rule['session_duration_min'] = isset($_POST['session_duration_min']) ? intval($_POST['session_duration_min']) : 0;
    $rule['session_duration_max'] = isset($_POST['session_duration_max']) ? intval($_POST['session_duration_max']) : 999;
    $rule['intensity'] = isset($_POST['intensity']) ? sanitize_text_field($_POST['intensity']) : '';
    $rule['workout_program'] = isset($_POST['workout_program']) ? wp_kses_post($_POST['workout_program']) : '';
    $rule['diet_program'] = isset($_POST['diet_program']) ? wp_kses_post($_POST['diet_program']) : '';
    $rule['status'] = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';
    
    global $wpdb;
    $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
    
    if ($is_edit) {
        // به‌روزرسانی قانون موجود
        $wpdb->update(
            $table_name,
            $rule,
            array('id' => $rule_id),
            array(
                '%s', '%s', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%s',
                '%d', '%d', '%s', '%s', '%s', '%s'
            ),
            array('%d')
        );
        
        echo '<div class="notice notice-success is-dismissible"><p>' . __('قانون با موفقیت به‌روزرسانی شد.', 'nutri-coach-program') . '</p></div>';
    } else {
        // افزودن قانون جدید
        $wpdb->insert(
            $table_name,
            $rule,
            array(
                '%s', '%s', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%s',
                '%d', '%d', '%s', '%s', '%s', '%s'
            )
        );
        
        $rule_id = $wpdb->insert_id;
        $is_edit = true;
        
        echo '<div class="notice notice-success is-dismissible"><p>' . __('قانون با موفقیت افزوده شد.', 'nutri-coach-program') . '</p></div>';
    }
}

// فهرست جنسیت‌ها
$genders = array(
    '' => __('-- فرقی ندارد --', 'nutri-coach-program'),
    'male' => __('مرد', 'nutri-coach-program'),
    'female' => __('زن', 'nutri-coach-program'),
);

// فهرست اهداف ورزشی
$fitness_goals = array(
    '' => __('-- فرقی ندارد --', 'nutri-coach-program'),
    'weight_loss' => __('کاهش وزن', 'nutri-coach-program'),
    'muscle_gain' => __('افزایش عضله', 'nutri-coach-program'),
    'maintenance' => __('حفظ وزن فعلی', 'nutri-coach-program'),
    'overall_health' => __('سلامت عمومی', 'nutri-coach-program'),
);

// فهرست شدت تمرین
$intensities = array(
    '' => __('-- فرقی ندارد --', 'nutri-coach-program'),
    'low' => __('کم', 'nutri-coach-program'),
    'medium' => __('متوسط', 'nutri-coach-program'),
    'high' => __('زیاد', 'nutri-coach-program'),
);

// فهرست وضعیت‌ها
$statuses = array(
    'active' => __('فعال', 'nutri-coach-program'),
    'inactive' => __('غیرفعال', 'nutri-coach-program'),
);

?>

<div class="wrap">
    <h1><?php echo $is_edit ? __('ویرایش قانون تمرینی', 'nutri-coach-program') : __('افزودن قانون تمرینی جدید', 'nutri-coach-program'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('save_rule_nonce'); ?>
        
        <div class="metabox-holder">
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle"><?php echo __('اطلاعات کلی', 'nutri-coach-program'); ?></h2>
                </div>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="title"><?php echo __('عنوان قانون', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="title" id="title" value="<?php echo esc_attr($rule['title']); ?>" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="status"><?php echo __('وضعیت', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <select name="status" id="status">
                                    <?php foreach ($statuses as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($rule['status'], $key); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle"><?php echo __('شرایط تطبیق', 'nutri-coach-program'); ?></h2>
                </div>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="gender"><?php echo __('جنسیت', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <select name="gender" id="gender">
                                    <?php foreach ($genders as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($rule['gender'], $key); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php echo __('سن (حداقل - حداکثر)', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="age_min" value="<?php echo esc_attr($rule['age_min']); ?>" class="small-text" min="0"> - 
                                <input type="number" name="age_max" value="<?php echo esc_attr($rule['age_max']); ?>" class="small-text" min="0">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
<tr>
                            <th scope="row">
                                <label><?php echo __('دور کمر (کمینه - بیشینه)', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="waist_min" value="<?php echo esc_attr($rule['waist_min']); ?>" class="small-text" min="0" step="0.1"> - 
                                <input type="number" name="waist_max" value="<?php echo esc_attr($rule['waist_max']); ?>" class="small-text" min="0" step="0.1">
                                <p class="description"><?php echo __('سانتی‌متر - مقدار 0 به معنی بدون محدودیت است', 'nutri-coach-program'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php echo __('دور باسن (کمینه - بیشینه)', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="hip_min" value="<?php echo esc_attr($rule['hip_min']); ?>" class="small-text" min="0" step="0.1"> - 
                                <input type="number" name="hip_max" value="<?php echo esc_attr($rule['hip_max']); ?>" class="small-text" min="0" step="0.1">
                                <p class="description"><?php echo __('سانتی‌متر - مقدار 0 به معنی بدون محدودیت است', 'nutri-coach-program'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php echo __('دور گردن (کمینه - بیشینه)', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="neck_min" value="<?php echo esc_attr($rule['neck_min']); ?>" class="small-text" min="0" step="0.1"> - 
                                <input type="number" name="neck_max" value="<?php echo esc_attr($rule['neck_max']); ?>" class="small-text" min="0" step="0.1">
                                <p class="description"><?php echo __('سانتی‌متر - مقدار 0 به معنی بدون محدودیت است', 'nutri-coach-program'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="fitness_goal"><?php echo __('هدف ورزشی', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <select name="fitness_goal" id="fitness_goal">
                                    <?php foreach ($fitness_goals as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($rule['fitness_goal'], $key); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php echo __('مدت زمان تمرین هر جلسه (دقیقه)', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="session_duration_min" value="<?php echo esc_attr($rule['session_duration_min']); ?>" class="small-text" min="0"> - 
                                <input type="number" name="session_duration_max" value="<?php echo esc_attr($rule['session_duration_max']); ?>" class="small-text" min="0">
                                <p class="description"><?php echo __('مقدار 0 به معنی بدون محدودیت است', 'nutri-coach-program'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="intensity"><?php echo __('شدت تمرین', 'nutri-coach-program'); ?></label>
                            </th>
                            <td>
                                <select name="intensity" id="intensity">
                                    <?php foreach ($intensities as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($rule['intensity'], $key); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle"><?php echo __('برنامه تمرینی', 'nutri-coach-program'); ?></h2>
                </div>
                <div class="inside">
                    <p><?php echo __('برنامه تمرینی را وارد کنید. می‌توانید از فرمت متنی ساده یا JSON استفاده کنید.', 'nutri-coach-program'); ?></p>
                    <p><?php echo __('نمونه ساختار JSON:', 'nutri-coach-program'); ?></p>
                    <pre>
{
    "days": [
        {
            "day": "شنبه",
            "exercises": [
                {
                    "id": "ex1",
                    "name": "پرس سینه",
                    "sets": 3,
                    "reps": "12-15",
                    "rest": 60
                },
                {
                    "id": "ex2",
                    "name": "جلو بازو با دمبل",
                    "sets": 3,
                    "reps": "10-12",
                    "rest": 45
                }
            ]
        },
        {
            "day": "دوشنبه",
            "exercises": [
                {
                    "id": "ex3",
                    "name": "اسکات",
                    "sets": 4,
                    "reps": "10",
                    "rest": 90
                }
            ]
        }
    ]
}
                    </pre>
                    <textarea name="workout_program" id="workout_program" rows="10" class="large-text code"><?php echo esc_textarea($rule['workout_program']); ?></textarea>
                </div>
            </div>
            
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle"><?php echo __('برنامه غذایی', 'nutri-coach-program'); ?></h2>
                </div>
                <div class="inside">
                    <p><?php echo __('برنامه غذایی را وارد کنید. می‌توانید از فرمت متنی ساده یا JSON استفاده کنید.', 'nutri-coach-program'); ?></p>
                    <p><?php echo __('نمونه ساختار JSON:', 'nutri-coach-program'); ?></p>
                    <pre>
{
    "days": [
        {
            "day": "همه روزه",
            "meals": [
                {
                    "id": "meal1",
                    "type": "breakfast",
                    "name": "صبحانه",
                    "time": "8:00",
                    "foods": [
                        "2 عدد تخم مرغ",
                        "30 گرم پنیر کم چرب",
                        "1 لیوان شیر"
                    ]
                },
                {
                    "id": "meal2",
                    "type": "lunch",
                    "name": "نهار",
                    "time": "13:00",
                    "foods": [
                        "150 گرم مرغ",
                        "1 پیمانه برنج",
                        "سالاد فصل"
                    ]
                }
            ]
        }
    ]
}
                    </pre>
                    <textarea name="diet_program" id="diet_program" rows="10" class="large-text code"><?php echo esc_textarea($rule['diet_program']); ?></textarea>
                </div>
            </div>
            
            <p class="submit">
                <input type="submit" name="save_rule" class="button button-primary" value="<?php echo esc_attr__('ذخیره قانون', 'nutri-coach-program'); ?>" />
                <a href="<?php echo admin_url('admin.php?page=nutri-coach-rules'); ?>" class="button"><?php echo esc_attr__('انصراف', 'nutri-coach-program'); ?></a>
            </p>
        </form>
    </div>
</div>