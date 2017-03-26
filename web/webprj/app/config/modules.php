<?php
/**
 * Register application modules
 */

$application->registerModules(
    array(
        'home' => array(
            'className' => 'App\Foreground\Module',
            'path' => '../app/modules/foreground/Module.php'
        ),
        'admin' => array(
            'className' => 'App\Background\Module',
            'path' => '../app/modules/background/Module.php'
        ),
        'wap' => array(
            'className' => 'App\Wap\Module',
            'path' => '../app/modules/wap/Module.php'
        )
    )
);

