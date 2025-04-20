<?php
/**
 * کلاس مدیریت برنامه غذایی
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * کلاس مدیریت برنامه غذایی
 */
class Nutri_Coach_Diet_Program {
    /**
     * ایجاد برنامه غذایی جدید
     *
     * @param array $args پارامترهای برنامه غذایی
     * @return array برنامه غذایی
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
                    'meals' => array(),
                ),
            );
        }
        
        return $program;
    }
    
    /**
     * افزودن روز به برنامه غذایی
     *
     * @param array $program برنامه غذایی
     * @param string $day_name نام روز
     * @return array برنامه غذایی به‌روزرسانی شده
     */
    public function add_day($program, $day_name) {
        if (!isset($program['days'])) {
            $program['days'] = array();
        }
        
        $program['days'][] = array(
            'day' => $day_name,
            'meals' => array(),
        );
        
        return $program;
    }
    
    /**
     * افزودن وعده غذایی به روز خاص
     *
     * @param array $program برنامه غذایی
     * @param int $day_index شماره روز
     * @param array $meal اطلاعات وعده غذایی
     * @return array برنامه غذایی به‌روزرسانی شده
     */
    public function add_meal($program, $day_index, $meal) {
        if (!isset($program['days'][$day_index])) {
            return $program;
        }
        
        // اطمینان از وجود آی‌دی یکتا
        if (empty($meal['id'])) {
            $meal['id'] = uniqid('meal_');
        }
        
        // اضافه کردن وعده غذایی به روز مورد نظر
        $program['days'][$day_index]['meals'][] = $meal;
        
        return $program;
    }
    
    /**
     * حذف وعده غذایی از برنامه
     *
     * @param array $program برنامه غذایی
     * @param int $day_index شماره روز
     * @param int $meal_index شماره وعده
     * @return array برنامه غذایی به‌روزرسانی شده
     */
    public function remove_meal($program, $day_index, $meal_index) {
        if (!isset($program['days'][$day_index]['meals'][$meal_index])) {
            return $program;
        }
        
        // حذف وعده غذایی
        array_splice($program['days'][$day_index]['meals'], $meal_index, 1);
        
        return $program;
    }
    
    /**
     * ویرایش وعده غذایی
     *
     * @param array $program برنامه غذایی
     * @param int $day_index شماره روز
     * @param int $meal_index شماره وعده
     * @param array $meal اطلاعات جدید وعده
     * @return array برنامه غذایی به‌روزرسانی شده
     */
    public function update_meal($program, $day_index, $meal_index, $meal) {
        if (!isset($program['days'][$day_index]['meals'][$meal_index])) {
            return $program;
        }
        
        // حفظ آی‌دی اصلی
        $meal['id'] = $program['days'][$day_index]['meals'][$meal_index]['id'];
        
        // به‌روزرسانی وعده غذایی
        $program['days'][$day_index]['meals'][$meal_index] = $meal;
        
        return $program;
    }
    
    /**
     * حذف روز از برنامه
     *
     * @param array $program برنامه غذایی
     * @param int $day_index شماره روز
     * @return array برنامه غذایی به‌روزرسانی شده
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
     * @param array $program برنامه غذایی
     * @param int $day_index شماره روز
     * @param string $day_name نام جدید روز
     * @return array برنامه غذایی به‌روزرسانی شده
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
     * مرتب‌سازی وعده‌های یک روز
     *
     * @param array $program برنامه غذایی
     * @param int $day_index شماره روز
     * @param array $meal_order ترتیب جدید وعده‌ها
     * @return array برنامه غذایی به‌روزرسانی شده
     */
    public function reorder_meals($program, $day_index, $meal_order) {
        if (!isset($program['days'][$day_index])) {
            return $program;
        }
        
        $meals = $program['days'][$day_index]['meals'];
        $new_meals = array();
        
        foreach ($meal_order as $index) {
            if (isset($meals[$index])) {
                $new_meals[] = $meals[$index];
            }
        }
        
        if (count($new_meals) === count($meals)) {
            $program['days'][$day_index]['meals'] = $new_meals;
        }
        
        return $program;
    }
    
    /**
     * مرتب‌سازی روزهای برنامه
     *
     * @param array $program برنامه غذایی
     * @param array $day_order ترتیب جدید روزها
     * @return array برنامه غذایی به‌روزرسانی شده
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
     * اضافه کردن توضیحات به وعده غذایی
     *
     * @param array $program برنامه غذایی
     * @param int $day_index شماره روز
     * @param int $meal_index شماره وعده
     * @param string $description توضیحات وعده
     * @return array برنامه غذایی به‌روزرسانی شده
     */
    public function add_meal_description($program, $day_index, $meal_index, $description) {
        if (!isset($program['days'][$day_index]['meals'][$meal_index])) {
            return $program;
        }
        
        // اضافه کردن توضیحات به وعده
        $program['days'][$day_index]['meals'][$meal_index]['description'] = $description;
        
        return $program;
    }
    
    /**
     * اضافه کردن تصویر به وعده غذایی
     *
     * @param array $program برنامه غذایی
     * @param int $day_index شماره روز
     * @param int $meal_index شماره وعده
     * @param string $image_url آدرس تصویر
     * @return array برنامه غذایی به‌روزرسانی شده
     */
    public function add_meal_image($program, $day_index, $meal_index, $image_url) {
        if (!isset($program['days'][$day_index]['meals'][$meal_index])) {
            return $program;
        }
        
        // اضافه کردن تصویر به وعده
        $program['days'][$day_index]['meals'][$meal_index]['image_url'] = esc_url($image_url);
        
        return $program;
    }
    
