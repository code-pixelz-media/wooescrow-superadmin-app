<?php

require 'DatabaseController.php';

header('Content-Type: application/json');

$dbConnection = new PDO('mysql:host=localhost;dbname=wooescrow_superadmin', 'root', 'root');
$licenseController = new DatabaseController($dbConnection);
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['path']) ? explode('/', trim($_GET['path'], '/')) : [];


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

switch ($method) {
    case 'POST':
        if ($request[0] === 'license') {

            if ($request[1] == 'create') {

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

            } elseif ($request[1] === 'verify') {

                $inputData = json_decode(file_get_contents('php://input'), true);
                if (!isset($inputData['email']) || !isset($inputData['license_key'])) {
                    respond('error', ['message' => 'Email and license key are required.'], 400);
                }

                $email = filter_var($inputData['email'], FILTER_VALIDATE_EMAIL);
                $licenseKey = trim($inputData['license_key']);
                $site_url = $inputData['site_url'];

                if (!$email || !$licenseKey) {
                    respond('error', ['message' => 'Invalid email or license key format.'], 400);
                }

                $license = $licenseController->verifyLicenseKey($email, $licenseKey);

                if ($license) {
                    if ($license['license_status'] === 'active' && strtotime($license['valid_until']) > time()) {
                        $response = $licenseController->insertSiteIrl($licenseKey,$site_url);
                        // respond('success', ['message' => 'License key testing.'], 201);
                        respond('success', [
                            'message' => 'License key is valid.',
                            'license_details' => [
                                'status' => $license['license_status'],
                                'valid_until' => $license['valid_until'],
                                'next_renewal' => $license['next_renewal'],
                                'insert' => $response,
                            ],201
                        ]);
                    } else {
                        respond('error', ['message' => 'License key is inactive or expired.'], 403);
                    }
                } else {
                    respond('error', ['message' => 'License key not found.'], 404);
                }
            } elseif($request[1] === 'deactivate'){
				$license_data = json_decode(file_get_contents('php://input'), true);
				$email = filter_var($license_data['email'], FILTER_VALIDATE_EMAIL);
				$licenseKey = trim($license_data['key']);
				if (!empty($email) || !empty($licenseKey)) {
					$license = $licenseController->verifyLicenseKey($email, $licenseKey);
					if ($license) {
						$license_key_id = $license['id'];
						$license_status = $license['license_status'];
						if (isset($license_key_id) && !empty($license_key_id)) {
							if($license_status == 'active'){
								$result = $licenseController->deactivateLicense($license_key_id);
								if ($result) {
									respond('success', ['message' => 'License key deactivated successfully'], 200);
								} else {
									respond('error', ['message' => 'Failed to deactivate license key.Cannot find license on database'], 500);
								}
							}else{
								respond('error', ['message' => 'Failed to deactivate license key.License key already deactivated'], 500);
							}
						} else {
							respond('error', ['message' => 'License data not found'], 400);
						}
					}
				}else{
					respond('error', ['message' => 'Invalid email or license key format.'], 400);
				}

            } else {
                respond('error', ['message' => 'Invalid endpoint'], 404);
            }
        }
        break;

    case 'GET':
        if ($request[0] === 'license') {
            if (isset($request[1]) && $request[1] === 'all') {
                $licenses = $licenseController->fetchAllLicenseKeys();
                respond('success', $licenses);
            } elseif (isset($request[1]) && is_numeric($request[1])) {
                $id = (int) $request[1];
                $license = $licenseController->fetchLicenseKeyById($id);
                if ($license) {
                    respond('success', $license);
                } else {
                    respond('error', ['message' => 'License not found'], 404);
                }
            } else {
                respond('error', ['message' => 'Invalid endpoint'], 400);
            }
        } else {
            respond('error', ['message' => 'Invalid endpoint'], 404);
        }
        break;

    case 'PUT':
        if ($request[0] === 'license' && isset($request[1])) {
            $id = (int) $request[1];
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data) {
                $result = $licenseController->updateLicenseKey($id, $data);
                if ($result) {
                    respond('success', ['message' => 'License key updated successfully'], 200);
                } else {
                    respond('error', ['message' => 'Failed to update license key'], 500);
                }
            } else {
                respond('error', ['message' => 'Invalid data'], 400);
            }
        } else {
            respond('error', ['message' => 'Invalid endpoint'], 404);
        }
        break;

    case 'DELETE':
        if ($request[0] == 'license' && $request[1] == 'delete') {
            $license_data = json_decode(file_get_contents('php://input'), true);
            $email = filter_var($license_data['email_address'], FILTER_VALIDATE_EMAIL);
            $licenseKey = trim($license_data['license_key']);
            if (!$email || !$licenseKey) {
                respond('error', ['message' => 'Invalid email or license key format.'], 400);
            }
            $license = $licenseController->verifyLicenseKey($email, $licenseKey);
            if ($license) {
                $license_key_id = $license['id'];
                if (isset($license_key_id) && !empty($license_key_id)) {
                    $result = $licenseController->deleteLicenseKey($license_key_id);
                    if ($result) {
                        respond('success', ['message' => 'License key deleted successfully'], 200);
                    } else {
                        respond('error', ['message' => 'Failed to delete license key.Cannot find license on database'], 500);
                    }
                } else {
                    respond('error', ['message' => 'License data not found'], 400);
                }
            } else {
                respond('error', ['message' => 'License data mismatched'], 404);
            }
          
        }

        break;

    default:
        respond('error', ['message' => 'Method Not Allowed'], 405);
        break;
}
