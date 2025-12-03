<?php

use Qsiq\RobotApi\Infrastructure\Robots\Mia\MiaRobotModule;

return [
    'api_key' => env('ROBOT_API_KEY'),

    'default_robot' => env('ROBOT_API_DEFAULT_ROBOT', 'mia'),

    'metrics' => [
        'total_time_key' => env('ROBOT_API_METRICS_TOTAL_KEY', 'robot_api:metrics:%s:total_time'),
        'count_key' => env('ROBOT_API_METRICS_COUNT_KEY', 'robot_api:metrics:%s:count'),
    ],
    'robots' => [
        'mia' => [
            'module' => MiaRobotModule::class,
            'connections' => [
                'virtual' => env('ROBOT_API_MIA_VIRTUAL_CONNECTION', 'mysql_virtual'),
                'smart_dial' => env('ROBOT_API_MIA_SMART_DIAL_CONNECTION', 'mysql_smart_dial'),
                'virtual_kz' => env('ROBOT_API_MIA_VIRTUAL_KZ_CONNECTION', 'mysql_virtual_kz'),
                'smart_dial_kz' => env('ROBOT_API_MIA_SMART_DIAL_KZ_CONNECTION', 'mysql_smart_dial_kz'),
            ],
            'tables' => [
                'bot' => env('ROBOT_API_MIA_BOT_TABLE', 'virtual.mia_bots'),
                'mod_base' => env('ROBOT_API_MIA_MOD_BASE_TABLE', 'smart_dial.mod_base'),
                'custom_prefix' => env('ROBOT_API_MIA_CUSTOM_PREFIX', 'smart_dial.custom_'),
            ],
            'columns' => [
                'lead' => env('ROBOT_API_MIA_BOT_LEAD_COLUMN', 'lead_id'),
                'queue' => env('ROBOT_API_MIA_BOT_QUEUE_COLUMN', 'queue_id'),
                'phone' => env('ROBOT_API_MIA_PHONE_COLUMN', 'phone'),
                'name' => env('ROBOT_API_MIA_NAME_COLUMN', 'name'),
                'license_sheet' => env('ROBOT_API_MIA_LICENSE_SHEET_COLUMN', 'lic_scheet'),
                'sum_ap' => env('ROBOT_API_MIA_SUM_AP_COLUMN', 'sum_ap'),
                'address' => env('ROBOT_API_MIA_ADDRESS_COLUMN', 'addr'),
            ],
        ],
    ],
];
