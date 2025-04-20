<?php
/**
 * کلاس مدیریت برنامه تمرینی
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * کلاس مدیریت برنامه تمرینی
 */
class Nutri_Coach_Workout_Program {
    /**
     * ایجاد برنامه تمرینی جدید
     *
     * @param array $args پارامترهای برنامه تمرینی
     * @return array برنامه تمرینی
     */
    public function create_program($args = array()) {
        $defaults = array(
            'days' => array(),
        );
        
        $program = wp_parse_args($args, $defaults);
        
        // اطمینان از وجود حداقل یک روز
        if (empty($program['days'])) {
            $program['days'] = array(
                array(
                    'day' => __('روز ۱', 'nutri-coach-program'),
                    'exercises' => array(),
                ),
            );
        }
        
        return $program;
    }
    
    /**
     * افزودن روز به برنامه تمرینی
     *
     * @param array $program برنامه تمرینی
     * @param string $day_name نام روز
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function add_day($program, $day_name) {
        if (!isset($program['days'])) {
            $program['days'] = array();
        }
        
        $program['days'][] = array(
            'day' => $day_name,
            'exercises' => array(),
        );
        
        return $program;
    }
    
    /**
     * افزودن تمرین به روز خاص
     *
     * @param array $program برنامه تمرینی
     * @param int $day_index شماره روز
     * @param array $exercise اطلاعات تمرین
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function add_exercise($program, $day_index, $exercise) {
        if (!isset($program['days'][$day_index])) {
            return $program;
        }
        
        // اطمینان از وجود آی‌دی یکتا
        if (empty($exercise['id'])) {
            $exercise['id'] = uniqid('ex_');
        }
        
        // اضافه کردن تمرین به روز مورد نظر
        $program['days'][$day_index]['exercises'][] = $exercise;
        
        return $program;
    }
    
    /**
     * حذف تمرین از برنامه
     *
     * @param array $program برنامه تمرینی
     * @param int $day_index شماره روز
     * @param int $exercise_index شماره تمرین
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function remove_exercise($program, $day_index, $exercise_index) {
        if (!isset($program['days'][$day_index]['exercises'][$exercise_index])) {
            return $program;
        }
        
        // حذف تمرین
        array_splice($program['days'][$day_index]['exercises'], $exercise_index, 1);
        
        return $program;
    }
    
    /**
     * ویرایش تمرین
     *
     * @param array $program برنامه تمرینی
     * @param int $day_index شماره روز
     * @param int $exercise_index شماره تمرین
     * @param array $exercise اطلاعات جدید تمرین
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function update_exercise($program, $day_index, $exercise_index, $exercise) {
        if (!isset($program['days'][$day_index]['exercises'][$exercise_index])) {
            return $program;
        }
        
        // حفظ آی‌دی اصلی
        $exercise['id'] = $program['days'][$day_index]['exercises'][$exercise_index]['id'];
        
        // به‌روزرسانی تمرین
        $program['days'][$day_index]['exercises'][$exercise_index] = $exercise;
        
        return $program;
    }
    
    /**
     * حذف روز از برنامه
     *
     * @param array $program برنامه تمرینی
     * @param int $day_index شماره روز
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function remove_day($program, $day_index) {
        if (!isset($program['days'][$day_index])) {
            return $program;
        }
        
        // حذف روز
        array_splice($program['days'], $day_index, 1);
        
        return $program;
    }
    
    /**
     * ویرایش نام روز
     *
     * @param array $program برنامه تمرینی
     * @param int $day_index شماره روز
     * @param string $day_name نام جدید روز
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function update_day_name($program, $day_index, $day_name) {
        if (!isset($program['days'][$day_index])) {
            return $program;
        }
        
        // به‌روزرسانی نام روز
        $program['days'][$day_index]['day'] = $day_name;
        
        return $program;
    }
    
    /**
     * مرتب‌سازی تمرین‌های یک روز
     *
     * @param array $program برنامه تمرینی
     * @param int $day_index شماره روز
     * @param array $exercise_order ترتیب جدید تمرین‌ها
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function reorder_exercises($program, $day_index, $exercise_order) {
        if (!isset($program['days'][$day_index])) {
            return $program;
        }
        
        $exercises = $program['days'][$day_index]['exercises'];
        $new_exercises = array();
        
        foreach ($exercise_order as $index) {
            if (isset($exercises[$index])) {
                $new_exercises[] = $exercises[$index];
            }
        }
        
        if (count($new_exercises) === count($exercises)) {
            $program['days'][$day_index]['exercises'] = $new_exercises;
        }
        
        return $program;
    }
    
    /**
     * مرتب‌سازی روزهای برنامه
     *
     * @param array $program برنامه تمرینی
     * @param array $day_order ترتیب جدید روزها
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function reorder_days($program, $day_order) {
        $days = $program['days'];
        $new_days = array();
        
        foreach ($day_order as $index) {
            if (isset($days[$index])) {
                $new_days[] = $days[$index];
            }
        }
        
        if (count($new_days) === count($days)) {
            $program['days'] = $new_days;
        }
        
        return $program;
    }
    
    /**
     * اضافه کردن توضیحات به تمرین
     *
     * @param array $program برنامه تمرینی
     * @param int $day_index شماره روز
     * @param int $exercise_index شماره تمرین
     * @param string $description توضیحات تمرین
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function add_exercise_description($program, $day_index, $exercise_index, $description) {
        if (!isset($program['days'][$day_index]['exercises'][$exercise_index])) {
            return $program;
        }
        
        // اضافه کردن توضیحات به تمرین
        $program['days'][$day_index]['exercises'][$exercise_index]['description'] = $description;
        
        return $program;
    }
    
    /**
     * اضافه کردن ویدیو به تمرین
     *
     * @param array $program برنامه تمرینی
     * @param int $day_index شماره روز
     * @param int $exercise_index شماره تمرین
     * @param string $video_url آدرس ویدیو
     * @return array برنامه تمرینی به‌روزرسانی شده
     */
    public function add_exercise_video($program, $day_index, $exercise_index, $video_url) {
        if (!isset($program['days'][$day_index]['exercises'][$exercise_index])) {
            return $program;
        }
        
        // اضافه کردن ویدیو به تمرین
        $program['days'][$day_index]['exercises'][$exercise_index]['video_url'] = esc_url($video_url);
        
        return $program;
    }
    
