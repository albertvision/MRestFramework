<?php

namespace Maleeby\MRest;

class Routing {

    private static $_uri = null;

    public static function dispatch() {
        $uriInfo = self::analizeUri();
        $uri = $uriInfo['uri'];
        $uriItems = explode('/', $uri);
        $httpMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $className = '\App\\' . ($uriItems[0] ? $uriItems[0] : MRest::getConfig()['defaultRouteClass']);

        if (!class_exists($className)) {
            throw new \Exception('Application class [' . $className . '] was not found', 404);
        }
        if (!is_callable([$className, $httpMethod])) {
            throw new \Exception('Method [' . $httpMethod . '] not allowed', 405);
        }
        if (isset($uriItems[0])) {
            unset($uriItems[0]);
        }
        return call_user_func_array([$className, $httpMethod], $uriItems);
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
        
        if(!isset($uriInfo['dirname']) || $uriInfo['dirname'] == '.') {
            $uriInfo['dirname'] = '';
        }
        
        return [
            'type' => isset($uriInfo['extension']) ? $uriInfo['extension'] : MRest::getConfig()['contentType'],
            'uri' => self::fixUri($uriInfo['dirname'].'/'.$uriInfo['filename'])
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

}
