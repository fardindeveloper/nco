<?php
/**
 * توابع عمومی افزونه Nutri Coach
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * محاسبه شاخص توده بدنی (BMI)
 *
 * @param float $weight وزن به کیلوگرم
 * @param float $height قد به سانتی‌متر
 * @return float شاخص توده بدنی
 */
function nutri_coach_calculate_bmi($weight, $height) {
    if ($weight <= 0 || $height <= 0) {
        return 0;
    }
    
    // تبدیل سانتی‌متر به متر
    $height_in_meters = $height / 100;
    
    // فرمول BMI: وزن (کیلوگرم) تقسیم بر مجذور قد (متر)
    $bmi = $weight / ($height_in_meters * $height_in_meters);
    
    return round($bmi, 2);
}

/**
 * دریافت دسته‌بندی BMI
 *
 * @param float $bmi شاخص توده بدنی
 * @return string دسته‌بندی BMI
 */
function nutri_coach_bmi_category($bmi) {
    if ($bmi < 18.5) {
        return __('کمبود وزن', 'nutri-coach-program');
    } elseif ($bmi < 25) {
        return __('طبیعی', 'nutri-coach-program');
    } elseif ($bmi < 30) {
        return __('اضافه وزن', 'nutri-coach-program');
    } elseif ($bmi < 35) {
        return __('چاقی درجه ۱', 'nutri-coach-program');
    } elseif ($bmi < 40) {
        return __('چاقی درجه ۲', 'nutri-coach-program');
    } else {
        return __('چاقی درجه ۳', 'nutri-coach-program');
    }
}

/**
 * محاسبه درصد چربی بدن با استفاده از فرمول نیروی دریایی آمریکا
 *
 * @param float $weight وزن به کیلوگرم
 * @param float $height قد به سانتی‌متر
 * @param float $neck دور گردن به سانتی‌متر
 * @param float $waist دور کمر به سانتی‌متر
 * @param float $hip دور باسن به سانتی‌متر (فقط برای زنان)
 * @param string $gender جنسیت (male یا female)
 * @return float درصد چربی بدن
 */
function nutri_coach_calculate_body_fat($weight, $height, $neck, $waist, $hip = 0, $gender = 'male') {
    if ($weight <= 0 || $height <= 0 || $neck <= 0 || $waist <= 0 || ($gender === 'female' && $hip <= 0)) {
        return 0;
    }
    
    if ($gender === 'male') {
        $body_fat = 495 / (1.0324 - 0.19077 * log10($waist - $neck) + 0.15456 * log10($height)) - 450;
    } else {
        $body_fat = 495 / (1.29579 - 0.35004 * log10($waist + $hip - $neck) + 0.22100 * log10($height)) - 450;
    }
    
    return round($body_fat, 2);
}

/**
 * دریافت دسته‌بندی درصد چربی بدن
 *
 * @param float $body_fat درصد چربی بدن
 * @param string $gender جنسیت (male یا female)
 * @return string دسته‌بندی درصد چربی بدن
 */
function nutri_coach_body_fat_category($body_fat, $gender = 'male') {
    if ($gender === 'male') {
        if ($body_fat < 6) {
            return __('ضروری', 'nutri-coach-program');
        } elseif ($body_fat < 14) {
            return __('ورزشکار', 'nutri-coach-program');
        } elseif ($body_fat < 18) {
            return __('تناسب اندام', 'nutri-coach-program');
        } elseif ($body_fat < 25) {
            return __('قابل قبول', 'nutri-coach-program');
        } else {
            return __('چاق', 'nutri-coach-program');
        }
    } else {
        if ($body_fat < 14) {
            return __('ضروری', 'nutri-coach-program');
        } elseif ($body_fat < 21) {
            return __('ورزشکار', 'nutri-coach-program');
        } elseif ($body_fat < 25) {
            return __('تناسب اندام', 'nutri-coach-program');
        } elseif ($body_fat < 32) {
            return __('قابل قبول', 'nutri-coach-program');
        } else {
            return __('چاق', 'nutri-coach-program');
        }
    }
}

