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
        {l s='Cron Command for Server' mod='acccrontask'}
    </div>
    <div class="form-wrapper">
        <div class="alert alert-info">
            <p>{l s='Advanced mode allows you to use your own cron task manager instead of PrestaShop web cron service. First, make sure the "curl" library is installed on your server.' mod='acccrontask'}</p>
            <p><strong>{l s='To execute your cron tasks, please insert the following line in your cron task manager:' mod='acccrontask'}</strong></p>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Cron Command:' mod='acccrontask'}
            </label>
            <div class="col-lg-9">
                <textarea class="form-control" rows="3" readonly>{$cron_command|escape:'html':'UTF-8'}</textarea>
                <p class="help-block">
                    {l s='Copy this command and add it to your server crontab' mod='acccrontask'}
                </p>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Execution URL:' mod='acccrontask'}
            </label>
            <div class="col-lg-9">
                <input type="text" class="form-control" value="{$cron_url|escape:'html':'UTF-8'}" readonly />
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <a href="{$link->getAdminLink('AdminAccCronTask')|escape:'html':'UTF-8'}" class="btn btn-default">
            <i class="process-icon-back"></i>
            {l s='Back' mod='acccrontask'}
        </a>
    </div>
</div>

