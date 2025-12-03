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
        
        // Ocultar todos los campos primero
        $('.field-hour').closest('.form-group').hide();
        $('.field-day-week').closest('.form-group').hide();
        $('.field-day-month').closest('.form-group').hide();
        $('.field-month').closest('.form-group').hide();
        $('.field-cron-unix-style').closest('.form-group').hide();
        $('.field-minute').closest('.form-group').hide();
        
        // Mostrar campos según la frecuencia
        switch(frequency) {
            case 5: // Cada hora
                // Solo minutos
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').hide();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 0: // Diario
                // Hora y minutos
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 1: // Semanal
                // Día de la semana, hora y minutos
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').show();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 2: // Mensual
                // Hora, minutos y día del mes
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').show();
                $('.field-month').closest('.form-group').hide();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 3: // Anual
                // Día del mes, mes, hora y minutos
                $('.field-minute').closest('.form-group').show();
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').show();
                $('.field-month').closest('.form-group').show();
                $('.field-cron-unix-style').closest('.form-group').hide();
                break;
                
            case 6: // Cron Unix Style
                // Solo cron unix style
                $('.field-cron-unix-style').closest('.form-group').show();
                $('.field-minute').closest('.form-group').hide();
                $('.field-hour').closest('.form-group').hide();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                break;
        }
    }
    
    // Ejecutar al cargar la página (con delay para asegurar que el DOM esté listo)
    setTimeout(function() {
        toggleFields();
    }, 100);
    
    // Ejecutar al cambiar la frecuencia
    $(document).on('change', 'select[name="frequency_day"], #frequency_day', function() {
        toggleFields();
    });
    
    // Validar minutos (0-59)
    $(document).on('blur', 'input[name="minute"]', function() {
        var minute = parseInt($(this).val());
        if (isNaN(minute) || minute < 0 || minute > 59) {
            alert('{l s="El minuto debe estar entre 0 y 59" mod="acccrontask"}');
            $(this).val('');
            $(this).focus();
        }
    });
    
    // Validar hora (0-23)
    $(document).on('blur', 'input[name="hour"]', function() {
        var hour = parseInt($(this).val());
        if ($(this).val() !== '' && (isNaN(hour) || hour < 0 || hour > 23)) {
            alert('{l s="La hora debe estar entre 0 y 23" mod="acccrontask"}');
            $(this).val('');
            $(this).focus();
        }
    });
    
    // Validar día del mes (1-31)
    $(document).on('blur', 'input[name="day_of_month"]', function() {
        var day = parseInt($(this).val());
        if ($(this).val() !== '' && (isNaN(day) || day < 1 || day > 31)) {
            alert('{l s="El día del mes debe estar entre 1 y 31" mod="acccrontask"}');
            $(this).val('');
            $(this).focus();
        }
    });
    
    // Validar al enviar el formulario
    $('form').on('submit', function(e) {
        var frequency = parseInt($('select[name="frequency_day"]').val() || $('#frequency_day').val());
        
        // Si la frecuencia es Cron Unix Style (6), validar el campo cron_unix_style
        if (frequency === 6) {
            var cronValue = $('input[name="cron_unix_style"]').val().trim();
            
            if (cronValue === '') {
                alert('{l s="Debe ingresar un formato cron unix style" mod="acccrontask"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            
            // Validar formato básico: debe tener 5 partes separadas por espacios
            var parts = cronValue.split(/\s+/);
            if (parts.length !== 5) {
                alert('{l s="El formato cron unix style debe tener 5 partes separadas por espacios: minuto hora día_mes mes día_semana. Ejemplo: 0 2 * * *" mod="acccrontask"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            
            // Validar cada parte - patrones más flexibles que permiten intervalos y listas
            var minutePattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            var hourPattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            var dayPattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            var monthPattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            var dayOfWeekPattern = /^(\*|\*\/\d+|\d+(-\d+)?(\/\d+)?|(\d+,)+\d+)$/;
            
            // Validar minuto (0-59)
            if (!minutePattern.test(parts[0])) {
                alert('{l s="El minuto (primera parte) no es válido. Ejemplos válidos: *, 5, 0-59, */5, 0,5,10" mod="acccrontask"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[0] !== '*' && !parts[0].includes('/') && !parts[0].includes('-') && !parts[0].includes(',')) {
                var minuteNum = parseInt(parts[0]);
                if (isNaN(minuteNum) || minuteNum < 0 || minuteNum > 59) {
                    alert('{l s="El minuto debe estar entre 0 y 59" mod="acccrontask"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            // Validar hora (0-23)
            if (!hourPattern.test(parts[1])) {
                alert('{l s="La hora (segunda parte) no es válida. Ejemplos válidos: *, 2, 0-23, */2" mod="acccrontask"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[1] !== '*' && !parts[1].includes('/') && !parts[1].includes('-') && !parts[1].includes(',')) {
                var hourNum = parseInt(parts[1]);
                if (isNaN(hourNum) || hourNum < 0 || hourNum > 23) {
                    alert('{l s="La hora debe estar entre 0 y 23" mod="acccrontask"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            // Validar día del mes (1-31)
            if (!dayPattern.test(parts[2])) {
                alert('{l s="El día del mes (tercera parte) no es válido. Ejemplos válidos: *, 1, 1-31" mod="acccrontask"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[2] !== '*' && !parts[2].includes('/') && !parts[2].includes('-') && !parts[2].includes(',')) {
                var dayNum = parseInt(parts[2]);
                if (isNaN(dayNum) || dayNum < 1 || dayNum > 31) {
                    alert('{l s="El día del mes debe estar entre 1 y 31" mod="acccrontask"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            // Validar mes (1-12)
            if (!monthPattern.test(parts[3])) {
                alert('{l s="El mes (cuarta parte) no es válido. Ejemplos válidos: *, 1, 1-12" mod="acccrontask"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[3] !== '*' && !parts[3].includes('/') && !parts[3].includes('-') && !parts[3].includes(',')) {
                var monthNum = parseInt(parts[3]);
                if (isNaN(monthNum) || monthNum < 1 || monthNum > 12) {
                    alert('{l s="El mes debe estar entre 1 y 12" mod="acccrontask"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            // Validar día de la semana (0-6)
            if (!dayOfWeekPattern.test(parts[4])) {
                alert('{l s="El día de la semana (quinta parte) no es válido. Ejemplos válidos: *, 0, 0-6" mod="acccrontask"}');
                $('input[name="cron_unix_style"]').focus();
                e.preventDefault();
                return false;
            }
            if (parts[4] !== '*' && !parts[4].includes('/') && !parts[4].includes('-') && !parts[4].includes(',')) {
                var dayOfWeekNum = parseInt(parts[4]);
                if (isNaN(dayOfWeekNum) || dayOfWeekNum < 0 || dayOfWeekNum > 6) {
                    alert('{l s="El día de la semana debe estar entre 0 y 6" mod="acccrontask"}');
                    $('input[name="cron_unix_style"]').focus();
                    e.preventDefault();
                    return false;
                }
            }
        }
    });
});
</script>


