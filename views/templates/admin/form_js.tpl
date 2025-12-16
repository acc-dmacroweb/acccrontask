{*
* 2007-2024 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2024 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
$(document).ready(function() {
    function toggleFields() {
        var frequency = parseInt($('select[name="frequency_day"]').val() || $('#frequency_day').val());
        
        if (isNaN(frequency)) {
            return;
        }
        
        // Hide all fields first
        $('.field-hour').closest('.form-group').hide();
        $('.field-day-week').closest('.form-group').hide();
        $('.field-day-month').closest('.form-group').hide();
        $('.field-month').closest('.form-group').hide();
        $('.field-cron-unix-style').closest('.form-group').hide();
        $('.field-minute').closest('.form-group').hide();
        
        // Show fields according to frequency
        switch(frequency) {
            case 5: // Every hour
                // Only minutes
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').hide();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 0: // Daily
                // Hour and minutes
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 1: // Weekly
                // Day of week, hour and minutes
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').show();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 2: // Monthly
                // Hour, minutes and day of month
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').show();
                $('.field-month').closest('.form-group').hide();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 3: // Yearly
                // Day of month, month, hour and minutes
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').show();
                $('.field-month').closest('.form-group').show();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 6: // Cron Unix Style
                // Only cron unix style
                $('.field-cron-unix-style').closest('.form-group').show();
                $('.field-minute').closest('.form-group').hide();
                $('.field-hour').closest('.form-group').hide();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                break;
        }
    }
    
    // Execute on page load (with delay to ensure DOM is ready)
    setTimeout(function() {
        toggleFields();
    }, 100);
    
    // Execute on frequency change
    $(document).on('change', 'select[name="frequency_day"], #frequency_day', function() {
        toggleFields();
    });
    
    // Validate minutes (0-59)
    $(document).on('blur', 'input[name="minute"]', function() {
        var minute = parseInt($(this).val());
        if (isNaN(minute) || minute < 0 || minute > 59) {
            alert('{l s="Minute must be between 0 and 59" mod="crontasksmanagerpro"}');
            $(this).val('');
            $(this).focus();
        }
    });
    
    // Validate hour (0-23)
    $(document).on('blur', 'input[name="hour"]', function() {
        var hour = parseInt($(this).val());
        if ($(this).val() !== '' && (isNaN(hour) || hour < 0 || hour > 23)) {
            alert('{l s="Hour must be between 0 and 23" mod="crontasksmanagerpro"}');
            $(this).val('');
            $(this).focus();
        }
    });
    
    // Validate day of month (1-31)
    $(document).on('blur', 'input[name="day_of_month"]', function() {
        var day = parseInt($(this).val());
        if ($(this).val() !== '' && (isNaN(day) || day < 1 || day > 31)) {
            alert('{l s="Day of month must be between 1 and 31" mod="crontasksmanagerpro"}');
            $(this).val('');
            $(this).focus();
        }
    });
    
    // Validate on form submit
    $('form').on('submit', function(e) {
        var frequency = parseInt($('select[name="frequency_day"]').val() || $('#frequency_day').val());
        
        // If frequency is Cron Unix Style (6), validate cron_unix_style field
        if (frequency === 6) {
            var cronValue = $('input[name="cron_unix_style"]').val().trim();
            
            if (cronValue === '') {
                alert('{l s="You must enter a cron unix style format" mod="crontasksmanagerpro"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            
            // Validate basic format: must have 5 parts separated by spaces
            var parts = cronValue.split(/\s+/);
            if (parts.length !== 5) {
                alert('{l s="The cron unix style format must have 5 parts separated by spaces: minute hour day_of_month month day_of_week. Example: 0 2 * * *" mod="crontasksmanagerpro"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            
            // Validate each part - more flexible patterns that allow intervals and lists
            var minutePattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            var hourPattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            var dayPattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            var monthPattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            var dayOfWeekPattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            
            // Validate minute (0-59)
            if (!minutePattern.test(parts[0])) {
                alert('{l s="The minute (first part) is not valid. Valid examples: *, 5, 0-59, */5, 0,5,10" mod="crontasksmanagerpro"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[0] !== '*' && !parts[0].includes('/') && !parts[0].includes('-') && !parts[0].includes(',')) {
                var minuteNum = parseInt(parts[0]);
                if (isNaN(minuteNum) || minuteNum < 0 || minuteNum > 59) {
                    alert('{l s="Minute must be between 0 and 59" mod="crontasksmanagerpro"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            // Validate hour (0-23)
            if (!hourPattern.test(parts[1])) {
                alert('{l s="The hour (second part) is not valid. Valid examples: *, 2, 0-23, */2" mod="crontasksmanagerpro"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[1] !== '*' && !parts[1].includes('/') && !parts[1].includes('-') && !parts[1].includes(',')) {
                var hourNum = parseInt(parts[1]);
                if (isNaN(hourNum) || hourNum < 0 || hourNum > 23) {
                    alert('{l s="Hour must be between 0 and 23" mod="crontasksmanagerpro"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            // Validate day of month (1-31)
            if (!dayPattern.test(parts[2])) {
                alert('{l s="The day of month (third part) is not valid. Valid examples: *, 1, 1-31" mod="crontasksmanagerpro"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[2] !== '*' && !parts[2].includes('/') && !parts[2].includes('-') && !parts[2].includes(',')) {
                var dayNum = parseInt(parts[2]);
                if (isNaN(dayNum) || dayNum < 1 || dayNum > 31) {
                    alert('{l s="Day of month must be between 1 and 31" mod="crontasksmanagerpro"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            // Validate month (1-12)
            if (!monthPattern.test(parts[3])) {
                alert('{l s="The month (fourth part) is not valid. Valid examples: *, 1, 1-12" mod="crontasksmanagerpro"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[3] !== '*' && !parts[3].includes('/') && !parts[3].includes('-') && !parts[3].includes(',')) {
                var monthNum = parseInt(parts[3]);
                if (isNaN(monthNum) || monthNum < 1 || monthNum > 12) {
                    alert('{l s="Month must be between 1 and 12" mod="crontasksmanagerpro"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            // Validate day of week (0-6)
            if (!dayOfWeekPattern.test(parts[4])) {
                alert('{l s="The day of week (fifth part) is not valid. Valid examples: *, 0, 0-6" mod="crontasksmanagerpro"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[4] !== '*' && !parts[4].includes('/') && !parts[4].includes('-') && !parts[4].includes(',')) {
                var dayOfWeekNum = parseInt(parts[4]);
                if (isNaN(dayOfWeekNum) || dayOfWeekNum < 0 || dayOfWeekNum > 6) {
                    alert('{l s="Day of week must be between 0 and 6" mod="crontasksmanagerpro"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
        }
    });
});
</script>


