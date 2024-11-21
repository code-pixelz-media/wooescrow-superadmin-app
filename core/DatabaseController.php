<?php

class DatabaseController
{

    private $conn;

    private $licenseTableName = 'wooescrow_license_keys';
    private $siteurlTableName = 'wooescrow_siteurl';

    public function __construct($dbConnection)
    {

        $this->conn = $dbConnection;
        // $this->deleteTable();
        $this->createTable();
    }
    private function deleteTable()
    {
        // Prepare the SQL statement
        $sql = "DROP TABLE IF EXISTS `{$this->licenseTableName}`";

        // Execute the query
        $this->conn->exec($sql);
    }

    private function createTable()
    {
        $sqlForLisence = "CREATE TABLE IF NOT EXISTS {$this->licenseTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            email_address VARCHAR(255) NOT NULL,
            license_key VARCHAR(255) NOT NULL,
            license_status VARCHAR(50) DEFAULT 'active' NOT NULL,
            total_sites INT(11) NOT NULL,
            active_sites INT(11) NOT NULL,
            created_at DATE NOT NULL,
            valid_until DATE NOT NULL,
            next_renewal DATE NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->conn->exec($sqlForLisence);


        $sqlForSiteUrl = "CREATE TABLE IF NOT EXISTS {$this->siteurlTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            site_url VARCHAR(255) NOT NULL,
            status VARCHAR(50) DEFAULT 'active' NOT NULL,
            license_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (license_id) REFERENCES {$this->licenseTableName} (id)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->conn->exec($sqlForSiteUrl);
    }

    public function insertLicenseKey($data)
    {
        $sql = "INSERT INTO {$this->licenseTableName} (order_id, user_id, email_address, site_url, license_key, license_status, created_at, valid_until, next_renewal)
                VALUES (:order_id, :user_id, :email_address, :site_url, :license_key, :license_status, :total_sites, :active_sites, :created_at, :valid_until, :next_renewal)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }


    public function fetchAllLicenseKeys()
    {
        $sql = "SELECT * FROM {$this->licenseTableName}";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function fetchLicenseKeyById($id)
    {
        $sql = "SELECT * FROM {$this->licenseTableName} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function updateLicenseKey($id, $data)
    {
        $sql = "UPDATE {$this->licenseTableName} SET 
                    order_id = :order_id, 
                    user_id = :user_id, 
                    email_address = :email_address, 
                    site_url = :site_url, 
                    license_key = :license_key, 
                    license_status = :license_status, 
                    valid_until = :valid_until, 
                    next_renewal = :next_renewal
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function deleteLicenseKey($id)
    {
        $sql = "DELETE FROM {$this->licenseTableName} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }


    public function deactivateLicense($id)
    {
        $sql = "UPDATE {$this->licenseTableName} SET license_status = :license_status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $licenseStatus = 'inactive';
        $stmt->bindParam(':license_status', $licenseStatus, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function verifyLicenseKey($email, $licenseKey)
    {
        $sql = "SELECT * FROM {$this->licenseTableName} WHERE email_address = :email_address AND license_key = :license_key ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email_address', $email, PDO::PARAM_STR);
        $stmt->bindParam(':license_key', $licenseKey, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertSiteIrl($licenseKey, $site_url)
    {
        try {
            // Step 1: Retrieve the existing serialized value
            $sql = "SELECT `site_url` FROM {$this->licenseTableName} WHERE license_key = :license_key";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':license_key', $licenseKey, PDO::PARAM_STR);
            $stmt->execute();

            $existingSerializedValue = $stmt->fetchColumn();

            if ($existingSerializedValue !== false) {
                // Step 2: Unserialize the value into an array
                $siteUrls = unserialize($existingSerializedValue);

                // Ensure it's an array before appending
                if (!is_array($siteUrls)) {
                    $siteUrls = [];
                }

                // Sanitize the new URL
                $site_url = filter_var($site_url, FILTER_SANITIZE_URL);

                // Step 3: Check if the URL already exists in the array
                if (!in_array($site_url, $siteUrls, true)) {
                    // Step 4: Append the new value
                    $siteUrls[] = $site_url;

                    // Step 5: Serialize the updated array
                    $updatedSerializedValue = serialize($siteUrls);

                    // Step 6: Update the table with the new serialized value
                    $updateSql = "UPDATE {$this->licenseTableName} SET site_url = :site_url WHERE license_key = :license_key";
                    $updateStmt = $this->conn->prepare($updateSql);
                    $updateStmt->bindParam(':site_url', $updatedSerializedValue, PDO::PARAM_STR);
                    $updateStmt->bindParam(':license_key', $licenseKey, PDO::PARAM_STR);

                    return $updateStmt->execute();
                } else {
                    // If the URL already exists, no update is performed
                    return "Site URL already exists, no update needed.";
                }
            } else {
                return "License key not found.";
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
