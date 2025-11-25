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

<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i>
        {l s='Comando Cron para el Servidor' mod='acccrontask'}
    </div>
    <div class="form-wrapper">
        <div class="alert alert-info">
            <p>{l s='El modo avanzado permite que usted utilice su propio gestor de tareas cron en lugar del servicio web de tareas cron de PrestaShop. Antes que nada, asegúrese de que la librería "curl" está instalada en su servidor.' mod='acccrontask'}</p>
            <p><strong>{l s='Para ejecutar sus tareas cron, por favor, inserte la siguiente línea en su gestor de tareas cron:' mod='acccrontask'}</strong></p>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Comando Cron:' mod='acccrontask'}
            </label>
            <div class="col-lg-9">
                <textarea class="form-control" rows="3" readonly>{$cron_command|escape:'html':'UTF-8'}</textarea>
                <p class="help-block">
                    {l s='Copie este comando y añádalo a su crontab del servidor' mod='acccrontask'}
                </p>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='URL de ejecución:' mod='acccrontask'}
            </label>
            <div class="col-lg-9">
                <input type="text" class="form-control" value="{$cron_url|escape:'html':'UTF-8'}" readonly />
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <a href="{$link->getAdminLink('AdminAccCronTask')|escape:'html':'UTF-8'}" class="btn btn-default">
            <i class="process-icon-back"></i>
            {l s='Volver' mod='acccrontask'}
        </a>
    </div>
</div>

