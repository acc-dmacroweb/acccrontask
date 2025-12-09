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
    public $cron_unix_style;
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
            'day_of_week' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'day_of_month' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'month' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'hour' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'cron_unix_style' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'last_execution' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'allow_null' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
        ],
    ];

    /**
     * Gets all active cron tasks
     * @return array
     */
    public static function getActiveCronJobs()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'acccrontask` 
                WHERE `active` = 1 
                ORDER BY `id_acccrontask` ASC';
        
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Checks if the task should be executed now
     * @return bool
     */
    public function shouldExecute()
    {
        if (!$this->active) {
            return false;
        }

        $now = time();
        $currentTime = getdate($now);
        
        // If there's last_execution, calculate if enough time has passed according to frequency
        if (!empty($this->last_execution)) {
            $lastExecutionTimestamp = strtotime($this->last_execution);
            if ($lastExecutionTimestamp === false) {
                return false;
            }
            
            // Calculate time elapsed since last execution
            $timeSinceLastExecution = $now - $lastExecutionTimestamp;
            
            // Check according to frequency
            switch ($this->frequency_day) {
                case 5: // Every hour
                    // Execute if at least 60 minutes have passed since last execution
                    return $timeSinceLastExecution >= 3600;
                    
                case 0: // Daily
                    // Execute if at least 24 hours have passed and it's the configured hour/minute
                    if ($timeSinceLastExecution >= 86400) {
                        return ($currentTime['hours'] == $this->hour && $currentTime['minutes'] == $this->minute);
                    }
                    return false;
                    
                case 1: // Weekly
                    // Execute if at least 7 days have passed and it's the configured day/hour/minute
                    if ($timeSinceLastExecution >= 604800) {
                        return ($currentTime['wday'] == $this->day_of_week && 
                                $currentTime['hours'] == $this->hour && 
                                $currentTime['minutes'] == $this->minute);
                    }
                    return false;
                    
                case 2: // Monthly
                    // Execute if it's the configured day of month and hour/minute
                    if ($currentTime['mday'] == $this->day_of_month && 
                        $currentTime['hours'] == $this->hour && 
                        $currentTime['minutes'] == $this->minute) {
                        // Check that at least 30 days have passed since last execution
                        return $timeSinceLastExecution >= 2592000; // 30 days
                    }
                    return false;
                    
                case 3: // Yearly
                    // Execute if it's the configured month/day/hour/minute
                    if ($currentTime['mon'] == $this->month && 
                        $currentTime['mday'] == $this->day_of_month && 
                        $currentTime['hours'] == $this->hour && 
                        $currentTime['minutes'] == $this->minute) {
                        // Check that at least 365 days have passed since last execution
                        return $timeSinceLastExecution >= 31536000; // 365 days
                    }
                    return false;
                    
                case 6: // Cron Unix Style
                    // For cron unix style, check if current time matches the pattern
                    if (empty($this->cron_unix_style) || !self::validateCronUnixStyle($this->cron_unix_style)) {
                        return false;
                    }
                    
                    $parts = preg_split('/\s+/', trim($this->cron_unix_style));
                    if (count($parts) != 5) {
                        return false;
                    }
                    
                    // Check if current time matches cron pattern
                    return ($this->matchesCronField($parts[0], $currentTime['minutes'], 0, 59) &&
                            $this->matchesCronField($parts[1], $currentTime['hours'], 0, 23) &&
                            $this->matchesCronField($parts[2], $currentTime['mday'], 1, 31) &&
                            $this->matchesCronField($parts[3], $currentTime['mon'], 1, 12) &&
                            $this->matchesCronField($parts[4], $currentTime['wday'], 0, 6) &&
                            $timeSinceLastExecution >= 60); // At least 1 minute since last execution
                    
                default:
                    return false;
            }
        } else {
            // If there's no last_execution, execute if it matches current configuration
            switch ($this->frequency_day) {
                case 5: // Every hour
                    return ($currentTime['minutes'] == $this->minute);
                    
                case 0: // Daily
                    return ($currentTime['hours'] == $this->hour && $currentTime['minutes'] == $this->minute);
                    
                case 1: // Weekly
                    return ($currentTime['wday'] == $this->day_of_week && 
                            $currentTime['hours'] == $this->hour && 
                            $currentTime['minutes'] == $this->minute);
                    
                case 2: // Monthly
                    return ($currentTime['mday'] == $this->day_of_month && 
                            $currentTime['hours'] == $this->hour && 
                            $currentTime['minutes'] == $this->minute);
                    
                case 3: // Yearly
                    return ($currentTime['mon'] == $this->month && 
                            $currentTime['mday'] == $this->day_of_month && 
                            $currentTime['hours'] == $this->hour && 
                            $currentTime['minutes'] == $this->minute);
                    
                case 6: // Cron Unix Style
                    if (empty($this->cron_unix_style) || !self::validateCronUnixStyle($this->cron_unix_style)) {
                        return false;
                    }
                    
                    $parts = preg_split('/\s+/', trim($this->cron_unix_style));
                    if (count($parts) != 5) {
                        return false;
                    }
                    
                    return ($this->matchesCronField($parts[0], $currentTime['minutes'], 0, 59) &&
                            $this->matchesCronField($parts[1], $currentTime['hours'], 0, 23) &&
                            $this->matchesCronField($parts[2], $currentTime['mday'], 1, 31) &&
                            $this->matchesCronField($parts[3], $currentTime['mon'], 1, 12) &&
                            $this->matchesCronField($parts[4], $currentTime['wday'], 0, 6));
                    
                default:
                    return false;
            }
        }
    }

    /**
     * Executes the task
     * @return bool
     */
    public function execute()
    {
        if (!$this->active) {
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

    /**
     * Executes the task manually without checking shouldExecute() or if it's active
     * Always executes and records execution time
     */
    public function executeNow()
    {
        // Always execute, regardless of whether it's active or not
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // ALWAYS record execution time, regardless of result
        $this->last_execution = date('Y-m-d H:i:s');
        $this->update();

        // Return true if execution was successful (code 200), false otherwise
        return $httpCode == 200 && empty($curlError);
    }

    /**
     * Calculates next execution based on cron_unix_style
     * @return int|false Timestamp of next execution or false if cannot be calculated
     */
   

    /**
     * Calculates next execution from individual fields (fallback)
     */
    

    /**
     * Checks if a value matches a cron field
     * @param string $field Cron field (e.g: "*", "5", "*\/10", "1-5")
     * @param int $value Value to check
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @return bool
     */
    protected function matchesCronField($field, $value, $min, $max)
    {
        if ($field === '*') {
            return true;
        }

        // Range: 1-5
        if (preg_match('/^(\d+)-(\d+)$/', $field, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            return $value >= $start && $value <= $end;
        }

        // Interval: */10
        if (preg_match('/^\*\/(\d+)$/', $field, $matches)) {
            $interval = (int)$matches[1];
            return ($value % $interval) == 0;
        }

        // List: 1,3,5
        if (strpos($field, ',') !== false) {
            $values = explode(',', $field);
            foreach ($values as $v) {
                if ((int)trim($v) == $value) {
                    return true;
                }
            }
            return false;
        }

        // Single value
        return (int)$field == $value;
    }

    /**
     * Generates unix style cron format from individual fields
     * @return string
     */
    public function generateCronUnixStyle()
    {
        switch ($this->frequency_day) {
            case 5: // Every hour
                return $this->minute . ' * * * *';
                
            case 0: // Daily
                return $this->minute . ' ' . $this->hour . ' * * *';
                
            case 1: // Weekly
                return $this->minute . ' ' . $this->hour . ' * * ' . $this->day_of_week;
                
            case 2: // Monthly
                return $this->minute . ' ' . $this->hour . ' ' . $this->day_of_month . ' * *';
                
            case 3: // Yearly
                return $this->minute . ' ' . $this->hour . ' ' . $this->day_of_month . ' ' . $this->month . ' *';
                
            case 6: // Cron Unix Style - if there's a valid value, use it; if not, generate from fields
                // If there's a valid cron_unix_style, use it
                if (!empty($this->cron_unix_style) && self::validateCronUnixStyle($this->cron_unix_style)) {
                    return $this->cron_unix_style;
                }
                // If not, generate from individual fields (though they'll normally be -1)
                // If fields have valid values, generate from them
                if ($this->day_of_month != -1 && $this->month != -1) {
                    return $this->minute . ' ' . $this->hour . ' ' . $this->day_of_month . ' ' . $this->month . ' *';
                } elseif ($this->day_of_month != -1) {
                    return $this->minute . ' ' . $this->hour . ' ' . $this->day_of_month . ' * *';
                } elseif ($this->day_of_week != -1) {
                    return $this->minute . ' ' . $this->hour . ' * * ' . $this->day_of_week;
                } elseif ($this->hour != 0) {
                    return $this->minute . ' ' . $this->hour . ' * * *';
                } else {
                    return $this->minute . ' * * * *';
                }
                
            default:
                return '* * * * *';
        }
    }

    /**
     * Validates unix style cron format
     * @param string $cronStyle Cron format to validate
     * @return bool
     */
    public static function validateCronUnixStyle($cronStyle)
    {
        if (empty($cronStyle)) {
            return false;
        }

        $parts = preg_split('/\s+/', trim($cronStyle));
        if (count($parts) != 5) {
            return false;
        }

        // Validate each part
        $ranges = [
            [0, 59],   // minute
            [0, 23],   // hour
            [1, 31],   // day of month
            [1, 12],   // month
            [0, 6],    // day of week
        ];

        foreach ($parts as $index => $part) {
            if ($part === '*') {
                continue;
            }

            // Range: 1-5
            if (preg_match('/^(\d+)-(\d+)$/', $part, $matches)) {
                $start = (int)$matches[1];
                $end = (int)$matches[2];
                if ($start < $ranges[$index][0] || $end > $ranges[$index][1] || $start > $end) {
                    return false;
                }
                continue;
            }

            // Interval: */10
            if (preg_match('/^\*\/(\d+)$/', $part, $matches)) {
                $interval = (int)$matches[1];
                if ($interval < 1 || $interval > $ranges[$index][1]) {
                    return false;
                }
                continue;
            }

            // List: 1,3,5
            if (strpos($part, ',') !== false) {
                $values = explode(',', $part);
                foreach ($values as $v) {
                    $val = (int)trim($v);
                    if ($val < $ranges[$index][0] || $val > $ranges[$index][1]) {
                        return false;
                    }
                }
                continue;
            }

            // Single value
            $val = (int)$part;
            if ($val < $ranges[$index][0] || $val > $ranges[$index][1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets default values before adding
     */
    public function add($autoDate = true, $nullValues = false)
    {
        if ($autoDate) {
            $this->date_add = date('Y-m-d H:i:s');
            $this->date_upd = date('Y-m-d H:i:s');
        }

        // If last_execution is empty, set as NULL
        if (empty($this->last_execution)) {
            $this->last_execution = null;
        }

        // ALWAYS update cron_unix_style based on individual fields
        $this->cron_unix_style = $this->generateCronUnixStyle();

        return parent::add($autoDate, $nullValues);
    }

    /**
     * Updates modification date before updating
     */
    public function update($nullValues = false)
    {
        $this->date_upd = date('Y-m-d H:i:s');
        
        // If last_execution is empty, set as NULL
        if (empty($this->last_execution)) {
            $this->last_execution = null;
        }
        
        // ALWAYS update cron_unix_style based on individual fields
        $this->cron_unix_style = $this->generateCronUnixStyle();
        
        return parent::update($nullValues);
    }
}