/**
 * محاسبه نسبت دور کمر به قد
 *
 * @param float $waist دور کمر به سانتی‌متر
 * @param float $height قد به سانتی‌متر
 * @return float نسبت دور کمر به قد
 */
function nutri_coach_calculate_waist_to_height_ratio($waist, $height) {
    if ($waist <= 0 || $height <= 0) {
        return 0;
    }
    
    return round($waist / $height, 2);
}

/**
 * دریافت دسته‌بندی نسبت دور کمر به قد
 *
 * @param float $ratio نسبت دور کمر به قد
 * @return string دسته‌بندی نسبت دور کمر به قد
 */
function nutri_coach_waist_to_height_category($ratio) {
    if ($ratio < 0.43) {
        return __('لاغری شدید', 'nutri-coach-program');
    } elseif ($ratio < 0.5) {
        return __('طبیعی', 'nutri-coach-program');
    } elseif ($ratio < 0.58) {
        return __('اضافه وزن', 'nutri-coach-program');
    } else {
        return __('چاقی', 'nutri-coach-program');
    }
}

/**
 * محاسبه نرخ متابولیسم پایه (BMR) با استفاده از فرمول Mifflin-St Jeor
 *
 * @param float $weight وزن به کیلوگرم
 * @param float $height قد به سانتی‌متر
 * @param int $age سن به سال
 * @param string $gender جنسیت (male یا female)
 * @return float نرخ متابولیسم پایه به کالری
 */
function nutri_coach_calculate_bmr($weight, $height, $age, $gender = 'male') {
    if ($weight <= 0 || $height <= 0 || $age <= 0) {
        return 0;
    }
    
    if ($gender === 'male') {
        $bmr = 10 * $weight + 6.25 * $height - 5 * $age + 5;
    } else {
        $bmr = 10 * $weight + 6.25 * $height - 5 * $age - 161;
    }
    
    return round($bmr);
}

/**
 * محاسبه مصرف کالری روزانه بر اساس BMR و سطح فعالیت
 *
 * @param float $bmr نرخ متابولیسم پایه
 * @param string $activity_level سطح فعالیت
 * @return float مصرف کالری روزانه
 */
function nutri_coach_calculate_tdee($bmr, $activity_level = 'sedentary') {
    $multiplier = 1.2; // کم‌تحرک
    
    switch ($activity_level) {
        case 'lightly_active':
            $multiplier = 1.375; // فعالیت سبک (ورزش 1-3 روز در هفته)
            break;
        case 'moderately_active':
            $multiplier = 1.55; // فعالیت متوسط (ورزش 3-5 روز در هفته)
            break;
        case 'very_active':
            $multiplier = 1.725; // فعالیت زیاد (ورزش 6-7 روز در هفته)
            break;
        case 'extra_active':
            $multiplier = 1.9; // فعالیت خیلی زیاد (ورزش های سنگین روزانه)
            break;
    }
    
    return round($bmr * $multiplier);
}

/**
 * محاسبه کالری مورد نیاز روزانه بر اساس هدف ورزشی
 *
 * @param float $tdee مصرف کالری روزانه
 * @param string $goal هدف ورزشی
 * @return float کالری مورد نیاز روزانه
 */
function nutri_coach_calculate_calorie_goal($tdee, $goal = 'maintenance') {
    switch ($goal) {
        case 'weight_loss':
            return round($tdee * 0.8); // 20% کاهش برای کاهش وزن
        case 'muscle_gain':
            return round($tdee * 1.1); // 10% افزایش برای افزایش عضله
        case 'maintenance':
        default:
            return round($tdee);
    }
}

/**
 * محاسبه نسبت درشت‌مغذی‌ها براساس هدف ورزشی
 *
 * @param float $calories کالری روزانه
 * @param string $goal هدف ورزشی
 * @return array مقادیر درشت‌مغذی‌ها (پروتئین، کربوهیدرات، چربی)
 */
