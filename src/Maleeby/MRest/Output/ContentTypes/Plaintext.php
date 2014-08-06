<?php

namespace Maleeby\MRest\Output\ContentTypes;

class Plaintext implements \MRest\Output\IOutput {
    public static function getHeader() {
        return 'text/plain';
    }

    public static function parse($data) {
        return http_build_query($data);
    }

//put your code here
}
