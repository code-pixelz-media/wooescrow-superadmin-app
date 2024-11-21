<?php


$plugin_path = $_SERVER['DOCUMENT_ROOT'] . '/core/libs/wooescrow.zip';
$secret_key = 'wooescrow';
$expiry_time = 300; //5 minutes

function validate_license_key($license_key, $email_address) {
    require 'DatabaseController.php';

    $dbConnection = new PDO('mysql:host=localhost;dbname=sql_superadmin_w', 'sql_superadmin_w', '9143be6cc92fb');
    $licenseController = new DatabaseController($dbConnection);

 
    $email = filter_var($email_address, FILTER_VALIDATE_EMAIL);
    $licenseKey = trim($license_key);


    if (empty($email) || empty($licenseKey)) {
        return false;
    }

    $license = $licenseController->verifyLicenseKey($email, $licenseKey);

    return $license && $license['license_status'] === 'active' && strtotime($license['valid_until']) > time();
}


function generate_signed_url($plugin_path) {
    global $secret_key, $expiry_time;
    $expiry = time() + $expiry_time;
    $hash = hash_hmac('sha256', $plugin_path . $expiry, $secret_key);
    return "https://superadmin-wooescrow.codepixelz.tech/core/update.php?file=wooescrow.zip&expiry=$expiry&hash=$hash";
}

// Validate the signed URL
function validate_signed_url($file, $expiry, $hash) {
    global $secret_key;
    $plugin_path = $_SERVER['DOCUMENT_ROOT'] . "/core/libs/$file";
    $expected_hash = hash_hmac('sha256', $plugin_path . $expiry, $secret_key);
    return hash_equals($expected_hash, $hash) && time() < $expiry;
}

// Process direct download requests
if (isset($_GET['file'], $_GET['expiry'], $_GET['hash'])) {
    if (validate_signed_url($_GET['file'], $_GET['expiry'], $_GET['hash'])) {
        $plugin_path = $_SERVER['DOCUMENT_ROOT'] . '/core/libs/' . basename($_GET['file']);
        if (file_exists($plugin_path)) {
            header("Content-Type: application/zip");
            header("Content-Disposition: attachment; filename=wooescrow.zip");
            header("Content-Length: " . filesize($plugin_path));
            readfile($plugin_path);
            exit;
        } else {
            header("HTTP/1.1 404 Not Found");
            echo 'Plugin file not found';
            exit;
        }
    } else {
        header("HTTP/1.1 403 Forbidden");
        echo 'Invalid or expired download link';
        exit;
    }
}

// Process update request to generate a signed URL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $license_key = $_POST['license_key'] ?? '';
    $email_address =  $_POST['email_address'] ?? '';
    if (validate_license_key($license_key , $email_address)) {
        echo generate_signed_url($plugin_path); 
        exit;
    } else {
        header("HTTP/1.1 403 Forbidden");
        echo 'Invalid license key';
        exit;
    }
} else {
    header("HTTP/1.1 405 Method Not Allowed");
    echo 'Method Not Allowed';
    exit;
}