function nutri_coach_calculate_macros($calories, $goal = 'maintenance') {
    $macros = array(
        'protein' => 0,
        'carbs' => 0,
        'fat' => 0
    );
    
    switch ($goal) {
        case 'weight_loss':
            // کاهش وزن: پروتئین بالا، کربوهیدرات کم، چربی متوسط
            $macros['protein'] = round(($calories * 0.4) / 4); // 40% پروتئین (4 کالری بر گرم)
            $macros['fat'] = round(($calories * 0.35) / 9); // 35% چربی (9 کالری بر گرم)
            $macros['carbs'] = round(($calories * 0.25) / 4); // 25% کربوهیدرات (4 کالری بر گرم)
            break;
            
        case 'muscle_gain':
            // افزایش عضله: پروتئین بالا، کربوهیدرات بالا، چربی کم
            $macros['protein'] = round(($calories * 0.3) / 4); // 30% پروتئین
            $macros['fat'] = round(($calories * 0.2) / 9); // 20% چربی
            $macros['carbs'] = round(($calories * 0.5) / 4); // 50% کربوهیدرات
            break;
            
        case 'maintenance':
        default:
            // حفظ وزن: توزیع متعادل
            $macros['protein'] = round(($calories * 0.3) / 4); // 30% پروتئین
            $macros['fat'] = round(($calories * 0.3) / 9); // 30% چربی
            $macros['carbs'] = round(($calories * 0.4) / 4); // 40% کربوهیدرات
            break;
    }
    
    return $macros;
}

/**
 * تبدیل تاریخ میلادی به شمسی
 *
 * @param string $date تاریخ میلادی (Y-m-d)
 * @return string تاریخ شمسی
 */
function nutri_coach_date_to_persian($date) {
    if (function_exists('jdate')) {
        // اگر افزونه jDate نصب باشد
        return jdate('Y/m/d', strtotime($date));
    } else {
        // درغیر این صورت تاریخ میلادی را برگشت می‌دهیم
        return date_i18n(get_option('date_format'), strtotime($date));
    }
}

/**
 * تبدیل آرایه تمرین به متن قابل خواندن
 *
 * @param array $exercise اطلاعات تمرین
 * @return string متن قابل خواندن
 */
function nutri_coach_exercise_to_text($exercise) {
    $text = $exercise['name'];
    
    if (!empty($exercise['sets']) && !empty($exercise['reps'])) {
        $text .= sprintf(__(': %d ست × %s تکرار', 'nutri-coach-program'), $exercise['sets'], $exercise['reps']);
    }
    
    if (!empty($exercise['rest'])) {
        $text .= sprintf(__(', استراحت: %d ثانیه', 'nutri-coach-program'), $exercise['rest']);
    }
    
    return $text;
}

/**
 * تبدیل آرایه وعده غذایی به متن قابل خواندن
 *
 * @param array $meal اطلاعات وعده غذایی
 * @return string متن قابل خواندن
 */
function nutri_coach_meal_to_text($meal) {
    $text = $meal['name'];
    
    if (!empty($meal['time'])) {
        $text .= sprintf(__(' (ساعت %s)', 'nutri-coach-program'), $meal['time']);
    }
    
    if (!empty($meal['foods'])) {
        $text .= ': ';
        $foods = array();
        
        foreach ($meal['foods'] as $food) {
            $foods[] = $food['name'] . ' (' . $food['amount'] . ')';
        }
        
        $text .= implode('، ', $foods);
    }
    
    return $text;
}

/**
 * دریافت نرخ پیشرفت بین دو تاریخ
 *
 * @param int $user_id شناسه کاربر
 * @param string $start_date تاریخ شروع
 * @param string $end_date تاریخ پایان
 * @param string $program_type نوع برنامه (workout یا diet)
 * @return float نرخ پیشرفت (درصد)
 */
function nutri_coach_get_progress_rate($user_id, $start_date, $end_date, $program_type = 'workout') {
    global $wpdb;
    
    $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'progress';
    
    $query = $wpdb->prepare(
        "SELECT AVG(completed_percentage) FROM $table_name 
        WHERE user_id = %d AND program_type = %s AND progress_date BETWEEN %s AND %s",
        $user_id,
        $program_type,
        $start_date,
        $end_date
    );
    
    $progress_rate = $wpdb->get_var($query);
    
    return $progress_rate ? round($progress_rate, 2) : 0;
}

