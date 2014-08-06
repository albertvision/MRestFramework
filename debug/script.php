<?php
require 'Curl.php';

$curl = new Curl();

$curl->get('http://mrest/frame/users/1.plaintext');
//echo $curl->error_code;
echo print_r(($curl->response));