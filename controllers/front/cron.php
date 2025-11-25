<?php
/**
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
 */

require_once dirname(__FILE__) . '/../../../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../../../init.php';

// Cargar el módulo para que se ejecute su autoload
$module = Module::getInstanceByName('acccrontask');
if (!$module || !$module->active) {
    http_response_code(404);
    die('Module not found or inactive');
}

$token = Tools::getValue('token');
// Usar el token fijo del módulo (siempre el mismo, independiente de la sesión)
$expectedToken = AccCronTask::getCronToken();

if (empty($token) || $token !== $expectedToken) {
    http_response_code(403);
    // Solo mostrar detalles en modo debug
    if (Tools::getValue('debug') == '1') {
        die('Invalid token.<br>Received: ' . htmlspecialchars($token) . '<br>Expected: ' . htmlspecialchars($expectedToken) . '<br>Length received: ' . strlen($token) . '<br>Length expected: ' . strlen($expectedToken));
    }
    die('Invalid token');
}

$cronJobs = AccCronTaskModel::getActiveCronJobs();

foreach ($cronJobs as $jobData) {
    $job = new AccCronTaskModel((int)$jobData['id_acccrontask']);
    
    if (Validate::isLoadedObject($job) && $job->shouldExecute()) {
        $job->execute();
    }
}

die('Cron executed');
