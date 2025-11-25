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

require_once _PS_MODULE_DIR_ . 'acccrontask/acccrontask.php';

class AdminAccCronTaskController extends ModuleAdminController
{
    public function __construct()
    {
        // Cargar el módulo para asegurar que las clases estén disponibles
        $module = Module::getInstanceByName('acccrontask');
        
        $this->bootstrap = true;
        $this->table = 'acccrontask';
        $this->className = 'AccCronTaskModel';
        $this->identifier = 'id_acccrontask';
        $this->lang = false;
        $this->context = Context::getContext();

        parent::__construct();

        $this->fields_list = [
            'name' => [
                'title' => $this->l('Nombre'),
                'width' => 'auto',
                'filter_key' => 'a!name',
            ],
            'url' => [
                'title' => $this->l('URL'),
                'width' => 'auto',
                'callback' => 'getUrlDisplay',
                'filter_key' => 'a!url',
            ],
            'hour' => [
                'title' => $this->l('Hora'),
                'width' => 'auto',
                'align' => 'center',
            ],
            'minute' => [
                'title' => $this->l('Minuto'),
                'width' => 'auto',
                'align' => 'center',
            ],
            'month' => [
                'title' => $this->l('Mes'),
                'width' => 'auto',
                'align' => 'center',
                'callback' => 'getMonthDisplay',
            ],
            'day_of_week' => [
                'title' => $this->l('Día de la semana'),
                'width' => 'auto',
                'align' => 'center',
                'callback' => 'getDayOfWeekDisplay',
            ],
            'last_execution' => [
                'title' => $this->l('Última ejecución'),
                'width' => 'auto',
                'type' => 'datetime',
                'align' => 'center',
            ],
            'active' => [
                'title' => $this->l('Activo'),
                'width' => 'auto',
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'class' => 'fixed-width-xs',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Eliminar seleccionados'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('¿Eliminar los elementos seleccionados?'),
            ],
        ];
    }

    public function initContent()
    {
        if (Tools::getValue('action') == 'generateCron') {
            $this->generateCronCommand();
            $this->context->smarty->assign('content', $this->content);
            return;
        }
        
        // Ya no necesitamos regenerar token, usamos el de AdminModules

        parent::initContent();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Añadir nueva tarea'),
        ];

        $this->toolbar_btn['export'] = [
            'href' => self::$currentIndex . '&action=generateCron&token=' . $this->token,
            'desc' => $this->l('Generar comando Cron'),
            'icon' => 'process-icon-cogs',
        ];

        $list = parent::renderList();
        