/**
 * تبدیل داده‌های آنتروپومتریک به نمودار
 *
 * @param array $data داده‌های آنتروپومتریک
 * @return array داده‌های نمودار
 */
function nutri_coach_anthropometric_chart_data($data) {
    if (empty($data)) {
        return array(
            'dates' => array(),
            'weight' => array(),
            'bmi' => array(),
            'body_fat' => array()
        );
    }
    
    // مرتب‌سازی داده‌ها بر اساس تاریخ (قدیمی به جدید)
    usort($data, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    
    $chart_data = array(
        'dates' => array(),
        'weight' => array(),
        'bmi' => array(),
        'body_fat' => array()
    );
    
    foreach ($data as $measurement) {
        $chart_data['dates'][] = $measurement['date'];
        $chart_data['weight'][] = floatval($measurement['weight']);
        $chart_data['bmi'][] = floatval($measurement['bmi']);
        $chart_data['body_fat'][] = floatval($measurement['body_fat']);
    }
    
    return $chart_data;
}

/**
 * دریافت جزئیات پیشرفت کاربر
 *
 * @param int $user_id شناسه کاربر
 * @param string $program_type نوع برنامه (workout یا diet)
 * @param int $limit تعداد رکوردها
 * @return array داده‌های پیشرفت
 */
function nutri_coach_get_user_progress_details($user_id, $program_type = 'workout', $limit = 10) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'progress';
    
    $query = $wpdb->prepare(
        "SELECT * FROM $table_name 
        WHERE user_id = %d AND program_type = %s 
        ORDER BY progress_date DESC 
        LIMIT %d",
        $user_id,
        $program_type,
        $limit
    );
    
    $progress_data = $wpdb->get_results($query, ARRAY_A);
    
    return $progress_data ? $progress_data : array();
}

/**
 * تبدیل دقیقه به فرمت ساعت:دقیقه
 *
 * @param int $minutes تعداد دقیقه
 * @return string فرمت ساعت:دقیقه
 */
function nutri_coach_minutes_to_time($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    return sprintf('%02d:%02d', $hours, $mins);
}

/**
 * دریافت اختلاف بین دو اندازه‌گیری
 *
 * @param float $current مقدار فعلی
 * @param float $previous مقدار قبلی
 * @param bool $percentage محاسبه درصد تغییرات
 * @return float میزان تغییرات
 */
function nutri_coach_get_measurement_diff($current, $previous, $percentage = false) {
    if (empty($previous) || $previous == 0) {
        return 0;
    }
    
    $diff = $current - $previous;
    
    if ($percentage) {
        $diff = ($diff / $previous) * 100;
    }
    
    return round($diff, 2);
}

/**
 * دریافت کلاس CSS برای نمایش تغییرات
 *
 * @param float $diff میزان تغییرات
 * @param bool $is_positive آیا تغییرات مثبت خوب است
 * @return string کلاس CSS
 */
function nutri_coach_get_diff_class($diff, $is_positive = true) {
    if ($diff == 0) {
        return 'neutral';
    }
    
    if (($diff > 0 && $is_positive) || ($diff < 0 && !$is_positive)) {
        return 'positive';
    } else {
        return 'negative';
    }
}

/**
 * دریافت متن نمایشی برای تغییرات
 *
 * @param float $diff میزان تغییرات
 * @param bool $percentage آیا درصد نمایش داده شود
 * @param bool $is_positive آیا تغییرات مثبت خوب است
 * @return string متن نمایشی
 */
function nutri_coach_get_diff_text($diff, $percentage = false, $is_positive = true) {
    $sign = ($diff > 0) ? '+' : '';
    $unit = $percentage ? '%' : '';
    
    $class = nutri_coach_get_diff_class($diff, $is_positive);
    $icon = 'neutral';
    
    if ($class === 'positive') {
        $icon = '↑';
    } elseif ($class === 'negative') {
        $icon = '↓';
    } else {
        $icon = '=';
    }
    
    return '<span class="diff-' . $class . '">' . $icon . ' ' . $sign . $diff . $unit . '</span>';
}

