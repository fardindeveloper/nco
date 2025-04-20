<?php
/**
 * کلاس پیگیری پیشرفت
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * کلاس پیگیری پیشرفت کاربران
 */
class Nutri_Coach_Progress_Tracker {
    /**
     * ثبت پیشرفت تمرین
     *
     * @param int $user_id شناسه کاربر
     * @param int $program_id شناسه برنامه
     * @param string $exercise_id شناسه تمرین
     * @param bool $completed آیا تمرین انجام شده است
     * @param int $sets تعداد ست‌ها
     * @param int $reps تعداد تکرارها
     * @param float $weight وزنه استفاده شده
     * @param string $notes یادداشت‌ها
     * @return bool نتیجه ثبت
     */
    public function track_exercise_progress($user_id, $program_id, $exercise_id, $completed, $sets = 0, $reps = 0, $weight = 0, $notes = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'exercise_progress';
        
        // بررسی آیا رکورد موجود است
        $existing_record = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM $table_name 
                WHERE user_id = %d AND program_id = %d AND exercise_id = %s AND DATE(date) = DATE(%s)",
                $user_id,
                $program_id,
                $exercise_id,
                current_time('mysql')
            )
        );
        
        if ($existing_record) {
            // به‌روزرسانی رکورد موجود
            $result = $wpdb->update(
                $table_name,
                array(
                    'completed' => $completed ? 1 : 0,
                    'sets' => $sets,
                    'reps' => $reps,
                    'weight' => $weight,
                    'notes' => $notes,
                ),
                array('id' => $existing_record->id),
                array('%d', '%d', '%d', '%f', '%s'),
                array('%d')
            );
            
            return $result !== false;
        } else {
            // ایجاد رکورد جدید
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'program_id' => $program_id,
                    'exercise_id' => $exercise_id,
                    'date' => current_time('mysql'),
                    'completed' => $completed ? 1 : 0,
                    'sets' => $sets,
                    'reps' => $reps,
                    'weight' => $weight,
                    'notes' => $notes,
                ),
                array('%d', '%d', '%s', '%s', '%d', '%d', '%d', '%f', '%s')
            );
            
            return $result !== false;
        }
    }
    
    /**
     * ثبت پیشرفت وعده غذایی
     *
     * @param int $user_id شناسه کاربر
     * @param int $program_id شناسه برنامه
     * @param string $meal_id شناسه وعده غذایی
     * @param bool $completed آیا وعده مصرف شده است
     * @param string $notes یادداشت‌ها
     * @return bool نتیجه ثبت
     */
    public function track_meal_progress($user_id, $program_id, $meal_id, $completed, $notes = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'meal_progress';
        
        // بررسی آیا رکورد موجود است
        $existing_record = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM $table_name 
                WHERE user_id = %d AND program_id = %d AND meal_id = %s AND DATE(date) = DATE(%s)",
                $user_id,
                $program_id,
                $meal_id,
                current_time('mysql')
            )
        );
        
        if ($existing_record) {
            // به‌روزرسانی رکورد موجود
            $result = $wpdb->update(
                $table_name,
                array(
                    'completed' => $completed ? 1 : 0,
                    'notes' => $notes,
                ),
                array('id' => $existing_record->id),
                array('%d', '%s'),
                array('%d')
            );
            
            return $result !== false;
        } else {
            // ایجاد رکورد جدید
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'program_id' => $program_id,
                    'meal_id' => $meal_id,
                    'date' => current_time('mysql'),
                    'completed' => $completed ? 1 : 0,
                    'notes' => $notes,
                ),
                array('%d', '%d', '%s', '%s', '%d', '%s')
            );
            
            return $result !== false;
        }
    }
    
    /**
     * دریافت پیشرفت تمرین‌ها
     *
     * @param int $user_id شناسه کاربر
     * @param int $program_id شناسه برنامه
     * @param string $period دوره زمانی (day, week, month, year, all)
     * @return array پیشرفت تمرین‌ها
     */
    public function get_exercise_progress($user_id, $program_id, $period = 'week') {
        global $wpdb;
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'exercise_progress';
        
        // تنظیم محدوده زمانی بر اساس دوره
        $date_condition = $this->get_date_condition($period);
        
        // ساخت کوئری
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE user_id = %d AND program_id = %d $date_condition
            ORDER BY date DESC",
            $user_id,
            $program_id
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * دریافت پیشرفت وعده‌های غذایی
     *
     * @param int $user_id شناسه کاربر
     * @param int $program_id شناسه برنامه
     * @param string $period دوره زمانی (day, week, month, year, all)
     * @return array پیشرفت وعده‌های غذایی
     */
    public function get_meal_progress($user_id, $program_id, $period = 'week') {
        global $wpdb;
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'meal_progress';
        
        // تنظیم محدوده زمانی بر اساس دوره
        $date_condition = $this->get_date_condition($period);
        
        // ساخت کوئری
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE user_id = %d AND program_id = %d $date_condition
            ORDER BY date DESC",
            $user_id,
            $program_id
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * دریافت آمار کلی پیشرفت
     *
     * @param int $user_id شناسه کاربر
     * @param int $program_id شناسه برنامه
     * @return array آمار پیشرفت
     */
    public function get_progress_stats($user_id, $program_id) {
        global $wpdb;
        
        // جدول تمرین‌ها
        $exercise_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'exercise_progress';
        
        // جدول وعده‌های غذایی
        $meal_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'meal_progress';
        
        // آمار کلی تمرین‌ها
        $exercise_stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                COUNT(*) as total_sessions,
                SUM(completed) as completed_sessions,
                MAX(date) as last_exercise_date
                FROM $exercise_table
                WHERE user_id = %d AND program_id = %d",
                $user_id,
                $program_id
            ),
            ARRAY_A
        );
        
        // آمار کلی وعده‌های غذایی
        $meal_stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                COUNT(*) as total_meals,
                SUM(completed) as completed_meals,
                MAX(date) as last_meal_date
                FROM $meal_table
                WHERE user_id = %d AND program_id = %d",
                $user_id,
                $program_id
            ),
            ARRAY_A
        );
        
        // آمار هفتگی تمرین‌ها
        $weekly_exercise_stats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                YEARWEEK(date, 1) as yearweek,
                COUNT(*) as total,
                SUM(completed) as completed
                FROM $exercise_table
                WHERE user_id = %d AND program_id = %d AND date >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
                GROUP BY YEARWEEK(date, 1)
                ORDER BY yearweek ASC",
                $user_id,
                $program_id
            ),
            ARRAY_A
        );
        
        // آمار هفتگی وعده‌های غذایی
        $weekly_meal_stats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                YEARWEEK(date, 1) as yearweek,
                COUNT(*) as total,
                SUM(completed) as completed
                FROM $meal_table
                WHERE user_id = %d AND program_id = %d AND date >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
                GROUP BY YEARWEEK(date, 1)
                ORDER BY yearweek ASC",
                $user_id,
                $program_id
            ),
            ARRAY_A
        );
        
        // دریافت روند وزن از اطلاعات آنتروپومتریک
        $weight_trend = $this->get_weight_trend($user_id);
        
        // ترکیب همه آمارها
        $stats = array(
            'exercise' => array(
                'total' => intval($exercise_stats['total_sessions']),
                'completed' => intval($exercise_stats['completed_sessions']),
                'completion_rate' => $exercise_stats['total_sessions'] > 0 ? 
                    round(($exercise_stats['completed_sessions'] / $exercise_stats['total_sessions']) * 100) : 0,
                'last_date' => $exercise_stats['last_exercise_date'],
                'weekly' => $weekly_exercise_stats,
            ),
            'meal' => array(
                'total' => intval($meal_stats['total_meals']),
                'completed' => intval($meal_stats['completed_meals']),
                'completion_rate' => $meal_stats['total_meals'] > 0 ? 
                    round(($meal_stats['completed_meals'] / $meal_stats['total_meals']) * 100) : 0,
                'last_date' => $meal_stats['last_meal_date'],
                'weekly' => $weekly_meal_stats,
            ),
            'weight_trend' => $weight_trend,
        );
        
        return $stats;
    }
    
    /**
     * دریافت روند وزن
     *
     * @param int $user_id شناسه کاربر
     * @return array روند وزن
     */
    private function get_weight_trend($user_id) {
        // اگر افزونه پروفایل موجود است، از آن استفاده کن
        if (function_exists('ncp_get_anthropometric_history')) {
            $history = ncp_get_anthropometric_history($user_id);
            
            if (!empty($history)) {
                $trend = array();
                
                foreach ($history as $record) {
                    // فقط رکوردهایی که وزن دارند
                    if (isset($record['weight']) && $record['weight'] > 0) {
                        $trend[] = array(
                            'date' => $record['date'],
                            'weight' => $record['weight'],
                        );
                    }
                }
                
                return $trend;
            }
        }
        
        return array();
    }
    
    /**
     * دریافت شرط زمانی SQL بر اساس دوره
     *
     * @param string $period دوره زمانی
     * @return string شرط زمانی SQL
     */
    private function get_date_condition($period) {
        switch ($period) {
            case 'day':
                return "AND DATE(date) = DATE(NOW())";
                
            case 'week':
                return "AND date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                
            case 'month':
                return "AND date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                
            case 'year':
                return "AND date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                
            case 'all':
            default:
                return "";
        }
    }
    
    /**
     * دریافت داده‌های نمودار پیشرفت
     *
     * @param int $user_id شناسه کاربر
     * @param int $program_id شناسه برنامه
     * @param string $period دوره زمانی
     * @return array داده‌های نمودار
     */
    public function get_chart_data($user_id, $program_id, $period = 'month') {
        // دریافت داده‌های پیشرفت
        $exercise_progress = $this->get_exercise_progress($user_id, $program_id, $period);
        $meal_progress = $this->get_meal_progress($user_id, $program_id, $period);
        $weight_trend = $this->get_weight_trend($user_id);
        
        // سازماندهی داده‌ها بر اساس تاریخ
        $dates = array();
        $exercise_data = array();
        $meal_data = array();
        $weight_data = array();
        
        // ساخت آرایه تاریخ‌ها
        $start_date = $this->get_start_date($period);
        $end_date = date('Y-m-d');
        
        $current_date = $start_date;
        while (strtotime($current_date) <= strtotime($end_date)) {
            $dates[] = $current_date;
            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }
        
        // مقداردهی اولیه داده‌ها
        foreach ($dates as $date) {
            $exercise_data[$date] = array(
                'total' => 0,
                'completed' => 0,
            );
            
            $meal_data[$date] = array(
                'total' => 0,
                'completed' => 0,
            );
            
            $weight_data[$date] = null;
        }
        
        // پر کردن داده‌های تمرین
        foreach ($exercise_progress as $progress) {
            $date = date('Y-m-d', strtotime($progress['date']));
            
            if (isset($exercise_data[$date])) {
                $exercise_data[$date]['total']++;
                
                if ($progress['completed']) {
                    $exercise_data[$date]['completed']++;
                }
            }
        }
        
        // پر کردن داده‌های وعده غذایی
        foreach ($meal_progress as $progress) {
            $date = date('Y-m-d', strtotime($progress['date']));
            
            if (isset($meal_data[$date])) {
                $meal_data[$date]['total']++;
                
                if ($progress['completed']) {
                    $meal_data[$date]['completed']++;
                }
            }
        }
        
        // پر کردن داده‌های وزن
        foreach ($weight_trend as $weight) {
            $date = date('Y-m-d', strtotime($weight['date']));
            
            if (isset($weight_data[$date])) {
                $weight_data[$date] = $weight['weight'];
            }
        }
        
        // تبدیل داده‌ها به فرمت مورد نیاز نمودار
        $chart_data = array(
            'labels' => $dates,
            'exercise' => array(
                'completion_rate' => array(),
            ),
            'meal' => array(
                'completion_rate' => array(),
            ),
            'weight' => array(),
        );
        
        foreach ($dates as $date) {
            // نرخ تکمیل تمرین
            $exercise_completion_rate = $exercise_data[$date]['total'] > 0 ? 
                ($exercise_data[$date]['completed'] / $exercise_data[$date]['total']) * 100 : 0;
            $chart_data['exercise']['completion_rate'][] = round($exercise_completion_rate);
            
            // نرخ تکمیل وعده غذایی
            $meal_completion_rate = $meal_data[$date]['total'] > 0 ? 
                ($meal_data[$date]['completed'] / $meal_data[$date]['total']) * 100 : 0;
            $chart_data['meal']['completion_rate'][] = round($meal_completion_rate);
            
            // وزن
            $chart_data['weight'][] = $weight_data[$date];
        }
        
        return $chart_data;
    }
    
    /**
     * دریافت تاریخ شروع بر اساس دوره
     *
     * @param string $period دوره زمانی
     * @return string تاریخ شروع
     */
    private function get_start_date($period) {
        switch ($period) {
            case 'day':
                return date('Y-m-d');
                
            case 'week':
                return date('Y-m-d', strtotime('-7 days'));
                
            case 'month':
                return date('Y-m-d', strtotime('-30 days'));
                
            case 'year':
                return date('Y-m-d', strtotime('-365 days'));
                
            case 'all':
            default:
                return date('Y-m-d', strtotime('-90 days')); // پیش‌فرض: 3 ماه گذشته
        }
    }
    
    /**
     * دریافت گزارش‌های پیشرفت
     *
     * @param int $offset تعداد رکوردهای رد شده
     * @param int $limit تعداد رکوردهای درخواستی
     * @param string $user_search جستجوی کاربر
     * @param string $date_from از تاریخ
     * @param string $date_to تا تاریخ
     * @param string $program_type نوع برنامه
     * @return array لیست گزارش‌ها
     */
    public function get_progress_reports($offset = 0, $limit = 20, $user_search = '', $date_from = '', $date_to = '', $program_type = '') {
        global $wpdb;
        
        // تعیین جدول مناسب بر اساس نوع برنامه
        if ($program_type === 'workout') {
            $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'exercise_progress';
        } elseif ($program_type === 'diet') {
            $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'meal_progress';
        } else {
            // اگر نوع برنامه مشخص نشده، هر دو جدول را ترکیب کن
            $exercise_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'exercise_progress';
            $meal_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'meal_progress';
            
            // ایجاد کوئری ترکیبی
            $union_query = "(SELECT user_id, program_id, 'workout' as program_type, date as progress_date, completed, notes, id FROM $exercise_table) 
                          UNION ALL 
                          (SELECT user_id, program_id, 'diet' as program_type, date as progress_date, completed, notes, id FROM $meal_table)";
            
            $table_name = "($union_query) as combined_reports";
        }
        
        // ایجاد شرط‌های SQL
        $where = array();
        $query_params = array();
        
        // فیلتر جستجوی کاربر
        if (!empty($user_search)) {
            // پیدا کردن آی‌دی کاربران بر اساس نام یا ایمیل
            $users = new WP_User_Query(array(
                'search' => '*' . $user_search . '*',
                'search_columns' => array('user_login', 'user_email', 'display_name'),
                'fields' => 'ID',
            ));
            
            $user_ids = $users->get_results();
            
            if (!empty($user_ids)) {
                $placeholders = implode(',', array_fill(0, count($user_ids), '%d'));
                $where[] = "user_id IN ($placeholders)";
                $query_params = array_merge($query_params, $user_ids);
            } else {
                // اگر هیچ کاربری پیدا نشد، نتیجه خالی باشد
                return array();
            }
        }
        
        // فیلتر تاریخ
        if (!empty($date_from)) {
            $where[] = "progress_date >= %s";
            $query_params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $where[] = "progress_date <= %s";
            $query_params[] = $date_to;
        }
        
        // فیلتر نوع برنامه (فقط برای کوئری ترکیبی)
        if (!empty($program_type) && $table_name !== "($union_query) as combined_reports") {
            $where[] = "program_type = %s";
            $query_params[] = $program_type;
        }
        
        // ترکیب شرط‌ها
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
        }
        
        // افزودن پارامترهای limit و offset
        $query_params[] = $limit;
        $query_params[] = $offset;
        
        // ایجاد و اجرای کوئری
        $query = "SELECT * FROM $table_name $where_clause ORDER BY progress_date DESC, id DESC LIMIT %d OFFSET %d";
        $progress_reports = $wpdb->get_results($wpdb->prepare($query, $query_params), ARRAY_A);
        
        // اضافه کردن فیلد completed_percentage برای هر رکورد
        foreach ($progress_reports as &$report) {
            $report['completed_percentage'] = $report['completed'] ? 100 : 0;
        }
        
        return $progress_reports;
    }
    
    /**
     * دریافت تعداد کل گزارش‌های پیشرفت
     *
     * @param string $user_search جستجوی کاربر
     * @param string $date_from از تاریخ
     * @param string $date_to تا تاریخ
     * @param string $program_type نوع برنامه
     * @return int تعداد کل گزارش‌ها
     */
    public function get_total_progress_reports($user_search = '', $date_from = '', $date_to = '', $program_type = '') {
        global $wpdb;
        
        // تعیین جدول مناسب بر اساس نوع برنامه
        if ($program_type === 'workout') {
            $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'exercise_progress';
        } elseif ($program_type === 'diet') {
            $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'meal_progress';
        } else {
            // اگر نوع برنامه مشخص نشده، هر دو جدول را ترکیب کن
            $exercise_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'exercise_progress';
            $meal_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'meal_progress';
            
            // ایجاد کوئری ترکیبی
            $union_query = "(SELECT user_id, 'workout' as program_type, date as progress_date FROM $exercise_table) 
                          UNION ALL 
                          (SELECT user_id, 'diet' as program_type, date as progress_date FROM $meal_table)";
            
            $table_name = "($union_query) as combined_reports";
        }
        
        // ایجاد شرط‌های SQL
        $where = array();
        $query_params = array();
        
        // فیلتر جستجوی کاربر
        if (!empty($user_search)) {
            // پیدا کردن آی‌دی کاربران بر اساس نام یا ایمیل
            $users = new WP_User_Query(array(
                'search' => '*' . $user_search . '*',
                'search_columns' => array('user_login', 'user_email', 'display_name'),
                'fields' => 'ID',
            ));
            
            $user_ids = $users->get_results();
            
            if (!empty($user_ids)) {
                $placeholders = implode(',', array_fill(0, count($user_ids), '%d'));
                $where[] = "user_id IN ($placeholders)";
                $query_params = array_merge($query_params, $user_ids);
            } else {
                // اگر هیچ کاربری پیدا نشد، نتیجه صفر باشد
                return 0;
            }
        }
        
        // فیلتر تاریخ
        if (!empty($date_from)) {
            $where[] = "progress_date >= %s";
            $query_params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $where[] = "progress_date <= %s";
            $query_params[] = $date_to;
        }
        
        // فیلتر نوع برنامه (فقط برای کوئری ترکیبی)
        if (!empty($program_type) && $table_name !== "($union_query) as combined_reports") {
            $where[] = "program_type = %s";
            $query_params[] = $program_type;
        }
        
        // ترکیب شرط‌ها
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
        }
        
        // ایجاد و اجرای کوئری
        $query = "SELECT COUNT(*) FROM $table_name $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($query, $query_params));
        
        return intval($total);
    }
    
    /**
     * دریافت پیشرفت کاربر
     *
     * @param int $user_id شناسه کاربر
     * @return array پیشرفت کاربر
     */
    public function get_user_progress($user_id) {
        global $wpdb;
        
        $exercise_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'exercise_progress';
        $meal_table = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'meal_progress';
        
        $exercise_query = $wpdb->prepare(
            "SELECT 'workout' as program_type, date as progress_date, completed, notes, id 
            FROM $exercise_table 
            WHERE user_id = %d",
            $user_id
        );
        
        $meal_query = $wpdb->prepare(
            "SELECT 'diet' as program_type, date as progress_date, completed, notes, id 
            FROM $meal_table 
            WHERE user_id = %d",
            $user_id
        );
        
        $query = "($exercise_query) UNION ALL ($meal_query) ORDER BY progress_date DESC";
        
        $progress_data = $wpdb->get_results($query, ARRAY_A);
        
        foreach ($progress_data as &$record) {
            $record['completed_percentage'] = $record['completed'] ? 100 : 0;
        }
        
        return $progress_data;
    }
}
?>