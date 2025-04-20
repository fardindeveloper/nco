/**
 * Nutri Coach Program - Frontend Script
 */
 
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // تب‌ها
        $('.tabs-nav li').on('click', function() {
            var tab_id = $(this).attr('data-tab');
            
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            
            $(this).closest('.workout-days-tabs, .diet-days-tabs')
                .next('.workout-days-content, .diet-days-content')
                .find('.tab-content')
                .removeClass('active');
                
            $('#' + tab_id).addClass('active');
        });
        
        // باز/بسته کردن جزئیات تمرین و وعده غذایی
        $('.exercise-header, .meal-header').on('click', function() {
            $(this).closest('.exercise-item, .meal-item').toggleClass('open');
        });
        
        // ذخیره پیشرفت تمرین
        $('.save-exercise').on('click', function() {
            var exerciseId = $(this).data('exercise-id');
            var programId = $('#program_id').val();
            var sets = $('#sets-' + exerciseId).val();
            var reps = $('#reps-' + exerciseId).val();
            var weight = $('#weight-' + exerciseId).val();
            var notes = $('#notes-' + exerciseId).val();
            var completed = $('#completed-' + exerciseId).is(':checked') ? 1 : 0;
            var statusElement = $(this).siblings('.save-status');
            
            statusElement.text(nutriCoachProgram.i18n.saving);
            
            $.ajax({
                url: nutriCoachProgram.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ncp_mark_exercise',
                    security: nutriCoachProgram.nonce,
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
                        statusElement.text(nutriCoachProgram.i18n.save_success);
                        setTimeout(function() {
                            statusElement.text('');
                        }, 3000);
                    } else {
                        statusElement.text(nutriCoachProgram.i18n.save_error);
                    }
                },
                error: function() {
                    statusElement.text(nutriCoachProgram.i18n.save_error);
                }
            });
        });
        
        // ذخیره پیشرفت وعده غذایی
        $('.save-meal').on('click', function() {
            var mealId = $(this).data('meal-id');
            var programId = $('#program_id').val();
            var notes = $('#notes-' + mealId).val();
            var completed = $('#completed-' + mealId).is(':checked') ? 1 : 0;
            var statusElement = $(this).siblings('.save-status');
            
            statusElement.text(nutriCoachProgram.i18n.saving);
            
            $.ajax({
                url: nutriCoachProgram.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ncp_mark_meal',
                    security: nutriCoachProgram.nonce,
                    program_id: programId,
                    meal_id: mealId,
                    notes: notes,
                    completed: completed
                },
                success: function(response) {
                    if (response.success) {
                        statusElement.text(nutriCoachProgram.i18n.save_success);
                        setTimeout(function() {
                            statusElement.text('');
                        }, 3000);
                    } else {
                        statusElement.text(nutriCoachProgram.i18n.save_error);
                    }
                },
                error: function() {
                    statusElement.text(nutriCoachProgram.i18n.save_error);
                }
            });
        });
        
        // بارگذاری آخرین وضعیت تمرین‌ها
        function loadExerciseProgress() {
            var programId = $('#program_id').val();
            
            if (!programId) {
                return;
            }
            
            $('.exercise-item').each(function() {
                var exerciseId = $(this).data('exercise-id');
                var item = $(this);
                
                $.ajax({
                    url: nutriCoachProgram.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ncp_get_exercise_progress',
                        security: nutriCoachProgram.nonce,
                        program_id: programId,
                        exercise_id: exerciseId
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            var progress = response.data;
                            
                            // پر کردن فرم با مقادیر قبلی
                            $('#sets-' + exerciseId).val(progress.sets);
                            $('#reps-' + exerciseId).val(progress.reps);
                            $('#weight-' + exerciseId).val(progress.weight);
                            $('#notes-' + exerciseId).val(progress.notes);
                            
                            if (progress.completed == 1) {
                                $('#completed-' + exerciseId).prop('checked', true);
                                item.addClass('completed');
                            } else {
                                $('#completed-' + exerciseId).prop('checked', false);
                                item.removeClass('completed');
                            }
                        }
                    }
                });
            });
        }
        
        // بارگذاری آخرین وضعیت وعده‌های غذایی
        function loadMealProgress() {
            var programId = $('#program_id').val();
            
            if (!programId) {
                return;
            }
            
            $('.meal-item').each(function() {
                var mealId = $(this).data('meal-id');
                var item = $(this);
                
                $.ajax({
                    url: nutriCoachProgram.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ncp_get_meal_progress',
                        security: nutriCoachProgram.nonce,
                        program_id: programId,
                        meal_id: mealId
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            var progress = response.data;
                            
                            // پر کردن فرم با مقادیر قبلی
                            $('#notes-' + mealId).val(progress.notes);
                            
                            if (progress.completed == 1) {
                                $('#completed-' + mealId).prop('checked', true);
                                item.addClass('completed');
                            } else {
                                $('#completed-' + mealId).prop('checked', false);
                                item.removeClass('completed');
                            }
                        }
                    }
                });
            });
        }
        
        // فقط در صفحه برنامه تمرینی/غذایی این توابع را اجرا کن
        if ($('.nutri-coach-program-container').length > 0) {
            loadExerciseProgress();
            loadMealProgress();
        }
    });
    
})(jQuery); 
