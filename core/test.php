<?php
header("Content-Type: application/json");
require 'DatabaseController.php';
// Database connection
try {
    $dbConnection = new PDO('mysql:host=localhost;dbname=wooescrow_superadmin', 'root', 'root');
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}
$licenseController = new DatabaseController($dbConnection);
// Get the relativeUriArrayed URI and HTTP method
$relativeUriArrayUri = $_SERVER['REQUEST_URI'];
$relativeUriArrayMethod = $_SERVER['REQUEST_METHOD'];

// Remove the base path dynamically
$scriptName = dirname($_SERVER['SCRIPT_NAME']); // e.g., /superadmin/wooescrow-superadmin-app/core
$relativeUri = str_replace($scriptName, '', $relativeUriArrayUri);

// Remove leading/trailing slashes and split into an array
$relativeUriArray = explode('/', trim($relativeUri, '/'));
// Route handling based on the relative URI
switch ($relativeUriArrayMethod) {
    case 'POST':
        // $rawInput = file_get_contents('php://input');
        // $data = json_decode($rawInput, true);

        // if ($data === null) {
        //     echo json_encode([
        //         "status" => "error",
        //         "message" => "Invalid JSON",
        //         "rawInput" => $rawInput,
        //         "jsonError" => json_last_error_msg()
        //     ]);
        //     exit;
        // }
        if ($relativeUriArray[1] === 'licenses') {
            if ($relativeUriArray[2] === 'create') {

                $data = json_decode(file_get_contents('php://input'), true);

                if ($data) {
                    $result = $licenseController->insertLicenseKey($data);
                    if ($result) {
                        respond('success', ['message' => 'License key created successfully'], 201);
                    } else {
                        respond('error', ['message' => 'Failed to create license key'], 500);
                    }
                } else {
                    respond('error', ['message' => 'Invalid data'], 400);
                }
            } elseif ($relativeUriArray[2] === 'verify') {

                $inputData = json_decode(file_get_contents('php://input'), true);
                if (!isset($inputData['email_address']) || !isset($inputData['license_key'])) {
                    respond('error', ['message' => 'Email and license key are required.'], 400);
                }

                $email = filter_var($inputData['email_address'], FILTER_VALIDATE_EMAIL);
                $licenseKey = trim($inputData['license_key']);
                $site_url = $inputData['site_url'];

                if (!$email || !$licenseKey) {
                    respond('error', ['message' => 'Invalid email or license key format.'], 400);
                }

                $license = $licenseController->verifyLicenseKey($email, $licenseKey, $site_url);
                if ($license) {
                    if ($license['license_status'] === 'active' && strtotime($license['valid_until']) > time() && $license['total_sites'] > $license['active_sites']) {
                        $response = $licenseController->insertSiteIrl($licenseKey, $site_url);
                        respond('success', [
                            'message' => 'License key is valid.',
                            'license_details' => [
                                'status' => $license['license_status'],
                                'valid_until' => $license['valid_until'],
                                'next_renewal' => $license['next_renewal'],
                                'verification' => $response ? 'verified for ' . $site_url . '.' : 'Error on insertion of site url.',
                            ],
                            201
                        ]);
                    } else {
                        respond('error', ['message' => 'License key is inactive or expired.'], 403);
                    }
                } else {
                    respond('error', ['message' => 'License key not found.'], 404);
                }
            } elseif ($relativeUriArray[2] === 'deactivate') {
                $license_data = json_decode(file_get_contents('php://input'), true);
                $email = filter_var($license_data['email_address'], FILTER_VALIDATE_EMAIL);
                $licenseKey = trim($license_data['license_key']);
                $site_url = $license_data['site_url'];
                if (!empty($email) || !empty($licenseKey)) {
                    $license = $licenseController->checkLicenseOnDeactivate($email, $licenseKey, $site_url);
                    if (!is_string($license)) {
                        $license_key_id = $license['id'];
                        $license_status = $license['license_status'];
                        if (isset($license_key_id) && !empty($license_key_id)) {
                            if ($license_status == 'active') {
                                $result = $licenseController->deactivateLicense($license_key_id,$site_url);
                                if ($result) {
                                    respond('success', ['message' => 'License key deactivated successfully'], 200);
                                } else {
                                    respond('error', ['message' => 'Failed to deactivate license key.Cannot find license on database'], 500);
                                }
                            } else {
                                respond('error', ['message' => 'Failed to deactivate license key.License key already deactivated'], 500);
                            }
                        } else {
                            respond('error', ['message' => 'License data not found'], 400);
                        }
                    } else {
                        respond('error', ['message' => $license], 400);
                    }
                } else {
                    respond('error', ['message' => 'Invalid email or license key format.'], 400);
                }
            } else {
                respond('error', ['message' => 'Invalid endpoint'], 404);
            }
        }
        break;
    case 'GET':
        if (isset($relativeUriArray[1]) && $relativeUriArray[1] === 'licenses') {
            if (isset($relativeUriArray[2]) && is_numeric($relativeUriArray[2])) {
                $id = (int) $relativeUriArray[2];
                $license = $licenseController->fetchLicenseKeyById($id);
                if ($license) {
                    respond('success', $license);
                } else {
                    respond('error', ['message' => 'License not found'], 404);
                }
            } else if (isset($relativeUriArray[2]) && $relativeUriArray[2] === 'all') {
                $licenses = $licenseController->fetchAllLicenseKeys();
                respond('success', $licenses);
            } else if (isset($relativeUriArray[2]) && $relativeUriArray[2] === 'activeSites') {
                $activeSites = $licenseController->fetchAllActiveSites();
                if (!empty($activeSites)) {
                    respond('success', $activeSites);
                } else {
                    respond('error', ['message' => 'No data found'], 404);
                }
            } else if (isset($relativeUriArray[2]) && $relativeUriArray[2] === 'deactiveSites') {
                $deactiveSites = $licenseController->fetchAllADeactiveSites();
                if (!empty($deactiveSites)) {
                    respond('success', $deactiveSites);
                } else {
                    respond('error', ['message' => 'No data found'], 404);
                }
            } else {
                $message = [];
                respond('error', $message, 400);
            }
        } else {
            $message = [];
            respond('error', $message, 400);
        }
        break;
    case 'PUT':
        if ($relativeUriArray[1] === 'licenses' && isset($relativeUriArray[1])) {
            if ($relativeUriArray[2] === 'update' && isset($relativeUriArray[2])) {
                $id = (int) $relativeUriArray[3];
                $data = json_decode(file_get_contents('php://input'), true);
                if ($data) {
                    $result = $licenseController->updateLicenseKey($id, $data);
                    if (!is_string($result)) {
                        respond('success', ['message' => 'License key updated successfully'], 200);
                    } else {
                        respond('error', ['message' => $result], 500);
                    }
                } else {
                    respond('error', ['message' => 'Invalid data'], 400);
                }
            } else {
                respond('error', ['message' => 'Invalid request'], 400);
            }
        } else {
            respond('error', ['message' => 'Invalid endpoint'], 404);
        }
        break;
    case 'DELETE':
        if ($relativeUriArray[1] === 'licenses' && isset($relativeUriArray[1])) {
            if ($relativeUriArray[2] === 'delete' && isset($relativeUriArray[2])) {
                $id = $relativeUriArray[3];

                $result = $licenseController->deleteLicenseKey($id);
                if (!is_string($result)) {
                    respond('success', ['message' => 'License key deleted successfully'], 200);
                } else {
                    respond('error', ['message' => $result], 404);
                }
            } else {
                respond('error', ['message' => 'Invalid request'], 400);
            }
        }

        break;
    default:
        respond('error', ['message' => 'Method Not Allowed'], 405);
        break;
}




function respond($status, $data, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'status' => $status,
        'timestamp' => date('Y-m-d H:i:s'),
        'total_results' => count($data),
        'data' => $data,
    ]);
    exit;
}
