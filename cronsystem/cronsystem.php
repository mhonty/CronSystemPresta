<?php

/**
 * CronSystem Module
 * Allows managing and executing scheduled tasks on each page load (Front and Back Office).
 *
 * @author Pedro Montalvo
 * @license MIT
 */

/**
 * CronSystem Module for PrestaShop
 *
 * This module allows you to define and manage scheduled tasks that execute
 * during Front Office or Back Office page loads. Ideal for lightweight cron
 * executions in hosting environments without access to native cron.
 *
 * HOW IT WORKS:
 * -------------
 * - Tasks are registered with a name, URL, and optional frequency.
 * - On each FO or BO page load, the hook `actionDispatcher` checks due tasks.
 * - If a task hasn't been executed in the defined interval, it is triggered.
 * - Tasks are expected to return "OK", "KO" or any HTTP status. The result is logged.
 * - One-shot tasks (`unica = 1`) are deactivated after execution.
 *
 * You can extend or customize:
 * - The allowed URL patterns → see `isRutaTareaValida()`
 * - The execution trigger (use another hook) → see `hookActionDispatcher()`
 * - The execution method (e.g., switch to async) → see `ejecutarTareas()`
 *
 * SECURITY:
 * ---------
 * For safety, only internal URLs are allowed (e.g., "module/...", "admin/...", "index.php/...")
 * Parameters are validated to avoid path traversal or unsafe input.
 *
 * Feel free to fork, adapt, and improve. Contributions welcome!
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Entity\Db;

require_once _PS_MODULE_DIR_ . 'cronsystem/classes/CronJob.php';

class CronSystem extends Module
{
    public function __construct()
    {
        $this->name = 'cronsystem';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Pedro Montalvo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Cron System');
        $this->description = $this->l('Manage scheduled tasks executed on every page load.') . '            https://github.com/mhonty/CronSystemPresta';
    }

    /**
     * Creates the database table for cron jobs.
     *
     * @return bool
     */
    protected function installDB(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'cron_jobs` (
            `id_cron_job` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `nombre` VARCHAR(255) NOT NULL,
            `tarea` TEXT NOT NULL,
            `frecuencia` INT UNSIGNED NOT NULL,
            `unica` TINYINT(1) NOT NULL DEFAULT 0,
            `ultima_ejecucion` DATETIME DEFAULT NULL,
            `estado_ultima_ejecucion` TINYINT(1) NOT NULL DEFAULT 0,
            `back_office` TINYINT(1) NOT NULL DEFAULT 0,
            `front_office` TINYINT(1) NOT NULL DEFAULT 0,
            `activo` TINYINT(1) NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Installs menu tabs in the Back Office.
     *
     * @return bool
     */
    private function installTabs(): bool
    {
        $langs = Language::getLanguages(false);

        $parentConfig = new Tab();
        $parentConfig->active = 1;
        $parentConfig->class_name = 'AdminCronSystemParentConfig';
        $parentConfig->name = [];
        foreach ($langs as $lang) {
            $parentConfig->name[$lang['id_lang']] = 'CronSystem';
        }
        $parentConfig->id_parent = (int) Tab::getIdFromClassName('ShopParameters');
        $parentConfig->module = $this->name;

        if (!$parentConfig->add()) {
            return false;
        }

        $configChildren = [
            'AdminCronSystemList' => 'Tareas',
            'AdminCronSystemAdd' => 'Añadir',
        ];

        foreach ($configChildren as $class => $label) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $class;
            $tab->name = [];
            foreach ($langs as $lang) {
                $tab->name[$lang['id_lang']] = $label;
            }
            $tab->id_parent = (int) $parentConfig->id;
            $tab->module = $this->name;
            if (!$tab->add()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Installs the module.
     *
     * @return bool
     */
    public function install(): bool
    {
        if (!parent::install()) {
            return false;
        }

        try {
            if (
                !$this->installDB() ||
                !$this->installTabs() ||
                !$this->registerHook('actionDispatcher')
            ) {
                throw new Exception('CronSystem installation failed: DB/Tabs not created.');
            }
        } catch (Exception $e) {
            $this->uninstall();
            return false;
        }

        return true;
    }

    /**
     * Uninstalls tabs from the Back Office.
     *
     * @return bool
     */
    protected function uninstallTabs(): bool
    {
        $tabs = Tab::getCollectionFromModule($this->name);
        foreach ($tabs as $tab) {
            if (!$tab->delete()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Removes the cron_jobs table.
     *
     * @return bool
     */
    protected function uninstallDB(): bool
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'cron_jobs`';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Uninstalls the module.
     *
     * @return bool
     */
    public function uninstall()
    {
        return $this->uninstallDB()
            && parent::uninstall();
    }

    /**
     * Hook executed on every page load (FO and BO).
     *
     * @param array $params
     * @return void
     */
    public function hookActionDispatcher($params)
    {
        $controller = Dispatcher::getInstance()->getController();
        $isBO = defined('_PS_ADMIN_DIR_') && strpos($controller, 'Admin') !== false;
        $isFO = !$isBO;

        $this->ejecutarTareas($isBO, $isFO);
    }

    /**
     * Validates task route format and prevents path traversal.
     *
     * @param string $ruta
     * @return bool
     */
    public function isRutaTareaValida(string $ruta): bool
    {
        if (!preg_match('#^(module|admin|index\.php|api)/#i', $ruta)) {
            error_log('CronSystem: Invalid route prefix: ' . $ruta);
            return false;
        }

        if (strpos($ruta, '..') !== false) {
            error_log('CronSystem: Path traversal detected in: ' . $ruta);
            return false;
        }

        $partes = explode('?', $ruta, 2);
        if (count($partes) === 2) {
            parse_str($partes[1], $params);
            foreach ($params as $clave => $valor) {
                if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $clave)) {
                    error_log('CronSystem: Invalid GET param key: ' . $clave);
                    return false;
                }
                if (!preg_match('/^[\w\-@!\.\$\+=,:;\/]+$/', $valor)) {
                    error_log('CronSystem: Invalid GET param value: ' . $valor);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Executes due tasks depending on context (FO/BO).
     *
     * @param bool $isBO
     * @param bool $isFO
     * @return void
     */
    protected function ejecutarTareas($isBO, $isFO)
    {
        error_log('CronSystem: Running tasks BO=' . ($isBO ? 'Yes' : 'No') . ' FO=' . ($isFO ? 'Yes' : 'No'));

        $query = new DbQuery();
        $query->select('*')
            ->from('cron_jobs')
            ->where('activo = 1')
            ->where('(frecuencia = 0 OR (frecuencia > 0 AND (ultima_ejecucion IS NULL OR TIMESTAMPDIFF(SECOND, ultima_ejecucion, NOW()) >= frecuencia)))')
            ->where('(' . ($isBO ? 'back_office = 1' : 'front_office = 1') . ')');

        $tareas = Db::getInstance()->executeS($query);


        // Each task is validated and executed if due.
        // Tasks are triggered via internal GET requests (5s timeout).
        // Customize here if you prefer cURL, async dispatching, logging to DB, etc.
        foreach ($tareas as $tarea) {
            try {
                if (!$this->isRutaTareaValida($tarea['tarea'])) {
                    error_log('CronSystem: Invalid route in task #' . $tarea['id_cron_job'] . ': ' . $tarea['tarea']);
                    continue;
                }

                $url = Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . ltrim($tarea['tarea'], '/');

                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 5,
                    ]
                ]);

                $response = Tools::file_get_contents($url, false, $context);
                $responseText = trim($response ?? '');

                $http_code = 0;
                if (isset($http_response_header) && is_array($http_response_header)) {
                    foreach ($http_response_header as $header) {
                        if (preg_match('#HTTP/\d+\.\d+\s+(\d{3})#', $header, $matches)) {
                            $http_code = (int) $matches[1];
                            break;
                        }
                    }
                }
                // Determine execution status based on response content and HTTP status
                // You can adapt this to parse JSON or detect other success patterns
                if ($response === false) {
                    $estado = 3;
                } elseif ($responseText === 'OK') {
                    $estado = 1;
                } elseif ($responseText === 'KO') {
                    $estado = 0;
                } elseif ($http_code >= 500) {
                    $estado = 5;
                } elseif ($http_code >= 400) {
                    $estado = 4;
                } else {
                    $estado = 2;
                }

                Db::getInstance()->update('cron_jobs', [
                    'ultima_ejecucion' => ['type' => 'sql', 'value' => 'NOW()'],
                    'estado_ultima_ejecucion' => $estado,
                    'activo' => $tarea['unica'] ? 0 : 1,
                ], 'id_cron_job = ' . (int) $tarea['id_cron_job']);
            } catch (Exception $e) {
                error_log('CronSystem: Task #' . $tarea['id_cron_job'] . ' failed: ' . $e->getMessage());

                Db::getInstance()->update('cron_jobs', [
                    'ultima_ejecucion' => ['type' => 'sql', 'value' => 'NOW()'],
                    'estado_ultima_ejecucion' => 0,
                ], 'id_cron_job = ' . (int) $tarea['id_cron_job']);
            }
        }
    }

    /**
     * Registers a cron task in the database.
     *
     * @param array $data
     * @return bool
     */
    public function registrarTarea(array $data): bool
    {
        // Register a new cron job safely. Called manually or via a controller.
        // This method validates required fields and stores task info in the DB.
        // You can extend this to allow start/end date, log level, etc.
        if (
            !isset($data['nombre']) || trim($data['nombre']) === '' ||
            !isset($data['tarea']) || trim($data['tarea']) === '' ||
            !isset($data['frecuencia']) || !is_numeric($data['frecuencia'])
        ) {
            error_log('CronSystem:: Missing required fields');
            return false;
        }

        if (!$this->isRutaTareaValida($data['tarea'])) {
            error_log('CronSystem: Invalid route in task: ' . $data['tarea']);
            return false;
        }

        if ((int) $data['frecuencia'] < 0) {
            error_log('CronSystem:: Negative frequency not allowed: ' . $data['frecuencia']);
            return false;
        }

        $tarea = [
            'nombre'        => pSQL($data['nombre']),
            'tarea'         => pSQL($data['tarea']),
            'frecuencia'    => (int) $data['frecuencia'],
            'unica'         => isset($data['unica']) ? (int) $data['unica'] : 0,
            'back_office'   => isset($data['bo']) ? (int) $data['bo'] : 0,
            'front_office'  => isset($data['fo']) ? (int) $data['fo'] : 1,
            'activo'        => 1,
        ];

        $success = Db::getInstance()->insert('cron_jobs', $tarea);

        if ($success) {
            error_log('CronSystem:: Task registered successfully: ' . $data['nombre']);
        } else {
            error_log('CronSystem:: Failed to insert task into database');
        }

        return $success;
    }
}

/**
 * © Pedro Montalvo, 2025 - MIT Licensed
 *
 * Want to contribute or suggest features?
 * Open an issue or PR at: https://github.com/mhonty/CronSystemPresta
 */
