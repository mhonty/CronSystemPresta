<?php

/**
 * AdminCronSystemListController
 *
 * Displays the list of scheduled cron jobs defined in the CronSystem module.
 * Allows toggling activation status and deleting tasks from the Back Office.
 *
 * This controller is rendered under the "Tareas" tab.
 */

class AdminCronSystemListController extends ModuleAdminController
{
    /**
     * Constructor for the list controller.
     * Sets up default table, identifier and configures available row actions.
     */
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        $this->meta_title = $this->l('CronSystem - Tareas programadas');
        $this->table = 'cron_jobs';
        $this->alias = 'a';
        $this->className = 'CronJob';
        $this->identifier = 'id_cron_job';
        $this->default_orderby = 'id_cron_job';
        $this->default_orderway = 'DESC';
        $this->lang = false;

        // Custom actions are handled via renderToggleButton and postProcess
        $this->actions = ['delete', 'custom_toggle'];
    }

    /**
     * Initializes the table fields shown in the task list.
     * Called from getList to set $fields_list dynamically.
     */
    public function initList()
    {
        $this->fields_list = [
            'id_cron_job' => [
                'title' => $this->l('ID'),
                'type' => 'int',
                'align' => 'center',
            ],
            'nombre' => [
                'title' => $this->l('Nombre'),
                'type' => 'text',
            ],
            'tarea' => [
                'title' => $this->l('Ruta'),
                'type' => 'text',
                'maxlength' => 100,
            ],
            'frecuencia' => [
                'title' => $this->l('Frecuencia (s)'),
                'type' => 'int',
            ],
            'ultima_ejecucion' => [
                'title' => $this->l('Última ejecución'),
                'type' => 'datetime',
                'search' => false,
            ],
            'estado_ultima_ejecucion' => [
                'title' => $this->l('Estado'),
                'callback' => 'renderEstado',
                'orderby' => false,
                'search' => false,
            ],
            'activo' => [
                'title' => $this->l('Activo'),
                'type' => 'bool',
                'orderby' => false,
                'callback' => 'renderToggleButton',
            ],
        ];

        // Only show delete in row actions (toggle is rendered manually)
        $this->actions = ['delete'];
    }

    /**
     * Overrides getList to manually define FROM and SELECT statements.
     * Useful for avoiding alias issues and for ensuring full control of SQL.
     */
    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        $this->initList();

        $this->_select = 'a.*';
        $this->_join = '';
        $this->_from = '`' . _DB_PREFIX_ . 'cron_jobs` AS a';

        if (empty($order_by) || $order_by === 'id_configuration') {
            $order_by = 'id_cron_job';
        }

        return parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
    }

    /**
     * Renders status label for last execution result.
     * Can be extended with more custom statuses or icons if needed.
     *
     * @param int $estado
     * @return string
     */
    public function renderEstado($estado)
    {
        switch ((int)$estado) {
            case 1:
                return '<span class="label label-success">OK</span>';
            case 0:
                return '<span class="label label-danger">KO</span>';
            case 2:
                return '<span class="label label-warning">Desconocido</span>';
            case 3:
                return '<span class="label label-default">Timeout</span>';
            case 4:
                return '<span class="label label-warning">HTTP 4xx</span>';
            case 5:
                return '<span class="label label-danger">HTTP 5xx</span>';
            default:
                return '-';
        }
    }

    /**
     * Handles activation toggle requests (custom action).
     * Toggled via `toggleActive` param in the URL.
     */
    public function postProcess()
    {
        if (Tools::isSubmit('toggleActive')) {
            $id = (int)Tools::getValue('id_cron_job');

            $actual = (int)Db::getInstance()->getValue(
                'SELECT activo FROM ' . _DB_PREFIX_ . 'cron_jobs WHERE id_cron_job = ' . $id
            );

            Db::getInstance()->update('cron_jobs', [
                'activo' => $actual ? 0 : 1
            ], 'id_cron_job = ' . $id);

            // Redirect to avoid re-submission
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
        }

        parent::postProcess();
    }

    /**
     * Renders the icon-only activation toggle in the list.
     *
     * @param bool $valor Current value (1 = active, 0 = inactive)
     * @param array $fila Full row data
     * @return string HTML
     */
    public function renderToggleButton($valor, $fila)
    {
        $id = (int)$fila['id_cron_job'];

        $label = $valor ? $this->l('Desactivar') : $this->l('Activar');
        $icon = $valor ? 'icon-remove text-danger' : 'icon-check text-success';

        $url = self::$currentIndex . '&' . $this->identifier . '=' . $id . '&toggleActive=1&token=' . $this->token;

        return '<a href="' . $url . '" title="' . $label . '">
                    <i class="' . $icon . '"></i>
                </a>';
    }

    /**
     * Ensures initList is executed before parent initContent.
     * Prevents broken list rendering.
     */
    public function initContent()
    {
        $this->initList();
        parent::initContent();
    }
}
/**
 * © Pedro Montalvo, 2025 - MIT Licensed
 *
 * Want to contribute or suggest features?
 * Open an issue or PR at: https://github.com/mhonty/CronSystemPresta
 */
