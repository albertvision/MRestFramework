<?php

namespace Maleeby\MRest;

class Routing {

    private static $_uri = null;
    private static $_uriData;
    private static $_config = [];

    public static function dispatch() {
        self::$_config = MRest::getConfig('routing');

        $uriInfo = self::analizeUri();
        $uri = $uriInfo['uri'];
        $uriItems = explode('/', $uri);
        $httpMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $className = self::fixNamespace(self::_callDispatcher(self::$_config['dispatcher']));
        
        $reflectionClass = new \ReflectionClass($className);
        if (!$reflectionClass->isInstantiable()) {
            throw new \Exception('Application class [' . $className . '] was not found', 404);
        }
        $class = new $className();

        if (!is_callable([$class, $httpMethod])) {
            throw new \Exception('Method [' . $httpMethod . '] not allowed', 405);
        }
        if (isset($uriItems[0])) {
            unset($uriItems[0]);
        }
        return call_user_func_array([$class, $httpMethod], $uriItems);
    }

    /**
     * Calls the route dispatcher which is specified in the configuration.
     * 
     * @param string $dispatcherCallback Dispatcher Callback
     * 
     * @return object
     * @throws \Exception
     */
    private static function _callDispatcher($dispatcherCallback) {
        $dispatcherCallback = $dispatcherCallback[0] !== '\\' ? __NAMESPACE__ . '\\' . $dispatcherCallback : $dispatcherCallback;

        if (!is_callable($dispatcherCallback)) {
            throw new \Exception('Routing dispacher [' . $dispatcherCallback . '] cannot be called.', 500);
        }

        return call_user_func_array($dispatcherCallback, [
            self::$_uri, self::$_config
        ]);
    }

    /**
     * Main dispatcher. Looks for patterns from the config.
     * 
     * @param string $uri Current URI
     * @param array $config Routing configuration
     * 
     * @return string Controller's callback
     */
    private static function _mainDispatcher($uri, $config) {
        foreach($config['routes'] as $routePattern => $className) {
            if(preg_match('#'.$routePattern.'#', $uri)) {
                $class = $className;
                break;
            }
        }
        
        return $class ? $class : self::_getController($uri, 'App\\');
    }

    /**
     * Returns the controller's data for an URI
     * 
     * @param string $uri URI
     * @param string $namespace URI namespace
     * 
     * @return array [string uri, string namespace, array defaults]
     */
    private static function _getController($uri, $namespace) {
        $dir = self::fixPath(APP_PATH . '/../' . $namespace . '/');

        if (!is_dir($dir . $uri)) {
            $uriParts = explode('/', $uri, 2);
            if (is_dir($dir . $uriParts[0])) {
                return self::_getController($uriParts[1], $namespace . '\\' . $uriParts[0]);
            }
        } else {
            $namespace = $namespace . '\\' . str_replace('/', '\\', $uri);
            $uri = '';
        }

        if (substr($namespace, -1) == '\\') {
            $namespace = substr($namespace, 0, strlen($namespace) - 1);
        }
        if (!$uri) {
            $uri = self::getNamespaceDefaultClass($namespace, 'App\\'); // Sets the default class
        }

        return $namespace . '\\' . $uri;
    }

    /**
     * Returns the default class of $namespace.
     * 
     * @param string $namespace Namespace of the controller
     * @return array Default method and controller
     */
    public static function getNamespaceDefaultClass($namespace, $defaultNamespace) {
        if (strpos($namespace, $defaultNamespace) === 0) {
            $namespace = substr($namespace, strlen($defaultNamespace));
        }

        $defaults = self::$_config['defaults'];
        $controller = $defaults[str_replace('\\', '/', $namespace)];

        if (!$controller) {
            $controller = $defaults['*'] ? $defaults['*'] : 'Index';
        }

        return $controller;
    }

    public static function getUri() {
        if (self::$_uri === null) {
            $uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
            self::$_uri = self::fixUri(substr($uri, strlen(BASE_URI)));
        }
        return self::$_uri;
    }

    public static function analizeUri() {
        $uri = self::getUri();
        $uriInfo = pathinfo($uri);

        if (!isset($uriInfo['dirname']) || $uriInfo['dirname'] == '.') {
            $uriInfo['dirname'] = '';
        }

        return [
            'type' => isset($uriInfo['extension']) ? $uriInfo['extension'] : MRest::getConfig()['contentType'],
            'uri' => self::fixUri($uriInfo['dirname'] . '/' . $uriInfo['filename'])
        ];
    }

    public static function fixUri($url) {
        $url = preg_replace('#/+#', '/', $url);

        if ($url[0] == '/') {
            $url = substr($url, 1, strlen($url));
        } if (substr($url, -1) == '/') {
            $url = substr($url, 0, strlen($url) - 1);
        }

        return $url;
    }

    public static function fixPath($path) {
        return preg_replace('#\\\+|/+#', DIRECTORY_SEPARATOR, $path);
    }
    
    public static function fixNamespace($namespace) {
        return preg_replace('/\\\+/', '\\', $namespace);
    }

}
