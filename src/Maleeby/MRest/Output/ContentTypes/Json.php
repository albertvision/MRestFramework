<?php

namespace Maleeby\MRest\Output\ContentTypes;

class Json implements \MRest\Output\IOutput {
    public static function getHeader() {
        return 'application/json';
    }
    public static function parse($data) {
        return json_encode($data);
    }

}