    /**
     * ایجاد برنامه تمرینی بر اساس قالب پیش‌فرض
     *
     * @param string $template نام قالب پیش‌فرض
     * @return array برنامه تمرینی
     */
    public function create_from_template($template) {
        $program = array();
        
        switch ($template) {
            case 'beginner_full_body':
                $program = $this->beginner_full_body_template();
                break;
                
            case 'intermediate_split':
                $program = $this->intermediate_split_template();
                break;
                
            case 'advanced_ppl':
                $program = $this->advanced_ppl_template();
                break;
                
            default:
                $program = $this->create_program();
                break;
        }
        
        return $program;
    }
    
    /**
     * قالب تمرینی تمام بدن برای مبتدیان
     *
     * @return array برنامه تمرینی
     */
    private function beginner_full_body_template() {
        $program = $this->create_program();
        
        // روز اول: تمام بدن
        $day_index = 0;
        $program = $this->add_day($program, __('شنبه - تمام بدن', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'squat',
            'name' => __('اسکات', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('پاها به اندازه عرض شانه باز، زانوها در راستای پا، پشت صاف، پایین بروید تا ران‌ها موازی زمین شوند.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'bench_press',
            'name' => __('پرس سینه', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('روی نیمکت دراز بکشید، بازوها را باز کنید، وزنه را تا نزدیکی قفسه سینه پایین بیاورید و سپس بالا ببرید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'row',
            'name' => __('زیر بغل', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('کمر را صاف نگه دارید، زانوها کمی خم، وزنه را به طرف شکم بکشید و آرنج‌ها را از بدن دور کنید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'shoulder_press',
            'name' => __('پرس شانه', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('دمبل‌ها را در کنار شانه‌ها نگه دارید، سپس به بالای سر فشار دهید تا بازوها کاملاً صاف شوند.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'plank',
            'name' => __('پلانک', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '30 ثانیه',
            'rest' => 60,
            'description' => __('روی ساعد و انگشتان پا، بدن را در یک خط مستقیم نگه دارید، شکم را سفت کنید.', 'nutri-coach-program'),
        ));
        
        // روز دوم: استراحت
        $program = $this->add_day($program, __('یکشنبه - استراحت', 'nutri-coach-program'));
        
        // روز سوم: تمام بدن
        $day_index = 2;
        $program = $this->add_day($program, __('دوشنبه - تمام بدن', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'deadlift',
            'name' => __('ددلیفت', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('پاها به اندازه عرض شانه، پشت صاف، وزنه را با دست‌های کشیده در جلوی ساق پا بگیرید و با فشار پاها بلند شوید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'push_up',
            'name' => __('شنا', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-15',
            'rest' => 60,
            'description' => __('دست‌ها کمی بیشتر از عرض شانه‌ها باز، بدن را در یک خط صاف نگه دارید، تا نزدیک زمین پایین بیایید و سپس بالا بروید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'lat_pulldown',
            'name' => __('زیر بغل قرقره', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('میله را از بالا بگیرید، آن را به سمت قفسه سینه پایین بکشید، شانه‌ها را عقب نگه دارید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'bicep_curl',
            'name' => __('جلو بازو', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('آرنج را کنار بدن نگه دارید، وزنه را به سمت شانه بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'tricep_extension',
            'name' => __('پشت بازو', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('دمبل را با دو دست بالای سر نگه دارید، آرنج را خم کنید تا وزنه پشت سر برود و سپس صاف کنید.', 'nutri-coach-program'),
        ));
        
        // روز چهارم: استراحت
        $program = $this->add_day($program, __('سه‌شنبه - استراحت', 'nutri-coach-program'));
        
        // روز پنجم: تمام بدن
        $day_index = 4;
        $program = $this->add_day($program, __('چهارشنبه - تمام بدن', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'leg_press',
            'name' => __('پرس پا', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('پاها را روی صفحه دستگاه قرار دهید، زانوها را خم کنید تا نزدیک قفسه سینه شوند، سپس پاها را صاف کنید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'incline_press',
            'name' => __('پرس سینه شیب‌دار', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('روی نیمکت شیب‌دار بنشینید، وزنه را به سمت بالای قفسه سینه پایین بیاورید و سپس بالا ببرید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'pull_up',
            'name' => __('بارفیکس', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => 'تا حد توان',
            'rest' => 90,
            'description' => __('میله را از بالا بگیرید، خود را بالا بکشید تا چانه بالای میله قرار گیرد.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'lateral_raise',
            'name' => __('نشر جانب', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('دمبل‌ها را در کنار بدن نگه دارید، آرنج را کمی خم کنید و دمبل‌ها را تا ارتفاع شانه بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'crunches',
            'name' => __('کرانچ شکم', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '15-20',
            'rest' => 60,
            'description' => __('روی زمین دراز بکشید، زانوها را خم کنید، دست‌ها را روی سینه یا کنار سر قرار دهید و بالاتنه را بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        return $program;
    }
    
    /**
     * قالب تمرینی اسپلیت برای افراد میانی
     *
     * @return array برنامه تمرینی
     */
    private function intermediate_split_template() {
        $program = $this->create_program();
        
        // روز اول: سینه و جلو بازو
        $day_index = 0;
        $program = $this->add_day($program, __('شنبه - سینه و جلو بازو', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'bench_press',
            'name' => __('پرس سینه هالتر', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 90,
            'description' => __('روی نیمکت دراز بکشید، بازوها را باز کنید، هالتر را تا نزدیکی قفسه سینه پایین بیاورید و سپس بالا ببرید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'incline_db_press',
            'name' => __('پرس سینه شیب‌دار با دمبل', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('روی نیمکت شیب‌دار بنشینید، دمبل‌ها را کنار سینه قرار دهید و به سمت بالا فشار دهید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'chest_fly',
            'name' => __('قفسه سینه با دمبل', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('روی نیمکت دراز بکشید، دمبل‌ها را با دست‌های کمی خمیده باز کنید و سپس به هم نزدیک کنید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'barbell_curl',
            'name' => __('جلو بازو هالتر', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('هالتر را با دست‌های باز بگیرید، آرنج را کنار بدن نگه دارید و وزنه را بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'hammer_curl',
            'name' => __('جلو بازو چکشی', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('دمبل‌ها را با حالت چکشی (انگشت شست بالا) بگیرید و بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        // روز دوم: پا و شکم
        $day_index = 1;
        $program = $this->add_day($program, __('یکشنبه - پا و شکم', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'squat',
            'name' => __('اسکات هالتر', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('هالتر را روی شانه‌ها قرار دهید، پاها به اندازه عرض شانه باز، زانوها را خم کنید تا ران‌ها موازی زمین شوند.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'leg_press',
            'name' => __('پرس پا', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('پاها را روی صفحه دستگاه قرار دهید، زانوها را خم کنید تا نزدیک قفسه سینه شوند، سپس پاها را صاف کنید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'leg_extension',
            'name' => __('جلو پا ماشین', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('روی دستگاه بنشینید، پاها را از زانو صاف کنید تا بالا بیایند.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'leg_curl',
            'name' => __('پشت پا ماشین', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('روی دستگاه بخوابید، پاها را از زانو خم کنید تا پاشنه به سمت باسن بیاید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'calf_raise',
            'name' => __('ساق پا', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '15-20',
            'rest' => 60,
            'description' => __('روی لبه پله یا دستگاه بایستید، پاشنه پا را پایین ببرید و سپس تا حد امکان بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'hanging_leg_raise',
            'name' => __('بالا آوردن پا آویزان', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('از میله آویزان شوید، پاها را تا زاویه 90 درجه بالا بیاورید و سپس به آرامی پایین بیاورید.', 'nutri-coach-program'),
        ));
        
        // روز سوم: استراحت
        $program = $this->add_day($program, __('دوشنبه - استراحت', 'nutri-coach-program'));
        
        // روز چهارم: پشت و پشت بازو
        $day_index = 3;
        $program = $this->add_day($program, __('سه‌شنبه - پشت و پشت بازو', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'deadlift',
            'name' => __('ددلیفت', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '6-8',
            'rest' => 120,
            'description' => __('پاها به اندازه عرض شانه، پشت صاف، وزنه را با دست‌های کشیده در جلوی ساق پا بگیرید و با فشار پاها بلند شوید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'pull_up',
            'name' => __('بارفیکس', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '8-10',
            'rest' => 90,
            'description' => __('میله را از بالا بگیرید، خود را بالا بکشید تا چانه بالای میله قرار گیرد.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'barbell_row',
            'name' => __('زیر بغل خم هالتر', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('از کمر خم شوید، پشت صاف، هالتر را به طرف شکم بکشید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'seated_cable_row',
            'name' => __('زیر بغل نشسته با کابل', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('روی دستگاه بنشینید، زانوها کمی خم، پشت صاف، دسته را به طرف شکم بکشید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'tricep_pushdown',
            'name' => __('پشت بازو کششی', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('مقابل دستگاه کابل بایستید، دسته را بگیرید و به سمت پایین فشار دهید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'overhead_extension',
            'name' => __('پشت بازو بالای سر', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('دمبل را با دو دست بالای سر نگه دارید، آرنج را خم کنید تا وزنه پشت سر برود و سپس صاف کنید.', 'nutri-coach-program'),
        ));
        
        // روز پنجم: شانه
        $day_index = 4;
        $program = $this->add_day($program, __('چهارشنبه - شانه', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'shoulder_press',
            'name' => __('پرس شانه هالتر', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 90,
            'description' => __('هالتر را جلوی شانه قرار دهید و به بالای سر فشار دهید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'lateral_raise',
            'name' => __('نشر جانب', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('دمبل‌ها را در کنار بدن نگه دارید، آرنج را کمی خم کنید و دمبل‌ها را تا ارتفاع شانه بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'front_raise',
            'name' => __('نشر جلو', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('دمبل‌ها را جلوی ران‌ها نگه دارید، دست‌ها را صاف جلو بیاورید تا به ارتفاع شانه برسند.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'rear_delt_fly',
            'name' => __('نشر عقب', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('روی نیمکت دراز بکشید یا خم شوید، دمبل‌ها را با آرنج کمی خم به کنار باز کنید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'shrugs',
            'name' => __('شراگ', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('دمبل یا هالتر را کنار بدن نگه دارید، شانه‌ها را بالا بیاورید و سپس پایین بیاورید.', 'nutri-coach-program'),
        ));
        
        return $program;
    }
    
    /**
     * قالب تمرینی پوش/پول/لگز برای افراد پیشرفته
     *
     * @return array برنامه تمرینی
     */
    private function advanced_ppl_template() {
        $program = $this->create_program();
        
        // روز اول: پوش (سینه، شانه، پشت بازو)
        $day_index = 0;
        $program = $this->add_day($program, __('شنبه - پوش (سینه، شانه، پشت بازو)', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'bench_press',
            'name' => __('پرس سینه هالتر', 'nutri-coach-program'),
            'sets' => 5,
            'reps' => '5',
            'rest' => 180,
            'description' => __('روی نیمکت دراز بکشید، بازوها را باز کنید، هالتر را تا نزدیکی قفسه سینه پایین بیاورید و سپس بالا ببرید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'incline_bench_press',
            'name' => __('پرس سینه شیب‌دار', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('روی نیمکت شیب‌دار بنشینید، هالتر را تا نزدیکی بالای قفسه سینه پایین بیاورید و سپس بالا ببرید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'weighted_dips',
            'name' => __('دیپ وزنه‌دار', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('وزنه را به کمربند وصل کنید، بین دو میله قرار بگیرید، بدن را پایین بیاورید تا آرنج‌ها 90 درجه خم شوند و سپس بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'shoulder_press',
            'name' => __('پرس سرشانه', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('هالتر یا دمبل‌ها را در سطح شانه نگه دارید و به بالای سر فشار دهید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'lateral_raises',
            'name' => __('نشر جانب', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('دمبل‌ها را در کنار بدن نگه دارید، آرنج را کمی خم کنید و دمبل‌ها را تا ارتفاع شانه بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'tricep_pushdown',
            'name' => __('پشت بازو کششی', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('مقابل دستگاه کابل بایستید، دسته را بگیرید و به سمت پایین فشار دهید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'skull_crushers',
            'name' => __('اسکال کراشر', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('روی نیمکت دراز بکشید، هالتر را با آرنج‌های صاف بالای سر نگه دارید، آرنج‌ها را خم کنید تا هالتر نزدیک پیشانی برسد، سپس بالا ببرید.', 'nutri-coach-program'),
        ));
        
        // روز دوم: پول (پشت، جلو بازو)
        $day_index = 1;
        $program = $this->add_day($program, __('یکشنبه - پول (پشت، جلو بازو)', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'deadlift',
            'name' => __('ددلیفت', 'nutri-coach-program'),
            'sets' => 5,
            'reps' => '5',
            'rest' => 180,
            'description' => __('پاها به اندازه عرض شانه، پشت صاف، وزنه را با دست‌های کشیده در جلوی ساق پا بگیرید و با فشار پاها بلند شوید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'pull_ups',
            'name' => __('بارفیکس', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('میله را از بالا بگیرید، خود را بالا بکشید تا چانه بالای میله قرار گیرد.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'barbell_row',
            'name' => __('زیر بغل خم هالتر', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('از کمر خم شوید، پشت صاف، هالتر را به طرف شکم بکشید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'cable_row',
            'name' => __('زیر بغل کابلی', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('روی دستگاه بنشینید، زانوها کمی خم، پشت صاف، دسته را به طرف شکم بکشید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'face_pull',
            'name' => __('فیس پول', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('مقابل دستگاه کابل بایستید، طناب را به سمت صورت بکشید، آرنج‌ها را بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'barbell_curl',
            'name' => __('جلو بازو هالتر', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('هالتر را با دست‌های باز بگیرید، آرنج را کنار بدن نگه دارید و وزنه را بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'incline_curl',
            'name' => __('جلو بازو شیب‌دار', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('روی نیمکت شیب‌دار بنشینید، دمبل‌ها را با آرنج کشیده نگه دارید و به سمت شانه بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        // روز سوم: پا و شکم
        $day_index = 2;
        $program = $this->add_day($program, __('دوشنبه - پا و شکم', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'squat',
            'name' => __('اسکات هالتر', 'nutri-coach-program'),
            'sets' => 5,
            'reps' => '5',
            'rest' => 180,
            'description' => __('هالتر را روی شانه‌ها قرار دهید، پاها به اندازه عرض شانه باز، زانوها را خم کنید تا ران‌ها موازی زمین شوند.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'leg_press',
            'name' => __('پرس پا', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('پاها را روی صفحه دستگاه قرار دهید، زانوها را خم کنید تا نزدیک قفسه سینه شوند، سپس پاها را صاف کنید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'romanian_deadlift',
            'name' => __('ددلیفت رومانیایی', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('با پاهای صاف یا کمی خم، از کمر خم شوید و هالتر را جلوی پا پایین ببرید، پشت صاف نگه دارید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'leg_extension',
            'name' => __('جلو پا ماشین', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('روی دستگاه بنشینید، پاها را از زانو صاف کنید تا بالا بیایند.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'leg_curl',
            'name' => __('پشت پا ماشین', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('روی دستگاه بخوابید، پاها را از زانو خم کنید تا پاشنه به سمت باسن بیاید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'calf_raise',
            'name' => __('ساق پا', 'nutri-coach-program'),
            'sets' => 5,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('روی لبه پله یا دستگاه بایستید، پاشنه پا را پایین ببرید و سپس تا حد امکان بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'ab_rollout',
            'name' => __('رول شکم', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-15',
            'rest' => 60,
            'description' => __('روی زانوها قرار بگیرید، چرخ شکم را روی زمین با دست‌ها بگیرید و به جلو حرکت دهید تا بدن کشیده شود، سپس به حالت اول برگردید.', 'nutri-coach-program'),
        ));
        
        // روز چهارم: استراحت
        $program = $this->add_day($program, __('سه‌شنبه - استراحت', 'nutri-coach-program'));
        
        // روز پنجم: پوش (سینه، شانه، پشت بازو)
        $day_index = 4;
        $program = $this->add_day($program, __('چهارشنبه - پوش (سینه، شانه، پشت بازو)', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'incline_bench_press',
            'name' => __('پرس سینه شیب‌دار هالتر', 'nutri-coach-program'),
            'sets' => 5,
            'reps' => '5',
            'rest' => 180,
            'description' => __('روی نیمکت شیب‌دار بنشینید، هالتر را تا نزدیکی بالای قفسه سینه پایین بیاورید و سپس بالا ببرید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'flat_dumbbell_press',
            'name' => __('پرس سینه با دمبل', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('روی نیمکت صاف دراز بکشید، دمبل‌ها را در کنار سینه قرار داده و به بالا فشار دهید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'chest_fly',
            'name' => __('قفسه سینه با دمبل', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('روی نیمکت دراز بکشید، دمبل‌ها را با دست‌های کمی خمیده باز کنید و سپس به هم نزدیک کنید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'arnold_press',
            'name' => __('پرس آرنولد', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('دمبل‌ها را جلوی شانه با کف دست به سمت صورت نگه دارید، همزمان با بالا بردن، دست‌ها را بچرخانید تا کف دست‌ها به جلو باشد.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'front_raises',
            'name' => __('نشر جلو', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('دمبل‌ها را جلوی ران‌ها نگه دارید، دست‌ها را صاف جلو بیاورید تا به ارتفاع شانه برسند.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'close_grip_bench',
            'name' => __('پرس سینه دست جمع', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '8-10',
            'rest' => 90,
            'description' => __('روی نیمکت دراز بکشید، هالتر را با دست‌های نزدیک به هم بگیرید، پایین بیاورید و سپس بالا ببرید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'overhead_extension',
            'name' => __('پشت بازو بالای سر', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('دمبل را با دو دست بالای سر نگه دارید، آرنج را خم کنید تا وزنه پشت سر برود و سپس صاف کنید.', 'nutri-coach-program'),
        ));
        
        // روز ششم: پول (پشت، جلو بازو)
        $day_index = 5;
        $program = $this->add_day($program, __('پنجشنبه - پول (پشت، جلو بازو)', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'weighted_pull_ups',
            'name' => __('بارفیکس وزنه‌دار', 'nutri-coach-program'),
            'sets' => 5,
            'reps' => '5',
            'rest' => 180,
            'description' => __('وزنه را به کمربند وصل کنید، میله را از بالا بگیرید، خود را بالا بکشید تا چانه بالای میله قرار گیرد.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'pendlay_row',
            'name' => __('زیر بغل پندلی', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('از کمر خم شوید تا بدن تقریباً موازی زمین باشد، هالتر را با حرکت انفجاری به سمت قفسه سینه بکشید و سپس به زمین برگردانید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'lat_pulldown',
            'name' => __('زیر بغل قرقره', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('میله را از بالا بگیرید، آن را به سمت قفسه سینه پایین بکشید، شانه‌ها را عقب نگه دارید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'seated_cable_row',
            'name' => __('زیر بغل نشسته با کابل', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('روی دستگاه بنشینید، زانوها کمی خم، پشت صاف، دسته را به طرف شکم بکشید.', 'nutri-coach-program'),
        ));
        
      $program = $this->add_exercise($program, $day_index, array(
            'id' => 'rear_delt_fly',
            'name' => __('نشر عقب', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('روی نیمکت دراز بکشید یا خم شوید، دمبل‌ها را با آرنج کمی خم به کنار باز کنید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'ez_bar_curl',
            'name' => __('جلو بازو هالتر EZ', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '8-10',
            'rest' => 90,
            'description' => __('هالتر EZ را با دست‌ها بگیرید، آرنج‌ها را ثابت نگه دارید و وزنه را بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'hammer_curl',
            'name' => __('جلو بازو چکشی', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('دمبل‌ها را با حالت چکشی (انگشت شست بالا) بگیرید و بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        // روز هفتم: پا و شکم
        $day_index = 6;
        $program = $this->add_day($program, __('جمعه - پا و شکم', 'nutri-coach-program'));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'front_squat',
            'name' => __('اسکات جلو', 'nutri-coach-program'),
            'sets' => 5,
            'reps' => '5',
            'rest' => 180,
            'description' => __('هالتر را روی ترقوه و جلوی شانه‌ها قرار دهید، آرنج‌ها را بالا نگه دارید، پاها به اندازه عرض شانه باز، زانوها را خم کنید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'bulgarian_split_squat',
            'name' => __('اسکات بلغاری', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '8-10',
            'rest' => 120,
            'description' => __('یک پا را روی نیمکت قرار دهید، پای جلو را تا زاویه 90 درجه خم کنید و سپس بالا بیایید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'walking_lunges',
            'name' => __('لانگز متحرک', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 90,
            'description' => __('با یک پا به جلو قدم بردارید، زانو را خم کنید تا به زاویه 90 درجه برسد، سپس با پای دیگر همین کار را انجام دهید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'leg_curl',
            'name' => __('پشت پا ماشین', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-12',
            'rest' => 60,
            'description' => __('روی دستگاه بخوابید، پاها را از زانو خم کنید تا پاشنه به سمت باسن بیاید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'seated_calf_raise',
            'name' => __('ساق پا نشسته', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('روی دستگاه بنشینید، پاشنه‌ها را پایین ببرید و سپس تا حد امکان بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'standing_calf_raise',
            'name' => __('ساق پا ایستاده', 'nutri-coach-program'),
            'sets' => 4,
            'reps' => '12-15',
            'rest' => 60,
            'description' => __('روی لبه پله یا دستگاه بایستید، پاشنه پا را پایین ببرید و سپس تا حد امکان بالا بیاورید.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'hanging_leg_raises',
            'name' => __('بالا آوردن پا آویزان', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '10-15',
            'rest' => 60,
            'description' => __('از میله آویزان شوید، پاها را صاف بالا بیاورید تا با بدن زاویه 90 درجه بسازند.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_exercise($program, $day_index, array(
            'id' => 'russian_twist',
            'name' => __('چرخش روسی', 'nutri-coach-program'),
            'sets' => 3,
            'reps' => '15-20',
            'rest' => 60,
            'description' => __('بنشینید و پاها را از زمین بلند کنید، بالاتنه را به چپ و راست بچرخانید.', 'nutri-coach-program'),
        ));
        
        return $program;
    }
    
    /**
     * تبدیل آرایه برنامه تمرینی به HTML
     *
     * @param array $program برنامه تمرینی
     * @return string خروجی HTML
     */
    public function render_program_html($program) {
        if (empty($program) || empty($program['days'])) {
            return '<p>' . __('برنامه تمرینی تعریف نشده است.', 'nutri-coach-program') . '</p>';
        }
        
        $html = '<div class="nutri-coach-workout-program">';
        
        foreach ($program['days'] as $day_index => $day) {
            $html .= '<div class="nutri-coach-workout-day">';
            $html .= '<h3 class="day-title">' . esc_html($day['day']) . '</h3>';
            
            if (empty($day['exercises'])) {
                $html .= '<p class="no-exercises">' . __('استراحت', 'nutri-coach-program') . '</p>';
            } else {
                $html .= '<div class="exercises-list">';
                
                foreach ($day['exercises'] as $exercise_index => $exercise) {
                    $html .= '<div class="exercise-item">';
                    $html .= '<h4 class="exercise-name">' . esc_html($exercise['name']) . '</h4>';
                    
                    $html .= '<div class="exercise-details">';
                    if (isset($exercise['sets']) && isset($exercise['reps'])) {
                        $html .= '<span class="sets-reps">' . sprintf(__('%d ست × %s تکرار', 'nutri-coach-program'), $exercise['sets'], $exercise['reps']) . '</span>';
                    }
                    
                    if (isset($exercise['rest'])) {
                        $html .= '<span class="rest-time">' . sprintf(__('استراحت: %d ثانیه', 'nutri-coach-program'), $exercise['rest']) . '</span>';
                    }
                    $html .= '</div>';
                    
                    if (!empty($exercise['description'])) {
                        $html .= '<div class="exercise-description">' . esc_html($exercise['description']) . '</div>';
                    }
                    
                    if (!empty($exercise['video_url'])) {
                        $html .= '<div class="exercise-video">';
                        $html .= '<a href="' . esc_url($exercise['video_url']) . '" target="_blank">' . __('مشاهده ویدیو', 'nutri-coach-program') . '</a>';
                        $html .= '</div>';
                    }
                    
                    $html .= '</div>'; // .exercise-item
                }
                
                $html .= '</div>'; // .exercises-list
            }
            
            $html .= '</div>'; // .nutri-coach-workout-day
        }
        
        $html .= '</div>'; // .nutri-coach-workout-program
        
        return $html;
    }
    
    /**
     * ذخیره برنامه تمرینی کاربر
     *
     * @param int $user_id شناسه کاربر
     * @param array $program برنامه تمرینی
     * @return bool نتیجه عملیات
     */
    public function save_user_program($user_id, $program) {
        if (!$user_id) {
            return false;
        }
        
        return update_user_meta($user_id, 'nutri_coach_workout_program', $program);
    }
    
    /**
     * دریافت برنامه تمرینی کاربر
     *
     * @param int $user_id شناسه کاربر
     * @return array برنامه تمرینی
     */
    public function get_user_program($user_id) {
        if (!$user_id) {
            return array();
        }
        
        $program = get_user_meta($user_id, 'nutri_coach_workout_program', true);
        
        if (!is_array($program) || empty($program)) {
            $program = $this->create_program();
        }
        
        return $program;
    }
    
    /**
     * افزودن یک قانون تمرینی جدید
     *
     * @param array $rule_data داده‌های قانون تمرینی
     * @return int|bool شناسه قانون یا false در صورت شکست
     */
    public function add_rule($rule_data) {
        global $wpdb;
        
        // بررسی وجود جدول قوانین
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        // تنظیم داده‌های پیش‌فرض
        $defaults = array(
            'title' => '',
            'status' => 'active',
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
            'intensity' => '',
            'workout_program' => '',
            'date_created' => current_time('mysql'),
        );
        
        // ترکیب داده‌های ورودی با پیش‌فرض‌ها
        $rule_data = wp_parse_args($rule_data, $defaults);
        
        // اطمینان از وجود عنوان
        if (empty($rule_data['title'])) {
            if (!empty($rule_data['gender']) && !empty($rule_data['fitness_goal'])) {
                // ایجاد عنوان خودکار
                $gender_text = $rule_data['gender'] === 'male' ? __('مردان', 'nutri-coach-program') : __('زنان', 'nutri-coach-program');
                $goal_text = '';
                
                switch ($rule_data['fitness_goal']) {
                    case 'weight_loss':
                        $goal_text = __('کاهش وزن', 'nutri-coach-program');
                        break;
                    case 'muscle_gain':
                        $goal_text = __('افزایش عضله', 'nutri-coach-program');
                        break;
                    case 'maintenance':
                        $goal_text = __('حفظ وزن', 'nutri-coach-program');
                        break;
                    case 'overall_health':
                        $goal_text = __('سلامتی عمومی', 'nutri-coach-program');
                        break;
                }
                
                $rule_data['title'] = sprintf(__('برنامه %s برای %s', 'nutri-coach-program'), $goal_text, $gender_text);
            } else {
                $rule_data['title'] = __('برنامه تمرینی جدید', 'nutri-coach-program');
            }
        }
        
        // درج در دیتابیس
        $result = $wpdb->insert(
            $table_name,
            $rule_data,
            array(
                '%s', // title
                '%s', // status
                '%s', // gender
                '%d', // age_min
                '%d', // age_max
                '%f', // waist_min
                '%f', // waist_max
                '%f', // hip_min
                '%f', // hip_max
                '%f', // neck_min
                '%f', // neck_max
                '%s', // fitness_goal
                '%s', // intensity
                '%s', // workout_program
                '%s', // date_created
            )
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * بروزرسانی یک قانون تمرینی
     *
     * @param int $rule_id شناسه قانون
     * @param array $rule_data داده‌های جدید قانون
     * @return bool نتیجه عملیات
     */
    public function update_rule($rule_id, $rule_data) {
        global $wpdb;
        
        if (!$rule_id) {
            return false;
        }
        
        // بررسی وجود جدول قوانین
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        // به‌روزرسانی در دیتابیس
        $result = $wpdb->update(
            $table_name,
            $rule_data,
            array('id' => $rule_id),
            array(
                '%s', // title
                '%s', // status
                '%s', // gender
                '%d', // age_min
                '%d', // age_max
                '%f', // waist_min
                '%f', // waist_max
                '%f', // hip_min
                '%f', // hip_max
                '%f', // neck_min
                '%f', // neck_max
                '%s', // fitness_goal
                '%s', // intensity
                '%s', // workout_program
                '%s', // date_updated
            ),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * دریافت یک قانون تمرینی
     *
     * @param int $rule_id شناسه قانون
     * @return array|bool داده‌های قانون یا false
     */
    public function get_rule($rule_id) {
        global $wpdb;
        
        if (!$rule_id) {
            return false;
        }
        
        // بررسی وجود جدول قوانین
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        // دریافت از دیتابیس
        $rule = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $rule_id
            ),
            ARRAY_A
        );
        
        return $rule;
    }
    
    /**
     * دریافت لیست قوانین تمرینی
     *
     * @param int $offset تعداد رکوردهای رد شده
     * @param int $limit تعداد رکوردهای درخواستی
     * @param string $gender فیلتر جنسیت
     * @param int $age_min فیلتر حداقل سن
     * @param int $age_max فیلتر حداکثر سن
     * @param string $fitness_goal فیلتر هدف ورزشی
     * @return array لیست قوانین
     */
    public function get_rules($offset = 0, $limit = 20, $gender = '', $age_min = '', $age_max = '', $fitness_goal = '') {
        global $wpdb;
        
        // بررسی وجود جدول قوانین
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        // ایجاد شرط‌های SQL
        $where = array();
        $query_params = array();
        
        if (!empty($gender)) {
            $where[] = 'gender = %s';
            $query_params[] = $gender;
        }
        
        if (!empty($age_min)) {
            $where[] = 'age_max >= %d';
            $query_params[] = $age_min;
        }
        
        if (!empty($age_max)) {
            $where[] = 'age_min <= %d';
            $query_params[] = $age_max;
        }
        
        if (!empty($fitness_goal)) {
            $where[] = 'fitness_goal = %s';
            $query_params[] = $fitness_goal;
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
        $query = "SELECT * FROM $table_name $where_clause ORDER BY id DESC LIMIT %d OFFSET %d";
        $rules = $wpdb->get_results($wpdb->prepare($query, $query_params), ARRAY_A);
        
        return $rules;
    }
    
    /**
     * دریافت تعداد کل قوانین تمرینی
     *
     * @param string $gender فیلتر جنسیت
     * @param int $age_min فیلتر حداقل سن
     * @param int $age_max فیلتر حداکثر سن
     * @param string $fitness_goal فیلتر هدف ورزشی
     * @return int تعداد کل قوانین
     */
    public function get_total_rules($gender = '', $age_min = '', $age_max = '', $fitness_goal = '') {
        global $wpdb;
        
        // بررسی وجود جدول قوانین
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        // ایجاد شرط‌های SQL
        $where = array();
        $query_params = array();
        
        if (!empty($gender)) {
            $where[] = 'gender = %s';
            $query_params[] = $gender;
        }
        
        if (!empty($age_min)) {
            $where[] = 'age_max >= %d';
            $query_params[] = $age_min;
        }
        
        if (!empty($age_max)) {
            $where[] = 'age_min <= %d';
            $query_params[] = $age_max;
        }
        
        if (!empty($fitness_goal)) {
            $where[] = 'fitness_goal = %s';
            $query_params[] = $fitness_goal;
        }
        
        // ترکیب شرط‌ها
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
        }
        
        // ایجاد و اجرای کوئری
        $query = "SELECT COUNT(*) FROM $table_name $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($query, $query_params));
        
        return $total;
    }
    
    /**
     * حذف یک قانون تمرینی
     *
     * @param int $rule_id شناسه قانون
     * @return bool نتیجه عملیات
     */
    public function delete_rule($rule_id) {
        global $wpdb;
        
        if (!$rule_id) {
            return false;
        }
        
        // بررسی وجود جدول قوانین
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        // حذف از دیتابیس
        $result = $wpdb->delete(
            $table_name,
            array('id' => $rule_id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * یافتن قوانین مناسب برای کاربر
     *
     * @param int $user_id شناسه کاربر
     * @return array لیست قوانین مناسب
     */
    public function find_matching_rules($user_id) {
        global $wpdb;
        
        if (!$user_id) {
            return array();
        }
        
        // دریافت اطلاعات کاربر
        $gender = get_user_meta($user_id, 'gender', true);
        $age = get_user_meta($user_id, 'age', true);
        $waist = get_user_meta($user_id, 'waist', true);
        $hip = get_user_meta($user_id, 'hip', true);
        $neck = get_user_meta($user_id, 'neck', true);
        $fitness_goal = get_user_meta($user_id, 'fitness_goal', true);
        
        // بررسی وجود جدول قوانین
        $table_name = $wpdb->prefix . NUTRI_COACH_PROGRAM_PREFIX . 'rules';
        
        // ایجاد شرط‌های SQL
        $where = array();
        $query_params = array();
        
        // قوانین فعال
        $where[] = 'status = %s';
        $query_params[] = 'active';
        
        // جنسیت
        if (!empty($gender)) {
            $where[] = '(gender = %s OR gender = %s)';
            $query_params[] = $gender;
            $query_params[] = '';
        }
        
        // سن
        if (!empty($age)) {
            $where[] = '(age_min <= %d AND age_max >= %d)';
            $query_params[] = $age;
            $query_params[] = $age;
        }
        
        // دور کمر
        if (!empty($waist)) {
            $where[] = '(waist_min <= %f AND waist_max >= %f)';
            $query_params[] = $waist;
            $query_params[] = $waist;
        }
        
        // دور باسن
        if (!empty($hip)) {
            $where[] = '(hip_min <= %f AND hip_max >= %f)';
            $query_params[] = $hip;
            $query_params[] = $hip;
        }
        
        // دور گردن
        if (!empty($neck)) {
            $where[] = '(neck_min <= %f AND neck_max >= %f)';
            $query_params[] = $neck;
            $query_params[] = $neck;
        }
        
        // هدف ورزشی
        if (!empty($fitness_goal)) {
            $where[] = '(fitness_goal = %s OR fitness_goal = %s)';
            $query_params[] = $fitness_goal;
            $query_params[] = '';
        }
        
        // ترکیب شرط‌ها
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
        }
        
        // ایجاد و اجرای کوئری
        $query = "SELECT * FROM $table_name $where_clause ORDER BY id DESC";
        $rules = $wpdb->get_results($wpdb->prepare($query, $query_params), ARRAY_A);
        
        return $rules;
    }
    
    /**
     * بارگذاری قالب تمرینی برای کاربر
     *
     * @param int $user_id شناسه کاربر
     * @param string $template نام قالب
     * @return bool نتیجه عملیات
     */
    public function load_workout_template($user_id, $template) {
        if (!$user_id || empty($template)) {
            return false;
        }
        
        // ایجاد برنامه بر اساس قالب
        $program = $this->create_from_template($template);
        
        // ذخیره برنامه برای کاربر
        return $this->save_user_program($user_id, $program);
    }
}