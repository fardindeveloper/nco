/**
 * اسکریپت JavaScript برای پنل ادمین افزونه Nutri Coach
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // مدیریت تب‌ها
    $('.nutri-coach-program-tabs .program-tab').on('click', function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        
        // فعال‌سازی تب
        $('.nutri-coach-program-tabs .program-tab').removeClass('active');
        $(this).addClass('active');
        
        // نمایش محتوای تب
        $('.program-content').hide();
        $('#' + target).show();
    });
    
    // مدیریت تب‌های روزها
    $('.nutri-coach-day-tabs .day-tab').on('click', function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        
        // فعال‌سازی تب
        $('.nutri-coach-day-tabs .day-tab').removeClass('active');
        $(this).addClass('active');
        
        // نمایش محتوای تب
        $('.day-content').hide();
        $('#' + target).show();
    });
    
    // افزودن حرکت جدید
    $('.nutri-coach-add-exercise button').on('click', function() {
        const day = $(this).data('day');
        const container = $('#exercises-day-' + day);
        const exerciseCount = container.find('.nutri-coach-exercise-item').length;
        const exerciseTemplate = $('#exercise-template').html();
        
        // جایگزینی متغیرها
        const newExercise = exerciseTemplate
            .replace(/\{day\}/g, day)
            .replace(/\{index\}/g, exerciseCount);
        
        container.append(newExercise);
        
        // فعال‌سازی مجدد ایونت‌ها برای حذف
        initializeRemoveEvents();
    });
    
    // افزودن وعده غذایی جدید
    $('.nutri-coach-add-meal button').on('click', function() {
        const day = $(this).data('day');
        const container = $('#meals-day-' + day);
        const mealCount = container.find('.nutri-coach-meal-item').length;
        const mealTemplate = $('#meal-template').html();
        
        // جایگزینی متغیرها
        const newMeal = mealTemplate
            .replace(/\{day\}/g, day)
            .replace(/\{index\}/g, mealCount);
        
        container.append(newMeal);
        
        // فعال‌سازی مجدد ایونت‌ها برای حذف
        initializeRemoveEvents();
    });
    
// دکمه‌های حذف حرکت و وعده غذایی
function initializeRemoveEvents() {
    $('.remove-exercise, .remove-meal').off('click').on('click', function(e) {
        e.preventDefault();
        if (confirm('آیا از حذف این مورد اطمینان دارید؟')) {
            $(this).closest('.nutri-coach-exercise-item, .nutri-coach-meal-item').remove();
        }
    });
}

// فراخوانی اولیه برای فعال‌سازی ایونت‌های حذف
initializeRemoveEvents();

// فیلترهای جستجو و مرتب‌سازی
$('.nutri-coach-filters select').on('change', function() {
    if ($(this).hasClass('auto-submit')) {
        $(this).closest('form').submit();
    }
});

// پیش‌نمایش مقادیر BMI و درصد چربی بدن
$('#weight, #height, #neck, #waist, #hip, #gender').on('change', function() {
    const weight = parseFloat($('#weight').val()) || 0;
    const height = parseFloat($('#height').val()) || 0;
    const neck = parseFloat($('#neck').val()) || 0;
    const waist = parseFloat($('#waist').val()) || 0;
    const hip = parseFloat($('#hip').val()) || 0;
    const gender = $('#gender').val();
    
    if (weight > 0 && height > 0) {
        // محاسبه BMI
        const bmi = calculateBMI(weight, height);
        $('#bmi-preview').text(bmi.toFixed(2) + ' (' + getBMICategory(bmi) + ')');
        
        // محاسبه درصد چربی اگر مقادیر لازم وارد شده باشند
        if (neck > 0 && waist > 0 && (gender === 'male' || (gender === 'female' && hip > 0))) {
            const bodyFat = calculateBodyFat(weight, height, neck, waist, hip, gender);
            $('#body-fat-preview').text(bodyFat.toFixed(2) + '% (' + getBodyFatCategory(bodyFat, gender) + ')');
        }
    }
});

// افزودن قوانین به برنامه
$('.add-rule-to-program').on('click', function() {
    const ruleId = $(this).data('rule-id');
    
    // ارسال درخواست Ajax
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'nutri_coach_add_rule_to_program',
            rule_id: ruleId,
            user_id: $('#user_id').val(),
            nonce: nutri_coach_params.nonce
        },
        beforeSend: function() {
            $('.rule-status-' + ruleId).html('<span class="spinner is-active"></span>');
        },
        success: function(response) {
            if (response.success) {
                $('.rule-status-' + ruleId).html('<span class="dashicons dashicons-yes" style="color: green;"></span> ' + response.data.message);
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            } else {
                $('.rule-status-' + ruleId).html('<span class="dashicons dashicons-no" style="color: red;"></span> ' + response.data.message);
            }
        },
        error: function() {
            $('.rule-status-' + ruleId).html('<span class="dashicons dashicons-no" style="color: red;"></span> خطا در ارتباط با سرور');
        }
    });
});

// محاسبه BMI
function calculateBMI(weight, height) {
    // تبدیل سانتی‌متر به متر
    const heightInMeters = height / 100;
    return weight / (heightInMeters * heightInMeters);
}

// دریافت دسته‌بندی BMI
function getBMICategory(bmi) {
    if (bmi < 18.5) {
        return 'کمبود وزن';
    } else if (bmi < 25) {
        return 'طبیعی';
    } else if (bmi < 30) {
        return 'اضافه وزن';
    } else if (bmi < 35) {
        return 'چاقی درجه ۱';
    } else if (bmi < 40) {
        return 'چاقی درجه ۲';
    } else {
        return 'چاقی درجه ۳';
    }
}

// محاسبه درصد چربی بدن (فرمول نیروی دریایی آمریکا)
function calculateBodyFat(weight, height, neck, waist, hip, gender) {
    if (gender === 'male') {
        return 495 / (1.0324 - 0.19077 * Math.log10(waist - neck) + 0.15456 * Math.log10(height)) - 450;
    } else {
        return 495 / (1.29579 - 0.35004 * Math.log10(waist + hip - neck) + 0.22100 * Math.log10(height)) - 450;
    }
}

// دریافت دسته‌بندی درصد چربی
function getBodyFatCategory(bodyFat, gender) {
    if (gender === 'male') {
        if (bodyFat < 6) {
            return 'ضروری';
        } else if (bodyFat < 14) {
            return 'ورزشکار';
        } else if (bodyFat < 18) {
            return 'تناسب اندام';
        } else if (bodyFat < 25) {
            return 'قابل قبول';
        } else {
            return 'چاق';
        }
    } else {
        if (bodyFat < 14) {
            return 'ضروری';
        } else if (bodyFat < 21) {
            return 'ورزشکار';
        } else if (bodyFat < 25) {
            return 'تناسب اندام';
        } else if (bodyFat < 32) {
            return 'قابل قبول';
        } else {
            return 'چاق';
        }
    }
}

// ارسال نتایج پیشرفت
$('.record-progress-form').on('submit', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const formData = form.serialize();
    
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: formData,
        beforeSend: function() {
            form.find('button[type="submit"]').prop('disabled', true).html('<span class="spinner is-active"></span> در حال ثبت...');
        },
        success: function(response) {
            if (response.success) {
                form.find('.message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                form.find('.message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                form.find('button[type="submit"]').prop('disabled', false).text('ثبت پیشرفت');
            }
        },
        error: function() {
            form.find('.message').html('<div class="notice notice-error"><p>خطا در ارتباط با سرور</p></div>');
            form.find('button[type="submit"]').prop('disabled', false).text('ثبت پیشرفت');
        }
    });
});

// قالب‌های برنامه تمرینی
$('#workout_template').on('change', function() {
    const template = $(this).val();
    if (template) {
        if (confirm('انتخاب قالب باعث جایگزینی برنامه فعلی می‌شود. آیا از انجام این کار اطمینان دارید؟')) {
            $('#loading-template').show();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'nutri_coach_load_workout_template',
                    template: template,
                    user_id: $('#user_id').val(),
                    nonce: nutri_coach_params.nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('خطا: ' + response.data.message);
                        $('#loading-template').hide();
                    }
                },
                error: function() {
                    alert('خطا در ارتباط با سرور');
                    $('#loading-template').hide();
                }
            });
        } else {
            $(this).val('');
        }
    }
});

// قالب‌های برنامه غذایی
$('#diet_template').on('change', function() {
    const template = $(this).val();
    if (template) {
        if (confirm('انتخاب قالب باعث جایگزینی برنامه فعلی می‌شود. آیا از انجام این کار اطمینان دارید؟')) {
            $('#loading-template').show();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'nutri_coach_load_diet_template',
                    template: template,
                    user_id: $('#user_id').val(),
                    nonce: nutri_coach_params.nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('خطا: ' + response.data.message);
                        $('#loading-template').hide();
                    }
                },
                error: function() {
                    alert('خطا در ارتباط با سرور');
                    $('#loading-template').hide();
                }
            });
        } else {
            $(this).val('');
        }
    }
});

// جاگزینی محتوای برنامه بر اساس جنسیت
$('#gender_filter').on('change', function() {
    const gender = $(this).val();
    
    if (gender) {
        $('.gender-content').hide();
        $('.gender-content-' + gender).show();
    } else {
        $('.gender-content').show();
    }
});

// تنظیم مقادیر پیش‌فرض برای فیلترها
const urlParams = new URLSearchParams(window.location.search);
for (const [key, value] of urlParams) {
    $('[name="' + key + '"]').val(value);
}

// راه‌اندازی ویرایشگر TinyMCE
if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '.nutri-coach-editor',
        height: 200,
        menubar: false,
        plugins: [
            'lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });
}

});