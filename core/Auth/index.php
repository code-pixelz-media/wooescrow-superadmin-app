<?php
header("Access-Control-Allow-Origin: http://localhost:3001");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
//header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require_once ("vendor/autoload.php");
require('Functions.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try
{
    $host = 'db';
    $dbname = 'zarx';
    $username = 'manu';
    $password = 'awesomemanu';

    $conn = new mysqli($host, $username, $password, $dbname);

    $result = $conn->query("select * from users");

    $authenticated  = false;

    if(mysqli_num_rows($result))
    {
        while($row = mysqli_fetch_assoc($result))
        {
            if($row["email"] == "test@test.com" && $row["password"] == '1234')
            {
                $authenticated = true;
            }
        }
    }

    echo json_encode([
        "authenticated" => $authenticated,
        "token" => (new Functions)->generateToken()
    ]);


}
catch(Exception $e)
{
    var_dump($e->getMessage());
    exit;
}