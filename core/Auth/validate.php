<?php
header("Access-Control-Allow-Origin: http://localhost:3001");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once ("vendor/autoload.php");
require('Functions.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    $headers = apache_request_headers();
    if (isset($headers["Authorization"]))
    {
        $token = str_ireplace("bearer ", "", $headers["Authorization"]);

        echo (new Functions)->validateToken($token);
    }

}
catch(\Exception $e)
{
    var_dump($e->getMessage());
    exit;
}





