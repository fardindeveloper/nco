<?php
/**
 * قالب نمایش برنامه کاربر
 *
 * @package Nutri_Coach_Program
 */

if (!defined('ABSPATH')) {
    exit;
}

// دریافت پارامترهای شورت‌کد
$user_id = $atts['user_id'];
$type = $atts['type']; // workout, diet, both

// دریافت اطلاعات کاربر
$user_data = apply_filters('nutri_coach_get_user_data', array(), $user_id);

// بررسی آیا کاربر برنامه فعال دارد
if (empty($user_data['has_program']) || !$user_data['has_program']) {
    echo '<div class="nutri-coach-notice">';
    echo '<p>' . __('شما هنوز برنامه فعالی ندارید. لطفاً با مربی خود تماس بگیرید.', 'nutri-coach-program') . '</p>';
    echo '</div>';
    return;
}

$program_id = $user_data['program_id'];

// دریافت داده‌های برنامه تمرینی و غذایی
$workout_program = isset($user_data['workout_program']) ? $user_data['workout_program'] : null;
$diet_program = isset($user_data['diet_program']) ? $user_data['diet_program'] : null;

// بررسی آیا داده‌های مورد نیاز موجود است
if (($type === 'workout' || $type === 'both') && empty($workout_program)) {
    echo '<div class="nutri-coach-notice">';
    echo '<p>' . __('برنامه تمرینی شما هنوز تنظیم نشده است. لطفاً با مربی خود تماس بگیرید.', 'nutri-coach-program') . '</p>';
    echo '</div>';
}

if (($type === 'diet' || $type === 'both') && empty($diet_program)) {
    echo '<div class="nutri-coach-notice">';
    echo '<p>' . __('برنامه غذایی شما هنوز تنظیم نشده است. لطفاً با مربی خود تماس بگیرید.', 'nutri-coach-program') . '</p>';
    echo '</div>';
}

// ایجاد نانس امنیتی برای اجکس
$ajax_nonce = wp_create_nonce('nutri_coach_program_nonce');

?>

