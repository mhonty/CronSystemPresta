<?php

/**
 * AdminCronSystemAddController
 *
 * Controller responsible for rendering and handling the task creation form
 * for the CronSystem module.
 *
 * This form allows administrators to register new scheduled tasks that will be
 * executed automatically on Front Office or Back Office page loads.
 *
 * Developers can modify the form structure, validations or hook it into other
 * admin actions to automate task registration.
 */

class AdminCronSystemAddController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        // Sets the page title in the Back Office
        $this->meta_title = $this->l('CronSystem - Añadir Tarea');
    }

    /**
     * Initializes and renders the controller content.
     * Includes the task registration form in the content area.
     */
    public function initContent()
    {
        parent::initContent();

        $form = $this->renderForm();
        $this->content .= $form;

        // Required to properly render the form in the BO layout
        $this->context->smarty->assign('content', $this->content);
    }

    /**
     * Builds the form used to register a new cron task.
     * You can add more fields here if you want to support advanced scheduling,
     * logging preferences, execution retries, etc.
     *
     * @return string
     */
    public function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_cancel_button = false;
        $helper->module = $this->module;
        $helper->table = 'cron_jobs';
        $helper->identifier = 'id_cron_job';
        $helper->submit_action = 'submit_add_cron_task';
        $helper->token = Tools::getAdminTokenLite('AdminCronSystemAdd');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminCronSystemAdd');
        $helper->tpl_vars = [
            'fields_value' => $this->getFieldsValues(),
        ];

        return $helper->generateForm([
            [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Nueva tarea programada'),
                    ],
                    'input' => [
                        [
                            'type' => 'text',
                            'label' => $this->l('Nombre'),
                            'name' => 'nombre',
                            'required' => true,
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Ruta tarea'),
                            'name' => 'tarea',
                            'desc' => $this->l('Ej: module/miModulo/miControlador?param1=valor'),
                            'required' => true,
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Frecuencia (segundos)'),
                            'name' => 'frecuencia',
                            'desc' => $this->l('0 para ejecutar una vez'),
                            'required' => true,
                            'desc' => $this->l('En Segundos, diaria: 86400, semanal: 604800, mensual: 2592000'),
                        ],
                        [
                            'type' => 'switch',
                            'label' => $this->l('¿Única ejecución?'),
                            'name' => 'unica',
                            'is_bool' => true,
                            'values' => [
                                ['id' => 'on', 'value' => 1, 'label' => $this->l('Sí')],
                                ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                            ],
                        ],
                        [
                            'type' => 'switch',
                            'label' => $this->l('Back Office'),
                            'name' => 'bo',
                            'is_bool' => true,
                            'values' => [
                                ['id' => 'on', 'value' => 1, 'label' => $this->l('Sí')],
                                ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                            ],
                        ],
                        [
                            'type' => 'switch',
                            'label' => $this->l('Front Office'),
                            'name' => 'fo',
                            'is_bool' => true,
                            'values' => [
                                ['id' => 'on', 'value' => 1, 'label' => $this->l('Sí')],
                                ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                            ],
                        ],
                    ],
                    'submit' => [
                        'title' => $this->l('Guardar tarea'),
                    ],
                ],
            ],
        ]);
    }

    /**
     * Loads submitted or default values into the form.
     *
     * @return array
     */
    protected function getFieldsValues()
    {
        return [
            'nombre' => Tools::getValue('nombre', ''),
            'tarea' => Tools::getValue('tarea', ''),
            'frecuencia' => Tools::getValue('frecuencia', ''),
            'unica' => Tools::getValue('unica', 0),
            'bo' => Tools::getValue('bo', 1),
            'fo' => Tools::getValue('fo', 1),
        ];
    }

    /**
     * Handles the form submission.
     * Performs validation and calls the module method to register the task.
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submit_add_cron_task')) {
            $data = [
                'nombre' => Tools::getValue('nombre'),
                'tarea' => Tools::getValue('tarea'),
                'frecuencia' => Tools::getValue('frecuencia'),
                'unica' => Tools::getValue('unica'),
                'bo' => Tools::getValue('bo'),
                'fo' => Tools::getValue('fo'),
            ];

            // Validate task path
            if (!$this->module->isRutaTareaValida($data['tarea'])) {
                $this->errors[] = $this->l('Ruta de tarea no válida.');
                return;
            }

            // Save task
            if ($this->module->registrarTarea($data)) {
                $this->confirmations[] = $this->l('Tarea registrada correctamente.');
            } else {
                $this->errors[] = $this->l('No se pudo registrar la tarea.');
            }
        }
    }
}
/**
 * © Pedro Montalvo, 2025 - MIT Licensed
 *
 * Want to contribute or suggest features?
 * Open an issue or PR at: https://github.com/mhonty/CronSystemPresta
 */