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

if (!defined('_PS_VERSION_')) {
    exit;
}

class AccCronTask extends Module
{
    public function __construct()
    {
        $this->name = 'acccrontask';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'ACC';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('ACC Cron Task');
        $this->description = $this->l('Gestión avanzada de tareas cron personalizadas');
        $this->confirmUninstall = $this->l('¿Está seguro de que desea desinstalar?');
        
        // Cargar clases del módulo usando el autoloader de PrestaShop
        $this->autoload();
    }
    
    /**
     * Autoload de clases del módulo
     * PrestaShop 1.7+ carga automáticamente las clases de modules/[nombre]/classes/
     * pero podemos asegurarnos aquí
     */
    protected function autoload()
    {
        $classesPath = $this->getLocalPath() . 'classes/';
        if (is_dir($classesPath)) {
            $files = glob($classesPath . '*.php');
            foreach ($files as $file) {
                $className = basename($file, '.php');
                if (!class_exists($className, false)) {
                    require_once $file;
                }
            }
        }
    }

    public function install()
    {
        return parent::install() &&
            $this->installDb() &&
            $this->registerHook('actionCronJob') &&
            $this->installTab();
    }
    
    /**
     * Genera un token fijo para el cron basado en la clave secreta de PrestaShop
     * Este token siempre será el mismo, independientemente de la sesión
     * @return string Token fijo
     */
    public static function getCronToken()
    {
        // Usar la clave secreta de PrestaShop para generar un token fijo
        // Este token siempre será el mismo en la misma instalación
        return md5(_COOKIE_KEY_ . 'acccrontask_cron_token' . _COOKIE_IV_);
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallDb() &&
            $this->uninstallTab();
    }

    protected function installDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'acccrontask` (
            `id_acccrontask` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `url` text NOT NULL,
            `frequency_day` int(11) NOT NULL DEFAULT 0,
            `minute` int(11) NOT NULL DEFAULT 0,
            `day_of_week` int(11) NOT NULL DEFAULT -1,
            `day_of_month` int(11) NOT NULL DEFAULT -1,
            `month` int(11) NOT NULL DEFAULT -1,
            `hour` int(11) NOT NULL DEFAULT 0,
            `active` tinyint(1) NOT NULL DEFAULT 1,
            `last_execution` datetime NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_acccrontask`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    protected function uninstallDb()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'acccrontask`');
    }

    protected function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminAccCronTask';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'ACC Cron Task';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminTools');
        $tab->module = $this->name;
        return $tab->add();
    }

    protected function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminAccCronTask');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return false;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminAccCronTask'));
    }
}

