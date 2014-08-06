<?php

namespace MRest\Output;

interface IOutput {
    public static function getHeader();
    public static function parse($data);
}