/**
 * بررسی آیا یک رشته تاریخ معتبر است
 *
 * @param string $date رشته تاریخ
 * @param string $format فرمت تاریخ
 * @return bool آیا تاریخ معتبر است
 */
function nutri_coach_is_valid_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * تعمیر و تنظیم رشته تاریخ
 *
 * @param string $date رشته تاریخ
 * @return string تاریخ تنظیم شده
 */
function nutri_coach_sanitize_date($date) {
    if (empty($date)) {
        return current_time('Y-m-d');
    }
    
    if (nutri_coach_is_valid_date($date)) {
        return $date;
    }
    
    $timestamp = strtotime($date);
    
    if ($timestamp === false) {
        return current_time('Y-m-d');
    }
    
    return date('Y-m-d', $timestamp);
}

/**
 * دریافت اختلاف روز بین دو تاریخ
 *
 * @param string $date1 تاریخ اول
 * @param string $date2 تاریخ دوم
 * @return int تعداد روزهای اختلاف
 */
function nutri_coach_date_diff_days($date1, $date2) {
    $timestamp1 = strtotime($date1);
    $timestamp2 = strtotime($date2);
    
    if ($timestamp1 === false || $timestamp2 === false) {
        return 0;
    }
    
    $diff = abs($timestamp1 - $timestamp2);
    
    return floor($diff / (60 * 60 * 24));
}

/**
 * دریافت نام روز هفته از تاریخ
 *
 * @param string $date تاریخ
 * @return string نام روز هفته
 */
function nutri_coach_get_day_name($date) {
    $timestamp = strtotime($date);
    
    if ($timestamp === false) {
        return '';
    }
    
    $day_of_week = date('w', $timestamp);
    $day_names = array(
        0 => __('یکشنبه', 'nutri-coach-program'),
        1 => __('دوشنبه', 'nutri-coach-program'),
        2 => __('سه‌شنبه', 'nutri-coach-program'),
        3 => __('چهارشنبه', 'nutri-coach-program'),
        4 => __('پنجشنبه', 'nutri-coach-program'),
        5 => __('جمعه', 'nutri-coach-program'),
        6 => __('شنبه', 'nutri-coach-program')
    );
    
    return $day_names[$day_of_week];
}

/**
 * محاسبه تعداد روزهای باقیمانده از برنامه
 *
 * @param string $start_date تاریخ شروع
 * @param int $duration طول دوره (روز)
 * @return int تعداد روزهای باقیمانده
 */
function nutri_coach_get_remaining_days($start_date, $duration) {
    $start_timestamp = strtotime($start_date);
    $current_timestamp = current_time('timestamp');
    
    if ($start_timestamp === false) {
        return 0;
    }
    
    $end_timestamp = $start_timestamp + ($duration * 24 * 60 * 60);
    
    if ($current_timestamp >= $end_timestamp) {
        return 0;
    }
    
    $remaining_seconds = $end_timestamp - $current_timestamp;
    
    return ceil($remaining_seconds / (24 * 60 * 60));
}

/**
 * تبدیل تاریخ به فرمت قابل خواندن
 *
 * @param string $date تاریخ
 * @return string تاریخ قابل خواندن
 */
function nutri_coach_format_date($date) {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = strtotime($date);
    
    if ($timestamp === false) {
        return $date;
    }
    
    return date_i18n(get_option('date_format'), $timestamp);
}

/**
 * محاسبه نرخ تبعیت از برنامه
 *
 * @param int $user_id شناسه کاربر
 * @param string $start_date تاریخ شروع
 * @param string $end_date تاریخ پایان
 * @return float نرخ تبعیت (درصد)
 */
