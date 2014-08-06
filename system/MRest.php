<?php

namespace MRest;

class MRest {

    private static $_config = [
        'appDir' => '../app',
        'contentType' => 'Json',
        'defaultRouteClass' => 'Index'
    ];

    public function __construct($config = []) {
        if (!is_array($config)) {
            throw new \Exception('Invalid configuration array', 500);
        }
        self::$_config = array_merge(self::$_config, $config);
        self::$_config['appDir'] = realpath(self::$_config['appDir']);
        if (!self::$_config['appDir'] || !is_readable(self::$_config['appDir'])) {
            throw new \Exception('Invalid application directory [' . $config['appDir'] . ']', 500);
        }

        define('APP_PATH', self::$_config['appDir']);
        define('SYS_PATH', realpath(__DIR__));
        define('PUBLIC_PATH', realpath(''));
        define('BASE_URI', str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', PUBLIC_PATH)));

        require SYS_PATH . DIRECTORY_SEPARATOR . 'AutoLoader.php';

        AutoLoader::init();
        AutoLoader::registerNamespace('\MRest', SYS_PATH);
        AutoLoader::registerNamespace('\App', APP_PATH);

        $userContentType = Routing::analizeUri();
        Output\Output::init($userContentType['type']);

        set_exception_handler(__NAMESPACE__ . '\ExceptionHandler::handle');
    }

    public function run() {
        $routeOutput = Routing::dispatch();
        if (!is_array($routeOutput)) {
            throw new \Exception('Invalid route output', 504);
        }

        Output\Output::render($routeOutput);
    }

    public static function getConfig() {
        return self::$_config;
    }

}
