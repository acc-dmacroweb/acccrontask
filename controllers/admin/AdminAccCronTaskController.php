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
        
        // Ensure module is available for translations (compatible with PS 9)
        if (!$this->module) {
            $this->module = Module::getInstanceByName('acccrontask');
        }
        
        // Ensure cron_unix_style column exists in database
        if ($this->module) {
            $this->module->addCronUnixStyleColumnIfNotExists();
        }

        $this->fields_list = [
            'name' => [
                'title' => $this->module->l('Name'),
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
                'title' => $this->module->l('Frequency'),
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
                'title' => $this->module->l('Hour'),
                'width' => 'auto',
                'align' => 'center',
            ],
            'minute' => [
                'title' => $this->module->l('Minute'),
                'width' => 'auto',
                'align' => 'center',
            ],
            'month' => [
                'title' => $this->module->l('Month'),
                'width' => 'auto',
                'align' => 'center',
                'callback' => 'getMonthDisplay',
            ],
            'day_of_week' => [
                'title' => $this->module->l('Day of week'),
                'width' => 'auto',
                'align' => 'center',
                'callback' => 'getDayOfWeekDisplay',
            ],
            'last_execution' => [
                'title' => $this->module->l('Last execution'),
                'width' => 'auto',
                'type' => 'datetime',
                'align' => 'center',
            ],
            'active' => [
                'title' => $this->module->l('Active'),
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
                'text' => $this->module->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->module->l('Delete selected items?'),
            ],
        ];
        
        // Configure custom action "execute now"
        $this->actions = ['edit', 'delete', 'executeNow'];
    }

    public function initContent()
    {
        if (Tools::getValue('action') == 'generateCron') {
            $this->generateCronCommand();
            $this->context->smarty->assign('content', $this->content);
            return;
        }
        
<<<<<<< HEAD
        // Register CSS and JS for listing
=======
        // Registrar CSS y JS para el listado
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
        if ($this->display == 'list' || $this->display == '') {
            $this->addCSS($this->module->getPathUri() . 'views/css/list.css');
            $this->addJS($this->module->getPathUri() . 'views/js/list.js');
        }
        
<<<<<<< HEAD
        // We no longer need to regenerate token, we use AdminModules token
=======
        // Ya no necesitamos regenerar token, usamos el de AdminModules
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0

        parent::initContent();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('executeNow');

        $this->toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->module->l('Add new task'),
        ];

        $this->toolbar_btn['export'] = [
            'href' => self::$currentIndex . '&action=generateCron&token=' . $this->token,
            'desc' => $this->module->l('Generate Cron command'),
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
                'title' => $this->module->l('Cron Task'),
                'icon' => 'icon-cog',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Name'),
                    'name' => 'name',
                    'required' => true,
                    'maxlength' => 255,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('URL'),
                    'name' => 'url',
                    'required' => true,
                    'desc' => $this->module->l('Complete URL to execute'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Frequency'),
                    'name' => 'frequency_day',
                    'required' => true,
                    'options' => [
                        'query' => [
                            ['id' => 5, 'name' => $this->module->l('Every hour')],
                            ['id' => 0, 'name' => $this->module->l('Daily')],
                            ['id' => 1, 'name' => $this->module->l('Weekly')],
                            ['id' => 2, 'name' => $this->module->l('Monthly')],
                            ['id' => 3, 'name' => $this->module->l('Yearly')],
                            ['id' => 6, 'name' => $this->module->l('Cron Unix Style')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 'frequency-selector',
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Minute'),
                    'name' => 'minute',
                    'required' => true,
                    'suffix' => $this->module->l('(0-59)'),
                    'class' => 'fixed-width-sm field-minute',
                    'desc' => $this->module->l('Execution minute (0-59)'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Hour'),
                    'name' => 'hour',
                    'required' => false,
                    'suffix' => $this->module->l('(0-23)'),
                    'class' => 'fixed-width-sm field-hour',
                    'desc' => $this->module->l('Execution hour (0-23)'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Day of week'),
                    'name' => 'day_of_week',
                    'required' => false,
                    'options' => [
                        'query' => [
                            ['id' => 0, 'name' => $this->module->l('Sunday')],
                            ['id' => 1, 'name' => $this->module->l('Monday')],
                            ['id' => 2, 'name' => $this->module->l('Tuesday')],
                            ['id' => 3, 'name' => $this->module->l('Wednesday')],
                            ['id' => 4, 'name' => $this->module->l('Thursday')],
                            ['id' => 5, 'name' => $this->module->l('Friday')],
                            ['id' => 6, 'name' => $this->module->l('Saturday')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 'field-day-week',
                    'desc' => $this->module->l('Day of week for weekly execution'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Day of month'),
                    'name' => 'day_of_month',
                    'required' => false,
                    'suffix' => $this->module->l('(1-31)'),
                    'class' => 'fixed-width-sm field-day-month',
                    'desc' => $this->module->l('Day of month for monthly or yearly execution'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Month'),
                    'name' => 'month',
                    'required' => false,
                    'options' => [
                        'query' => [
                            ['id' => 1, 'name' => $this->module->l('January')],
                            ['id' => 2, 'name' => $this->module->l('February')],
                            ['id' => 3, 'name' => $this->module->l('March')],
                            ['id' => 4, 'name' => $this->module->l('April')],
                            ['id' => 5, 'name' => $this->module->l('May')],
                            ['id' => 6, 'name' => $this->module->l('June')],
                            ['id' => 7, 'name' => $this->module->l('July')],
                            ['id' => 8, 'name' => $this->module->l('August')],
                            ['id' => 9, 'name' => $this->module->l('September')],
                            ['id' => 10, 'name' => $this->module->l('October')],
                            ['id' => 11, 'name' => $this->module->l('November')],
                            ['id' => 12, 'name' => $this->module->l('December')],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 'field-month',
                    'desc' => $this->module->l('Month for yearly execution'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Cron Unix Style'),
                    'name' => 'cron_unix_style',
                    'required' => false,
                    'desc' => $this->module->l('Unix style cron format: minute hour day_of_month month day_of_week. Examples: "0 2 * * *" (daily at 2:00), "*/5 * * * *" (every 5 minutes), "0 0 1 * *" (monthly on day 1)'),
                    'class' => 'field-cron-unix-style',
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Active'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
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
                'title' => $this->module->l('Save'),
            ],
        ];

        $form = parent::renderForm();
        
        // Add JavaScript to show/hide fields according to frequency
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
            ['id' => -1, 'name' => $this->module->l('All')],
            ['id' => 0, 'name' => $this->module->l('Sunday')],
            ['id' => 1, 'name' => $this->module->l('Monday')],
            ['id' => 2, 'name' => $this->module->l('Tuesday')],
            ['id' => 3, 'name' => $this->module->l('Wednesday')],
            ['id' => 4, 'name' => $this->module->l('Thursday')],
            ['id' => 5, 'name' => $this->module->l('Friday')],
            ['id' => 6, 'name' => $this->module->l('Saturday')],
        ];
    }

    protected function getMonthOptions()
    {
        return [
            ['id' => -1, 'name' => $this->module->l('All')],
            ['id' => 1, 'name' => $this->module->l('January')],
            ['id' => 2, 'name' => $this->module->l('February')],
            ['id' => 3, 'name' => $this->module->l('March')],
            ['id' => 4, 'name' => $this->module->l('April')],
            ['id' => 5, 'name' => $this->module->l('May')],
            ['id' => 6, 'name' => $this->module->l('June')],
            ['id' => 7, 'name' => $this->module->l('July')],
            ['id' => 8, 'name' => $this->module->l('August')],
            ['id' => 9, 'name' => $this->module->l('September')],
            ['id' => 10, 'name' => $this->module->l('October')],
            ['id' => 11, 'name' => $this->module->l('November')],
            ['id' => 12, 'name' => $this->module->l('December')],
        ];
    }

    public function getUrlDisplay($url, $row)
    {
        return Tools::substr($url, 0, 50) . '...';
    }

    public function getMonthDisplay($month, $row)
    {
        if ($month == -1) {
            return $this->module->l('All');
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
            return $this->module->l('All');
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
            5 => $this->module->l('Every hour'),
            0 => $this->module->l('Daily'),
            1 => $this->module->l('Weekly'),
            2 => $this->module->l('Monthly'),
            3 => $this->module->l('Yearly'),
            6 => $this->module->l('Cron Unix Style'),
        ];
        
        return isset($frequencies[$frequency]) ? $frequencies[$frequency] : $frequency;
    }

    public function getCronUnixStyleDisplay($cronStyle, $row)
    {
        // Only show if frequency_day = 6 (Cron Unix Style)
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

        // Translated days of week
        $days = [
            $this->module->l('Sunday'),
            $this->module->l('Monday'),
            $this->module->l('Tuesday'),
            $this->module->l('Wednesday'),
            $this->module->l('Thursday'),
            $this->module->l('Friday'),
            $this->module->l('Saturday')
        ];

        // Translated months
        $months = [
            '',
            $this->module->l('January'),
            $this->module->l('February'),
            $this->module->l('March'),
            $this->module->l('April'),
            $this->module->l('May'),
            $this->module->l('June'),
            $this->module->l('July'),
            $this->module->l('August'),
            $this->module->l('September'),
            $this->module->l('October'),
            $this->module->l('November'),
            $this->module->l('December')
        ];

        // Interpret minute
        if ($minute === '*') {
            $minuteDesc = $this->module->l('every minute');
        } elseif (preg_match('/^\*\/(\d+)$/', $minute, $matches)) {
            $interval = (int)$matches[1];
            if ($interval == 1) {
                $minuteDesc = $this->module->l('every minute');
            } else {
                $minuteDesc = sprintf($this->module->l('every %d minutes'), $interval);
            }
        } elseif (strpos($minute, ',') !== false) {
            $minuteDesc = $this->module->l('minutes') . ' ' . str_replace(',', ', ', $minute);
        } elseif (strpos($minute, '-') !== false) {
            $minuteDesc = $this->module->l('minutes') . ' ' . $minute;
        } else {
            $minuteDesc = sprintf($this->module->l('minute %s'), $minute);
        }

        // Interpret hour
        if ($hour === '*') {
            $hourDesc = '';
        } elseif (preg_match('/^\*\/(\d+)$/', $hour, $matches)) {
            $interval = (int)$matches[1];
            $hourDesc = sprintf($this->module->l('every %d hours'), $interval);
        } elseif (strpos($hour, ',') !== false) {
            $hourDesc = $this->module->l('hours') . ' ' . str_replace(',', ', ', $hour);
        } elseif (strpos($hour, '-') !== false) {
            $hourDesc = $this->module->l('hours') . ' ' . $hour;
        } else {
            $hourDesc = sprintf($this->module->l('hour %s'), $hour);
        }

        // Build description
        if ($minute === '*' && $hour === '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek === '*') {
            $description = $this->module->l('Every minute');
        } elseif ($minute !== '*' && $hour === '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek === '*') {
            // Only specific minute
            if (preg_match('/^\*\/(\d+)$/', $minute, $matches)) {
                if ($matches[1] == 1) {
                    $description = $this->module->l('Every minute');
                } else {
                    $description = sprintf($this->module->l('Every %d minutes'), (int)$matches[1]);
                }
            } else {
                $description = sprintf($this->module->l('Every hour at minute %s'), $minute);
            }
        } elseif ($minute !== '*' && $hour !== '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek === '*') {
            // Specific hour and minute - daily
            $description = sprintf($this->module->l('Daily at %s:%s'), $hour, $minute);
        } elseif ($minute !== '*' && $hour !== '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek !== '*') {
            // Weekly
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
                    $this->module->l('Weekly: %s at %s:%s'),
                    implode(', ', $dayNames),
                    $hour,
                    $minute
                );
            } else {
                $dayIndex = (int)$dayOfWeek;
                $dayName = isset($days[$dayIndex]) ? $days[$dayIndex] : sprintf($this->module->l('day %s'), $dayOfWeek);
                $description = sprintf($this->module->l('Every %s at %s:%s'), $dayName, $hour, $minute);
            }
        } elseif ($minute !== '*' && $hour !== '*' && $dayOfMonth !== '*' && $month === '*' && $dayOfWeek === '*') {
            // Monthly
            if ($dayOfMonth === '*') {
                $description = sprintf($this->module->l('Monthly at %s:%s'), $hour, $minute);
            } else {
                $description = sprintf($this->module->l('Day %s of every month at %s:%s'), $dayOfMonth, $hour, $minute);
            }
        } elseif ($minute !== '*' && $hour !== '*' && $dayOfMonth !== '*' && $month !== '*' && $dayOfWeek === '*') {
            // Yearly
            $monthIndex = (int)$month;
            $monthName = isset($months[$monthIndex]) ? $months[$monthIndex] : sprintf($this->module->l('month %s'), $month);
            $description = sprintf($this->module->l('Yearly: day %s of %s at %s:%s'), $dayOfMonth, $monthName, $hour, $minute);
        } else {
            // Complex format - show summary
            $description = $minuteDesc;
            if ($hourDesc) {
                $description .= ', ' . $hourDesc;
            }
            if ($dayOfMonth !== '*') {
                $description .= ', ' . sprintf($this->module->l('day %s'), $dayOfMonth);
            }
            if ($month !== '*') {
                $description .= ', ' . sprintf($this->module->l('month %s'), $month);
            }
            if ($dayOfWeek !== '*') {
                $description .= ', ' . sprintf($this->module->l('weekday %s'), $dayOfWeek);
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
                $this->confirmations[] = $this->module->l('Task executed successfully');
            } else {
                $this->errors[] = $this->module->l('Error executing task');
            }
        } else {
            $this->errors[] = $this->module->l('Task not found');
        }
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    /**
     * Processes cron task deletion
     * Ensures object is loaded before deleting
     */
    public function processDelete()
    {
        // Ensure object is loaded before deleting
        if (!Validate::isLoadedObject($this->object)) {
            $id = (int)Tools::getValue($this->identifier);
            if ($id) {
                $this->object = new $this->className($id);
            }
        }
        
        // Call parent method that handles deletion
        return parent::processDelete();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        
        if ($this->display == 'list' || $this->display == '') {
            $this->toolbar_btn['export'] = [
                'href' => self::$currentIndex . '&action=generateCron&token=' . $this->token,
                'desc' => $this->module->l('Generate Cron command'),
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
                'desc' => $this->module->l('Generate Cron command'),
                'icon' => 'process-icon-cogs',
            ];
        }
    }

    public function generateCronCommand()
    {
        // Use fixed module token (always the same, independent of session)
        $token = AccCronTask::getCronToken();
        
        $baseUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . 
                   $_SERVER['HTTP_HOST'] . 
                   __PS_BASE_URI__;
        
        $cronUrl = $baseUrl . 'modules/acccrontask/controllers/front/cron.php?token=' . $token;
        
        // The cron command runs every minute by default
        // This allows the module to internally check which tasks should be executed
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
        // Check all possible submits (add, update, save and continue, etc.)
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

            // Validate minute (always required, except for Cron Unix Style)
            if ($frequencyDay != 6 && ($minute < 0 || $minute > 59)) {
                $this->errors[] = $this->module->l('Minute must be between 0 and 59');
                return false;
            }

            // Validate according to frequency
            switch ($frequencyDay) {
                case 6: // Cron Unix Style
                    // Only cron_unix_style is required
                    if (empty($cronUnixStyle)) {
                        $this->errors[] = $this->module->l('You must enter a cron unix style format');
                        return false;
                    }
                    if (!AccCronTaskModel::validateCronUnixStyle($cronUnixStyle)) {
                        $this->errors[] = $this->module->l('The cron unix style format is not valid. It must be: minute hour day_of_month month day_of_week. Example: "0 2 * * *"');
                        return false;
                    }
                    // Clear other fields
                    $hour = 0;
                    $minute = 0;
                    $dayOfWeek = -1;
                    $dayOfMonth = -1;
                    $month = -1;
                    break;
                    
                case 5: // Every hour
                    // Only minutes, hour is ignored
                    $hour = 0;
                    $dayOfWeek = -1;
                    $dayOfMonth = -1;
                    $month = -1;
                    break;
                    
                case 0: // Daily
                    // Hour and minutes required
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->module->l('Hour must be between 0 and 23');
                        return false;
                    }
                    $dayOfWeek = -1;
                    $dayOfMonth = -1;
                    $month = -1;
                    break;
                    
                case 1: // Weekly
                    // Day of week, hour and minutes required
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->module->l('Hour must be between 0 and 23');
                        return false;
                    }
                    if ($dayOfWeek == '' || $dayOfWeek == -1) {
                        $this->errors[] = $this->module->l('You must select a day of the week');
                        return false;
                    }
                    $dayOfMonth = -1;
                    $month = -1;
                    break;
                    
                case 2: // Monthly
                    // Hour, minutes and day of month required
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->module->l('Hour must be between 0 and 23');
                        return false;
                    }
                    $dayOfMonth = (int)$dayOfMonth;
                    if ($dayOfMonth < 1 || $dayOfMonth > 31) {
                        $this->errors[] = $this->module->l('Day of month must be between 1 and 31');
                        return false;
                    }
                    $dayOfWeek = -1;
                    $month = -1;
                    break;
                    
                case 3: // Yearly
                    // Day of month, month, hour and minutes required
                    $hour = (int)$hour;
                    if ($hour < 0 || $hour > 23) {
                        $this->errors[] = $this->module->l('Hour must be between 0 and 23');
                        return false;
                    }
                    $dayOfMonth = (int)$dayOfMonth;
                    if ($dayOfMonth < 1 || $dayOfMonth > 31) {
                        $this->errors[] = $this->module->l('Day of month must be between 1 and 31');
                        return false;
                    }
                    if ($month == '' || $month == -1) {
                        $this->errors[] = $this->module->l('You must select a month');
                        return false;
                    }
                    $dayOfWeek = -1;
                    break;
            }
            
            // Save values
            $_POST['hour'] = $hour;
            $_POST['minute'] = $minute;
            $_POST['day_of_week'] = (int)$dayOfWeek;
            $_POST['day_of_month'] = (int)$dayOfMonth;
            $_POST['month'] = (int)$month;
            
            // ALWAYS generate cron_unix_style based on selected individual fields
            // Create a temporary object to generate cron_unix_style
            $tempTask = new AccCronTaskModel();
            $tempTask->frequency_day = $frequencyDay;
            $tempTask->hour = (int)$hour;
            $tempTask->minute = (int)$minute;
            $tempTask->day_of_week = (int)$dayOfWeek;
            $tempTask->day_of_month = (int)$dayOfMonth;
            $tempTask->month = (int)$month;
            
            // If frequency is 6 (Cron Unix Style) and user entered a valid value, use it temporarily
            // so generateCronUnixStyle() can use it if there are no valid individual fields
            if ($frequencyDay == 6 && !empty($cronUnixStyle) && AccCronTaskModel::validateCronUnixStyle($cronUnixStyle)) {
                $tempTask->cron_unix_style = $cronUnixStyle;
            }
            
            // ALWAYS generate cron_unix_style based on individual fields
            $generatedCron = $tempTask->generateCronUnixStyle();
            $_POST['cron_unix_style'] = $generatedCron;
        }

        // Process with parent method (will now include updated cron_unix_style)
        return parent::postProcess();
    }
}