function nutri_coach_calculate_adherence_rate($user_id, $start_date, $end_date) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'progress';
    
    // محاسبه تعداد روزهای بین دو تاریخ
    $days = nutri_coach_date_diff_days($start_date, $end_date) + 1;
    
    if ($days <= 0) {
        return 0;
    }
    
    // دریافت تعداد روزهایی که پیشرفت ثبت شده است
    $workout_query = $wpdb->prepare(
        "SELECT COUNT(DISTINCT progress_date) 
        FROM $table_name 
        WHERE user_id = %d 
        AND program_type = 'workout' 
        AND progress_date BETWEEN %s AND %s",
        $user_id,
        $start_date,
        $end_date
    );
    
    $workout_days = $wpdb->get_var($workout_query);
    
    $diet_query = $wpdb->prepare(
        "SELECT COUNT(DISTINCT progress_date) 
        FROM $table_name 
        WHERE user_id = %d 
        AND program_type = 'diet' 
        AND progress_date BETWEEN %s AND %s",
        $user_id,
        $start_date,
        $end_date
    );
    
    $diet_days = $wpdb->get_var($diet_query);
    
    // محاسبه نرخ تبعیت
    $workout_adherence = ($workout_days / $days) * 100;
    $diet_adherence = ($diet_days / $days) * 100;
    
    return array(
        'workout' => round($workout_adherence, 2),
        'diet' => round($diet_adherence, 2),
        'total' => round(($workout_adherence + $diet_adherence) / 2, 2)
    );
}

/**
 * محاسبه میانگین پیشرفت روزانه
 *
 * @param int $user_id شناسه کاربر
 * @param string $start_date تاریخ شروع
 * @param string $end_date تاریخ پایان
 * @return array میانگین پیشرفت روزانه
 */
function nutri_coach_calculate_average_daily_progress($user_id, $start_date, $end_date) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'progress';
    
    $workout_query = $wpdb->prepare(
        "SELECT AVG(completed_percentage) 
        FROM $table_name 
        WHERE user_id = %d 
        AND program_type = 'workout' 
        AND progress_date BETWEEN %s AND %s",
        $user_id,
        $start_date,
        $end_date
    );
    
    $workout_avg = $wpdb->get_var($workout_query);
    
    $diet_query = $wpdb->prepare(
        "SELECT AVG(completed_percentage) 
        FROM $table_name 
        WHERE user_id = %d 
        AND program_type = 'diet' 
        AND progress_date BETWEEN %s AND %s",
        $user_id,
        $start_date,
        $end_date
    );
    
    $diet_avg = $wpdb->get_var($diet_query);
    
    return array(
        'workout' => round($workout_avg ? $workout_avg : 0, 2),
        'diet' => round($diet_avg ? $diet_avg : 0, 2),
        'total' => round((($workout_avg ? $workout_avg : 0) + ($diet_avg ? $diet_avg : 0)) / 2, 2)
    );
}

/**
 * محاسبه روند پیشرفت
 *
 * @param int $user_id شناسه کاربر
 * @param string $program_type نوع برنامه
 * @param int $days تعداد روزهای اخیر
 * @return float روند پیشرفت (درصد تغییر)
 */
function nutri_coach_calculate_progress_trend($user_id, $program_type = 'workout', $days = 7) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'progress';
    $current_date = current_time('Y-m-d');
    $past_date = date('Y-m-d', strtotime("-$days days", strtotime($current_date)));
    
    // دریافت پیشرفت اخیر
    $recent_query = $wpdb->prepare(
        "SELECT AVG(completed_percentage) 
        FROM $table_name 
        WHERE user_id = %d 
        AND program_type = %s 
        AND progress_date BETWEEN %s AND %s",
        $user_id,
        $program_type,
        date('Y-m-d', strtotime("-" . ($days / 2) . " days", strtotime($current_date))),
        $current_date
    );
    
    $recent_avg = $wpdb->get_var($recent_query);
    
    // دریافت پیشرفت قبلی
    $previous_query = $wpdb->prepare(
        "SELECT AVG(completed_percentage) 
        FROM $table_name 
        WHERE user_id = %d 
        AND program_type = %s 
        AND progress_date BETWEEN %s AND %s",
        $user_id,
        $program_type,
        $past_date,
        date('Y-m-d', strtotime("-" . ($days / 2 + 1) . " days", strtotime($current_date)))
    );
    
    $previous_avg = $wpdb->get_var($previous_query);
    
    // محاسبه روند
    if (!$previous_avg || $previous_avg == 0) {
        return 0;
    }
    
    $trend = (($recent_avg ? $recent_avg : 0) - ($previous_avg ? $previous_avg : 0)) / $previous_avg * 100;
    
    return round($trend, 2);
}