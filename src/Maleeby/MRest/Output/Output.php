<?php

namespace Maleeby\MRest\Output;

class Output {

    private static $_contentType = null;
    private static $_loaded = null;

    public static function init($contentType) {
        if (self::$_loaded === null) {
            self::setContentType($contentType);
            self::$_loaded = true;
        }
    }

    public static function setContentType($contentType) {
        $className = self::_getTypeClassName($contentType);
        if (!class_exists($className)) {
            throw new \Exception('Class [' . $className . '] was not found', 500);
        }
        self::$_contentType = $contentType;
    }

    private static function _getTypeClassName($contentType) {
        return __NAMESPACE__ . '\ContentTypes\\' . $contentType;
    }

    public static function render($data) {
        if (!is_array($data)) {
            throw new \Exception('Invalid data for render', 500);
        }
        $className = self::_getTypeClassName(self::$_contentType);
        header('Content-Type: ' . $className::getHeader());
        echo $className::parse($data);
    }

}
