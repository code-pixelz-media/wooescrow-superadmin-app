<?php
require 'vendor/autoload.php';
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// $dbConnection = new PDO('mysql:host=localhost;dbname=sql_superadmin_w', 'sql_superadmin_w', '9143be6cc92fb');
// $licenseController = new DatabaseController($dbConnection);

class Functions
{
    public $conn;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'sql_superadmin_w';
        $username = 'sql_superadmin_w';
        $password = '9143be6cc92fb';

        $this->conn = new mysqli($host, $username, $password, $dbname);

        // Ensure keys directory exists
        if (!file_exists('keys')) {
            mkdir('keys', 0777, true);
        }

        // $this->generatePrivatePublicKey();
    }

    private function generatePrivatePublicKey()
    {
        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ];

        // Generate private key
        $privateKey = openssl_pkey_new($config);

        // Check if private key generation failed
        if (!$privateKey) {
            throw new \Exception('Failed to generate private key: ' . openssl_error_string());
        }

        // Export private key
        if (!openssl_pkey_export($privateKey, $privateKeyPEM)) {
            throw new \Exception('Failed to export private key: ' . openssl_error_string());
        }

        // Get public key details
        $publicKeyDetails = openssl_pkey_get_details($privateKey);
        if (!$publicKeyDetails || !isset($publicKeyDetails['key'])) {
            throw new \Exception('Failed to extract public key details: ' . openssl_error_string());
        }

        $publicKeyPEM = $publicKeyDetails['key'];

        // Save keys to files
        file_put_contents('keys/private_key.pem', $privateKeyPEM);
        file_put_contents('keys/public_key.pem', $publicKeyPEM);

        echo "Private and public keys generated successfully";
    }

    public function generateToken(): string
    {
        $privateKey = file_get_contents("keys/private_key.pem");

        $payload = [
            "iss" => "zarx",
            "sub" => "token_tutorial",
            "iat" => time(),
            "exp" => time() + 3600,
            "uid" => "12345"
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    public function validateToken($token)
    {
        $publicKey = file_get_contents("keys/public_key.pem");

        try {
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            if ($decoded->sub == "token_tutorial") {
                return true;
            }

            return false;

        } catch (ExpiredException $e) {
            if ($e->getMessage() == "Expired token") {
                $this->refreshToken();
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            exit;
        }
    }

    private function refreshToken()
    {
        $result = $this->conn->query("SELECT * FROM refresh_token");

        if (mysqli_num_rows($result)) {
            while ($row = mysqli_fetch_assoc($result)) {
                if ($this->timeInFuture($row["expires"])) {
                    $this->generateToken();
                    exit;
                }
            }
        }
    }

    private function timeInFuture($expires): bool
    {
        $timeToCheck = strtotime($expires);
        $currentTime = time();

        return $timeToCheck >= $currentTime;
    }
}