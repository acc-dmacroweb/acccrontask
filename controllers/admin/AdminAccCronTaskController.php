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
        $this->bootstrap = true;
        $this->table = 'acccrontask';
        $this->className = 'AccCronTaskModel';
        $this->identifier = 'id_acccrontask';
        $this->lang = false;
        $this->context = Context::getContext();

        parent::__construct();
        
        // Asegurar que el módulo esté disponible para traducciones (compatible con PS 9)
        if (!$this->module) {
            $this->module = Module::getInstanceByName('acccrontask');
        }
        
        // Asegurar que la columna cron_unix_style existe en la base de datos
        if ($this->module) {
            $this->module->addCronUnixStyleColumnIfNotExists();
        }

        $this->fields_list = [
            'name' => [
                'title' => $this->module->l('Nombre'),
                'width' => 'auto',
                'filter_key' => 'a!name',
            ],
            'url' => [
                'title' => $this->module->l('URL'),
                'width' => 'auto',
                'callback' => 'getUrlDisplay',
                'filter_key' => 'a!url',
            ],
            'frequency_day' => [
                'title' => $this->module->l('Frecuencia'),
                'width' => 'auto',
                'align' => 'center',
                'callback' => 'getFrequencyDisplay',
            ],
            'cron_unix_style' => [
                'title' => $this->module->l('Cron Unix Style'),
                'width' => 'auto',
                'align' => 'center',
                'callback' => 'getCronUnixStyleDisplay',
                'orderby' => false,
            ],
            'hour' => [
                'title' => $this->module->l('Hora'),
                'width' => 'auto',
                'align' => 'center',
            ],
            'minute' => [
                'title' => $this->module->l('Minuto'),
                'width' => 'auto',
                'align' => 'center',
            ],
            'month' => [
                'title' => $this->module->l('Mes'),
                'width' => 'auto',
                'align' => 'center',
                'callback' => 'getMonthDisplay',
            ],
            'day_of_week' => [
                'title' => $this->module->l('Día de la semana'),
                'width' => 'auto',
                'align' => 'center',
                'callback' => 'getDayOfWeekDisplay',
            ],
            'last_execution' => [
                'title' => $this->module->l('Última ejecución'),
                'width' => 'auto',
                'type' => 'datetime',
                'align' => 'center',
            ],
            'active' => [
                'title' => $this->module->l('Activo'),
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
                'text' => $this->module->l('Eliminar seleccionados'),
                'icon' => 'icon-trash',
                'confirm' => $this->module->l('¿Eliminar los elementos seleccionados?'),
            ],
        ];
        
        // Configurar acción personalizada "ejecutar ahora"
        $this->actions = ['edit', 'delete', 'executeNow'];
    }

    public function initContent()
    {
        if (Tools::getValue('action') == 'generateCron') {
            $this->generateCronCommand();
            $this->context->smarty->assign('content', $this->content);
            return;
        }
        
        // Registrar CSS y JS para el listado
        if ($this->display == 'list' || $this->display == '') {
            $this->addCSS($this->module->getPathUri() . 'views/css/list.css');
            $this->addJS($this->module->getPathUri() . 'views/js/list.js');
        }
        
        // Ya no necesitamos regenerar token, usamos el de AdminModules

        parent::initContent();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('executeNow');

        $this->toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->module->l('Añadir nueva tarea'),
        ];

        $this->toolbar_btn['export'] = [
            'href' => self::$currentIndex . '&action=generateCron&token=' . $this->token,
            'desc' => $this->module->l('Generar comando Cron'),
            'icon' => 'process-icon-cogs',
        ];

        $list = parent::renderList();
        
        $this->context->smarty->assign([
            'list' => $list,
            'token' => $this->token,
            'current_index' => self::$currentIndex,
            'controller' => 'AdminAccCronTask',
            'module_dir' => $this->module->getPathUri(),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'acccrontask/views/templates/admin/list_override.tpl'
        );
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Tarea Cron'),
                'icon' => 'icon-cog',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Nombre'),
                    'name' => 'name',
                    'required' => true,
                    'maxlength' => 255,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('URL'),
                    'name' => 'url',
                    'required' => true,
                    'desc' => $this->module->l('URL completa a ejecutar'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Frecuencia'),
                    'name' => 'frequency_day',
                    'required' => true,
                    'options' => [
                        'query' => [
                            ['id' => 5, 'name' => $this->module->l('Cada hora')],
                            ['id' => 0, 'name' => $this->module->l('Diario')],
                            ['id' => 1, 'name' => $this->module->l('Semanal')],
                            ['id' => 2, 'name' => $this->module->l('Mensual')],
                            ['id' => 3, 'name' => $this->module->l('Anual')],
                            ['id' => 6, 'name' => $this->module->l('Cron Unix Style')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 'frequency-selector',
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Minuto'),
                    'name' => 'minute',
                    'required' => true,
                    'suffix' => $this->module->l('(0-59)'),
                    'class' => 'fixed-width-sm field-minute',
                    'desc' => $this->module->l('Minuto de ejecución (0-59)'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Hora'),
                    'name' => 'hour',
                    'required' => false,
                    'suffix' => $this->module->l('(0-23)'),
                    'class' => 'fixed-width-sm field-hour',
                    'desc' => $this->module->l('Hora de ejecución (0-23)'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Día de la semana'),
                    'name' => 'day_of_week',
                    'required' => false,
                    'options' => [
                        'query' => [
                            ['id' => 0, 'name' => $this->module->l('Domingo')],
                            ['id' => 1, 'name' => $this->module->l('Lunes')],
                            ['id' => 2, 'name' => $this->module->l('Martes')],
                            ['id' => 3, 'name' => $this->module->l('Miércoles')],
                            ['id' => 4, 'name' => $this->module->l('Jueves')],
                            ['id' => 5, 'name' => $this->module->l('Viernes')],
                            ['id' => 6, 'name' => $this->module->l('Sábado')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 'field-day-week',
                    'desc' => $this->module->l('Día de la semana para ejecución semanal'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Día del mes'),
                    'name' => 'day_of_month',
                    'required' => false,
                    'suffix' => $this->module->l('(1-31)'),
                    'class' => 'fixed-width-sm field-day-month',
                    'desc' => $this->module->l('Día del mes para ejecución mensual o anual'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Mes'),
                    'name' => 'month',
                    'required' => false,
                    'options' => [
                        'query' => [
                            ['id' => 1, 'name' => $this->module->l('Enero')],
                            ['id' => 2, 'name' => $this->module->l('Febrero')],
                            ['id' => 3, 'name' => $this->module->l('Marzo')],
                            ['id' => 4, 'name' => $this->module->l('Abril')],
                            ['id' => 5, 'name' => $this->module->l('Mayo')],
                            ['id' => 6, 'name' => $this->module->l('Junio')],
                            ['id' => 7, 'name' => $this->module->l('Julio')],
                            ['id' => 8, 'name' => $this->module->l('Agosto')],
                            ['id' => 9, 'name' => $this->module->l('Septiembre')],
                            ['id' => 10, 'name' => $this->module->l('Octubre')],
                            ['id' => 11, 'name' => $this->module->l('Noviembre')],
                            ['id' => 12, 'name' => $this->module->l('Diciembre')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 'field-month',
                    'desc' => $this->module->l('Mes para ejecución anual'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Cron Unix Style'),
                    'name' => 'cron_unix_style',
                    'required' => false,
                    'desc' => $this->module->l('Formato cron unix style: minuto hora día_mes mes día_semana. Ejemplos: "0 2 * * *" (diario a las 2:00), "*/5 * * * *" (cada 5 minutos), "0 0 1 * *" (mensual el día 1)'),
                    'class' => 'field-cron-unix-style',
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Activo'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->l('Sí'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Guardar'),
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
            ['id' => -1, 'name' => $this->module->l('Todos')],
            ['id' => 0, 'name' => $this->module->l('Domingo')],
            ['id' => 1, 'name' => $this->module->l('Lunes')],
            ['id' => 2, 'name' => $this->module->l('Martes')],
            ['id' => 3, 'name' => $this->module->l('Miércoles')],
            ['id' => 4, 'name' => $this->module->l('Jueves')],
            ['id' => 5, 'name' => $this->module->l('Viernes')],
            ['id' => 6, 'name' => $this->module->l('Sábado')],
        ];
    }

    protected function getMonthOptions()
    {
        return [
            ['id' => -1, 'name' => $this->module->l('Todos')],
            ['id' => 1, 'name' => $this->module->l('Enero')],
            ['id' => 2, 'name' => $this->module->l('Febrero')],
            ['id' => 3, 'name' => $this->module->l('Marzo')],
            ['id' => 4, 'name' => $this->module->l('Abril')],
            ['id' => 5, 'name' => $this->module->l('Mayo')],
            ['id' => 6, 'name' => $this->module->l('Junio')],
            ['id' => 7, 'name' => $this->module->l('Julio')],
            ['id' => 8, 'name' => $this->module->l('Agosto')],
            ['id' => 9, 'name' => $this->module->l('Septiembre')],
            ['id' => 10, 'name' => $this->module->l('Octubre')],
            ['id' => 11, 'name' => $this->module->l('Noviembre')],
            ['id' => 12, 'name' => $this->module->l('Diciembre')],
        ];
    }

    public function getUrlDisplay($url, $row)
    {
        return Tools::substr($url, 0, 50) . '...';
    }

    public function getMonthDisplay($month, $row)
    {
        if ($month == -1) {
            return $this->module->l('Todos');
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
            return $this->module->l('Todos');
        }
        $days = $this->getDayOfWeekOptions();
        foreach ($days as $d) {
            if ($d['id'] == $day) {
                return $d['name'];
            }
        }
        return $day;
    }

    public function getFrequencyDisplay($frequency, $row)
    {
        $frequencies = [
            5 => $this->module->l('Cada hora'),
            0 => $this->module->l('Diario'),
            1 => $this->module->l('Semanal'),
            2 => $this->module->l('Mensual'),
            3 => $this->module->l('Anual'),
            6 => $this->module->l('Cron Unix Style'),
        ];
        
        return isset($frequencies[$frequency]) ? $frequencies[$frequency] : $frequency;
    }

    public function getCronUnixStyleDisplay($cronStyle, $row)
    {
        // Solo mostrar si frequency_day = 6 (Cron Unix Style)
        $frequencyDay = isset($row['frequency_day']) ? (int)$row['frequency_day'] : 0;
        if ($frequencyDay != 6) {
            return '-';
        }

        if (empty($cronStyle)) {
            return '-';
        }

        $parts = preg_split('/\s+/', trim($cronStyle));
        if (count($parts) != 5) {
            return $cronStyle;
        }

        $minute = $parts[0];
        $hour = $parts[1];
        $dayOfMonth = $parts[2];
        $month = $parts[3];
        $dayOfWeek = $parts[4];

        $description = '';

        // Días de la semana traducidos
        $days = [
            $this->module->l('Domingo'),
            $this->module->l('Lunes'),
            $this->module->l('Martes'),
            $this->module->l('Miércoles'),
            $this->module->l('Jueves'),
            $this->module->l('Viernes'),
            $this->module->l('Sábado')
        ];

        // Meses traducidos
        $months = [
            '',
            $this->module->l('Enero'),
            $this->module->l('Febrero'),
            $this->module->l('Marzo'),
            $this->module->l('Abril'),
            $this->module->l('Mayo'),
            $this->module->l('Junio'),
            $this->module->l('Julio'),
            $this->module->l('Agosto'),
            $this->module->l('Septiembre'),
            $this->module->l('Octubre'),
            $this->module->l('Noviembre'),
            $this->module->l('Diciembre')
        ];

        // Interpretar minuto
        if ($minute === '*') {
            $minuteDesc = $this->module->l('cada minuto');
        } elseif (preg_match('/^\*\/(\d+)$/', $minute, $matches)) {
            $interval = (int)$matches[1];
            if ($interval == 1) {
                $minuteDesc = $this->module->l('cada minuto');
            } else {
                $minuteDesc = sprintf($this->module->l('cada %d minutos'), $interval);
            }
        } elseif (strpos($minute, ',') !== false) {
            $minuteDesc = $this->module->l('minutos') . ' ' . str_replace(',', ', ', $minute);
        } elseif (strpos($minute, '-') !== false) {
            $minuteDesc = $this->module->l('minutos') . ' ' . $minute;
        } else {
            $minuteDesc = sprintf($this->module->l('minuto %s'), $minute);
        }

        // Interpretar hora
        if ($hour === '*') {
            $hourDesc = '';
        } elseif (preg_match('/^\*\/(\d+)$/', $hour, $matches)) {
            $interval = (int)$matches[1];
            $hourDesc = sprintf($this->module->l('cada %d horas'), $interval);
        } elseif (strpos($hour, ',') !== false) {
            $hourDesc = $this->module->l('horas') . ' ' . str_replace(',', ', ', $hour);
        } elseif (strpos($hour, '-') !== false) {
            $hourDesc = $this->module->l('horas') . ' ' . $hour;
        } else {
            $hourDesc = sprintf($this->module->l('hora %s'), $hour);
        }

        // Construir descripción
        if ($minute === '*' && $hour === '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek === '*') {
            $description = $this->module->l('Cada minuto');
        } elseif ($minute !== '*' && $hour === '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek === '*') {
            // Solo minuto específico
            if (preg_match('/^\*\/(\d+)$/', $minute, $matches)) {
                if ($matches[1] == 1) {
                    $description = $this->module->l('Cada minuto');
                } else {
                    $description = sprintf($this->module->l('Cada %d minutos'), (int)$matches[1]);
                }
            } else {
                $description = sprintf($this->module->l('Cada hora en el minuto %s'), $minute);
            }
        } elseif ($minute !== '*' && $hour !== '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek === '*') {
            // Hora y minuto específicos - diario
            $description = sprintf($this->module->l('Diario a las %s:%s'), $hour, $minute);
        } elseif ($minute !== '*' && $hour !== '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek !== '*') {
            // Semanal
            if (strpos($dayOfWeek, ',') !== false) {
                $dayNums = explode(',', $dayOfWeek);
                $dayNames = [];
                foreach ($dayNums as $d) {
                    $dayIndex = (int)trim($d);
                    if (isset($days[$dayIndex])) {
                        $dayNames[] = $days[$dayIndex];
                    }
                }
                $description = sprintf(
                    $this->module->l('Semanal: %s a las %s:%s'),
                    implode(', ', $dayNames),
                    $hour,
                    $minute
                );
            } else {
                $dayIndex = (int)$dayOfWeek;
                $dayName = isset($days[$dayIndex]) ? $days[$dayIndex] : sprintf($this->module->l('día %s'), $dayOfWeek);
                $description = sprintf($this->module->l('Cada %s a las %s:%s'), $dayName, $hour, $minute);
            }
        } elseif ($minute !== '*' && $hour !== '*' && $dayOfMonth !== '*' && $month === '*' && $dayOfWeek === '*') {
            // Mensual
            if ($dayOfMonth === '*') {
                $description = sprintf($this->module->l('Mensual a las %s:%s'), $hour, $minute);
            } else {
                $description = sprintf($this->module->l('Día %s de cada mes a las %s:%s'), $dayOfMonth, $hour, $minute);
            }
        } elseif ($minute !== '*' && $hour !== '*' && $dayOfMonth !== '*' && $month !== '*' && $dayOfWeek === '*') {
            // Anual
            $monthIndex = (int)$month;
            $monthName = isset($months[$monthIndex]) ? $months[$monthIndex] : sprintf($this->module->l('mes %s'), $month);
            $description = sprintf($this->module->l('Anual: día %s de %s a las %s:%s'), $dayOfMonth, $monthName, $hour, $minute);
        } else {
            // Formato complejo - mostrar resumen
            $description = $minuteDesc;
            if ($hourDesc) {
                $description .= ', ' . $hourDesc;
            }
            if ($dayOfMonth !== '*') {
                $description .= ', ' . sprintf($this->module->l('día %s'), $dayOfMonth);
            }
            if ($month !== '*') {
                $description .= ', ' . sprintf($this->module->l('mes %s'), $month);
            }
            if ($dayOfWeek !== '*') {
                $description .= ', ' . sprintf($this->module->l('día semana %s'), $dayOfWeek);
            }
        }

        return '<span>' . $description . '</span>';
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

    public function processExecuteNow()
    {
        $cron = new AccCronTaskModel((int)Tools::getValue($this->identifier));
        if (Validate::isLoadedObject($cron)) {
            $result = $cron->executeNow();
            if ($result) {
                $this->confirmations[] = $this->module->l('Tarea ejecutada correctamente');
            } else {
                $this->errors[] = $this->module->l('Error al ejecutar la tarea');
            }
        } else {
            $this->errors[] = $this->module->l('Tarea no encontrada');
        }
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    /**
     * Procesa la eliminación de una tarea cron
     * Asegura que el objeto esté cargado antes de eliminar
     */
    public function processDelete()
    {
        // Asegurar que el objeto esté cargado antes de eliminar
        if (!Validate::isLoadedObject($this->object)) {
            $id = (int)Tools::getValue($this->identifier);
            if ($id) {
                $this->object = new $this->className($id);
            }
        }
        
        // Llamar al método padre que maneja la eliminación
        return parent::processDelete();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        
        if ($this->display == 'list' || $this->display == '') {
            $this->toolbar_btn['export'] = [
                'href' => self::$currentIndex . '&action=generateCron&token=' . $this->token,
                'desc' => $this->module->l('Generar comando Cron'),
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
                'desc' => $this->module->l('Generar comando Cron'),
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
        // Verificar todos los posibles submits (añadir, actualizar, guardar y continuar, etc.)
        $isSubmitting = Tools::isSubmit('submitAdd' . $this->table) || 
                       Tools::isSubmit('submitAdd' . $this->table . 'AndStay') ||
                       isset($_POST['submitAdd' . $this->table]) ||
                       isset($_POST['submitAdd' . $this->table . 'AndStay']);
        
        if ($isSubmitting) {
            $frequencyDay = (int)Tools::getValue('frequency_day');
            $hour = Tools::getValue('hour');
            $minute = (int)Tools::getValue('minute');
            $dayOfWeek = Tools::getValue('day_of_week');
            $dayOfMonth = Tools::getValue('day_of_month');
            $month = Tools::getValue('month');
            $cronUnixStyle = trim(Tools::getValue('cron_unix_style'));

            // Validar minuto (siempre requerido, excepto para Cron Unix Style)
            if ($frequencyDay != 6 && ($minute < 0 || $minute > 59)) {
                $this->errors[] = $this->module->l('El minuto debe estar entre 0 y 59');
                return false;
            }

            // Validar según la frecuencia
            switch ($frequencyDay) {
                case 6: // Cron Unix Style
                    // Solo cron_unix_style es requerido
                    if (empty($cronUnixStyle)) {
                        $this->errors[] = $this->module->l('Debe ingresar un formato cron unix style');
                        return false;
                    }
                    if (!AccCronTaskModel::validateCronUnixStyle($cronUnixStyle)) {
                        $this->errors[] = $this->module->l('El formato cron unix style no es válido. Debe ser: minuto hora día_mes mes día_semana. Ejemplo: "0 2 * * *"');
                        return false;
                    }
                    // Limpiar otros campos
                    $hour = 0;
                    $minute = 0;
                    $dayOfWeek = -1;
                    $dayOfMonth = -1;
                    $month = -1;
                    break;
                    
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
                        $this->errors[] = $this->module->l('La hora debe estar entre 0 y 23');
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
                        $this->errors[] = $this->module->l('La hora debe estar entre 0 y 23');
                        return false;
                    }
                    if ($dayOfWeek == '' || $dayOfWeek == -1) {
                        $this->errors[] = $this->module->l('Debe seleccionar un día de la semana');
                        return false;
                    }
                    $dayOfMonth = -1;
                    $month = -1;
                    break;
                    
                case 2: // Mensual
                    // Hora, minutos y día del mes requeridos
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->module->l('La hora debe estar entre 0 y 23');
                        return false;
                    }
                    $dayOfMonth = (int)$dayOfMonth;
                    if ($dayOfMonth < 1 || $dayOfMonth > 31) {
                        $this->errors[] = $this->module->l('El día del mes debe estar entre 1 y 31');
                        return false;
                    }
                    $dayOfWeek = -1;
                    $month = -1;
                    break;
                    
                case 3: // Anual
                    // Día del mes, mes, hora y minutos requeridos
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->module->l('La hora debe estar entre 0 y 23');
                        return false;
                    }
                    $dayOfMonth = (int)$dayOfMonth;
                    if ($dayOfMonth < 1 || $dayOfMonth > 31) {
                        $this->errors[] = $this->module->l('El día del mes debe estar entre 1 y 31');
                        return false;
                    }
                    if ($month == '' || $month == -1) {
                        $this->errors[] = $this->module->l('Debe seleccionar un mes');
                        return false;
                    }
                    $dayOfWeek = -1;
                    break;
            }
            
            // Guardar valores
            $_POST['hour'] = $hour;
            $_POST['minute'] = $minute;
            $_POST['day_of_week'] = (int)$dayOfWeek;
            $_POST['day_of_month'] = (int)$dayOfMonth;
            $_POST['month'] = (int)$month;
            
            // SIEMPRE generar cron_unix_style basándose en los campos individuales seleccionados
            // Crear un objeto temporal para generar el cron_unix_style
            $tempTask = new AccCronTaskModel();
            $tempTask->frequency_day = $frequencyDay;
            $tempTask->hour = (int)$hour;
            $tempTask->minute = (int)$minute;
            $tempTask->day_of_week = (int)$dayOfWeek;
            $tempTask->day_of_month = (int)$dayOfMonth;
            $tempTask->month = (int)$month;
            
            // Si es frecuencia 6 (Cron Unix Style) y el usuario ingresó un valor válido, usarlo temporalmente
            // para que generateCronUnixStyle() pueda usarlo si no hay campos individuales válidos
            if ($frequencyDay == 6 && !empty($cronUnixStyle) && AccCronTaskModel::validateCronUnixStyle($cronUnixStyle)) {
                $tempTask->cron_unix_style = $cronUnixStyle;
            }
            
            // SIEMPRE generar el cron_unix_style basándose en los campos individuales
            $generatedCron = $tempTask->generateCronUnixStyle();
            $_POST['cron_unix_style'] = $generatedCron;
        }

        // Procesar con el método padre (ahora incluirá el cron_unix_style actualizado)
        return parent::postProcess();
    }
}

