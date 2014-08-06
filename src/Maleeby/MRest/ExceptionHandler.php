<?php

namespace Maleeby\MRest;

class ExceptionHandler {
    public function __construct() {
        ;
    }
    public function handle(\Exception $err) {
        $output = [
            'type' => 'EXCEPTION',
            'message' => $err->getMessage(),
            'file' => $err->getFile(),
            'line' => $err->getLine(),
            'trace' => $err->getTrace()
        ];
        http_response_code(is_int($err->getCode()) ? $err->getCode() : 500);
        Output\Output::render($output);
        exit;
    }
}
