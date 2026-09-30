<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración general del área GSI
    |--------------------------------------------------------------------------
    */

    // Días previos al vencimiento para considerar un caso "próximo a vencer" (semáforo amarillo)
    'near_due_days' => (int) env('GSI_NEAR_DUE_DAYS', 2),

    // Etiqueta de la aplicación
    'app_label' => 'Bitácora GSI',

    // Formato corto de horas para mostrar tiempos
    'hours_format' => 1,
];