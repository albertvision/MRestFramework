<?php

namespace App;

class MainClass {

    private static $_db = null;
    private static $_db2 = null;

    /**
     * 
     * @return \MRest\Libs\FluentPdo\FluentPDO
     */
    protected static function db() {
        if (self::$_db === null) {
            self::$_db = new \MRest\Libs\DB('main');
        }
        return self::$_db;
    }

}
