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
        
        // Mostrar campos según la frecuencia
        switch(frequency) {
            case 5: // Cada hora
                // Solo minutos
                $('.field-hour').closest('.form-group').hide();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                break;
                
            case 0: // Diario
                // Hora y minutos
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                break;
                
            case 1: // Semanal
                // Día de la semana, hora y minutos
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').show();
                $('.field-day-month').closest('.form-group').hide();
                $('.field-month').closest('.form-group').hide();
                break;
                
            case 2: // Mensual
                // Hora, minutos y día del mes
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').show();
                $('.field-month').closest('.form-group').hide();
                break;
                
            case 3: // Anual
                // Día del mes, mes, hora y minutos
                $('.field-hour').closest('.form-group').show();
                $('.field-day-week').closest('.form-group').hide();
                $('.field-day-month').closest('.form-group').show();
                $('.field-month').closest('.form-group').show();
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
});
</script>

