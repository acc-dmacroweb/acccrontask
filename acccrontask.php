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

        $this->displayName = $this->l('Cron Tasks Manager PRO');
        $this->description = $this->l('Advanced management of custom cron tasks');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        
        // Load module classes using PrestaShop autoloader
        $this->autoload();
    }
    
    /**
     * Module classes autoload
     * PrestaShop 1.7+ automatically loads classes from modules/[name]/classes/
     * but we can ensure it here
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
        if (!parent::install()) {
            return false;
        }
        
        if (!$this->installDb()) {
            return false;
        }
        
        if (!$this->installTab()) {
            return false;
        }
        
        // Clear cache after installation
        $this->clearCache();
        
        return true;
    }
    
    /**
     * Clears PrestaShop cache
     * Compatible with PrestaShop 1.7 and 9
     */
    protected function clearCache()
    {
        try {
            // Método compatible con PrestaShop 1.7 y 9
            if (class_exists('Tools') && method_exists('Tools', 'clearCache')) {
                Tools::clearCache();
            }
            
            // Método para PrestaShop 1.7
            if (class_exists('Cache') && method_exists('Cache', 'clean')) {
                Cache::clean('*');
            }
            
            // Método adicional para PrestaShop 9
            if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
                if (class_exists('PrestaShop\PrestaShop\Core\Cache\Clearer\CacheClearerInterface')) {
                    // Para PS 8+, usar el sistema de caché moderno si está disponible
                    // Esto es opcional y no crítico
                }
            }
        } catch (Exception $e) {
            // Si falla la limpieza de caché, no es crítico, continuar
            // En PS 1.7 puede que algunos métodos no existan
        }
    }
    
    /**
     * Generates a fixed token for cron based on PrestaShop secret key
     * This token will always be the same, regardless of session
     * @return string Fixed token
     */
    public static function getCronToken()
    {
        // Use PrestaShop secret key to generate a fixed token
        // This token will always be the same in the same installation
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
            `cron_unix_style` varchar(255) NULL,
            `active` tinyint(1) NOT NULL DEFAULT 1,
            `last_execution` datetime NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_acccrontask`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $result = Db::getInstance()->execute($sql);
        
        // Add cron_unix_style field if table already exists (for updates)
        $this->addCronUnixStyleColumnIfNotExists();
        
        return $result;
    }
    
    /**
     * Adds cron_unix_style column if it doesn't exist (for updates)
     */
    public function addCronUnixStyleColumnIfNotExists()
    {
        $sql = 'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'acccrontask` LIKE "cron_unix_style"';
        $exists = Db::getInstance()->executeS($sql);
        
        if (empty($exists)) {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'acccrontask` 
                    ADD COLUMN `cron_unix_style` varchar(255) NULL AFTER `hour`';
            Db::getInstance()->execute($sql);
            
            // Generate cron_unix_style for existing tasks
            $this->generateCronUnixStyleForExistingTasks();
        }
    }
    
    /**
     * Generates cron_unix_style for existing tasks that don't have it
     */
    protected function generateCronUnixStyleForExistingTasks()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'acccrontask` WHERE `cron_unix_style` IS NULL OR `cron_unix_style` = ""';
        $tasks = Db::getInstance()->executeS($sql);
        
        if ($tasks) {
            foreach ($tasks as $taskData) {
                $task = new AccCronTaskModel((int)$taskData['id_acccrontask']);
                if (Validate::isLoadedObject($task)) {
                    $cronStyle = $task->generateCronUnixStyle();
                    $task->cron_unix_style = $cronStyle;
                    $task->update();
                }
            }
        }
    }

    protected function uninstallDb()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'acccrontask`');
    }

    protected function installTab()
    {
        // Check if Tab already exists
        $id_tab = (int)Tab::getIdFromClassName('AdminAccCronTask');
        
        if ($id_tab) {
            $tab = new Tab($id_tab);
        } else {
            $tab = new Tab();
        }
        
        $tab->active = 1;
        $tab->class_name = 'AdminAccCronTask';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Cron Tasks Manager PRO';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminTools');
        $tab->module = $this->name;
        
        // For PrestaShop 9.0+, we do NOT set route_name
        // The system will automatically generate the route based on class_name and module
        // In PS 1.7 and PS 8 it works without route_name (as in PS 1.7)
        // In PS 9, we simply don't touch route_name so it uses the automatic system
        // We don't set route_name in PS 9 to avoid "route does not exist" errors
        
        if ($id_tab) {
            return $tab->update();
        } else {
            return $tab->add();
        }
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

    /**
     * Updates Tab for PrestaShop 9.0+
     * Useful if module was installed before this update
     * Only necessary for PS 9 where route_name is mandatory
     */
    public function updateTabForPS9()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminAccCronTask');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            // For PrestaShop 9.0+, we do NOT set route_name
            // The system will automatically generate the route
            // We only ensure that basic fields are correct
            if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
                // Ensure module is assigned
                if (empty($tab->module)) {
                    $tab->module = $this->name;
                }
                
                // Ensure active is set to 1
                $tab->active = 1;
                
                // Ensure class_name is correct
                if (empty($tab->class_name)) {
                    $tab->class_name = 'AdminAccCronTask';
                }
                
                // We do NOT set route_name - the system will generate it automatically
                // If route_name is set and causes problems, we remove it
                if (property_exists($tab, 'route_name') && !empty($tab->route_name)) {
                    $tab->route_name = '';
                }
                
                if ($tab->update()) {
                    $this->clearCache();
                    return true;
                }
            }
        }
        return false;
    }

    public function getContent()
    {
        // Ensure cron_unix_style column exists (for already installed modules)
        $this->addCronUnixStyleColumnIfNotExists();
        
        // Only update Tab for PS 9.0+ (where route_name is mandatory)
        // PS 1.7 and PS 8 work without route_name
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            // Force Tab update in PS 9
            $this->updateTabForPS9();
            // Clear routing cache after updating
            $this->clearCache();
        }
        
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminAccCronTask'));
    }
}

