<?php

namespace Maleeby\MRest\Output;

interface IOutput {
    public static function getHeader();
    public static function parse($data);
}
