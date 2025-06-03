<?php

/**
 * Class CronJob
 *
 * Represents a single scheduled task in the CronSystem module.
 * This model maps to the 'cron_jobs' database table and allows
 * PrestaShop's ObjectModel features (validation, CRUD operations, etc.)
 */

class CronJob extends ObjectModel
{
    /** @var int Primary key */
    public $id_cron_job;

    /** @var string Task name (for display and identification) */
    public $nombre;

    /** @var string Relative path to the controller or script to execute */
    public $tarea;

    /** @var int Frequency in seconds; 0 = execute once */
    public $frecuencia;

    /** @var bool Whether this task should be executed only once */
    public $unica;

    /** @var string|null Datetime of the last execution */
    public $ultima_ejecucion;

    /** @var int Status of last execution (see status codes in renderEstado) */
    public $estado_ultima_ejecucion;

    /** @var bool Whether the task can run during Back Office requests */
    public $back_office;

    /** @var bool Whether the task can run during Front Office requests */
    public $front_office;

    /** @var bool Whether the task is currently active */
    public $activo;

    /** @var string Date of task creation */
    public $date_add;

    /** @var string Date of last task update */
    public $date_upd;

    /**
     * ObjectModel definition for PrestaShop ORM
     */
    public static $definition = [
        'table'   => 'cron_jobs',
        'primary' => 'id_cron_job',
        'fields'  => [
            'nombre'                   => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true],
            'tarea'                    => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true],
            'frecuencia'              => ['type' => self::TYPE_INT],
            'unica'                   => ['type' => self::TYPE_BOOL],
            'ultima_ejecucion'        => ['type' => self::TYPE_DATE],
            'estado_ultima_ejecucion' => ['type' => self::TYPE_INT],
            'back_office'             => ['type' => self::TYPE_BOOL],
            'front_office'            => ['type' => self::TYPE_BOOL],
            'activo'                  => ['type' => self::TYPE_BOOL],
            'date_add'                => ['type' => self::TYPE_DATE],
            'date_upd'                => ['type' => self::TYPE_DATE],
        ],
        'orderBy' => 'id_cron_job'
    ];
}
/**
 * © Pedro Montalvo, 2025 - MIT Licensed
 *
 * Want to contribute or suggest features?
 * Open an issue or PR at: https://github.com/mhonty/CronSystemPresta
 */
