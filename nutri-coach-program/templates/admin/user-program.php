<?php
/**
 * قالب جزئیات برنامه کاربر
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

// بررسی پارامترها
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'view';

// بررسی کاربر
$user = get_userdata($user_id);
if (!$user) {
    wp_die(__('کاربر یافت نشد.', 'nutri-coach-program'));
}

// دریافت اطلاعات برنامه
global $wpdb;
$table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'user_programs';

$program = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d AND user_id = %d",
        $program_id,
        $user_id
    ),
    ARRAY_A
);

if (!$program) {
    wp_die(__('برنامه یافت نشد.', 'nutri-coach-program'));
}

// ذخیره تغییرات
if (isset($_POST['save_program']) && check_admin_referer('save_user_program')) {
    $workout_program = isset($_POST['workout_program']) ? wp_kses_post($_POST['workout_program']) : '';
    $diet_program = isset($_POST['diet_program']) ? wp_kses_post($_POST['diet_program']) : '';
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';
    
    $wpdb->update(
        $table_name,
        array(
            'workout_program' => $workout_program,
            'diet_program' => $diet_program,
            'status' => $status,
        ),
        array('id' => $program_id),
        array('%s', '%s', '%s'),
        array('%d')
    );
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('برنامه کاربر با موفقیت به‌روزرسانی شد.', 'nutri-coach-program') . '</p></div>';
    
    // بارگذاری مجدد برنامه
    $program = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d AND user_id = %d",
            $program_id,
            $user_id
        ),
        ARRAY_A
    );
}

// دریافت اطلاعات قانون
$rule_id = $program['rule_id'];
$rule_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
$rule = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $rule_table WHERE id = %d",
        $rule_id
    ),
    ARRAY_A
);

// فرمت برنامه‌ها
$workout_program = json_decode($program['workout_program'], true);
$diet_program = json_decode($program['diet_program'], true);

// بررسی آیا برنامه‌ها JSON معتبر هستند
if (json_last_error() !== JSON_ERROR_NONE) {
    $workout_program = $program['workout_program'];
}

if (json_last_error() !== JSON_ERROR_NONE) {
    $diet_program = $program['diet_program'];
}

// دریافت اطلاعات کاربر از افزونه پروفایل
$user_data = apply_filters('nutri_coach_get_user_data', array(), $user_id);

?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php 
        if ($action === 'edit') {
            printf(__('ویرایش برنامه کاربر: %s', 'nutri-coach-program'), $user->display_name); 
        } else {
            printf(__('برنامه کاربر: %s', 'nutri-coach-program'), $user->display_name); 
        }
        ?>
    </h1>
    
    <hr class="wp-header-end">
    
    <div class="metabox-holder">
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle"><?php echo __('اطلاعات کاربر', 'nutri-coach-program'); ?></h2>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php echo __('نام کاربری:', 'nutri-coach-program'); ?></th>
                        <td><?php echo esc_html($user->user_login); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('ایمیل:', 'nutri-coach-program'); ?></th>
                        <td><?php echo esc_html($user->user_email); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('نام نمایشی:', 'nutri-coach-program'); ?></th>
                        <td><?php echo esc_html($user->display_name); ?></td>
                    </tr>
                    
                    <?php if (!empty($user_data)) : ?>
                        <tr>
                            <th><?php echo __('سن:', 'nutri-coach-program'); ?></th>
                            <td><?php echo isset($user_data['age']) ? esc_html($user_data['age']) : __('ثبت نشده', 'nutri-coach-program'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo __('جنسیت:', 'nutri-coach-program'); ?></th>
                            <td>
                                <?php 
                                if (isset($user_data['gender'])) {
                                    if ($user_data['gender'] === 'male') {
                                        echo __('مرد', 'nutri-coach-program');
                                    } elseif ($user_data['gender'] === 'female') {
                                        echo __('زن', 'nutri-coach-program');
                                    } else {
                                        echo esc_html($user_data['gender']);
                                    }
                                } else {
                                    echo __('ثبت نشده', 'nutri-coach-program');
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo __('قد:', 'nutri-coach-program'); ?></th>
                            <td><?php echo isset($user_data['height']) ? esc_html($user_data['height']) . ' ' . __('سانتی‌متر', 'nutri-coach-program') : __('ثبت نشده', 'nutri-coach-program'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo __('وزن:', 'nutri-coach-program'); ?></th>
                            <td><?php echo isset($user_data['weight']) ? esc_html($user_data['weight']) . ' ' . __('کیلوگرم', 'nutri-coach-program') : __('ثبت نشده', 'nutri-coach-program'); ?></td>
                        </tr>
                        
                        <?php if (isset($user_data['bmi'])) : ?>
                            <tr>
                                <th><?php echo __('BMI:', 'nutri-coach-program'); ?></th>
                                <td>
                                    <?php echo esc_html($user_data['bmi']); ?>
                                    (<?php echo isset($user_data['bmi_status']) ? esc_html($user_data['bmi_status']) : ''; ?>)
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($user_data['neck_circumference'])) : ?>
                            <tr>
                                <th><?php echo __('دور گردن:', 'nutri-coach-program'); ?></th>
                                <td><?php echo esc_html($user_data['neck_circumference']) . ' ' . __('سانتی‌متر', 'nutri-coach-program'); ?></td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($user_data['waist_circumference'])) : ?>
                            <tr>
                                <th><?php echo __('دور کمر:', 'nutri-coach-program'); ?></th>
                                <td><?php echo esc_html($user_data['waist_circumference']) . ' ' . __('سانتی‌متر', 'nutri-coach-program'); ?></td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($user_data['hip_circumference'])) : ?>
                            <tr>
                                <th><?php echo __('دور باسن:', 'nutri-coach-program'); ?></th>
                                <td><?php echo esc_html($user_data['hip_circumference']) . ' ' . __('سانتی‌متر', 'nutri-coach-program'); ?></td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($user_data['fitness_goal'])) : ?>
                            <tr>
                                <th><?php echo __('هدف ورزشی:', 'nutri-coach-program'); ?></th>
                                <td>
                                    <?php 
                                    if ($user_data['fitness_goal'] === 'weight_loss') {
                                        echo __('کاهش وزن', 'nutri-coach-program');
                                    } elseif ($user_data['fitness_goal'] === 'muscle_gain') {
                                        echo __('افزایش عضله', 'nutri-coach-program');
                                    } elseif ($user_data['fitness_goal'] === 'maintenance') {
                                        echo __('حفظ وزن فعلی', 'nutri-coach-program');
                                    } elseif ($user_data['fitness_goal'] === 'overall_health') {
                                        echo __('سلامت عمومی', 'nutri-coach-program');
                                    } else {
                                        echo esc_html($user_data['fitness_goal']);
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle"><?php echo __('اطلاعات برنامه', 'nutri-coach-program'); ?></h2>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php echo __('شناسه برنامه:', 'nutri-coach-program'); ?></th>
                        <td><?php echo esc_html($program['id']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('قانون استفاده شده:', 'nutri-coach-program'); ?></th>
                        <td>
                            <?php 
                            if ($rule) {
                                echo '<a href="' . admin_url('admin.php?page=nutri-coach-rules&action=edit&id=' . $rule['id']) . '">' . esc_html($rule['title']) . '</a>';
                            } else {
                                echo __('قانون یافت نشد', 'nutri-coach-program');
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo __('تاریخ شروع:', 'nutri-coach-program'); ?></th>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($program['start_date'])); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('تاریخ پایان:', 'nutri-coach-program'); ?></th>
                        <td>
                            <?php 
                            if (!empty($program['end_date']) && $program['end_date'] !== '0000-00-00 00:00:00') {
                                echo date_i18n(get_option('date_format'), strtotime($program['end_date']));
                            } else {
                                echo __('نامشخص', 'nutri-coach-program');
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo __('وضعیت:', 'nutri-coach-program'); ?></th>
                        <td>
                            <?php 
                            if ($program['status'] === 'active') {
                                echo '<span class="status-active">' . __('فعال', 'nutri-coach-program') . '</span>';
                            } else {
                                echo '<span class="status-inactive">' . __('غیرفعال', 'nutri-coach-program') . '</span>';
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($action === 'edit') : ?>
            <form method="post" action="">
                <?php wp_nonce_field('save_user_program'); ?>
                
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php echo __('برنامه تمرینی', 'nutri-coach-program'); ?></h2>
                    </div>
                    <div class="inside">
                        <p><?php echo __('برنامه تمرینی کاربر را ویرایش کنید. می‌توانید از فرمت متنی ساده یا JSON استفاده کنید.', 'nutri-coach-program'); ?></p>
                        <textarea name="workout_program" id="workout_program" rows="15" class="large-text code"><?php echo esc_textarea($program['workout_program']); ?></textarea>
                    </div>
                </div>
                
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php echo __('برنامه غذایی', 'nutri-coach-program'); ?></h2>
                    </div>
                    <div class="inside">
                        <p><?php echo __('برنامه غذایی کاربر را ویرایش کنید. می‌توانید از فرمت متنی ساده یا JSON استفاده کنید.', 'nutri-coach-program'); ?></p>
                        <textarea name="diet_program" id="diet_program" rows="15" class="large-text code"><?php echo esc_textarea($program['diet_program']); ?></textarea>
                    </div>
                </div>
                
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php echo __('تنظیمات', 'nutri-coach-program'); ?></h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="status"><?php echo __('وضعیت', 'nutri-coach-program'); ?></label>
                                </th>
                                <td>
                                    <select name="status" id="status">
                                        <option value="active" <?php selected($program['status'], 'active'); ?>><?php echo __('فعال', 'nutri-coach-program'); ?></option>
                                        <option value="inactive" <?php selected($program['status'], 'inactive'); ?>><?php echo __('غیرفعال', 'nutri-coach-program'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="save_program" class="button button-primary" value="<?php echo esc_attr__('ذخیره برنامه', 'nutri-coach-program'); ?>" />
                    <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-programs'); ?>" class="button"><?php echo esc_attr__('انصراف', 'nutri-coach-program'); ?></a>
                </p>
            </form>
        <?php else : ?>
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle"><?php echo __('برنامه تمرینی', 'nutri-coach-program'); ?></h2>
                </div>
                <div class="inside">
                    <?php if (is_array($workout_program)) : ?>
                        <?php if (isset($workout_program['days'])) : ?>
                            <div class="workout-program-container">
                                <?php foreach ($workout_program['days'] as $day) : ?>
                                    <div class="workout-day">
                                        <h3><?php echo esc_html($day['day']); ?></h3>
                                        
                                        <?php if (isset($day['exercises']) && is_array($day['exercises'])) : ?>
                                            <table class="wp-list-table widefat fixed striped">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('تمرین', 'nutri-coach-program'); ?></th>
                                                        <th><?php echo __('ست', 'nutri-coach-program'); ?></th>
                                                        <th><?php echo __('تکرار', 'nutri-coach-program'); ?></th>
                                                        <th><?php echo __('استراحت', 'nutri-coach-program'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($day['exercises'] as $exercise) : ?>
                                                        <tr>
                                                            <td><?php echo esc_html($exercise['name']); ?></td>
                                                            <td><?php echo isset($exercise['sets']) ? esc_html($exercise['sets']) : '-'; ?></td>
                                                            <td><?php echo isset($exercise['reps']) ? esc_html($exercise['reps']) : '-'; ?></td>
                                                            <td><?php echo isset($exercise['rest']) ? esc_html($exercise['rest']) . ' ' . __('ثانیه', 'nutri-coach-program') : '-'; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else : ?>
                                            <p><?php echo __('تمرینی برای این روز تعریف نشده است.', 'nutri-coach-program'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="workout-program-raw">
                                <pre><?php echo esc_html(json_encode($workout_program, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="workout-program-text">
                            <?php echo nl2br(esc_html($workout_program)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle"><?php echo __('برنامه غذایی', 'nutri-coach-program'); ?></h2>
                </div>
                <div class="inside">
                    <?php if (is_array($diet_program)) : ?>
                        <?php if (isset($diet_program['days'])) : ?>
                            <div class="diet-program-container">
                                <?php foreach ($diet_program['days'] as $day) : ?>
                                    <div class="diet-day">
                                        <h3><?php echo esc_html($day['day']); ?></h3>
                                        
                                        <?php if (isset($day['meals']) && is_array($day['meals'])) : ?>
                                            <table class="wp-list-table widefat fixed striped">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('وعده', 'nutri-coach-program'); ?></th>
                                                        <th><?php echo __('زمان', 'nutri-coach-program'); ?></th>
                                                        <th><?php echo __('غذاها', 'nutri-coach-program'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($day['meals'] as $meal) : ?>
                                                        <tr>
                                                            <td>
                                                                <?php echo esc_html($meal['name']); ?>
                                                                <?php if (isset($meal['type'])) : ?>
                                                                    <div class="meal-type">
                                                                        <?php 
                                                                        if ($meal['type'] === 'breakfast') {
                                                                            echo __('صبحانه', 'nutri-coach-program');
                                                                        } elseif ($meal['type'] === 'lunch') {
                                                                            echo __('نهار', 'nutri-coach-program');
                                                                        } elseif ($meal['type'] === 'dinner') {
                                                                            echo __('شام', 'nutri-coach-program');
                                                                        } elseif ($meal['type'] === 'snack') {
                                                                            echo __('میان وعده', 'nutri-coach-program');
                                                                        } else {
                                                                            echo esc_html($meal['type']);
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo isset($meal['time']) ? esc_html($meal['time']) : '-'; ?></td>
                                                            <td>
                                                                <?php if (isset($meal['foods']) && is_array($meal['foods'])) : ?>
                                                                    <ul class="meal-foods">
                                                                        <?php foreach ($meal['foods'] as $food) : ?>
                                                                            <li><?php echo esc_html($food); ?></li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                <?php else : ?>
                                                                    <?php echo isset($meal['foods']) ? esc_html($meal['foods']) : '-'; ?>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else : ?>
                                            <p><?php echo __('وعده‌ای برای این روز تعریف نشده است.', 'nutri-coach-program'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="diet-program-raw">
                                <pre><?php echo esc_html(json_encode($diet_program, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="diet-program-text">
                            <?php echo nl2br(esc_html($diet_program)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <p>
                <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-programs&action=edit&user_id=' . $user_id . '&program_id=' . $program_id); ?>" class="button button-primary"><?php echo esc_attr__('ویرایش برنامه', 'nutri-coach-program'); ?></a>
                <a href="<?php echo admin_url('admin.php?page=nutri-coach-user-programs'); ?>" class="button"><?php echo esc_attr__('بازگشت به لیست', 'nutri-coach-program'); ?></a>
                <a href="<?php echo admin_url('admin.php?page=nutri-coach-progress-reports&user_id=' . $user_id); ?>" class="button"><?php echo esc_attr__('گزارش پیشرفت', 'nutri-coach-program'); ?></a>
            </p>
        <?php endif; ?>
    </div>
</div> 
