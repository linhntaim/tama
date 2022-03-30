<?php

return [
    'console' => [
        'commands' => [
            'logging_except' => [
                Illuminate\Queue\Console\WorkCommand::class,
                Illuminate\Console\Scheduling\ScheduleRunCommand::class,
            ],
        ],
    ],
];