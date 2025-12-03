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
     * Obtiene todas las tareas cron activas
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
     * Verifica si la tarea debe ejecutarse ahora
     * @return bool
     */
    public function shouldExecute()
    {
        if (!$this->active) {
            return false;
        }

        $now = time();
        $currentTime = getdate($now);
        
        // Si hay last_execution, calcular si ha pasado el tiempo suficiente según la frecuencia
        if (!empty($this->last_execution)) {
            $lastExecutionTimestamp = strtotime($this->last_execution);
            if ($lastExecutionTimestamp === false) {
                return false;
            }
            
            // Calcular el tiempo transcurrido desde la última ejecución
            $timeSinceLastExecution = $now - $lastExecutionTimestamp;
            
            // Verificar según la frecuencia
            switch ($this->frequency_day) {
                case 5: // Cada hora
                    // Ejecutar si han pasado al menos 60 minutos desde la última ejecución
                    return $timeSinceLastExecution >= 3600;
                    
                case 0: // Diario
                    // Ejecutar si han pasado al menos 24 horas y es la hora/minuto configurada
                    if ($timeSinceLastExecution >= 86400) {
                        return ($currentTime['hours'] == $this->hour && $currentTime['minutes'] == $this->minute);
                    }
                    return false;
                    
                case 1: // Semanal
                    // Ejecutar si han pasado al menos 7 días y es el día/hora/minuto configurado
                    if ($timeSinceLastExecution >= 604800) {
                        return ($currentTime['wday'] == $this->day_of_week && 
                                $currentTime['hours'] == $this->hour && 
                                $currentTime['minutes'] == $this->minute);
                    }
                    return false;
                    
                case 2: // Mensual
                    // Ejecutar si es el día del mes configurado y la hora/minuto
                    if ($currentTime['mday'] == $this->day_of_month && 
                        $currentTime['hours'] == $this->hour && 
                        $currentTime['minutes'] == $this->minute) {
                        // Verificar que hayan pasado al menos 30 días desde la última ejecución
                        return $timeSinceLastExecution >= 2592000; // 30 días
                    }
                    return false;
                    
                case 3: // Anual
                    // Ejecutar si es el mes/día/hora/minuto configurado
                    if ($currentTime['mon'] == $this->month && 
                        $currentTime['mday'] == $this->day_of_month && 
                        $currentTime['hours'] == $this->hour && 
                        $currentTime['minutes'] == $this->minute) {
                        // Verificar que hayan pasado al menos 365 días desde la última ejecución
                        return $timeSinceLastExecution >= 31536000; // 365 días
                    }
                    return false;
                    
                case 6: // Cron Unix Style
                    // Para cron unix style, verificar si el tiempo actual coincide con el patrón
                    if (empty($this->cron_unix_style) || !self::validateCronUnixStyle($this->cron_unix_style)) {
                        return false;
                    }
                    
                    $parts = preg_split('/\s+/', trim($this->cron_unix_style));
                    if (count($parts) != 5) {
                        return false;
                    }
                    
                    // Verificar si el tiempo actual coincide con el patrón cron
                    return ($this->matchesCronField($parts[0], $currentTime['minutes'], 0, 59) &&
                            $this->matchesCronField($parts[1], $currentTime['hours'], 0, 23) &&
                            $this->matchesCronField($parts[2], $currentTime['mday'], 1, 31) &&
                            $this->matchesCronField($parts[3], $currentTime['mon'], 1, 12) &&
                            $this->matchesCronField($parts[4], $currentTime['wday'], 0, 6) &&
                            $timeSinceLastExecution >= 60); // Al menos 1 minuto desde la última ejecución
                    
                default:
                    return false;
            }
        } else {
            // Si no hay last_execution, ejecutar si coincide con la configuración actual
            switch ($this->frequency_day) {
                case 5: // Cada hora
                    return ($currentTime['minutes'] == $this->minute);
                    
                case 0: // Diario
                    return ($currentTime['hours'] == $this->hour && $currentTime['minutes'] == $this->minute);
                    
                case 1: // Semanal
                    return ($currentTime['wday'] == $this->day_of_week && 
                            $currentTime['hours'] == $this->hour && 
                            $currentTime['minutes'] == $this->minute);
                    
                case 2: // Mensual
                    return ($currentTime['mday'] == $this->day_of_month && 
                            $currentTime['hours'] == $this->hour && 
                            $currentTime['minutes'] == $this->minute);
                    
                case 3: // Anual
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
     * Ejecuta la tarea
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
     * Ejecuta la tarea manualmente sin verificar shouldExecute() ni si está activa
     * Siempre ejecuta y registra la hora de ejecución
     */
    public function executeNow()
    {
        // Ejecutar siempre, sin importar si está activa o no
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

        // SIEMPRE registrar la hora de ejecución, independientemente del resultado
        $this->last_execution = date('Y-m-d H:i:s');
        $this->update();

        // Retornar true si la ejecución fue exitosa (código 200), false en caso contrario
        return $httpCode == 200 && empty($curlError);
    }

    /**
     * Calcula la próxima ejecución basada en cron_unix_style
     * @return int|false Timestamp de la próxima ejecución o false si no se puede calcular
     */
   

    /**
     * Calcula la próxima ejecución desde los campos individuales (fallback)
     */
    

    /**
     * Verifica si un valor coincide con un campo cron
     * @param string $field Campo cron (ej: "*", "5", "*\/10", "1-5")
     * @param int $value Valor a verificar
     * @param int $min Valor mínimo
     * @param int $max Valor máximo
     * @return bool
     */
    protected function matchesCronField($field, $value, $min, $max)
    {
        if ($field === '*') {
            return true;
        }

        // Rango: 1-5
        if (preg_match('/^(\d+)-(\d+)$/', $field, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            return $value >= $start && $value <= $end;
        }

        // Intervalo: */10
        if (preg_match('/^\*\/(\d+)$/', $field, $matches)) {
            $interval = (int)$matches[1];
            return ($value % $interval) == 0;
        }

        // Lista: 1,3,5
        if (strpos($field, ',') !== false) {
            $values = explode(',', $field);
            foreach ($values as $v) {
                if ((int)trim($v) == $value) {
                    return true;
                }
            }
            return false;
        }

        // Valor único
        return (int)$field == $value;
    }

    /**
     * Genera el formato cron unix style desde los campos individuales
     * @return string
     */
    public function generateCronUnixStyle()
    {
        switch ($this->frequency_day) {
            case 5: // Cada hora
                return $this->minute . ' * * * *';
                
            case 0: // Diario
                return $this->minute . ' ' . $this->hour . ' * * *';
                
            case 1: // Semanal
                return $this->minute . ' ' . $this->hour . ' * * ' . $this->day_of_week;
                
            case 2: // Mensual
                return $this->minute . ' ' . $this->hour . ' ' . $this->day_of_month . ' * *';
                
            case 3: // Anual
                return $this->minute . ' ' . $this->hour . ' ' . $this->day_of_month . ' ' . $this->month . ' *';
                
            case 6: // Cron Unix Style - si hay un valor válido, usarlo; si no, generar desde campos
                // Si hay un cron_unix_style válido, usarlo
                if (!empty($this->cron_unix_style) && self::validateCronUnixStyle($this->cron_unix_style)) {
                    return $this->cron_unix_style;
                }
                // Si no, generar desde los campos individuales (aunque normalmente estarán en -1)
                // Si los campos están en valores válidos, generar desde ellos
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
     * Valida el formato cron unix style
     * @param string $cronStyle Formato cron a validar
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

        // Validar cada parte
        $ranges = [
            [0, 59],   // minuto
            [0, 23],   // hora
            [1, 31],   // día del mes
            [1, 12],   // mes
            [0, 6],    // día de la semana
        ];

        foreach ($parts as $index => $part) {
            if ($part === '*') {
                continue;
            }

            // Rango: 1-5
            if (preg_match('/^(\d+)-(\d+)$/', $part, $matches)) {
                $start = (int)$matches[1];
                $end = (int)$matches[2];
                if ($start < $ranges[$index][0] || $end > $ranges[$index][1] || $start > $end) {
                    return false;
                }
                continue;
            }

            // Intervalo: */10
            if (preg_match('/^\*\/(\d+)$/', $part, $matches)) {
                $interval = (int)$matches[1];
                if ($interval < 1 || $interval > $ranges[$index][1]) {
                    return false;
                }
                continue;
            }

            // Lista: 1,3,5
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

            // Valor único
            $val = (int)$part;
            if ($val < $ranges[$index][0] || $val > $ranges[$index][1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Asigna valores por defecto antes de agregar
     */
    public function add($autoDate = true, $nullValues = false)
    {
        if ($autoDate) {
            $this->date_add = date('Y-m-d H:i:s');
            $this->date_upd = date('Y-m-d H:i:s');
        }

        // Si last_execution está vacío, establecer como NULL
        if (empty($this->last_execution)) {
            $this->last_execution = null;
        }

        // SIEMPRE actualizar cron_unix_style basándose en los campos individuales
        $this->cron_unix_style = $this->generateCronUnixStyle();

        return parent::add($autoDate, $nullValues);
    }

    /**
     * Actualiza la fecha de modificación antes de actualizar
     */
    public function update($nullValues = false)
    {
        $this->date_upd = date('Y-m-d H:i:s');
        
        // Si last_execution está vacío, establecer como NULL
        if (empty($this->last_execution)) {
            $this->last_execution = null;
        }
        
        // SIEMPRE actualizar cron_unix_style basándose en los campos individuales
        $this->cron_unix_style = $this->generateCronUnixStyle();
        
        return parent::update($nullValues);
    }
}

