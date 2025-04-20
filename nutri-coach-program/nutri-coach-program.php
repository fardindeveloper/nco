<?php
/**
 * Plugin Name: Nutri Coach - برنامه ریزی و پیگیری
 * Plugin URI: https://yourwebsite.com/nutri-coach
 * Description: افزونه برنامه ریزی تمرینی و غذایی و پیگیری پیشرفت برای Nutri Coach
 * Version: 1.0.0
 * Author: نام شما
 * Author URI: https://yourwebsite.com
 * Text Domain: nutri-coach-program
 * Domain Path: /languages
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

/**
 * تعریف ثابت‌های افزونه
 */
define('NUTRI_COACH_PROGRAM_VERSION', '1.0.0');
define('NUTRI_COACH_PROGRAM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NUTRI_COACH_PROGRAM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NUTRI_COACH_PROGRAM_PREFIX', 'ncp_program_');

/**
 * کلاس اصلی افزونه
 */
final class Nutri_Coach_Program {
    // نمونه منحصر به فرد
    private static $instance = null;
    
    /**
     * گرفتن نمونه منحصر به فرد (Singleton Pattern)
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * سازنده کلاس
     */
    private function __construct() {
        // بارگذاری افزونه
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
        
        // بررسی وابستگی‌ها
        add_action('admin_init', array($this, 'check_dependencies'));
        
        // فعال‌سازی و غیرفعال‌سازی افزونه
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // بارگذاری فایل‌های ضروری
        $this->load_dependencies();
        
        // منوی پیشخوان وردپرس
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // ثبت پست تایپ‌های سفارشی
        add_action('init', array($this, 'register_post_types'));
        
        // ثبت متاباکس‌ها
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        
        // ذخیره متاباکس‌ها
        add_action('save_post', array($this, 'save_meta_boxes'));
        
        // ثبت شورت‌کدها
        add_shortcode('nutri_coach_program', array($this, 'program_shortcode'));
        add_shortcode('nutri_coach_progress', array($this, 'progress_shortcode'));
        
        // اجکس برای تیک زدن تمرین‌ها
        add_action('wp_ajax_ncp_mark_exercise', array($this, 'ajax_mark_exercise'));
        add_action('wp_ajax_ncp_mark_meal', array($this, 'ajax_mark_meal'));
        
        // اضافه کردن اسکریپت‌ها و استایل‌ها
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // اتصال به پروفایل کاربر
        add_filter('nutri_coach_user_data', array($this, 'add_program_data_to_profile'), 10, 2);
        
        // هوک‌های دیگر
        add_action('nutri_coach_profile_updated', array($this, 'profile_updated'), 10, 2);
        add_action('nutri_coach_anthropometric_added', array($this, 'anthropometric_added'), 10, 2);
    }
    
