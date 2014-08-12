<?php

namespace Maleeby\MRest;

class Routing {

    /**
     * Current URI
     * @var string
     */
    private static $_uri = null;
    
    /**
     * Routing configuration
     * @var array
     */
    private static $_config = [];

    /**
     * Initialization of the Routing class
     * 
     * @return mixed
     * @throws \Exception
     */
    public static function dispatch() {
        self::$_config = MRest::getConfig('routing');

        $routeData = self::_callDispatcher(self::$_config['dispatcher']);

        if (!class_exists($routeData['class']) || !(new \ReflectionClass($routeData['class']))->isInstantiable()) {
            throw new \Exception('Application class [' . $routeData['class'] . '] was not found', 404);
        }
        $class = new $routeData['class']();

        if (!is_callable([$class, $routeData['method']])) {
            throw new \Exception('Method [' . $routeData['method'] . '] not allowed', 405);
        }

        return call_user_func_array([$class, $routeData['method']], $routeData['attributes']);
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
        $uriInfo = self::analizeUri();
        $uri = $uriInfo['uri'];
        $uriItems = explode('/', $uri);
        $attributes = [];

        foreach ($config['routes'] as $routePattern => $className) {
            if (preg_match('#' . $routePattern . '#', $uri)) {
                $class = $className;
                $itemCount = count(explode('/', str_replace('\\/', '/', $routePattern)));

                for ($i = 0; $i < $itemCount; $i++) {
                    unset($uriItems[$i]);
                }
                $attributes = array_values($uriItems);
                break;
            }
        }

        if (!$class) {
            $class = self::_getController($uri, 'App\\');
            $classUri = self::_getControllerBaseUri($class);
            
            if(strpos($uri, $classUri) === 0 && strlen($uri) > strlen($classUri)) {
                $attributesUri = substr($uri, strlen($classUri)+1);
                $attributes = explode('/', $attributesUri);
            }
        }
        print_r([
            'class' => self::fixNamespace($class ? $class : self::_getController($uri, 'App\\')),
            'method' => strtolower($_SERVER['REQUEST_METHOD']),
            'attributes' => $attributes
        ]);
        exit;
        return [
            'class' => self::fixNamespace($class ? $class : self::_getController($uri, 'App\\')),
            'method' => strtolower($_SERVER['REQUEST_METHOD']),
            'attributes' => $attributes
        ];
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
        $dir = realpath(APP_PATH . '/../' . $namespace) . DIRECTORY_SEPARATOR;

        if (!is_dir($dir . $uri)) {
            $uriParts = explode('/', $uri, 2);
            if (is_dir($dir . $uriParts[0])) {
                return self::_getController($uriParts[1], $namespace . $uriParts[0] . '\\');
            } elseif (is_file($dir . $uriParts[0] . '.php')) {
                $uri = $uriParts[0];
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

        return self::fixNamespace($namespace . '\\' . $uri);
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

    /**
     * Get the base URI of controller
     * 
     * @param string $class Class name
     * @return string Class base URI
     */
    private static function _getControllerBaseUri($class) {
        return self::fixUri(substr($class, strlen('App\\')));
    }

    /**
     * Get the current URI
     * @return string The current URI
     */
    public static function getUri() {
        if (self::$_uri === null) {
            $uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
            self::$_uri = self::fixUri(substr($uri, strlen(BASE_URI)));
        }
        return self::$_uri;
    }

    /**
     * Make an analize of the URI. 
     * Returns the output content type and the URI
     * 
     * @return array
     */
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

    /**
     * Fix the URI. Removes the multiple slashes
     * 
     * @param string $url URI
     * @return string The cleaned URI
     */
    public static function fixUri($url) {
        $url = preg_replace('#/+|\\\+#', '/', $url);

        if ($url[0] == '/') {
            $url = substr($url, 1, strlen($url));
        } if (substr($url, -1) == '/') {
            $url = substr($url, 0, strlen($url) - 1);
        }

        return $url;
    }

    /**
     * Fix the path. Removes the multiple / and \ from the path and replaces them with the OS directory separator
     * @param string $path Path
     * @return string Filtered path
     */
    public static function fixPath($path) {
        return preg_replace('#\\\+|/+#', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Fix the namespace. Removes the multiple \ and / from it and replaces them with \
     * 
     * @param string $namespace Namespace
     * @return string Filtered namespace
     */
    public static function fixNamespace($namespace) {
        $namespace = preg_replace('/\\\+|\/+/', '\\', $namespace);

        return substr($namespace, -1) == '\\' ? substr($namespace, 0, strlen($namespace) - 1) : $namespace;
    }

}