        $this->context->smarty->assign([
            'list' => $list,
            'token' => $this->token,
            'current_index' => self::$currentIndex,
            'controller' => 'AdminAccCronTask',
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'acccrontask/views/templates/admin/list_override.tpl'
        );
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Tarea Cron'),
                'icon' => 'icon-cog',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Nombre'),
                    'name' => 'name',
                    'required' => true,
                    'maxlength' => 255,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('URL'),
                    'name' => 'url',
                    'required' => true,
                    'desc' => $this->l('URL completa a ejecutar'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Frecuencia'),
                    'name' => 'frequency_day',
                    'required' => true,
                    'options' => [
                        'query' => [
                            ['id' => 5, 'name' => $this->l('Cada hora')],
                            ['id' => 0, 'name' => $this->l('Diario')],
                            ['id' => 1, 'name' => $this->l('Semanal')],
                            ['id' => 2, 'name' => $this->l('Mensual')],
                            ['id' => 3, 'name' => $this->l('Anual')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 'frequency-selector',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Minuto'),
                    'name' => 'minute',
                    'required' => true,
                    'suffix' => $this->l('(0-59)'),
                    'class' => 'fixed-width-sm field-minute',
                    'desc' => $this->l('Minuto de ejecución (0-59)'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Hora'),
                    'name' => 'hour',
                    'required' => false,
                    'suffix' => $this->l('(0-23)'),
                    'class' => 'fixed-width-sm field-hour',
                    'desc' => $this->l('Hora de ejecución (0-23)'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Día de la semana'),
                    'name' => 'day_of_week',
                    'required' => false,
                    'options' => [
                        'query' => [
                            ['id' => 0, 'name' => $this->l('Domingo')],
                            ['id' => 1, 'name' => $this->l('Lunes')],
                            ['id' => 2, 'name' => $this->l('Martes')],
                            ['id' => 3, 'name' => $this->l('Miércoles')],
                            ['id' => 4, 'name' => $this->l('Jueves')],
                            ['id' => 5, 'name' => $this->l('Viernes')],
                            ['id' => 6, 'name' => $this->l('Sábado')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 'field-day-week',
                    'desc' => $this->l('Día de la semana para ejecución semanal'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Día del mes'),
                    'name' => 'day_of_month',
                    'required' => false,
                    'suffix' => $this->l('(1-31)'),
                    'class' => 'fixed-width-sm field-day-month',
                    'desc' => $this->l('Día del mes para ejecución mensual o anual'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Mes'),
                    'name' => 'month',
                    'required' => false,
                    'options' => [
                        'query' => [
                            ['id' => 1, 'name' => $this->l('Enero')],
                            ['id' => 2, 'name' => $this->l('Febrero')],
                            ['id' => 3, 'name' => $this->l('Marzo')],
                            ['id' => 4, 'name' => $this->l('Abril')],
                            ['id' => 5, 'name' => $this->l('Mayo')],
                            ['id' => 6, 'name' => $this->l('Junio')],
                            ['id' => 7, 'name' => $this->l('Julio')],
                            ['id' => 8, 'name' => $this->l('Agosto')],
                            ['id' => 9, 'name' => $this->l('Septiembre')],
                            ['id' => 10, 'name' => $this->l('Octubre')],
                            ['id' => 11, 'name' => $this->l('Noviembre')],
                            ['id' => 12, 'name' => $this->l('Diciembre')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 'field-month',
                    'desc' => $this->l('Mes para ejecución anual'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Activo'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Sí'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Guardar'),
            ],
        ];

        $form = parent::renderForm();
        
        // Agregar JavaScript para mostrar/ocultar campos según la frecuencia
        $this->context->smarty->assign([
            'form' => $form,
        ]);
        
        $js = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'acccrontask/views/templates/admin/form_js.tpl'
        );
        
        return $form . $js;
    }

    protected function getMinuteOptions()
    {
        $options = [];
        for ($i = 0; $i < 60; $i += 5) {
            $options[] = [
                'id' => $i,
                'name' => str_pad($i, 2, '0', STR_PAD_LEFT),
            ];
        }
        return $options;
    }

    protected function getDayOfWeekOptions()
    {
        return [
            ['id' => -1, 'name' => $this->l('Todos')],
            ['id' => 0, 'name' => $this->l('Domingo')],
            ['id' => 1, 'name' => $this->l('Lunes')],
            ['id' => 2, 'name' => $this->l('Martes')],
            ['id' => 3, 'name' => $this->l('Miércoles')],
            ['id' => 4, 'name' => $this->l('Jueves')],
            ['id' => 5, 'name' => $this->l('Viernes')],
            ['id' => 6, 'name' => $this->l('Sábado')],
        ];
    }

    protected function getMonthOptions()
    {
        return [
            ['id' => -1, 'name' => $this->l('Todos')],
            ['id' => 1, 'name' => $this->l('Enero')],
            ['id' => 2, 'name' => $this->l('Febrero')],
            ['id' => 3, 'name' => $this->l('Marzo')],
            ['id' => 4, 'name' => $this->l('Abril')],
            ['id' => 5, 'name' => $this->l('Mayo')],
            ['id' => 6, 'name' => $this->l('Junio')],
            ['id' => 7, 'name' => $this->l('Julio')],
            ['id' => 8, 'name' => $this->l('Agosto')],
            ['id' => 9, 'name' => $this->l('Septiembre')],
            ['id' => 10, 'name' => $this->l('Octubre')],
            ['id' => 11, 'name' => $this->l('Noviembre')],
            ['id' => 12, 'name' => $this->l('Diciembre')],
        ];
    }

    public function getUrlDisplay($url, $row)
    {
        return Tools::substr($url, 0, 50) . '...';
    }

    public function getMonthDisplay($month, $row)
    {
        if ($month == -1) {
            return $this->l('Todos');
        }
        $months = $this->getMonthOptions();
        foreach ($months as $m) {
            if ($m['id'] == $month) {
                return $m['name'];
            }
        }
        return $month;
    }

    public function getDayOfWeekDisplay($day, $row)
    {
        if ($day == -1) {
            return $this->l('Todos');
        }
        $days = $this->getDayOfWeekOptions();
        foreach ($days as $d) {
            if ($d['id'] == $day) {
                return $d['name'];
            }
        }
        return $day;
    }

    public function processStatus()
    {
        $cron = new AccCronTaskModel((int)Tools::getValue($this->identifier));
        if (Validate::isLoadedObject($cron)) {
            $cron->active = !$cron->active;
            $cron->save();
        }
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function initToolbar()
    {
        parent::initToolbar();
        
        if ($this->display == 'list' || $this->display == '') {
            $this->toolbar_btn['export'] = [
                'href' => self::$currentIndex . '&action=generateCron&token=' . $this->token,
                'desc' => $this->l('Generar comando Cron'),
                'icon' => 'process-icon-cogs',
            ];
        }
    }
    
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        
        if ($this->display == 'list' || $this->display == '') {
            $this->page_header_toolbar_btn['export'] = [
                'href' => self::$currentIndex . '&action=generateCron&token=' . $this->token,
                'desc' => $this->l('Generar comando Cron'),
                'icon' => 'process-icon-cogs',
            ];
        }
    }

    public function generateCronCommand()
    {
        // Usar el token fijo del módulo (siempre el mismo, independiente de la sesión)
        $token = AccCronTask::getCronToken();
        
        $baseUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . 
                   $_SERVER['HTTP_HOST'] . 
                   __PS_BASE_URI__;
        
        $cronUrl = $baseUrl . 'modules/acccrontask/controllers/front/cron.php?token=' . $token;
        
        // El comando cron se ejecuta cada hora a los 5 minutos por defecto
        // Esto permite que el módulo verifique internamente qué tareas deben ejecutarse
        $cronCommand = '* * * * * curl -k "' . $cronUrl . '"';

        $this->context->smarty->assign([
            'cron_command' => $cronCommand,
            'cron_url' => $cronUrl,
            'link' => $this->context->link,
        ]);

        $this->content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'acccrontask/views/templates/admin/cron_command.tpl'
        );

        $this->context->smarty->assign('content', $this->content);
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $frequencyDay = (int)Tools::getValue('frequency_day');
            $hour = Tools::getValue('hour');
            $minute = (int)Tools::getValue('minute');
            $dayOfWeek = Tools::getValue('day_of_week');
            $dayOfMonth = Tools::getValue('day_of_month');
            $month = Tools::getValue('month');

            // Validar minuto (siempre requerido)
            if ($minute < 0 || $minute > 59) {
                $this->errors[] = $this->l('El minuto debe estar entre 0 y 59');
                return false;
            }

            // Validar según la frecuencia
            switch ($frequencyDay) {
                case 5: // Cada hora
                    // Solo minutos, hora se ignora
                    $hour = 0;
                    $dayOfWeek = -1;
                    $dayOfMonth = -1;
                    $month = -1;
                    break;
                    
                case 0: // Diario
                    // Hora y minutos requeridos
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->l('La hora debe estar entre 0 y 23');
                        return false;
                    }
                    $dayOfWeek = -1;
                    $dayOfMonth = -1;
                    $month = -1;
                    break;
                    
                case 1: // Semanal
                    // Día de la semana, hora y minutos requeridos
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->l('La hora debe estar entre 0 y 23');
                        return false;
                    }
                    if ($dayOfWeek == '' || $dayOfWeek == -1) {
                        $this->errors[] = $this->l('Debe seleccionar un día de la semana');
                        return false;
                    }
                    $dayOfMonth = -1;
                    $month = -1;
                    break;
                    
                case 2: // Mensual
                    // Hora, minutos y día del mes requeridos
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->l('La hora debe estar entre 0 y 23');
                        return false;
                    }
                    $dayOfMonth = (int)$dayOfMonth;
                    if ($dayOfMonth < 1 || $dayOfMonth > 31) {
                        $this->errors[] = $this->l('El día del mes debe estar entre 1 y 31');
                        return false;
                    }
                    $dayOfWeek = -1;
                    $month = -1;
                    break;
                    
                case 3: // Anual
                    // Día del mes, mes, hora y minutos requeridos
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->l('La hora debe estar entre 0 y 23');
                        return false;
                    }
                    $dayOfMonth = (int)$dayOfMonth;
                    if ($dayOfMonth < 1 || $dayOfMonth > 31) {
                        $this->errors[] = $this->l('El día del mes debe estar entre 1 y 31');
                        return false;
                    }
                    if ($month == '' || $month == -1) {
                        $this->errors[] = $this->l('Debe seleccionar un mes');
                        return false;
                    }
                    $dayOfWeek = -1;
                    break;
            }

            $_POST['hour'] = $hour;
            $_POST['minute'] = $minute;
            $_POST['day_of_week'] = (int)$dayOfWeek;
            $_POST['day_of_month'] = (int)$dayOfMonth;
            $_POST['month'] = (int)$month;
        }

        return parent::postProcess();
    }
}