    /**
     * بارگذاری ترجمه‌ها
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain('nutri-coach-program', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    /**
     * بررسی وابستگی‌ها
     */
    public function check_dependencies() {
        // بررسی وجود افزونه پروفایل
        if (!function_exists('nutri_coach_profile')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php _e('افزونه "Nutri Coach - برنامه ریزی و پیگیری" نیاز به افزونه "Nutri Coach - پروفایل کاربر" دارد. لطفاً آن را نصب و فعال کنید.', 'nutri-coach-program'); ?></p>
                </div>
                <?php
            });
        }
    }
    
    /**
     * عملیات فعال‌سازی افزونه
     */
    public function activate() {
        // ایجاد جداول مورد نیاز
        $this->create_tables();
        
        // تنظیم فلش‌های وردپرس برای ریدایرکت
        flush_rewrite_rules();
    }
    
    /**
     * عملیات غیرفعال‌سازی افزونه
     */
    public function deactivate() {
        // تنظیم فلش‌های وردپرس برای ریدایرکت
        flush_rewrite_rules();
    }
    
    /**
     * ایجاد جداول مورد نیاز
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // جدول برای قوانین تمرینی
        $table_rules = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        $sql_rules = "CREATE TABLE $table_rules (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            gender varchar(20) DEFAULT NULL,
            age_min int DEFAULT 0,
            age_max int DEFAULT 999,
            waist_min float DEFAULT 0,
            waist_max float DEFAULT 999,
            hip_min float DEFAULT 0,
            hip_max float DEFAULT 999,
            neck_min float DEFAULT 0,
            neck_max float DEFAULT 999,
            fitness_goal varchar(50) DEFAULT NULL,
            session_duration_min int DEFAULT 0,
            session_duration_max int DEFAULT 999,
            intensity varchar(50) DEFAULT NULL,
            workout_program longtext DEFAULT NULL,
            diet_program longtext DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            date_created datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // جدول برای پیگیری پیشرفت تمرین‌ها
        $table_exercise_progress = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'exercise_progress';
        
        $sql_exercise_progress = "CREATE TABLE $table_exercise_progress (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            program_id bigint(20) NOT NULL,
            exercise_id varchar(50) NOT NULL,
            date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            completed tinyint(1) DEFAULT 0,
            sets int DEFAULT 0,
            reps int DEFAULT 0,
            weight float DEFAULT 0,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY program_id (program_id),
            KEY date (date)
        ) $charset_collate;";
        
        // جدول برای پیگیری پیشرفت برنامه غذایی
        $table_meal_progress = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'meal_progress';
        
        $sql_meal_progress = "CREATE TABLE $table_meal_progress (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            program_id bigint(20) NOT NULL,
            meal_id varchar(50) NOT NULL,
            date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            completed tinyint(1) DEFAULT 0,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY program_id (program_id),
            KEY date (date)
        ) $charset_collate;";
        
        // جدول برای برنامه‌های اختصاص‌یافته به کاربران
        $table_user_programs = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'user_programs';
        
        $sql_user_programs = "CREATE TABLE $table_user_programs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            rule_id bigint(20) NOT NULL,
            workout_program longtext DEFAULT NULL,
            diet_program longtext DEFAULT NULL,
            start_date datetime DEFAULT CURRENT_TIMESTAMP,
            end_date datetime DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY rule_id (rule_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_rules);
        dbDelta($sql_exercise_progress);
        dbDelta($sql_meal_progress);
        dbDelta($sql_user_programs);
    }
    
    /**
     * بارگذاری وابستگی‌ها
     */
    private function load_dependencies() {
        // بارگذاری کلاس برنامه تمرینی
        require_once NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'includes/class-workout-program.php';
        
        // بارگذاری کلاس برنامه غذایی
        require_once NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'includes/class-diet-program.php';
        
        // بارگذاری کلاس پیگیری پیشرفت
        require_once NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'includes/class-progress-tracker.php';
        
        // بارگذاری توابع عمومی
        require_once NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'includes/functions.php';
    }
    
    /**
     * ثبت پست تایپ‌های سفارشی
     */
    public function register_post_types() {
        // ثبت پست تایپ تمرینات
        register_post_type('ncp_exercise', array(
            'labels' => array(
                'name' => __('تمرینات', 'nutri-coach-program'),
                'singular_name' => __('تمرین', 'nutri-coach-program'),
                'add_new' => __('افزودن تمرین جدید', 'nutri-coach-program'),
                'add_new_item' => __('افزودن تمرین جدید', 'nutri-coach-program'),
                'edit_item' => __('ویرایش تمرین', 'nutri-coach-program'),
                'new_item' => __('تمرین جدید', 'nutri-coach-program'),
                'view_item' => __('مشاهده تمرین', 'nutri-coach-program'),
                'search_items' => __('جستجوی تمرینات', 'nutri-coach-program'),
                'not_found' => __('تمرینی یافت نشد', 'nutri-coach-program'),
                'not_found_in_trash' => __('تمرینی در سطل زباله یافت نشد', 'nutri-coach-program'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-universal-access',
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_menu' => 'nutri-coach',
        ));
        
        // ثبت پست تایپ غذاها
        register_post_type('ncp_meal', array(
            'labels' => array(
                'name' => __('غذاها', 'nutri-coach-program'),
                'singular_name' => __('غذا', 'nutri-coach-program'),
                'add_new' => __('افزودن غذای جدید', 'nutri-coach-program'),
                'add_new_item' => __('افزودن غذای جدید', 'nutri-coach-program'),
                'edit_item' => __('ویرایش غذا', 'nutri-coach-program'),
                'new_item' => __('غذای جدید', 'nutri-coach-program'),
                'view_item' => __('مشاهده غذا', 'nutri-coach-program'),
                'search_items' => __('جستجوی غذاها', 'nutri-coach-program'),
                'not_found' => __('غذایی یافت نشد', 'nutri-coach-program'),
                'not_found_in_trash' => __('غذایی در سطل زباله یافت نشد', 'nutri-coach-program'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-food',
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_menu' => 'nutri-coach',
        ));
    }
    
    /**
     * ثبت متاباکس‌ها
     */
    public function register_meta_boxes() {
        // متاباکس برای تمرینات
        add_meta_box(
            'ncp_exercise_details',
            __('جزئیات تمرین', 'nutri-coach-program'),
            array($this, 'exercise_meta_box_callback'),
            'ncp_exercise',
            'normal',
            'high'
        );
        
        // متاباکس برای غذاها
        add_meta_box(
            'ncp_meal_details',
            __('جزئیات غذا', 'nutri-coach-program'),
            array($this, 'meal_meta_box_callback'),
            'ncp_meal',
            'normal',
            'high'
        );
    }
    
    /**
     * نمایش متاباکس تمرینات
     */
    public function exercise_meta_box_callback($post) {
        // ایجاد یک nonce برای اعتبارسنجی
        wp_nonce_field('ncp_save_exercise_meta', 'ncp_exercise_meta_nonce');
        
        // دریافت مقادیر ذخیره شده
        $exercise_type = get_post_meta($post->ID, '_ncp_exercise_type', true);
        $muscle_group = get_post_meta($post->ID, '_ncp_muscle_group', true);
        $difficulty = get_post_meta($post->ID, '_ncp_difficulty', true);
        $equipment = get_post_meta($post->ID, '_ncp_equipment', true);
        $video_url = get_post_meta($post->ID, '_ncp_video_url', true);
        
        // انواع تمرین
        $exercise_types = array(
            'strength' => __('قدرتی', 'nutri-coach-program'),
            'cardio' => __('هوازی', 'nutri-coach-program'),
            'flexibility' => __('انعطاف‌پذیری', 'nutri-coach-program'),
            'balance' => __('تعادلی', 'nutri-coach-program'),
        );
        
        // گروه‌های عضلانی
        $muscle_groups = array(
            'chest' => __('سینه', 'nutri-coach-program'),
            'back' => __('پشت', 'nutri-coach-program'),
            'shoulders' => __('شانه‌ها', 'nutri-coach-program'),
            'biceps' => __('جلو بازو', 'nutri-coach-program'),
            'triceps' => __('پشت بازو', 'nutri-coach-program'),
            'legs' => __('پاها', 'nutri-coach-program'),
            'abs' => __('شکم', 'nutri-coach-program'),
            'full_body' => __('کل بدن', 'nutri-coach-program'),
        );
        
        // سطح دشواری
        $difficulty_levels = array(
            'beginner' => __('مبتدی', 'nutri-coach-program'),
            'intermediate' => __('متوسط', 'nutri-coach-program'),
            'advanced' => __('پیشرفته', 'nutri-coach-program'),
        );
        
        // تجهیزات مورد نیاز
        $equipment_types = array(
            'none' => __('بدون تجهیزات', 'nutri-coach-program'),
            'dumbbell' => __('دمبل', 'nutri-coach-program'),
            'barbell' => __('هالتر', 'nutri-coach-program'),
            'kettlebell' => __('کتل‌بل', 'nutri-coach-program'),
            'resistance_band' => __('کش', 'nutri-coach-program'),
            'machine' => __('دستگاه', 'nutri-coach-program'),
            'other' => __('سایر', 'nutri-coach-program'),
        );
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="ncp_exercise_type"><?php echo __('نوع تمرین', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <select name="ncp_exercise_type" id="ncp_exercise_type">
                        <option value=""><?php echo __('-- انتخاب کنید --', 'nutri-coach-program'); ?></option>
                        <?php foreach ($exercise_types as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($exercise_type, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_muscle_group"><?php echo __('گروه عضلانی', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <select name="ncp_muscle_group" id="ncp_muscle_group">
                        <option value=""><?php echo __('-- انتخاب کنید --', 'nutri-coach-program'); ?></option>
                        <?php foreach ($muscle_groups as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($muscle_group, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_difficulty"><?php echo __('سطح دشواری', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <select name="ncp_difficulty" id="ncp_difficulty">
                        <option value=""><?php echo __('-- انتخاب کنید --', 'nutri-coach-program'); ?></option>
                        <?php foreach ($difficulty_levels as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($difficulty, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_equipment"><?php echo __('تجهیزات مورد نیاز', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <select name="ncp_equipment" id="ncp_equipment">
                        <option value=""><?php echo __('-- انتخاب کنید --', 'nutri-coach-program'); ?></option>
                        <?php foreach ($equipment_types as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($equipment, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_video_url"><?php echo __('آدرس ویدیو', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <input type="url" name="ncp_video_url" id="ncp_video_url" value="<?php echo esc_attr($video_url); ?>" class="regular-text">
                    <p class="description"><?php echo __('آدرس ویدیوی آموزشی این تمرین (یوتیوب یا آپارات)', 'nutri-coach-program'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * نمایش متاباکس غذاها
     */
    public function meal_meta_box_callback($post) {
        // ایجاد یک nonce برای اعتبارسنجی
        wp_nonce_field('ncp_save_meal_meta', 'ncp_meal_meta_nonce');
        
        // دریافت مقادیر ذخیره شده
        $meal_type = get_post_meta($post->ID, '_ncp_meal_type', true);
        $calories = get_post_meta($post->ID, '_ncp_calories', true);
        $protein = get_post_meta($post->ID, '_ncp_protein', true);
        $carbs = get_post_meta($post->ID, '_ncp_carbs', true);
        $fat = get_post_meta($post->ID, '_ncp_fat', true);
        $diet_type = get_post_meta($post->ID, '_ncp_diet_type', true);
        $ingredients = get_post_meta($post->ID, '_ncp_ingredients', true);
        
        // انواع وعده غذایی
        $meal_types = array(
            'breakfast' => __('صبحانه', 'nutri-coach-program'),
            'lunch' => __('نهار', 'nutri-coach-program'),
            'dinner' => __('شام', 'nutri-coach-program'),
            'snack' => __('میان وعده', 'nutri-coach-program'),
        );
        
        // انواع رژیم غذایی
        $diet_types = array(
            'normal' => __('معمولی', 'nutri-coach-program'),
            'vegetarian' => __('گیاهخواری', 'nutri-coach-program'),
            'vegan' => __('وگان', 'nutri-coach-program'),
            'keto' => __('کتوژنیک', 'nutri-coach-program'),
            'paleo' => __('پالئو', 'nutri-coach-program'),
            'low_carb' => __('کم کربوهیدرات', 'nutri-coach-program'),
            'low_fat' => __('کم چربی', 'nutri-coach-program'),
            'high_protein' => __('پرپروتئین', 'nutri-coach-program'),
        );
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="ncp_meal_type"><?php echo __('نوع وعده', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <select name="ncp_meal_type" id="ncp_meal_type">
                        <option value=""><?php echo __('-- انتخاب کنید --', 'nutri-coach-program'); ?></option>
                        <?php foreach ($meal_types as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($meal_type, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_calories"><?php echo __('کالری (kcal)', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <input type="number" name="ncp_calories" id="ncp_calories" value="<?php echo esc_attr($calories); ?>" class="small-text" min="0" step="1">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_protein"><?php echo __('پروتئین (گرم)', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <input type="number" name="ncp_protein" id="ncp_protein" value="<?php echo esc_attr($protein); ?>" class="small-text" min="0" step="0.1">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_carbs"><?php echo __('کربوهیدرات (گرم)', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <input type="number" name="ncp_carbs" id="ncp_carbs" value="<?php echo esc_attr($carbs); ?>" class="small-text" min="0" step="0.1">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_fat"><?php echo __('چربی (گرم)', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <input type="number" name="ncp_fat" id="ncp_fat" value="<?php echo esc_attr($fat); ?>" class="small-text" min="0" step="0.1">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_diet_type"><?php echo __('نوع رژیم', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <select name="ncp_diet_type" id="ncp_diet_type">
                        <option value=""><?php echo __('-- انتخاب کنید --', 'nutri-coach-program'); ?></option>
                        <?php foreach ($diet_types as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($diet_type, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ncp_ingredients"><?php echo __('مواد لازم', 'nutri-coach-program'); ?></label>
                </th>
                <td>
                    <textarea name="ncp_ingredients" id="ncp_ingredients" rows="5" class="large-text"><?php echo esc_textarea($ingredients); ?></textarea>
                    <p class="description"><?php echo __('هر ماده را در یک خط بنویسید. مثال: 100 گرم برنج', 'nutri-coach-program'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * ذخیره متاباکس‌ها
     */
    public function save_meta_boxes($post_id) {
        // ذخیره متاباکس تمرینات
        if (isset($_POST['ncp_exercise_meta_nonce']) && wp_verify_nonce($_POST['ncp_exercise_meta_nonce'], 'ncp_save_exercise_meta')) {
            // ذخیره نوع تمرین
            if (isset($_POST['ncp_exercise_type'])) {
                update_post_meta($post_id, '_ncp_exercise_type', sanitize_text_field($_POST['ncp_exercise_type']));
            }
            
            // ذخیره گروه عضلانی
            if (isset($_POST['ncp_muscle_group'])) {
                update_post_meta($post_id, '_ncp_muscle_group', sanitize_text_field($_POST['ncp_muscle_group']));
            }
            
            // ذخیره سطح دشواری
            if (isset($_POST['ncp_difficulty'])) {
                update_post_meta($post_id, '_ncp_difficulty', sanitize_text_field($_POST['ncp_difficulty']));
            }
            
            // ذخیره تجهیزات مورد نیاز
            if (isset($_POST['ncp_equipment'])) {
                update_post_meta($post_id, '_ncp_equipment', sanitize_text_field($_POST['ncp_equipment']));
            }
            
            // ذخیره آدرس ویدیو
            if (isset($_POST['ncp_video_url'])) {
                update_post_meta($post_id, '_ncp_video_url', esc_url_raw($_POST['ncp_video_url']));
            }
        }
        
        // ذخیره متاباکس غذاها
        if (isset($_POST['ncp_meal_meta_nonce']) && wp_verify_nonce($_POST['ncp_meal_meta_nonce'], 'ncp_save_meal_meta')) {
            // ذخیره نوع وعده
            if (isset($_POST['ncp_meal_type'])) {
                update_post_meta($post_id, '_ncp_meal_type', sanitize_text_field($_POST['ncp_meal_type']));
            }
            
            // ذخیره کالری
            if (isset($_POST['ncp_calories'])) {
                update_post_meta($post_id, '_ncp_calories', absint($_POST['ncp_calories']));
            }
            
            // ذخیره پروتئین
            if (isset($_POST['ncp_protein'])) {
                update_post_meta($post_id, '_ncp_protein', floatval($_POST['ncp_protein']));
            }
            
            // ذخیره کربوهیدرات
            if (isset($_POST['ncp_carbs'])) {
                update_post_meta($post_id, '_ncp_carbs', floatval($_POST['ncp_carbs']));
            }
            
            // ذخیره چربی
            if (isset($_POST['ncp_fat'])) {
                update_post_meta($post_id, '_ncp_fat', floatval($_POST['ncp_fat']));
            }
            
            // ذخیره نوع رژیم
            if (isset($_POST['ncp_diet_type'])) {
                update_post_meta($post_id, '_ncp_diet_type', sanitize_text_field($_POST['ncp_diet_type']));
            }
            
            // ذخیره مواد لازم
            if (isset($_POST['ncp_ingredients'])) {
                update_post_meta($post_id, '_ncp_ingredients', sanitize_textarea_field($_POST['ncp_ingredients']));
            }
        }
    }
    
    /**
     * اضافه کردن منو به پیشخوان وردپرس
     */
    public function admin_menu() {
        // اضافه کردن صفحه قوانین تمرینی
        add_submenu_page(
            'nutri-coach',
            __('قوانین تمرینی', 'nutri-coach-program'),
            __('قوانین تمرینی', 'nutri-coach-program'),
            'manage_options',
            'nutri-coach-rules',
            array($this, 'rules_page')
        );
        
        // اضافه کردن صفحه برنامه‌های کاربران
        add_submenu_page(
            'nutri-coach',
            __('برنامه‌های کاربران', 'nutri-coach-program'),
            __('برنامه‌های کاربران', 'nutri-coach-program'),
            'manage_options',
            'nutri-coach-user-programs',
            array($this, 'user_programs_page')
        );
        
        // اضافه کردن صفحه گزارش‌های پیشرفت
        add_submenu_page(
            'nutri-coach',
            __('گزارش‌های پیشرفت', 'nutri-coach-program'),
            __('گزارش‌های پیشرفت', 'nutri-coach-program'),
            'manage_options',
            'nutri-coach-progress-reports',
            array($this, 'progress_reports_page')
        );
    }
    
    /**
     * صفحه قوانین تمرینی
     */
    public function rules_page() {
        // بررسی اکشن (نمایش/افزودن/ویرایش/حذف)
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        
        if ($action === 'add' || $action === 'edit') {
            // فرم افزودن/ویرایش قانون
            include(NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'templates/admin/rule-form.php');
        } else {
            // لیست قوانین
            include(NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'templates/admin/rules-list.php');
        }
    }
    
    /**
     * صفحه برنامه‌های کاربران
     */
    public function user_programs_page() {
        // بررسی اکشن (نمایش/افزودن/ویرایش/حذف)
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        
        if ($action === 'view' || $action === 'edit') {
            // مشاهده/ویرایش برنامه کاربر
            include(NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'templates/admin/user-program.php');
        } else {
            // لیست برنامه‌های کاربران
            include(NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'templates/admin/user-programs-list.php');
        }
    }
    
/**
 * صفحه گزارش‌های پیشرفت
 */
public function progress_reports_page() {
    // ایجاد نمونه از کلاس پیگیری پیشرفت
    require_once NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'includes/class-progress-tracker.php';
    $nutri_coach_progress = new Nutri_Coach_Progress_Tracker();
    
    // بررسی پارامترها (کاربر، تاریخ و...)
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($user_id > 0) {
        // نمایش گزارش پیشرفت یک کاربر خاص
        include(NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'templates/admin/user-progress-report.php');
    } else {
        // لیست کاربران برای انتخاب
        include(NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'templates/admin/progress-reports-list.php');
    }
}
    
    /**
     * شورت‌کد نمایش برنامه تمرینی و غذایی
     */
    public function program_shortcode($atts) {
        // پارامترهای شورت‌کد
        $atts = shortcode_atts(array(
            'user_id' => 0,
            'type' => 'both', // workout, diet, both
        ), $atts);
        
        // اگر آی‌دی کاربر مشخص نشده، کاربر جاری را استفاده کن
        if ($atts['user_id'] <= 0) {
            $atts['user_id'] = get_current_user_id();
        }
        
        // اگر کاربر لاگین نیست
        if ($atts['user_id'] <= 0) {
            return '<div class="nutri-coach-login-required">' .
                   __('برای مشاهده برنامه خود لطفا وارد شوید.', 'nutri-coach-program') .
                   '<br><a href="' . wp_login_url(get_permalink()) . '" class="button">' . __('ورود', 'nutri-coach-program') . '</a>' .
                   '</div>';
        }
        
        // شروع بافر خروجی
        ob_start();
        
        // بارگذاری قالب برنامه
        include(NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'templates/program.php');
        
        // بازگرداندن خروجی
        return ob_get_clean();
    }
    
    /**
     * شورت‌کد نمایش پیشرفت کاربر
     */
    public function progress_shortcode($atts) {
        // پارامترهای شورت‌کد
        $atts = shortcode_atts(array(
            'user_id' => 0,
            'type' => 'both', // workout, diet, both
            'period' => 'month', // week, month, year, all
        ), $atts);
        
        // اگر آی‌دی کاربر مشخص نشده، کاربر جاری را استفاده کن
        if ($atts['user_id'] <= 0) {
            $atts['user_id'] = get_current_user_id();
        }
        
        // اگر کاربر لاگین نیست
        if ($atts['user_id'] <= 0) {
            return '<div class="nutri-coach-login-required">' .
                   __('برای مشاهده پیشرفت خود لطفا وارد شوید.', 'nutri-coach-program') .
                   '<br><a href="' . wp_login_url(get_permalink()) . '" class="button">' . __('ورود', 'nutri-coach-program') . '</a>' .
                   '</div>';
        }
        
        // شروع بافر خروجی
        ob_start();
        
        // بارگذاری قالب پیشرفت
        include(NUTRI_COACH_PROGRAM_PLUGIN_DIR . 'templates/progress.php');
        
        // بازگرداندن خروجی
        return ob_get_clean();
    }
    
    /**
     * اجکس برای تیک زدن تمرین‌ها
     */
    public function ajax_mark_exercise() {
        // بررسی امنیتی
        check_ajax_referer('nutri_coach_program_nonce', 'security');
        
        // بررسی آیا کاربر لاگین است
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('برای ثبت پیشرفت باید وارد شوید.', 'nutri-coach-program')
            ));
            return;
        }
        
        $user_id = get_current_user_id();
        $program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
        $exercise_id = isset($_POST['exercise_id']) ? sanitize_text_field($_POST['exercise_id']) : '';
        $completed = isset($_POST['completed']) ? (intval($_POST['completed']) === 1) : false;
        $sets = isset($_POST['sets']) ? intval($_POST['sets']) : 0;
        $reps = isset($_POST['reps']) ? intval($_POST['reps']) : 0;
        $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        // بررسی پارامترها
        if ($program_id <= 0 || empty($exercise_id)) {
            wp_send_json_error(array(
                'message' => __('پارامترهای نامعتبر.', 'nutri-coach-program')
            ));
            return;
        }
        
        // تعامل با کلاس پیگیری پیشرفت
        $progress_tracker = new Nutri_Coach_Progress_Tracker();
        $result = $progress_tracker->track_exercise_progress($user_id, $program_id, $exercise_id, $completed, $sets, $reps, $weight, $notes);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('پیشرفت با موفقیت ثبت شد.', 'nutri-coach-program')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('خطا در ثبت پیشرفت.', 'nutri-coach-program')
            ));
        }
    }
    
    /**
     * اجکس برای تیک زدن وعده‌های غذایی
     */
    public function ajax_mark_meal() {
        // بررسی امنیتی
        check_ajax_referer('nutri_coach_program_nonce', 'security');
        
        // بررسی آیا کاربر لاگین است
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('برای ثبت پیشرفت باید وارد شوید.', 'nutri-coach-program')
            ));
            return;
        }
        
        $user_id = get_current_user_id();
        $program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
        $meal_id = isset($_POST['meal_id']) ? sanitize_text_field($_POST['meal_id']) : '';
        $completed = isset($_POST['completed']) ? (intval($_POST['completed']) === 1) : false;
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        // بررسی پارامترها
        if ($program_id <= 0 || empty($meal_id)) {
            wp_send_json_error(array(
                'message' => __('پارامترهای نامعتبر.', 'nutri-coach-program')
            ));
            return;
        }
        
        // تعامل با کلاس پیگیری پیشرفت
        $progress_tracker = new Nutri_Coach_Progress_Tracker();
        $result = $progress_tracker->track_meal_progress($user_id, $program_id, $meal_id, $completed, $notes);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('پیشرفت با موفقیت ثبت شد.', 'nutri-coach-program')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('خطا در ثبت پیشرفت.', 'nutri-coach-program')
            ));
        }
    }
    
    /**
     * اضافه کردن اسکریپت‌ها و استایل‌های سمت کاربر
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'nutri-coach-program-style',
            NUTRI_COACH_PROGRAM_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            NUTRI_COACH_PROGRAM_VERSION
        );
        
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js',
            array(),
            '3.7.1',
            true
        );
        
        wp_enqueue_script(
            'nutri-coach-program-script',
            NUTRI_COACH_PROGRAM_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery', 'chart-js'),
            NUTRI_COACH_PROGRAM_VERSION,
            true
        );
        
        wp_localize_script(
            'nutri-coach-program-script',
            'nutriCoachProgram',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('nutri_coach_program_nonce'),
                'i18n' => array(
                    'save_success' => __('اطلاعات با موفقیت ذخیره شد.', 'nutri-coach-program'),
                    'save_error' => __('خطا در ذخیره اطلاعات.', 'nutri-coach-program'),
                ),
            )
        );
    }
    
    /**
     * اضافه کردن اسکریپت‌ها و استایل‌های سمت مدیر
     */
    public function enqueue_admin_scripts($hook) {
        // اضافه کردن استایل‌ها فقط در صفحات مربوط به افزونه
        if (strpos($hook, 'nutri-coach') !== false) {
            wp_enqueue_style(
                'nutri-coach-program-admin-style',
                NUTRI_COACH_PROGRAM_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                NUTRI_COACH_PROGRAM_VERSION
            );
            
            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js',
                array(),
                '3.7.1',
                true
            );
            
            wp_enqueue_script(
                'nutri-coach-program-admin-script',
                NUTRI_COACH_PROGRAM_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'chart-js'),
                NUTRI_COACH_PROGRAM_VERSION,
                true
            );
            
            wp_localize_script(
                'nutri-coach-program-admin-script',
                'nutriCoachProgramAdmin',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('nutri_coach_program_admin_nonce'),
                )
            );
        }
    }
    
    /**
     * افزودن داده‌های برنامه به پروفایل کاربر
     */
    public function add_program_data_to_profile($user_data, $user_id) {
        // بررسی آیا کاربر برنامه دارد
        global $wpdb;
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'user_programs';
        
        $program = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND status = 'active' ORDER BY id DESC LIMIT 1",
                $user_id
            ),
            ARRAY_A
        );
        
        if ($program) {
            // اضافه کردن اطلاعات برنامه به داده‌های کاربر
            $user_data['has_program'] = true;
            $user_data['program_id'] = $program['id'];
            $user_data['program_start_date'] = $program['start_date'];
            
            // اضافه کردن جزئیات برنامه‌ها
            if (!empty($program['workout_program'])) {
                $user_data['workout_program'] = json_decode($program['workout_program'], true);
            }
            
            if (!empty($program['diet_program'])) {
                $user_data['diet_program'] = json_decode($program['diet_program'], true);
            }
            
            // اضافه کردن آمار پیشرفت
            $progress_tracker = new Nutri_Coach_Progress_Tracker();
            $user_data['progress_stats'] = $progress_tracker->get_progress_stats($user_id, $program['id']);
        } else {
            $user_data['has_program'] = false;
        }
        
        return $user_data;
    }
    
    /**
     * واکنش به بروزرسانی پروفایل کاربر
     */
    public function profile_updated($user_id, $data) {
        // بررسی آیا نیاز به بروزرسانی برنامه کاربر است
        $this->check_and_update_user_program($user_id);
    }
    
    /**
     * واکنش به افزودن داده‌های آنتروپومتریک جدید
     */
    public function anthropometric_added($user_id, $data) {
        // بررسی آیا نیاز به بروزرسانی برنامه کاربر است
        $this->check_and_update_user_program($user_id);
    }
    
    /**
     * بررسی و بروزرسانی برنامه کاربر
     */
    private function check_and_update_user_program($user_id) {
        // دریافت اطلاعات کاربر
        $user_data = apply_filters('nutri_coach_get_user_data', array(), $user_id);
        
        if (empty($user_data)) {
            return;
        }
        
        // یافتن قانون مناسب
        $rule_id = $this->find_matching_rule($user_data);
        
        if ($rule_id > 0) {
            // اختصاص برنامه به کاربر
            $this->assign_program_to_user($user_id, $rule_id);
        }
    }
    
    /**
     * یافتن قانون مناسب برای کاربر
     */
    private function find_matching_rule($user_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        // استخراج اطلاعات کاربر
        $gender = isset($user_data['gender']) ? $user_data['gender'] : '';
        $age = isset($user_data['age']) ? intval($user_data['age']) : 0;
        $waist = isset($user_data['waist_circumference']) ? floatval($user_data['waist_circumference']) : 0;
        $hip = isset($user_data['hip_circumference']) ? floatval($user_data['hip_circumference']) : 0;
        $neck = isset($user_data['neck_circumference']) ? floatval($user_data['neck_circumference']) : 0;
        $fitness_goal = isset($user_data['fitness_goal']) ? $user_data['fitness_goal'] : '';
        
        // ساخت شرط پیچیده SQL
        $where_conditions = array();
        $where_params = array();
        
        // شرط جنسیت
        if (!empty($gender)) {
            $where_conditions[] = "(gender = %s OR gender = '')";
            $where_params[] = $gender;
        }
        
        // شرط سن
        if ($age > 0) {
            $where_conditions[] = "((age_min <= %d AND age_max >= %d) OR (age_min = 0 AND age_max = 0) OR (age_min = 0 AND age_max >= %d) OR (age_min <= %d AND age_max = 0))";
            $where_params[] = $age;
            $where_params[] = $age;
            $where_params[] = $age;
            $where_params[] = $age;
        }
        
        // شرط دور کمر
        if ($waist > 0) {
            $where_conditions[] = "((waist_min <= %f AND waist_max >= %f) OR (waist_min = 0 AND waist_max = 0) OR (waist_min = 0 AND waist_max >= %f) OR (waist_min <= %f AND waist_max = 0))";
            $where_params[] = $waist;
            $where_params[] = $waist;
            $where_params[] = $waist;
            $where_params[] = $waist;
        }
        
        // شرط دور باسن
        if ($hip > 0) {
            $where_conditions[] = "((hip_min <= %f AND hip_max >= %f) OR (hip_min = 0 AND hip_max = 0) OR (hip_min = 0 AND hip_max >= %f) OR (hip_min <= %f AND hip_max = 0))";
            $where_params[] = $hip;
            $where_params[] = $hip;
            $where_params[] = $hip;
            $where_params[] = $hip;
        }
        
        // شرط دور گردن
        if ($neck > 0) {
            $where_conditions[] = "((neck_min <= %f AND neck_max >= %f) OR (neck_min = 0 AND neck_max = 0) OR (neck_min = 0 AND neck_max >= %f) OR (neck_min <= %f AND neck_max = 0))";
            $where_params[] = $neck;
            $where_params[] = $neck;
            $where_params[] = $neck;
            $where_params[] = $neck;
        }
        
        // شرط هدف ورزشی
        if (!empty($fitness_goal)) {
            $where_conditions[] = "(fitness_goal = %s OR fitness_goal = '')";
            $where_params[] = $fitness_goal;
        }
        
        // وضعیت فعال
        $where_conditions[] = "status = %s";
        $where_params[] = 'active';
        
        // ساخت کوئری نهایی
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT id FROM $table_name WHERE $where_clause ORDER BY id DESC LIMIT 1";
        $prepared_query = $wpdb->prepare($query, $where_params);
        
        $rule_id = $wpdb->get_var($prepared_query);
        
        return $rule_id ? intval($rule_id) : 0;
    }
    
    /**
     * اختصاص برنامه به کاربر
     */
    private function assign_program_to_user($user_id, $rule_id) {
        global $wpdb;
        
        // بررسی آیا کاربر قبلاً این برنامه را دارد
        $table_user_programs = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'user_programs';
        
        $existing_program = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_user_programs WHERE user_id = %d AND rule_id = %d AND status = 'active'",
                $user_id,
                $rule_id
            )
        );
        
        if ($existing_program) {
            // کاربر قبلاً این برنامه را دارد، نیازی به اختصاص مجدد نیست
            return;
        }
        
        // دریافت اطلاعات قانون
        $table_rules = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        $rule = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_rules WHERE id = %d",
                $rule_id
            ),
            ARRAY_A
        );
        
        if (!$rule) {
            return;
        }
        
        // غیرفعال کردن برنامه‌های قبلی کاربر
        $wpdb->update(
            $table_user_programs,
            array('status' => 'inactive'),
            array('user_id' => $user_id, 'status' => 'active'),
            array('%s'),
            array('%d', '%s')
        );
        
        // اختصاص برنامه جدید به کاربر
        $wpdb->insert(
            $table_user_programs,
            array(
                'user_id' => $user_id,
                'rule_id' => $rule_id,
                'workout_program' => $rule['workout_program'],
                'diet_program' => $rule['diet_program'],
                'start_date' => current_time('mysql'),
                'status' => 'active',
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s')
        );
    }
}

/**
 * فانکشن دسترسی آسان به نمونه کلاس اصلی
 */
function nutri_coach_program() {
    return Nutri_Coach_Program::instance();
}

// راه‌اندازی افزونه
nutri_coach_program();