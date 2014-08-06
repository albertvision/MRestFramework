<?php

namespace MRest;

class AutoLoader {

    private static $_namespaces = [];
    private static $_instance = null;

    private function __construct() {
        spl_autoload_register('self::autoloadRegister');
    }

    public static function init() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function registerNamespace($namespaceName, $namespaceLocation) {
        if (isset(self::$_namespaces[$namespaceName])) {
            throw new \Exception('Namespace [' . $namespaceName . '] is already registered', 500);
        }
        $namespacePath = realpath($namespaceLocation);
        if (!$namespacePath || !is_readable($namespacePath)) {
            throw new \Exception('Namespace location [' . $namespaceLocation . '] was not found', 500);
        }

        self::$_namespaces[$namespaceName[0] == '\\' ? substr($namespaceName, 1) : $namespaceName] = $namespacePath;
    }

    public function autoloadRegister($className) {
        foreach (self::$_namespaces as $name => $path) {
            if (strpos($className, $name) === 0) {
                $_filePath = realpath($path . DIRECTORY_SEPARATOR . substr($className, strlen($name) + 1) . '.php');
                break;
            }
        }
        
        if (!$_filePath || !is_readable($_filePath)) {
            throw new \Exception('Class [' . $className . '] was not found', 404);
        }
        include $_filePath;
    }

}