    /**
     * ایجاد برنامه غذایی بر اساس قالب پیش‌فرض
     *
     * @param string $template نام قالب پیش‌فرض
     * @return array برنامه غذایی
     */
    public function create_from_template($template) {
        $program = array();
        
        switch ($template) {
            case 'balanced':
                $program = $this->balanced_template();
                break;
                
            case 'high_protein':
                $program = $this->high_protein_template();
                break;
                
            case 'keto':
                $program = $this->keto_template();
                break;
                
            case 'vegetarian':
                $program = $this->vegetarian_template();
                break;
                
            default:
                $program = $this->create_program();
                break;
        }
        
        return $program;
    }
    
    /**
     * قالب غذایی متعادل
     *
     * @return array برنامه غذایی
     */
    private function balanced_template() {
        $program = $this->create_program();
        
        // روز اول
        $day_index = 0;
        $program = $this->add_day($program, __('شنبه', 'nutri-coach-program'));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'breakfast_1',
            'name' => __('صبحانه', 'nutri-coach-program'),
            'time' => '8:00',
            'foods' => array(
                array(
                    'name' => __('املت سبزیجات', 'nutri-coach-program'),
                    'amount' => __('2 تخم‌مرغ + 1/2 فنجان سبزیجات', 'nutri-coach-program'),
                    'calories' => 300,
                    'macros' => array(
                        'protein' => 16,
                        'carbs' => 8,
                        'fat' => 22
                    )
                ),
                array(
                    'name' => __('نان سبوس‌دار', 'nutri-coach-program'),
                    'amount' => __('1 تکه', 'nutri-coach-program'),
                    'calories' => 80,
                    'macros' => array(
                        'protein' => 3,
                        'carbs' => 15,
                        'fat' => 1
                    )
                ),
                array(
                    'name' => __('میوه فصل', 'nutri-coach-program'),
                    'amount' => __('1 عدد متوسط', 'nutri-coach-program'),
                    'calories' => 80,
                    'macros' => array(
                        'protein' => 0,
                        'carbs' => 20,
                        'fat' => 0
                    )
                )
            ),
            'description' => __('وعده صبحانه متعادل با ترکیبی از پروتئین، کربوهیدرات پیچیده و میوه.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'lunch_1',
            'name' => __('نهار', 'nutri-coach-program'),
            'time' => '13:00',
            'foods' => array(
                array(
                    'name' => __('مرغ کبابی', 'nutri-coach-program'),
                    'amount' => __('120 گرم', 'nutri-coach-program'),
                    'calories' => 200,
                    'macros' => array(
                        'protein' => 35,
                        'carbs' => 0,
                        'fat' => 6
                    )
                ),
                array(
                    'name' => __('برنج قهوه‌ای', 'nutri-coach-program'),
                    'amount' => __('1 فنجان پخته', 'nutri-coach-program'),
                    'calories' => 220,
                    'macros' => array(
                        'protein' => 5,
                        'carbs' => 45,
                        'fat' => 2
                    )
                ),
                array(
                    'name' => __('سالاد سبزیجات', 'nutri-coach-program'),
                    'amount' => __('2 فنجان', 'nutri-coach-program'),
                    'calories' => 50,
                    'macros' => array(
                        'protein' => 2,
                        'carbs' => 10,
                        'fat' => 0
                    )
                ),
                array(
                    'name' => __('روغن زیتون', 'nutri-coach-program'),
                    'amount' => __('1 قاشق غذاخوری', 'nutri-coach-program'),
                    'calories' => 120,
                    'macros' => array(
                        'protein' => 0,
                        'carbs' => 0,
                        'fat' => 14
                    )
                )
            ),
            'description' => __('نهار متعادل با پروتئین کم‌چرب، کربوهیدرات مرکب و سبزیجات.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'snack_1',
            'name' => __('میان‌وعده', 'nutri-coach-program'),
            'time' => '17:00',
            'foods' => array(
                array(
                    'name' => __('ماست کم‌چرب', 'nutri-coach-program'),
                    'amount' => __('1 فنجان', 'nutri-coach-program'),
                    'calories' => 120,
                    'macros' => array(
                        'protein' => 12,
                        'carbs' => 14,
                        'fat' => 3
                    )
                ),
                array(
                    'name' => __('میوه خشک و آجیل', 'nutri-coach-program'),
                    'amount' => __('30 گرم', 'nutri-coach-program'),
                    'calories' => 150,
                    'macros' => array(
                        'protein' => 5,
                        'carbs' => 12,
                        'fat' => 10
                    )
                )
            ),
            'description' => __('میان‌وعده غنی از پروتئین و چربی‌های سالم.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'dinner_1',
            'name' => __('شام', 'nutri-coach-program'),
            'time' => '20:00',
            'foods' => array(
                array(
                    'name' => __('ماهی قزل‌آلا', 'nutri-coach-program'),
                    'amount' => __('150 گرم', 'nutri-coach-program'),
                    'calories' => 250,
                    'macros' => array(
                        'protein' => 30,
                        'carbs' => 0,
                        'fat' => 14
                    )
                ),
                array(
                    'name' => __('سیب‌زمینی پخته', 'nutri-coach-program'),
                    'amount' => __('1 عدد متوسط', 'nutri-coach-program'),
                    'calories' => 150,
                    'macros' => array(
                        'protein' => 3,
                        'carbs' => 33,
                        'fat' => 0
                    )
                ),
                array(
                    'name' => __('سبزیجات بخارپز', 'nutri-coach-program'),
                    'amount' => __('1 فنجان', 'nutri-coach-program'),
                    'calories' => 50,
                    'macros' => array(
                        'protein' => 2,
                        'carbs' => 10,
                        'fat' => 0
                    )
                )
            ),
            'description' => __('شام سبک با پروتئین دریایی، کربوهیدرات و سبزیجات.', 'nutri-coach-program'),
        ));
        
        // روز دوم
        $day_index = 1;
        $program = $this->add_day($program, __('یکشنبه', 'nutri-coach-program'));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'breakfast_2',
            'name' => __('صبحانه', 'nutri-coach-program'),
            'time' => '8:00',
            'foods' => array(
                array(
                    'name' => __('جو دوسر', 'nutri-coach-program'),
                    'amount' => __('1/2 فنجان خشک', 'nutri-coach-program'),
                    'calories' => 150,
                    'macros' => array(
                        'protein' => 5,
                        'carbs' => 27,
                        'fat' => 3
                    )
                ),
                array(
                    'name' => __('شیر کم‌چرب', 'nutri-coach-program'),
                    'amount' => __('1 فنجان', 'nutri-coach-program'),
                    'calories' => 100,
                    'macros' => array(
                        'protein' => 8,
                        'carbs' => 12,
                        'fat' => 2
                    )
                ),
                array(
                    'name' => __('توت‌ها', 'nutri-coach-program'),
                    'amount' => __('1/2 فنجان', 'nutri-coach-program'),
                    'calories' => 40,
                    'macros' => array(
                        'protein' => 0,
                        'carbs' => 10,
                        'fat' => 0
                    )
                ),
                array(
                    'name' => __('کره بادام زمینی', 'nutri-coach-program'),
                    'amount' => __('1 قاشق غذاخوری', 'nutri-coach-program'),
                    'calories' => 90,
                    'macros' => array(
                        'protein' => 4,
                        'carbs' => 3,
                        'fat' => 8
                    )
                )
            ),
            'description' => __('صبحانه سرشار از فیبر، پروتئین و چربی‌های سالم.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'lunch_2',
            'name' => __('نهار', 'nutri-coach-program'),
            'time' => '13:00',
            'foods' => array(
                array(
                    'name' => __('سالاد مرغ و سبزیجات', 'nutri-coach-program'),
                    'amount' => __('350 گرم', 'nutri-coach-program'),
                    'calories' => 350,
                    'macros' => array(
                        'protein' => 35,
                        'carbs' => 15,
                        'fat' => 15
                    )
                ),
                array(
                    'name' => __('نان پیتا سبوس‌دار', 'nutri-coach-program'),
                    'amount' => __('1 عدد', 'nutri-coach-program'),
                    'calories' => 150,
                    'macros' => array(
                        'protein' => 6,
                        'carbs' => 30,
                        'fat' => 2
                    )
                ),
                array(
                    'name' => __('میوه فصل', 'nutri-coach-program'),
                    'amount' => __('1 عدد متوسط', 'nutri-coach-program'),
                    'calories' => 80,
                    'macros' => array(
                        'protein' => 0,
                        'carbs' => 20,
                        'fat' => 0
                    )
                )
            ),
            'description' => __('نهار سبک و مغذی با سالاد پروتئینی، نان سبوس‌دار و میوه.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'snack_2',
            'name' => __('میان‌وعده', 'nutri-coach-program'),
            'time' => '17:00',
            'foods' => array(
                array(
                    'name' => __('هویج و خیار', 'nutri-coach-program'),
                    'amount' => __('2 عدد هویج متوسط + 1 خیار', 'nutri-coach-program'),
                    'calories' => 70,
                    'macros' => array(
                        'protein' => 2,
                        'carbs' => 15,
                        'fat' => 0
                    )
                ),
               array(
                    'name' => __('حمص', 'nutri-coach-program'),
                    'amount' => __('2 قاشق غذاخوری', 'nutri-coach-program'),
                    'calories' => 80,
                    'macros' => array(
                        'protein' => 4,
                        'carbs' => 9,
                        'fat' => 4
                    )
                )
            ),
            'description' => __('میان‌وعده سبک حاوی فیبر و پروتئین گیاهی.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'dinner_2',
            'name' => __('شام', 'nutri-coach-program'),
            'time' => '20:00',
            'foods' => array(
                array(
                    'name' => __('خوراک لوبیا و سبزیجات', 'nutri-coach-program'),
                    'amount' => __('350 گرم', 'nutri-coach-program'),
                    'calories' => 300,
                    'macros' => array(
                        'protein' => 15,
                        'carbs' => 45,
                        'fat' => 8
                    )
                ),
                array(
                    'name' => __('پنیر کم‌چرب', 'nutri-coach-program'),
                    'amount' => __('30 گرم', 'nutri-coach-program'),
                    'calories' => 80,
                    'macros' => array(
                        'protein' => 14,
                        'carbs' => 0,
                        'fat' => 3
                    )
                )
            ),
            'description' => __('شام گیاهی سرشار از فیبر و پروتئین.', 'nutri-coach-program'),
        ));
        
        // روزهای دیگر را به همین ترتیب اضافه کنید...
        
        return $program;
    }
    
    /**
     * قالب غذایی پرپروتئین
     *
     * @return array برنامه غذایی
     */
    private function high_protein_template() {
        $program = $this->create_program();
        
        // روز اول
        $day_index = 0;
        $program = $this->add_day($program, __('شنبه', 'nutri-coach-program'));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'breakfast_1',
            'name' => __('صبحانه', 'nutri-coach-program'),
            'time' => '7:30',
            'foods' => array(
                array(
                    'name' => __('تخم‌مرغ', 'nutri-coach-program'),
                    'amount' => __('4 عدد (2 زرده و 4 سفیده)', 'nutri-coach-program'),
                    'calories' => 240,
                    'macros' => array(
                        'protein' => 28,
                        'carbs' => 2,
                        'fat' => 14
                    )
                ),
                array(
                    'name' => __('نان جو', 'nutri-coach-program'),
                    'amount' => __('1 تکه', 'nutri-coach-program'),
                    'calories' => 80,
                    'macros' => array(
                        'protein' => 4,
                        'carbs' => 15,
                        'fat' => 1
                    )
                )
            ),
            'description' => __('صبحانه سرشار از پروتئین با تخم‌مرغ و کربوهیدرات مرکب.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'snack_1',
            'name' => __('میان‌وعده صبح', 'nutri-coach-program'),
            'time' => '10:30',
            'foods' => array(
                array(
                    'name' => __('پروتئین وی', 'nutri-coach-program'),
                    'amount' => __('1 اسکوپ', 'nutri-coach-program'),
                    'calories' => 120,
                    'macros' => array(
                        'protein' => 25,
                        'carbs' => 2,
                        'fat' => 1
                    )
                ),
                array(
                    'name' => __('موز', 'nutri-coach-program'),
                    'amount' => __('1 عدد', 'nutri-coach-program'),
                    'calories' => 100,
                    'macros' => array(
                        'protein' => 1,
                        'carbs' => 25,
                        'fat' => 0
                    )
                )
            ),
            'description' => __('میان‌وعده پروتئینی سریع و مغذی.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'lunch_1',
            'name' => __('نهار', 'nutri-coach-program'),
            'time' => '13:30',
            'foods' => array(
                array(
                    'name' => __('سینه مرغ', 'nutri-coach-program'),
                    'amount' => __('180 گرم', 'nutri-coach-program'),
                    'calories' => 300,
                    'macros' => array(
                        'protein' => 54,
                        'carbs' => 0,
                        'fat' => 7
                    )
                ),
                array(
                    'name' => __('برنج قهوه‌ای', 'nutri-coach-program'),
                    'amount' => __('1 فنجان پخته', 'nutri-coach-program'),
                    'calories' => 220,
                    'macros' => array(
                        'protein' => 5,
                        'carbs' => 45,
                        'fat' => 2
                    )
                ),
                array(
                    'name' => __('سبزیجات', 'nutri-coach-program'),
                    'amount' => __('1.5 فنجان', 'nutri-coach-program'),
                    'calories' => 75,
                    'macros' => array(
                        'protein' => 3,
                        'carbs' => 15,
                        'fat' => 0
                    )
                )
            ),
            'description' => __('نهار سرشار از پروتئین با کربوهیدرات مرکب و سبزیجات.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'snack_2',
            'name' => __('میان‌وعده عصر', 'nutri-coach-program'),
            'time' => '16:30',
            'foods' => array(
                array(
                    'name' => __('ماست یونانی کم‌چرب', 'nutri-coach-program'),
                    'amount' => __('1 فنجان', 'nutri-coach-program'),
                    'calories' => 130,
                    'macros' => array(
                        'protein' => 22,
                        'carbs' => 8,
                        'fat' => 0
                    )
                ),
                array(
                    'name' => __('توت‌فرنگی', 'nutri-coach-program'),
                    'amount' => __('1/2 فنجان', 'nutri-coach-program'),
                    'calories' => 25,
                    'macros' => array(
                        'protein' => 0,
                        'carbs' => 6,
                        'fat' => 0
                    )
                ),
                array(
                    'name' => __('بادام', 'nutri-coach-program'),
                    'amount' => __('10 عدد', 'nutri-coach-program'),
                    'calories' => 70,
                    'macros' => array(
                        'protein' => 3,
                        'carbs' => 2,
                        'fat' => 6
                    )
                )
            ),
            'description' => __('میان‌وعده غنی از پروتئین و توت‌های آنتی‌اکسیدانی.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'dinner_1',
            'name' => __('شام', 'nutri-coach-program'),
            'time' => '20:00',
            'foods' => array(
                array(
                    'name' => __('ماهی سالمون', 'nutri-coach-program'),
                    'amount' => __('150 گرم', 'nutri-coach-program'),
                    'calories' => 280,
                    'macros' => array(
                        'protein' => 38,
                        'carbs' => 0,
                        'fat' => 14
                    )
                ),
                array(
                    'name' => __('سیب‌زمینی شیرین', 'nutri-coach-program'),
                    'amount' => __('150 گرم', 'nutri-coach-program'),
                    'calories' => 130,
                    'macros' => array(
                        'protein' => 2,
                        'carbs' => 30,
                        'fat' => 0
                    )
                ),
                array(
                    'name' => __('بروکلی', 'nutri-coach-program'),
                    'amount' => __('1 فنجان', 'nutri-coach-program'),
                    'calories' => 55,
                    'macros' => array(
                        'protein' => 4,
                        'carbs' => 11,
                        'fat' => 0
                    )
                )
            ),
            'description' => __('شام سرشار از اسیدهای چرب امگا-3 و پروتئین با کربوهیدرات مرکب.', 'nutri-coach-program'),
        ));
        
        // روزهای دیگر را به همین ترتیب اضافه کنید...
        
        return $program;
    }
    
    /**
     * قالب غذایی کتوژنیک
     *
     * @return array برنامه غذایی
     */
    private function keto_template() {
        $program = $this->create_program();
        
        // روز اول
        $day_index = 0;
        $program = $this->add_day($program, __('شنبه', 'nutri-coach-program'));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'breakfast_1',
            'name' => __('صبحانه', 'nutri-coach-program'),
            'time' => '8:00',
            'foods' => array(
                array(
                    'name' => __('تخم‌مرغ', 'nutri-coach-program'),
                    'amount' => __('3 عدد', 'nutri-coach-program'),
                    'calories' => 210,
                    'macros' => array(
                        'protein' => 18,
                        'carbs' => 0,
                        'fat' => 15
                    )
                ),
                array(
                    'name' => __('آووکادو', 'nutri-coach-program'),
                    'amount' => __('1/2 عدد', 'nutri-coach-program'),
                    'calories' => 120,
                    'macros' => array(
                        'protein' => 1,
                        'carbs' => 6,
                        'fat' => 11
                    )
                ),
                array(
                    'name' => __('پنیر چدار', 'nutri-coach-program'),
                    'amount' => __('30 گرم', 'nutri-coach-program'),
                    'calories' => 110,
                    'macros' => array(
                        'protein' => 7,
                        'carbs' => 0,
                        'fat' => 9
                    )
                )
            ),
            'description' => __('صبحانه کتوژنیک سرشار از چربی‌های سالم و پروتئین.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'lunch_1',
            'name' => __('نهار', 'nutri-coach-program'),
            'time' => '13:00',
            'foods' => array(
                array(
                    'name' => __('گوشت گوساله', 'nutri-coach-program'),
                    'amount' => __('150 گرم', 'nutri-coach-program'),
                    'calories' => 375,
                    'macros' => array(
                        'protein' => 38,
                        'carbs' => 0,
                        'fat' => 24
                    )
                ),
                array(
                    'name' => __('سالاد سبزیجات کم‌کربوهیدرات', 'nutri-coach-program'),
                    'amount' => __('2 فنجان', 'nutri-coach-program'),
                    'calories' => 50,
                    'macros' => array(
                        'protein' => 2,
                        'carbs' => 6,
                        'fat' => 0
                    )
                ),
                array(
                    'name' => __('روغن زیتون', 'nutri-coach-program'),
                    'amount' => __('2 قاشق غذاخوری', 'nutri-coach-program'),
                    'calories' => 240,
                    'macros' => array(
                        'protein' => 0,
                        'carbs' => 0,
                        'fat' => 28
                    )
                )
            ),
            'description' => __('نهار کتوژنیک با نسبت بالای چربی به پروتئین.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'snack_1',
            'name' => __('میان‌وعده', 'nutri-coach-program'),
            'time' => '16:30',
            'foods' => array(
                array(
                    'name' => __('مغزها (بادام، گردو، فندق)', 'nutri-coach-program'),
                    'amount' => __('30 گرم', 'nutri-coach-program'),
                    'calories' => 180,
                    'macros' => array(
                        'protein' => 6,
                        'carbs' => 5,
                        'fat' => 16
                    )
                ),
                array(
                    'name' => __('پنیر گودا', 'nutri-coach-program'),
                    'amount' => __('20 گرم', 'nutri-coach-program'),
                    'calories' => 80,
                    'macros' => array(
                        'protein' => 5,
                        'carbs' => 0,
                        'fat' => 6
                    )
                )
            ),
            'description' => __('میان‌وعده کتوژنیک سرشار از چربی‌های سالم.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'dinner_1',
            'name' => __('شام', 'nutri-coach-program'),
            'time' => '20:00',
            'foods' => array(
                array(
                    'name' => __('ماهی قزل‌آلا', 'nutri-coach-program'),
                    'amount' => __('180 گرم', 'nutri-coach-program'),
                    'calories' => 330,
                    'macros' => array(
                        'protein' => 36,
                        'carbs' => 0,
                        'fat' => 20
                    )
                ),
                array(
                    'name' => __('گل کلم روغنی', 'nutri-coach-program'),
                    'amount' => __('200 گرم', 'nutri-coach-program'),
                    'calories' => 200,
                    'macros' => array(
                        'protein' => 4,
                        'carbs' => 8,
                        'fat' => 16
                    )
                ),
                array(
                    'name' => __('اسفناج سرخ شده', 'nutri-coach-program'),
                    'amount' => __('1 فنجان', 'nutri-coach-program'),
                    'calories' => 100,
                    'macros' => array(
                        'protein' => 3,
                        'carbs' => 3,
                        'fat' => 8
                    )
                )
            ),
            'description' => __('شام کتوژنیک با ماهی سرشار از امگا-3 و سبزیجات کم‌کربوهیدرات.', 'nutri-coach-program'),
        ));
        
        // روزهای دیگر را به همین ترتیب اضافه کنید...
        
        return $program;
    }
    
    /**
     * قالب غذایی گیاهخواری
     *
     * @return array برنامه غذایی
     */
    private function vegetarian_template() {
        $program = $this->create_program();
        
        // روز اول
        $day_index = 0;
        $program = $this->add_day($program, __('شنبه', 'nutri-coach-program'));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'breakfast_1',
            'name' => __('صبحانه', 'nutri-coach-program'),
            'time' => '8:00',
            'foods' => array(
                array(
                    'name' => __('جو دوسر', 'nutri-coach-program'),
                    'amount' => __('1/2 فنجان خشک', 'nutri-coach-program'),
                    'calories' => 150,
                    'macros' => array(
                        'protein' => 5,
                        'carbs' => 27,
                        'fat' => 3
                    )
                ),
                array(
                    'name' => __('شیر بادام', 'nutri-coach-program'),
                    'amount' => __('1 فنجان', 'nutri-coach-program'),
                    'calories' => 40,
                    'macros' => array(
                        'protein' => 1,
                        'carbs' => 2,
                        'fat' => 3
                    )
                ),
                array(
                    'name' => __('توت‌ها', 'nutri-coach-program'),
                    'amount' => __('1/2 فنجان', 'nutri-coach-program'),
                    'calories' => 40,
                    'macros' => array(
                        'protein' => 0,
                        'carbs' => 10,
                        'fat' => 0
                    )
                ),
                array(
                    'name' => __('بذر چیا', 'nutri-coach-program'),
                    'amount' => __('1 قاشق غذاخوری', 'nutri-coach-program'),
                    'calories' => 60,
                    'macros' => array(
                        'protein' => 2,
                        'carbs' => 5,
                        'fat' => 4
                    )
                )
            ),
            'description' => __('صبحانه گیاهی سرشار از فیبر و مواد مغذی.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'lunch_1',
            'name' => __('نهار', 'nutri-coach-program'),
            'time' => '13:00',
            'foods' => array(
                array(
                    'name' => __('توفو', 'nutri-coach-program'),
                    'amount' => __('150 گرم', 'nutri-coach-program'),
                    'calories' => 180,
                    'macros' => array(
                        'protein' => 18,
                        'carbs' => 3,
                        'fat' => 11
                    )
                ),
                array(
                    'name' => __('کینوا', 'nutri-coach-program'),
                    'amount' => __('1/2 فنجان پخته', 'nutri-coach-program'),
                    'calories' => 110,
                    'macros' => array(
                        'protein' => 4,
                        'carbs' => 20,
                        'fat' => 2
                    )
                ),
                array(
                    'name' => __('سبزیجات سرخ شده', 'nutri-coach-program'),
                    'amount' => __('1.5 فنجان', 'nutri-coach-program'),
                    'calories' => 100,
                    'macros' => array(
                        'protein' => 3,
                        'carbs' => 15,
                        'fat' => 4
                    )
                )
            ),
            'description' => __('نهار گیاهی با پروتئین گیاهی، غلات کامل و سبزیجات.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'snack_1',
            'name' => __('میان‌وعده', 'nutri-coach-program'),
            'time' => '16:30',
            'foods' => array(
                array(
                    'name' => __('حمص', 'nutri-coach-program'),
                    'amount' => __('1/4 فنجان', 'nutri-coach-program'),
                    'calories' => 100,
                    'macros' => array(
                        'protein' => 5,
                        'carbs' => 12,
                        'fat' => 4
                    )
                ),
                array(
                    'name' => __('هویج و کرفس', 'nutri-coach-program'),
                    'amount' => __('1 فنجان', 'nutri-coach-program'),
                    'calories' => 50,
                    'macros' => array(
                        'protein' => 1,
                        'carbs' => 12,
                        'fat' => 0
                    )
                )
            ),
            'description' => __('میان‌وعده گیاهی با پروتئین و فیبر.', 'nutri-coach-program'),
        ));
        
        $program = $this->add_meal($program, $day_index, array(
            'id' => 'dinner_1',
            'name' => __('شام', 'nutri-coach-program'),
            'time' => '20:00',
            'foods' => array(
                array(
                    'name' => __('عدس', 'nutri-coach-program'),
                    'amount' => __('1 فنجان پخته', 'nutri-coach-program'),
                    'calories' => 230,
                    'macros' => array(
                        'protein' => 18,
                        'carbs' => 40,
                        'fat' => 1
                    )
                ),
                array(
                    'name' => __('برنج قهوه‌ای', 'nutri-coach-program'),
                    'amount' => __('1/2 فنجان پخته', 'nutri-coach-program'),
                    'calories' => 110,
                    'macros' => array(
                        'protein' => 2,
                        'carbs' => 22,
                        'fat' => 1
                    )
                ),
                array(
                    'name' => __('کدو سبز بخارپز', 'nutri-coach-program'),
                    'amount' => __('1 فنجان', 'nutri-coach-program'),
                    'calories' => 30,
                    'macros' => array(
                        'protein' => 1,
                        'carbs' => 7,
                        'fat' => 0
                    )
                ),
                array(
                    'name' => __('روغن زیتون', 'nutri-coach-program'),
                    'amount' => __('1 قاشق غذاخوری', 'nutri-coach-program'),
                    'calories' => 120,
                    'macros' => array(
                        'protein' => 0,
                        'carbs' => 0,
                        'fat' => 14
                    )
                )
            ),
            'description' => __('شام گیاهی سرشار از پروتئین، فیبر و چربی‌های سالم.', 'nutri-coach-program'),
        ));
        
        // روزهای دیگر را به همین ترتیب اضافه کنید...
        
        return $program;
    }
    
    /**
     * تبدیل آرایه برنامه غذایی به HTML
     *
     * @param array $program برنامه غذایی
     * @return string خروجی HTML
     */
    public function render_program_html($program) {
        if (empty($program) || empty($program['days'])) {
            return '<p>' . __('برنامه غذایی تعریف نشده است.', 'nutri-coach-program') . '</p>';
        }
        
        $html = '<div class="nutri-coach-diet-program">';
        
        foreach ($program['days'] as $day_index => $day) {
            $html .= '<div class="nutri-coach-diet-day">';
            $html .= '<h3 class="day-title">' . esc_html($day['day']) . '</h3>';
            
            if (empty($day['meals'])) {
                $html .= '<p class="no-meals">' . __('هیچ وعده غذایی تعریف نشده است.', 'nutri-coach-program') . '</p>';
            } else {
                $html .= '<div class="meals-list">';
                
                foreach ($day['meals'] as $meal_index => $meal) {
                    $html .= '<div class="meal-item">';
                    $html .= '<h4 class="meal-name">' . esc_html($meal['name']) . ' <span class="meal-time">(' . esc_html($meal['time']) . ')</span></h4>';
                    
                    if (!empty($meal['foods'])) {
                        $html .= '<div class="foods-list">';
                        
                        $total_calories = 0;
                        $total_macros = array(
                            'protein' => 0,
                            'carbs' => 0,
                            'fat' => 0
                        );
                        
                        foreach ($meal['foods'] as $food) {
                            $html .= '<div class="food-item">';
                            $html .= '<span class="food-name">' . esc_html($food['name']) . '</span>';
                            $html .= '<span class="food-amount">' . esc_html($food['amount']) . '</span>';
                            $html .= '<span class="food-calories">' . esc_html($food['calories']) . ' ' . __('کالری', 'nutri-coach-program') . '</span>';
                            
                            if (!empty($food['macros'])) {
                                $html .= '<span class="food-macros">';
                                $html .= sprintf(
                                    __('پروتئین: %dg, کربوهیدرات: %dg, چربی: %dg', 'nutri-coach-program'),
                                    $food['macros']['protein'],
                                    $food['macros']['carbs'],
                                    $food['macros']['fat']
                                );
                                $html .= '</span>';
                                
                                // محاسبه مجموع
                                $total_calories += $food['calories'];
                                $total_macros['protein'] += $food['macros']['protein'];
                                $total_macros['carbs'] += $food['macros']['carbs'];
                                $total_macros['fat'] += $food['macros']['fat'];
                            }
                            
                            $html .= '</div>'; // .food-item
                        }
                        
                        // نمایش مجموع
                        $html .= '<div class="meal-totals">';
                        $html .= '<strong>' . __('مجموع:', 'nutri-coach-program') . '</strong> ';
                        $html .= '<span class="total-calories">' . $total_calories . ' ' . __('کالری', 'nutri-coach-program') . '</span>, ';
                        $html .= sprintf(
                            __('پروتئین: %dg, کربوهیدرات: %dg, چربی: %dg', 'nutri-coach-program'),
                            $total_macros['protein'],
                            $total_macros['carbs'],
                            $total_macros['fat']
                        );
                        $html .= '</div>'; // .meal-totals
                        
                        $html .= '</div>'; // .foods-list
                    }
if (!empty($meal['foods'])) {
                        $html .= '<div class="foods-list">';
                        
                        $total_calories = 0;
                        $total_macros = array(
                            'protein' => 0,
                            'carbs' => 0,
                            'fat' => 0
                        );
                        
                        foreach ($meal['foods'] as $food) {
                            $html .= '<div class="food-item">';
                            $html .= '<span class="food-name">' . esc_html($food['name']) . '</span>';
                            $html .= '<span class="food-amount">' . esc_html($food['amount']) . '</span>';
                            $html .= '<span class="food-calories">' . esc_html($food['calories']) . ' ' . __('کالری', 'nutri-coach-program') . '</span>';
                            
                            if (!empty($food['macros'])) {
                                $html .= '<span class="food-macros">';
                                $html .= sprintf(
                                    __('پروتئین: %dg, کربوهیدرات: %dg, چربی: %dg', 'nutri-coach-program'),
                                    $food['macros']['protein'],
                                    $food['macros']['carbs'],
                                    $food['macros']['fat']
                                );
                                $html .= '</span>';
                                
                                // محاسبه مجموع
                                $total_calories += $food['calories'];
                                $total_macros['protein'] += $food['macros']['protein'];
                                $total_macros['carbs'] += $food['macros']['carbs'];
                                $total_macros['fat'] += $food['macros']['fat'];
                            }
                            
                            $html .= '</div>'; // .food-item
                        }
                        
                        // نمایش مجموع
                        $html .= '<div class="meal-totals">';
                        $html .= '<strong>' . __('مجموع:', 'nutri-coach-program') . '</strong> ';
                        $html .= '<span class="total-calories">' . $total_calories . ' ' . __('کالری', 'nutri-coach-program') . '</span>, ';
                        $html .= sprintf(
                            __('پروتئین: %dg, کربوهیدرات: %dg, چربی: %dg', 'nutri-coach-program'),
                            $total_macros['protein'],
                            $total_macros['carbs'],
                            $total_macros['fat']
                        );
                        $html .= '</div>'; // .meal-totals
                        
                        $html .= '</div>'; // .foods-list
                    }
                    
                    if (!empty($meal['description'])) {
                        $html .= '<div class="meal-description">' . esc_html($meal['description']) . '</div>';
                    }
                    
                    if (!empty($meal['image_url'])) {
                        $html .= '<div class="meal-image">';
                        $html .= '<img src="' . esc_url($meal['image_url']) . '" alt="' . esc_attr($meal['name']) . '">';
                        $html .= '</div>';
                    }
                    
                    $html .= '</div>'; // .meal-item
                }
                
                $html .= '</div>'; // .meals-list
            }
            
            $html .= '</div>'; // .nutri-coach-diet-day
        }
        
        $html .= '</div>'; // .nutri-coach-diet-program
        
        return $html;
    }
    
    /**
     * ذخیره برنامه غذایی کاربر
     *
     * @param int $user_id شناسه کاربر
     * @param array $program برنامه غذایی
     * @return bool نتیجه عملیات
     */
    public function save_user_program($user_id, $program) {
        if (!$user_id) {
            return false;
        }
        
        return update_user_meta($user_id, 'nutri_coach_diet_program', $program);
    }
    
    /**
     * دریافت برنامه غذایی کاربر
     *
     * @param int $user_id شناسه کاربر
     * @return array برنامه غذایی
     */
    public function get_user_program($user_id) {
        if (!$user_id) {
            return array();
        }
        
        $program = get_user_meta($user_id, 'nutri_coach_diet_program', true);
        
        if (!is_array($program) || empty($program)) {
            $program = $this->create_program();
        }
        
        return $program;
    }
    
    /**
     * محاسبه کالری روزانه براساس اطلاعات کاربر
     *
     * @param int $user_id شناسه کاربر
     * @param string $goal هدف کاربر (کاهش وزن، افزایش عضله، حفظ وزن)
     * @return int کالری روزانه توصیه‌شده
     */
    public function calculate_daily_calories($user_id, $goal = 'maintenance') {
        $weight = floatval(get_user_meta($user_id, 'weight', true));
        $height = floatval(get_user_meta($user_id, 'height', true));
        $age = intval(get_user_meta($user_id, 'age', true));
        $gender = get_user_meta($user_id, 'gender', true);
        $activity_level = get_user_meta($user_id, 'activity_level', true);
        
        if (empty($weight) || empty($height) || empty($age) || empty($gender)) {
            return 0;
        }
        
        // محاسبه نرخ متابولیسم پایه (BMR) با استفاده از فرمول Mifflin-St Jeor
        if ($gender === 'male') {
            $bmr = 10 * $weight + 6.25 * $height - 5 * $age + 5;
        } else {
            $bmr = 10 * $weight + 6.25 * $height - 5 * $age - 161;
        }
        
        // ضریب فعالیت
        $activity_multiplier = 1.2; // کم‌تحرک
        switch ($activity_level) {
            case 'lightly_active':
                $activity_multiplier = 1.375; // فعالیت سبک (ورزش 1-3 روز در هفته)
                break;
            case 'moderately_active':
                $activity_multiplier = 1.55; // فعالیت متوسط (ورزش 3-5 روز در هفته)
                break;
            case 'very_active':
                $activity_multiplier = 1.725; // فعالیت زیاد (ورزش 6-7 روز در هفته)
                break;
            case 'extra_active':
                $activity_multiplier = 1.9; // فعالیت خیلی زیاد (ورزش های سنگین روزانه)
                break;
        }
        
        // تعداد کالری نگهدارنده
        $maintenance_calories = $bmr * $activity_multiplier;
        
        // تنظیم بر اساس هدف
        switch ($goal) {
            case 'weight_loss':
                return round($maintenance_calories * 0.8); // 20% کاهش برای کاهش وزن
            case 'muscle_gain':
                return round($maintenance_calories * 1.1); // 10% افزایش برای افزایش عضله
            case 'maintenance':
            default:
                return round($maintenance_calories);
        }
    }
    
    /**
     * محاسبه مقادیر درشت‌مغذی‌ها براساس کالری و هدف
     *
     * @param int $calories کالری روزانه
     * @param string $goal هدف کاربر
     * @return array مقادیر درشت‌مغذی‌ها (پروتئین، کربوهیدرات، چربی)
     */
    public function calculate_macros($calories, $goal = 'maintenance') {
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
     * بارگذاری قالب غذایی برای کاربر
     *
     * @param int $user_id شناسه کاربر
     * @param string $template نام قالب
     * @return bool نتیجه عملیات
     */
    public function load_diet_template($user_id, $template) {
        if (!$user_id || empty($template)) {
            return false;
        }
        
        // ایجاد برنامه بر اساس قالب
        $program = $this->create_from_template($template);
        
        // ذخیره برنامه برای کاربر
        return $this->save_user_program($user_id, $program);
    }
}