<div class="nutri-coach-program-container">
    <input type="hidden" id="program_id" value="<?php echo esc_attr($program_id); ?>">
    <input type="hidden" id="ajax_nonce" value="<?php echo esc_attr($ajax_nonce); ?>">
    
    <?php if (($type === 'workout' || $type === 'both') && !empty($workout_program)) : ?>
        <div class="nutri-coach-workout-program">
            <h2><?php echo __('برنامه تمرینی', 'nutri-coach-program'); ?></h2>
            
            <?php if (isset($workout_program['days']) && is_array($workout_program['days'])) : ?>
                <div class="workout-program-container">
                    <div class="workout-days-tabs">
                        <ul class="tabs-nav">
                            <?php foreach ($workout_program['days'] as $index => $day) : ?>
                                <li <?php echo $index === 0 ? 'class="active"' : ''; ?> data-tab="workout-day-<?php echo esc_attr($index); ?>">
                                    <?php echo esc_html($day['day']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="workout-days-content">
                        <?php foreach ($workout_program['days'] as $index => $day) : ?>
                            <div id="workout-day-<?php echo esc_attr($index); ?>" class="tab-content <?php echo $index === 0 ? 'active' : ''; ?>">
                                <h3><?php echo esc_html($day['day']); ?></h3>
                                
                                <?php if (isset($day['exercises']) && is_array($day['exercises'])) : ?>
                                    <div class="exercise-list">
                                        <?php foreach ($day['exercises'] as $exercise) : ?>
                                            <div class="exercise-item" data-exercise-id="<?php echo esc_attr($exercise['id']); ?>">
                                                <div class="exercise-header">
                                                    <h4><?php echo esc_html($exercise['name']); ?></h4>
                                                    <div class="exercise-toggle"></div>
                                                </div>
                                                
                                                <div class="exercise-details">
                                                    <div class="exercise-info">
                                                        <div class="info-item">
                                                            <span class="info-label"><?php echo __('ست:', 'nutri-coach-program'); ?></span>
                                                            <span class="info-value"><?php echo isset($exercise['sets']) ? esc_html($exercise['sets']) : '-'; ?></span>
                                                        </div>
                                                        
                                                        <div class="info-item">
                                                            <span class="info-label"><?php echo __('تکرار:', 'nutri-coach-program'); ?></span>
                                                            <span class="info-value"><?php echo isset($exercise['reps']) ? esc_html($exercise['reps']) : '-'; ?></span>
                                                        </div>
                                                        
                                                        <div class="info-item">
                                                            <span class="info-label"><?php echo __('استراحت:', 'nutri-coach-program'); ?></span>
                                                            <span class="info-value"><?php echo isset($exercise['rest']) ? esc_html($exercise['rest']) . ' ' . __('ثانیه', 'nutri-coach-program') : '-'; ?></span>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (isset($exercise['description'])) : ?>
                                                        <div class="exercise-description">
                                                            <?php echo wpautop(esc_html($exercise['description'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (isset($exercise['video_url'])) : ?>
                                                        <div class="exercise-video">
                                                            <?php echo wp_oembed_get(esc_url($exercise['video_url'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="exercise-tracker">
                                                        <h5><?php echo __('ثبت پیشرفت', 'nutri-coach-program'); ?></h5>
                                                        
                                                        <div class="tracker-form">
                                                            <div class="form-row">
                                                                <label for="sets-<?php echo esc_attr($exercise['id']); ?>"><?php echo __('ست‌ها:', 'nutri-coach-program'); ?></label>
                                                                <input type="number" id="sets-<?php echo esc_attr($exercise['id']); ?>" min="0" step="1" value="<?php echo isset($exercise['sets']) ? esc_attr($exercise['sets']) : '1'; ?>">
                                                            </div>
                                                            
                                                            <div class="form-row">
                                                                <label for="reps-<?php echo esc_attr($exercise['id']); ?>"><?php echo __('تکرارها:', 'nutri-coach-program'); ?></label>
                                                                <input type="number" id="reps-<?php echo esc_attr($exercise['id']); ?>" min="0" step="1" value="0">
                                                            </div>
                                                            
                                                            <div class="form-row">
                                                                <label for="weight-<?php echo esc_attr($exercise['id']); ?>"><?php echo __('وزنه (کیلوگرم):', 'nutri-coach-program'); ?></label>
                                                                <input type="number" id="weight-<?php echo esc_attr($exercise['id']); ?>" min="0" step="0.5" value="0">
                                                            </div>
                                                            
                                                            <div class="form-row">
                                                                <label for="notes-<?php echo esc_attr($exercise['id']); ?>"><?php echo __('یادداشت:', 'nutri-coach-program'); ?></label>
                                                                <textarea id="notes-<?php echo esc_attr($exercise['id']); ?>"></textarea>
                                                            </div>
                                                            
                                                            <div class="form-row checkbox-row">
                                                                <label class="checkbox-label">
                                                                    <input type="checkbox" id="completed-<?php echo esc_attr($exercise['id']); ?>">
                                                                    <?php echo __('انجام شد', 'nutri-coach-program'); ?>
                                                                </label>
                                                            </div>
                                                            
                                                            <div class="form-actions">
                                                                <button type="button" class="button save-exercise" data-exercise-id="<?php echo esc_attr($exercise['id']); ?>">
                                                                    <?php echo __('ذخیره', 'nutri-coach-program'); ?>
                                                                </button>
                                                                <span class="save-status"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <p><?php echo __('تمرینی برای این روز تعریف نشده است.', 'nutri-coach-program'); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="workout-program-text">
                    <?php echo nl2br(esc_html(json_encode($workout_program, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if (($type === 'diet' || $type === 'both') && !empty($diet_program)) : ?>
        <div class="nutri-coach-diet-program">
            <h2><?php echo __('برنامه غذایی', 'nutri-coach-program'); ?></h2>
            
            <?php if (isset($diet_program['days']) && is_array($diet_program['days'])) : ?>
                <div class="diet-program-container">
                    <div class="diet-days-tabs">
                        <ul class="tabs-nav">
                            <?php foreach ($diet_program['days'] as $index => $day) : ?>
                                <li <?php echo $index === 0 ? 'class="active"' : ''; ?> data-tab="diet-day-<?php echo esc_attr($index); ?>">
                                    <?php echo esc_html($day['day']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="diet-days-content">
                        <?php foreach ($diet_program['days'] as $index => $day) : ?>
                            <div id="diet-day-<?php echo esc_attr($index); ?>" class="tab-content <?php echo $index === 0 ? 'active' : ''; ?>">
                                <h3><?php echo esc_html($day['day']); ?></h3>
                                
                                <?php if (isset($day['meals']) && is_array($day['meals'])) : ?>
                                    <div class="meal-list">
                                        <?php foreach ($day['meals'] as $meal) : ?>
                                            <div class="meal-item" data-meal-id="<?php echo esc_attr($meal['id']); ?>">
                                                <div class="meal-header">
                                                    <h4>
                                                        <?php echo esc_html($meal['name']); ?>
                                                        <?php if (isset($meal['time'])) : ?>
                                                            <span class="meal-time"><?php echo esc_html($meal['time']); ?></span>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <div class="meal-toggle"></div>
                                                </div>
                                                
                                                <div class="meal-details">
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
                                                    
                                                    <?php if (isset($meal['foods']) && is_array($meal['foods'])) : ?>
                                                        <div class="meal-foods">
                                                            <h5><?php echo __('غذاها:', 'nutri-coach-program'); ?></h5>
                                                            <ul>
                                                                <?php foreach ($meal['foods'] as $food) : ?>
                                                                    <li><?php echo esc_html($food); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (isset($meal['description'])) : ?>
                                                        <div class="meal-description">
                                                            <?php echo wpautop(esc_html($meal['description'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="meal-tracker">
                                                        <h5><?php echo __('ثبت پیشرفت', 'nutri-coach-program'); ?></h5>
                                                        
                                                        <div class="tracker-form">
                                                            <div class="form-row">
                                                                <label for="notes-<?php echo esc_attr($meal['id']); ?>"><?php echo __('یادداشت:', 'nutri-coach-program'); ?></label>
                                                                <textarea id="notes-<?php echo esc_attr($meal['id']); ?>"></textarea>
                                                            </div>
                                                            
                                                            <div class="form-row checkbox-row">
                                                                <label class="checkbox-label">
                                                                    <input type="checkbox" id="completed-<?php echo esc_attr($meal['id']); ?>">
                                                                    <?php echo __('انجام شد', 'nutri-coach-program'); ?>
                                                                </label>
                                                            </div>
                                                            
                                                            <div class="form-actions">
                                                                <button type="button" class="button save-meal" data-meal-id="<?php echo esc_attr($meal['id']); ?>">
                                                                    <?php echo __('ذخیره', 'nutri-coach-program'); ?>
                                                                </button>
                                                                <span class="save-status"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <p><?php echo __('وعده‌ای برای این روز تعریف نشده است.', 'nutri-coach-program'); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="diet-program-text">
                    <?php echo nl2br(esc_html(json_encode($diet_program, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="nutri-coach-program-actions">
        <a href="<?php echo add_query_arg('action', 'progress', get_permalink()); ?>" class="button button-primary">
            <?php echo __('مشاهده گزارش پیشرفت', 'nutri-coach-program'); ?>
        </a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // تب‌ها
    $('.tabs-nav li').click(function() {
        var tab_id = $(this).attr('data-tab');
        
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        
        $(this).closest('.workout-days-tabs, .diet-days-tabs').next('.workout-days-content, .diet-days-content').find('.tab-content').removeClass('active');
        $('#' + tab_id).addClass('active');
    });
    
    // باز/بسته کردن جزئیات تمرین
    $('.exercise-toggle, .meal-toggle').click(function() {
        $(this).closest('.exercise-item, .meal-item').toggleClass('open');
    });
    
    // ذخیره پیشرفت تمرین
    $('.save-exercise').click(function() {
        var exerciseId = $(this).data('exercise-id');
        var programId = $('#program_id').val();
        var sets = $('#sets-' + exerciseId).val();
        var reps = $('#reps-' + exerciseId).val();
        var weight = $('#weight-' + exerciseId).val();
        var notes = $('#notes-' + exerciseId).val();
        var completed = $('#completed-' + exerciseId).is(':checked') ? 1 : 0;
        var statusElement = $(this).siblings('.save-status');
        
        statusElement.text('<?php echo esc_js(__('در حال ذخیره...', 'nutri-coach-program')); ?>');
        
        $.ajax({
            url: nutriCoachProgram.ajaxurl,
            type: 'POST',
            data: {
                action: 'ncp_mark_exercise',
                security: $('#ajax_nonce').val(),
                program_id: programId,
                exercise_id: exerciseId,
                sets: sets,
                reps: reps,
                weight: weight,
                notes: notes,
                completed: completed
            },
            success: function(response) {
                if (response.success) {
                    statusElement.text('<?php echo esc_js(__('ذخیره شد', 'nutri-coach-program')); ?>');
                    setTimeout(function() {
                        statusElement.text('');
                    }, 3000);
                } else {
                    statusElement.text('<?php echo esc_js(__('خطا در ذخیره', 'nutri-coach-program')); ?>');
                }
            },
            error: function() {
                statusElement.text('<?php echo esc_js(__('خطا در ذخیره', 'nutri-coach-program')); ?>');
            }
        });
    });
    
    // ذخیره پیشرفت وعده غذایی
    $('.save-meal').click(function() {
        var mealId = $(this).data('meal-id');
        var programId = $('#program_id').val();
        var notes = $('#notes-' + mealId).val();
        var completed = $('#completed-' + mealId).is(':checked') ? 1 : 0;
        var statusElement = $(this).siblings('.save-status');
        
        statusElement.text('<?php echo esc_js(__('در حال ذخیره...', 'nutri-coach-program')); ?>');
        
        $.ajax({
            url: nutriCoachProgram.ajaxurl,
            type: 'POST',
            data: {
                action: 'ncp_mark_meal',
                security: $('#ajax_nonce').val(),
                program_id: programId,
                meal_id: mealId,
                notes: notes,
                completed: completed
            },
            success: function(response) {
                if (response.success) {
                    statusElement.text('<?php echo esc_js(__('ذخیره شد', 'nutri-coach-program')); ?>');
                    setTimeout(function() {
                        statusElement.text('');
                    }, 3000);
                } else {
                    statusElement.text('<?php echo esc_js(__('خطا در ذخیره', 'nutri-coach-program')); ?>');
                }
            },
            error: function() {
                statusElement.text('<?php echo esc_js(__('خطا در ذخیره', 'nutri-coach-program')); ?>');
            }
        });
    });
});
</script> 
