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
        if (!parent::install()) {
            return false;
        }
        
        if (!$this->installDb()) {
            return false;
        }
        
        if (!$this->installTab()) {
            return false;
        }
        
        // Limpiar caché después de la instalación
        $this->clearCache();
        
        return true;
    }
    
    /**
     * Limpia la caché de PrestaShop
     * Compatible con PrestaShop 1.7 y 9
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
        // Verificar si el Tab ya existe
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
            $tab->name[$lang['id_lang']] = 'ACC Cron Task';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminTools');
        $tab->module = $this->name;
        
        // Para PrestaShop 9.0+, NO establecemos route_name
        // El sistema generará la ruta automáticamente basándose en class_name y módulo
        // En PS 1.7 y PS 8 funciona sin route_name (como en PS 1.7)
        // En PS 9, simplemente no tocamos route_name para que use el sistema automático
        // No establecemos route_name en PS 9 para evitar errores de "route does not exist"
        
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
     * Actualiza el Tab para PrestaShop 9.0+
     * Útil si el módulo fue instalado antes de esta actualización
     * Solo necesario para PS 9 donde route_name es obligatorio
     */
    public function updateTabForPS9()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminAccCronTask');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            // Para PrestaShop 9.0+, NO establecemos route_name
            // El sistema generará la ruta automáticamente
            // Solo aseguramos que los campos básicos estén correctos
            if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
                // Asegurar que el módulo esté asignado
                if (empty($tab->module)) {
                    $tab->module = $this->name;
                }
                
                // Asegurar que active esté en 1
                $tab->active = 1;
                
                // Asegurar que class_name esté correcto
                if (empty($tab->class_name)) {
                    $tab->class_name = 'AdminAccCronTask';
                }
                
                // NO establecemos route_name - el sistema lo generará automáticamente
                // Si route_name está establecido y causa problemas, lo eliminamos
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
        // Solo actualizar Tab para PS 9.0+ (donde route_name es obligatorio)
        // PS 1.7 y PS 8 funcionan sin route_name
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            // Forzar actualización del Tab en PS 9
            $this->updateTabForPS9();
            // Limpiar caché de routing después de actualizar
            $this->clearCache();
        }
        
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminAccCronTask'));
    }
}

