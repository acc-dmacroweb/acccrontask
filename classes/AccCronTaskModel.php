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

class AccCronTaskModel extends ObjectModel
{
    public $id_acccrontask;
    public $name;
    public $url;
    public $frequency_day;
    public $minute;
    public $day_of_week;
    public $day_of_month;
    public $month;
    public $hour;
    public $active;
    public $last_execution;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'acccrontask',
        'primary' => 'id_acccrontask',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'url' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl', 'required' => true],
            'frequency_day' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'minute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'day_of_week' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'day_of_month' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'month' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'hour' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'last_execution' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'allow_null' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }

    public function add($auto_date = true, $null_values = false)
    {
        if ($auto_date) {
            $this->date_add = date('Y-m-d H:i:s');
            $this->date_upd = date('Y-m-d H:i:s');
        }
        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        $this->date_upd = date('Y-m-d H:i:s');
        return parent::update($null_values);
    }

    public static function getActiveCronJobs()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'acccrontask`
                WHERE `active` = 1
                ORDER BY `hour`, `minute`';
        
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public function shouldExecute()
    {
        $now = getdate();
        $currentHour = (int)$now['hours'];
        $currentMinute = (int)$now['minutes'];
        $currentDayOfWeek = (int)$now['wday'];
        $currentDayOfMonth = (int)$now['mday'];
        $currentMonth = (int)$now['mon'];

        // Si es "Cada hora" (frequency_day = 5), solo verificar el minuto
        if ($this->frequency_day == 5) {
            if ($this->minute != $currentMinute) {
                return false;
            }
            return true;
        }

        // Para otras frecuencias, verificar hora y minuto
        if ($this->hour != $currentHour || $this->minute != $currentMinute) {
            return false;
        }

        if ($this->day_of_week != -1 && $this->day_of_week != $currentDayOfWeek) {
            return false;
        }

        if ($this->day_of_month != -1 && $this->day_of_month != $currentDayOfMonth) {
            return false;
        }

        if ($this->month != -1 && $this->month != $currentMonth) {
            return false;
        }

        return true;
    }

    public function execute()
    {
        if (!$this->active || !$this->shouldExecute()) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->last_execution = date('Y-m-d H:i:s');
        $this->update();

        return $httpCode == 200;
    }
}

